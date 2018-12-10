<?php
/**
 * 相册
 */
namespace mobile\control;

use core;
use lib;
defined('SAFE_CONST') or exit('Access Invalid!');
class sns_album extends mobileMemberControl
{
    public function __construct()
    {
        parent::__construct();
    }
    /**
     * 上传图片
     *
     * @param
     * @return
     */
    public function file_uploadOp()
    {
        /**
         * 读取语言包
         */
        core\language::read('sns_home');
        $lang = core\language::getLangContent();
        $member_id = $this->member_info['member_id'];
        $class_id = isset($_POST['category_id']) ? intval($_POST['category_id']) : 0;
        if ($member_id <= 0 && $class_id <= 0) {
            echo json_encode(array('state' => 'false', 'message' => core\language::get('sns_upload_pic_fail'), 'origin_file_name' => $_FILES['file']['name']));
            exit;
        }
        $model = model();
        // 验证图片数量
        $count = $model->table('sns_albumpic')->where(array('member_id' => $member_id))->count();
        if (core\config::get('malbum_max_sum') != 0 && $count >= core\config::get('malbum_max_sum')) {
            output_error('已经超出允许上传图片数量，不能在上传图片！');
        }
        /**
         * 上传图片
         */
        $upload = new lib\uploadfile();
        $upload_dir = ATTACH_MALBUM . DS . $member_id . DS;
        $upload->set('default_dir', $upload_dir . $upload->getSysSetPath());
        $thumb_width = '240,1024';
        $thumb_height = '2048,1024';
        $upload->set('max_size', core\config::get('image_max_filesize'));
        $upload->set('thumb_width', $thumb_width);
        $upload->set('thumb_height', $thumb_height);
        $upload->set('fprefix', $member_id);
        $upload->set('thumb_ext', '_240,_1024');
        $result = $upload->upfile('file');
        if (!$result) {
            echo json_encode(array('state' => 'false', 'message' => core\language::get('sns_upload_pic_fail'), 'origin_file_name' => $_FILES['file']['name']));
            exit;
        }
        $img_path = $upload->getSysSetPath() . $upload->file_name;
        list($width, $height, $type, $attr) = getimagesize(BASE_UPLOAD_PATH . DS . ATTACH_MALBUM . DS . $member_id . DS . $img_path);
        $image = explode('.', $_FILES['file']['name']);
        $model_sns_alumb = model('sns_album');
        $ac_id = $model_sns_alumb->getSnsAlbumClassDefault($member_id);
        $insert = array();
        $insert['ap_name'] = $image['0'];
        $insert['ac_id'] = $ac_id;
        $insert['ap_cover'] = $img_path;
        $insert['ap_size'] = intval($_FILES['file']['size']);
        $insert['ap_spec'] = $width . 'x' . $height;
        $insert['upload_time'] = time();
        $insert['member_id'] = $member_id;
        $result = $model->table('sns_albumpic')->insert($insert);
        $data = array();
        $data['file_id'] = $result;
        $data['file_name'] = $img_path;
        $data['origin_file_name'] = $_FILES['file']['name'];
        $data['file_path'] = $img_path;
        $data['file_url'] = snsThumb($img_path, 240);
        $data['state'] = 'true';
        /**
         * 整理为json格式
         */
        output_data($data);
    }
}