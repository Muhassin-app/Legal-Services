<?php
namespace Database\Seeders;
use DB;
use Illuminate\Database\Seeder;
use App\Models\PaymentOption;
class PaymentOptionSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run(){

      $option_count = DB::table('payment_options')->count();

      $payment_options = array(
        array('id' => '1','code' => 'cod','path' => '','title' => __('Cash On Delivery'), 'off_site' => '0', 'status' => '0'),
        // array('id' => '2','code' => 'loyalty-points','path' => '','title' => 'loyalty Points', 'offsite' => '0', 'status' => '1'),
        array('id' => '3', 'path' => 'omnipay/paypal', 'code' => 'paypal',  'title' => 'PayPal', 'off_site' => '1', 'status' => '0'),
        array('id' => '4', 'path' => 'omnipay/stripe', 'code' => 'stripe', 'title' => 'Stripe', 'off_site' => '0', 'status' => '0'),
        array('id' => '5', 'path' => 'paystackhq/omnipay-paystack', 'code' => 'paystack', 'title' => 'Paystack', 'off_site' => '1', 'status' => '0'),
        array('id' => '6', 'path' => 'omnipay/payfast', 'code' => 'payfast', 'title' => 'Payfast', 'off_site' => '1', 'status' => '0'),
        array('id' => '7', 'path' => 'mobbex/sdk', 'code' => 'mobbex', 'title' => 'Mobbex', 'off_site' => '1', 'status' => '0'),
        array('id' => '8', 'path' => 'yoco/yoco-php-laravel', 'code' => 'yoco', 'title' => 'Yoco', 'off_site' => '1', 'status' => '0'),
        array('id' => '9', 'path' => 'paylink/paylink', 'code' => 'paylink', 'title' => 'Paylink', 'off_site' => '1', 'status' => '0'),
        array('id' => '10', 'path' => 'razorpay/razorpay', 'code' => 'razorpay', 'title' => 'Razorpay', 'off_site' => '0', 'status' => '0'),
        array('id' => '11', 'path' => 'adyen/php-api-library', 'code' => 'gcash', 'title' => 'GCash', 'off_site' => '1', 'status' => '0'),
        array('id' => '12', 'path' => 'rak/simplify', 'code' => 'simplify', 'title' => 'Simplify', 'off_site' => '1', 'status' => '0'),
        array('id' => '13', 'path' => 'square/square', 'code' => 'square', 'title' => 'Square', 'off_site' => '1', 'status' => '0'),
        array('id' => '14', 'path' => 'tradesafe/omnipay-ozow', 'code' => 'ozow', 'title' => 'Ozow', 'off_site' => '1', 'status' => '0'),
        array('id' => '15', 'path' => 'pagarme/pagarme-php', 'code' => 'pagarme', 'title' => 'Pagarme', 'off_site' => '1', 'status' => '0'),
        array('id' => '17', 'path' => 'checkout/checkout-sdk-php', 'code' => 'checkout', 'title' => 'Checkout', 'off_site' => '0', 'status' => '0'),
        array('id' => '18', 'path' => 'academe/omnipay-authorizenetapi', 'code' => 'authorize_net', 'title' => 'Authorize.net', 'off_site' => '1', 'status' => '0'),
        array('id' => '19', 'path' => 'omnipay/stripe', 'code' => 'stripe_fpx', 'title' => 'Stripe FPX', 'off_site' => '1', 'status' => '0'),
        array('id' => '20', 'path' => 'kongapay/pay', 'code' => 'kongapay', 'title' => 'KongaPay', 'off_site' => '1', 'status' => '0'),
        array('id' => '21', 'path' => 'vivawallet/pay', 'code' => 'viva_wallet', 'title' => 'Viva Wallet', 'off_site' => '1', 'status' => '0'),
        array('id' => '22', 'path' => 'ccavenue/pay', 'code' => 'ccavenue', 'title' => 'CCAvenue', 'off_site' => '1', 'status' => '0'),
        array('id' => '23', 'path' => 'easypaisa/pay', 'code' => 'easypaisa', 'title' => 'Easypaisa', 'off_site' => '1', 'status' => '0'),
        array('id' => '24', 'path' => 'cashfree', 'code' => 'cashfree', 'title' => 'Cashfree', 'off_site' => '1', 'status' => '0'),
        array('id' => '25', 'path' => 'easebuzz', 'code' => 'easebuzz', 'title' => 'PAYMENT GATEWAY - EASEBUZZ', 'off_site' => '1', 'status' => '0'),
        array('id' => '26', 'path' => 'tarsoft/toyyibpay', 'code' => 'toyyibpay', 'title' => 'Toyyibpay', 'off_site' => '1', 'status' => '0'),
        array('id' => '27', 'path' => '', 'code' => 'paytab', 'title' => 'PayTab', 'off_site' => '1', 'status' => '0'),
        array('id' => '28', 'path' => 'vnpay', 'code' => 'vnpay', 'title' => 'VNPay', 'off_site' => '1', 'status' => '0'),
        array('id' => '29', 'path' => '', 'code' => 'mvodafone', 'title' => 'Mpesa Vodafone', 'off_site' => '1', 'status' => '0'),
        array('id' => '30', 'path' => '', 'code' => 'flutterwave', 'title' => 'Flutter Wave', 'off_site' => '0', 'status' => '0'),
        array('id' => '31', 'path' => '', 'code' => 'payu', 'title' => 'PayU', 'off_site' => '1', 'status' => '0'),
        array('id' => '32', 'path' => '', 'code' => 'payphone', 'title' => 'PayPhone', 'off_site' => '0', 'status' => '0'),
        array('id' => '33', 'path' => 'braintree/braintree_php', 'code' => 'braintree', 'title' => 'Braintree', 'off_site' => '1', 'status' => '0'),
        array('id' => '34', 'path' => 'windcave', 'code' => 'windcave', 'title' => 'Windcave', 'off_site' => '1', 'status' => '0'),
        array('id' => '35', 'path' => 'paytech', 'code' => 'paytech', 'title' => 'PayTech', 'off_site' => '1', 'status' => '0'),
        array('id' => '36', 'path' => 'mycash', 'code' => 'mycash', 'title' => 'MyCash', 'off_site' => '1', 'status' => '0'),
        array('id' => '37', 'path' => '', 'code' => 'stripe_oxxo', 'title' => 'Stripe OXXO', 'off_site' => '1', 'status' => '0'),
        array('id' => '38', 'path' => '', 'code' => 'offline_manual', 'title' => 'Offline Manual Payment', 'off_site' => '0', 'status' => '0'),
        array('id' => '39', 'path' => '', 'code' => 'stripe_ideal', 'title' => 'Stripe Ideal', 'off_site' => '1', 'status' => '0'),
        array('id' => '40', 'path' => '', 'code' => 'userede', 'title' => 'Userede', 'off_site' => '1', 'status' => '0'),
        array('id' => '41', 'path' => '', 'code' => 'openpay', 'title' => 'Open-pay', 'off_site' => '1', 'status' => '0'),
        array('id' => '42', 'path' => '', 'code' => 'dpo', 'title' => 'Direct Pay Online', 'off_site' => '1', 'status' => '0'),
        array('id' => '43', 'path' => '', 'code' => 'upay', 'title' => 'UnionBank Payments and Collections Solution', 'off_site' => '1', 'status' => '0'),
        array('id' => '44', 'path' => 'conekta/conekta-php', 'code' => 'conekta', 'title' => 'Conekta', 'off_site' => '1', 'status' => '0'),
        array('id' => '45', 'path' => 'laravel_payment/telr', 'code' => 'telr', 'title' => 'Telr', 'off_site' => '1', 'status' => '0'),
        array('id' => '46', 'path' => '', 'code' => 'mastercard', 'title' => 'Mastercard', 'off_site' => '1', 'status' => '0'),
        array('id' => '47', 'path' => 'khalti/khalti', 'code' => 'khalti', 'title' => 'Khalti', 'off_site' => '1', 'status' => '0'),
        array('id' => '48', 'path' => '', 'code' => 'mtn_momo', 'title' => 'Mtn Momo', 'off_site' => '1', 'status' => '0'),
        array('id' => '49', 'path' => '', 'code' => 'plugnpay', 'title' => 'plugnpay', 'off_site' => '1', 'status' => '0'),
	      array('id' => '50', 'path' => '', 'code' => 'azul', 'title' => 'Azulpay', 'off_site' => '1', 'status' => '0'),
        array('id' => '51', 'path' => '', 'code' => 'payway', 'title' => 'Payway', 'off_site' => '1', 'status' => '0'),
        array('id' => '52', 'path' => '', 'code' => 'skip_cash', 'title' => 'SkpCash', 'off_site' => '1', 'status' => '0'),
        array('id' => '53', 'path' => '', 'code' => 'nmi', 'title' => 'Nmi', 'off_site' => '1', 'status' => '0'),
        array('id' => '54', 'path' => '', 'code' => 'yappy', 'title' => 'yappy', 'off_site' => '1', 'status' => '0'),
        array('id' => '55', 'path' => '', 'code' => 'data_trans', 'title' => 'Data Trans', 'off_site' => '0', 'status' => '0'),
        array('id' => '56', 'path' => '', 'code' => 'obo', 'title' => "obo", 'off_site' => '1', 'status' => '0'),
        array('id' => '57', 'path' => '', 'code' => 'pesapal', 'title' => 'pesapal', 'off_site' => '1', 'status' => '0'),
        array('id' => '58', 'path' => '', 'code' => 'powertrans', 'title' => 'powertrans', 'off_site' => '1', 'status' => '0'),
        array('id' => '59', 'path' => '', 'code' => 'livee', 'title' => 'livee', 'off_site' => '1', 'status' => '1'),
        array('id' => '60', 'path' => '', 'code' => 'PayViaCompany', 'title' => 'Pay Via Company', 'off_site' => '1', 'status' => '0'),
        array('id' => '62', 'path' => '', 'code' => 'mpesasafari', 'title' => 'Mpesa Safari', 'off_site' => '1', 'status' => '0'),
        array('id' => '65', 'path' => '', 'code' => 'totalpay', 'title' => 'TotalPay', 'off_site' => '1', 'status' => '0'),
        array('id' => '67', 'path' => '', 'code' => 'thawani', 'title' => 'Thawani', 'off_site' => '1', 'status' => '0'),
        array('id' => '68', 'path' => '', 'code' => 'icici', 'title' => 'Icici Upi', 'off_site' => '1', 'status' => '0'),
        array('id' => '69', 'path' => '', 'code' => 'hitpay', 'title' => 'HitPay', 'off_site' => '1', 'status' => '0'),
        array('id' => '70', 'path' => '', 'code' => 'cyber_source', 'title' => 'Cyber Source', 'off_site' => '0', 'status' => '0'),
        array('id' => '71', 'path' => '', 'code' => 'orange_pay', 'title' => 'Orange Pay', 'off_site' => '0', 'status' => '0'),
      );


      if($option_count == 0)
      {
        DB::statement('SET FOREIGN_KEY_CHECKS=0;');
        DB::table('payment_options')->truncate();
        DB::statement('SET FOREIGN_KEY_CHECKS=1;');

        DB::table('payment_options')->insert($payment_options);
      }
      else{
          foreach ($payment_options as $option) {
              $payop = PaymentOption::where('code', $option['code'])->first();

              if ($payop !== null) {
                  $payop->update(['id' => $option['id'], 'title' => $option['title'],'off_site' => $option['off_site']]);
              } else {
                  $payop = PaymentOption::create([
                    'id' => $option['id'],
                    'title' => $option['title'],
                    'code' => $option['code'],
                    'path' => $option['path'],
                    'off_site' => $option['off_site'],
                    'status' => $option['status'],
                  ]);
              }
          }
      }

    }
}
