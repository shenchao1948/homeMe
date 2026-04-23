<?php
declare (strict_types = 1);

namespace app\admin\controller;

use app\admin\model\Admin;
use think\facade\Session;
use think\Request;

class Index
{
    public function index()
    {
        if (!Session::has('admin_id')) {
            return redirect('/admin/login');
        }
        return view('dashboard');
    }
    
    public function login()
    {
        if (Session::has('admin_id')) {
            return redirect('/admin/index');
        }
        return view('index/index');
    }
    
    public function doLogin(Request $request)
    {
        $username = $request->post('username', '');
        $password = $request->post('password', '');
        
        if (empty($username) || empty($password)) {
            return json(['code' => 0, 'msg' => '用户名和密码不能为空']);
        }
        
        $admin = Admin::where('username', $username)->find();
        
        if (!$admin || !$admin->verifyPassword($password)) {
            return json(['code' => 0, 'msg' => '用户名或密码错误']);
        }
        
        Session::set('admin_id', $admin->id);
        Session::set('admin_username', $admin->username);
        Session::set('role_id', $admin->role_id);
        
        return json(['code' => 1, 'msg' => '登录成功', 'data' => [
            'username' => $admin->username
        ]]);
    }
    
    public function logout()
    {
        Session::delete('admin_id');
        Session::delete('admin_username');
        Session::delete('role_id');
        return redirect('/admin/login');
    }
}
