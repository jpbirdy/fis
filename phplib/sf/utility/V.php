<?php
/**
 * @desc   验证用的快捷方法，封装常用的单个验证方法
 * @author liumingjun@baidu.com
 */
class SF_Utility_V {
    const TERMINAL_PC = 'PC';
    const TERMINAL_WAP = 'WAP';
    const TERMINAL_APP = 'APP';
    const DEFAULT_CHANNLE = 'baidutuan_tg';

    /**
     * @param      $orderSn
     * @param bool $throwException
     * @return bool
     */
    public static function isOrderSn($orderSn, $throwException = false) {
        return SF_Utility_Manager::validator($orderSn, 'order_sn', $throwException)
            ->isMatch('~^\d{15,16}$~', 'invalid order_sn')->isPass();
    }

    /**
     * @desc 判断是否是有效的item_id
     * @param      $itemId
     * @param bool $throwException
     * @return bool
     */
    public static function isItemId($itemId, $throwException = false) {
        return SF_Utility_Validate_ManagerSF::createValidChain($itemId, 'item_id', $throwException)
            ->isInt()->isGreaterThanOrEqualToZero()->notZero()->isPass();
    }

    /**
     * @desc 判断pass_uid是否有效;
     * @param      $passUid
     * @param bool $throwException
     * @return bool
     */
    public static function isPassuid($passUid, $throwException = false) {
        return SF_Utility_Validate_ManagerSF::createValidChain($passUid, 'pass_uid', $throwException)
            ->isInt()->notZero()->isPass();
    }

    /**
     * @param $type
     * @return bool
     */
    public static function isTpV2($type) {
        return $type == SF_Const_Coupon::COUPON_TYPE_TPV2;
    }

    /**
     * @param $type
     * @return bool
     */
    public static function isDeliver($type) {
        return $type == 5;
    }

    /**
     * @param $type
     * @return bool
     */
    public static function isThirdCoupon($type) {
        return $type == SF_Const_Coupon::COUPON_TYPE_THIRD;
    }

    /**
     * @param $type
     * @return bool
     */
    public static function isSelfCoupon($type) {
        return $type === SF_Const_Coupon::COUPON_TYPE_OWN;
    }

    /**
     * @param $tpId
     * @return bool
     */
    public static function isSelfItemByTpId($tpId) {
        return !($tpId > 0);
    }

    /**
     * @param      $phone
     * @param bool $throwException
     * @return bool
     */
    public static function isPhone($phone, $throwException = false) {
        return SF_Utility_Validate_ManagerSF::createValidChain($phone, 'phone', $throwException)->isPhoneNum()->isPass();
    }


    /**
     * @desc 判断是否是抽奖商品
     * @param  int $itemType
     * @return bool
     */
    public static function isLotteryItem($itemType) {
        return $itemType == SF_Const_Item::ITEM_TYPE_LOTT;
    }

    /**
     * @desc 判断是否是抽奖订单
     * @param  int $orderType
     * @return bool
     */
    public static function isLotteryOrder($orderType) {
        return $orderType == SF_Const_Order::ORDER_TYPE_LOTT;
    }

    /**
     * @desc 商品是否支持过期退款;
     * @param $refundType
     * @return bool
     */
    public static function isExpirRefund($refundType) {
        return $refundType == SF_Const_Refund::ITEM_REFUND_EXPIRE;
    }

    /**
     * @desc 商品是否支持随时退款;
     * @param $refundType
     * @return bool
     */
    public static function isAnyTimeRefund($refundType) {
        return $refundType == SF_Const_Refund::ITEM_REFUND_ANYTIME;
    }

    /**
     * 是否过期（包含被八爪鱼强制下线）
     * @param $status
     * @return bool
     */
    public static function isItemExpired($status) {
        return $status == SF_Const_Item::ITEM_STATUS_OVER;
    }

    /**
     * @param $status
     * @return bool
     */
    public static function isItemOnLine($status) {
        return $status == SF_Const_Item::ITEM_STATUS_PUB;
    }

    /**
     * @param $status
     * @return bool
     */
    public static function isItemSeldout($status) {
        return $status == SF_Const_Item::ITEM_STATUS_SOLDOUT;
    }

    /**
     * 支付减去库存
     * @param $flag
     * @return bool
     */
    public static function isReduceStockPaid($flag) {
        return $flag == SF_Const_Item::REDUCE_STOCK_AFTER_ORDER_PAID;
    }

    /**
     * 下单减去库存
     * @param $flag
     * @return bool
     */
    public static function isReduceStockCreate($flag) {
        return $flag == SF_Const_Item::REDUCE_STOCK_BEFORE_ORDER_PAID;
    }

    /**
     * @param array $order
     * @return bool
     */
    public static function isOrderPaid(array $order) {
        return SF_Const_Order::STATUS_PAY == $order['status'];
    }

    /**
     * @param array $order
     * @return bool
     */
    public static function isOrderUnPaid(array $order) {
        return SF_Const_Order::STATUS_UNPAY == $order['status'];
    }

    /**
     * @param $channelFrom
     * @return mixed
     */
    public static function channelFromToTerminalType($channelFrom = false) {
        $map = array(
            self::DEFAULT_CHANNLE => self::TERMINAL_PC,
            'baidutuan_wx' => self::TERMINAL_WAP,
            'tuanbai_ios' => self::TERMINAL_APP,
            'tuanbai_android' => self::TERMINAL_APP,
            'tuandutuan_mm' => self::TERMINAL_APP,
            'baidutuan_ad' => self::TERMINAL_APP,
            'baidutuan_is' => self::TERMINAL_APP,
            'baidutuan_tg ' => self::TERMINAL_PC,
            'tuanapp_ios' => self::TERMINAL_APP,
            'tuanapp_android' => self::TERMINAL_APP,
            'baidutuan_mp' => self::TERMINAL_WAP,
            'baidutuan_mm' => self::TERMINAL_WAP,
            'baidutuan_app' => self::TERMINAL_APP,
            'badidutuan_wx' => self::TERMINAL_WAP,
            'tuangou_pc' => self::TERMINAL_PC,
            'baidutuangou' => self::TERMINAL_PC,
            'baiduboxapp' => self::TERMINAL_APP,
            'baidutuan_ios' => self::TERMINAL_APP,
            'baidutuanboxapp' => self::TERMINAL_APP,
        );

        if (false === $channelFrom) {
            return array_keys($map);
        }

        if (!array_key_exists($channelFrom, $map)) {
            $channelFrom = self::DEFAULT_CHANNLE;
        }

        return $map[$channelFrom];
    }
}
