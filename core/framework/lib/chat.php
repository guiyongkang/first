<?php
/**
 * chat
 *
 */
namespace lib;
use core;
defined('SAFE_CONST') or exit('Access Invalid!');
class chat {
    public static function getChatHtml($layout) {
        $web_html = '';
        if ($layout != 'layout/msg_layout.php' && $layout != 'layout/store_joinin_layout.php') {
            define('CHAT_TEMPLATES_URL',BASE_SITE_URL . DS . DIR_CHAT . '/templates/default');
            define('CHAT_RESOURCE_URL',BASE_SITE_URL . DS . DIR_CHAT . '/resource');
            $avatar = getMemberAvatar(core\session::get('avatar'));
            $nchash = getNchash();
            $formhash = core\security::getTokenValue();
			$member_id = core\session::get('member_id');
			$member_name = core\session::get('member_name');
			$store_id = core\session::get('store_id');
			$store_name = core\session::get('store_name');
            $css_url = CHAT_TEMPLATES_URL;
            $chat_url = BASE_SITE_URL . DS . DIR_CHAT;
            $node_url = NODE_SITE_URL;
            $web_html = <<<EOT
					<link href="{$css_url}/css/chat.css" rel="stylesheet" type="text/css">
					<div style="clear: both;"></div>
					<div id="web_chat_dialog" style="display: none;float:right;">
					</div>
					<a id="chat_login" href="javascript:void(0)" style="display: none;"></a>
					<script type="text/javascript">
					var APP_URL = '{$chat_url}';
					var connect_url = "{$node_url}";

					var layout = "{$layout}";
					var act_op = "{$_GET['act']}_{$_GET['op']}";
					var user = {};

					user['u_id'] = "{$member_id}";
					user['u_name'] = "{$member_name}";
					user['s_id'] = "{$store_id}";
					user['s_name'] = "{$store_name}";
					user['avatar'] = "{$avatar}";

					$("#chat_login").nc_login({
					  action:'/index.php?act=login',
					  nchash:'{$nchash}',
					  formhash:'{$formhash}'
					});
					</script>
EOT;
            if (defined('APP_ID') && APP_ID != 'shop') {
                $web_html.= '<link href="' . RESOURCE_SITE_URL . '/js/perfect-scrollbar.min.css" rel="stylesheet" type="text/css">';
                $web_html.= '<script type="text/javascript" src="' . RESOURCE_SITE_URL . '/js/perfect-scrollbar.min.js"></script>';
                $web_html.= '<script type="text/javascript" src="' . RESOURCE_SITE_URL . '/js/jquery.mousewheel.js"></script>';
            }
            $web_html.= '<script type="text/javascript" src="' . RESOURCE_SITE_URL . '/js/jquery.charCount.js" charset="utf-8"></script>';
            $web_html.= '<script type="text/javascript" src="' . RESOURCE_SITE_URL . '/js/jquery.smilies.js" charset="utf-8"></script>';
            $web_html.= '<script type="text/javascript" src="' . CHAT_RESOURCE_URL . '/js/user.js" charset="utf-8"></script>';
        }
        if ($layout == 'layout/seller_layout.php') {
			$seller_id = core\session::get('seller_id');
			$seller_name = core\session::get('seller_name');
			$seller_is_admin = core\session::get('seller_is_admin');
            $web_html.= '<script type="text/javascript" src="' . CHAT_RESOURCE_URL . '/js/store.js" charset="utf-8"></script>';
            $seller_smt_limits = '';
            if (!empty(core\session::get('seller_smt_limits')) && is_array(core\session::get('seller_smt_limits'))) {
                $seller_smt_limits = implode(',', core\session::get('seller_smt_limits'));
            }
            $web_html.= <<<EOT
					<script type="text/javascript">
					user['seller_id'] = "{$seller_id}";
					user['seller_name'] = "{$seller_name}";
					user['seller_is_admin'] = "{$seller_is_admin}";
					var smt_limits = "{$seller_smt_limits}";
					</script>
EOT;
            
        }
        return $web_html;
    }
}