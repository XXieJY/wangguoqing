<?php
namespace app\gongju\controller;
use think\Controller;
use think\Db;
use think\Request;
//书籍的公共操作类
class Booklei extends Controller{
    //添加书籍
    public function book_add($data){
        Db::startTrans();//开启事务
        $book =Db::name('Book');//书籍表
        $content=Db::name('Content');//书籍卷表
        $bang = Db::name('BookStatistical'); //书籍榜单
        try{
            //添加书籍
           $re1= $book->insert($data);
            //获取书籍ID
            $book_id=$book->getLastInsID();
            //添加榜单
           $re2= $bang->insert(['book_id'=>$book_id]);
            //添加卷
            //创建卷
            $res['book_id'] = $book_id;
            $res['type'] = 1;
            $res['volume_id']=1;
            $res['volume_fid']=0;
            $res['title'] = '第一卷';
            $res['time'] = date('Y-m-d H:i:s');
            $res['update_time']=date('Y-m-d H:i:s');
          $re3=  $content->insert($res);
            //统计参数增加
            $tongji['day'] = array('exp', "day+1");
            $tongji['weeks'] = array('exp', "weeks+1");
            $tongji['month'] = array('exp', "month+1");
            $tongji['total'] = array('exp', "total+1");
          $re4=  Db::name('SystemTongji')->where(array('id' => 3))->update($tongji);
            $tongjis['letter'] = array('exp', "letter+1");
         $re5=   Db::name('Cp')->where(array('cp_id' => $data['cp_id']))->update($tongjis);
         if($re1 && $re2 && $re3 && $re4 && $re5){
             Db::commit();//提交事务
         }

        }catch (\Exception $e){
            Db::rollback();//回滚事务
        }

        //返回参数
        if ($book_id) {
            return $book_id;
        } else {
            return 0;
        }

    }

}