<?php
/**
 * @desc
 * @author liumingjun@baidu.com
 */
require_once 'UnitTest/init/PTestInit.php';
PTestInit::setTest_App('Sample');
/**
 * Class Test_Validator
 */
class Test_Validator extends Testcase_PTest {

    /**
     *
     * @param $value
     * @param $hint
     * @return SF_Utility_Validate_ManagerSF
     */
    function createChain($value, $hint)
    {
        $hint = '[ValidatorTest]'.$hint;

        return  SF_Utility_Validate_ManagerSF::createValidChain($value,$hint,false);
    }

    /**
     * @param $hint
     * @param $value
     * @return string
     */
    private function getUtHint($hint, $value)
    {
        return $hint . ' test fail.' . 'now value[' . json_encode($value) . ']';
    }

    public function testNotEmpty()
    {
        $valueArr = array(
            '',
            null
        );
        $hint = 'not Empty';
        foreach ($valueArr as $value) {
            $this->assertFalse($this->createChain($value, $hint)->notEmpty()->isPass(), $this->getUtHint($hint, $value));
        }
    }

    public function testNotEmptyArray()
    {
        $valueArr = array(
            array(),
        );
        $hint = 'not Empty Array';
        foreach ($valueArr as $value) {
            $this->assertFalse($this->createChain($value, $hint)->notEmptyArray()->isPass(), $this->getUtHint($hint, $value));
        }
    }

    public function testIsEmail()
    {
        $successValueArr = array(
            'asf@126.com',
            '1231233@126.com',
        );
        $hint = 'is Email Address';
        foreach ($successValueArr as $value) {
            $this->assertTrue($this->createChain($value, $hint)->isEmail()->isPass(), $this->getUtHint($hint, $value));
        }

        $failValueArr = array(
            'asf12.com',
            '1231233afdffdsfdsf.com',
        );
        foreach ($failValueArr as $value) {
            $this->assertFalse($this->createChain($value, $hint)->isEmail()->isPass(), $this->getUtHint($hint, $value));
        }
    }

    public function testIsMatch()
    {
        $valueArr = array(
            '123456789012345',
        );
        $hint = 'is Match';
        foreach ($valueArr as $value) {
            $this->assertTrue($this->createChain($value, $hint)->isMatch("~^\d{15}$~", 'invalid order_sn')->isPass(), $this->getUtHint($hint, $value));
        }
    }

    public function testIsPhoneNum()
    {
        $valueArr = array(
            '12345678901',
        );
        $hint = 'is phone number';
        foreach ($valueArr as $value) {
            $this->assertTrue($this->createChain($value, $hint)->isPhoneNum()->isPass(), $this->getUtHint($hint, $value));
        }
    }

    public function testNotZero()
    {
        $valueArr = array(
            1,
        );
        $hint = 'is not zero';
        foreach ($valueArr as $value) {
            $this->assertTrue($this->createChain($value, $hint)->notZero()->isPass(), $this->getUtHint($hint, $value));
        }
    }

    public function testIsNumeric()
    {
        $valueArr = array(
            0,
            123123,
            '12312321'
        );
        $hint = 'is numeric';
        foreach ($valueArr as $value) {
            $this->assertTrue($this->createChain($value, $hint)->isNumeric()->isPass(), $this->getUtHint($hint, $value));
        }
    }


    public function testIsGreaterthanOrEqualtoZero()
    {
        $valueArr = array(
            0,
            123123,
            '12312321'
        );
        $hint = 'is IsGreaterthanOrEqualtoZero';
        foreach ($valueArr as $value) {
            $this->assertTrue($this->createChain($value, $hint)->isNumeric()->isPass(), $this->getUtHint($hint, $value));
        }
    }

    public function testIsInt()
    {
        $valueArr = array(
            0,
            123123,
            '12312321'
        );
        $hint = 'is IsInt';
        foreach ($valueArr as $value) {
            $this->assertTrue($this->createChain($value, $hint)->isInt()->isPass(), $this->getUtHint($hint, $value));
        }
    }

    public function testIsJson()
    {
        $valueArr = array(
            json_encode(array(
                'test' => 123123,
                'aaaa' =>1233123,
            )),
        );
        $hint = 'is IsJson';
        foreach ($valueArr as $value) {
            $this->assertTrue($this->createChain($value, $hint)->isJson()->isPass(), $this->getUtHint($hint, $value));
        }

        $valueArr = array(
            '}123123{fsdfs',
        );

        foreach ($valueArr as $value) {
            $this->assertFalse($this->createChain($value, $hint)->isJson()->isPass(), $this->getUtHint($hint, $value));
        }
    }

    public function testIsIndexArray()
    {
        $hint = 'is IsIndexArray';
        $valueArr = array(
            array(1,2,3),
            array(
                array(
                    'create_time'=>112312321
                ),
            ),
        );

        foreach ($valueArr as $value) {
            $this->assertTrue($this->createChain($value, $hint)->isIndexArray()->isPass(), $this->getUtHint($hint, $value));
        }


        $valueArr = array(
            //array('a'=>1,2,3),
            array(
                'create_time'=>112312321
            ),
        );
        foreach ($valueArr as $value) {
            $this->assertFalse($this->createChain($value, $hint)->isIndexArray()->isPass(), $this->getUtHint($hint, $value));
        }

    }


    public function testIsAssocArray()
    {

        $valueArr = array(
            array('a'=>1,'b'=>2,'c'=>3),
            array('a'=>1,2,3),
        );
        $hint = 'is IsAssocArray';
        foreach ($valueArr as $value) {
            $this->assertTrue($this->createChain($value, $hint)->isAssocArray()->isPass(), $this->getUtHint($hint, $value));
        }


        $valueArr = array(
            array(1,2,3)
        );
        foreach ($valueArr as $value) {
            $this->assertFalse($this->createChain($value, $hint)->isAssocArray()->isPass(), $this->getUtHint($hint, $value));
        }

    }

    public function testIsArray()
    {
        $valueArr = array(
            array('a'=>1,'b'=>2,'c'=>3),
        );
        $hint = 'is testIsArray';
        foreach ($valueArr as $value) {
            $this->assertTrue($this->createChain($value, $hint)->isArray()->isPass(), $this->getUtHint($hint, $value));
        }
    }

    public function testNotContain()
    {
        $hint = 'is testNotContain';

        $badArr = array(
            'a'
        );
        $valueArr = array(
            'abcdefg',
        );
        foreach ($valueArr as $value) {
            $this->assertFalse($this->createChain($value, $hint)->notContain($badArr)->isPass(), $this->getUtHint($hint, $value));
        }

        $valueArr = array(
            'bcdefg',
        );
        foreach ($valueArr as $value) {
            $this->assertTrue($this->createChain($value, $hint)->notContain($badArr)->isPass(), $this->getUtHint($hint, $value));
        }
    }

    public function testIsGreaterThan()
    {
        $valueArr = array(
           6,
           7
        );
        $hint = 'is testIsGreaterThan';
        foreach ($valueArr as $value) {
            $this->assertTrue($this->createChain($value, $hint)->isGreaterThan(5)->isPass(), $this->getUtHint($hint, $value));
        }
    }


    public function testIsLessThan()
    {
        $valueArr = array(
            6,
            7
        );
        $hint = 'is testIsLessThan';
        foreach ($valueArr as $value) {
            $this->assertTrue($this->createChain($value, $hint)->isLessThan(10)->isPass(), $this->getUtHint($hint, $value));
        }
    }

    public function testRunExtValidator()
    {
        $valueArr = array(
            '123123@123.com',
        );
        $hint = 'is runExtValidator';
        foreach ($valueArr as $value) {
            $this->assertTrue($this->createChain($value, $hint)->runExtValidator(new SF_Utility_Validate_Validator_EMailValidatorSF())->isPass(), $this->getUtHint($hint, $value));
        }
    }

}