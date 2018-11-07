<?php
namespace app\author\controller;
use think\Controller;
use think\Db;
use think\Request;
class Wallet extends Base{
    public function index(){
        $author =$this->account;
        $book =get_book($author['author_id']);
        if(request()->isPost()){
            $bookName=input('post.bookName');
        }elseif(request()->isGet()){
            $bookName=input('get.bookName');
            if($bookName==""){
                $bookName=$book[0]['book_name'];
            }
        }

       $content= $this->buyBook($bookName);//章节订阅
       $con =$content->toArray();
      $totalBuy= $this->totalBuy($bookName);//章节总订阅
      $yTotal =$this->yTotal($bookName);//昨日新增
      $num =$this->vipChapter($bookName);//vip章节数

        $totalChapter =$this->totalChapter($bookName);//章节最高订阅
      // print_r($con);exit();
        return $this->fetch('',[
            'book'   =>$book,
            'bookName' =>$bookName,
            'content'  =>$con['data'],
            'page'     =>$content,
            'total'    =>$con['total'],
            'a'        =>($con['current_page']-1)*10,
            'totalBuy' =>$totalBuy,
            'yTotal'  =>$yTotal,
            'average'  =>$num==0?0:round($totalBuy/$num),
            'totalChapter'  =>$totalChapter
        ]);
    }
    //章节订阅
    public function buyBook($bookName){
        $book=Db::name('Book')->where(['book_name'=>trim($bookName)])->find();
       $content= Db::name('Content')->where(['book_id'=>$book['book_id'],'type'=>0])
           ->paginate(10, false, [
               'query' => Request::instance()->param(),//不丢失已存在的url参数
           ]);
       return $content;

    }
    //章节总订阅
    public function totalBuy($bookName){

        $book=Db::name('Book')->where(['book_name'=>trim($bookName)])->find();
       $dyc= Db::name('Content')->where(['book_id'=>$book['book_id'],'type'=>0])->sum('dyc');
        $vipdyc= Db::name('Content')->where(['book_id'=>$book['book_id'],'type'=>0])->sum('vipdyc');
       $total =$dyc+$vipdyc;
       return $total;
    }
    //昨日新增
    public function yTotal($bookName){
        $book=Db::name('Book')->where(['book_name'=>trim($bookName)])->find();
        $time =date('Y-m-d',strtotime('-1 day'));
        $where=[
            'book_id' =>$book['book_id'],
            'date'  =>['like',"%$time%"],
            'type'  =>1,
            'money'  =>['gt',0]
       ];
      $yTotal= Db::name('UserConsumerecord')->where($where)->count('money');

        return $yTotal;
    }
    //vip章节数
    public function vipChapter($bookName){
        $book=Db::name('Book')->where(['book_name'=>trim($bookName)])->find();
       $num= Db::name('Content')->where(['book_id'=>$book['book_id'],'the_price'=>['neq',0]])->count('title');

        return $num;

    }

   //章节最高订阅数
    public function totalChapter($bookName){

        $book=Db::name('Book')->where(['book_name'=>trim($bookName)])->find();
        $content= Db::name('Content')->where(['book_id'=>$book['book_id'],'type'=>0])->field('content_id,dyc,vipdyc')->select();
       foreach ($content as $k=>$v){
           $content[$k]['total']=$v['dyc']+$v['vipdyc'];
       }
       if(count($content)>0){

           foreach ($content as $k=>$v) {
               $a[] = $v['total'];
           }
           //按照age升序排列
           array_multisort($a, SORT_DESC, $content);
       }


        return $content[0]['total'];
    }

  //道具
    public function obtain(){

        $author =$this->account;
        $book =get_book($author['author_id']);

        if(request()->isPost()){
            $res =input('post.');

            $bookName  =trim($res['bookName']);
            if($res['month']){
                $time =$res['month'];
            }else{
                $time="";
            }
            if($res['prop']=="打赏"){
                $type =4;
            }elseif ($res['prop']=="月票"){
                $type=3;
            }elseif ($res['prop']=="推荐票"){
                $type=2;
            }else{
                $type=0;
            }
            if($time=="" && $type==0){
                $dasahngTotal= $this->getObtainInfo($bookName);

            }elseif ($time=="" && $type!=0){
                $dasahngTotal= $this->getObtainInfo($bookName,'',$type);
            }elseif ($time!="" && $type!=0){
                $dasahngTotal= $this->getObtainInfo($bookName,$time,$type);
            }elseif ($time!="" && $type==0){
                $dasahngTotal= $this->getObtainInfo($bookName,$time);
            }
            $dasahng=$dasahngTotal->toArray();
            // print_r($dasahng);exit();
            $shang=$this->dashangTotal($bookName);//总打赏
            $vipvote =$this->vipvoteTotal($bookName);//总月票
            $vote =$this->voteTotal($bookName);//总推荐票

            return $this->fetch('',[
                'book'   =>$book,
                'obtain' =>$dasahng,
                'bookName' =>$bookName,
                'shang'   =>$shang,
                'vipvote' =>$vipvote,
                'vote'   =>$vote,
                'page'  =>$dasahngTotal,
                'a'    =>($dasahng['current_page']-1)*10
            ]);

        }elseif (request()->isGet()){
            $res =input('get.');
            $bookName  =trim($res['bookName']);
            if($bookName==""){
                $bookName=$book[0]['book_name'];
            }
            if($res['month']){
                $time =$res['month'];
            }else{
                $time="";
            }
            if($res['prop']=="打赏"){
                $type =4;
            }elseif ($res['prop']=="月票"){
                $type=3;
            }elseif ($res['prop']=="推荐票"){
                $type=2;
            }else{
                $type=0;
            }
            if($time=="" && $type==0){
                $dasahngTotal= $this->getObtainInfo($bookName);

            }elseif ($time=="" && $type!=0){
                $dasahngTotal= $this->getObtainInfo($bookName,'',$type);
            }elseif ($time!="" && $type!=0){
                $dasahngTotal= $this->getObtainInfo($bookName,$time,$type);
            }elseif ($time!="" && $type==0){
                $dasahngTotal= $this->getObtainInfo($bookName,$time);
            }
            $dasahng=$dasahngTotal->toArray();
            // print_r($dasahng);exit();
            $shang=$this->dashangTotal($bookName);//总打赏
            $vipvote =$this->vipvoteTotal($bookName);//总月票
            $vote =$this->voteTotal($bookName);//总推荐票

            return $this->fetch('',[
                'book'   =>$book,
                'obtain' =>$dasahng,
                'bookName' =>$bookName,
                'shang'   =>$shang,
                'vipvote' =>$vipvote,
                'vote'   =>$vote,
                'page'  =>$dasahngTotal,
                'a'    =>($dasahng['current_page']-1)*10
            ]);

        }
   }


   /*
    * 总打赏
    */
   private function dashangTotal($bookName){
       $book=Db::name('Book')->where(['book_name'=>trim($bookName)])->find();
       $where =[
           'book_id'  =>$book['book_id'],
           'type'    =>4,

       ];
      $count= Db::name('UserConsumerecord')->where($where)->sum('money');

      return $count;

   }
   /*
    * 总月票
    */
    private function vipvoteTotal($bookName){
        $book=Db::name('Book')->where(['book_name'=>trim($bookName)])->find();
        $where =[
            'book_id'  =>$book['book_id'],
            'type'    =>3,

        ];
        $count= Db::name('UserConsumerecord')->where($where)->sum('count');

        return $count;

    }

    /*
   * 总推荐票
   */
    private function voteTotal($bookName){
        $book=Db::name('Book')->where(['book_name'=>trim($bookName)])->find();
        $where =[
            'book_id'  =>$book['book_id'],
            'type'    =>2,

        ];
        $count= Db::name('UserConsumerecord')->where($where)->sum('count');

        return $count;

    }
 /*
  * 查询道具数据
  * $time 查询时间
  * $type 查询方式
  */
   public function getObtainInfo($bookName,$time='',$type=0)
   {
       $book=Db::name('Book')->where(['book_name'=>trim($bookName)])->find();
       if($time=="" && $type==0){
           $where =[
               'book_id'  =>$book['book_id'],
               'type'    =>['in','2,3,4']
           ];

       }elseif ($time=="" && $type!=0){
           $where =[
               'book_id'  =>$book['book_id'],
               'type'    =>$type
           ];
       }elseif ($time!="" && $type!=0){
           $where =[
               'date'  =>['like',"%$time%"],
               'book_id'  =>$book['book_id'],
               'type'    =>$type
           ];

       }elseif ($time!="" && $type==0){

           $where =[
               'date'  =>['like',"%$time%"],
               'book_id'  =>$book['book_id'],
               'type'    =>['in','2,3,4']
           ];
       }

       $dashang= Db::view('UserConsumerecord','type,count,dosomething,date')
           ->view('User','pen_name','UserConsumerecord.user_id=User.user_id','left')
           ->where($where)
           ->order('UserConsumerecord.date desc')
           ->paginate(10, false, [
               'query' => Request::instance()->param(),//不丢失已存在的url参数
           ]);
       //print_r($dashang);exit();
       return $dashang;
   }


    //收入
    public function income(){

        $author =$this->account;
        $book =get_book($author['author_id']);
        if(request()->isPost()){
            $res =input('post.');
            $time =$res['month'];
            $BeginDate=date('Y-m-01', strtotime(date("Y-m-d")));
            $LastData= date('Y-m-d', strtotime("$BeginDate +1 month -1 day"));
            if($LastData==date('Y-m-d')){
                $isok =1;
            }else{
                $isok =0;
            }
            if($time==date('Y-m')){
                $auth =1;
            }else{
                $auth =0;
            }
            if($res['bookName']=="全部"){

             //   print_r($time);exit();
                $income= $this->getBookIncome($author['author_id'],'',$time);//订阅汇总
                $dashang=$this->getDasahngBook($author['author_id'],'',$time);//打赏汇总
                $vipvote =$this->getVipvoteBook($author['author_id'],'',$time);//月票汇总
                $quanqin =$this->getQuanqinBook($author['author_id'],'',$time);//全勤统计
                $money =$this->getAllMoney($author['author_id'],'',$time);//总收入
                return $this->fetch('',[
                    'book'   =>$book,
                    'income'  =>$income,
                    'time'   =>$time,
                    'dashang'  =>$dashang,
                    'vipvote'  =>$vipvote,
                    'quanqin'  =>$quanqin,
                    'money'  =>$money,
                    'bookName'  =>$res['bookName'],
                    'isok'  =>$isok,
                    'auth'  =>$auth

                ]);
            }else{
                $bookName =$res['bookName'];
                $income= $this->getBookIncome('',$bookName,$time);//订阅汇总
                $dashang=$this->getDasahngBook('',$bookName,$time);//打赏汇总
                $vipvote =$this->getVipvoteBook('',$bookName,$time);//月票汇总
                $quanqin =$this->getQuanqinBook('',$bookName,$time);//全勤统计
                $money =$this->getAllMoney('',$bookName,$time);//总收入
                return $this->fetch('',[
                    'book'   =>$book,
                    'income'  =>$income,
                    'time'   =>$time,
                    'dashang'  =>$dashang,
                    'vipvote'  =>$vipvote,
                    'quanqin'  =>$quanqin,
                    'money'  =>$money,
                    'bookName'  =>$bookName,
                    'isok'   =>$isok,
                    'auth'   =>$auth

                ]);

            }

        }else{
            $time=date('Y-m');
            $income= $this->getBookIncome($author['author_id'],'',$time);//订阅汇总
            $dashang=$this->getDasahngBook($author['author_id'],'',$time);//打赏汇总
            $vipvote =$this->getVipvoteBook($author['author_id'],'',$time);//月票汇总
            $quanqin =$this->getQuanqinBook($author['author_id'],'',$time);//全勤统计
            $money =$this->getAllMoney($author['author_id'],'',$time);//总收入

            $BeginDate=date('Y-m-01', strtotime(date("Y-m-d")));
            $LastData= date('Y-m-d', strtotime("$BeginDate +1 month -1 day"));
            if($LastData==date('Y-m-d')){
                $isok =1;
            }else{
                $isok =0;
            }
            if($time==date('Y-m')){
                $auth =1;
            }else{
                $auth =0;
            }

            return $this->fetch('',[
                'book'   =>$book,
                'income'  =>$income,
                'time'   =>$time,
                'dashang'  =>$dashang,
                'vipvote'  =>$vipvote,
                'quanqin'  =>$quanqin,
                'money'  =>$money,
                'bookName'  =>'全部',
                'isok'   =>$isok,
                'auth'   =>$auth
            ]);
        }
    }
    /*
     * 获取总收入
     */
    public function getAllMoney($authorId='',$bookName='',$time){
        if($authorId!="" && $bookName=="") {
            $where = [
                'author_id' => $authorId,
                'is_show' => 1,
                'audit' => 1
            ];
        }
        if($authorId=="" && $bookName!=""){
            $where = [
                'book_name' => $bookName,
                'is_show' => 1,
                'audit' => 1
            ];

        }
        $book =Db::name('Book')->where($where)->field('book_id,book_name')->select();
        $count=count($book);
        $ids=[];
        for ($i=0;$i<$count;$i++){
            $ids[]=$book[$i]['book_id'];
        }
        $id =implode(',',$ids);
        $vipmoney =$this->getAllBookvipBuy($id,$time);
        $money =$this->getAllBookBuy($id,$time);
        $dashang =$this->getAllDashang($id,$time);
        $vipvote =$this->getAllVipVote($id,$time);

        $totalMoney =round($vipmoney*0.9+$money*0.9*0.6+$dashang*0.9*0.7+$vipvote*0.5,2);
        return $totalMoney;
    // print_r($totalMoney);exit();
    }
    /*
     * 订阅vip总金额
     */
    private function getAllBookvipBuy($id,$time){
        $where=[
            'date' =>['like',"%$time%"],
            'book_id'  =>['in',$id],
            'type'   =>['in','1,5'],
            'vip'    =>1
        ];
        $vipmoney =Db::name('UserConsumerecord')->where($where)->sum('money');

        return round($vipmoney/100,2);

    }
    /*
     * 订阅普通总金额
     */
    private function getAllBookBuy($id,$time){
        $where=[
            'date' =>['like',"%$time%"],
            'book_id'  =>['in',$id],
            'type'   =>['in','1,5'],
            'vip'    =>0
        ];
        $money =Db::name('UserConsumerecord')->where($where)->sum('money');

        return round($money/100,2);

    }
    /*
     * 打赏总金额
     */
    private function getAllDashang($id,$time){
        $where=[
            'date' =>['like',"%$time%"],
            'book_id'  =>['in',$id],
            'type'   =>4,
        ];
        $money=  Db::name('UserConsumerecord')->where($where)->sum('money');
        return round($money/100,2);

    }
    /*
     * 月票总金额
     */
    private function getAllVipVote($id,$time){
        $where=[
            'date' =>['like',"%$time%"],
            'book_id'  =>['in',$id],
            'type'   =>3,
        ];
        $total=  Db::name('UserConsumerecord')->where($where)->sum('count');
        return $total;

    }
 /*
  * 订阅汇总
  */
    public function getBookIncome($authorId='',$bookName='',$time){

          if($authorId!="" && $bookName=="") {
              $where = [
                  'author_id' => $authorId,
                  'is_show' => 1,
                  'audit' => 1
              ];
          }
          if($authorId=="" && $bookName!=""){

              $where = [
                  'book_name' => $bookName,
                  'is_show' => 1,
                  'audit' => 1
              ];
          }
              $book =Db::name('Book')->where($where)->field('book_id,book_name')->select();
              $count=count($book);
              for ($i=0;$i<$count;$i++){
                  $book[$i]['people']=$this->getOneBookPeople($book[$i]['book_id'],$time);
                  $book[$i]['chapter'] =$this->getOneChapter($book[$i]['book_id'],$time);
                  $book[$i]['vipMoney'] =$this->getVipBuyBook($book[$i]['book_id'],$time);
                  $book[$i]['money']=$this->getNoVipBuyBook($book[$i]['book_id'],$time);
                  $book[$i]['alance']=round($book[$i]['vipMoney']/100*0.9+$book[$i]['money']/100*0.9*0.6,2);
              }
            return $book;


    }

    //获取单本书的订阅总人数
    private function getOneBookPeople($bookId,$time){

        if(!is_numeric($bookId)){
            $this->error('参数错误');
        }
        $where=[
            'date' =>['like',"%$time%"],
            'book_id'  =>$bookId,
            'type'   =>['in','1,5']

        ];
       $people= Db::name('UserConsumerecord')->where($where)->group('user_id')->count('user_id');
       return $people;
    }
    //订阅章节总量
    private function getOneChapter($bookId,$time){

        if(!is_numeric($bookId)){
            $this->error('参数错误');
        }
        $where=[
            'date' =>['like',"%$time%"],
            'book_id'  =>$bookId,
            'type'   =>['in','1,5'],
            'money'   =>['gt',0]
        ];
        $chapter= Db::name('UserConsumerecord')->where($where)->sum('total');
        return $chapter;

    }
    //vip用户订阅总额
    private function getVipBuyBook($bookId,$time){

        if(!is_numeric($bookId)){
            $this->error('参数错误');
        }
        $where=[
            'date' =>['like',"%$time%"],
            'book_id'  =>$bookId,
            'type'   =>['in','1,5'],
            'vip'   =>1
        ];
        $vipMoney =Db::name('UserConsumerecord')->where($where)->sum('money');

        return $vipMoney;

    }
    //普通用户订阅总额
    private function getNoVipBuyBook($bookId,$time){

        if(!is_numeric($bookId)){
            $this->error('参数错误');
        }
        $where=[
            'date' =>['like',"%$time%"],
            'book_id'  =>$bookId,
            'type'   =>['in','1,5'],
            'vip'   =>0
        ];
        $Money =Db::name('UserConsumerecord')->where($where)->sum('money');

        return  $Money;

    }
  /*
   * 打赏汇总
   */
  public function getDasahngBook($authorId='',$bookName="",$time){

      if($authorId!="" && $bookName=="") {
          $where = [
              'author_id' => $authorId,
              'is_show' => 1,
              'audit' => 1
          ];

      }
      if($authorId=="" && $bookName!=""){
          $where = [
              'book_name' => $bookName,
              'is_show' => 1,
              'audit' => 1
          ];

      }
          $book =Db::name('Book')->where($where)->field('book_id,book_name')->select();
          $count=count($book);
          for ($i=0;$i<$count;$i++){
                $book[$i]['people'] =$this->getDashangPeople($book[$i]['book_id'],$time);
                $book[$i]['money'] =$this->getDashangMoney($book[$i]['book_id'],$time);
                $book[$i]['alance']=round($book[$i]['money']/100*0.9*0.7,2);
          }
         return $book;

  }
   /*
    * 打赏总人数
    */
   private function getDashangPeople($bookId,$time){

       if(!is_numeric($bookId)){
           $this->error('参数错误');
       }
       $where=[
           'date' =>['like',"%$time%"],
           'book_id'  =>$bookId,
           'type'   =>4,
       ];
       $people=  Db::name('UserConsumerecord')->where($where)->group('user_id')->count();
       return $people;
   }
   /*
    * 打赏总金额
    */
   private function getDashangMoney($bookId,$time){
       if(!is_numeric($bookId)){
           $this->error('参数错误');
       }
       $where=[
           'date' =>['like',"%$time%"],
           'book_id'  =>$bookId,
           'type'   =>4,
       ];
       $money=  Db::name('UserConsumerecord')->where($where)->sum('money');
       return $money;
   }

   /*
    * 月票汇总
    */
   public function getVipvoteBook($authorId='',$bookName="",$time){

       if($authorId!="" && $bookName=="") {
           $where = [
               'author_id' => $authorId,
               'is_show' => 1,
               'audit' => 1
           ];
       }
       if($authorId=="" && $bookName!=""){

           $where = [
               'book_name' => $bookName,
               'is_show' => 1,
               'audit' => 1
           ];
       }
       $book =Db::name('Book')->where($where)->field('book_id,book_name')->select();
       $count=count($book);
       for ($i=0;$i<$count;$i++){
            $book[$i]['people']  =$this->getVipvotePeople($book[$i]['book_id'],$time);
            $book[$i]['total'] =$this->getVipvoteTotal($book[$i]['book_id'],$time);
            $book[$i]['alance'] =round($book[$i]['total']*0.5,2);
       }

         return $book;
   }
  /*
   * 月票总人数
   */
   private function getVipvotePeople($bookId,$time){
       if(!is_numeric($bookId)){
           $this->error('参数错误');
       }
       $where=[
           'date' =>['like',"%$time%"],
           'book_id'  =>$bookId,
           'type'   =>3,
       ];
       $people=  Db::name('UserConsumerecord')->where($where)->group('user_id')->count();
       return $people;

   }

   /*
    * 月票总量
    */
   private function getVipvoteTotal($bookId,$time){
       if(!is_numeric($bookId)){
           $this->error('参数错误');
       }
       $where=[
           'date' =>['like',"%$time%"],
           'book_id'  =>$bookId,
           'type'   =>3,
       ];
       $total=  Db::name('UserConsumerecord')->where($where)->sum('count');
       return $total;

   }

   /*
    * 全勤统计
    *
    */
   public function getQuanqinBook($authorId='',$bookName='',$time){

      $month =date('Y-m');
      if($month==$time){
          if($authorId!="" && $bookName=="") {
              $where = [
                  'author_id' => $authorId,
                  'is_show' => 1,
                  'audit' => 1
              ];
          }
          if($authorId=="" && $bookName!=""){
              $where = [
                  'book_name' => $bookName,
                  'is_show' => 1,
                  'audit' => 1
              ];

          }
          $book =Db::name('Book')->where($where)->field('book_id,book_name,contract_id,full_id,full')->select();
          $count=count($book);
          for ($i=0;$i<$count;$i++){
              $book[$i]['isQuan'] =$this->isQuanqin($book[$i]['book_id']);
              $book[$i]['contract'] =$this->contract($book[$i]['contract_id']);
              $book[$i]['word'] =$this->full_id($book[$i]['full_id']);
              if($book[$i]['full']<=1){
                  if($book[$i]['contract_id']==3){
                      if($book[$i]['full_id']==2){
                          $book[$i]['money']=200;
                      }elseif ($book[$i]['full_id']==3){
                          $book[$i]['money'] =400;
                      }elseif ($book[$i]['full_id']==4){
                          $book[$i]['money'] =600;
                      }else{
                          $book[$i]['money'] =0;
                      }
                  }elseif ($book[$i]['contract_id']==4){
                      if($book[$i]['full_id']==2){
                          $book[$i]['money']=300;
                      }elseif ($book[$i]['full_id']==3){
                          $book[$i]['money'] =500;
                      }elseif ($book[$i]['full_id']==4){
                          $book[$i]['money'] =800;
                      }else{
                          $book[$i]['money'] =0;
                      }

                  }elseif ($book[$i]['contract_id']==5){
                      if($book[$i]['full_id']==2){
                          $book[$i]['money']=400;
                      }elseif ($book[$i]['full_id']==3){
                          $book[$i]['money'] =700;
                      }elseif ($book[$i]['full_id']==4){
                          $book[$i]['money'] =1000;
                      }else{
                          $book[$i]['money'] =0;
                      }
                  }elseif ($book[$i]['contract_id']==6){
                      if($book[$i]['full_id']==2){
                          $book[$i]['money']=600;
                      }elseif ($book[$i]['full_id']==3){
                          $book[$i]['money'] =1000;
                      }elseif ($book[$i]['full_id']==4){
                          $book[$i]['money'] =1800;
                      }else{
                          $book[$i]['money'] =0;
                      }
                  }elseif ($book[$i]['contract_id']==7 || $book[$i]['contract_id']==8){

                      if($book[$i]['full_id']==3){
                          $book[$i]['money']=500;
                      }elseif ($book[$i]['full_id']==4){
                          $book[$i]['money'] =800;
                      }else{
                          $book[$i]['money'] =0;
                      }
                  }else{
                      $book[$i]['money'] =0;
                  }
              }else{
                  $book[$i]['money'] =0;
              }
          }

      }else{

         $book= $this->lastQuanqin($authorId,$bookName,$time);
      }



      return $book;

   }

   /*
    * 上月全勤统计
    */
   public function lastQuanqin($authorId='',$bookName,$time){

       if($authorId!="" && $bookName=="") {
           $where = [
               'author_id' => $authorId,
               'is_show' => 1,
               'audit' => 1
           ];
       }
       if($authorId=="" && $bookName!=""){
           $where = [
               'book_name' => $bookName,
               'is_show' => 1,
               'audit' => 1
           ];
       }
       $book =Db::name('Book')->where($where)->field('book_id,book_name,contract_id,full_id,full')->select();
       foreach ($book as $k=>$v){
              $v['quanqin'] =$this->quanqin($v['book_id'],$time);
              $book[$k]['money'] =empty($v['quanqin']['money'])? '0':$v['quanqin']['money'];
              if($book[$k]['money']==0){
                  $book[$k]['full'] =30;
              }else{
                  $book[$k]['full'] =0;
                  $book[$k]['contract'] =$this->contract($v['quanqin']['contract']);
                  $book[$k]['word'] =$this->full_id($v['quanqin']['full_id']);
              }

       }
      // print_r($book);exit();

       return $book;

   }
    //上月全勤计算
    public function quanqin($bookId,$time){
        if(!is_numeric($bookId)){
            $this->error('参数错误');
        }

        $where['time'] =array('like',"%$time%");
        $book= Db::name('BookQuanqin')->where($where)->select();

        foreach ($book as $k=>$v){
            if($v['book_id']==$bookId){
                //全勤奖
                if($v['contract']==3 && $v['full_id']==2){
                    $arr =[
                        'money'  =>300,
                        'contract' =>3,
                        'full_id'  =>2
                    ];

                }elseif ($v['contract']==3 && $v['full_id']==3){

                    $arr =[
                        'money'  =>500,
                        'contract' =>3,
                        'full_id'  =>3
                    ];
                }elseif ($v['contract']==3 && $v['full_id']==4){

                    $arr =[
                        'money'  =>800,
                        'contract' =>3,
                        'full_id'  =>4
                    ];
                }elseif ($v['contract']==4 && $v['full_id']==2){
                    $arr =[
                        'money'  =>400,
                        'contract' =>4,
                        'full_id'  =>2
                    ];

                }elseif ($v['contract']==4 && $v['full_id']==3){
                    $arr =[
                        'money'  =>700,
                        'contract' =>4,
                        'full_id'  =>3
                    ];

                }elseif ($v['contract']==4 && $v['full_id']==4){
                    $arr =[
                        'money'  =>1000,
                        'contract' =>4,
                        'full_id'  =>4
                    ];

                }elseif ($v['contract']==5 && $v['full_id']==2){
                    $arr =[
                        'money'  =>600,
                        'contract' =>5,
                        'full_id'  =>2
                    ];

                }elseif ($v['contract']==5 && $v['full_id']==3){
                    $arr =[
                        'money'  =>1000,
                        'contract' =>5,
                        'full_id'  =>3
                    ];

                }elseif ($v['contract']==5 && $v['full_id']==4){
                    $arr =[
                        'money'  =>1500,
                        'contract' =>5,
                        'full_id'  =>4
                    ];

                }elseif ($v['contract']==6 && $v['full_id']==2){
                    $arr =[
                        'money'  =>800,
                        'contract' =>6,
                        'full_id'  =>2
                    ];

                }elseif ($v['contract']==6 && $v['full_id']==3){
                    $arr =[
                        'money'  =>1500,
                        'contract' =>6,
                        'full_id'  =>3
                    ];

                }elseif ($v['contract']==6 && $v['full_id']==4){
                    $arr =[
                        'money'  =>2400,
                        'contract' =>6,
                        'full_id'  =>4
                    ];

                }elseif ($v['contract']>=7 && $v['full_id']==3){
                    $arr =[
                        'money'  =>500,
                        'contract' =>$v['contract'],
                        'full_id'  =>3
                    ];

                }elseif ($v['contract']>=7 && $v['full_id']==5){
                    $arr =[
                        'money'  =>800,
                        'contract' =>$v['contract'],
                        'full_id'  =>5
                    ];

                }

                return $arr;

            }else{


            }


        }

    }

   /*
    * 根据book_id判断某本书本月是否符合全勤
    */
   public function isQuanqin($bookId){
       if(!is_numeric($bookId)){
           $this->error('参数错误');
       }
       $where=[
           'Book.book_id' =>$bookId,
           'Book.is_show'=>1,
           'Book.audit'=>1,
           'Content.type'=>0,
           'Content.num'=>1
       ];
       $shijian =date('Y-m-2 23:59:59');
       $book =Db::view('Book','book_id,book_name,author_name,full,full_id,contract_id')
           ->view('BookEdit','edit_name','BookEdit.e_id=Book.e_id')
           ->view('Content','title,time','Content.book_id=Book.book_id')
           ->where($where)
           ->find();
      if(strtotime($book['time'])>strtotime($shijian)){
          return 0;
      }
       $titleTime=date('Y-m-d',strtotime($book['time']));
       if($titleTime==date('Y-m-02') && $book['full']==1){
            return 1;
       }
       if($book['full']==1 && $book['full_id']<2){
           return 0;
       }
       if($book['full']<=1 && $book['full_id']>=2){
           return 1;
       }
       if($book['full']>1){
           return 0;
       }

   }
   /*
    * 签约类型
    */
   private function contract($contract_id){
        if(!is_numeric($contract_id)){
            $this->error('参数错误');
        }
        switch ($contract_id){
            case 1:
                $str ="驻站";
                break;
            case 2:
                $str ="新咚";
                break;
            case 3:
                $str ="C级";
                break;
            case 4:
                $str ="B级";
                break;
            case 5:
                $str ="A级";
                break;
            case 6:
                $str ="S级";
                break;
            case 7:
                $str ="保底分成";
                break;
            case 8:
                $str ="大神买断";
                break;

        }
       return $str;

   }
   /*
    * 日更字数
    */
   private function full_id($full_id){
       if(!is_numeric($full_id)){
           $this->error('参数错误');
       }
       switch ($full_id){
           case 0:
               $str="待更新";
             break;
           case 1:
               $str="日更一千";
               break;
           case 2:
               $str="日更两千";
               break;
           case 3:
               $str="日更四千";
               break;
           case 4:
               $str="日更八千";
               break;
       }
       return $str;

   }

    //申请薪酬
    public function apply(){

        return $this->fetch();
    }

    /*
     * 渠道收入
     */
    public function qudao(){
        $author =$this->account;
        $book =get_book($author['author_id']);
        return $this->fetch('',[
            'book'  =>$book
        ]);

    }
}