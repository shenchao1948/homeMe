<?php
use think\facade\Route;

// 登录相关（不需要中间件）
Route::get('login', 'Index/login');
Route::post('doLogin', 'Index/doLogin');

// 需要权限验证的路由
Route::group('', function () {
    Route::get('logout', 'Index/logout');

    Route::get('index', 'Index/index');
    Route::get('dashboard', 'Index/index');

    Route::get('room/index', 'Room/index');
    Route::get('room/getRoomList', 'Room/getRoomList');
    Route::post('room/join', 'Room/join');
    Route::get('room/joinPage/:id', 'Room/joinPage');
    Route::post('room/setAiControl', 'Room/setAiControl');

    Route::get('user/index', 'User/index');
    Route::get('user/getUserList', 'User/getUserList');
    Route::get('user/getOnlineUsers', 'User/getOnlineUsers');

    Route::get('permission/index', 'Permission/index');
    Route::get('permission/getRoleList', 'Permission/getRoleList');
    Route::post('permission/saveRole', 'Permission/saveRole');
    Route::post('permission/deleteRole', 'Permission/deleteRole');
})->middleware(\app\admin\middleware\AiControl::class);
