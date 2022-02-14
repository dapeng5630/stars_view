<?php

namespace app\home\controller;

use think\Controller;
use think\facade\Request;
use think\Db;
use think\Session;
use think\captcha\Captcha;

class Login extends Controller
{
    public function login()
    {
        $captcha = new Captcha();
        return $captcha->entry();
    }


    // 登录
    public function index()
    {
        // if (!empty(Request::param('username')) && !empty(Request::param('password')) && !empty(Request::param('code'))) {
        if (!empty(Request::param('username')) && !empty(Request::param('password'))) {
            // 用户登录信息
            $user_data['user'] = Request::param('username');
            $user_data['password'] = Request::param('password');

            
            

            // 验证码
            // $value_code = Request::param('code');
            // $captcha = new Captcha();
            // if (!$captcha->check($value_code)) {
            //     return json('验证失败');
            // }
            
            // 获取本平台用户信息
            $user_info = Db::name('Zabbix_user')
                ->join('think_grade_permission ', ' think_zabbix_user.grade = think_grade_permission.id')
                ->where('think_zabbix_user.username', $user_data['user'])
                ->find();
            if(empty($user_info)){
                $error_message['code'] = 504;
                $error_message['error'] = '账号或密码错误';
                return json($error_message);
            }
            
            // 获取登录用户对应接口基础信息
            $api_nfo = Db::name('Api_info')
                ->find();

            // 获取接口的用户token
            $zabbix_api_token = zabbix_api_token($user_data, $api_nfo);
            // 验证登是否能获取token
            if ($zabbix_api_token['code'] == 200) {

                // 把登录得本平台用户信息存如session
                Session('user_info', $user_info);

                // 把登录得本平台用户的接口信息存如session
                $api_nfo['token'] = $zabbix_api_token['token'];
                Session('user_info.api_info', $api_nfo);

                // 把用户的接口信息存如session
                $api_parameter['filter'] = [
                    'username' => $user_data['user'],
                ];
                $userid = zabbix_api($api_nfo['user_get'], $api_parameter);
                $api_nfo['userid'] = $userid[0]['userid'];
                Session('user_info.api_info', $api_nfo);


                $login_message['code'] = '200';
                $login_message['message'] = '登录成功';
                $key_token = array(
                    'token' => $zabbix_api_token['token'],
                    'userid' => $userid[0]['userid'],
                    'tokenHead' => 'bearer',
                );
                $login_message['obj'] = $key_token;
                return json($login_message);
            } else {
                $error_message['code'] = 504;
                $error_message['error'] = $zabbix_api_token['error'];
                return json($error_message);
            }
        } else {
            $this->redirect('http://localhost:8082/#/');
        }
    }

    // 登出
    public function logout()
    {

        session(null);
        $logout['code'] = 200;
        $logout['message'] = '已注销退出';
        $logout['obj'] = json(array());
        return json($logout);
    }

    // 验证码
    public function captcha()
    {
        $captcha = new Captcha();
        return $captcha->entry();
    }


    public function server_data()
    {
        // 获取主机列表资源参数
        $limit = 100;
        $server_api_parameter = array(
            // "output" => "extend",
            "output" => [
                "hostid",
                "host",
                "name",
                "available",
                "ipmi_available",
                "jmx_available",
                "snmp_available",
            ],
            "selectInterfaces" => [
                "interfaceid",
                "ip",
                "port",
            ],
            "selectApplications" => [
                "applicationid",
                "hostid",
            ],

            "limit" => $limit,
        );
        $api_method = 'host.get';
        $server_liset = zabbix_api($api_method, $server_api_parameter);
    }
}
