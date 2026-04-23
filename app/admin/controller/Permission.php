<?php
declare (strict_types = 1);

namespace app\admin\controller;

use app\admin\model\AdminRole;
use think\facade\Session;
use think\Request;

class Permission
{
    public function index()
    {
        if (!Session::has('admin_id')) {
            return redirect('/admin/login');
        }
        return view('permission/index');
    }
    
    public function getRoleList(Request $request)
    {
        $roles = AdminRole::order('id', 'asc')->select();
        
        $roleList = [];
        foreach ($roles as $role) {
            $roleList[] = [
                'id' => $role->id,
                'role_name' => $role->role_name,
                'description' => $role->description,
                'permissions' => $role->permissions,
                'create_time' => $role->create_time
            ];
        }
        
        return json([
            'code' => 0,
            'msg' => '',
            'data' => $roleList
        ]);
    }
    
    public function saveRole(Request $request)
    {
        if (!Session::has('admin_id')) {
            return json(['code' => 0, 'msg' => '请先登录']);
        }
        
        $id = $request->post('id', 0);
        $roleName = $request->post('role_name', '');
        $description = $request->post('description', '');
        $permissions = $request->post('permissions', []);
        
        if (empty($roleName)) {
            return json(['code' => 0, 'msg' => '角色名称不能为空']);
        }
        
        if ($id > 0) {
            $role = AdminRole::find($id);
            if (!$role) {
                return json(['code' => 0, 'msg' => '角色不存在']);
            }
            $role->role_name = $roleName;
            $role->description = $description;
            $role->permissions = $permissions;
            $role->save();
        } else {
            AdminRole::create([
                'role_name' => $roleName,
                'description' => $description,
                'permissions' => $permissions
            ]);
        }
        
        return json(['code' => 1, 'msg' => '保存成功']);
    }
    
    public function deleteRole(Request $request)
    {
        if (!Session::has('admin_id')) {
            return json(['code' => 0, 'msg' => '请先登录']);
        }
        
        $id = $request->post('id', 0);
        
        if ($id <= 0) {
            return json(['code' => 0, 'msg' => '参数错误']);
        }
        
        $adminCount = \app\admin\model\Admin::where('role_id', $id)->count();
        if ($adminCount > 0) {
            return json(['code' => 0, 'msg' => '该角色下还有管理员，无法删除']);
        }
        
        AdminRole::destroy($id);
        
        return json(['code' => 1, 'msg' => '删除成功']);
    }
}
