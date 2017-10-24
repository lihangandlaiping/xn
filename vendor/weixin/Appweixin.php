<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2016/10/17
 * Time: 17:21
 */
class Appweixin
{
    private  $private_key='';
    private $appid='';
    private $mch_id='';
    private $appsecret='';
    function __construct($private_key,$appid,$mch_id,$appsecret)
    {
        $this->private_key=$private_key;
        $this->appid=$appid;
        $this->mch_id=$mch_id;
        $this->appsecret=$appsecret;
    }


    /**
     * app 支付签名
     * @param $total_fee 总金额
     * @param $notify_url 回调地址
     * @param $body 支付描述
     * @param $ordersn 订单号
     * @return array
     */
    public function wxSignature($total_fee,$notify_url,$body,$ordersn)
    {
        require_once 'lib/Unifiedorder.php';
        $conf=array(
            'appid'=>$this->appid,
            'mch_id'=>$this->mch_id,
            'notify_url'=>$notify_url,
            'out_trade_no'=>$ordersn,
            'total_fee'=>$total_fee*100,
            'trade_type'=>'APP',
            'body'=>$body

        );
        $order=new Unifiedorder($conf);
        $request=$order->getOrder($this->private_key);
        if($request['code']==1)
        {
            $datas["appid"] = $request['data']['appid'];
            $datas["noncestr"] = $order->getNonceStr();
            $datas["package"] = "Sign=WXPay";
            $datas["partnerid"] = $request['data']['mch_id'];
            $datas["prepayid"] = $request['data']['prepay_id'];
            $datas["timestamp"] = time();
            $s = $order->wxSign($datas,$this->private_key);
            $datas["sign"] = $s;
            return array('code'=>1,'data'=>$datas);
        }
        else
        {
            return array('code'=>0,'data'=>$request['data']);
        }
    }

    /**
     * 输入二维码图像
     * @param $order_sn
     * @param $total_fee
     * @param $notify_url
     * @param $body
     * @return array
     */
    function getWxNativePay($order_sn,$total_fee,$notify_url,$body)
    {
        require_once 'lib/Unifiedorder.php';
        $conf=array(
            'appid'=>$this->appid,
            'mch_id'=>$this->mch_id,
            'notify_url'=>$notify_url,
            'out_trade_no'=>$order_sn,
            'total_fee'=>$total_fee*100,
            'trade_type'=>'NATIVE',
            'body'=>$body

        );
        $order=new Unifiedorder($conf);
        $request=$order->getOrder($this->private_key);
        if($request['code']==1)
        {
            $url=$request['data']['code_url'];
            error_reporting(E_ERROR);
            require_once 'lib/phpqrcode.php';
            $url = urldecode($url);
            QRcode::png($url);
        }
        echo 'error';
    }

    /**
     * 微信浏览器html5支付
     * @param $order_sn
     * @param $total_fee
     * @param $notify_url
     * @param $body
     */
    public function html5Pay($order_sn,$total_fee,$notify_url,$body)
    {
        require_once 'lib/Unifiedorder.php';
        $conf=array(
            'appid'=>$this->appid,
            'mch_id'=>$this->mch_id,
            'notify_url'=>$notify_url,
            'openid'=>$this->get_openid(),
            'out_trade_no'=>$order_sn,
            'total_fee'=>$total_fee*100,
            'trade_type'=>'JSAPI',
            'body'=>$body

        );

        $order=new Unifiedorder($conf);
        $request=$order->getOrder($this->private_key);

        if($request['code']==1)
        {
            $datas["appid"] = $request['appid'];
            $datas["noncestr"] = $order->getNonceStr();
            $datas["package"] = "prepay_id={$request['prepay_id']}";
            $datas["signType"] = 'MD5';
            $datas["timestamp"] = time();
            $s = $order->wxSign($datas,$this->private_key);
            $datas["paySign"] = $s;
            $script='<script>
function onBridgeReady(){
   WeixinJSBridge.invoke(
            "getBrandWCPayRequest", {
           "appId" :"'.$datas["appid"].'",     //公众号名称，由商户传入
           "timeStamp":"'.$datas["timestamp"].'",         //时间戳，自1970年以来的秒数
           "nonceStr" : "'.$datas["noncestr"].'", //随机串
           "package" : "'.$datas["package"].'",
           "signType" : "MD5",         //微信签名方式：
           "paySign" : "'.$datas["paySign"].'" //微信签名
       },
       function(res){
           if(res.err_msg == "get_brand_wcpay_request：ok" ) {

           }     // 使用以上方式判断前端返回,微信团队郑重提示：res.err_msg将在用户支付成功后返回    ok，但并不保证它绝对可靠。
       }
   );
}
window.onload=function(){
if (typeof WeixinJSBridge == "undefined"){
   if( document.addEventListener ){
       document.addEventListener("WeixinJSBridgeReady", onBridgeReady, false);
   }else if (document.attachEvent){
       document.attachEvent("WeixinJSBridgeReady", onBridgeReady);
       document.attachEvent("onWeixinJSBridgeReady", onBridgeReady);
   }
}else{
   onBridgeReady();
}
}

</script>';
        }
        else
        {
            echo 'error';
        }
    }
    /**
     * 获取用户openid
     */
    private   function get_openid()
    {
        $openid=session('wx_openid');
        if($openid)return $openid;
        $code=input('code','');
        if(!$code)
        {
            $redirect_url='http://'.$_SERVER['HTTP_HOST'].$_SERVER['REQUEST_URI'];
            $redirect_url=urlencode($redirect_url);
            $url="https://open.weixin.qq.com/connect/oauth2/authorize?appid={$this->appid}&redirect_uri={$redirect_url}&response_type=code&scope=snsapi_base&state=STATE#wechat_redirect";
            redirect($url);exit;
        }
        else
        {
            $url="https://api.weixin.qq.com/sns/oauth2/access_token?appid={$this->appid}&secret={$this->appsecret}&code={$code}&grant_type=authorization_code";
            $result=$this->send_request($url);
            $result=json_decode($result,true);
            if(isset($result['errcode']))
            {
                return false;
            }
            else
            {
                $openid=$result['openid'];
                $access_token=$result['access_token'];
                $url="https://api.weixin.qq.com/sns/oauth2/refresh_token?appid={$this->appid}&grant_type=refresh_token&refresh_token={$result['refresh_token']} ";
                $result=$this->send_request($url);
                $result=json_decode($result,true);
                if(isset($result['access_token'])){return $result['openid'];}
                session('wx_openid',$openid);
                return $openid;
            }
        }
    }
    /**
     * @param $url
     * @param string $type 请求方式
     * @param string $postdata post数据 数组格式
     * @return mixed
     */
    private  function send_request($url,$type='get',$postdata='')
    {

        $ch=curl_init();
        curl_setopt($ch, CURLOPT_URL, $url); //设置访问路径
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, TRUE); //设置可以返回字符串
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER,false);
        curl_setopt($ch, CURLOPT_SSL_VERIFYHOST,false);
        $head=array('User-Agent:Mozilla/5.0 (Windows NT 6.1; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/51.0.2704.103 Safari/537.36');
        curl_setopt($ch, CURLOPT_HTTPHEADER,$head);
        if($type=='post')
        {

            curl_setopt($ch, CURLOPT_POST,TRUE);//post请求
            curl_setopt($ch, CURLOPT_POSTFIELDS, $postdata);//设置传递的参数

        }
        $request=curl_exec($ch);
        curl_close($ch);
        return $request;
    }

}