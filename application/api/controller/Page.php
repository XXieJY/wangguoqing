<?php
namespace app\api\controller;
use think\Controller;
use think\Db;
class Page extends Controller{
    public function pre_page(){
        $current =input('post.current');
        $bang=input('post.bang');
        $bangs=input('post.bangs');
        $type=input('post.type');
        $p=input('post.p');
        if($p=="newBook"){
            $xin="abc";
        }elseif($p=="vipvote"){
            $xin='ab';
        }elseif($p=="words"){
            $xin='a';

        }else{
            $xin='';
        }
        $list=$this->echo_click_weeks($current,$bangs,$type);
        if(!$list){
            return 1;
        }else{
            $page=($current-1)*10;
            return $this->fetch('',[
                'weeks'   =>$list['weeks'],
                'a'     =>$page,
                'current'  =>$current,
                'page'    =>$list['page'],
                'bang'    =>$bang,
                'bangs'    =>$bangs,
                'xin'     =>$xin,
                'type'    =>$type,
                'p'       =>$p

            ]);
        }
    }

    public function next_page(){
        $current =input('post.current');
        $bang=input('post.bang');
        $bangs=input('post.bangs');
        $type=input('post.type');
        $p=input('post.p');

        if($p=="newBook"){
            $xin="abc";
        }elseif($p=="vipvote"){
            $xin='ab';
        }elseif($p=="words"){
            $xin='a';

        }else{
            $xin='';
        }
        $current=$current+1;

        $list=$this->echo_click_weeks($current,$bangs,$type);
        if(!$list){
            return 1;
        }else{
            $page=($current-1)*10;
            return $this->fetch('',[
                'weeks'   =>$list['weeks'],
                'a'     =>$page,
                'current'  =>$current,
                'page'    =>$list['page'],
                'bang'    =>$bang,
                'bangs'    =>$bangs,
                'xin'     =>$xin,
                'type'    =>$type,
                'p'       =>$p
            ]);
        }

    }
  public function cur_current(){
      $current =input('post.current');
      $bang=input('post.bang');
      $bangs=input('post.bangs');
      $type=input('post.type');
      $p=input('post.p');
      if($p=="newBook"){
          $xin="abc";
      }elseif($p=="vipvote"){
          $xin='ab';
      }elseif($p=="words"){
          $xin='a';

      }else{
          $xin='';
      }
      $list=$this->echo_click_weeks($current,$bangs,$type);
      if(!$list){
          return 1;
      }else{
          $page=($current-1)*10;
          return $this->fetch('',[
              'weeks'   =>$list['weeks'],
              'a'     =>$page,
              'current'  =>$current,
              'page'    =>$list['page'],
              'bang'   =>$bang,
              'bangs'    =>$bangs,
              'xin'    =>$xin,
              'type'   =>$type,
              'p'       =>$p
          ]);
      }

  }

  public function current_one(){
      $current =input('post.current');
      $bang=input('post.bang');
      $bangs=input('post.bangs');
      $type=input('post.type');
      $p=input('post.p');
      if($p=="newBook"){
          $xin="abc";
      }elseif($p=="vipvote"){
          $xin='ab';
      }elseif($p=="words"){
          $xin='a';

      }else{
          $xin='';
      }
      $current=$current+1;

      $list=$this->echo_click_weeks($current,$bangs,$type);
      if(!$list){
          return 1;
      }else{
          $page=($current-1)*10;
          return $this->fetch('',[
              'weeks'   =>$list['weeks'],
              'a'     =>$page,
              'current'  =>$current,
              'page'    =>$list['page'],
              'bang'   =>$bang,
              'bangs'    =>$bangs,
              'xin'     =>$xin,
              'type'    =>$type,
              'p'       =>$p
          ]);
      }

  }
    public function current_two(){
        $current =input('post.current');
        $bang=input('post.bang');
        $bangs=input('post.bangs');
        $type=input('post.type');
        $p=input('post.p');
        if($p=="newBook"){
            $xin="abc";
        }elseif($p=="vipvote"){
            $xin='ab';
        }elseif($p=="words"){
            $xin='a';

        }else{
            $xin='';
        }
        $current=$current+2;

        $list=$this->echo_click_weeks($current,$bangs,$type);
        if(!$list){
            return 1;
        }else{
            $page=($current-1)*10;
            return $this->fetch('',[
                'weeks'   =>$list['weeks'],
                'a'     =>$page,
                'current'  =>$current,
                'page'    =>$list['page'],
                'bang'    =>$bang,
                'bangs'    =>$bangs,
                'xin'     =>$xin,
                'type'   =>$type,
                'p'       =>$p
            ]);
        }

    }
    public function current_three(){
        $current =input('post.current');
        $bang=input('post.bang');
        $bangs=input('post.bangs');
        $type=input('post.type');
        $p=input('post.p');
        if($p=="newBook"){
            $xin="abc";
        }elseif($p=="vipvote"){
            $xin='ab';
        }elseif($p=="words"){
            $xin='a';

        }else{
            $xin='';
        }
        $current=$current+3;

        $list=$this->echo_click_weeks($current,$bangs,$type);
        if(!$list){
            return 1;
        }else{
            $page=($current-1)*10;
            return $this->fetch('',[
                'weeks'   =>$list['weeks'],
                'a'     =>$page,
                'current'  =>$current,
                'page'    =>$list['page'],
                'bang'    =>$bang,
                'bangs'    =>$bangs,
                'xin'     =>$xin,
                'type'    =>$type,
                'p'       =>$p
            ]);
        }

    }
    public function one_current(){
        $current =input('post.current');
        $bang=input('post.bang');
        $bangs=input('post.bangs');
        $type=input('post.type');
        $p=input('post.p');
        if($p=="newBook"){
            $xin="abc";
        }elseif($p=="vipvote"){
            $xin='ab';
        }elseif($p=="words"){
            $xin='a';

        }else{
            $xin='';
        }
        $current=$current-1;

        $list=$this->echo_click_weeks($current,$bangs,$type);
        if(!$list){
            return 1;
        }else{
            $page=($current-1)*10;
            return $this->fetch('',[
                'weeks'   =>$list['weeks'],
                'a'     =>$page,
                'current'  =>$current,
                'page'    =>$list['page'],
                'bang'    =>$bang,
                'bangs'    =>$bangs,
                'xin'     =>$xin,
                'type'   =>$type,
                'p'       =>$p
            ]);
        }

    }
    public function two_current(){
        $current =input('post.current');
        $bang=input('post.bang');
        $bangs=input('post.bangs');
        $type=input('post.type');
        $p=input('post.p');
        if($p=="newBook"){
            $xin="abc";
        }elseif($p=="vipvote"){
            $xin='ab';
        }elseif($p=="words"){
            $xin='a';

        }else{
            $xin='';
        }
        $current=$current-2;

        $list=$this->echo_click_weeks($current,$bangs,$type);
        if(!$list){
            return 1;
        }else{
            $page=($current-1)*10;
            return $this->fetch('',[
                'weeks'   =>$list['weeks'],
                'a'     =>$page,
                'current'  =>$current,
                'page'    =>$list['page'],
                'bang'    =>$bang,
                'bangs'    =>$bangs,
                'xin'     =>$xin,
                'type'    =>$type,
                'p'       =>$p
            ]);
        }

    }
    public function three_current(){
        $current =input('post.current');
        $bang=input('post.bang');
        $bangs=input('post.bangs');
        $type=input('post.type');
        $p=input('post.p');
        if($p=="newBook"){
            $xin="abc";
        }elseif($p=="vipvote"){
            $xin='ab';
        }elseif($p=="words"){
            $xin='a';

        }else{
            $xin='';
        }
        $current=$current-3;

        $list=$this->echo_click_weeks($current,$bangs,$type);
        if(!$list){
            return 1;
        }else{
            $page=($current-1)*10;
            return $this->fetch('',[
                'weeks'   =>$list['weeks'],
                'a'     =>$page,
                'current'  =>$current,
                'page'    =>$list['page'],
                'bang'    =>$bang,
                'bangs'    =>$bangs,
                'xin'     =>$xin,
                'type'    =>$type,
                'p'       =>$p
            ]);
        }

    }
    /*
    * 点击榜周榜
    */
    public function echo_click_weeks($current,$bang,$type){
        $list=[];
        $where['Book.is_show']=1;
        $where['Book.audit']=1;
        if($type==1){
            $where['Book.type_id']=16;
        }
        //分页变量
        $pageSize = 10;//每页显示的记录数
        $totalRow = 0;//总记录数
        $totalPage = 0;//总页数
        $start = ($current-1)*$pageSize;//每页记录的起始值

       if($type==2){
           $time=date('Y-m');
           $where['Book.create_time'] =array('like',"%$time%");
           $totalRow=Db::view('Book','book_id,book_name,author_name,upload_img,book_brief')
               ->view('Content','title,num,time','Content.book_id=Book.book_id and Content.num=Book.chapter')
               ->view('BookStatistical',"{$bang}",'BookStatistical.book_id=Book.book_id')
               ->where($where)
               ->count();
           $totalPage =ceil($totalRow/$pageSize);

           $click_weeks=Db::view('Book','book_id,book_name,author_name,upload_img,book_brief')
               ->view('Content','title,num,time','Content.book_id=Book.book_id and Content.num=Book.chapter')
               ->view('BookStatistical',"{$bang}",'BookStatistical.book_id=Book.book_id')
               ->where($where)
               ->limit($start,$pageSize)
               ->order("BookStatistical.{$bang} desc,Book.create_time desc")
               ->select();
       }else{
           $totalRow=Db::view('Book','book_id,book_name,author_name,upload_img,book_brief')
               ->view('Content','title,num,time','Content.book_id=Book.book_id and Content.num=Book.chapter')
               ->view('BookStatistical',"{$bang}",'BookStatistical.book_id=Book.book_id')
               ->where($where)
               ->count();
           $totalPage =ceil($totalRow/$pageSize);
           $click_weeks=Db::view('Book','book_id,book_name,author_name,upload_img,book_brief')
               ->view('Content','title,num,time','Content.book_id=Book.book_id and Content.num=Book.chapter')
               ->view('BookStatistical',"{$bang}",'BookStatistical.book_id=Book.book_id')
               ->where($where)
               ->limit($start,$pageSize)
               ->order("BookStatistical.{$bang} desc")
               ->select();
       }

        $list=[
            'page'    =>$totalPage,
            'weeks'   =>$click_weeks
        ];
        return $list;
    }
}