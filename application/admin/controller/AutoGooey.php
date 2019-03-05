<?php

namespace app\admin\controller;
use think\AjaxPage;
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
	* 3、根据用户uid发放蜜糖和蜂王浆
	*
	*
	*/
	public function sentGooey(){

		// 获取后台相关的配置信息
		$where['inc_type'] = 'game';
		$where['name'] = array('in','five_get_gooey,six_worker_bee_days');
		$configData = Db::name('config')->where($where)->column('name,value');

		// 获取采蜜时间结束并且没发放蜜糖的订单
		$gooeyWhere['is_out'] = 0;
		$gooeyWhere['status'] = 1;
		$userBeeData = Db::name('get_gooey')->where($gooeyWhere)->column('id,uid,bee_num,total_honey_num,is_out,create_time,status');
		echo "<pre/>";
		// echo $configData['five_get_gooey'];
		var_dump($userBeeData);
		// var_dump($configData);
		die;
	}
}


