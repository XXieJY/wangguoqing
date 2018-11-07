<?php
namespace app\author\controller;
use think\Controller;
use think\Db;
use think\Request;
class Writer extends Base {

    public function index(){

        return $this->fetch();
    }

    public function update(){

        if(!request()->isPost()){
            $this->error('系统错误');
        }
        $ids=$this->account;
        $data =input('post.');
        //校验旧密码
       $re1= Db::name('Writer')->where(['pass_word'=>md5($data['oldpwd'].config('author.ALL_ps'))])->find();
       if(!is_array($re1)){
           $this->error('旧密码输入有误');
       }
       if(!$this->is_password($data['newpwd'])){
           $this->error('密码必须包括大写字母小写字母数字特殊字符且长度大于8位');
       }

       $re2= Db::name('Writer')->where(['author_id'=>$ids['author_id']])->update(['pass_word'=>md5($data['newpwd'].config('author.ALL_ps'))]);
       if($re2){
           $this->success('密码修改成功，请重新登录','Login/logout');
       }else{
           $this->error('尴尬！系统出错了');
       }
    }

    //验证密码的强度
    private function is_password($password){
        return  preg_match('/^.*(?=.{8,16})(?=.*\d)(?=.*[A-Z])(?=.*[a-z])(?=.*[!+@#%^&*()_-]).*$/',$password)? true :false;
    }

    //作者信息
    public function info(){
        $Ids=$this->account;
      $author=  Db::name('Writer')->where(['author_id'=>$Ids['author_id']])->find();
        return $this->fetch('',[
            'author'   =>$author
        ]);
    }

    //修改作者信息
    public function reInfo(){
        $author=$this->account;
        if(!request()->isPost()){
            $this->error('系统错误');
        }
        $data =input('post.');

       if($data['GP']=="-省-"){
          $this->error('请选择省份地址');
       }
        $con['pen_name']   =$data['uname'];
        $con['user_name']  =$data['name'];
        $con['sex']=$data['sex']=="男"? '1':'0';
        $con['qq'] =$data['qq'];
        $con['phone']  =$data['realPhone'];
        $con['email']=  $data['email'];
        $con['address'] =$data['GP'].$data['GC'].$data['GT'].$data['address'];
        $con['sign']  =$data['sign'];
        $con['update_time']=date('Y-m-d H:i:s');
      $re=  Db::name('Writer')->where(['author_id'=>$author['author_id']])->update($con);
      if($re){
         $re1= Db::name('User')->where(['user_id'=>$author['user_id']])->update(['sex'=>$con['sex']]);
         $this->success('修改成功');
      }else{
          $this->error('修改失败');
      }
    }

    //银行账号信息
    public function bank(){
        $author=$this->account;
        if(request()->isPost()){

            $data=input('post.');
            if($data['GP']=="-省-"){
                $this->error('请选择省份地址');
            }
            $con['user_name']  =$data['user_name'];
            $con['bank']   =$data['bank'];
            $con['bank_number']  =$data['bankId'];
            $con['bank_open'] =$data['GP'].$data['GC'].$data['GT'].$data['bankName'];
          $re=  Db::name('Writer')->where(['author_id'=>$author['author_id']])->update($con);

          if($re){
              $this->success('信息修改成功');
          }else{
              $this->error('信息修改失败');
          }

        }else{

            $info=Db::name('Writer')->where(['author_id'=>$author['author_id']])->find();
            return $this->fetch('',[
                'info'    =>$info
            ]);
        }

    }

    //读者绑定
    public function user(){
         $author =$this->account;
       $info=  Db::name('User')->where(['user_id'=>$author['user_id']])->find();

        return $this->fetch('',[
            'info'   =>$info
        ]);
    }
}