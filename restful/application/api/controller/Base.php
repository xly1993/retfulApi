<?php
/**
 * Created by PhpStorm.
 * User: xv
 * Date: 2018/8/20
 * Time: 15:24
 */

namespace app\api\controller;


use think\Controller;
use think\Db;
use think\Image;
use think\Request;
use think\Session;
use think\Validate;

class Base extends Controller
{
   protected  $request;//用来处理参数
   protected  $validater;//用来验证数据/参数
   protected  $params;//过滤后符合要求的参数
   protected  $rules ;
   protected  function _initialize()
   {
       parent::_initialize();
       $this->request = Request::instance();
       ##获取参数规则
       $this->rules = Apivalidate::rules();
//       $this->check_time($this->request->only(['time']));
//       $this->check_token($this->request->param());
       $res = $this->check_params($this->request->param(true));

   }

   /**
    * @param [array] $arr 包含时间戳的参数数组
    */

   protected function check_time($arr)
   {
       if(!isset($arr['time'])||intval($arr['time'])<=1){
           $this->return_msg(400,'时间戳不正确');
       }
       if(time()-intval($arr['time'])>60){
           $this->return_msg(400,'请求超时');
       }
   }

    /***
     * @param [array] $arr 包含所有参数
     */
   protected function check_token($arr)
   {
       /********api传过来的token*********/
     if(!isset($arr['token'])||empty($arr['token'])){
         $this->return_msg(400,'token不能为空');
     }
     $app_token = $arr['token'];//api传过来的token

     /***********服务器端生成的token*******/
     unset($arr['token']);
     $service_token = '';
     foreach ($arr as $key=>$value){
         $service_token .= md5($value);
     }
     $service_token = md5('api_'.$service_token.'_api');

     /************对比返回结果****************/
     if($app_token!=$service_token){
         $this->return_msg(400,'token值不正确!');
     }
   }

    /***
     * @param $arr 除了token和time的其他参数
     */
   protected function check_params($arr)
   {
        /*******获取参数验证规则*******/
        $rule = isset($this->rules[\request()->controller()][\request()->action()])?$this->rules[\request()->controller()][\request()->action()]:'';

        /************如果参数为空直接返回****************/
        if(empty($rule)){
            $this->params = $arr;
        }else{
            $this->validater = new Validate($rule);
            if(!$this->validater->check($arr)){
                $this->return_msg(400,$this->validater->getError());
                die;
            }
            /**********如果正常，通过验证****************/
            $this->params = $arr;
        }

   }

    /***
     * 检测用户名 string [有可能是手机号或者是邮箱]
     * @param $username
     * @return string
     */
   protected function check_username($username)
   {
      $is_email = Validate::is($username,'email')?1:0;
      $is_phone = preg_match('/^1[34578]\d{9}$/',$username)?4:2;
      $flag = $is_email+$is_phone;
      switch ($flag){
          /***********not phone not email************/
          case 2:
              $this->return_msg(400,'邮箱或手机号不正确');
              break;
          /***********is email not phone************/
          case 3:
              return 'email';
              break;
          /***********is phone not email************/
          case 4:
              return 'phone';
              break;
      }
   }

    /*****
     * @param $value 手机/邮箱 是否存在数据库
     * @param [string] $type 手机/邮箱
     * @param [int] $exist 该数据是否已经存在数据库 1存在0不存在
     */
   protected function check_exist($value,$type,$exist)
   {
       $type_num = $type == 'phone'?2:4;
       $flag = $type_num+$exist;##1,0
       $phone_res = Db::name('user')->where('user_phone','=',$value)->find();
       $email_res = \db('user')->where('user_email',$value)->find();
       switch ($flag){
           case 2:
            if($phone_res){
                $this->return_msg(400,'此手机号已被占用');
            }
            break;
           case 3:
               if(!$phone_res){
                   $this->return_msg(400,'此手机号不存在!');
               }
               break;
           case 4:
               if($email_res){
                   $this->return_msg(400,'此邮箱已被占用');
               }
               break;
           case 5:
               if(!$email_res){
                   $this->return_msg(400,'此邮箱不存在!');
               }
               break;
       }
   }

    /*****
     * 验证验证码是否正确
     * @param $user_name 用户名
     * @param $code  验证码
     * @return [json]
     */
   protected function check_code($user_name,$code)
   {
       /**********检测是否超时***********/
       $last_time = Session::get($user_name.'time');
       if(time()-$last_time>3000){
           $this->return_msg(400,'验证码超时，请在5分钟内输入');
       }
       /*************检测验证码是否正码****************/
       if(Session::get($user_name.'code')!=$code){
           $this->return_msg(400,'验证码不正确');
       }
       /****************每个验证码只验证一次*****************/
       Session::delete($user_name.'code');
   }

    /***
     * 文件上传
     * @param $file 文件
     * @param string $type 文件类型 默认为空
     * @return mixed 文件地址
     */
   protected  function upload_file($file,$type=''){
       $info = $file->move(ROOT_PATH.'public'.DS.'uploads');

       if($info){
          $path = 'uploads/'.$info->getSaveName();
          /**********裁剪图片*********/
          if(!empty($type)){
              $this->image_eidt($path,$type);
          }
          return str_replace('\\','/',$path);
       }else{
           $this->return_msg(400,$file->getError());
       }
   }

    /***
     * 文件(图片)裁剪
     * @param $path 文件路径
     * @param $type  文件类型
     */
   protected function image_eidt($path,$type){
       $image = Image::open(ROOT_PATH.'public/'.$path);
       switch ($type){
           case 'head_img':
           $image->thumb(200,200,Image::THUMB_CENTER)->save(ROOT_PATH.'public/'.$path);
           break;
       }
   }





   /***
     * api数据返回
     * @param $code [int] [结果码200正常/4**数据问题/5**服务器问题]
     * @param string $msg 返回信息
     * @param array $data  返回数据
     * @return 返回的json 数据
     */
   protected function return_msg($code,$msg='',$data=[])
   {
       $return_data['code'] = $code;
       $return_data['msg'] = $msg;
       $return_data['data'] = $data;
       echo json_encode($return_data,JSON_UNESCAPED_UNICODE);
       die;
   }


    /**
     * var string $method 加解密方法，可通过openssl_get_cipher_methods()获得
     */
    protected $method = 'AES-128-ECB';

    /**
     * var string $secret_key 加解密的密钥
     */
    protected $secret_key = 'ceshi';

    /**
     * var string $iv 加解密的向量，有些方法需要设置比如CBC
     */
    protected $iv = '';

    /**
     * var string $options （不知道怎么解释，目前设置为0没什么问题）
     */
    protected $options = 0;
    /**
     * 加密方法，对数据进行加密，返回加密后的数据
     *
     * @param string $data 要加密的数据
     *
     * @return string
     *
     */
    public function encrypt($data)
    {
        return openssl_encrypt($data, $this->method, $this->secret_key, $this->options, $this->iv);
    }

    /**
     * 解密方法，对数据进行解密，返回解密后的数据
     *
     * @param string $data 要解密的数据
     *
     * @return string
     *
     */
    public function decrypt($data)
    {
        return openssl_decrypt($data, $this->method, $this->secret_key, $this->options, $this->iv);
    }










}