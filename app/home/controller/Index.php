<?php
declare (strict_types = 1);

namespace app\home\controller;

use app\home\model\RoomCommons;
use app\home\model\User;
use think\Request;

class Index
{
    public function index()
    {
        // 如果未登录，先执行登录逻辑
        $userList = session('userList');
        if(empty($userList)){
            $this->login();
        }

        return view("index",array(
            "hostUrl" => request()->getHostUrl(),
            "token" => session("userList.user_token"),
        ));
    }
    
    public function getChatHistory(Request $request)
    {
        $userId = session('userList.id');
        $limit = $request->get('limit', 50);
        
        if (!$userId) {
            return json(['code' => 0, 'msg' => '用户ID不能为空']);
        }
        
        $history = RoomCommons::where('user_id', $userId)
            ->order('create_time', 'desc')
            ->limit($limit)
            ->field('id,message_type,content,create_time')
            ->select()
            ->order("id","asc")
            ->toArray();
        
        return json([
            'code' => count($history)>0?200:1,
            'msg' => 'success',
            'data' => json_decode(json_encode($history), true)
        ]);
    }
    
    public function getTodayMessages(Request $request)
    {
        $date = $request->get('date', date('Y-m-d'));
        
        $startTime = $date . ' 00:00:00';
        $endTime = $date . ' 23:59:59';
        
        $count = RoomCommons::whereBetween('create_time', [$startTime, $endTime])->count();
        
        return json([
            'code' => 0,
            'msg' => 'success',
            'count' => $count
        ]);
    }
    protected function login()
    {
        // ThinkPHP 会根据配置自动从 X-Real-IP 或 X-Forwarded-For 获取真实 IP
        $ip = request()->ip();

        $user = User::where("user_ip",$ip)->find();
        if(!$user){
            $token = $this->request->buildToken('__token__', 'sha1');
            $user = User::create(array(
                "user_ip" => $ip,
                "user_token" => $token,
            ));
            session("userList",$user->toArray());
        }else{
            session("userList",$user->toArray());
        }
    }

    public function testIP()
    {
        echo "<h2>ThinkPHP request()->ip() 测试结果</h2>";
        echo "<p><strong>获取到的 IP:</strong> " . request()->ip() . "</p>";
        echo "<hr>";
        echo "<h3>HTTP Header 信息:</h3>";
        echo "<pre>";

        $headers = [
            'X-Real-IP' => request()->header('X-Real-IP') ?: '(empty)',
            'X-Forwarded-For' => request()->header('X-Forwarded-For') ?: '(empty)',
            'HTTP_CLIENT_IP' => request()->header('HTTP_CLIENT_IP') ?: '(empty)',
            'REMOTE_ADDR' => $_SERVER['REMOTE_ADDR'] ?? '(empty)',
        ];

        foreach ($headers as $key => $value) {
            // 确保值是字符串
            if (is_array($value)) {
                $value = implode(', ', $value);
            }
            echo str_pad($key, 25) . ": " . htmlspecialchars((string)$value) . "\n";
        }

        echo "</pre>";
        echo "<hr>";
        echo "<p style='color: #999;'>提示: 如果看到 REMOTE_ADDR 是 127.0.0.1,但 X-Real-IP 有值,说明配置成功!</p>";
        echo "<p><a href='/'>返回首页</a></p>";
        die();
    }

    public function logout()
    {
        session(null);
        return json(['code' => 200, 'msg' => 'success']);
    }
}