<?php
// ===================================
// api/utils/token.php
// ===================================
class Token {
    private static $secret_key = "your_secret_key_here_change_this_in_production";
    private static $encrypt_method = "AES-256-CBC";
    private static $secret_iv = "your_secret_iv_here";

    public static function generate($user_id) {
        $token_data = array(
            "user_id" => $user_id,
            "created_at" => time(),
            "expires_at" => time() + (7 * 24 * 60 * 60) // 7 days
        );

        $token_json = json_encode($token_data);
        
        // Encrypt
        $key = hash('sha256', self::$secret_key);
        $iv = substr(hash('sha256', self::$secret_iv), 0, 16);
        
        $encrypted = openssl_encrypt($token_json, self::$encrypt_method, $key, 0, $iv);
        return base64_encode($encrypted);
    }

    public static function validate($token) {
        try {
            // Decrypt
            $key = hash('sha256', self::$secret_key);
            $iv = substr(hash('sha256', self::$secret_iv), 0, 16);
            
            $decrypted = openssl_decrypt(base64_decode($token), self::$encrypt_method, $key, 0, $iv);
            $token_data = json_decode($decrypted, true);

            // Check expiration
            if ($token_data && isset($token_data['expires_at']) && $token_data['expires_at'] > time()) {
                return $token_data['user_id'];
            }
        } catch (Exception $e) {
            return false;
        }

        return false;
    }
}