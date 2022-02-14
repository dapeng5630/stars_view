<?php


/*
* 获取token
*/
function zabbix_api_token($user_info,$api_nfo,$way = "GET")
{
    
    $api_info = array();

    $api_info['jsonrpc'] = $api_nfo['jsonrpc'];
    $api_info['method'] = $api_nfo['user_login'];
    $api_parameter = array();
    $api_parameter['user'] = $user_info['user'];
    $api_parameter['password'] =  $user_info['password'];
    $api_info['params'] = $api_parameter;
    $api_info['id'] = '1';
    $api_json_info = json_encode($api_info);

    $ch = curl_init();
    

    curl_setopt($ch, CURLOPT_URL, $api_nfo['ip']);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
    curl_setopt($ch, CURLOPT_CUSTOMREQUEST, $way);
    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
    curl_setopt($ch, CURLOPT_POSTFIELDS, $api_json_info);

    $headers = array();
    $headers[] = 'User-Agent: Apipost client Runtime/+https://www.apipost.cn/';
    $headers[] = 'Content-Type: application/json';
    curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);

    $result = curl_exec($ch);
    

    if (curl_errno($ch)) {
        $error_message['code'] =402;
        $error_message['message'] = 'Error:' . curl_error($ch);
        echo 'Error:' . curl_error($ch);
        exit;
    }

    curl_close($ch);
    $resultArr = json_decode($result, true);
    
    if (!empty($resultArr['result'])) {
        $success_message['code'] = 200;
        $success_message['token'] = $resultArr['result'];
        return $success_message;
        
    } else {
        $error_message['code'] = 504;
        $error_message['error'] = $resultArr['error']['message'];
        return $error_message;
    }
}

/*
* $ip 请求地址
* $api_parameter_json 请求参数
* $way 请求途径默认GET
*/
function zabbix_api($api_method, $api_parameter, $way = 'GET')
{
    
    if (empty($api_method)) {
        return 'method参数错误,且不能为空';
    }
    if (empty($api_parameter)) {
        return 'parameter参数错误,且不能为空';
    }

    $api_info = array();
    $api_info['jsonrpc'] = Session('user_info.api_info.jsonrpc');
    $api_info['method'] = $api_method;
    $api_info['params'] = $api_parameter;
    $api_info['auth'] = Session('user_info.api_info.token');
    $api_info['id'] = empty(Session('user_info.api_info.userid')) ? 1 : Session('user_info.api_info.userid');
    $api_json_info = json_encode($api_info);
    
    $ch = curl_init();

    curl_setopt($ch, CURLOPT_URL, Session('user_info.api_info.ip'));
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
    curl_setopt($ch, CURLOPT_CUSTOMREQUEST, $way);
    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
    curl_setopt($ch, CURLOPT_POSTFIELDS, $api_json_info);

    $headers = array();
    $headers[] = 'User-Agent: Apipost client Runtime/+https://www.apipost.cn/';
    $headers[] = 'Content-Type: application/json';
    curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);

    $result = curl_exec($ch);

    if (curl_errno($ch)) {
        echo 'Error:' . curl_error($ch);
    }
    curl_close($ch);

    $resultArr = json_decode($result, true);
    
    if (!empty($resultArr['result']) != 0) {
        $api_result = $resultArr['result'];
        return $api_result;
    } else {
        return false;
    }
}


function grade_problem($problem_number)
{
    switch ($problem_number) {
        case 0:
            // $result['name'] = '未分类';
            $result = '#B5B5B5';
            break;
        case 1:
            // $result['name'] = '信息';
            $result = '#63B8FF';
            break;
        case 2:
            // $result['name'] = '警告';
            $result = '#EEAD0E';
            break;
        case 3:
            // $result['name'] = '一般严重';
            $result = '#EEAD0E';
            break;
        case 4:
            // $result['name'] = '严重';
            $result = '#EE7600';
            break;
        case 5:
            // $result['name'] = '灾难';
            $result = '#DC143C';
            break;
        default:
            // $result['name'] = '灾难';
            $result = '参数错误';
            break;
    }
    return $result;
}

/*
* 获取年份有多少天
*/
function cal_days_in_year($year)
{
    $days = 0;
    for ($month = 1; $month <= 12; $month++) {
        $days = $days + cal_days_in_month(CAL_GREGORIAN, $month, $year);
    }
    return $days;
}

function format_date($time)
{

    $t['y'] = date('Y', time()) - date('Y', $time);
    $t['m'] = date('m', time()) - date('m', $time);
    $t['d'] = date('d', time()) - date('d', $time);
    $t['h'] = date('h', time()) - date('h', $time);
    $t['i'] = date('i', time()) - date('i', $time);
    $t['s'] = date('s', time()) - date('s', $time);

    $t_result = '';
    foreach ($t as $key => $value) {
        if ($value != 0) {
            $t_result = $t_result . $value . $key . ' ';
        }
    }
    return $t_result;
}
