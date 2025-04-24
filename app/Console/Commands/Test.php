<?php
namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\PaymentOption;
use GuzzleHttp\Client;
use Illuminate\Support\Facades\URL;

class Test extends Command
{

    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'test:mtn';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Command description';

    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * Execute the console command.
     *
     * @return int
     */
    public function handle()
    {
        $site_url = url('/');
        $domain_name = self::getDomainName($site_url);

        $primaryKey = '8cd27bfbf6274bdbb509a9f465ca6427';
        $xReferenceId = 'a9a17b3f-08e7-4eba-8a2b-7fec48fdcd0c';
        $headers = [
            'X-Reference-Id' => $xReferenceId,
            'Ocp-Apim-Subscription-Key' => $primaryKey,
            'Content-Type' => 'application/json'
        ];

        $params = [
            'providerCallbackHost' => $domain_name
        ];

        try {
            // Create a sandbox user
            $client = new Client();
            $response = $client->request('POST', 'https://sandbox.momodeveloper.mtn.com/v1_0/apiuser', [
                'headers' => $headers,
                'body' => json_encode($params)
            ]);

            // Create an apiKey
            $response = $client->request('post', 'https://sandbox.momodeveloper.mtn.com/v1_0/apiuser/' . $xReferenceId . '/apikey', [
                'headers' => $headers
            ]);

            $apiKey = json_decode($response->getBody()->getContents(), true)['apiKey'];
            
            $headers = [
                'Authorization' => 'Basic ' . base64_encode($xReferenceId . ':' . $apiKey),
                'Ocp-Apim-Subscription-Key' => $primaryKey
            ];

            // Create an accessToken
            $response = $client->request('post', 'https://sandbox.momodeveloper.mtn.com/collection/token/', [
                'headers' => $headers
            ]);

            print_r($response->getBody()->getContents());
        } catch (\Exception $exception) {
            throw $exception;
        }
    }

    function getDomainName($url)
    {
        $disallowed = [
            'http://',
            'https://'
        ];
        foreach ($disallowed as $d) {
            if (strpos($url, $d) === 0) {
                return str_replace($d, '', $url);
            }
        }
        return $url;
    }
}
