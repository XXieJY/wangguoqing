<?php
namespace app\index\controller;
use app\author\controller\Redis;
use think\Controller;
use think\Db;
use think\Request;
class Bookinfo extends Controller{
    public function _empty(){
        $this->error('方法不存在');
    }
    public function index($bookid,$current=1){

        if(!is_numeric($bookid)){
            $this->error('参数不合法');
        }
        $urlName =$this->check_url();
        $url='http://'.$_SERVER['HTTP_HOST'].$_SERVER['REQUEST_URI'];//获取该界面的完整的url；
        $book=$this->get_book_info($bookid);//书籍详情

        $other =$this->get_other_book($bookid);//作者其他书籍

          $gongju =new Gongju();
          $gongju->add_book_click($bookid);//更新书籍点击量

        $chapter =$this->get_book_chapter_list($bookid);//章节列表

        $message=$this->get_message($bookid,$current);//评论

        $bookType=$this->echo_book_type($bookid);//同类作品

        $exception =$this->get_new_state($bookid);

        $voteone=$this->get_vipvote($bookid);//票王

        $fans =$this->get_vote($bookid);//第一粉丝

        $fan =$this->get_fans($bookid);//铁杆粉丝

        $isCollection =$gongju->isCollection($bookid);//判断是否已加入书架

        $isSign =$this->isSign($bookid);//判断是否签约

        $wallet =$this->getWallet();//读者钱包
        $totalChapter =$this->totalChapter($bookid);//获取总章节
        $title1 =Db::name('Content')->where(['book_id'=>$bookid,'the_price'=>['gt',0]])->field('title')->order('num asc')->find();//开始收费章节
        $title2 =Db::name('Content')->where(['book_id'=>$bookid,'the_price'=>['gt',0]])->field('title')->order('num desc')->find();//最后收费章节
        $money=$this->buyFull($bookid,cookie('shudong_user')['user_id']);

       //print_r($fan);exit();
      return   $this->fetch('',[
          'ok'  =>0,
          'book'  =>$book,
          'other'  =>$other,
          'count'  =>count($other),
          'chapter'  =>$chapter,
          'count1'   =>count($chapter),
          'bookid'   =>$bookid,
          'message'  =>$message,
          'count2'   =>$this->get_message_count($bookid),
          'current'  =>$current,
         'url'     =>$url,
          'bookType' =>$bookType,
          'vote'     =>$this->echo_vote($bookid),
          'votevip'  =>$this->echo_votevip($bookid),
          'exceptional' =>$exception,
          'voteone'    =>empty($voteone)?'0':$voteone[0],
          'fans'    =>empty($fans)?'0':$fans[0],
          'fan'     =>$fan,
          'a'       =>4,
          'collection'  =>$isCollection,
          'isSign'  =>$isSign==true?'1':'0',
          'urlName'  =>$urlName,
          'wallet'     =>$wallet,
          'total' =>$totalChapter,
          'title1'  =>$title1['title'],
          'title2'   =>$title2['title'],
          'money'    =>$money
      ]);
    }
    /*
       * 钱包余额
       */
    private function getWallet(){
        if(cookie('shudong_user')){

            $useArr =cookie('shudong_user');
            $wallet= Db::name('User')->where(['user_id'=>$useArr['user_id']])->field('alance,dobing,vipvote,vote')->find();

        }else{


            $wallet=[
                'alance'  =>0,
                'dobing'  =>0,
                'vipvote' =>0,
                'vote'    =>0

            ];
        }

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
        }
        return $money;
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


      switch ($url){

          case "index.html":
              $urlName ="";
              break;
          case "ranking.html":
              $urlName ="排行";
              break;
          case "curbook.html":
              $urlName ="宅文";
              break;
          case "tongren.html":
              $urlName ="同人";
              break;
          case "Book.html":
              $urlName ="书库";
              break;
          default :
              $urlName ="";
              break;

      }

      return $urlName;


  }
    /*
     * 获取书籍的详情
     */
    public function get_book_info($bookid){

            $redis =Redis::getRedisConn();

            if($redis->get(REDIS_BOOK_INFO_PC.$bookid)){

                $book =unserialize($redis->get(REDIS_BOOK_INFO_PC.$bookid));

            }else{

                $where['Book.audit']=1;
                $where['Book.book_id']=$bookid;
                $where['Book.is_show']=1;
                $book =Db::view('Book','book_id,book_name,type_id,author_id,author_name,gender,state,vip,cp_id,level,words,book_brief,upload_img,keywords,update_time')
                    ->view('BookStatistical','click_total,collection_total,vote_total','BookStatistical.book_id=Book.book_id')
                    ->view('BookType','book_type','BookType.type_id=Book.type_id')
                    ->where($where)
                    ->find();
                $book['keywords']=$this->resave_keywords($book['keywords']);
                $book['book_brief']=str_replace("\n","</p><p style=\"text-indent: 2em;\">",$book['book_brief']);
                $book['jibie']=$this->is_author($book['author_id']);
                $book['is_vip']=$this->is_vip($book['author_id']);
                $book['portrait']=$this->portait($book['author_id']);
                $book['sex'] =$this->sex($book['author_id']);
                $redis->set(REDIS_BOOK_INFO_PC.$bookid,serialize($book),3600);
            }



         return $book;
    }
    /*
     * 获取作者头像
     */
    private function portait($author_id){

      $writer=  Db::name('Writer')->where(['author_id'=>$author_id])->find();
      $user =  Db::name('User')->where(['user_id'=>$writer['user_id']])->find();
      if(strlen($user['portrait'])<60){
          if ($user['portrait']=="user/portrait/portrait.jpg"){

              $user['portrait']="http://images.shuddd.com/user/portrait/portrait".$user['sex'].".png";

          }else{
              $user['portrait']="http://images.shuddd.com/".$user['portrait'];
          }

      }
      return $user['portrait'];

    }
    /*
     * 作者性别
     */
    private function sex($author_id){

        $writer =Db::name('Writer')->where(['author_id'=>$author_id])->find();
        return $writer['sex'];
    }
    /*
     * 修改标签
     */
    public function resave_keywords($keywords){

           $key =explode("|",$keywords);
           return $key;
    }
    /*
     * 根据author_id查找作者or读者
     */
    public function is_author($author_id){

        $author=Db::name('Writer')->field('user_id')->where(['author_id'=>$author_id])->find();
        if($author['user_id']==0){
            return 0;
        }else{

            $user=Db::name('User')->field('mem_vip')->where(['user_id'=>$author['user_id']])->find();

            return $user['mem_vip'];
        }
    }


    /*
     * 判断作者是否是vip用户
     */
    public function is_vip($author_id){
        $author=Db::name('Writer')->field('user_id')->where(['author_id'=>$author_id])->find();
        if($author['user_id']==0){
            return 0;
        }else{
            $user=Db::name('User')->field('days')->where(['user_id'=>$author['user_id']])->find();
            return $user['days'];
        }

    }
    /*
     * 作者其他作品
     */
    public function get_other_book($bookid){

       $author= Db::name('Book')->field('author_id')->where(['book_id'=>$bookid])->find();

       $where['is_show']=1;
       $where['audit']=1;
       $where['book_id']=array('neq',$bookid);
       $where['author_id'] =$author['author_id'];

       $book=Db::view('Book','book_id,book_name,upload_img,words')
                ->view('BookType','book_type','BookType.type_id=Book.type_id')
                ->view('BookStatistical','click_total','BookStatistical.book_id=Book.book_id')
                ->where($where)
                ->order('update_time desc')
                ->limit(3)
                ->select();

       return $book;
    }

    /*
     * 查询书籍章节列表
     */
    public function get_book_chapter_list($bookid){
        $redis =Redis::getRedisConn();
        if($redis->get(REDIS_CHAPTER_LIST_PC.$bookid)){

            $chapter =unserialize($redis->get(REDIS_CHAPTER_LIST_PC.$bookid));

        }else{
            $where['book_id']=$bookid;
            $where['state']=1;
            $where['status']=0;
            $where['type']=0;
            $chapter=Db::name('Content')->where($where)->field('book_id,num,the_price,title')->select();
            $redis->set(REDIS_CHAPTER_LIST_PC.$bookid,serialize($chapter),3600);
        }

        return $chapter;
    }
    /*
     * 书籍评论
     */
    public function get_message($bookid,$current){
           $list=[];
            $where['book_id']=$bookid;
            $where['f_id']=0;
            $where['status']=1;
        //分页变量
        $pageSize = 10;//每页显示的记录数
        $totalRow = 0;//总记录数
        $totalPage = 0;//总页数
        $start = ($current-1)*$pageSize;//每页记录的起始值
        $totalRow=Db::view('BookMessage','z_id,f_id,top,content,thumb,num,time')
            ->view('User','pen_name,portrait,mem_vip,days,is_author','User.user_id=BookMessage.user_id')
            ->where($where)
            ->count();
        $totalPage =ceil($totalRow/$pageSize);
            $message= Db::view('BookMessage','z_id,f_id,top,content,thumb,num,time')
                ->view('User','pen_name,portrait,mem_vip,days,is_author,sex','User.user_id=BookMessage.user_id')
                ->where($where)
                ->limit($start,$pageSize)
                ->order(['BookMessage.top'=>'desc','BookMessage.update_time'=>'desc'])
                ->select();
            $length=count($message);
            for ($i=0;$i<$length;$i++){
                $map['status']=1;
                $map['f_id']=$message[$i]['z_id'];
                $message[$i]['msg']=Db::view('BookMessage','z_id,f_id,top,content,thumb,num,time')
                    ->view('User','pen_name,portrait,mem_vip,days,sex,is_author','User.user_id=BookMessage.user_id')
                    ->where($map)
                    ->select();

                $message[$i]['isThumb'] =$this->isThumb($message[$i]['z_id']);
            }
        for ($i=0;$i<$length;$i++){
            if(strlen($message[$i]['portrait'])<60){
                if($message[$i]['portrait']=="user/portrait/portrait.jpg"){

                    $message[$i]['portrait']="http://images.shuddd.com/user/portrait/portrait".$message[$i]['sex'].".png";

                }else{
                    $message[$i]['portrait']="http://images.shuddd.com/".$message[$i]['portrait'];
                }

            }
            for ($j=0;$j<count($message[$i]['msg']);$j++){
                if(strlen($message[$i]['msg'][$j]['portrait'])<60){

                    if($message[$i]['msg'][$j]['portrait']=="user/portrait/portrait.jpg"){

                        $message[$i]['msg'][$j]['portrait']="http://images.shuddd.com/user/portrait/portrait".$message[$i]['msg'][$j]['sex'].".png";

                    }else{
                        $message[$i]['msg'][$j]['portrait']="http://images.shuddd.com/".$message[$i]['msg'][$j]['portrait'];
                    }

                }
            }
        }
            $list=[
                'message'=>$message,
                'page'   =>$totalPage
            ];
           // print_r($list);exit();
         return $list;
    }

  /*
   * 判断用户是否点赞
   *
   */
 private function isThumb($zid){
     $useArr =cookie('shudong_user');
     $where['message_id'] =$zid;
     $where['user_id'] =$useArr['user_id'];
     $result =  Db::name('MessageThumb')->where($where)->find();
     if(!(is_array($result))){

         return 0;
     }
     if($result['status']==1){
         return 1;
     }else{
         return 0;
     }

 }

   /*
    * 获取书籍评论条数
    */
   public function get_message_count($bookid){
       $where['book_id']=$bookid;
       $where['f_id']=0;
       $where['status']=1;
       $message= Db::view('BookMessage','z_id,f_id,top,content,thumb,num,time')
           ->view('User','pen_name,portrait,mem_vip,days,is_author','User.user_id=BookMessage.user_id')
           ->where($where)
           ->order(['BookMessage.top'=>'desc','BookMessage.update_time'=>'desc'])
           ->select();
       $length=count($message);

       return $length;

   }
    /*
     * 同类书籍推荐
     */
    public function echo_book_type($bookid){
         $redis =Redis::getRedisConn();
         if($redis->get(REDIS_BOOK_INFO_TUIJIAN_PC.$bookid)){

              $book =unserialize($redis->get(REDIS_BOOK_INFO_TUIJIAN_PC.$bookid));
         }else{
             $where['is_show']=1;
             $where['audit']=1;
             $where['book_id']=array('neq',$bookid);

             $type=Db::name('Book')->field('type_id')->where(['book_id'=>$bookid])->find();
             $where['Book.type_id']=$type['type_id'];
             $book=Db::view('Book','book_id,book_name,upload_img,words,author_id,author_name,book_brief,level')
                 ->view('BookType','book_type','BookType.type_id=Book.type_id')
                 ->view('BookStatistical','click_total','BookStatistical.book_id=Book.book_id')
                 ->where($where)
                 ->limit(12)
                 ->select();
             for ($i=0;$i<count($book);$i++){
                 $book[$i]['portrait'] =getUserImage($book[$i]['author_id']);
             }
             $redis->set(REDIS_BOOK_INFO_TUIJIAN_PC.$bookid,serialize($book),3600);

         }

          return $book;
    }
    /*
     * 推荐票
     */
    public function echo_vote($bookid){

        $vote=Db::name('BookStatistical')->field('vote_total')->where(['book_id'=>$bookid])->find();
        return $vote['vote_total'];

    }
    /*
     * 月票
     */
    public function echo_votevip($bookid){

        $votevip=Db::name('BookStatistical')->field('vipvote_total')->where(['book_id'=>$bookid])->find();
        return $votevip['vipvote_total'];

    }
    /*
     * 动态打赏图
     */
    public function get_new_state($bookid){

        $where['UserConsumerecord.type']=array('in','3,4');
        $where['UserConsumerecord.book_id']=$bookid;
        $exceptional=Db::view('UserConsumerecord','dosomething,date')
            ->view('User','pen_name','User.user_id=UserConsumerecord.user_id')
            ->where($where)
            ->order('date desc')
            ->limit(5)
            ->select();
        $count =count($exceptional);
        for ($i=0;$i<$count;$i++){
            $exceptional[$i]['time']=$this->getTime($exceptional[$i]['date']);
        }
        return $exceptional;

    }
  private  function getTime($time)
    {

        //获取今天凌晨的时间戳
        $day = strtotime(date('Y-m-d 00:00:00'));
        //获取昨天凌晨的时间戳
        $pday = strtotime(date('Y-m-d 00:00:00',strtotime('-1 day')));
        //获取现在的时间戳
        $nowtime = strtotime(date('Y-m-d H:i:s'));

        $tc = $nowtime-strtotime($time);
        if(strtotime($time)<$pday){
            $str = date('m-d',strtotime($time));
        }elseif(strtotime($time)<$day && strtotime($time)>$pday){
            $str = "昨天";
        }elseif($tc>60*60){
            $str = floor($tc/(60*60))."小时前";
        }elseif($tc>60){
            $str = floor($tc/60)."分钟前";
        }else{
            $str = "刚刚";
        }
        return $str;
    }
    /*
    * 票王
    */
    public function get_vipvote($bookid){
        $sql="SELECT a.user_id,b.pen_name,b.mem_vip,b.days,b.portrait,b.sex,SUM(a.count) AS m FROM shudong_user_consumerecord a INNER JOIN shudong_user b ON a.user_id=b.user_id WHERE a.type=? AND a.book_id=? GROUP BY a.user_id ORDER BY m DESC LIMIT 1";
        $vipvote=Db::query($sql,[3,$bookid]);
        foreach ($vipvote as $k=>$v){
           if(strlen( $vipvote[$k]['portrait'])<60){

               if($vipvote[$k]['portrait']=="user/portrait/portrait.jpg"){

                   $vipvote[$k]['portrait']="http://images.shuddd.com/user/portrait/portrait".$vipvote[$k]['sex'].".png";

               }else{
                   $vipvote[$k]['portrait']="http://images.shuddd.com/".$vipvote[$k]['portrait'];
               }
           }
        }
        return $vipvote;
    }
    /*
     * 第一粉丝
     */
    public function get_vote($bookid){
        $sql="SELECT a.user_id,b.pen_name,b.mem_vip,b.days,b.portrait,b.sex,SUM(a.count) AS m FROM shudong_user_consumerecord a INNER JOIN shudong_user b ON a.user_id=b.user_id WHERE a.type=? AND a.book_id=? GROUP BY a.user_id ORDER BY m DESC LIMIT 1";
        $vote=Db::query($sql,[2,$bookid]);
        foreach ($vote as $k=>$v){
            if(strlen( $vote[$k]['portrait'])<60){
                if($vote[$k]['portrait']=="user/portrait/portrait.jpg"){

                    $vote[$k]['portrait']="http://images.shuddd.com/user/portrait/portrait".$vote[$k]['sex'].".png";

                }else{
                    $vote[$k]['portrait']="http://images.shuddd.com/".$vote[$k]['portrait'];
                }
            }
        }
        return $vote;

    }
    /*
     * 铁杆粉丝榜
     */
    public function get_fans($bookid){

        $fans=Db::view('BookFans','user_id,fan_value')
            ->view('User','sex,portrait,pen_name','User.user_id=BookFans.user_id')
            ->where(['BookFans.book_id'=>$bookid])
            ->order('fan_value desc')
            ->limit(20)
            ->select();
        for ($i=0;$i<count($fans);$i++){
            if(strlen($fans[$i]['portrait'])<60){
                if($fans[$i]['portrait']=="user/portrait/portrait.jpg"){

                    $fans[$i]['portrait']="http://images.shuddd.com/user/portrait/portrait".$fans[$i]['sex'].".png";

                }else{
                    $fans[$i]['portrait']="http://images.shuddd.com/".$fans[$i]['portrait'];
                }
            }
        }
       
        return $fans;
    }

    /*
     * 判断该书是否签约
     */
    private function isSign($bookId){

         if(!is_numeric($bookId)){
             $this->error('参数错误');
         }
       $book=  Db::name('Book')->where(['book_id'=>$bookId])->field('contract_id,sign_id')->find();
       if($book['contract_id']>=2 && $book['sign_id']==4){
           return true;
       }else{
           return false;
       }
    }

  public function buyOrder(){
        $res =input('post.');
       if($res['type']==1){
               //全本订阅
           $this->buyAllChapter($res['bookid'],$res['userid']);
       }else{
              //自动订阅下一章
           $this->ziBuy($res['bookid'],$res['userid']);
       }

  }

    /*
     * 自动订阅
     */

    public function ziBuy($bookid,$userid){

      $buy=  Db::name('BookBuy')->where(['book_id'=>$bookid,'user_id'=>$userid])->find();
      if($buy['is_type']==1){

          $this->error('你已经自动订阅该书籍');
      }else{

          $result=   Db::name('BookBuy')->where(['book_id'=>$bookid,'user_id'=>$userid])->update(['is_type'=>1]);
          if($result){

              $this->redirect(url('/bookinfo/'.$bookid));
          }else{
              $this->error('自动订阅失败');
          }
      }

    }

    /*
     * 全本订阅
     */
    public function buyAllChapter($bookid,$userid){

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
        $user =Db::name('User')->where(['user_id'=>$userid])->find();
        $vip =$user['days'];
        if($vip>0){
            //vip用户
            $bookMoney =$this->buyFull($bookid,$userid);//全本购买剩余章节所需要的金额

            $useMoney =$this->getWallet();//用户钱包的余额

            if($useMoney['alance']<$bookMoney){

                $this->error('余额不足，请充值');
                exit();
            }
            //在这里处理VIP用户全本订阅的逻辑块
            $this->buyFullBookVip($bookid,$userid,$bookMoney,1,$nums);
        }else{
            //普通用户
            $bookMoney =$this->buyFull($bookid,$userid);//全本购买剩余章节所需要的金额

            $useMoney =$this->getWallet();//用户钱包的余额

            if($useMoney['alance']<$bookMoney){

                $this->error('余额不足，请充值');
                exit();
            }
            //在这里处理普通用户全本订阅的逻辑块

            $this->buyFullBookNoVip($bookid,$userid,$bookMoney,0,$nums);

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
    public function buyFullBookNoVip($bookid,$userid,$money,$type,$nums){

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
                $this->redirect(url('/bookinfo/'.$bookid));
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
    public function buyFullBookVip($bookid,$userid,$money,$type,$nums){

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
            $re3 =$gongju->adddycVIPFull($bookid,$nums);//vip用户咚币订阅次数

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
                $this->redirect(url('/bookinfo/'.$bookid));
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