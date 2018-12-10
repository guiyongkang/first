<?php
/**
 * 平台充值卡
 **/
namespace admin\control;

use core;
use lib;
defined('SAFE_CONST') or exit('Access Invalid!');
class rechargecard extends SystemControl
{
    const EXPORT_SIZE = 100;
    public function __construct()
    {
        parent::__construct();
    }
    public function indexOp()
    {
        $model = model('rechargecard');
        $condition = array();
        if (isset($_GET['form_submit'])) {
            $sn = trim((string) $_GET['sn']);
            $batchflag = trim((string) $_GET['batchflag']);
            $state = trim((string) $_GET['state']);
            if (strlen($sn) > 0) {
                $condition['sn'] = array('like', "%{$sn}%");
                core\tpl::output('sn', $sn);
            }
            if (strlen($batchflag) > 0) {
                $condition['batchflag'] = array('like', "%{$batchflag}%");
                core\tpl::output('batchflag', $batchflag);
            }
            if ($state === '0' || $state === '1') {
                $condition['state'] = $state;
                core\tpl::output('state', $state);
            }
            if ($condition) {
                core\tpl::output('form_submit', 'ok');
            }
        }
        $cardList = $model->getRechargeCardList($condition, 20);
        core\tpl::output('card_list', $cardList);
        core\tpl::output('show_page', $model->showpage());
        core\tpl::showpage('rechargecard.index');
    }
    public function add_cardOp()
    {
        if (!chksubmit()) {
            core\tpl::showpage('rechargecard.add_card');
            return;
        }
        $denomination = (double) $_POST['denomination'];
        if ($denomination < 0.01) {
            error('面额不能小于0.01');
            return;
        }
        if ($denomination > 1000) {
            error('面额不能大于1000');
            return;
        }
        $snKeys = array();
        switch ($_POST['type']) {
            case '0':
                $total = (int) $_POST['total'];
                if ($total < 1 || $total > 9999) {
                    error('总数只能是1~9999之间的整数');
                    exit;
                }
                $prefix = (string) $_POST['prefix'];
                if (!preg_match('/^[0-9a-zA-Z]{0,16}$/', $prefix)) {
                    error('前缀只能是16字之内字母数字的组合');
                    exit;
                }
                while (count($snKeys) < $total) {
                    $snKeys[$prefix . md5(uniqid(mt_rand(), true))] = null;
                }
                break;
            case '1':
                $f = $_FILES['_textfile'];
                if (!$f || $f['error'] != 0) {
                    error('文件上传失败');
                    exit;
                }
                if (!is_uploaded_file($f['tmp_name'])) {
                    error('未找到已上传的文件');
                    exit;
                }
                foreach (file($f['tmp_name']) as $sn) {
                    $sn = trim($sn);
                    if (preg_match('/^[0-9a-zA-Z]{1,50}$/', $sn)) {
                        $snKeys[$sn] = null;
                    }
                }
                break;
            case '2':
                foreach (explode("\n", (string) $_POST['manual']) as $sn) {
                    $sn = trim($sn);
                    if (preg_match('/^[0-9a-zA-Z]{1,50}$/', $sn)) {
                        $snKeys[$sn] = null;
                    }
                }
                break;
            default:
                error('参数错误');
                exit;
        }
        $totalKeys = count($snKeys);
        if ($totalKeys < 1 || $totalKeys > 9999) {
            error('只能在一次操作中增加1~9999个充值卡号');
            exit;
        }
        if (empty($snKeys)) {
            error('请输入至少一个合法的卡号');
            exit;
        }
        $snOccupied = 0;
        $model = model('rechargecard');
        // chunk size = 50
        foreach (array_chunk(array_keys($snKeys), 50) as $snValues) {
            foreach ($model->getOccupiedRechargeCardSNsBySNs($snValues) as $sn) {
                $snOccupied++;
                unset($snKeys[$sn]);
            }
        }
        if (empty($snKeys)) {
            error('操作失败，所有新增的卡号都与已有的卡号冲突');
            exit;
        }
        $batchflag = $_POST['batchflag'];
        $adminName = $this->admin_info['name'];
        $ts = time();
        $snToInsert = array();
        foreach (array_keys($snKeys) as $sn) {
            $snToInsert[] = array('sn' => $sn, 'denomination' => $denomination, 'batchflag' => $batchflag, 'admin_name' => $adminName, 'tscreated' => $ts);
        }
        if (!$model->insertAll($snToInsert)) {
            error('操作失败');
            exit;
        }
        $countInsert = count($snToInsert);
        $this->log('新增' . $countInsert . '张充值卡（面额￥' . $denomination . '，批次标识“' . $batchflag . '”）');
        $msg = '操作成功';
        if ($snOccupied > 0) {
            $msg .= '有 ' . $snOccupied . ' 个卡号与已有的未使用卡号冲突';
        }
        error($msg, urlAdmin('rechargecard', 'index'));
    }
    public function del_cardOp()
    {
        if (empty($_GET['id'])) {
            error('参数错误');
        }
        model('rechargecard')->delRechargeCardById($_GET['id']);
        $this->log('删除充值卡（#ID: ' . $_GET['id'] . '）');
        success('操作成功', getReferer());
    }
    public function del_card_batchOp()
    {
        if (empty($_POST['ids']) || !is_array($_POST['ids'])) {
            error('参数错误');
        }
        model('rechargecard')->delRechargeCardById($_POST['ids']);
        $count = count($_POST['ids']);
        $this->log("删除{$count}张充值卡");
        success('操作成功', getReferer());
    }
    /**
     * 导出
     */
    public function export_step1Op()
    {
        $model = model('rechargecard');
        $condition = array();
        if (isset($_GET['form_submit'])) {
            $sn = trim((string) $_GET['sn']);
            $batchflag = trim((string) $_GET['batchflag']);
            $state = trim((string) $_GET['state']);
            if (strlen($sn) > 0) {
                $condition['sn'] = array('like', '%' . $sn . '%');
                core\tpl::output('sn', $sn);
            }
            if (strlen($batchflag) > 0) {
                $condition['batchflag'] = array('like', '%' . $batchflag. '%');
                core\tpl::output('batchflag', $batchflag);
            }
            if ($state === '0' || $state === '1') {
                $condition['state'] = $state;
                core\tpl::output('state', $state);
            }
            if ($condition) {
                core\tpl::output('form_submit', 'ok');
            }
        }
        if (!empty($_GET['curpage']) && !is_numeric($_GET['curpage'])) {
            $count = $model->getRechargeCardCount($condition);
            $array = array();
            if ($count > self::EXPORT_SIZE) {
                //显示下载链接
                $page = ceil($count / self::EXPORT_SIZE);
                for ($i = 1; $i <= $page; $i++) {
                    $limit1 = ($i - 1) * self::EXPORT_SIZE + 1;
                    $limit2 = $i * self::EXPORT_SIZE > $count ? $count : $i * self::EXPORT_SIZE;
                    $array[$i] = $limit1 . ' ~ ' . $limit2;
                }
                core\tpl::output('list', $array);
                core\tpl::output('murl', 'index.php?act=rechargecard&op=index');
                core\tpl::showpage('export.excel');
                return;
            } else {
                //如果数量小，直接下载
                $data = $model->getRechargeCardList($condition, self::EXPORT_SIZE);
                $this->createExcel($data);
            }
        } else {
            //下载
            $limit1 = ((!empty($_GET['curpage']) ? $_GET['curpage'] : 1) - 1) * self::EXPORT_SIZE;
            $limit2 = self::EXPORT_SIZE;
            $data = $model->getRechargeCardList($condition, 20, $limit1 . ',' . $limit2);
            $this->createExcel($data);
        }
    }
    /**
     * 生成excel
     *
     * @param array $data
     */
    private function createExcel($data = array())
    {
        core\language::read('export');
        $excel_obj = new lib\excel();
        $excel_data = array();
        //设置样式
        $excel_obj->setStyle(array('id' => 's_title', 'Font' => array('FontName' => '宋体', 'Size' => '12', 'Bold' => '1')));
        //header
        $excel_data[0][] = array('styleid' => 's_title', 'data' => '充值卡卡号');
        $excel_data[0][] = array('styleid' => 's_title', 'data' => '批次标识');
        $excel_data[0][] = array('styleid' => 's_title', 'data' => '面额(元)');
        $excel_data[0][] = array('styleid' => 's_title', 'data' => '发布管理员');
        $excel_data[0][] = array('styleid' => 's_title', 'data' => '发布时间');
        $excel_data[0][] = array('styleid' => 's_title', 'data' => '领取人');
        //data
        foreach ((array) $data as $k => $v) {
            $tmp = array();
            $tmp[] = array('data' => "\t" . $v['sn']);
            $tmp[] = array('data' => "\t" . $v['batchflag']);
            $tmp[] = array('data' => "\t" . $v['denomination']);
            $tmp[] = array('data' => "\t" . $v['admin_name']);
            $tmp[] = array('data' => "\t" . date('Y-m-d H:i:s', $v['tscreated']));
            if ($v['state'] == 1 && $v['member_id'] > 0 && $v['tsused'] > 0) {
                $tmp[] = array('data' => "\t" . $v['member_name']);
            } else {
                $tmp[] = array('data' => "\t-");
            }
            $excel_data[] = $tmp;
        }
        $excel_data = $excel_obj->charset($excel_data, CHARSET);
        $excel_obj->addArray($excel_data);
        $excel_obj->addWorksheet($excel_obj->charset('充值卡', CHARSET));
        $excel_obj->generateXML($excel_obj->charset('充值卡', CHARSET) . $_GET['curpage'] . '-' . date('Y-m-d-H', time()));
    }
}