<?php
namespace app\index\controller;
use app\author\controller\Redis;
use think\Controller;
use think\Db;
class Curbook extends Controller{

    public function index(){
        $redis =Redis::getRedisConn();
        //宅文轮播缓存
        if($redis->get(REDIS_CURBOOK_LUN_PC)){
            $solid =unserialize($redis->get(REDIS_CURBOOK_LUN_PC));
        }else{

            $solid= $this->solid();//宅文轮播图
            $redis->set(REDIS_CURBOOK_LUN_PC,serialize($solid),3600);
        }
        //宅文深夜神触缓存
        if($redis->get(REDIS_CURBOOK_GOD_TOUCH_PC)){

            $touch =unserialize($redis->get(REDIS_CURBOOK_GOD_TOUCH_PC));

        }else{
            $touch =$this->god_touch();//深夜神触
            $redis->set(REDIS_CURBOOK_GOD_TOUCH_PC,serialize($touch),3600);
        }

        $lianzai =$this->lianzai();//连载中
        $wanjie =$this->wanjie();//已完结
        $newBook =$this->newBook();//最近新书
        $newChapter =$this->newChapter();//最新章节
        $click=$this->echo_click();//点击榜
        $collection =$this->echo_collection();//收藏榜
        $vote =$this->echo_vote();//推荐榜
        $exce =$this->echo_exceptional();//畅销榜
        // print_r($lianzai);exit();
        return $this->fetch('',[
            'solid'   =>$solid,
            'touch'   =>$touch,
            'lianzai' =>$lianzai,
            'wanjie'   =>$wanjie,
            'newBook'  =>$newBook,
            'newChapter'  =>$newChapter,
            'a'          =>$a=1,
            'click'    =>$click,
            'b'        =>$b=3,
            'collection'   =>$collection,
            'c'         =>$c=3,
            'vote'    =>$vote,
            'd'       =>$d=3,
            'exce'    =>$exce,
            'e'       =>$e=3,
            'ok'     =>$ok=3
        ]);
    }
    /*
     * 宅圈轮播图
     * $promoter_id=24
     */
    public function solid(){
        $solid =Db::name('BookPromote')->where(['promote_id'=>24])->field('book_id,book_name,upload_img')->limit(5)->select();
        return $solid;
    }

    /*
     * 深夜神触
     * $type_id!=16 $promoter_id=25
     */
    public function god_touch(){
        $touch =Db::view('Book','book_id,book_name,book_brief,words')
            ->view('BookPromote','promote_id','BookPromote.book_id=Book.book_id')
            ->view('BookType','book_type','BookType.type_id=Book.type_id')
            ->view('BookStatistical','click_total','BookStatistical.book_id=Book.book_id')
            ->where(['BookPromote.promote_id'=>25])
            ->order('BookPromote.xu asc')
            ->limit(8)
            ->select();
        return $touch;
    }
    /*
     * 连载中
     */
    public function lianzai(){

        $where=[
            'Book.type_id'  =>['neq',16],
            'Book.state'    =>1,
            'is_show'   =>1,
            'audit'    =>1
        ] ;
        $lianzai =Db::view('Book','book_id,book_name,author_id,author_name,upload_img,words,level,book_brief')
                  ->view('BookType','book_type','BookType.type_id=Book.type_id')
                  ->view('BookStatistical','click_total','BookStatistical.book_id=Book.book_id')
                 ->view('Content','title','Content.book_id=Book.book_id and Content.num=Book.chapter')
                  ->where($where)
                  ->order('Content.time desc')
                  ->limit(12)
                  ->select();
        for ($i=0;$i<count($lianzai);$i++){

            $lianzai[$i]['portrait']=getUserImage($lianzai[$i]['author_id']);
        }
      //  print_r($lianzai);exit();
        return $lianzai;
    }

    /*
     * 已完结
     */
    public function wanjie(){

        $where=[
            'Book.type_id'  =>['neq',16],
            'state'    =>2,
            'is_show'   =>1,
            'audit'    =>1
        ] ;
        $wanjie =Db::view('Book','book_id,book_name,author_id,author_name,upload_img,words,level,book_brief')
            ->view('BookType','book_type','BookType.type_id=Book.type_id')
            ->view('BookStatistical','click_total','BookStatistical.book_id=Book.book_id')
            ->where($where)
            ->order('create_time desc')
            ->limit(12)
            ->select();
        for ($i=0;$i<count($wanjie);$i++){

            $wanjie[$i]['portrait']=getUserImage($wanjie[$i]['author_id']);
        }
        return $wanjie;
    }
    /*
     * 最近新书
     */
    public function newBook(){
        $where=[
            'Book.type_id'  =>['neq',16],
            'is_show'   =>1,
            'audit'    =>1
        ] ;
        $newBook =Db::view('Book','book_id,book_name,author_id,author_name,upload_img,words,level,book_brief')
            ->view('BookType','book_type','BookType.type_id=Book.type_id')
            ->view('BookStatistical','click_total','BookStatistical.book_id=Book.book_id')
            ->where($where)
            ->order('create_time desc')
            ->limit(12)
            ->select();
        for ($i=0;$i<count($newBook);$i++){

            $newBook[$i]['portrait']=getUserImage($newBook[$i]['author_id']);
        }
        return $newBook;

    }

    /*
 * 最新章节
 */
    public function newChapter(){

        $chapter=Db::view('Book','book_id,book_name,words,author_name')
            ->view('Content','title,time,the_price','Content.book_id=Book.book_id and Content.num=Book.chapter','LEFT')
            ->view('BookType','book_type','BookType.type_id=Book.type_id')
            ->where(['is_show'=>1,'audit'=>1,'Book.type_id'=>['neq',16]])
            ->order('Content.time desc')
            ->limit(30)
            ->select();
        return $chapter;

    }
    /*
     * 点击榜
     */
    public function echo_click(){
        $click=Db::view('Book','book_id,book_name,author_name,upload_img')
            ->view('BookStatistical','click_weeks','BookStatistical.book_id=Book.book_id')
            ->view('BookType','book_type','BookType.type_id=Book.type_id')
            ->where(['is_show'=>1,'audit'=>1,'Book.type_id'=>['neq',16]])
            ->order('click_weeks desc')
            ->limit(10)
            ->select();
        return $click;
    }
    /*
     * 收藏榜
     * 周榜
      */
    public function echo_collection(){
        $collection=Db::view('Book','book_id,book_name,author_name,upload_img')
            ->view('BookStatistical','collection_weeks','BookStatistical.book_id=Book.book_id')
            ->view('BookType','book_type','BookType.type_id=Book.type_id')
            ->where(['is_show'=>1,'audit'=>1,'Book.type_id'=>['neq',16]])
            ->order('collection_weeks desc')
            ->limit(10)
            ->select();
        return $collection;
    }

    /*
    * 推荐榜
    * 周榜
    */
    public function echo_vote(){

        $vote=Db::view('Book','book_id,book_name,author_name,upload_img')
            ->view('BookStatistical','vote_weeks','BookStatistical.book_id=Book.book_id')
            ->view('BookType','book_type','BookType.type_id=Book.type_id')
            ->where(['is_show'=>1,'audit'=>1,'Book.type_id'=>['neq',16]])
            ->order('vote_weeks desc')
            ->limit(10)
            ->select();
        return $vote;
    }
    /*
     * 打赏榜
     * 周榜
     * exceptional_weeks
     */
    public function echo_exceptional(){

        $exce=Db::view('Book','book_id,book_name,author_name,upload_img')
            ->view('BookStatistical','click_weeks','BookStatistical.book_id=Book.book_id')
            ->view('BookType','book_type','BookType.type_id=Book.type_id')
            ->where(['is_show'=>1,'audit'=>1,'Book.type_id'=>['neq',16]])
            ->order('money_weeks desc')
            ->limit(10)
            ->select();
        return $exce;
    }
}