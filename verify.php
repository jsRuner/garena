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
    function __construct()
    {
        $headers =[
            'User-Agent'=>'Mozilla/5.0 (Macintosh; Intel Mac OS X 10_12_5) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/60.0.3112.101 Safari/537.36'
        ];
        $this->register_client = new Client(['cookies'=>true,"headers"=>$headers]);

        //数据库
        $db = new Sqlite(realpath('.').'/garena.db');
        $this->db = $db;
        $this->log_file = date('YmdHis').'已经进行邮箱验证的账号.txt';

    }

    //qq邮箱 网易邮箱 sohu邮箱 新浪邮箱。
    public function login_email($username,$password,$email,$email_password,$reg_time)
    {
        //提取邮箱的后缀。
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

        }catch (Exception $e){
            print_r($e->getMessage());
            echo PHP_EOL;
            return false;
        }

        //只遍历最新的2份邮件。
        if ($num >2){
            $num = 2;
        }

        for ($mid=1;$mid<=$num;$mid++){
            $msg = $storage->getMessage($mid); //倒序。1表示最新的。
            $subject =  $msg->getHeaders()->getSubject();
            $email_date = $msg->getHeaders()->getDate();
            $email_from = $msg->getHeaders()->getFrom();

            //比较时间。与注册的日期比较。
            $email_time = strtotime($email_date);
            $reg_time = strtotime($reg_time);

            //超过30分钟则直接跳过。
            if ( ($email_time - $reg_time) >= 60*30){
                continue;
            }
            echo $subject.PHP_EOL;
            echo $email_date.PHP_EOL;
            echo $email_from.PHP_EOL;

            if ($subject == "Verify Your Garena Account Email Address" && $email_from == "account@garena.com"){
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
                        $response = $this->register_client->request('get', $url,['connect_timeout' => 8,]);
                        $code = $response->getStatusCode(); // 200
                        $reason = $response->getReasonPhrase(); // OK
                        $body = $response->getBody();
                        echo $code.PHP_EOL;
                        echo $reason.PHP_EOL;
                        echo $body.PHP_EOL;


                    }  catch (\Exception $e) {
                        print $e->getMessage();
                        //todo:记录日志。验证失败。
                        return false;
                    }
                    //更新状态。
                    $data = [$email=>[
                        'status'=>2,
                        'verify_time'=>date('Y-m-d H:i:s')
                    ]];
                    $this->db->update('account','email',$data);
                    file_put_contents($this->log_file,date('Y-m-d H:i:s').PHP_EOL.' 账号:'.$username.' 密码:123qwe123 邮箱:'.$email.PHP_EOL,FILE_APPEND);
                    return true;
                }
                break;
            }
        }
    }

    public function send_email_init()
    {
        sleep(2);
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

        //todo:需要改进判断
        if (is_array($format_body) && isset($format_body['status']) && $format_body['status'] == 0){
            //发送邮件成功。需要更新状态。
            $where = ['email'=>$email];
            $data = ['status'=>1];
            $this->db->update('account',$where,$data);
            return true;
        }else{
            return false;
        }
    }



    /**
     * 批量邮箱。去验证账号。
     * todo:需要增加代理。否则GG。
     */
    public function login_emails()
    {
        $rows = $this->db->all('SELECT `username`,`password`,`email`,`email_password`,`reg_time` FROM account where status = 0');
        if (empty($rows)){
            echo 'no account to login'.PHP_EOL;
            sleep(2);
            return;
        }
        foreach ($rows as $row){
            list($username,$password,$email,$email_password,$reg_time) = $row;

            if (empty($email) || empty($email_password)){
                continue;
            }

            echo $email.PHP_EOL;
            echo $email_password.PHP_EOL;

            $temp = strtotime($reg_time);
            if ((time() - $temp) <= 60*15){
                echo "waiting 15 min to login email".PHP_EOL;
            }else{
                $this->login_email($username,$password,$email,$email_password,$reg_time);
            }
            sleep(1);
        }

    }
}

$register = new Register();

while (true){
    try{
        $register->login_emails();
    }catch (Exception $e){
        echo $e->getMessage();
        echo PHP_EOL;
    }
}



