<!DOCTYPE html>
<html lang="zh-CN">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>AI对话管理后台</title>
    <link rel="stylesheet" href="/layui/css/layui.css">
    <style>
        body {
            margin: 0;
            padding: 0;
            overflow: hidden;
        }
        .header {
            background: #393D49;
            color: white;
            height: 60px;
            line-height: 60px;
            padding: 0 20px;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }
        .logo {
            font-size: 20px;
            font-weight: bold;
        }
        .user-info {
            display: flex;
            align-items: center;
            gap: 15px;
        }
        .container {
            display: flex;
            height: calc(100vh - 60px);
        }
        .sidebar {
            width: 200px;
            background: #393D49;
            overflow-y: auto;
        }
        .main-content {
            flex: 1;
            overflow-y: auto;
            padding: 20px;
            background: #f2f2f2;
        }
        .menu-item {
            padding: 15px 20px;
            color: white;
            cursor: pointer;
            transition: background 0.3s;
        }
        .menu-item:hover {
            background: #009688;
        }
        .menu-item.active {
            background: #009688;
        }
        .stat-card {
            background: white;
            padding: 20px;
            border-radius: 5px;
            margin-bottom: 20px;
            box-shadow: 0 2px 5px rgba(0,0,0,0.1);
            transition: transform 0.3s;
        }
        .stat-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 5px 15px rgba(0,0,0,0.2);
        }
        .stat-title {
            font-size: 14px;
            color: #999;
            margin-bottom: 10px;
        }
        .stat-value {
            font-size: 32px;
            font-weight: bold;
            color: #333;
        }
        .stat-icon {
            float: right;
            font-size: 48px;
            opacity: 0.2;
        }
        .quick-actions {
            background: white;
            padding: 20px;
            border-radius: 5px;
            margin-top: 20px;
            box-shadow: 0 2px 5px rgba(0,0,0,0.1);
        }
        .quick-actions h3 {
            margin-top: 0;
            margin-bottom: 15px;
            color: #333;
        }
        .action-btn {
            margin-right: 10px;
            margin-bottom: 10px;
        }
    </style>
</head>
<body>
    <div class="header">
        <div class="logo">AI对话管理后台</div>
        <div class="user-info">
            <span id="adminUsername"><?php echo \think\facade\Session::get('admin_username'); ?></span>
            <a href="/admin/logout" class="layui-btn layui-btn-sm layui-btn-danger">退出登录</a>
        </div>
    </div>
    
    <div class="container">
        <div class="sidebar">
            <div class="menu-item active" data-url="/admin/dashboard">
                <i class="layui-icon layui-icon-home"></i> 首页
            </div>
            <div class="menu-item" data-url="/admin/room/index">
                <i class="layui-icon layui-icon-chat"></i> 房间管理
            </div>
            <div class="menu-item" data-url="/admin/user/index">
                <i class="layui-icon layui-icon-user"></i> 用户管理
            </div>
            <div class="menu-item" data-url="/admin/permission/index">
                <i class="layui-icon layui-icon-auz"></i> 权限管理
            </div>
        </div>
        
        <div class="main-content" id="mainContent">
            <div class="layui-row layui-col-space15">
                <div class="layui-col-md3">
                    <div class="stat-card">
                        <i class="layui-icon layui-icon-user stat-icon" style="color: #1E9FFF;"></i>
                        <div class="stat-title">在线用户数</div>
                        <div class="stat-value" id="onlineCount" style="color: #1E9FFF;">-</div>
                    </div>
                </div>
                <div class="layui-col-md3">
                    <div class="stat-card">
                        <i class="layui-icon layui-icon-chat stat-icon" style="color: #FFB800;"></i>
                        <div class="stat-title">总房间数</div>
                        <div class="stat-value" id="roomCount" style="color: #FFB800;">-</div>
                    </div>
                </div>
                <div class="layui-col-md3">
                    <div class="stat-card">
                        <i class="layui-icon layui-icon-dialogue stat-icon" style="color: #5FB878;"></i>
                        <div class="stat-title">今日消息数</div>
                        <div class="stat-value" id="todayMessages" style="color: #5FB878;">-</div>
                    </div>
                </div>
                <div class="layui-col-md3">
                    <div class="stat-card">
                        <i class="layui-icon layui-icon-group stat-icon" style="color: #FF5722;"></i>
                        <div class="stat-title">总用户数</div>
                        <div class="stat-value" id="totalUsers" style="color: #FF5722;">-</div>
                    </div>
                </div>
            </div>
            
            <div class="quick-actions">
                <h3>快捷操作</h3>
                <button class="layui-btn action-btn" onclick="location.href='/admin/room/index'">
                    <i class="layui-icon layui-icon-chat"></i> 查看房间
                </button>
                <button class="layui-btn layui-btn-normal action-btn" onclick="location.href='/admin/user/index'">
                    <i class="layui-icon layui-icon-user"></i> 查看用户
                </button>
                <button class="layui-btn layui-btn-warm action-btn" onclick="location.href='/admin/permission/index'">
                    <i class="layui-icon layui-icon-auz"></i> 权限管理
                </button>
                <button class="layui-btn layui-btn-danger action-btn" id="refreshData">
                    <i class="layui-icon layui-icon-refresh"></i> 刷新数据
                </button>
            </div>
        </div>
    </div>

    <script src="/static/js/jquery.js"></script>
    <script src="/layui/layui.js"></script>
    <script>
        $(document).ready(function(){
            loadDashboardData();
            
            $('#refreshData').click(function(){
                var btn = $(this);
                btn.addClass('layui-btn-disabled');
                loadDashboardData();
                setTimeout(function(){
                    btn.removeClass('layui-btn-disabled');
                }, 1000);
            });
            
            $('.menu-item').click(function(){
                $('.menu-item').removeClass('active');
                $(this).addClass('active');
                
                var url = $(this).data('url');
                window.location.href = url;
            });
            
            setInterval(loadDashboardData, 30000);
        });
        
        function loadDashboardData(){
            $.ajax({
                url: '/admin/user/getOnlineUsers',
                type: 'GET',
                success: function(res){
                    if(res.code === 0){
                        $('#onlineCount').text(res.count);
                    }
                }
            });
            
            $.ajax({
                url: '/admin/room/getRoomList?page=1&limit=1',
                type: 'GET',
                success: function(res){
                    if(res.code === 0){
                        $('#roomCount').text(res.count);
                    }
                }
            });
            
            $.ajax({
                url: '/admin/user/getUserList?page=1&limit=1',
                type: 'GET',
                success: function(res){
                    if(res.code === 0){
                        $('#totalUsers').text(res.count);
                    }
                }
            });
            
            var today = new Date().toISOString().split('T')[0];
            $.ajax({
                url: '/api/stats/today-messages',
                type: 'GET',
                data: {date: today},
                success: function(res){
                    if(res.code === 0){
                        $('#todayMessages').text(res.count || 0);
                    }
                },
                error: function(){
                    $('#todayMessages').text('-');
                }
            });
        }
    </script>
</body>
</html>
