<?php
namespace app\index\controller;
use think\Controller;
use think\Db;
class Notify extends Controller{


    public function index(){

        // 引入核心类文件
        vendor("alipay.pagepay.service.AlipayTradeService");
        // 获取支付宝配置参数
        $config = config("zhifubao");

        // 获取返回值
        $arr =input('post.');
        // 检查数据
        $alipaySevice = new \AlipayTradeService($config);
        $alipaySevice->writeLog(var_export($arr,true));
        $result = $alipaySevice->check($arr);
         $alipaySevice->writeLog($result);
        // 判断检查结果数据
        if($result) {
            // 获取相关数据
            $out_trade_no = $arr['out_trade_no'];       //商户订单号
            $trade_no     = $arr['trade_no'];           //支付宝交易号
            $trade_status = $arr['trade_status'];       //交易状态
            $total_amount = $arr['total_amount'];       //交易金额
            $buyerid      = $arr['buyer_id'];           //卖家支付宝账号id

            // 判断数据是否做过处理，如果做过处理，return，没有做过处理，执行支付成功代码
            if($trade_status == 'TRADE_SUCCESS') {

                    $data =[
                        'transaction'   =>$trade_no,
                        'state'          =>1,
                        'time'           =>date('Y-m-d H:i:s')

                    ];
                    Db::name('SystemPay')->where(['trade'=>$out_trade_no])->update($data);
            }

            echo "success";

        }else {
            echo "fail";
        }


    }
}