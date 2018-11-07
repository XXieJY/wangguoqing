<?php
namespace app\api\controller;
use think\Controller;
use think\Db;
class Page1 extends Controller{

    public function pre_page1(){

        $bookid=input('post.bookid');
        $current=input('post.current');
        $current=$current-1;
        $message= $this->get_message($bookid,$current);
        $book =Db::name('Book')->where(['book_id'=>$bookid])->find();
        if(!$message){
            return 1;
        }else{
            return $this->fetch('',[
                'message' =>$message,
                'current'=>$current,
                'bookid' =>$bookid,
                'book'   =>$book
            ]);
        }

    }
    public function next_page1(){

        $bookid=input('post.bookid');
        $current=input('post.current');
        $current=$current+1;
       $message= $this->get_message($bookid,$current);
        $book =Db::name('Book')->where(['book_id'=>$bookid])->find();
       if(!$message){
           return 1;
       }else{
           return $this->fetch('',[
               'message' =>$message,
               'current'=>$current,
               'bookid' =>$bookid,
               'book'   =>$book
           ]);
       }

    }

    public function one_page(){

    $bookid=input('post.bookid');
    $current=input('post.current');
    $current=$current-1;
    $message= $this->get_message($bookid,$current);
    $book =Db::name('Book')->where(['book_id'=>$bookid])->find();
    if(!$message){
        return 1;
    }else{
        return $this->fetch('',[
            'message' =>$message,
            'current'=>$current,
            'bookid' =>$bookid,
            'book'   =>$book
        ]);
    }

}

    public function two_page(){

        $bookid=input('post.bookid');
        $current=input('post.current');
        $current=$current+1;
        $message= $this->get_message($bookid,$current);
        $book =Db::name('Book')->where(['book_id'=>$bookid])->find();
        if(!$message){
            return 1;
        }else{
            return $this->fetch('',[
                'message' =>$message,
                'current'=>$current,
                'bookid' =>$bookid,
                'book'    =>$book
            ]);
        }

    }

    public function get_message($bookid,$current){
        $list=[];
        $where['book_id']=$bookid;
        $where['f_id']=0;
        $where['status']=1;
        //分页变量
        $pageSize = 10;//每页显示的记录数
        $totalRow = 0;//总记录数
        $totalPage = 0;//总页数
        $start = ($current-1)*$pageSize;//每页记录的起始值
        $totalRow=Db::view('BookMessage','z_id,f_id,top,content,thumb,num,time')
            ->view('User','pen_name,portrait,mem_vip,days,is_author','User.user_id=BookMessage.user_id')
            ->where($where)
            ->count();
        $totalPage =ceil($totalRow/$pageSize);
        $message= Db::view('BookMessage','z_id,f_id,top,content,thumb,num,time')
            ->view('User','pen_name,portrait,mem_vip,days,is_author,sex','User.user_id=BookMessage.user_id')
            ->where($where)
            ->limit($start,$pageSize)
            ->order(['BookMessage.top'=>'desc','BookMessage.update_time'=>'desc'])
            ->select();
        $length=count($message);
        for ($i=0;$i<$length;$i++){
            $map['status']=1;
            $map['f_id']=$message[$i]['z_id'];
            $message[$i]['msg']=Db::view('BookMessage','z_id,f_id,top,content,thumb,num,time')
                ->view('User','pen_name,portrait,mem_vip,days,is_author,sex','User.user_id=BookMessage.user_id')
                ->where($map)
                ->select();
            $message[$i]['isThumb'] =$this->isThumb($message[$i]['z_id']);
        }
        for ($i=0;$i<$length;$i++){
            if(strlen($message[$i]['portrait'])<60){
                if($message[$i]['portrait']=="user/portrait/portrait.jpg"){

                    $message[$i]['portrait']="http://images.shuddd.com/user/portrait/portrait".$message[$i]['sex'].".png";

                }else{
                    $message[$i]['portrait']="http://images.shuddd.com/".$message[$i]['portrait'];
                }

            }
            for ($j=0;$j<count($message[$i]['msg']);$j++){
                if(strlen($message[$i]['msg'][$j]['portrait'])<60){

                    if($message[$i]['msg'][$j]['portrait']=="user/portrait/portrait.jpg"){

                        $message[$i]['msg'][$j]['portrait']="http://images.shuddd.com/user/portrait/portrait".$message[$i]['msg'][$j]['sex'].".png";

                    }else{
                        $message[$i]['msg'][$j]['portrait']="http://images.shuddd.com/".$message[$i]['msg'][$j]['portrait'];
                    }

                }
            }
        }
        $list=[
            'message'  =>$message,
            'page'     =>$totalPage
            ];
        return $list;
    }

    /*
  * 判断用户是否点赞
  *
  */
    private function isThumb($zid){
        $useArr =cookie('shudong_user');
        $where['message_id'] =$zid;
        $where['user_id'] =$useArr['user_id'];
        $result =  Db::name('MessageThumb')->where($where)->find();
        if(!(is_array($result))){

            return 0;
        }
        if($result['status']==1){
            return 1;
        }else{
            return 0;
        }

    }
}