<?php
/**
 * 下载设置
 **/
namespace admin\control;

use core;
use lib;
defined('SAFE_CONST') or exit('Access Invalid!');
class mb_app extends SystemControl
{
    public function __construct()
    {
        parent::__construct();
    }
    /**
     * 设置下载地址
     *
     */
    public function mb_appOp()
    {
        $model_setting = model('setting');
        $mobile_apk = $model_setting->getRowSetting('mobile_apk');
        $mobile_apk_version = $model_setting->getRowSetting('mobile_apk_version');
        $mobile_ios = $model_setting->getRowSetting('mobile_ios');
        if (chksubmit()) {
            $update_array = array();
            $update_array['mobile_apk'] = $_POST['mobile_apk'];
            $update_array['mobile_apk_version'] = $_POST['mobile_apk_version'];
            $update_array['mobile_ios'] = $_POST['mobile_ios'];
            $state = $model_setting->updateSetting($update_array);
            if ($state) {
                $this->log('设置手机端下载地址');
                success(core\language::get('nc_common_save_succ'), 'index.php?act=mb_app&op=mb_app');
            } else {
                error(core\language::get('nc_common_save_fail'));
            }
        }
        core\tpl::output('mobile_apk', $mobile_apk);
        core\tpl::output('mobile_version', $mobile_apk_version);
        core\tpl::output('mobile_ios', $mobile_ios);
        core\tpl::showpage('mb_app.edit');
    }
    /**
     * 生成二维码
     */
    public function mb_qrOp()
    {
        $url = urlShop('mb_app', 'index');
        $mobile_app = 'mb_app.png';
        require_once BASE_RESOURCE_PATH . DS . 'phpqrcode' . DS . 'index.php';
        $PhpQRCode = new \PhpQRCode();
        $PhpQRCode->set('pngTempDir', BASE_UPLOAD_PATH . DS . ATTACH_COMMON . DS);
        $PhpQRCode->set('date', $url);
        $PhpQRCode->set('pngTempName', $mobile_app);
        $PhpQRCode->init();
        $this->log('生成手机端二维码');
        success('生成二维码成功', 'index.php?act=mb_app&op=mb_app');
    }
}