<!DOCTYPE html>
<html lang="zh-CN">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>房间管理</title>
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
        <div class="menu-item active" data-url="/admin/room/index">
            <i class="layui-icon layui-icon-chat"></i> 房间管理
        </div>
        <div class="menu-item" data-url="/admin/user/index">
            <i class="layui-icon layui-icon-user"></i> 用户管理
        </div>
        <div class="menu-item" data-url="/admin/permission/index">
            <i class="layui-icon layui-icon-auz"></i> 权限管理
        </div>
    </div>

    <div class="main-content">
        <table id="roomTable" lay-filter="roomTable"></table>
    </div>
</div>

<script type="text/html" id="toolbar">
    <div class="layui-btn-container">
        <button class="layui-btn layui-btn-sm" lay-event="refresh">
            <i class="layui-icon layui-icon-refresh"></i> 刷新
        </button>
    </div>
</script>

<script type="text/html" id="operation">
    <a class="layui-btn layui-btn-xs" lay-event="join">加入房间</a>
</script>

<script src="/static/js/jquery.js"></script>
<script src="/layui/layui.js"></script>
<script>
    layui.use(['table', 'layer'], function(){
        var table = layui.table;
        var layer = layui.layer;

        table.render({
            elem: '#roomTable',
            url: '/admin/room/getRoomList',
            toolbar: '#toolbar',
            cols: [[
                {field: 'id', title: 'ID', width: 80, sort: true},
                {field: 'room_code', title: '房间编码'},
                {field: 'room_name', title: '房间名称'},
                {field: 'create_user', title: '创建者'},
                {field: 'user_count', title: '总人数', width: 100},
                {field: 'online_count', title: '在线人数', width: 100, templet: function(d){
                    if(d.online_count > 0){
                        return '<span style="color: #5FB878; font-weight: bold;">' + d.online_count + '</span>';
                    } else {
                        return '<span style="color: #999;">0</span>';
                    }
                }},
                {field: 'message_count', title: '消息数', width: 100},
                {field: 'create_time', title: '创建时间', width: 180},
                {fixed: 'right', title: '操作', toolbar: '#operation', width: 120}
            ]],
            page: true,
            limit: 10,
            limits: [10, 20, 50, 100]
        });

        table.on('tool(roomTable)', function(obj){
            var data = obj.data;
            if(obj.event === 'join'){
                layer.open({
                    type: 2,
                    title: '加入房间 - ' + data.room_name,
                    area: ['600px', '400px'],
                    content: '/admin/room/joinPage?id=' + data.id
                });
            }
        });

        $('.menu-item').click(function(){
            window.location.href = $(this).data('url');
        });
    });
</script>
</body>
</html>
