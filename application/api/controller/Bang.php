<?php
namespace app\api\controller;
use think\Controller;
use think\Db;
class Bang extends Controller{
    public function weeks(){
        $bang=input('post.bang');
        $bangs=input('post.bangs');
        $type=input('post.type');
        $current=input('post.current');
        $list=$this->echo_click_weeks($current,$bangs,$type);
        if(!$list){
            return 1;
        }else{
            return  $this->fetch('',[
                'weeks'   =>$list['weeks'],
                'a'     =>($current-1)*10,
                'current'  =>$current,
                'page'    =>$list['page'],
                'bang'   =>$bang,
                'bangs'  =>$bangs,
                'type'   =>$type
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
        $list=[
            'page'    =>$totalPage,
            'weeks'   =>$click_weeks
        ];
        return $list;
    }
    public function month(){
        $bang=input('post.bang');
        $bangs=input('post.bangs');
        $type=input('post.type');
        $current=input('post.current');

        $list=$this->echo_click_month($current,$bangs,$type);
        if(!$list){
            return 1;
        }else{
          return  $this->fetch('',[
                'weeks'   =>$list['weeks'],
                'a'     =>($current-1)*10,
                'current'  =>$current,
                'page'    =>$list['page'],
              'bang'     =>$bang,
              'bangs'    =>$bangs,
              'type'     =>$type
            ]);
        }
    }

    /*
    * 点击榜月榜
    */
    public function echo_click_month($current,$bang,$type){
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
        $list=[
            'page'    =>$totalPage,
            'weeks'   =>$click_weeks
        ];
        return $list;
    }

    public function total(){
        $bang=input('post.bang');
        $bangs=input('post.bangs');
        $type=input('post.type');
        $current=input('post.current');

        $list=$this->echo_click_total($current,$bangs,$type);
        if(!$list){
            return 1;
        }else{
            return  $this->fetch('',[
                'weeks'   =>$list['weeks'],
                'a'     =>($current-1)*10,
                'current'  =>$current,
                'page'    =>$list['page'],
                'bang'    =>$bang,
                'bangs'   =>$bangs,
                'type'    =>$type
            ]);
        }

    }

    /*
   * 点击榜总榜
   */
    public function echo_click_total($current,$bang,$type){
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
        $list=[
            'page'    =>$totalPage,
            'weeks'   =>$click_weeks
        ];
        return $list;
    }
    /*
      * 金主榜周榜
      */
    public function jinzhu_weeks(){
        $today=date('Y-m-d H:i:s');
        $endToday=date('Y-m-d 00:00:00', (time() - ((date('w') == 0 ? 7 : date('w')) - 1) * 24 * 3600));
        $sql="SELECT a.user_id, b.pen_name,portrait,b.sex, SUM(a.money) AS money from shudong_user_consumerecord a INNER  JOIN shudong_user b ON a.user_id=b.user_id WHERE a.date BETWEEN ? AND ?  GROUP BY a.user_id  ORDER BY money DESC LIMIT 0,20";
        $info=Db::query($sql,[$endToday, $today]);
        for ($i=0;$i<20;$i++){
            if(strlen($info[$i]['portrait'])<60){
                if ($info[$i]['portrait']=="user/portrait/portrait.jpg"){

                    $info[$i]['portrait']="http://images.shuddd.com/user/portrait/portrait".$info[$i]['sex'].".png";

                }else{
                    $info[$i]['portrait']="http://images.shuddd.com/".$info[$i]['portrait'];
                }
            }
        }
        if(!$info){
            return 1;
        }else{

            return $this->fetch('',[
                'jinzhu'   =>$info,
                'b'         =>4

            ]);
        }

    }
    /*
     * 金主榜月榜
     */
    public function jinzhu_month(){
        $today=date('Y-m-d H:i:s');
        $endToday=date("Y-m-01 00:00:00");
        $sql="SELECT a.user_id, b.pen_name,portrait,b.sex, SUM(a.money) AS money from shudong_user_consumerecord a INNER  JOIN shudong_user b ON a.user_id=b.user_id WHERE a.date BETWEEN ? AND ?  GROUP BY a.user_id  ORDER BY money DESC LIMIT 0,20";
        $info=Db::query($sql,[$endToday, $today]);
        for ($i=0;$i<20;$i++){
            if(strlen($info[$i]['portrait'])<60){
                if ($info[$i]['portrait']=="user/portrait/portrait.jpg"){

                    $info[$i]['portrait']="http://images.shuddd.com/user/portrait/portrait".$info[$i]['sex'].".png";

                }else{
                    $info[$i]['portrait']="http://images.shuddd.com/".$info[$i]['portrait'];
                }
            }
        }
        if(!$info){
            return 1;
        }else{

            return $this->fetch('',[
                'jinzhu'   =>$info,
                'b'         =>4

            ]);
        }

    }
    /*
     * 金主榜总榜
     */
    public function jinzhu_total(){

        $sql="SELECT a.user_id, b.pen_name,portrait,b.sex, SUM(a.money) AS money from shudong_user_consumerecord a INNER  JOIN shudong_user b ON a.user_id=b.user_id  GROUP BY a.user_id  ORDER BY money DESC LIMIT 0,20";
        $info=Db::query($sql);
        for ($i=0;$i<20;$i++){
            if(strlen($info[$i]['portrait'])<60){
                if ($info[$i]['portrait']=="user/portrait/portrait.jpg"){

                    $info[$i]['portrait']="http://images.shuddd.com/user/portrait/portrait".$info[$i]['sex'].".png";

                }else{
                    $info[$i]['portrait']="http://images.shuddd.com/".$info[$i]['portrait'];
                }
            }
        }
        if(!$info){
            return 1;
        }else{

            return $this->fetch('',[
                'jinzhu'   =>$info,
                'b'         =>4

            ]);
        }

    }
}