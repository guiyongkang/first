<?php
defined('SAFE_CONST') or exit('Access Invalid!');
/**
 * top 数组是顶部菜单 ，left数组是左侧菜单
 * left数组中'args'=>'welcome,dashboard,dashboard',三个分别为op,act,nav，权限依据act来判断
 */
$arr = array(
    'top' => array(
        array(
            'args' => 'dashboard',
            'text' => $lang['nc_console']
        ) ,
        array(
            'args' => 'setting',
            'text' => $lang['nc_config']
        ) ,
        array(
            'args' => 'goods',
            'text' => $lang['nc_goods']
        ) ,
        array(
            'args' => 'store',
            'text' => $lang['nc_store']
        ) ,
        array(
            'args' => 'member',
            'text' => $lang['nc_member']
        ) ,
        array(
            'args' => 'trade',
            'text' => $lang['nc_trade']
        ) ,
        
        array(
            'args' => 'operation',
            'text' => $lang['nc_operation']
        ) ,
        array(
            'args' => 'stat',
            'text' => $lang['nc_stat']
        ) ,
		array(
			'args' => 'wechat',
			'text' => $lang['nc_wechat']
		),
		array(
			'args' => 'distributor',
			'text' => $lang['nc_distributor']
		),
    ) ,
    'left' => array(
        array(
            'nav' => 'dashboard',
            'text' => $lang['nc_normal_handle'],
            'list' => array(
                array(
                    'args' => 'welcome,dashboard,dashboard',
                    'text' => $lang['nc_welcome_page']
                ) ,
                array(
                    'args' => 'aboutus,dashboard,dashboard',
                    'text' => $lang['nc_aboutus']
                ) ,
                array(
                    'args' => 'base,setting,dashboard',
                    'text' => $lang['nc_web_set']
                ) ,
                array(
                    'args' => 'member,member,dashboard',
                    'text' => $lang['nc_member_manage']
                ) ,
                array(
                    'args' => 'list,ownshop,dashboard',
                    'text' => $lang['nc_store_manage']
                ) ,
                array(
                    'args' => 'goods,goods,dashboard',
                    'text' => $lang['nc_goods_manage']
                ) ,
                array(
                    'args' => 'index,order,dashboard',
                    'text' => $lang['nc_order_manage']
                ) ,
            )
        ) ,
        array(
            'nav' => 'setting',
            'text' => $lang['nc_config'],
            'list' => array(
                array(
                    'args' => 'base,setting,setting',
                    'text' => $lang['nc_web_set']
                ) ,
                array(
                    'args' => 'sms,account,setting',
                    'text' => $lang['nc_web_account_syn']
                ) ,
                array(
                    'args' => 'param,upload,setting',
                    'text' => $lang['nc_upload_set']
                ) ,
                /*array(
                    'args' => 'seo,setting,setting',
                    'text' => $lang['nc_seo_set']
                ) ,*/
                array(
                    'args' => 'email,message,setting',
                    'text' => $lang['nc_message_set']
                ) ,
                
                array(
                    'args' => 'admin,admin,setting',
                    'text' => $lang['nc_limit_manage']
                ) ,
                array(
                    'args' => 'index,express,setting',
                    'text' => $lang['nc_admin_express_set']
                ) ,
                array(
                    'args' => 'waybill_list,waybill,setting',
                    'text' => '运单模板'
                ) ,
                array(
                    'args' => 'index,offpay_area,setting',
                    'text' => $lang['nc_admin_offpay_area_set']
                ) ,
                array(
                    'args' => 'clear,cache,setting',
                    'text' => $lang['nc_admin_clear_cache']
                ) ,
                array(
                    'args' => 'db,db,setting',
                    'text' => '数据备份'
                ) ,
                /*array(
                    'args' => 'perform,perform,setting',
                    'text' => $lang['nc_admin_perform_opt']
                ) ,*/
                array(
                    'args' => 'search,search,setting',
                    'text' => $lang['nc_admin_search_set']
                ) ,
                array(
                    'args' => 'list,admin_log,setting',
                    'text' => $lang['nc_admin_log']
                ) ,
            )
        ) ,
        array(
            'nav' => 'goods',
            'text' => $lang['nc_goods'],
            'list' => array(
                array(
                    'args' => 'goods_class,goods_class,goods',
                    'text' => $lang['nc_class_manage']
                ) ,
                array(
                    'args' => 'brand,brand,goods',
                    'text' => $lang['nc_brand_manage']
                ) ,
                array(
                    'args' => 'goods,goods,goods',
                    'text' => $lang['nc_goods_manage']
                ) ,
                array(
                    'args' => 'type,type,goods',
                    'text' => $lang['nc_type_manage']
                ) ,
                array(
                    'args' => 'spec,spec,goods',
                    'text' => $lang['nc_spec_manage']
                ) ,
                array(
                    'args' => 'list,goods_album,goods',
                    'text' => $lang['nc_album_manage']
                ) ,
                /*array(
                    'args' => 'index,web_channel,goods',
                    'text' => '频道管理'
                ) ,*/
                array(
                    'args' => 'goodsr,goodsr,goods',
                    'text' => '全返商品'
                ),
                array(
                    'args' => 'getfanyong,goodsr,goods',
                    'text' => '全返明细'
                )
            )
        ) ,
        array(
            'nav' => 'store',
            'text' => $lang['nc_store'],
            'list' => array(
                /*array(
                    'args' => 'store,store,store',
                    'text' => $lang['nc_store_manage']
                ) ,*/
				array(
                    'args' => 'list,ownshop,store',
                    'text' => $lang['nc_store_manage']
                ) ,
                /*array(
                    'args' => 'store_grade,store_grade,store',
                    'text' => $lang['nc_store_grade']
                ) ,*/
                array(
                    'args' => 'store_class,store_class,store',
                    'text' => $lang['nc_store_class']
                ) ,
                /*array(
                    'args' => 'store_domain_setting,domain,store',
                    'text' => $lang['nc_domain_manage']
                ) ,*/
                /*array(
                    'args' => 'stracelist,sns_strace,store',
                    'text' => $lang['nc_s_snstrace']
                ) ,*/
                /*array(
                    'args' => 'help_store,help_store,store',
                    'text' => '店铺帮助'
                ) ,*/
                /*array(
                    'args' => 'edit_info,store_joinin,store',
                    'text' => '开店首页'
                ) ,*/
            )
        ) ,
        array(
            'nav' => 'member',
            'text' => $lang['nc_member'],
            'list' => array(
                array(
                    'args' => 'member,member,member',
                    'text' => $lang['nc_member_manage']
                ) ,
                array(
                    'args' => 'index,member_grade,member',
                    'text' => '会员级别'
                ) ,
				
                array(
                    'args' => 'index,exppoints,member',
                    'text' => $lang['nc_exppoints_manage']
                ) ,
				
                /*array(
                    'args' => 'notice,notice,member',
                    'text' => $lang['nc_member_notice']
                ) ,*/
				/*
                array(
                    'args' => 'addpoints,points,member',
                    'text' => $lang['nc_member_pointsmanage']
                ) ,*/
                array(
                    'args' => 'predeposit,predeposit,member',
                    'text' => $lang['nc_member_predepositmanage']
                ) ,
                /*array(
                    'args' => 'sharesetting,sns_sharesetting,member',
                    'text' => $lang['nc_binding_manage']
                ) ,*/
                array(
                    'args' => 'class_list,sns_malbum,member',
                    'text' => $lang['nc_member_album_manage']
                ) ,
                /*array(
                    'args' => 'tracelist,snstrace,member',
                    'text' => $lang['nc_snstrace']
                ) ,*/
                /*array(
                    'args' => 'member_tag,sns_member,member',
                    'text' => $lang['nc_member_tag']
                ) ,*/
                /*array(
                    'args' => 'chat_log,chat_log,member',
                    'text' => '聊天记录'
                )*/
            )
        ) ,
        array(
            'nav' => 'trade',
            'text' => $lang['nc_trade'],
            'list' => array(
                array(
                    'args' => 'index,order,trade',
                    'text' => $lang['nc_order_manage']
                ) ,
                array(
                    'args' => 'index,vr_order,trade',
                    'text' => '虚拟订单'
                ) ,
                array(
                    'args' => 'refund_manage,refund,trade',
                    'text' => '退款管理'
                ) ,
                array(
                    'args' => 'return_manage,returned,trade',
                    'text' => '退货管理'
                ) ,
                array(
                    'args' => 'refund_manage,vr_refund,trade',
                    'text' => '虚拟订单退款'
                ) ,
                /*array(
                    'args' => 'consulting,consulting,trade',
                    'text' => $lang['nc_consult_manage']
                ) ,*/
                /*array(
                    'args' => 'inform_list,inform,trade',
                    'text' => $lang['nc_inform_config']
                ) ,*/
                array(
                    'args' => 'evalgoods_list,evaluate,trade',
                    'text' => $lang['nc_goods_evaluate']
                ) ,
                /*array(
                    'args' => 'complain_new_list,complain,trade',
                    'text' => $lang['nc_complain_config']
                ) ,*/
            )
        ) ,
        
        array(
            'nav' => 'operation',
            'text' => $lang['nc_operation'],
            'list' => array(
                array(
                    'args' => 'setting,operation,operation',
                    'text' => $lang['nc_operation_set']
                ) ,
                /*array(
                    'args' => 'index,groupbuy,operation',
                    'text' => $lang['nc_groupbuy_manage']
                ) ,
                array(
                    'args' => 'index,vr_groupbuy,operation',
                    'text' => '虚拟抢购设置'
                ) ,*/
                array(
                    'args' => 'index,promotion_xianshi,operation',
                    'text' => $lang['nc_promotion_xianshi']
                ) ,
                array(
                    'args' => 'index,promotion_mansong,operation',
                    'text' => $lang['nc_promotion_mansong']
                ) ,
                /*array(
                    'args' => 'bundling_list,promotion_bundling,operation',
                    'text' => $lang['nc_promotion_bundling']
                ) ,*/
                /*array(
                    'args' => 'goods_list,promotion_booth,operation',
                    'text' => $lang['nc_promotion_booth']
                ) ,*/
                array(
                    'args' => 'index,voucher,operation',
                    'text' => $lang['nc_voucher_price_manage']
                ) ,
                array(
                    'args' => 'index,bill,operation',
                    'text' => $lang['nc_bill_manage']
                ) ,
                array(
                    'args' => 'index,vr_bill,operation',
                    'text' => '虚拟订单结算'
                ) ,
                /*array(
                    'args' => 'activity,activity,operation',
                    'text' => $lang['nc_activity_manage']
                ) ,*/
                /*array(
                    'args' => 'pointprod,pointprod,operation',
                    'text' => $lang['nc_pointprod']
                ) ,
                array(
                    'args' => 'index,red_packet,operation',
                    'text' => '红包管理'
                ) ,*/
                /*array(
                    'args' => 'index,mall_consult,operation',
                    'text' => '平台客服'
                ) ,*/
                array(
                    'args' => 'index,rechargecard,operation',
                    'text' => '平台充值卡'
                ) ,
                /*array(
                    'args' => 'index,delivery,operation',
                    'text' => '物流自提服务站'
                ),*/
				array(
					'args' => 'article_class,article_class,operation',
					'text' => $lang['nc_article_class']
				) ,
				array(
					'args' => 'article,article,operation',
					'text' => $lang['nc_article_manage']
				) ,
            )
        ) ,
        array(
            'nav' => 'stat',
            'text' => $lang['nc_stat'],
            'list' => array(
                array(
                    'args' => 'general,stat_general,stat',
                    'text' => $lang['nc_statgeneral']
                ) ,
                /*array(
                    'args' => 'scale,stat_industry,stat',
                    'text' => $lang['nc_statindustry']
                ) ,
                array(
                    'args' => 'newmember,stat_member,stat',
                    'text' => $lang['nc_statmember']
                ) ,
                array(
                    'args' => 'newstore,stat_store,stat',
                    'text' => $lang['nc_statstore']
                ) ,
                array(
                    'args' => 'income,stat_trade,stat',
                    'text' => $lang['nc_stattrade']
                ) ,
                array(
                    'args' => 'pricerange,stat_goods,stat',
                    'text' => $lang['nc_statgoods']
                ) ,
                array(
                    'args' => 'promotion,stat_marketing,stat',
                    'text' => $lang['nc_statmarketing']
                ) ,
                array(
                    'args' => 'refund,stat_aftersale,stat',
                    'text' => $lang['nc_stataftersale']
                ) ,*/
            )
        ) ,
		array(
			'nav' => 'wechat',
			'text' => $lang['nc_wechat'],
			'list' => array(
				array(
					'args' => 'setting_manage,wechat,wechat',
					'text' => $lang['nc_wechat_setting']
				) ,
				array(
					'args' => 'api_manage,wechat,wechat',
					'text' => $lang['nc_wechat_api']
				) ,
				array(
					'args' => 'material_manage,wechat,wechat',
					'text' => $lang['nc_wechat_material']
				) ,
				array(
					'args' => 'subcribe_manage,wechat,wechat',
					'text' => $lang['nc_wechat_subcribe']
				) ,
				array(
					'args' => 'menu_manage,wechat,wechat',
					'text' => $lang['nc_wechat_menu']
				) ,
				array(
					'args' => 'keyword_manage,wechat,wechat',
					'text' => $lang['nc_wechat_keyword']
				) ,
				array(
					'args' => 'url_manage,wechat,wechat',
					'text' => $lang['nc_wechat_url']
				)
			)
		),
		array(
			'nav' => 'distributor',
			'text' => $lang['nc_distributor'],
			'list' => array(
				array(
					'args' => 'goodslist,distributor,distributor',
					'text' => $lang['nc_distributor_goodslist']
				),
				array(
					'args' => 'setting,distributor,distributor',
					'text' => $lang['nc_distributor_setting']
				),
				array(
					'args' => 'levellist,distributor,distributor',
					'text' => $lang['nc_distributor_levellist']
				),
				array(
					'args' => 'dislists,distributor_list,distributor',
					'text' => $lang['nc_distributor_dislists']
				),
				array(
					'args' => 'pubareas,distributor_list,distributor',
					'text' => $lang['nc_distributor_pubareas']
				),
				array(
					'args' => 'publists,distributor_list,distributor',
					'text' => $lang['nc_distributor_publists']
				),
				array(
					'args' => 'disrecord,distributor_list,distributor',
					'text' => $lang['nc_distributor_disrecord']
				),
				array(
					'args' => 'withdrawmethod,distributor_list,distributor',
					'text' => $lang['nc_distributor_withdrawmethod']
				),
				array(
					'args' => 'withdrawrecord,distributor_list,distributor',
					'text' => $lang['nc_distributor_withdrawrecord']
				),
				array(
					'args' => 'lists,team,distributor',
					'text' => $lang['nc_distributor_team']
				)
			)
		),
    ) ,
);
if (core\config::get('flea_isuse') == 1) {
    $arr['top'][] = array(
        'args' => 'flea',
        'text' => '闲置'
    );
    $arr['left'][] = array(
        'nav' => 'flea',
        'text' => '闲置',
        'list' => array(
            array(
                'args' => 'flea_index,flea_index,flea',
                'text' => 'SEO设置'
            ) ,
            array(
                'args' => 'flea_class,flea_class,flea',
                'text' => '分类管理'
            ) ,
            array(
                'args' => 'flea_class_index,flea_class_index,flea',
                'text' => '首页分类管理'
            ) ,
            array(
                'args' => 'flea,flea,flea',
                'text' => '闲置管理'
            ) ,
            array(
                'args' => 'flea_region,flea_region,flea',
                'text' => '地区管理'
            ) ,
            array(
                'args' => 'adv_manage,flea_index,flea',
                'text' => '闲置幻灯'
            ) ,
        )
    );
}
if (core\config::get('mobile_isuse')) {
    $arr['top'][] = array(
        'args' => 'mobile',
        'text' => $lang['nc_mobile']
    );
    $arr['left'][] = array(
        'nav' => 'mobile',
        'text' => $lang['nc_mobile'],
        'list' => array(
            array(
                'args' => 'index_edit,mb_special,mobile',
                'text' => '首页编辑'
            ) ,
            array(
                'args' => 'special_list,mb_special,mobile',
                'text' => '专题设置'
            ) ,
            array(
                'args' => 'mb_category_list,mb_category,mobile',
                'text' => $lang['nc_mobile_catepic']
            ) ,
            /*array(
                'args' => 'mb_app,mb_app,mobile',
                'text' => '下载设置'
            ) ,*/
            array(
                'args' => 'flist,mb_feedback,mobile',
                'text' => $lang['nc_mobile_feedback']
            ) ,
            array(
                'args' => 'index,mb_payment,mobile',
                'text' => '手机支付'
            )
        )
    );
}
if (core\config::get('microshop_isuse')) {
    $arr['top'][] = array(
        'args' => 'microshop',
        'text' => $lang['nc_microshop']
    );
    $arr['left'][] = array(
        'nav' => 'microshop',
        'text' => $lang['nc_microshop'],
        'list' => array(
            array(
                'args' => 'manage,microshop,microshop',
                'text' => $lang['nc_microshop_manage']
            ) ,
            array(
                'args' => 'goods_manage,microshop,microshop',
                'text' => $lang['nc_microshop_goods_manage']
            ) ,
            array(
                'args' => 'goodsclass_list,microshop,microshop',
                'text' => $lang['nc_microshop_goods_class']
            ) ,
            array(
                'args' => 'personal_manage,microshop,microshop',
                'text' => $lang['nc_microshop_personal_manage']
            ) ,
            array(
                'args' => 'personalclass_list,microshop,microshop',
                'text' => $lang['nc_microshop_personal_class']
            ) ,
            array(
                'args' => 'store_manage,microshop,microshop',
                'text' => $lang['nc_microshop_store_manage']
            ) ,
            array(
                'args' => 'comment_manage,microshop,microshop',
                'text' => $lang['nc_microshop_comment_manage']
            ) ,
            array(
                'args' => 'adv_manage,microshop,microshop',
                'text' => $lang['nc_microshop_adv_manage']
            )
        )
    );
}
if (core\config::get('cms_isuse')) {
    $arr['top'][] = array(
        'args' => 'cms',
        'text' => $lang['nc_cms']
    );
    $arr['left'][] = array(
        'nav' => 'cms',
        'text' => $lang['nc_cms'],
        'list' => array(
            array(
                'args' => 'cms_manage,cms_manage,cms',
                'text' => $lang['nc_cms_manage']
            ) ,
            array(
                'args' => 'cms_index,cms_index,cms',
                'text' => $lang['nc_cms_index_manage']
            ) ,
            array(
                'args' => 'cms_article_list,cms_article,cms',
                'text' => $lang['nc_cms_article_manage']
            ) ,
            array(
                'args' => 'cms_article_class_list,cms_article_class,cms',
                'text' => $lang['nc_cms_article_class']
            ) ,
            array(
                'args' => 'cms_picture_list,cms_picture,cms',
                'text' => $lang['nc_cms_picture_manage']
            ) ,
            array(
                'args' => 'cms_picture_class_list,cms_picture_class,cms',
                'text' => $lang['nc_cms_picture_class']
            ) ,
            array(
                'args' => 'cms_special_list,cms_special,cms',
                'text' => $lang['nc_cms_special_manage']
            ) ,
            array(
                'args' => 'cms_navigation_list,cms_navigation,cms',
                'text' => $lang['nc_cms_navigation_manage']
            ) ,
            array(
                'args' => 'cms_tag_list,cms_tag,cms',
                'text' => $lang['nc_cms_tag_manage']
            ) ,
            array(
                'args' => 'comment_manage,cms_comment,cms',
                'text' => $lang['nc_cms_comment_manage']
            )
        )
    );
}
if (core\config::get('circle_isuse')) {
    $arr['top'][] = array(
        'args' => 'circle',
        'text' => $lang['nc_circle']
    );
    $arr['left'][] = array(
        'nav' => 'circle',
        'text' => $lang['nc_circle'],
        'list' => array(
            array(
                'args' => 'index,circle_setting,circle',
                'text' => $lang['nc_circle_setting']
            ) ,
            array(
                'args' => 'index,circle_memberlevel,circle',
                'text' => $lang['nc_circle_memberlevel']
            ) ,
            array(
                'args' => 'class_list,circle_class,circle',
                'text' => $lang['nc_circle_classmanage']
            ) ,
            array(
                'args' => 'circle_list,circle_manage,circle',
                'text' => $lang['nc_circle_manage']
            ) ,
            array(
                'args' => 'theme_list,circle_theme,circle',
                'text' => $lang['nc_circle_thememanage']
            ) ,
            array(
                'args' => 'member_list,circle_member,circle',
                'text' => $lang['nc_circle_membermanage']
            ) ,
            array(
                'args' => 'inform_list,circle_inform,circle',
                'text' => $lang['nc_circle_informnamage']
            ) ,
            array(
                'args' => 'adv_manage,circle_setting,circle',
                'text' => $lang['nc_circle_advmanage']
            ) ,
            array(
                'args' => 'index,circle_cache,circle',
                'text' => $lang['nc_circle_cache']
            )
        )
    );
}
if (core\config::get('pc_isuse')) {
	$arr['top'][] = array(
        'args' => 'website',
        'text' => $lang['nc_website']
    );
	$arr['left'][] = array(
        'nav' => 'website',
        'text' => $lang['nc_website'],
        'list' => array(
            array(
                'args' => 'document,document,website',
                'text' => $lang['nc_document']
            ) ,
            array(
                'args' => 'navigation,navigation,website',
                'text' => $lang['nc_navigation']
            ) ,
            array(
                'args' => 'ap_manage,adv,website',
                'text' => $lang['nc_adv_manage']
            ) ,
            array(
                'args' => 'web_config,web_config,website',
                'text' => $lang['nc_web_index']
            ) ,
			array(
                'args' => 'index,payment,website',
                'text' => $lang['nc_pay_method']
            ) ,
            array(
                'args' => 'rec_list,rec_position,website',
                'text' => $lang['nc_admin_res_position']
            ) ,
            array(
                'args' => 'link,link,website',
                'text' => '友情连接'
            ) ,
        )
    );
}
return $arr;