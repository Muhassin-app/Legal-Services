<?php
namespace App\Http\Traits;

use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Collection;
use App\Models\PaymentOption;
use Log;

trait PlugnpaypaymentManager
{

    private $plugnpay_publisher_name;

    private $api_url;

    private $environment;

    public function __construct()
    {
        $plugnpay_creds = PaymentOption::select('credentials', 'test_mode')->where('code', 'plugnpay')
            ->where('status', 1)
            ->first();
        $creds_arr = isset($plugnpay_creds->credentials) ? json_decode($plugnpay_creds->credentials) : null;
        $this->plugnpay_publisher_name = $creds_arr->plugnpay_publisher_name ?? '';
        $this->api_url = "https://pay1.plugnpay.com/payment/pnpremote.cgi";
        // $this->api_url = "https://pay1.plugnpay.com/payment/pay.cgi";

        if ($plugnpay_creds->test_mode == '1') {

            $this->environment = 'sandbox';
        } else {
            $this->environment = 'live';
        }
    }

    public function createPaymentRequest($data)
    {
        if (isset($data['plugnpay_publisher_name'])) {
            $plugnpay_publisher_name = $data['plugnpay_publisher_name'];
        }
        if (isset($data['api_url'])) {
            $api_url = $data['api_url'];
        }
        if (isset($data['environment'])) {
            $environment = $data['environment'];
        } else {
            $environment = $this->environment;
        }

        // Is curl complied into PHP?
        $is_curl_compiled_into_php = "yes";
        // Possible answers are:
        // 'yes' -> means that curl is compiled into PHP [DEFAULT]
        // 'no' -> means that curl is not-compiled into PHP & must be called externally
        // If you selected 'no' to the above question, then set the absolute path to curl
        $curl_path = "/usr/bin/curl";
        // [e.g.: '/usr/bin/curl' on Unix/Linux or 'c:/curl/curl.exe' on Windows servers]
        // If you are unsure of this, check with your hosting company.

        // Set URL that you will post the transaction to
        // $pnp_post_url = "https://pay1.plugnpay.com/payment/pnpremote.cgi";
        // This should never need to be changed...

        $pnp_post_url = $this->api_url ?? $api_url;
        $order_number = $data['order_number'];
        $publisher_name = $this->plugnpay_publisher_name ?? $plugnpay_publisher_name;
        $publisher_email = "Delivadrinks@gmail.com";
        $card_number = $data['cno'];
        $card_cvv = $data['cv'];
        $card_exp = $data['dt'];
        $card_amount = $data['amount'];
        // $card_name = auth()->user()->name;
        if ($environment == 'sandbox') {
            $card_name = 'cardtest';
        } else {

            if (isset($data['cname'])) {
                $card_name = $data['cname'];
            } else {
                $card_name = auth()->user()->name;
            }
        }

        if (isset($data['come_from'])) {
            $email = $data['user']['email'];
        } else {
            $email = auth()->user()->email ?? "Delivadrinks@gmail.com";
        }
        // billing address info
        $card_address1 = $data['caddr1'] ?? "";
        $card_address2 = $data['caddr2'] ?? "";
        $card_zip = $data['czip'] ?? "";
        $card_city = $data['city'] ?? "";
        $card_state = $data['state'] ?? "";
        $card_country = $data['country'] ?? "";
        // shipping address info
        $shipname = "";

        $pnp_post_values = "";

        /*
         * This is where you build the query string to be posted to plugnpay. You
         * can replace this code with your own or you need to follow the instructions
         * in the README file for calling this script.
         */

        if ($pnp_post_values == "") {
            $pnp_post_values .= "publisher-name=" . $publisher_name . "&";
            $pnp_post_values .= "publisher-email=" . $publisher_email . "&";
            $pnp_post_values .= "card-number=" . $card_number . "&";
            $pnp_post_values .= "card-cvv=" . $card_cvv . "&";
            $pnp_post_values .= "card-exp=" . $card_exp . "&";
            $pnp_post_values .= "card-amount=" . $card_amount . "&";
            $pnp_post_values .= "card-name=" . $card_name . "&";
            $pnp_post_values .= "email=" . $email . "&";
            $pnp_post_values .= "ipaddress=" . $_SERVER['REMOTE_ADDR'] . "&";
            // billing address info
            $pnp_post_values .= "card-address1=" . $card_address1 . "&";
            $pnp_post_values .= "card-address2=" . $card_address2 . "&";
            $pnp_post_values .= "card-zip=" . $card_zip . "&";
            $pnp_post_values .= "card-city=" . $card_city . "&";
            $pnp_post_values .= "card-state=" . $card_state . "&";
            $pnp_post_values .= "card-country=" . $card_country . "&";
            // shipping address info
            $pnp_post_values .= "shipname=" . $shipname . "&";
            $pnp_post_values .= "address1=" . $card_address1 . "&";
            $pnp_post_values .= "address2=" . $order_number . "&";
            $pnp_post_values .= "zip=" . $card_zip . "&";
            $pnp_post_values .= "state=" . $card_state . "&";
            $pnp_post_values .= "country=" . $card_country . "&";
        }

        /**
         * ************************************************************************
         * UNLESS YOU KNOW WHAT YOU ARE DOING YOU SHOULD NOT CHANGE THE BELOW CODE
         * ************************************************************************
         */

        if ($is_curl_compiled_into_php == "no") {
            // do external PHP curl connection
            exec("$curl_path -d \"$pnp_post_values\" https://pay1.plugnpay.com/payment/pnpremote.cgi", $pnp_result_page);
            // NOTES:
            // -- The '-k' attribute can be added before the '-d' attribute to turn off curl's SSL certificate validation feature.
            // -- Only use the '-k' attribute if you know your curl path is correct & are getting back a blank response in $pnp_result_page.

            $pnp_result_decoded = urldecode($pnp_result_page[1]);
        } else {
            // do internal PHP curl connection
            // init curl handle
            $pnp_ch = curl_init($pnp_post_url);
            curl_setopt($pnp_ch, CURLOPT_RETURNTRANSFER, 1);
            curl_setopt($pnp_ch, CURLOPT_POSTFIELDS, $pnp_post_values);
            # curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, FALSE); // Upon problem, uncomment for additional Windows 2003 compatibility

            // perform ssl post
            $pnp_result_page = curl_exec($pnp_ch);

            $pnp_result_decoded = urldecode($pnp_result_page);
        }

        // decode the result page and put it into transaction_array
        $pnp_temp_array = explode('&', $pnp_result_decoded);
        foreach ($pnp_temp_array as $entry) {
            list ($name, $value) = explode('=', $entry);
            $pnp_transaction_array[$name] = $value;
        }

        /**
         * ************************************************************************
         * UNLESS YOU KNOW WHAT YOU ARE DOING DO NOT CHANGE THE ABOVE CODE
         * ************************************************************************
         */
        /*
         * These statements handle the results for the transaction and where
         * the customer is sent next. If you don't want this script to handle
         * the final transaction process set $pnp_handle_post_process="no" and
         * it will be skipped. You can edit the sepearate HTML files to look
         * the way you want them to or each can be replaced with a php script.
         * Your php scripts should use $pnp_transaction_array[] to check the
         * transaction status. All the documented plugnpay fields should be
         * valid in pnp_transation_array.
         */
        
        return json_encode($pnp_transaction_array);
    }
    // public function createPaymentRequest($data)
    // {
    // $pnp_post_url = $this->api_url;
    // $order_number = $data['order_number'];

    // $publisher_name = $this->plugnpay_publisher_name;
    // $publisher_email = "Delivadrinks@gmail.com";
    // $card_number = "4111111111111111";
    // $card_cvv = "456";
    // $card_exp = "03/34";
    // $card_amount = $data['amount'];
    // $card_name = "cardtest";
    // $email = "Delivadrinks@gmail.com";
    // // billing address info
    // $card_address1 = "Saint Michael";
    // $card_address2 = "abc add";
    // $card_zip = "151604";
    // $card_city = "Warrens";
    // $card_state = "Bridgetown";
    // $card_country = "Barbados";
    // // shipping address info
    // $shipname = "ab add";

    // $pnp_post_values = "";
    // if ($pnp_post_values == "") {
    // $pnp_post_values .= "publisher-name=" . $publisher_name . "&";
    // $pnp_post_values .= "publisher-email=" . $publisher_email . "&";
    // $pnp_post_values .= "card-number=" . $card_number . "&";
    // $pnp_post_values .= "card-cvv=" . $card_cvv . "&";
    // $pnp_post_values .= "card-exp=" . $card_exp . "&";
    // $pnp_post_values .= "card-amount=" . $card_amount . "&";
    // $pnp_post_values .= "card-name=" . $card_name . "&";
    // $pnp_post_values .= "email=" . $email . "&";
    // $pnp_post_values .= "ipaddress=" . $_SERVER['REMOTE_ADDR'] . "&";
    // // billing address info
    // $pnp_post_values .= "card-address1=" . $card_address1 . "&";
    // $pnp_post_values .= "card-address2=" . $card_address2 . "&";
    // $pnp_post_values .= "card-zip=" . $card_zip . "&";
    // $pnp_post_values .= "card-city=" . $card_city . "&";
    // $pnp_post_values .= "card-state=" . $card_state . "&";
    // $pnp_post_values .= "card-country=" . $card_country . "&";
    // // shipping address info
    // $pnp_post_values .= "shipname=" . $shipname . "&";
    // $pnp_post_values .= "address1=" . $card_address1 . "&";
    // $pnp_post_values .= "address2=" . $card_address2 . "&";
    // $pnp_post_values .= "zip=" . $card_zip . "&";
    // $pnp_post_values .= "state=" . $card_state . "&";
    // $pnp_post_values .= "country=" . $card_country . "&";
    // // order detail
    // // $pnp_post_values .= "order-id=" . $order_number . "&";
    // }

    // // do internal PHP curl connection
    // // init curl handle
    // $pnp_ch = curl_init($pnp_post_url);
    // curl_setopt($pnp_ch, CURLOPT_RETURNTRANSFER, 1);
    // curl_setopt($pnp_ch, CURLOPT_POSTFIELDS, $pnp_post_values);
    // #curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, FALSE); // Upon problem, uncomment for additional Windows 2003 compatibility

    // // perform ssl post
    // $pnp_result_page = curl_exec($pnp_ch);

    // $pnp_result_decoded = urldecode($pnp_result_page);

    // // decode the result page and put it into transaction_array
    // $pnp_temp_array = explode('&',$pnp_result_decoded);
    // foreach ($pnp_temp_array as $entry) {
    // list($name,$value) = explode('=',$entry);
    // $pnp_transaction_array[$name] = $value;
    // }

    // // --------------------------------------------------------------------

    // $post_data = array(
    // 'x-public-key' => 'B2us0I0woZ'
    // //'x-public-key' => $this->public_key
    // );

    // $curl = curl_init();
    // curl_setopt_array($curl, array(
    // CURLOPT_URL => 'https://g.payx.ph/payment_request',
    // CURLOPT_RETURNTRANSFER => true,
    // CURLOPT_ENCODING => '',
    // CURLOPT_MAXREDIRS => 10,
    // CURLOPT_TIMEOUT => 0,
    // CURLOPT_FOLLOWLOCATION => true,
    // CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
    // CURLOPT_CUSTOMREQUEST => 'POST',
    // CURLOPT_POSTFIELDS => $post_data));

    // $response = curl_exec($curl);
    // curl_close($curl);
    //// Log::info($response);
    // return json_decode($response);
    // }
}
