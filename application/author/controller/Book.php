<?php
namespace app\author\controller;
use think\Controller;
use think\Db;
use think\Request;
use app\author\controller\Redis;
use Qiniu\Auth;// 引入鉴权类
use Qiniu\Storage\UploadManager;// 引入上传类
use Qiniu\Storage\BucketManager;
vendor('Qiniu.autoload');
class Book extends Base {


    public function index(){

        $date=date('Y-m-d');
        $author=$this->account;

        //获取作者的书籍信息
        $book=Db::view('Book','book_id,book_name,upload_img,words,sign_id,audit')
            ->view('BookEdit','edit_name,qq','BookEdit.e_id=Book.e_id')
            ->view('BookStatistical','collection_total','BookStatistical.book_id=Book.book_id')
            ->where(['Book.author_id'=>$author['author_id'],'is_show'=>1])
            ->order('Book.create_time desc')
            ->select();
        foreach ($book as $k=>$v){

            $chapter =$this->get_new_chapter($v['book_id']);
            $book[$k]['title']=$chapter['title'];
            $book[$k]['num'] =$chapter['num'];
            $book[$k]['time'] =$chapter['time'];
        }
        $integral =$this->get_jifen($author['author_id']);
        return $this->fetch('',[
            'date'   =>$date,
            'book'   =>$book,
            'integral'  =>$integral,
            'author_id'  =>$author['author_id']
        ]);
    }

    //获取最新章节
    private function get_new_chapter($bookid){

        $where['book_id']=$bookid;
        $where['type'] =0;
        $where['status'] =0;
        $where['state']=1;

        $chapter= Db::name('Content')->where($where)->field('title,num,time')->order('num desc')->find();
        if(is_array($chapter)){
            return $chapter;
        }else{
            $arr =[
                'title'  =>"暂无章节",
                'time'   =>date('Y-m-d H:i:s'),
                'num'   =>0
            ];
            return $arr;
        }


    }
 //获取作者积分
    private function get_jifen($author_id){

      $author=  Db::name('Writer')->where(['author_id'=>$author_id])->find();
        return $author['integral'];
    }
    public function add(){

        $bookType=$this->book_type();//获取书籍分类
        $keyword=$this->keyword();//获取书籍标签
        $editName=$this->edit_name();//网站编辑
       return $this->fetch('',[
           'bookType'   =>$bookType,
           'keyword'    =>$keyword,
           'editName'   =>$editName
       ]);
    }
    //新书上传
    public function save(){
      $author=$this->account;
        if(!request()->isPost()){
            $this->error('系统错误');
        }
        $data=input('post.');
        print_r($data);exit();
        $type=book_type(trim($data['type']));
       // print_r($type);exit();
        $key=trim($data['tag']);
       $keyword= $this->chuli_key($key);
       if($data['bookName']==""){
           $this->error('书籍名称不能为空');
       }
        $m= \think\Loader::controller('gongju/Chapterlei')->sensitive_words($data['bookBrief']);
        if($m){
            $this->error('请修改作品简介敏感词：'.$m);
        }
        $n= \think\Loader::controller('gongju/Chapterlei')->sensitive_words($data['bookName']);
        if($n){
            $this->error('请修改书名敏感词：'.$n);
        }
        //判断书籍名称是否重名
       $ok=Db::name('Book')->where(['book_name'=>$data['bookName']])->find();
       if(is_array($ok)){
           $this->error('书名已重名，请修改书名重新上传');
       }
       if($data['cp']=="书咚网首发"){
           $con['cp_id']=1;
       }elseif ($data['cp']=="其他网站首发"){
           $con['cp_id']=2;
       }else{
           $con['cp_id']=1;
       }
       $con['e_id']=edit_id($data['editName']);
       $con['book_name']=html_entity_decode(trim($data['bookName']));
       $con['author_id']=$author['author_id'];
       $con['author_name']=$author['pen_name'];
       $con['type_id']=$type;
       if($data['level']=="G级"){
           $con['level']=0;
       }elseif ($data['level']=="R18+"){
           $con['level']=1;
       }else{
           $con['level']=1;
       }
      if($con['state']=="连载中"){
           $con['state']=1;
      }elseif ($con['state']=="已完结"){
           $con['state']=2;
      }else{
           $con['state']=1;
      }

       $con['is_show']=1;
       $con['audit']=0;
       $con['keywords']=$keyword;
       $con['book_brief']=$data['bookBrief'];
       $con['create_time']=date('Y-m-d H:i:s');
       $con['update_time']=date('Y-m-d H:i:s');
       if($data['weaving']){
           $con['weaving']=$data['weaving'];
       }
       if($data['check']){
           $con['is_from']=$data['check'];
       }

        if($file=request()->file('uploadImg')){
            $size=  $file->getSize();
            if($size>204800){
                $this->error('书籍封面不能超过200K');
            }

            $filePath = $file->getRealPath();
            $ext = pathinfo($file->getInfo('name'), PATHINFO_EXTENSION);  //后缀

            // 上传到七牛后保存的文件名
            $key ='Upload/book/zhong/'.substr(md5($file->getRealPath()) , 0, 5). date('YmdHis') . rand(0, 9999) . '.' . $ext;

            // 需要填写你的 Access Key 和 Secret Key
            $accessKey = config('qiniu.ak');
            $secretKey = config('qiniu.sk');
            // 构建鉴权对象
            $auth = new Auth($accessKey, $secretKey);
            // 要上传的空间
            $bucket = config('qiniu.bucket');
            $domain = config('qiniu.image_url');
            $token = $auth->uploadToken($bucket);
            // 初始化 UploadManager 对象并进行文件的上传
            $uploadMgr = new UploadManager();
            // 调用 UploadManager 的 putFile 方法进行文件的上传
            list($ret, $err) = $uploadMgr->putFile($token, $key, $filePath);
            if ($err !== null) {
                echo ["err"=>1,"msg"=>$err,"data"=>""];
            } else {
                //处理图片路径报存到数据库
                $fileName=$ret['key'];
                $imageArr=explode('/',$fileName);
                $imageName =$imageArr[3];
                $con['upload_img']=$imageName;
            }

        }
      //print_r($con);exit();
        $id= \think\Loader::controller('gongju/Booklei')->book_add($con);
        if($id>0){
            //  $this->success('作品添加成功',url('Chapter/index',['id'=>$id]),'',1);

            $this->success('作品添加成功',url('Chapter/add',['bookid'=>$id]));
           // $this->redirect('Chapter/index',['id'=>$id]);
        }else{
            $this->error('作品添加失败');
        }

    }
    //作品分类
    public function book_type(){
        $bookType=Db::name('BookType')->select();
        return $bookType;
    }

    //获取书籍标签
    public function keyword(){
        $keyword =Db::name('SystemKeys')->select();
        return $keyword;

    }
    //网站编辑
    public function edit_name(){

        $editName =Db::name('BookEdit')->field('e_id,edit_name,qq')->select();
        return $editName;
    }
    //处理书籍标签
    public function chuli_key($key){
       $list=explode(' ',$key);
       for ($i=0;$i<count($list);$i++){
          //去除空格bug
           if($list[$i]!=""){
              $arr[$i]=$list[$i];
           }
       }

     $keywords=implode('|',$arr);

      return $keywords;

    }

    //上传须知
    public function notice(){

        return $this->fetch();
    }
    //作品设置
    public function set($id){
        if(!is_numeric($id)){
            $this->error('参数错误');
        }
       $shell= $this->shell($id);
        if($shell===false){
            $this->error('您不是该书的作者，没有权限操作');
        }
        $keyword=$this->keyword();
        $book=Db::view('Book','book_id,book_name,book_brief,level,upload_img,e_id,state,cp_id,keywords')
               ->view('BookType','book_type','BookType.type_id=Book.type_id')
               ->view('BookEdit','edit_name,qq','BookEdit.e_id=Book.e_id')
               ->where(['Book.book_id'=>$id])
               ->find();
        $keyArr =$this->chuli_keywords($book['keywords']);
        return $this->fetch('',[
            'book'  =>$book,
            'keyword'  =>$keyword,
            'key'     =>$keyArr
        ]);
    }

    //字符串与数组的转换’
    private function chuli_keywords($arr){

        $list=explode('|',$arr);
        return $list;
    }

    //修改作品设置
    public function unsave(){
        if(!request()->isPost()){
            $this->error('系统错误');
        }
        $data=input('post.');

       // print_r($data);exit();
      //  $type=book_type($data['type']);
        $con['cp_id']=$data['cp']=="书咚网首发"?"1":"2";
     //   $con['e_id']=edit_id($data['editName']);
      //  $con['book_name']=$data['bookName'];
      //  $con['type_id']=$type;
        $con['level']=$data['level']=="G级"?"0":"1";
        $con['state']=$data['state']=="连载中"?"1":"2";
        $key=trim($data['tag']);
        $keyword= $this->chuli_key($key);
        $con['keywords']=$keyword;
        $con['book_brief']=$data['bookBrief'];
        $con['update_time']=date('Y-m-d H:i:s');
        if($data['weaving']){
            $con['weaving']=$data['weaving'];
        }
        if($file=request()->file('uploadImg')){

            $size=  $file->getSize();
            if($size>204800){
                $this->error('书籍封面不能超过200K');
            }

            $filePath = $file->getRealPath();
            $ext = pathinfo($file->getInfo('name'), PATHINFO_EXTENSION);  //后缀

            // 上传到七牛后保存的文件名
            $key ='Upload/book/zhong/'.substr(md5($file->getRealPath()) , 0, 5). date('YmdHis') . rand(0, 9999) . '.' . $ext;

            // 需要填写你的 Access Key 和 Secret Key
            $accessKey = config('qiniu.ak');
            $secretKey = config('qiniu.sk');
            // 构建鉴权对象
            $auth = new Auth($accessKey, $secretKey);
            // 要上传的空间
            $bucket = config('qiniu.bucket');
            $domain = config('qiniu.image_url');
            $token = $auth->uploadToken($bucket);
            // 初始化 UploadManager 对象并进行文件的上传
            $uploadMgr = new UploadManager();
            // 调用 UploadManager 的 putFile 方法进行文件的上传
            list($ret, $err) = $uploadMgr->putFile($token, $key, $filePath);
            if ($err !== null) {
                echo ["err"=>1,"msg"=>$err,"data"=>""];
            } else {
                //处理图片路径报存到数据库
                $fileName=$ret['key'];
                $imageArr=explode('/',$fileName);
                $imageName =$imageArr[3];
                $con['upload_img']=$imageName;
            }


        }
        $m= \think\Loader::controller('gongju/Chapterlei')->sensitive_words($data['bookBrief']);
        if($m){
            $this->error('请修改作品简介敏感词：'.$m);
        }

      $result=  Db::name('Book')->where(['book_id'=>$data['bookid']])->update($con);
        if($result){
            $this->success('书籍修改成功');
        }else{
            $this->error('书籍修改失败');
        }

    }
}