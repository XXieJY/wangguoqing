<?php
namespace app\index\controller;
use think\Controller;
use think\Db;
use think\Request;
use app\author\controller\Redis;
class Login extends Controller{

    public function index(){

        return $this->fetch();
    }

    public function login(){

        $res =input('post.');
       // print_r($res);exit();
        $url =empty($res['url'])?"/index.html":$res['url'];
        if(!$this->isPhone($res['phone'])){
            $this->error('手机号不合法');
        }
       $user= Db::name('User')->where(['phone'=>$res['phone'],'user_pass'=>md5(md5($res['password']))])->find();
        if(is_array($user)){
            $redis =Redis::getRedisConn();
            $date =date('Y-m-d');
            $signKey = REDIS_INTEGRAL_PREFIX_PC . REDIS_SIGN_IN_PREFIX_PC . $date . ':' . $user['user_id'];
             //print_r($redis->get($signKey));exit();
            if(!$redis->get($signKey)) {
                if ($user['days'] > 0) {

                    $updata['vote'] = array('exp', "vote+2");
                } else {

                    $updata['vote'] = array('exp', "vote+1");
                }
                $updata['login_time'] = date('Y-m-d H:i:s');

                Db::name('User')->where(['user_id' => $user['user_id']])->update($updata);
              $aaa=  Db::name('User')->where(['user_id' => $user['user_id']])->find();
                $time1 = time();
                $time2 = strtotime(date('Y-m-d 23:59:59'));
                $time3 = $time2 - $time1;
                $redis->set($signKey, 1, $time3);
                $this->getUserInfo($user['user_id']);
                $this->redirect(url('/login/ok',['vote'=>$aaa['vote']]));
            }else{

                $this->getUserInfo($user['user_id']);
                $this->redirect($url);
               // $this->redirect(url('/login/ok',['vote'=>$user['vote']]));
            }
        }else{
            $this->error('账号或者密码错误');
        }

    }
    private function getUserInfo($userid){

        $user =Db::name('User')->where(['user_id'=>$userid])->find();

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
    public function ok($vote=1){

        return $this->fetch('',[
            'vote'  =>$vote
        ]);
    }

    /*
     * 验证手机号的合法性
     */
    private function isPhone($phone){

        if (!is_numeric($phone)) {
            return false;
        }
        return preg_match('/^1[3|4|5|6|7|8|9][0-9]\d{8}$/', $phone) ? true : false;
    }

    /*
     * 退出登陆
     */
      public function logout(){

          cookie('shudong_user',null);
          $this->redirect('/index');
      }

}
