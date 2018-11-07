<?php
namespace app\api\controller;
use think\Controller;
use think\Db;
class index extends Controller{
    public function click_zhou(){

            $click=Db::view('Book','book_id,book_name,author_name,upload_img')
                ->view('BookStatistical','click_weeks','BookStatistical.book_id=Book.book_id')
                ->view('BookType','book_type','BookType.type_id=Book.type_id')
                ->where(['is_show'=>1,'audit'=>1])
                ->order('click_weeks desc')
                ->limit(10)
                ->select();

       if(!$click){
           return 1;
       }else{
           return $this->fetch('',[
               'click'  =>$click,
               'a'      =>$a=4,
           ]);

       }

    }
    public function click_month(){

            $click=Db::view('Book','book_id,book_name,author_name,upload_img')
                ->view('BookStatistical','click_month','BookStatistical.book_id=Book.book_id')
                ->view('BookType','book_type','BookType.type_id=Book.type_id')
                ->where(['is_show'=>1,'audit'=>1])
                ->order('click_month desc')
                ->limit(10)
                ->select();

      if(!$click){
          return 1;
      }else{
          return $this->fetch('',[
              'click'  =>$click,
              'a'      =>$a=4,
          ]);
      }

    }

    public function click_total(){

            $click=Db::view('Book','book_id,book_name,author_name,upload_img')
                ->view('BookStatistical','click_total','BookStatistical.book_id=Book.book_id')
                ->view('BookType','book_type','BookType.type_id=Book.type_id')
                ->where(['is_show'=>1,'audit'=>1])
                ->order('click_total desc')
                ->limit(10)
                ->select();

        if(!$click){
            return 1;
        }else{
            return $this->fetch('',[
                'click'  =>$click,
                'a'      =>$a=4,
            ]);
        }

    }
    public function collection_weeks(){

               $collection=Db::view('Book','book_id,book_name,author_name,upload_img')
                   ->view('BookStatistical','collection_weeks','BookStatistical.book_id=Book.book_id')
                   ->view('BookType','book_type','BookType.type_id=Book.type_id')
                   ->where(['is_show'=>1,'audit'=>1])
                   ->order('collection_weeks desc')
                   ->limit(10)
                   ->select();

        if(!$collection){
            return 1;
        }else{
            return $this->fetch('',[
                'collection'  =>$collection,
                'b'      =>$b=4,
            ]);
        }

    }
    public function collection_month(){

            $collection=Db::view('Book','book_id,book_name,author_name,upload_img')
                ->view('BookStatistical','collection_month','BookStatistical.book_id=Book.book_id')
                ->view('BookType','book_type','BookType.type_id=Book.type_id')
                ->where(['is_show'=>1,'audit'=>1])
                ->order('collection_month desc')
                ->limit(10)
                ->select();

        if(!$collection){
            return 1;
        }else{
            return $this->fetch('',[
                'collection'  =>$collection,
                'b'      =>$b=4,
            ]);
        }

    }

    public function collection_total(){

            $collection=Db::view('Book','book_id,book_name,author_name,upload_img')
                ->view('BookStatistical','collection_total','BookStatistical.book_id=Book.book_id')
                ->view('BookType','book_type','BookType.type_id=Book.type_id')
                ->where(['is_show'=>1,'audit'=>1])
                ->order('collection_total desc')
                ->limit(10)
                ->select();
        if(!$collection){
            return 1;
        }else{
            return $this->fetch('',[
                'collection'  =>$collection,
                'b'      =>$b=4,
            ]);
        }

    }

    public function buy_weeks(){


             $buy=Db::view('Book','book_id,book_name,author_name,upload_img')
                 ->view('BookStatistical','click_weeks','BookStatistical.book_id=Book.book_id')
                 ->view('BookType','book_type','BookType.type_id=Book.type_id')
                 ->where(['is_show'=>1,'audit'=>1])
                 ->order('money_weeks desc')
                 ->limit(10)
                 ->select();
        if(!$buy){
            return 1;
        }else{
            return $this->fetch('',[
                'buy'  =>$buy,
                'c'      =>$c=4,
            ]);
        }


    }
    public function buy_month(){

            $buy=Db::view('Book','book_id,book_name,author_name,upload_img')
                ->view('BookStatistical','click_month','BookStatistical.book_id=Book.book_id')
                ->view('BookType','book_type','BookType.type_id=Book.type_id')
                ->where(['is_show'=>1,'audit'=>1])
                ->order('money_month desc')
                ->limit(10)
                ->select();

        if(!$buy){
            return 1;
        }else{
            return $this->fetch('',[
                'buy'  =>$buy,
                'c'      =>$c=4,
            ]);
        }


    }

    public function buy_total(){

            $buy=Db::view('Book','book_id,book_name,author_name,upload_img')
                ->view('BookStatistical','click_total','BookStatistical.book_id=Book.book_id')
                ->view('BookType','book_type','BookType.type_id=Book.type_id')
                ->where(['is_show'=>1,'audit'=>1])
                ->order('money_total desc')
                ->limit(10)
                ->select();
        if(!$buy){
            return 1;
        }else{
            return $this->fetch('',[
                'buy'  =>$buy,
                'c'      =>$c=4,
            ]);
        }


    }

    public function vote_weeks(){

           $vote=Db::view('Book','book_id,book_name,author_name,upload_img')
               ->view('BookStatistical','vote_weeks','BookStatistical.book_id=Book.book_id')
               ->view('BookType','book_type','BookType.type_id=Book.type_id')
               ->where(['is_show'=>1,'audit'=>1])
               ->order('vote_weeks desc')
               ->limit(10)
               ->select();
       if(!$vote){
           return 1;
       }else{
           return $this->fetch('',[
               'vote'   =>$vote,
               'd'       =>$d=4
           ]);
       }

    }
    public function vote_month(){

            $vote=Db::view('Book','book_id,book_name,author_name,upload_img')
                ->view('BookStatistical','vote_month','BookStatistical.book_id=Book.book_id')
                ->view('BookType','book_type','BookType.type_id=Book.type_id')
                ->where(['is_show'=>1,'audit'=>1])
                ->order('vote_month desc')
                ->limit(10)
                ->select();

        if(!$vote){
            return 1;
        }else{
            return $this->fetch('',[
                'vote'   =>$vote,
                'd'       =>$d=4
            ]);
        }

    }

    public function vote_total(){

            $vote=Db::view('Book','book_id,book_name,author_name,upload_img')
                ->view('BookStatistical','vote_total','BookStatistical.book_id=Book.book_id')
                ->view('BookType','book_type','BookType.type_id=Book.type_id')
                ->where(['is_show'=>1,'audit'=>1])
                ->order('vote_total desc')
                ->limit(10)
                ->select();

        if(!$vote){
            return 1;
        }else{
            return $this->fetch('',[
                'vote'   =>$vote,
                'd'       =>$d=4
            ]);
        }

    }

    public function count_weeks(){

            $count=Db::view('Book','book_id,book_name,author_name,upload_img')
                ->view('BookStatistical','count_weeks','BookStatistical.book_id=Book.book_id')
                ->view('BookType','book_type','BookType.type_id=Book.type_id')
                ->where(['is_show'=>1,'audit'=>1])
                ->order('count_weeks desc')
                ->limit(10)
                ->select();

        if(!$count){
            return 1;
        }else{
            return $this->fetch('',[

                'count'   =>$count,
                'e'     =>$e=4
            ]);
        }

    }
    public function count_month(){

            $count=Db::view('Book','book_id,book_name,author_name,upload_img')
                ->view('BookStatistical','count_month','BookStatistical.book_id=Book.book_id')
                ->view('BookType','book_type','BookType.type_id=Book.type_id')
                ->where(['is_show'=>1,'audit'=>1])
                ->order('count_month desc')
                ->limit(10)
                ->select();

        if(!$count){
            return 1;
        }else{
            return $this->fetch('',[

                'count'   =>$count,
                'e'     =>$e=4
            ]);
        }

    }

    public function count_total(){

            $count=Db::view('Book','book_id,book_name,author_name,upload_img')
                ->view('BookStatistical','count_total','BookStatistical.book_id=Book.book_id')
                ->view('BookType','book_type','BookType.type_id=Book.type_id')
                ->where(['is_show'=>1,'audit'=>1])
                ->order('count_total desc')
                ->limit(10)
                ->select();

        if(!$count){
            return 1;
        }else{
            return $this->fetch('',[

                'count'   =>$count,
                'e'     =>$e=4
            ]);
        }

    }
    public function vipvote_month(){

            $vipvote=Db::view('Book','book_id,book_name,author_name,upload_img')
                ->view('BookStatistical','vipvote_month','BookStatistical.book_id=Book.book_id')
                ->view('BookType','book_type','BookType.type_id=Book.type_id')
                ->where(['is_show'=>1,'audit'=>1])
                ->order('vipvote_month desc')
                ->limit(10)
                ->select();

        if(!$vipvote){
            return 1;
        }else{
            return $this->fetch('',[
               'vipvote'   =>$vipvote,
               'g'          =>$g=4
            ]);
        }

    }
    public function vipvote_total(){

            $vipvote=Db::view('Book','book_id,book_name,author_name,upload_img')
                ->view('BookStatistical','vipvote_total','BookStatistical.book_id=Book.book_id')
                ->view('BookType','book_type','BookType.type_id=Book.type_id')
                ->where(['is_show'=>1,'audit'=>1])
                ->order('vipvote_total desc')
                ->limit(10)
                ->select();

        if(!$vipvote){
            return 1;
        }else{
            return $this->fetch('',[
                'vipvote'   =>$vipvote,
                'g'          =>$g=4
            ]);
        }

    }
    public function t_click_weeks(){

           $tongren=Db::view('Book','book_id,book_name,author_name,upload_img')
               ->view('BookStatistical','vote_weeks','BookStatistical.book_id=Book.book_id')
               ->view('BookType','book_type','BookType.type_id=Book.type_id')
               ->where(['is_show'=>1,'audit'=>1,'Book.type_id'=>16])
               ->order('vote_weeks desc')
               ->limit(10)
               ->select();

        if(!$tongren){
           return 1;
        }else{
           return $this->fetch('',[
               'tongren'     =>$tongren,
               'h'            =>$h=4
           ]);
        }
    }
    public function t_click_month(){

            $tongren=Db::view('Book','book_id,book_name,author_name,upload_img')
                ->view('BookStatistical','vote_month','BookStatistical.book_id=Book.book_id')
                ->view('BookType','book_type','BookType.type_id=Book.type_id')
                ->where(['is_show'=>1,'audit'=>1,'Book.type_id'=>16])
                ->order('vote_month desc')
                ->limit(10)
                ->select();

        if(!$tongren){
            return 1;
        }else{
            return $this->fetch('',[
                'tongren'     =>$tongren,
                'h'            =>$h=4
            ]);
        }
    }
    public function t_click_total(){

            $tongren=Db::view('Book','book_id,book_name,author_name,upload_img')
                ->view('BookStatistical','vote_total','BookStatistical.book_id=Book.book_id')
                ->view('BookType','book_type','BookType.type_id=Book.type_id')
                ->where(['is_show'=>1,'audit'=>1,'Book.type_id'=>16])
                ->order('vote_total desc')
                ->limit(10)
                ->select();

        if(!$tongren){
            return 1;
        }else{
            return $this->fetch('',[
                'tongren'     =>$tongren,
                'h'            =>$h=4
            ]);
        }
    }
}