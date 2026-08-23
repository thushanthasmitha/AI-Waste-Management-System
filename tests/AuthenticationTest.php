<?php
// tests/AuthenticationTest.php

use PHPUnit\Framework\TestCase;

require_once __DIR__ . '/../includes/AuthValidator.php';

class AuthenticationTest extends TestCase {

    /**
     * @testdox TC-01: Valid user registration data
     */
    public function test_tc01_valid_user_registration_data() {
        $result = AuthValidator::validateRegistration('user@example.com', 'password123');
        $this->assertEquals('Validation successful', $result);
    }

    /**
     * @testdox TC-02: Invalid email format
     */
    public function test_tc02_invalid_email_format() {
        $result = AuthValidator::validateRegistration('invalid-email-format', 'password123');
        $this->assertEquals('Invalid email rejected', $result);
    }

    /**
     * @testdox TC-03: Short password
     */
    public function test_tc03_short_password() {
        $result = AuthValidator::validateRegistration('user@example.com', '123');
        $this->assertEquals('Short password rejected', $result);
    }

    /**
     * @testdox TC-04: Valid login data
     */
    public function test_tc04_valid_login_data() {
        $result = AuthValidator::validateLogin('user@example.com', 'password123');
        $this->assertEquals('Login data accepted', $result);
    }

    /**
     * @testdox TC-05: Invalid login email
     */
    public function test_tc05_invalid_login_email() {
        $result = AuthValidator::validateLogin('user-invalid-email', 'password123');
        $this->assertEquals('Invalid email rejected', $result);
    }

    /**
     * @testdox TC-06: Empty password
     */
    public function test_tc06_empty_password() {
        $result = AuthValidator::validateLogin('user@example.com', '');
        $this->assertEquals('Empty password rejected', $result);
    }
}
?>