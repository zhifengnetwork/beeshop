<?php

namespace app\mobile\controller;
use think\Db;
use app\common\model\UserCode;
use app\common\model\Users;

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

        $access_token = httpRequest(SITE_URL."/mobile/api/access_token");
   
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
            $n = -1 * (count($EventKey) - 9);
            $EventKey = substr($EventKey,$n);
        }
        if($EventKey==$FromUserName) return false;
        $this->register($EventKey);//上级
        $this->register($FromUserName);//下级

        //写表
        $user = new Users();
        $user_id = $user->where(['openid'=>$EventKey])->value('user_id');
        $this->write_log('EventKey:'.$EventKey);
        $this->write_log('FromUserName:'.$FromUserName);

        $user->where(['openid'=>$FromUserName])->save(['first_leader'=>$user_id]);

        $this->write_log('user_id:'.$user_id);
        $this->write_log('结束');

    }
    //查询users信息是否有注册
    public function register($openid)
    {
        $this->write_log("添加新用户");
        $user = M('users')->where("openid",$openid)->find();
        if (empty($user)) {
            //通过  openid + access_token 获取 用户信息
            $access_token = httpRequest(SITE_URL."/mobile/api/access_token");
            $url = "https://api.weixin.qq.com/cgi-bin/user/info?access_token=".$access_token."&openid=".$openid."&lang=zh_CN";
            //注册
            $data = httpRequest($url);
            // $this->write_log($data);
            $data = json_decode($data,true);

            $user = new Users();
            $user->openid = $data['openid'];
            $user->nickname = $data['nickname'];
            $user->sex = $data['sex'];
            $user->head_pic = $data['headimgurl'];
            $user->save();
        }
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