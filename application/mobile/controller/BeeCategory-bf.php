<?php

namespace app\mobile\controller; 
// use app\common\logic\GoodsPromFactory;
use app\common\logic\SearchWordLogic;
use app\common\logic\GoodsLogic;
use think\AjaxPage;
use think\Page;
use think\Db;
use think\Cookie;
class BeeCategory extends MobileBase {

    public $user_id = 0;
    public $user = array();
    public $config = array();
    public $accTime = 60; // 3600

    public function _initialize()
    {
        parent::_initialize();
        $this->config = tpCache('game'); //配置信息
        if (session('?user')) {
           $user = session('user');
           $user = M('users')->where("user_id", $user['user_id'])->find();
           session('user', $user);  //覆盖session 中的 user
           $this->user = $user;
           $this->user_id = $user['user_id'];
           $this->assign('user', $user); //存储用户信息
           $this->assign('user_id', $this->user_id);
        } else {
           header("location:" . U('User/login'));
           exit;
        }
    }

    
    /*
    * 获取用户蜜蜂分类列表
    * 1、点击采蜜时，倒计时1小时，倒计时结束后可再次点击采蜜进入下一轮。
    * 2、判断采蜜次数是否大于60次
    * 3、获取最近一次采蜜时间+1小时倒计时。剩余倒计时时间=(采蜜时间+1小时倒计时)-当前时间
    */ 
    public function beeCategory(){
        $where = ' is_oviposition=2 and depart_num < 60 and status = 1 and uid='.$this->user_id;
        // 统计该用户蜜蜂种类数量
        $categoryData = Db::query('select sum(worker_bee) worker_bee,sum(scout_bee) scout_bee,sum(house_bee) house_bee,sum(security_bee) security_bee from tp_user_bee where'. $where);
        $whereFw['uid'] = $this->user_id;
        $whereFw['level'] = 2;
        $whereFw['status'] = 1;
        $fwData = M('user_bee')->where($whereFw)->count('id');
        if($categoryData){

            $wheres['uid'] = $this->user_id;
            $wheres['is_oviposition'] = 2; // 已孵化
            $wheres['depart_num'] = ['<', 60]; // 采蜜次数少于60的
            $oneDatas = M('user_bee')->field('uid, depart_time')->where($wheres)->order('depart_time desc')->find();
            $ifTime = $oneDatas['depart_time']+$this->accTime; // 一小时
            $categoryData[0]['fwNum'] = $fwData;
            if(time()<$ifTime){
                $flag = 1; // 正在采蜜中
            }else{
                $flag = 2; // 不在采蜜中
            }
        }else{
            $categoryData[0]['worker_bee'] = 0;
            $categoryData[0]['fwNum'] = $fwData;
            $flag = 2; // 不在采蜜中
        }
        $this->assign('categoryData', $categoryData);
        $this->assign('flag', $flag);
        return $this->fetch('/bee/classify');
    }

    /*
    * Type: 804喂养蜂王浆，805喂养蜜糖，806蜂箱消耗阳光值，807蜜蜂消耗露水
    * 使用蜂王浆每天喂养蜂王
    * 喂养蜂王浆的数量后台设置
    */
    public function feedDebby(){

        // 判断是否存在蜂王
        $beeOne = M('user_bee')->where(['uid'=>$this->user_id, 'level'=>2])->find();
        if(!$beeOne){
            return json(['code'=>'-1','msg'=>'你还没蜂王哦']);
        }
        /* // 获取bee_flow记录判断当天是否已经喂养过一次
        $flowData = M('bee_flow')->where(['uid'=>$this->user_id, 'type'=>804])->whereTime('create_time', 'today')->find();
        if($flowData){
            return json(['code'=>'-1','msg'=>'蜂王今天喂食过了哦']);
        } */
        $t = time();
        $start_time = mktime(0,0,0,date('m'),date('d'),date('Y'));;  //当天开始时间
        // 获取bee_flow记录判断当天是否已经喂养过一次
        // $where1['uid'] = $this->user_id;
        // $where1['type'] = 804; // 标记
        // $where1['create_time'] = ['>', $start_time]; 
        // $flowData = M('bee_flow')->where($where1)->count('id');
        // if($flowData){
        //     return json(['code'=>'-1','msg'=>'蜂王喂食过了哦']);
        // }
       
        $five_can_num = $this->config['five_can_num']?$this->config['five_can_num']:6;
        $where2['uid'] = $this->user_id;
        $where2['type'] = 804; // 标记
        $where2['create_time'] = ['>', $start_time]; 
        $getData = M('bee_flow')->where($where2)->count('id');
        if($getData>=$five_can_num){
            return json(['code'=>'-1','msg'=>'今天已超出最大操作次数...']);
        }
        
        // 判断当前用户的蜂王浆是否足够
        $beeMilkNum = $this->config['seven_bee_milk_days']?$this->config['seven_bee_milk_days']:1;

        $beeMilk = M('user_bee_account')->field('uid,bee_milk')->where(['uid'=>$this->user_id])->find();
        if($beeMilk['bee_milk']<$beeMilkNum){
            return json(['code'=>'-1','msg'=>'你的蜂王浆不足哦']);
        }

        // 执行喂养操作
        $decRes = M('user_bee_account')->where(['uid'=>$this->user_id])->setDec('bee_milk', $beeMilkNum);
        $resU = M('users')->where(['user_id'=>$this->user_id])->setInc('pay_points', $beeMilkNum); // 蜂王浆users表字段

        if(!$decRes){
            return json(['code'=>'-1','msg'=>'喂养失败,稍后再试']);
        }
        // 插入喂养记录
        $logs = array(
            'uid' => $this->user_id,
            'type' => 804,
            'inc_or_dec' => 2,
            'num' => $beeMilkNum,
            'create_time' => time(),
            'note' => '蜂王喂食蜂王浆'.$beeMilkNum.'滴'
        );
        $res = M('bee_flow')->save($logs);
        if($decRes&&$res){
            return json(['code'=>200,'msg'=>'喂养蜂王成功']);
        }else{
            return json(['code'=>'-1','msg'=>'喂养蜂王失败']);
        }
    }

    /*
     * 用户点击采蜜
     * 1、判断该用户是否有可用的工蜂
     * 2、判断侦查蜂是否已经外出侦查完成
     * 3、判断是否一天操作过次数
     * 4、判断是否在后台设置的冷却期时间
     */
    public function gatherHoney()
    {
        $where['uid'] = $this->user_id;
        $where['is_oviposition'] = 2; // 已孵化
        $where['depart_num'] = ['<', 60]; // 采蜜次数少于60的
        // 获取一条满足采蜜记录
        $data = M('user_bee')->where($where)->find();
        if($data){

            $t = time();
            $start_time = mktime(0,0,0,date('m'),date('d'),date('Y'));;  //当天开始时间
            // 判断是否喂养
            $five_can_num = $this->config['five_can_num']?$this->config['five_can_num']:6;
            $whereW['uid'] = $this->user_id;
            $whereW['type'] = 804; // 喂养标记
            $whereW['create_time'] = ['>', $start_time]; 
            $getDataW = M('bee_flow')->where($whereW)->count('id');

            // 采蜜次数
            $where2['uid'] = $this->user_id;
            $where2['type'] = 701; // 采蜜标记
            $where2['create_time'] = ['>', $start_time]; 
            $getData = M('bee_flow')->where($where2)->count('id');
            if($getDataW < $getData){
                return json(['code'=>'-1','msg'=>'请先喂养蜂王哦..']);
            }

            // 判断是否在后台设置的冷却期时间
            $five_cooling_time = $this->config['five_cooling_time']?$this->config['five_cooling_time']*60:30*60;
            $where3['uid'] = $this->user_id;
            $where3['type'] = 701; // 采蜜标记
            $getData2 = M('bee_flow')->where($where3)->order('create_time desc')->find();
            if($getData2){
                if(time() < $getData2['create_time']+$five_cooling_time){
                    return json(['code'=>'-1','msg'=>'采蜜冷却中...']);
                }
            }

            // 判断当天是否采蜜达到后台设置次数
            if($getData>=$five_can_num){
                return json(['code'=>'-1','msg'=>'今天已超出最大操作次数...']);
            }

            // 采蜜前判断侦查蜂是否已经外出以及侦查完
            $where1['uid'] = $this->user_id;
            $where1['type'] = 702; // 侦查标记
            $outData = M('bee_flow')->where($where1)->order('create_time desc')->find();
            if(!$outData){
                return json(['code'=>'-1','msg'=>'采蜜前请派出工蜂侦查..']);
            }
            // 判断今天是否有一条侦查记录大于等于开始时间
            if($start_time >= $outData['create_time']+$this->accTime){
                return json(['code'=>'-1','msg'=>'采蜜前请派出工蜂侦查...']);
            }

            // 比较侦查次数和采蜜次数
            $where5['uid'] = $this->user_id;
            $where5['type'] = 702; // 侦查标记
            $where2['create_time'] = ['>', $start_time]; 
            $caiNum = M('bee_flow')->where($where5)->count('id');
            if($caiNum < $getData){
                return json(['code'=>'-1','msg'=>'采蜜前请派出工蜂侦查....']);
            }
            // 判断是否侦查完成
            if(time() < $outData['create_time']+$this->accTime){
                return json(['code'=>'-1','msg'=>'正在等待工蜂侦查完成...']);
            }
        
            return json(['code'=>200,'msg'=>'获取成功','data'=>$data]);
            
        }else{
            return json(['code'=>'-1','msg'=>'你暂无工蜂']);
        }
    }


    /*
    * 用户确定采蜜操作
    * 更新已经孵化的记录
    * 1、记录当前采蜜时间
    * 2、采蜜次数增加1
    * 3、返回倒计时时间给前端页面
    */
    public function confirmHoney(){

        $where['uid'] = $this->user_id;
        $where['is_oviposition'] = 2;
        $where['depart_num'] = ['<', 60];
        $userTable = M('user_bee');

        // 判断是否在采蜜时间内
        $oneData = $userTable->field('uid, depart_time')->where($where)->find();
        if($oneData['depart_time']){

            $ifTime = $oneData['depart_time']+$this->accTime; // 一小时
            if(time()<$ifTime){
                return json(['code'=>'-1','msg'=>'工蜂正在采蜜中哦']);
            }
        }
        // 更新user_bee采蜜时间和采蜜次数记录
        $updateTime['depart_time'] = time();
        $beeNum = $userTable->where($where)->count('id');
        $res = $userTable->where($where)->update($updateTime);
        $res2 = $userTable->where($where)->setInc('depart_num', 1);
        if($res && $res2){     
            // 日志
            $log = array(
                'uid' => $this->user_id,
                'type' => 701,
                'create_time' => time(),
                'note' => '工蜂采蜜'
            );
            $getHoeny = array(
                'uid' => $this->user_id,
                'bee_num' => $beeNum,
                'honey_num' => $this->config['six_worker_bee_days']?$this->config['six_worker_bee_days']:310,
                'total_honey_num' => $this->config['six_worker_bee_days']?$this->config['six_worker_bee_days']*$beeNum:310*$beeNum,
                'create_time' => time()
            );
            M('get_gooey')->save($getHoeny); // 插入采蜜记录
            $this->insert_log($log);       
            return json(['code'=>200,'msg'=>'采蜜成功']);
        }else{
            return json(['code'=>'-1','msg'=>'采蜜失败,稍后再试']);
        }

    }

    /*
    * 获取当前用户蜜蜂是否在采蜜中，获取采蜜倒计时时间
    */
    public function getDownTime(){

        $wheres['uid'] = $this->user_id;
        $wheres['is_oviposition'] = 2; // 已孵化
        $wheres['depart_num'] = ['<', 60]; // 采蜜次数少于60的
        $oneDatas = M('user_bee')->field('uid, depart_time')->where($wheres)->order('depart_time desc')->find();
        $ifTime = $oneDatas['depart_time']+$this->accTime; // +1小时
        $res = array();
        if(time()<$ifTime){
            $res['flag'] = 1; // 正在采蜜中
            $res['times'] = $ifTime-time();
        }else{
            $res['flag'] = 2; // 不在采蜜中
        }
        return json($res);
    }


    /*
    * 侦查蜂侦查操作60分钟内只能操作一次
    */
    public function checkBeeAction(){

        $where1['uid'] = $this->user_id;
        $where1['is_oviposition'] = 2; // 已孵化
        $where1['depart_num'] = ['<', 60]; // 采蜜次数少于60的
        // 获取一条满足采蜜记录
        $data = M('user_bee')->where($where1)->find();
        if(!$data){
           return json(['code'=>'-1','msg'=>'暂无侦查蜂']);
        }
        // 查询60分钟内是否侦查过
        $where['uid'] = $this->user_id;
        $where['type'] = 702;
        $checkOne = M('bee_flow')->field('uid,create_time')->where($where)->order('create_time desc')->find();
        $checkTime = $checkOne['create_time']+$this->accTime; // +1小时
        if(time()<$checkTime){
            return json(['code'=>'-1','msg'=>'已经在侦查中...']);
        }

        // 判断是否在后台设置的冷却期时间
        $five_cooling_time = $this->config['five_cooling_time']?$this->config['five_cooling_time']*60:30*60;
        $where3['uid'] = $this->user_id;
        $where3['type'] = 702; // 采蜜标记
        $getData2 = M('bee_flow')->where($where3)->order('create_time desc')->find();
        if($getData2){
            if(time() < $getData2['create_time']+$five_cooling_time){
                return json(['code'=>'-1','msg'=>'侦查蜂正在冷却中...']);
            }
        }

        // 判断当天是否达到后台设置次数
        $five_can_num = $this->config['five_can_num']?$this->config['five_can_num']:6;
        $where2['uid'] = $this->user_id;
        $where2['type'] = 702; // 侦查标记
        $getData = M('bee_flow')->where($where2)->whereTime('create_time', 'today')->count('id');
        if($getData>=$five_can_num){
            return json(['code'=>'-1','msg'=>'今天已超出最大操作次数...']);
        }

        $checkData = array(
            'uid' => $this->user_id,
            'type' => 702,
            'create_time' => time(),
            'note' => '派侦查蜂外出侦查'
        );
        $res = $this->insert_log($checkData);      
        if($res){
            return json(['code'=>200,'msg'=>'侦查蜂成功外出侦查']);
        }else{
            return json(['code'=>'-1','msg'=>'侦查失败,稍后再试']);
        }
    }

    /*
    * 安保蜂安保操作60分钟内只能操作一次
    */
    public function securityBeeAction(){

        $where1['uid'] = $this->user_id;
        $where1['is_oviposition'] = 2; // 已孵化
        $where1['depart_num'] = ['<', 60]; // 采蜜次数少于60的
        // 获取一条满足采蜜记录
        $data = M('user_bee')->where($where1)->find();
        if(!$data){
           return json(['code'=>'-1','msg'=>'暂无安保蜂']);
        }

        // 查询60分钟内是否安保过
        $where['uid'] = $this->user_id;
        $where['type'] = 703;
        $checkOne = M('bee_flow')->field('uid,create_time')->where($where)->order('create_time desc')->find();
        $checkTime = $checkOne['create_time']+$this->accTime; // +1小时
        if(time()<$checkTime){
            return json(['code'=>'-1','msg'=>'正在安保中...']);
        }

        // 判断当天是否达到后台设置次数
        $five_can_num = $this->config['five_can_num']?$this->config['five_can_num']:6;
        $where2['uid'] = $this->user_id;
        $where2['type'] = 703; // 侦查标记
        $getData = M('bee_flow')->where($where2)->whereTime('create_time', 'today')->count('id');
        if($getData>=$five_can_num){
            return json(['code'=>'-1','msg'=>'今天已超出最大操作次数...']);
        }

        $checkData = array(
            'uid' => $this->user_id,
            'type' => 703,
            'create_time' => time(),
            'note' => '派安保蜂外出安保'
        );
        $res = $this->insert_log($checkData);      
        if($res){
            return json(['code'=>200,'msg'=>'安保蜂成功外出安保']);
        }else{
            return json(['code'=>'-1','msg'=>'安保失败,稍后再试']);
        }
    }


    /*
    * 内勤蜂操作60分钟内只能操作一次
    */
    public function houseBeeAction(){

        $where1['uid'] = $this->user_id;
        $where1['is_oviposition'] = 2; // 已孵化
        $where1['depart_num'] = ['<', 60]; // 采蜜次数少于60的
        // 获取一条满足采蜜记录
        $data = M('user_bee')->where($where1)->find();
        if(!$data){
           return json(['code'=>'-1','msg'=>'暂无内勤蜂']);
        }
        // 查询60分钟内是否安保过
        $where['uid'] = $this->user_id;
        $where['type'] = 704;
        $checkOne = M('bee_flow')->field('uid,create_time')->where($where)->order('create_time desc')->find();
        $checkTime = $checkOne['create_time']+$this->accTime; // +1小时
        if(time()<$checkTime){
            return json(['code'=>'-1','msg'=>'正在内勤中...']);
        }

        
        // 判断当天是否达到后台设置次数
        $five_can_num = $this->config['five_can_num']?$this->config['five_can_num']:6;
        $where2['uid'] = $this->user_id;
        $where2['type'] = 704; // 侦查标记
        $getData = M('bee_flow')->where($where2)->whereTime('create_time', 'today')->count('id');
        if($getData>=$five_can_num){
            return json(['code'=>'-1','msg'=>'今天已超出最大操作次数...']);
        }

        $checkData = array(
            'uid' => $this->user_id,
            'type' => 704,
            'create_time' => time(),
            'note' => '派内勤蜂外出内勤'
        );
        $res = $this->insert_log($checkData);      
        if($res){
            return json(['code'=>200,'msg'=>'内勤蜂成功外出内勤']);
        }else{
            return json(['code'=>'-1','msg'=>'内勤失败,稍后再试']);
        }
    }


    
    // 道具日志流水
    public function insert_log($data)
    {
        $res = M('bee_flow')->save($data);
        return $res;
    }

    // 返回客户端数据信息
    public function msg($code, $msg, $data=''){

        $res = [
            'data'=>$data,
            'code'=>1,
            'msg'=>'操作完成'
        ];
        return json($res);
    }

}