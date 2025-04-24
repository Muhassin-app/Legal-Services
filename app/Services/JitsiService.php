<?php

namespace App\Services;

use App\Models\ClientPreference;
use App\Models\JitsiInvitation;
use App\Models\JitsiMeeting;
use App\Models\UserDevice;
use Illuminate\Support\Arr;
use Illuminate\Support\Str;
use Illuminate\Support\Stringable;

final class JitsiService
{
    protected static array $preferences;

    protected static ClientPreference $clientPreference;

    protected ?JitsiMeeting $meeting;

    protected function getClientPreference()
    {
        return static::$clientPreference = ClientPreference::query()->first();
    }

    protected function getMeeting(?JitsiMeeting $jitsiMeeting = null): JitsiMeeting
    {
        return tap($jitsiMeeting ?? $this->meeting, function ($meeting) {
            assert(! is_null($meeting), 'No target meeting found');

            $this->meeting = $meeting;
        });
    }

    public function getPreferences(?string $key = null)
    {
        static::$preferences ??= $this->getClientPreference()->jitsi_preferences;

        if (is_null($key)) {
            return static::$preferences;
        }

        return static::$preferences[$key] ?? null;
    }

    public function fetchMeeting(string $roomName, int $orderId, array $meetingData = []): JitsiMeeting
    {
        $roomName    = (string) $this->normalizeRoomName($roomName);
        $meetingData = Arr::except($meetingData, ['order_id', 'room_name']);

        return $this->meeting = JitsiMeeting::query()->firstOrCreate([
            'room_name' => $roomName,
            'order_id'  => $orderId,
        ], $meetingData);
    }

    public function join(?JitsiMeeting $jitsiMeeting = null, bool $moderator = false): JitsiInvitation
    {
        $jitsiMeeting = $this->getMeeting($jitsiMeeting);

        return JitsiInvitation::query()->firstOrCreate([
            'user_id'          => auth()->id(),
            'jitsi_meeting_id' => $jitsiMeeting->id,
        ], [
            'is_moderator'  => $moderator,
            'jitsi_auth_id' => sprintf('auth%d|%s', auth()->id(), Str::uuid()->toString()),
        ]);
    }

    public function normalizeRoomName(string $roomName): Stringable
    {
        return Str::of($roomName)->snake()->replace('_', '-')->prepend($this->getPreferences('api_key') . '/');
    }

    public function notify(?JitsiMeeting $meeting = null)
    {
        $meeting = $this->getMeeting($meeting);
        $appId   = $this->getPreferences('api_key');

        /** @var \App\Models\Order $order */
        $order = $meeting->order;

        $receiver = auth()->id() == $order->user_id
            ? $order->lawyer_id
            : $order->user_id;

        if ($meeting->invitation()->where('user_id', $receiver)->exists()) {
            return;
        }

        $devices = UserDevice::query()
            ->where('user_id', $receiver)
            ->pluck('device_token')
            ->reject(fn($token) => is_null($token));

        if ($devices->isEmpty()) {
            return;
        }

        FirebaseService::sendBasicNotification(
            trans('Incomming request to join meeting'),
            trans('Request to join meeting for') . " {$order->order_number}",
            $devices->toArray(),
            [
                'room_name' => (string) Str::of($meeting->room_name)->remove($appId . '/'),
            ],
        );
    }
}
