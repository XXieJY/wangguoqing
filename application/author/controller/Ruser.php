<?php
namespace app\author\controller;
use think\Controller;
use think\Db;
use think\Request;
class Ruser extends Base{
    public function index(){

        $author =$this->account;
        $book= get_book($author['author_id']);
      if(request()->isPost()){
          $res=input('post.');
          $messges= $this->getBookMessageOne($res['bookName'],$res['message']);
          $page=$messges->toArray();
          return $this->fetch('',[
              'book'   =>$book,
              'message' =>$messges,
              'a'      =>($page['current_page']-1)*10,
              'bookName' =>$res['bookName'],

          ]);

      }elseif(request()->isGet()){
             $res=input('get.');

             if(array_key_exists('bookName',$res)){

                 $messges= $this->getBookMessageOne($res['bookName'],$res['message']);
                 $page=$messges->toArray();
             }else{

                 $messges= $this->bookMessage($book[0]['book_name']);

                 $page=$messges->toArray();
             }
              return $this->fetch('',[
                  'book'   =>$book,
                  'message' =>$messges,
                  'a'      =>($page['current_page']-1)*10,
                  'bookName' =>$book[0]['book_name'],

              ]);
      }

    }

    /*
     * 书评
     */
    private function bookMessage($bookName){

        $book =Db::name('Book')->where(['book_name'=>trim($bookName)])->find();

            $wheres=[
                'book_id' =>$book['book_id'],
                'f_id' =>0,
            ];
        $messges= Db::view('BookMessage','z_id,book_id,thumb,num,content,top,time,update_time')
            ->view('User','pen_name','User.user_id=BookMessage.user_id')
            ->where($wheres)
            ->order('BookMessage.top desc,BookMessage.update_time desc')
            ->paginate(10, false, [
                'query' => Request::instance()->param(),//不丢失已存在的url参数
            ]);

        return $messges;
    }
    /*
     * 查询某本书的书评
     */
    private function getBookMessageOne($bookName,$msg=""){

        $book =Db::name('Book')->where(['book_name'=>trim($bookName)])->find();

        if($msg!=""){
            $user=  Db::name('User')->where(['pen_name'=>$msg])->find();

            if($user){
                $wheres=[
                    'book_id' =>$book['book_id'],
                    'f_id' =>0,
                    'User.user_id' =>$user['user_id']
                ];
            }else{
                $wheres=[
                    'book_id' =>$book['book_id'],
                    'f_id' =>0,
                    'content' =>['like',"%{$msg}%"]
                ];
            }
        }else{
            $wheres=[
                'book_id' =>$book['book_id'],
                'f_id' =>0,
            ];
        }
        $messges= Db::view('BookMessage','z_id,book_id,thumb,num,content,top,time,update_time')
            ->view('User','pen_name','User.user_id=BookMessage.user_id')
            ->where($wheres)
            ->order('BookMessage.top desc,BookMessage.update_time desc')
            ->paginate(10, false, [
                'query' => Request::instance()->param(),//不丢失已存在的url参数
            ]);

        return $messges;
    }

    /*
     * 评论置顶
     */
  public function topMessage(){
     $zid =input('post.zid');
     if(!is_numeric($zid)){
         $this->error('参数错误');
     }

     $re1=Db::name('BookMessage')->where(['z_id'=>$zid])->find();
     if($re1['top']==1){
         //将该条评论取消置顶
         $data=[
             'top' =>0,
             'update_time' =>date('Y-m-d H:i:s')
         ];
         $result= Db::name('BookMessage')->where(['z_id'=>$zid])->update($data);
         if($result){
             $res=[
                 'code'  =>300,
                 'msg'   =>'success'
             ];
             return $res;

         }else{
             $res=[
                 'code'  =>301,
                 'msg'   =>'fail'
             ];
             return $res;

         }

     }else{
         //将该条评论置顶
         $data=[
             'top' =>1,
             'update_time' =>date('Y-m-d H:i:s')
         ];
         $result= Db::name('BookMessage')->where(['z_id'=>$zid])->update($data);
         if($result){
             $res=[
                 'code'  =>200,
                 'msg'   =>'success'
             ];
             return $res;

         }else{
             $res=[
                 'code'  =>201,
                 'msg'   =>'fail'
             ];
             return $res;

         }

     }

}

/*
 * 删除评论
 *
 */
public function delete(){
    $zid=input('post.zid');
    if(!is_numeric($zid)){

        $this->error('参数错误');
    }
   $result= Db::name('BookMessage')->where(['z_id'=>$zid])->delete();
    if($result){
        $data =[
            'code' =>200,
            'msg'   =>'success'
        ];
        return $data;
    }else{
        $data =[
            'code' =>201,
            'msg'   =>'fail'
        ];
        return $data;

    }

}

/*
 * 批量删除
 */
public function piliang(){
    if(!$this->request->isPost()){
        $this->error('系统错误');
    }

    $value=input('post.checkbox/a');

    foreach ($value as $k=>$v){

        Db::name('BookMessage')->where(['z_id'=>$v])->delete();

    }

    $this->success('书评删除成功');


}

   public function user(){

       $author =$this->account;
       $book=  get_book($author['author_id']);
       if(request()->isPost()){
           $res =input('post.');
           if($res['penName']==""){
               $userInfo= $this->getOneBookUserInfo($res['bookName']);
           }else{
               $userInfo= $this->getOneBookUserInfo2($res['bookName'],$res['penName']);
           }

           $page=$this->page1($res['bookName'],$res['penName']);
           return $this->fetch('',[
               'book'   =>$book,
               'userInfo' =>$userInfo['data'],
               'page'   =>$page,
               'a'      =>($userInfo['current_page']-1)*10,
               'bookName' =>$res['bookName'],
               'auth'     =>0
           ]);

       }elseif (request()->isGet()){

           $res =input('get.');

           if($res){
                $bookName =$res['bookName']==""?$book[0]['book_name']:$res['bookName'];
               if($res['penName']==""){
                   $userInfo= $this->getOneBookUserInfo($bookName,$res['type']);
               }else{
                   $userInfo= $this->getOneBookUserInfo2($bookName,$res['penName']);
               }

               $page=$this->page1($bookName,$res['penName']);

           }else{
               $bookName =$book[0]['book_name'];
               $userInfo= $this->getOneBookUserInfo($bookName,$res['type']);
               $page=$this->page($book[0]['book_name']);

           }
           return $this->fetch('',[
               'book'   =>$book,
               'userInfo' =>$userInfo['data'],
               'page'   =>$page,
               'a'      =>($userInfo['current_page']-1)*10,
               'bookName' =>$bookName,
               'auth'     =>0
           ]);


       }

   }
   /*
    * 读者信息
    */
//   public function getUserInfo($authorId){
//
//       if(!is_numeric($authorId)){
//           $this->error('参数错误');
//       }
//       $ids=[];
//       //获取作者所有的已审核上线的书籍
//       $where['author_id']=$authorId;
//       $where['is_show'] =1;
//       $where['audit']  =1;
//       $books= Db::name('Book')->where($where)->field('book_id,book_name')->select();
//       for ($i=0;$i<count($books);$i++){
//           $ids[]=$books[$i]['book_id'];
//       }
//       $id=implode(',',$ids);
//       $where=[
//           'book_id' =>['in',$id]
//       ];
//      $user= Db::name('UserConsumerecord')->where($where)->field('user_id,book_id,date')->group('user_id')
//               ->paginate(10, false, [
//              'query' => Request::instance()->param(),//不丢失已存在的url参数
//             ]);
//
//      $user=$user->toArray();
//
//      foreach ($user['data'] as $k=>$v){
//          $v['name']=Db::name('User')->where(['user_id'=>$v['user_id']])->field('pen_name')->find();
//          $user['data'][$k]['pen_name']=$v['name']['pen_name'];
//      }
//
//
//
//     // $sql="SELECT a.user_id,a.book_id,b.pen_name,a.date FROM shudong_user_consumerecord AS a INNER JOIN shudong_user AS b ON a.user_id=b.user_id WHERE book_id IN({$id}) GROUP BY user_id";
//
//    // $user= Db::query($sql);
//     foreach ($user['data'] as $k=>$v){
//         $user['data'][$k]['vote']=$this->getVoteByUserId($user['data'][$k]['user_id'],$id);
//         $user['data'][$k]['vipvote'] =$this->getVipvoteByUserId($user['data'][$k]['user_id'],$id);
//         $user['data'][$k]['value'] =$this->getFansValueByUserId($user['data'][$k]['user_id'],$id);
//     }
//
//     $list = $values=[];
//     $count=count($user['data']);
//     $list['total']=$user['total'];
//     $list['per_page'] =$user['per_page'];
//     $list['current_page'] =$user['current_page'];
//     $list['last_page'] =$user['last_page'];
//     for ($i=0;$i<$count;$i++){
//         if($user['data'][$i]['vote']==0 && $user['data'][$i]['vipvote']==0 && $user['data'][$i]['value']==0){
//             continue;
//         }
//       $list['data'][]=$user['data'][$i];
//     }
//      if(is_array($list['data'])){
//
//          foreach ($list['data'] as $vo) {
//              $values[] = $vo['value'];
//          }
//          //  print_r($values);exit();
//          //按照age升序排列
//          array_multisort($values, SORT_DESC, $list['data']);
//      }
//
//      // print_r($list);exit();
//       return $list;
//
//   }

   /*
    * 查询某本书的读者信息
    */
   private function getOneBookUserInfo($bookName,$type=""){

       $book =Db::name('Book')->where(['book_name'=>trim($bookName)])->find();

       $where=[
           'book_id' =>$book['book_id']
       ];
       $user= Db::name('UserConsumerecord')->where($where)->field('user_id,book_id,date')->group('user_id')
               ->paginate(10, false, [
               'query' => Request::instance()->param(),//不丢失已存在的url参数
              ]);

       $user=$user->toArray();

       foreach ($user['data'] as $k=>$v){
           $v['name']=Db::name('User')->where(['user_id'=>$v['user_id']])->field('pen_name')->find();
           $user['data'][$k]['pen_name']=$v['name']['pen_name'];
       }

       // $sql="SELECT a.user_id,a.book_id,b.pen_name,a.date FROM shudong_user_consumerecord AS a INNER JOIN shudong_user AS b ON a.user_id=b.user_id WHERE book_id IN({$id}) GROUP BY user_id";

       // $user= Db::query($sql);
       foreach ($user['data'] as $k=>$v){
           $user['data'][$k]['vote']=$this->getVoteByUserId($user['data'][$k]['user_id'],$book['book_id']);
           $user['data'][$k]['vipvote'] =$this->getVipvoteByUserId($user['data'][$k]['user_id'],$book['book_id']);
           $user['data'][$k]['value'] =$this->getFansValueByUserId($user['data'][$k]['user_id'],$book['book_id']);
       }

       $list = $values=[];
       $count=count($user['data']);
       $list['total']=$user['total'];
       $list['per_page'] =$user['per_page'];
       $list['current_page'] =$user['current_page'];
       $list['last_page'] =$user['last_page'];
       for ($i=0;$i<$count;$i++){
           if($user['data'][$i]['vote']==0 && $user['data'][$i]['vipvote']==0 && $user['data'][$i]['value']==0){
               continue;
           }
           $list['data'][]=$user['data'][$i];
       }
       if($type==1){
           if(count(($list['data']))>0){
               foreach ($list['data'] as $vo) {
                   $values[] = $vo['vote'];
               }
               //  print_r($values);exit();
               //按照value升序排列
               array_multisort($values, SORT_DESC, $list['data']);

           }
       }elseif ($type==2){
           if(count(($list['data']))>0){
               foreach ($list['data'] as $vo) {
                   $values[] = $vo['vote'];
               }
               //  print_r($values);exit();
               //按照value升序排列
               array_multisort($values, SORT_ASC, $list['data']);

           }
       }elseif ($type==3){
           if(count(($list['data']))>0){
               foreach ($list['data'] as $vo) {
                   $values[] = $vo['vipvote'];
               }
               //  print_r($values);exit();
               //按照value升序排列
               array_multisort($values, SORT_DESC, $list['data']);

           }
       }elseif ($type==4){
           if(count(($list['data']))>0){
               foreach ($list['data'] as $vo) {
                   $values[] = $vo['vipvote'];
               }
               //  print_r($values);exit();
               //按照value升序排列
               array_multisort($values, SORT_ASC, $list['data']);

           }
       }elseif ($type==6){
           if(count(($list['data']))>0){
               foreach ($list['data'] as $vo) {
                   $values[] = $vo['value'];
               }
               //  print_r($values);exit();
               //按照value升序排列
               array_multisort($values, SORT_ASC, $list['data']);

           }
       }else{
           if(count(($list['data']))>0){
               foreach ($list['data'] as $vo) {
                   $values[] = $vo['value'];
               }
               //  print_r($values);exit();
               //按照value升序排列
               array_multisort($values, SORT_DESC, $list['data']);

           }

       }


//print_r(json($list));exit();
       return $list;

   }

    /*
        * 查询某本书的读者信息
        */
    private function getOneBookUserInfo2($bookName,$penName){

        $book =Db::name('Book')->where(['book_name'=>trim($bookName)])->find();
        $user=Db::name('User')->where(['pen_name'=>trim($penName)])->find();

        $where=[
            'book_id' =>$book['book_id'],
            'user_id' =>$user['user_id']
        ];
        $user= Db::name('UserConsumerecord')->where($where)->field('user_id,book_id,date')->group('user_id')
            ->paginate(10, false, [
                'query' => Request::instance()->param(),//不丢失已存在的url参数
            ]);

        $user=$user->toArray();
            foreach ($user['data'] as $k=>$v){
               $v['name'] =Db::name('User')->where(['user_id'=>$v['user_id']])->field('pen_name')->find();
               $user['data'][$k]['pen_name']=$v['name']['pen_name'];

            }
        // $sql="SELECT a.user_id,a.book_id,b.pen_name,a.date FROM shudong_user_consumerecord AS a INNER JOIN shudong_user AS b ON a.user_id=b.user_id WHERE book_id IN({$id}) GROUP BY user_id";
        // $user= Db::query($sql);
        foreach ($user['data'] as $k=>$v){
            $user['data'][$k]['vote']=$this->getVoteByUserId($user['data'][$k]['user_id'],$book['book_id']);
            $user['data'][$k]['vipvote'] =$this->getVipvoteByUserId($user['data'][$k]['user_id'],$book['book_id']);
            $user['data'][$k]['value'] =$this->getFansValueByUserId($user['data'][$k]['user_id'],$book['book_id']);
        }

        $list = $values=[];
        $count=count($user['data']);
        $list['total']=$user['total'];
        $list['per_page'] =$user['per_page'];
        $list['current_page'] =$user['current_page'];
        $list['last_page'] =$user['last_page'];
        for ($i=0;$i<$count;$i++){
            if($user['data'][$i]['vote']==0 && $user['data'][$i]['vipvote']==0 && $user['data'][$i]['value']==0){
                continue;
            }
            $list['data'][]=$user['data'][$i];
        }
        if(is_array($list['data'])){
            foreach ($list['data'] as $vo) {
                $values[] = $vo['value'];
            }
            //  print_r($values);exit();
            //按照age升序排列
            array_multisort($values, SORT_DESC, $list['data']);

        }

        return $list;

    }


   /*
    * 分页
    */
   private function page($bookName){

       $book =Db::name('Book')->where(['book_name'=>['like',"%$bookName%"]])->find();

       $where=[
           'book_id' =>$book['book_id']
       ];
       $user= Db::name('UserConsumerecord')->where($where)->field('user_id,book_id,date')->group('user_id')
           ->paginate(10, false, [
               'query' => Request::instance()->param(),//不丢失已存在的url参数
           ]);

       return $user;
   }

    private function page1($bookName,$penName){

       if($penName==""){

           $book =Db::name('Book')->where(['book_name'=>['like',"%$bookName%"]])->find();

           $where=[
               'book_id' =>$book['book_id']
           ];
           $user= Db::name('UserConsumerecord')->where($where)->field('user_id,book_id,date')->group('user_id')
               ->paginate(10, false, [
                   'query' => Request::instance()->param(),//不丢失已存在的url参数
               ]);
       }else{

           $book =Db::name('Book')->where(['book_name'=>['like',"%$bookName%"]])->find();
           $user=Db::name('User')->where(['pen_name'=>trim($penName)])->find();

           $where=[
               'book_id' =>$book['book_id'],
               'user_id' =>$user['user_id']
           ];
           $user= Db::name('UserConsumerecord')->where($where)->field('user_id,book_id,date')->group('user_id')
               ->paginate(10, false, [
                   'query' => Request::instance()->param(),//不丢失已存在的url参数
               ]);
       }

        return $user;
    }


   /*
    * 推荐票
    */
   private function getVoteByUserId($userId,$id){

       $where=[
           'book_id'  =>['in',$id],
           'user_id'  =>$userId,
           'type'     =>2
       ];
       $vote =Db::name('UserConsumerecord')->where($where)->sum('count');
       return $vote;
   }
   /*
    * 月票
    */
   private function getVipvoteByUserId($userId,$id){
       $where=[
           'book_id'  =>['in',$id],
           'user_id'  =>$userId,
           'type'     =>3
       ];
       $vipvote =Db::name('UserConsumerecord')->where($where)->sum('count');
       return $vipvote;
   }
   /*
    * 粉丝值
    */
   private function getFansValueByUserId($userId,$id){
       $where =[
           'book_id'  =>['in',$id],
           'user_id'  =>$userId

       ];

      $value= Db::name('BookFans')->where($where)->sum('fan_value');
       return $value;

   }
}