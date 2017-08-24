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


class Register {

    private $register_client;

    private $jar; //特定的cookie.登录使用。


    function __construct()
    {
        $this->jar = new \GuzzleHttp\Cookie\CookieJar;
        $headers =[
            'User-Agent'=>'Mozilla/5.0 (Macintosh; Intel Mac OS X 10_12_5) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/60.0.3112.101 Safari/537.36'
        ];
        $this->register_client = new Client(['cookies'=>true,"headers"=>$headers]);
    }

    public function generate_username( $length = 8 ) {
        // 密码字符集，可任意添加你需要的字符
        $chars = 'abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789';
        $username = "";
        for ( $i = 0; $i < $length; $i++ )
        {
        // 这里提供两种字符获取方式
        // 第一种是使用 substr 截取$chars中的任意一位字符；
        // 第二种是取字符数组 $chars 的任意元素
        // $username .= substr($chars, mt_rand(0, strlen($chars) – 1), 1);
            $username .= $chars[ mt_rand(0, strlen($chars) - 1) ];
        }


        echo $username.PHP_EOL;

        //发送请求。判断是否有效果。api/register  check

        $response = $this->register_client->request('GET', 'https://sso.garena.com/api/register/check', [
            'form_params' => [
                'username' => $username,
                'format' => "json",
                'id' => time()/1000,
            ],
            'headers' => [
                'User-Agent' => 'Mozilla/5.0 (Macintosh; Intel Mac OS X 10.12; rv:54.0) Gecko/20100101 Firefox/54.0',
                'Host' => 'sso.garena.com',
                'Referer'=>"https://sso.garena.com/ui/register?redirect_uri=https%3A%2F%2Fsso.garena.com%2Fui%2Flogin%3Flocale%3Den-US%26redirect_uri%3Dhttps%253A%252F%252Fintl.garena.com%252F%26app_id%3D10000%26display%3Dpage&display=page&locale=en-US",
                'Accept'     => 'application/json',
            ]
        ]);

        $code = $response->getStatusCode(); // 200
        $reason = $response->getReasonPhrase(); // OK
        $body = $response->getBody();
        $remainingBytes = $body->getContents();

        echo $code.PHP_EOL;
        echo $reason.PHP_EOL;
        echo $body.PHP_EOL;
        echo $remainingBytes.PHP_EOL;
        echo $body !=="{\"result\": 0}".PHP_EOL;

        $result = json_decode($body,true);

        var_dump($result);

        if (isset($result['result']) && $result['result'] ==0){
            echo "用户名可用".PHP_EOL;
        }else{
            $this->generate_username($length);
        }
        return $username;
    }

    public function generate_email( $length = 6 ) {
        // 密码字符集，可任意添加你需要的字符
        $chars = 'abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789';
        $email = "";
        for ( $i = 0; $i < $length; $i++ )
        {
            // 这里提供两种字符获取方式
            // 第一种是使用 substr 截取$chars中的任意一位字符；
            // 第二种是取字符数组 $chars 的任意元素
            // $email .= substr($chars, mt_rand(0, strlen($chars) – 1), 1);
            $email .= $chars[ mt_rand(0, strlen($chars) - 1) ];
        }

        $email = $email."@qq.com";


        echo $email.PHP_EOL;

        //发送请求。判断是否有效果。api/register  check
        $response = $this->register_client->request('GET', 'https://sso.garena.com/api/register/check', [
            'form_params' => [
                'username' => $email,
                'format' => "json",
                'id' => time()/1000,
            ],
            'headers' => [
                'User-Agent' => 'Mozilla/5.0 (Macintosh; Intel Mac OS X 10.12; rv:54.0) Gecko/20100101 Firefox/54.0',
                'Host' => 'sso.garena.com',
                'Referer'=>"https://sso.garena.com/ui/register?redirect_uri=https%3A%2F%2Fsso.garena.com%2Fui%2Flogin%3Flocale%3Den-US%26redirect_uri%3Dhttps%253A%252F%252Fintl.garena.com%252F%26app_id%3D10000%26display%3Dpage&display=page&locale=en-US",
                'Accept'     => 'application/json',
            ]
        ]);

        $code = $response->getStatusCode(); // 200
        $reason = $response->getReasonPhrase(); // OK
        $body = $response->getBody();
        $remainingBytes = $body->getContents();

        echo $code.PHP_EOL;
        echo $reason.PHP_EOL;
        echo $body.PHP_EOL;
        echo $remainingBytes.PHP_EOL;
        echo $body !=="{\"result\": 0}".PHP_EOL;

        $result = json_decode($body,true);

        var_dump($result);

        if (isset($result['result']) && $result['result'] ==0){
            echo "邮箱可用".PHP_EOL;
        }else{
            $this->generate_email($length);
        }
        return $email;
    }

    public function gener_captcha($length=16)
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

    public function login_email()
    {
//       $storage = new afinogen89\getmail\storage\Pop3(['host' => 'pop.qq.com', 'user' => 'doudouchidou@qq.com', 'password' => 'foxovsofjgllbbgc']);
//        $storage = new afinogen89\getmail\storage\Pop3(['host' => 'pop3.sohu.com', 'user' => 'ppag331278bc69af@sohu.com', 'password' => '123qwe123']);
        $storage = new afinogen89\getmail\storage\Pop3(['host' => 'pop3.sohu.com', 'user' => 'baji677990@sohu.com', 'password' => 'zhangrangyong']);

        echo $storage->countMessages().PHP_EOL;


        for ($mid=1;$mid<=5;$mid++){
            $msg = $storage->getMessage($mid); //倒序。5表示第一个邮件。1表示最新的。
            $subject =  $msg->getHeaders()->getSubject();
            echo $subject.PHP_EOL;
            echo $msg->getHeaders()->getDate().PHP_EOL;
            echo $msg->getHeaders()->getFrom().PHP_EOL;

            if ($subject == "Verify Your Garena Account Email Address"){
                    //获取内容。定位链接。get访问。根据内容判断是否成功。
                $msg_content = $msg->getMsgBody();
                echo $msg_content.PHP_EOL;
                preg_match_all('/href="https:\/\/account.garena.com\/ui\/account\/email\/verify(.*?)" style=/i',$msg_content,$arr);
                var_dump($arr);
                if (!empty($arr)){
                    //发送请求。
                    $url = "https://account.garena.com/ui/account/email/verify".$arr[1][0];

                    echo $url.PHP_EOL;

                    try{
                        $response = $this->register_client->request('get', $url);
                        $code = $response->getStatusCode(); // 200
                        $reason = $response->getReasonPhrase(); // OK
                        $body = $response->getBody();
                        echo $code.PHP_EOL;
                        echo $reason.PHP_EOL;
                        echo $body.PHP_EOL;


                    }  catch (Exception $e) {
                        print $e->getMessage();
                        //todo:记录日志。验证失败。
                    }
                }
                break;
            }
        }
    }



    public function captcha_text($captcha_file='captchas/599c4fe6cc77d.jpg')
    {
        $damaUrl = 'http://api.ruokuai.com/create.json';
        $filename = $captcha_file;	//img.jpg是测试用的打码图片，4位的字母数字混合码,windows下的PHP环境这里需要填写完整路径
        $ch = curl_init();
        $postFields = array('username' => 'xxooff',
            'password' => '123qwe123',
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
        echo "执行reg方法".PHP_EOL;

//        $response = $client->post('https://sso.garena.com/api/register');

        if ($captcha_text && $captcha_key){

            var_dump([
                'form_params' => [
                    'username' => $username,
                    'email' => $email,
                    'password' => '9d77624958b23754324211e4dc540e365473b0bfc0d358ff3857bcb8954697d1b90f7b7f6f23c6cd210e906c5c79632ca2faad7285c2704d8b1eefa5a1ecda57ecf300912a45cc493fb58869934b7b1cab807ad3332610d859cc47c9695aa14884fd6f389ef9f7e65667016ef15371002b1c771749e930ce323dafa00b9ea3f2',
                    'location' => 'US',
                    'redirect_uri' => 'https://sso.garena.com/ui/login?locale=en-US&redirect_uri=https%3A%2F%2Fintl.garena.com%2F&app_id=10100&display=page',
                    'format' => 'json',
                    'id' => time()/1000,
                    'captcha'=>$captcha_text,
                    'captcha_key'=>$captcha_key
                ]
            ]);

            $response = $this->register_client->request('POST', 'https://sso.garena.com/api/register', [
                'form_params' => [
                    'username' => $username,
                    'email' => $email,
                    'password' => '9d77624958b23754324211e4dc540e365473b0bfc0d358ff3857bcb8954697d1b90f7b7f6f23c6cd210e906c5c79632ca2faad7285c2704d8b1eefa5a1ecda57ecf300912a45cc493fb58869934b7b1cab807ad3332610d859cc47c9695aa14884fd6f389ef9f7e65667016ef15371002b1c771749e930ce323dafa00b9ea3f2',
                    'location' => 'US',
                    'redirect_uri' => 'https://sso.garena.com/ui/login?locale=en-US&redirect_uri=https%3A%2F%2Fintl.garena.com%2F&app_id=10100&display=page',
                    'format' => 'json',
                    'id' => time()/1000,
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
                    'id' => time()/1000,
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
        echo "执行登录前的方法".PHP_EOL;

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
//        $response = $this->register_client->request('get', 'https://127.0.0.1:8888/?'.http_build_query($query));
        $response = $this->register_client->request('get', 'http://172.17.0.8:8888?'.http_build_query($query));
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
        echo "执行登录方法".PHP_EOL;
        //加密密码
        //http://127.0.0.1:8888/?password=1&v1=2&v2=3
        $password = $this->gener_password('123qwe123',$v1,$v2);

        $query = [
            'account' => $username,
//            'password' => 'db182980f822ceecd351d030767989f6',
            'password' => $password,
            'redirect_uri' => 'https://account.garena.com/',

            'format' => 'json',
            'id' => (string)$this->getMillisecond(),
            'app_id' => 10100,
        ];

        var_dump($query);

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
        echo "初始化登录中...".PHP_EOL;
        var_dump($login_info);
        sleep(3);
//        $url ="https://account.garena.com/?session_key=92fcba983ca7b3666674f5b5723db96fe96fb043d4e55c96bb9cbc35d1b8b3dd";
        $url = "https://account.garena.com/api/account/init?session_key=".$login_info['session_key'];
        echo $url.PHP_EOL;
//        $url = "https://account.garena.com/api/account/init?session_key=92fcba983ca7b3666674f5b5723db96fe96fb043d4e55c96bb9cbc35d1b8b3dd;
        $response = $this->register_client->request('get', $url);

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
        //登录前的操作
        //失败则获取验证码
        //解析验证码
        //登录账号
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

    public function email_apply($email,$password)
    {
        //登录
        //发送验证链接
    }

    public function reg_account($email)
    {
        //获取注册的用户名
        //获取邮箱
        //提交注册请求。
        //如果提示验证码。则获取验证码。
        //解析验证码
        //再次提交请求。

        $username_len = rand(7,15);
        $username = $this->generate_username($username_len);
//        $email = $this->generate_email(6);
        $reg_result = $this->reg($username,$email);

        if (!$reg_result){
            //获取验证码。
            $captcha_info = $this->gener_captcha();
            //解析验证码
            $captcha_text = $this->captcha_text($captcha_info['captcha_file']);
            //再次注册。还不行，则记录日志。
            $reg_result = $this->reg($username,$email,$captcha_text,$captcha_info['captcha_key']);
        }
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

        foreach ($emails as $email){
            $email =stristr($email,"----",true);
            $email = trim($email);
            echo $email.PHP_EOL;

            $this->reg_account($email); // 使用指定的邮箱。使用指定的密码注册。
            break;
        }

    }

    public function send_email_init()
    {
        //https://account.garena.com/api/account/verify_email/init
        echo "初始化邮箱中...".PHP_EOL;
        sleep(3);
//        $url ="https://account.garena.com/?session_key=92fcba983ca7b3666674f5b5723db96fe96fb043d4e55c96bb9cbc35d1b8b3dd";
        $url = "https://account.garena.com/api/account/verify_email/init";
        echo $url.PHP_EOL;
        $response = $this->register_client->request('get', $url);
        $code = $response->getStatusCode(); // 200
        $reason = $response->getReasonPhrase(); // OK
        $body = $response->getBody();
        echo $code.PHP_EOL;
        echo $reason.PHP_EOL;
        echo $body.PHP_EOL;
        return true;
        
    }

    public function send_email($email)
    {
        //发送邮件。https://account.garena.com/api/account/verify_email/apply  {"email":"540045865@qq.com","locale":"en"}
//        {"status":0,"next_action":{"max_retry":5,"verified_info":{"email":"ppag331278bc69af@sohu.com"},"action_type":12,"error_count":0}}
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
//        $response = $this->register_client->request('POST', 'https://account.garena.com/api/account/verify_email/apply', [
//            'form_params' => $data
//        ]);

        $code = $response->getStatusCode(); // 200
        $reason = $response->getReasonPhrase(); // OK
        $body = $response->getBody();

        echo $code.PHP_EOL;
        echo $reason.PHP_EOL;
        echo $body.PHP_EOL;

        $format_body = json_decode($body,true);

        //todo:需要改进判断
        if (is_array($format_body) && isset($format_body['status']) && $format_body['status'] == 0){

            return true;
        }else{
            return false;
        }
    }

    public function verify_email_before($username,$email)
    {
        //登录
        $login_result = $this->login_account($username);
        //发送邮件
        if ($login_result){
            $this->send_email_init();
            $this->send_email($email);
        }


    }



}


$register = new Register();

$register->login_email();

//$register->init_account();
//$register->reg_account(); //注册账号
//$register->login_account("rZJ5tQASWM"); //登录账号
//$register->login_account("hiphper321"); //登录账号
//$register->reg_accounts("sohu.txt"); //批量

//$register->verify_email_before("hiphper321",'ppag331278bc69af@sohu.com');



