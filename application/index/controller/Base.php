<?php
namespace app\index\controller;
use think\Controller;
use think\Db;
class Base extends Controller{

    public $account;
    public function _initialize() {
        // 判定用户是否登录
        $isLogin = $this->isLogin();
        if(!$isLogin) {
            return $this->redirect(url('/login'));
        }

    }

    //判定是否登录
    public function isLogin() {
        // 获取sesssion
        $user = $this->getLoginUser();
        if($user) {
            return true;
        }
        return false;

    }

    public function getLoginUser() {
        if(!$this->account) {
            $this->account = cookie('shudong_user');
        }
        return $this->account;
    }


    /*
      * 我的关注
      */
    public function myFocusUser(){

        $user=  Db::view('UserFocus','user_id')
            ->view('User','pen_name,portrait,days,sex','User.user_id=UserFocus.user_id')
            ->where(['UserFocus.beUser_id'=>cookie('shudong_user')['user_id']])
            ->select();
        foreach ($user as $k=>$v){
            $user[$k]['mTime'] =$this->getMessgae($v['user_id']);
            if(strlen( $user[$k]['portrait'])<60){

                if($user[$k]['portrait']=="user/portrait/portrait.jpg"){

                    $user[$k]['portrait']="http://images.shuddd.com/user/portrait/portrait".$user[$k]['sex'].".png";

                }else{
                    $user[$k]['portrait']="http://images.shuddd.com/".$user[$k]['portrait'];
                }
            }
        }
        return $user;

    }


    /*
     * 我的粉丝
     */
    public function myFans(){

        $user=  Db::view('UserFocus','beUser_id')
            ->view('User','pen_name,portrait,days,sex','User.user_id=UserFocus.beUser_id')
            ->where(['UserFocus.user_id'=>cookie('shudong_user')['user_id']])
            ->select();
        foreach ($user as $k=>$v){
            $user[$k]['mTime'] =$this->getMessgae($v['beUser_id']);
            if(strlen( $user[$k]['portrait'])<60){

                if($user[$k]['portrait']=="user/portrait/portrait.jpg"){

                    $user[$k]['portrait']="http://images.shuddd.com/user/portrait/portrait".$user[$k]['sex'].".png";

                }else{
                    $user[$k]['portrait']="http://images.shuddd.com/".$user[$k]['portrait'];
                }
            }
        }
        return $user;
    }

    /*
       * 获取用户最近发表的一条评论
       */
    public function getMessgae($userId){

        $message= Db::name('BookMessage')->where(['user_id'=>$userId,'f_id'=>0])->field('time')->order('time desc')->find();
        if($message['time']){
            return $this->getTime($message['time']);
        }else{

            return $message['time'];
        }
    }

    private  function getTime($time)
    {

        //获取今天凌晨的时间戳
        $day = strtotime(date('Y-m-d 00:00:00'));
        //获取昨天凌晨的时间戳
        $pday = strtotime(date('Y-m-d 00:00:00',strtotime('-1 day')));
        //获取现在的时间戳
        $nowtime = strtotime(date('Y-m-d H:i:s'));

        $tc = $nowtime-strtotime($time);
        if(strtotime($time)<$pday){
            $str = date('m-d',strtotime($time));
        }elseif(strtotime($time)<$day && strtotime($time)>$pday){
            $str = "昨天";
        }elseif($tc>60*60){
            $str = floor($tc/(60*60))."小时前";
        }elseif($tc>60){
            $str = floor($tc/60)."分钟前";
        }else{
            $str = "刚刚";
        }
        return $str;
    }



}