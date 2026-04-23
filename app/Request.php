<?php
namespace app;

// 应用请求对象类
class Request extends \think\Request
{
    // 代理服务器 IP 白名单
    protected $proxyServerIp = [
        '127.0.0.1',
        '::1',
        '192.168.0.0/16',
        '10.0.0.0/8',
        '172.16.0.0/12',
    ];
    
    // 从哪个 header 获取真实 IP
    //protected $proxyServerIpHeader = array('X-Real-IP');

    public function getHostUrl()
    {
        $host = request()->domain();
        $maxUrl = request()->url();
        $homeStr = request()->root();
        if(empty($homeStr)){
            return $host;
        }else{
            $list = explode($homeStr,$maxUrl);
            $list = current($list);
            return $host.$list;
        }
    }
}
