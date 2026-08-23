<?php
// includes/AuthValidator.php

class AuthValidator {
    
    public static function validateRegistration($email, $password) {
        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            return "Invalid email rejected";
        }
        if (strlen($password) < 6) {
            return "Short password rejected";
        }
        return "Validation successful";
    }

    public static function validateLogin($email, $password) {
        if (empty($password)) {
            return "Empty password rejected";
        }
        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            return "Invalid email rejected";
        }
        return "Login data accepted";
    }
}
?>