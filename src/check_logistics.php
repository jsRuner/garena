<?php
/**
 * Created by JetBrains PhpStorm.
 * User: 吴文付 hiwower@gmail.com
 * Date: 2017/11/8
 * Time: 上午9:04
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


$flag = file_exists("'vendor/autoload.php'");


if ($flag){
    require 'vendor/autoload.php'; //注意命令执行的位置。

}else{
    require '../vendor/autoload.php'; //注意命令执行的位置。

}



//require 'vendor/autoload.php'; //注意命令执行的位置。
use GuzzleHttp\Client;

class check_logistics
{

    private $http_client;

    private $infos;


    function __construct()
    {
        $headers = [
            'User-Agent' => 'Mozilla/5.0 (Macintosh; Intel Mac OS X 10_12_5) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/60.0.3112.101 Safari/537.36'
        ];
        $this->http_client = new Client(
            [
                'timeout' => 65,
                'cookies' => true,
                'headers' => $headers,
//                'proxy'=>$this->proxy
            ]
        );
        $this->infos = [];
        date_default_timezone_set("Asia/Shanghai");
    }

    public function readInfo()
    {
        if (file_exists("code.txt")){
            $handle = @fopen("code.txt", "r");
        }else{
            $handle = @fopen("../code.txt", "r");
        }

        if ($handle) {
            while (!feof($handle)) {
                $buffer = trim(fgets($handle, 4096));
                //跳过空白
                if (empty($buffer)){
                    continue;
                }
                $info = explode("----", $buffer);
                $this->infos[] = $info;
//                var_dump($info);
            }
            fclose($handle);
        }
    }

    public function writeInfo()
    {
        $result = "";
        foreach ($this->infos as $info) {
            $result .= implode("----", $info);
            //win 则\r\n 否则\r
            $result .= strtoupper(substr(PHP_OS,0,3))==='WIN'?'\r\n':'\r';
        }

        $file = strtoupper(substr(PHP_OS,0,3))==='WIN'?'result.txt':'../result.txt';

        file_put_contents("result.txt", $result);
    }

    public function checkMore()
    {
        foreach ($this->infos as &$info) {
            $result = $this->check(trim($info[2]), trim($info[3]));
            $info[] = trim($result);
            sleep(3); //3秒一次。
        }
    }
    function time_tran($the_time)
    {
        $now_time = time();
        $show_time = strtotime($the_time);
        $dur = $now_time - $show_time;
        if ($dur < 0) {
            return 0;
        } else {
            if ($dur < 60) {
                return $dur . '秒前';
            } else {
                if ($dur < 60 * 60) {
                    return floor($dur / 60) . '分钟前';
                } else {
                    if ($dur < 60 * 60 * 24) {
                        return floor($dur / 3600) . '小时前';
                    } else {
                        if ($dur < 60 * 60 * 24 * 365) {//365天内
                            return floor($dur / 86400) . '天前';
                        } else {
                            return "时间太久";
                        }
                    }
                }
            }
        }
    }
    //快递编号 shunfeng  youzhengguonei  yunda  yuantong  shentong  zhongtong
    //https://cdn.kuaidi100.com/js/share/query_v4.js?version=201708181710  执行查询的Js
    public function check($type, $postid)
    {

        switch ($type) {
            case "顺丰":
                $type = "shunfeng";
                break;
            case "邮政国内":
                $type = "youzhengguonei";
                break;
            case "韵达":
                $type = "yunda";
                break;
            case "圆通":
                $type = "yuantong";
                break;

            case "申通":
                $type = "shentong";
                break;
            case "中通":
                $type = "zhongtong";
                break;
            default:
                $type = "shunfeng";
        }
        $params = [
            "type" => $type,
            "postid" => $postid,
            "id" => 19,
            "validcode" => "",
            "tmp" => rand(1, 10) / 10
        ];
        $url = "https://www.kuaidi100.com/query?" . http_build_query($params);
        $response = $this->http_client->request("get", $url);
        $body = $response->getBody();
        $formatResult = json_decode($body, true);
        var_dump($formatResult);
        $info = "";
        //接口是否正常。
        if (!isset($formatResult['status']) || $formatResult['status'] != 200) {
            //接口异常。输出信息。退出执行。
            $info = isset($formatResult['message']) ? $formatResult['message'] : "接口异常";
        } else {
            //有物流信息的。
            if (isset($formatResult['data']) && is_array($formatResult['data']) && count($formatResult['data'] >= 1)) {
                //获取日期。
                $lastDate = $formatResult['data'][0]['time'];
                echo $lastDate;
                $info = $this->time_tran($lastDate);
            } else {
                $info = "没物流";
            }
        }
        return $info;
    }

}
$check_logistics = new check_logistics();
$check_logistics->readInfo();
$check_logistics->checkMore();
$check_logistics->writeInfo();
