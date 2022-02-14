<?php
namespace think;

// 定义应用目录
define('APP_PATH', __DIR__ . '/mdab/');
// 加载框架基础引导文件
require __DIR__ . '/thinkphp/base.php';
// 添加额外的代码
// ...

// 执行应用并响应
Container::get('app', [APP_PATH])->run()->send();

// Container::get('app')->path(APP_PATH)->run()->send();