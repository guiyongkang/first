<?php
defined('SAFE_CONST') or exit('Access Invalid!');
/**
 * 调用推荐位
 *
 * @param int $rec_id 推荐位ID
 * @return string 推荐位内容
 */
function rec($rec_id = null)
{
    import('function.rec_position');
    return rec_position($rec_id);
}
/**
 * 当访问的act或op不存在时调用此函数并退出脚本
 *
 * @param string $act
 * @param string $op
 * @return void
 */
function requestNotFound($act = null, $op = null)
{
    showMessage('您访问的页面不存在！', APP_URL, 'exception', 'error', 1, 3000);
    exit;
}

/**
 * 输出信息
 *
 * @param string $msg 输出信息
 * @param string/array $url 跳转地址 当$url为数组时，结构为 array('msg'=>'跳转连接文字','url'=>'跳转连接');
 * @param string $show_type 输出格式 默认为html
 * @param string $msg_type 信息类型 succ 为成功，error为失败/错误
 * @param string $is_show  是否显示跳转链接，默认是为1，显示
 * @param int $time 跳转时间，默认为2秒
 * @return string 字符串类型的返回结果
 */
/*function showMessage($msg, $url = '', $show_type = 'html', $msg_type = 'succ', $is_show = 1, $time = 2000)
{
    \core\language::read('core_lang_index');
    $lang = \core\language::getLangContent();
    $url = $url != '' ? $url : getReferer();
    $msg_type = in_array($msg_type, array('succ', 'error')) ? $msg_type : 'error';

    switch ($show_type) {
        case 'json':
            $return = '{';
            $return .= '"msg":"' . $msg . '",';
            $return .= '"url":"' . $url . '"';
            $return .= '}';
            echo $return;
            break;
        case 'exception':
            echo '<!DOCTYPE html>';
            echo '<html>';
            echo '<head>';
            echo '<meta http-equiv="Content-Type" content="text/html; charset=' . CHARSET . '" />';
            echo '<title></title>';
            echo '<style type="text/css">';
            echo 'body { font-family: "Verdana";padding: 0; margin: 0;}';
            echo 'h2 { font-size: 12px; line-height: 30px; border-bottom: 1px dashed #CCC; padding-bottom: 8px;width:800px; margin: 20px 0 0 150px;}';
            echo 'dl { float: left; display: inline; clear: both; padding: 0; margin: 10px 20px 20px 150px;}';
            echo 'dt { font-size: 14px; font-weight: bold; line-height: 40px; color: #333; padding: 0; margin: 0; border-width: 0px;}';
            echo 'dd { font-size: 12px; line-height: 40px; color: #333; padding: 0px; margin:0;}';
            echo '</style>';
            echo '</head>';
            echo '<body>';
            echo '<h2>' . $lang['error_info'] . '</h2>';
            echo '<dl>';
            echo '<dd>' . $msg . '</dd>';
            echo '<dt><p /></dt>';
            echo '<dd>' . isset($lang['error_notice_operate']) ? $lang['error_notice_operate'] : '' . '</dd>';
            echo '<dd><p /><p /><p /><p /></dd>';
            echo '<dd><p /><p /><p /><p /></dd>';
            echo '</dl>';
            echo '</body>';
            echo '</html>';
            exit;
            break;
        case 'javascript':
            echo "<script>";
            echo "alert('" . $msg . "');";
            echo "location.href='" . $url . "'";
            echo "</script>";
            exit;
            break;
        case 'tenpay':
            echo "<html><head>";
            echo "<meta name=\"TENCENT_ONLINE_PAYMENT\" content=\"China TENCENT\">";
            echo "<script language=\"javascript\">";
            echo "window.location.href='" . $url . "';";
            echo "</script>";
            echo "</head><body></body></html>";
            exit;
            break;
        default:

            //不显示右侧工具条

            \core\tpl::output('hidden_nctoolbar', 1);
            if (is_array($url)) {
                foreach ($url as $k => $v) {
                    $url[$k]['url'] = $v['url'] ? $v['url'] : getReferer();
                }
            }

            //读取信息布局的语言包

            \core\language::read('msg');

             // html输出形式
             // 指定为指定项目目录下的error模板文件

            \core\tpl::setDir('');
            \core\tpl::output('html_title', \core\language::get('nc_html_title'));
            \core\tpl::output('msg', $msg);
            \core\tpl::output('url', $url);
            \core\tpl::output('msg_type', $msg_type);
            \core\tpl::output('is_show', $is_show);
            \core\tpl::showpage('msg', 'msg_layout', $time);
    }
    exit;
}*/


/**
 * 编辑器内容
 *
 * @param int $id 编辑器id名称，与name同名
 * @param string $value 编辑器内容
 * @param string $width 宽 带px
 * @param string $height 高 带px
 * @param string $style 样式内容
 * @param string $upload_state 上传状态，默认是开启
 */
function showEditor($id, $value = '', $width = '700px', $height = '300px', $style = 'visibility:hidden;', $upload_state = "true", $media_open = false, $type = 'all')
{
    //是否开启多媒体
    $media = '';
    if ($media_open) {
        $media = ", 'flash', 'media'";
    }
    switch ($type) {
        case 'basic':
            $items = "['source', '|', 'fullscreen', 'undo', 'redo', 'cut', 'copy', 'paste', '|', 'about']";
            break;
        case 'simple':
            $items = "['source', '|', 'fullscreen', 'undo', 'redo', 'cut', 'copy', 'paste', '|', 'fontname', 'fontsize', 'forecolor', 'hilitecolor', 'bold', 'italic', 'underline', 'removeformat', 'justifyleft', 'justifycenter', 'justifyright', 'insertorderedlist', 'insertunorderedlist', '|', 'emoticons', 'link', '|', 'about']";
            break;
        default:
            $items = "['source', '|', 'fullscreen', 'undo', 'redo', 'print', 'cut', 'copy', 'paste', 'plainpaste', 'wordpaste', '|', 'justifyleft', 'justifycenter', 'justifyright', 'justifyfull', 'insertorderedlist', 'insertunorderedlist', 'indent', 'outdent', 'subscript', 'superscript', '|', 'selectall', 'clearhtml','quickformat','|', 'formatblock', 'fontname', 'fontsize', '|', 'forecolor', 'hilitecolor', 'bold', 'italic', 'underline', 'strikethrough', 'lineheight', 'removeformat', '|'" . $media . ", 'table', 'hr', 'emoticons', 'link', 'unlink', '|', 'about']";
            break;
    }
    //图片、Flash、视频、文件的本地上传都可开启。默认只有图片，要启用其它的需要修改resource\kindeditor\php下的upload_json.php的相关参数
    echo '<textarea id="' . $id . '" name="' . $id . '" style="width:' . $width . ';height:' . $height . ';' . $style . '">' . $value . '</textarea>';
    echo '
	<script src="' . RESOURCE_SITE_URL . '/kindeditor/kindeditor-min.js" charset="utf-8"></script>
	<script src="' . RESOURCE_SITE_URL . '/kindeditor/lang/zh_CN.js" charset="utf-8"></script>
	<script>
		var KE;
		KindEditor.ready(function(K) {
			KE = K.create("textarea[name=\'' . $id . '\']", {
							items : ' . $items . ',
							cssPath : "' . RESOURCE_SITE_URL . '/kindeditor/themes/default/default.css",
							allowImageUpload : ' . $upload_state . ',
							allowFlashUpload : false,
							allowMediaUpload : false,
							allowFileManager : false,
							syncType:"form",
							afterCreate : function() {
								var self = this;
								self.sync();
							},
							afterChange : function() {
								var self = this;
								self.sync();
							},
							afterBlur : function() {
								var self = this;
								self.sync();
							}
			});
			KE.appendHtml = function(id,val) {
				this.html(this.html() + val);
				if (this.isCreated) {
					var cmd = this.cmd;
					cmd.range.selectNodeContents(cmd.doc.body).collapse(false);
					cmd.select();
				}
				return this;
			}
		});
	</script>
	';
    return true;
}
/**
 * 二级域名解析
 * @return int 店铺id
 */
function subdomain()
{
    $store_id = 0;
    /**
     * 获得系统配置,二级域名功能是否开启
     */
    if (\core\config::get('enabled_subdomain') == '1') {
        //开启了二级域名
        $line = explode(SUBDOMAIN_SUFFIX, $_SERVER['HTTP_HOST']);
        $line = trim($line[0], '.');
        if (empty($line) || strtolower($line) == 'www') {
            return 0;
        }
        $model_store = Model('store');
        $store_info = $model_store->getStoreInfo(array('store_domain' => $line));
        //二级域名存在
        if ($store_info['store_id'] > 0) {
            $store_id = $store_info['store_id'];
            $_GET['store_id'] = $store_info['store_id'];
        }
    }
    return $store_id;
}
/**
 * 取得商品默认大小图片
 *
 * @param string $key	图片大小 small tiny
 * @return string
 */
function defaultGoodsImage($key)
{
    $file = str_ireplace('.', '_' . $key . '.', \core\config::get('default_goods_image'));
    return ATTACH_COMMON . DS . $file;
}
/**
 * 取得用户头像图片
 *
 * @param string $member_avatar
 * @return string
 */
function getMemberAvatar($member_avatar)
{
    if (empty($member_avatar)) {
        return UPLOAD_SITE_URL . DS . ATTACH_COMMON . DS . \core\config::get('default_user_portrait');
    } else {
        if (file_exists(BASE_UPLOAD_PATH . DS . ATTACH_AVATAR . DS . $member_avatar)) {
            return UPLOAD_SITE_URL . DS . ATTACH_AVATAR . DS . $member_avatar;
        } else {
            return UPLOAD_SITE_URL . DS . ATTACH_COMMON . DS . \core\config::get('default_user_portrait');
        }
    }
}
/**
 * 成员头像
 * @param string $member_id
 * @return string
 */
function getMemberAvatarForID($id)
{
    if (file_exists(BASE_UPLOAD_PATH . '/' . ATTACH_AVATAR . '/avatar_' . $id . '.jpg')) {
        return UPLOAD_SITE_URL . '/' . ATTACH_AVATAR . '/avatar_' . $id . '.jpg';
    } else {
        return UPLOAD_SITE_URL . '/' . ATTACH_COMMON . DS . \core\config::get('default_user_portrait');
    }
}
/**
 * 成员头像 SAFE_CONST-1
 * @param string $member_id
 * @return string
 */
function getMemberAvatarHttps($member_avatar)
{
    if (empty($member_avatar)) {
        return UPLOAD_SITE_URL_HTTPS . DS . ATTACH_COMMON . DS . c('default_user_portrait');
    } else {
        if (file_exists(BASE_UPLOAD_PATH . DS . ATTACH_AVATAR . DS . $member_avatar)) {
            return UPLOAD_SITE_URL_HTTPS . DS . ATTACH_AVATAR . DS . $member_avatar;
        } else {
            return UPLOAD_SITE_URL_HTTPS . DS . ATTACH_COMMON . DS . c('default_user_portrait');
        }
    }
}
/**
 * 取得店铺标志
 *
 * @param string $img 图片名
 * @param string $type 查询类型 store_logo/store_avatar
 * @return string
 */
function getStoreLogo($img, $type = 'store_avatar')
{
    if ($type == 'store_avatar') {
        if (empty($img)) {
            return UPLOAD_SITE_URL . DS . ATTACH_COMMON . DS . \core\config::get('default_store_avatar');
        } else {
            return UPLOAD_SITE_URL . DS . ATTACH_STORE . DS . $img;
        }
    } elseif ($type == 'store_logo') {
        if (empty($img)) {
            return UPLOAD_SITE_URL . DS . ATTACH_COMMON . DS . \core\config::get('default_store_logo');
        } else {
            return UPLOAD_SITE_URL . DS . ATTACH_STORE . DS . $img;
        }
    }
}
/**
 * 获取文章URL
 */
function getCMSArticleUrl($article_id)
{
    if (URL_MODEL) {
        // 开启伪静态
        return APP_URL . DS . 'article-' . $article_id . '.html';
    } else {
        return APP_URL . DS . 'index.php?act=article&op=article_detail&article_id=' . $article_id;
    }
}
/**
 * 获取画报URL
 */
function getCMSPictureUrl($picture_id)
{
    if (URL_MODEL) {
        // 开启伪静态
        return APP_URL . DS . 'picture-' . $picture_id . '.html';
    } else {
        return APP_URL . DS . 'index.php?act=picture&op=picture_detail&picture_id=' . $picture_id;
    }
}
/**
 * 获取文章图片URL
 */
function getCMSArticleImageUrl($image_path, $image_name, $type = 'list')
{
    if (empty($image_name)) {
        return UPLOAD_SITE_URL . DS . ATTACH_CMS . DS . 'no_cover.png';
    } else {
        $image_array = unserialize($image_name);
        if (!empty($image_array['name'])) {
            $image_name = $image_array['name'];
        }
        if (!empty($image_array['path'])) {
            $image_path = $image_array['path'];
        }
        $ext_array = array('list', 'max');
        $file_path = ATTACH_CMS . DS . 'article' . DS . $image_path . DS . str_ireplace('.', '_' . $type . '.', $image_name);
        if (file_exists(BASE_PATH . DS . $file_name)) {
            $image_name = UPLOAD_SITE_URL . DS . $file_path;
        } else {
            $image_name = UPLOAD_SITE_URL . DS . ATTACH_CMS . DS . 'no_cover.png';
        }
        return $image_name;
    }
}
/**
 * 获取文章图片URL
 */
function getCMSImageName($image_name_string)
{
    $image_array = unserialize($image_name_string);
    if (!empty($image_array['name'])) {
        $image_name = $image_array['name'];
    } else {
        $image_name = $image_name_string;
    }
    return $image_name;
}
/**
 * 获取CMS专题图片
 */
function getCMSSpecialImageUrl($image_name = '')
{
    return UPLOAD_SITE_URL . DS . ATTACH_CMS . DS . 'special' . DS . $image_name;
}
/**
 * 获取CMS专题路径
 */
function getCMSSpecialImagePath($image_name = '')
{
    return BASE_UPLOAD_PATH . DS . ATTACH_CMS . DS . 'special' . DS . $image_name;
}
/**
 * 获取CMS首页图片
 */
function getCMSIndexImageUrl($image_name = '')
{
    return UPLOAD_SITE_URL . DS . ATTACH_CMS . DS . 'index' . DS . $image_name;
}
/**
 * 获取CMS首页图片路径
 */
function getCMSIndexImagePath($image_name = '')
{
    return BASE_UPLOAD_PATH . DS . ATTACH_CMS . DS . 'index' . DS . $image_name;
}
/**
 * 获取CMS专题Url
 */
function getCMSSpecialUrl($special_id)
{
    return APP_URL . DS . 'index.php?act=special&op=special_detail&special_id=' . $special_id;
}
/**
 * 获取商城专题Url
 */
function getShopSpecialUrl($special_id)
{
    return APP_URL . DS . 'index.php?act=special&op=special_detail&special_id=' . $special_id;
}
/**
 * 获取CMS专题静态文件
 */
function getCMSSpecialHtml($special_id)
{
    $special_file = BASE_UPLOAD_PATH . DS . ATTACH_CMS . DS . 'special_html' . DS . md5('special' . intval($special_id)) . '.html';
    if (is_file($special_file)) {
        return $special_file;
    } else {
        return false;
    }
}
/**
 * 获取微商城个人秀图片地址
 */
function getMicroshopPersonalImageUrl($personal_info, $type = '')
{
    $ext_array = array('list', 'tiny');
    $personal_image_array = array();
    $personal_image_list = explode(',', $personal_info['commend_image']);
    if (!empty($personal_image_list)) {
        foreach ($personal_image_list as $value) {
            if (!empty($type) && in_array($type, $ext_array)) {
                $file_name = str_replace('.', '_' . $type . '.', $value);
            } else {
                $file_name = $value;
            }
            $file_path = $personal_info['commend_member_id'] . DS . $file_name;
            if (is_file(BASE_UPLOAD_PATH . DS . ATTACH_MICROSHOP . DS . $file_path)) {
                $personal_image_array[] = UPLOAD_SITE_URL . DS . ATTACH_MICROSHOP . DS . $file_path;
            } else {
                $personal_image_array[] = getMicroshopDefaultImage();
            }
        }
    } else {
        $personal_image_array[] = getMicroshopDefaultImage();
    }
    return $personal_image_array;
}
function getMicroshopDefaultImage()
{
    return UPLOAD_SITE_URL . '/' . defaultGoodsImage('240');
}
/**
 * 获取开店申请图片
 */
function getStoreJoininImageUrl($image_name = '')
{
    return UPLOAD_SITE_URL . DS . ATTACH_STORE_JOININ . DS . $image_name;
}
/**
 * 获取开店装修图片地址
 */
function getStoreDecorationImageUrl($image_name = '', $store_id = null)
{
    if (empty($store_id)) {
        $image_name_array = explode('_', $image_name);
        $store_id = $image_name_array[0];
    }
    $image_path = DS . ATTACH_STORE_DECORATION . DS . $store_id . DS . $image_name;
    if (is_file(BASE_UPLOAD_PATH . $image_path)) {
        return UPLOAD_SITE_URL . $image_path;
    } else {
        return '';
    }
}
/**
 * 获取运单图片地址
 */
function getWaybillImageUrl($image_name = '')
{
    $image_path = DS . ATTACH_WAYBILL . DS . $image_name;
    if (is_file(BASE_UPLOAD_PATH . $image_path)) {
        return UPLOAD_SITE_URL . $image_path;
    } else {
        return UPLOAD_SITE_URL . '/' . defaultGoodsImage('240');
    }
}
/**
 * 获取手机专题图片地址
 */
function getMbSpecialImageUrl($image_name = '')
{
    $name_array = explode('_', $image_name);
    if (count($name_array) == 2) {
        $image_path = DS . ATTACH_MOBILE . DS . 'special' . DS . $name_array[0] . DS . $image_name;
    } else {
        $image_path = DS . ATTACH_MOBILE . DS . 'special' . DS . $image_name;
    }
    if (is_file(BASE_UPLOAD_PATH . $image_path)) {
        return UPLOAD_SITE_URL . $image_path;
    } else {
        return UPLOAD_SITE_URL . '/' . defaultGoodsImage('240');
    }
}
/**
 * sns表情标示符替换为html
 */
function parsesmiles($message)
{
    $smilescache_file = BASE_DATA_PATH . DS . 'smilies' . DS . 'smilies.php';
    if (file_exists($smilescache_file)) {
        include $smilescache_file;
        if (strtoupper(CHARSET) == 'GBK') {
            $smilies_array = \core\language::getGBK($smilies_array);
        }
        if (!empty($smilies_array) && is_array($smilies_array)) {
            $imagesurl = RESOURCE_SITE_URL . DS . 'js' . DS . 'smilies' . DS . 'images' . DS;
            $replace_arr = array();
            foreach ($smilies_array['replacearray'] as $key => $smiley) {
                $replace_arr[$key] = '<img src="' . $imagesurl . $smiley['imagename'] . '" title="' . $smiley['desc'] . '" border="0" alt="' . $imagesurl . $smiley['desc'] . '" />';
            }
            $message = preg_replace($smilies_array['searcharray'], $replace_arr, $message);
        }
    }
    return $message;
}
/**
 * 输出validate的验证信息
 *
 * @param array/string $error
 */
function showValidateError($error)
{
    if (!empty($_GET['inajax'])) {
        foreach (explode('<br/>', $error) as $v) {
            if (trim($v != '')) {
                showDialog($v, '', 'error', '', 3);
            }
        }
    } else {
        showDialog($error, '', 'error', '', 3);
    }
}
/**
 * 延时加载分页功能，判断是否有更多连接和limitstart值和经过验证修改的$delay_eachnum值
 * @param int $delay_eachnum 延时分页每页显示的条数
 * @param int $delay_page 延时分页当前页数
 * @param int $count 总记录数
 * @param bool $ispage 是否在分页模式中实现延时分页(前台显示的两种不同效果)
 * @param int $page_nowpage 分页当前页数
 * @param int $page_eachnum 分页每页显示条数
 * @param int $page_limitstart 分页初始limit值
 * @return array array('hasmore'=>'是否显示更多连接','limitstart'=>'加载的limit开始值','delay_eachnum'=>'经过验证修改的$delay_eachnum值');
 */
function lazypage($delay_eachnum, $delay_page, $count, $ispage = false, $page_nowpage = 1, $page_eachnum = 1, $page_limitstart = 1)
{
    //是否有多余
    $hasmore = true;
    $limitstart = 0;
    if ($ispage == true) {
        if ($delay_eachnum < $page_eachnum) {
            //当延时加载每页条数小于分页的每页条数时候实现延时加载，否则按照普通分页程序流程处理
            $page_totlepage = ceil($count / $page_eachnum);
            //计算limit的开始值
            $limitstart = $page_limitstart + ($delay_page - 1) * $delay_eachnum;
            if ($page_totlepage > $page_nowpage) {
                //当前不为最后一页
                if ($delay_page >= $page_eachnum / $delay_eachnum) {
                    $hasmore = false;
                }
                //判断如果分页的每页条数与延时加载每页的条数不能整除的处理
                if ($hasmore == false && $page_eachnum % $delay_eachnum > 0) {
                    $delay_eachnum = $page_eachnum % $delay_eachnum;
                }
            } else {
                //当前最后一页
                $showcount = ($page_totlepage - 1) * $page_eachnum + $delay_eachnum * $delay_page;
                //已经显示的记录总数
                if ($count <= $showcount) {
                    $hasmore = false;
                }
            }
        } else {
            $hasmore = false;
        }
    } else {
        if ($count <= $delay_page * $delay_eachnum) {
            $hasmore = false;
        }
        //计算limit的开始值
        $limitstart = ($delay_page - 1) * $delay_eachnum;
    }
    return array('hasmore' => $hasmore, 'limitstart' => $limitstart, 'delay_eachnum' => $delay_eachnum);
}
/**
 * 加载广告
 *
 * @param  $ap_id 广告位ID
 * @param $type 广告返回类型 html,js
 */
function loadadv($ap_id = null, $type = 'html')
{
    if (!is_numeric($ap_id)) {
        return false;
    }
    if (!function_exists('advshow')) {
        import('function.adv');
    }
    return advshow($ap_id, $type);
}
/**
 * 格式化ubb标签
 *
 * @param string $theme_content/$reply_content 话题内容/回复内容
 * @return string
 */
function ubb($ubb)
{
    $ubb = str_replace(array('[B]', '[/B]', '[I]', '[/I]', '[U]', '[/U]', '[IMG]', '[/IMG]', '[/FONT]', '[/FONT-SIZE]', '[/FONT-COLOR]'), array('<b>', '</b>', '<i>', '</i>', '<u>', '</u>', '<img class="pic" src="', '"/>', '</span>', '</span>', '</span>'), preg_replace(array("/\\[URL=(.*)\\](.*)\\[\\/URL\\]/iU", "/\\[FONT=([A-Za-z ]*)\\]/iU", "/\\[FONT-SIZE=([0-9]*)\\]/iU", "/\\[FONT-COLOR=([A-Za-z0-9]*)\\]/iU", "/\\[SMILIER=([A-Za-z_]*)\\/\\]/iU", "/\\[FLASH\\](.*)\\[\\/FLASH\\]/iU", "/\\n/i"), array("<a href=\"\$1\" target=\"_blank\">\$2</a>", "<span style=\"font-family:\$1\">", "<span style=\"font-size:\$1px\">", "<span style=\"color:#\$1\">", "<img src=\"" . APP_URL . '/templates/' . TPL_CIRCLE_NAME . "/images/smilier/\$1.png\">", "<embed src=\"\$1\" type=\"application/x-shockwave-flash\" allowscriptaccess=\"always\" allowfullscreen=\"true\" wmode=\"opaque\" width=\"480\" height=\"400\"></embed>", "<br />"), $ubb));
    return $ubb;
}
/**
 * 去掉ubb标签
 *
 * @param string $theme_content/$reply_content 话题内容/回复内容
 * @return string
 */
function removeUBBTag($ubb)
{
    $ubb = str_replace(array('[B]', '[/B]', '[I]', '[/I]', '[U]', '[/U]', '[/FONT]', '[/FONT-SIZE]', '[/FONT-COLOR]'), array('', '', '', '', '', '', '', '', ''), preg_replace(array("/\\[URL=(.*)\\](.*)\\[\\/URL\\]/iU", "/\\[FONT=([A-Za-z ]*)\\]/iU", "/\\[FONT-SIZE=([0-9]*)\\]/iU", "/\\[FONT-COLOR=([A-Za-z0-9]*)\\]/iU", "/\\[SMILIER=([A-Za-z_]*)\\/\\]/iU", "/\\[IMG\\](.*)\\[\\/IMG\\]/iU", "/\\[FLASH\\](.*)\\[\\/FLASH\\]/iU", "<img class='pi' src=\"\$1\"/>"), array("\$2", "", "", "", "", "", "", ""), $ubb));
    return $ubb;
}
/**
 * 话题图片绝对路径
 *
 * @param $param string 文件名称
 * @return string
 */
function themeImagePath($param)
{
    return BASE_UPLOAD_PATH . '/' . ATTACH_CIRCLE . '/theme/' . $param;
}
/**
 * 话题图片url
 *
 * @param $param string
 * @return string
 */
function themeImageUrl($param)
{
    return UPLOAD_SITE_URL . '/' . ATTACH_CIRCLE . '/theme/' . $param;
}
/**
 * 圈子logo
 *
 * @param $param string 圈子id
 * @return string
 */
function circleLogo($id)
{
    if (file_exists(BASE_UPLOAD_PATH . '/' . ATTACH_CIRCLE . '/group/' . $id . '.jpg')) {
        return UPLOAD_SITE_URL . '/' . ATTACH_CIRCLE . '/group/' . $id . '.jpg';
    } else {
        return UPLOAD_SITE_URL . '/' . ATTACH_CIRCLE . '/default_group_logo.gif';
    }
}
/**
 * sns 来自
 * @param $param string $trace_from
 * @return string
 */
function snsShareFrom($sign)
{
    switch ($sign) {
        case '1':
        case '2':
            return lang('sns_from') . '<a target="_black" href="' . APP_URL . '">' . lang('sns_shop') . '</a>';
            break;
        case '3':
            return lang('sns_from') . '<a target="_black" href="' . APP_URL . '">' . lang('nc_modules_microshop') . '</a>';
            break;
        case '4':
            return lang('sns_from') . '<a target="_black" href="' . APP_URL . '">CMS</a>';
            break;
        case '5':
            return lang('sns_from') . '<a target="_black" href="' . APP_URL . '">' . lang('nc_circle') . '</a>';
            break;
    }
}
/**
 * 输出聊天信息
 *
 * @return string
 */
function getChat($layout)
{
    if (!\core\config::get('node_chat') || !file_exists(BASE_CORE_PATH . '/framework/lib/chat.php')) {
        return '';
    }
    return \lib\chat::getChatHtml($layout);
}
/**
 * 拼接动态URL，参数需要小写
 *
 * 调用示例
 *
 * 若指向网站首页，可以传空:
 * url() => 表示act和op均为index，返回当前站点网址
 *
 * url('search,'index','array('cate_id'=>2)); 实际指向 index.php?act=search&op=index&cate_id=2
 * 传递数组参数时，若act（或op）值为index,则可以省略
 * 上面示例等同于
 * url('search','',array('act'=>'search','cate_id'=>2));
 *
 * @param string $act control文件名
 * @param string $op op方法名
 * @param array $args URL其它参数
 * @param boolean $model 默认取当前系统配置
 * @param string $site_url 生成链接的网址，默认取当前网址
 * @return string
 */
function url($act = '', $op = '', $args = array(), $model = false, $site_url = '')
{
    //伪静态文件扩展名
    $ext = '.html';
    //入口文件名
    $file = 'index.php';
    //    $site_url = empty($site_url) ? APP_URL : $site_url;
    $act = trim($act);
    $op = trim($op);
    $args = !is_array($args) ? array() : $args;
    //定义变量存放返回url
    $url_string = '';
    if (empty($act) && empty($op) && empty($args)) {
        return $site_url;
    }
    $act = !empty($act) ? $act : 'index';
    $op = !empty($op) ? $op : 'index';
    $model = $model ? URL_MODEL : $model;
    if ($model) {
        //伪静态模式
        $url_perfix = "{$act}-{$op}";
        if (!empty($args)) {
            $url_perfix .= '-';
        }
        $url_string = $url_perfix . http_build_query($args, '', '-') . $ext;
        $url_string = str_replace('=', '-', $url_string);
    } else {
        //默认路由模式
        $url_perfix = "act={$act}&op={$op}";
        if (!empty($args)) {
            $url_perfix .= '&';
        }
        $url_string = $file . '?' . $url_perfix . http_build_query($args);
    }
    //将商品、店铺、分类、品牌、文章自动生成的伪静态URL使用短URL代替
    $reg_match_from = array('/^category-index\\.html$/', '/^channel-index-id-(\\d+)\\.html$/', '/^goods-index-goods_id-(\\d+)\\.html$/', '/^show_store-index-store_id-(\\d+)\\.html$/', '/^show_store-goods_all-store_id-(\\d+)-stc_id-(\\d+)-key-([0-5])-order-([0-2])-curpage-(\\d+)\\.html$/', '/^article-show-article_id-(\\d+)\\.html$/', '/^article-article-ac_id-(\\d+)\\.html$/', '/^document-index-code-([a-z_]+)\\.html$/', '/^search-index-cate_id-(\\d+)-b_id-([0-9_]+)-a_id-([0-9_]+)-key-([0-3])-order-([0-2])-type-([0-1])-gift-([0-1])-area_id-(\\d+)-curpage-(\\d+)\\.html$/', '/^brand-list-brand-(\\d+)-key-([0-3])-order-([0-2])-type-([0-1])-gift-([0-1])-area_id-(\\d+)-curpage-(\\d+)\\.html$/', '/^brand-index\\.html$/', '/^promotion-index\\.html$/', '/^promotion-index-gc_id-(\\d+)\\.html$/', '/^show_groupbuy-index\\.html$/', '/^show_groupbuy-groupbuy_detail-group_id-(\\d+)\\.html$/', '/^show_groupbuy-groupbuy_list-class-(\\d+)-s_class-(\\d+)-groupbuy_price-(\\d+)-groupbuy_order_key-(\\d+)-groupbuy_order-(\\d+)-curpage-(\\d+)\\.html$/', '/^show_groupbuy-groupbuy_soon-class-(\\d+)-s_class-(\\d+)-groupbuy_price-(\\d+)-groupbuy_order_key-(\\d+)-groupbuy_order-(\\d+)-curpage-(\\d+)\\.html$/', '/^show_groupbuy-groupbuy_history-class-(\\d+)-s_class-(\\d+)-groupbuy_price-(\\d+)-groupbuy_order_key-(\\d+)-groupbuy_order-(\\d+)-curpage-(\\d+)\\.html$/', '/^show_groupbuy-vr_groupbuy_list-vr_class-(\\d+)-vr_s_class-(\\d+)-vr_area-(\\d+)-vr_mall-(\\d+)-groupbuy_price-(\\d+)-groupbuy_order_key-(\\d+)-groupbuy_order-(\\d+)-curpage-(\\d+)\\.html$/', '/^show_groupbuy-vr_groupbuy_soon-vr_class-(\\d+)-vr_s_class-(\\d+)-vr_area-(\\d+)-vr_mall-(\\d+)-groupbuy_price-(\\d+)-groupbuy_order_key-(\\d+)-groupbuy_order-(\\d+)-curpage-(\\d+)\\.html$/', '/^show_groupbuy-vr_groupbuy_history-vr_class-(\\d+)-vr_s_class-(\\d+)-vr_area-(\\d+)-vr_mall-(\\d+)-groupbuy_price-(\\d+)-groupbuy_order_key-(\\d+)-groupbuy_order-(\\d+)-curpage-(\\d+)\\.html$/', '/^pointshop-index.html$/', '/^pointprod-plist.html$/', '/^pointprod-pinfo-id-(\\d+).html$/', '/^pointvoucher-index.html$/', '/^pointgrade-index.html$/', '/^pointgrade-exppointlog-curpage-(\\d+).html$/', '/^goods-comments_list-goods_id-(\\d+)-type-([0-3])-curpage-(\\d+).html$/', '/^special-special_list.html$/', '/^special-special_detail-special_id-(\\d+).html$/');
    $reg_match_to = array('category.html', 'channel-\\1.html', 'item-\\1.html', 'shop-\\1.html', 'shop_view-\\1-\\2-\\3-\\4-\\5.html', 'article-\\1.html', 'article_cate-\\1.html', 'document-\\1.html', 'cate-\\1-\\2-\\3-\\4-\\5-\\6-\\7-\\8-\\9.html', 'brand-\\1-\\2-\\3-\\4-\\5-\\6-\\7.html', 'brand.html', 'promotion.html', 'promotion-\\1.html', 'groupbuy.html', 'groupbuy_detail-\\1.html', 'groupbuy_list-\\1-\\2-\\3-\\4-\\5-\\6.html', 'groupbuy_soon-\\1-\\2-\\3-\\4-\\5-\\6.html', 'groupbuy_history-\\1-\\2-\\3-\\4-\\5-\\6.html', 'vr_groupbuy_list-\\1-\\2-\\3-\\4-\\5-\\6-\\7-\\8.html', 'vr_groupbuy_soon-\\1-\\2-\\3-\\4-\\5-\\6-\\7-\\8.html', 'vr_groupbuy_history-\\1-\\2-\\3-\\4-\\5-\\6-\\7-\\8.html', 'integral.html', 'integral_list.html', 'integral_item-\\1.html', 'voucher.html', 'grade.html', 'explog-\\1.html', 'comments-\\1-\\2-\\3.html', 'special.html', 'special-\\1.html');
    $url_string = preg_replace($reg_match_from, $reg_match_to, $url_string);
    return rtrim($site_url, '/') . '/' . $url_string;
}
/**
 * 商城会员中心使用的URL链接函数，强制使用动态传参数模式
 *
 * @param string $act control文件名
 * @param string $op op方法名
 * @param array $args URL其它参数
 * @param string $store_domian 店铺二级域名
 * @return string
 */
function urlShop($act = '', $op = '', $args = array(), $store_domain = '')
{
    // 如何使自营店则返回javascript:;
    //    if ($act == 'show_store' && $op != 'goods_all') {
    //        static $ownShopIds = null;
    //        if ($ownShopIds === null) {
    //            $ownShopIds = Model('store')->getOwnShopIds();
    //        }
    //        if (isset($args['store_id']) && in_array($args['store_id'], $ownShopIds)) {
    //            return 'javascript:;';
    //        }
    //    }
    // 开启店铺二级域名
    if (intval(\core\config::get('enabled_subdomain')) == 1 && !empty($store_domain)) {
        return 'http://' . $store_domain . '.' . SUBDOMAIN_SUFFIX . '/';
    }
    // 默认标志为不开启伪静态
    $rewrite_flag = false;
    // 如果平台开启伪静态开关，并且为伪静态模块，修改标志为开启伪静态
    $rewrite_item = array('category:index', 'channel:index', 'goods:index', 'goods:comments_list', 'search:index', 'show_store:index', 'show_store:goods_all', 'article:show', 'article:article', 'document:index', 'brand:list', 'brand:index', 'promotion:index', 'show_groupbuy:index', 'show_groupbuy:groupbuy_detail', 'show_groupbuy:groupbuy_list', 'show_groupbuy:groupbuy_soon', 'show_groupbuy:groupbuy_history', 'show_groupbuy:vr_groupbuy_list', 'show_groupbuy:vr_groupbuy_soon', 'show_groupbuy:vr_groupbuy_history', 'pointshop:index', 'pointvoucher:index', 'pointprod:pinfo', 'pointprod:plist', 'pointgrade:index', 'pointgrade:exppointlog', 'store_snshome:index', 'special:special_list', 'special:special_detail');
    if (URL_MODEL && in_array($act . ':' . $op, $rewrite_item)) {
        $rewrite_flag = true;
        $tpl_args = array();
        // url参数临时数组
        switch ($act . ':' . $op) {
            case 'search:index':
                if (!empty($args['keyword'])) {
                    $rewrite_flag = false;
                    break;
                }
                $tpl_args['cate_id'] = empty($args['cate_id']) ? 0 : $args['cate_id'];
                $tpl_args['b_id'] = empty($args['b_id']) || intval($args['b_id']) == 0 ? 0 : $args['b_id'];
                $tpl_args['a_id'] = empty($args['a_id']) || intval($args['a_id']) == 0 ? 0 : $args['a_id'];
                $tpl_args['key'] = empty($args['key']) ? 0 : $args['key'];
                $tpl_args['order'] = empty($args['order']) ? 0 : $args['order'];
                $tpl_args['type'] = empty($args['type']) ? 0 : $args['type'];
                $tpl_args['gift'] = empty($args['gift']) ? 0 : $args['gift'];
                $tpl_args['area_id'] = empty($args['area_id']) ? 0 : $args['area_id'];
                $tpl_args['curpage'] = empty($args['curpage']) ? 0 : $args['curpage'];
                $args = $tpl_args;
                break;
            case 'show_store:goods_all':
                if (isset($args['inkeyword'])) {
                    $rewrite_flag = false;
                    break;
                }
                $tpl_args['store_id'] = empty($args['store_id']) ? 0 : $args['store_id'];
                $tpl_args['stc_id'] = empty($args['stc_id']) ? 0 : $args['stc_id'];
                $tpl_args['key'] = empty($args['key']) ? 0 : $args['key'];
                $tpl_args['order'] = empty($args['order']) ? 0 : $args['order'];
                $tpl_args['curpage'] = empty($args['curpage']) ? 0 : $args['curpage'];
                $args = $tpl_args;
                break;
            case 'brand:list':
                $tpl_args['brand'] = empty($args['brand']) ? 0 : $args['brand'];
                $tpl_args['key'] = empty($args['key']) ? 0 : $args['key'];
                $tpl_args['order'] = empty($args['order']) ? 0 : $args['order'];
                $tpl_args['type'] = empty($args['type']) ? 0 : $args['type'];
                $tpl_args['gift'] = empty($args['gift']) ? 0 : $args['gift'];
                $tpl_args['area_id'] = empty($args['area_id']) ? 0 : $args['area_id'];
                $tpl_args['curpage'] = empty($args['curpage']) ? 0 : $args['curpage'];
                $args = $tpl_args;
                break;
            case 'show_groupbuy:index':
            case 'show_groupbuy:groupbuy_detail':
                break;
            case 'show_groupbuy:groupbuy_list':
            case 'show_groupbuy:groupbuy_soon':
            case 'show_groupbuy:groupbuy_history':
                $tpl_args['class'] = empty($args['class']) ? 0 : $args['class'];
                $tpl_args['s_class'] = empty($args['s_class']) ? 0 : $args['s_class'];
                $tpl_args['groupbuy_price'] = empty($args['groupbuy_price']) ? 0 : $args['groupbuy_price'];
                $tpl_args['groupbuy_order_key'] = empty($args['groupbuy_order_key']) ? 0 : $args['groupbuy_order_key'];
                $tpl_args['groupbuy_order'] = empty($args['groupbuy_order']) ? 0 : $args['groupbuy_order'];
                $tpl_args['curpage'] = empty($args['curpage']) ? 0 : $args['curpage'];
                $args = $tpl_args;
                break;
            case 'show_groupbuy:vr_groupbuy_list':
            case 'show_groupbuy:vr_groupbuy_soon':
            case 'show_groupbuy:vr_groupbuy_history':
                $tpl_args['vr_class'] = empty($args['vr_class']) ? 0 : $args['vr_class'];
                $tpl_args['vr_s_class'] = empty($args['vr_s_class']) ? 0 : $args['vr_s_class'];
                $tpl_args['vr_area'] = empty($args['vr_area']) ? 0 : $args['vr_area'];
                $tpl_args['vr_mall'] = empty($args['vr_mall']) ? 0 : $args['vr_mall'];
                $tpl_args['groupbuy_price'] = empty($args['groupbuy_price']) ? 0 : $args['groupbuy_price'];
                $tpl_args['groupbuy_order_key'] = empty($args['groupbuy_order_key']) ? 0 : $args['groupbuy_order_key'];
                $tpl_args['groupbuy_order'] = empty($args['groupbuy_order']) ? 0 : $args['groupbuy_order'];
                $tpl_args['curpage'] = empty($args['curpage']) ? 0 : $args['curpage'];
                $args = $tpl_args;
                break;
            case 'goods:comments_list':
                $tpl_args['goods_id'] = empty($args['goods_id']) ? 0 : $args['goods_id'];
                $tpl_args['type'] = empty($args['type']) ? 0 : $args['type'];
                $tpl_args['curpage'] = empty($args['curpage']) ? 0 : $args['curpage'];
                $args = $tpl_args;
                break;
            case 'pointgrade:exppointlog':
                $tpl_args['curpage'] = empty($args['curpage']) ? 0 : $args['curpage'];
                $args = $tpl_args;
                break;
            case 'promotion:index':
                $args = empty($args['gc_id']) ? NULL : $args;
                break;
            default:
                break;
        }
    }
    return url($act, $op, $args, $rewrite_flag, BASE_SITE_URL . DS . DIR_PC);
}
/**
 * 商城后台使用的URL链接函数，强制使用动态传参数模式
 *
 * @param string $act control文件名
 * @param string $op op方法名
 * @param array $args URL其它参数
 * @return string
 */
function urlAdmin($act = '', $op = '', $args = array())
{
    return url($act, $op, $args, false, BASE_SITE_URL . DS . DIR_ADMIN);
}
function urlBiz($act = '', $op = '', $args = array())
{
    return url($act, $op, $args, false, BASE_SITE_URL . DS . DIR_BIZ);
}
/**
 * CMS使用的URL链接函数，强制使用动态传参数模式
 *
 * @param string $act control文件名
 * @param string $op op方法名
 * @param array $args URL其它参数
 * @return string
 */
function urlCMS($act = '', $op = '', $args = array())
{
    return url($act, $op, $args, false, BASE_SITE_URL . DS . DIR_CMS);
}
/**
 * 圈子使用的URL链接函数，强制使用动态传参数模式
 *
 * @param string $act control文件名
 * @param string $op op方法名
 * @param array $args URL其它参数
 * @return string
 */
function urlCircle($act = '', $op = '', $args = array())
{
    return url($act, $op, $args, false, BASE_SITE_URL . DS . DIR_CIRCLE);
}
/**
 * 微商城使用的URL链接函数，强制使用动态传参数模式
 *
 * @param string $act control文件名
 * @param string $op op方法名
 * @param array $args URL其它参数
 * @return string
 */
function urlMicroshop($act = '', $op = '', $args = array())
{
    return url($act, $op, $args, false, BASE_SITE_URL . DS . DIR_MICROSHOP);
}
/**
 * 验证是否为平台店铺
 *
 * @return boolean
 */
function checkPlatformStore()
{
    return \core\session::get('is_own_shop');
}
/**
 * 验证是否为平台店铺 并且绑定了全部商品类目
 *
 * @return boolean
 */
function checkPlatformStoreBindingAllGoodsClass()
{
    return checkPlatformStore() && \core\session::get('bind_all_gc');
}
/**
 * 获得店铺状态样式名称
 * @param $param array $store_info
 * @return string
 */
function getStoreStateClassName($store_info)
{
    $result = 'open';
    if (intval($store_info['store_state']) === 1) {
        $store_end_time = intval($store_info['store_end_time']);
        if ($store_end_time > 0) {
            if ($store_end_time < TIMESTAMP) {
                $result = 'expired';
            } elseif ($store_end_time - 864000 < TIMESTAMP) {
                //距离到期10天
                $result = 'expire';
            }
        }
    } else {
        $result = 'close';
    }
    return $result;
}


/**
 * 产生验证码
 *
 * @param string $nchash 哈希数
 * @return string
 */
function makeSeccode($nchash)
{
    $seccode = random(6, 1);
    $seccodeunits = '';
    $s = sprintf('%04s', base_convert($seccode, 10, 23));
    $seccodeunits = 'ABCEFGHJKMPRTVXY2346789';
    if ($seccodeunits) {
        $seccode = '';
        for ($i = 0; $i < 4; $i++) {
            $unit = ord($s[$i]);
            $seccode .= $unit >= 0x30 && $unit <= 0x39 ? $seccodeunits[$unit - 0x30] : $seccodeunits[$unit - 0x57];
        }
    }
    setNcCookie('seccode' . $nchash, encrypt(strtoupper($seccode) . "\t" . time() . "\t" . $nchash, MD5_KEY), 3600);
    return $seccode;
}
function getFlexigridArray($in_array, $fields_array, $data, $format_array)
{
    $out_array = $in_array;
    if ($out_array["operation"]) {
        $out_array["operation"] = "--";
    }
    if ($fields_array && is_array($fields_array)) {
        foreach ($fields_array as $key => $value) {
            $k = "";
            if (is_int($key)) {
                $k = $value;
            } else {
                $k = $key;
            }
            if (is_array($data) && array_key_exists($k, $data)) {
                $out_array[$k] = $data[$k];
                if ($format_array && in_array($k, $format_array)) {
                    $out_array[$k] = ncpriceformatb($data[$k]);
                }
            } else {
                $out_array[$k] = "--";
            }
        }
    }
    return $out_array;
}
function ncPriceFormatb($price)
{
    return number_format($price, 2, '.', '');
}
/**
 * 验证验证码
 *
 * @param string $nchash 哈希数
 * @param string $value 待验证值
 * @return boolean
 */
function checkSeccode($nchash, $value)
{
	$decrypt_hash = explode("\t", decrypt(cookie('seccode' . $nchash), MD5_KEY));
	if(count($decrypt_hash) == 3){
		list($checkvalue, $checktime, $checkidhash) = $decrypt_hash;
	}else{
		$checkvalue = $checktime = $checkidhash = '';
	}
    $return = $checkvalue == strtoupper($value) && $checkidhash == $nchash;
    if (!$return) {
        setNcCookie('seccode' . $nchash, '', -3600);
    }
    return $return;
}
/**
 * 设置cookie
 *
 * @param string $name cookie 的名称
 * @param string $value cookie 的值
 * @param int $expire cookie 有效周期
 * @param string $path cookie 的服务器路径 默认为 /
 * @param string $domain cookie 的域名
 * @param string $secure 是否通过安全的 HTTPS 连接来传输 cookie,默认为false
 * @param string $pre cookie名称前缀(设置cookie QQ wap 登录 )
 */
function setNcCookie($name, $value, $expire = '3600', $path = '', $domain = '', $secure = false, $pre = true)
{
    if (empty($path)) {
        $path = '/';
    }
    if (empty($domain)) {
        $domain = defined('SUBDOMAIN_SUFFIX') ? SUBDOMAIN_SUFFIX : '';
    }
	if($pre){
		$name = defined('COOKIE_PRE') ? COOKIE_PRE . $name : strtoupper(substr(md5(MD5_KEY), 0, 4)) . '_' . $name;
	}
    $expire = !empty($expire) ? intval($expire) : (defined('SESSION_EXPIRE') ? intval(SESSION_EXPIRE) : 3600);
    $result = setcookie($name, $value, time() + $expire, $path, $domain, $secure);
    $_COOKIE[$name] = $value;
}
/**
 * 取得COOKIE的值
 *
 * @param string $name
 * @return unknown
 */
function cookie($name = '')
{
    $name = defined('COOKIE_PRE') ? COOKIE_PRE . $name : strtoupper(substr(md5(MD5_KEY), 0, 4)) . '_' . $name;
    return isset($_COOKIE[$name]) ? $_COOKIE[$name] : '';
}

/**
 * 不显示信息直接跳转
 *
 * @param string $url
 */
function redirect($url = '')
{
    if (empty($url)) {
        if (!empty($_REQUEST['ref_url'])) {
            $url = $_REQUEST['ref_url'];
        } else {
            $url = getReferer();
        }
    }
    header('Location: ' . $url);
    exit;
}
/**
 * 取上一步来源地址
 *
 * @param
 * @return string 字符串类型的返回结果
 */
function getReferer()
{
    return empty($_SERVER['HTTP_REFERER']) ? (isset($_GET['ref_url']) ? $_GET['ref_url'] : (isset($_POST['ref_url']) ? $_POST['ref_url'] : '')) : $_SERVER['HTTP_REFERER'];
}
/**
 * 取验证码hash值
 *
 * @param
 * @return string 字符串类型的返回结果
 */
function getNchash($act = '', $op = '')
{
    $act = $act ? $act : $_GET['act'];
    $op = $op ? $op : $_GET['op'];
    if (\core\config::get('captcha_status_login')) {
        return substr(md5(APP_URL . $act . $op), 0, 8);
    } else {
        return '';
    }
}
/**
 * 转换特殊字符
 *
 * @param string $string 要转换的字符串
 * @return string 字符串类型的返回结果
 */
function replaceSpecialChar($string)
{
    $str = str_replace("\r\n", "", $string);
    $str = str_replace("\t", "    ", $string);
    $str = str_replace("\n", "", $string);
    return $string;
}


/**
* 价格格式化
*
* @param int	$price
* @return string	$price_format
*/
function ncPriceFormatForList($price)
{
    if ($price >= 10000) {
        return number_format(floor($price / 100) / 100, 2, '.', '') . lang('ten_thousand');
    } else {
        return lang('currency') . $price;
    }
}

/**
 * 通知邮件/通知消息 内容转换函数
 *
 * @param string $message 内容模板
 * @param array $param 内容参数数组
 * @return string 通知内容
 */
function ncReplaceText($message, $param)
{
    if (!is_array($param)) {
        return false;
    }
    foreach ($param as $k => $v) {
        $message = str_replace('{$' . $k . '}', $v, $message);
    }
    return $message;
}

/**
 * unicode转为utf8
 * @param string $str 待转的字符串
 * @return string
 */
function unicodeToUtf8($str, $order = "little")
{
    $utf8string = "";
    $n = strlen($str);
    for ($i = 0; $i < $n; $i++) {
        if ($order == "little") {
            $val = str_pad(dechex(ord($str[$i + 1])), 2, 0, STR_PAD_LEFT) . str_pad(dechex(ord($str[$i])), 2, 0, STR_PAD_LEFT);
        } else {
            $val = str_pad(dechex(ord($str[$i])), 2, 0, STR_PAD_LEFT) . str_pad(dechex(ord($str[$i + 1])), 2, 0, STR_PAD_LEFT);
        }
        $val = intval($val, 16);
        // 由于上次的.连接，导致$val变为字符串，这里得转回来。
        $i++;
        // 两个字节表示一个unicode字符。
        $c = "";
        if ($val < 0x7f) {
            // 0000-007F
            $c .= chr($val);
        } elseif ($val < 0x800) {
            // 0080-07F0
            $c .= chr(0xc0 | $val / 64);
            $c .= chr(0x80 | $val % 64);
        } else {
            // 0800-FFFF
            $c .= chr(0xe0 | $val / 64 / 64);
            $c .= chr(0x80 | $val / 64 % 64);
            $c .= chr(0x80 | $val % 64);
        }
        $utf8string .= $c;
    }
    /* 去除bom标记 才能使内置的iconv函数正确转换 */
    if (ord(substr($utf8string, 0, 1)) == 0xef && ord(substr($utf8string, 1, 2)) == 0xbb && ord(substr($utf8string, 2, 1)) == 0xbf) {
        $utf8string = substr($utf8string, 3);
    }
    return $utf8string;
}
/*
 * 重写$_SERVER['REQUREST_URI']
 */
function request_uri()
{
    if (isset($_SERVER['REQUEST_URI'])) {
        $uri = $_SERVER['REQUEST_URI'];
    } else {
        if (isset($_SERVER['argv'])) {
            $uri = $_SERVER['PHP_SELF'] . '?' . $_SERVER['argv'][0];
        } else {
            $uri = $_SERVER['PHP_SELF'] . '?' . $_SERVER['QUERY_STRING'];
        }
    }
    return $uri;
}
/*
 * 自定义memory_get_usage()
 *
 * @return 内存使用额度，如果该方法无效，返回0
 */
if (!function_exists('memory_get_usage')) {
    function memory_get_usage()
    {
        //目前程序不兼容5以下的版本
        return 0;
    }
}
// 记录和统计时间（微秒）
function addUpTime($start, $end = '', $dec = 3)
{
    static $_info = array();
    if (!empty($end)) {
        // 统计时间
        if (!isset($_info[$end])) {
            $_info[$end] = microtime(TRUE);
        }
        return number_format($_info[$end] - (isset($_info[$start]) ? $_info[$start] : 0), $dec);
    } else {
        // 记录时间
        $_info[$start] = microtime(TRUE);
    }
}

/**
 * 取得随机数
 *
 * @param int $length 生成随机数的长度
 * @param int $numeric 是否只产生数字随机数 1是0否
 * @return string
 */
function random($length, $numeric = 0)
{
    $seed = base_convert(md5(microtime() . $_SERVER['DOCUMENT_ROOT']), 16, $numeric ? 10 : 35);
    $seed = $numeric ? str_replace('0', '', $seed) . '012340567890' : $seed . 'zZ' . strtoupper($seed);
    $hash = '';
    $max = strlen($seed) - 1;
    for ($i = 0; $i < $length; $i++) {
        $hash .= $seed[mt_rand(0, $max)];
    }
    return $hash;
}
/**
 * 返回模板文件所在完整目录
 *
 * @param str $tplpath
 * @return string
 */
function template($tplpath)
{
    if (strpos($tplpath, ':') !== false) {
        $tpltmp = explode(':', $tplpath);
        return BASE_DATA_PATH . '/' . $tpltmp[0] . '/tpl/' . $tpltmp[1] . '.php';
    } else {
        return BASE_PATH . '/templates/' . TPL_NAME . '/' . $tplpath . '.php';
    }
}
/**
 * 检测FORM是否提交
 * @param  $check_token 是否验证token
 * @param  $check_captcha 是否验证验证码
 * @param  $return_type 'alert','num'
 * @return boolean
 */
function chksubmit($check_token = false, $check_captcha = false, $return_type = 'alert')
{
    $submit = isset($_POST['form_submit']) ? $_POST['form_submit'] : (isset($_GET['form_submit']) ? $_GET['form_submit'] : '');
    if ($submit != 'ok') {
        return false;
    }
    if ($check_token && !\core\security::checkToken()) {
        if ($return_type == 'alert') {
            showDialog('Token error!');
        } else {
            return -11;
        }
    }
    if ($check_captcha) {
        if (!checkSeccode($_POST['nchash'], $_POST['captcha'])) {
            setNcCookie('seccode' . $_POST['nchash'], '', -3600);
            if ($return_type == 'alert') {
                showDialog('验证码错误!');
            } else {
                return -12;
            }
        }
        setNcCookie('seccode' . $_POST['nchash'], '', -3600);
    }
    return true;
}



/**
 * 输出错误信息
 *
 * @param string $error 错误信息
 */
function halt($error)
{
    throw_exception($error);
}
/**
 * 去除代码中的空白和注释
 *
 * @param string $content 待压缩的内容
 * @return string
 */
function compress_code($content)
{
    $stripStr = '';
    //分析php源码
    $tokens = token_get_all($content);
    $last_space = false;
    for ($i = 0, $j = count($tokens); $i < $j; $i++) {
        if (is_string($tokens[$i])) {
            $last_space = false;
            $stripStr .= $tokens[$i];
        } else {
            switch ($tokens[$i][0]) {
                case T_COMMENT:
                    //过滤各种PHP注释
                //过滤各种PHP注释
                case T_DOC_COMMENT:
                    break;
                case T_WHITESPACE:
                    //过滤空格
                    if (!$last_space) {
                        $stripStr .= ' ';
                        $last_space = true;
                    }
                    break;
                default:
                    $last_space = false;
                    $stripStr .= $tokens[$i][1];
            }
        }
    }
    return $stripStr;
}
/**
 * 取得对象实例
 *
 * @param object $class
 * @param string $method
 * @param array $args
 * @return object
 */
function get_obj_instance($class, $method = '', $args = array())
{
    static $_cache = array();
    $key = $class . $method . (empty($args) ? null : md5(serialize($args)));
    if (isset($_cache[$key])) {
        return $_cache[$key];
    } else {
        if (class_exists($class)) {
            $obj = new $class();
            if (method_exists($obj, $method)) {
                if (empty($args)) {
                    $_cache[$key] = $obj->{$method}();
                } else {
                    $_cache[$key] = call_user_func_array(array(&$obj, $method), $args);
                }
            } else {
                $_cache[$key] = $obj;
            }
            return $_cache[$key];
        } else {
            throw_exception('Class ' . $class . ' isn\'t exists!');
        }
    }
}
/**
 * 返回以原数组某个值为下标的新数据
 *
 * @param array $array
 * @param string $key
 * @param int $type 1一维数组2二维数组
 * @return array
 */
function array_under_reset($array, $key, $type = 1)
{
    if (is_array($array)) {
        $tmp = array();
        foreach ($array as $v) {
            if ($type === 1) {
                $tmp[$v[$key]] = $v;
            } elseif ($type === 2) {
                $tmp[$v[$key]][] = $v;
            }
        }
        return $tmp;
    } else {
        return $array;
    }
}


/**
 * 快速调用语言包
 *
 * @param string $key
 * @return string
 */
function lang($key = '')
{
    if (class_exists('\core\language')) {
        if (strpos($key, ',') !== false) {
            $tmp = explode(',', $key);
            $str = \core\language::get($tmp[0]) . \core\language::get($tmp[1]);
            return isset($tmp[2]) ? $str . \core\language::get($tmp[2]) : $str;
        } else {
            return \core\language::get($key);
        }
    } else {
        return null;
    }
}
/**
 * 加载完成业务方法的文件
 *
 * @param string $filename
 * @param string $file_ext
 */
function loadfunc($filename, $file_ext = '.php')
{
    if (preg_match('/^[\\w\\d\\/_.]+$/i', $filename . $file_ext)) {
        $file = realpath(BASE_PATH . '/framework/function/' . $filename . $file_ext);
    } else {
        $file = false;
    }
    if (!$file) {
        exit($filename . $file_ext . ' isn\'t exists!');
    } else {
        require $file;
    }
}
/**
 * 实例化类
 *
 * @param string $model_name 模型名称
 * @return obj 对象形式的返回结果
 */
function nc_class($classname = null)
{
    static $_cache = array();
    if (!is_null($classname) && isset($_cache[$classname])) {
        return $_cache[$classname];
    }
    $file_name = BASE_PATH . '/framework/libraries/' . $classname . '.class.php';
    $newname = $classname . 'Class';
    if (file_exists($file_name)) {
        require_once $file_name;
        if (class_exists($newname)) {
            return $_cache[$classname] = new $newname();
        }
    }
    throw_exception('Class Error:  Class ' . $classname . ' is not exists!');
}
/**
 * 封装分页操作到函数，方便调用
 *
 * @param string $cmd 命令类型
 * @param mixed $arg 参数
 * @return mixed
 */
function pagecmd($cmd = '', $arg = '')
{
    static $page;
    if (empty($page)) {
        $page = new \lib\page();
    }
    switch (strtolower($cmd)) {
        case 'seteachnum':
            $page->setEachNum($arg);
            break;
        case 'settotalnum':
            $page->setTotalNum($arg);
            break;
        case 'setstyle':
            $page->setStyle($arg);
            break;
        case 'show':
            return $page->show($arg);
            break;
        case 'obj':
            return $page;
            break;
        case 'gettotalnum':
            return $page->getTotalNum();
            break;
        case 'gettotalpage':
            return $page->getTotalPage();
            break;
        default:
            break;
    }
}

/**
 * 规范数据返回函数
 * @param unknown $state
 * @param unknown $msg
 * @param unknown $data
 * @return multitype:unknown
 */
function callback($state = true, $msg = '', $data = array())
{
    return array('state' => $state, 'msg' => $msg, 'data' => $data);
}
/**
 * 读取缓存信息
 *
 * @param string $key 要取得缓存键
 * @param string $prefix 键值前缀
 * @param string $fields 所需要的字段
 * @return array/bool
 */
function rcache($key = null, $prefix = '', $fields = '*'){
    return array();
}

/**
 * 写入缓存
 *
 * @param string $key 缓存键值
 * @param array $data 缓存数据
 * @param string $prefix 键值前缀
 * @param int $period 缓存周期  单位分，0为永久缓存
 * @return bool 返回值
 */
function wcache($key = null, $data = array(), $prefix, $period = 0){
    return;
}

/**
 * 删除缓存
 * @param string $key 缓存键值
 * @param string $prefix 键值前缀
 * @return boolean
 */
function dcache($key = null, $prefix = ''){
    return true;
}

/**
 * 获得二维码图片
 * @param string $member_id
 * @return string
 */
function getMemberQrcodeImgForID($id,$type)
{
    if (file_exists(BASE_UPLOAD_PATH . '/' . ATTACH_QRCODE . '/'.$type.'_qrcode_' . $id . '.png')) {
        return UPLOAD_SITE_URL . '/' . ATTACH_QRCODE . '/'.$type.'_qrcode_' . $id . '.png';
    }elseif(file_exists(BASE_UPLOAD_PATH . '/' . ATTACH_QRCODE . '/'.$type.'_qrcode_' . $id . '.jpg')) {
		return UPLOAD_SITE_URL . '/' . ATTACH_QRCODE . '/'.$type.'_qrcode_' . $id . '.jpg';
	}else{
		return '';
	}
}

/**
 * 获得推广图片
 * @param string $member_id
 * @return string
 */
function getMemberPosterImgForID($id,$type)
{
    if (file_exists(BASE_UPLOAD_PATH . '/' . ATTACH_POSTER . '/'.$type.'_poster_' . $id . '.jpg')) {
        return UPLOAD_SITE_URL . '/' . ATTACH_POSTER . '/'.$type.'_poster_' . $id . '.jpg';
    }else {
		return '';
	}
}
function email_is_open()
{
	$email_host = \core\config::get('email_host');
	$email_id = \core\config::get('email_id');
	$email_pass = \core\config::get('email_pass');
	$email_port = \core\config::get('email_port');
	$email_addr = \core\config::get('email_addr');
	if($email_host && $email_id && $email_pass && $email_port && $email_addr){
		return true;
	}
	return false;
}
function mobile_is_open()
{
	$mobile_host_type = \core\config::get('mobile_host_type');
	$mobile_host = \core\config::get('mobile_host');
	$mobile_username = \core\config::get('mobile_username');
	if($mobile_host_type == 1){
		$mobile_key = true;
	}
	if($mobile_host_type == 2){
		$mobile_key = \core\config::get('mobile_key');
	}
	$mobile_signature = \core\config::get('mobile_signature');
	if($mobile_host_type && $mobile_host && $mobile_username && $mobile_key && $mobile_signature){
		return true;
	}
	return false;
}