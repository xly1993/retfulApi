<?php
// +----------------------------------------------------------------------
// | ThinkPHP [ WE CAN DO IT JUST THINK ]
// +----------------------------------------------------------------------
// | Copyright (c) 2006~2016 http://thinkphp.cn All rights reserved.
// +----------------------------------------------------------------------
// | Licensed ( http://www.apache.org/licenses/LICENSE-2.0 )
// +----------------------------------------------------------------------
// | Author: liu21st <liu21st@gmail.com>
// +----------------------------------------------------------------------

##用户登录
\think\Route::post('user','api/User/login');
##用户注册
\think\Route::post('user/register','api/User/register');
##用户上传头像
\think\Route::post('user/icon','api/User/upload_head_img');
##用户修改密码
\think\Route::post('user/change_pwd','api/User/change_pwd');
##用户找回密码
\think\Route::get('user/find_pwd','api/User/find_pwd');
##用户绑定手机号/邮箱
\think\Route::post('user/bind_username','api/User/bind_username');
##修改用户昵称
\think\Route::post('user/nickname','api/User/nickname');




##获取验证码
\think\Route::get('code/:time/:token/:username/:is_exist','api/Code/get_code');



##用户中奖概率接口
\think\Route::get('price','api/Price/gaiLv');
