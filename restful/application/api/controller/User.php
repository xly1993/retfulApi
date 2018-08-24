<?php
/**
 * Created by PhpStorm.
 * User: xv
 * Date: 2018/8/20
 * Time: 15:14
 */

namespace app\api\controller;


class User extends Base
{
    /**
     * 用户登录
     * @return [json]
     */
   public function login()
   {
       /************接受参数************/
       $data = $this->params;
      /**************检测用户名*********/
      $user_name_type = $this->check_username($data['user_name']);
      switch ($user_name_type){
          case 'phone':
              $this->check_exist($data['user_name'],'phone',1);
              $db_res = db('user')
                  ->field('user_id,user_name,user_phone,user_email,user_rtime,user_pwd')
                  ->where('user_phone',$data['user_name'])
                  ->find();
              break;
          case 'email':
              $this->check_exist($data['user_name'],'email',1);
              $db_res = db('user')
                  ->field('user_id,user_name,user_phone,user_email,user_rtime,user_pwd')
                  ->where('user_phone',$data['user_name'])
                  ->find();
              break;
      }
      if(md5($data['user_pwd']==$db_res['user_pwd'])){
          unset($db_res['user_pwd']);
          $this->return_msg(200,'登录成功',$db_res);
      }else{
          $this->return_msg(400,'用户名或者密码不正确');
      }
   }
    /**
     * 用户注册
     * @return [json]
     */
   public function register()
   {
       $data = $this->params;

       /*************密码md5加密***************/
       $data['user_pwd'] = md5($data['user_pwd']);
       /*********检查验证码*********/
       $this->check_code($data['user_name'],$data['code']);
       /**********检测用户名********/
       $user_name_type = $this->check_username($data['user_name']);
       switch ($user_name_type){
           case'phone':
               $this->check_exist($data['user_name'],'phone',0);
               $data['user_phone'] = $data['user_name'];
               break;
           case 'email':
               $this->check_exist($data['user_name'],'email',0);
               $data['user_email'] = $data['user_name'];
       }
       /************将用户信息写入数据库*************/
       unset($data['user_name']);
       $data['user_rtime'] = time();
       $res = db('user')->insertGetId($data);
       if(!$res){
           $this->return_msg(400,'用户注册失败');
       }else{
           $this->return_msg(200,'用户注册成功',$res);
       }
   }
   /**
     * 用户头像上传
     */
   public function upload_head_img()
   {
     /*********接收参数*************/
     $data = $this->params;
     /*******上传文件,获得路径******/
     $head_img_path = $this->upload_file($data['user_icon'],'head_img');
     /************存入数据库**************/
     $res = db('user')->where('user_id',$data['user_id'])->setField('user_icon',$head_img_path);
     if($res){
         $this->return_msg(200,'头像上传成功',$head_img_path);
     }else{
         $this->return_msg(400,'头像上传失败');
     }

   }
   /**
     * 用户修改密码
     */
   public function change_pwd()
   {
     /*******接受参数********/
     $data = $this->params;
     /************检查用户名并取出数据库中的密码******************/
     $user_name_type = $this->check_username($data['user_name']);
     switch($user_name_type){
         case 'phone':
             $this->check_exist($data['user_name'],'phone',1);
             $where['user_phone'] = $data['user_name'];
             break;
         case 'email':
             $this->check_exist($data['user_name'],'email',1);
             $where['user_email'] = $data['user_name'];
             break;
     }
     /*********判断原始密码是否正确************/
     $db_ini_pwd = db('user')->where($where)->value('user_pwd');
     if($db_ini_pwd !== md5($data['user_ini_pwd'])){
         $this->return_msg(400,'原密码不正确');
     }
     /*********把新的密码存入数据库************/
     $db_res = db('user')->where($where)->setField('user_pwd',md5($data['user_pwd']));
     if($db_res!==false){
         $this->return_msg(200,'修改密码成功');
     }else{
         $this->return_msg(400,'修改密码失败');
     }
   }
   /**
     * 用户找回密码 即密码不返回,发送验证码,更新密码
     * @return [json]
     */
   public function find_pwd()
   {
       /**********接受参数***********/
        $data = $this->params;
       /***********检测验证码********/
       $this->check_code($data['user_name'],$data['code']);
       /************检测用户名*******/
       $user_name_type = $this->check_username($data['user_name']);
       switch ($user_name_type){
           case 'phone':
               $this->check_exist($data['user_name'],'phone',1);
               $where['user_phone'] = $data['user_name'];
               break;
           case 'email':
               $this->check_exist($data['user_name'],'email',1);
               $where['user_email'] = $data['user_name'];
               break;
       }
       /**********修改数据库********/
       $db_res = db('user')->where($where)->setField('user_pwd',md5($data['user_pwd']));
       if($db_res!==false){
           $this->return_msg(200,'密码修改成功');
       }else{
           $this->return_msg(400,'密码修改失败');
       }

   }
   /***
     * 绑定手机号或者邮箱
     */
   public function bind_username()
   {
       /***********接收数据************/
       $data = $this->params;
       /***********检查验证码************/
       $this->check_code($data['user_name'],$data['code']);
       /************判断用户名************/
       $user_name_type = $this->check_username($data['user_name']);
       switch ($user_name_type){
           case 'phone':
               $type_name = '手机号';
               $update['user_phone'] = $data['user_name'];
               break;
           case 'email':
               $type_name = '邮箱';
               $update['user_email'] = $data['user_name'];
               break;
       }
       /***********修改数据库************/
       $res = db('user')->where('user_id',$data['user_id'])->update($update);
       if($res!==false){
           $this->return_msg(200,$type_name.'绑定成功!');
       }else{
           $this->return_msg(400,$type_name.'绑定失败!');
       }

   }
   /***
     * 修改昵称
     */
   public function nickname()
   {
       /************接受参数**************/
       $data = $this->params;
       /***********修改数据库************/
       $res = db('user')->where('user_id',$data['user_id'])->update(['user_nickname'=>$data['nickname']]);
       if($res!==false){
           $this->return_msg(200,'昵称修改成功');
       }else{
           $this->return_msg(400,'昵称修改失败');
       }
   }

}