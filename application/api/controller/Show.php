<?php
namespace app\api\controller;
use think\Controller;
use think\Db;
class Show extends Controller{

    public function show(){

         $bookid=input('post.bookid');
        if(!is_numeric($bookid)){
            return 1;
        }
        $where['book_id']=$bookid;
        $where['state']=1;
        $where['status']=0;
        $where['type']=0;
        $chapter=Db::name('Content')->where($where)->field('book_id,num,the_price,title')->select();
        if(!$chapter){
            return 2;
        }else{
            return $this->fetch('',[
                'chapter'  =>$chapter,
                'count'    =>count($chapter)

            ]);
        }
    }

    public function daoxu(){
        $bookid=input('post.bookid');
        if(!is_numeric($bookid)){
            return 1;
        }
        $where['book_id']=$bookid;
        $where['state']=1;
        $where['status']=0;
        $where['type']=0;
        $chapter=Db::name('Content')->where($where)->field('book_id,num,the_price,title')->order('num desc')->select();
        if(!$chapter){
            return 2;
        }else{
            return $this->fetch('',[
                'chapter'  =>$chapter,
                'count'    =>count($chapter)

            ]);
        }
    }

    public function showlast(){

        $bookid=input('post.bookid');
        if(!is_numeric($bookid)){
            return 1;
        }
        $where['book_id']=$bookid;
        $where['state']=1;
        $where['status']=0;
        $where['type']=0;
        $chapter=Db::name('Content')->where($where)->field('book_id,num,the_price,title')->order('num desc')->select();
        if(!$chapter){
            return 2;
        }else{
            return $this->fetch('',[
                'chapter'  =>$chapter,
                'count'    =>count($chapter)

            ]);
        }
    }

    public function zhengxu(){
        $bookid=input('post.bookid');
        if(!is_numeric($bookid)){
            return 1;
        }
        $where['book_id']=$bookid;
        $where['state']=1;
        $where['status']=0;
        $where['type']=0;
        $chapter=Db::name('Content')->where($where)->field('book_id,num,the_price,title')->order('num asc')->select();
        if(!$chapter){
            return 2;
        }else{
            return $this->fetch('',[
                'chapter'  =>$chapter,
                'count'    =>count($chapter)

            ]);
        }

    }
}