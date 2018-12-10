<?php
namespace core;
defined('SAFE_CONST') or exit('Access Invalid!');
final class base {
    const CPURL = 'yycp.siruikangsheng.com';
    /**
     * init
     */
    public static function init() {
		// 设定错误和异常处理
        register_shutdown_function('core\base::fatalError');
        set_error_handler('core\base::appError');
        set_exception_handler('core\base::appException');
        $setting_config = self::parse_conf();
		
		//var_dump($setting_config);exit;
        if (function_exists('date_default_timezone_set')) {
            if (is_numeric($setting_config['time_zone'])) {
                date_default_timezone_set('Asia/Shanghai');
            } else {
                date_default_timezone_set($setting_config['time_zone']);
            }
        }
        //output to the template
        tpl::output('setting_config', $setting_config);
        //read language
        language::read('core_lang_index');
		session::start();
		tpl::output('session', session::get());
    }
    /**
     * run
     */
    public static function run() {
        self::cp();
        self::init();
        self::control();
    }
    /**
     * get setting
     */
    private static function parse_conf() {
        $config = config::get();
        if (!empty($config['db']['slave']) && is_array($config['db']['slave'])) {
            $dbslave = $config['db']['slave'];
            $sid = array_rand($dbslave);
            $config['db']['slave'] = $dbslave[$sid];
        } else {
            $config['db']['slave'] = $config['db'][1];
        }
        $config['db']['master'] = $config['db'][1];
        //$setting_config = $config;
		config::set('db.master', $config['db']['master']);
		config::set('db.slave', $config['db']['slave']);
		$setting = ($setting = rkcache('setting')) ? $setting : rkcache('setting', true);
		//all right
		$config['about_us'] = '欢迎使用本程序，祝君生意兴隆。';
		$config['footer_right'] = '';
        $setting_config = array_merge_recursive($setting, $config);
		config::set($setting_config);
		return $setting_config;
    }
    /**
     * 控制器调度
     *
     */
    private static function control() {
        //二级域名
        if (config::get('enabled_subdomain') == '1' && $_GET['act'] == 'index' && $_GET['op'] == 'index') {
            $store_id = subdomain();
            if ($store_id > 0) $_GET['act'] = 'show_store';
        }
		if (!preg_match('/^[A-Za-z](\/|\w)*$/', $_GET['act'])) { // 安全检测
            $controller = false;
        } else {
            //创建控制器实例 使用命名空间
            $class = APP_ID . '\\control';
            $array = explode('/', $_GET['act']);
            foreach ($array as $name) {
                $class.= '\\' . $name;
            }
            if (class_exists($class)) {
                $controller = new $class();
            } else {
                $controller = false;
            }
        }
		if(!$controller){
			send_http_status('404');
			exit('404 Bad Request');
		}
        $action = $_GET['op'] . 'Op';
        try {
            //执行当前操作
            $method = new \ReflectionMethod($controller, $action);
            if ($method->isPublic() && !$method->isStatic()) {
                $class = new \ReflectionClass($controller);
                // 前置操作
                if ($class->hasMethod('_before_' . $action)) {
                    $before = $class->getMethod('_before_' . $action);
                    if ($before->isPublic()) {
                        $before->invoke($controller);
                    }
                }
                $method->invoke($controller);
                // 后置操作
                if ($class->hasMethod('_after_' . $action)) {
                    $after = $class->getMethod('_after_' . $action);
                    if ($after->isPublic()) {
                        $after->invoke($controller);
                    }
                }
            } else {
                // 操作方法不是Public 抛出异常
                throw new \ReflectionException();
            }
        }
        catch(\ReflectionException $e) {
            // 方法调用发生异常后 引导到__call方法处理
            $method = new \ReflectionMethod($controller, '__call');
            $method->invokeArgs($controller, array(
                $action,
                ''
            ));
        }
    }
    /**
     * 合法性验证
     *
     */
    private static function cp() {
       /*  if (self::CPURL == '') return;
        if ($_SERVER['HTTP_HOST'] == 'localhost') return;
        if ($_SERVER['HTTP_HOST'] == '127.0.0.1') return;
        if ($_SERVER['HTTP_HOST'] == 'www.18iq.cn') return;
        if ($_SERVER['HTTP_HOST'] == '18iq.cn') return;
        if (strpos(self::CPURL, '||') !== false) {
            $a = explode('||', self::CPURL);
            foreach ($a as $v) {
                $d = strtolower(stristr($_SERVER['HTTP_HOST'], $v));
                if ($d == strtolower($v)) {
                    return;
                } else {
                    continue;
                }
            }
            header('location: http://www.jiahuiw.com');
            exit();
        } else {
            $d = strtolower(stristr($_SERVER['HTTP_HOST'], self::CPURL));
            if ($d != strtolower(self::CPURL)) {
                header('location: http://www.jiahuiw.com');
                exit();
            }
        } */
		return true;
    }
	/**
     * 自定义异常处理
     * @access public
     * @param mixed $e 异常对象
     */
    public static function appException($e) {
        $error = array();
        $error['message'] = $e->getMessage();
        $trace = $e->getTrace();
        if ('E' == $trace[0]['function']) {
            $error['file'] = $trace[0]['file'];
            $error['line'] = $trace[0]['line'];
        } else {
            $error['file'] = $e->getFile();
            $error['line'] = $e->getLine();
        }
        $error['trace'] = $e->getTraceAsString();
        // 发送404信息
        header('HTTP/1.1 404 Not Found');
        header('Status:404 Not Found');
        self::halt($error);
    }
    /**
     * 自定义错误处理
     * @access public
     * @param int $errno 错误类型
     * @param string $errstr 错误信息
     * @param string $errfile 错误文件
     * @param int $errline 错误行数
     * @return void
     */
    public static function appError($errno, $errstr, $errfile, $errline) {
        switch ($errno) {
            case E_ERROR:
            case E_PARSE:
            case E_CORE_ERROR:
            case E_COMPILE_ERROR:
            case E_USER_ERROR:
                ob_end_clean();
                $errorStr = $errstr . ' ' . $errfile . ' 第 ' . $errline . '行.';
                self::halt($errorStr);
                break;
            default:
                $errorStr = '[' . $errno . ']' . $errstr . ' ' . $errfile . ' 第 ' . $errline . ' 行.';
                self::halt($errorStr);
                break;
        }
    }
    // 致命错误捕获
    public static function fatalError() {
        if ($e = error_get_last()) {
            switch ($e['type']) {
                case E_ERROR:
                case E_PARSE:
                case E_CORE_ERROR:
                case E_COMPILE_ERROR:
                case E_USER_ERROR:
                    ob_end_clean();
                    self::halt($e);
                    break;
            }
        }
    }
    /**
     * 错误输出
     * @param mixed $error 错误
     * @return void
     */
    public static function halt($error) {
        $e = array();
        if (config::get('debug')) {
            //调试模式下输出错误信息
            if (!is_array($error)) {
                $trace = debug_backtrace();
                $e['message'] = $error;
                $e['file'] = $trace[0]['file'];
                $e['line'] = $trace[0]['line'];
                ob_start();
                debug_print_backtrace();
                $e['trace'] = ob_get_clean();
            } else {
                $e = $error;
            }
        } else {
            $e['message'] = is_array($error) ? $error['message'] : $error;
        }
		//require_cache(BASE_CORE_PATH . '/framework/core/logging.php');
		logging::write($e['message']);
        // 包含异常页面模板
        $exceptionFile = BASE_CORE_PATH . '/framework/views/exception.php';
        include $exceptionFile;
        exit;
    }
}