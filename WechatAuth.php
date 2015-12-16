<?php namespace App\Api;
/**
 * @author kangyu
 * @package weixin auth2
 * @description 微信auth2授权
 */
class WechatAuth{

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
	 * [__construct description]
	 * @param [type] $app_id      [description]
	 * @param [type] $app_secret  [description]
	 * @param [type] $user_detail [description]
	 */
	public function __construct($app_id, $app_secret)
	{
		$this->app_id = $app_id;
		$this->app_secret = $app_secret;
	}

	/**
	 * [has_userdetail 判断是否有存储微信用户信息Model]
	 * @return boolean [有Model返回true，否则返回false]
	 */
	// private function has_user_detail()
	// {
	// 	if ($this->userdetail) {
	// 		return true;
	// 	} else {
	// 		return false;
	// 	}
	// }

	/**
	 * [redirect_url_generate 产生微信授权链接]
	 * @param  [type] $redirect_uri [跳转地址]
	 * @param  string $scope        [授权作用域 默认为snsapi_userinfo 可选snsapi_base]
	 * @return [type]               [微信授权链接]
	 */
	public function redirect_url_generate($redirect_uri, $scope='snsapi_userinfo')
	{
		$url = "https://open.weixin.qq.com/connect/oauth2/authorize?appid=$this->app_id&redirect_uri=$redirect_uri&response_type=code&scope=$scope&state=imaginiha#wechat_redirect";
		return $url;
	}

	/**
	 * [get_detail 获取用户详细信息]
	 * @param  Request $request [description]
	 * @return [type]           [description]
	 */
	public function get_detail($code)
	{
		
		// $code = $request->input('code');//取传入的参数

		$res = $this->getWebJson($code);//获取网页调用access_token,openid所在json对象

		$web_access_token = $res->access_token;//获取网页授权access_token
		$openid = $res->openid;//获取openid

		// $request->session()->put('openid',$openid);//openid存入session
		 
		// session(['openid' => $openid]);//openid存入session

		$url = "https://api.weixin.qq.com/sns/userinfo?access_token=$web_access_token&openid=$openid&lang=zh_CN";//通过网页授权access_token和openid请求用户信息url
		$detail_json = json_decode($this->httpGet($url));//获取用户信息并且decode为json对象


		return $detail_json;
	}

	/**
	 * curl请求函数
	 * @param  [type] $url [description]
	 * @return [type]      [description]
	 */
	private function httpGet($url)
	{
		$curl = curl_init();

		curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
		curl_setopt($curl, CURLOPT_URL, $url);
		// curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, false);
		//curl_setopt($curl, CURLOPT_SSL_VERIFYHOST, false);

		$res = curl_exec($curl);
		curl_close($curl);

		return $res;
	}

	/**
	 * 获取网页调用access_token,openid所在json
	 */
	public function getWebJson($code)
	{
		$url = "https://api.weixin.qq.com/sns/oauth2/access_token?appid=$this->app_id&secret=$this->app_secret&code=$code&grant_type=authorization_code";

		$res = json_decode($this->httpGet($url));

		return $res;
	}
}
