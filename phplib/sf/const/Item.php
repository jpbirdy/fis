<?php
/**
 * author: jiachenhui01@baidu.com
 * Date: 13-12-26
 * Time: 上午11:51
 */
    class SF_Const_Item
    {
        //商品类型
        const ITEM_TYPE_COM = 1;   			//普通商品
        const ITEM_TYPE_LOTT = 2;  			//抽奖商品
        const ITEM_TYPE_THIRD_COUPON = 3;	//三方站点券
        const ITEM_TYPE_TPV2 = 4;           //tpv2的商品
        const ITEM_TYPE_DELIVERY = 5;       //物流单

        //商品减库存类型
        const REDUCE_STOCK_AFTER_ORDER_PAID = 0; //支付减库存
        const REDUCE_STOCK_BEFORE_ORDER_PAID = 1; //下单减库存

        //商品状态
        const ITEM_STATUS_PUB = 1; //在线
        const ITEM_STATUS_SOLDOUT = 2; //卖光
        const ITEM_STATUS_OVER = 3; //过期和强制下线

        //是否支持退款
        const ITEM_SUPPORT_REFUND_YES = 1; //支持退款
        const ITEM_SUPPORT_REFUND_NO = 0; //不支持退款
    }