<?php
namespace pc\control;
use core;
use lib;
defined('SAFE_CONST') or exit('Access Invalid!');
class control
{
	protected $member_info = array();
    /**
     * 检查短消息数量
     *
     */
    protected function checkMessage()
    {
        if (core\session::get('member_id')) {
            return;
        }
        //判断cookie是否存在
        $cookie_name = 'msgnewnum' . core\session::get('member_id');
        if (cookie($cookie_name) != null) {
            $countnum = intval(cookie($cookie_name));
        } else {
            $message_model = model('message');
            $countnum = $message_model->countNewMessage(core\session::get('member_id'));
            setNcCookie($cookie_name, $countnum, 2 * 3600);
            //保存2小时
        }
        core\tpl::output('message_num', $countnum);
    }
    /**
     *  输出头部的公用信息
     *
     */
    protected function showLayout()
    {
        $this->checkMessage();
        //短消息检查
        $this->article();
        //文章输出
        $this->showCartCount();
        core\tpl::output('hot_search', explode(',', core\config::get('hot_search')));
        //热门搜索
        $model_class = model('goods_class');
        $goods_class = $model_class->get_all_category();
        $model_channel = model('web_channel');
        $goods_channel = $model_channel->getChannelList(array('channel_show' => '1'));
        //多频道开始
        foreach ($goods_class as $key => $value) {
            foreach ($goods_channel as $k => $v) {
                if ($value['gc_id'] == $v['gc_id']) {
                    $goods_class[$value['gc_id']]['channel_gc_id'] = $v['gc_id'];
                    $goods_class[$value['gc_id']]['channel_id'] = $v['channel_id'];
                }
                if (!empty($value['class2']) && is_array($value['class2'])) {
                    foreach ($value['class2'] as $kk => $vv) {
                        if ($vv['gc_id'] == $v['gc_id']) {
                            $goods_class[$value['gc_id']]['class2'][$vv['gc_id']]['channel_gc_id'] = $v['gc_id'];
                            $goods_class[$value['gc_id']]['class2'][$vv['gc_id']]['channel_id'] = $v['channel_id'];
                        }
                    }
                }
            }
        }
        //多频道结束
        core\tpl::output('show_goods_class', $goods_class);
        //商品分类
        //获取导航
        core\tpl::output('nav_list', self::filter_nav(rkcache('nav', true)));
    }
    /**
     * 过滤导航菜单，将未开启的导航菜单关闭
     * @param array $nav_list
     * @return array
     */
    private function filter_nav($nav_list)
    {
        foreach ($nav_list as $key => $nav) {
            if ($nav['nav_is_close'] == '1') {
                unset($nav_list[$key]);
            }
        }
        return $nav_list;
    }
    /**
     * 显示购物车数量
     */
    protected function showCartCount()
    {
        if (cookie('cart_goods_num') != null) {
            $cart_num = intval(cookie('cart_goods_num'));
        } else {
            //已登录状态，存入数据库,未登录时，优先存入缓存，否则存入COOKIE
            if (core\session::get('member_id')) {
                $save_type = 'db';
            } else {
                $save_type = 'cookie';
            }
            $cart_num = model('cart')->getCartNum($save_type, array('buyer_id' => core\session::get('member_id')));
            //查询购物车商品种类
        }
        core\tpl::output('cart_goods_num', $cart_num);
    }
    /**
     * 输出会员等级
     * @param bool $is_return 是否返回会员信息，返回为true，输出会员信息为false
     */
    protected function getMemberAndGradeInfo($is_return = false)
    {
        //会员详情及会员级别处理
        if (core\session::get('member_id')) {
            $model_member = model('member');
            $this->member_info = $model_member->getMemberInfoByID(core\session::get('member_id'));
            if ($this->member_info) {
                $member_gradeinfo = $model_member->getOneMemberGrade(intval($this->member_info['member_exppoints']));
                $this->member_info = array_merge($this->member_info, $member_gradeinfo);
            }
        }
        if ($is_return == true) {
            //返回会员信息
            return $this->member_info;
        } else {
            //输出会员信息
            core\tpl::output('member_info', $this->member_info);
        }
    }
    /**
     * 验证会员是否登录
     *
     */
    protected function checkLogin()
    {
        if (core\session::get('is_login') !== '1') {
            if (trim($_GET['op']) == 'favoritesgoods' || trim($_GET['op']) == 'favoritesstore') {
                $lang = core\language::getLangContent('UTF-8');
                echo json_encode(array('done' => false, 'msg' => $lang['no_login']));
                die;
            }
            $ref_url = request_uri();
            if (!empty($_GET['inajax'])) {
                showDialog('', '', 'js', 'login_dialog();', 200);
            } else {
                header('location: index.php?act=login&ref_url=' . urlencode($ref_url));
            }
            exit;
        }
    }
    /**
     * 添加到任务队列
     *
     * @param array $goods_array
     * @param boolean $ifdel 是否删除以原记录
     */
    protected function addcron($data = array(), $ifdel = false)
    {
        $model_cron = model('cron');
        if (isset($data[0])) {
            // 批量插入
            $where = array();
            foreach ($data as $k => $v) {
                if (isset($v['content'])) {
                    $data[$k]['content'] = serialize($v['content']);
                }
                // 删除原纪录条件
                if ($ifdel) {
                    $where[] = '(type = ' . $data['type'] . ' and exeid = ' . $data['exeid'] . ')';
                }
            }
            // 删除原纪录
            if ($ifdel) {
                $model_cron->delCron(implode(',', $where));
            }
            $model_cron->addCronAll($data);
        } else {
            // 单条插入
            if (isset($data['content'])) {
                $data['content'] = serialize($data['content']);
            }
            // 删除原纪录
            if ($ifdel) {
                $model_cron->delCron(array('type' => $data['type'], 'exeid' => $data['exeid']));
            }
            $model_cron->addCron($data);
        }
    }
    //文章输出
    protected function article()
    {
        if ($article = rkcache("index/article")) {
            core\tpl::output('show_article', $article['show_article']);
            core\tpl::output('article_list', $article['article_list']);
            return;
        }
        $model_article_class = model('article_class');
        $model_article = model('article');
        $show_article = array();
        //商城公告
        $article_list = array();
        //下方文章
        $notice_class = array('notice');
        $code_array = array('member', 'store', 'payment', 'sold', 'service', 'about');
        $notice_limit = 5;
        $faq_limit = 5;
        $class_condition = array();
        $class_condition['home_index'] = 'home_index';
        $class_condition['order'] = 'ac_sort asc';
        $article_class = $model_article_class->getClassList($class_condition);
        $class_list = array();
        if (!empty($article_class) && is_array($article_class)) {
            foreach ($article_class as $key => $val) {
                $ac_code = $val['ac_code'];
                $ac_id = $val['ac_id'];
                $val['list'] = array();
                //文章
                $class_list[$ac_id] = $val;
            }
        }
        $condition = array();
        $condition['article_show'] = '1';
        $condition['home_index'] = 'home_index';
        $condition['field'] = 'article.article_id,article.ac_id,article.article_url,article.article_title,article.article_time,article_class.ac_name,article_class.ac_parent_id';
        $condition['order'] = 'article_sort asc,article_time desc';
        $condition['limit'] = '300';
        $article_array = $model_article->getJoinList($condition);
        if (!empty($article_array) && is_array($article_array)) {
            foreach ($article_array as $key => $val) {
                $ac_id = $val['ac_id'];
                $ac_parent_id = $val['ac_parent_id'];
                if ($ac_parent_id == 0) {
                    //顶级分类
                    $class_list[$ac_id]['list'][] = $val;
                } else {
                    $class_list[$ac_parent_id]['list'][] = $val;
                }
            }
        }
        if (!empty($class_list) && is_array($class_list)) {
            foreach ($class_list as $key => $val) {
                $ac_code = $val['ac_code'];
                if (in_array($ac_code, $notice_class)) {
                    $list = $val['list'];
                    array_splice($list, $notice_limit);
                    $val['list'] = $list;
                    $show_article[$ac_code] = $val;
                }
                if (in_array($ac_code, $code_array)) {
                    $list = $val['list'];
                    $val['class']['ac_name'] = $val['ac_name'];
                    array_splice($list, $faq_limit);
                    $val['list'] = $list;
                    $article_list[] = $val;
                }
            }
        }
        wkcache('index/article', array('show_article' => $show_article, 'article_list' => $article_list));
        core\tpl::output('show_article', $show_article);
        core\tpl::output('article_list', $article_list);
    }
    /**
     * 自动登录
     */
    protected function auto_login()
    {
        $data = cookie('auto_login');
        if (empty($data)) {
            return false;
        }
        $model_member = model('member');
        if (core\session::get('is_login')) {
            $model_member->auto_login();
        }
        $member_id = intval(decrypt($data, MD5_KEY));
        if ($member_id <= 0) {
            return false;
        }
        $member_info = $model_member->getMemberInfoByID($member_id);
        $model_member->createSession($member_info);
    }
}
class BaseHomeControl extends control 
{
    public function __construct()
	{
        //输出头部的公用信息
        $this->showLayout();
        //输出会员信息
        $this->getMemberAndGradeInfo(false);

        core\language::read('common,home_layout');

        if (!empty($_GET['column']) && strtoupper(CHARSET) == 'GBK'){
            $_GET = core\language::getGBK($_GET);
        }
        if(!core\config::get('site_status')) halt(core\config::get('closed_reason'));
	    // 自动登录
        $this->auto_login();
    }
}