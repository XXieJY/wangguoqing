<?php
namespace app\index\controller;
use app\author\controller\Redis;
use think\Controller;
use think\Db;
class Chapterlist extends Controller{

    public function index($bookid){
        if(!is_numeric($bookid)){
            $this->error('参数不合法');
        }
        $url='http://'.$_SERVER['HTTP_HOST'].$_SERVER['REQUEST_URI'];//获取该界面的完整的url；
        $book=$this->get_book($bookid);//获取书籍信息
        $chapter =$this->get_chapter($bookid);//获取小说章节
        $exceptional =$this->get_new_state($bookid);//获得最新滚动图
        $vipvote=$this->get_vipvote($bookid);//票王
        $vote=$this->get_vote($bookid);//第一粉丝
        $fans=$this->get_fans($bookid);//粉丝榜

      //print_r($fans);exit();
        return $this->fetch('',[
            'book'    =>$book,
            'ok'      =>0,
            'chapter'  =>$chapter,
            'exceptional'  =>$exceptional,
            'vipvote'     =>$vipvote[0],
            'vote'      =>$vote[0],
            'fan'    =>$fans,
            'a'   =>3,
            'url'  =>$url
        ]);
    }

    public function get_book($id){
        $book =Db::name('Book')->field('book_id,book_name,author_name,update_time')->where(['book_id'=>$id])->find();
        return $book;
    }


    //获取书籍的章节
    public function get_chapter($bookid){

              $redis =Redis::getRedisConn();
              if($redis->get(REDIS_CHAPTER_LIST_TWO_PC.$bookid)){

                  $chapter =unserialize($redis->get(REDIS_CHAPTER_LIST_TWO_PC.$bookid));

              }else{
                  $chapter =Db::name('Content')->field('volume_id,book_id,title')->where(['book_id'=>$bookid,'type'=>1])->select();
                  $length =count($chapter);
                  for($i=0;$i<$length;$i++){
                      $chapter[$i]['chapter'] =Db::name('Content')
                          ->field('content_id,num,title,the_price')
                          ->where(['book_id'=>$bookid,'state'=>1, 'type'=>0,'volume_fid'=>$chapter[$i]['volume_id']])
                          ->select();
                  }
                  $redis->set(REDIS_CHAPTER_LIST_TWO_PC.$bookid,serialize($chapter),3600);
              }



        return $chapter;

    }

    /*
     * 动态滚动图
     */
    public function get_new_state($bookid){

        $where['UserConsumerecord.type']=array('in','3,4');
        $where['UserConsumerecord.book_id']=$bookid;
        $exceptional=Db::view('UserConsumerecord','dosomething,date')
                     ->view('User','pen_name','User.user_id=UserConsumerecord.user_id')
                     ->where($where)
                     ->order('date desc')
                     ->limit(4)
                     ->select();

        return $exceptional;

    }
    /*
     * 票王
     */
    public function get_vipvote($bookid){
        $sql="SELECT a.user_id,b.pen_name,b.mem_vip,b.portrait,b.sex, SUM(a.count) AS m FROM shudong_user_consumerecord a INNER JOIN shudong_user b ON a.user_id=b.user_id WHERE a.type=? AND a.book_id=? GROUP BY a.user_id ORDER BY m DESC LIMIT 1";
        $vipvote=Db::query($sql,[3,$bookid]);
      //  print_r($vipvote);exit();
        for ($i=0;$i<count($vipvote);$i++){
            if(strlen($vipvote[$i]['portrait'])<60){
                if($vipvote[$i]['portrait']=="user/portrait/portrait.jpg"){

                    $vipvote[$i]['portrait']="http://images.shuddd.com/user/portrait/portrait".$vipvote[$i]['sex'].".png";

                }else{
                    $vipvote[$i]['portrait']="http://images.shuddd.com/".$vipvote[$i]['portrait'];
                }
            }
        }
        return $vipvote;
    }
    /*
     * 第一粉丝
     */
    public function get_vote($bookid){
        $sql="SELECT a.user_id,b.pen_name,b.mem_vip,b.portrait,b.sex, SUM(a.count) AS m FROM shudong_user_consumerecord a INNER JOIN shudong_user b ON a.user_id=b.user_id WHERE a.type=? AND a.book_id=? GROUP BY a.user_id ORDER BY m DESC LIMIT 1";
        $vote=Db::query($sql,[2,$bookid]);
        for ($i=0;$i<count($vote);$i++){
            if(strlen($vote[$i]['portrait'])<60){

                if($vote[$i]['portrait']=="user/portrait/portrait.jpg"){

                    $vote[$i]['portrait']="http://images.shuddd.com/user/portrait/portrait".$vote[$i]['sex'].".png";

                }else{
                    $vote[$i]['portrait']="http://images.shuddd.com/".$vote[$i]['portrait'];
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
                 ->view('User','portrait,pen_name,sex','User.user_id=BookFans.user_id')
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
}