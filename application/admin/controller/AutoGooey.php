<?php

namespace app\admin\controller;
use think\Db;

/**
 * 根据采蜜订单自动处理发放蜜糖
 *
 */
class AutoGooey
{
	/**
	* 工蜂每次采蜜310克蜜糖，另外采蜜完成每次自动转入2滴蜂王浆到玩家帐户
	* 1、获取后台设置的配置信息
	* 2、获取用户的采蜜订单并且是未发放的蜜糖的订单
	* 3、循环判断订单是否已经采蜜结束
	* 4、根据用户uid发放蜜糖和蜂王浆
	* 5、插入日志记录
	*
	*/
	public function sentGooey(){

		// 获取后台相关的配置信息
		$where['inc_type'] = 'game';
		$where['name'] = array('in','five_get_gooey,six_worker_bee_days,five_get_make');
		$configData = Db::name('config')->where($where)->column('name,value');
		$five_get_gooey = $configData['five_get_gooey']?$configData['five_get_gooey']:30; // 采蜜时间
		$five_get_make = $configData['five_get_make']?$configData['five_get_make']:30; // 酿蜜时间
		$six_worker_bee_days = $configData['six_worker_bee_days']?$configData['six_worker_bee_days']:310; // 每次采蜜获得的蜜糖数量
		$seven_sun_days = $configData['seven_sun_days']?$configData['seven_sun_days']:10; // 蜂箱每天消耗阳光值
		$seven_water_days = $configData['seven_water_days']?$configData['seven_water_days']:10; // 蜜蜂每天消耗露水值
		$getTime = 60;// ($five_get_gooey+$five_get_make)*60; // 采蜜和酿蜜总时间
		// 获取采蜜时间结束并且没发放蜜糖的订单
		$gooeyWhere['is_out'] = 0;
		$gooeyWhere['status'] = 1;
		$userBeeData = array();
		$userBeeData = M('get_gooey')->where($gooeyWhere)->column('id,uid,bee_num,honey_num,total_honey_num,is_out,create_time,status');
		// 判断订单是否已经采蜜结束(采蜜时间+后台设置的时间)30*2(采蜜与酿蜜)
		// 循环添加用户获取到的蜜糖以及蜂王浆(每次100克)
		if($userBeeData){

			$countNum = 0;
			foreach ($userBeeData as $k => $v) {

				// 判断当前时间是否小于采蜜时间加1小时
				$getGooeyTime = $v['create_time']+$getTime;
				if(time()>$getGooeyTime){
                    $seven_sun_days_gooey=$seven_sun_days*5*$v['bee_num'];//后台设置蜂箱消耗阳光值转换成蜜糖数
                    $seven_water_days_gooey=$seven_water_days*5*$v['bee_num'];//后台设置蜜蜂消耗露水值转换成蜜糖数
					$gainTotalGooey = ($v['honey_num']*$v['bee_num'])-($v['bee_num']*100)-($v['bee_num']*$seven_sun_days*5)-($v['bee_num']*$seven_water_days*5); // 采蜜的蜂王数量*采蜜获取的蜜糖数，减去蜂箱消耗的等值的蜜糖和蜜蜂消耗的等值的蜜糖，最终得到的蜜糖   20190328再改需求为将等值蜜糖转化为蜂箱消耗的阳光，等值蜜糖转化为蜜蜂消耗的露水
					$autoBeeMilk = $v['bee_num']*2; // 采蜜蜂王的数量*2(100克蜜糖=2滴蜂王券)
                    $seven_sun_days=$seven_sun_days*$v['bee_num'];
                    $seven_water_days=$seven_water_days*$v['bee_num'];
					$res1 = Db::name('user_bee_account')->where('uid', $v['uid'])->setInc('gooey', $gainTotalGooey); // 蜜糖
					$res11 = Db::name('user_bee_account')->where('uid', $v['uid'])->setInc('sun_value', $seven_sun_days); // 阳光
					$res12 = Db::name('user_bee_account')->where('uid', $v['uid'])->setInc('water', $seven_water_days); // 露水
//					$res2 = Db::name('user_bee_account')->where('uid', $v['uid'])->setInc('bee_milk', $autoBeeMilk); // 蜂王浆
					$resU = Db::name('users')->where(['user_id'=>$v['uid']])->setInc('pay_points', $autoBeeMilk); // 蜂王浆users表字段
					// 修改当前订单
					$updateGooey['is_out'] = 2; // 采蜜结束已发放蜜糖
					$updateGooey['sent_bee_milk'] = $autoBeeMilk; // 采蜜结束发放的蜂王浆
					$updateGooey['day_milk_time'] = time(); // 发放蜂王券时间
					$updateGooey['sent_honey_time'] = time(); // 发放本次采蜜的蜜糖时间
					$res3 = Db::name('get_gooey')->where('uid', $v['uid'])->update($updateGooey);
					// var_dump($updateGooey);
					// echo '<br/>';

					// 记录插入流水表
			        $gooeyLog1 = array(
			        	'uid' => $v['uid'],
			            'type' => 802,
			            'inc_or_dec' => 1,
			            'num' => $gainTotalGooey,
			            'create_time' => time(),
			            'note' => '工蜂采蜜获得蜜糖'.$gainTotalGooey.'克'
			        );
			        $gooeyLog2 = array(
			        	'uid' => $v['uid'],
			            'type' => 803,
			            'inc_or_dec' => 1,
			            'num' => $autoBeeMilk,
			            'create_time' => time(),
			            'note' => '工蜂采蜜获得蜜糖转蜂王券'.$autoBeeMilk.'张'
			        );
			        $gooeyLog3 = array(
			        	'uid' => $v['uid'],
			            'type' => 401,
			            'inc_or_dec' => 1,
			            'num' => $seven_sun_days,
			            'create_time' => time(),
			            'note' => '将'.$seven_sun_days_gooey.'克蜜糖转化为'.$seven_sun_days.'阳光值'
			        );
			        $gooeyLog4 = array(
			        	'uid' => $v['uid'],
			            'type' => 301,
			            'inc_or_dec' => 1,
			            'num' => $seven_water_days,
			            'create_time' => time(),
			            'note' => '将'.$seven_water_days_gooey.'克蜜糖转化为'.$seven_water_days.'露水值'
			        );
					// var_dump($gooeyLog);
					// echo '<br/>';
			        $res4 = Db::name('bee_flow')->save($gooeyLog1);  
			        $res5 = Db::name('bee_flow')->save($gooeyLog2);  
			        $res6 = Db::name('bee_flow')->save($gooeyLog3);
			        $res7 = Db::name('bee_flow')->save($gooeyLog4);

			     $countNum++;
				}
			}

			echo 'dispose '.$countNum." the orders\n";
		}else{
			echo "No dispose the orders\n";
		}

	}
}


