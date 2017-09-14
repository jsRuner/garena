<?php
/**
 * Created by JetBrains PhpStorm.
 * User: 吴文付 hiwower@gmail.com
 * Date: 2017/9/1
 * Time: 上午10:24
 * description: garena的账号注册机。
 */

///////////////////////////////////////////////////////////////////
//                            _ooOoo_                             //
//                           o8888888o                            //
//                           88" . "88                            //
//                           (| ^_^ |)                            //
//                           O\  =  /O                            //
//                        ____/`---'\____                         //
//                      .'  \\|     |//  `.                       //
//                     /  \\|||  :  |||//  \                      //
//                    /  _||||| -:- |||||-  \                     //
//                    |   | \\\  -  /// |   |                     //
//                    | \_|  ''\---/''  |   |                     //
//                    \  .-\__  `-`  ___/-. /                     //
//                  ___`. .'  /--.--\  `. . ___                   //
//                ."" '<  `.___\_<|>_/___.'  >'"".                //
//              | | :  `- \`.;`\ _ /`;.`/ - ` : | |               //
//              \  \ `-.   \_ __\ /__ _/   .-` /  /               //
//        ========`-.____`-.___\_____/___.-`____.-'========       //
//                             `=---='                            //
//        ^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^      //
//                佛祖保佑       永无BUG        永不修改              //
////////////////////////////////////////////////////////////////////

require 'vendor/autoload.php';
require 'account.php';
use GuzzleHttp\Client;

const API_REGISTER_CHECK = "https://sso.garena.com/api/register/check";
const CAPTCHA_URL = "https://captcha.garena.com/image";
const API_RUOKUAI = "http://api.ruokuai.com/create.json";
const API_REGISTER = "https://sso.garena.com/api/register";
const API_PRELOGIN = "https://sso.garena.com/api/prelogin";
const API_PASSWD = "http://666coder.com:8888";
const API_LOGIN = "https://sso.garena.com/api/login";
const API_ACCOUNT_INIT = "https://account.garena.com/api/account/init";
const API_ACCOUNT_VERIFY_EMAIL_INIT = "https://account.garena.com/api/account/verify_email/init";
const API_ACCOUNT_VERIFY_EMAIL_APPLY = "https://account.garena.com/api/account/verify_email/apply";


const API_FETCH_IPS = "http://ttvp.mimidaili.com/ip/";

const API_RUOKUAI_ERROR = "http://api.ruokuai.com/reporterror.json";

class Register_machinse{
    private $account;
    private $db;
    private $http_client;
    private $debug;

    private $login_flag; //是否注册ok。
    private $send_flag;//是否发送成功。

    private $proxy;

    private  $captcha_id; //验证码的题目id


    function __construct()
    {
        $this->account = new Account();
        $this->db =  new \Workerman\MySQL\Connection(
            "rm-uf6m0ljyp35io68r5o.mysql.rds.aliyuncs.com",
            "3306", "garena",
            "bynxe0YET27QBDlK",
            "garena");

        $headers =[
            'User-Agent'=>'Mozilla/5.0 (Macintosh; Intel Mac OS X 10_12_5) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/60.0.3112.101 Safari/537.36'
        ];

//        $this->fetch_ip();


        $this->http_client = new Client(
            [
                'timeout'=>65,
                'cookies'=>true,
                'headers'=>$headers,
//                'proxy'=>$this->proxy
            ]
        );
        $this->debug = true;
        $this->login_flag = false;
    }

    public function fetch_ip()
    {
        //http://ttvp.mimidaili.com/ip/?tid=557732816270489&num=10&delay=1&protocol=https&foreign=none
        $form_params = [
            'tid'=>'557732816270489',
            'num'=>1,
//            'protocol'=>'https',
            'foreign'=>'only',
            'delay'=>1,
            'exclude_ports'=>'8080',
            'filter'=>'on'
        ];
        $ip = file_get_contents(API_FETCH_IPS.'?'.http_build_query($form_params));
        $this->proxy = 'tcp://'.$ip;
        echo $this->proxy.PHP_EOL;
    }

    public function register()
    {
        $this->generate_account();
        $this->post_account();
        if ($this->login_flag){
             $this->login_account();
             $this->send_email();
        }
    }


    //用户名-邮箱-密码。
    public function generate_account()
    {
        $length = rand(8,12);
        $chars = 'abcdefghijklmnopqrstuvwxyz0123456789';
        $username = "";
        for ( $i = 0; $i < $length; $i++ )
        {
            $username .= $chars[ mt_rand(0, strlen($chars) - 1) ];
        }
        $email = $username.'@tempr.email';
        $passwd = '123qwe123';//todo:需要解决密码可以自由配置的问题。

        $response = $this->http_client->request('GET', API_REGISTER_CHECK, [
            'form_params' => [
                'username' => $username,
                'email'=>$email,
                'format' => "json",
                'id' => $this->getMillisecond(),
            ]
        ]);
        $body = $response->getBody();
        if ($this->debug){
            echo 'generate_account methond'.PHP_EOL;
            echo $body.PHP_EOL;
        }
        $result = json_decode($body,true);
        if (!is_array($result) || isset($result['error']) ){
            return false;
        }
        $this->account->setUsername($username);
        $this->account->setEmail($email);
        $this->account->setPasswd($passwd);
        return true;
    }

    public function post_account($captcha='',$captcha_key='')
    {
        $form_params = [
            'username' => $this->account->getUsername(),
            'email' => $this->account->getEmail(),
            'password' => '9d77624958b23754324211e4dc540e365473b0bfc0d358ff3857bcb8954697d1b90f7b7f6f23c6cd210e906c5c79632ca2faad7285c2704d8b1eefa5a1ecda57ecf300912a45cc493fb58869934b7b1cab807ad3332610d859cc47c9695aa14884fd6f389ef9f7e65667016ef15371002b1c771749e930ce323dafa00b9ea3f2',
            'location' => 'US',
            'redirect_uri' => 'https://sso.garena.com/ui/login?locale=en-US&redirect_uri=https%3A%2F%2Fintl.garena.com%2F&app_id=10100&display=page',
            'format' => 'json',
            'id' => $this->getMillisecond()
        ];

        if ($captcha && $captcha_key){
            $form_params['captcha'] = $captcha;
            $form_params['captcha_key'] = $captcha_key;
        }
        $response = $this->http_client->request('POST', API_REGISTER, [
            'form_params' => $form_params
        ]);

        $body = $response->getBody();
        if ($this->debug){
            echo 'post_account methond'.PHP_EOL;
            echo $body.PHP_EOL;
        }
        $result = json_decode($body,true);
        if (!is_array($result) || isset($result['error']) ){
            if (is_array($result) && $result['error'] == 'error_require_captcha'){
                //处理验证码。
                $captcha_info = $this->fetch_captcha();
                $captcha = $this->captch2text($captcha_info['captcha_file']);
                if ($captcha){
                    $this->post_account($captcha,$captcha_info['captcha_key']);
                }

            }

            if (is_array($result) && $result['error'] == 'error_captcha'){
                $this->report_error();
            }
            return false;
        }
        $this->login_flag = true;
        return true;
    }

    public function login_account($captcha='',$captcha_key='')
    {
        $form_params = [
            'account' => $this->account->getUsername(),
            'format' => 'json',
            'id' => $this->getMillisecond(),
            'app_id' => 10100,
        ];


        if ($captcha && $captcha_key){
            $form_params['captcha'] = $captcha;
            $form_params['captcha_key'] = $captcha_key;
        }

        if ($this->debug){
            echo 'login_account method'.PHP_EOL;
            echo json_encode($form_params).PHP_EOL;
        }

        $response = $this->http_client->request('get', API_PRELOGIN.'?'.http_build_query($form_params));

        $body = $response->getBody();
        if ($this->debug){
            echo 'login_account method'.PHP_EOL;
            echo $body.PHP_EOL;
        }

        $result = json_decode($body,true);
        if (!is_array($result) || isset($result['error']) ){
            if (is_array($result) && $result['error'] == 'error_require_captcha'){
                //处理验证码。
                $captcha_info = $this->fetch_captcha();
                $captcha = $this->captch2text($captcha_info['captcha_file']);
                if ($captcha){
                    $this->login_account($captcha,$captcha_info['captcha_key']);
                }

            }

            if (is_array($result) && $result['error'] == 'error_captcha'){
                $this->report_error();
            }
            return false;
        }

        //获取密码
        $passwd = $this->fetch_passwd($this->account->getPasswd(),$result['v1'],$result['v2']);
        $form_params = [
            'account' => $this->account->getUsername(),
            'password' => $passwd,
            'redirect_uri' => 'https://account.garena.com/',
            'format' => 'json',
            'id' => $this->getMillisecond(),
            'app_id' => 10100,
        ];

        $response = $this->http_client->request('get', API_LOGIN.'?'.http_build_query($form_params));
        $body = $response->getBody();
        if ($this->debug){
            echo 'login_account method ：'.API_LOGIN.PHP_EOL;
            echo $body.PHP_EOL;
        }

        $result = json_decode($body,true);
        if (is_array($result) && isset($result['error'])){
            return false;
        }
        sleep(1);
        $url = API_ACCOUNT_INIT."?session_key=".$result['session_key'];
        $response =$this->http_client->request('get',$url);
        $body = $response->getBody();
        if ($this->debug){
            echo $url.PHP_EOL;
            echo $body.PHP_EOL;
        }
        sleep(3);


        return true;
    }

    public function send_email()
    {
        $this->http_client->request('get', API_ACCOUNT_VERIFY_EMAIL_INIT);
        $form_params =[
            'email'=>$this->account->getEmail(),
            'locale'=>"en"
        ];
        $body = json_encode($form_params);
        if ($this->debug){
            echo 'send_email method step 1'.PHP_EOL;
            echo $body.PHP_EOL;
        }

        $response = $this->http_client->request('post', API_ACCOUNT_VERIFY_EMAIL_APPLY, [
           'body' => $body,
       ]);
        $body = $response->getBody();
        if ($this->debug){
            echo 'send_email method step 2'.PHP_EOL;
            echo $body.PHP_EOL;
        }
        //next_action
        $result = json_decode($body,true);
        if (!is_array($result) || isset($result['error'])){
            return false;
        }
        //{"status":0,"next_action":{"max_retry":5,"verified_info":{"email":"rlgnvzjt@tempr.email"},"action_type":12,"error_count":0}}
        if (isset($result['status']) && isset($result['next_action'])){
            $insert_id = $this->db->insert('account')->cols(array(
                'username'=>$this->account->getUsername(),
                'passwd'=>$this->account->getPasswd(),
                'email'=>$this->account->getEmail(),
                'reg_date'=>$this->account->getRegDate(),
            ))->query();
        }

    }

    public function fetch_passwd($passwd,$v1,$v2)
    {
        $form_params = [
            'password' => $passwd,
            'v1' => $v1,
            'v2' => $v2,
        ];
        $response = $this->http_client->request('get', 'http://666coder.com:8888?'.http_build_query($form_params));
        $body = $response->getBody();
        $result = json_decode($body,true);

        if (is_array($result) && isset($result['result'])){
            return $result['result'];
        }else{
            return false;
        }

    }

    public function fetch_captcha()
    {
        $length = 16;
        $chars = 'abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789';
        $captcha_key = "";
        for ( $i = 0; $i < $length; $i++ )
        {
            $captcha_key .= $chars[ mt_rand(0, strlen($chars) - 1) ];
        }

        $captcha_src = "https://captcha.garena.com/image?key=".$captcha_key;

        $captcha_file = "captchas/".uniqid().'.jpg';

        $response = $this->http_client->request('get',$captcha_src);

        $body = $response->getBody();
        file_put_contents($captcha_file,$body);
        if ($this->debug){
            echo 'fetch_captcha'.PHP_EOL;
            echo $captcha_file.PHP_EOL;
        }
        return [
            'captcha_file'=>$captcha_file,
            'captcha_key'=>$captcha_key,
        ];
    }

    public function report_error()
    {
        //上报错码。
        $damaUrl = API_RUOKUAI_ERROR;
        $ch = curl_init();

        include "配置文件.php";
        $postFields = array(
            'username' => $config['username'],
            'password' => $config['password'],
            'softid' => 87478,	//改成你自己的
            'softkey' => '8f200eeed01f4847bd02f6a2829dcb75',	//改成你自己的
            'id' => $this->captcha_id
        );

        curl_setopt($ch, CURLOPT_HEADER, FALSE);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, TRUE);
        curl_setopt($ch, CURLOPT_URL,$damaUrl);
        curl_setopt($ch, CURLOPT_TIMEOUT, 60);	//设置本机的post请求超时时间，如果timeout参数设置60 这里至少设置65
        curl_setopt($ch, CURLOPT_POST, TRUE);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $postFields);

        $body = curl_exec($ch);
        curl_close($ch);
        if ($this->debug){
            echo 'report_error method'.PHP_EOL;
            echo $body.PHP_EOL;
        }
    }

    public function captch2text($captcha_file = '')
    {
        if (empty($captcha_file)){
            return '';
        }
        $damaUrl = API_RUOKUAI;
        $filename = realpath('.').'/'.$captcha_file;	//img.jpg是测试用的打码图片，4位的字母数字混合码,windows下的PHP环境这里需要填写完整路径
        $ch = curl_init();

        include "配置文件.php";
        $postFields = array('username' => $config['username'],
            'password' => $config['password'],
            'typeid' => 1000,	//4位的字母数字混合码   类型表http://www.ruokuai.com/pricelist.aspx
            'timeout' => 60,	//中文以及选择题类型需要设置更高的超时时间建议90以上
            'softid' => 87478,	//改成你自己的
            'softkey' => '8f200eeed01f4847bd02f6a2829dcb75',	//改成你自己的
            'image' => new CURLFile($filename)
        );

        curl_setopt($ch, CURLOPT_HEADER, FALSE);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, TRUE);
        curl_setopt($ch, CURLOPT_URL,$damaUrl);
        curl_setopt($ch, CURLOPT_TIMEOUT, 200);	//设置本机的post请求超时时间，如果timeout参数设置60 这里至少设置65
        curl_setopt($ch, CURLOPT_POST, TRUE);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $postFields);

        $body = curl_exec($ch);
        curl_close($ch);
        if ($this->debug){
            echo 'captch2text method'.PHP_EOL;
            echo $body.PHP_EOL;
        }
        $result = json_decode($body,true);
        if(is_array($result) && isset($result['Result'])){
            $this->captcha_id = $result['Result'];
            return $result['Result'];
        }else{
            return false;
        }
    }
    //提交用户名是否可用请求。邮箱是否可用请求。
    //提交注册请求。
    //登录账号。发送邮件。
    //保存账号信息
    private function getMillisecond() {
        list($s1, $s2) = explode(' ', microtime());
        return (float)sprintf('%.0f', (floatval($s1) + floatval($s2)) * 1000);
    }

}
$register_machinese = new Register_machinse();
while (true){
    try{

//        $register_machinese = new Register_machinse();
        $register_machinese->register();
    }catch (Exception $e){

    }
    echo "-------------------".PHP_EOL;
}
