<?php
declare(strict_types=1);

namespace app\webSocket;

use app\home\model\Room;
use app\home\model\RoomCommons;
use app\home\model\RoomUser;
use app\home\model\User;
use app\admin\model\OnlineUser;
use Workerman\Worker;
use Workerman\Connection\TcpConnection;
use Workerman\Timer;

class AiServer
{
    private const MAX_ROOM_COUNT = 50;

    private Worker $worker;

    private array $clients = [];

    private array $userConnections = [];

    private array $userToRoom = [];

    private array $roomUsers = [];

    private const HEARTBEAT_TIME = 55;

    private const PING_INTERVAL = 25;

    private const MAX_MESSAGE_LENGTH = 500;

    private const MESSAGE_COOLDOWN = 1;

    private array $lastMessageTime = [];

    private ?Aliyun $aliyun = null;

    public function __construct()
    {

    }

    public function onWorkerStart(Worker $worker): void
    {
        echo "WebSocket服务器已在端口2346启动\n";

        Timer::add(self::PING_INTERVAL, function() use ($worker) {
            $currentTime = time();
            foreach ($worker->connections as $connection) {
                if (isset($connection->lastMessageTime)) {
                    $timeSinceLastMessage = $currentTime - $connection->lastMessageTime;
                    if ($timeSinceLastMessage >= self::HEARTBEAT_TIME) {
                        echo "连接 {$connection->id} 超时，关闭连接\n";
                        $connection->close();
                        continue;
                    }

                    if ($timeSinceLastMessage >= self::PING_INTERVAL) {
                        $this->sendToClient($connection, [
                            'type' => 'ping',
                            'timestamp' => $currentTime
                        ]);
                    }
                }
            }
        });
    }

    public function onConnect(TcpConnection $connection): void
    {
        $connection->lastMessageTime = time();
        $this->clients[$connection->id] = [
            "connection" => $connection,
            "userID" => null
        ];
        echo "新客户端连接: {$connection->id}, 当前连接数: " . count($this->clients) . "\n";
    }

    public function onMessage(TcpConnection $connection, string $data): void
    {
        $connection->lastMessageTime = time();

        echo "📨 [服务端] 收到消息 - 连接ID: {$connection->id}, 数据长度: " . strlen($data) . "\n";

        try {
            $message = json_decode($data, true, 512, JSON_THROW_ON_ERROR);

            if (!is_array($message)) {
                echo "❌ [服务端] 消息格式错误\n";
                $this->sendErrorMessage($connection, 'Invalid message format');
                return;
            }

            echo "✅ [服务端] 消息解析成功, type: " . ($message['type'] ?? 'unknown') . "\n";
            $this->handleMessageType($connection, $message);

        } catch (\JsonException $e) {
            echo "❌ [服务端] JSON解析错误: " . $e->getMessage() . "\n";
            $this->sendErrorMessage($connection, 'JSON decode error: ' . $e->getMessage());
        } catch (\Exception $e) {
            echo "❌ [服务端] 处理消息异常: " . $e->getMessage() . "\n";
            echo "   文件: " . $e->getFile() . ":" . $e->getLine() . "\n";
            $this->sendErrorMessage($connection, 'Server error: ' . $e->getMessage());
        }
    }

    private function handleMessageType(TcpConnection $connection, array $message): void
    {
        $type = $message['type'] ?? '';

        switch ($type) {
            case 'auth':
                $this->handleAuth($connection, $message['data'] ?? []);
                break;

            case 'chat':
                $this->handleChat($connection, $message['data'] ?? []);
                break;

            case 'pong':
                break;

            default:
                $this->sendErrorMessage($connection, 'Unknown message type: ' . $type);
        }
    }

    private function handleAuth(TcpConnection $connection, array $data): void
    {
        $token = $data['token'] ?? '';
        $userId = $data['user_id'] ?? 0;

        if (empty($token) || empty($userId)) {
            $this->sendErrorMessage($connection, 'Authentication failed: missing parameters');
            $connection->close();
            return;
        }

        $authResult = $this->validateToken($token,$userId);
        $userId = "".$authResult['user_id'];
        if (!$authResult['success']) {
            $this->sendErrorMessage($connection, 'Authentication failed: ' . $authResult['message']);
            $connection->close();
            return;
        }

        $connection->userId = $token;
        
        if (!isset($this->userConnections[$token])) {
            $this->userConnections[$token] = [];
        }
        
        $this->userConnections[$token][$connection->id] = $connection;
        $this->clients[$connection->id]["userID"] = $token;
        
        $roomId = $authResult['room_id'];
        $this->userToRoom[$token] = $roomId;
        
        if (!isset($this->roomUsers[$roomId])) {
            $this->roomUsers[$roomId] = [];
        }
        if (!in_array($token, $this->roomUsers[$roomId])) {
            $this->roomUsers[$roomId][] = $token;
        }

        $this->updateOnlineUserStatus($token, $roomId);

        $this->sendToClient($connection, [
            'type' => 'system',
            'data' => [
                'event' => 'authenticated',
                'room_id' => $roomId,
                'message' => 'Authentication successful'
            ]
        ]);

        echo "用户 {$userId} (token: {$token}) 认证成功，加入房间 {$roomId}，当前连接数: " . count($this->userConnections[$token]) . "\n";
    }

    private function updateOnlineUserStatus(string $userToken, int $roomId): void
    {
        try {
            $onlineUser = OnlineUser::where('user_token', $userToken)->find();
            
            if ($onlineUser) {
                $onlineUser->room_id = $roomId;
                $onlineUser->last_active_time = date('Y-m-d H:i:s');
                $onlineUser->save();
            } else {
                OnlineUser::create([
                    'user_token' => $userToken,
                    'room_id' => $roomId,
                    'chat_count' => 0,
                    'login_time' => date('Y-m-d H:i:s'),
                    'last_active_time' => date('Y-m-d H:i:s')
                ]);
            }
        } catch (\Exception $e) {
            echo "更新在线用户状态失败: " . $e->getMessage() . "\n";
        }
    }

    private function updateUserChatCount(string $userToken): void
    {
        try {
            $onlineUser = OnlineUser::where('user_token', $userToken)->find();
            if ($onlineUser) {
                $onlineUser->chat_count = $onlineUser->chat_count + 1;
                $onlineUser->last_active_time = date('Y-m-d H:i:s');
                $onlineUser->save();
            }
        } catch (\Exception $e) {
            echo "更新用户聊天次数失败: " . $e->getMessage() . "\n";
        }
    }

    private function removeOnlineUser(string $userToken): void
    {
        try {
            OnlineUser::where('user_token', $userToken)->delete();
        } catch (\Exception $e) {
            echo "删除在线用户记录失败: " . $e->getMessage() . "\n";
        }
    }

    private function validateToken(string $token, string $userId): array
    {
        try {
            $user = User::where('user_token', $token)->find();
            
            if (empty($user) || empty($user->id)) {
                return [
                    'success' => false,
                    'message' => 'Invalid token',
                    'room_id' => null,
                    'user_id' => $userId
                ];
            }

            $roomUser = RoomUser::where('user_id', $user->id)->find();
            
            if (!$roomUser) {
                $room = Room::create(['create_user' => $user->id]);
                $roomId = $room->id;
                
                RoomUser::create([
                    'user_id' => (string)$user->id,
                    'room_id' => $roomId
                ]);
                
                echo "为用户 {$user->id} 创建新房间 {$roomId}\n";
            } else {
                $roomId = $roomUser->room_id;
            }

            return [
                'success' => true,
                'message' => 'Success',
                'room_id' => $roomId,
                'user_id' => (string)$user->id
            ];
            
        } catch (\Exception $e) {
            echo "Token验证异常: " . $e->getMessage() . "\n";
            return [
                'success' => false,
                'message' => 'Server error during authentication',
                'room_id' => null,
                'user_id' => $userId
            ];
        }
    }

    private function handleChat(TcpConnection $connection, array $data): void
    {
        if (!isset($connection->userId)) {
            $this->sendErrorMessage($connection, 'Not authenticated');
            return;
        }

        $userToken = $connection->userId;
        
        if (!$this->checkMessageRateLimit($userToken)) {
            $this->sendErrorMessage($connection, 'Message rate limit exceeded');
            return;
        }

        $content = trim($data['content'] ?? '');
        
        if (empty($content)) {
            $this->sendErrorMessage($connection, 'Empty message content');
            return;
        }

        if (mb_strlen($content) > self::MAX_MESSAGE_LENGTH) {
            $this->sendErrorMessage($connection, 'Message too long (max ' . self::MAX_MESSAGE_LENGTH . ' characters)');
            return;
        }

        $targetUserId = $data['target_user_id'] ?? 0;
        $roomId = $data['room_id'] ?? ($this->userToRoom[$userToken] ?? null);

        $isAiMessage = ($data['is_ai'] ?? false) || $targetUserId === 'ai';

        if ($isAiMessage) {
            $this->handleAiChat($connection, $content, $roomId, $userToken);
            return;
        }

        $messageData = [
            'type' => 'chat',
            'data' => [
                'isStreaming' => false,
                'content' => $content,
                'timestamp' => time(),
                'room_id' => $roomId,
                'sender_id' => $userToken
            ]
        ];

        if ($targetUserId > 0 && isset($this->userConnections[$targetUserId])) {
            $this->sendToClient($this->userConnections[$targetUserId], $messageData);
            echo "私聊消息: {$userToken} -> {$targetUserId}\n";
        } elseif ($roomId) {
            $this->broadcastToRoom($messageData, $roomId, $connection);
            echo "房间消息: 房间 {$roomId}, 发送者 {$userToken}\n";
        } else {
            $this->sendErrorMessage($connection, 'No valid room or target user');
            return;
        }

        $this->updateUserChatCount($userToken);

        $this->sendToClient($connection, [
            'type' => 'system',
            'data' => ['event' => 'message_sent']
        ]);
    }

    private function handleAiChat(TcpConnection $connection, string $message, ?int $roomId, string $userToken): void
    {
        echo "🤖 [服务端] 开始处理AI对话 - 用户: {$userToken}, 房间: {$roomId}\n";
        
        if ($this->aliyun === null) {
            try {
                echo "   [服务端] 初始化阿里云AI服务...\n";
                $this->aliyun = new Aliyun();
                echo "   ✅ [服务端] AI服务初始化成功\n";
            } catch (\Exception $e) {
                echo "   ❌ [服务端] AI服务初始化失败: " . $e->getMessage() . "\n";
                $this->sendErrorMessage($connection, 'AI服务初始化失败: ' . $e->getMessage());
                return;
            }
        }

        // 先发送用户消息给其他客户端
        $userMessageData = [
            'type' => 'chat',
            'data' => [
                'isStreaming' => false,
                'content' => $message,
                'timestamp' => time(),
                'room_id' => $roomId,
                'sender_id' => $userToken,
                'sender_connection_id' => $connection->id,
                'is_ai' => false
            ]
        ];

        $this->sendToOtherUserConnections($userToken, $connection->id, $userMessageData);
        
        if ($roomId) {
            $this->broadcastToRoom($userMessageData, $roomId, $connection);
        }

        // 【关键】立即发送 start 信号
        echo "   📤 [服务端] 发送 AI start 信号...\n";
        $startSignal = [
            'type' => 'chat',
            'data' => [
                'isStreaming' => true,
                'content' => '',
                'timestamp' => time(),
                'room_id' => $roomId,
                'sender_id' => 'ai',
                'is_ai' => true,
                'status' => 'start'
            ]
        ];
        
        $this->sendToAllUserConnections($userToken, $startSignal);
        echo "   ✅ [服务端] AI start 信号已发送\n";

        // 获取聊天历史
        echo "   📚 [服务端] 获取聊天历史...\n";
        $contextMessages = $this->getRecentChatHistory($userToken, 20);
        echo "   ✅ [服务端] 获取到 " . count($contextMessages) . " 条历史记录\n";

        $fullResponse = '';
        $chunkCount = 0;

        echo "   🔄 [服务端] 开始调用阿里云AI API...\n";
        $success = $this->aliyun->streamChatWithContext($message, $contextMessages, function($chunk) use ($userToken, $roomId, &$fullResponse, &$chunkCount) {
            $fullResponse .= $chunk;
            $chunkCount++;
            
            if ($chunkCount % 5 === 0) { // 每5个chunk打印一次日志
                echo "   📊 [服务端] 已发送 {$chunkCount} 个内容块\n";
            }
            
            $this->sendToAllUserConnections($userToken, [
                'type' => 'chat',
                'data' => [
                    'isStreaming' => true,
                    'content' => $chunk,
                    'timestamp' => time(),
                    'room_id' => $roomId,
                    'sender_id' => 'ai',
                    'is_ai' => true,
                    'status' => 'streaming'
                ]
            ]);
        });

        echo "   🏁 [服务端] AI响应完成 - 总chunk数: {$chunkCount}, 成功: " . ($success ? '是' : '否') . "\n";

        // 发送结束信号
        $this->sendToAllUserConnections($userToken, [
            'type' => 'chat',
            'data' => [
                'isStreaming' => false,
                'content' => '',
                'timestamp' => time(),
                'room_id' => $roomId,
                'sender_id' => 'ai',
                'is_ai' => true,
                'status' => 'end',
                'full_content' => $fullResponse
            ]
        ]);

        if ($success) {
            echo "   💾 [服务端] 保存聊天历史...\n";
            $this->saveChatHistory($userToken, $message, $fullResponse, $roomId);
            $this->updateUserChatCount($userToken);
            echo "   ✅ [服务端] AI对话处理完成\n";
        } else {
            echo "   ❌ [服务端] AI服务响应失败\n";
            $this->sendErrorMessage($connection, 'AI服务响应失败');
        }
    }

    private function getRecentChatHistory(string $userToken, int $limit = 30): array
    {
        try {
            $user = User::where('user_token', $userToken)->find();
            if (!$user || empty($user->id)) {
                echo "获取对话历史失败：未找到用户信息\n";
                return [];
            }
            
            $userId = $user->id;
            
            $history = RoomCommons::where('user_id', $userId)
                ->order('create_time', 'desc')
                ->limit($limit)
                ->select()
                ->toArray();
            
            $history = array_reverse($history);

            
            return $history;
            
        } catch (\Exception $e) {
            echo "❌ 获取对话历史异常: " . $e->getMessage() . "\n";
            echo "错误文件: " . $e->getFile() . ":" . $e->getLine() . "\n";
            return [];
        }
    }

    private function saveChatHistory(string $userToken, string $userMessage, string $aiResponse, ?int $roomId): void
    {
        try {
            $user = User::where('user_token', $userToken)->find();
            if (!$user || empty($user->id)) {
                return;
            }
            
            $userId = $user->id;
            
            RoomCommons::create([
                'user_id' => $userId,
                'room_id' => $roomId,
                'message_type' => 'user',
                'content' => $userMessage
            ]);
            
            RoomCommons::create([
                'user_id' => $userId,
                'room_id' => $roomId,
                'message_type' => 'ai',
                'content' => $aiResponse
            ]);

        } catch (\Exception $e) {
            echo "❌ 保存对话历史异常: " . $e->getMessage() . "\n";
            echo "错误文件: " . $e->getFile() . ":" . $e->getLine() . "\n";
        }
    }

    private function checkMessageRateLimit(string $userToken): bool
    {
        $currentTime = time();
        
        if (isset($this->lastMessageTime[$userToken])) {
            $timeSinceLastMessage = $currentTime - $this->lastMessageTime[$userToken];
            if ($timeSinceLastMessage < self::MESSAGE_COOLDOWN) {
                return false;
            }
        }
        
        $this->lastMessageTime[$userToken] = $currentTime;
        return true;
    }

    private function sendToAllUserConnections(string $userToken, array $data): void
    {
        if (!isset($this->userConnections[$userToken])) {
            return;
        }

        $sentCount = 0;
        foreach ($this->userConnections[$userToken] as $connId => $connection) {
            try {
                $this->sendToClient($connection, $data);
                $sentCount++;
            } catch (\Exception $e) {
                unset($this->userConnections[$userToken][$connId]);
            }
        }

    }

    private function sendToOtherUserConnections(string $userToken, int $excludeConnId, array $data): void
    {
        if (!isset($this->userConnections[$userToken])) {
            return;
        }

        $sentCount = 0;
        foreach ($this->userConnections[$userToken] as $connId => $connection) {
            if ($connId == $excludeConnId) {
                continue;
            }
            
            try {
                $this->sendToClient($connection, $data);
                $sentCount++;
            } catch (\Exception $e) {
                echo "发送消息到连接 {$connId} 失败: " . $e->getMessage() . "\n";
                unset($this->userConnections[$userToken][$connId]);
            }
        }
    }

    private function broadcastToRoom(array $message, int $roomId, TcpConnection $excludeConnection = null): void
    {
        if (!isset($this->roomUsers[$roomId])) {
            return;
        }

        $sentCount = 0;
        foreach ($this->roomUsers[$roomId] as $userToken) {
            if ($excludeConnection && isset($excludeConnection->userId) && $excludeConnection->userId === $userToken) {
                continue;
            }

            if (isset($this->userConnections[$userToken])) {
                foreach ($this->userConnections[$userToken] as $connId => $connection) {
                    try {
                        $this->sendToClient($connection, $message);
                        $sentCount++;
                    } catch (\Exception $e) {
                        echo "广播消息到连接 {$connId} 失败: " . $e->getMessage() . "\n";
                        unset($this->userConnections[$userToken][$connId]);
                    }
                }
            }
        }

        echo "消息广播到房间 {$roomId}, 发送给 {$sentCount} 个连接\n";
    }

    private function sendToClient(TcpConnection $connection, array $data): void
    {
        try {
            $data = $this->ensureUtf8Encoding($data);
            
            $json = json_encode($data, JSON_UNESCAPED_UNICODE | JSON_THROW_ON_ERROR | JSON_INVALID_UTF8_SUBSTITUTE);
            $connection->send($json);
        } catch (\JsonException $e) {
            echo "JSON编码错误: " . $e->getMessage() . "\n";
            echo "原始数据: " . print_r($data, true) . "\n";
        }
    }

    private function ensureUtf8Encoding($data)
    {
        if (is_string($data)) {
            if (!mb_check_encoding($data, 'UTF-8')) {
                $converted = mb_convert_encoding($data, 'UTF-8', 'GBK');
                if ($converted !== false && mb_check_encoding($converted, 'UTF-8')) {
                    return $converted;
                }
                return mb_convert_encoding($data, 'UTF-8', 'UTF-8');
            }
            return $data;
        } elseif (is_array($data)) {
            foreach ($data as $key => $value) {
                $data[$key] = $this->ensureUtf8Encoding($value);
            }
            return $data;
        }
        
        return $data;
    }

    private function sendErrorMessage(TcpConnection $connection, string $message): void
    {
        $this->sendToClient($connection, [
            'type' => 'system',
            'data' => [
                'event' => 'error',
                'message' => $message
            ]
        ]);
    }

    public function onClose(TcpConnection $connection): void
    {
        unset($this->clients[$connection->id]);

        if (isset($connection->userId)) {
            $userToken = $connection->userId;
            
            if (isset($this->userConnections[$userToken])) {
                unset($this->userConnections[$userToken][$connection->id]);
                
                if (empty($this->userConnections[$userToken])) {
                    unset($this->userConnections[$userToken]);
                    
                    if (isset($this->userToRoom[$userToken])) {
                        $roomId = $this->userToRoom[$userToken];
                        if (isset($this->roomUsers[$roomId])) {
                            $key = array_search($userToken, $this->roomUsers[$roomId]);
                            if ($key !== false) {
                                unset($this->roomUsers[$roomId][$key]);
                                $this->roomUsers[$roomId] = array_values($this->roomUsers[$roomId]);
                            }
                            
                            if (empty($this->roomUsers[$roomId])) {
                                unset($this->roomUsers[$roomId]);
                                echo "房间 {$roomId} 已清空并删除\n";
                            }
                        }
                        
                        unset($this->userToRoom[$userToken]);
                    }
                    
                    unset($this->lastMessageTime[$userToken]);
                    
                    $this->removeOnlineUser($userToken);
                    
                    echo "用户 {$userToken} 的所有连接已断开\n";
                } else {
                    echo "用户 {$userToken} 的一个连接断开，剩余 " . count($this->userConnections[$userToken]) . " 个连接\n";
                }
            }
        }

        echo "客户端 {$connection->id} 断开连接, 剩余连接数: " . count($this->clients) . "\n";
    }

    public function onError(TcpConnection $connection, int $code, string $msg): void
    {
        echo "连接错误 [{$code}]: {$msg}\n";
        unset($this->clients[$connection->id]);

        if (isset($connection->userId)) {
            $userToken = $connection->userId;
            unset($this->userConnections[$userToken]);
            unset($this->userToRoom[$userToken]);
            unset($this->lastMessageTime[$userToken]);
        }
    }

    public function run(): void
    {
        $sslConfig = config('aliyun.websocket_ssl', []);
        $isSslEnabled = $sslConfig['enable'] ?? false;
        $port = 2346; 

        echo "正在启动 WebSocket 服务器 (端口: {$port}, SSL: " . ($isSslEnabled ? '开启' : '关闭') . ")...\n";

        $workerContext = [];
        if ($isSslEnabled) {
            $localCert = $sslConfig['local_cert'] ?? '';
            $localPk = $sslConfig['local_pk'] ?? '';

            if (!file_exists($localCert) || !file_exists($localPk)) {
                echo "❌ [ERROR] 证书文件不存在！请检查 config/aliyun.php\n";
            } else {
                $workerContext = array(
                    'ssl' => array(
                        'local_cert'  => $localCert,
                        'local_pk'    => $localPk,
                        'verify_peer' => false,
                    )
                );
                echo "✅ WSS (SSL) 模式已启用，证书加载成功\n";
            }
        }

        $worker = new Worker("websocket://0.0.0.0:{$port}", $workerContext);
        
        if ($isSslEnabled) {
            $worker->transport = 'ssl';
        }

        $worker->count = 1; 
        $worker->name = 'ChatWebSocket';

        $worker->onWorkerStart = [$this, 'onWorkerStart'];
        $worker->onConnect = [$this, 'onConnect'];
        $worker->onMessage = [$this, 'onMessage'];
        $worker->onClose = [$this, 'onClose'];
        $worker->onError = [$this, 'onError'];

        Worker::runAll();
    }
}
