<?php
namespace app\author\controller;
use think\Controller;
use think\Db;
class Common extends Controller{

    //防止用户通过不正确的链接进入接受协议的界面
    public function resuft_agree($id){
          //根据$id查找数据库
       $author= Db::name('Writer')->where(['author_id'=>$id])->find();
       if(!is_array($author)){
           $this->error('请先注册成为作者','Register/index');
       }else{
           if($author['uid']==1){
               $this->error('请先注册成为作者','Register/index');
           }
       }
    }
    //防止用户通过不正确的链接进入到资料填写的界面
    public function resuft_info($id){
        $author= Db::name('Writer')->where(['author_id'=>$id])->find();
        if(!is_array($author)){
            $this->error('请先注册成为作者','Register/index');
        }else{
            if($author['uid']==1){
                $this->error('请先注册成为作者','Register/index');
            }elseif ($author['pen_name'] || $author['user_name']){
                $this->error('请先注册成为作者','Register/index');
            }
        }

    }
}