<?php
namespace app\api\controller;
use think\Controller;
use Aliyun\DySDKLite\SignatureHelper;
require_once EXTEND_PATH."/sms/SignatureHelper.php"; //SignatureHelper.php 的路径
class Sendsms extends Controller{
    public function _initialize(){
        $this->accessKeyId = "LTAIkribH49KRwWh"; //keyid
        $this->accessKeySecret = "CdBaL2uTcvYmYVAI21e0wE0sJ5vusZ"; //keysecret
        $this->SignName = "书咚网络"; //签名
        $this->CodeId = "SMS_135000017"; //验证码模板id
    }
    //发送验证码
    public function code($phone,&$msg){

        if(!$this->isphone($phone)){
            $msg = "手机号不正确";
            return false;
        }

        $params["PhoneNumbers"] = $phone;
        $params["TemplateCode"] = $this->CodeId; //模板

            $code = rand(1000,9999);
            session("code".$phone,$code,'shudong_code');

        $params['TemplateParam'] = ["code" => $code]; //验证码
        return $this->send($params,$msg);
    }

    private function isphone($phone){
        if (!is_numeric($phone)) {
            return false;
        }else{
            return true;
        }

    }

    //发送

    private function send($params=[],&$msg){

        $params["SignName"] = $this->SignName;

        if(!empty($params["TemplateParam"]) && is_array($params["TemplateParam"])) {
            $params["TemplateParam"] = json_encode($params["TemplateParam"], JSON_UNESCAPED_UNICODE);
        }
        $helper = new SignatureHelper();
        $content = $helper->request(
            $this->accessKeyId,
            $this->accessKeySecret,
            "dysmsapi.aliyuncs.com",
            array_merge($params, array(
                "RegionId" => "cn-hangzhou",
                "Action" => "SendSms",
                "Version" => "2017-05-25",
            ))
        );

        if($content===false){
            $msg = "发送异常";
            return false;
        }else{
            $data = (array)$content;
            if($data['Code']=="OK"){
                $msg = "发送成功";
                return true;
            }else{
                $msg = "发送失败 ".$data['Message'];
                return false;
            }
        }
    }


}