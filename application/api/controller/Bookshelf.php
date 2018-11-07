<?php
namespace app\api\controller;
use think\Db;
use think\Controller;
class Bookshelf extends Controller{

    /* 书籍置顶*/
    public function ziDing(){

        $bookId =input('post.bookid');
        $userId =input('post.userId');
        $data =[
            'top'   =>1,
            'update_time'  =>date('Y-m-d H:i:s')
        ];
      $result=  Db::name('BookCollection')->where(['book_id'=>$bookId,'user_id'=>$userId])->update($data);
      if($result){

          return $msg=[
              'code'   =>200,
              'data'  =>'success'

          ];

      }else{
          return $msg=[
              'code'   =>201,
              'data'  =>'fail'

          ];

      }
    }

    /* 取消书籍置顶*/
    public function cancelDing(){

        $bookId =input('post.bookid');
        $userId =input('post.userId');
        $data =[
            'top'   =>0,
        ];
        $result=  Db::name('BookCollection')->where(['book_id'=>$bookId,'user_id'=>$userId])->update($data);
        if($result){

            return $msg=[
                'code'   =>200,
                'data'  =>'success'

            ];

        }else{
            return $msg=[
                'code'   =>201,
                'data'  =>'fail'

            ];

        }
    }

    /* 删除收藏*/
    public function delete(){

        $bookId =input('post.bookid');
        $userId =input('post.userId');

        $result=  Db::name('BookCollection')->where(['book_id'=>$bookId,'user_id'=>$userId])->delete();
        if($result){

            return $msg=[
                'code'   =>200,
                'data'  =>'success'

            ];

        }else{
            return $msg=[
                'code'   =>201,
                'data'  =>'fail'

            ];

        }
    }
    //自动订阅
    public function buy(){
        $bookId =input('post.bookid');
        $userId =input('post.userId');
        $where =[
            'book_id'  =>$bookId,
            'user_id'   =>$userId
        ];
       $result= Db::name('BookBuy')->where($where)->find();
       if(count($result)!=0){
          $res= Db::name('BookBuy')->where($where)->update(['is_type'=>1]);
          if($res){

              return $msg=[
                  'code'   =>200,
                  'data'  =>'success'

              ];
          }else{

              return $msg=[
                  'code'   =>201,
                  'data'  =>'fail'

              ];
          }

       }else{

           $data =[
               'book_id'  =>$bookId,
               'user_id'   =>$userId,
               'is_type'  =>1

           ];
          $res= Db::name('BookBuy')->insert($data);
           if($res){

               return $msg=[
                   'code'   =>200,
                   'data'  =>'success'

               ];
           }else{

               return $msg=[
                   'code'   =>201,
                   'data'  =>'fail'

               ];
           }
       }

    }

    //取消自动订阅
    public function cBuy(){

        $bookId =input('post.bookid');
        $userId =input('post.userId');
        $where =[
            'book_id'  =>$bookId,
            'user_id'   =>$userId
        ];
       $res= Db::name('BookBuy')->where($where)->update(['is_type'=>0]);
        if($res){

            return $msg=[
                'code'   =>200,
                'data'  =>'success'

            ];
        }else{

            return $msg=[
                'code'   =>201,
                'data'  =>'fail'

            ];
        }
    }
}