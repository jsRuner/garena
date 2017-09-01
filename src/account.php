<?php
/**
 * Created by JetBrains PhpStorm.
 * User: 吴文付 hiwower@gmail.com
 * Date: 2017/9/1
 * Time: 上午10:28
 * description:账号本身。
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

Class Account{
    private $username;
    private $passwd;
    private $email;
    private $reg_date;
    private $jihuo_date;

    function __construct()
    {
        $this->reg_date = date('Y-m-d H:i:s');
    }

    /**
     * @return mixed
     */
    public function getUsername()
    {
        return $this->username;
    }

    /**
     * @param mixed $username
     */
    public function setUsername($username)
    {
        $this->username = $username;
    }

    /**
     * @return mixed
     */
    public function getPasswd()
    {
        return $this->passwd;
    }

    /**
     * @param mixed $passwd
     */
    public function setPasswd($passwd)
    {
        $this->passwd = $passwd;
    }

    /**
     * @return mixed
     */
    public function getEmail()
    {
        return $this->email;
    }

    /**
     * @param mixed $email
     */
    public function setEmail($email)
    {
        $this->email = $email;
    }

    /**
     * @return mixed
     */
    public function getRegDate()
    {
        return $this->reg_date;
    }

    /**
     * @param mixed $reg_date
     */
    public function setRegDate($reg_date)
    {
        $this->reg_date = $reg_date;
    }

    /**
     * @return mixed
     */
    public function getJihuoDate()
    {
        return $this->jihuo_date;
    }

    /**
     * @param mixed $jihuo_date
     */
    public function setJihuoDate($jihuo_date)
    {
        $this->jihuo_date = $jihuo_date;
    }
}

