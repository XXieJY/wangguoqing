<?php
namespace app\index\controller;
use think\Controller;
use think\Db;
class Fans extends Controller{
    //粉丝记录
    public function index($bookid,$num){

         $userId =cookie('shudong_user')['user_id'];
         $where =[
              'book_id'  =>$bookid,
              'user_id'  =>$userId
         ];
       $fans=  Db::name('BookFans')->where($where)->find();

       if(count($fans)==0){
           $data =[
               'book_id'   =>$bookid,
               'user_id'   =>$userId,
               'fan_value' =>$num,
               'time'       =>date('Y-m-d H:i:s')
           ];
          $result= Db::name('BookFans')->insert($data);
           return $result;


       }else{
          $up['fan_value'] =array('exp',"fan_value+$num");
          $result=  Db::name('BookFans')->where($where)->update($up);
          return $result;
       }

    }
}