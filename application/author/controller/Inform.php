<?php
namespace app\author\controller;
use think\Controller;
use think\Db;
use think\Request;
class Inform extends Base{
    public function notice(){

        $ad=Db::name('WriterAd')->where(['state'=>1])->limit(5)->order('time desc')->select();

        foreach ($ad as $k=>$v){
            $v['content']=html_entity_decode($v['content']);
            $v['content']=str_replace("\n","</p><p class=\"item\">",$v['content']);
            $ad[$k]['content']=$v['content'];
        }


        return $this->fetch('',[
            'ad'   =>$ad
        ]);
    }

    public function sign(){

        return $this->fetch();
    }
    //作者邮箱
    public function email(){
        $author =$this->account;

        //将邮箱修改为已读
       $result= Db::name('WriterEmail')->where(['author_id'=>$author['author_id']])->update(['is_show'=>1]);
       if($result){
           $this->redirect('Inform/email');
       }

       $email= Db::name('WriterEmail')->where(['author_id'=>$author['author_id'],'state'=>1])->order('time desc')->paginate(10);
        return $this->fetch('',[
            'email'   =>$email,
            'a'        =>$a=1
        ]);
    }

    //删邮件
    function deleEmail(){
        $id =input('post.content_id');
       $result= Db::name('WriterEmail')->where(['id'=>$id])->update(['state'=>0]);
        if($result){
            return 1;
        }else{
            return 0;
        }
    }

    //作者咨询
    public function refer(){
        $author =$this->account;
        if(request()->isPost()){
            $data =input('post.');
            if($data['bookName']==""){
                $this->error('请选择作品');
            }
            $con['author_id']  =$author['author_id'];
            $con['book_name']  =$data['bookName'];
            $con['title']      =$data['title'];
            $con['content']   =$data['content'];
            $con['time']      =date('Y-m-d H:i:s');
           $result= Db::name('WriterRefer')->insert($con);
           if($result){
               $this->success('发送成功');
           }else{
               $this->error('发送失败');
           }

        }else{
            $book =Db::name('Book')->where(['author_id'=>$author['author_id']])->select();
            return $this->fetch('',[
                'book'    =>$book
            ]);

        }

    }

    //关于书咚
    public function adoat(){

        return $this->fetch();
    }

    //批量删除邮件
    public function piliang(){
        if(!request()->isPost()){
            $this->error('系统错误');
        }
        $value=input('post.checkbox/a');

        foreach ($value as $k=>$v){

            Db::name('WriterEmail')->where(['id'=>$v])->update(['state'=>0]);

        }

        $this->success('邮件删除成功');
    }
}