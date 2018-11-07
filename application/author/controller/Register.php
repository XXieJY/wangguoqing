<?php
namespace app\author\controller;
use think\Controller;
use think\Db;
use think\Request;
class Register extends Controller{
    public function index(){
        return $this->fetch();
    }
    //注册
    public function register(){
        if(!request()->isPost()){

            $this->error('系统错误');
        }
        $data=input('post.');

        //检测状态
//       $this->is_state($data['phone']);

       if(!$this->is_email($data['email'])){
           $this->error('邮箱格式不正确');
       }

       if(!$this->is_phone($data['phone'])){
           $this->error('手机号码格式不正确');
       }

       if(!$this->is_password($data['password'])){
           $this->error('密码必须包括大写字母小写字母数字特殊字符且长度大于8位');
       }
       if($data['password']!=$data['nextword']){
           $this->error('两次输入的密码不一样');
       }
        $a1= model('Writer')->getInfoByEmail($data['email']);
      if($a1){
          $this->error('该邮箱已注册过');
      }
        $a2= model('Writer')->getInfoByPhone($data['phone']);

        if($a2){
            $this->error('该手机号已注册过');
        }
      if(!$this->check_code($data['phone'],$data['code'])){
            $this->error('验证码不正确');
      }
      //数据入库
       $Id= model('Writer')->add($data);
       // echo $Id;exit();
       if($Id){
           Db::startTrans();//开启事务
           try{
               $userid=  \think\Loader::controller('gongju/User')->add($data['phone'],$data['email']);
              $ok= model('Writer')->getUser($Id,$userid);
              if($userid && $ok){
                  Db::commit();//提交事务
              }
           }catch (\Exception $e){
               Db::rollback();//回滚事务
           }

           $this->redirect(url('Register/agreement',['id'=>$Id]));
       }

    }
    public function agreement($id){
        if(!is_numeric($id)){
            $this->error('参数错误');
        }
        $res= \think\Loader::controller('author/Common')->resuft_agree($id);
        return $this->fetch('',[
            'id'  =>$id
        ]);
    }
    //判断邮箱合法性
    private function is_email($email){

        return preg_match('/^(\w-*\.*)+@(\w-?)+(\.\w{2,})+$/', $email) ? true : false;

    }
    //验证手机号码的合法性
    private function is_phone($phone){
        if (!is_numeric($phone)) {
            return false;
        }
        return preg_match('/^1[3|4|5|6|7|8|9][0-9]\d{4,8}$/', $phone) ? true : false;
    }
    //验证密码的强度
    private function is_password($password){
      return  preg_match('/^.*(?=.{8,16})(?=.*\d)(?=.*[A-Z])(?=.*[a-z])(?=.*[!+@#%^&*()_-]).*$/',$password)? true :false;
    }
    //判断验证码
    private function check_code($phone,$code){
          $num =session("code".$phone,'','shudong_code');
         return $num==$code? true : false;
    }
//   //判断作者注册状态
//    public function is_state($phone){
//        $state=  \think\Loader::controller('gongju/Check')->is_agreement($phone);
//
//        if($state['code']==1){
//           $this->redirect(url('Register/agreement',['id'=>$state['id']]));
//        }elseif ($state['code']==2){
//            $this->redirect(url('Register/user',['id'=>$state['id']]));
//        }
//    }

    //不同意协议
    public function disAgreen($id){

         if(!is_numeric($id)){
             $this->error('参数错误');
         }
        $author= Db::name('Writer')->where(['author_id'=>$id,'uid'=>0])->find();
      $ok=  \think\Loader::controller('gongju/User')->delete($id,$author['user_id']);
      if($ok){
          $this->redirect('Register/index');
      }
    }

    //同意协议
    public function agreen($id){
        if(!is_numeric($id)){
            $this->error('参数错误');
        }

       $result= Db::name('Writer')->where(['author_id'=>$id,'uid'=>0])->update(['is_agree'=>2,'update_time'=>date('Y-m-d H:i:s')]);
        if($result){
            $this->redirect(url('Register/user',['id'=>$id]));
        }

    }

    public function user($id){

        if(!is_numeric($id)){
            $this->error('参数错误');
        }
        $res= \think\Loader::controller('author/Common')->resuft_info($id);
        if(request()->isPost()){

            $data=input('post.');
            if(!$this->is_card($data['idCard'])){
                $this->error('身份证号不合法');
            }
           $result= Db::name('Writer')->where(['pen_name'=>$data['uname']])->find();
            if(is_array($result)){
                $this->error('笔名重复');
            }
            $result1=Db::name('Writer')->where(['card'=>$data['idCard']])->find();
            if(is_array($result1)){
                $this->error('身份证号码重复');
            }
            $str="抽插,抽动,做爱,强奸,迷奸,轮奸,诱奸,奸杀,奸污,群奸,群p,群P,迷幻药,催情药,淫穴,淫水,淫汁,淫叫,淫乱,骚穴,浪穴,蜜穴,嫩穴,骚货,骚妇,肉棒,肉棍,大屌,肉洞,乳房,乳头,咪咪,揉捏,揉胸,抚摸下体,舔胸,援,阴蒂,敏感点,阴户,阴道,阳具,鸡巴,射精,自慰,手淫,撸管,打飞机,要射了,要泄了,爱液横流,春水泛滥,爱液泛滥,春水横流,顶花心,到花心,胸推,乳交,兽交,口交,性交,乱交,性爱,足交,肛门,肛交,爆菊,操逼,草逼,艹逼,内射,深喉,颜射,内射,裸聊,卖淫,约炮,插屁屁,少年阿宾,少妇白洁,恋童癖,漏阴癖,色情聊,激情裸,性爱视频,成人视频,性爱直播,成人直播,亂倫,乱伦,充气娃娃,振动棒,震动棒,跳蛋,龙根,操嫂子,操了嫂,把精子,SM,sm,做保健,叫床,娇喘,门保健,门按摩,擠乳汁,挤乳汁,奇淫散,黑木耳,粉木耳,酣战连连,香汗靡靡,赤裸着,毛泽东,毛泽西,周恩来,蒋介石,习近平,习远平,习晋平,李克强,薄熙来,王岐山,温家宝,江泽民,林彪,胡锦涛,胡景涛,彭丽媛,回良玉,郭伯雄,徐才厚,吴邦国,李咏曰,李洪志,黎阳平,特朗普,金正恩,赵紫阳,张春桥,北京市,东莞,香港,澳门,台湾,越南,日本,韩国,朝鲜,印度,老挝,美国,俄罗斯,苏联,意大利,加拿大,基督教,伊斯兰教,犹太教,印度教,搞台独,搞传销,法轮功,法伦功,警察殴打,警方包庇,城管打人,共狗,苍蝇水,吸毒,城管打人,警察打人,冰毒,藏獨,上访,藏独,儿园凶,儿园杀,儿园砍,儿园惨,赌球网,安局豪华,被中共,报复执法,仿真枪,麻醉枪,售卖枪支,售卖毒品,买卖枪支,买卖毒品,售信用,售五四,手枪,售三棱,售热武,售枪支,售冒名,售麻醉,售氯胺售猎枪,售军用,售健卫,售假币,艳照门,手机窃,伦理电影,情色小说,情色书籍,情色交易,权色交易,保护伞,中央军委,1040阳光工程,北部湾建设,维卡币,云币,暗黑币,四人帮,傻逼,傻屌,操你妈,草你妈,艹你妈,草尼玛,日你妈,贱货,妈逼,脑残,妈的智障,我操,bitch,他妈的,你他妈,草哭,操哭,艹苦,新疆暴乱,新疆暴动,新疆骚乱,西藏暴乱,西藏暴动,西藏骚乱,东突分子,东突份子,分裂份子,分裂分子,乌鲁木齐7,乌鲁木齐七,七五事件,新疆和田,伊塔事件,文化大革命,中国暴乱,中国暴动,基地分子,巴仁乡暴,暴力恐怖事件,鄯善县骚乱,龙湾事件,六四事,60年代大饥荒,建国门事件,天安门事件,天安门自焚,八六学潮,八九学潮";
            $keyword=explode(',',$str);
            for ($i = 0; $i < count($keyword); $i++) {    //根据数组元素数量执行for循环
                //应用substr_count检测文章的标题和内容中是否包含敏感词
                if (substr_count($data['uname'], $keyword[$i]) > 0) {
                    $m = $m . $keyword[$i] .' ';
                }
            }
            if($m){
                $this->error('笔名出现敏感词：'.$m);
            }
            $con['uid']=1;
            $con['pen_name']=$data['uname'];
            $con['user_name']=$data['name'];
            $con['card']      =$data['idCard'];
            $con['qq']    =$data['qq'];
            $con['sign'] =$data['signature'];
            $con['update_time'] =date('Y-m-d H:i:s');
            //数据入库
          $ok=  Db::name('Writer')->where(['author_id'=>$data['id']])->update($con);
          if($ok){
              //更新读者信息
              $userIds=Db::name('Writer')->where(['author_id'=>$data['id']])->find();
              Db::name('User')->where(['user_id'=>$userIds['user_id']])->update(['pen_name'=>$userIds['pen_name'],'update_time'=>date('Y-m-d H:i:s')]);
              $this->redirect('Register/success');
          }else{
              $this->error('系统错误');
          }

        }else{
            return $this->fetch('',[
                'id'   =>$id
            ]);
        }

    }

    //验证身份证的合法性
    public function is_card($card){

        return preg_match('/(^\d{15}$)|(^\d{17}([0-9]|X)$)/', $card) ? true : false;
    }

    public function success(){
        return $this->fetch();
    }
}