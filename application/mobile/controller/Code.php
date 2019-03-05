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
        if (empty($openid)) {
            return "/public/images/error.jpg";
        }
    
        $model = new UserCode();
        $img = $model->where(['openid'=>$openid])->value('img');
        if($img){
            return $img;
        }

        $access_token = httpRequest("http://www.jiusheyounong.com/mobile/api/access_token");
   
        $url = "https://api.weixin.qq.com/cgi-bin/qrcode/create?access_token=".$access_token;
        $data['action_name'] = 'QR_LIMIT_STR_SCENE';
        $data['action_info']['scene']['scene_str'] = $openid;
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

    public function next()
    {
        $EventKey = $_GET['shangji'];
        $FromUserName = $_GET['xiaji'];
        $event = $_GET['event'];
        if ($event == 'subscribe') {
            $arr = explode($EventKey, '_');
            $EventKey = $arr[1];
        }
        $shangji_user_noopenid = $this->users($EventKey)['first_leader'];
        $xiaji_user_noopenid = $this->users($FromUserName)['first_leader'];

        if (empty($shangji_user_noopenid)) exit;
        if (empty($xiaji_user_noopenid)) exit;

        $this->write_log($FromUserName.'---'.$EventKey.'---------'.$event);

    }
    //查询users信息是否有注册
    public function users($openid)
    {
        $user = M('users')->where("openid",$openid)->find();
        if (empty($user)) {
            exit;
        }
        return $user;
    }

    //
    public function user_update()
    {

        # code...
    }

    public function write_log($content)
    {
        $content = "[".date('Y-m-d H:i:s')."]".$content."\r\n";
        $dir = rtrim(str_replace('\\','/',$_SERVER['DOCUMENT_ROOT']),'/').'/logs';
        if(!is_dir($dir)){
            mkdir($dir,0777,true);
        }
        if(!is_dir($dir)){
            mkdir($dir,0777,true);
        }
        $path = $dir.'/'.date('Ymd').'.txt';
        file_put_contents($path,$content,FILE_APPEND);
    }
    

}