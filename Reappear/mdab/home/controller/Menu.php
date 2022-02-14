<?php

namespace app\home\controller;

use think\Controller;
use think\facade\Request;
use think\Db;
use \Cache;

class Menu extends Common
{


    /*
    * 菜单列表
    */
    public function menu_list()
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
        // dump($Menu_group['data']);
        // exit;
        if (!empty($Menu_group['data'])) {
            $Menu_group['code'] = 200;
            return json($Menu_group);
        } else {
            $error_message['code'] = 504;
            $error_message['error'] = '数据错误';
            return json($error_message);
        }
    }


    /*
    * 菜单添加
    */
    public function menu_add()
    {
        if (Request::isPost()) {


            if (empty(Request::post()['menu_name'])) {
                $this->error('菜单名，参数错误，且不能为空');
            }
            if (empty(Request::post()['function_name'])) {
                $this->error('控制器名/方法名，参数错误，且不能为空');
            }
            if (empty(Request::post()['type'])) {
                $this->error('类型，参数错误，且不能为空');
            }

            $menu_add_result = Db::name('Menu')
                ->data(Request::post())
                ->insert();
            if ($menu_add_result < 1) {
                $this->error('添加失败');
            } else {
                $this->success('添加成功', url('Menu/menu_list'));
            }
        } else {
            $key_words = [
                'id',
                'menu_name',
            ];
            $menu_superior_group = Db::name('Menu')
                ->field($key_words)
                ->where('type', 'dropdown')
                ->select();
            $this->assign('menu_superior_group', $menu_superior_group);
            return $this->fetch();
        }
    }



    /*
    * 菜单修改
    */
    public function menu_update()
    {
        if (Request::isPost()) {

            if (empty(Request::post()['menu_name'])) {
                $this->error('菜单名，参数错误，且不能为空');
            }
            if (empty(Request::post()['function_name'])) {
                $this->error('控制器名/方法名，参数错误，且不能为空');
            }
            if (empty(Request::post()['type'])) {
                $this->error('类型，参数错误，且不能为空');
            }

            $menu_updata_result = Db::name('Menu')
                ->where('id', Request::post()['id'])
                ->data(Request::post())
                ->update();

            if ($menu_updata_result < 1) {
                $this->error('修改失败');
            } else {
                $this->success('修改成功');
            }
        } else {


            $menu_superior = Db::name('Menu')
                ->where('id', Request::param('id'))
                ->find();
            $this->assign('menu_superior', $menu_superior);

            $key_words = [
                'id',
                'menu_name',
            ];
            $menu_superior_group = Db::name('Menu')
                ->field($key_words)
                ->where('type', 'dropdown')
                ->select();

            $this->assign('menu_superior_group', $menu_superior_group);
            return $this->fetch();
        }
    }

    /*
    * 菜单删除
    */
    public function menu_delete()
    {
        if (Request::isGet()) {

            if (!is_numeric(Request::param('id'))) {
                $this->error('参数错误');
            }

            $menu_delete_result = Db::name('Menu')
                ->where('id', Request::param('id'))
                ->delete();
            // echo  Db::getLastSql();
            if ($menu_delete_result < 1) {
                $this->error('删除失败');
            } else {
                if (Request::param('nickname') == 1) {
                    return json($menu_delete_result);
                }
                $this->success('删除成功');
            }
            exit;
        }
        $this->error('参数错误');
    }
}
