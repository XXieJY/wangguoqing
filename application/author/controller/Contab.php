<?php
namespace app\author\controller;
use think\Controller;
use think\Db;
class Contab extends Controller{

    //系统发送月票
    public function send_votevip(){

        $user=Db::name('User')->where(['days'=>['gt',0]])->field('user_id,vipvote,days')->select();

        $data['vipvote']=array('exp',"vipvote+1");
        $count=count($user);
        for ($i=0;$i<$count;$i++){
            Db::name('User')->where(['user_id'=>$user[$i]['user_id']])->update($data);
        }

    }

    //系统发放订阅月票
    public function send_dingyue(){

          $time=$this->GetMonth();
          $sql="SELECT user_id, SUM(money) AS m FROM shudong_user_consumerecord WHERE type=1 AND date like '%{$time}%'  GROUP BY user_id";
          $user= Db::query($sql);
          $count=count($user);
         for($i=0;$i<$count;$i++){

             if($user[$i]['m']>=1500 && $user[$i]['m']<3000){
                 Db::name('User')->where(['user_id'=>$user[$i]['user_id']])->update(['vipvote'=>['exp',"vipvote+1"]]);
             }elseif ($user[$i]['m']>=3000 && $user[$i]['m']<4500){
                 Db::name('User')->where(['user_id'=>$user[$i]['user_id']])->update(['vipvote'=>['exp',"vipvote+2"]]);
             }elseif ($user[$i]['m']>=4500 && $user[$i]['m']<6000){
                 Db::name('User')->where(['user_id'=>$user[$i]['user_id']])->update(['vipvote'=>['exp',"vipvote+3"]]);
             }elseif ($user[$i]['m']>=6000 && $user[$i]['m']<7500){
                 Db::name('User')->where(['user_id'=>$user[$i]['user_id']])->update(['vipvote'=>['exp',"vipvote+4"]]);
             }elseif ($user[$i]['m']>=7500 ){
                 Db::name('User')->where(['user_id'=>$user[$i]['user_id']])->update(['vipvote'=>['exp',"vipvote+5"]]);
             }
         }

    }

   private function GetMonth($sign="1")
    {
        //得到系统的年月
        $tmp_date=date("Ym");
        //切割出年份
        $tmp_year=substr($tmp_date,0,4);
        //切割出月份
        $tmp_mon =substr($tmp_date,4,2);
        $tmp_nextmonth=mktime(0,0,0,$tmp_mon+1,1,$tmp_year);
        $tmp_forwardmonth=mktime(0,0,0,$tmp_mon-1,1,$tmp_year);
        if($sign==0){
            //得到当前月的下一个月
            return $fm_next_month=date("Y-m",$tmp_nextmonth);
        }else{
            //得到当前月的上一个月
            return $fm_forward_month=date("Y-m",$tmp_forwardmonth);
        }
    }
}