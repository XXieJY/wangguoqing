<?php
namespace app\index\controller;
use think\Controller;
class Aboat extends Controller{

    public function index(){

        return $this->fetch();
    }

    public function help(){

        return $this->fetch();
    }

    public function contract(){

        return $this->fetch();
    }
}