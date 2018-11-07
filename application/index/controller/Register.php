<?php
namespace app\index\controller;
use think\Controller;
use think\Db;
use think\Request;
use app\author\controller\Redis;
class Register extends Controller{

    public function index(){

        return $this->fetch();
    }

    public function register(){

        $res =input('post.');
        if(!$this->isPhone($res['phone'])){
            $this->error('手机号不合法');
        }
        if(!$this->password($res['passWord'])){
            $this->error('密码只能是数字字母');
        }
        if($res['passWord']!=$res['confirmWord']){
            $this->error('两次输入的密码不一致');
        }
        if(!$this->check_code($res['phone'],$res['code'])){
            $this->error('验证码不正确');
        }
       $result= Db::name('User')->where(['phone'=>$res['phone']])->find();
        if($result){
            $this->error('该手机号已注册过');
        }
        $redis =Redis::getRedisConn();
        $redis->incr('USER:COUNT');
        $index = $redis->get('USER:COUNT');
        $number=600000000+$index;
        $code =$this->pridCode();
        $data =[

            'phone' =>$res['phone'],
            'user_pass' =>md5(md5($res['passWord'])),
            'pen_name'  =>$res['penName'],
            'user_name' =>'咚者'.$number,
            'is_tourist' =>0,
            'sex'      =>$res['sex']==0?'1':'0',
            'login_ip'=>request()->ip(),
            'integral' =>50,
            'dobing'   =>50,
            'mem_vip'   =>2,
            'create_time'=>date('Y-m-d H:i:s'),
            'update_time'=>date('Y-m-d H:i:s'),
             'sign_time' =>date('Y-m-d H:i:s'),
            'login_time'=>date('Y-m-d H:i:s'),
            'device_info'  =>"pc登陆ip:".request()->ip(),
            'code'        =>$code
        ];
       $ok= Db::name('User')->insert($data);
       if($ok){
           $this->redirect(url('/register/ok'));
       }else{
           $this->error('注册失败');
       }

    }


    //验证手机的合法性
    private function isPhone($phone){
        if (!is_numeric($phone)) {
            return false;
        }
        return preg_match('/^1[3|4|5|6|7|8|9][0-9]\d{4,8}$/', $phone) ? true : false;

    }
    //验证密码的合法性
    private function password($pwd){

        return preg_match('/^[A-Za-z0-9]+$/', $pwd) ? true : false;

    }

    //判断验证码
    private function check_code($phone,$code){
        $num =session("code".$phone,'','shudong_code');
        return $num==$code? true : false;
    }

    //随机生成推广码
    private function pridCode(){
        $str="abcdefghijklmnopqrstuvwxyz0123456789";
        $length =strlen($str)-1;//获取字符串长度

        $code="";
        for ($i=0;$i<6;$i++){
            $strat =rand(0,$length);//随机截取字符串的开始位置
            $code.=substr($str,$strat,1);
        }

        return $code;


    }

    //注册成功界面
    public function ok(){

        return $this->fetch();
    }


    //忘记密码
    public function forget(){

        return $this->fetch();
    }

    //修改密码并登陆
    public function signLogin(){

        $res =input('post.');
        $url =empty($res['url'])?"/index.html":$res['url'];
        //print_r($res);exit();
        if(!$this->isPhone($res['phone'])){
            $this->error('手机号不合法');
        }
        if(!$this->password($res['passWord'])){
            $this->error('密码只能是数字字母');
        }
        if($res['passWord']!=$res['cofirmWord']){
            $this->error('两次输入的密码不一致');
        }
        if(!$this->check_code($res['phone'],$res['code'])){
            $this->error('验证码不正确');
        }
       $result= Db::name('User')->where(['phone'=>$res['phone']])->update(['user_pass'=>md5(md5($res['passWord'])),'update_time'=>date('Y-m-d H:i:s')]);
        if($result){
            //登陆
          $user=  Db::name('User')->where(['phone'=>$res['phone']])->find();

           $this->getUserInfo($user['user_id']);
            $data['login_time']  =date('Y-m-d H:i:s');
            $data['login_ip'] =request()->ip();
            Db::name('User')->where(['user_id'=>$user['user_id']])->update($data);
            $this->redirect($url);

        }else{

            $this->error('修改失败');
        }

    }

    private function getUserInfo($userid){

        $user =Db::name('User')->where(['user_id'=>$userid])->find();
        cookie('shudong_user',null);
        $user['bookCount']= Db::name('BookCollection')->where(['user_id'=>$userid])->field('book_id')->count();
        $user['newCount'] =Db::name('UserMessage')->where(['user_id'=>$userid,'state'=>0,'type'=>0])->count();
        if(strlen($user['portrait'])<60){
            if ($user['portrait']=="user/portrait/portrait.jpg"){

                $user['portrait']="http://images.shuddd.com/user/portrait/portrait".$user['sex'].".png";

            }else{
                $user['portrait']="http://images.shuddd.com/".$user['portrait'];
            }
        }
        cookie('shudong_user',$user,86400);
    }
}