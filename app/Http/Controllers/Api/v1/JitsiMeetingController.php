<?php

namespace App\Http\Controllers\Api\v1;

use App\Http\Controllers\Api\v1\BaseController as Controller;

use App\Http\Traits\ApiResponser;
use App\Models\JitsiMeeting;
use App\Services\JitsiService;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rules;

class JitsiMeetingController extends Controller
{
    use ApiResponser;

    public function create(Request $request, JitsiService $jitsiService)
    {
        $validation = validator($request->all(), [
            'room_name'     => ['required'],
            'order_id'      => ['required', new Rules\Exists('orders', 'id')],
        ]);

        if ($validation->fails()) {
            return $this->errorResponse('Validation failed', Response::HTTP_UNPROCESSABLE_ENTITY, $validation->errors());
        }

        $jitsiPreferences = $jitsiService->getPreferences();

        if (! isset($jitsiPreferences['domain_url'])) {
            return $this->errorResponse('Jitsi is not configured', Response::HTTP_INTERNAL_SERVER_ERROR);
        }

        ['domain_url' => $domainUrl] = $jitsiPreferences;
        [$meeting, $authToken]       = DB::transaction(function () use ($validation, $jitsiService) {
            [
                'room_name' => $room,
                'order_id'  => $order
            ] = $validated = $validation->validated();

            $meeting    = $jitsiService->fetchMeeting($room, $order, $validated);
            $invitation = $jitsiService->join(
                $meeting,
                /** moderator: */
                $meeting->wasRecentlyCreated
            );

            return [$meeting, $invitation->createToken()];
        });

        /**
         * @var \App\Models\JitsiMeeting $meeting
         */

        rescue(fn() => $jitsiService->notify());

        return $this->successResponse([
            'meeting'     => $meeting->unsetRelations(),
            'room_name'   => $meeting->room_name,
            'meeting_url' => "{$domainUrl}/{$meeting->room_name}",
            'token'       => $authToken->get()
        ], 'New meeting created', Response::HTTP_CREATED);
    }

    public function cancel(string $meetingId)
    {
        $meeting = JitsiMeeting::query()->firstWhere('unique_id', $meetingId);

        if (is_null($meeting)) {
            return $this->errorResponse('Meeting does not exist', Response::HTTP_NOT_FOUND);
        }

        $meeting->invitation()->where('user_id', auth()->id())->delete();

        if (! $meeting->invitation()->exists()) {
            $meeting->completed_at = now();
            $meeting->save();
        }

        return $this->successResponse(
            null,
            'Meeting has been successfully completed.',
            Response::HTTP_OK
        );
    }
}
