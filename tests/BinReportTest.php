<?php
// tests/BinReportTest.php

use PHPUnit\Framework\TestCase;

class BinReportValidator {
    public static function validateReport($location, $wasteType, $description) {
        if (empty($location)) {
            return 'Empty location rejected';
        }
        if (empty($wasteType)) {
            return 'Empty waste type rejected';
        }
        if (empty($description)) {
            return 'Empty description rejected';
        }
        return 'Valid report accepted';
    }
}

class BinReportTest extends TestCase {

    /**
     * @testdox TC-07: Valid bin report
     */
    public function test_tc07_valid_bin_report() {
        $result = BinReportValidator::validateReport('Colombo 07', 'Plastic', 'Bin is overflowing');
        $this->assertEquals('Valid report accepted', $result);
    }

    /**
     * @testdox TC-08: Empty location
     */
    public function test_tc08_empty_location() {
        $result = BinReportValidator::validateReport('', 'Plastic', 'Bin is overflowing');
        $this->assertEquals('Empty location rejected', $result);
    }

    /**
     * @testdox TC-09: Empty waste type
     */
    public function test_tc09_empty_waste_type() {
        $result = BinReportValidator::validateReport('Colombo 07', '', 'Bin is overflowing');
        $this->assertEquals('Empty waste type rejected', $result);
    }

    /**
     * @testdox TC-10: Empty description
     */
    public function test_tc10_empty_description() {
        $result = BinReportValidator::validateReport('Colombo 07', 'Plastic', '');
        $this->assertEquals('Empty description rejected', $result);
    }
}
?>