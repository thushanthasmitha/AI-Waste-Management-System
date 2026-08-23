<?php
// tests/AiPredictionTest.php

use PHPUnit\Framework\TestCase;

class AiPredictionValidator {
    public static function predictOverflowRisk($fillLevel) {
        if ($fillLevel >= 50) {
            return 'High Risk status';
        }
        return 'Normal Status';
    }
}

class AiPredictionTest extends TestCase {

    /**
     * @testdox TC-11: High overflow risk
     */
    public function test_tc11_high_overflow_risk() {
        $status = AiPredictionValidator::predictOverflowRisk(85);
        $this->assertEquals('High Risk status', $status);
    }

    /**
     * @testdox TC-12: Normal overflow risk
     */
    public function test_tc12_normal_overflow_risk() {
        $status = AiPredictionValidator::predictOverflowRisk(30);
        $this->assertEquals('Normal Status', $status);
    }

    /**
     * @testdox TC-13: 50% risk boundary
     */
    public function test_tc13_50_risk_boundary() {
        $status = AiPredictionValidator::predictOverflowRisk(50);
        $this->assertEquals('High Risk status', $status);
    }

    /**
     * @testdox TC-14: Low overflow risk
     */
    public function test_tc14_low_overflow_risk() {
        $status = AiPredictionValidator::predictOverflowRisk(10);
        $this->assertEquals('Normal Status', $status);
    }
}
?>