<?php
namespace app\api\controller;
use think\Controller;

class Send extends Controller{
    public function code(){
        $phone=input('post.phone');
      if(!is_numeric($phone)){
          return 1;
      }
        $code = new Sendsms();
        $code->code($phone,$msg);
        if($msg=="发送成功"){

            return 2;
        }else{
            return 3;
        }

    }

}