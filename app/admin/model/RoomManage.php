<?php
declare (strict_types = 1);

namespace app\admin\model;

use think\Model;

class RoomManage extends Model
{
    protected $name = 'room_manage';
    
    protected $pk = 'id';
    
    protected $autoWriteTimestamp = true;
    
    protected $createTime = 'create_time';
    protected $updateTime = 'update_time';
    
    protected $type = [
        'room_id' => 'integer',
        'ai_enabled' => 'boolean',
        'admin_id' => 'integer',
        'online_count' => 'integer'
    ];
}
