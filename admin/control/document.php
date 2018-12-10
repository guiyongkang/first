<?php
/**
 * 系统文章管理
 **/
namespace admin\control;

use core;
use lib;
defined('SAFE_CONST') or exit('Access Invalid!');
class document extends SystemControl
{
    public function __construct()
    {
        parent::__construct();
        core\language::read('document');
    }
    /**
     * 系统文章管理首页
     */
    public function indexOp()
    {
        $this->documentOp();
        exit;
    }
    /**
     * 系统文章列表
     */
    public function documentOp()
    {
        $model_doc = model('document');
        $doc_list = $model_doc->getList();
        core\tpl::output('doc_list', $doc_list);
        core\tpl::showpage('document.index');
    }
    /**
     * 系统文章编辑
     */
    public function editOp()
    {
        $lang = core\language::getLangContent();
        /**
         * 更新
         */
        if (chksubmit()) {
            /**
             * 验证
             */
            $obj_validate = new lib\validate();
            $obj_validate->validateparam = array(array("input" => $_POST["doc_title"], "require" => "true", "message" => $lang['document_index_title_null']), array("input" => $_POST["doc_content"], "require" => "true", "message" => $lang['document_index_content_null']));
            $error = $obj_validate->validate();
            if ($error != '') {
                error($error);
            } else {
                $param = array();
                $param['doc_id'] = intval($_POST['doc_id']);
                $param['doc_title'] = trim($_POST['doc_title']);
                $param['doc_content'] = trim($_POST['doc_content']);
                $param['doc_time'] = time();
                $model_doc = model('document');
                $result = $model_doc->update($param);
                if ($result) {
                    /**
                     * 更新图片信息ID
                     */
                    $model_upload = model('upload');
                    if (isset($_POST['file_id']) && is_array($_POST['file_id'])) {
                        foreach ($_POST['file_id'] as $k => $v) {
                            $v = intval($v);
                            $update_array = array();
                            $update_array['upload_id'] = $v;
                            $update_array['item_id'] = intval($_POST['doc_id']);
                            $model_upload->update($update_array);
                            unset($update_array);
                        }
                    }
                    $url = 'index.php?act=document&op=edit&doc_id=' . intval($_POST['doc_id']);
                    $this->log(lang('nc_edit,document_index_document') . '[ID:' . $_POST['doc_id'] . ']', 1);
                    success($lang['nc_common_save_succ'], $url);
                } else {
                    error($lang['nc_common_save_fail']);
                }
            }
        }
        /**
         * 编辑
         */
        if (empty($_GET['doc_id'])) {
            error($lang['miss_argument']);
        }
        $model_doc = model('document');
        $doc = $model_doc->getOneById(intval($_GET['doc_id']));
        /**
         * 模型实例化
         */
        $model_upload = model('upload');
        $condition['upload_type'] = '4';
        $condition['item_id'] = $doc['doc_id'];
        $file_upload = $model_upload->getUploadList($condition);
        if (is_array($file_upload)) {
            foreach ($file_upload as $k => $v) {
                $file_upload[$k]['upload_path'] = UPLOAD_SITE_URL . '/' . ATTACH_ARTICLE . '/' . $file_upload[$k]['file_name'];
            }
        }
        core\tpl::output('PHPSESSID', session_id());
        core\tpl::output('file_upload', $file_upload);
        core\tpl::output('doc', $doc);
        core\tpl::showpage('document.edit');
    }
    /**
     * 系统文章图片上传
     */
    public function document_pic_uploadOp()
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
        $insert_array['upload_type'] = '4';
        $insert_array['file_size'] = $_FILES['fileupload']['size'];
        $insert_array['item_id'] = isset($_POST['item_id']) ? intval($_POST['item_id']) : 0;
        $insert_array['upload_time'] = time();
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