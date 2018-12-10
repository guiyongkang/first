<?php
/**
 * 数据库备份
 *
 */
namespace admin\control;

use core;
defined('SAFE_CONST') or exit('Access Invalid!');
class db extends SystemControl
{
    public function __construct()
    {
        parent::__construct();
        core\language::read('db');
    }
    /**
     * 数据库管理
     */
    public function dbOp()
    {
        $lang = core\language::getLangContent();
        $model_db = model('database');
        $this->outputdb();
        /**
         * 保存数据库备份参数到session中
         */
        if (chksubmit()) {
            /**
             * 验证分卷大小
             */
            if (intval($_POST['file_size']) < 10) {
                error($lang['db_index_min_size']);
            }
            /**
             * 验证文件夹是否重复
             */
            if (is_dir(BASE_DATA_PATH . DS . 'sql_back' . DS . $_POST['backup_name'])) {
                error($lang['db_index_name_exists']);
            }
            /**
             * 表列表
             */
            if ($_POST['backup_type'] == 'all') {
                $table_list = $model_db->getTableList('all');
            }
            if ($_POST['backup_type'] == 'custom') {
                /**
                 * 验证是否未选
                 */
                if (empty($_POST['tables'])) {
                    error($lang['db_index_choose']);
                }
                if (is_array($_POST['tables'])) {
                    foreach ($_POST['tables'] as $k => $v) {
                        $table_list[] = $v;
                    }
                }
            }
            /**
             * 将相关内容写入session
             */;
			core\session::set('db_backup', array());
			core\session::set('db_backup.size', intval($_POST['file_size']) * 1024);
			core\session::set('db_backup.table_name', '');
			core\session::set('db_backup.op', 'create');
			core\session::set('db_backup.back_file', $_POST['backup_name']);
			core\session::set('db_backup.backup_tables', $table_list);
			core\session::set('db_backup.limit', 0);
			core\session::set('db_backup.md5', substr(md5(microtime(true)), 0, 5));
            success($lang['db_index_backup_to_wait'], 'index.php?act=db&op=db&step=1');
        }
		
        /**
         * 数据库备份步骤
         */
        if (isset($_GET['step']) && intval($_GET['step']) >= 1) {
            /**
             * 备份
             */
            $model_db->backUp(intval($_GET['step']));
            /**
             * 判断是否备份完毕
             */
			$backup_tables = core\session::get('db_backup.backup_tables');
            if (core\session::get('db_backup.op') == 'insert' && $backup_tables[count($backup_tables) - 1] == core\session::get('db_backup.table_name') && intval($_GET['step']) > 1 && core\session::get('db_backup.limit') == 0) {
                /**
                 * 销毁
                 */
				core\session::delete('db_backup');
                /**
                 * 跳转
                 */
                $url = 'index.php?act=db&op=db';
                success($lang['db_index_backup_succ'], $url);
            } else {
                /**
                 * 跳转
                 */
                $url = 'index.php?act=db&op=db&step=' . (intval($_GET['step']) + 1);
                success($lang['db_index_backup_succ1'] . intval($_GET['step']) . $lang['db_index_backup_succ2'], $url);
            }
        }
        /**
         * 数据库列表
         */
        $table_list = $model_db->getTableList('all');
        /**
         * 取备份文件夹目录名
         */
        $back_dir = $model_db->getBackDir();
        core\tpl::output('back_dir', $back_dir);
        core\tpl::output('table_list', $table_list);
        core\tpl::showpage('db.index');
    }
    /**
     * 数据库遍历
     */
    private function outputdb()
    {
        $db_id = isset($_GET['db_id']) ? intval($_GET['db_id']) : '';
        if (isset($_GET['type']) && $_GET['type'] == 'html') {
            $html_path = $model_special->getdbSpecialHtmlPath($db_id);
            if (!is_file($html_path)) {
                ob_start();
                core\tpl::output('list', $data);
                core\tpl::showpage('db_id');
                file_put_contents($html_path, ob_get_clean());
            }
            header('Location: ' . $model_special->getdblist($db_id));
            die;
        }
    }
    /**
     * 数据库恢复
     */
    public function db_restoreOp()
    {
        $lang = core\language::getLangContent();
        /**
         * 删除
         */
        if (chksubmit()) {
            if (!empty($_POST['dir_name']) && is_array($_POST['dir_name'])) {
                $dir = BASE_DATA_PATH . DS . 'sql_back';
                foreach ($_POST['dir_name'] as $k => $v) {
                    if (file_exists(BASE_DATA_PATH . DS . 'sql_back' . DS . $v)) {
                        $file_list = array();
                        readFileList($dir . DS . $v, $file_list);
                        /**
                         * 删除文件
                         */
                        if (is_array($file_list)) {
                            foreach ($file_list as $k_file => $v_file) {
                                unlink($dir . DS . $v . DS . $v_file);
                            }
                        }
                        /**
                         * 删除目录
                         */
                        rmdir($dir . DS . $v);
                    } else {
                        error($lang['db_restore_file_not_exists']);
                    }
                }
                success($lang['db_restore_del_succ']);
            } else {
                error($lang['db_restore_choose_file_to_del']);
            }
        }
        $tmp_list = readDirList(BASE_DATA_PATH . DS . 'sql_back');
        /**
         * 整理内容
         */
        $dir_list = array();
        if (is_array($tmp_list)) {
            foreach ($tmp_list as $k => $v) {
                $dir_list[$k]['name'] = $v;
                $dir_list[$k]['make_time'] = date('Y-m-d H:i:s', filemtime(BASE_DATA_PATH . DS . 'sql_back' . DS . $v));
                $dir_list[$k]['size'] = number_format(getDirSize(BASE_DATA_PATH . DS . 'sql_back' . DS . $v) / 1024, 2) . 'KB';
                $dir_list[$k]['file_num'] = count(glob(BASE_DATA_PATH . DS . 'sql_back' . DS . $v . DS . "*.sql"));
            }
        }
        core\tpl::output('dir_list', $dir_list);
        core\tpl::showpage('db.restore');
    }
    /**
     * 数据库备份导入
     */
    public function db_importOp()
    {
        $lang = core\language::getLangContent();
        if (!empty($_GET['dir_name']) && file_exists(BASE_DATA_PATH . DS . 'sql_back' . DS . $_GET['dir_name'])) {
            $model_db = model('database');
            $result = $model_db->import($_GET['dir_name'], intval($_GET['step']));
            /**
             * 导入成功，返回列表
             */
            if ($result == 'succ') {
                $url = 'index.php?act=db&op=db_restore';
                success($lang['db_import_succ'], $url);
            }
            /**
             * 继续导入
             */
            if ($result == 'continue') {
                $url = 'index.php?act=db&op=db_import&dir_name=' . $_GET['dir_name'] . '&step=' . (intval($_GET['step']) + 1);
                success($lang['db_index_backup_succ1'] . intval($_GET['step']) . $lang['db_import_succ2'], $url);
            }
            /**
             * 导入失败
             */
            if ($result == false) {
                error($lang['db_import_fail']);
            }
        } else {
            error($lang['db_import_file_not_exists']);
        }
    }
    /**
     * 数据库 删除
     */
    public function db_delOp()
    {
        $lang = core\language::getLangContent();
        if (!empty($_GET['dir_name']) && file_exists(BASE_DATA_PATH . DS . 'sql_back' . DS . $_GET['dir_name'])) {
            $dir = BASE_DATA_PATH . DS . 'sql_back' . DS . $_GET['dir_name'];
            $file_list = array();
            readFileList($dir, $file_list);
            /**
             * 删除文件
             */
            if (is_array($file_list)) {
                foreach ($file_list as $k => $v) {
                    unlink($v);
                }
            }
            /**
             * 删除目录
             */
            rmdir(BASE_DATA_PATH . DS . 'sql_back' . DS . $_GET['dir_name']);
            success($lang['db_del_succ']);
        } else {
            error($lang['db_restore_file_not_exists']);
        }
    }
    /**
     * ajax操作
     */
    public function ajaxOp()
    {
        switch ($_GET['branch']) {
            /**
             * 数据库 sql列表
             */
            case 'db_file':
                if ($_GET['dir_name'] != '.' && $_GET['dir_name'] != '..' && is_dir(BASE_DATA_PATH . DS . 'sql_back' . DS . $_GET['dir_name'])) {
                    $dir = BASE_DATA_PATH . DS . 'sql_back' . DS . $_GET['dir_name'];
                    $tmp = array();
                    readFileList($dir, $tmp);
                    /**
                     * 整理内容
                     */
                    if (is_array($tmp)) {
                        $file_list = array();
                        foreach ($tmp as $k => $v) {
                            $file_list[$k]['name'] = $v;
                            $file_list[$k]['make_time'] = date('Y-m-d H:i:s', filemtime($dir . DS . $v));
                            $file_list[$k]['size'] = number_format(filesize($dir . DS . $v) / 1024, 2) . 'KB';
                        }
                        $output = json_encode($file_list);
                        print_r($output);
                    }
                    exit;
                } else {
                    echo 'false';
                    exit;
                }
                break;
        }
    }
}