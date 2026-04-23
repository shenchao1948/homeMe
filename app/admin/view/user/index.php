<!DOCTYPE html>
<html lang="zh-CN">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>用户管理</title>
    <link rel="stylesheet" href="/layui/css/layui.css">
    <style>
        body { margin: 0; padding: 0; overflow: hidden; }
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
        .logo { font-size: 20px; font-weight: bold; }
        .user-info { display: flex; align-items: center; gap: 15px; }
        .container { display: flex; height: calc(100vh - 60px); }
        .sidebar { width: 200px; background: #393D49; overflow-y: auto; }
        .main-content { flex: 1; overflow-y: auto; padding: 20px; background: #f2f2f2; }
        .menu-item {
            padding: 15px 20px;
            color: white;
            cursor: pointer;
            transition: background 0.3s;
        }
        .menu-item:hover, .menu-item.active { background: #009688; }
    </style>
</head>
<body>
<div class="header">
    <div class="logo">AI对话管理后台</div>
    <div class="user-info">
        <span><?php echo \think\facade\Session::get('admin_username'); ?></span>
        <a href="/admin/logout" class="layui-btn layui-btn-sm layui-btn-danger">退出登录</a>
    </div>
</div>

<div class="container">
    <div class="sidebar">
        <div class="menu-item" data-url="/admin/index">
            <i class="layui-icon layui-icon-home"></i> 首页
        </div>
        <div class="menu-item" data-url="/admin/room/index">
            <i class="layui-icon layui-icon-chat"></i> 房间管理
        </div>
        <div class="menu-item active" data-url="/admin/user/index">
            <i class="layui-icon layui-icon-user"></i> 用户管理
        </div>
        <div class="menu-item" data-url="/admin/permission/index">
            <i class="layui-icon layui-icon-auz"></i> 权限管理
        </div>
    </div>

    <div class="main-content">
        <div class="layui-form" style="margin-bottom: 15px;">
            <div class="layui-inline">
                <select id="onlineFilter" lay-filter="onlineFilter">
                    <option value="">全部用户</option>
                    <option value="1">在线用户</option>
                    <option value="0">离线用户</option>
                </select>
            </div>
        </div>
        <table id="userTable" lay-filter="userTable"></table>
    </div>
</div>

<script type="text/html" id="statusTpl">
    {{# if(d.is_online){ }}
    <span style="color: #5FB878;">● 在线</span>
    {{# } else { }}
    <span style="color: #FF5722;">● 离线</span>
    {{# } }}
</script>

<script src="/static/js/jquery.js"></script>
<script src="/layui/layui.js"></script>
<script>
    layui.use(['table', 'form', 'layer'], function(){
        var table = layui.table;
        var form = layui.form;
        var layer = layui.layer;

        var onlineStatus = '';

        table.render({
            elem: '#userTable',
            url: '/admin/user/getUserList?online=' + onlineStatus,
            cols: [[
                {field: 'id', title: 'ID', width: 80, sort: true},
                {field: 'username', title: '用户名'},
                {field: 'user_token', title: '用户Token'},
                {field: 'is_online', title: '状态', width: 100, templet: '#statusTpl'},
                {field: 'chat_count', title: '聊天次数', width: 100, sort: true},
                {field: 'room_id', title: '所在房间', width: 100},
                {field: 'last_active_time', title: '最后活跃时间', width: 180},
                {field: 'login_time', title: '登录时间', width: 180}
            ]],
            page: true,
            limit: 10,
            limits: [10, 20, 50, 100]
        });

        form.on('select(onlineFilter)', function(data){
            onlineStatus = data.value;
            table.reload('userTable', {
                url: '/admin/user/getUserList?online=' + onlineStatus,
                page: {curr: 1}
            });
        });

        $('.menu-item').click(function(){
            window.location.href = $(this).data('url');
        });
    });
</script>
</body>
</html>
