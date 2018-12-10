<?php
namespace core;

defined('SAFE_CONST') or exit('Access Invalid!');
class logging
{
    protected static $config = array('log_time_format' => ' c ', 'log_file_size' => 2097152, 'log_path' => '');
    private static $log = array();
    /**
     * 日志写入接口
     * @access public
     * @param string $log 日志信息
     * @param string $destination  写入目标
     * @return void
     */
    public static function write($log, $destination = '')
    {
        self::$config['log_path'] = BASE_DATA_PATH . '/log/';
        $now = date(self::$config['log_time_format']);
        if (empty($destination)) {
            $destination = self::$config['log_path'] . date('y_m_d') . '.log';
        }
        // 自动创建日志目录
        $log_dir = dirname($destination);
        if (!is_dir($log_dir)) {
            mkdir($log_dir, 0755, true);
        }
        //检测日志文件大小，超过配置大小则备份日志文件重新生成
        if (is_file($destination) && floor(self::$config['log_file_size']) <= filesize($destination)) {
            rename($destination, dirname($destination) . '/' . time() . '-' . basename($destination));
        }
        $url = $_SERVER['REMOTE_ADDR'] . ' ' . $_SERVER['REQUEST_URI'];
        $url .= ' ( act=' . $_GET['act'] . '&op=' . $_GET['op'] . ' ) ';
        self::$log[] = $log;
        error_log($now . $url . "\r\n" . $log . "\r\n", 3, $destination);
    }
    /**
     * 记录调试信息
     * @param mixed  $msg  调试信息
     * @param string $type 信息类型
     * @return void
     */
    public static function record($msg)
    {
        self::$log[] = $msg;
    }
    public static function read()
    {
        return self::$log;
    }
}