<?php
use think\facade\Route;

// home 应用路由
Route::get('ai/history', 'home/Index/getChatHistory');
Route::get('api/stats/today-messages', 'home/Index/getTodayMessages');

// admin 应用会自动通过 /admin/ 前缀访问

Route::rule('admin/login', function() {
    return app(\app\admin\controller\Index::class)->login();
});

Route::rule('admin/doLogin', function() {
    return app(\app\admin\controller\Index::class)->doLogin(request());
})->method('POST');

Route::rule('admin/logout', function() {
    return app(\app\admin\controller\Index::class)->logout();
})->middleware(\app\admin\middleware\AiControl::class);

Route::rule('admin/index', function() {
    return app(\app\admin\controller\Index::class)->index();
})->middleware(\app\admin\middleware\AiControl::class);

Route::rule('admin/room/index', function() {
    return app(\app\admin\controller\Room::class)->index();
})->middleware(\app\admin\middleware\AiControl::class);

Route::rule('admin/room/getRoomList', function() {
    return app(\app\admin\controller\Room::class)->getRoomList(request());
})->middleware(\app\admin\middleware\AiControl::class);

Route::rule('admin/room/join', function() {
    return app(\app\admin\controller\Room::class)->join(request());
})->method('POST')->middleware(\app\admin\middleware\AiControl::class);

Route::rule('admin/room/joinPage/:id', function($id) {
    return app(\app\admin\controller\Room::class)->joinPage(request());
})->middleware(\app\admin\middleware\AiControl::class);

Route::rule('admin/room/setAiControl', function() {
    return app(\app\admin\controller\Room::class)->setAiControl(request());
})->method('POST')->middleware(\app\admin\middleware\AiControl::class);

Route::rule('admin/user/index', function() {
    return app(\app\admin\controller\User::class)->index();
})->middleware(\app\admin\middleware\AiControl::class);

Route::rule('admin/user/getUserList', function() {
    return app(\app\admin\controller\User::class)->getUserList(request());
})->middleware(\app\admin\middleware\AiControl::class);

Route::rule('admin/user/getOnlineUsers', function() {
    return app(\app\admin\controller\User::class)->getOnlineUsers(request());
})->middleware(\app\admin\middleware\AiControl::class);

Route::rule('admin/permission/index', function() {
    return app(\app\admin\controller\Permission::class)->index();
})->middleware(\app\admin\middleware\AiControl::class);

Route::rule('admin/permission/getRoleList', function() {
    return app(\app\admin\controller\Permission::class)->getRoleList(request());
})->middleware(\app\admin\middleware\AiControl::class);

Route::rule('admin/permission/saveRole', function() {
    return app(\app\admin\controller\Permission::class)->saveRole(request());
})->method('POST')->middleware(\app\admin\middleware\AiControl::class);

Route::rule('admin/permission/deleteRole', function() {
    return app(\app\admin\controller\Permission::class)->deleteRole(request());
})->method('POST')->middleware(\app\admin\middleware\AiControl::class);
