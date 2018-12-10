<?php
defined('SAFE_CONST') or exit('Access Invalid!');
function require_cache($filename) {
    static $_importFiles = array();
    if (!isset($_importFiles[$filename])) {
        if (is_file($filename)) {
            require $filename;
            $_importFiles[$filename] = true;
        } else {
            $_importFiles[$filename] = false;
        }
    }
    return $_importFiles[$filename];
}
function autoload($class) {
    if (false !== strpos($class, '\\')) {
        $tmpArr = explode('\\', $class);
        $name = array_shift($tmpArr);
        if (is_dir(BASE_CORE_PATH . '/framework/' . $name)) {
            // 框架目录下面的命名空间自动定位
            $path = BASE_CORE_PATH . '/framework';
        } else {
            $path = BASE_ROOT_PATH;
        }
        $filename = $path . '/' . str_replace('\\', '/', $class) . '.php';
        if (!require_cache($filename)) {
            return false;
        }
    }else{
	    return false;
	}
}
/**
 * KV缓存 读
 *
 * @param string $key 缓存名称
 * @param boolean $callback 缓存读取失败时是否使用回调 true代表使用cache.model中预定义的缓存项 默认不使用回调
 * @param callable $callback 传递非boolean值时 通过is_callable进行判断 失败抛出异常 成功则将$key作为参数进行回调
 * @return mixed
 */
function rkcache($key, $callback = false)
{
	$cacher =  \core\cache::connect(\core\config::get('cache'));
    if (!$cacher) {
        throw_exception('Cannot fetch cache object!');
    }
    $value = $cacher->get($key);
    if ($value === false && $callback !== false) {
        if ($callback === true) {
            $callback = array(model('cache'), 'call');
        }
        if (!is_callable($callback)) {
            throw_exception('Invalid rkcache callback!');
        }
        $value = call_user_func($callback, $key);
        wkcache($key, $value);
    }
    return $value;
}
/**
 * KV缓存 写
 *
 * @param string $key 缓存名称
 * @param mixed $value 缓存数据 若设为否 则下次读取该缓存时会触发回调（如果有）
 * @param int $expire 缓存时间 单位秒 null代表不过期
 * @return boolean
 */
function wkcache($key, $value, $expire = null)
{
	$cacher =  \core\cache::connect(\core\config::get('cache'));
    if (!$cacher) {
        throw_exception('Cannot fetch cache object!');
    }
    return $cacher->set($key, $value, $expire);
}
/**
 * KV缓存 删
 *
 * @param string $key 缓存名称
 * @return boolean
 */
function dkcache($key)
{
    $cacher =  \core\cache::connect(\core\config::get('cache'));
    if (!$cacher) {
        throw_exception('Cannot fetch cache object!');
    }
    return $cacher->rm($key);
}
/**
 * 消息提示，主要适用于普通页面AJAX提交的情况
 *
 * @param string $message 消息内容
 * @param string $url 提示完后的URL去向
 * @param stting $alert_type 提示类型 error/succ/notice 分别为错误/成功/警示
 * @param string $extrajs 扩展JS
 * @param int $time 停留时间
 */
function showDialog($message = '', $url = '', $alert_type = 'error', $extrajs = '', $time = 2)
{
    if (empty($_GET['inajax'])) {
        if ($url == 'reload') {
            $url = '';
        }
		if($alert_type == 'error'){
			error($message . $extrajs, $url);
		}else{
			success($message . $extrajs, $url);
		}
    }
    $message = str_replace("'", "\\'", strip_tags($message));
    $paramjs = null;
    if ($url == 'reload') {
        $paramjs = 'window.location.reload()';
    } elseif ($url != '') {
        $paramjs = 'window.location.href =\'' . $url . '\'';
    }
    if ($paramjs) {
        $paramjs = 'function (){' . $paramjs . '}';
    } else {
        $paramjs = 'null';
    }
    $modes = array('error' => 'alert', 'succ' => 'succ', 'notice' => 'notice', 'js' => 'js');
    $cover = $alert_type == 'error' ? 1 : 0;
    $extra = 'showDialog(\'' . $message . '\', \'' . $modes[$alert_type] . '\', null, ' . ($paramjs ? $paramjs : 'null') . ', ' . $cover . ', null, null, null, null, ' . (is_numeric($time) ? $time : 'null') . ', null);';
    $extra = $extra ? '<script type="text/javascript" reload="1">' . $extra . '</script>' : '';
    if ($extrajs != '' && substr(trim($extrajs), 0, 7) != '<script') {
        $extrajs = '<script type="text/javascript" reload="1">' . $extrajs . '</script>';
    }
    $extra .= $extrajs;
    ob_end_clean();
    header("Expires: -1");
    header("Cache-Control: no-store, private, post-check=0, pre-check=0, max-age=0", FALSE);
    header("Pragma: no-cache");
    header("Content-type: text/xml; charset=" . CHARSET);
    $string = '<?xml version="1.0" encoding="' . CHARSET . '"?>' . "\r\n";
    $string .= '<root><![CDATA[' . $message . $extra . ']]></root>';
    echo $string;
    exit;
}

function error($message, $jumpUrl = '') {
   dispatchJump($message, $jumpUrl, $status = 0);
}
	
function success($message, $jumpUrl = '') {
	dispatchJump($message, $jumpUrl, $status = 1);
}
/**
 * 默认跳转操作 支持错误导向和正确跳转
 * 调用模板显示 默认为框架目录下面的msg_jump.php页面
 * @param string $message 提示信息
 * @param string $jumpUrl 页面跳转地址
 * @param Boolean $status 状态
 * @param mixed $ajax 是否为Ajax方式 当数字时指定跳转时间
 * @access protected
 * @return void
 */
function dispatchJump($message, $jumpUrl = '', $status = 1, $ajax = false) {
    if(true === $ajax || IS_AJAX) {// AJAX提交
        $data = is_array($ajax) ? $ajax : array();
        $data['info'] = $message;
        $data['status'] = $status;
        $data['url'] = $jumpUrl;
        ajaxReturn($data);
    }
    if(is_int($ajax)) $waitSecond = $ajax;
    // 提示标题
	$msgTitle = $status ? '操作成功' : '操作失败';
    if($status) { //发送成功信息
        // 成功操作后默认停留1秒
        if(!isset($waitSecond)) $waitSecond = 1;
        // 默认操作成功自动返回操作前页面
        if(empty($jumpUrl)) $jumpUrl = isset($_SERVER['HTTP_REFERER']) ? $_SERVER['HTTP_REFERER'] : '';
		require(BASE_CORE_PATH . '/framework/views/msg_jump.php');
    }else {
        //发生错误时候默认停留3秒
        if(!isset($waitSecond)) $waitSecond = 3;
        // 默认发生错误的话自动返回上页
        if(empty($jumpUrl)) $jumpUrl = 'javascript:history.back(-1)';
		require(BASE_CORE_PATH . '/framework/views/msg_jump.php');
        // 中止执行  避免出错后继续执行
    }
    exit;
}

function dispatchJump_admin($message) {
	require(BASE_CORE_PATH . '/framework/views/msg_jump_admin.php');
    exit;
}

/**
 * Ajax方式返回数据到客户端
 * @access protected
 * @param mixed $data 要返回的数据
 * @param String $type AJAX返回数据格式
 * @param int $json_option 传递给json_encode的option参数
 * @return void
 */
function ajaxReturn($data, $type = '', $json_option = 0) {
    if(empty($type)) $type = 'JSON';
    switch (strtoupper($type)){
        case 'JSON' :
            // 返回JSON数据格式到客户端 包含状态信息
            //header('Content-Type:application/json; charset=utf-8');
            header('Content-Type:text/html; charset=utf-8');
            exit(json_encode($data, $json_option));
        case 'XML'  :
            // 返回xml格式数据
            header('Content-Type:text/xml; charset=utf-8');
            exit(xml_encode($data));
        case 'JSONP':
            // 返回JSON数据格式到客户端 包含状态信息
            header('Content-Type:application/json; charset=utf-8');
            $handler = isset($_GET['callback']) ? $_GET['callback'] : 'jsonpReturn';
            exit($handler.'('.json_encode($data, $json_option).');');  
        case 'EVAL' :
            // 返回可执行的js脚本
            header('Content-Type:text/html; charset=utf-8');
            exit($data);
    }
}
/**
 * 循环创建目录
 *
 * @param string $dir 待创建的目录
 * @param  $mode 权限
 * @return boolean
 */
function mk_dir($dir, $mode = '0777')
{
    if (is_dir($dir) || mkdir($dir, $mode)) {
        return true;
    }
    if (!mk_dir(dirname($dir), $mode)) {
        return false;
    }
    return mkdir($dir, $mode);
}

/**
 * 抛出异常
 *
 * @param string $error 异常信息
 */
function throw_exception($error)
{
    if (!defined('IGNORE_EXCEPTION')) {
        throw new Exception($error);;
    } else {
        exit($error);
    }
}
/**
 * 数据库模型实例化入口
 *
 * @param string $model 模型名称
 * @return obj 对象形式的返回结果
 */
function model($model = null, $base_path = null)
{
    static $_cache = array();
    $cache_key = $model . '.' . $base_path;
    if (!is_null($model) && isset($_cache[$cache_key])) {
        return $_cache[$cache_key];
    }
    $base_path = $base_path == null ? COMMON_PATH : $base_path;
    $file_name = $base_path . '/model/' . $model . '.php';
    $class_name = '\\common\\model\\' . $model;
    if (!file_exists($file_name)) {
        return $_cache[$cache_key] = new \core\model($model);
    } else {
        require_cache($file_name);
        if (!class_exists($class_name)) {
            $error = 'Model Error:  Class ' . $class_name . ' is not exists!';
            throw_exception($error);
        } else {
            return $_cache[$cache_key] = new $class_name();
        }
    }
}
/**
 * 行为模型实例
 *
 * @param string $model 模型名称
 * @return obj 对象形式的返回结果
 */
function logic($model = null, $base_path = null)
{
    static $_cache = array();
    $cache_key = $model . '.' . $base_path;
    if (!is_null($model) && isset($_cache[$cache_key])) {
        return $_cache[$cache_key];
    }
    $base_path = $base_path == null ? COMMON_PATH : $base_path;
    $file_name = $base_path . '/logic/' . $model . '.php';
    $class_name = '\\common\\logic\\' . $model;
    if (!file_exists($file_name)) {
        $error = 'Logic Error:  File ' . $file_name . ' is not exists!';
        throw_exception($error);
    } else {
        require_cache($file_name);
        if (!class_exists($class_name)) {
            $error = 'Logic Error:  Class ' . $class_name . ' is not exists!';
            throw_exception($error);
        } else {
            return $_cache[$cache_key] = new $class_name();
        }
    }
}
/**
 * 读取目录列表
 * 不包括 . .. 文件 三部分
 *
 * @param string $path 路径
 * @return array 数组格式的返回结果
 */
function readDirList($path)
{
    if (is_dir($path)) {
        $handle = opendir($path);
        $dir_list = array();
        if ($handle) {
            while (false !== ($dir = readdir($handle))) {
                if ($dir != '.' && $dir != '..' && is_dir($path . DS . $dir)) {
                    $dir_list[] = $dir;
                }
            }
            return $dir_list;
        } else {
            return false;
        }
    } else {
        return false;
    }
}
/**
 * 获取文件列表(所有子目录文件)
 *
 * @param string $path 目录
 * @param array $file_list 存放所有子文件的数组
 * @param array $ignore_dir 需要忽略的目录或文件
 * @return array 数据格式的返回结果
 */
function readFileList($path, &$file_list, $ignore_dir = array())
{
    $path = rtrim($path, '/');
    if (is_dir($path)) {
        $handle = opendir($path);
        if ($handle) {
            while (false !== ($dir = readdir($handle))) {
                if ($dir != '.' && $dir != '..') {
                    if (!in_array($dir, $ignore_dir)) {
                        if (is_file($path . DS . $dir)) {
                            $file_list[] = $path . DS . $dir;
                        } elseif (is_dir($path . DS . $dir)) {
                            readFileList($path . DS . $dir, $file_list, $ignore_dir);
                        }
                    }
                }
            }
            closedir($handle);
            //			return $file_list;
        } else {
            return false;
        }
    } else {
        return false;
    }
}
/**
 * 删除缓存目录下的文件或子目录文件
 *
 * @param string $dir 目录名或文件名
 * @return boolean
 */
function delCacheFile($dir)
{
    //防止删除cache以外的文件
    if (strpos($dir, '..') !== false) {
        return false;
    }
    $path = BASE_DATA_PATH . DS . 'cache' . DS . $dir;
    if (is_dir($path)) {
        $file_list = array();
        readFileList($path, $file_list);
        if (!empty($file_list)) {
            foreach ($file_list as $v) {
                if (basename($v) != 'index.html') {
                    unlink($v);
                }
            }
        }
    } else {
        if (basename($path) != 'index.html') {
            unlink($path);
        }
    }
    return true;
}
/**
 * 获取目录大小
 *
 * @param string $path 目录
 * @param int $size 目录大小
 * @return int 整型类型的返回结果
 */
function getDirSize($path, $size = 0)
{
    $dir = dir($path);
    if (!empty($dir->path) && !empty($dir->handle)) {
        while ($filename = $dir->read()) {
            if ($filename != '.' && $filename != '..') {
                if (is_dir($path . DS . $filename)) {
                    $size += getDirSize($path . DS . $filename);
                } else {
                    $size += filesize($path . DS . $filename);
                }
            }
        }
    }
    return $size ? $size : 0;
}
/**
* 价格格式化
*
* @param int	$price
* @return string	$price_format
*/
function ncPriceFormat($price)
{
    $price_format = number_format($price, 2, '.', '');
    return $price_format;
}
/**
 * 字符串切割函数，一个字母算一个位置,一个字算2个位置
 *
 * @param string $string 待切割的字符串
 * @param int $length 切割长度
 * @param string $dot 尾缀
 */
function str_cut($string, $length, $dot = '')
{
    $string = str_replace(array('&nbsp;', '&amp;', '&quot;', '&#039;', '&ldquo;', '&rdquo;', '&mdash;', '&lt;', '&gt;', '&middot;', '&hellip;'), array(' ', '&', '"', "'", '“', '”', '—', '<', '>', '·', '…'), $string);
    $strlen = strlen($string);
    if ($strlen <= $length) {
        return $string;
    }
    $maxi = $length - strlen($dot);
    $strcut = '';
    if (strtolower(CHARSET) == 'utf-8') {
        $n = $tn = $noc = 0;
        while ($n < $strlen) {
            $t = ord($string[$n]);
            if ($t == 9 || $t == 10 || 32 <= $t && $t <= 126) {
                $tn = 1;
                $n++;
                $noc++;
            } elseif (194 <= $t && $t <= 223) {
                $tn = 2;
                $n += 2;
                $noc += 2;
            } elseif (224 <= $t && $t < 239) {
                $tn = 3;
                $n += 3;
                $noc += 2;
            } elseif (240 <= $t && $t <= 247) {
                $tn = 4;
                $n += 4;
                $noc += 2;
            } elseif (248 <= $t && $t <= 251) {
                $tn = 5;
                $n += 5;
                $noc += 2;
            } elseif ($t == 252 || $t == 253) {
                $tn = 6;
                $n += 6;
                $noc += 2;
            } else {
                $n++;
            }
            if ($noc >= $maxi) {
                break;
            }
        }
        if ($noc > $maxi) {
            $n -= $tn;
        }
        $strcut = substr($string, 0, $n);
    } else {
        $dotlen = strlen($dot);
        $maxi = $length - $dotlen;
        for ($i = 0; $i < $maxi; $i++) {
            $strcut .= ord($string[$i]) > 127 ? $string[$i] . $string[++$i] : $string[$i];
        }
    }
    $strcut = str_replace(array('&', '"', "'", '<', '>'), array('&amp;', '&quot;', '&#039;', '&lt;', '&gt;'), $strcut);
    return $strcut . $dot;
}
/**
 * 将字符部分加密并输出
 * @param unknown $str
 * @param unknown $start 从第几个位置开始加密(从1开始)
 * @param unknown $length 连续加密多少位
 * @return string
 */
function encryptShow($str, $start, $length)
{
    $end = $start - 1 + $length;
    $array = str_split($str);
    foreach ($array as $k => $v) {
        if ($k >= $start - 1 && $k < $end) {
            $array[$k] = '*';
        }
    }
    return implode('', $array);
}
/**
 * 取得IP
 *
 *
 * @return string 字符串类型的返回结果
 */
function getIp()
{
    if (!empty($_SERVER['HTTP_CLIENT_IP']) && $_SERVER['HTTP_CLIENT_IP'] != 'unknown') {
        $ip = $_SERVER['HTTP_CLIENT_IP'];
    } elseif (!empty($_SERVER['HTTP_X_FORWARDED_FOR']) && $_SERVER['HTTP_X_FORWARDED_FOR'] != 'unknown') {
        $ip = $_SERVER['HTTP_X_FORWARDED_FOR'];
    } else {
        $ip = $_SERVER['REMOTE_ADDR'];
    }
    return preg_match('/^\\d[\\d.]+\\d$/', $ip) ? $ip : '';
}
/**
 * 加密函数
 *
 * @param string $txt 需要加密的字符串
 * @param string $key 密钥
 * @return string 返回加密结果
 */
function encrypt($txt, $key = '')
{
    if (empty($txt)) {
        return $txt;
    }
    if (empty($key)) {
        $key = md5(MD5_KEY);
    }
    $chars = 'ABCDEFGHIJKLMNOPQRSTUVWXYZabcdefghijklmnopqrstuvwxyz0123456789-_.';
    $ikey = '-x6g6ZWm2G9g_vr0Bo.pOq3kRIxsZ6rm';
    $nh1 = rand(0, 64);
    $nh2 = rand(0, 64);
    $nh3 = rand(0, 64);
    $ch1 = $chars[$nh1];
    $ch2 = $chars[$nh2];
    $ch3 = $chars[$nh3];
    $nhnum = $nh1 + $nh2 + $nh3;
    $knum = 0;
    $i = 0;
    while (isset($key[$i])) {
        $knum += ord($key[$i++]);
    }
    $mdKey = substr(md5(md5(md5($key . $ch1) . $ch2 . $ikey) . $ch3), $nhnum % 8, $knum % 8 + 16);
    $txt = base64_encode(time() . '_' . $txt);
    $txt = str_replace(array('+', '/', '='), array('-', '_', '.'), $txt);
    $tmp = '';
    $j = 0;
    $k = 0;
    $tlen = strlen($txt);
    $klen = strlen($mdKey);
    for ($i = 0; $i < $tlen; $i++) {
        $k = $k == $klen ? 0 : $k;
        $j = ($nhnum + strpos($chars, $txt[$i]) + ord($mdKey[$k++])) % 64;
        $tmp .= $chars[$j];
    }
    $tmplen = strlen($tmp);
    $tmp = substr_replace($tmp, $ch3, $nh2 % ++$tmplen, 0);
    $tmp = substr_replace($tmp, $ch2, $nh1 % ++$tmplen, 0);
    $tmp = substr_replace($tmp, $ch1, $knum % ++$tmplen, 0);
    return $tmp;
}
/**
 * 解密函数
 *
 * @param string $txt 需要解密的字符串
 * @param string $key 密匙
 * @return string 字符串类型的返回结果
 */
function decrypt($txt, $key = '', $ttl = 0)
{
    if (empty($txt)) {
        return $txt;
    }
    if (empty($key)) {
        $key = md5(MD5_KEY);
    }
    $chars = 'ABCDEFGHIJKLMNOPQRSTUVWXYZabcdefghijklmnopqrstuvwxyz0123456789-_.';
    $ikey = '-x6g6ZWm2G9g_vr0Bo.pOq3kRIxsZ6rm';
    $knum = 0;
    $i = 0;
    $tlen = strlen($txt);
    while (isset($key[$i])) {
        $knum += ord($key[$i++]);
    }
    $ch1 = $txt[$knum % $tlen];
    $nh1 = strpos($chars, $ch1);
    $txt = substr_replace($txt, '', $knum % $tlen--, 1);
    $ch2 = $txt[$nh1 % $tlen];
    $nh2 = strpos($chars, $ch2);
    $txt = substr_replace($txt, '', $nh1 % $tlen--, 1);
    $ch3 = $txt[$nh2 % $tlen];
    $nh3 = strpos($chars, $ch3);
    $txt = substr_replace($txt, '', $nh2 % $tlen--, 1);
    $nhnum = $nh1 + $nh2 + $nh3;
    $mdKey = substr(md5(md5(md5($key . $ch1) . $ch2 . $ikey) . $ch3), $nhnum % 8, $knum % 8 + 16);
    $tmp = '';
    $j = 0;
    $k = 0;
    $tlen = strlen($txt);
    $klen = strlen($mdKey);
    for ($i = 0; $i < $tlen; $i++) {
        $k = $k == $klen ? 0 : $k;
        $j = strpos($chars, $txt[$i]) - $nhnum - ord($mdKey[$k++]);
        while ($j < 0) {
            $j += 64;
        }
        $tmp .= $chars[$j];
    }
    $tmp = str_replace(array('-', '_', '.'), array('+', '/', '='), $tmp);
    $tmp = trim(base64_decode($tmp));
    if (preg_match('/\\d{10}_/s', substr($tmp, 0, 11))) {
        if ($ttl > 0 && time() - substr($tmp, 0, 11) > $ttl) {
            $tmp = null;
        } else {
            $tmp = substr($tmp, 11);
        }
    }
    return $tmp;
}
function send_email($email, $subject, $message)
{
	require_cache(BASE_RESOURCE_PATH . DS . 'phpmailer/class.phpmailer.php');
	require_cache(BASE_RESOURCE_PATH . DS . 'phpmailer/class.smtp.php');
	$mail = new PHPMailer;

	//$mail->SMTPDebug = 3;                               // Enable verbose debug output

	$mail->isSMTP();                                      // Set mailer to use SMTP
	$mail->Host = \core\config::get('email_host');        // Specify main and backup SMTP servers  eg:smtp1.example.com;smtp2.example.com
	$mail->SMTPAuth = true;                               // Enable SMTP authentication
	$mail->Username = \core\config::get('email_id');      // SMTP username
	$mail->Password = \core\config::get('email_pass');    // SMTP password
	//$mail->SMTPSecure = 'tls';                          // Enable TLS encryption, `ssl` also accepted
	$mail->Port = \core\config::get('email_port');        // TCP port to connect to

	$mail->setFrom(\core\config::get('email_addr'), \core\config::get('site_name'));
	$mail->addAddress($email, '544731308');   // Add a recipient
	//$mail->addAddress(ellen@example.com);   // Name is optional
	//$mail->addReplyTo('info@example.com', 'Information');
	//$mail->addCC('cc@example.com');
	//$mail->addBCC('bcc@example.com');

	//$mail->addAttachment('/var/tmp/file.tar.gz');        // Add attachments
	//$mail->addAttachment('/tmp/image.jpg', 'new.jpg');   // Optional name
	$mail->isHTML(false);                                  // Set email format to HTML

	$mail->Subject = $subject;                             //Here is the subject
	$mail->Body    = $message;                             //This is the HTML message body <b>in bold!</b>
	$mail->AltBody = $message; //This is the body in plain text for non-HTML mail clients

	if(!$mail->send()) {
		return 'Email Error: ' . $mail->ErrorInfo;
	} else {
		return true;
	}
}
/**
 * 发送HTTP状态
 * @param integer $code 状态码
 * @return void
 */
function send_http_status($code) 
{
    static $_status = array(
        // Informational 1xx
        100 => 'Continue',
        101 => 'Switching Protocols',
        // Success 2xx
        200 => 'OK',
        201 => 'Created',
        202 => 'Accepted',
        203 => 'Non-Authoritative Information',
        204 => 'No Content',
        205 => 'Reset Content',
        206 => 'Partial Content',
        // Redirection 3xx
        300 => 'Multiple Choices',
        301 => 'Moved Permanently',
        302 => 'Moved Temporarily ', // 1.1
        303 => 'See Other',
        304 => 'Not Modified',
        305 => 'Use Proxy',
        // 306 is deprecated but reserved
        307 => 'Temporary Redirect',
        // Client Error 4xx
        400 => 'Bad Request',
        401 => 'Unauthorized',
        402 => 'Payment Required',
        403 => 'Forbidden',
        404 => 'Not Found',
        405 => 'Method Not Allowed',
        406 => 'Not Acceptable',
        407 => 'Proxy Authentication Required',
        408 => 'Request Timeout',
        409 => 'Conflict',
        410 => 'Gone',
        411 => 'Length Required',
        412 => 'Precondition Failed',
        413 => 'Request Entity Too Large',
        414 => 'Request-URI Too Long',
        415 => 'Unsupported Media Type',
        416 => 'Requested Range Not Satisfiable',
        417 => 'Expectation Failed',
        // Server Error 5xx
        500 => 'Internal Server Error',
        501 => 'Not Implemented',
        502 => 'Bad Gateway',
        503 => 'Service Unavailable',
        504 => 'Gateway Timeout',
        505 => 'HTTP Version Not Supported',
        509 => 'Bandwidth Limit Exceeded'
    );
    if (isset($_status[$code])) {
        header('HTTP/1.1 ' . $code . ' ' . $_status[$code]);
        // 确保FastCGI模式下正常
        header('Status:' . $code . ' ' . $_status[$code]);
    }
}
function _unserialize($data)
{
	preg_replace_callback('#s:(\d+):"(.*?)";#s',function($match){return 's:'.strlen($match[2]).':"'.$match[2].'";';},$data);
}
function is_serialized($data)
{
	$data = trim($data);
	if ('N;' == $data)
		return true;
	if (!preg_match('/^([adObis]):/', $data, $badions))
		return false;
	switch ($badions[1]) {
		case 'a' :
		case 'O' :
		case 's' :
			if (preg_match("/^{$badions[1]}:[0-9]+:.*[;}]\$/s", $data))
				return true;
			break;
		case 'b' :
		case 'i' :
		case 'd' :
			if (preg_match("/^{$badions[1]}:[0-9.E-]+;\$/", $data))
				return true;
			break;
	}
	return false;
}