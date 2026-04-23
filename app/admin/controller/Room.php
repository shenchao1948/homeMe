<?php
declare (strict_types = 1);

namespace app\admin\controller;

use app\admin\model\RoomManage;
use app\admin\model\OnlineUser;
use app\home\model\Room as HomeRoom;
use app\home\model\User as HomeUser;
use app\home\model\RoomUser;
use think\facade\Session;
use think\Request;

class Room
{
    public function index()
    {
        if (!Session::has('admin_id')) {
            return redirect((string)url('admin/Index/login'));
        }
        return view('room/index');
    }
    
    public function getRoomList(Request $request)
    {
        $page = $request->get('page', 1);
        $limit = $request->get('limit', 10);
        
        $rooms = HomeRoom::order('id', 'desc')
            ->paginate([
                'list_rows' => $limit,
                'page' => $page
            ]);
        
        $roomList = [];
        foreach ($rooms as $room) {
            $userCount = RoomUser::where('room_id', $room->id)->count();
            $messageCount = \app\home\model\RoomCommons::where('room_id', $room->id)->count();
            
            $onlineCount = $this->getRoomOnlineCount($room->id);
            
            $roomList[] = [
                'id' => $room->id,
                'room_code' => $room->room_code,
                'room_name' => $room->room_name,
                'create_user' => $room->create_user,
                'user_count' => $userCount,
                'online_count' => $onlineCount,
                'message_count' => $messageCount,
                'create_time' => $room->create_time
            ];
        }
        
        return json([
            'code' => 0,
            'msg' => '',
            'count' => $rooms->total(),
            'data' => $roomList
        ]);
    }
    
    private function getRoomOnlineCount(int $roomId): int
    {
        try {
            $onlineUsers = OnlineUser::where('room_id', $roomId)->select();
            return count($onlineUsers);
        } catch (\Exception $e) {
            return 0;
        }
    }
    
    public function join(Request $request)
    {
        if (!Session::has('admin_id')) {
            return json(['code' => 0, 'msg' => '请先登录']);
        }
        
        $roomId = $request->param('id', 0);
        $aiEnabled = $request->get('ai_enabled', 1);
        
        if (!$roomId) {
            return json(['code' => 0, 'msg' => '房间ID不能为空']);
        }
        
        $room = HomeRoom::find($roomId);
        if (!$room) {
            return json(['code' => 0, 'msg' => '房间不存在']);
        }
        
        Session::set('join_room_id', $roomId);
        Session::set('ai_enabled', $aiEnabled);
        
        return json([
            'code' => 1,
            'msg' => '加入房间成功',
            'data' => [
                'room_id' => $roomId,
                'room_name' => $room->room_name,
                'ai_enabled' => $aiEnabled
            ]
        ]);
    }
    
    public function joinPage(Request $request)
    {
        if (!Session::has('admin_id')) {
            return redirect((string)url('admin/Index/login'));
        }
        
        $roomId = $request->param('id', 0);
        return view('room/join', ['room_id' => $roomId]);
    }
    
    public function setAiControl(Request $request)
    {
        if (!Session::has('admin_id')) {
            return json(['code' => 0, 'msg' => '请先登录']);
        }
        
        $roomId = $request->post('room_id', 0);
        $aiEnabled = $request->post('ai_enabled', 1);
        
        if (!$roomId) {
            return json(['code' => 0, 'msg' => '房间ID不能为空']);
        }
        
        $roomManage = RoomManage::where('room_id', $roomId)->find();
        
        if ($roomManage) {
            $roomManage->ai_enabled = $aiEnabled;
            $roomManage->save();
        } else {
            RoomManage::create([
                'room_id' => $roomId,
                'admin_id' => Session::get('admin_id'),
                'ai_enabled' => $aiEnabled
            ]);
        }
        
        return json(['code' => 1, 'msg' => '设置成功']);
    }
}
