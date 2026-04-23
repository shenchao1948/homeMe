<!DOCTYPE html>
<html lang="zh-CN">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>管理员登录 - AI对话管理系统</title>
    <link rel="stylesheet" href="/layui/css/layui.css">
    <style>
        body {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            height: 100vh;
            display: flex;
            justify-content: center;
            align-items: center;
            margin: 0;
        }
        .login-container {
            background: white;
            padding: 40px;
            border-radius: 10px;
            box-shadow: 0 10px 40px rgba(0,0,0,0.2);
            width: 400px;
        }
        .login-title {
            text-align: center;
            font-size: 24px;
            color: #333;
            margin-bottom: 30px;
            font-weight: bold;
        }
        .login-form {
            padding: 20px 0;
        }
    </style>
</head>
<body>
<div class="login-container">
    <h2 class="login-title">AI对话管理系统</h2>
    <form class="layui-form login-form" lay-filter="loginForm">
        <div class="layui-form-item">
            <label class="layui-form-label">用户名</label>
            <div class="layui-input-block">
                <input type="text" name="username" required lay-verify="required"
                       placeholder="请输入用户名" autocomplete="off" class="layui-input">
            </div>
        </div>
        <div class="layui-form-item">
            <label class="layui-form-label">密码</label>
            <div class="layui-input-block">
                <input type="password" name="password" required lay-verify="required"
                       placeholder="请输入密码" autocomplete="off" class="layui-input">
            </div>
        </div>
        <div class="layui-form-item">
            <div class="layui-input-block">
                <button class="layui-btn layui-btn-fluid" lay-submit lay-filter="loginSubmit">登录</button>
            </div>
        </div>
    </form>
</div>

<script src="/static/js/jquery.js"></script>
<script src="/layui/layui.js"></script>
<script>
    layui.use(['form', 'layer'], function(){
        var form = layui.form;
        var layer = layui.layer;

        form.on('submit(loginSubmit)', function(data){
            $.ajax({
                url: '/admin/doLogin',
                type: 'POST',
                data: data.field,
                dataType: 'json',
                success: function(res){
                    if(res.code === 1){
                        layer.msg('登录成功', {icon: 1}, function(){
                            location.href = '/admin/index';
                        });
                    } else {
                        layer.msg(res.msg, {icon: 2});
                    }
                },
                error: function(){
                    layer.msg('网络错误', {icon: 2});
                }
            });
            return false;
        });
    });
</script>
</body>
</html>