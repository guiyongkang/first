<?php
namespace cache;

class memcached
{
    protected $handler;
    protected $options = ['host' => '127.0.0.1', 'port' => 11211, 'expire' => 0, 'timeout' => 0, 'prefix' => '', 'username' => '', 'password' => ''];
    /**
     * 架构函数
     * @param array $options 缓存参数
     * @access public
     */
    public function __construct($options = [])
    {
        if (!extension_loaded('memcached')) {
            throw new \BadFunctionCallException('not support: memcached');
        }
        if (!empty($options)) {
            $this->options = array_merge($this->options, $options);
        }
        $this->handler = new \Memcached();
        // 设置连接超时时间（单位：毫秒）
        if ($this->options['timeout'] > 0) {
            $this->handler->setOption(\Memcached::OPT_CONNECT_TIMEOUT, $this->options['timeout']);
        }
        // 支持集群
        $hosts = explode(',', $this->options['host']);
        $ports = explode(',', $this->options['port']);
        if (empty($ports[0])) {
            $ports[0] = 11211;
        }
        // 建立连接
        $servers = [];
        foreach ((array) $hosts as $i => $host) {
            $servers[] = [$host, isset($ports[$i]) ? $ports[$i] : $ports[0], 1];
        }
        $this->handler->addServers($servers);
        if ('' != $this->options['username']) {
            $this->handler->setOption(\Memcached::OPT_BINARY_PROTOCOL, true);
            $this->handler->setSaslAuthData($this->options['username'], $this->options['password']);
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
        return $this->handler->get($name) ? true : false;
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
        $result = $this->handler->get($this->options['prefix'] . $name);
        return false !== $result ? $result : $default;
    }
    /**
     * 写入缓存
     * @access public
     * @param string    $name 缓存变量名
     * @param mixed     $value  存储数据
     * @param integer   $expire  有效时间（秒）
     * @return bool
     */
    public function set($name, $value, $expire = null)
    {
        if (is_null($expire)) {
            $expire = $this->options['expire'];
        }
        $name = $this->options['prefix'] . $name;
        $expire = 0 == $expire ? 0 : $_SERVER['REQUEST_TIME'] + $expire;
        if ($this->handler->set($name, $value, $expire)) {
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
        return $this->handler->increment($name, $step);
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
        return $this->handler->decrement($name, $step);
    }
    /**
     * 删除缓存
     * @param    string  $name 缓存变量名
     * @param bool|false $ttl
     * @return bool
     */
    public function rm($name, $ttl = false)
    {
        $name = $this->options['prefix'] . $name;
        return false === $ttl ? $this->handler->delete($name) : $this->handler->delete($name, $ttl);
    }
    /**
     * 清除缓存
     * @access public
     * @return bool
     */
    public function clear()
    {
        return $this->handler->flush();
    }
}