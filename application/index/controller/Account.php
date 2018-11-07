<?php
namespace app\index\controller;
use think\Controller;
use think\Db;
//第三方登陆
class Account extends Controller{
     //微信登陆
    public function weixin(){

        //-------配置
        $AppID = 'wx9e9f32644ead1937';
        $AppSecret = '15a2bab75cbd203c11ec4d18962915fe';
        $callback  =  'http://www.shuddd.com/weixincallback'; //回调地址
        //-------生成唯一随机串防CSRF攻击
        $state  = md5(uniqid(rand(), TRUE));

        $redirect_uri =urlEncode($callback);

        $url="https://open.weixin.qq.com/connect/qrconnect?appid=".$AppID."&redirect_uri=".$redirect_uri."&response_type=code&scope=snsapi_login&state=".$state."#wechat_redirect";

        //var_dump($url);exit;
        header("Location: ".$url);
    }

    public function weixincallback() {
        header("Content-type: text/html; charset=utf-8");

        $AppID = 'wx9e9f32644ead1937';
        $AppSecret = '15a2bab75cbd203c11ec4d18962915fe';
        $url='https://api.weixin.qq.com/sns/oauth2/access_token?appid='.$AppID.'&secret='.$AppSecret.'&code='.$_GET['code'].'&grant_type=authorization_code';
       $arr =$this->get_curl($url);
       //得到 access_token 与 openid
        $url1='https://api.weixin.qq.com/sns/userinfo?access_token='.$arr['access_token'].'&openid='.$arr['openid'].'&lang=zh_CN';
       $user=$this->get_curl($url1);
       //得到 用户资料并将用户资料入库
        $Third =new Thirdaccount();
        $isOk =$Third->isLogin($user['unionid'],1);
        if($isOk==1){

            $Third->auth_login($user['unionid'],1);

        }elseif($isOk==2){

            $Third->auth_register($user['openid'],$user['unionid'],$user['nickname'],$user['headimgurl'],$user['sex'],1);
        }

    }

    private function get_curl($url){

        $ch = curl_init();
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, FALSE);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, TRUE);
        curl_setopt($ch, CURLOPT_URL, $url);
        $json =  curl_exec($ch);
        curl_close($ch);
        $arr=json_decode($json,1);

        return $arr;

    }

    //QQ登陆
    public function qq(){

        Vendor('QQauth.qqConnectAPI');
        $qc = new \QC();
        $qc->qq_login();

    }
    //QQ反馈
    public function qqcallback() {
        Vendor('QQauth.qqConnectAPI');
        $qc = new \QC();
        $access_token = $qc->qq_callback(); //获取授权代码
        $url ="https://graph.qq.com/oauth2.0/me?access_token=".$access_token."&unionid=1";

        $unionid =$this->getUnionid($url);//获取全网唯一凭证

        $openid = $qc->get_openid(); //获取唯一登录ID
        $qc = new \QC($access_token, $openid);
        $user = $qc->get_user_info();

        //得到 用户资料并将用户资料入库
        $Third =new Thirdaccount();
        $isOk =$Third->isLogin($unionid,2);
        if($isOk==1){
            //登陆
          $Third->auth_login($unionid,2);

        }elseif ($isOk==2){
            //注册
         $Third->auth_register($openid,$unionid,$user['nickname'],$user['figureurl_1'],$this->sex($user['gender']),2);

        }

    }

        private function getUnionid($url){

            $response =file_get_contents($url);
            //--------检测错误是否发生
            if(strpos($response, "callback") !== false){

                $lpos = strpos($response, "(");
                $rpos = strrpos($response, ")");
                $response = substr($response, $lpos + 1, $rpos - $lpos -1);
            }
            $user = json_decode($response,1);
            //------记录openid
            return $user['unionid'];
        }


    private function sex($str){

        if($str=="女"){
            return 0;
        }else{

            return 1;
        }


    }


}