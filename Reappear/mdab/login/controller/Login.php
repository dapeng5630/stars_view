<?php
namespace app\admin\controller;
use think\Controller;
use think\facade\Request;
use think\Db;
use \Cache;

class index extends Common
{   
    public function index()
    {   
        echo 123;
        exit;
        if (Request::isPost()) {
            $data['identity'] = Request::post('user');
            $data['signal'] = md5(Request::post('password').config('app.signalsa'));
            dump($data);
            

            $res = Db::name('Night_user')
            ->where($data)
            ->find();
            if ($res['id'] > 0) {
                Session::set('name',$data['identity']);
            }
            // dump(Cache::get('name')); 
            
        }else{
            return "参数错误";
        }

        

    }


    
}    
