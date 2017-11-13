<?php

namespace wx_model;
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2017/4/25
 * Time: 13:54
 */
class Weixinmodel
{
    private $appid = 'wxab2dda3612ed50d4';//微信appid
    private $appsecret = 'c969e09896120a8f937ef7f37466f9e6';//微信应用密钥
    private $token = '';

    function __construct($wx_config = array('appid' => '', 'appsecret' => '', 'token' => ''))
    {
        if (!empty($wx_config['appid'])) $this->appid = $wx_config['appid'];
        if (!empty($wx_config['appsecret'])) $this->appsecret = $wx_config['appsecret'];
        if (!empty($wx_config['token'])) $this->token = $wx_config['token'];
    }


    /**
     * 自定义菜单
     * @param $data
     * @return array
     */
    function crate_menu($data)
    {
        if (!is_array($data)) exit('菜单格式错误');
        $access_token = $this->get_access_token();
        $url = 'https://api.weixin.qq.com/cgi-bin/menu/create?access_token=' . $access_token;
        $result = $this->send_request($url, $data, 'post');
        file_put_contents('wx.txt',$result);
        $result = json_decode($result, true);
        if ($result['errcode'] == 0)
            return array('code' => 1, 'msg' => '菜单生成成功');
        return array('code' => 0, 'msg' => '菜单生成失败');
    }

    /**
     * 删除自定义菜单
     * @return array
     */
    function delete_menu()
    {
        $access_token = $this->get_access_token();
        $url = 'https://api.weixin.qq.com/cgi-bin/menu/delete?access_token=' . $access_token;
        $result = $this->send_request($url);
        $result = json_decode($result, true);
        if ($result['errcode'] == 0)
            return array('code' => 1, 'msg' => '菜单删除成功');
        return array('code' => 0, 'msg' => '菜单删除失败');
    }

    /**
     * 文本消息模板
     * @param $FromUserName 发送者ID
     * @param $ToUserName  接受者ID
     * @param $message_info 内容
     */
    function text_message($FromUserName, $ToUserName, $message_info)
    {
        $content = "<xml>
<ToUserName><![CDATA[%s]]></ToUserName>
<FromUserName><![CDATA[%s]]></FromUserName>
<CreateTime>%s</CreateTime>
<MsgType><![CDATA[text]]></MsgType>
<Content><![CDATA[%s]]></Content>
</xml>";
        $resultStr = sprintf($content, $FromUserName, $ToUserName, time(), $message_info);
        echo $resultStr;
        exit;

    }

    /**
     * 回复图文消息
     * @param $FromUserName
     * @param $ToUserName
     * @param $message_info
     */
    function image_message($FromUserName, $ToUserName, $message_info)
    {
        $content = "<xml>
<ToUserName><![CDATA[%s]]></ToUserName>
<FromUserName><![CDATA[%s]]></FromUserName>
<CreateTime>%s</CreateTime>
<MsgType><![CDATA[image]]></MsgType>
<Image>
<MediaId><![CDATA[%s]]></MediaId>
</Image>
</xml>";
        $resultStr = sprintf($content, $FromUserName, $ToUserName, time(), $message_info);
        echo $resultStr;
        exit;
    }

    /**
     * 回复视频语音消息
     * @param $FromUserName
     * @param $ToUserName
     * @param $message_info
     */
    function voice_message($FromUserName, $ToUserName, $message_info)
    {
        $content = "<xml>
<ToUserName><![CDATA[%s]]></ToUserName>
<FromUserName><![CDATA[%s]]></FromUserName>
<CreateTime>%s</CreateTime>
<MsgType><![CDATA[voice]]></MsgType>
<Voice>
<MediaId><![CDATA[%s]]></MediaId>
</Voice>
</xml>";
        $resultStr = sprintf($content, $FromUserName, $ToUserName, time(), $message_info);
        echo $resultStr;
        exit;
    }

    /**
     * 回复图文消息
     * @param $FromUserName
     * @param $ToUserName
     * @param $message_info
     */
    function news_message($FromUserName, $ToUserName, $message_info)
    {
        $content = '<xml>
<ToUserName><![CDATA[%s]]></ToUserName>
<FromUserName><![CDATA[%s]]></FromUserName>
<CreateTime>%s</CreateTime>
<MsgType><![CDATA[news]]></MsgType>';
        $resultStr = sprintf($content, $FromUserName, $ToUserName, time());
        $resultStr .= '<ArticleCount>' . count($message_info) . '</ArticleCount><Articles>';
        foreach ($message_info as $value) {
            $resultStr .= '<item>
<Title><![CDATA[' . $value['title'] . ']]></Title>
<Description><![CDATA[' . $value['digest'] . ']]></Description>
<PicUrl><![CDATA[' . ltrim($value['image_url'], '.') . ']]></PicUrl>
<Url><![CDATA[' . $value['content_source_url'] . ']]></Url>
</item>';
        }
        $resultStr .= '</Articles></xml>';
        echo $resultStr;
        exit;
    }


    /**
     * 微信模板行业设置
     * @param $data 模板消息ID
     * @return array
     */
    function set_industry($data)
    {
        $access_token = $this->get_access_token();
        $url = 'https://api.weixin.qq.com/cgi-bin/template/api_set_industry?access_token=' . $access_token;
        $result = $this->send_request($url, $data, 'post');
        $result = json_decode($result, true);
        if ($result['errcode'] == 0)
            return array('code' => 1, 'msg' => '行业设置成功');
        return array('code' => 0, 'msg' => '行业设置失败');
    }

    /**
     * 获取微信公招行业信息
     * @return array
     */
    function get_industry()
    {
        $access_token = $this->get_access_token();
        $url = 'https://api.weixin.qq.com/cgi-bin/template/get_industry?access_token=' . $access_token;
        $result = $this->send_request($url);
        $result = json_decode($result, true);
        if ($result['errcode'] == 0)
            return array('code' => 1, 'msg' => '获取行业成功');
        return array('code' => 0, 'msg' => '获取行业失败');
    }


    /**
     * 获取行业模板信息
     * @return array
     */
    function get_private_template()
    {
        $access_token = $this->get_access_token();
        $url = 'https://api.weixin.qq.com/cgi-bin/template/get_all_private_template?access_token=' . $access_token;
        $result = $this->send_request($url);
        $result = json_decode($result, true);
        if ($result['errcode'] == 0)
            return array('code' => 1, 'msg' => '获取行业模板信息成功', $result);
        return array('code' => 0, 'msg' => '获取行业模板信息失败');
    }

    /**
     * 删除模板消息
     * @return array
     */
    function del_private_template($data)
    {
        $access_token = $this->get_access_token();
        $url = 'https://api.weixin.qq.com/cgi-bin/template/del_private_template?access_token=' . $access_token;
        $result = $this->send_request($url, $data, 'post');
        $result = json_decode($result, true);
        if ($result['errcode'] == 0)
            return array('code' => 1, 'msg' => '删除模板信息成功', $result);
        return array('code' => 0, 'msg' => '删除模板信息失败');
    }

    /**
     * 发送模板消息
     * @param $data
     * @return array
     */
    function send_template($data)
    {
        $access_token = $this->get_access_token();
        $url = 'https://api.weixin.qq.com/cgi-bin/template/del_private_template?access_token=' . $access_token;
        $result = $this->send_request($url, $data, 'post');
        $result = json_decode($result, true);
        if ($result['errcode'] == 0)
            return array('code' => 1, 'msg' => '模板信息发送成功', $result);
        return array('code' => 0, 'msg' => '模板信息发送失败');
    }


    /**
     * 上传图片素材到微信
     */
    function upload_wx($file, $type = 'image')
    {
        $filename = $file;
        $data['media'] = '@' . $filename;
        $access_token = $this->get_access_token();
        $url = "https://api.weixin.qq.com/cgi-bin/media/upload?access_token={$access_token}&type={$type}";
        $result = $this->curl_post_upload($url, $data);
        return $result;
    }

    /**
     * 上传永久素材到微信
     * @param $file
     * @param $filename
     * @param string $type
     * @return bool|mixed
     */
    function upload_img_wx($file, $filename, $type = 'image')
    {
        $img = pathinfo($filename);
        $imginfo = filesize($filename);
        $imginfo = $imginfo / 1000;
        $data['media'] = '@' . $filename;
        $data['form-data'] = array('filename' => $img['basename'], 'content-type' => $img['extension'], 'filelength' => $imginfo);
        $access_token = $this->get_access_token();
        $url = "https://api.weixin.qq.com/cgi-bin/material/add_material?access_token={$access_token}&type={$type}";
        $result = $this->curl_post_upload($url, $data);
        if ($result) {
            return $result;
        }
        return false;
    }

    /**
     * 上传微信logo
     * @param $file
     * @param $filename
     * @return bool|mixed
     */
    function upload_img_logo($file, $filename)
    {
        $img = pathinfo($filename);
        $imginfo = filesize($filename);
        $imginfo = $imginfo / 1000;
        $data['media'] = '@' . $filename;
        $data['form-data'] = array('filename' => $file, 'content-type' => $img['extension'], 'filelength' => $imginfo);
        $access_token = $this->get_access_token();
        $url = "https://api.weixin.qq.com/cgi-bin/media/uploadimg?access_token={$access_token}";
        $result = $this->curl_post_upload($url, $data);
        if ($result) {
            return $result;
        }
        return false;
    }

    /**
     * 下载文件
     * @param $media_id
     * @return mixed
     */
    function dow_media($media_id)
    {
        if (empty($media_id)) exit('请设置media_id');
        $access_token = $this->get_access_token();
        $url = "http://file.api.weixin.qq.com/cgi-bin/media/get?access_token={$access_token}&media_id={$media_id}";
        $result = $this->send_request($url);
        $result = json_decode($result, true);
        return $result;
    }

    /**
     * 上传永久素材
     * @param $file
     * @param string $type
     */
    function add_news($data)
    {
        $access_token = $this->get_access_token();
        $url = "https://api.weixin.qq.com/cgi-bin/material/add_news?access_token=" . $access_token;
        $result = $this->send_request($url, $data, 'post');
        $result = json_decode($result, true);
        return $result;
    }

    /**
     * 获取永久图文素材
     */
    function get_material($data)
    {
        $access_token = $this->get_access_token();
        $url = "https://api.weixin.qq.com/cgi-bin/material/get_material?access_token=" . $access_token;
        $result = $this->send_request($url, $data, 'post');
        $result = json_decode($result, true);
        return $result;
    }

    /**
     * 删除永久图文素材
     */
    function del_material($data)
    {
        $access_token = $this->get_access_token();
        $url = "https://api.weixin.qq.com/cgi-bin/material/del_material?access_token=" . $access_token;
        $result = $this->send_request($url, $data, 'post');
        $result = json_decode($result, true);
        return $result;
    }

    /**
     * 修改永久图文信息
     * @param $data
     * @return mixed
     */
    function update_news($data)
    {
        $access_token = $this->get_access_token();
        $url = "https://api.weixin.qq.com/cgi-bin/material/update_news?access_token=" . $access_token;
        $result = $this->send_request($url, $data, 'post');
        $result = json_decode($result, true);
        return $result;
    }

    /**
     * 获取素材总数
     * @return mixed
     */
    function get_materialcount()
    {
        $access_token = $this->get_access_token();
        $url = "https://api.weixin.qq.com/cgi-bin/material/get_materialcount?access_token=" . $access_token;
        $result = $this->send_request($url);
        $result = json_decode($result, true);
        return $result;
    }

    /**
     * 获取素材列表
     */
    function batchget_material($data)
    {
        $access_token = $this->get_access_token();
        $url = "https://api.weixin.qq.com/cgi-bin/material/batchget_material?access_token=" . $access_token;
        $result = $this->send_request($url, $data, 'post');
        $result = json_decode($result, true);
        return $result;
    }

    /**
     * 上传群发图文消息素材
     * @param $data
     * @return mixed
     */
    function uploadnews($data)
    {
        $access_token = $this->get_access_token();
        $url = 'https://api.weixin.qq.com/cgi-bin/media/uploadnews?access_token=' . $access_token;
        $result = $this->send_request($url, $data, 'post');
        $result = json_decode($result, true);
        return $result;
    }

    /**
     * 发送群发图文消息素材
     * @param $data
     * @return mixed
     */
    function sendall($data)
    {
        $access_token = $this->get_access_token();
        $url = 'https://api.weixin.qq.com/cgi-bin/message/mass/sendall?access_token=' . $access_token;
        $result = $this->send_request($url, $data, 'post');
        $result = json_decode($result, true);
        return $result;
    }

    /**
     * 根据open_id发送群发图文消息素材
     * @param $data
     * @return mixed
     */
    function send_open_all($data)
    {
        $access_token = $this->get_access_token();
        $url = 'https://api.weixin.qq.com/cgi-bin/message/mass/send?access_token=' . $access_token;
        $result = $this->send_request($url, $data, 'post');
        $result = json_decode($result, true);
        return $result;
    }

    /**
     * 群发预览接口
     * @param $data
     * @return mixed
     */
    function preview($data)
    {
        $access_token = $this->get_access_token();
        $url = 'https://api.weixin.qq.com/cgi-bin/message/mass/preview?access_token=' . $access_token;
        $result = $this->send_request($url, $data, 'post');
        $result = json_decode($result, true);
        return $result;
    }

    /**
     * 创建卡卷
     * @param $data
     * @return mixed
     */
    function create_card($data)
    {
        $access_token = $this->get_access_token();
        $url = 'https://api.weixin.qq.com/card/create?access_token=' . $access_token;
        $result = $this->send_request($url, $data, 'post');
        $result = json_decode($result, true);
        return $result;
    }

    /**
     * 获取卡卷信息
     * @param $data
     * @return mixed
     */
    function get_card($data)
    {
        $access_token = $this->get_access_token();
        $url = 'https://api.weixin.qq.com/card/code/get?access_token=' . $access_token;
        $result = $this->send_request($url, $data, 'post');
        $result = json_decode($result, true);
        return $result;
    }

    /**
     * 核销优惠劵
     * @param $data
     * @return mixed
     */
    function consume_card($data)
    {
        $access_token = $this->get_access_token();
        $url = 'https://api.weixin.qq.com/card/code/consume?access_token=' . $access_token;
        $result = $this->send_request($url, $data, 'post');
        $result = json_decode($result, true);
        return $result;
    }

    /**
     * 创建优惠劵code
     * @param $data
     * @return mixed
     */
    function createCardCode($data)
    {
        $access_token = $this->get_access_token();
        $url = 'https://api.weixin.qq.com/card/qrcode/create?access_token=' . $access_token;
        $result = $this->send_request($url, $data, 'post');
        $result = json_decode($result, true);
        return $result;
    }

    /**
     * 创建会员领取二维码
     * @param $data
     * @return mixed
     */
    function create_user_card($data)
    {
        $access_token = $this->get_access_token();
        $url = 'https://api.weixin.qq.com/card/qrcode/create?access_token=' . $access_token;
        $result = $this->send_request($url, $data, 'post');
        $result = json_decode($result, true);
        return $result;
    }

    /**
     * 创建会员卡
     */
    function create_user($data)
    {
        $access_token = $this->get_access_token();
        $url = 'https://api.weixin.qq.com/card/create?access_token=' . $access_token;
        $result = $this->send_request($url, $data, 'post');
        $result = json_decode($result, true);
        return $result;
    }

    /**
     * 修改会员卡
     * @param $data
     * @return mixed
     */
    function update_card($data)
    {
        $access_token = $this->get_access_token();
        $url = 'https://api.weixin.qq.com/card/update?access_token=' . $access_token;
        $result = $this->send_request($url, $data, 'post');
        $result = json_decode($result, true);
        return $result;
    }

    /**
     * 更新会员的会员卡余额积分等信息
     * @param $data
     * @return mixed
     */
    function updateuser($data)
    {
        $access_token = $this->get_access_token();
        $url = 'https://api.weixin.qq.com/card/membercard/updateuser?access_token=' . $access_token;
        $result = $this->send_request($url, $data, 'post');
        $result = json_decode($result, true);
        return $result;
    }

    /**
     * 根据会员卡号获取会员信息（验证当前会员卡是否已经领取）
     * @param $card_id
     * @param $code
     * @return mixed
     */
    function getCodeUser($card_id, $code)
    {
        $data = array('code' => $code, 'card_id' => $card_id, 'check_consume' => false);
//        $data=array('code'=>'WX1707180002037','card_id'=>$card_id,'check_consume'=>false);
        $access_token = $this->get_access_token();
        $url = 'https://api.weixin.qq.com/card/code/get?access_token=' . $access_token;
        $result = $this->send_request($url, $data, 'post');
        $result = json_decode($result, true);
        return $result;
    }

    function getWxCodeUser($openid, $card_id)
    {
        $data = array('openid' => $openid, 'card_id' => $card_id);
//        $data=array('code'=>'WX1707180002037','card_id'=>$card_id,'check_consume'=>false);
        $access_token = $this->get_access_token();
        $url = 'https://api.weixin.qq.com/card/user/getcardlist?access_token=' . $access_token;
        $result = $this->send_request($url, $data, 'post');
        $result = json_decode($result, true);
        return $result;
    }

    function getCardList()
    {

    }

    /**
     * 激活微信会员卡
     * @param $card_id 微信会员卡ID
     * @param $code 用户会员卡卡号
     * @param string $membership_number 显示卡号
     * @param string $init_balance 初始余额
     * @param string $init_bonus_record 积分同步说明
     * @param string $init_bonus 初始积分
     * @param string $background_pic_url 背景图
     * @return array
     */
    function cardActivate($card_id, $code, $membership_number = false, $init_balance = '', $init_bonus_record = '', $init_bonus = '', $background_pic_url = '')
    {
        $data = array_filter(compact('card_id', 'code', 'init_balance', 'init_bonus_record', 'init_bonus', 'background_pic_url'));
        if ($membership_number !== false) $data['membership_number'] = $membership_number;
        if (count($data) < 3) return array('errcode' => '100', 'errmsg' => '参数错误');
        $access_token = $this->get_access_token();
        $url = 'https://api.weixin.qq.com/card/membercard/activate?access_token=' . $access_token;
        $result = $this->send_request($url, $data, 'post');
        $result = json_decode($result, true);
        return $result;

    }

    /**
     * code 解码
     * @param $encrypt_code 加密code
     * @return mixed
     */
    function decryptCode($encrypt_code)
    {
        $access_token = $this->get_access_token();
        $url = 'https://api.weixin.qq.com/card/code/decrypt?access_token=' . $access_token;
        $result = $this->send_request($url, array('encrypt_code' => $encrypt_code), 'post');
        $result = json_decode($result, true);
        return $result;
    }

    /**
     * 修改会员卡卡号
     * TODO::会员卡类型限制不能修改
     * @param $card_id
     * @param $code
     * @param $new_code
     * @return mixed
     */
    function updateUserCode($card_id, $code, $new_code)
    {
        $data = array('code' => $code, 'card_id' => $card_id, 'new_code' => $new_code);
        $access_token = $this->get_access_token();
        $url = 'https://api.weixin.qq.com/card/code/update?access_token=' . $access_token;
        $result = $this->send_request($url, $data, 'post');
        $result = json_decode($result, true);
        return $result;
    }


    /**
     * 删除会员拥有的会员卡
     * @param $card_id 微信会员卡ID
     * @param $code 会员卡编号
     * @return mixed
     */
    function delUserCard($card_id, $code)
    {
        $access_token = $this->get_access_token();
        $url = 'https://api.weixin.qq.com/card/code/unavailable?access_token=' . $access_token;
        $result = $this->send_request($url, array('code' => $code, 'card_id' => $card_id), 'post');
        $result = json_decode($result, true);
        return $result;
    }


    /**
     * 删除微信会员卡
     * @param $card_id 微信会员卡ID
     * @return mixed
     */
    function delCard($card_id)
    {
        $access_token = $this->get_access_token();
        $url = 'https://api.weixin.qq.com/card/delete?access_token=' . $access_token;
        $result = $this->send_request($url, array('card_id' => $card_id), 'post');
        $result = json_decode($result, true);
        return $result;
    }

    /**
     * 获取会员信息
     * @param $openid
     * @return mixed
     */
    function get_user_info($openid)
    {
        $access_token = $this->get_access_token();
        $url = "https://api.weixin.qq.com/cgi-bin/user/info?access_token={$access_token}&openid={$openid}&lang=zh_CN";
        $result = $this->send_request($url);
        $result = json_decode($result, true);
        return $result;
    }

    /**
     * 获取微信会员openid列表
     * @param $end_openid 结束openid
     */
    function getWxUserOpenIdList($end_openid=''){
        $access_token = $this->get_access_token();
        $url = "https://api.weixin.qq.com/cgi-bin/user/get?access_token={$access_token}&next_openid={$end_openid}";
        $result = $this->send_request($url);
        $result = json_decode($result, true);
        return $result;
    }

    function getWxUserList($data){
        $access_token = $this->get_access_token();
        $url = "https://api.weixin.qq.com/cgi-bin/user/info/batchget?access_token={$access_token}";
        $result = $this->send_request($url, array('user_list'=>$data), 'post');
        $result = json_decode($result, true);
        return $result;
    }

    /**
     * 批量添加用户标签
     * @param $openid_list
     * @param $tagid
     * @return mixed
     */
    function userBatchtagging($openid_list,$tagid){
        $access_token = $this->get_access_token();
        $url = "https://api.weixin.qq.com/cgi-bin/tags/members/batchtagging?access_token={$access_token}";
        $result = $this->send_request($url, array('openid_list'=>$openid_list,'tagid'=>$tagid), 'post');
        $result = json_decode($result, true);
        return $result;
    }

    /**
     * 批量取消用户标签
     * @param $openid_list
     * @param $tagid
     * @return mixed
     */
    function userBatchuntagging($openid_list,$tagid){
        $access_token = $this->get_access_token();
        $url = "https://api.weixin.qq.com/cgi-bin/tags/members/batchuntagging?access_token={$access_token}";
        $result = $this->send_request($url, array('openid_list'=>$openid_list,'tagid'=>$tagid), 'post');
        $result = json_decode($result, true);
        return $result;
    }
    /**
     * 创建标签
     * @param $name
     * @return mixed
     */
    function createTags($name)
    {
        $access_token = $this->get_access_token();
        $url = "https://api.weixin.qq.com/cgi-bin/tags/create?access_token={$access_token}";
        $result = $this->send_request($url, array('tag'=>array('name' => $name)), 'post');
        $result = json_decode($result, true);
        return $result;
    }

    /**
     * 修改标签
     * @param $name
     * @param $wx_id
     * @return mixed
     */
    function updateTags($name,$wx_id){
        $access_token = $this->get_access_token();
        $url = "https://api.weixin.qq.com/cgi-bin/tags/update?access_token={$access_token}";
        $result = $this->send_request($url, array('tag'=>array('id'=>$wx_id,'name' => $name)), 'post');
        $result = json_decode($result, true);
        return $result;
    }

    /**
     * 获取线上标签
     * @return mixed
     */
    function getAllTags(){
        $access_token = $this->get_access_token();
        $url = "https://api.weixin.qq.com/cgi-bin/tags/get?access_token={$access_token}";
        $result = $this->send_request($url);
        $result = json_decode($result, true);
        return $result;
    }

    /**
     * 删除标签
     * @param $wx_id
     * @return mixed
     */
    function delTags($wx_id){
        $access_token = $this->get_access_token();
        $url = "https://api.weixin.qq.com/cgi-bin/tags/delete?access_token={$access_token}";
        $result = $this->send_request($url, array('tag'=>array('id'=>$wx_id)), 'post');
        $result = json_decode($result, true);
        return $result;
    }

    /**
     * 获取微信店铺列表
     * @param int $begin
     * @return mixed
     */
    function getShopList($begin = 0)
    {
        $data = array('begin' => $begin * 50, 'limit' => 50);
        $access_token = $this->get_access_token();
        $url = 'https://api.weixin.qq.com/cgi-bin/poi/getpoilist?access_token=' . $access_token;
        $result = $this->send_request($url, $data, 'post');
        $result = json_decode($result, true);
        return $result;
    }

    /**
     * 获取店铺分类描述
     * @return mixed
     */
    function getShopWxcategory()
    {
        $access_token = $this->get_access_token();
        $url = 'http://api.weixin.qq.com/cgi-bin/poi/getwxcategory?access_token=' . $access_token;
        $result = $this->send_request($url, '', 'get');
        $result = json_decode($result, true);
        return $result;
    }

    /**
     * 添加门店信息
     * @param $data
     * @return mixed
     */
    function addShop($data)
    {
        $data['offset_type'] = 1;
        $info = array('business' => array('base_info' => array_filter($data)));
//        echo json_encode($info, JSON_UNESCAPED_UNICODE);exit;
        $access_token = $this->get_access_token();
        $url = 'http://api.weixin.qq.com/cgi-bin/poi/addpoi?access_token=' . $access_token;
        $result = $this->send_request($url, $info, 'post');
        $result = json_decode($result, true);
        return $result;
    }

    /**
     * 修改门店信息
     * @param $data
     * @return mixed
     */
    function updateShop($data)
    {
        $info = array('business' => array('base_info' => $data));
        $access_token = $this->get_access_token();
        $url = 'https://api.weixin.qq.com/cgi-bin/poi/updatepoi?access_token=' . $access_token;
        $result = $this->send_request($url, $info, 'post');
        $result = json_decode($result, true);
        return $result;
    }

    /**
     * 删除店铺
     * @param $data 微信店铺id
     */
    function delShop($data)
    {
        $access_token = $this->get_access_token();
        $url = 'https://api.weixin.qq.com/cgi-bin/poi/delpoi?access_token=' . $access_token;
        $result = $this->send_request($url, $data, 'post');
        $result = json_decode($result, true);
        return $result;
    }

    /**
     * 获取微信用户新增数量
     * @param $data
     * @return mixed
     */
    function getWxUserSummary($data)
    {
        $access_token = $this->get_access_token();
        $url = 'https://api.weixin.qq.com/datacube/getusersummary?access_token=' . $access_token;
        $result = $this->send_request($url, $data, 'post');
        $result = json_decode($result, true);
        return $result;
    }

    /**
     * 获取微信总人数
     * @param $data
     * @return mixed
     */
    function getWxUserCumulate($data)
    {
        $access_token = $this->get_access_token();
        $url = 'https://api.weixin.qq.com/datacube/getusercumulate?access_token=' . $access_token;
        $result = $this->send_request($url, $data, 'post');
        $result = json_decode($result, true);
        return $result;
    }


    /**
     * 网页授权
     * @return mixed
     */
    public function authorize()
    {
        if (!isset($_GET['code'])) {
            $redirect_url =  'http://' . $_SERVER['HTTP_HOST'] . $_SERVER['REQUEST_URI'];
            $url = "https://open.weixin.qq.com/connect/oauth2/authorize?appid={$this->appid}&redirect_uri={$redirect_url}&response_type=code&scope=snsapi_userinfo&state=STATE#wechat_redirect";
            redirect($url);
        } else {
            $url = "https://api.weixin.qq.com/sns/oauth2/access_token?appid={$this->appid}&secret={$this->appsecret}&code={$_GET['code']}&grant_type=authorization_code";
            $result = $this->send_request($url);
            $result = substr($result, mb_strpos($result, '{'));
            $result = json_decode($result, true);
            if (isset($result['errcode'])) {
                return false;
            } else {
                $openid = $result['openid'];
                $access_token = $result['access_token'];
                $url = "https://api.weixin.qq.com/sns/oauth2/refresh_token?appid={$this->appid}&grant_type=refresh_token&refresh_token={$result['refresh_token']} ";
                $result = $this->send_request($url);
                $result = json_decode($result, true);
                if (isset($result['access_token'])) {
                    $access_token = $result['access_token'];
                    $openid = $result['openid'];
                }
                $url = "https://api.weixin.qq.com/sns/userinfo?access_token={$access_token}&openid={$openid}&lang=zh_CN";
                $result = $this->send_request($url);
                $result = json_decode($result, true);
                if (isset($result['errcode'])) {
                    exit('用户信息拉取失败');
                }
                return $result;
            }
        }

    }


    /**
     * 获取微信access_token
     * @return mixed
     */
    private function get_access_token()
    {
        $data = \think\Cache::get($this->appid .'weixin_access_token');
        //不存在access_token或者access_token超时则重新获取。
        if (empty($data) || ($data['expires_in'] < time())) {
            $url = 'https://api.weixin.qq.com/cgi-bin/token?grant_type=client_credential&appid=' . trim($this->appid) . '&secret=' . trim($this->appsecret);
            $data = $this->send_request($url);
            $data = json_decode($data, true);
            if ($data && !empty($data['access_token'])) {
                $bey = \think\Cache::set($this->appid . 'weixin_access_token', array('access_token' => $data['access_token'], 'expires_in' => time() + 7000), 7000);
                if ($bey > 0) {
                    return $data['access_token'];
                }
            }
        }
//        if(empty($data['access_token']))$CI->error('获取微信access_token失败，请检测相关配置信息');
        $url = 'https://api.weixin.qq.com/cgi-bin/getcallbackip?access_token=' . $data['access_token'];
        $resutl = $this->send_request($url);
        $resutl = json_decode($resutl, true);
        if (isset($resutl['errcode'])) {
            if ($resutl['errcode'] == '40001' || $resutl['errcode'] == '40014' || $resutl['errcode'] == '42001') {
                $url = 'https://api.weixin.qq.com/cgi-bin/token?grant_type=client_credential&appid=' . $this->appid . '&secret=' . $this->appsecret;
                $data = $this->send_request($url);
                $data = json_decode($data, true);
                if ($data && !empty($data['access_token'])) {
                    \think\Cache::set($this->appid . 'weixin_access_token', array('access_token' => $data['access_token'], 'expires_in' => time() + 7000), 7000);
                    return $data['access_token'];
                }
                return false;
            }
            return false;
        }
        return $data['access_token'];
    }

    /**
     * 微信url请求
     * @param $url
     * @param string $data
     * @param string $type
     * @return mixed
     */
    private function send_request($url, $data = '', $type = 'get')
    {
        if ($type == 'get' && !empty($data)) {
            $url .= '?' . http_build_query($data);
        }
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url); //设置访问路径
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, TRUE); //设置可以返回字符串
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
        curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, false);
        $head = array('User-Agent:Mozilla/5.0 (Windows NT 6.1; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/51.0.2704.103 Safari/537.36');
        curl_setopt($ch, CURLOPT_HTTPHEADER, $head);
        if ($type == 'post') {
            curl_setopt($ch, CURLOPT_POST, TRUE);//post请求
            $data = json_encode($data, JSON_UNESCAPED_UNICODE);
            curl_setopt($ch, CURLOPT_POSTFIELDS, $data);//设置传递的参数
        }
        $request = curl_exec($ch);
        curl_close($ch);
        return $request;
    }

    /**
     * 上传素材
     * @param $url
     * @param string $data 素材内容
     * @return mixed
     */
    private function curl_post_upload($url, $data = '')
    {
        //创建一个新cURL资源
        $curl = curl_init();
        //设置URL和相应的选项
        curl_setopt($curl, CURLOPT_URL, $url);
        curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, FALSE);
        curl_setopt($curl, CURLOPT_SSL_VERIFYHOST, false);
        if (!empty($data)) {
            curl_setopt($curl, CURLOPT_POST, 1);
            @curl_setopt($curl, CURLOPT_POSTFIELDS, $data);
        }
        curl_setopt($curl, CURLOPT_RETURNTRANSFER, 1);
        //执行curl，抓取URL并把它传递给浏览器
        $output = curl_exec($curl);
        //关闭cURL资源，并且释放系统资源
        curl_close($curl);
        $result = json_decode($output, true);
        return $result;
    }

    /**
     * 获取js 凭证
     * @param int $type 1.普通js调用凭证，2.卡卷js调用凭证
     * @param int $type
     * @param string $card_id
     * @param string $code
     * @param string $openid
     * @return mixed
     */
    function get_js_config($type = 1, $card_id = '', $code = '', $openid = '')//http://res.wx.qq.com/open/js/jweixin-1.2.0.js
    {
        $hash = '';
        $chars = 'ABCDEFGHIJKMNPQRSTUVWXYZabcdefghijkmnpqrstuvwxyz';
        $max = strlen($chars) - 1;
        for ($i = 0; $i < 16; $i++) {
            $hash .= $chars[mt_rand(0, $max)];
        }
        if ($type == 1) {
            $data['jsapi_ticket'] = $this->get_js_ticket(1);
            if (empty($data['jsapi_ticket'])) $data['jsapi_ticket'] = $this->get_js_ticket(1);
            $data['noncestr'] = $hash;
            $data['timestamp'] = time();
            $data['url'] = 'http://' . $_SERVER['HTTP_HOST'] . $_SERVER['REQUEST_URI'];
            ksort($data);
            $str = '';
            foreach ($data as $key => $val) {
                $str .= '&';
                $str .= $key . '=' . $val;
            }
            $str = ltrim($str, '&');
            $data['signature'] = sha1($str);
        } else {
            $data['api_ticket'] = $this->get_js_ticket(2);
            $data['timestamp'] = time();
            $data['card_id'] = $card_id;
            $data['code'] = $code;
            $data['nonce_str'] = $hash;
            $data['openid'] = $openid;
            $str_arr = $data;
            sort($str_arr, SORT_STRING);
            $data['signature'] = sha1(implode($str_arr));
        }

        $data['app_id'] = $this->appid;
        return $data;
    }

    /**
     * 获取Js调用凭证
     * @param int $type 1.普通js调用凭证，2.卡卷js调用凭证
     * @return bool
     */
    function get_js_ticket($type = 1)
    {
        $data =  \think\Cache::get($this->appid . ($type == 1 ? "jsapi" : "wx_card") . 'js_ticket');//file_get_contents($this->js_ticket);
        if (empty($data['endtime']) || ($data['endtime'] > time())) {
            $access_token = $this->get_access_token();
            $url = "https://api.weixin.qq.com/cgi-bin/ticket/getticket?access_token={$access_token}&type=" . ($type == 1 ? "jsapi" : "wx_card");
            $result = $this->send_request($url);
            $results = json_decode($result, true);
            if ($results['errcode'] == 0) {
                \think\Cache::set($this->appid . ($type == 1 ? "jsapi" : "wx_card") . 'js_ticket', array('access_token' => $results['ticket'], 'endtime' => time() + 7000), 7000);
                return $results['ticket'];
            }
            return false;
        } else {
            return $data['access_token'];
        }
    }

}