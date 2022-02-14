<?php

namespace app\home\controller;

use think\Controller;
use think\facade\Request;
use think\Db;
use \Cache;

class Monitor extends Common
{



    // 仪表盘
    public function mon_dash_board()
    {

        $dash_board = [];

        // 警报列表1000
        $api_method = 'event.get';
        $api_parameter = array(
            "output" => "extend",
            "limit" => "10",
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
            // $event_list[$key]['grade_colour'] =  grade_problem($event_list[$key]['severity']);

            $event_list[$key]['hosts_name'] =  empty($event_list[$key]['hosts']) ? $value['name'] : $event_list[$key]['hosts'][0]['name'];
            $event_list[$key]['opdata_status'] =  !empty($event_list[$key]['opdata']) ? '1' : "";
            $event_list[$key]['acknowledged_status'] =  !empty($event_list[$key]['acknowledged']) ? '是' : "否";
            if (count($event_list[$key]['tags']) != 0) {
                $event_list[$key]['tags'] =  $event_list[$key]['tags'][0]['tag'] . ':' . $event_list[$key]['tags'][0]['value'];
            } else {
                $event_list[$key]['tags'] = " ";
            }
        }
        $dash_board['event_list'] = $event_list;




        // 监控主机数总数
        $api_method = 'host.get';
        $api_parameter = array(
            'countOutput' => 'extend',
        );
        $server_number = zabbix_api($api_method, $api_parameter);
        $dash_board['serverNumber'] = $server_number;

        // 监控可用主机数
        $api_method = 'host.get';
        $api_parameter = array(
            'output' => ['name'],
            'filter' => [
                'available' => '1',
            ],
        );
        $normal_number = count(zabbix_api($api_method, $api_parameter));
        $dash_board['normalNumber'] = $normal_number;

        // 监控不可用主机数
        $error_number = (int)$server_number - (int)$normal_number;
        $dash_board['errorNumber'] = $error_number;

        // 警报数
        $api_method = 'event.get';
        $api_parameter = array(
            'countOutput' => 'extend',
        );
        $alert_number = zabbix_api($api_method, $api_parameter);
        $dash_board['alertNumber'] = $alert_number;


        return json($dash_board);
    }

    // 主机列表
    public function mon_ser_list()
    {





        // 获取主机列表资源参数
        $server_api_parameter = array(
            "output" => [
                "hostid",
                "host",
                "name",
            ],
            // "output" =>  "extend",
            "selectInterfaces" => [
                "ip",
                "port",
                "type",
                "available",
            ],

            "selectTags" => [
                "tag",
                "value",
            ],
            "sortfield" => "name",
            "sortorder" => "ASC", // ASC DESC

            "selectGroups" => [
                "groupid",
                "name",
            ],
        );

        if (!empty(Request::param('name'))) {
            $server_api_parameter['search']['name'] = Request::param('name');
        }
        if (!empty(Request::param('ip'))) {
            $server_api_parameter['search']['ip'] = Request::param('ip');
        }
        if (!empty(Request::param('port'))) {
            $server_api_parameter['search']['port'] = Request::param('port');
        }
        if (!empty(Request::param('type'))) {
            $server_api_parameter['search']['type'] = Request::param('type');
        }
        // if (!empty(Request::param('dns'))) {
        //     $server_api_parameter['search']['dns'] = Request::param('dns');
        // }

        if (!empty(Request::param('host_group'))) {
            $server_api_parameter['groupids'] = explode(",", Request::param('host_group'));
        }

        if (!empty(Request::param('severity'))) {
            $server_api_parameter['severities'] = explode(',', Request::param('severity'));
        }

        $api_method = 'host.get';
        $server_liset = zabbix_api($api_method, $server_api_parameter);

        if ($server_liset) {
            foreach ($server_liset as $key => $value) {
                if (Request::param('available') == '0' || Request::param('available') == 1) {
                    if (Request::param('available') == 1) {
                        if ($value['interfaces'][0]['available'] == 0) {
                            unset($server_liset[$key]);
                            continue;
                        }
                    } else {
                        if ($value['interfaces'][0]['available'] != 0) {
                            unset($server_liset[$key]);
                            continue;
                        }
                    }
                }
                $server_liset[$key]['ip'] = $value['interfaces'][0]['ip'];
                $server_liset[$key]['ip_port'] = $value['interfaces'][0]['ip'] . ':' . $value['interfaces'][0]['port'];

                switch ($value['interfaces'][0]['type']) {
                    case 1:
                        $server_liset[$key]['interfacesType'] = 'agent';
                        break;
                    case 2:
                        $server_liset[$key]['interfacesType'] = 'SNMP';
                        break;
                    case 3:
                        $server_liset[$key]['interfacesType'] = 'IPMI';
                        break;
                    case 4:
                        $server_liset[$key]['interfacesType'] = 'JMX';
                        break;
                    default:
                        $server_liset[$key]['interfacesType'] = '参数错误';
                        break;
                }
                $server_liset[$key]['interfacesAvailable'] = $value['interfaces'][0]['available'];
                if (count($server_liset[$key]['tags'])) {
                    $server_liset[$key]['timeTags'] = $value['tags'][0]['tag'] . ':' . $value['tags'][0]['value'];
                }

                $server_liset[$key]['id'] = $key + 1;
            }

            $server_data['data'] = array_merge($server_liset);

            $server_data['serNumber'] = count($server_liset);



            // dump($server_data);
            $server_data['code'] = 200;
            return json($server_data);
        } else {
            $error_message['code'] = 504;
            $error_message['error'] = '无数据';
            return json($error_message);
        }
    }






    // 最新数据监控项
    public function mon_latest_data()
    {
        // 判断是否有提交监控项的 hostid host_group itemame
        if (!empty(Request::param('hostid')) || !empty(Request::param('host_group')) || !empty(Request::param('itemame'))) {
            $item_api_parameter = array(
                // 'countOutput' => "extend",

                "output" => [
                    "itemid",
                    "name",
                    "description",
                    "lastclock",
                ],
                // "output" => "extend",
                "selectHosts" => [
                    "name"
                ],
                "sortfield" => "name",
                "sortorder" => "ASC", // ASC DESC
                "selectTags" => [
                    "tag",
                    "value",
                ],
                "limit" => 10000,
            );

            if (!empty(Request::param('itemame'))) {
                $item_api_parameter['search']['name'] = Request::param('itemame');
            }
            if (!empty(Request::param('host_group'))) {
                $item_api_parameter['groupids'] = explode(",", Request::param('host_group'));
            }
            if (!empty(Request::param('hostid'))) {
                $item_api_parameter['hostids'] = explode(",", Request::param('hostid'));
            }

            // 获取zabbix 接口的监控项数据
            $api_method = 'item.get';
            $item_api_result_arr = zabbix_api($api_method, $item_api_parameter);

            if ($item_api_result_arr) {

                $item_data = array();
                foreach ($item_api_result_arr as $key => $value) {

                    $item_api_result_arr[$key]['tagsvalue'] = count($value["tags"]) >= 1 ? $value["tags"][0]["tag"] . ':' . $value["tags"][0]["value"] : '';
                }
                $item_data['data'] = $item_api_result_arr;
                $item_data['itemNumber'] = count($item_api_result_arr);

                // dump($item_data);
                $item_data['code'] = 200;
                return json($item_data);
            } else {
                $error_message['code'] = 504;
                $error_message['error'] = '无数据';
                return json($error_message);
            }
            exit;
        } else {
            $error_message['code'] = 504;
            $error_message['error'] = '无数据';
            return json($error_message);
        }


        // dump($item_arr);
    }

    

    // 实时动态图
    public function mon_dynamic_data()
    {
        if (!empty(Request::param('itemid'))) {
            $itemid = explode(',', Request::param('itemid'));
            // 查询开始时间  默认七天前
            $time_from = Request::param('srartTime') ? Request::param('srartTime') : strtotime(date("Y-m-d H:i:s", strtotime('-7 day'))); // -7 day -1 hour
            // 查询结束时间  默认当前时间
            $time_till = Request::param('finishTime') ? Request::param('finishTime') : time();

            // 查询条数默认 100000
            $limit = 100000;
            // 查询顺序 默认升序
            $sortorder = 'ASC';
            // 判断是否是更新数据还是初始数据
            if (Request::param('limit') == 1) {
                // 更新数据只查一条
                $limit = 1;
                // 倒序查询
                $sortorder = 'DESC';
            }
            $event_arr = array();
            $num = 0;
            foreach ($itemid as $key => $value) {
                $num = $num + 1;

                $api_parameter = array(
                    "output" => "extend",
                    "itemids" => $value,
                    "sortfield" => "clock",
                    "sortorder" => $sortorder, //DESC ASC
                    "time_from" => $time_from,
                    "time_till" => $time_till,
                    "limit" => $limit,
                );

                $api_method = 'history.get';
                $event_arr[$key] = zabbix_api($api_method, $api_parameter);

                // dump($event_arr);
                $item_host_api_parameter = array(
                    "output" => [
                        "itemid",
                        "name",
                        "units",
                    ],
                    // "output" => 'extend',
                    "sortfield" => "name",
                    "sortorder" => "ASC", // ASC DESC
                    "limit" => 10000,
                    "itemids" => $value,
                );

                // 按提交的数据获取zabbix api的历史记录
                $item_host_api_method = 'item.get';
                $item_host_one[$key] = zabbix_api($item_host_api_method, $item_host_api_parameter);
                // dump($item_host_one);



                if ($event_arr[$key] && $item_host_one[$key]) {
                    $history_data[$key]['data'] = $event_arr[$key];

                    $history_data[$key]['itemName'] = $item_host_one[$key][0]['name'];
                    $history_data[$key]['itemid'] = $item_host_one[$key][0]['itemid'];
                    $history_data[$key]['units'] = $item_host_one[$key][0]['units'];

                    $history_data[$key]['code'] = 200;
                } else {
                    
                    $history_data[$key]['code'] = 504;
                    $history_data[$key]['error'] = '第' . $num . '个监控项无数据';
                }
            }

            // dump($history_data);
            // $history_data['code'] = 200;
            return json($history_data);

        } else {
            $error_message['code'] = 504;
            $error_message['error'] = '参数错误';
            return json($error_message);
        }
    }

    // 主机下拉列表
    public function hostselect()
    {
        $host_api_parameter = array(
            "output" => [
                "hostid",
                "name",
            ],
            "selectInterfaces" => [
                "ip",
            ],
            "sortfield" => "name",
            "sortorder" => "ASC", // ASC DESC

        );
        if (!empty(Request::param('host_group'))) {
            $host_api_parameter['groupids'] = explode(",", Request::param('host_group'));
        }
        $host_api_method = 'host.get';
        $host_data = zabbix_api($host_api_method, $host_api_parameter);

        if ($host_data) {
            foreach ($host_data as $key => $value) {
                $host_data[$key]['ip'] = $value['interfaces'][0]['ip'];
                unset($host_data[$key]['interfaces']);
            }
            // dump($host_data);
            $hostData['data'] = $host_data;
            $hostData['code'] = 200;
            return json($hostData);
        } else {
            $error_message['code'] = 504;
            $error_message['error'] = '数据太大或无数据';
            return json($error_message);
        }
    }

    // 主机群下拉列表
    public function groupselect()
    {
        $group_api_parameter = array(
            "output" => [
                "groupid",
                "name",
            ],
            "sortfield" => "groupid",
            "sortorder" => "ASC", // ASC DESC
        );
        $group_api_method = 'hostgroup.get';
        $group_data = zabbix_api($group_api_method, $group_api_parameter);
        if ($group_data) {
            $groupData['data'] = $group_data;
            $groupData['code'] = 200;
            return json($groupData);
        } else {
            $error_message['code'] = 504;
            $error_message['error'] = '数据太大或无数据';
            return json($error_message);
        }
    }

    // 监控项下拉列表
    public function itemselect()
    {
        if (!empty(Request::param('itemid'))) {
            $item_host_api_parameter = array(
                "output" => [
                    "itemid",
                ],
                "selectHosts" => [
                    "name",
                    "hostid",
                ],
                "sortfield" => "name",
                "sortorder" => "ASC", // ASC DESC
                "limit" => 10000,
            );
            $item_host_api_parameter['itemids'] = explode(",", Request::param('itemid'));
            $item_host_api_method = 'item.get';
            $item_host_data = zabbix_api($item_host_api_method, $item_host_api_parameter);
            if ($item_host_data) {
                $item_api_parameter['hostids'] = $item_host_data[0]['hosts'][0]['hostid'];
            } else {
                $error_message['code'] = 504;
                $error_message['error'] = '数据太大或无数据';
                return json($error_message);
            }
        } else if (!empty(Request::param('hostid'))) {
            $item_api_parameter['hostids'] = explode(",", Request::param('hostid'));
        } else {
            $error_message['code'] = 504;
            $error_message['error'] = '参数错误';
            return json($error_message);
        }

        $item_api_parameter['output'] = array(
            "itemid",
            "name",
        );
        $item_api_parameter['sortfield'] = 'name';
        $item_api_parameter['sortorder'] = 'ASC'; // ASC DESC
        $item_api_parameter['limit'] = '10000'; // ASC DESC

        $item_api_method = 'item.get';
        $item_data = zabbix_api($item_api_method, $item_api_parameter);
        // dump($item_data);
        $itemData['data'] = $item_data;
        $itemData['code'] = 200;
        return json($itemData);
    }
}
