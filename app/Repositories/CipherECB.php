<?php
/**
 * Created by PhpStorm.
 * User: zhaojipeng
 * Date: 17/7/26
 * Time: 14:24
 */

namespace App\Repositories;

class CipherECB
{
    private $key = 'RMkwCI9VSwWPDhiSqf9Rxnlptelw7saZ';

    public function setKey($key)
    {
        $this->key = $key;
    }

    public function encrypt($input)
    {
        $size = mcrypt_get_block_size(MCRYPT_RIJNDAEL_128, MCRYPT_MODE_ECB);
        $pad = $size - (strlen($input) % $size);
        $input = $input . str_repeat(chr($pad), $pad);

        $td = mcrypt_module_open(MCRYPT_RIJNDAEL_128, '', MCRYPT_MODE_ECB, '');
        $iv = mcrypt_create_iv(mcrypt_enc_get_iv_size($td), MCRYPT_RAND);
        mcrypt_generic_init($td, $this->key, $iv);
        $data = mcrypt_generic($td, $input);
        mcrypt_generic_deinit($td);
        mcrypt_module_close($td);
        return base64_encode($data);
    }


    public function decrypt($input)
    {
        $input = base64_decode($input);
        $decrypted = mcrypt_decrypt(MCRYPT_RIJNDAEL_128, $this->key, $input, MCRYPT_MODE_ECB);
        $dec_s = strlen($decrypted);
        $padding = ord($decrypted[$dec_s - 1]);
        return substr($decrypted, 0, -$padding);
    }
}
