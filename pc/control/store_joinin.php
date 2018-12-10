<?php
/**
 * 商家入驻
 **/
namespace pc\control;

use core;
use lib;
defined('SAFE_CONST') or exit('Access Invalid!');
class store_joinin extends BaseHomeControl
{
    private $joinin_detail = NULL;
    public function __construct()
    {
        parent::__construct();
        core\tpl::setLayout('store_joinin_layout');
        $this->checkLogin();
        $model_seller = model('seller');
        $seller_info = $model_seller->getSellerInfo(array('member_id' => core\session::get('member_id')));
        if (!empty($seller_info)) {
            header('location:' . urlBiz('seller_login'));
        }
        if ($_GET['op'] != 'check_seller_name_exist' && $_GET['op'] != 'checkname') {
            $this->check_joinin_state();
        }
        $phone_array = explode(',', core\config::get('site_phone'));
        core\tpl::output('phone_array', $phone_array);
        $model_help = model('help');
        $condition = array();
        $condition['type_id'] = '99';
        // 默认显示入驻流程;
        $list = $model_help->getShowStoreHelpList($condition);
        core\tpl::output('list', $list);
        // 左侧帮助类型及帮助
        core\tpl::output('show_sign', 'joinin');
        core\tpl::output('html_title', core\config::get('site_name') . ' - ' . '商家入驻');
        core\tpl::output('article_list', '');
        // 底部不显示文章分类
    }
    private function check_joinin_state()
    {
        $model_store_joinin = model('store_joinin');
        $joinin_detail = $model_store_joinin->getOne(array('member_id' => core\session::get('member_id')));
        if (!empty($joinin_detail)) {
            $this->joinin_detail = $joinin_detail;
            switch (intval($joinin_detail['joinin_state'])) {
                case STORE_JOIN_STATE_NEW:
                    $this->step4();
                    $this->show_join_message('入驻申请已经提交，请等待管理员审核', FALSE, '3');
                    break;
                case STORE_JOIN_STATE_PAY:
                    $this->show_join_message('已经提交，请等待管理员核对后为您开通店铺', FALSE, '4');
                    break;
                case STORE_JOIN_STATE_VERIFY_SUCCESS:
                    if (!in_array($_GET['op'], array('pay', 'pay_save'))) {
                        $this->payOp();
                    }
                    break;
                case STORE_JOIN_STATE_VERIFY_FAIL:
                    if (!in_array($_GET['op'], array('step1', 'step2', 'step3', 'step4'))) {
                        $this->show_join_message('审核失败:' . $joinin_detail['joinin_message'], APP_URL . DS . 'index.php?act=store_joinin&op=step1');
                    }
                    break;
                case STORE_JOIN_STATE_PAY_FAIL:
                    if (!in_array($_GET['op'], array('pay', 'pay_save'))) {
                        $this->show_join_message('付款审核失败:' . $joinin_detail['joinin_message'], APP_URL . DS . 'index.php?act=store_joinin&op=pay');
                    }
                    break;
                case STORE_JOIN_STATE_FINAL:
                    header('location:' . urlBiz('seller_login'));
                    break;
            }
        }
    }
    public function indexOp()
    {
        $this->step0Op();
    }
    public function step0Op()
    {
        $model_document = model('document');
        $document_info = $model_document->getOneByCode('open_store');
        core\tpl::output('agreement', $document_info['doc_content']);
        core\tpl::output('step', '0');
        core\tpl::output('sub_step', 'step0');
        core\tpl::showpage('store_joinin_apply');
        exit;
    }
    public function step1Op()
    {
        core\tpl::output('step', '1');
        core\tpl::output('sub_step', 'step1');
        core\tpl::showpage('store_joinin_apply');
        exit;
    }
    public function step2Op()
    {
        if (!empty($_POST)) {
            $param = array();
            $param['member_name'] = core\session::get('member_name');
            $param['company_name'] = $_POST['company_name'];
            $param['company_province_id'] = isset($_POST['province_id']) ? ntval($_POST['province_id']) : 0;
            $param['company_address'] = $_POST['company_address'];
            $param['company_address_detail'] = $_POST['company_address_detail'];
            $param['company_phone'] = isset($_POST['company_phone']) ? $_POST['company_phone'] : '';
            $param['company_employee_count'] = isset($_POST['company_employee_count']) ? intval($_POST['company_employee_count']) : 0;
            $param['company_registered_capital'] = intval($_POST['company_registered_capital']);
            $param['contacts_name'] = $_POST['contacts_name'];
            $param['contacts_phone'] = isset($_POST['contacts_phone']) ? $_POST['contacts_phone'] : '';
            $param['contacts_email'] = $_POST['contacts_email'];
            $param['business_licence_number'] = $_POST['business_licence_number'];
            $param['business_licence_address'] = $_POST['business_licence_address'];
            $param['business_licence_start'] = $_POST['business_licence_start'];
            $param['business_licence_end'] = $_POST['business_licence_end'];
            $param['business_sphere'] = $_POST['business_sphere'];
            $param['business_licence_number_electronic'] = $this->upload_image('business_licence_number_electronic');
            $param['organization_code'] = $_POST['organization_code'];
            $param['organization_code_electronic'] = $this->upload_image('organization_code_electronic');
            $param['general_taxpayer'] = $this->upload_image('general_taxpayer');
            $this->step2_save_valid($param);
            $model_store_joinin = model('store_joinin');
            $joinin_info = $model_store_joinin->getOne(array('member_id' => core\session::get('member_id')));
            if (empty($joinin_info)) {
                $param['member_id'] = core\session::get('member_id');
                $model_store_joinin->save($param);
            } else {
                $model_store_joinin->modify($param, array('member_id' => core\session::get('member_id')));
            }
        }
        core\tpl::output('step', '2');
        core\tpl::output('sub_step', 'step2');
        core\tpl::showpage('store_joinin_apply');
        exit;
    }
    private function step2_save_valid($param)
    {
        $obj_validate = new lib\validate();
        $obj_validate->validateparam = array(array('input' => $param['company_name'], 'require' => 'true', 'validator' => 'Length', 'min' => '1', 'max' => '50', 'message' => '公司名称不能为空且必须小于50个字'), array('input' => $param['company_address'], 'require' => 'true', 'validator' => 'Length', 'min' => '1', 'max' => '50', 'message' => '公司地址不能为空且必须小于50个字'), array('input' => $param['company_address_detail'], 'require' => 'true', 'validator' => 'Length', 'min' => '1', 'max' => '50', 'message' => '公司详细地址不能为空且必须小于50个字'), array('input' => $param['company_registered_capital'], 'require' => 'true', 'validator' => 'Number', '注册资金不能为空且必须是数字'), array('input' => $param['contacts_name'], 'require' => 'true', 'validator' => 'Length', 'min' => '1', 'max' => '20', 'message' => '联系人姓名不能为空且必须小于20个字'), array('input' => $param['contacts_phone'], 'require' => 'true', 'validator' => 'Length', 'min' => '1', 'max' => '20', 'message' => '联系人电话不能为空'), array('input' => $param['contacts_email'], 'require' => 'true', 'validator' => 'email', 'message' => '电子邮箱不能为空'), array('input' => $param['business_licence_number'], 'require' => 'true', 'validator' => 'Length', 'min' => '1', 'max' => '20', 'message' => '营业执照号不能为空且必须小于20个字'), array('input' => $param['business_licence_address'], 'require' => 'true', 'validator' => 'Length', 'min' => '1', 'max' => '50', 'message' => '营业执照所在地不能为空且必须小于50个字'), array('input' => $param['business_licence_start'], 'require' => 'true', 'message' => '营业执照有效期不能为空'), array('input' => $param['business_licence_end'], 'require' => 'true', 'message' => '营业执照有效期不能为空'), array('input' => $param['business_licence_number_electronic'], 'require' => 'true', 'message' => '营业执照电子版不能为空'));
        $error = $obj_validate->validate();
        if ($error != '') {
            error($error);
        }
    }
    public function step3Op()
    {
        if (!empty($_POST)) {
            $param = array();
            $param['bank_account_name'] = $_POST['bank_account_name'];
            $param['bank_account_number'] = $_POST['bank_account_number'];
            $param['bank_name'] = $_POST['bank_name'];
            $param['bank_code'] = isset($_POST['bank_code']) ? $_POST['bank_code'] : '';
            $param['bank_address'] = $_POST['bank_address'];
            $param['bank_licence_electronic'] = $this->upload_image('bank_licence_electronic');
            if (!empty($_POST['is_settlement_account'])) {
                $param['is_settlement_account'] = 1;
                $param['settlement_bank_account_name'] = $_POST['bank_account_name'];
                $param['settlement_bank_account_number'] = $_POST['bank_account_number'];
                $param['settlement_bank_name'] = $_POST['bank_name'];
                $param['settlement_bank_code'] = isset($_POST['bank_code']) ? $_POST['bank_code'] : '';
                $param['settlement_bank_address'] = $_POST['bank_address'];
            } else {
                $param['is_settlement_account'] = 2;
                $param['settlement_bank_account_name'] = $_POST['settlement_bank_account_name'];
                $param['settlement_bank_account_number'] = $_POST['settlement_bank_account_number'];
                $param['settlement_bank_name'] = $_POST['settlement_bank_name'];
                $param['settlement_bank_code'] = isset($_POST['settlement_bank_code']) ? $_POST['settlement_bank_code'] : '';
                $param['settlement_bank_address'] = $_POST['settlement_bank_address'];
            }
            $param['tax_registration_certificate'] = $_POST['tax_registration_certificate'];
            $param['taxpayer_id'] = isset($_POST['taxpayer_id']) ? $_POST['taxpayer_id'] : '';
            $param['tax_registration_certificate_electronic'] = $this->upload_image('tax_registration_certificate_electronic');
            $this->step3_save_valid($param);
            $model_store_joinin = model('store_joinin');
            $model_store_joinin->modify($param, array('member_id' => core\session::get('member_id')));
        }
        // 商品分类
        $gc = model('goods_class');
        $gc_list = $gc->getGoodsClassListByParentId(0);
        core\tpl::output('gc_list', $gc_list);
        // 店铺等级
        $grade_list = rkcache('store_grade', true);
        // 附加功能
        if (!empty($grade_list) && is_array($grade_list)) {
            foreach ($grade_list as $key => $grade) {
                $sg_function = explode('|', $grade['sg_function']);
                if (!empty($sg_function[0]) && is_array($sg_function)) {
                    foreach ($sg_function as $key1 => $value) {
                        if ($value == 'editor_multimedia') {
							if(!isset($grade_list[$key]['function_str'])){
								$grade_list[$key]['function_str'] = '';
							}
                            $grade_list[$key]['function_str'] .= '富文本编辑器';
                        }
                    }
                } else {
                    $grade_list[$key]['function_str'] = '无';
                }
            }
        }
        core\tpl::output('grade_list', $grade_list);
        // 店铺分类
        $model_store = model('store_class');
        $store_class = $model_store->getStoreClassList(array(), '', false);
        core\tpl::output('store_class', $store_class);
        core\tpl::output('step', '3');
        core\tpl::output('sub_step', 'step3');
        core\tpl::showpage('store_joinin_apply');
        exit;
    }
    private function step3_save_valid($param)
    {
        $obj_validate = new lib\validate();
        $obj_validate->validateparam = array(array('input' => $param['bank_account_name'], 'require' => 'true', 'validator' => 'Length', 'min' => '1', 'max' => '50', 'message' => '银行开户名不能为空且必须小于50个字'), array('input' => $param['bank_account_number'], 'require' => 'true', 'validator' => 'Length', 'min' => '1', 'max' => '20', 'message' => '银行账号不能为空且必须小于20个字'), array('input' => $param['bank_name'], 'require' => 'true', 'validator' => 'Length', 'min' => '1', 'max' => '50', 'message' => '开户银行支行名称不能为空且必须小于50个字'), array('input' => $param['bank_address'], 'require' => 'true', '开户行所在地不能为空'), array('input' => $param['settlement_bank_account_name'], 'require' => 'true', 'validator' => 'Length', 'min' => '1', 'max' => '50', 'message' => '银行开户名不能为空且必须小于50个字'), array('input' => $param['settlement_bank_account_number'], 'require' => 'true', 'validator' => 'Length', 'min' => '1', 'max' => '20', 'message' => '银行账号不能为空且必须小于20个字'), array('input' => $param['settlement_bank_name'], 'require' => 'true', 'validator' => 'Length', 'min' => '1', 'max' => '50', 'message' => '开户银行支行名称不能为空且必须小于50个字'), array('input' => $param['settlement_bank_address'], 'require' => 'true', '开户行所在地不能为空'), array('input' => $param['tax_registration_certificate'], 'require' => 'true', 'validator' => 'Length', 'min' => '1', 'max' => '20', 'message' => '税务登记证号不能为空且必须小于20个字'), array('input' => $param['tax_registration_certificate_electronic'], 'require' => 'true', 'message' => '税务登记证号电子版不能为空'));
        $error = $obj_validate->validate();
        if ($error != '') {
            error($error);
        }
    }
    public function check_seller_name_existOp()
    {
        $condition = array();
        $condition['seller_name'] = $_GET['seller_name'];
        $model_seller = model('seller');
        $result = $model_seller->isSellerExist($condition);
        if ($result) {
            echo 'true';
        } else {
            echo 'false';
        }
    }
    public function step4Op()
    {
        $store_class_ids = array();
        $store_class_names = array();
        if (!empty($_POST['store_class_ids'])) {
            foreach ($_POST['store_class_ids'] as $value) {
                $store_class_ids[] = $value;
            }
        }
        if (!empty($_POST['store_class_names'])) {
            foreach ($_POST['store_class_names'] as $value) {
                $store_class_names[] = $value;
            }
        }
        // 取最小级分类最新分佣比例
        $sc_ids = array();
        foreach ($store_class_ids as $v) {
            $v = explode(',', trim($v, ','));
            if (!empty($v) && is_array($v)) {
                $sc_ids[] = end($v);
            }
        }
        if (!empty($sc_ids)) {
            $store_class_commis_rates = array();
            $goods_class_list = model('goods_class')->getGoodsClassListByIds($sc_ids);
            if (!empty($goods_class_list) && is_array($goods_class_list)) {
                $sc_ids = array();
                foreach ($goods_class_list as $v) {
                    $store_class_commis_rates[] = $v['commis_rate'];
                }
            }
        }
        $param = array();
        $param['seller_name'] = $_POST['seller_name'];
        $param['store_name'] = $_POST['store_name'];
        $param['store_class_ids'] = serialize($store_class_ids);
        $param['store_class_names'] = serialize($store_class_names);
        $param['joinin_year'] = intval($_POST['joinin_year']);
        $param['joinin_state'] = STORE_JOIN_STATE_NEW;
        $param['store_class_commis_rates'] = implode(',', $store_class_commis_rates);
        // 取店铺等级信息
        $grade_list = rkcache('store_grade', true);
        if (!empty($grade_list[$_POST['sg_id']])) {
            $param['sg_id'] = $_POST['sg_id'];
            $param['sg_name'] = $grade_list[$_POST['sg_id']]['sg_name'];
            $param['sg_info'] = serialize(array('sg_price' => $grade_list[$_POST['sg_id']]['sg_price']));
        }
        // 取最新店铺分类信息
        $store_class_info = model('store_class')->getStoreClassInfo(array('sc_id' => intval($_POST['sc_id'])));
        if ($store_class_info) {
            $param['sc_id'] = $store_class_info['sc_id'];
            $param['sc_name'] = $store_class_info['sc_name'];
            $param['sc_bail'] = $store_class_info['sc_bail'];
        }
        // 店铺应付款
        $param['paying_amount'] = floatval($grade_list[$_POST['sg_id']]['sg_price']) * $param['joinin_year'] + floatval($param['sc_bail']);
        $this->step4_save_valid($param);
        $model_store_joinin = model('store_joinin');
        $model_store_joinin->modify($param, array('member_id' => core\session::get('member_id')));
        @header('location: index.php?act=store_joinin');
    }
    private function step4_save_valid($param)
    {
        $obj_validate = new lib\validate();
        $obj_validate->validateparam = array(array('input' => $param['store_name'], 'require' => 'true', 'validator' => 'Length', 'min' => '1', 'max' => '50', 'message' => '店铺名称不能为空且必须小于50个字'), array('input' => $param['sg_id'], 'require' => 'true', 'message' => '店铺等级不能为空'), array('input' => $param['sc_id'], 'require' => 'true', 'message' => '店铺分类不能为空'));
        $error = $obj_validate->validate();
        if ($error != '') {
            error($error);
        }
    }
    public function payOp()
    {
        if (!empty($this->joinin_detail['sg_info'])) {
            $store_grade_info = model('store_grade')->getOneGrade($this->joinin_detail['sg_id']);
            $this->joinin_detail['sg_price'] = $store_grade_info['sg_price'];
        } else {
            $this->joinin_detail['sg_info'] = @unserialize($this->joinin_detail['sg_info']);
            if (is_array($this->joinin_detail['sg_info'])) {
                $this->joinin_detail['sg_price'] = $this->joinin_detail['sg_info']['sg_price'];
            }
        }
        core\tpl::output('joinin_detail', $this->joinin_detail);
        core\tpl::output('step', '4');
        core\tpl::output('sub_step', 'pay');
        core\tpl::showpage('store_joinin_apply');
        exit;
    }
    public function pay_saveOp()
    {
        $param = array();
        $param['paying_money_certificate'] = $this->upload_image('paying_money_certificate');
        $param['paying_money_certificate_explain'] = $_POST['paying_money_certificate_explain'];
        $param['joinin_state'] = STORE_JOIN_STATE_PAY;
        if (empty($param['paying_money_certificate'])) {
            error('请上传付款凭证');
        }
        $model_store_joinin = model('store_joinin');
        $model_store_joinin->modify($param, array('member_id' => core\session::get('member_id')));
        header('location: index.php?act=store_joinin');
    }
    private function step4()
    {
        $model_store_joinin = model('store_joinin');
        $joinin_detail = $model_store_joinin->getOne(array('member_id' => core\session::get('member_id')));
        $joinin_detail['store_class_ids'] = unserialize($joinin_detail['store_class_ids']);
        $joinin_detail['store_class_names'] = unserialize($joinin_detail['store_class_names']);
        $joinin_detail['store_class_commis_rates'] = explode(',', $joinin_detail['store_class_commis_rates']);
        $joinin_detail['sg_info'] = unserialize($joinin_detail['sg_info']);
        core\tpl::output('joinin_detail', $joinin_detail);
    }
    private function show_join_message($message, $btn_next = FALSE, $step = '2')
    {
        core\tpl::output('joinin_message', $message);
        core\tpl::output('btn_next', $btn_next);
        core\tpl::output('step', $step);
        core\tpl::output('sub_step', 'step4');
        core\tpl::showpage('store_joinin_apply');
        exit;
    }
    private function upload_image($file)
    {
        $pic_name = '';
        $upload = new lib\uploadfile();
        $uploaddir = ATTACH_PATH . DS . 'store_joinin' . DS;
        $upload->set('default_dir', $uploaddir);
        $upload->set('allow_type', array('jpg', 'jpeg', 'gif', 'png'));
        if (!empty($_FILES[$file]['name'])) {
            $result = $upload->upfile($file);
            if ($result) {
                $pic_name = $upload->file_name;
                $upload->file_name = '';
            }
        }
        return $pic_name;
    }
    /**
     * 检查店铺名称是否存在
     *
     * @param        	
     *
     * @return
     *
     */
    public function checknameOp()
    {
        /**
         * 实例化卖家模型
         */
        $model_store = model('store');
        $store_name = $_GET['store_name'];
        $store_info = $model_store->getStoreInfo(array('store_name' => $store_name));
        if (!empty($store_info['store_name']) && $store_info['member_id'] != core\session::get('member_id')) {
            echo 'false';
        } else {
            echo 'true';
        }
    }
}