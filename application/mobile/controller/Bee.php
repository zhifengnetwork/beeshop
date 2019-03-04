<?php
/**
 * tpshop
 * ============================================================================
 * * 版权所有 2015-2027 深圳搜豹网络科技有限公司，并保留所有权利。
 * 网站地址: http://www.tp-shop.cn
 * ----------------------------------------------------------------------------
 * 这不是一个自由软件！您只能在不用于商业目的的前提下对程序代码进行修改和使用 .
 * 不允许对程序代码以任何形式任何目的的再发布。
 * 采用TP5助手函数可实现单字母函数M D U等,也可db::name方式,可双向兼容
 * ============================================================================
 * $Author: IT宇宙人 2015-08-10 $
 */
namespace app\mobile\controller; 
use app\common\logic\GoodsPromFactory;
use app\common\logic\SearchWordLogic;
use app\common\logic\GoodsLogic;
use app\common\model\SpecGoodsPrice;
use think\AjaxPage;
use think\Page;
use think\Verify;
use think\Db;
use think\Cookie;
class Bee extends MobileBase {

    public $user_id = 0;

     /*
    * 初始化操作
    */
    public function _initialize()
    {
        parent::_initialize();
        $this->config = tpCache('game');
        if (session('?user')) {
            $user = session('user');
            $user = M('users')->where("user_id", $user['user_id'])->find();
            session('user', $user);  //覆盖session 中的 user
            $this->user = $user;
            $this->user_id = $user['user_id'];
            $this->assign('user', $user); //存储用户信息
        }
        $nologin = array(
            'login', 'pop_login', 'do_login', 'logout', 'verify', 'set_pwd', 'finished',
            'verifyHandle', 'reg', 'send_sms_reg_code', 'find_pwd', 'check_validate_code',
            'forget_pwd', 'check_captcha', 'check_username', 'send_validate_code', 'express' , 'bind_guide', 'bind_account',
        );
        $is_bind_account = tpCache('basic.is_bind_account');
        if (!$this->user_id && !in_array(ACTION_NAME, $nologin)) {
            if(strstr($_SERVER['HTTP_USER_AGENT'],'MicroMessenger') && $is_bind_account){
                header("location:" . U('Mobile/User/bind_guide'));//微信浏览器, 调到绑定账号引导页面
            }else{
                header("location:" . U('Mobile/User/login'));
            }
            exit;
        }

        $user = session('user');
        $user = M('users')->where("user_id", $user['user_id'])->find();
        session('user', $user);  //覆盖session 中的 user
        $this->user = $user;
        $this->user_id = $user['user_id'];
        $this->assign('user', $user); //存储用户信息
        $this->assign('user_id', $this->user_id);
    }
	
    // 游戏加载页面
    public function index(){

       

        return $this->fetch('/bee/greet');
    }

    // 游戏主页
    public function beeIndex(){
        $level = 0;
        $mating = 0;

        $bee = M('user_bee')->where(array('uid' => $this->user_id))->select();
        foreach($bee as $key => $value){
            if($value['level'] == 2){
                $level = count($key);
            }
            if($value['is_mating'] == 1){
                $mating = count($key);
            }
        }

        $user_prop = M('user_bee_account')->where(array('uid' => $this->user_id))->find();
        $user_prop['level'] = $level;
        $user_prop['mating'] = $mating;
//        dump($user_prop['bee_milk']);exit;
        $this->assign('user_prop', $user_prop);

         //增加头像
         $head_pic = session('user.head_pic');
         $this->assign('head_pic',$head_pic);
         
        return $this->fetch('/bee/index');
    }
    private function user_find($user_id)
    {
    	$where = "uid = '".$user_id."'";
    	$file = Db::name('user_bee_account')->where($where)->find();
    	return $file;
    }

    /*
    * 用户信息
    */ 
    public function userInfo(){
        return $this->fetch('/bee/user');
    }

    /*
    * 好友
    */ 
    public function beeFriend(){
        return $this->fetch('/bee/friend');
    }

     
    /*
    * 邀请好友
    */ 
    public function beeInvite(){
        //传递快速注册的地址和分享用户的手机
        $user_id = session('user.user_id');
        $mobile  = M('users')->where('user_id', $user_id)->value('mobile');
        $path    = $_SERVER['HTTP_HOST'];
        $url     = 'http://' . $path . '/Mobile/User/reg?mobile=' . $mobile;
        $this->assign('url', $url);
        return $this->fetch('/bee/invite');
    }

     /*
    * 兑换
    */ 
    public function beeExchange()
    {
        $this->config = tpCache('game'); //配置信息
        //兑换所需蜜糖
        $bee_num = $_POST['bee_num'];

        if ($_POST['exchange_nums'] == null){
            $_POST['exchange_nums'] =1;
        }
        //状态类型 1：兑换雄峰，2：兑换蜂王浆，3：兑换蜜糖，4：兑换露水，5：兑换阳光值
        $type = $_POST['type'];
        //用户id
        $user_id = session('user.user_id');
        // 实例化User对象
        $bee_account = M("user_bee_account");
        $bee_milk = $bee_account->where('uid',$user_id)->find();
        switch ($type) {
            case 1:
                $bee_num = $this->config['three_drip_bee_milk'];
//                var_dump($bee_num);die;
                if ($bee_milk['bee_milk'] < $bee_num){
                    $msg = "对不起，您的蜂王浆数量不足".$bee_num."滴!!";
                    return json(array('msg'=>$msg,'type'=>0));
                    exit();
                }
                $milk = $bee_milk['bee_milk']-$bee_num;
                $data = array(
                    "bee_milk"=>$milk,
                    "drone" =>$bee_milk['drone']+1
                );
                $msg = "恭喜您，".$bee_num."滴蜂王浆兑换一只雄峰成功";
                $update = $bee_account->where('uid',$user_id)->setField($data);
                if ($update)
                {
                    //增加蜂王浆兑换雄峰日志记录
                    $this->add_bee_flow($user_id,201,"减少".$bee_num."滴蜂王浆",2);
                    $this->add_bee_flow($user_id,501,"增加1只雄峰",1);
                }
                break;
            case 2:
                //蜜糖所需总数
                $nums = $this->config['eight_one_gooey']*$_POST['exchange_nums'];
                if ($bee_milk['gooey'] < $nums){
                    $msg = "对不起，您的蜜糖数量不足".$nums."克!!";
                    return json(array('msg'=>$msg,'type'=>0));
                    exit();
                }
                $gooey = $bee_milk['gooey']-$nums;
                $data = array(
                    "gooey"=>$gooey,
                    "bee_milk" =>$bee_milk['bee_milk']+$_POST['exchange_nums']
                );
                $msg = "恭喜您，".$nums."克蜜糖浆兑".$this->config['eight_exchange_bee_milk']*$_POST['exchange_nums']."滴蜂王浆成功";
                $update = $bee_account->where('uid',$user_id)->setField($data);
                if ($update)
                {
                    //增加蜂王浆兑换雄峰日志记录
                    $this->add_bee_flow($user_id,801,"减少".$nums."蜜糖",2);
                    $this->add_bee_flow($user_id,201,"增加".$this->config['eight_exchange_bee_milk']*$_POST['exchange_nums']."滴蜂王浆",1);
                }
                break;
            case 3:
                //蜜糖所需总数
                $nums = $this->config['eight_one_gooey']*$_POST['exchange_nums'];
                if ($bee_milk['gooey'] < $nums){
                    $msg = "对不起，您的蜜糖数量不足".$nums."克!!";
                    return json(array('msg'=>$msg,'type'=>0));
                    exit();
                }
                $gooey = $bee_milk['gooey']-$nums;
                $data = array(
                    "gooey"=>$gooey,
                    "bee_milk" =>$bee_milk['bee_milk']+$_POST['exchange_nums']
                );
                $note = $nums."克蜜糖浆兑".$this->config['eight_exchange_bee_milk']*$_POST['exchange_nums']."滴蜂王浆成功";
                $msg = "恭喜您，".$nums."克蜜糖浆兑".$this->config['eight_exchange_bee_milk']*$_POST['exchange_nums']."滴蜂王浆成功";
                $update = $bee_account->where('uid',$user_id)->setField($data);
                if ($update)
                {
                    //增加蜂王浆兑换雄峰日志记录
                    $this->add_bee_flow($user_id,801,"减少".$nums."蜜糖",2);
                    $this->add_bee_flow($user_id,201,"增加".$this->config['eight_exchange_bee_milk']*$_POST['exchange_nums']."滴蜂王浆",1);
                }
                break;
            case 4:
                //蜜糖所需总数
                $nums = $this->config['eight_two_gooey']*$_POST['exchange_nums'];
                if ($bee_milk['gooey'] < $nums){
                    $msg = "对不起，您的蜜糖数量不足".$nums."克!!";
                    return json(array('msg'=>$msg,'type'=>0));
                    exit();
                }
                $gooey = $bee_milk['gooey']-$nums;
                $data = array(
                    "gooey"=>$gooey,
                    "water" =>$bee_milk['water']+$_POST['exchange_nums']
                );
                $note = $nums."克蜜糖浆兑".$this->config['eight_exchange_water']*$_POST['exchange_nums']."露水成功";
                $msg = "恭喜您，".$nums."克蜜糖浆兑".$this->config['eight_exchange_water']*$_POST['exchange_nums']."露水成功";
                $update = $bee_account->where('uid',$user_id)->setField($data);
                if ($update)
                {
                    //增加蜂王浆兑换雄峰日志记录
                    $this->add_bee_flow($user_id,801,"减少".$nums."蜜糖",2);
                    $this->add_bee_flow($user_id,201,"增加".$this->config['eight_exchange_water']*$_POST['exchange_nums']."滴露水",1);
                }
                break;
            case 5:
                //蜜糖所需总数
                $nums = $this->config['eight_three_gooey']*$_POST['exchange_nums'];
                if ($bee_milk['gooey'] < $nums){
                    $msg = "对不起，您的蜜糖数量不足".$nums."克!!";
                    return json(array('msg'=>$msg,'type'=>0));
                    exit();
                }
                $gooey = $bee_milk['gooey']-$nums;
                $data = array(
                    "gooey"=>$gooey,
                    "sun_value" =>$bee_milk['sun_value']+$_POST['exchange_nums']
                );
                $note = $nums."克蜜糖浆兑".$this->config['eight_exchange_sun']*$_POST['exchange_nums']."阳光值成功";
                $msg = "恭喜您，".$nums."克蜜糖浆兑".$this->config['eight_exchange_sun']*$_POST['exchange_nums']."阳光值成功";
                $update = $bee_account->where('uid',$user_id)->setField($data);
                if ($update)
                {
                    //增加蜂王浆兑换雄峰日志记录
                    $this->add_bee_flow($user_id,801,"减少".$nums."蜜糖",2);
                    $this->add_bee_flow($user_id,201,"增加".$this->config['eight_exchange_sun']*$_POST['exchange_nums']."阳光值",1);
                }
                break;
            default:
        }
        return json(array('msg'=>$msg,'type'=>1));
    }

    public function duihuan(){
        return $this->fetch('/bee/duihuan');
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
            $this->error('兑换失败', U('/bee/duihuan'));
            exit();
        }
    }

    /*
    * 点击转盘获取奖励
    * 
    */
    public function rotate(){
        $type = 601;
        $userId = session('user.user_id');
        $isPrize = 0;
        $prize_num = M('bee_flow')->where('uid', $userId)->where('type', $type)->whereTime('create_time', 'today')->count();

        //判断是否有抽奖机会
        if ($prize_num>=$this->config['prize_count']) {
            $msg = "你没有抽奖机会了哦";
            return json(array('is_prize'=>$isPrize,'msg'=>$msg));
        }
       
        $prize_arr = array();
        $isConfig  = true;

        for ($i=0; $i < 5; $i++) { 
            $prizeMilk = $this->config['prize_bee_milk'.($i+1)];
            $rate = $this->config['prize_rate'.($i+1)];

            if ((!$prizeMilk && $prizeMilk !== 0) || (!$rate && $rate !== 0)) {
                $isConfig = false;
                break;
            }

            array_push($prize_arr, array('id'=>$i+1,'value'=>$prizeMilk,'prize'=>$prizeMilk.'滴蜂王浆','v'=>$rate));
        }

        array_push($prize_arr, array('id'=>6,'value'=>0,'prize'=>'下次没准就能中哦','v'=>$this->config['prize_rate6']));
        
        foreach ($prize_arr as $key => $val) {
            $arr[$val['id']] = $val['v'];
        }
        
        //判断后台是否设置奖项及设置是否完整
        if (!$isConfig || count($arr) < 1) {
            return json(array('is_prize' => $isPrize,'msg' => "还没有设置奖项"));
        }

        $rid = $this->getRand($arr);   //根据概率获取奖项id

        $res['prize'] = $prize_arr[$rid - 1]['prize'];    //中奖项
        // unset($ptize_arr[$rid - 1]);    //将中奖项从数组中剔除,剩下的未中奖项
        // shuffle($prize_arr);    //打乱数组顺序

        // for ($i=0; $i < count($prize_arr); $i++) { 
        //     $pr[] = $prize_arr[$i]['prize'];
        // }

        $isPrize = 1;
        $result = array('is_prize' => $isPrize,'prize' => $res['prize'],'id' => $rid);
        $bool   = true;

        if ($userId) {
            //统计抽了多少次奖
            $count = M('bee_flow')->where('uid', $userId)->where('type', $type)->count();

            $data = array(
                'bid'         => 1,
                'uid'         => $userId,
                'type'        => $type,
                'inc_or_dec'  => $count+1,
                'num'         => $prize_arr[$rid-1]['value'],
                'status'      => 1,
                'create_time' => time(),
                'note'        => "转盘抽奖"
            );
            $res = M('bee_flow')->insert($data);
            $bool = $res ? $bool : false;
        } else {
            $bool = false;
        }

        if (!$bool){
            unset($result);
            $result['is_prize'] = 0;
            $result['msg'] = "抽奖失败";
        }

        return json($result);
    }

    /*
    * 获取奖项
    */
    private function getRand($proArr)
    {
        $result = '';
        $proSum = array_sum($proArr);   //概率数组的总概率精度
        
        //概率数组循环
        foreach ($proArr as $key => $proCur) {

            $randNum = mt_rand(1, $proSum);
            if ($randNum <= $proCur){
                $result = $key;
                break;
            } else {
                $proSum -= $proCur;
            }
        }

        unset($proArr);

        return $result;
    }

    /*
    * 获取奖项名称
    */
    public function getPrizeText(){
        $result = array();
        for ($i=0; $i < 5; $i++) { 
            $drop = $this->config['prize_bee_milk'.($i+1)];
            array_push($result, $drop."滴蜂王浆");
        }

        array_push($result, "下次没准就能中哦");

        return json($result);
    }
    
    
}