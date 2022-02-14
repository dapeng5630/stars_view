<?php

namespace app\home\controller;

use think\Controller;
use think\facade\Request;
use think\Db;
use \Cache;

class Api extends Common
{

    /*
    * api_list 接口列表
    */
    public function api_list()
    {

        $api_group = Db::name('Api')
            ->select();
        foreach ($api_group as $key => $value) {
            $api_group[$key]['update_time'] = date('Y-m-d H:i:s', $value['update_time']);
            $api_group[$key]['status'] = $value['status'] == 1 ? true : false;
        }
        return json($api_group);
    }


    /*
    * api_add 接口添加
    */
    public function api_add()
    {
        if (Request::isPost()) {


            $data['api_state'] = 0;

            if (empty(Request::post('api_name'))) {
                $error_message['code'] = 500;
                $error_message['message'] = 'api_name,参数错误,且不能为空';
                return json($error_message);
            } else {
                $data['api_name'] = Request::post('api_name');
            }

            if (empty(Request::post('jsonrpc'))) {
                $error_message['code'] = 500;
                $error_message['message'] = 'jsonrpc,参数错误,且不能为空';
                return json($error_message);
            } else {
                $data['jsonrpc'] = Request::post('jsonrpc');
            }

            if (empty(Request::post('ip'))) {
                $error_message['code'] = 500;
                $error_message['message'] = 'ip,参数错误,且不能为空';
                return json($error_message);
            } else {
                $data['ip'] = Request::post('ip');
            }

            if (empty(Request::post('user_login'))) {
                $error_message['code'] = 500;
                $error_message['message'] = 'user_login,参数错误,且不能为空';
                return json($error_message);
            } else {
                $data['user_login'] = Request::post('user_login');
            }

            if (empty(Request::post('user_get'))) {
                $error_message['code'] = 500;
                $error_message['message'] = 'user_get,参数错误,且不能为空';
                return json($error_message);
            } else {
                $data['user_get'] = Request::post('user_get');
            }

            if (empty(Request::post('user'))) {
                $error_message['code'] = 500;
                $error_message['message'] = 'user,参数错误,且不能为空';
                return json($error_message);
            } else {
                $data['user'] = Request::post('user');
            }

            if (empty(Request::post('password'))) {
                $error_message['code'] = 500;
                $error_message['message'] = 'password,参数错误,且不能为空';
                return json($error_message);
            } else {
                $data['password'] = Request::post('password');
            }
            $data['update_time'] = time();

            $api_add_result = Db::name('Api')
                ->data(Request::post())
                ->insert();
            if ($api_add_result < 1) {
                $error_message['code'] = 500;
                $error_message['message'] = '添加失败';
                return json($error_message);
            } else {
                $success_message['code'] = 200;
                $success_message['message'] = '添加成功';
                return json($success_message);
            }
        } else {
            $error_message['code'] = 500;
            $error_message['message'] = '参数错误';
            return json($error_message);
        }
    }


    public function api_update()
    {

        if (Request::param('id')) {
        }

        $data = [];

        if (!empty(Request::param('status'))){
            $api_status_result = Db::name('Api')
                    ->where('status', 1)
                    ->find();
            if (Request::param('status') == true) {
                if (!empty($api_status_result['id']) && Request::param('id') != $api_status_result['id']) {
                    $error_message['code'] = 504;
                    $error_message['message'] = '已有启用api,同类型只能存在一条启用api';
                    return json($error_message);
                }
                $data['status'] = Request::param('status');
            } else if (Request::param('status') == false) {
                if (empty($api_status_result['id']) && Request::param('id') != $api_status_result['id']) {
                    $error_message['code'] = 504;
                    $error_message['message'] = '已有启用api,同类型只能存在一条启用api';
                    return json($error_message);
                }
            }
        }
        if (Request::param('status') == true) {
            $api_status_result = Db::name('Api')
                ->where('status', 1)
                ->find();

            if (!empty($api_status_result['id']) && Request::param('id') != $api_status_result['id']) {
                $error_message['code'] = 500;
                $error_message['message'] = '已有启用api,同类型只能存在一条启用api';
                return json($error_message);
            }
            $data['status'] = Request::param('status');
        } else if (empty(Request::param('status'))) {
            $error_message['code'] = 500;
            $error_message['message'] = '状态,参数错误,且不能为空';
            return json($error_message);
        } else {
            $data['status'] = Request::param('status');
        }

        if (Request::param('nickname') == 1) {
            $api_updata_result = Db::name('Api')
                ->where('id', Request::param('id'))
                ->data($data)
                ->update();
            if ($api_updata_result < 1) {
                return json('2');
            } else {
                return json($api_updata_result);
            }
        } else {
            if (!empty(Request::param('api_name'))) {
                $data['api_name'] = Request::param('api_name');
            } else {
                $error_message['code'] = 500;
                $error_message['message'] = '接口别名,参数错误,且不能为空';
                return json($error_message);
            }

            if (!empty(Request::param('jsonrpc'))) {
                $data['jsonrpc'] = Request::param('jsonrpc');
            } else {
                $error_message['code'] = 500;
                $error_message['message'] = 'jsonrpc,参数错误,且不能为空';
                return json($error_message);
            }

            if (!empty(Request::param('ip'))) {
                $data['ip'] = Request::param('ip');
            } else {
                $error_message['code'] = 500;
                $error_message['message'] = 'ip,参数错误,且不能为空';
                return json($error_message);
            }

            if (!empty(Request::param('user_login'))) {
                $data['user_login'] = Request::param('user_login');
            } else {
                $error_message['code'] = 500;
                $error_message['message'] = 'user_login,参数错误,且不能为空';
                return json($error_message);
            }

            if (!empty(Request::param('user_get'))) {
                $data['user_get'] = Request::param('user_get');
            } else {
                $error_message['code'] = 500;
                $error_message['message'] = 'user_get,参数错误,且不能为空';
                return json($error_message);
            }


            $data['update_time'] = time();



            $api_updata_result = Db::name('Api')
                ->where('id', Request::param('id'))
                ->data($data)
                ->update();

            if ($api_updata_result < 1) {
                $error_message['code'] = 500;
                $error_message['message'] = '修改失败';
                return json($error_message);
                $this->error('修改失败');
            } else {
                $success_message['code'] = 200;
                $success_message['message'] = '修改成功';
                return json($success_message);
            }
        }
    }

    /*
    * 接口删除
    */
    public function api_delete()
    {

        if (!empty(Request::param('id'))) {

            $api_delete_result = Db::name('Api')
                ->where('id', Request::param('id'))
                ->delete();
            // echo  Db::getLastSql();
            if ($api_delete_result < 1) {
                $error_message['code'] = 500;
                $error_message['message'] = '删除失败';
                return json($error_message);
            } else {
                $success_message['code'] = 200;
                $success_message['message'] = '删除成功';
                return json($success_message);
            }
            exit;
        } else {
            $error_message['code'] = 500;
            $error_message['message'] = '参数错误';
            return json($error_message);
        }
    }

    /*
    * 接口批量删除
    */
    public function api_batch_delete()
    {

        if (!empty(Request::param('id'))) {
            $data['id'] = explode(',', trim(Request::param('id'), ','));
            $api_delete_result = Db::name('Api')
                ->where($data)
                ->delete();
            // echo  Db::getLastSql();
            if ($api_delete_result < 1) {
                $error_message['code'] = 500;
                $error_message['message'] = '删除失败';
                return json($error_message);
            } else {
                $success_message['code'] = 200;
                $success_message['message'] = '删除成功';
                return json($success_message);
            }
            exit;
        } else {
            $error_message['code'] = 500;
            $error_message['message'] = '参数错误';
            return json($error_message);
        }
    }

    public function api_info_cache_update()
    {
        $this->api_info_cache();
        $this->success('更新成功');
    }
}
