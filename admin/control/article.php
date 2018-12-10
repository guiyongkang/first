<?php
/**
 * 文章管理
 *
 **/
namespace admin\control;

use core;
use lib;
defined('SAFE_CONST') or exit('Access Invalid!');
class article extends SystemControl
{
    public function __construct()
    {
        parent::__construct();
        core\language::read('article');
    }
    /**
     * 文章管理
     */
    public function articleOp()
    {
        $lang = core\language::getLangContent();
        $model_article = model('article');
        /**
         * 删除
         */
        if (chksubmit()) {
            if (is_array($_POST['del_id']) && !empty($_POST['del_id'])) {
                $model_upload = model('upload');
                foreach ($_POST['del_id'] as $k => $v) {
                    $v = intval($v);
                    /**
                     * 删除图片
                     */
                    $condition['upload_type'] = '1';
                    $condition['item_id'] = $v;
                    $upload_list = $model_upload->getUploadList($condition);
                    if (is_array($upload_list)) {
                        foreach ($upload_list as $k_upload => $v_upload) {
                            $model_upload->del($v_upload['upload_id']);
                            unlink(BASE_UPLOAD_PATH . DS . ATTACH_ARTICLE . DS . $v_upload['file_name']);
                        }
                    }
                    $model_article->del($v);
                }
                $this->log(lang('article_index_del_succ') . '[ID:' . implode(',', $_POST['del_id']) . ']', null);
                success($lang['article_index_del_succ']);
            } else {
                error($lang['article_index_choose']);
            }
        }
        /**
         * 检索条件
         */
        $condition['ac_id'] = isset($_GET['search_ac_id']) ? intval($_GET['search_ac_id']) : 0;
        $condition['like_title'] = isset($_GET['search_title']) ? trim($_GET['search_title']) : '';
        /**
         * 分页
         */
        $page = new lib\page();
        $page->setEachNum(10);
        $page->setStyle('admin');
        /**
         * 列表
         */
        $article_list = $model_article->getArticleList($condition, $page);
        /**
         * 整理列表内容
         */
        if (is_array($article_list)) {
            /**
             * 取文章分类
             */
            $model_class = model('article_class');
            $class_list = $model_class->getClassList($condition);
            $tmp_class_name = array();
            if (is_array($class_list)) {
                foreach ($class_list as $k => $v) {
                    $tmp_class_name[$v['ac_id']] = $v['ac_name'];
                }
            }
            foreach ($article_list as $k => $v) {
                /**
                 * 发布时间
                 */
                $article_list[$k]['article_time'] = date('Y-m-d H:i:s', $v['article_time']);
                /**
                 * 所属分类
                 */
                if (array_key_exists($v['ac_id'], $tmp_class_name)) {
                    $article_list[$k]['ac_name'] = $tmp_class_name[$v['ac_id']];
                }
            }
        }
        /**
         * 分类列表
         */
        $model_class = model('article_class');
        $parent_list = $model_class->getTreeClassList(2);
        if (is_array($parent_list)) {
            $unset_sign = false;
            foreach ($parent_list as $k => $v) {
                $parent_list[$k]['ac_name'] = str_repeat("&nbsp;", $v['deep'] * 2) . $v['ac_name'];
            }
        }
        core\tpl::output('article_list', $article_list);
        core\tpl::output('page', $page->show());
        core\tpl::output('search_title', isset($_GET['search_title']) ? trim($_GET['search_title']) : '');
        core\tpl::output('search_ac_id', isset($_GET['search_ac_id']) ? intval($_GET['search_ac_id']) : 0);
        core\tpl::output('parent_list', $parent_list);
        core\tpl::showpage('article.index');
    }
    /**
     * 文章添加
     */
    public function article_addOp()
    {
        $lang = core\language::getLangContent();
        $model_article = model('article');
        /**
         * 保存
         */
        if (chksubmit()) {
            /**
             * 验证
             */
            $obj_validate = new lib\validate();
            $obj_validate->validateparam = array(array("input" => $_POST["article_title"], "require" => "true", "message" => $lang['article_add_title_null']), array("input" => $_POST["ac_id"], "require" => "true", "message" => $lang['article_add_class_null']), array("input" => $_POST["article_content"], "require" => "true", "message" => $lang['article_add_content_null']), array("input" => $_POST["article_sort"], "require" => "true", 'validator' => 'Number', "message" => $lang['article_add_sort_int']));
            $error = $obj_validate->validate();
            if ($error != '') {
                error($error);
            } else {
                $insert_array = array();
                $insert_array['article_title'] = trim($_POST['article_title']);
                $insert_array['ac_id'] = intval($_POST['ac_id']);
                $insert_array['article_url'] = trim($_POST['article_url']);
                $insert_array['article_show'] = trim($_POST['article_show']);
                $insert_array['article_sort'] = trim($_POST['article_sort']);
                $insert_array['article_content'] = trim($_POST['article_content']);
                $insert_array['article_time'] = time();
                $result = $model_article->add($insert_array);
                if ($result) {
                    /**
                     * 更新图片信息ID
                     */
                    $model_upload = model('upload');
                    if (!empty($_POST['file_id']) && is_array($_POST['file_id'])) {
                        foreach ($_POST['file_id'] as $k => $v) {
                            $v = intval($v);
                            $update_array = array();
                            $update_array['upload_id'] = $v;
                            $update_array['item_id'] = $result;
                            $model_upload->update($update_array);
                            unset($update_array);
                        }
                    }
                    $url = 'index.php?act=article&op=article';
                    $this->log(lang('article_add_ok') . '[' . $_POST['article_title'] . ']', null);
                    success($lang['article_add_ok'], $url);
                } else {
                    error($lang['article_add_fail']);
                }
            }
        }
        /**
         * 分类列表
         */
        $model_class = model('article_class');
        $parent_list = $model_class->getTreeClassList(2);
        if (is_array($parent_list)) {
            $unset_sign = false;
            foreach ($parent_list as $k => $v) {
                $parent_list[$k]['ac_name'] = str_repeat("&nbsp;", $v['deep'] * 2) . $v['ac_name'];
            }
        }
        /**
         * 模型实例化
         */
        $model_upload = model('upload');
        $condition['upload_type'] = '1';
        $condition['item_id'] = '0';
        $file_upload = $model_upload->getUploadList($condition);
        if (is_array($file_upload)) {
            foreach ($file_upload as $k => $v) {
                $file_upload[$k]['upload_path'] = UPLOAD_SITE_URL . '/' . ATTACH_ARTICLE . '/' . $file_upload[$k]['file_name'];
            }
        }
        core\tpl::output('PHPSESSID', session_id());
        core\tpl::output('ac_id', isset($_GET['ac_id']) ? intval($_GET['ac_id']) : 0);
        core\tpl::output('parent_list', $parent_list);
        core\tpl::output('file_upload', $file_upload);
        core\tpl::showpage('article.add');
    }
    /**
     * 文章编辑
     */
    public function article_editOp()
    {
        $lang = core\language::getLangContent();
        $model_article = model('article');
        if (chksubmit()) {
            /**
             * 验证
             */
            $obj_validate = new lib\validate();
            $obj_validate->validateparam = array(array("input" => $_POST["article_title"], "require" => "true", "message" => $lang['article_add_title_null']), array("input" => $_POST["ac_id"], "require" => "true", "message" => $lang['article_add_class_null']), array("input" => $_POST["article_content"], "require" => "true", "message" => $lang['article_add_content_null']), array("input" => $_POST["article_sort"], "require" => "true", 'validator' => 'Number', "message" => $lang['article_add_sort_int']));
            $error = $obj_validate->validate();
            if ($error != '') {
                error($error);
            } else {
                $update_array = array();
                $update_array['article_id'] = intval($_POST['article_id']);
                $update_array['article_title'] = trim($_POST['article_title']);
                $update_array['ac_id'] = intval($_POST['ac_id']);
                $update_array['article_url'] = trim($_POST['article_url']);
                $update_array['article_show'] = trim($_POST['article_show']);
                $update_array['article_sort'] = trim($_POST['article_sort']);
                $update_array['article_content'] = trim($_POST['article_content']);
                $result = $model_article->update($update_array);
                if ($result) {
                    /**
                     * 更新图片信息ID
                     */
                    $model_upload = model('upload');
                    if (isset($_POST['file_id']) && is_array($_POST['file_id'])) {
                        foreach ($_POST['file_id'] as $k => $v) {
                            $update_array = array();
                            $update_array['upload_id'] = intval($v);
                            $update_array['item_id'] = intval($_POST['article_id']);
                            $model_upload->update($update_array);
                            unset($update_array);
                        }
                    }
                    $url = 'index.php?act=article&op=article_edit&article_id=' . intval($_POST['article_id']);
                    $this->log(lang('article_edit_succ') . '[' . $_POST['article_title'] . ']', null);
                    success($lang['article_edit_succ'], $url);
                } else {
                    error($lang['article_edit_fail']);
                }
            }
        }
        $article_array = $model_article->getOneArticle(intval($_GET['article_id']));
        if (empty($article_array)) {
            error($lang['param_error']);
        }
        /**
         * 文章类别模型实例化
         */
        $model_class = model('article_class');
        /**
         * 父类列表，只取到第一级
         */
        $parent_list = $model_class->getTreeClassList(2);
        if (is_array($parent_list)) {
            $unset_sign = false;
            foreach ($parent_list as $k => $v) {
                $parent_list[$k]['ac_name'] = str_repeat("&nbsp;", $v['deep'] * 2) . $v['ac_name'];
            }
        }
        /**
         * 模型实例化
         */
        $model_upload = model('upload');
        $condition['upload_type'] = '1';
        $condition['item_id'] = $article_array['article_id'];
        $file_upload = $model_upload->getUploadList($condition);
        if (is_array($file_upload)) {
            foreach ($file_upload as $k => $v) {
                $file_upload[$k]['upload_path'] = UPLOAD_SITE_URL . '/' . ATTACH_ARTICLE . '/' . $file_upload[$k]['file_name'];
            }
        }
        core\tpl::output('PHPSESSID', session_id());
        core\tpl::output('file_upload', $file_upload);
        core\tpl::output('parent_list', $parent_list);
        core\tpl::output('article_array', $article_array);
        core\tpl::showpage('article.edit');
    }
    /**
     * 文章图片上传
     */
    public function article_pic_uploadOp()
    {
        /**
         * 上传图片
         */
        $upload = new lib\uploadfile();
        $upload->set('default_dir', ATTACH_ARTICLE);
        $result = $upload->upfile('fileupload');
        if ($result) {
            $_POST['pic'] = $upload->file_name;
        } else {
            echo 'error';
            exit;
        }
        /**
         * 模型实例化
         */
        $model_upload = model('upload');
        /**
         * 图片数据入库
         */
        $insert_array = array();
        $insert_array['file_name'] = $_POST['pic'];
        $insert_array['upload_type'] = '1';
        $insert_array['file_size'] = $_FILES['fileupload']['size'];
        $insert_array['upload_time'] = time();
        $insert_array['item_id'] = empty($_POST['item_id']) ? 0 : intval($_POST['item_id']);
        $result = $model_upload->add($insert_array);
        if ($result) {
            $data = array();
            $data['file_id'] = $result;
            $data['file_name'] = $_POST['pic'];
            $data['file_path'] = $_POST['pic'];
            /**
             * 整理为json格式
             */
            $output = json_encode($data);
            echo $output;
        }
    }
    /**
     * ajax操作
     */
    public function ajaxOp()
    {
        switch ($_GET['branch']) {
            /**
             * 删除文章图片
             */
            case 'del_file_upload':
                if (intval($_GET['file_id']) > 0) {
                    $model_upload = model('upload');
                    /**
                     * 删除图片
                     */
                    $file_array = $model_upload->getOneUpload(intval($_GET['file_id']));
                    unlink(BASE_UPLOAD_PATH . DS . ATTACH_ARTICLE . DS . $file_array['file_name']);
                    /**
                     * 删除信息
                     */
                    $model_upload->del(intval($_GET['file_id']));
                    echo 'true';
                    exit;
                } else {
                    echo 'false';
                    exit;
                }
                break;
        }
    }
}