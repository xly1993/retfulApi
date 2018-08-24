<?php
/**
 * Created by PhpStorm.
 * User: xv
 * Date: 2018/8/20
 * Time: 15:16
 */

namespace app\api\model;
use think\Model;

class User extends Model
{
    /**
     * @param array $data 插入的值
     * @return false|int
     */
    public function insertData($data)
    {
        return User::save($data);
    }

    public  function selectList($data,$type=null)
    {
        if(empty($type)){
            return $this->select();
            die;
        }else{
            return $this->where($data)->find();
            die;
        }

    }

}