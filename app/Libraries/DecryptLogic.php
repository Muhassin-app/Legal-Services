<?php
namespace App\Libraries;

class DecryptLogic
{
    public static function Decrypt($data, $key)
    {
        $encrypted= $data;
        // $key = $decryptionKey;
        $iv=$key . "\0\0\0\0";
        $key=$key . "\0\0\0\0";
        $encrypted = base64_decode($encrypted);	
        $decrypted=openssl_decrypt($encrypted, "aes-128-cbc", $key, OPENSSL_RAW_DATA | OPENSSL_NO_PADDING,$iv);
        if($decrypted != '')
           {
               $dec_s2 = strlen($decrypted);
               $padding = ord($decrypted[$dec_s2-1]);
               $decrypted = substr($decrypted, 0, -$padding);
           }
        $data = substr(trim(gzinflate(base64_decode($decrypted))),3);
        return $data;
    }
}

?>
