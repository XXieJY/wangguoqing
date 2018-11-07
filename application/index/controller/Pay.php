<?php
namespace app\index\controller;
use think\Controller;
use think\Db;
class Pay extends Base {

      public function index(){

          return 111;
      }

      public function alipay(){

          try{
              // 配置参数
              $res = array();
              $res['out_trade_no'] =date('YmdHis').rand(1000000,9999999);       // 商户订单号
              $res['subject']      = "充值0.1元" ;         // 商品名称
              $res['total_amount'] = 0.1;          // 商品总价
              $res['body']         = "充值0.1元到账1咚币";    // 商品描述

              $user =$this->account;
              $data =[
                  'user_id'  =>$user['user_id'],
                  'type'      =>"支付宝",
                  'is_vip'    =>0,
                  'is_from'   =>0,
                  'trade'     =>$res['out_trade_no'],
                  'money'     =>0.1,
                  'readmoney'  =>1,
                  'dobing'     =>0,
                  'state'      =>0,
                  'create_time'   =>date('Y-m-d H:i:s'),
                  'ctime'      =>date('Y-m-d')
              ];
              Db::name('SystemPay')->insert($data);

              // 引入支付核心文件
              vendor('alipay.AopSdk');
              vendor("alipay.pagepay.service.AlipayTradeService");
              vendor("alipay.pagepay.buildermodel.AlipayTradePagePayContentBuilder");
              // 获取支付宝配置参数
              $config = config("zhifubao");
              //商户订单号，商户网站订单系统中唯一订单号，必填
              $out_trade_no = $res["out_trade_no"];
              //订单名称，必填
              $subject = trim($res["subject"]);
              //付款金额，必填
              $total_amount = $res["total_amount"];
              //商品描述，可空
              $body = trim($res["body"]);
              //构造参数
              $payRequestBuilder = new \AlipayTradePagePayContentBuilder();
              $payRequestBuilder->setBody($body);
              $payRequestBuilder->setSubject($subject);
              $payRequestBuilder->setTotalAmount($total_amount);
              $payRequestBuilder->setOutTradeNo($out_trade_no);
              $aop = new \AlipayTradeService($config);
              /**
               * pagePay 电脑网站支付请求
               * @param $builder 业务参数，使用buildmodel中的对象生成。
               * @param $return_url 同步跳转地址，公网可以访问
               * @param $notify_url 异步通知地址，公网可以访问
               * @return $response 支付宝返回的信息
               */
              $response = $aop->pagePay($payRequestBuilder,$config['return_url'],$config['notify_url']);
              //输出支付二维码
              var_dump($response);

          }catch (\Exception $exception){

              $exception->getMessage();
          }


      }




}