<?php
/**
 * @desc
 * @author liumingjun@baidu.com
 */
require_once 'UnitTest/init/PTestInit.php';
PTestInit::setTest_App('Sample');
/**
 * Class Test_Entity
 */
class Test_Entity extends Testcase_PTest
{

    public function testInit()
    {
        $val = array(
            SF_Entity_CallServiceRequestModel::REQUEST_PARAM_OP => 1,
            SF_Entity_CallServiceRequestModel::REQUEST_PARAM_TOKEN =>2,
            SF_Entity_CallServiceRequestModel::REQUEST_PARAM_DATA =>3,
        );

        $callServiceRequestModel = new SF_Entity_CallServiceRequestModel();

        $callServiceRequestModel->instantiate($val);

        $this->assertTrue($callServiceRequestModel->token === $val[SF_Entity_CallServiceRequestModel::REQUEST_PARAM_TOKEN], 'entity初始化单测运行失败');
    }

    public function testMerge()
    {
        $firstVal = array(
            SF_Entity_CallServiceRequestModel::REQUEST_PARAM_OP => 1,
            SF_Entity_CallServiceRequestModel::REQUEST_PARAM_TOKEN =>2,
            SF_Entity_CallServiceRequestModel::REQUEST_PARAM_DATA =>3,
        );

        $entity = new SF_Entity_CallServiceRequestModel();

        $entity->instantiate($firstVal);

        $secondVal = array(
            SF_Entity_CallServiceRequestModel::REQUEST_PARAM_TOKEN =>2222,
        );

        $entity->merge($secondVal);


        $this->assertTrue($entity->token === $secondVal[SF_Entity_CallServiceRequestModel::REQUEST_PARAM_TOKEN], 'entity初始化单测运行失败');

    }

    public function testEmpty()
    {
        $firstVal = array(
            SF_Entity_CallServiceRequestModel::REQUEST_PARAM_OP => 1,
            SF_Entity_CallServiceRequestModel::REQUEST_PARAM_TOKEN =>2,
            SF_Entity_CallServiceRequestModel::REQUEST_PARAM_DATA =>3,
        );

        $entity = new SF_Entity_CallServiceRequestModel($firstVal);

        $this->assertTrue(!empty($entity->psSeg));
    }

    public function testUnset()
    {
        $firstVal = array(
            SF_Entity_CallServiceRequestModel::REQUEST_PARAM_OP => 1,
            SF_Entity_CallServiceRequestModel::REQUEST_PARAM_TOKEN =>2,
            SF_Entity_CallServiceRequestModel::REQUEST_PARAM_DATA =>3,
        );

        $entity = new SF_Entity_CallServiceRequestModel($firstVal);

        unset($entity->psSeg);

        $this->assertTrue(empty($entity->psSeg));

    }


}