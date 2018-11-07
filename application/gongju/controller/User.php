<?php
namespace app\gongju\controller;
use app\author\controller\Redis;
use think\Controller;
use think\Db;
use think\Request;
class User extends Controller{
    /*
     * 根据手机号给作者分配读者账号
     * 没有，创建一个新的读者账号
     * 有，找到给作者
     */
    public function add($phone,$email){

       $result= Db::name('User')->where(['phone'=>$phone,'is_author'=>0])->find();
       if(is_array($result)){
           Db::name('User')->where(['phone'=>$phone,'is_author'=>0])->update(['is_author'=>1]);
           return $result['user_id'];
       }else{
           //创建一条新的记录
           $redis =Redis::getRedisConn();
           $redis->incr('USER:COUNT');
           $index = $redis->get('USER:COUNT');
           $number=600000000+$index;
           $data['user_name']='咚者'.$number;
           $data['pen_name']=$data['user_name'];
           $data['is_author']=1;
           $data['phone']=$phone;
           $data['email']=$email;
           $data['login_ip']=request()->ip();
           $data['create_time']=date('Y-m-d H:i:s');
           Db::name('User')->insert($data);
          return Db::getLastInsID();
       }

    }

    //删除作者信息
    public function delete($id,$userid){
        Db::startTrans();//开启事务
        try{
          $a1= Db::name('Writer')->where(['author_id'=>$id])->delete();
          $a2=Db::name('User')->where(['user_id'=>$userid])->update(['is_author'=>0]);
          if($a1 && $a2){
              Db::commit();//提交事务
              return true;
          }

        }catch (\Exception $e){
            Db::rollback();//回滚事务
        }

    }
}