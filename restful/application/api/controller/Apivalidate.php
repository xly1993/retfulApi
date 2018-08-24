<?php
/**
 * Created by PhpStorm.
 * User: xv
 * Date: 2018/8/21
 * Time: 10:17
 */

namespace app\api\controller;


class Apivalidate
{
   public static function rules()
   {
       return $rules = array(
           ##User表模块
           'User'=>array(
               'login'=>array(
                   'user_name' => ['require','max'=>50],
                   'user_pwd'  => 'require|min:6'
               ),
               'register'=>array(
                   'user_name' => ['require','max'=>50],
                   'user_pwd' => 'require|min:6',
                   'code' => 'require|number|length:6'
               ),
               'upload_head_img'=>array(
                   'user_id'=>'require|number',
                   'user_icon'=>'require|image|fileSize:2000000|fileExt:jpg,png,jpeg'
               ),
               'change_pwd'=>array(
                   'user_name' => ['require','max'=>50],
                   'user_ini_pwd'=>'require|min:6',
                   'user_pwd' => 'require|min:6',
               ),
               'find_pwd'=>array(
                   'user_name' => ['require','max'=>50],
                   'user_pwd' => 'require|min:6',
                   'code' => 'require|number|length:6'
               ),
//               'bind_phone'=>array(
//                   'user_id' => 'require|number',
//                   'phone' => ['require','regex'=>'/^1[34578]\d{9}$/'],
//                   'code' => 'require|number|length:6'
//               ),
//               'bind_email'=>array(
//                   'user_id' => 'require|number',
//                   'email' => ['require','email'],
//                   'code' => 'require|number|length:6'
//               ),
               'bind_username'=>array(
                   'user_id' => 'require|number',
                   'user_name' => ['require','max'=>50],
                   'code' => 'require|number|length:6'
               ),
               'nickname'=>array(
                   'user_id'=>'require|number',
                   'nickname'=> 'max:30'
               ),
           ),
           ##验证码
           'Code'=>array(
               'get_code'=>array(
                   'username' => 'require',
                   'is_exist' => 'require|number|length:1'
               ),
           ),

           ##用户中奖接口
           'Price'=>array(
               'gaiLv'=>array(
                   'uid'=>'require|alphaNum|min:6|max:50'
               ),
           ),



       );
   }
}