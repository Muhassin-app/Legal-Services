<?php

namespace App\Http\Controllers\Client;

use App\Http\Controllers\Controller;
use App\Http\Traits\ApiResponser;
use App\Models\JitsiMeeting;
use App\Services\JitsiService;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Validation\Rules;

class JitsiMeetingController extends Controller
{
    use ApiResponser;

    public function create(Request $request, JitsiService $jitsiService)
    {
        $validation = validator($request->all(), [
            'room_name' => ['required'],
            'order_id'  => ['required', new Rules\Exists('orders', 'id')],
        ]);

        if ($validation->fails()) {
            return $this->errorResponse('Validation failed', Response::HTTP_UNPROCESSABLE_ENTITY, $validation->errors());
        }

        [
            'room_name' => $room,
            'order_id'  => $order
        ] = $validated = $validation->validated();

        $jitsiPreferences = $jitsiService->getPreferences();

        if (! isset($jitsiPreferences['domain_url'])) {
            return $this->errorResponse('Jitsi is not configured', Response::HTTP_INTERNAL_SERVER_ERROR);
        }

        $meeting = $jitsiService->fetchMeeting($room, $order, $validated);

        return $this->successResponse([
            'app_id'      => $jitsiService->getPreferences('api_key'),
            'room_name'   => $meeting->room_name,
        ], 'New meeting created', Response::HTTP_CREATED);
    }

    public function join(string $domain, JitsiService $jitsiService, int $orderId)
    {
        $jitsiMeeting = JitsiMeeting::query()->firstWhere('order_id', $orderId);

        if (is_null($jitsiMeeting)) {
            abort(Response::HTTP_NOT_FOUND);
        }

        $invitation = $jitsiService->join($jitsiMeeting, true);
        $authToken  = $invitation->createToken();

        rescue(fn() => $jitsiService->notify());

        return view('backend.jitsi', [
            'token'   => $authToken->get(),
            'meeting' => $jitsiMeeting,
        ]);
    }

    public function leave(Request $request, JitsiService $jitsiService, string $domain, string $roomName)
    {
        $request->validate([
            'order_id' => [new Rules\Exists('jitsi_meetings', 'order_id')],
        ]);

        $jitsiMeeting = $jitsiService->fetchMeeting($roomName, $request->input('order_id'));
        $jitsiMeeting->invitation()->where('user_id', auth()->id())->delete();

        if (! $jitsiMeeting->invitation()->exists()) {
            $jitsiMeeting->delete();
        }

        return $this->successResponse([], 'Meeting left successfully.');
    }
}
