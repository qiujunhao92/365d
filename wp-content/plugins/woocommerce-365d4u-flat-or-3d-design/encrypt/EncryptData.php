<?php
if (!class_exists('EncryptData365d4u')) {
    class EncryptData365d4u
    {
        private static $secret_key= '906906';

        /**
         * Encrypt data
         * @param $data
         * @return false|string
         */
        public static function encrypt($plaintext)
        {
            if (!$plaintext || !is_string($plaintext)) {
                return '';
            }
            $iv_length = openssl_cipher_iv_length('aes-128-cbc');
            $iv = openssl_random_pseudo_bytes($iv_length);  // 生成随机 IV
            $ciphertext = openssl_encrypt($plaintext, 'aes-128-cbc', self::$secret_key, OPENSSL_RAW_DATA, $iv);
            return bin2hex($iv . $ciphertext);  // 将 IV 和密文组合并转换为十六进制
        }

        public static function decrypt($hexCiphertext)
        {
            $iv_length = openssl_cipher_iv_length('aes-128-cbc');
            $ciphertext = hex2bin($hexCiphertext);  // 从十六进制转换回二进制
            $iv = substr($ciphertext, 0, $iv_length);  // 提取 IV
            $ciphertext = substr($ciphertext, $iv_length);
            return openssl_decrypt($ciphertext, 'aes-128-cbc', self::$secret_key, OPENSSL_RAW_DATA, $iv);
        }
    }
}