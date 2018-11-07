<?php
namespace app\api\controller;
// 引入鉴权类
use Qiniu\Auth;
// 引入上传类
use Qiniu\Storage\UploadManager;
use think\Controller;

/**
 * 七牛图片上传基础类库
 */
class Upload
{
    public static function image(){
        if(empty($_FILES['file']['tmp_name'])){
            exception('你提交的图片数据不合法',404);
        }
        // 图片的本地路径
        $file = $_FILES['file']['tmp_name'];
        // 换取上传图片的后缀
        // $ext = explode('.',$_FILES['file']['name'])[1];
        $pathinfo = pathinfo($_FILES['file']['name']);
        $ext = $pathinfo['extension'];
        $config = config('qiniu');
        // 构建一个鉴权对象
        $auth = new Auth($config['ak'],$config['sk']);
        // 生成上传的token
        $token = $auth->uploadToken($config['bucket']);
        // 上传到七牛后保存的文件名
        $key = date('Y').'/'.date('m').'/'.substr(md5($file),0,5).date('YmdHis').mt_rand(0,9999).'.'.$ext;

        // 初始化UploadManager类
        $uploadMgr = new UploadManager();
        list($ret,$err) = $uploadMgr->putFile($token,$key,$file);
        if($err !== null){
            return null;
        }else{
            return $key;
        }
    }

    public function upload()
    {
        // 捕获异常
        try {
            // 返回qiniu上的文件名
            $image = Upload::image();
        } catch (\Exception $e) {
            echo json_encode(['status' => 0, 'message' => $e->getMessage()]);
        }
        // 返回给uploadify插件状态
        if ($image) {
            $data = [
                'status' => 1,
                'message' => 'OK',
                'data' => config('qiniu.image_url') . '/' . $image,
            ];
            echo json_encode($data);
            exit;
        } else {
            echo json_encode(['status' => 0, 'message' => '上传失败']);
        }

    }

    }
