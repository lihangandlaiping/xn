<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2016/10/21
 * Time: 16:03
 */
class Uninpay{
    private $merId='';
    function __construct($merId)
    {
        $this->merId=$merId;
    }

    /**
     * 返回预支付tn
     * @param $order_sn
     * @param $total_fee
     * @param $notify_url
     * @return bool
     */
    public function returnSign($order_sn,$total_fee,$notify_url)
    {
        require_once  'sdk/acp_service.php';
        header ( 'Content-type:text/html;charset=utf-8' );
        // $total_fee=$val['value'];
        $params = array(

            //以下信息非特殊情况不需要改动
            'version' => '5.0.0',                 //版本号
            'encoding' => 'utf-8',				  //编码方式
            'txnType' => '02',				      //交易类型
            'txnSubType' => '01',				  //交易子类
            'bizType' => '000201',				  //业务类型
            'frontUrl' =>  'http://www.baidu.com',  //前台通知地址
            'backUrl' => $notify_url,	  //后台通知地址
            'signMethod' => '01',	              //签名方法
            'channelType' => '08',	              //渠道类型，07-PC，08-手机
            'accessType' => '0',		          //接入类型
            'currencyCode' => '156',	          //交易币种，境内商户固定156

            //TODO 以下信息需要填写
            'merId' =>$this->merId,		//商户代码，请改自己的测试商户号，此处默认取demo演示页面传递的参数
            'orderId' => $order_sn,	//商户订单号，8-32位数字字母，不能含“-”或“_”，此处默认取demo演示页面传递的参数，可以自行定制规则
            'txnTime' => date('YmdHis'),	//订单发送时间，格式为YYYYMMDDhhmmss，取北京时间，此处默认取demo演示页面传递的参数
            'txnAmt' => $total_fee*100,	//交易金额，单位分，此处默认取demo演示页面传递的参数
// 		'reqReserved' =>'透传信息',        //请求方保留域，透传字段，查询、通知、对账文件中均会原样出现，如有需要请启用并修改自己希望透传的数据

            //TODO 其他特殊用法请查看 pages/api_05_app/special_use_preauth.php
        );
        AcpService::sign ( $params ); // 签名
        $url = SDK_App_Request_Url;

        $result_arr = AcpService::post ( $params, $url);
        if($result_arr["respCode"]=='00'&&$result_arr["tn"])
        {
            return $result_arr["tn"];
        }
       return false;
    }
}