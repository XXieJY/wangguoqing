<?php
namespace app\api\controller;
use think\Controller;
use think\Db;
class Register extends Controller{

   public function check(){

       $email =input('post.email');
       $info= Db::name('Writer')->where(['email'=>$email,'uid'=>0])->find();
       if(is_array($info)){

           if($info['is_agree']==0){
               //作者处于签署协议状态;
               return [
                   'code' =>1,
                   'id'   =>$info['author_id']
               ];
           }elseif ($info['is_agree']==2 && $info['pen_name']=="" && $info['user_name']=="" && $info['card']=="" && $info['qq']==""){
               return [
                   'code'   =>2,
                   'id'     =>$info['author_id']
               ];
           }

       }else{
           return 0;
       }
   }
   public function delete(){

       $id =input('post.id');
      $author= Db::name('Writer')->where(['author_id'=>$id,'uid'=>0])->find();
       $ok=\think\Loader::controller('gongju/User')->delete($id,$author['user_id']);
       if($ok){
           return 1;
       }else{
           return 0;
       }
   }
}