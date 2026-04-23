<?php
return [
    // 阿里云百炼 API Key（用于AI模型调用）
    'api_key' => '',
    
    // 阿里云访问密钥（用于其他阿里云服务）
    'access_key_id' => 'your_access_key_id',
    'access_key_secret' => 'your_access_key_secret',
    
    // 区域和端点配置
    'region_id' => 'cn-hangzhou',
    'endpoint' => 'http://ecs.aliyuncs.com',
    
    // 超时配置（秒）
    'timeout' => 30.0,
    'connect_timeout' => 10.0,
    
    // 阿里云百炼 API 基础URL
    // 使用标准模型API（支持多轮对话上下文）
    // 'base_url' => 'https://dashscope.aliyuncs.com/api/v1/services/aigc/text-generation/generation',
    // 应用API（不支持多轮对话，已注释）
    'base_url' => 'https://dashscope.aliyuncs.com/api/v1/apps/a2f54f5a75c34b40a7000725dc57e53b/completion',

    // 默认模型配置
    'default_model' => 'qwen-plus-latest',
    'temperature' => 0.7,
    'top_p' => 0.8,
    'max_tokens' => 1500,
    
    /**
     * WebSocket SSL 配置
     */
    'websocket_ssl' => [
        'enable' => false, // 生产环境建议开启 true，本地调试可设为 false
        'port'   => 2346, // 统一端口
        // 证书路径 (请填写服务器上的绝对路径)
        'local_cert' => 'C:/phpEnv/nginxSSL/www.shenchao.me.pem', 
        'local_pk'   => 'C:/phpEnv/nginxSSL/www.shenchao.me.key',
    ],
];
