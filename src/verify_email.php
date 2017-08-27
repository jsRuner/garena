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
    public $log_file;//记录注册后的账号信息。



    function __construct()
    {
        $headers =[
            'User-Agent'=>'Mozilla/5.0 (Macintosh; Intel Mac OS X 10_12_5) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/60.0.3112.101 Safari/537.36'
        ];
        $this->register_client = new Client(['cookies'=>true,"headers"=>$headers]);
        $this->log_file = dirname(dirname(__FILE__)).'/邮箱检查结果.txt';

    }



    /**
     * 批量检查邮件是否开启pop3
     */
    public function check_emails_pop3($emailfile)
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
            echo $email.PHP_EOL;

            $email_password = stristr($email_item,"----");
            $email_password = trim($email_password,"----");
            echo $email_password.PHP_EOL;

            if (empty($email) || empty($email_password)){
                continue;
            }
            $this->check_email_pop3($email,$email_password);
        }

    }


    public function check_email_pop3($email,$email_password)
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
            file_put_contents($this->log_file,date('Y-m-d H:i:s').PHP_EOL.'邮箱:'.$email.' 密码:'.$email_password.'没开启pop3或者账号密码错误'.PHP_EOL,FILE_APPEND);
            return false;
        }
        file_put_contents($this->log_file,date('Y-m-d H:i:s').PHP_EOL.'邮箱:'.$email.' 密码:'.$email_password.'可以使用'.PHP_EOL,FILE_APPEND);
        return true;
    }

    /**
     *模拟登录新浪邮箱
     *
     * @param $username
     * @param $v1
     * @param $v2
     * @return bool|mixed
     */
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




}


$register = new Register();


file_put_contents($register->log_file,PHP_EOL."------------------------------[".date('Y-m-d H:i:s')."开始执行]-----------------------------------------------------------".PHP_EOL,FILE_APPEND);
file_put_contents($register->log_file,PHP_EOL,FILE_APPEND);
file_put_contents($register->log_file,PHP_EOL,FILE_APPEND);



$register->check_emails_pop3(dirname(dirname(__FILE__))."/邮箱.txt"); //批量



file_put_contents($register->log_file,PHP_EOL,FILE_APPEND);
file_put_contents($register->log_file,PHP_EOL,FILE_APPEND);
file_put_contents($register->log_file,PHP_EOL."------------------------------[".date('Y-m-d H:i:s')."结束执行]-----------------------------------------------------------".PHP_EOL,FILE_APPEND);


