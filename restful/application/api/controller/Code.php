<?php
/**
 * Created by PhpStorm.
 * User: xv
 * Date: 2018/8/21
 * Time: 14:04
 */

namespace app\api\controller;
use SUBmail\MESSAGEXsend;
use think\Session;

class Code extends Base
{
   /**
     * 获取验证码入口
     */
   public function get_code()
   {
       $username = $this->params['username'];
       $exist = $this->params['is_exist'];
       $username_type = $this->check_username($username);
       switch ($username_type){
           case 'phone':
               $this->get_code_by_username($username,'phone',$exist);
               break;
           case 'email':
               $this->get_code_by_username($username,'email',$exist);
               break;
       }
   }
   /***
     * 通过手机/邮箱获取验证码
     * @param [string] $username [手机号/邮箱]
     * @param [string] $type 判断是发送手机还是邮箱
     * @param [int] $exist [手机号/邮箱是否应该存在于数据库中 1:是 0:否]
     * @return [json]
     */
   private function get_code_by_username($username,$type,$exist){
       if($type == 'phone'){
           $type_name = '手机';
       }else{
           $type_name = '邮箱';
       }
        /**********检测手机号/邮箱是否存在***************/
       $this->check_exist($username,$type,$exist);
       /**********检查验证码请求频率30秒一次***************/
       if($time=Session::get($username.'time')){
            if(time()-$time<30){
                $this->return_msg(400,$type_name.'验证码，每30秒只能发送一次');
            }
       }
       /**********生成验证码***************/
       $code = $this->make_code(6);
        /**********使用session存储验证码***************/
       Session::set($username.'code',$code);
        /**********使用session存储验证码发送时间***************/
       Session::set($username.'time',time());


        /**********发送验证码***************/
       if($type=='phone'){
            $this->send_code_to_phone($username,$code);
       }else{
            $this->send_code_to_email($username,$code);
       }

   }
   /***
     * 生成的验证码
     * @param [int] $num 验证码位数
     * @return int 返回验证码
     */
   private function make_code($num)
   {
       $max = pow(10,$num)-1;
       $min =pow(10,$num-1);
       return $code = mt_rand($min,$max);
   }
   /****
     * 向手机发送验证码
     * @param [string] $phone 手机号
     * @param [int] $code 验证码
     * @return [json]
     */
   private function send_code_to_phone($phone,$code)
   {
//       $curl = curl_init();
//       curl_setopt($curl,CURLOPT_URL,'https://api.mysubmail.com/message/xsend');
//       curl_setopt($curl,CURLOPT_HEADER,0);
//       curl_setopt($curl,CURLOPT_RETURNTRANSFER,1);
//       curl_setopt($curl,CURLOPT_POST,1);
//       $data = [
//           'appid'=>15180,
//           'to' =>$phone,
//           'project'=>'9CTTG2',
//           'vars'=>'{"code":'.$code.',"time":"60"}',
//           'signature'=>'76a9e82484c83345b7850395ceb818fb'
//       ];
//       curl_setopt($curl,CURLOPT_POSTFIELDS,$data);
//       $res = curl_exec($curl);
//       /**********打印错误信息**********/
//       //dump(curl_error($curl));
//       curl_close($curl);
//       $res= json_decode($res);
//       if($res->status != 'success'){
//           $this->return_msg(400,$res->msg);
//       }else{
//           $this->return_msg(200,'验证码已经发送，一天最多只能发送5次,请在一分钟内验证码');
//       }
    $message_configs = array(
        'appid'=>'15180',
        'appkey'=>'76a9e82484c83345b7850395ceb818fb',
        'sign_type'=>'normal',
        'server'=>'https://api.mysubmail.com/'
    );
    $submail = new MESSAGEXsend($message_configs);
    $submail->SetTo($phone);
    $submail->SetProject('9CTTG2');
    $submail->AddVar('code',$code);
    $submail->AddVar('time',60);
    $xsend = $submail->xsend();
    if($xsend['status']!=='success'){
        $this->return_msg(400,$xsend['msg']);
    }else{
        $this->return_msg(200,'验证码已经发送，一天最多只能发送5次,请在一分钟内验证码');
    }




   }
   /***
     * 向邮箱发送验证码
     * @param [string] $email 被发送者的邮箱
     * @param [int] $code 被发送的验证码
     * @return [json] 返回json格式信息
     */
   private function send_code_to_email($email,$code)
    {
        $toemail = $email;
        $mail = new \PHPMailer\PHPMailer();
        $mail->isSMTP();
        $mail->CharSet = 'UTF-8';
        $mail->Host = 'smtp.163.com';
        $mail->SMTPAuth = true;
        $mail->SMTPSecure='ssl';
        $mail->Username ='18856482792@163.com';
        $mail->Password = 'xly199302060888';
        $mail->Port = 994;
        $mail->setFrom('18856482792@163.com','短信验证码测试');
        $mail->addAddress($toemail,'test');
        $mail->addReplyTo('18856482792@163.com','Reply');
        $mail->Subject = '您有新的验证码';
        $mail->Body = "这是一个测试邮件,您的验证码是$code,验证码有效期为5分钟,本邮件请勿回复";
        if(!$mail->send()){
            $this->return_msg(400,$mail->ErrorInfo);
        }else{
            $this->return_msg(200,'验证码已发送，请注意查收');
        }

    }
}