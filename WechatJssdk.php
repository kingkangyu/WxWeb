<?php namespace App\Api;
/**
 * @author kangyu
 * @package weixin jssdk
 * @description 微信jssdk签名接口，需要PHP开启memcached
 */
//memcaced
use memcached;

//Redis不方便 没有过期时间
class WechatJssdk {
  /**
   * [$app_id 微信公众号AppID]
   * @var [string]
   */
  private $app_id;

  /**
   * [$app_secret 微信公众号AppSecret]
   * @var [string]
   */
  private $app_secret;

  /**
   * [$pre 缓存键值前缀]
   * @var [string]
   */
  private $pre;

  public function __construct($appId, $appSecret) {
    $this->appId = $appId;
    $this->appSecret = $appSecret;
    $this->pre = $appId;
  }
  /**
   * [getSignPackage 获取jssdk签名]
   * @return [array] [签名数组]
   */
  public function getSignPackage() {
    $jsapiTicket = $this->getJsApiTicket();

    // 注意 URL 一定要动态获取，不能 hardcode.
    $protocol = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off' || $_SERVER['SERVER_PORT'] == 443) ? "https://" : "http://";
    $url = "$protocol$_SERVER[HTTP_HOST]$_SERVER[REQUEST_URI]";

    $timestamp = time();
    $nonceStr = $this->createNonceStr();

    // 这里参数的顺序要按照 key 值 ASCII 码升序排序
    $string = "jsapi_ticket=$jsapiTicket&noncestr=$nonceStr"."&timestamp=".$timestamp."&url=$url";
    
    $signature = sha1($string);

    $signPackage = array(
      "appId"     => $this->appId,
      "nonceStr"  => $nonceStr,
      "timestamp" => $timestamp,
      "url"       => $url,
      "signature" => $signature,
      "rawString" => $string
    );
    return $signPackage; 
  }

  /**
   * [createNonceStr 生成长度为16的随机字符串]
   * @param  integer $length [description]
   * @return [array]          [长度为16随机字符串]
   */
  private function createNonceStr($length = 16) {
    $chars = "abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789";
    $str = "";
    for ($i = 0; $i < $length; $i++) {
      $str .= substr($chars, mt_rand(0, strlen($chars) - 1), 1);
    }
    return $str;
  }

  /**
   * [getJsApiTicket 生成jssdk的jsapi_ticket]
   * @return [type] [description]
   */
  private function getJsApiTicket() {
    // jsapi_ticket 应该全局存储与更新
    $pre = $this->pre;
    /////////////////////////////////////////////////////////////////////
    // memcache                                                        //
    // $mem = new Memcache;                                            //
    // $mem->connect('localhost', 11211) or die ("Could not connect"); //
    /////////////////////////////////////////////////////////////////////
    
    // memcached                        
    $mem = new memcached('jssdk');      
    $mem->addServer('localhost',11211);

    //memcache and memcached
    $ticket = $mem->get($pre.'ticket');

    if (empty($ticket)) {
      $accessToken = $this->getAccessToken();
      // 如果是企业号用以下 URL 获取 ticket
      // $url = "https://qyapi.weixin.qq.com/cgi-bin/get_jsapi_ticket?access_token=$accessToken";
      $url = "https://api.weixin.qq.com/cgi-bin/ticket/getticket?type=jsapi&access_token=$accessToken";
      $res = json_decode($this->httpGet($url));
      $ticket = $res->ticket;
      if ($ticket) {
        /////////////////////////////////////////////////
        // memcache                                    //
        // $mem->set($pre.'ticket', $ticket, 0, 7000); //
        /////////////////////////////////////////////////
        
        //memcahed 
        $mem->set($pre.'ticket', $ticket, time()+7000);

      }
    }
    return $ticket;
  }

  /**
   * [getAccessToken 获取access_token,并对access_token进行缓存]
   * @return [array] [access_token]
   */
  public function getAccessToken() {
    // access_token 应该全局存储与更新，以下代码以写入到文件中做示例
    $pre = $this->pre;
    /////////////////////////////////////////////////////////////////////
    //memcache                                                         //
    // $mem = new Memcache;                                            //
    // $mem->connect('localhost', 11211) or die ("Could not connect"); //
    /////////////////////////////////////////////////////////////////////
    
    ///////////////
    // memcached //
    ///////////////
    $mem = new memcached('jssdk');
    $mem->addServer('localhost',11211);

    $access_token = $mem->get($pre.'access_token');
    if (empty($access_token)) {
      // 如果是企业号用以下URL获取access_token
      // $url = "https://qyapi.weixin.qq.com/cgi-bin/gettoken?corpid=$this->appId&corpsecret=$this->appSecret";
      $url = "https://api.weixin.qq.com/cgi-bin/token?grant_type=client_credential&appid=$this->appId&secret=$this->appSecret";
      $res = json_decode($this->httpGet($url));
      $access_token = $res->access_token;
      // var_dump($access_token);
      if($access_token){
        ////////////////////////////////////////////////////////////
        // memcahe                                                //
        // $mem->set($pre.'access_token', $access_token, 0,7000); //
        ////////////////////////////////////////////////////////////

        //////////////
        // memcahed //
        //////////////
        $mem->set($pre.'access_token', $access_token, time()+7000);
      }  
    }
    return $access_token;
  }

  public function httpGet($url) {
    $curl = curl_init();
    curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($curl, CURLOPT_TIMEOUT, 500);
    curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, false);
    curl_setopt($curl, CURLOPT_SSL_VERIFYHOST, false);
    curl_setopt($curl, CURLOPT_URL, $url);

    $res = curl_exec($curl);
    curl_close($curl);

    return $res;
  }
  public function httpPost($url,$data = null){
    $curl = curl_init();
    curl_setopt($curl, CURLOPT_URL, $url);
    curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, FALSE);
    curl_setopt($curl, CURLOPT_SSL_VERIFYHOST, FALSE);
    if (!empty($data)){
        curl_setopt($curl, CURLOPT_POST, 1);
        curl_setopt($curl, CURLOPT_POSTFIELDS, $data);
    }
    curl_setopt($curl, CURLOPT_RETURNTRANSFER, 1);
    $output = curl_exec($curl);
    curl_close($curl);
    return $output;
  }
}

