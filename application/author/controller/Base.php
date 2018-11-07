<?php
namespace app\author\controller;
use think\Controller;
use think\Db;
//作者权限验证
class Base extends Controller{
    public $account;

    public function _initialize() {

        // 判定用户是否登录
        $isLogin = $this->isLogin();
        if(!$isLogin) {
            return $this->redirect(url('Login/index'));
        }

        $email=  $this->is_email();

        $this->assign('user',$this->account);
        $this->assign('e_info',$email);
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
            $this->account = session('info', '', 'shudong_info');
        }
        return $this->account;
    }
   //判断作者权限
    public function shell($bookid){

        $info=$this->account;
       $shell=  Db::name('Book')->where(['author_id'=>$info['author_id'],'book_id'=>$bookid])->find();
       if(is_array($shell)){
           return true;
       }else{
           return false;
       }
    }
    //判断是否有未读邮件
    public function  is_email(){

      $email=  Db::name('WriterEmail')->where(['author_id'=>$this->account['author_id'],'is_show'=>0])->find();

      if(is_array($email)){
          return true;
      }else{
          return false;
      }
    }
}