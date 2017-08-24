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


    function __construct()
    {
//        $jar = new \GuzzleHttp\Cookie\CookieJar;
        $this->register_client = new Client(['cookies'=>true]);
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
//        $pop3 = new \Pop3\Connection("pop.qq.com", "doudouchidou@qq.com", "foxovsofjgllbbgc");
//        $pop3 = new afinogen89\getmail\protocol\Pop3('pop.qq.com');
//
//        $pop3->login('doudouchidou@qq.com', 'foxovsofjgllbbgc');
//        $msgList = $pop3->getList();
////        var_dump($msgList);
//        $pop3->logout();
//
//        arsort($msgList);



        $storage = new afinogen89\getmail\storage\Pop3(['host' => 'pop.qq.com', 'user' => 'doudouchidou@qq.com', 'password' => 'foxovsofjgllbbgc']);
//        foreach ($msgList as $msgindex){

        $num = $storage->countMessages();

            $msg = $storage->getMessage($num);
            $msg->saveToFile('1.eml');
            echo $msg->getHeaders()->getSubject();

            foreach($msg->getParts() as $part) {
                echo $part->getContentDecode().PHP_EOL;
            }

            sleep(5);
//        }
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
                    'redirect_uri' => 'https://sso.garena.com/ui/login?locale=en-US&redirect_uri=https%3A%2F%2Fintl.garena.com%2F&app_id=10000&display=page',
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
                    'redirect_uri' => 'https://sso.garena.com/ui/login?locale=en-US&redirect_uri=https%3A%2F%2Fintl.garena.com%2F&app_id=10000&display=page',
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
                    'redirect_uri' => 'https://sso.garena.com/ui/login?locale=en-US&redirect_uri=https%3A%2F%2Fintl.garena.com%2F&app_id=10000&display=page',
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
                'id' => time()*1000,
                'app_id' => 10000,

                'captcha'=>$captcha_text,
                'captcha_key'=>$captcha_key
            ];
            $response = $this->register_client->request('get', 'https://sso.garena.com/api/prelogin?'.http_build_query($query));
        }else{

            $query = [
                'account' => $username,

                'format' => 'json',
                'id' => time()*1000,
                'app_id' => 10000,
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

    public function login($username,$v1,$v2)
    {
        echo "执行登录方法".PHP_EOL;
        //加密密码
        $password = '123qwe123';
        $passwordMd5 = MD5($password);
        $passwordKey = hash("sha256",hash("sha256",$passwordMd5.$v1).$v2);

        $key = $passwordKey;

        $key = pack('H*', $key);
//        $key = pack('H*', "bcb04b7e103a0cd8b54763051cef08bc55abe029fdebae5e1d417e2ffb2a00a3");


        $key_size =  strlen($key);
        echo "Key size: " . $key_size . "\n";

        $plaintext = $password;
//        $plaintext = "This string was AES-256 / CBC / ZeroBytePadding encrypted.";
        # 为 CBC 模式创建随机的初始向量
        $iv_size = mcrypt_get_iv_size(MCRYPT_RIJNDAEL_128, MCRYPT_MODE_CBC);
        $iv = mcrypt_create_iv($iv_size, MCRYPT_RAND);

        # 创建和 AES 兼容的密文（Rijndael 分组大小 = 128）
        # 仅适用于编码后的输入不是以 00h 结尾的
        # （因为默认是使用 0 来补齐数据）
        $ciphertext = mcrypt_encrypt(MCRYPT_RIJNDAEL_128, $key,
            $plaintext, MCRYPT_MODE_CBC, $iv);

        # 将初始向量附加在密文之后，以供解密时使用
//        $ciphertext = $iv . $ciphertext;

        # 对密文进行 base64 编码
//        $ciphertext_base64 = base64_encode($ciphertext);

        $ciphertext = base64_decode($ciphertext);

        $ciphertext = pack('H*',$ciphertext);

        $query = [
            'account' => $username,
//            'password' => 'db182980f822ceecd351d030767989f6',
            'password' => $ciphertext,
            'redirect_uri' => 'https://intl.garena.com/',

            'format' => 'json',
            'id' => time()*1000,
            'app_id' => 10000,
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
            return true;
        }

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
            //再次注册。还不行，则记录日志。
            $reg_result = $this->pre_login($username,$captcha_text,$captcha_info['captcha_key']);

//            if ($reg_result){
//            }
        }
        $this->login($username,$reg_result['v1'],$reg_result['v2']);


    }

    public function email_apply($email,$password)
    {
        //登录
        //发送验证链接
    }

    public function reg_account()
    {
        //获取注册的用户名
        //获取邮箱
        //提交注册请求。
        //如果提示验证码。则获取验证码。
        //解析验证码
        //再次提交请求。

        $username_len = rand(7,15);
        $username = $this->generate_username($username_len);
        $email = $this->generate_email(6);
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




}


$register = new Register();
//$register->reg_account();

//$register->pre_login("rZJ5tQASWM");
$register->login_account("rZJ5tQASWM");

//$register->login('rZJ5tQASWM','1321','2321');

exit();
















//$register->login_email();
$register->captcha_text('captchas/599c4fea10d20.jpg');

exit();

for ($i=1;$i<=10;$i++){

    $register->gener_captcha();
}

exit();

$username_len = rand(7,15);
$username = $register->generate_username($username_len);

$email = $register->generate_email(6);

$register->reg($username,$email);
