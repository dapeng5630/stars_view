<?php

namespace app\home\controller;

use think\Controller;
use think\facade\Request;
use think\Db;
use \Cache;

class Index extends Common
{

    // 首页
    public function index()
    {
        // 警报列表1000
        $api_method = 'event.get';

        $api_parameter = array(
            "output" => "extend",
            "limit" => "15",
            "sortfield" => ["clock", "eventid"],
            "sortorder" => "DESC",
            "filter" => [
                "severity" => [5, 4, 3],
                "source" => "0",
                "value" => "1",
            ]

        );
        $event_arr = zabbix_api($api_method, $api_parameter);
        $event_list_test = array();
        $event_list = array();
        foreach ($event_arr as $key => $value) {
            $api_parameter = array(
                "output" => "extend",
                "eventids" => $value['eventid'],
                "selectHosts" => "extend",
                "select_acknowledges" => "extend",
                "selectTags" => "extend",
                "selectSuppressionData" => "extend"
            );
            $event_list[$key] = zabbix_api($api_method, $api_parameter)[0];

            $event_list[$key]['clock_time'] = date('Y-m-d H:i:s', $event_list[$key]['clock']);

            $event_list[$key]['continued_time'] = format_date($event_list[$key]['clock']);
            $event_list[$key]['grade_colour'] =  grade_problem($event_list[$key]['severity']);
            $event_list[$key]['hosts_name'] =  $event_list[$key]['hosts'][0]['name'];
            $event_list[$key]['opdata_status'] =  !empty($event_list[$key]['opdata']) ? '1' : "";
            if (count($event_list[$key]['tags']) != 0) {
                $event_list[$key]['tags'] =  $event_list[$key]['tags'][0]['tag'] . ':' . $event_list[$key]['tags'][0]['value'];
            } else {
                $event_list[$key]['tags'] = " ";
            }
        }
        $this->assign('event_list', $event_list);



        // 监控主机数总数
        $api_method = 'host.get';
        $api_parameter = array(
            'countOutput' => 'extend',
        );
        $server_number = zabbix_api($api_method, $api_parameter);
        $this->assign('server_number', $server_number);

        // 监控可用主机数
        $api_method = 'host.get';
        $api_parameter = array(
            'output' => ['name'],
            'filter' => [
                'available' => '1',
            ],
        );
        $normal_number = count(zabbix_api($api_method, $api_parameter));
        $this->assign('normal_number', $normal_number);

        // 监控不可用主机数
        $error_number = (int)$server_number - (int)$normal_number;
        $this->assign('error_number', $error_number);

        // 警报数
        $api_method = 'event.get';
        $api_parameter = array(
            'countOutput' => 'extend',
        );
        $alert_number = zabbix_api($api_method, $api_parameter);
        $this->assign('alert_number', $alert_number);

        return $this->fetch();
    }










    public function ajax_data()
    {
        $ch = curl_init();

        curl_setopt($ch, CURLOPT_URL, 'http://10.240.96.165/zabbix/api_jsonrpc.php');
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($ch, CURLOPT_CUSTOMREQUEST, 'GET');

        curl_setopt($ch, CURLOPT_POSTFIELDS, "{\n    \"jsonrpc\": \"2.0\",\n    \"method\": \"host.get\",\n    \"params\": {\n        \"output\": [\"name\"],\n        \n        \n    },\n    \"auth\": \"35da76f487d33a291e336bf088a4365d684ee35943d059052e01d51adb878146\",\n    \"id\": 1\n}");

        $headers = array();
        $headers[] = 'User-Agent: Apipost client Runtime/+https://www.apipost.cn/';
        $headers[] = 'Content-Type: application/json';
        curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);

        $result = curl_exec($ch);
        if (curl_errno($ch)) {
            echo 'Error:' . curl_error($ch);
        }
        curl_close($ch);

        dump(json($result));
    }


    public function test_zabbix()
    {
        $api_info = array(
            'jsonrpc' => '2.0',
            'method' => 'host.get', //调用的zabbix的api，用于远程登录
            'params' => array(
                'output' => array(
                    'name',
                ),
            ),
            'auth' => '35da76f487d33a291e336bf088a4365d684ee35943d059052e01d51adb878146',
            'id' => '1',
        );

        $api_info_json = json_encode($api_info);
        $ch = curl_init();

        curl_setopt($ch, CURLOPT_URL, 'http://10.240.96.165/zabbix/api_jsonrpc.php');
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($ch, CURLOPT_POST, 1);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $api_info_json);

        $headers = array();
        $headers[] = 'User-Agent: Apipost client Runtime/+https://www.apipost.cn/';
        $headers[] = 'Content-Type: application/json';
        curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);

        $result = curl_exec($ch);
        dump($result);
        if (curl_errno($ch)) {
            echo 'Error:' . curl_error($ch);
        }
        curl_close($ch);
        $result = json_decode($result, true);
        dump($result);
    }

    public function data_exhibit()
    {
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, 'http://10.240.96.165/zabbix/api_jsonrpc.php');
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($ch, CURLOPT_CUSTOMREQUEST, 'GET');
        curl_setopt($ch, CURLOPT_POSTFIELDS, "{\n    \"jsonrpc\": \"2.0\",\n    \"method\": \"history.get\",\n    \"params\": {\n        \"output\": \"extend\",\n        \"history\": 0,\n        \"itemids\": \"29170\",\n        \"sortfield\": \"clock\",\n        \"sortorder\": \"DESC\",\n        \"limit\": 10\n    },\n    \"auth\": \"f85671ec01d621b27b06ff73b92797a1d2bd86f6cd8305cd1b31a0ac5c7b5dbe\",\n    \"id\": 1\n}");
        $headers = array();
        $headers[] = 'User-Agent: Apipost client Runtime/+https://www.apipost.cn/';
        $headers[] = 'Content-Type: application/json';
        curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
        $result = curl_exec($ch);
        if (curl_errno($ch)) {
            echo 'Error:' . curl_error($ch);
        }
        curl_close($ch);
        $result = json_decode($result, true)['result'];
        foreach ($result as $k => $v) {
            $result[$k]['ip'] = '10.240.96.165';
            $result[$k]['clock_date'] = date('Y-m-d H:i:s', $result[$k]['clock']);
        }
        dump($result);

        return $this->fetch();
    }
}
