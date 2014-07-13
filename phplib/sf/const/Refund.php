<?php
/**
 * @desc 
 * author: liaohuiqin
 * date: 13-12-13
 * time: 下午8:42
 */

class SF_Const_Refund {

    const NUM_EACH_BATCH = 100; //每次批量更新/插入的元素个数;
    const BATCHNO_START = 1; //起始退款批次;
    const BATCHNO_NOT_ALLOCATE = 0 ; //没有分配退款批次;

    const STATUS_WAITING = 1; //退款审核中
    const STATUS_PASS = 2; //审核通过
    const STATUS_REFUNDING = 3;//退款中，已经发出请求给支付系统
    const STATUS_REFUSE = 4;    //退款拒绝
    const STATUS_CANCEL = 5;    //退款取消
    const STATUS_SUCC = 6;   //退款成功
    const STATUS_FAIL = 7;    //退款失败，会重试;

    const TYPE_AUTO_FROM_BUY = 1; //购买过程中异常发起的自动退款；
    const TYPE_AUTO_FROM_EXPIRE = 2; //过期发起的自动退款;
    const TYPE_NORMAL_FROM_USER =  3;   //用户发起的普通退款;
    const TYPE_NORMAL_FROM_CS = 4; //客服发起的普通退款;
    const TYPE_FORCE_FROM_TP  = 5;  //TP方发起的退款，等同于强制退款;
    const TYPE_FORCE_FROM_CS = 6; //来自客服(customer service)的强制退款;
    const TYPE_FREE_FROM_CS = 7;    //来自客服的免单退款;
    const TYPE_AUTO_FROM_LBSPAY = 100; //来自lbspay的单方面退款，比如引发反作弊;

    const CANCEL_TYPE_FROM_USER = 3; //用户发起的取消退款;
    const CANCEL_TYPE_FROM_CS = 4; //客服发起的取消退款;
    const CANCEL_TYPE_FROM_COUPON = 100; //由于期间券为验证等操作导致券无法退款;

    const APPLY_REFUND_FAIL = 0; //为了兼容之前的系统的返回，给出apply_refund的定义，0表示退款失败;
    const APPLY_REFUND_SUCC = 1; //为了兼容之前的系统的返回，给出apply_refund的定义，1表示退款成功;

    static $arrRefundApplyReason = array(
        self::TYPE_AUTO_FROM_EXPIRE => '由于商品过期引发自动退款',
        self::TYPE_AUTO_FROM_LBSPAY => '由于命中反作弊引发支付平台单方面自动退款',
    );
    const REASON_LBSPAY_REFUND = '内部退款';

    //如果是tp-v2的订单，以下退款类型不需要跟TP交互;
    static $arrNoNeedToTpType  = array(
        self::TYPE_AUTO_FROM_BUY,
        self::TYPE_FORCE_FROM_TP,
        self::TYPE_FREE_FROM_CS
    );


    static $arrCanRetryRefund = array(
        //能够重复发起退款的状态集合;
        'refund_status' => array(
            self::STATUS_REFUSE,
            self::STATUS_WAITING),

        //能够重复发起退款的退款类型;
        'refund_type' => array(
            self::TYPE_NORMAL_FROM_CS,
            self::TYPE_NORMAL_FROM_USER,
            self::TYPE_FORCE_FROM_CS,
        ),
        //能够重复发起退款的券状态;
        'coupon_status' => array(
            SF_Const_Coupon::COUPON_STATUS_REFUNDINT_AUDITING,
            SF_Const_Coupon::COUPON_STATUS_REFUNDINT_REJECT,
        )
    );

    //可以向支付平台发起请求的退款记录条件;
    static $arrCanRefunding = array(
        'refund_status' => array(
            self::STATUS_PASS,
            self::STATUS_FAIL,
            self::STATUS_REFUNDING,
        ),
        'refund_type' => array(
            self::TYPE_FORCE_FROM_TP,
            self::TYPE_NORMAL_FROM_CS,
            self::TYPE_AUTO_FROM_EXPIRE,
            self::TYPE_FORCE_FROM_CS,
            self::TYPE_AUTO_FROM_BUY,
            self::TYPE_FREE_FROM_CS,
            self::TYPE_NORMAL_FROM_USER,
        )
    );

    static $arrCannotCancelRefund = array(
        //不能取消的退款类型;
        'refund_type' => array(
            self::TYPE_AUTO_FROM_LBSPAY,
            self::TYPE_AUTO_FROM_BUY,
            self::TYPE_AUTO_FROM_EXPIRE,
            self::TYPE_FORCE_FROM_TP,
        ),
    );
    const REFUSE_REASON = 'refuse_reason';
    
    //商品退款类型，过期退&随时退
    const ITEM_REFUND_EXPIRE = 1;
    const ITEM_REFUND_ANYTIME = 1;

} 