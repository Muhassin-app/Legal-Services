<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Facades\Storage;
use JWT\Token;

class JitsiInvitation extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'jitsi_meeting_id',
        'jitsi_auth_id',
        'is_moderator',
    ];

    protected $casts = [
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    public function meeting(): BelongsTo
    {
        return $this->belongsTo(JitsiMeeting::class);
    }

    public function invited(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function createToken()
    {
        /** @var \App\Services\JitsiService */
        $jitsiService = app(\App\Services\JitsiService::class);

        $key = Storage::disk('s3')->get($jitsiService->getPreferences('private_key'));

        $authToken = new Token([
            'key'       => $key,
            'algorithm' => 'RS256',
            'expiry'    => now()->addYear()->getTimestamp(),
            'subject'   => $jitsiService->getPreferences('api_key'),
            'issuer'    => 'chat',
            'type'      => 'JWT',
        ]);

        /** @var \JWT\Token $authToken */
        $authToken = \Closure::bind(function () use ($jitsiService) {
            /** @var Token $this */

            $this->header['kid'] = $jitsiService->getPreferences('api_secret');
            return $this;
        }, $authToken, $authToken)();

        /** @var \JWT\Token $authToken */
        $authToken = \Closure::bind(function (JitsiInvitation $invitation) {
            /** @var Token $this */

            $this->claims['nbf'] = time();
            $this->claims['context'] = [
                'user' => [
                    'name'      => auth()->user()->name,
                    'email'     => auth()->user()->email,
                    'id'        => $invitation->jitsi_auth_id,
                    'moderator' => $invitation->is_moderator ? 'true' : 'false',
                ],
                'features'  => [
                    'livestreaming' => 'true',
                    'outbound-call' => 'true',
                    'transcription' => 'true',
                    'recording'     => 'true',
                ],
            ];

            return $this;
        }, $authToken, $authToken)($this);

        return $authToken->setClaim('aud', 'jitsi')
            ->setClaim('room', '*');
    }
}
