<?php
namespace app\author\controller;
use think\Controller;
use think\Db;
use think\Request;
class Login extends Controller{

    public function index(){

        return $this->fetch();
    }

    public function login(){

        if(!request()->isPost()){
            $this->error('系统错误');
        }
        $data=input('post.');

        //验证邮箱格式
        if(!preg_match('/^(\w-*\.*)+@(\w-?)+(\.\w{2,})+$/',$data['email'])){
            $this->error('邮箱格式不合法');
        }
        //验证密码的强度
        if(!preg_match('/^.*(?=.{8,16})(?=.*\d)(?=.*[A-Z])(?=.*[a-z])(?=.*[!+@#%^&*()_-]).*$/',$data['password'])){
            $this->error('密码必须包含大写字母小写字母特殊字符数字且长度必须大于8');
        }
        $info= model('Writer')->getInfo($data);

        if($info){
            //验证作者权限
            if($info->uid==1){
                session('info',$info,'shudong_info');
               $this->success('登陆成功',url('Inform/notice'));

            }else{
                $this->error('作者权限还没有激活');
            }
        }else{

            $this->error('账号或密码错误');
        }
    }
 //忘记密码
    public function forget(){
       if(request()->isPost()){
           $data =input('post.');
           //验证邮箱格式
           if(!preg_match('/^(\w-*\.*)+@(\w-?)+(\.\w{2,})+$/',$data['email'])){
               $this->error('邮箱格式不合法');
           }
           //验证密码的强度
           if(!preg_match('/^.*(?=.{8,16})(?=.*\d)(?=.*[A-Z])(?=.*[a-z])(?=.*[!+@#%^&*()_-]).*$/',$data['password'])){
               $this->error('密码必须包含大写字母小写字母特殊字符数字且长度必须大于8');
           }

           if(!$this->check_code($data['phone'],$data['code'])){
               $this->error('验证码不正确');
           }
           if($data['password']!=$data['repassword']){
               $this->error('两次输入的密码不一样');
           }

          $result= Db::name('Writer')->where(['email'=>$data['email'],'phone'=>$data['phone']])->find();
           if(!is_array($result)){
               $this->error('注册的邮箱和手机号不一致');
           }
          $ok= Db::name('Writer')->where(['email'=>$data['email'],'phone'=>$data['phone']])->update(['pass_word'=>md5($data['password'].config('author.ALL_ps'))]);
           if($ok){
//               session("code".$data['phone'],null,'shudong_code');
               $this->success('密码修改成功',url('Login/index'));
           }else{
               $this->error('密码修改失败');
           }

       }else{
           return $this->fetch();

       }

    }


    //判断验证码
    private function check_code($phone,$code){
        $num =session("code".$phone,'','shudong_code');
        return $num==$code? true : false;
    }


  //退出
    public function logout(){

        session('info',null,'shudong_info');

        $this->redirect('Login/index');
    }
}