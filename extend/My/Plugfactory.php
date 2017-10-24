<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2016/10/17
 * Time: 17:14
 */
namespace My;
class Plugfactory
    {
        private static $alipay=null;
        private static $wxpay=null;
        private static $unionpay=null;
    private static $email=null;
        function __construct()
        {

        }

    /**
     * 支付宝
     * @param $partner 商户号
     * @param $seller_id 商户账号（邮箱）
     * @param $subject 单位名称
     * @param $private_key_path 私钥文件地址
     */
        public static function getAliPay($partner='',$seller_id='',$subject='',$private_key='',$private_key_path='')
        {
            if(self::$alipay)return self::$alipay;
            vendor('alipay/Appalipay');
            $alipay=config('alipay');
            if(isset($alipay['partner'])&&$alipay['partner'])$partner=$alipay['partner'];
            if(isset($alipay['seller_id'])&&$alipay['seller_id'])$seller_id=$alipay['seller_id'];
            if(isset($alipay['subject'])&&$alipay['subject'])$subject=$alipay['subject'];
            if(isset($alipay['private_key'])&&$alipay['private_key'])$private_key=$alipay['private_key'];
            if(isset($alipay['private_key_path'])&&$alipay['private_key_path'])$private_key_path=$alipay['private_key_path'];

            $alipay=new \Appalipay($partner,$seller_id,$subject,$private_key,$private_key_path);
            self::$alipay=$alipay;
            return $alipay;
        }

    /**
     * app微信支付
     * @param $private_key 私钥
     * @param $appid appid
     * @param $mch_id 商户号
     * @return mixed
     */
        public static function getWxPay($private_key='',$appid='',$mch_id='',$appsecret='')
        {
            if(self::$wxpay)return self::$wxpay;
            vendor('weixin/Appweixin');
            $alipay=config('weixin');
            if(isset($alipay['private_key'])&&$alipay['private_key'])$private_key=$alipay['private_key'];
            if(isset($alipay['appid'])&&$alipay['appid'])$appid=$alipay['appid'];
            if(isset($alipay['mch_id'])&&$alipay['mch_id'])$mch_id=$alipay['mch_id'];
            if(isset($alipay['appsecret'])&&$alipay['appsecret'])$appsecret=$alipay['appsecret'];
            $weixin=new \Appweixin($private_key,$appid,$mch_id,$appsecret);
            self::$wxpay=$weixin;
            return $weixin;
        }

    /**
     * 银联
     * @param string $merId 商户号
     * @return \Uninpay
     */
        public static function getUnionPay($merId='')
        {
            if(self::$unionpay)return self::$unionpay;
            vendor('uninpay/Uninpay');
            $alipay=config('uninpay');
            if(isset($alipay['merId'])&&$alipay['merId'])$merId=$alipay['merId'];
            $union=new \Uninpay($merId);
            self::$unionpay=$union;
            return $union;
        }

    /**
     * 邮箱
     * @param string $email_host 邮箱服务器
     * @param string $email_port 邮箱端口
     * @param string $email_id   企业邮箱账号
     * @param string $email_pass 邮箱密码
     * @param string $site_name 发送人
     * @return \Uninpay
     */
        public static function getEmail($email_host='',$email_port='',$email_id='',$email_pass='',$site_name='')
        {
            if(self::$email)return self::$email;
            vendor('email/Email');
            $alipay=config('email_conf');
            if(isset($alipay['email_host'])&&$alipay['email_host'])$email_host=$alipay['email_host'];
            if(isset($alipay['email_port'])&&$alipay['email_port'])$email_port=$alipay['email_port'];
            if(isset($alipay['email_id'])&&$alipay['email_id'])$email_id=$alipay['email_id'];
            if(isset($alipay['email_pass'])&&$alipay['email_pass'])$email_pass=$alipay['email_pass'];
            if(isset($alipay['site_name'])&&$alipay['site_name'])$site_name=$alipay['site_name'];
            $conf=array(
                'email_host'=>$email_host,
                'email_port'=>$email_port,//邮箱服务器端口
                'email_id'=>$email_id,//企业邮箱账号
                'email_pass'=>$email_pass,//企业邮箱密码
                'email_addr'=>$email_id,//企业邮箱账号
                'site_name'=>$site_name
            );
            $email=new \Email($conf);
            self::$email=$email;
            return $email;
        }
    }