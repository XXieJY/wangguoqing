<?php
namespace app\api\controller;
use think\Controller;
use think\Db;
use think\Request;
class Promote extends Controller{

    public function tuijian(){
        $type =input('post.type');
        if($type=="申请主编推荐"){
            $bookName =input('post.bookName');
            $reason =input('post.reason');
            if($reason==""){//判断是否输入理由
                return 0;
            }
            //判断是否申请过推荐
            $timestamp = time();
            $begin=date('Y-m-d H:i:s', strtotime("this week Monday", $timestamp));
            $end=date('Y-m-d H:i:s', strtotime(date('Y-m-d', strtotime("this week Sunday", $timestamp))) + 24 * 3600 - 1);

            $where['time']=array(array('gt',$begin),array('lt',$end));
            $where['book_name'] =$bookName;
            $where['state']  =0;
            $where['type']  =1;
            $result= Db::name('BookApplyPromote')->where($where)->find();
            if($result){
                return 1;
            }else{
                $data['book_name'] =$bookName;
                $data['type']   =1;
                $data['reason'] =$reason;
                $data['state']=0;
                $data['time'] =date('Y-m-d H:i:s');
                $ok=  Db::name('BookApplyPromote')->insert($data);
                if($ok){
                    return 2;
                }else{
                    return 3;
                }

            }
        }
       if($type=="积分兑换推荐"){
           $bookName =input('post.bookName');
           $recomd   =input('post.recomd');
           $integral =input('post.integral');
           $author_id =input('post.author_id');
           $jifen =$this->jifen($recomd);
           //判断积分是否够用
           if($integral<$jifen){
                   return 4;
           }else{
               $con['book_name'] =$bookName;
               $con['type']  =2;
               $con['state'] =1;
               $result= Db::name('BookApplyPromote')->where($con)->find();
               if($result){
                   return 5;
               }else{
                  //数据入库
                   $data['book_name']=$bookName;
                   $data['type']  =2;
                   $data['reason'] =$recomd;
                   $data['state'] =0;
                   $data['time'] =date('Y-m-d H:i:s');
                  $ok= Db::name('BookApplyPromote')->insert($data);
                  if($ok){
                      Db::name('Writer')->where(['author_id'=>$author_id])->update(['integral'=>array('exp',"integral-$jifen")]);
                      return 6;
                  }else{
                      return 7;
                  }
               }

           }


       }

    }

    //积分换算
    public function jifen($str){
        switch ($str) {

            case "三星推荐位兑换300作者积分":
                $jifen = 300;
                break;
            case "四星推荐位兑换1000作者积分":
                $jifen = 1000;
                break;
            case "五星推荐位兑换1800作者积分":
                $jifen = 1800;
                break;
            case "超五星推荐位兑换4000作者积分":
                $jifen = 4000;
                break;

        }

        return $jifen;
    }
}