<?php
namespace app\api\controller;
use think\Controller;
use think\Db;
class Content extends Controller{

     public function get_content(){
         $bookid      =input('post.bookid');
         $content_id  =input('post.content_id');
         $content=$this->content($bookid,$content_id);
         if(!$content){
             return 1;
         }else{
             return $this->fetch('',[
                 'content'   =>$content,
                 'time'      =>date('Y-m-d H:i:s')
             ]);
         }
     }

     //获取书籍内容
    public function content($bookid,$content_id){

        $content=Db::view('Content','content_id,title,number,the_price,state,time,update_time')
            ->view('Contents','content,msg','Contents.content_id=Content.content_id')
            ->where(['Content.book_id'=>$bookid,'type'=>0,'Content.content_id'=>$content_id])
            ->find();
        $content['content']=html_entity_decode($content['content']);
        $content['content']=str_replace("\n","</p><p style=\"text-indent: 2em;line-height: 40px;font-size: 16px;\">",$content['content']);
        return $content;

    }

    //删除章节
    /*
    public function delete(){

        $content_id  =input('post.content_id');
        $is =\think\Loader::controller('gongju/Chapterlei')->delete($content_id);
        if($is==1){
            return 1;
        }else{
            return 2;
        }

    }
*/
   //删除草稿箱
    public function dele(){
        Db::startTrans();//开启事务
        $bookid =input('post.bookid');
        $content_id =input('post.content_id');
       $chapter =Db::name('Content')->where(['book_id'=>$bookid,'content_id'=>$content_id,'state'=>3])->find();
       if(!is_array($chapter)){
           $this->error('没有该章节');
       }
     try{
         $re1=  Db::name('Content')->where(array('content_id' => $content_id))->delete();
         $re2=      Db::name('Contents')->where(array('content_id' => $content_id))->delete();
         //更新坐标
         $map['book_id'] =$bookid;
         $map['num'] = array('GT', $chapter['num']); //查找比该坐标大的数据
         $gengxinid = Db::name('Content')->where($map)->field('content_id')->order('num ASC')->select();
         if (is_array($gengxinid)) {
             $xinzb = $chapter['num'];
             for ($i = 0; $i < count($gengxinid); $i++) {
                 Db::name('Content')->where(array('content_id' => $gengxinid[$i]['content_id']))->update(array('num' => $xinzb));
                 $xinzb++;
             }
         }

         Db::commit();//提交事务
         return 1;

     }catch (\Exception $e){
         Db::rollback();//回滚事务
     }





    }



    //书籍签约
    public function apply(){
         $bookName =input('post.bookName');
        $book= Db::name('Book')->where(['book_name'=>$bookName,'audit'=>1])->find();
        if($book['sign_id']==1){
            return 1;
        }elseif ($book['sign_id']==2){
            return 2;
        }elseif ($book['sign_id']==3){
            return 3;
        }elseif ($book['sign_id']==4 && $book['contract_id']==2){
            return 4;
        }elseif ($book['contract_id']>2){
            return 6;
        }
       //修改申请状态
        $con['sign_id']=1;
        $con['con_id']=2;
        $con['sign_time']=date('Y-m-d H:i:s');
       $re=  Db::name('Book')->where(['book_name'=>$bookName,'audit'=>1])->update($con);
       if($re){
           return 5;
       }else{
           return 0;
       }

    }

    public function applys(){
         $bookName =input('post.bookName');
        $book= Db::name('Book')->where(['book_name'=>$bookName,'audit'=>1])->find();
        if($book['sign_id']==1){
            return 1;
        }elseif ($book['sign_id']==2){
            return 2;
        }elseif ($book['sign_id']==3){
            return 3;
        }elseif ($book['con_id']>4){
            return 4;
        }
        //获取当前合同级别
        $con_id =$book['con_id'];
        if($con_id==1){
            $con_id=$con_id+2;
        }else{
            $con_id=$con_id+1;
        }
        //修改申请状态
        $con['sign_id']=1;
        $con['con_id']=$con_id;
        $con['sign_time']=date('Y-m-d H:i:s');
        $re=  Db::name('Book')->where(['book_name'=>$bookName,'audit'=>1])->update($con);
        if($re){
            return 5;
        }else{
            return 0;
        }
    }
}