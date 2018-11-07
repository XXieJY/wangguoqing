<?php
namespace app\index\controller;
use think\Controller;
use think\Db;
class User extends Base{

      public function index(){

          $page=$this->bookShelf(cookie('shudong_user')['user_id']);
          $bookShelf =$page->toArray();//我的书架
          foreach ($bookShelf['data'] as $k=>$v){

              $bookShelf['data'][$k]['geng'] =$this->isUpdate($v['book_id']);
              $bookShelf['data'][$k]['isBuy'] =$this->isBuy($v['book_id']);
          }
         // print_r($bookShelf);
         $user= $this->myFocusUser();//我的关注
          $fans =$this->myFans();//我的粉丝
        //  print_r($fans);
          return $this->fetch('',[
              'page'   =>$page,
              'bookShelf'  =>$bookShelf['data'],
              'user'     =>$user,
              'fans'     =>$fans
          ]);
      }

      /*
       * 我的书架
       */
      public function bookShelf($userId){


           $book= Db::view('BookCollection','book_id,top,chapter,time,update_time')
                     ->view('Book','book_name,upload_img','Book.book_id=BookCollection.book_id')
                     ->view('BookType','book_type','BookType.type_id=Book.type_id')
                     ->view('BookStatistical','click_total','BookStatistical.book_id=Book.book_id')
                     ->view('Content','title,time','Content.num=Book.chapter and Content.book_id=Book.book_id')
                     ->where(['user_id'=>$userId])
                     ->order(['top'=>'desc','update_time'=>'desc','time'=>'desc'])
                     ->paginate(20);

          return $book;

      }
      /*
       * 判断书籍是否有最近更新，以当天时间为标准
       */
      public function isUpdate($bookId){

          $time =date("Y-m-d");
          $where=[
              'book_id'   =>$bookId,
              'time'      =>['like',"%$time%"]

          ];
         $content= Db::name('Content')->where($where)->find();
         if(count($content)==0){
              return 0;
         }else{
             return 1;
         }
      }

      /*
       * 判断该书籍是否自动订阅
       */
      public function isBuy($bookId){

         $buy= Db::name('BookBuy')->where(['book_id'=>$bookId,'user_id'=>cookie('shudong_user')['user_id']])->find();
         if(count($buy)!=0){

             return $buy['is_type'];

         }else{

             return 0;
         }

      }

      /*
       * 我的最近阅读
       */

      public function read(){

          $user= $this->myFocusUser();//我的关注
          $fans =$this->myFans();//我的粉丝
          $page =$this->lastRead(cookie('shudong_user')['user_id']);//最近阅读的书籍
          $book =$page->toArray();
          return $this->fetch('',[
              'user'     =>$user,
              'fans'     =>$fans,
              'book'     =>$book['data'],
              'page'     =>$page
          ]);
      }
    /*
     * 最近阅读记录，取书架里面的书籍信息
     */
    public function lastRead($userId){

       $page= Db::view('BookCollection','book_id,chapter')
            ->view('Book','book_name','Book.book_id=BookCollection.book_id')
            ->view('Content','title,time','Content.book_id=Book.book_id and Content.num=Book.chapter')
            ->where(['user_id'=>$userId])
            ->order('time desc')
            ->paginate();
       return $page;
    }

    /*
     * 我的订阅
     */

    public function order(){

        $user= $this->myFocusUser();//我的关注
        $fans =$this->myFans();//我的粉丝
        $page =$this->myOrder(cookie('shudong_user')['user_id']);//我的订阅记录
        $book =$page->toArray();
        foreach ($book['data'] as $k=>$v){

            $book['data'][$k]['chapters'] =$this->getBuyChapterLastRead($v['book_id'],$v['user_id']);
        }
        return $this->fetch('',[
            'user'     =>$user,
            'fans'     =>$fans,
            'page'    =>$page,
            'book'    =>$book['data']

        ]);
    }
    /*
     * 我的订阅书籍
     */
    public function myOrder($userId){

       $page= Db::view('BookBuy','is_buy,book_id,user_id')
            ->view('Book','book_name','Book.book_id=BookBuy.book_id')
            ->view('Content','title,time','Content.book_id=Book.book_id and Content.num=Book.chapter')
            ->where(['user_id'=>$userId,'BookBuy.is_buy'=>['neq',""]])
            ->paginate();
       return $page;
    }

    /*
     * 获取购买章节的最新阅读记录
     */
    public function getBuyChapterLastRead($bookid,$userid){

             $buy= Db::name('BookBuy')->where(['user_id'=>$userid,'book_id'=>$bookid])->find();
             $num =end(explode(',',$buy['is_buy']));
             return $num;
    }

    /*
     * 我的书评
     */
    public function message(){
        $user= $this->myFocusUser();//我的关注
        $fans =$this->myFans();//我的粉丝
        $page =$this->myMessage(cookie('shudong_user')['user_id']);
        $msg =$page->toArray();
        foreach ($msg['data'] as $k=>$v){
            if(strlen( $v['portrait'])<60) {

                if ($v['portrait'] == "user/portrait/portrait.jpg") {

                    $msg['data'][$k]['portrait'] = "http://images.shuddd.com/user/portrait/portrait" . $v['sex'] . ".png";

                } else {
                    $msg['data'][$k]['portrait'] = "http://images.shuddd.com/" . $v['portrait'];
                }
            }
        }

       // print_r($msg);
        return $this->fetch('',[
            'user'     =>$user,
            'fans'     =>$fans,
            'page'     =>$page,
            'msg'     =>$msg['data']
        ]);
    }

    /*
     * 获取书评
     */
    public function myMessage($userId){

         $page= Db::view('BookMessage','content,thumb,num,time')
              ->view('User','pen_name,portrait,mem_vip,sex,days','User.user_id=BookMessage.user_id')
              ->where(['BookMessage.user_id'=>$userId,'f_id'=>0,'status'=>1])
              ->order('time desc')
              ->paginate(20);
          return $page;
    }



}