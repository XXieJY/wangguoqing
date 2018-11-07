<?php
namespace app\index\controller;
use app\author\controller\Redis;
use app\index\controller\CommonUtil;
use think\Controller;
use think\Db;
use think\Request;

class Thirdaccount extends Controller{
    //登陆
    public function auth_login($unionid,$type){
            if($type==1){

                $user= Db::name('User')->where(['wechat_union_id'=>$unionid])->find();

            }elseif ($type==2){

                $user= Db::name('User')->where(['qq_union_id'=>$unionid])->find();
            }

            $redis =Redis::getRedisConn();
            $date =date('Y-m-d');
            $signKey = REDIS_INTEGRAL_PREFIX . REDIS_SIGN_IN_PREFIX . $date . ':' . $user['user_id'];
           // print_r($redis->get($signKey));exit();
            if(!$redis->get($signKey)){
                   if($user['days']>0){

                       $updata['vote']=array('exp',"vote+2");
                   }else{

                       $updata['vote']=array('exp',"vote+1");
                   }
                       $updata['login_time'] =date('Y-m-d H:i:s');

                Db::name('User')->where(['user_id'=>$user['user_id']])->update($updata);
                $time1 =time();
                $time2=strtotime(date('Y-m-d 23:59:59'));
                $time3 =$time2-$time1;
                $redis->set($signKey,1,$time3);
                cookie('shudong_user',$user,86400);
                $this->redirect('/index');

            }else{
                cookie('shudong_user',$user,86400);
                $this->redirect('/index');
            }




    }
    //注册
    public function auth_register($openid,$unionid,$nickName,$headImg,$sex,$type){


                if($sex!=1){
                    $sex=0;
                }
                $redis =Redis::getRedisConn();
                $redis->incr('USER:COUNT');
                $index = $redis->get('USER:COUNT');
                $number=600000000+$index;
                $data =[
                    'user_name'  =>'咚者'.$number,
                    'pen_name'  =>$nickName,
                    'portrait'  =>$headImg,
                    'sex'        =>$sex,
                    'is_tourist'=>0,
                    'login_ip'  =>request()->ip(),
                    'login_time' =>date('Y-m-d H:i:s'),
                    'create_time' =>date('Y-m-d H:i:s'),

                ];
                if($type==1){
                   $data['wechat_open_id'] =$openid;
                   $data['wechat_union_id']  =$unionid;
                   $data['device_info'] ="PC微信登陆：主机IP：".request()->ip();
                }elseif ($type==2){
                    $data['qq_open_id'] =$openid;
                    $data['qq_union_id']=$unionid;
                    $data['device_info'] ="PC腾讯QQ登陆：主机IP：".request()->ip();
                }
                $result= Db::name('User')->insert($data);
                if($result){
                    $id=Db::getLastInsID();
                    $user= Db::name('User')->where(['user_id'=>$id])->find();
                    $date =date('Y-m-d');
                    $signKey = REDIS_INTEGRAL_PREFIX . REDIS_SIGN_IN_PREFIX . $date . ':' . $user['user_id'];

                    if(!$redis->get($signKey)){

                        $integral=$user['integral']+5;//新用户获取积分
                        $level  =CommonUtil::men_vip($integral);//新用户级别
                        $vote =1;//给用户一张推荐票
                        $update =[
                            'integral'  =>$integral,
                            'mem_vip'  =>$level,
                            'vote'     =>$vote
                        ];
                        Db::name('User')->where(['user_id'=>$user['user_id']])->update($update);
                        $time1 =time();
                        $time2=strtotime(date('Y-m-d 23:59:59'));
                        $time3 =$time2-$time1;
                        $redis->set($signKey,1,$time3);
                    }
                    cookie('shudong_user',$user,86400);

                $this->redirect('/index');
            }



    }

    public function isLogin($unionid,$type){

        if($type==1){
            $where=[
                'wechat_union_id' =>$unionid
            ];

        }elseif ($type==2){

            $where['qq_union_id'] =$unionid;
        }

          $result= Db::name('User')->where($where)->find();
          if(count($result)>0){

              return 1;

          }else{

              return 2;
          }
    }
}