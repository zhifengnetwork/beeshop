<?php

namespace app\mobile\controller;
use think\Db;

class Code extends MobileBase
{
    /**
     * 获取二维码
     */
    public function create_code()
    {
    
        $openid = session('user.openid');
        if(I('test') == 1){
            //测试
            $openid = 'testopenid';
        }
       
        $access_token = httpRequest("http://www.jiusheyounong.com/mobile/api/access_token");
   
        $url = "https://api.weixin.qq.com/cgi-bin/qrcode/create?access_token=".$access_token;
        $data['action_name'] = 'QR_LIMIT_STR_SCENE';
        $data['action_info']['scene']['scene_str'] = "openid_".$openid;
        $data = json_encode($data);
        $res = httpRequest($url,'POST',$data);
        
        dump($res);
    }

}