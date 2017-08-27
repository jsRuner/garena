<?php
/**
 * Created by JetBrains PhpStorm.
 * User: 吴文付 hiwower@gmail.com
 * Date: 2017/8/22
 * Time: 下午9:27
 * description:文件描叙
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


use GuzzleHttp\Client;


use BootPress\SQLite\Component as Sqlite;
class Register {

    private $register_client;


    private $db;

    private $log_file;//记录注册后的账号信息。

    private  $current_account; //当前注册的账号信息。

    private $debug;


    function __construct($debug=false)
    {
        $this->debug = $debug;

        $headers =[
            'User-Agent'=>'Mozilla/5.0 (Macintosh; Intel Mac OS X 10_12_5) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/60.0.3112.101 Safari/537.36'
        ];
        $this->register_client = new Client(['cookies'=>true,"headers"=>$headers,'connect_timeout' => 30]);

        //数据库
        $db = new Sqlite(dirname(__FILE__).'/garena.db');
        if ($db->created) {

            echo '数据库创建成功'.PHP_EOL;

            $db->settings('version', '1.0');

            $db->create('account', array(
                'id' => 'INTEGER PRIMARY KEY',
                'username' => 'VARCHAR NOT NULL DEFAULT ""',
                'password' => 'VARCHAR NOT NULL DEFAULT "123qwe123"',
                'email' => 'VARCHAR NOT NULL DEFAULT ""',
                'email_password' => 'VARCHAR NOT NULL DEFAULT ""',
                'reg_time' => 'DATETIME NOT NULL DEFAULT ""',
                'reg_time' => 'DATETIME NOT NULL DEFAULT ""',
                'verify_time' => 'DATETIME NOT NULL DEFAULT ""',
                'status' => 'Inte NOT NULL DEFAULT "0"',
            ), array('unique'=>'username,email'));
        }

        $this->db = $db;
        $this->log_file = dirname(dirname(__FILE__)).'/待激活记录.txt';

    }

    private function generate_username( $length = 8 ) {
        $chars = 'abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789';
        $username = "";
        for ( $i = 0; $i < $length; $i++ )
        {
            $username .= $chars[ mt_rand(0, strlen($chars) - 1) ];
        }
        $response = $this->register_client->request('GET', 'https://sso.garena.com/api/register/check', [
            'form_params' => [
                'username' => $username,
                'format' => "json",
                'id' => $this->getMillisecond(),
            ],
            'headers' => [
                'User-Agent' => 'Mozilla/5.0 (Macintosh; Intel Mac OS X 10.12; rv:54.0) Gecko/20100101 Firefox/54.0',
                'Host' => 'sso.garena.com',
                'Accept'     => 'application/json',
            ]
        ]);

        $code = $response->getStatusCode(); // 200
        $reason = $response->getReasonPhrase(); // OK
        $body = $response->getBody();

        echo $code.PHP_EOL;
        echo $reason.PHP_EOL;
        echo $body.PHP_EOL;

        $result = json_decode($body,true);
        if (isset($result['result']) && $result['result'] ==0){
            echo "用户名可用".PHP_EOL;
        }else{
            $this->generate_username($length);
        }
        return $username;
    }

    public function is_register_email($email)
    {
        $query = [
            'email' => $email,
            'format' => "json",
            'id' => $this->getMillisecond(),
        ];

        $response = $this->register_client->request('GET', 'https://sso.garena.com/api/register/check?'.http_build_query($query), [
            'headers' => [
                'User-Agent' => 'Mozilla/5.0 (Macintosh; Intel Mac OS X 10.12; rv:54.0) Gecko/20100101 Firefox/54.0',
                'Host' => 'sso.garena.com',
                'Accept'     => 'application/json',
                'X-Requested-With'=>'XMLHttpRequest'
            ]
        ]);

        $code = $response->getStatusCode(); // 200
        $reason = $response->getReasonPhrase(); // OK
        $body = $response->getBody();

        echo $code.PHP_EOL;
        echo $reason.PHP_EOL;
        echo $body.PHP_EOL;

        $result = json_decode($body,true);

        if (!is_array($result) || isset($result['error']) ){
            file_put_contents($this->log_file,date('Y-m-d H:i:s').PHP_EOL.'邮箱:'.$email.PHP_EOL.'已经被注册,无法使用'.PHP_EOL,FILE_APPEND);
            return false;
        }else{
            return true;
        }
    }

    private function gener_captcha($length=16)
    {
        $chars = 'abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789';
        $captcha_key = "";
        for ( $i = 0; $i < $length; $i++ )
        {
            $captcha_key .= $chars[ mt_rand(0, strlen($chars) - 1) ];
        }

        $captcha_src = "https://captcha.garena.com/image?key=".$captcha_key;

        $captcha_file = "captchas/".uniqid().'.jpg';

        $response = $this->register_client->request('get',$captcha_src);

        $body = $response->getBody();


        file_put_contents($captcha_file,$body);

        echo $captcha_file.PHP_EOL;


        return [
            'captcha_file'=>$captcha_file,
            'captcha_key'=>$captcha_key,
        ];



    }

    public function captcha_text($captcha_file)
    {
        if (empty($captcha_file)){
            return '';
        }
        $damaUrl = 'http://api.ruokuai.com/create.json';
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
        curl_setopt($ch, CURLOPT_TIMEOUT, 65);	//设置本机的post请求超时时间，如果timeout参数设置60 这里至少设置65
        curl_setopt($ch, CURLOPT_POST, TRUE);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $postFields);

        $result = curl_exec($ch);

        curl_close($ch);

        var_dump($result);

        $format_result = json_decode($result,true);

        file_put_contents('captcha.txt',date('Y-m-d H:i:s').PHP_EOL.$result.PHP_EOL,FILE_APPEND);

        if(is_array($format_result) && isset($format_result['Result'])){
            return $format_result['Result'];
        }else{
            return false;
        }
    }



    public function reg($username,$email,$captcha_text=false,$captcha_key=false)
    {
        if ($captcha_text && $captcha_key){
            $response = $this->register_client->request('POST', 'https://sso.garena.com/api/register', [
                'form_params' => [
                    'username' => $username,
                    'email' => $email,
                    'password' => '9d77624958b23754324211e4dc540e365473b0bfc0d358ff3857bcb8954697d1b90f7b7f6f23c6cd210e906c5c79632ca2faad7285c2704d8b1eefa5a1ecda57ecf300912a45cc493fb58869934b7b1cab807ad3332610d859cc47c9695aa14884fd6f389ef9f7e65667016ef15371002b1c771749e930ce323dafa00b9ea3f2',
                    'location' => 'US',
                    'redirect_uri' => 'https://sso.garena.com/ui/login?locale=en-US&redirect_uri=https%3A%2F%2Fintl.garena.com%2F&app_id=10100&display=page',
                    'format' => 'json',
                    'id' => $this->getMillisecond(),
                    'captcha'=>$captcha_text,
                    'captcha_key'=>$captcha_key
                ]
            ]);

        }else{

            $response = $this->register_client->request('POST', 'https://sso.garena.com/api/register', [
                'form_params' => [
                    'username' => $username,
                    'email' => $email,
                    'password' => '9d77624958b23754324211e4dc540e365473b0bfc0d358ff3857bcb8954697d1b90f7b7f6f23c6cd210e906c5c79632ca2faad7285c2704d8b1eefa5a1ecda57ecf300912a45cc493fb58869934b7b1cab807ad3332610d859cc47c9695aa14884fd6f389ef9f7e65667016ef15371002b1c771749e930ce323dafa00b9ea3f2',
                    'location' => 'US',
                    'redirect_uri' => 'https://sso.garena.com/ui/login?locale=en-US&redirect_uri=https%3A%2F%2Fintl.garena.com%2F&app_id=10100&display=page',
                    'format' => 'json',
                    'id' => $this->getMillisecond(),
                ]
            ]);
        }
        $code = $response->getStatusCode(); // 200
        $reason = $response->getReasonPhrase(); // OK
        $body = $response->getBody();

        echo $code.PHP_EOL;
        echo $reason.PHP_EOL;
        echo $body.PHP_EOL;

        $format_body = json_decode($body,true);

        //todo:需要改进判断
        if (is_array($format_body) && isset($format_body['error'])){
            return false;
        }else{
            return true;
        }

    }

    /**
     * 登录前的请求。
     *
     * @param $username
     * @param bool $captcha_text
     * @param bool $captcha_key
     */
    public function pre_login($username,$captcha_text=false,$captcha_key=false)
    {
        if ($captcha_text && $captcha_key){

            $query = [
                'account' => $username,

                'format' => 'json',
                'id' => (string)$this->getMillisecond(),
                'app_id' => 10100,

                'captcha'=>$captcha_text,
                'captcha_key'=>$captcha_key
            ];
            $response = $this->register_client->request('get', 'https://sso.garena.com/api/prelogin?'.http_build_query($query));
        }else{

            $query = [
                'account' => $username,
                'format' => 'json',
                'id' => (string)$this->getMillisecond(),
                'app_id' => 10100,
            ];
            $response = $this->register_client->request('get', 'https://sso.garena.com/api/prelogin?'.http_build_query($query));
        }


        $code = $response->getStatusCode(); // 200
        $reason = $response->getReasonPhrase(); // OK
        $body = $response->getBody();

        echo $code.PHP_EOL;
        echo $reason.PHP_EOL;
        echo $body.PHP_EOL;

        $format_body = json_decode($body,true);

        //todo:需要改进判断
        if (is_array($format_body) && isset($format_body['error'])){
            return false;
        }else{

            return $format_body;
        }


    }

    public function getMillisecond() {
        list($s1, $s2) = explode(' ', microtime());
        return (float)sprintf('%.0f', (floatval($s1) + floatval($s2)) * 1000);
    }


    public  function gener_password($password, $v1,$v2){

        $query = [
            'password' => $password,
            'v1' => $v1,
            'v2' => $v2,
        ];
        $response = $this->register_client->request('get', 'http://666coder.com:8888?'.http_build_query($query));
        $code = $response->getStatusCode(); // 200
        $reason = $response->getReasonPhrase(); // OK
        $body = $response->getBody();

        echo $code.PHP_EOL;
        echo $reason.PHP_EOL;
        echo $body.PHP_EOL;

        $format_body = json_decode($body,true);

        if (is_array($format_body) && isset($format_body['result'])){
            return $format_body['result'];
        }else{
            return false;
        }


    }

    public function login($username,$v1,$v2)
    {
        $password = $this->gener_password('123qwe123',$v1,$v2);

        $query = [
            'account' => $username,
            'password' => $password,
            'redirect_uri' => 'https://account.garena.com/',

            'format' => 'json',
            'id' => (string)$this->getMillisecond(),
            'app_id' => 10100,
        ];

        $response = $this->register_client->request('get', 'https://sso.garena.com/api/login?'.http_build_query($query));


        $code = $response->getStatusCode(); // 200
        $reason = $response->getReasonPhrase(); // OK
        $body = $response->getBody();

        echo $code.PHP_EOL;
        echo $reason.PHP_EOL;
        echo $body.PHP_EOL;

        $format_body = json_decode($body,true);

        //todo:需要改进判断
        if (is_array($format_body) && isset($format_body['error'])){
            return false;
        }else{
            return $format_body;
        }

    }

    /**
     * 初始化
     *
     * @param $session_key
     *
     */
    public function init_account($login_info=[])
    {
        sleep(2);
        $url = "https://account.garena.com/api/account/init?session_key=".$login_info['session_key'];

        try{

            $response = $this->register_client->request('get', $url);
        }catch (Exception $e){
            return false;
        }

        $code = $response->getStatusCode(); // 200
        $reason = $response->getReasonPhrase(); // OK
        $body = $response->getBody();
        echo $code.PHP_EOL;
        echo $reason.PHP_EOL;
        echo $body.PHP_EOL;
        return true;
    }

    public function login_account($username)
    {
        $reg_result = $this->pre_login($username);

        if (!$reg_result){
            //获取验证码。
            $captcha_info = $this->gener_captcha();
            //解析验证码
            $captcha_text = $this->captcha_text($captcha_info['captcha_file']);

            $reg_result = $this->pre_login($username,$captcha_text,$captcha_info['captcha_key']);

        }
        $login_result = $this->login($username,$reg_result['v1'],$reg_result['v2']);

        if ($login_result){

            return $this->init_account($login_result);
        }else{
            return false;
        }

    }


    public function reg_account($email)
    {
        $username_len = rand(7,15);
        $username = $this->generate_username($username_len);
        $reg_result = $this->reg($username,$email);

        if (!$reg_result){
            //获取验证码。
            $captcha_info = $this->gener_captcha();
            //解析验证码
            $captcha_text = $this->captcha_text($captcha_info['captcha_file']);
            $reg_result = $this->reg($username,$email,$captcha_text,$captcha_info['captcha_key']);
            if (!$reg_result){
//                todo:注册失败.
                return false;
            }

        }
        $current_account = $this->current_account;
        //注册成功。写入数据库。
        $data = [
            'username'=>$email,
            'password'=>'123qwe123',
            'email'=>$email,
            'email_password'=>$current_account['email_password'],
            'reg_time'=>date('Y-m-d H:i:s'),
            'status'=>0
        ];
        $this->db->insert('account',$data);
        //去发送激活邮件。
        return $this->verify_email_before($username,$email);
    }

    /**
     * 批量注册账号
     */
    public function reg_accounts($emailfile)
    {

        $file = fopen($emailfile, "r");
        $emails=array();
        //输出文本中所有的行，直到文件结束为止。
        while(! feof($file))
        {
            $emails[]= fgets($file);//fgets()函数从文件指针中读取一行
        }
        fclose($file);
        $emails=array_filter($emails);

        foreach ($emails as $email_item){

            $this->current_account = [];

            $email =stristr($email_item,"----",true);
            $email = trim($email);

            $email_password = stristr($email_item,"----");
            $email_password = trim($email_password,"----");

            $email_password = trim($email_password);


            echo $email.PHP_EOL;
            echo $email_password.PHP_EOL;

            if (empty($email) || empty($email_password)){
                continue;
            }

            //如果已经存在。则只去激活。
            $rows = $this->db->all('SELECT `username`,`password`,`email`,`email_password`,`reg_time`,`status` FROM account where email = \''.$email.'\'');

            foreach ($rows as $row) {
                list($username, $password, $email, $email_password, $reg_time,$status) = $row;
                break;
            }
            if ($username){
                //只激活。
                $this->verify_email_before($username,$email);
                continue;
            }


//            $ispop3_result = $this->check_email_pop3($email,$email_password);
            $ispop3_result = true;
            if($ispop3_result){
                //判断邮箱是否Ok
                $is_register_reuslt = $this->is_register_email($email);
                //未注册同时支持pop3才可以注册。
                if ($is_register_reuslt && $ispop3_result){
                    $this->current_account=[
                        'email'=>$email,
                        'email_password'=>$email_password
                    ];

                    try{
                        $this->reg_account($email);
                    }catch (Exception $e){
                        print_r($e->getMessage());
                        echo PHP_EOL;
                    }

                }
            }


        }

    }

    public function send_email_init()
    {
        sleep(2);
        $url = "https://account.garena.com/api/account/verify_email/init";
        $response = $this->register_client->request('get', $url);
        $code = $response->getStatusCode(); // 200
        $reason = $response->getReasonPhrase(); // OK
        $body = $response->getBody();
        echo $code.PHP_EOL;
        echo $reason.PHP_EOL;
        echo $body.PHP_EOL;
        return true;
        
    }

    /**
     * 发送邮件
     *
     * @param $email
     * @return bool
     */
    private function send_email($email)
    {
        $data = [
            'email'=>$email,
            'locale'=>"en"
        ];
        $body = json_encode($data);
        echo $body.PHP_EOL;
       $response = $this->register_client->request('post', 'https://account.garena.com/api/account/verify_email/apply', [
            'body' => $body,
            'headers'=>[
                'Content-Type'=>'application/json',
                'Origin'>'https://account.garena.com',
                'Referer'=>'https://account.garena.com/security/verify_email/apply',
                'User-Agent'=>'Mozilla/5.0 (Macintosh; Intel Mac OS X 10_12_5) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/60.0.3112.101 Safari/537.36'
            ]
        ]);
        $code = $response->getStatusCode(); // 200
        $reason = $response->getReasonPhrase(); // OK
        $body = $response->getBody();

        echo $code.PHP_EOL;
        echo $reason.PHP_EOL;
        echo $body.PHP_EOL;

        $format_body = json_decode($body,true);

        if (is_array($format_body) && isset($format_body['status']) && $format_body['status'] == 0){
            //发送邮件成功。需要更新状态。
            $data = [$email=>[
                'status'=>1
            ]];
            $this->db->update('account','email',$data);
            return true;
        }else{
            return false;
        }
    }

    /**
     * 登录账号,发送邮件验证.
     *
     * @param $username
     * @param $email
     * @return bool
     */
    private function verify_email_before($username,$email)
    {
        //登录
        $login_result = $this->login_account($username);
        //发送邮件
        if ($login_result){
            $this->send_email_init();

            $send_result = $this->send_email($email);

            if ($send_result){
                file_put_contents($this->log_file,date('Y-m-d H:i:s').PHP_EOL.'账号:'.$username.' 密码:123qwe123 邮箱:'.$email."注册成功.激活邮件已经发送。".PHP_EOL,FILE_APPEND);
                return true;
            }else{
                return false;
            }

        }
        return false;
    }

    /**
     * 判断邮箱是否支持pop3
     *
     *
     * @param $email
     * @param $email_password
     * @return bool
     */
    private function check_email_pop3($email,$email_password)
    {
        //判断是否support pop3
        $suffix_email = stristr($email,'@');

        switch ($suffix_email){
            case '@qq.com':
                $host = "pop.qq.com";
                break;
            case '@163.com':
                $host = "pop.163.com";
                break;
            case '@126.com':
                $host = "pop.126.com";
                break;
            case '@sohu.com':
                $host = "pop3.sohu.com";
                break;
            default:
                $host = "pop.sina.com"; // 默认新浪的
                break;
        }
        try{
            $storage = new afinogen89\getmail\storage\Pop3(['host' => $host, 'user' => $email, 'password' => $email_password]);
            $num =  $storage->countMessages();
        }
        catch (Exception $e) {
            print $e->getMessage();
            echo PHP_EOL;
            file_put_contents($this->log_file,date('Y-m-d H:i:s').PHP_EOL.'邮箱:'.$email.' 密码:'.$email_password.'没开启支持pop3或者账号密码错误,无法使用'.PHP_EOL,FILE_APPEND);
            //todo:记录日志。验证失败。
            return false;
        }
        return true;
    }




}


$register = new Register();

file_put_contents($register->log_file,PHP_EOL."------------------------------[".date('Y-m-d H:i:s')."开始执行]-----------------------------------------------------------".PHP_EOL,FILE_APPEND);
file_put_contents($register->log_file,PHP_EOL,FILE_APPEND);
file_put_contents($register->log_file,PHP_EOL,FILE_APPEND);


$register->reg_accounts(dirname(dirname(__FILE__))."/邮箱.txt");

file_put_contents($register->log_file,PHP_EOL,FILE_APPEND);
file_put_contents($register->log_file,PHP_EOL,FILE_APPEND);
file_put_contents($register->log_file,PHP_EOL."------------------------------[".date('Y-m-d H:i:s')."结束执行]-----------------------------------------------------------".PHP_EOL,FILE_APPEND);



