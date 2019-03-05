<?php

namespace app\mobile\controller;
use think\Db;
use app\common\model\UserCode;

class Code extends MobileBase
{
    /**
     * 获取二维码
     * 引用地址 ：
     *      /Mobile/code/create_code
     */
    public function create_code()
    {
    
        $openid = session('user.openid');
        if(I('test') == 1){
            //测试
            $openid = 'testopenid';
        }
    
        $model = new UserCode();
        $img = $model->where(['openid'=>$openid])->value('img');
        if($img){
            return $img;
        }

        $access_token = httpRequest("http://www.jiusheyounong.com/mobile/api/access_token");
   
        $url = "https://api.weixin.qq.com/cgi-bin/qrcode/create?access_token=".$access_token;
        $data['action_name'] = 'QR_LIMIT_STR_SCENE';
        $data['action_info']['scene']['scene_str'] = "openid_".$openid;
        $data = json_encode($data);
        $res = httpRequest($url,'POST',$data);
        $result = json_decode($res,true);

        $result['openid'] = $openid;
        $result['img'] = "https://mp.weixin.qq.com/cgi-bin/showqrcode?ticket=".$result['ticket'];

        $model = new UserCode();
        $model->openid = $openid;
        $model->url = $result['url'];
        $model->ticket = $result['ticket'];
        $model->img = $result['img'];

        $re = $model->save();

        return $result['img'];

    }

}