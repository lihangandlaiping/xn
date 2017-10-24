<?php
/**
 * 统一下单
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2016/10/24
 * Time: 11:28
 */
class Unifiedorder
{
    private $conf=array(
        'appid'=>'',
        'mch_id'=>'',
        'notify_url'=>'',
        'out_trade_no'=>'',
        'total_fee'=>'',
        'trade_type'=>'',
        'body'=>'',
        'appsecret'=>''

    );
    private $url='https://api.mch.weixin.qq.com/pay/unifiedorder';
    function __construct($conf=null)
    {
        if($conf)$this->conf=$conf;
    }
    /**
     * 微信签名
     */
    public  function wxSign($data,$keys)
    {
        ksort($data);
        $str='';
        foreach($data as $key=>$val)
        {
            if($val)
            {
                if($str!='')$str.='&';
                $str.="{$key}={$val}";
            }
        }
        $str.='&key='.$keys;
        $sign=strtoupper(MD5($str));
        return $sign;
    }
    /**
     * 获取客户端ip
     * @return string
     */
    private function getIp()
    {
        $cip='';
        if ($_SERVER['REMOTE_ADDR']) {
            $cip = $_SERVER['REMOTE_ADDR'];
        } elseif (getenv("REMOTE_ADDR")) {
            $cip = getenv("REMOTE_ADDR");
        } elseif (getenv("HTTP_CLIENT_IP")) {
            $cip = getenv("HTTP_CLIENT_IP");
        } else {
            $cip = "unknown";
        }
        return $cip;
    }
    /**
     *
     * 产生随机字符串，不长于32位
     * @param int $length
     * @return 产生的随机字符串
     */
    public  function getNonceStr($length = 32)
    {
        $chars = "abcdefghijklmnopqrstuvwxyz0123456789";
        $str ="";
        for ( $i = 0; $i < $length; $i++ )  {
            $str .= substr($chars, mt_rand(0, strlen($chars)-1), 1);
        }
        return $str;
    }

    /**
     *获取统一支付信息
     * @param $private_key 支付秘钥
     * @return array
     */
    public function getOrder($private_key)
    {
        $data=$this->conf;
        $data['nonce_str']=$this->getNonceStr();
        $data['spbill_create_ip']=$this->getIp();
        $data['sign']=$this->wxSign($data,$private_key);
        $xml = "<xml>";
        foreach ($data as $key=>$val)
        {
            if (is_numeric($val))
            {
                $xml.="<".$key.">".$val."</".$key.">";

            }
            else
                $xml.="<".$key."><![CDATA[".$val."]]></".$key.">";
        }
        $xml.="</xml>";
        $ch=curl_init();
        curl_setopt($ch, CURLOPT_URL, $this->url); //设置访问路径
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, TRUE); //设置可以返回字符串
        curl_setopt($ch, CURLOPT_POST,TRUE);//post请求
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER,false);
        curl_setopt($ch, CURLOPT_SSL_VERIFYHOST,false);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $xml);//设置传递的参数
        $request=curl_exec($ch);
        curl_close($ch);
        $request=json_decode(json_encode(simplexml_load_string($request, 'SimpleXMLElement', LIBXML_NOCDATA)),true);
        if($request['return_code']=='SUCCESS'&&$request['result_code']=='SUCCESS')
        {

            return array('code'=>1,'data'=>$request);
        }
        else
        {
            return array('code'=>0,'data'=>$request['return_msg']);
        }
    }
    /**
     * 获取用户openid
     */
    public  function get_openid()
    {
        $openid=session('wx_openid');
        if($openid)return $openid;
        $code=input('code','');
        if(!$code)
        {
            $redirect_url='http://'.$_SERVER['HTTP_HOST'].$_SERVER['REQUEST_URI'];
            $redirect_url=urlencode($redirect_url);
            $url="https://open.weixin.qq.com/connect/oauth2/authorize?appid={$this->conf['appid']}&redirect_uri={$redirect_url}&response_type=code&scope=snsapi_base&state=STATE#wechat_redirect";
            redirect($url);return false;
        }
        else
        {
            $url="https://api.weixin.qq.com/sns/oauth2/access_token?appid={$this->conf['appid']}&secret={$this->conf['appsecret']}&code={$code}&grant_type=authorization_code";
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
                $url="https://api.weixin.qq.com/sns/oauth2/refresh_token?appid={$this->conf['appid']}&grant_type=refresh_token&refresh_token={$result['refresh_token']} ";
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
