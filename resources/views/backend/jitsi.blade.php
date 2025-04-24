<!DOCTYPE html>

@use(Illuminate\Support\Js)

@php
    $jitsiService = app('jitsi');
    $jitsiAppId   = $jitsiService->getPreferences('api_key');

    $domain = $jitsiService->getPreferences('domain_url');
    $domain = parse_url($domain)['host'];

    $leaveRoute = url('client/jitsi/leave') . '/';
@endphp

<html lang="en">
    <head>
        <meta charset="UTF-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <title>Jitsi</title>

        <style>
            iframe {
                position: absolute;
                top: 0;
                left: 0;
            }
        </style>
    </head>

    <body>
    <div id="meet"></div>

    <script src="https://cdn.jsdelivr.net/npm/axios/dist/axios.min.js"></script>
    <script src="{{ $jitsiService->getPreferences('domain_url') }}/{{ $jitsiAppId }}/external_api.js"></script>
    <script>
        function initIframeApi() {
            const jitsiAppId = {{ Js::from($jitsiAppId) }};

            const options = {
                roomName: {{ Js::from($meeting->room_name) }},
                jwt: {{ Js::from($token) }},
                width: '100%',
                height: '100vh',
                parentNode: document.querySelector('#meet')
            };

            return new JitsiMeetExternalAPI({{ Js::from($domain) }}, options);
        }

        window.onload = function () {
            window.jitsiApi = initIframeApi();

            jitsiApi.addListener('videoConferenceJoined', (event) => onConferenceJoined(jitsiApi, event));
            jitsiApi.addListener('videoConferenceLeft', (event) => onConferenceLeave(jitsiApi, event));
        };

        function onConferenceJoined(jitsiApi, event) {
            // Mark the conference joined.
            window.conferenceJoined = true;

            setTimeout(() => {
                jitsiApi.executeCommand('hangup');
            }, 3600000); // Terminate call after 1 hour
                         // ============================================================================================
                         // Note that this is a temporary solution. It probably should be handled by a server side
                         // event.
                         //
                         // Since royo does not have socket events any where on the web but rather polling (which is
                         // rather inefficient); We would have to fallback to sending silent firebase notifications for
                         // event management.
        }

        async function onConferenceLeave(jitsiApi, event) {
            // Cleanup the Jitsi IFrame, when 'videoConferenceLeft' is triggered. Also ensures that if any page cache
            // is present it does not pickup on the previous IFrame (not that it should but still... Just in case).
            jitsiApi.dispose();

            // Only "mark" it "leaved" if it was joined in the first place. We do not want to accidentally delete the
            // meeting in case of a reload (Yes 'videoConferenceLeft' is triggered even when you have not joined the
            // meeting and are doing a full page reload).
            if (! window.conferenceJoined) {
                return;
            }

            const { roomName } = event;
            const leaveRoute = {{ Js::from($leaveRoute) }} + roomName;

            const response = await axios.post(leaveRoute, {
                order_id: {{ $meeting->order_id }},
            });

            window.close();
        }
    </script>
    </body>
</html>
