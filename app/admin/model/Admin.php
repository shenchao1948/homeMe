<?php
declare (strict_types = 1);

namespace app\admin\model;

use think\Model;

class Admin extends Model
{
    protected $name = 'admin';
    
    protected $pk = 'id';
    
    protected $autoWriteTimestamp = true;
    
    protected $createTime = 'create_time';
    protected $updateTime = 'update_time';
    
    protected $hidden = ['password'];
    
    public function setPasswordAttr($value)
    {
        $password = password_hash($value, PASSWORD_DEFAULT);
        return $password;
    }
    
    public function verifyPassword(string $password): bool
    {
        return password_verify($password, $this->password);
    }
    
    public function role()
    {
        return $this->belongsTo(AdminRole::class, 'role_id', 'id');
    }
}
