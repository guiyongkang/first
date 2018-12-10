<?php
/**
 * 红包管理
 *
 */
namespace admin\control;

use core;
use lib;
defined('SAFE_CONST') or exit('Access Invalid!');
class red_packet extends SystemControl
{
    public function __construct()
    {
        parent::__construct();
        core\language::read('red_packet');
    }
    /**
     * 红包列表
     */
    public function indexOp()
    {
        $model = model();
        // 条件
        $condition_arr = array();
        // 名称
        if (!empty($_GET['packet_name'])) {
            $condition_arr['packet_name'] = array("like", "%{$_GET['packet_name']}%");
        }
        // 状态
        if (!empty($_GET['state'])) {
            $condition_arr['state'] = $_GET['state'];
        }
        // 活动列表
        $list = $model->table('red_packet')->where($condition_arr)->page('10')->order('id desc')->select();
        // 输出
        core\tpl::output('show_page', $model->showpage());
        core\tpl::output('list', $list);
        core\tpl::showpage('red_packet.index');
    }
    /**
     * 未抽取红包记录
     */
    public function listOp()
    {
        $model = model();
        $id = $_GET['id'];
        $packet = $model->table('red_packet')->where(array('id' => $id))->find();
        if (empty($packet)) {
            error('该红包活动不存在');
        }
        $list = $model->table('red_packet_list')->where(array('packet_id' => $id))->page('20')->select();
        $lists = $model->table('red_packet_list')->where(array('packet_id' => $id))->limit('100000')->select();
        if (!empty($lists)) {
            $total = 0;
            foreach ($lists as $k => $v) {
                $total += $v['packet_price'];
            }
        }
        core\tpl::output('total', $total);
        core\tpl::output('number', count($lists));
        core\tpl::output('list', $list);
        core\tpl::output('show_page', $model->showpage());
        core\tpl::showpage('red_packet.list');
    }
    /**
     * 已使用红包记录
     */
    public function uselistOp()
    {
        $model = model();
        $id = $_GET['id'];
        $packet = $model->table('red_packet')->where(array('id' => $id))->find();
        if (empty($packet)) {
            error('该红包活动不存在');
        }
        $condition = array();
        $condition['packet_id'] = $id;
        if (!empty($_GET['state'])) {
            $condition['is_use'] = $_GET['state'];
        }
        $list = $model->table('red_packet_rec')->where($condition)->page('20')->select();
        $lists = $model->table('red_packet_rec')->where($condition)->limit('100000')->select();
        if (!empty($lists)) {
            $total = 0;
            foreach ($lists as $k => $v) {
                $total += $v['packet_price'];
            }
        }
        core\tpl::output('id', $id);
        core\tpl::output('total', $total);
        core\tpl::output('number', count($lists));
        core\tpl::output('list', $list);
        core\tpl::output('show_page', $model->showpage());
        core\tpl::showpage('red_packet.uselist');
    }
    /**
     * 新建红包活动
     */
    public function newOp()
    {
        $model = model();
        // 新建处理
        if (!chksubmit()) {
            core\tpl::showpage('red_packet.add');
            exit;
        }
        // 提交表单
        $obj_validate = new lib\validate();
        $validate_arr[] = array("input" => $_POST["packet_name"], "require" => "true", "message" => "红包名称不能为空");
        $validate_arr[] = array("input" => $_POST["start_time"], "require" => "true", "message" => "开始时间不能为空");
        $validate_arr[] = array("input" => $_POST["end_time"], "require" => "true", "message" => "结束时间不能为空");
        $validate_arr[] = array("input" => $_POST["packet_number"], "require" => "true", "message" => "红包数量不能为空");
        $validate_arr[] = array("input" => $_POST["packet_amount"], "require" => "true", "message" => "红包总金额不能为空");
        $validate_arr[] = array("input" => $_POST["win_rate"], "require" => "true", "message" => "中奖机率不能为空");
        $obj_validate->validateparam = $validate_arr;
        $error = $obj_validate->validate();
        if ($error != '') {
            error(core\language::get('error') . $error);
        }
        if ($_POST["packet_number"] > $_POST["packet_amount"]) {
            error('红包的数量必须大于红包金额');
        }
        /*
         * if(empty($_POST['valid_date']) && empty($_POST['valid_date2'])){
         * error('必须填写一项红包有效期');
         * }
         * if(!empty($_POST['valid_date']) && !empty($_POST['valid_date2'])){
         * error('只能填写一项红包有效期');
         * }
         * if(!empty($_POST['valid_date'])){
         * $valid_date = strtotime(trim($_POST['valid_date']));
         * }
         * if(!empty($_POST['valid_date2'])){
         * if($_POST['valid_date2'] <= 0){
         * error('红包有效天数不能小于或等于0');
         * }
         * $valid_date2 = $_POST['valid_date2'];
         * }
         */
        // 保存
        $input = array();
        $input['packet_name'] = trim($_POST['packet_name']);
        $input['start_time'] = strtotime(trim($_POST['start_time']));
        $input['end_time'] = strtotime(trim($_POST['end_time']));
        $input['packet_number'] = trim($_POST['packet_number']);
        $input['packet_amount'] = trim($_POST['packet_amount']);
        $input['valid_date'] = 1;
        // $valid_date;
        $input['valid_date2'] = 1;
        // $valid_date2;
        $input['win_rate'] = trim($_POST['win_rate']);
        $input['packet_descript'] = '';
        // trim($_POST['packet_descript']);
        $input['add_time'] = time();
        $result = $model->table('red_packet')->insert($input);
        if ($result) {
            $bonus_total = $input['packet_amount'];
            $bonus_count = $input['packet_number'];
            $average = $input['packet_amount'] / $input['packet_number'];
            $bonus_max = $average * 2;
            // 此算法要求设置的最大值要大于平均值
            $bonus_min = 1;
            // 判断每个红包金额累加与红包总金额是否相等，否则重新计算
            do {
                $result_bonus = $this->getBonus($bonus_total, $bonus_count, $bonus_max, $bonus_min);
                $total_money = 0;
                $arr = array();
                foreach ($result_bonus as $v) {
                    $total_money += $v;
                }
            } while ($total_money != $bonus_total);
            foreach ($result_bonus as $val) {
                $model->table('red_packet_list')->insert(array('packet_id' => $result, 'packet_price' => $val));
            }
            success(core\language::get('nc_common_op_succ'), 'index.php?act=red_packet');
        } else {
            error(core\language::get('nc_common_op_fail'));
        }
    }
    /**
     * 删除活动
     */
    public function delOp()
    {
        $model = model();
        $id = $_GET['id'];
        $red_packet = $model->table('red_packet')->where(array('id' => $id))->find();
        if (empty($id) || empty($red_packet)) {
            error('该红包活动不存在');
        }
        $result = $model->table('red_packet')->where(array('id' => $id))->delete();
        if ($result) {
            $model->table('red_packet_list')->where(array('packet_id' => $id))->delete();
            success(core\language::get('nc_common_op_succ'), 'index.php?act=red_packet');
        } else {
            error(core\language::get('nc_common_op_fail'));
        }
    }
    /**
     * 关闭/开启活动
     */
    public function stateOp()
    {
        $model = model();
        $state = $_GET['type'];
        $id = $_GET['id'];
        if (!in_array($state, array('close', 'open')) || empty($id)) {
            error('参数错误');
        }
        $red_packet = $model->table('red_packet')->where(array('id' => $id))->find();
        if (empty($id) || empty($red_packet)) {
            error('该红包活动不存在');
        }
        if ($state == 'open') {
            $result = $model->table('red_packet')->where(array('id' => $id))->update(array('state' => 1));
        }
        if ($state == 'close') {
            $result = $model->table('red_packet')->where(array('id' => $id))->update(array('state' => 2));
        }
        if ($result) {
            success(core\language::get('nc_common_op_succ'), 'index.php?act=red_packet');
        } else {
            error(core\language::get('nc_common_op_fail'));
        }
    }
    /**
     * 编辑活动/保存编辑活动
     */
    public function editOp()
    {
        $model = model();
        if (!chksubmit()) {
            if (empty($_GET['id'])) {
                error('参数错误');
            }
            $red_packet = $model->table('red_packet')->where(array('id' => $_GET['id']))->find();
            core\tpl::output('red_packet', $red_packet);
            core\tpl::showpage('red_packet.edit');
            exit;
        }
        // 提交表单
        $obj_validate = new lib\validate();
        $validate_arr[] = array("input" => $_POST["packet_name"], "require" => "true", "message" => "红包名称不能为空");
        $validate_arr[] = array("input" => $_POST["start_time"], "require" => "true", "message" => "开始时间不能为空");
        $validate_arr[] = array("input" => $_POST["end_time"], "require" => "true", "message" => "结束时间不能为空");
        $validate_arr[] = array("input" => $_POST["packet_number"], "require" => "true", "message" => "红包数量不能为空");
        $validate_arr[] = array("input" => $_POST["packet_amount"], "require" => "true", "message" => "红包总金额不能为空");
        $validate_arr[] = array("input" => $_POST["win_rate"], "require" => "true", "message" => "中奖机率不能为空");
        $obj_validate->validateparam = $validate_arr;
        $error = $obj_validate->validate();
        if ($error != '') {
            error(core\language::get('error') . $error);
        }
        // 构造更新内容
        $update = array();
        $update['packet_name'] = trim($_POST['packet_name']);
        $update['start_time'] = strtotime(trim($_POST['start_time']));
        $update['end_time'] = strtotime(trim($_POST['end_time']));
        $update['packet_number'] = trim($_POST['packet_number']);
        $update['packet_amount'] = trim($_POST['packet_amount']);
        $update['win_rate'] = trim($_POST['win_rate']);
        $update['packet_descript'] = '';
        // trim($_POST['packet_descript']);
        $result = $model->table('red_packet')->where(array('id' => $_GET['id']))->update($update);
        if ($result) {
            success(core\language::get('nc_common_op_succ'), 'index.php?act=red_packet');
        } else {
            error(core\language::get('nc_common_op_fail'));
        }
    }
    /**
     * 求一个数的平方
     * 
     * @param
     *        	$n
     */
    public function sqr($n)
    {
        return $n * $n;
    }
    /**
     * 生产min和max之间的随机数，但是概率不是平均的，从min到max方向概率逐渐加大。
     * 先平方，然后产生一个平方值范围内的随机数，再开方，这样就产生了一种“膨胀”再“收缩”的效果。
     */
    public function xRandom($bonus_min, $bonus_max)
    {
        $sqr = intval($this->sqr($bonus_max - $bonus_min));
        $rand_num = rand(0, $sqr - 1);
        return intval(sqrt($rand_num));
    }
    /**
     *
     * @param $bonus_total 红包总额        	
     * @param $bonus_count 红包个数        	
     * @param $bonus_max 每个小红包的最大额        	
     * @param $bonus_min 每个小红包的最小额        	
     * @return 存放生成的每个小红包的值的一维数组
     */
    public function getBonus($bonus_total, $bonus_count, $bonus_max, $bonus_min)
    {
        $result = array();
        $average = $bonus_total / $bonus_count;
        $a = $average - $bonus_min;
        $b = $bonus_max - $bonus_min;
        //
        // 这样的随机数的概率实际改变了，产生大数的可能性要比产生小数的概率要小。
        // 这样就实现了大部分红包的值在平均数附近。大红包和小红包比较少。
        $range1 = $this->sqr($average - $bonus_min);
        $range2 = $this->sqr($bonus_max - $average);
        for ($i = 0; $i < $bonus_count; $i++) {
            // 因为小红包的数量通常是要比大红包的数量要多的，因为这里的概率要调换过来。
            // 当随机数>平均值，则产生小红包
            // 当随机数<平均值，则产生大红包
            if (rand($bonus_min, $bonus_max) > $average) {
                // 在平均线上减钱
                $temp = $bonus_min + $this->xRandom($bonus_min, $average);
                $result[$i] = $temp;
                $bonus_total -= $temp;
            } else {
                // 在平均线上加钱
                $temp = $bonus_max - $this->xRandom($average, $bonus_max);
                $result[$i] = $temp;
                $bonus_total -= $temp;
            }
        }
        // 如果还有余钱，则尝试加到小红包里，如果加不进去，则尝试下一个。
        while ($bonus_total > 0) {
            for ($i = 0; $i < $bonus_count; $i++) {
                if ($bonus_total > 0 && $result[$i] < $bonus_max) {
                    $result[$i]++;
                    $bonus_total--;
                }
            }
        }
        // 如果钱是负数了，还得从已生成的小红包中抽取回来
        while ($bonus_total < 0) {
            for ($i = 0; $i < $bonus_count; $i++) {
                if ($bonus_total < 0 && $result[$i] > $bonus_min) {
                    $result[$i]--;
                    $bonus_total++;
                }
            }
        }
        return $result;
    }
}