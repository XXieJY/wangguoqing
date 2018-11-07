<?php
namespace app\author\model;
use think\Model;
use think\Request;
class Writer extends Model{

    //添加数据
    public function add($where){
        $data=[
            'uid'         =>0,
            'email'      =>$where['email'],
            'pass_word' =>md5($where['password'].config('author.ALL_ps')),
            'phone'     =>$where['phone'],
            'login_ip'  =>request()->ip(),
            'time'      =>date('Y-m-d H:i:s'),
            'update_time' =>date('Y-m-d H:i:s')
        ];

          $this->save($data);
          return $this->getLastInsID();
    }

    //获取登录信息
    public function getInfo($res){
        $where =[
            'email'  =>$res['email'],
            'pass_word' =>md5($res['password'].config('author.ALL_ps'))
        ];
        return $this->where($where)->find();
    }
    //根据邮箱找数据
    public function getInfoByEmail($email){
        return $this->where(['email'=>$email,'uid'=>1])->find();
    }
    //根据手机号找数据
    public function getInfoByPhone($phone){
        return $this->where(['phone'=>$phone,'uid'=>1])->find();
    }
    //绑定读者账号
    public function getUser($author,$userId){
        return $this->save(['user_id'=>$userId],['author_id'=>$author]);
    }
}