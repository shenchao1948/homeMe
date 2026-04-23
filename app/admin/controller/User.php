<?php
declare (strict_types = 1);

namespace app\admin\controller;

use app\admin\model\OnlineUser;
use app\home\model\User as HomeUser;
use app\home\model\RoomUser;
use think\facade\Session;
use think\Request;

class User
{
    public function index()
    {
        if (!Session::has('admin_id')) {
            return redirect('/admin/login');
        }
        return view('user/index');
    }
    
    public function getUserList(Request $request)
    {
        $page = $request->get('page', 1);
        $limit = $request->get('limit', 10);
        $online = $request->get('online', '');
        
        $query = HomeUser::order('id', 'desc');
        
        if ($online !== '') {
            if ($online == '1') {
                $onlineTokens = OnlineUser::column('user_token');
                if (!empty($onlineTokens)) {
                    $query->whereIn('user_token', $onlineTokens);
                } else {
                    $query->whereRaw('1=0');
                }
            } else {
                $onlineTokens = OnlineUser::column('user_token');
                if (!empty($onlineTokens)) {
                    $query->whereNotIn('user_token', $onlineTokens);
                }
            }
        }
        
        $users = $query->paginate([
            'list_rows' => $limit,
            'page' => $page
        ]);
        
        $onlineTokens = OnlineUser::column('last_active_time', 'user_token');
        
        $userList = [];
        foreach ($users as $user) {
            $isOnline = isset($onlineTokens[$user->user_token]);
            
            $chatCount = \app\home\model\RoomCommons::where('user_id', $user->id)->count();
            
            $roomUser = RoomUser::where('user_id', $user->id)->find();
            $roomId = $roomUser ? $roomUser->room_id : null;
            
            $userList[] = [
                'id' => $user->id,
                'username' => $user->username ?? '用户' . $user->id,
                'user_token' => $user->user_token,
                'is_online' => $isOnline,
                'chat_count' => $chatCount,
                'room_id' => $roomId,
                'last_active_time' => $isOnline ? $onlineTokens[$user->user_token] : null,
                'login_time' => null
            ];
        }
        
        return json([
            'code' => 0,
            'msg' => '',
            'count' => $users->total(),
            'data' => $userList
        ]);
    }
    
    public function getOnlineUsers(Request $request)
    {
        $onlineUsers = OnlineUser::order('last_active_time', 'desc')->select();
        
        $userList = [];
        foreach ($onlineUsers as $onlineUser) {
            $user = HomeUser::where('user_token', $onlineUser->user_token)->find();
            if ($user) {
                $chatCount = \app\home\model\RoomCommons::where('user_id', $user->id)->count();
                
                $roomUser = RoomUser::where('user_id', $user->id)->find();
                $roomId = $roomUser ? $roomUser->room_id : null;
                
                $userList[] = [
                    'id' => $user->id,
                    'username' => $user->username ?? '用户' . $user->id,
                    'user_token' => $onlineUser->user_token,
                    'is_online' => true,
                    'chat_count' => $chatCount,
                    'room_id' => $roomId,
                    'last_active_time' => $onlineUser->last_active_time,
                    'login_time' => $onlineUser->login_time
                ];
            }
        }
        
        return json([
            'code' => 0,
            'msg' => '',
            'count' => count($userList),
            'data' => $userList
        ]);
    }
}
