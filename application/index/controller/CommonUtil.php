<?php
namespace app\index\controller;

class CommonUtil{
   //用户级别
    public function men_vip($value){

        if ($value < 1) {
            $level = 1;
        } elseif ($value < 201) {
            $level = 2;
        } elseif ($value < 501) {
            $level = 3;
        } elseif ($value < 1001) {
            $level = 4;
        } elseif ($value < 2001) {
            $level = 5;
        } elseif ($value < 5001) {
            $level = 6;
        } elseif ($value < 10001) {
            $level = 7;
        } elseif ($value < 20001) {
            $level = 8;
        } elseif ($value < 50001) {
            $level = 9;
        } elseif ($value < 100001) {
            $level = 10;
        } elseif ($value < 200001) {
            $level = 11;
        } elseif ($value < 500001) {
            $level = 12;
        } elseif ($value < 1000001) {
            $level = 13;
        } elseif ($value < 2000001) {
            $level = 14;
        } else {
            $level = 15;
        }
        return $level;



    }

}