<?php
declare (strict_types = 1);

namespace app\admin\model;

use think\Model;

class OnlineUser extends Model
{
    protected $name = 'online_user';
    
    protected $pk = 'id';
    
    protected $autoWriteTimestamp = true;
    
    protected $createTime = 'login_time';
    protected $updateTime = 'last_active_time';
    
    protected $type = [
        'chat_count' => 'integer',
        'room_id' => 'integer'
    ];
    
    public function user()
    {
        return $this->belongsTo(\app\home\model\User::class, 'user_token', 'user_token');
    }
}
