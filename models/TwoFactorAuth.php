<?php
// models/TwoFactorAuth.php

require_once __DIR__ . '/../config/database.php';

class TwoFactorAuth {
    private static function base32Decode($secret) {
        $base32chars = 'ABCDEFGHIJKLMNOPQRSTUVWXYZ234567';
        $base32charsFlipped = array_flip(str_split($base32chars));
        $paddingCharCount = substr_count($secret, $base32chars[0]);
        $allowedValues = array(6, 4, 3, 1, 0);
        if (!in_array($paddingCharCount, $allowedValues)) return false;
        for ($i = 0; $i < 4; $i++) {
            if ($paddingCharCount == $allowedValues[$i] &&
                substr($secret, -($allowedValues[$i])) != str_repeat($base32chars[0], $allowedValues[$i])) return false;
        }
        $secret = str_replace('=', '', $secret);
        $secret = str_split($secret);
        $binaryString = "";
        for ($i = 0; $i < count($secret); $i = $i + 8) {
            $x = "";
            if (!in_array($secret[$i], array_keys($base32charsFlipped))) return false;
            for ($j = 0; $j < 8; $j++) {
                $x .= str_pad(base_convert(@$base32charsFlipped[@$secret[$i + $j]], 10, 2), 5, '0', STR_PAD_LEFT);
            }
            $eightBits = str_split($x, 8);
            for ($z = 0; $z < count($eightBits); $z++) {
                $binaryString .= (($y = chr(base_convert($eightBits[$z], 2, 10))) || ord($y) == 48) ? $y : "";
            }
        }
        return $binaryString;
    }

    private static function getTimestamp() {
        return floor(microtime(true) / 30);
    }

    public static function generateSecret($length = 16) {
        $validChars = 'ABCDEFGHIJKLMNOPQRSTUVWXYZ234567';
        $secret = '';
        for ($i = 0; $i < $length; $i++) {
            $secret .= $validChars[random_int(0, strlen($validChars) - 1)];
        }
        return $secret;
    }

    public static function getQRCodeUrl($email, $secret) {
        $issuer = urlencode('PMO-EMS');
        $label = urlencode($email);
        return "otpauth://totp/{$issuer}:{$label}?secret={$secret}&issuer={$issuer}";
    }

    public static function verifyCode($secret, $code, $discrepancy = 1) {
        if (strlen($code) != 6) {
            return false;
        }

        $timestamp = self::getTimestamp();
        $binarySecret = self::base32Decode($secret);

        for ($i = -$discrepancy; $i <= $discrepancy; $i++) {
            $calculatedCode = self::getCode($binarySecret, $timestamp + $i);
            if (self::timingSafeEquals($calculatedCode, $code)) {
                return true;
            }
        }
        return false;
    }

    private static function getCode($secret, $timestamp) {
        $timestamp = pack('N*', 0) . pack('N*', $timestamp);
        $hash = hash_hmac('SHA1', $timestamp, $secret, true);
        $offset = ord(substr($hash, -1)) & 0x0F;
        $hashpart = substr($hash, $offset, 4);
        $value = unpack('N', $hashpart);
        $value = $value[1];
        $value = $value & 0x7FFFFFFF;
        $modulo = pow(10, 6);
        return str_pad($value % $modulo, 6, '0', STR_PAD_LEFT);
    }

    private static function timingSafeEquals($safeString, $userString) {
        if (function_exists('hash_equals')) {
            return hash_equals($safeString, $userString);
        }
        $safeLen = strlen($safeString);
        $userLen = strlen($userString);
        if ($userLen != $safeLen) {
            return false;
        }
        $result = 0;
        for ($i = 0; $i < $userLen; $i++) {
            $result |= (ord($safeString[$i]) ^ ord($userString[$i]));
        }
        return $result === 0;
    }

    public static function generateBackupCodes() {
        $codes = [];
        for ($i = 0; $i < 8; $i++) {
            $codes[] = bin2hex(random_bytes(4));
        }
        return $codes;
    }

    public static function setup($userId, $userType, $secret) {
        global $pdo;
        $backupCodes = self::generateBackupCodes();
        $backupCodesJson = json_encode($backupCodes);

        $stmt = $pdo->prepare("INSERT INTO two_factor_auth (user_id, user_type, secret_key, backup_codes) 
                              VALUES (?, ?, ?, ?) 
                              ON DUPLICATE KEY UPDATE 
                              secret_key = VALUES(secret_key), 
                              backup_codes = VALUES(backup_codes)");
        
        return $stmt->execute([$userId, $userType, $secret, $backupCodesJson]);
    }

    public static function enable($userId, $userType) {
        global $pdo;
        $stmt = $pdo->prepare("UPDATE two_factor_auth SET is_enabled = TRUE 
                              WHERE user_id = ? AND user_type = ?");
        return $stmt->execute([$userId, $userType]);
    }

    public static function disable($userId, $userType) {
        global $pdo;
        $stmt = $pdo->prepare("UPDATE two_factor_auth SET is_enabled = FALSE 
                              WHERE user_id = ? AND user_type = ?");
        return $stmt->execute([$userId, $userType]);
    }

    public static function getStatus($userId, $userType) {
        global $pdo;
        $stmt = $pdo->prepare("SELECT * FROM two_factor_auth 
                              WHERE user_id = ? AND user_type = ?");
        $stmt->execute([$userId, $userType]);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    public static function verifyBackupCode($userId, $userType, $code) {
        global $pdo;
        $stmt = $pdo->prepare("SELECT backup_codes FROM two_factor_auth 
                              WHERE user_id = ? AND user_type = ?");
        $stmt->execute([$userId, $userType]);
        $result = $stmt->fetch(PDO::FETCH_ASSOC);

        if (!$result) {
            return false;
        }

        $backupCodes = json_decode($result['backup_codes'], true);
        $index = array_search($code, $backupCodes);

        if ($index !== false) {
            // Remove used backup code
            unset($backupCodes[$index]);
            $backupCodes = array_values($backupCodes); // Reindex array
            $stmt = $pdo->prepare("UPDATE two_factor_auth SET backup_codes = ? 
                                  WHERE user_id = ? AND user_type = ?");
            $stmt->execute([json_encode($backupCodes), $userId, $userType]);
            return true;
        }

        return false;
    }
} 