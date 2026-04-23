<!DOCTYPE html>
<html lang="zh-CN">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>加入房间</title>
    <link rel="stylesheet" href="/layui/css/layui.css">
    <style>
        body { padding: 20px; }
        .form-container { max-width: 500px; margin: 0 auto; }
    </style>
</head>
<body>
<div class="form-container">
    <form class="layui-form" lay-filter="joinForm">
        <input type="hidden" name="room_id" value="<?php echo $room_id; ?>">

        <div class="layui-form-item">
            <label class="layui-form-label">AI对话控制</label>
            <div class="layui-input-block">
                <input type="radio" name="ai_enabled" value="1" title="允许AI对话" checked>
                <input type="radio" name="ai_enabled" value="0" title="禁止AI对话">
            </div>
        </div>

        <div class="layui-form-item">
            <div class="layui-input-block">
                <button class="layui-btn" lay-submit lay-filter="joinSubmit">确认加入</button>
                <button type="reset" class="layui-btn layui-btn-primary">重置</button>
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

        form.on('submit(joinSubmit)', function(data){
            $.ajax({
                url: '/admin/room/join',
                type: 'POST',
                data: data.field,
                dataType: 'json',
                success: function(res){
                    if(res.code === 1){
                        layer.msg('加入成功', {icon: 1}, function(){
                            var index = parent.layer.getFrameIndex(window.name);
                            parent.layer.close(index);
                            parent.location.reload();
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
