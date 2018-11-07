<?php
namespace app\api\controller;
use think\Controller;
use think\Db;
class Search extends  Controller{
    public function index(){

        $keyword =trim(input('post.keyword'));

        if(mb_strlen($keyword,'utf8')>=2){
            //查询作者
          //  $where['pen_name'] =array('like',"%$keyword%");
          //  $author=  Db::name('Writer')->where($where)->field('pen_name')->select();

            //查询书籍
            $con['book_name'] =array('like',"%$keyword%");
            $con['is_show']=1;
            $con['audit']=1;
            $book=Db::name('Book')->where($con)->field('book_name')->select();

            return $this->fetch('',[
            //    'author' =>$author,
                'book'   =>$book
            ]);
        }
    }
}