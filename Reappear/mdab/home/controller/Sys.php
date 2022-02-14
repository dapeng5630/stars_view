<?php

namespace app\home\controller;

use think\Controller;
use think\facade\Request;
use think\Db;
use \Cache;

class Sys extends Common
{
    // zabbix用户更新
    public function sys_zabbix_user_up()
    {
        // 获取zabbix用户列表
        $api_parameter = array(
            'output' => [
                'userid',
                'username',
            ],
            "sortfield" => "userid",
            "sortorder" => "ASC", // ASC DESC
        );
        $zabbix_user = zabbix_api(Session('user_info.api_info.user_get'), $api_parameter);

        // 本系统存储得zabbix用户
        $system_user_arr = Db::name('Zabbix_user')
            ->select();

        // 删除重复得用户
        foreach ($system_user_arr as $group_key => $group_value) {
            foreach ($zabbix_user as $key => $value) {
                if ($value['userid'] == $group_value['userid']) {
                    unset($zabbix_user[$key]);
                    unset($system_user_arr[$group_key]);
                } else {
                }
            }
        }

        // 添加新用户
        foreach ($zabbix_user as $key => $value) {
            $zabbix_user[$key]['grade'] = 1;
            $zabbix_user[$key]['user_type'] = 'zabbix';
        }
        if (count($zabbix_user) < 1) {
            $error_message['code'] = 504;
            $error_message['error'] = '与接口平台用户数据一致无需更新';
            return json($error_message);
        }

        $add_result = Db::name('Zabbix_user')
            ->data($zabbix_user)
            ->insertAll();

        if ($add_result > 0) {
            // 删除不存在得用户
            $id = '';
            foreach ($system_user_arr as $key => $value) {
                $id = $id . $value['id'] . ',';
            }
            $id = trim($id, ',');
            $delete_result = Db::name('Zabbix_user')
                ->where('id', 'in', $id)
                ->delete();
            $success_message['code'] = 200;
            $success_message['info'] = '更新成功';
            return json($success_message);
        } else {
            $error_message['code'] = 504;
            $error_message['error'] = '更新失败';
            return json($error_message);
        }
    }


    public function sys_user_info()
    {
        Session('user_info');
        $data = Session('user_info');
        // dump($data);
        return json($data);
    }

    /*
    * 系统功能列表
    */
    public function sys_menu_config()
    {
        // dump(Session('user_info.Menu_info_arr'));
        // $id = '';
        // for ($i=1;$i<100;$i++) {
        //     $id = $id . $i.',';
        // }
        // dump($id);
        return json(Session('user_info.Menu_info_arr'));
    }

    /*
    * zabbix接口信息
    */
    public function sys_zabbix_info()
    {
        $api_group = Db::name('Api_info')
            ->find();
        if ($api_group) {
            $zabbix_info['data'] = $api_group;
            $zabbix_info['code'] = 200;
            return json($zabbix_info);
        } else {
            $error_message['error'] = '参数错误';
            $error_message['code'] = 504;
            return json($error_message);
        }
    }

    /*
    * zabbix接口信息
    */
    public function sys_user_info_up()
    {

        if (empty(Request::param('jsonrpc')) || empty(Request::param('ip')) || empty(Request::param('user_login')) || empty(Request::param('user_get'))) {
            $error_message['error'] = '参数错误';
            $error_message['code'] = 504;
            return json($error_message);
        }

        $api_data['jsonrpc'] = Request::param('jsonrpc');
        $api_data['ip'] = Request::param('ip');
        $api_data['user_login'] = Request::param('user_login');
        $api_data['user_get'] = Request::param('user_get');
        $api_update_result = Db::name('Api_info')
            ->where('id', Request::param('id'))
            ->data($api_data)
            ->update();
        if ($api_update_result > 0) {
            $success_message['code'] = 200;
            $success_message['success'] = '更新成功';
            return json($success_message);
        } else {
            $error_message['error'] = '更新失败';
            $error_message['code'] = 504;
            return json($error_message);
        }
    }

    /*
    * 菜单列表
    */
    public function sys_menu_list()
    {
        $key_words = [
            'id',
            'name',
            'type',
            'pid',
        ];
        $menu_result = Db::name('Menu')
            ->field($key_words)
            ->select();

        $Menu_group = array();

        foreach ($menu_result as $key => $value) {
            if (!($value['pid'] >= 1)) {
                unset($value['type']);
                $Menu_group['data'][$key] = $value;
                foreach ($menu_result as $twokey => $twovalue) {
                    if ($twovalue['pid'] >= 1  && $twovalue['pid'] == $value['id']) {
                        unset($twovalue['type']);
                        $Menu_group['data'][$key]['children'][$twokey] = $twovalue;
                        foreach ($menu_result as $threekey => $threevalue) {
                            if ($threevalue['pid'] >= 1  && $threevalue['pid'] == $twovalue['id']) {
                                unset($threevalue['type']);
                                $Menu_group['data'][$key]['children'][$twokey]['children'][$threekey] = $threevalue;
                            }
                        }
                    }
                }
            }
            
            
        }
        
        if (!empty($Menu_group['data'])) {
            $Menu_group['code'] = 200;
            return json($Menu_group);
        } else {
            $error_message['code'] = 504;
            $error_message['error'] = '数据错误';
            return json($error_message);
        }
    }
}
