<?php
namespace app\index\controller;
use think\Controller;
use think\Db;
use think\Request;
class Book extends Controller{

    public function index(){

       // print_r(input('param.'));exit();
      //   print_r(request()->baseUrl());
       // return $this->fetch();
        if(request()->isPost()){
            $bookNmae=trim(input('post.book_name'));
           // print_r($bookNmae);exit();
            $where['book_name']=array('like',"%$bookNmae%");
            $current =input('post.jump')==""? '1':input('post.jump');
            if($current[0]==0){
                $current=substr($current,1);
            }

        }else{
            $current =input('param.page')==""? '1':input('param.page');
        }

        $bookType= $this->book_type();//作品分类
        $book_key =$this->book_key();//作品标签

        $type=input('param.type');
        $key=input('param.key');
        $rank=input('param.rank');
        $time=input('param.time');
        $words=input('param.words');
        $state=input('param.state');
        $where['is_show']=1;
        $where['audit']=1;
       //时间条件
        switch ($time) {
            case 0:
                $this->assign('t0', 'current');
                $this->assign('t1', '');
                $this->assign('t2', '');
                $this->assign('t3', '');
                $this->assign('t4', '');

                break;
            case 1:
                $start=date('Y-m-d H:i:s');
                $end =date("Y-m-d 00:00:00",strtotime("-3 day"));
                $where['Content.time']=array(array('gt',$end),array('lt',$start));
                $this->assign('t0', '');
                $this->assign('t1', 'current');
                $this->assign('t2', '');
                $this->assign('t3', '');
                $this->assign('t4', '');

                break;
            case 2:
                $start=date('Y-m-d H:i:s');
                $end =date("Y-m-d 00:00:00",strtotime("-7 day"));
                $where['Content.time']=array(array('gt',$end),array('lt',$start));
                $this->assign('t0', '');
                $this->assign('t1', '');
                $this->assign('t2', 'current');
                $this->assign('t3', '');
                $this->assign('t4', '');

                break;
            case 3:
                $start=date('Y-m-d H:i:s');
                $end =date("Y-m-d 00:00:00",strtotime("-15 day"));
                $where['Content.time']=array(array('gt',$end),array('lt',$start));
                $this->assign('t0', '');
                $this->assign('t1', '');
                $this->assign('t2', '');
                $this->assign('t3', 'current');
                $this->assign('t4', '');

                break;
            case 4:
                $start=date('Y-m-d H:i:s');
                $end =date("Y-m-d 00:00:00",strtotime("-30 day"));
                $where['Content.time']=array(array('gt',$end),array('lt',$start));
                $this->assign('t0', '');
                $this->assign('t1', '');
                $this->assign('t2', '');
                $this->assign('t3', '');
                $this->assign('t4', 'current');

                break;
        }
        if($type==0){
          $this->assign('type','current');
        }else{
            $this->assign('type','');
            $where['BookType.type_id']=$type;
        }
        if($key==0){
            $this->assign('key1','current');

        }else{
            $this->assign('key1','');
           $keywords =Db::name('SystemKeys')->where(['id'=>$key])->find();
           $keyword=$keywords['key'];
            $where['Book.keywords']=array('like',"%$keyword%");
        }
        //排行榜
        switch ($rank) {
            case 0:$order = 'Content.time desc';
                $this->assign('cl0', 'current');
                $this->assign('cl1', '');
                $this->assign('cl2', '');
                $this->assign('cl3', '');
                $this->assign('cl4', '');
                $this->assign('cl5', '');
                $this->assign('cl6', '');
                $this->assign('cl7', '');
                break;
            case 1:$order = 'BookStatistical.click_weeks desc';
                $this->assign('cl0', '');
                $this->assign('cl1', 'current');
                $this->assign('cl2', '');
                $this->assign('cl3', '');
                $this->assign('cl4', '');
                $this->assign('cl5', '');
                $this->assign('cl6', '');
                $this->assign('cl7', '');
                break;
            case 2:$order = 'BookStatistical.click_month desc';
                $this->assign('cl0', '');
                $this->assign('cl1', '');
                $this->assign('cl2', 'current');
                $this->assign('cl3', '');
                $this->assign('cl4', '');
                $this->assign('cl5', '');
                $this->assign('cl6', '');
                $this->assign('cl7', '');
                break;
            case 3:$order = 'BookStatistical.click_total desc';
                $this->assign('cl0', '');
                $this->assign('cl1', '');
                $this->assign('cl2', '');
                $this->assign('cl3', 'current');
                $this->assign('cl4', '');
                $this->assign('cl5', '');
                $this->assign('cl6', '');
                $this->assign('cl7', '');
                break;
            case 4:$order = 'BookStatistical.vote_weeks desc';
                $this->assign('cl0', '');
                $this->assign('cl1', '');
                $this->assign('cl2', '');
                $this->assign('cl3', '');
                $this->assign('cl4', 'current');
                $this->assign('cl5', '');
                $this->assign('cl6', '');
                $this->assign('cl7', '');
                break;
            case 5:$order = 'BookStatistical.vote_month desc';
                $this->assign('cl0', '');
                $this->assign('cl1', '');
                $this->assign('cl2', '');
                $this->assign('cl3', '');
                $this->assign('cl4', '');
                $this->assign('cl5', 'current');
                $this->assign('cl6', '');
                $this->assign('cl7', '');
                break;
            case 6:$order = 'BookStatistical.vote_total desc';
                $this->assign('cl0', '');
                $this->assign('cl1', '');
                $this->assign('cl2', '');
                $this->assign('cl3', '');
                $this->assign('cl4', '');
                $this->assign('cl5', '');
                $this->assign('cl6', 'current');
                $this->assign('cl7', '');
                break;
            case 7:$order = 'BookStatistical.collection_total desc';
                $this->assign('cl0', '');
                $this->assign('cl1', '');
                $this->assign('cl2', '');
                $this->assign('cl3', '');
                $this->assign('cl4', '');
                $this->assign('cl5', '');
                $this->assign('cl6', '');
                $this->assign('cl7', 'current');
                break;
        }
        //字数
        switch ($words) {
            case 0:
                $this->assign('w0', 'current');
                $this->assign('w1', '');
                $this->assign('w2', '');
                $this->assign('w3', '');
                $this->assign('w4', '');
                $this->assign('w5', '');

                break;
            case 1:
                $start=0;
                $end =300000;
                $where['Book.words']=array(array('gt',$start),array('lt',$end));
                $this->assign('w0', '');
                $this->assign('w1', 'current');
                $this->assign('w2', '');
                $this->assign('w3', '');
                $this->assign('w4', '');
                $this->assign('w5', '');

                break;
            case 2:
                $start=300000;
                $end =500000;
                $where['Book.words']=array(array('gt',$start),array('lt',$end));
                $this->assign('w0', '');
                $this->assign('w1', '');
                $this->assign('w2', 'current');
                $this->assign('w3', '');
                $this->assign('w4', '');
                $this->assign('w5', '');
                break;
            case 3:
                $start=500000;
                $end =1000000;
                $where['Book.words']=array(array('gt',$start),array('lt',$end));
                $this->assign('w0', '');
                $this->assign('w1', '');
                $this->assign('w2', '');
                $this->assign('w3', 'current');
                $this->assign('w4', '');
                $this->assign('w5', '');
                break;
            case 4:
                $start=1000000;
                $end =2000000;
                $where['Book.words']=array(array('gt',$start),array('lt',$end));
                $this->assign('w0', '');
                $this->assign('w1', '');
                $this->assign('w2', '');
                $this->assign('w3', '');
                $this->assign('w4', 'current');
                $this->assign('w5', '');

                break;
            case 5:
                $start=2000000;
                $where['Book.words']=array(array('gt',$start));
                $this->assign('w0', '');
                $this->assign('w1', '');
                $this->assign('w2', '');
                $this->assign('w3', '');
                $this->assign('w4', '');
                $this->assign('w5', 'current');

                break;
        }

        //其他选择
        switch ($state) {
            case 0:
                $this->assign('q0', 'current');
                $this->assign('q1', '');
                $this->assign('q2', '');
                $this->assign('q3', '');
                $this->assign('q4', '');
                $this->assign('q5', '');
                $this->assign('q6', '');
                $this->assign('q7', '');
                $this->assign('q8', '');
                break;
            case 1:
                $where['vip']=0;
                $this->assign('q0', '');
                $this->assign('q1', 'current');
                $this->assign('q2', '');
                $this->assign('q3', '');
                $this->assign('q4', '');
                $this->assign('q5', '');
                $this->assign('q6', '');
                $this->assign('q7', '');
                $this->assign('q8', '');
                break;
            case 2:
                $where['Book.state']=2;
                $this->assign('q0', '');
                $this->assign('q1', '');
                $this->assign('q2', 'current');
                $this->assign('q3', '');
                $this->assign('q4', '');
                $this->assign('q5', '');
                $this->assign('q6', '');
                $this->assign('q7', '');
                $this->assign('q8', '');
                break;
            case 3:
                $where['level']=1;
                $this->assign('q0', '');
                $this->assign('q1', '');
                $this->assign('q2', '');
                $this->assign('q3', 'current');
                $this->assign('q4', '');
                $this->assign('q5', '');
                $this->assign('q6', '');
                $this->assign('q7', '');
                $this->assign('q8', '');
                break;
            case 4:
                $where['gender']=1;
                $this->assign('q0', '');
                $this->assign('q1', '');
                $this->assign('q2', '');
                $this->assign('q3', '');
                $this->assign('q4', 'current');
                $this->assign('q5', '');
                $this->assign('q6', '');
                $this->assign('q7', '');
                $this->assign('q8', '');
                break;
            case 5:
                $where['gender']=0;
                $this->assign('q0', '');
                $this->assign('q1', '');
                $this->assign('q2', '');
                $this->assign('q3', '');
                $this->assign('q4', '');
                $this->assign('q5', 'current');
                $this->assign('q6', '');
                $this->assign('q7', '');
                $this->assign('q8', '');
                break;
            case 6:
                $where['Book.state']=1;
                $this->assign('q0', '');
                $this->assign('q1', '');
                $this->assign('q2', '');
                $this->assign('q3', '');
                $this->assign('q4', '');
                $this->assign('q5', '');
                $this->assign('q6', 'current');
                $this->assign('q7', '');
                $this->assign('q8', '');
                break;
            case 7:
                $where['Book.vip']=1;
                $this->assign('q0', '');
                $this->assign('q1', '');
                $this->assign('q2', '');
                $this->assign('q3', '');
                $this->assign('q4', '');
                $this->assign('q5', '');
                $this->assign('q6', '');
                $this->assign('q7', 'current');
                $this->assign('q8', '');
                break;
            case 8:
                $where['Book.contract_id']=array('gt',1);
                $where['Book.sign_id']=4;
                $this->assign('q0', '');
                $this->assign('q1', '');
                $this->assign('q2', '');
                $this->assign('q3', '');
                $this->assign('q4', '');
                $this->assign('q5', '');
                $this->assign('q6', '');
                $this->assign('q7', '');
                $this->assign('q8', 'current');
                break;
        }

       //print_r($where);
        $book=$this->get_rank_list($where,$order,$current);//书籍分类;
        $vote =$this->echo_vote();
        //print_r($vote);
       return $this->fetch('',[
           'ok'           =>5,
           'bookType'    =>$bookType,
           'book_key'    =>$book_key,
           'book'        =>$book[0],
           'a'           =>($current-1)*30,
           'vote'        =>$vote,
           'total'       =>$book[1],
           'current'     =>$current
       ]);
    }


    /*
     * 作品分类
     */
    public function book_type(){
       $bookType= Db::name('BookType')->order('xu ASC')->select();
       return $bookType;
    }

    /*
     * 作品标签
     */
    public function book_key(){
        $book_key =Db::name('SystemKeys')->select();
        return $book_key;
    }

    public function get_rank_list($where,$order,$current){

        //分页变量
        $pageSize = 30;//每页显示的记录数
        $totalRow = 0;//总记录数
        $totalPage = 0;//总页数
        $start = ($current-1)*$pageSize;//每页记录的起始值

        $totalRow=Db::view('Book','book_id,book_name,words,author_name')
            ->view('Content','title,the_price,time','Content.book_id=Book.book_id and Content.num=Book.chapter')
            ->view('BookType','book_type','BookType.type_id=Book.type_id')
            ->view('BookStatistical','click_weeks','BookStatistical.book_id=Book.book_id')
            ->where($where)
            ->count();
        $totalPage =ceil($totalRow/$pageSize);
        

        $book=Db::view('Book','book_id,book_name,words,author_name')
              ->view('Content','title,the_price,time','Content.book_id=Book.book_id and Content.num=Book.chapter')
              ->view('BookType','book_type','BookType.type_id=Book.type_id')
              ->view('BookStatistical','click_weeks','BookStatistical.book_id=Book.book_id')
              ->where($where)
              ->order($order)
              ->limit($start,$pageSize)
              ->select();
        $list =[
            '0' =>$book,
            '1'  =>$totalPage
        ];
        return $list;
    }
    /*
     * 畅销榜
     *
     */

    public function echo_vote(){

        $vote=Db::view('Book','book_id,book_name,author_name,upload_img,book_brief')
            ->view('BookStatistical','click_total,vote_total,vipvote_total','BookStatistical.book_id=Book.book_id')
            ->view('BookType','book_type','BookType.type_id=Book.type_id')
            ->where(['is_show'=>1,'audit'=>1])
            ->order('money_weeks desc')
            ->limit(2)
            ->select();
       for ($i=0;$i<count($vote);$i++){
           $vote[$i]['count'] =count($this->getBookBuy($vote[$i]['book_id']));
           $vote[$i]['user'] =$this->getBookBuy($vote[$i]['book_id']);
       }



        return $vote;
    }

    /*
     * 获取订阅或打赏的人数
     */
    private function getBookBuy($bookid){

         if(!is_numeric($bookid)){
             $this->error('参数错误');
         }
         $sql="SELECT user_id,SUM(`money`) AS `money` FROM shudong_user_consumerecord WHERE book_id ={$bookid} AND type IN (1,4,5) GROUP BY user_id ORDER BY `money` DESC limit 0,5 ";
          $user=  Db::query($sql);
          foreach ($user as $k=>$v){
              $arr =Db::name('User')->where(['user_id'=>$v['user_id']])->field('portrait,sex')->find();
              if(strlen($arr['portrait'])<60) {

                  if ($arr['portrait'] == "user/portrait/portrait.jpg") {

                     $user[$k]['portrait'] = "http://images.shuddd.com/user/portrait/portrait".$arr['sex'].".png";

                  } else {
                     $user[$k]['portrait'] = "http://images.shuddd.com/".$arr['portrait'];
                  }
             }else{

                  $user[$k]['portrait'] =$arr['portrait'];
              }
          }

          return $user;
    }

}