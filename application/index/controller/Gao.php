<?php
namespace app\index\controller;
use think\Controller;
use think\Db;
class Gao extends Controller{

    public function index(){

        return $this->fetch();;
    }

    public function welfare(){

        return $this->fetch();
    }
}