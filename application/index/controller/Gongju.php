<?php
namespace app\index\controller;
use app\author\controller\Redis;
use think\Controller;
use think\Db;
use think\Exception;
class Gongju extends Controller{

    //增加书籍点击量
    public function add_book_click($bookid){
        if(!is_numeric($bookid)){
            $this->error('参数错误');
        }
        Db::startTrans();//开启事务
        try{
            $where['time']=date('Y-m-d');
            $where['book_id']=$bookid;
            $result=Db::name('BookTongji')->where($where)->find();
            if(!$result){
                //创建一条新的记录
                $tongji['book_id']=$bookid;
                $tongji['click']=1;
                $tongji['time']=$where['time'];
                $re1=  Db::name('BookTongji')->insert($tongji);
                $data['click_day']=array('exp',"click_day+1");
                $data['click_weeks']=array('exp',"click_weeks+1");
                $data['click_month']=array('exp',"click_month+1");
                $data['click_total']=array('exp',"click_total+1");

                $re2=  Db::name('BookStatistical')->where(['book_id'=>$bookid])->update($data);
                if($re1 && $re2){
                    Db::commit();//提交事务
                }

            }else{
                //更新记录
                $re1=  Db::name('BookTongji')->where($where)->update(['click'=>['exp',"click+1"]]);
                $data['click_day']=array('exp',"click_day+1");
                $data['click_weeks']=array('exp',"click_weeks+1");
                $data['click_month']=array('exp',"click_month+1");
                $data['click_total']=array('exp',"click_total+1");

                $re2=  Db::name('BookStatistical')->where(['book_id'=>$bookid])->update($data);
                if($re1 && $re2){
                    Db::commit();//提交事务
                }
            }
        }catch (\Exception $e){
            Db::rollback();//回滚事务
        }
    }

    //增加章节点击量
    public function add_book_chapter($bookid,$num){
        if(!is_numeric($bookid) || !is_numeric($num)){
            $this->error('参数错误');
        }
        Db::name('Content')->where(['book_id'=>$bookid,'num'=>$num])->update(['clicknum'=>['exp',"clicknum+1"]]);
    }

    /*
    * 判断书籍是否已加入书架
    */
    public function isCollection($bookid){

        $useArr =cookie('shudong_user');
        $where['book_id'] =$bookid;
        $where['user_id'] =$useArr['user_id'];
        $result=Db::name('BookCollection')->where($where)->find();
        if($result){
            return true;
        }else{
            return false;
        }
    }

    /*
     * 判断用户是否登录
     */
    public function isLogin(){

            if(!cookie('shudong_user')){
                $this->redirect('/login');
            }
    }

    /*
     * 同步更新书架的阅读记录
     */
    public function updateBookCollection($userId,$bookid,$num){
          if(!is_numeric($bookid) ||!is_numeric($num)){
              $this->error('参数错误');
          }

          //查找该书是否在书架
       $result= Db::name('BookCollection')->where(['user_id'=>$userId,'book_id'=>$bookid])->find();
          if(count($result)>0){
              $redis =Redis::getRedisConn();//连接Redis
              $redis->set(REDIS_BOOKSHELF_CONTENT_PREFIX .$userId,null);//清除书架缓存
               Db::name('BookCollection')->where(['user_id'=>$userId,'book_id'=>$bookid])->update(['chapter'=>$num,'time'=>date('Y-m-d H:i:s')]);

          }
    }
   /*
    * 免费章节阅读
    */
   public function getFreeChapter($userId,$bookid,$num){
       if(!is_numeric($bookid) || !is_numeric($num)){
           $this->error('参数错误');
       }
       //先从bookbuy表查找该条记录，没有则创建一条记录
      $buy= Db::name('BookBuy')->where(['user_id'=>$userId,'book_id'=>$bookid])->find();

      if(count($buy)>0){
          //判断该章节是否已读
          $nums =explode(',',$buy['chapter_id']);
          if(!in_array($num,$nums)){
              $data['chapter_id'] = array('exp', "CONCAT(chapter_id,',$num')");
              Db::name('BookBuy')->where(['user_id'=>$userId,'book_id'=>$bookid])->update($data);
          }

      }else{
          //创建一条记录
           $jilu =[
               'book_id'  =>$bookid,
               'user_id'  =>$userId,
               'chapter_id' =>$num,
           ];
         Db::name('BookBuy')->where(['user_id'=>$userId,'book_id'=>$bookid])->insert($jilu);
      }

   }

   //用户阅读收费章节时做加入书架操作
    public function joinCollection($userId,$bookid,$num){
        Db::startTrans();//开启事务
        $redis =Redis::getRedisConn();//连接Redis
        $redis->set(REDIS_BOOKSHELF_CONTENT_PREFIX .$userId,null);//清除书架缓存
        try {
            if (!is_numeric($bookid) || !is_numeric($num)) {
                $this->error('参数错误');
            }
            $result = Db::name('BookCollection')->where(['user_id' => $userId, 'book_id' => $bookid])->find();
            if (count($result) > 0) {

                Db::name('BookCollection')->where(['user_id' => $userId, 'book_id' => $bookid])->update(['chapter' => $num, 'time' => date('Y-m-d H:i:s')]);

            } else {
                //没有收藏就加入书架
                $data = [
                    'book_id' => $bookid,
                    'user_id' => $userId,
                    'chapter' => $num,
                    'time' => date('Y-m-d H:i:s'),
                    'update_time' => date('Y-m-d H:i:s')
                ];
                $re1 = Db::name('BookCollection')->insert($data);
                //增加书籍收藏量
                $st['collection_day'] = array('exp', "collection_day+1");
                $st['collection_weeks'] = array('exp', "collection_weeks+1");
                $st['collection_month'] = array('exp', "collection_month+1");
                $st['collection_total'] = array('exp', "collection_total+1");
                $re2 = Db::name('BookStatistical')->where(['book_id' => $bookid])->update($st);
                $tong = Db::name('BookTongji')->where(['book_id' => $bookid, 'time' => date('Y-m-d')])->find();

                if (count($tong) > 0) {
                    $tongji['collection'] = array('exp', "collection+1");
                    $re3 = Db::name('BookTongji')->where(['book_id' => $bookid, 'time' => date('Y-m-d')])->update($tongji);
                    if ($re1 && $re2 && $re3) {
                        Db::commit();//提交事务
                    }

                } else {
                    $tongjis['book_id'] = $bookid;
                    $tongjis['collection'] = 1;
                    $tongjis['time'] = date('Y-m-d');
                    $re4 = Db::name('BookTongji')->insert($tongjis);
                    if ($re1 && $re2 && $re4) {
                        Db::commit();//提交事务
                    }
                }
            }
        }catch (\Exception $exception){
            $exception->getMessage();
            Db::rollback();//事务回滚
        }

    }

    /*
     * 普通订阅次数
     */
    public function adddycs($contentId){

       $result= Db::name('Content')->where(['content_id'=>$contentId])->update(['dycs'=>['exp',"dycs+1"]]);
       return $result;
    }
    /*
     * 普通咚币订阅次数
     */
    public function adddyc($contentId){

        $result= Db::name('Content')->where(['content_id'=>$contentId])->update(['dyc'=>['exp',"dyc+1"],'dycs'=>['exp',"dycs+1"]]);
        return $result;

    }
    /*
     * 全本订阅普通咚币订阅次数
     */
    public function adddycFull($bookid,$nums){
           $numArr =explode(',',$nums);

           foreach ($numArr as $k=>$v){

               Db::name('Content')->where(['book_id'=>$bookid,'num'=>$v])->update(['dyc'=>['exp',"dyc+1"],'dycs'=>['exp',"dycs+1"]]);
           }
           return 1;

    }


    /*
     * vip订阅次数
     */
   public function addvipdycs($contentId){

       $result= Db::name('Content')->where(['content_id'=>$contentId])->update(['vipdycs'=>['exp',"vipdycs+1"]]);
       return $result;

   }
    /*
  * 全本订阅VIP咚币订阅次数
  */
    public function adddycVIPFull($bookid,$nums){
        $numArr =explode(',',$nums);

        foreach ($numArr as $k=>$v){

            Db::name('Content')->where(['book_id'=>$bookid,'num'=>$v])->update(['vipdyc'=>['exp',"vipdyc+1"],'vipdycs'=>['exp',"vipdycs+1"]]);
        }
        return 1;

    }
   /*
    * vip咚币订阅次数
    */
    public function addvipdyc($contentId){

        $result= Db::name('Content')->where(['content_id'=>$contentId])->update(['vipdyc'=>['exp',"vipdyc+1"],'vipdycs'=>['exp',"vipdycs+1"]]);
        return $result;

    }

    /*
     * 更新书籍购买统计表
     */
    public function updateBuy($bookid,$money){

            $save['buy_day'] = array('exp', "buy_day+$money");
            $save['buy_weeks'] = array('exp', "buy_weeks+$money");
            $save['buy_month'] = array('exp', "buy_month+$money");
            $save['buy_total'] = array('exp', "buy_total+$money");
            $save['money_day'] = array('exp', "money_day+$money");
            $save['money_weeks'] = array('exp', "money_weeks+$money");
            $save['money_month'] = array('exp', "money_month+$money");
            $save['money_total'] = array('exp', "money_total+$money");
            $re1=  Db::name('BookStatistical')->where(array('book_id' =>$bookid))->update($save);
            $where=[
                'book_id'  =>$bookid,
                'time'     =>date('Y-m-d')
            ];

              $data['buy'] =array('exp',"buy+$money");
              $data['money'] =array('exp',"money+$money");
             $re2= Db::name('BookTongji')->where($where)->update($data);

          if($re1 && $re2){

             return 1;
          }else{

              return 0;
          }




    }

}