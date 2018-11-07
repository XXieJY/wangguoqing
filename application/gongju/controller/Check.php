<?php
namespace app\gongju\controller;
use think\Controller;
use think\Db;
use think\Request;

class Check extends Controller{
    /*
     * 检测是否处于接收协议状态
     */
    public function is_agreement($phone){
        $info= Db::name('Writer')->where(['phone'=>$phone])->find();
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
            return false;
        }
    }
}