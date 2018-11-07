<?php
use think\Route;
return [
//    '__pattern__' => [
//        'name' => '\w+',
//    ],
//    '[hello]'     => [
//        ':id'   => ['index/hello', ['method' => 'get'], ['id' => '\d+']],
//        ':name' => ['index/hello', ['method' => 'post']],
//    ],

     'index'             =>'index/index/index', //首页
     'ranking'           =>'index/ranking/index',//排行
     'curbook'          =>'index/curbook/index',//宅文
    'tongren'           =>'index/tongren/index',//同人
    'gao'             =>'index/gao/index', //约稿公告
    'welfare'             =>'index/gao/welfare', //作者福利
    'aboat'             =>'index/aboat/index', //关于书咚
    'help'             =>'index/aboat/help', //寻求帮助
    'contract'             =>'index/aboat/contract', //关于书咚
    'listclick'          =>'index/listclick/index',
    'user'                =>'index/user/index',//用户中心
    'user/read'                =>'index/user/read',//我的阅读
    'user/order'                =>'index/user/order',//我的订阅
    'user/message'                =>'index/user/message',//我的评论
    'login'             =>'index/login/index',//登陆
    'login/ok'             =>['index/login/ok',['method'=>'get'],['vote'=>'\d+']],//登陆成功
    'register'             =>'index/register/index',//注册
    'register/register'             =>'index/register/register',//注册
    'forget'             =>'index/register/forget',//忘记密码
    'register/signLogin'             =>'index/register/signLogin',//忘记密码
    'register/ok'             =>'index/register/ok',//忘记密码
    'logout'             =>'index/login/logout', //退出
   'book'               =>'index/book/index',
    'pay/index'               =>'index/pay/index',
    'pay/alipay'               =>'index/pay/alipay',
    'notify'                    =>'index/notify/index',
    'success'                    =>'index/success/index',
    'login/login'      =>['index/login/login',['method'=>'post']],
    'chapterlist/:bookid'       =>['index/Chapterlist/index',['method' => 'get'], ['bookid' => '\d+']],//书籍目录界面
    'bookinfo/:bookid'       => ['index/Bookinfo/index',['method' => 'get'], ['bookid' => '\d+']],//详情界面
    'bookinfo/buyOrder'           =>['index/bookinfo/buyOrder',['method'=>'post']],
    'read/:bookid/:num'       => ['index/read/index',['method' => 'get'], ['bookid' => '\d+','num'=>'\d+']],//阅读界面
    'vipread/:bookid/:num'       => ['index/vipread/index',['method' => 'get'], ['bookid' => '\d+','num'=>'\d+']],//阅读界面
    'vipread/buyOrder'           =>['index/vipread/buyOrder',['method'=>'post']],
    'promote/:bookid/:num'       => ['index/read/promote',['method' => 'get'], ['bookid' => '\d+','num'=>'\d+']],//阅读界面
    'weixin'             =>'index/account/weixin',//微信扫码登陆
    'weixincallback'     =>'index/Account/weixincallback',//微信回调地址
    'qq'         =>'index/account/qq',//QQ回调地址
    'qqcallback'         =>'index/account/qqcallback',//QQ回调地址
];

