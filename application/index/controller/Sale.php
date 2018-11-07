<?php
namespace app\index\controller;
use think\Controller;
use think\Db;
class Sale extends Controller{
    //销售统计
    public function index($bookid,$money){

           $book =Db::name('Book')->where(['book_id'=>$bookid])->field('book_name,cp_id')->find();
           $where =[
               'book_id'   =>$bookid,
               'time'      =>date('Y-m-d')
           ];
         $cp=  Db::name('CpMoneyday')->where($where)->find();
         if(count($cp)>0){
             $save['consumption'] = array('exp', "consumption+$money");
            $result= Db::name('CpMoneyday')->where($where)->update($save);
            return $result;
         }else{
             //创造一条记录
             $data['book_id'] = $bookid;
             $data['cp_id'] = $book['cp_id'];
             $data['time'] = date('Y-m-d');
             $data['book_name'] = $book['book_name'];
             $data['consumption'] = $money;
             $result=  Db::name('CpMoneyday')->insert($data);
             if($result){
                 return $result;
             }else{

                 $this->error('系统错误');
             }

         }

    }
}