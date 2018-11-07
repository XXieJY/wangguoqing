<?php
use think\Db;
error_reporting(E_ERROR | E_WARNING | E_PARSE);
/*
 * 书籍级别判断
 */
function level($level){
    if($level==0){
        $str="G级";
    }else{
        $str="R18+";
    }
    return $str;
}
/*
 * 处理数字
 */
function num($num){
    $number =$num/10000;
    $str =round($number,1);
    if($str<=0.1){
        $str=0.1;
    }
    return $str."万";
}
/*
 * 处理字符串长度
 */

function title($title){

    $len = mb_strlen($title,"utf-8");
    if($len > 5)
    {
        $title = mb_substr($title,0,4,"utf-8")."...";
    }
    return $title;
}
/*
 * 处理字符串长度
 */

function newtitle($title){

    $len = mb_strlen($title,"utf-8");
    if($len > 15)
    {
        $title = mb_substr($title,0,14,"utf-8")."...";
    }
    return $title;
}
/*
 * 处理书籍详情长度
 */

function book_brief($title){

    $len = mb_strlen($title,"utf-8");
    if($len > 90)
    {
        $title = mb_substr($title,0,89,"utf-8")."...";
    }
    return $title;
}

/*
 * 用户等级
 */
function mem_vip($level){

    switch ($level) {

        case 1:
           $str="初入咚家";
            break;
        case 2:
            $str="无名咚者";
            break;
        case 3:
            $str="咚力三段";
            break;
        case 4:
            $str="略有小成";
            break;
        case 5:
            $str="小有咚名";
            break;
        case 6:
            $str="出县咚师";
            break;
        case 7:
            $str="声明远杨";
            break;
        case 8:
            $str="咚名达府";
            break;
        case 9:
            $str="赫赫有名";
            break;
        case 10:
            $str="鸣州咚宗";
            break;
        case 11:
            $str="荣耀王者";
            break;
        case 12:
            $str="恐怖如咚";
            break;
        case 13:
            $str="镇国支柱";
            break;
        case 14:
            $str="一代至尊";
            break;
        case 15:
            $str="咚传天下";
            break;
    }

    return $str;
}
//书籍分类
function book_type($bookType){

    switch ($bookType) {

        case "青春日常":
            $str=1;
            break;
        case "异界玄幻":
            $str=2;
            break;
        case "动漫穿越":
            $str=3;
            break;
        case "神秘科幻":
            $str=4;
            break;
        case "游戏世界":
            $str=5;
            break;
        case "超现实都市":
            $str=6;
            break;
        case "幻想修仙":
            $str=7;
            break;
        case "战争历史":
            $str=8;
            break;
        case "悬疑灵异":
            $str=9;
            break;
        case "搞笑吐槽":
            $str=10;
            break;
        case "无敌爽文":
            $str=11;
            break;
        case "轻小说":
            $str=12;
            break;
        case "萌系变身":
            $str=13;
            break;
        case "唯美幻想":
            $str=14;
            break;
        case "耽美绝爱":
            $str=15;
            break;
        case "同人衍生":
            $str=16;
            break;
    }

    return $str;
}
//编辑
function edit_id($editName){
    switch ($editName) {

        case "大刀":
            $str = 2;
            break;
        case "七七":
            $str = 3;
            break;
        case "默认编辑":
            $str = suiji();
            break;
        case "司辰":
            $str = 5;
            break;

    }
    return $str;

}

function suiji(){

    $arr=array(2,3,5);
    $i=rand(0,2);
    return $arr[$i];
}
//编辑
function edit_name($editid){
    switch ($editid) {

        case "2":
            $str = '大刀';
            break;
        case "3":
            $str = '七七';
            break;
        case "4":
            $str = '默认编辑';
            break;
        case "5":
            $str = '司辰';
            break;

    }
    return $str;

}

//签约状态
function sign($sign_id){
    switch ($sign_id) {

        case "0":
            $str = '无意向';
            break;
        case "1":
            $str = '作者已申请';
            break;
        case "2":
            $str = '已邀约';
            break;
        case "3":
            $str = '签约中';
            break;
        case "4":
            $str = '已签约';
            break;
        case "5":
            $str = '签约失败';
            break;

    }
    return $str;

}

//分卷
function juan($v_id){
    switch ($v_id) {

        case "1":
            $str = '第一卷';
            break;
        case "2":
            $str = '第二卷';
            break;
        case "3":
            $str = '第三卷';
            break;
        case "4":
            $str = '第四卷';
            break;
        case "5":
            $str = '第五卷';
            break;
        case "6":
            $str = '第六卷';
            break;
        case "7":
            $str = '第七卷';
            break;
        case "8":
            $str = '第八卷';
            break;
        case "9":
            $str = '第九卷';
            break;
        case "10":
            $str = '第十卷';
            break;
        case "11":
            $str = '第十一卷';
            break;
        case "12":
            $str = '第十二卷';
            break;
        case "13":
            $str = '第十三卷';
            break;
        case "14":
            $str = '第十四卷';
            break;
        case "15":
            $str = '第十五卷';
            break;
        case "16":
            $str = '第十六卷';
            break;
        case "17":
            $str = '第十七卷';
            break;
        case "18":
            $str = '第十八卷';
            break;
        case "19":
            $str = '第十九卷';
            break;
        case "20":
            $str = '第二十卷';
            break;


    }
    return $str;


}

//获取所有作品
 function get_book($author_id){

    $book=  Db::name('Book')->field('book_name')->where(['author_id'=>$author_id,'is_show'=>1,'audit'=>1])->order('create_time desc')->select();
    return $book;
}
//判断道具类型
function obtain($type){
    switch ($type) {

        case "2":
            $str = '推荐票';
            break;
        case "3":
            $str = '月票';
            break;
        case "4":
            $str = '打赏';
            break;

    }
    return $str;

}

/*
 * 根据作者id获取用户头像
 */
function getUserImage($authorId){

    $user=  Db::view('Writer','user_id')
        ->view('User','sex,portrait','Writer.user_id=User.user_id')
        ->where(['author_id'=>$authorId])
        ->find();
    if($user['portrait']<60){
        if($user['portrait']=="user/portrait/portrait.jpg"){

            $user['portrait']="http://images.shuddd.com/user/portrait/portrait".$user['sex'].".png";

        }else{
            $user['portrait']="http://images.shuddd.com/".$user['portrait'];
        }

    }
    return $user['portrait'];
}

/*
 * 频道
 */
function gender($gender){

    switch ($gender){
        case 1:
            $str="男神爱看";
            break;
        case 0:
            $str="女神爱看";
            break;
    }

    return $str;

}


