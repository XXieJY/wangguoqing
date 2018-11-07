<?php
namespace app\index\controller;
use app\author\controller\Redis;
use think\Controller;
use think\Db;
class Index extends Controller
{
    public function index($tag="")
    {
        if($tag!=""){

          $seo=  $this->seo($tag);
        }else{

            $seo="";
        }
       // print_r($seo);
       $ban =$this->ban();//pc横幅
        $slide= $this->slide();
        $godTouch =$this->god_touch();//深夜神触
        $push=$this->push();//主编强推
        $hot=$this->hot();//火热连载
        $dachu=$this->dachu();//大触神作
        $xinzuo=$this->xinzuo();//潜力新作
        $newBook =$this->newBook();//新书预热
        $chapter =$this->newChapter();//最新章节
        $click =$this->echo_click();//点击榜
        $collection =$this->echo_collection();//收藏榜
        $vote =$this->echo_vote();//推荐榜
        $tongren =$this->echo_tongren();//同人榜

        return $this->fetch('',[
            'ban'  =>$ban,
            'slide'  =>$slide,
            'godTouch' =>$godTouch,
            'push'    =>$push,
            'hot'     =>$hot,
            'dachu'   =>$dachu,
            'xinzuo'  =>$xinzuo,
            'newBook'  =>$newBook,
            'chapter'  =>$chapter,
            'a'        =>$a=1,
            'click'    =>$click,
            'b'        =>$b=4,
            'collection'  =>$collection,
            'c'      =>$c=4,
            'vote'   =>$vote,
            'd'     =>$d=4,
            'tongren'  =>$tongren,
            'e'   =>$e=4,
            'ok'    =>$ok=1,
            'seo'   =>$seo
        ]);
     }

     public function seo($tag){
           if($tag==11211){

               return [
                   'title'  =>'重口味h小说_小说_免费阅读_书咚网',
                   'keywords'  =>'书咚，小说写作，二次元小说，无敌文，爽文，耽美小说，18禁小说，限制级小说，宅文，玄幻小说，暧昧都市小说，泛次元，重口味小说，重口味h文',
                   'description'  =>'书咚官网-最好看的重口味h小说网站，书咚官网提供重口味小说、黑暗流小说、黑暗风小说、暧昧都市小说、玄幻小说、耽美小说等免费小说在线阅读，小说最新更新，章节每日连载。'

               ];
           }elseif ($tag==11212){

               return [
                   'title'  =>'书咚1',
                   'keywords'  =>'书咚1',
                   'description'  =>'书咚1'

               ];
           }else{

               return "";
           }

     }


     /*
      * 横幅
      */

     public function ban(){

        $ban= Db::name('SystemBan')->order('id asc')->select();

        return $ban;

     }

     /*
      * 首页轮播图
      * $promote_id=18
      */
    public function slide(){
        $redis =Redis::getRedisConn();

        if($redis->get(REDIS_INDEX_SLIDE_PC)){
            $slide=unserialize($redis->get(REDIS_INDEX_SLIDE_PC));

        }else{
            $slide =Db::name('BookPromote')->where(['promote_id'=>18])
                ->field('book_id,book_name,upload_img,wap_title,wap_url')->order('xu asc')->limit(5)->select();
            $redis->set(REDIS_INDEX_SLIDE_PC,serialize($slide),86400);

        }

         return $slide;
    }
    /*
     * 深夜神触
     * $promote_id=19
     */
    public function god_touch(){

        $redis =Redis::getRedisConn();
        if($redis->get(REDIS_INDEX_GOD_TOUCH_PC)){

            $godTouch =unserialize($redis->get(REDIS_INDEX_GOD_TOUCH_PC));

        }else{
            $godTouch =Db::view('BookPromote','book_id,book_name')
                ->view('Book','level,upload_img','Book.book_id=BookPromote.book_id')
                ->where(['promote_id'=>19])
                ->order('xu asc')
                ->limit(12)
                ->select();
            $redis->set(REDIS_INDEX_GOD_TOUCH_PC,serialize($godTouch),86400);

        }


        return $godTouch;
    }
    /*
     * 主编强推
     * $promote_id=20
     */
    public function push(){

        $redis =Redis::getRedisConn();
        if($redis->get(REDIS_INDEX_PUSH_PC)){
            $push =unserialize($redis->get(REDIS_INDEX_PUSH_PC));

        }else{

            $push =Db::view('BookPromote','book_id,book_name,book_brief')
                ->view('Book','level,words,author_id,author_name,upload_img','Book.book_id=BookPromote.book_id')
                ->view('BookType','book_type','BookType.type_id=Book.type_id')
                ->view('BookStatistical','click_total','BookStatistical.book_id=Book.book_id')
                ->where(['promote_id'=>20])
                ->order('BookPromote.xu asc')
                ->limit(12)
                ->select();
            for ($i=0;$i<count($push);$i++){

                $push[$i]['portrait']=getUserImage($push[$i]['author_id']);
            }

            $redis->set(REDIS_INDEX_PUSH_PC,serialize($push),86400);
        }

        return $push;

    }
    /*
     * 火热连载
     * $promote_id=21
     */
    public function hot(){

            $redis =Redis::getRedisConn();
            if($redis->get(REDIS_INDEX_HOT_PC)){

                $hot =unserialize($redis->get(REDIS_INDEX_HOT_PC));

            }else{

                $hot =Db::view('BookPromote','book_id,book_name,book_brief')
                    ->view('Book','level,words,author_id,author_name,upload_img','Book.book_id=BookPromote.book_id')
                    ->view('BookType','book_type','BookType.type_id=Book.type_id')
                    ->view('BookStatistical','click_total','BookStatistical.book_id=Book.book_id')
                    ->where(['promote_id'=>21])
                    ->order('BookPromote.xu asc')
                    ->limit(12)
                    ->select();
                for ($i=0;$i<count($hot);$i++){

                    $hot[$i]['portrait']=getUserImage($hot[$i]['author_id']);
                }

                $redis->set(REDIS_INDEX_HOT_PC,serialize($hot),86400);

            }



        return $hot;

    }
    /*
     * 大触神作
     * $promote=22
     */
    public function dachu(){

             $redis =Redis::getRedisConn();
             if($redis->get(REDIS_INDEX_DACHU_PC)){

              $dachu =unserialize($redis->get(REDIS_INDEX_DACHU_PC));

             }else{

                 $dachu =Db::view('BookPromote','book_id,book_name,book_brief')
                     ->view('Book','level,words,author_id,author_name,upload_img','Book.book_id=BookPromote.book_id')
                     ->view('BookType','book_type','BookType.type_id=Book.type_id')
                     ->view('BookStatistical','click_total','BookStatistical.book_id=Book.book_id')
                     ->where(['promote_id'=>22])
                     ->order('BookPromote.xu asc')
                     ->limit(12)
                     ->select();
                 for ($i=0;$i<count($dachu);$i++){

                     $dachu[$i]['portrait']=getUserImage($dachu[$i]['author_id']);
                 }
                $redis->set(REDIS_INDEX_DACHU_PC,serialize($dachu),86400);
             }


        return $dachu;

    }
    /*
     * 潜力新作
     * $promote_id=23
     */
    public function xinzuo(){

            $redis =Redis::getRedisConn();
            if($redis->get(REDIS_INDEX_XINZUO_PC)){

                $xinzuo =unserialize($redis->get(REDIS_INDEX_XINZUO_PC));

            }else{
                $xinzuo =Db::view('BookPromote','book_id,book_name,book_brief')
                    ->view('Book','level,words,author_id,author_name,upload_img','Book.book_id=BookPromote.book_id')
                    ->view('BookType','book_type','BookType.type_id=Book.type_id')
                    ->view('BookStatistical','click_total','BookStatistical.book_id=Book.book_id')
                    ->where(['promote_id'=>23])
                    ->order('BookPromote.xu asc')
                    ->limit(6)
                    ->select();
                for ($i=0;$i<count($xinzuo);$i++){

                    $xinzuo[$i]['portrait']=getUserImage($xinzuo[$i]['author_id']);
                }

                $redis->set(REDIS_INDEX_XINZUO_PC,serialize($xinzuo),86400);

            }

        return $xinzuo;
    }
    /*
     * 新书预热
     * $promote_id=8
     */
    public function newBook(){

            $redis =Redis::getRedisConn();
            if($redis->get(REDIS_INDEX_NEWBOOK_PC)){

                $newBook =unserialize($redis->get(REDIS_INDEX_NEWBOOK_PC));

            }else{
                $newBook =Db::view('BookPromote','book_id,book_name,book_brief')
                    ->view('Book','level,words,author_id,author_name,upload_img','Book.book_id=BookPromote.book_id','LEFT')
                    ->view('BookType','book_type','BookType.type_id=Book.type_id','LEFT')
                    ->view('BookStatistical','click_total','BookStatistical.book_id=Book.book_id')
                    ->where(['promote_id'=>28])
                    ->order('BookPromote.xu asc')
                    ->limit(6)
                    ->select();
                for ($i=0;$i<count($newBook);$i++){

                    $newBook[$i]['portrait']=getUserImage($newBook[$i]['author_id']);
                }
                    $redis->set(REDIS_INDEX_NEWBOOK_PC,serialize($newBook),86400);

            }

        return $newBook;

    }
    /*
     * 最新章节
     */
    public function newChapter(){

           $chapter=Db::view('Book','book_id,book_name,words,author_name')
               ->view('Content','title,time,the_price','Content.book_id=Book.book_id and Content.num=Book.chapter')
               ->view('BookType','book_type','BookType.type_id=Book.type_id')
               ->where(['is_show'=>1,'audit'=>1])
               ->order('Content.time desc')
               ->limit(30)
               ->select();
        return $chapter;
    }
    /*
     * 点击榜
     * 周榜
     */
    public function echo_click(){
        $click =Db::view('Book','book_id,book_name,author_name,upload_img')
                 ->view('BookStatistical','click_weeks','BookStatistical.book_id=Book.book_id')
                 ->view('BookType','book_type','BookType.type_id=Book.type_id')
                 ->where(['is_show'=>1,'audit'=>1])
                 ->order('BookStatistical.click_weeks desc')
                 ->limit(10)
                 ->select();

        return $click;
    }

    /*
     * 收藏榜
     * 周榜
     */

    public function echo_collection(){
        $collection =Db::view('Book','book_id,book_name,author_name,upload_img')
            ->view('BookStatistical','collection_weeks','BookStatistical.book_id=Book.book_id')
            ->view('BookType','book_type','BookType.type_id=Book.type_id')
            ->where(['is_show'=>1,'audit'=>1])
            ->order('BookStatistical.collection_weeks desc')
            ->limit(10)
            ->select();
        return $collection;


    }
    /*
     * 推荐榜
     * 周榜
     */
    public function echo_vote(){

        $vote =Db::view('Book','book_id,book_name,author_name,upload_img')
            ->view('BookStatistical','vote_weeks','BookStatistical.book_id=Book.book_id')
            ->view('BookType','book_type','BookType.type_id=Book.type_id')
            ->where(['is_show'=>1,'audit'=>1])
            ->order('BookStatistical.vote_weeks desc')
            ->limit(10)
            ->select();
        return $vote;
    }

    /*
     * 同人榜
     */
    public function echo_tongren(){
        $tongren =Db::view('Book','book_id,book_name,author_name,upload_img')
            ->view('BookStatistical','vote_weeks','BookStatistical.book_id=Book.book_id')
            ->view('BookType','book_type','BookType.type_id=Book.type_id')
            ->where(['is_show'=>1,'audit'=>1,'Book.type_id'=>16])
            ->order('BookStatistical.vote_weeks desc')
            ->limit(10)
            ->select();
        return $tongren;
    }


}
