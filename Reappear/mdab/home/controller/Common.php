<?php

namespace app\home\controller;

use think\Controller;
use think\facade\Request;
use think\Model;
use think\Db;
use think\Session;
use think\Cache;

/**
 * 
 */
class Common extends Controller
{

    public $Menu_info = [];

    public function __construct()
    {
        parent::__construct();

        $qualifications_id = '';
        // 用户信息验证
        if (Session('user_info.userid') >= 1) {

            // 菜单组
            $key_words = [
                'id',
                'name',
                'component',
                'path',
                'type',
                'iconCls',
                'pid',
                'hidden',
                'function_name',
            ];
            $Menu_arr = Db::name('Menu')
                ->field($key_words)
                ->where('id!=1')
                ->order('id asc')
                ->select();
            // 用户权限数组
            $qualifications_group = explode(',', Session('user_info.permission'));
            $Menu_list = array();
            foreach ($Menu_arr as $key => $value) {
                // 判断当前用户是否有权限访问当前功能
                if (Request::action() == $value['function_name']) {
                    if (!in_array($value['id'], $qualifications_group)) {
                        $error_message['code'] = 402;
                        $error_message['message'] = 'Insufficient permissions';
                        $e = json_encode($error_message);
                        echo $e;
                        exit;
                    }
                }

                // 排列出当前用户可以访问得功能列表
                if ($value['pid'] == 1 && $value['type'] != 'hidden') {
                    $Menu_list[$key] = $value;
                    foreach ($Menu_arr as $Secondary_key => $Secondary_value) {
                        if ($Secondary_value['pid'] > 1 && $Secondary_value['type'] != 'hidden' && $Secondary_value['pid'] == $value['id']) {
                            $Menu_list[$key]['children'][$Secondary_key] = $Secondary_value;
                        }
                    }
                }
            }
            // 缓存用户功能列表
            Session('user_info.Menu_info_arr', $Menu_list);
            
        } else {
            $login_out_url = url('Login/index');
            if (!strstr($login_out_url, '/index.php')) {
                $login_out_url = '/index.php' . $login_out_url;
            }
            $this->redirect('http://localhost:8082/#/');
        }
    }


}
