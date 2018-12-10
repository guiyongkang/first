<?php
/**
 * 手机专题
 *
 */
namespace admin\control;

use core;
use lib;
defined('SAFE_CONST') or exit('Access Invalid!');
class mb_special extends SystemControl
{
    public function __construct()
    {
        parent::__construct();
    }
    /**
     * 专题列表
     */
    public function special_listOp()
    {
        $model_mb_special = model('mb_special');
		$array = array();
        $mb_special_list = $model_mb_special->getMbSpecialList($array, 10);
        core\tpl::output('list', $mb_special_list);
        core\tpl::output('page', $model_mb_special->showpage(2));
        $this->show_menu('special_list');
        core\tpl::showpage('mb_special.list');
    }
    /**
     * 保存专题
     */
    public function special_saveOp()
    {
        $model_mb_special = model('mb_special');
        $param = array();
        $param['special_desc'] = $_POST['special_desc'];
        $result = $model_mb_special->addMbSpecial($param);
        if ($result) {
            $this->log('添加手机专题' . '[ID:' . $result . ']', 1);
            success(lang('nc_common_save_succ'), urlAdmin('mb_special', 'special_list'));
        } else {
            $this->log('添加手机专题' . '[ID:' . $result . ']', 0);
            error(lang('nc_common_save_fail'), urlAdmin('mb_special', 'special_list'));
        }
    }
    /**
     * 编辑专题描述 
     */
    public function update_special_descOp()
    {
        $model_mb_special = model('mb_special');
        $param = array();
        $param['special_desc'] = $_GET['value'];
        $result = $model_mb_special->editMbSpecial($param, $_GET['id']);
        $data = array();
        if ($result) {
            $this->log('保存手机专题' . '[ID:' . $result . ']', 1);
            $data['result'] = true;
        } else {
            $this->log('保存手机专题' . '[ID:' . $result . ']', 0);
            $data['result'] = false;
            $data['message'] = '保存失败';
        }
        echo json_encode($data);
        die;
    }
    /**
     * 删除专题
     */
    public function special_delOp()
    {
        $model_mb_special = model('mb_special');
        $result = $model_mb_special->delMbSpecialByID($_POST['special_id']);
        if ($result) {
            $this->log('删除手机专题' . '[ID:' . $_POST['special_id'] . ']', 1);
            success(lang('nc_common_del_succ'), urlAdmin('mb_special', 'special_list'));
        } else {
            $this->log('删除手机专题' . '[ID:' . $_POST['special_id'] . ']', 0);
            error(lang('nc_common_del_fail'), urlAdmin('mb_special', 'special_list'));
        }
    }
    /**
     * 编辑首页
     */
    public function index_editOp()
    {
        $model_mb_special = model('mb_special');
        $special_item_list = $model_mb_special->getMbSpecialItemListByID($model_mb_special::INDEX_SPECIAL_ID);
        core\tpl::output('list', $special_item_list);
        core\tpl::output('page', $model_mb_special->showpage(2));
        core\tpl::output('module_list', $model_mb_special->getMbSpecialModuleList());
        core\tpl::output('special_id', $model_mb_special::INDEX_SPECIAL_ID);
        $this->show_menu('index_edit');
        core\tpl::showpage('mb_special_item.list');
    }
    /**
     * 编辑专题
     */
    public function special_editOp()
    {
        $model_mb_special = model('mb_special');
        $special_item_list = $model_mb_special->getMbSpecialItemListByID($_GET['special_id']);
        core\tpl::output('list', $special_item_list);
        core\tpl::output('page', $model_mb_special->showpage(2));
        core\tpl::output('module_list', $model_mb_special->getMbSpecialModuleList());
        core\tpl::output('special_id', $_GET['special_id']);
        $this->show_menu('special_item_list');
        core\tpl::showpage('mb_special_item.list');
    }
    /**
     * 专题项目添加
     */
    public function special_item_addOp()
    {
        $model_mb_special = model('mb_special');
        $param = array();
        $param['special_id'] = $_POST['special_id'];
        $param['item_type'] = $_POST['item_type'];
        //广告只能添加一个
        if ($param['item_type'] == 'adv_list') {
            $result = $model_mb_special->isMbSpecialItemExist($param);
            if ($result) {
                echo json_encode(array('error' => '广告条板块只能添加一个'));
                die;
            }
        }
        //推荐只能添加一个
        if ($param['item_type'] == 'goods1') {
            $result = $model_mb_special->isMbSpecialItemExist($param);
            if ($result) {
                echo json_encode(array('error' => '限时板块只能添加一个'));
                die;
            }
        }
        //团购只能添加一个
        if ($param['item_type'] == 'goods2') {
            $result = $model_mb_special->isMbSpecialItemExist($param);
            if ($result) {
                echo json_encode(array('error' => '团购板块只能添加一个'));
                die;
            }
        }
        //end
        $item_info = $model_mb_special->addMbSpecialItem($param);
        if ($item_info) {
            echo json_encode($item_info);
            die;
        } else {
            echo json_encode(array('error' => '添加失败'));
            die;
        }
    }
    /**
     * 专题项目删除
     */
    public function special_item_delOp()
    {
        $model_mb_special = model('mb_special');
        $condition = array();
        $condition['item_id'] = $_POST['item_id'];
        $result = $model_mb_special->delMbSpecialItem($condition, $_POST['special_id']);
        if ($result) {
            echo json_encode(array('message' => '删除成功'));
            die;
        } else {
            echo json_encode(array('error' => '删除失败'));
            die;
        }
    }
    /**
     * 专题项目编辑
     */
    public function special_item_editOp()
    {
        $model_mb_special = model('mb_special');
        $theitemid = $_GET['item_id'];
        $item_info = $model_mb_special->getMbSpecialItemInfoByID($theitemid);
        core\tpl::output('item_info', $item_info);
        if ($item_info['special_id'] == 0) {
            $this->show_menu('index_edit');
        } else {
            $this->show_menu('special_item_list');
        }
        //2015推荐 2016团购
        if ($theitemid == 2015) {
            core\tpl::showpage('mb_special_item.edit1');
        } else {
            if ($theitemid == 2016) {
                core\tpl::showpage('mb_special_item.edit2');
            }
        }
        core\tpl::showpage('mb_special_item.edit');
    }
    /**
     * 专题项目保存
     */
    public function special_item_saveOp()
    {
        $model_mb_special = model('mb_special');
        $result = $model_mb_special->editMbSpecialItemByID(array('item_data' => $_POST['item_data']), $_POST['item_id'], $_POST['special_id']);
        if ($result) {
            if ($_POST['special_id'] == $model_mb_special::INDEX_SPECIAL_ID) {
                success(lang('nc_common_save_succ'), urlAdmin('mb_special', 'index_edit'));
            } else {
                success(lang('nc_common_save_succ'), urlAdmin('mb_special', 'special_edit', array('special_id' => $_POST['special_id'])));
            }
        } else {
            success(lang('nc_common_save_succ'), '');
        }
    }
    /**
     * 图片上传
     */
    public function special_image_uploadOp()
    {
        $data = array();
        if (!empty($_FILES['special_image']['name'])) {
            $prefix = 's' . $_POST['special_id'];
            $upload = new lib\uploadfile();
            $upload->set('default_dir', ATTACH_MOBILE . DS . 'special' . DS . $prefix);
            $upload->set('fprefix', $prefix);
            $upload->set('allow_type', array('gif', 'jpg', 'jpeg', 'png'));
            $result = $upload->upfile('special_image');
            if (!$result) {
                $data['error'] = $upload->error;
            }
            $data['image_name'] = $upload->file_name;
            $data['image_url'] = getMbSpecialImageUrl($data['image_name']);
        }
        echo json_encode($data);
    }
    /**
     * 商品列表
     */
    public function goods_listOp()
    {
        $keyw = $_GET['keyword'];
        //SAFE_CONST
        $condition = array();
        $model_true_goods = model('goods');
        if ($keyw == '2015') {
            $model_goods = model('p_xianshi_goods');
            $condition['goods_name'] = array('like', '%%');
            $goods_id_list = $model_goods->getXianshiGoodsExtendIds($condition);
            $goods_list = $model_true_goods->getGoodsOnlineListAndPromotionByIdArray($goods_id_list);
            core\tpl::output('goods_list', $goods_list);
            core\tpl::output('show_page', $model_true_goods->showpage());
            core\tpl::showpage('mb_special_widget.goods1', 'null_layout');
        } else {
            if ($keyw == '2016') {
                $model_goods_ids = model('groupbuy');
                $condition['goods_name'] = array('like', '%%');
                $goods_list_arr = $model_goods_ids->getGroupbuyGoodsExtendIds($condition);
                $goods_list = $model_true_goods->getGoodsOnlineListAndPromotionByIdArray($goods_list_arr);
                core\tpl::output('goods_list', $goods_list);
                core\tpl::output('show_page', $model_true_goods->showpage());
                core\tpl::showpage('mb_special_widget.goods2', 'null_layout');
            } else {
                $model_goods = model('goods');
                $condition['goods_name'] = array('like', '%' . $_GET['keyword'] . '%');
                $goods_list = $model_goods->getGoodsOnlineList($condition, 'goods_id,goods_name,goods_promotion_price,goods_image', 10);
                core\tpl::output('goods_list', $goods_list);
                core\tpl::output('show_page', $model_goods->showpage());
                core\tpl::showpage('mb_special_widget.goods', 'null_layout');
            }
        }
    }
    /**
     * 更新项目排序
     */
    public function update_item_sortOp()
    {
        $item_id_string = $_POST['item_id_string'];
        $special_id = $_POST['special_id'];
        if (!empty($item_id_string)) {
            $model_mb_special = model('mb_special');
            $item_id_array = explode(',', $item_id_string);
            $index = 0;
            foreach ($item_id_array as $item_id) {
                $result = $model_mb_special->editMbSpecialItemByID(array('item_sort' => $index), $item_id, $special_id);
                $index++;
            }
        }
        $data = array();
        $data['message'] = '操作成功';
        echo json_encode($data);
    }
    /**
     * 更新项目启用状态
     */
    public function update_item_usableOp()
    {
        $model_mb_special = model('mb_special');
        $result = $model_mb_special->editMbSpecialItemUsableByID($_POST['usable'], $_POST['item_id'], $_POST['special_id']);
        $data = array();
        if ($result) {
            $data['message'] = '操作成功';
        } else {
            $data['error'] = '操作失败';
        }
        echo json_encode($data);
    }
	//首页模块管理
	public function index_moduleOp()
	{
		$index_module = model('index_module');
		if (chksubmit()) {//删除
			if (empty($_GET['id'])) {
				error('缺少参数');
			}
			$id = array();
			if (is_array($_GET['id'])) {
				$id = $_GET['id'];
			} else {
				$id[] = intval($_GET['id']);
			}
			$array_intersect = array_intersect($id, array(1,2,3,4,5,6,7,8));
			if(count($array_intersect)){
				error('前8项数据不能删除！');
			}
			$condition = array();
			$condition['id'] = array('in', $id);
			$flag = $index_module->where($condition)->delete();
			if($flag){
				success('删除成功！');
			}else{
				error('删除失败！');
			}
		}
		$condition = array();
        $list = $index_module->field('*')->where($condition)->order('sort asc,id asc')->select();
        core\tpl::output('list', $list);
        core\tpl::showpage('index_module');
	}
	public function index_module_addOp()
	{
		$index_module = model('index_module');
		if (chksubmit()) {
			$data = array();
			$data['name'] = $_POST['name'];
			$data['url'] = $_POST['url'];
			$data['bg_color'] = $_POST['bg_color'];
			$data['sort'] = $_POST['sort'];
			$data['status'] = $_POST['state'];
			$flag = $index_module->insert($data);
			if($flag){
				if (!empty($_FILES['bg_img']['name'])) {
					//上传图片
					$upload = new lib\uploadfile();
					$upload->set('default_dir', ATTACH_COMMON . DS . 'index_icon');
					$type = substr(strrchr($_FILES['bg_img']['name'], '.'), 1);
					$upload->set('file_name', 'index_module_' . $flag . '.' . $type);
					$result = $upload->upfile('bg_img');
					if($result) {
						$index_module->where(array('id'=>$flag))->update(array('bg_img'=>$upload->file_name));
					}
				}
			}
			if($flag){
				success('插入成功！');
			}else{
				error('插入失败！');
			}
		}
        core\tpl::showpage('index_module_add');
	}
	public function index_module_editOp()
	{
		$index_module = model('index_module');
		if (chksubmit()) {
			$condition = $data = array();
			$condition['id'] = $_POST['id'];
			$data['name'] = $_POST['name'];
			$data['url'] = $_POST['url'];
			$data['bg_color'] = $_POST['bg_color'];
			$data['sort'] = $_POST['sort'];
			$data['status'] = $_POST['state'];
			if (!empty($_FILES['bg_img']['name'])) {
				//上传图片
				$upload = new lib\uploadfile();
				$upload->set('default_dir', ATTACH_COMMON . DS . 'index_icon');
				$type = substr(strrchr($_FILES['bg_img']['name'], '.'), 1);
				$upload->set('file_name', 'index_module_' . $_POST['id'] . '.' . $type);
				$result = $upload->upfile('bg_img');
				if($result) {
					$data['bg_img'] = $upload->file_name;
				}
			}
			$flag = $index_module->where($condition)->update($data);
			if($flag){
				success('修改成功！');
			}else{
				error('修改失败！');
			}
		}
		$condition = array();
		$condition['id'] = intval($_GET['id']);
        $info = $index_module->field('*')->where($condition)->find();
        core\tpl::output('info', $info);
        core\tpl::showpage('index_module_edit');
	}
    /**
     * 页面内导航菜单
     * @param string 	$menu_key	当前导航的menu_key
     * @param array 	$array		附加菜单
     * @return
     */
    private function show_menu($menu_key = '')
    {
        $menu_array = array();
        if ($menu_key == 'index_edit') {
            $menu_array[] = array('menu_key' => 'index_edit', 'menu_name' => '编辑', 'menu_url' => 'javascript:;');
			$menu_array[] = array('menu_key' => 'index_module_edit', 'menu_name' => '首页板块', 'menu_url' => urlAdmin('mb_special', 'index_module'));
        } else {
            $menu_array[] = array('menu_key' => 'special_list', 'menu_name' => '列表', 'menu_url' => urlAdmin('mb_special', 'special_list'));
        }
        if ($menu_key == 'special_item_list') {
            $menu_array[] = array('menu_key' => 'special_item_list', 'menu_name' => '编辑专题', 'menu_url' => 'javascript:;');
        }
        if ($menu_key == 'index_edit') {
            core\tpl::output('item_title', '首页编辑');
        } else {
            core\tpl::output('item_title', '专题设置');
        }
        core\tpl::output('menu', $menu_array);
        core\tpl::output('menu_key', $menu_key);
    }
}