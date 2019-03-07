<?php

namespace app\mobile\controller;
use think\Db;
use app\common\model\UserCode;
use app\common\model\Users;
use app\common\model\UserBeeAccount;

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

        $flas = $user->where(['openid'=>$FromUserName])->save(['first_leader'=>$user_id]);
        if($flas){
            //添加奖励
            $this->add_bee($user_id);
        }else{
            return false;
        }
        

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

            $user_id = $user->user_id;

            //不存在则创建个第三方账号
            M('OauthUsers')->save(array('oauth'=>'weixin' , 'openid'=>$openid ,'user_id'=>$user_id , 'unionid'=>$data['unionid'], 'oauth_child'=>'mp'));
        }
        
    }

    public function bonus()
    {   
        $user_id = session('user.user_id');
        $users = $this->users($user_id);
        
        $meetUser = get_uper_user($users);
        foreach($meetUser['recUser'] as $level=>$user)
	    	{
                if($level>2) return true;
	    		$agentLevel = $this->get_agent_user($user);
	    	}

    }
    //查找用户信息
    private function get_agent_user($user)
    {
        //直推5人
        $users = M('users')->where(["first_leader"=>$user['user_id'],"is_bee"=>1])->select();
       
        $nums  = $users?count($users):false;
        
        if($nums<5) return false;
        
        $agentGrade = $this->is_agent_user($user['user_id']);
        
        if($agentGrade>=3) return true;
        $flag = $this->upgrade_agent($agentGrade,$user['user_id']);

    }

    //是否已经在合伙人了
    private function is_agent_user($user_id)
    {
        $where = "user_id = ".$user_id;
        $agent = M('users')->where($where)->find();
        return $agent?$agent['distribut_level']:false;
    }

    //查找用户信息
    private function users($user_id)
    {
        $user_leader = M('users')->where('user_id',$user_id)->find();
        return $user_leader;
    }
    //根据判断条件进行用户升级
    private function upgrade_agent($grade,$user_id){
        if($grade<1){
            $flag = $this->get_child_agent($user_id,5);
        }else if($grade<2){
            $flag = $this->get_child_agent($user_id,30);
        }else if($grade<3){
            $flag = $this->get_child_agent($user_id,100);
        }
        if(!$flag) return false;
        $newGrade 	= $grade + 1;
        $data = array("distribut_level"=>$newGrade);
        $flag = M('users')->where('user_id',$user_id)->update($data);
    }

    //判断直推条件是否满足
    private function get_child_agent($userId,$nums)
    {
        $users = M('users')->field('User_ID')->where(['first_leader'=>$userId,'is_bee'=>1])->select();
        if(count($users)<$nums) 
            return false;
        else
            return true;
    }

    //添加获赠100滴蜂王浆，并随机派发阳光值和露水
    public function add_bee($user_id)
    {
        $user_bee_accout = new UserBeeAccount();
        $data = $user_bee_accout->where(['uid'=>$user_id])->find();

        $this->write_log($user_id);
        $this->config = tpCache('game'); //配置信息
        $bee_milk = $this->config['nine_give_bee_milk'];
        $sun_value = $this->config['nine_random_sun'];
        $water = $this->config['nine_random_water'];
        
        $this->write_log('bee_milk----'.$bee_milk);
        $this->write_log('sun_value----'.$sun_value);
        $this->write_log('water----'.$water);
        // 
        if(empty($data)){
            $user_bee_accout->uid = $user_id;
            $user_bee_accout->bee_milk = $bee_milk;
            $user_bee_accout->sun_value = $sun_value;
            $user_bee_accout->water = $water;
            $user_bee_accout->create_time = time();
            $user_bee_accout->update_time = time();
            $user_bee_accout->status = 1;
            // $this->write_log(json_encode($array));
            $flat = $user_bee_accout->save();
        }else{
            $dataarr = array(
                'bee_milk'=>$data['bee_milk']+$bee_milk,
                'sun_value'=>$data['sun_value']+$sun_value,
                'water'=>$data['water']+$water,
                'update_time'=>time()
            );
            $flat = $user_bee_accout->where('uid',$user_id)->update($dataarr);
        }
        if($flat){
            //同步积分字段
            update_user($user_id,$bee_milk,1);
            $this->add_bee_flow($user_id,201,"推荐新用户,赠送".$bee_milk."滴蜂王浆",1);
            $this->add_bee_flow($user_id,401,"推荐新用户,赠送".$sun_value."阳光值",1);
            $this->add_bee_flow($user_id,301,"推荐新用户,赠送".$water."滴露水",1);
            $this->write_log("结束");
        }else{
            return false;
        }
    }

    /*
     * $userid = 用户id
     * $type = 类型
     * $note = 备注信息
     * $inc_or_dec = 1增加，2减少
     */
    public function add_bee_flow($userid,$type,$note,$inc_or_dec)
    {
        $data = array(
            "uid" => $userid,
            "type"=> $type,
            "note"=> $note,
            "inc_or_dec"=>$inc_or_dec,
            "create_time"=>time(),
            "status"=>1,
            "num"=>1
        );
        $bee_flow = M('bee_flow');
        if ($bee_flow->insert($data))
        {
            return $this->fetch('/bee/duihuan');    //成功后跳转  lst 界面
        }else{
            $this->error('失败', U('/bee/duihuan'));
            exit();
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