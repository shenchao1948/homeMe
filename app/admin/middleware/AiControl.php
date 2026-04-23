<?php
declare (strict_types = 1);

namespace app\admin\middleware;

use app\admin\model\AdminRole;
use think\facade\Session;

class AiControl
{
    public function handle($request, \Closure $next)
    {
        if (!Session::has('admin_id')) {
            if ($request->isAjax()) {
                return json(['code' => -1, 'msg' => '请先登录']);
            }
            return redirect('/admin/login');
        }
        
        $roleId = Session::get('role_id');
        $controller = $request->controller();
        $action = $request->action();
        
        if ($roleId) {
            $role = AdminRole::find($roleId);
            if ($role && $role->permissions) {
                $moduleKey = strtolower($controller);
                $permissions = $role->permissions;
                
                if (isset($permissions[$moduleKey])) {
                    $modulePerm = $permissions[$moduleKey];
                    
                    if ($modulePerm === 0) {
                        if ($request->isAjax()) {
                            return json(['code' => -1, 'msg' => '没有权限访问该模块']);
                        }
                        return response('<h1>没有权限访问该模块</h1>', 403);
                    }
                    
                    if ($modulePerm === 1 && in_array(strtolower($action), ['save', 'update', 'delete', 'create'])) {
                        if ($request->isAjax()) {
                            return json(['code' => -1, 'msg' => '该模块只读，无写入权限']);
                        }
                        return response('<h1>该模块只读，无写入权限</h1>', 403);
                    }
                }
            }
        }
        
        return $next($request);
    }
}
