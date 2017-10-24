<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2016/10/17
 * Time: 17:46
 */
class Appalipay{
    private  $partner='';
    private $seller_id='';
    private $subject='';
    private $private_key='';
    private $private_key_path='';
    function __construct($partner,$seller_id,$subject,$private_key,$private_key_path)
    {
        $this->partner=$partner;
        $this->seller_id=$seller_id;
        $this->subject=$subject;
        $this->private_key=$private_key;
        $this->private_key_path=$private_key_path;
    }
    /**
     * 支付宝RSA签名
     * @param $data 待签名数据
     * @param $private_key_path 商户私钥文件路径
     * return 签名结果
     */
     private function rsaSign($data, $private_key_path) {

        $priKey = file_get_contents($private_key_path);
        $res = openssl_get_privatekey($priKey);
        openssl_sign($data, $sign, $res);
        openssl_free_key($res);
        //base64编码
        $sign = base64_encode($sign);
        $sign=urlencode($sign);
        return $sign;
    }

    /**
     * 支付宝app签名
     * @param $out_trade_no 订单编号
     * @param $body 描述
     * @param $notify_url 回调地址
     * @param $total_fee 金额
     * @return string
     */
    function paySignature($ordersn,$body,$notify_url,$total_fee)
    {
        $data['partner']=$this->partner;
        $data['seller_id']=$this->seller_id;
        $data['out_trade_no']=$ordersn;
        $data['subject']=$this->subject;
        $data['body']=$body;
        $data['total_fee']=$total_fee;
        $data['notify_url']=$notify_url;
        $data['service']='mobile.securitypay.pay';
        $data['payment_type']='1';
        $data['_input_charset']='utf-8';
        $data['it_b_pay']='30m';
        ksort($data);
        $str='';
        foreach($data as $key=>$val)
        {
            if($val)
            {
                if($str!='')$str.='&';
                $str.="{$key}=\"{$val}\"";
            }

        }

        $sign=$this->rsaSign($str,$this->private_key_path);
        $data['sign']=$sign;
        $data['sign_type']='RSA';
        $str.='&sign="'.$sign.'"&sign_type="RSA"';
        return $str;
    }

    /**
     * 手机支付
     * @param $order_sn
     * @param $total_fee
     * @param $show_url
     * @param $notify_url
     * @param string $body
     */
    public function getMobileHtml($order_sn,$total_fee,$show_url,$notify_url,$body='支付宝安全支付')
    {
        require_once("lib/alipay_submit.class.php");
        $alipay_config=array(
            'service'=>'alipay.wap.create.direct.pay.by.user',
            'payment_type'=>'1',
            'transport'=>'http',
            'input_charset'=> strtolower('utf-8'),
            'sign_type'=>strtoupper('RSA'),
            'partner'=>$this->partner,
            'seller_id'=>$this->partner,
            'private_key'=>$this->private_key

        );
        $parameter = array(
            "service"       => $alipay_config['service'],
            "partner"       => $alipay_config['partner'],
            "seller_id"  => $alipay_config['seller_id'],
            "payment_type"	=> $alipay_config['payment_type'],
            "notify_url"	=> $notify_url,
            "return_url"	=> $show_url,
            "_input_charset"	=> trim(strtolower($alipay_config['input_charset'])),
            "out_trade_no"	=> $order_sn,
            "subject"	=> $this->subject,
            "total_fee"	=> $total_fee,
            "show_url"	=> $show_url,
            "app_pay"	=> "Y",//启用此参数能唤起钱包APP支付宝
            "body"	=> $body,
            //其他业务参数根据在线开发文档，添加参数.文档地址:https://doc.open.alipay.com/doc2/detail.htm?spm=a219a.7629140.0.0.2Z6TSk&treeId=60&articleId=103693&docType=1
            //如"参数名"	=> "参数值"   注：上一个参数末尾需要“,”逗号。

        );

//建立请求
        $alipaySubmit = new AlipaySubmit($alipay_config);
        return $html_text = $alipaySubmit->buildRequestForm($parameter,"get", "确认");
    }

    /**
     * @param $order_sn
     * @param $total_fee
     * @param $show_url
     * @param $notify_url
     * @param string $body
     * @return 提交表单HTML文本
     */
    public function getPcHtml($order_sn,$total_fee,$show_url,$notify_url,$body='支付宝安全支付')
    {
        require_once("lib/alipay_submit.class.php");
        $alipay_config=array(
            'service'=>'create_direct_pay_by_user',
            'payment_type'=>'1',
            'transport'=>'http',
            'input_charset'=> strtolower('utf-8'),
            'sign_type'=>strtoupper('RSA'),
            'partner'=>$this->partner,
            'seller_id'=>$this->partner,
            'private_key'=>$this->private_key

        );
        $parameter = array(
            "service"       => $alipay_config['service'],
            "partner"       =>$alipay_config['partner'],
            "seller_id"  => $alipay_config['seller_id'],
            "payment_type"	=>$alipay_config['payment_type'],
            "notify_url"	=> $notify_url,
            "return_url"	=> $show_url,

            "anti_phishing_key"=>'',
            "exter_invoke_ip"=>'',
            "out_trade_no"	=> $order_sn,
            "subject"	=>  $this->subject,
            "total_fee"	=> $total_fee,
            "body"	=> $body,
            "_input_charset"	=> strtolower('utf-8')
            //其他业务参数根据在线开发文档，添加参数.文档地址:https://doc.open.alipay.com/doc2/detail.htm?spm=a219a.7629140.0.0.kiX33I&treeId=62&articleId=103740&docType=1
            //如"参数名"=>"参数值"

        );
        //建立请求
        $alipaySubmit = new AlipaySubmit($alipay_config);
        return $html_text = $alipaySubmit->buildRequestForm($parameter,"get", "确认");
    }
}