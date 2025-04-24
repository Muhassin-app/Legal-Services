<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use App\Models\ClientPreference;
use App\Models\Client;
use Config;
use DB,Log;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;

class MailConfigServiceProvider extends ServiceProvider
{
/**
* Bootstrap services.
*
* @return void
*/
public function boot(Request $request)
{
	// $mail = ClientPreference::where('id', '>', 0)->first(['id', 'mail_type', 'mail_driver', 'mail_host', 'mail_port', 'mail_username', 'mail_password', 'mail_encryption', 'mail_from']);
	$mail = Cache::remember('client_preference', 60 * 60, function () {
		return ClientPreference::where('id', '>', 0)->first(['id', 'mail_type', 'mail_driver', 'mail_host', 'mail_port', 'mail_username', 'mail_password', 'mail_encryption', 'mail_from']);
	});

	if (array_key_exists("code", $request->header())) {
	$header = $request->header();
	$clientCode = $header['code'][0];

	// $client = Client::where('code',$clientCode)->first();
	$client = Cache::remember('client', 60 * 60, function () use($clientCode) {
		return Client::where('code',$clientCode)->first();
	});
	if($client){

		$schemaName = 'royo_' . $client->database_name;
		$database_host = !empty($client->database_host) ? $client->database_host : env('DB_HOST', '127.0.0.1');
		$database_port = !empty($client->database_port) ? $client->database_port : env('DB_PORT', '3306');
		$database_username = !empty($client->database_username) ? $client->database_username : env('DB_USERNAME', 'root');
		$database_password = !empty($client->database_password) ? $client->database_password : env('DB_PASSWORD', '');

		$default = [
		'driver' => env('DB_CONNECTION', 'mysql'),
		'host' => $database_host,
		'port' => $database_port,
		'database' => $schemaName,
		'username' => $database_username,
		'password' => $database_password,
		'charset' => 'utf8mb4',
		'collation' => 'utf8mb4_unicode_ci',
		'prefix' => '',
		'prefix_indexes' => true,
		'strict' => false,
		'engine' => null
		];

		Config::set("database.connections.$schemaName", $default);
		config(["database.connections.mysql.database" => $schemaName]);

		$mail = ClientPreference::on($schemaName)->where('id', '>', 0)->first(['id', 'mail_type', 'mail_driver', 'mail_host', 'mail_port', 'mail_username', 'mail_password', 'mail_encryption', 'mail_from']);
		DB::disconnect($database_username);
		}



		if (isset($mail->id) && isset($mail->mail_driver)  && isset($mail->mail_host)  && isset($mail->mail_port)  && isset($mail->mail_encryption)  && isset($mail->mail_username) ){
			$config = array(
			'driver' => $mail->mail_driver,
			'host' => $mail->mail_host,
			'port' => $mail->mail_port,
			'from' => array('address' => $mail->mail_from, 'name' => $mail->mail_from),
			'encryption' => $mail->mail_encryption,
			'username' => $mail->mail_username,
			'password' => $mail->mail_password,
			'sendmail' => '/usr/sbin/sendmail -bs',
			'pretend' => false
			);
			Config::set('mail', $config);
		}
	}
}
/**
* Register services.
*
* @return void
*/
public function register()
{

}


   
}
