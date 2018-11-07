<?php

namespace app\author\controller;
// 用户数索引
define('REDIS_CHAPTER_CONTENT_INDEX','CHAPTER:CONTENT:');//APP章节内容缓存
define('REDIS_CHAPTER_CONTENT_INDEX_PC','PC:CHAPTER:CONTENT:');//pc章节内容缓存
define('REDIS_BOOKSHELF_CONTENT_PREFIX','BOOKSHELF:CONTENT:');//APP书架缓存前缀
define('REDIS_INDEX_SLIDE_PC','PC:INDEX:SLIDE');//pc首页轮播缓存
define('REDIS_INDEX_GOD_TOUCH_PC','PC:INDEX:GOD_TOUCH');//pc首页深夜神触缓存
define('REDIS_INDEX_PUSH_PC','PC:INDEX:PUSH');//pc首页主编强推缓存
define('REDIS_INDEX_HOT_PC','PC:INDEX:HOT');//pc首页火热连载缓存
define('REDIS_INDEX_DACHU_PC','PC:INDEX:DACHU');//pc首页大触神作缓存
define('REDIS_INDEX_XINZUO_PC','PC:INDEX:XINZUO');//pc首页潜力新作缓存
define('REDIS_INDEX_NEWBOOK_PC','PC:INDEX:NEWBOOK');//pc首页新书预热缓存
define('REDIS_BOOK_INFO_PC','PC:BOOKINFO');//pc书籍详情缓存
define('REDIS_BOOK_INFO_TUIJIAN_PC','PC:BOOKINFO:TUIJIAN');//pc书籍详情同类书籍推荐缓存
define('REDIS_CHAPTER_LIST_PC','PC:CHAPTER:LIST');//pc书籍章节列表缓存
define('REDIS_CHAPTER_LIST_TWO_PC','PC:CHAPTER:LIST:TWO');//pc书籍章节列表缓存
define('REDIS_CURBOOK_LUN_PC','PC:CURBOOK:LUN');//pc宅文轮播缓存
define('REDIS_CURBOOK_GOD_TOUCH_PC','PC:CURBOOK:GOD_TOUCH');//pc宅文深夜神触缓存
define('REDIS_TONGREN_LUN_PC','PC:TONGREN:LUN');//pc同人轮播缓存
define('REDIS_TONGREN_GOD_TOUCH_PC','PC:TONGREN:GOD_TOUCH');//pc同人深夜神触缓存
// 经验值前缀
define('REDIS_INTEGRAL_PREFIX_PC','PC_INTEGRAL:');
// 签到前缀
define('REDIS_SIGN_IN_PREFIX_PC','PC_SIGN_IN:');
class Redis
{
    private static $redisInstance;
    // 定义redis连接
    const host = '47.104.140.120';
    const pass = 'shudong123!';
    const port =  7000;
    /**
     * 私有化构造函数
     * 原因：防止外界调用构造新的对象
     */
    private function __construct(){


    }
    /**
     * 获取redis连接的唯一出口
     */
    static public function getRedisConn(){
        if(!self::$redisInstance instanceof self){
            self::$redisInstance = new self;
        }
        // 获取当前单例
        $temp = self::$redisInstance;
        // 调用私有化方法
        return $temp->connRedis();
    }
    /**
     * 连接ocean 上的redis的私有化方法
     * @return Redis
     */
    static private function connRedis()
    {
        try {
            $redis_ocean = new \Redis();
            $redis_ocean->connect(self::host, self::port);
            $redis_ocean->auth(self::pass);
        }catch (\Exception $e){
            echo $e->getMessage().'<br/>';
        }
        return $redis_ocean;
    }

    /**
     * 写入缓存
     * @param string $key 键名
     * @param string $value 键值
     * @param int $exprie 过期时间 0:永不过期
     * @return bool
     */
    public static function set($key, $value, $exprie = 0)
    {
        if ($exprie == 0) {
            $set = self::getRedisConn()->set($key, $value);
        } else {
            $set = self::getRedisConn()->setex($key, $exprie, $value);
        }
        return $set;
    }

    /**
     * 读取缓存
     * @param string $key 键值
     * @return mixed
     */
    public static function get($key)
    {
        $fun = is_array($key) ? 'Mget' : 'get';
        return self::getRedisConn()->{$fun}($key);
    }


}