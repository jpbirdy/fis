<?php
/**
 * @desc 如果要写一个单测，直接复制然后稍作改写就行
 * @author liumingjun@baidu.com
 */
require_once 'UnitTest/init/PTestInit.php';
PTestInit::setTest_App('Sample');

/**
 * @desc 以test作为前缀即可
 *       关联PHPUNIT和PHPTEST/UNITTEST的类库，可以更方便使用
 * Class Write_Anything 单测的例子
 */
class Write_Anything extends Testcase_PTest {
    public function testMethod() {
        $this->assertTrue(true,'2222');
    }
}