<?php
namespace app\index\controller;
use think\Controller;
use think\Db;
use think\Request;
use app\author\controller\Redis;
class Vipread extends Controller{
    public function _empty(){

        $this->error('页面不存在');
    }
    public function index($bookid,$num){

           if(!is_numeric($bookid) || !is_numeric($num)){

               $this->error('参数不合法');
           }
           if($this->isVip($bookid,$num)===false){

               $this->error('该章节不是vip');
           }
           $url='http://'.$_SERVER['HTTP_HOST'].$_SERVER['REQUEST_URI'];//获取该界面的完整的url；
        $user =cookie('shudong_user');
        $gongju = new Gongju();
        $exception =$this->getBookExceptional($bookid);//获取书籍的打赏信息
        $wallet =$this->getWallet();//读者钱包
        $isCollection =$gongju->isCollection($bookid);//判断是否已加入书架
        $book=$this->get_book_info($bookid);//获取书籍信息
        $chapter=$this->get_chapter($bookid);//获取书籍章节
        $neirong= $this->getContent($bookid,$num);//获取章节内容
        $totalChapter =$this->totalChapter($bookid);//获取总章节
        $preNum =$num<=1? '1':$num-1;
        $a1= Db::name('Content')->where(['book_id'=>$bookid,'num'=>$preNum])->field('type')->find();
        if($a1['type']==1){
            $preNum=$preNum-1;
        }
        $nextNum = $num>=$totalChapter?$totalChapter:$num+1;
        $a2= Db::name('Content')->where(['book_id'=>$bookid,'num'=>$nextNum])->field('type')->find();
        if($a2['type']==1){
            $nextNum=$nextNum+1;
        }
        $title1 =Db::name('Content')->where(['book_id'=>$bookid,'the_price'=>['gt',0]])->field('title')->order('num asc')->find();//开始收费章节
        $title2 =Db::name('Content')->where(['book_id'=>$bookid,'the_price'=>['gt',0]])->field('title')->order('num desc')->find();//最后收费章节
        $money=$this->buyFull($bookid,$user['user_id']);

           return $this->fetch('',[

               'book'   =>$book,
               'chapter' =>$chapter,
               'content'  =>$neirong,
               'collection' =>$isCollection,
               'exception'  =>$exception,
               'wallet'     =>$wallet,
               'pre'  =>$preNum,
               'next'  =>$nextNum,
               'total' =>$totalChapter,
               'title1'  =>$title1['title'],
               'title2'   =>$title2['title'],
               'money'    =>$money,
               'url'    =>$url,
               'user'   =>$user,


           ]);


    }

    //判断是否是vip章节
    private function isVip($bookid,$num){

        $where =[
            'book_id' =>$bookid,
            'num'     =>$num,
            'state'   =>1,
            'status'   =>0,
            'type'    =>0
        ];

       $result= Db::name('Content')->where($where)->find();

       return $result['the_price']==0? false:true;

    }
   //获取该书的详细信息
    public function get_book_info($bookid){

        $book=Db::name('Book')->where(['book_id'=>$bookid])->find();
        $book['count'] =  Db::name('BookMessage')->where(['book_id'=>$bookid])->count();
        return $book;
    }

    /*
        * 根据bookid 和num获取书籍的章节内容
        */

    private function getContent($bookid,$num){

        $redis = Redis::getRedisConn();//连接redis

        $redisKey = REDIS_CHAPTER_CONTENT_INDEX_PC.$bookid."_".$num;
       //  $redis->set($redisKey,null);
        if($redis->get($redisKey)){

            $content =unserialize($redis->get($redisKey));
        }else{
            $content =Db::view('Content','content_id,the_price,title,num,number,time')
                ->view('Contents','content,msg','Contents.content_id=Content.content_id')
                ->where(['book_id'=>$bookid,'num'=>$num])
                ->find();
            if($bookid==110 && $num>1){
                $content['content']=str_replace("\n","</p><p>",$content['content']);

            }else{

                $content['content']=str_replace("\n","</p><p style=\"text-indent: 2em;\">",$content['content']);
            }
            $content['msg'] =str_replace("\n","</p><p style=\"text-indent: 2em;\">",$content['msg']);
            if(cookie('shudong_user')['days']>0){
                $content['the_price']=round($content['the_price']*0.6);
            }
            $redis->set($redisKey,serialize($content),3600);
        }

        return $content;
    }

    //获取书籍的章节
    public function get_chapter($bookid){

        $redis =Redis::getRedisConn();
        if($redis->get(REDIS_CHAPTER_LIST_TWO_PC.$bookid)){

            $chapter =unserialize($redis->get(REDIS_CHAPTER_LIST_TWO_PC.$bookid));
        }else{
            $chapter =Db::name('Content')->field('volume_id,title,book_id')->where(['book_id'=>$bookid,'type'=>1])->select();
            $length =count($chapter);
            for($i=0;$i<$length;$i++){
                $chapter[$i]['chapter'] =Db::name('Content')
                    ->field('content_id,title,the_price,num')
                    ->where(['book_id'=>$bookid,'state'=>1, 'status'=>0,'type'=>0,'volume_fid'=>$chapter[$i]['volume_id']])
                    ->select();
            }

            $redis->set(REDIS_CHAPTER_LIST_TWO_PC.$bookid,serialize($chapter),3600);

        }

        return $chapter;
    }

    /*
    * 获取书籍的打赏信息
    */
    private function getBookExceptional($bookid){

        $sql ="SELECT t1.user_id,SUM(t1.count) as `count`,t2.pen_name,t2.portrait,t2.sex FROM shudong_user_consumerecord AS t1 INNER JOIN  shudong_user t2 ON t1.user_id=t2.user_id  WHERE type=4 AND book_id=? GROUP BY user_id ORDER BY `count` DESC limit 0,15";
        $exception= Db::query($sql,[$bookid]);
        for ($i=0;$i<count($exception);$i++){
            if(strlen($exception[$i]['portrait'])<60){
                if ($exception[$i]['portrait']=="user/portrait/portrait.jpg"){

                    $exception[$i]['portrait']="http://images.shuddd.com/user/portrait/portrait".$exception[$i]['sex'].".png";

                }else{
                    $exception[$i]['portrait']="http://images.shuddd.com/".$exception[$i]['portrait'];
                }

            }
        }
        return $exception;
    }

    /*
     * 钱包余额
     */
    private function getWallet(){

            $useArr =cookie('shudong_user');
            $wallet= Db::name('User')->where(['user_id'=>$useArr['user_id']])->field('alance,dobing,vipvote,vote')->find();

       return $wallet;
    }


    /*
         * 获取书籍总章节数
         */

    private function totalChapter($bookid){

        if(!is_numeric($bookid)){
            $this->error('书籍参数错误');
        }
        $num= Db::name('Content')->where(['book_id'=>$bookid,'type'=>0,'state'=>1, 'status'=>0])->field('num')->order('num desc')->select();

        return count($num);
    }

    /*
     * 全本订阅的总金额
     */
    private function buyFull($bookid,$userId){

        $user =Db::name('User')->where(['user_id'=>$userId])->find();
         //该书收费章节的总金额
          $totalMoney =Db::name('Content')->where(['book_id'=>$bookid])->sum('the_price');
          //获取已消费章节的总金额
           $chapter =Db::name('BookBuy')->where(['book_id'=>$bookid,'user_id'=>$userId])->find();
           $tArr =$chapter['is_buy'];
           $where =[
               'book_id'  =>$bookid,
               'num'      =>['in',$tArr]
           ];
         $buyMoney=  Db::name('Content')->where($where)->sum('the_price');
          $money =$totalMoney-$buyMoney;
         if($user['days']>0){

             return round($money*0.6);
         }else{

             return $money;
         }

    }

    /*
 * 判断书籍的上一级路径
 */
    public function check_url(){

        $a=strrpos($_SERVER['HTTP_REFERER'],'/');
        $b=strrpos($_SERVER['HTTP_REFERER'],'?');
        if($b>0){
            $url =substr($_SERVER['HTTP_REFERER'],$a+1,$b-$a-1);
        }else{
            $url =substr($_SERVER['HTTP_REFERER'],$a+1);
        }

        return $url;

    }

    public function buyOrder(){

        $res =input('post.');
       // print_r($res);exit();
        $gongju = new Gongju();
        $content =Db::name('Content')->where(['book_id'=>$res['bookid'],'num'=>$res['num'],'type'=>0])->find();
        if($res['type']==1){
            //订阅本章
            $type=  $this->VIP($res['userid']);

            $this->buy($type,$res['userid'],$res['bookid'],$res['num'],$content['content_id'],$content['the_price']);
            $gongju->joinCollection($res['userid'],$res['bookid'],$res['num']);//加入书架

        }elseif ($res['type']==2){
            //全本订阅
            $this->buyAllChapter($res['bookid'],$res['userid'],$res['num']);

        }else{
            //自动订阅下一章
            $this->ziBuy($res['bookid'],$res['userid'],$res['num']);
        }
    }

    /*
    * 判断用户是否是vip用户
    */
    private function VIP($userId){


        $user= Db::name('User')->where(['user_id'=>$userId])->field('days')->find();
        if($user['days']>0){
            return 2;
        }else{
            return 1;
        }
    }

    /*
 * 在此处处理收费章节
 */
    public function buy($type,$userId,$bookid,$num,$contentId,$money){

        switch ($type){
            case 1:
                $this->noVipBuy($userId,$bookid,$num,$contentId,$money,$type);//普通订阅
                break;
            case 2:
                $this->vipBuy($userId,$bookid,$num,$contentId,$money,$type);//vip订阅
                break;
        }
    }

    /*
    * 普通用户订阅
    */

    public function noVipBuy($userId,$bookid,$num,$contentId,$money,$type){
        //判断用户是否购买
        $where=[
            'book_id'  =>$bookid,
            'user_id'  =>$userId
        ];

        $bookBuy= Db::name('BookBuy')->where($where)->find();

        if(count($bookBuy)==0){
            $this->addbuy($bookid,$userId,$num,$contentId,$money,$type);
        }elseif($bookBuy['is_buy']==''){
            $this->updateBookBuy($bookid,$userId,$num,$contentId,$money,$type);
        }else{

            $nums=explode(',',$bookBuy['is_buy']);

            if(!in_array($num,$nums)) {
                //没有购买记录就去创建购买记录
                $this->Consumption($bookid,$userId,$num,$contentId,$money);

            }
        }
    }

    /*
     * vip用户订阅
     */
    public function vipBuy($userId,$bookid,$num,$contentId,$money,$type){
        //判断用户是否购买
        $where=[
            'book_id'  =>$bookid,
            'user_id'  =>$userId
        ];
        $bookBuy= Db::name('BookBuy')->where($where)->find();
        if(count($bookBuy)==0){

            $this->addbuy($bookid,$userId,$num,$contentId,$money,$type);

        }elseif ($bookBuy['is_buy']==''){

            $this->updateBookBuy($bookid,$userId,$num,$contentId,$money,$type);

        }elseif(count($bookBuy['is_buy'])>0){

            $nums=explode(',',$bookBuy['is_buy']);
            if(!in_array($num,$nums)) {

                $this->vipconsumption($bookid,$money,$num,$contentId);

            }

        }
    }

    /*
   * 构造购买记录表
   */
    public function addbuy($bookid,$userId,$num,$contentId,$money,$type){
        Db::startTrans();
        $data=[
            'book_id'  =>$bookid,
            'user_id'  =>$userId,
            'chapter_id'=>$num,
            'is_buy'   =>$num
        ];
        $re1= Db::name('BookBuy')->insert($data);//创建一条购买记录

        switch ($type){
            case 1:
                $this->oneConsumption($re1,$bookid,$userId,$num,$contentId,$money);//普通消费
                break;

            case 2:
                $this->vipOneConsumption($re1,$bookid,$money,$num,$contentId);//vip消费
                break;
        }




    }
    /*
    * 更新购买记录表
    */
    public function updateBookBuy($bookid,$userId,$num,$contentId,$money,$type){

        Db::startTrans();//开启事务;
        $where=[
            'book_id'  =>$bookid,
            'user_id'  =>$userId,
        ];
        $buy =Db::name('BookBuy')->where($where)->find();
        //判断该章节是否已读
        $nums =explode(',',$buy['chapter_id']);
        if(!in_array($num,$nums)){
            $data['chapter_id'] = array('exp', "CONCAT(chapter_id,',$num')");
            Db::name('BookBuy')->where(['user_id'=>$userId,'book_id'=>$bookid])->update($data);
        }
        $re1= Db::name('BookBuy')->where($where)->update(['is_buy'=>$num]);

        switch ($type){
            case 1:
                $this->oneConsumption($re1,$bookid,$userId,$num,$contentId,$money);  //普通消费

                break;
            case 2:
                $this->vipOneConsumption($re1,$bookid,$money,$num,$contentId);//vip消费

                break;
        }

    }
    /*
    * 普通用户首次订阅收费章节
    */
    public function oneConsumption($re1,$bookid,$userId,$num,$contentId,$money){

        $gongju =new Gongju();

        $user=  Db::name('User')->where(['user_id'=>$userId])->field('pen_name,alance,dobing,be_code')->find();
        if($user['pen_name']==cookie('shudong_user')['pen_name']){
            //此处处理用户的消费逻辑
            if($user['alance']+$user['dobing']>=$money){
                //先扣赠币再扣咚币
                if($user['dobing']>=$money){

                    $map['dobing'] =array('exp',"dobing-$money");
                    $re2 =Db::name('User')->where(['user_id'=>$userId])->update($map);//扣除用户的赠币
                    $re3= $gongju->adddycs($contentId);//增加普通订阅次数
                    $re4=   $this->insertOneBuy($bookid,0,$money,0,$num);//添加用户购买记录

                    if($re1 && $re2 && $re3 && $re4){

                        Db::commit();//提交事务
                        $this->redirect(url('/read/'.$bookid.'/'.$num));
                    }else{
                        Db::rollback();
                    }

                }elseif ($user['dobing']<$money){

                    $money1=$money-$user['dobing'];
                    $map['dobing']=0;
                    $map['alance']=array('exp',"alance-$money1");

                    $re2 =Db::name('User')->where(['user_id'=>$userId])->update($map);//扣除用户的赠币

                    //增加粉丝值
                    $fans =new Fans();
                    $re3= $fans->index($bookid,$money1);
                    $re4 =$gongju->adddyc($contentId);//普通用户咚币订阅次数
                    //跟新书籍排名号
                    $re5= $gongju->updateBuy($bookid,$money1);
                    //增加销售记录
                    $sale =new Sale();
                    $re6= $sale->index($bookid,$money1);

                    $re7= $this->insertOneBuy($bookid,0,$money,$money1,$num);

                    if($user['be_code']!=""){

                        $re8= Db::name('User')->where(['code'=>$user['be_code']])->update(['buy_dobing'=>['exp',"buy_dobing+$money1"]]);
                    }else{

                        $re8=1;
                    }
                    if($re1 && $re2 && $re3 && $re4 && $re5 && $re6 && $re7 && $re8){

                        Db::commit();//提交事务
                        $this->redirect(url('/read/'.$bookid.'/'.$num));
                    }else{
                        Db::rollback();
                    }
                }
            }else{
                $this->redirect(url('/vipread/'.$bookid.'/'.$num));
            }

        }else{

            $this->error('系统错误，请重新登录','/login');
        }

    }

    /*
       * 普通用户订阅收费章节
       */
    public function Consumption($bookid,$userId,$num,$contentId,$money){

        Db::startTrans();//开启事务
        $gongju =new Gongju();
        $user=  Db::name('User')->where(['user_id'=>$userId])->field('pen_name,alance,dobing,be_code')->find();
        if($user['pen_name']==cookie('shudong_user')['pen_name']){
            //此处处理用户的消费逻辑
            if($user['alance']+$user['dobing']>=$money){
                //先扣赠币再扣咚币
                if($user['dobing']>=$money){

                    $map['dobing'] =array('exp',"dobing-$money");
                    $re1 =Db::name('User')->where(['user_id'=>$userId])->update($map);//扣除用户的赠币
                    $re2= $gongju->adddycs($contentId);//增加普通订阅次数
                    $re3=   $this->insertOneBuy($bookid,0,$money,0,$num);//添加用户购买记录
                    $data['chapter_id'] = array('exp', "CONCAT(chapter_id,',$num')");
                    $data['is_buy'] = array('exp', "CONCAT(is_buy,',$num')");
                    $re4= Db::name('BookBuy')->where(['user_id'=>$userId,'book_id'=>$bookid])->update($data);
                    if($re1 && $re2 && $re3 && $re4){
                        Db::commit();//提交事务
                        $this->redirect(url('/read/'.$bookid.'/'.$num));
                    }else{
                        Db::rollback();
                    }

                }elseif ($user['dobing']<$money){

                    $money1=$money-$user['dobing'];
                    $map['dobing']=0;
                    $map['alance']=array('exp',"alance-$money1");

                    $re1 =Db::name('User')->where(['user_id'=>$userId])->update($map);//扣除用户的赠币

                    //增加粉丝值
                    $fans =new Fans();
                    $re2= $fans->index($bookid,$money1);
                    $re3 =$gongju->adddyc($contentId);//普通用户咚币订阅次数
                    //跟新书籍排名号
                    $re4= $gongju->updateBuy($bookid,$money1);

                    //增加销售记录
                    $sale =new Sale();
                    $re5= $sale->index($bookid,$money1);

                    $re6= $this->insertOneBuy($bookid,0,$money,$money1,$num);

                    $data['chapter_id'] = array('exp', "CONCAT(chapter_id,',$num')");
                    $data['is_buy'] = array('exp', "CONCAT(is_buy,',$num')");
                    $re7= Db::name('BookBuy')->where(['user_id'=>$userId,'book_id'=>$bookid])->update($data);

                    if($user['be_code']!=""){

                       $re8= Db::name('User')->where(['code'=>$user['be_code']])->update(['buy_dobing'=>['exp',"buy_dobing+$money1"]]);
                    }else{

                        $re8=1;
                    }

                    if($re1 && $re2 && $re3 && $re4 && $re5 && $re6 && $re7 && $re8){

                        Db::commit();//提交事务
                        $this->redirect(url('/read/'.$bookid.'/'.$num));
                    }else{
                        Db::rollback();
                    }
                }
            }else{
                $this->redirect(url('/vipread/'.$bookid.'/'.$num));
            }

        }else{

            $this->error('系统错误，请重新登录','/login');
        }



    }

    /*
    * vip用户首次购买章节
    */
    public function vipOneConsumption($re1,$bookid,$money,$num,$contentId){

        //验证用户权限
        $money=round($money*0.6);
        $gongju =new Gongju();
        $where['user_id']=cookie('shudong_user')['user_id'];
        $user=  Db::name('User')->where($where)->field('pen_name,alance,dobing')->find();
        if($user['pen_name']==cookie('shudong_user')['pen_name']){
            //扣钱
            if($user['alance']+$user['dobing']>=$money){
                //先扣赠币再扣咚币
                if($user['dobing']>=$money){
                    $map['dobing'] =array('exp',"dobing-$money");
                    $re2=  Db::name('User')->where($where)->update($map);
                    $re3= $gongju->addvipdycs($contentId);
                    $re4=   $this->insertOneBuy($bookid,1,$money,0,$num);//添加用户购买记录
                    if($re1 && $re2 && $re3 && $re4){
                        Db::commit();//提交事务
                        $this->redirect(url('/read/'.$bookid.'/'.$num));
                    }else{
                        Db::rollback();//事务回滚
                    }

                }elseif ($user['dobing']<$money){

                    $money1=$money-$user['dobing'];
                    $map['dobing']=0;
                    $map['alance']=array('exp',"alance-$money1");
                    $re2=  Db::name('User')->where($where)->update($map);
                    //增加粉丝值
                    $fans =new Fans();
                    $re3= $fans->index($bookid,$money1);
                    $re4 =$gongju->addvipdyc($contentId);
                    //跟新数据排名号
                    $re5= $gongju->updateBuy($bookid,$money1);
                    //增加销售记录
                    $sale =new Sale();
                    $re6= $sale->index($bookid,$money1);
                    $re7= $this->insertOneBuy($bookid,1,$money,$money1,$num);
                    if($re1 && $re2 && $re3 && $re4 && $re5 && $re6 && $re7){

                        Db::commit();//提交事务
                        $this->redirect(url('/read/'.$bookid.'/'.$num));

                    }else{
                        Db::rollback();//事务回滚
                    }
                }

            }else{

                $this->redirect(url('/vipread/'.$bookid.'/'.$num));
            }

        }else{

            $this->error('系统错误请重新登录','/login');
        }

    }

    /*
        * vip消费
        */
    public function vipconsumption($bookid,$money,$num,$contentId){

        Db::startTrans();//开启事务

        //验证用户权限
        $money=round($money*0.6);
        $gongju =new Gongju();
        $where['user_id']=cookie('shudong_user')['user_id'];
        $user=  Db::name('User')->where($where)->field('pen_name,alance,dobing')->find();
        if($user['pen_name']==cookie('shudong_user')['pen_name']){
            //扣钱
            if($user['alance']+$user['dobing']>=$money){
                //先扣赠币再扣咚币
                if($user['dobing']>=$money){

                    $map['dobing'] =array('exp',"dobing-$money");
                    $re1=  Db::name('User')->where($where)->update($map);
                    $re2= $gongju->addvipdycs($contentId);
                    $re3=   $this->insertOneBuy($bookid,1,$money,0,$num);//添加用户购买记录
                    $data['chapter_id'] = array('exp', "CONCAT(chapter_id,',$num')");
                    $data['is_buy'] = array('exp', "CONCAT(is_buy,',$num')");
                    $re4= Db::name('BookBuy')->where(['user_id'=>cookie('shudong_user')['user_id'],'book_id'=>$bookid])->update($data);
                    if($re1 && $re2 && $re3 && $re4){

                        Db::commit();//提交事务
                        $this->redirect(url('/read/'.$bookid.'/'.$num));
                    }else{
                        Db::rollback();
                    }

                }elseif ($user['dobing']<$money){

                    $money1=$money-$user['dobing'];
                    $map['dobing']=0;
                    $map['alance']=array('exp',"alance-$money1");
                    $re1=  Db::name('User')->where($where)->update($map);
                    //增加粉丝值
                    $fans =new Fans();
                    $re2= $fans->index($bookid,$money1);
                    $re3 =$gongju->addvipdyc($contentId);
                    //跟新数据排名号
                    $re4= $gongju->updateBuy($bookid,$money1);
                    //增加销售记录
                    $sale =new Sale();
                    $re5= $sale->index($bookid,$money1);
                    $re6= $this->insertOneBuy($bookid,1,$money,$money1,$num);
                    $data['chapter_id'] = array('exp', "CONCAT(chapter_id,',$num')");
                    $data['is_buy'] = array('exp', "CONCAT(is_buy,',$num')");
                    $re7= Db::name('BookBuy')->where(['user_id'=>cookie('shudong_user')['user_id'],'book_id'=>$bookid])->update($data);
                    if($re1 && $re2 && $re3 && $re4 && $re5 && $re6 && $re7){

                        Db::commit();//提交事务
                        $this->redirect(url('/read/'.$bookid.'/'.$num));
                    }else{
                        Db::rollback();
                    }
                }

            }else{

                $this->redirect(url('/vipread/'.$bookid.'/'.$num));
            }

        }else{

            $this->error('系统错误请重新登录','/login');
        }


    }

    /*
   * 用户单章订阅记录
   */
    public function insertOneBuy($bookId,$vip,$count,$money,$num){

        $content= Db::name('Content')->where(['book_id'=>$bookId,'num'=>$num])->field('title')->find();
        $data =[
            'user_id'  =>cookie('shudong_user')['user_id'],
            'book_id'  =>$bookId,
            'type'     =>1,
            'count'    =>$count,
            'money'    =>$money,
            'vip'      =>$vip,
            'total'    =>1,
            'chapters' =>$num,
            'dosomething' =>"订购了".$content['title'],
            'date'         =>date('Y-m-d H:i:s')
        ];

        $result= Db::name('UserConsumerecord')->insert($data);

        return $result;

    }
   /*
    * 自动订阅
    */

   public function ziBuy($bookid,$userid,$num){

          $result=   Db::name('BookBuy')->where(['book_id'=>$bookid,'user_id'=>$userid])->update(['is_type'=>1]);
          if($result){

              $this->redirect(url('/read/'.$bookid.'/'.$num));
          }else{
              $this->error('自动订阅失败');
          }
   }

   /*
    * 全本订阅
    */
   public function buyAllChapter($bookid,$userid,$num){

         //获取全本订阅的章节数
       $where=[
           'book_id'   =>$bookid,
           'the_price'  =>['gt',0],
           'type'      =>0,
           'status'    =>0,
           'state'     =>1
       ];
       $numArr =[];
       $chapter =Db::name('Content')->where($where)->field('num')->select();//书籍收费章节的总章节
       foreach ($chapter as $k=>$v){
           $numArr[] =$v['num'];
       }
       $buyChapter =Db::name('BookBuy')->where(['book_id'=>$bookid,'user_id'=>$userid])->field('is_buy')->find();//用户已购买的收费章节
       $buyChapterArr =explode(',',$buyChapter['is_buy']);

       $nums =implode(',',array_diff($numArr,$buyChapterArr));//剩余未购买的章节

       $vip =$this->VIP($userid);
       if($vip==2){
           //vip用户
           $bookMoney =$this->buyFull($bookid,$userid);//全本购买剩余章节所需要的金额

           $useMoney =$this->getWallet();//用户钱包的余额

           if($useMoney['alance']<$bookMoney){

               $this->error('余额不足，请充值');
               exit();
           }
           //在这里处理VIP用户全本订阅的逻辑块
           $this->buyFullBookVip($bookid,$userid,$bookMoney,1,$nums,$num);
       }else{
           //普通用户
           $bookMoney =$this->buyFull($bookid,$userid);//全本购买剩余章节所需要的金额

           $useMoney =$this->getWallet();//用户钱包的余额

           if($useMoney['alance']<$bookMoney){

               $this->error('余额不足，请充值');
               exit();
           }
           //在这里处理普通用户全本订阅的逻辑块

           $this->buyFullBookNoVip($bookid,$userid,$bookMoney,0,$nums,$num);

       }


   }

   /*
    * @param $bookid 书籍id
    * @param  $userid 用户id
    * @param $money 订阅的钱
    * @param $type 用户的类型
    * @param $nums 全本订阅的所有章节
    * @param $num 当前章节
    */
   public function buyFullBookNoVip($bookid,$userid,$money,$type,$nums,$num){

          Db::startTrans();//开启事务

              $gongju =new Gongju();
              $user=  Db::name('User')->where(['user_id'=>$userid])->field('pen_name,alance,dobing,be_code')->find();
              if($user['pen_name']==cookie('shudong_user')['pen_name']){

                  $money1=$money-$user['dobing'];
                  $map['dobing']=0;
                  $map['alance']=array('exp',"alance-$money1");

                  $re1 =Db::name('User')->where(['user_id'=>$userid])->update($map);//扣除用户的赠币

                  //增加粉丝值
                  $fans =new Fans();
                  $re2= $fans->index($bookid,$money1);
                  $re3 =$gongju->adddycFull($bookid,$nums);//普通用户咚币订阅次数

                  //跟新书籍排名号
                  $re4= $gongju->updateBuy($bookid,$money1);

                  //增加销售记录
                  $sale =new Sale();
                  $re5= $sale->index($bookid,$money1);

                  $re6= $this->insertMoreBuy($bookid,$type,$money,$money1,$nums);

                  $data['chapter_id'] = array('exp', "CONCAT(chapter_id,',$nums')");
                  $data['is_buy'] = array('exp', "CONCAT(is_buy,',$nums')");
                  $re7= Db::name('BookBuy')->where(['user_id'=>$userid,'book_id'=>$bookid])->update($data);

                  if($user['be_code']!=""){

                      $re8= Db::name('User')->where(['code'=>$user['be_code']])->update(['buy_dobing'=>['exp',"buy_dobing+$money1"]]);
                  }else{

                      $re8=1;
                  }

                  if($re1 && $re2 && $re3 && $re4 && $re5 && $re6 && $re7 && $re8){

                      Db::commit();//提交事务
                      $this->redirect(url('/read/'.$bookid.'/'.$num));
                  }else{
                      Db::rollback();
                      $this->error('全本订阅失败');
                  }

              }else{

                  $this->error('系统错误请重新登录','/login');
              }




   }

    /*
    * @param $bookid 书籍id
    * @param  $userid 用户id
    * @param $money 订阅的钱
    * @param $type 用户的类型
    * @param $nums 全本订阅的所有章节
    * @param $num 当前章节
    */
    public function buyFullBookVip($bookid,$userid,$money,$type,$nums,$num){

        Db::startTrans();//开启事务

        $gongju =new Gongju();
        $user=  Db::name('User')->where(['user_id'=>$userid])->field('pen_name,alance,dobing')->find();
        if($user['pen_name']==cookie('shudong_user')['pen_name']){

            $money1=$money-$user['dobing'];
            $map['dobing']=0;
            $map['alance']=array('exp',"alance-$money1");

            $re1 =Db::name('User')->where(['user_id'=>$userid])->update($map);//扣除用户的赠币

            //增加粉丝值
            $fans =new Fans();
            $re2= $fans->index($bookid,$money1);
            $re3 =$gongju->adddycVIPFull($bookid,$nums);//普通用户咚币订阅次数

            //跟新书籍排名号
            $re4= $gongju->updateBuy($bookid,$money1);

            //增加销售记录
            $sale =new Sale();
            $re5= $sale->index($bookid,$money1);

            $re6= $this->insertMoreBuy($bookid,$type,$money,$money1,$nums);

            $data['chapter_id'] = array('exp', "CONCAT(chapter_id,',$nums')");
            $data['is_buy'] = array('exp', "CONCAT(is_buy,',$nums')");
            $re7= Db::name('BookBuy')->where(['user_id'=>$userid,'book_id'=>$bookid])->update($data);

            if($re1 && $re2 && $re3 && $re4 && $re5 && $re6 && $re7){

                Db::commit();//提交事务
                $this->redirect(url('/read/'.$bookid.'/'.$num));
            }else{
                Db::rollback();
                $this->error('全本订阅失败');
            }

        }else{

            $this->error('系统错误请重新登录','/login');
        }




    }

    /*
   * 用户多章订阅记录
   */
    public function insertMoreBuy($bookId,$vip,$count,$money,$nums){
        $total =count(explode(',',$nums));
        $data =[
            'user_id'  =>cookie('shudong_user')['user_id'],
            'book_id'  =>$bookId,
            'type'     =>5,
            'count'    =>$count,
            'money'    =>$money,
            'vip'      =>$vip,
            'total'    =>$total,
            'chapters' =>$nums,
            'dosomething' =>"订购了".$nums."等章节",
            'date'         =>date('Y-m-d H:i:s')
        ];

        $result= Db::name('UserConsumerecord')->insert($data);

        return $result;

    }

}