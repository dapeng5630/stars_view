<?php

namespace app\home\controller;

use think\Controller;
use think\facade\Request;
use think\Db;
use \Cache;

class Grade extends Common
{
    public $tables = 'Grade_permission';

    /*
    * 角色添加
    */
    public function grade_add()
    {
        // 角色名称
        if (!empty(Request::param('gradeName'))) {
            $grade_add_data['grade_name'] = Request::param('gradeName');
        } else {
            $error_message['code'] = 504;
            $error_message['error'] = '请填写角色名称';
            return json($error_message);
        }


        $grade_add_result = Db::name($this->tables)
            ->data($grade_add_data)
            ->insert();
        if ($grade_add_result) {
            $success_message['code'] = 200;
            $success_message['success'] = '添加成功';
            return json($success_message);
        } else {
            $error_message['code'] = 504;
            $error_message['error'] = '添加失败';
            return json($error_message);
        }
    }

    /*
    * 角色列表
    */
    public function grade_list()
    {

        

        // 记录数量
        if (empty(Request::param('gradeSize'))) {
            $grade_size = 10;
        } else {
            $grade_size = Request::param('gradeSize');
        }

        // 页码
        if (empty(Request::param('gradePage'))) {
            $grade_page = 1;
        } else {
            $grade_page = Request::param('gradePage');
        }

        // 总数
        $grade_count = Db::name($this->tables)
            ->where('grade_name', 'like', '%' . Request::param('gradeName') . '%')
            ->count();

        // 起始位
        if ($grade_page == 1) {
            $start_number = 0;
        } else {
            $start_number = ($grade_page - 1) * $grade_size;
        }

        if ($grade_count < $start_number) {
            $error_message['code'] = 504;
            $error_message['error'] = '请求最大数超过基本记录数';
            return json($error_message);
        }

        // 获取字段
        $Grade_key = [
            'id',
            'grade_name',
            'permission',
        ];
        $grade_list['data'] = Db::name($this->tables)
            ->field($Grade_key)
            ->where('grade_name', 'like', '%' . Request::param('gradeName') . '%')
            ->limit($start_number, $grade_size)
            ->order('id desc')
            ->select();

        // dump($grade_list['data']);
        // dump(Db::name($this->tables)->getLastSql());
        if ($grade_list['data']) {
            $grade_list['count'] = $grade_count;
            $grade_list['code'] = 200;
            return json($grade_list);
        } else {
            $error_message['code'] = 504;
            $error_message['error'] = '无此数据';
            return json($error_message);
        }
    }

    /*
    * 角色删除
    */
    public function grade_delete()
    {

        // 判断是否有提交删除的角色id
        if (empty(Request::param('gradeid'))) {
            $error_message['code'] = 504;
            $error_message['error'] = '参数错误';
            return json($error_message);
        }

        // 基础操作不能删除
        if(Request::param('gradeid') == '1'){
            $error_message['code'] = 504;
            $error_message['error'] = '基础操作不能删除';
            return json($error_message);
        }
        $gradeid['id'] = explode(',', Request::param('gradeid'));

        // 删除提交的角色id的记录
        $grade_delete_result = Db::name($this->tables)
            ->where($gradeid)
            ->delete();
        // dump($grade_delete_result);
        // exit;
        if ($grade_delete_result) {
            $success_message['code'] = 200;
            $success_message['success'] = '删除成功';
            return json($success_message);
        } else {
            $error_message['code'] = 504;
            $error_message['error'] = '删除失败';
            return json($error_message);
        }
    }


    /*
    * 角色修改
    */
    public function grade_update()
    {
        if (!empty(Request::param('id')) && !empty(Request::param('menuGroup'))) {
            

            // 角色权限
            $grade_add_data['permission'] = Request::param('menuGroup');


            $grade_update_result = Db::name($this->tables)
                ->data($grade_add_data)
                ->where('id', Request::param('id'))
                ->update();
        // dump(Db::name($this->tables)->getLastSql());

                // return json(Db::name($this->tables)->getLastSql());
            if ($grade_update_result) {
                $success_message['code'] = 200;
                $success_message['success'] = '修改成功';
                return json($success_message);
            } else {
                $error_message['code'] = 504;
                $error_message['error'] = '参数错误';
                return json($error_message);
            }
        } else {
            $error_message['code'] = 504;
            $error_message['error'] = '参数错误';
            return json($error_message);
        }
    }
}
