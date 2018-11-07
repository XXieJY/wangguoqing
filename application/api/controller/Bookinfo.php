<?php
namespace app\api\controller;
use think\Controller;
use think\Db;
class Bookinfo extends Controller{

    public function collect(){
        Db::startTrans();//开启事务
        $bookid =input('post.bookid');
        if(!is_numeric($bookid)){
            $this->error('参数错误');
        }
        $useArr =cookie('shudong_user');
        if(!is_array($useArr)){
            $this->error('请登录');
        }
        //判断是否已经将该书加入书架
        $where['book_id'] =$bookid;
        $where['user_id'] =$useArr['user_id'];
        $result= Db::name('BookCollection')->where($where)->find();
        if(!$result){
            $data['book_id']  =$bookid;
            $data['user_id']  =$useArr['user_id'];
            $data['time']     =date('Y-m-d H:i:s');
            $data['update_time'] =date('Y-m-d H:i:s');
           $ok= Db::name('BookCollection')->insert($data);
           if($ok){
               //增加该书的收藏量
               cache('book_info'.$bookid,null);
               try{
                   $st['collection_day'] =array('exp',"collection_day+1");
                   $st['collection_weeks'] =array('exp',"collection_weeks+1");
                   $st['collection_month'] =array('exp',"collection_month+1");
                   $st['collection_total'] =array('exp',"collection_total+1");
                 $re1=  Db::name('BookStatistical')->where(['book_id'=>$bookid])->update($st);
                 $tong= Db::name('BookTongji')->where(['book_id'=>$bookid,'time'=>date('Y-m-d')])->find();
                 if(is_array($tong)){
                     $tongji['collection']=array('exp',"collection+1");
                   $re2=  Db::name('BookTongji')->where(['book_id'=>$bookid,'time'=>date('Y-m-d')])->update($tongji);
                   if($re1 && $re2){
                       Db::commit();//提交事务
                   }

                 }else{
                     $tongjis['book_id'] =$bookid;
                     $tongjis['collection'] =1;
                     $tongjis['time']    =date('Y-m-d');
                    $re3= Db::name('BookTongji')->insert($tongjis);
                    if($re1 && $re3){
                        Db::commit();//提交事务
                    }
                 }


               }catch (\Exception $exception){

                   Db::rollback();//事务回滚
               }
               $res=[
                   'code'  =>200,
                   'msg'   =>'success',
               ];

               return $res;
           }else{
               $res=[
                   'code'  =>201,
                   'msg'   =>'fail',
               ];
               return $res;
           }

        }else{

            $res=[
                'code'  =>202,
                'msg'   =>'fail',
            ];
            return $res;

        }

    }

    public function remove(){
        Db::startTrans();//开启事务
        $bookid =input('post.bookid');
        if(!is_numeric($bookid)){
            $this->error('参数错误');
        }
        //删除书架记录
        $useArr =cookie('shudong_user');
        if(!is_array($useArr)){
            $this->error('请登录');
        }
        $where['book_id']=$bookid;
        $where['user_id'] =$useArr['user_id'];
        $result= Db::name('BookCollection')->where($where)->delete();
        if($result){
            cache('book_info'.$bookid,null);
            try{
                $st['collection_day'] =array('exp',"collection_day-1");
                $st['collection_weeks'] =array('exp',"collection_weeks-1");
                $st['collection_month'] =array('exp',"collection_month-1");
                $st['collection_total'] =array('exp',"collection_total-1");
                $re1=  Db::name('BookStatistical')->where(['book_id'=>$bookid])->update($st);
                $tong= Db::name('BookTongji')->where(['book_id'=>$bookid,'time'=>date('Y-m-d')])->find();
                if(is_array($tong)){
                    $tongji['collection']=array('exp',"collection-1");
                    $re2=  Db::name('BookTongji')->where(['book_id'=>$bookid,'time'=>date('Y-m-d')])->update($tongji);
                    if($re1 && $re2){
                        Db::commit();//提交事务
                    }

                }else {
                    $tongjis['book_id'] = $bookid;
                    $tongjis['collection'] = -1;
                    $tongjis['time'] = date('Y-m-d');
                    $re3 = Db::name('BookTongji')->insert($tongjis);
                    if ($re1 && $re3) {
                        Db::commit();//提交事务
                    }
                }
            }catch (\Exception $exception){
                Db::rollback();//事务回滚
            }
            $res=[
                'code' =>200,
                'msg'  =>'success',
            ];
            return $res;
        }else{
            $res=[
                'code' =>201,
                'msg'  =>'fail'

            ];
        }

    }

    //发表评论
    public function replay(){

         $bookid =input('post.bookid');
         $content =input('post.content');
         if(!is_numeric($bookid)){
             $this->error('参数错误');
         }
         $useArr =cookie('shudong_user');
         if(!is_array($useArr)){
             $this->error('请登录');
         }
         $data =[
             'book_id'  =>$bookid,
             'user_id'  =>$useArr['user_id'],
             'content'  =>trim($content),
             'time'     =>date('Y-m-d H:i:s'),
             'update_time'  =>date('Y-m-d H:i:s')
         ];
       $result=  Db::name('BookMessage')->insert($data);
       if($result){
          $code=[
              'code' =>200,
              'msg'  =>'success'
          ];
          return $code;

       }else{
           $code=[
               'code' =>201,
               'msg'  =>'fail'
           ];
           return $code;

       }

    }

    //评论点赞
    public function thumb(){
        $zid =input('post.zid');
        Db::startTrans();//开启事务
        if(!is_numeric($zid)){
            $this->error('参数错误');
        }

        $useArr =cookie('shudong_user');
        if(!is_array($useArr)){
            $this->error('请登录');
        }
        $where=[
           'message_id'  =>$zid,
            'user_id'    =>$useArr['user_id']
        ];
       $result= Db::name('MessageThumb')->where($where)->find();
       if(!$result){

           try{
               $data=[
                   'message_id' =>$zid,
                   'user_id'    =>$useArr['user_id'],
                   'status'     =>1,
                   'time'       =>date('Y-m-d H:i:s')
               ];
               $re1=  Db::name('MessageThumb')->insert($data);

               $re2= Db::name('BookMessage')->where(['z_id'=>$zid])->update(['thumb'=>['exp',"thumb+1"]]);
               $thumb= Db::name('BookMessage')->where(['z_id'=>$zid])->field('thumb,book_id')->find();
               $html="<span>取消点赞</span>(<span class=\"assit\">".$thumb['thumb']."</span>)";

               if($re1 && $re2){
                   Db::commit();//提交事务
                   $code =[
                       'code'  =>200,
                       'msg'   =>'success',
                       'html'  =>$html
                   ];
                   return $code;
               }else{
                   Db::rollback();//事务回滚
                   $code =[
                       'code'  =>201,
                       'msg'   =>'fail',

                   ];
                   return $code;
               }

           }catch (\Exception $exception){

               Db::rollback();//事务回滚
           }

       }else{
           if($result['status']==1){
               //取消点赞
               try{
                   $re3=  Db::name('MessageThumb')->where($where)->update(['status'=>0]);
                   $re4 =Db::name('BookMessage')->where(['z_id'=>$zid])->update(['thumb'=>['exp',"thumb-1"]]);
                   $thumb= Db::name('BookMessage')->where(['z_id'=>$zid])->field('thumb,book_id')->find();
                   $html="<span>赞</span>(<span class=\"assit\">".$thumb['thumb']."</span>)";
                   if($re3 && $re4){
                       Db::commit();//提交事务
                       $code =[
                           'code'  =>300,
                           'msg'   =>'success',
                           'html'  =>$html
                       ];
                       return $code;
                   }else{
                       Db::rollback();//事务回滚
                       $code =[
                           'code'  =>301,
                           'msg'   =>'fail'
                       ];
                       return $code;
                   }


               }catch (\Exception $exception){

                   Db::rollback();//事务回滚
               }

           }else{
               //点赞
               try{
                   $re5=  Db::name('MessageThumb')->where($where)->update(['status'=>1]);
                   $re6 =Db::name('BookMessage')->where(['z_id'=>$zid])->update(['thumb'=>['exp',"thumb+1"]]);
                   $thumb= Db::name('BookMessage')->where(['z_id'=>$zid])->field('thumb,book_id')->find();
                   $html="<span>取消点赞</span>(<span class=\"assit\">".$thumb['thumb']."</span>)";

                   if($re5 && $re6){
                       Db::commit();//提交事务
                       $code =[
                           'code'  =>200,
                           'msg'   =>'success',
                           'html'  =>$html
                       ];
                       return $code;
                   }else{
                       Db::rollback();//事务回滚
                       $code =[
                           'code'  =>201,
                           'msg'   =>'fail'
                       ];
                       return $code;
                   }


               }catch (\Exception $exception){

                   Db::rollback();//事务回滚
               }
           }
       }

    }
  /*
   * 回复评论
   */

  public function replayMsg(){
      Db::startTrans();//开启事务
      try{
          $zid =input('post.zid');
          $content =input('post.content');
          if(!is_numeric($zid)){
              $this->error('参数错误');
          }
          $useArr =cookie('shudong_user');
          if(!is_array($useArr)){
              $this->error('请登录');
          }
          $data['f_id'] =$zid;
          $data['book_id'] =0;
          $data['user_id']  =$useArr['user_id'];
          $data['content']  =$content;
          $data['time']    =date('Y-m-d H:i:s');
          $data['update_time'] =date('Y-m-d H:i:s');
          $re1=  Db::name('BookMessage')->insert($data);
          if($re1){
             $re2= Db::name('BookMessage')->where(['z_id'=>$zid])->update(['num'=>['exp',"num+1"]]);
              if($re2){
                  Db::commit();//提交事务
                  $msg=[
                      'code'  =>200,
                      'msg'   =>'success'
                  ];
                  return $msg;
              }
          } else{
              Db::rollback();//事务回滚
              $msg=[
                  'code'  =>201,
                  'msg'   =>'fail'
              ];
              return $msg;

          }


      }catch (\Exception $exception){
          Db::rollback();//事务回滚
      }
  }

  //打赏
    public function dashang(){

      Db::startTrans();//开启事务
        try{
            $bookid =input('post.bookid');
            $userid =input('post.userid');
            $dobing =input('post.dobing');
            //增加用户消费记录信息
            $data1=[
                'user_id'  =>$userid,
                'book_id'  =>$bookid,
                'type'     =>4,
                'count'    =>$dobing,
                'money'    =>$dobing,
                'dosomething'  =>"打赏了".$dobing."咚币",
                'date'    =>date('Y-m-d H:i:s')
            ];
            $re1= Db::name('UserConsumerecord')->insert($data1);
            //增加粉丝值
            $ok= Db::name('BookFans')->where(['book_id'=>$bookid,'user_id'=>$userid])->find();
            if($ok){
                $re4=  Db::name('BookFans')->where(['book_id'=>$bookid,'user_id'=>$userid])->update(['fan_value'=>['exp',"fan_value+$dobing"]]);

            }else{
                $data4=[
                    'book_id'  =>$bookid,
                    'user_id' =>$userid,
                    'fan_value'  =>$dobing,
                    'time'      =>date('Y-m-d H:i:s')
                ];
                $re4= Db::name('BookFans')->insert($data4);
            }
            if($dobing>=2000){
                 $count =floor($dobing/2000);
                $data2=[
                    'user_id'  =>$userid,
                    'book_id'  =>$bookid,
                    'type'     =>3,
                    'count'    =>$count,
                    'money'    =>0,
                    'dosomething'  =>"投了".$count."张月票",
                    'date'    =>date('Y-m-d H:i:s')
                ];
                $re2= Db::name('UserConsumerecord')->insert($data2);

                $data3=[
                    'vipvote'  =>['exp',"vipvote+$count"],
                    'reward'   =>['exp',"reward+$dobing"],
                    'money'    =>['exp',"money+$dobing"],
                ];
                $re3= Db::name('BookTongji')->where(['book_id'=>$bookid,'time'=>date('Y-m-d')])->update($data3);
                $data6 =[
                    'user_id'  =>$userid,
                    'type'    =>3,
                    'count'   =>2,
                    'operate'  =>-1,
                    'thing'    =>'投了'.$count.'张月票',
                    'platform'  =>'pc',
                    'time'     =>date('Y-m-d H:i:s')
                ];
                $re7=Db::name('UserAccountDetail')->insert($data6);
                $data8 =[
                    'vipvote_day'  =>['exp'=>"vipvote_day+$count"],
                    'vipvote_weeks'  =>['exp'=>"vipvote_weeks+$count"],
                    'vipvote_month'  =>['exp'=>"vipvote_month+$count"],
                    'vipvote_total'  =>['exp'=>"vipvote_total+$count"],

                ];
                Db::name('BookStatistical')->where(['book_id'=>$bookid])->update($data8);

            }else{

                $data3=[
                    'reward'   =>['exp',"reward+$dobing"],
                    'money'    =>['exp',"money+$dobing"],
                ];
                $re3= Db::name('BookTongji')->where(['book_id'=>$bookid,'time'=>date('Y-m-d')])->update($data3);

            }
            //扣除用户的咚币
            $re5 =Db::name('User')->where(['user_id'=>$userid])->update(['alance'=>['exp',"alance-$dobing"]]);
            //用户的详细消费记录
            $data5 =[
                'user_id'  =>$userid,
                'type'    =>1,
                'count'   =>$dobing,
                'operate'  =>-1,
                'thing'    =>'打赏了'.$dobing.'咚币',
                'platform'  =>'pc',
                'time'     =>date('Y-m-d H:i:s')
            ];
            $re6=Db::name('UserAccountDetail')->insert($data5);
            //统计用户的打赏情况
            $data7 =[
                'exceptional_day'  =>['exp',"exceptional_day+$dobing"],
                'exceptional_weeks'  =>['exp',"exceptional_weeks+$dobing"],
                'exceptional_month'  =>['exp',"exceptional_month+$dobing"],
                'exceptional_total'  =>['exp',"exceptional_total+$dobing"],
                'money_day'  =>['exp',"money_day+$dobing"],
                'money_weeks'  =>['exp',"money_weeks+$dobing"],
                'money_month'  =>['exp',"money_month+$dobing"],
                'money_total'  =>['exp',"money_total+$dobing"],
            ];
            Db::name('BookStatistical')->where(['book_id'=>$bookid])->update($data7);
            Db::commit();//事务提交
            return 200;

        }catch (\Exception $exception){

            Db::rollback();
            return 201;
        }

    }
  //推荐票
    public function tuijian(){
          Db::startTrans();//开启事务
          try{
              $bookid =input('post.bookid');
              $userid =input('post.userid');
              $vote =input('post.vote');
              if($vote==0){
                  return 201;
              }
              //添加用户推荐票消费记录
              $data1=[
                  'user_id'  =>$userid,
                  'book_id'  =>$bookid,
                  'type'    =>2,
                  'count'    =>$vote,
                  'dosomething'  =>'投了'.$vote.'张推荐票',
                  'date'   =>date('Y-m-d H:i:s')

              ];
             $re1= Db::name('UserConsumerecord')->insert($data1);
             //扣除用户的推荐票
              $re2 =Db::name('User')->where(['user_id'=>$userid])->update(['vote'=>['exp',"vote-$vote"]]);
             //添加用户的推荐票消费记录
              $data2=[
                  'user_id'   =>$userid,
                  'type'      =>4,
                  'count'     =>$vote,
                  'operate'   =>-1,
                  'thing'     =>'投了'.$vote.'张推荐票',
                  'platform'  =>'pc',
                  'time'       =>date('Y-m-d H:i:s')

              ];
              $re3 =Db::name('UserAccountDetail')->insert($data2);
              //增加统计记录
              $re4= Db::name('BookTongji')->where(['book_id'=>$bookid,'time'=>date('Y-m-d')])->update(['vote'=>['exp',"vote+$vote"]]);
             $data3=[
                 'vote_day'   =>['exp',"vote_day+$vote"],
                 'vote_weeks'   =>['exp',"vote_weeks+$vote"],
                 'vote_month'   =>['exp',"vote_month+$vote"],
                 'vote_total'   =>['exp',"vote_total+$vote"],
             ];
             $re5 =Db::name('BookStatistical')->where(['book_id'=>$bookid])->update($data3);
             if($re1 && $re2 && $re3 && $re4 && $re5){

                 Db::commit();//提交事务

                 return 200;
             }else{

                 Db::rollback();//事务回滚
                 return 202;
             }

          }catch (\Exception $exception){

              Db::rollback();//事务回滚
              return 202;
          }

    }

//月票
    public function yuepiao(){
        Db::startTrans();//开启事务
        try{
            $bookid =input('post.bookid');
            $userid =input('post.userid');
            $vipvote =input('post.vipvote');
            if($vipvote==0){
                return 201;
            }
            //添加用户推荐票消费记录
            $data1=[
                'user_id'  =>$userid,
                'book_id'  =>$bookid,
                'type'    =>3,
                'count'    =>$vipvote,
                'dosomething'  =>'投了'.$vipvote.'张月票',
                'date'   =>date('Y-m-d H:i:s')

            ];
            $re1= Db::name('UserConsumerecord')->insert($data1);
            //扣除用户的推荐票
            $re2 =Db::name('User')->where(['user_id'=>$userid])->update(['vipvote'=>['exp',"vipvote-$vipvote"]]);
            //添加用户的推荐票消费记录
            $data2=[
                'user_id'   =>$userid,
                'type'      =>3,
                'count'     =>$vipvote,
                'operate'   =>-1,
                'thing'     =>'投了'.$vipvote.'张月票',
                'platform'  =>'pc',
                'time'       =>date('Y-m-d H:i:s')

            ];
            $re3 =Db::name('UserAccountDetail')->insert($data2);
            //增加统计记录
            $re4= Db::name('BookTongji')->where(['book_id'=>$bookid,'time'=>date('Y-m-d')])->update(['vipvote'=>['exp',"vipvote+$vipvote"]]);
            $data3=[
                'vipvote_day'   =>['exp',"vipvote_day+$vipvote"],
                'vipvote_weeks'   =>['exp',"vipvote_weeks+$vipvote"],
                'vipvote_month'   =>['exp',"vipvote_month+$vipvote"],
                'vipvote_total'   =>['exp',"vipvote_total+$vipvote"],
            ];
            $re5 =Db::name('BookStatistical')->where(['book_id'=>$bookid])->update($data3);
            if($re1 && $re2 && $re3 && $re4 && $re5){

                Db::commit();//提交事务

                return 200;
            }else{

                Db::rollback();//事务回滚
                return 202;
            }

        }catch (\Exception $exception){

            Db::rollback();//事务回滚
            return 202;
        }

    }



}