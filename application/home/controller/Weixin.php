<?php
/**
 * tpshop
 * ============================================================================
 * 版权所有 2015-2027 深圳搜豹网络科技有限公司，并保留所有权利。
 * 网站地址: http://www.tp-shop.cn
 * ----------------------------------------------------------------------------
 * 这不是一个自由软件！您只能在不用于商业目的的前提下对程序代码进行修改和使用 .
 * 不允许对程序代码以任何形式任何目的的再发布。
 * 采用TP5助手函数可实现单字母函数M D U等,也可db::name方式,可双向兼容
 * ============================================================================
 */

namespace app\home\controller;

use app\common\logic\WechatLogic;

class Weixin
{
    /**
     * 处理接收推送消息
     */
    public function index()
    {
    	$data = file_get_contents("php://input");
    	if ($data) {
    		$re = $this->xmlToArray($data);
    		$this->write_log(json_encode($re));
    		// if ($re['Event']=='subscribe' || $re['Event']=='SCAN') {
	    		$url = SITE_URL.'/mobile/code/next?shangji='.$re['EventKey'].'&xiaji='.$re['FromUserName'].'&event='.$re['Event'];
	    		httpRequest($url);
    		// }
    	}

		exit($_GET['echostr']);
	        $logic = new WechatLogic;
	        $logic->handleMessage();
    }

    public function xmlToArray($xml)
    {
    	$obj = simplexml_load_string($xml, 'SimpleXMLElement', LIBXML_NOCDATA);
		$json = json_encode($obj);
		$arr = json_decode($json, true);  
		return $arr;
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