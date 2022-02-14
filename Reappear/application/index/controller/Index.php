<?php
namespace app\index\controller;

class Index
{
    public function index()
    {
        exit();
        
    }

    public function hello($name = 'ThinkPHP5')
    {
        return 'hello,' . $name;
    }
}
