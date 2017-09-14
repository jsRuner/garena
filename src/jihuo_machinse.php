<?php
/**
 * Created by JetBrains PhpStorm.
 * User: 吴文付 hiwower@gmail.com
 * Date: 2017/9/1
 * Time: 下午2:06
 * description:激活账号。
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
use Nette\Mail\Message;



const API_EMAIL_LOGIN = "https://tempr.email/";
const API_ACCOUNT_EMAIL_VERIFY = "https://account.garena.com/ui/account/email/verify";

class  Jihuo_machinse {
    private $account; //是数组。非对象。
    private $db;
    private $http_client;
    private $debug;

    private $send_flag;

    public function __construct()
    {
        $this->db =  new \Workerman\MySQL\Connection(
            "rm-uf6m0ljyp35io68r5o.mysql.rds.aliyuncs.com",
            "3306", "garena",
            "bynxe0YET27QBDlK",
            "garena");
        $headers =[
            'User-Agent'=>'Mozilla/5.0 (Macintosh; Intel Mac OS X 10_12_5) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/60.0.3112.101 Safari/537.36'
        ];
        $this->http_client = new Client(
            [
                'timeout'=>20,
                'cookies'=>true,
                'headers'=>$headers
            ]
        );
        $this->debug = true;

        $this->send_flag = true; // 默认正常发送邮件。

    }

    //0是待激活 1是激活成功。2表示邮件发送。
    public function jihuo_account()
    {
        $this->send_account();
        $result = $this->get_account();
        if ($result){
            $this->login_email();
        }

    }

    public function send_account()
    {
        $sql = "select `id`,`username`,`passwd`,`email` from `account` WHERE `status`= 1 ";
        if ($this->debug){
            echo $sql.PHP_EOL;
        }
        $result = $this->db->query($sql);
        $num = count($result);
        if ($result && $num >=100 ){
            //todo：发送邮件。
            $body = '';
            $ids = '';
            foreach ($result as $item){
                $body = $body.'账号:'.$item['username'].'密码:'.$item['passwd'].PHP_EOL;
                $ids = $ids.$item['id'].',';
            }
            $ids = trim($ids,',');




            $mail = new Message;
            $mail->setFrom('hi_php@163.com')
                ->addTo('doudouchidou@yeah.net')
                ->addTo('zhangxiaoquan7777@163.com')
                ->setSubject(date('Y-m-d-H-i-s') . "账号" . $num . "条")
                ->setBody($body);

            $mailer = new Nette\Mail\SmtpMailer([
                'host' => 'smtp.163.com',
                'username' => 'hi_php@163.com',
                'password' => 'xxoo123',
//                'secure' => 'ssl', //使用465端口发送邮件。
            ]);

            try{

                $mailer->send($mail);
            }catch (Exception $e){
                $this->send_flag = false;
            }

            if ($this->send_flag){
                $sql = "update `account` set `status`=2 WHERE  `id` in (".$ids.")";
                $this->db->query($sql);
            }





            return true;
        }else{
            echo 'no account to send '.PHP_EOL;
            $this->account = false;
            return false;
        }
    }

    public function get_account()
    {
        $reg_date = date('Y-m-d H:i:s',time()-60*30);//todo:需要修改为30
        $sql = "select `email` from `account` WHERE `status`= 0  order by `id` desc LIMIT 10   ";
//        $sql = "select `email` from `account` WHERE `status`= 0 AND `reg_date` > '".$reg_date. "' order by `id` desc LIMIT 10   ";
        if ($this->debug){
            echo $sql.PHP_EOL;
        }
        $result = $this->db->query($sql);
        if ($result){
            $rand_key = array_rand($result);
            $this->account = $result[$rand_key];
            return true;
        }else{
            echo 'no account'.PHP_EOL;
            $this->account = false;
            return false;
        }
    }


    public function login_email()
    {
        //登录邮箱
        //获取邮件
        //访问邮件内容
        //激活链接
        $email = $this->account['email'];
        if ($this->debug){
            echo $email.PHP_EOL;
        }
        $prefix = strstr($email,'@',true);
        $suffix = strstr($email,'@');
        $suffix = trim($suffix,'@');
        $form_params = [
            'LocalPart' => $prefix,
            'DomainId' => $suffix,
            'DomainType' => 'public',
            'DomainId'=>1,
            'PrivateDomain'=>'',
            'Password'=>'',
            'LoginButton'=>'Postfach abrufen',
            'CopyAndPaste'=>$email
        ];
        $response = $this->http_client->request('POST', API_EMAIL_LOGIN, [
            'form_params' => $form_params
        ]);
        $body = $response->getBody();
        //匹配邮件链接。
        preg_match_all('/<a href="(.*?)">.*?Verify Your Garena Account Email Address.*?<\/a>/',$body,$arr);
        if ($this->debug){
            var_dump($arr);
        }
        //访问指定邮件。
        if (!isset($arr[1][0])){
            return false;
        }
        $url = $arr[1][0];
        $this->http_client->request('GET', $url);
        $url = str_replace('.htm','',$url);
        $url = str_replace('https://tempr.email/message','',$url);
        $url = "https://tempr.email/public/messages/getHtmlMessage.php?file=htmlMessage".$url."_UTF-8.htm";
        echo $url.PHP_EOL;

        $response = $this->http_client->request('GET', $url);
        $body = $response->getBody();

        if ($this->debug){
            echo $body.PHP_EOL;
        }

        //提取激活链接
        preg_match_all('/href="https:\/\/account.garena.com\/ui\/account\/email\/verify(.*?)" style=/i',$body,$arr);
        if ($this->debug){
            var_dump($arr);
        }

        if (!isset($arr[1][0])){
            return false;
        }

        $url = API_ACCOUNT_EMAIL_VERIFY.$arr[1][0];
        try{
            $response = $this->http_client->request('get',$url);
        }catch (Exception  $e){
            $this->db->delete('account')->where('email=\''.$email.'\'')->query();
        }
        //激活成功。
        $this->db->update('account')->cols(array('status'=>1,'jihuo_date'=>date('Y-m-d H:i:s')))->where('email=\''.$email.'\'')->query();
    }
}

//
$jihuo_machinse = new Jihuo_machinse();
//
//
while(true){
    try{

        $jihuo_machinse->jihuo_account();
    }catch (Exception $e){

    }
    sleep(1);
    echo '--------------------------'.PHP_EOL;
}

