<?php
/**
 * 快递公司
 **/
namespace admin\control;

use core;
use lib;
defined('SAFE_CONST') or exit('Access Invalid!');
class express extends SystemControl
{
    public function __construct()
    {
        parent::__construct();
        core\language::read('express');
    }
    public function indexOp()
    {
        $lang = core\language::getLangContent();
        $model = model('express');
        if (preg_match('/^[A-Z]$/', isset($_GET['letter']) ? $_GET['letter'] : '')) {
            $model->where(array('e_letter' => $_GET['letter']));
        }
        $list = $model->page(10)->order('e_order,e_state desc,id')->select();
        core\tpl::output('page', $model->showpage());
        core\tpl::output('list', $list);
        core\tpl::showpage('express.index');
    }
    /**
     * ajax操作
     */
    public function ajaxOp()
    {
        switch ($_GET['branch']) {
            case 'state':
                $model_brand = model('express');
                $update_array = array();
                $update_array['id'] = intval($_GET['id']);
                $update_array[$_GET['column']] = trim($_GET['value']);
                $model_brand->update($update_array);
                dkcache('express');
                $this->log(lang('nc_edit,express_name,express_state') . '[ID:' . intval($_GET['id']) . ']', 1);
                echo 'true';
                exit;
                break;
            case 'order':
                $_GET['value'] = $_GET['value'] == 0 ? 2 : 1;
                $model_brand = model('express');
                $update_array = array();
                $update_array['id'] = intval($_GET['id']);
                $update_array[$_GET['column']] = trim($_GET['value']);
                $model_brand->update($update_array);
                dkcache('express');
                $this->log(lang('nc_edit,express_name,express_state') . '[ID:' . intval($_GET['id']) . ']', 1);
                echo 'true';
                exit;
                break;
            case 'e_zt_state':
                $model_brand = model('express');
                $update_array = array();
                $update_array['id'] = intval($_GET['id']);
                $update_array[$_GET['column']] = trim($_GET['value']);
                $model_brand->update($update_array);
                dkcache('express');
                $this->log(lang('nc_edit,express_name,express_state') . '[ID:' . intval($_GET['id']) . ']', 1);
                echo 'true';
                exit;
                break;
        }
        dkcache('express');
    }
}