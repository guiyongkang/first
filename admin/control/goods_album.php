<?php
/**
 * 相册管理
 **/
namespace admin\control;

use core;
use lib;
defined('SAFE_CONST') or exit('Access Invalid!');
class goods_album extends SystemControl
{
    public function __construct()
    {
        parent::__construct();
        core\language::read('goods_album');
    }
    /**
     * 相册列表
     */
    public function listOp()
    {
        $model = model();
        if (chksubmit()) {
            if (isset($_POST['aclass_id']) && is_array($_POST['aclass_id'])) {
                foreach ($_POST['aclass_id'] as $k => $v) {
                    if (!is_numeric($v)) {
                        unset($_POST['aclass_id'][$k]);
                    }
                }
            }
            if (!empty($_POST['aclass_id'])) {
                $pic = $model->table('album_pic')->field('apic_cover')->where(array('aclass_id' => array('in', $_POST['aclass_id'])))->select();
                if (is_array($pic)) {
                    foreach ($pic as $v) {
                        $this->del_file($v['apic_cover']);
                    }
                }
                $model->table('album_pic')->where(array('aclass_id' => array('in', $_POST['aclass_id'])))->delete();
                $model->table('album_class')->where(array('aclass_id' => array('in', $_POST['aclass_id'])))->delete();
                $this->log(lang('nc_delete,g_album_one') . '[ID:' . implode(',', $_POST['aclass_id']) . ']', 1);
                success(core\language::get('nc_common_del_succ'));
            }
        }
        $condiiton = array();
		$store_name = '';
        if (isset($_GET['keyword']) && is_numeric($_GET['keyword'])) {
            $condiiton['store.store_id'] = $_GET['keyword'];
            $store_name = $model->table('store')->getfby_store_id($_GET['keyword'], 'store_name');
        } elseif (!empty($_GET['keyword'])) {
            $store_name = $_GET['keyword'];
            $store_id = $model->table('store')->getfby_store_name($_GET['keyword'], 'store_id');
            if (is_numeric($store_id)) {
                $condiiton['store.store_id'] = $store_id;
            } else {
                $condiiton['store.store_id'] = 0;
            }
        }
        $model->table('album_class,store')->where($condiiton)->join('inner')->on('album_class.store_id=store.store_id');
        $list = $model->field('album_class.*,store.store_name')->page(10)->select();
        core\tpl::output('page', $model->showpage());
        $model->cls()->table('album_pic')->field('aclass_id,count(*) as pcount')->group('aclass_id');
        if (is_array($list) && !empty($list)) {
            foreach ($list as $v) {
                $class[] = $v['aclass_id'];
            }
            $model->where(array('aclass_id' => array('in', implode(',', $class))));
        }
		$pic_count = array();
        $count = $model->select();
        if (is_array($count)) {
            foreach ($count as $v) {
                $pic_count[$v['aclass_id']] = $v['pcount'];
            }
        }
        core\tpl::output('pic_count', $pic_count);
        core\tpl::output('list', $list);
        core\tpl::output('store_name', $store_name);
        core\tpl::showpage('goods_album.index');
    }
    /**
     * 图片列表
     */
    public function pic_listOp()
    {
        $model = model();
        $condiiton = array();
		$store_name = '';
        if (isset($_GET['keyword']) && is_numeric($_GET['keyword'])) {
            $condiiton['store_id'] = $_GET['keyword'];
            $store_name = $model->table('store')->getfby_store_id($_GET['keyword'], 'store_name');
        } elseif (!empty($_GET['keyword'])) {
            $store_name = $_GET['keyword'];
            $store_id = $model->table('store')->getfby_store_name($_GET['keyword'], 'store_id');
            if (is_numeric($store_id)) {
                $condiiton['store_id'] = $store_id;
            } else {
                $condiiton['store_id'] = 0;
            }
        } elseif (isset($_GET['aclass_id']) && is_numeric($_GET['aclass_id'])) {
            $condiiton['aclass_id'] = $_GET['aclass_id'];
        }
        $list = $model->table('album_pic')->where($condiiton)->order('apic_id desc')->page(40)->select();
        $show_page = $model->showpage();
        core\tpl::output('page', $show_page);
        core\tpl::output('list', $list);
        core\tpl::output('store_name', $store_name);
        core\tpl::showpage('goods_album.pic_list');
    }
    /**
     * 删除相册
     */
    public function aclass_delOp()
    {
        $aclass_id = isset($_GET['aclass_id']) ? intval($_GET['aclass_id']) : '';
        if (!is_numeric($aclass_id)) {
            error(core\language::get('param_error'));
        }
        $model = model();
        $pic = $model->table('album_pic')->field('apic_cover')->where(array('aclass_id' => $aclass_id))->select();
        if (is_array($pic)) {
            foreach ($pic as $v) {
                $this->del_file($v['apic_cover']);
            }
        }
        $model->table('album_pic')->where(array('aclass_id' => $aclass_id))->delete();
        $model->table('album_class')->where(array('aclass_id' => $aclass_id))->delete();
        $this->log(lang('nc_delete,g_album_one') . '[ID:' . intval($_GET['aclass_id']) . ']', 1);
        success(core\language::get('nc_common_del_succ'));
    }
    /**
     * 删除一张图片及其对应记录
     *
     */
    public function del_album_picOp()
    {
        list($apic_id, $filename) = @explode('|', $_GET['key']);
        if (!is_numeric($apic_id) || empty($filename)) {
            exit('0');
        }
        $this->del_file($filename);
        model('album_pic')->where(array('apic_id' => $apic_id))->delete();
        $this->log(lang('nc_delete,g_album_pic_one') . '[ID:' . $apic_id . ']', 1);
        exit('1');
    }
    /**
     * 删除多张图片
     *
     */
    public function del_more_picOp()
    {
        $model = model('album_pic');
        $list = $model->where(array('apic_id' => array('in', $_POST['delbox'])))->select();
        if (is_array($list)) {
            foreach ($list as $v) {
                $this->del_file($v['apic_cover']);
            }
        }
        $model->where(array('apic_id' => array('in', $_POST['delbox'])))->delete();
        $this->log(lang('nc_delete,g_album_pic_one') . '[ID:' . implode(',', $_POST['delbox']) . ']', 1);
        redirect();
    }
    /**
     * 删除图片文件
     *
     */
    private function del_file($filename)
    {
        //取店铺ID
        if (preg_match('/^(\\d+_)/', $filename)) {
            $store_id = substr($filename, 0, strpos($filename, '_'));
        } else {
            $store_id = model()->cls()->table('album_pic')->getfby_apic_cover($filename, 'store_id');
        }
        $path = BASE_UPLOAD_PATH . '/' . ATTACH_GOODS . '/' . $store_id . '/' . $filename;
        $ext = strrchr($path, '.');
        $type = array('_tiny', '_small', '_mid', '_max', '_240x240');
        foreach ($type as $v) {
            if (is_file($fpath = $path . $v . $ext)) {
                unlink($fpath);
            }
        }
        if (is_file($path)) {
            unlink($path);
        }
    }
}