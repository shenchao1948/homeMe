<!DOCTYPE html>
<html lang="zh-CN">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>权限管理</title>
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
        <div class="menu-item" data-url="/admin/user/index">
            <i class="layui-icon layui-icon-user"></i> 用户管理
        </div>
        <div class="menu-item active" data-url="/admin/permission/index">
            <i class="layui-icon layui-icon-auz"></i> 权限管理
        </div>
    </div>

    <div class="main-content">
        <div style="margin-bottom: 15px;">
            <button class="layui-btn" id="addRole">
                <i class="layui-icon layui-icon-add-1"></i> 添加角色
            </button>
        </div>
        <table id="roleTable" lay-filter="roleTable"></table>
    </div>
</div>

<script type="text/html" id="toolbar">
    <div class="layui-btn-container">
        <button class="layui-btn layui-btn-xs" lay-event="edit">编辑</button>
        <button class="layui-btn layui-btn-danger layui-btn-xs" lay-event="delete">删除</button>
    </div>
</script>

<script src="/static/js/jquery.js"></script>
<script src="/layui/layui.js"></script>
<script>
    layui.use(['table', 'layer', 'form'], function(){
        var table = layui.table;
        var layer = layui.layer;
        var form = layui.form;

        table.render({
            elem: '#roleTable',
            url: '/admin/permission/getRoleList',
            cols: [[
                {field: 'id', title: 'ID', width: 80, sort: true},
                {field: 'role_name', title: '角色名称'},
                {field: 'description', title: '描述'},
                {field: 'permissions', title: '权限配置', templet: function(d){
                        return JSON.stringify(d.permissions || {});
                    }},
                {field: 'create_time', title: '创建时间', width: 180},
                {fixed: 'right', title: '操作', toolbar: '#toolbar', width: 150}
            ]]
        });

        table.on('tool(roleTable)', function(obj){
            var data = obj.data;
            if(obj.event === 'edit'){
                openRoleForm(data);
            } else if(obj.event === 'delete'){
                layer.confirm('确定删除该角色吗？', function(index){
                    $.ajax({
                        url: '/admin/permission/deleteRole',
                        type: 'POST',
                        data: {id: data.id},
                        dataType: 'json',
                        success: function(res){
                            if(res.code === 1){
                                layer.msg('删除成功', {icon: 1});
                                table.reload('roleTable');
                            } else {
                                layer.msg(res.msg, {icon: 2});
                            }
                        }
                    });
                    layer.close(index);
                });
            }
        });

        $('#addRole').click(function(){
            openRoleForm();
        });

        function openRoleForm(data){
            var isEdit = !!data;
            layer.open({
                type: 1,
                title: (isEdit ? '编辑' : '添加') + '角色',
                area: ['600px', '500px'],
                content: '<div style="padding: 20px;">' +
                    '<form class="layui-form" lay-filter="roleForm">' +
                    '<input type="hidden" name="id" value="' + (data ? data.id : 0) + '">' +
                    '<div class="layui-form-item">' +
                    '<label class="layui-form-label">角色名称</label>' +
                    '<div class="layui-input-block">' +
                    '<input type="text" name="role_name" value="' + (data ? data.role_name : '') + '" required class="layui-input">' +
                    '</div></div>' +
                    '<div class="layui-form-item">' +
                    '<label class="layui-form-label">描述</label>' +
                    '<div class="layui-input-block">' +
                    '<input type="text" name="description" value="' + (data ? data.description : '') + '" class="layui-input">' +
                    '</div></div>' +
                    '<div class="layui-form-item">' +
                    '<label class="layui-form-label">房间管理</label>' +
                    '<div class="layui-input-block">' +
                    '<select name="permissions[room]" lay-verify="required">' +
                    '<option value="2" ' + (data && data.permissions && data.permissions.room == 2 ? 'selected' : '') + '>读写</option>' +
                    '<option value="1" ' + (data && data.permissions && data.permissions.room == 1 ? 'selected' : '') + '>只读</option>' +
                    '<option value="0" ' + (data && data.permissions && data.permissions.room == 0 ? 'selected' : '') + '>不显示</option>' +
                    '</select></div></div>' +
                    '<div class="layui-form-item">' +
                    '<label class="layui-form-label">用户管理</label>' +
                    '<div class="layui-input-block">' +
                    '<select name="permissions[user]" lay-verify="required">' +
                    '<option value="2" ' + (data && data.permissions && data.permissions.user == 2 ? 'selected' : '') + '>读写</option>' +
                    '<option value="1" ' + (data && data.permissions && data.permissions.user == 1 ? 'selected' : '') + '>只读</option>' +
                    '<option value="0" ' + (data && data.permissions && data.permissions.user == 0 ? 'selected' : '') + '>不显示</option>' +
                    '</select></div></div>' +
                    '<div class="layui-form-item">' +
                    '<label class="layui-form-label">权限管理</label>' +
                    '<div class="layui-input-block">' +
                    '<select name="permissions[permission]" lay-verify="required">' +
                    '<option value="2" ' + (data && data.permissions && data.permissions.permission == 2 ? 'selected' : '') + '>读写</option>' +
                    '<option value="1" ' + (data && data.permissions && data.permissions.permission == 1 ? 'selected' : '') + '>只读</option>' +
                    '<option value="0" ' + (data && data.permissions && data.permissions.permission == 0 ? 'selected' : '') + '>不显示</option>' +
                    '</select></div></div>' +
                    '<div class="layui-form-item">' +
                    '<div class="layui-input-block">' +
                    '<button class="layui-btn" lay-submit lay-filter="roleSubmit">保存</button>' +
                    '<button type="button" class="layui-btn layui-btn-primary" onclick="parent.layer.closeAll()">取消</button>' +
                    '</div></div>' +
                    '</form></div>',
                success: function(){
                    form.render();
                    form.on('submit(roleSubmit)', function(formData){
                        var permissions = {};
                        $('select[name^="permissions["]').each(function(){
                            var name = $(this).attr('name').match(/\[(\w+)\]/)[1];
                            permissions[name] = parseInt($(this).val());
                        });
                        formData.field.permissions = permissions;
                        
                        $.ajax({
                            url: '/admin/permission/saveRole',
                            type: 'POST',
                            data: formData.field,
                            dataType: 'json',
                            traditional: true,
                            success: function(res){
                                if(res.code === 1){
                                    layer.msg('保存成功', {icon: 1});
                                    layer.closeAll();
                                    table.reload('roleTable');
                                } else {
                                    layer.msg(res.msg, {icon: 2});
                                }
                            }
                        });
                        return false;
                    });
                }
            });
        }

        $('.menu-item').click(function(){
            window.location.href = $(this).data('url');
        });
    });
</script>
</body>
</html>
