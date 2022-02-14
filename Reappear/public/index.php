<?php
// +----------------------------------------------------------------------
// | ThinkPHP [ WE CAN DO IT JUST THINK ]
// +----------------------------------------------------------------------
// | Copyright (c) 2006-2018 http://thinkphp.cn All rights reserved.
// +----------------------------------------------------------------------
// | Licensed ( http://www.apache.org/licenses/LICENSE-2.0 )
// +----------------------------------------------------------------------
// | Author: liu21st <liu21st@gmail.com>
// +----------------------------------------------------------------------
namespace think;
// c0558209c21ce4afcbf1ef454408729dece9b47beab45de73c3b417a4213d87d
// 35da76f487d33a291e336bf088a4365d684ee35943d059052e01d51adb878146

// 定义应用目录
define('APP_PATH', __DIR__ . '/../mdab/');
// 加载框架基础引导文件
require __DIR__ . '/../thinkphp/base.php';
// 添加额外的代码
// ...

// 执行应用并响应
Container::get('app', [APP_PATH])->run()->send();

/*
// [ 应用入口文件 ]
namespace think;

// 加载基础文件
require __DIR__ . '/../thinkphp/base.php';

// 执行应用并响应
Container::get('app')->run()->send();
*/
