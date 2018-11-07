<?php
namespace app\gongju\controller;
use think\Controller;
use think\Db;
use think\Request;
class Chapterlei extends Controller{

    //新增章节
    public function add($data,$bookid,$price){
        Db::startTrans();//开启事务
        $book=Db::name('Book');
        $conn=Db::name('Content');
       //获取当前卷
      $juan=  $conn->where(['book_id'=>$bookid,'title'=>$data['roll']])->find();
      //根据当前卷获取当前最大章节坐标
       $zuobiao= $conn->field('num')->where(['book_id'=>$bookid,'volume_fid'=>$juan['volume_id']])->order('num desc')->find();
       if(is_array($zuobiao)){
         $myzb=$zuobiao['num']+1;
       }else{
           $myzb=$juan['num']+1;
       }

        //更新坐标
        $map['book_id']=$bookid;
        $map['num']    =array('EGT', $myzb); //查找比该坐标大的数据
        $newzb=$conn->where($map)->field('content_id')->order('num asc')->select();
        if(is_array($newzb)){
            $xinzb=$myzb+1;
            for ($i=0;$i<count($newzb);$i++){
                $conn->where(['content_id'=>$newzb[$i]['content_id']])->update(['num'=>$xinzb]);
                $xinzb++;
            }
        }

      //数据入库
        $con['book_id'] = $bookid;
        $con['num'] = $myzb;
        $con['title'] = $data['chapter'];
        $con['number'] = $data['number'];
        if($con['number']<1000){
            $con['the_price']=0;
        }else{
            $con['the_price'] = ceil($con['number'] / 1000 * $price);
        }
        $con['price']=$price;
        $con['state']=1;
        $con['volume_fid']=$juan['volume_id'];
        $con['time'] = date('Y-m-d H:i:s', time());
        if($data['time']){
            $con['update_time']=$data['time'];
            $con['status']=1;
        }else{
            $con['update_time']=date('Y-m-d H:i:s');
            $con['status']=0;
        }

        $cid = $conn->insert($con);
        $content_id=$conn->getLastInsID();
        //插入内容
        $neirongs['content_id'] = $content_id;
        $neirongs['content'] = $data['txt'];
        $neirongs['msg']   =$data['msg'];

       try{
          $re1= Db::name('Contents')->insert($neirongs);
           //更新字数
           $words=$con['number'];
           if(!$data['time']){
               $datas['chapter'] = array('exp', "chapter+1"); //总数多少章
           }
           $aaa=$book->where(['book_id'=>$bookid])->find();
           if($aaa['vip']==0){
               if( $con['the_price']>0){
                   $datas['vip']=1;
                   $datas['vip_time']=date('Y-m-d H:i:s');
               }
           }

           $datas['words'] = array('exp', "words+$words");
           $datas['update_time']=date('Y-m-d H:i:s');
           $re2=  $book->where(array('book_id' => $bookid))->update($datas);
           //更新统计字数
           $st['count_day']=array('exp',"count_day+$words");
           $st['count_weeks']=array('exp',"count_weeks+$words");
           $st['count_month']=array('exp',"count_month+$words");
           $st['count_total']=array('exp',"count_total+$words");
         $re3=  Db::name('BookStatistical')->where(['book_id'=>$bookid])->update($st);
         if($re1 && $re2 && $re3){
             Db::commit();//提交事务
             return 1;
         }

       }catch (\Exception $e){
           Db::rollback();//回滚事务
       }




    }
   //删除章节
    public function delete($connkid){
        Db::startTrans();//开启事务
        $content = Db::name('Content');
        $book = Db::name('Book');
        //查看该章节是否存在
        $conn = $content->where(array('content_id' => $connkid))->find();
        if (!is_array($conn)) {
            $this->error("章节不存在");
            exit();
        }
        try{
            //删除章节
          $re1=  $content->where(array('content_id' => $connkid))->delete();
           $re2=      Db::name('Contents')->where(array('content_id' => $connkid))->delete();
            //更新坐标
            $map['book_id'] = $conn['book_id'];
            $map['num'] = array('GT', $conn['num']); //查找比该坐标大的数据
            $gengxinid = $content->where($map)->field('content_id')->order('num ASC')->select();
            if (is_array($gengxinid)) {
                $xinzb = $conn['num'];
                for ($i = 0; $i < count($gengxinid); $i++) {
                    $content->where(array('content_id' => $gengxinid[$i]['content_id']))->update(array('num' => $xinzb));
                    $xinzb++;
                }
            }
            //更新作品
            $words=$conn['number'];
            $bata['words'] = array('exp', "words-$words");
            $bata['chapter'] = array('exp', "chapter-1");
           $re3= $book->where(array('book_id' => $conn['book_id']))->update($bata);
            //更新统计字数
            $st['count_day']=array('exp',"count_day-$words");
            $st['count_weeks']=array('exp',"count_weeks-$words");
            $st['count_month']=array('exp',"count_month-$words");
            $st['count_total']=array('exp',"count_total-$words");
            $re4=  Db::name('BookStatistical')->where(['book_id'=>$conn['book_id']])->update($st);
            if($re1 && $re2 && $re3 && $re4){

                Db::commit();//提交事务
                return 1;
            }

        }catch (\Exception $e){
            Db::rollback();//回滚事务
        }




    }

    public function shangchuan($name, $book_id, $xuhao,$volume_id,$price) {

        //准备工作
        $book = Db::name('Book');
        $con = Db::name('Content');
        $cons = Db::name('Contents');
        $id = 0; //ID号
        $content = NULL; //内容
        $zongzishu = 0; //总字数
        $nums = 1; //时间
        $fp = @fopen('./Upload/text/'.$name,'r');

        if ($fp){
            while (!feof($fp)){
                $bruce = fgets($fp);
                $title = strstr($bruce, "###");
                // echo $title;exit();
                if ($title) {
                    if ($id) {
                        $number = $this->trimall($content);
                        if($number<1000){
                            $the_price=0;
                        }else{
                            $the_price = ceil($number / 1000 * $price);
                        }
                        $con->where(array('content_id' => $id))->update(array('number' => $number, 'the_price' => $the_price,'price'=>$price));
                        //插入内容
                        $keyword= Db::name('SystemKeyword')->select();
                        foreach ($keyword as $k=>$v){
                            $content=str_replace($v,"**",$content);
                        }
                        $neirongs['content_id'] = $id;
                        $neirongs['content'] = trim($content);

                        //    $neirongs['content']=mb_convert_encoding ( $neirongs['content'], 'UTF-8','GB2312');
                        Db::name('Contents')->insert($neirongs);
                        $zongzishu = $zongzishu + $number;
                        $content = NULL;
                    }

                    $xinzb= Db::name('Content')->where(['book_id'=>$book_id,'num'=>['egt',$xuhao]])->select();
                    if(is_array($xinzb)){
                            $aaa=$xuhao+1;
                            for ($i=0;$i<count($xinzb);$i++){
                                Db::name('Content')->where(['content_id'=>$xinzb[$i]['content_id']])->update(['num'=>$aaa]);
                                $aaa++;
                            }
                    }


                    $title = str_replace("###", "", trim($title));
                    //  $title =  mb_convert_encoding ( $title, 'UTF-8','GB2312');
                    //  echo $encode;exit();
                    //准备内同数据
                    $data['book_id'] = $book_id;
                    $data['num'] = $xuhao;
                    $data['title'] = $title;
                    $data['price']=$price;
                    $data['volume_fid']=$volume_id;
                    $data['time'] = date('Y-m-d H:i:s', time());
                    $data['update_time'] = date('Y-m-d H:i:s', time()); //发布时间
                    // var_dump($data);exit();
                    $con->insert($data); //添加作品章节内容
                    $id =$con->getLastInsID();

                    $nums = $nums + 3;
                    $xuhao++;


                }else {
                    $content = $content.$bruce;
                    //dump($content);exit;
                }
            }
            $number = $this->trimall($content);
            if($number<1000){
                $the_price=0;
            }else{
                $the_price = ceil($number / 1000 *$price);
            }
            $con->where(array('content_id' => $id))->update(array('number' => $number, 'the_price' => $the_price,'price'=>$price));
            //插入内容
            $keyword= Db::name('SystemKeyword')->select();
            foreach ($keyword as $k=>$v){
                $content=str_replace($v,"**",$content);
            }

            $neirongs['content_id'] = $id;
            $neirongs['content'] = trim($content);

            //   $neirongs['content'] =  mb_convert_encoding ( $neirongs['content'], 'UTF-8','GB2312');
            $res = $cons->insert($neirongs);
            //echo $cons->getLastSql();
            //echo $res;exit;
            $zongzishu = $zongzishu + $number;
            //$content = NULL;
            //更新书籍表格
         if($price>0){
                 $datas['vip'] =1;
                 $datas['vip_time']  =date('Y-m-d H:i:s');
         }
            $chaptering = $con->where(array('book_id' => $book_id,'type'=>0))->field('num')->order('num desc')->find();
            $datas['chapter']=$chaptering['num'];
            $datas['words'] = array('exp', "words+$zongzishu");
            $datas['update_time'] =date('Y-m-d H:i:s');
            $book->where(array('book_id' => $book_id))->update($datas);
            //更新书籍统计表
            $st['count_day']=array('exp',"count_day+$zongzishu");
            $st['count_weeks']=array('exp',"count_weeks+$zongzishu");
            $st['count_month']=array('exp',"count_month+$zongzishu");
            $st['count_total']=array('exp',"count_total+$zongzishu");
             Db::name('BookStatistical')->where(['book_id'=>$book_id])->update($st);
            //生成txt内容文件
            //file_put_contents("Upload/Book/".$book_id."/txt/".$id.".txt",$content);

        }else {
            echo "打开失败:Upload/text/$name";
        }
        $this->success("上传成功");

    }

    //修改章节
    public function save($data){
       Db::startTrans();//开启事务
       $book =Db::name('Book');
       $content =Db::name('Content');
       $stat=Db::name('BookStatistical');
       try{
           $chapter =$content->field('number')->where(['content_id'=>$data['contentId']])->find();
           if(!is_array($chapter)){
               $this->error('系统错误');
           }
           $words =$chapter['number'];
           //更新书籍字数统计
           $b_data['words']=array('exp',"words-$words");
          $re1= $book->where(['book_id'=>$data['bookid']])->update($b_data);
           //更新统计字数
           $st['count_total']=array('exp',"count_total-$words");
          $re2= $stat->where(['book_id'=>$data['bookid']])->update($st);
           $con['title'] = $data['chapter'];
           $con['number'] = $data['number'];
           if($con['number']<1000){
               $con['the_price']=0;
           }else{
               $con['the_price'] = ceil($con['number'] / 1000 * $data['price']);
           }
           $con['price']=$data['price'];
           if($chapter['status']!=0){//章节本身属于定时发布状态可以修改发布时间
               if($data['time']){
                   $con['update_time']=$data['time'];
               }
           }else{
               $con['update_time'] =date('Y-m-d H:i:s');
           }

          //修改章节
          $re3= $content->where(['content_id'=>$data['contentId']])->update($con);
         //修改内容
           $neirongs['content'] = $data['txt'];
           $neirongs['msg']   =$data['msg'];
          $re4= Db::name('Contents')->where(['content_id'=>$data['contentId']])->update($neirongs);
           //更新字数
           $word=$con['number'];
           $datas['words'] = array('exp', "words+$word");
           $datas['update_time'] =date('Y-m-d H:i:s');
          $re5= $book->where(array('book_id' => $data['bookid']))->update($datas);
           //更新统计字数
           $sts['count_total']=array('exp',"count_total+$word");
          $re6= $stat->where(['book_id'=>$data['bookid']])->update($sts);
          if($re1 && $re2 && $re3 && $re4 && $re5 && $re6){
              Db::commit();//提交事务
              return 1;
          }

       }catch (\Exception $e){
           Db::rollback();//回滚事务
       }



    }

    //提交草稿箱
    public function update($data){
        Db::startTrans();//开启事务
        //修改书籍章节状态
        try{
            $con['title']    =$data['chapter'];
            $con['number'] =$data['number'];
            if($con['number']<1000){
                $con['the_price']=0;
            }else{
                $con['the_price']=ceil($con['number'] / 1000 * $data['price']);
            }
            $con['price']  =$data['price'];
            $con['state']  =1;
                if($data['time']){
                    $con['update_time']=$data['time'];
                    $con['status']  =1;
                }else{
                    $con['update_time'] =date('Y-m-d H:i:s');
                }
           $re1=  Db::name('Content')->where(['content_id'=>$data['contentId']])->update($con);
            //修改内容
            $neirong['content']   =$data['txt'];
            $neirong['msg']       =$data['msg'];
          $re2=  Db::name('Contents')->where(['content_id'=>$data['contentId']])->update($neirong);
            //更新书籍
            $words =$con['number'];

           $abc= Db::name('Book')->where(['book_id'=>$data['bookid']])->find();
           if($abc['vip']==0){
               if($con['the_price']>0){
                   $b_data['vip'] =1;
                   $b_data['vip_time']  =date('Y-m-d H:i:s');
               }
           }

            $b_data['words'] =array('exp',"words+$words");
            $b_data['chapter']  =array('exp',"chapter+1");
            $b_data['update_time']  =date('Y-m-d H:i:s');
           $re3= Db::name('Book')->where(['book_id'=>$data['bookid']])->update($b_data);
            //更新统计
            $st['count_day']=array('exp',"count_day+$words");
            $st['count_weeks']=array('exp',"count_weeks+$words");
            $st['count_month']=array('exp',"count_month+$words");
            $st['count_total']=array('exp',"count_total+$words");
           $re4= Db::name('BookStatistical')->where(['book_id'=>$data['bookid']])->update($st);

           if($re1 && $re2 && $re3 && $re4 ){
               Db::commit();//提交事务
               return 1;
           }

        }catch (\Exception $e){
            Db::rollback();//回滚事务
        }

  }

    //字数统计函数
    public function trimall($str)
    {
        ///删除空格
        $qian = array(" ","\t", "\n", "\r");
        $hou = array("", "", "", "");
        $str = str_replace($qian, $hou, $str);
        $str = mb_convert_encoding($str, 'GBK', 'UTF-8');
        preg_match_all("/[" . chr(0xa1) . "-" . chr(0xff) . "]{2}/", $str, $m);
        $mu = count($m[0]);
        unset($str);
        unset($m);
        return $mu;



    }
}