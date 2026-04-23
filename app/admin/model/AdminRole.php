<?php
declare (strict_types = 1);

namespace app\admin\model;

use think\Model;

class AdminRole extends Model
{
    protected $name = 'admin_role';
    
    protected $pk = 'id';
    
    protected $autoWriteTimestamp = true;
    
    protected $createTime = 'create_time';
    protected $updateTime = 'update_time';
    
    protected $type = [
        'permissions' => 'array'
    ];
    
    public function admins()
    {
        return $this->hasMany(Admin::class, 'role_id', 'id');
    }
    
    public function setPermissionsAttr($value)
    {
        if (is_array($value)) {
            return json_encode($value, JSON_UNESCAPED_UNICODE);
        }
        return $value;
    }
    
    public function getPermissionsAttr($value)
    {
        if (empty($value)) {
            return [];
        }
        if (is_string($value)) {
            $result = json_decode($value, true);
            return is_array($result) ? $result : [];
        }
        return $value;
    }
}
