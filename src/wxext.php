<?php

/*
 * 微信缓存控制器
 * weiyeying
 * 2016、1.15
 */

namespace wyy\wxext;

use yii;

class Wxcache {

    public $appid;
    public $apppwd;
    public $dir;
    private $cache_time = 3600; //缓存时间3600秒
    private $token = 'token.txt';
    private $ticket = 'tiket.txt';

    /**
     * 系统初始化
     * 
     * * */
    public function __construct($appid, $apppwd) {
        $this->appid = $appid;
        $this->apppwd = $apppwd;
        $dirs = dirname(Yii::$app->BasePath);
        $this->dir = $dirs . "/common/ext/";
    }

    /*     * *
     * $url 获取地址
     * $file 文件名称
     * $pro 接口返回参数名称
     * 获取缓存文件参数
     * * */

    private function set_chace($url, $file, $pro) {
        $arr = array();
        //不存在缓存情况处理
        if (!is_file($this->dir . $file)) {
            $url = $url;
            $data = file_get_contents($url);
            $wxtoken = json_decode($data, true);
            $arr['id'] = $wxtoken[$pro];
            $arr['ext_time'] = time();
            file_put_contents($this->dir . $file, json_encode($arr));
        }

        //缓存超时处理
        $cache = file_get_contents($this->dir . $file);
        $token = json_decode($cache, true);
        if (time() - $token['ext_time'] > $this->cache_time) {
            $url = $url;
            $data = file_get_contents($url);
            $wxtoken = json_decode($data, true);
            $arr['id'] = $wxtoken[$pro];
            $arr['ext_time'] = time();
            $token['id'] = $wxtoken[$pro];
            file_put_contents($this->dir . $file, json_encode($arr));
        }

        return $token['id'];
    }

    //获取token
    public function get_access_token() {
        $url = "https://api.weixin.qq.com/cgi-bin/token?grant_type=client_credential&appid=$this->appid&secret=$this->apppwd";
        $file = $this->token;
        $pro = 'access_token';
        $r = $this->set_chace($url, $file, $pro);
        return $r;
    }

    //获取tiket
    public function get_jsapi_ticket() {
        $token = $this->get_access_token();
        $url = "https://api.weixin.qq.com/cgi-bin/ticket/getticket?access_token=$token&type=jsapi";
        $file = $this->ticket;
        $pro = 'ticket';
        return $this->set_chace($url, $file, $pro);
    }

    /*     * ****
     * 获取js_sdk
     * **** */

    public function getSignPackage() {
        $jsapiTicket = $this->get_jsapi_ticket();

        // 注意 URL 一定要动态获取，不能 hardcode.
        $protocol = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off' || $_SERVER['SERVER_PORT'] == 443) ? "https://" : "http://";
        $url = "$protocol$_SERVER[HTTP_HOST]$_SERVER[REQUEST_URI]";

        $timestamp = time();
        $nonceStr = $this->createNonceStr();

        // 这里参数的顺序要按照 key 值 ASCII 码升序排序
        $string = "jsapi_ticket=$jsapiTicket&noncestr=$nonceStr&timestamp=$timestamp&url=$url";

        $signature = sha1($string);

        $signPackage = array(
            "appId" => $this->appid,
            "nonceStr" => $nonceStr,
            "timestamp" => $timestamp,
            "url" => $url,
            "signature" => $signature,
            "rawString" => $string
        );
        return $signPackage;
    }

    //jssdk获取随机验证码
    private function createNonceStr($length = 16) {
        $chars = "abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789";
        $str = "";
        for ($i = 0; $i < $length; $i++) {
            $str .= substr($chars, mt_rand(0, strlen($chars) - 1), 1);
        }
        return $str;
    }

    //返回option wecate掉用
    public static function return_option() {
        $options = array(
            'appid' => '111', //填写高级调用功能的app id
            'appsecret' => '222', //填写高级调用功能的密钥
        );
        return $options;
    }

   

    private function http_post($url, $param, $post_file = false) {
        $oCurl = curl_init();
        if (stripos($url, "https://") !== FALSE) {
            curl_setopt($oCurl, CURLOPT_SSL_VERIFYPEER, FALSE);
            curl_setopt($oCurl, CURLOPT_SSL_VERIFYHOST, false);
            curl_setopt($oCurl, CURLOPT_SSLVERSION, 1); //CURL_SSLVERSION_TLSv1
        }
        if (is_string($param) || $post_file) {
            $strPOST = $param;
        } else {
            $aPOST = array();
            foreach ($param as $key => $val) {
                $aPOST[] = $key . "=" . urlencode($val);
            }
            $strPOST = join("&", $aPOST);
        }
        curl_setopt($oCurl, CURLOPT_URL, $url);
        curl_setopt($oCurl, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($oCurl, CURLOPT_POST, true);
        curl_setopt($oCurl, CURLOPT_POSTFIELDS, $strPOST);
        $sContent = curl_exec($oCurl);
        $aStatus = curl_getinfo($oCurl);
        curl_close($oCurl);
        if (intval($aStatus["http_code"]) == 200) {
            return $sContent;
        } else {
            return false;
        }
    }

}
