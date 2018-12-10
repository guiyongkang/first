<?php
namespace cache;

/**
 * Wincache缓存驱动
 * 
 */
class wincache
{
    protected $options = ['prefix' => '', 'expire' => 0];
    /**
     * 架构函数
     * @param array $options 缓存参数
     * @throws Exception
     * @access public
     */
    public function __construct($options = [])
    {
        if (!function_exists('wincache_ucache_info')) {
            throw new \BadFunctionCallException('not support: WinCache');
        }
        if (!empty($options)) {
            $this->options = array_merge($this->options, $options);
        }
    }
    /**
     * 判断缓存
     * @access public
     * @param string $name 缓存变量名
     * @return bool
     */
    public function has($name)
    {
        $name = $this->options['prefix'] . $name;
        return wincache_ucache_exists($name);
    }
    /**
     * 读取缓存
     * @access public
     * @param string $name 缓存变量名
     * @param mixed  $default 默认值
     * @return mixed
     */
    public function get($name, $default = false)
    {
        $name = $this->options['prefix'] . $name;
        return wincache_ucache_exists($name) ? wincache_ucache_get($name) : $default;
    }
    /**
     * 写入缓存
     * @access public
     * @param string    $name 缓存变量名
     * @param mixed     $value  存储数据
     * @param integer   $expire  有效时间（秒）
     * @return boolean
     */
    public function set($name, $value, $expire = null)
    {
        if (is_null($expire)) {
            $expire = $this->options['expire'];
        }
        $name = $this->options['prefix'] . $name;
        if (wincache_ucache_set($name, $value, $expire)) {
            return true;
        }
        return false;
    }
    /**
     * 自增缓存（针对数值缓存）
     * @access public
     * @param string    $name 缓存变量名
     * @param int       $step 步长
     * @return false|int
     */
    public function inc($name, $step = 1)
    {
        return wincache_ucache_inc($name, $step);
    }
    /**
     * 自减缓存（针对数值缓存）
     * @access public
     * @param string    $name 缓存变量名
     * @param int       $step 步长
     * @return false|int
     */
    public function dec($name, $step = 1)
    {
        return wincache_ucache_dec($name, $step);
    }
    /**
     * 删除缓存
     * @access public
     * @param string $name 缓存变量名
     * @return boolean
     */
    public function rm($name)
    {
        return wincache_ucache_delete($this->options['prefix'] . $name);
    }
    /**
     * 清除缓存
     * @access public
     * @return boolean
     */
    public function clear()
    {
        return;
    }
}