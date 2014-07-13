<?php
/**
 * @desc
 * @author liumingjun@baidu.com
 */
require_once 'UnitTest/init/PTestInit.php';
PTestInit::setTest_App('tradecenter');
class Test_SplitDBEngine extends Testcase_PTest
{
    public function __construct()
    {
        Fis_Appenv::setCurrApp('tradecenter');
        SF_Log_Manager::setUnitTestEnv();
    }

    const NODE = 'trade';

    const TABLE = 'trade_coupon';

    const INSERT_TYPE = 20;

    /**
     * @desc 获取查询条件
     *
     * @return array
     */
    protected function getConds()
    {
        return array(
            'type=' => self::INSERT_TYPE

        );
    }


    public function atestSelect()
    {
        $res = SF_DataSource_Manager::getDBInstance(self::NODE)->select(self::TABLE, 'id');
        $this->assertTrue($res !== false, '建立DB错误');
    }

    public function testCreate()
    {
        $entry = array(
            'id' => time(),
            'code' => time(),
            'order_sn' => 12312321321,
            'item_id' => 12312,
            'pass_uid' =>123231,
            'status' => 11,
            'poi_id' => 13123123321,
            'create_time' => time(),
            'update_time' => time(),
            'use_time' => time(),
            'refund_apply_time' => time(),
            'refund_time' => time(),
            'expire_time' => time(),
            'settle_time' => time(),
            'send_time' => time(),
            'send_phone' => 12321123,
            'flag' => 1,
            'type' =>1,
            'tp_id' =>423,
            'interpose_type' =>1,
            'interpose_status' =>1,
            'refund_batchno' => 1,
            'op_reason' => 123123213,
        );
        $res = SF_DataSource_Manager::getDBInstance(self::NODE)->create(self::TABLE, $entry);
        $this->assertTrue($res > 0, 'create方法无法正常运行');
    }

    public function atestUpdate()
    {
        $timeStamp = time();
        $entry = array(
            'cur_table' => $timeStamp,
            'update_time' => time(),
        );


        $res = SF_DataSource_Manager::getDBInstance(self::NODE)->update(self::TABLE, $entry, $this->getConds());
        $this->assertTrue($res >= 0, '更新失败，请查原因');

        $res = SF_DataSource_Manager::getDBInstance(self::NODE)->select(self::TABLE, 'cur_table', $this->getConds());

        $this->assertTrue(count($res) > 0, '获取数据出错');

        $this->assertTrue($timeStamp === intval($res[0]['cur_table']), '未成功更新');
    }

    public function atestDelete()
    {
        $res = SF_DataSource_Manager::getDBInstance(self::NODE)->delete(self::TABLE, $this->getConds());
        $this->assertTrue($res >= 1, '删除错误');

        $res = SF_DataSource_Manager::getDBInstance(self::NODE)->select(self::TABLE, 'cur_table', $this->getConds());

        $this->assertTrue(count($res) === 0, '未成功删除');

    }
} 