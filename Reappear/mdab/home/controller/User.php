<?php

namespace app\home\controller;

use think\Controller;
use think\facade\Request;
use think\Db;
use \Cache;

class User extends Common
{
    public $tables = 'Zabbix_user';
    // 用户列表
    public function user_list()
    {
        $user_group = array();
        $user_arr = array();
        $group_arr = array();

        $user_size = 20;

        // 页码
        if (empty(Request::param('userPage'))) {
            $user_page = 1;
        } else {
            $user_page = Request::param('userPage');
        }

        // 总数
        $user_count = Db::name($this->tables)
        ->where('username', 'like', '%' . Request::param('userName') . '%')
        ->count();

        // 起始位
        if ($user_page == 1) {
            $start_number = 0;
        } else {
            $start_number = ($user_page - 1) * $user_size;
        }

        if ($user_count < $start_number) {
            $error_message['code'] = 504;
            $error_message['error'] = '请求最大数超过基本记录数';
            return json($error_message);
        }

        $user_arr = Db::name($this->tables)
            ->where('username', 'like', '%' . Request::param('userName') . '%')
            ->limit($start_number, $user_size)
            ->order('userid desc')
            ->select();
        
        
        
        if (count($user_arr) >= 1) {
            $user_group['count'] = $user_count;
            $user_group['data'] = $user_arr;
            $user_group['code'] = 200;
            return json($user_group);
        } else {
            $error_message['code'] = 504;
            $error_message['error'] = '数据错误';
            return json($error_message);
        }
    }

    // 用户权限修改
    public function user_update()
    {
        $user_data = [];
        if (empty(Request::param('uid'))) {
            $this->error('userid参数错误，且不能为空');
        } else {
            $where['userid'] = Request::param('uid');
        }

        if (empty(Request::param('gradeid'))) {
            $this->error('角色参数错误，且不能为空');
        } else {
            $user_data['grade'] = Request::param('gradeid');
        }
        $user_add_result = Db::name($this->tables)
            ->where($where)
            ->data($user_data)
            ->update();
        if ($user_add_result < 1) {
            $update_error['code'] = 504;
            $update_error['error'] = '修改失败';
            return json($update_error);
        } else {
            $update_success['code'] = 200;
            $update_success['success'] = '修改成功';
            return json($update_success);
        }
    }
}
