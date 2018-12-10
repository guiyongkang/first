<?php
defined('SAFE_CONST') or exit('Access Invalid!');
$menu_list = array(
    'goods' => array(
        'name' => '商品',
        'child' => array(
            array(
                'name' => '商品发布',
                'act' => 'store_goods_add',
                'op' => 'index'
            ) ,
            /*array(
                'name' => '淘宝导入',
                'act' => 'taobao_import',
                'op' => 'index'
            ) ,*/
            array(
                'name' => '出售中的商品',
                'act' => 'store_goods_online',
                'op' => 'index'
            ) ,
            array(
                'name' => '仓库中的商品',
                'act' => 'store_goods_offline',
                'op' => 'index'
            ) ,
            array(
                'name' => '关联版式',
                'act' => 'store_plate',
                'op' => 'index'
            ) ,
            array(
                'name' => '商品规格',
                'act' => 'store_spec',
                'op' => 'index'
            ) ,
            array(
                'name' => '图片空间',
                'act' => 'store_album',
                'op' => 'album_cate'
            )
        )
    ) ,
    'order' => array(
        'name' => '订单物流',
        'child' => array(
            array(
                'name' => '实物交易订单',
                'act' => 'store_order',
                'op' => 'index'
            ) ,
            array(
                'name' => '虚拟兑码订单',
                'act' => 'store_vr_order',
                'op' => 'index'
            ) ,
            array(
                'name' => '发货',
                'act' => 'store_deliver',
                'op' => 'index'
            ) ,
            array(
                'name' => '发货设置',
                'act' => 'store_deliver_set',
                'op' => 'daddress_list'
            ) ,
            array(
                'name' => '运单模板',
                'act' => 'store_waybill',
                'op' => 'waybill_manage'
            ) ,
            array(
                'name' => '评价管理',
                'act' => 'store_evaluate',
                'op' => 'list'
            ) ,
            array(
                'name' => '售卖区域',
                'act' => 'store_transport',
                'op' => 'index'
            )
        )
    ) ,
    'promotion' => array(
        'name' => '促销',
        'child' => array(
            /*array(
                'name' => '抢购管理',
                'act' => 'store_groupbuy',
                'op' => 'index'
            ) ,*/
            array(
                'name' => '限时折扣',
                'act' => 'store_promotion_xianshi',
                'op' => 'xianshi_list'
            ) ,
            array(
                'name' => '满即送',
                'act' => 'store_promotion_mansong',
                'op' => 'mansong_list'
            ) ,
            /*array(
                'name' => '优惠套装',
                'act' => 'store_promotion_bundling',
                'op' => 'bundling_list'
            ) ,*/
            /*array(
                'name' => '推荐展位',
                'act' => 'store_promotion_booth',
                'op' => 'booth_goods_list'
            ) ,*/
            array(
                'name' => '代金券管理',
                'act' => 'store_voucher',
                'op' => 'templatelist'
            ) ,
            /*array(
                'name' => '活动管理',
                'act' => 'store_activity',
                'op' => 'store_activity'
            )*/
        )
    ) ,
    'store' => array(
        'name' => '店铺',
        'child' => array(
            array(
                'name' => '店铺设置',
                'act' => 'store_setting',
                'op' => 'store_setting'
            ) ,
            /*array(
                'name' => '店铺装修',
                'act' => 'store_decoration',
                'op' => 'decoration_setting'
            ) ,*/
            /*array(
                'name' => '店铺导航',
                'act' => 'store_navigation',
                'op' => 'navigation_list'
            ) ,*/
            /*array(
                'name' => '店铺动态',
                'act' => 'store_sns',
                'op' => 'index'
            ) ,*/
            array(
                'name' => '店铺信息',
                'act' => 'store_info',
                'op' => 'bind_class'
            ) ,
            array(
                'name' => '店铺分类',
                'act' => 'store_goods_class',
                'op' => 'index'
            ) ,
            /*array(
                'name' => '线下商铺',
                'act' => 'store_live',
                'op' => 'store_live'
            ) ,*/
            array(
                'name' => '品牌申请',
                'act' => 'store_brand',
                'op' => 'brand_list'
            )
        )
    ) ,
    'consult' => array(
        'name' => '售后服务',
        'child' => array(
            /*array(
                'name' => '咨询管理',
                'act' => 'store_consult',
                'op' => 'consult_list'
            ) ,*/
            /*array(
                'name' => '投诉管理',
                'act' => 'store_complain',
                'op' => 'index'
            ) ,*/
            array(
                'name' => '退款记录',
                'act' => 'store_refund',
                'op' => 'index'
            ) ,
            array(
                'name' => '退货记录',
                'act' => 'store_return',
                'op' => 'index'
            )
        )
    ) ,
    'statistics' => array(
        'name' => '统计结算',
        'child' => array(
            /*array(
                'name' => '店铺概况',
                'act' => 'statistics_general',
                'op' => 'general'
            ) ,
            array(
                'name' => '商品分析',
                'act' => 'statistics_goods',
                'op' => 'goodslist'
            ) ,
            array(
                'name' => '运营报告',
                'act' => 'statistics_sale',
                'op' => 'sale'
            ) ,
            array(
                'name' => '行业分析',
                'act' => 'statistics_industry',
                'op' => 'hot'
            ) ,
            array(
                'name' => '流量统计',
                'act' => 'statistics_flow',
                'op' => 'storeflow'
            ) ,*/
            array(
                'name' => '实物结算',
                'act' => 'store_bill',
                'op' => 'index'
            ) ,
            array(
                'name' => '虚拟结算',
                'act' => 'store_vr_bill',
                'op' => 'index'
            )
        )
    ) ,
    'message' => array(
        'name' => '客服消息',
        'child' => array(
            /*array(
                'name' => '客服设置',
                'act' => 'store_callcenter',
                'op' => 'index'
            ) ,*/
            array(
                'name' => '系统消息',
                'act' => 'store_msg',
                'op' => 'index'
            ) ,
            array(
                'name' => '聊天记录查询',
                'act' => 'store_im',
                'op' => 'index'
            )
        )
    ) ,
    'account' => array(
        'name' => '账号',
        'child' => array(
            array(
                'name' => '账号列表',
                'act' => 'store_account',
                'op' => 'account_list'
            ) ,
            array(
                'name' => '账号组',
                'act' => 'store_account_group',
                'op' => 'group_list'
            ) ,
            array(
                'name' => '账号日志',
                'act' => 'seller_log',
                'op' => 'log_list'
            ) ,
            array(
                'name' => '店铺消费',
                'act' => 'store_cost',
                'op' => 'cost_list'
            )
        )
    )
);