<?php
/**
 * Created by PhpStorm.
 * User: xv
 * Date: 2018/8/24
 * Time: 9:43
 */

namespace app\api\controller;


class Price extends Base
{
    /**
     * 确定用户是否中奖
     */
    public function gaiLv()
    {
        /**********接收参数***********/
        $data = $this->params;
        $data['time'] = date("Y-m-d");
        /*********检查中奖规则******/
        $this->check_price($data);

        $prize_arr = array(
            '0' => array('id'=>1,'prize'=>'小牛杯','v'=>500),
            '1' => array('id'=>2,'prize'=>'音乐盒','v'=>300),
            '2' => array('id'=>3,'prize'=>'优酷vip','v'=>1000),
            '3' => array('id'=>4,'prize'=>'谢谢参与','v'=>1000),

        );
        foreach ($prize_arr as $key => $val) {
            $arr[$val['id']] = $val['v'];
        }

        $rid = $this->get_rand($arr); //根据概率获取奖项id

        $res['yes'] = $prize_arr[$rid-1]['prize']; //中奖项
        unset($prize_arr[$rid-1]); //将中奖项从数组中剔除，剩下未中奖项
        shuffle($prize_arr); //打乱数组顺序
        for($i=0;$i<count($prize_arr);$i++){
            $pr[] = $prize_arr[$i]['prize'];
        }
        $res['no'] = $pr;
        if($res['yes']!=='没准下次就能中哦')
        {
            /********随机生成一个领取码*********/
            $data['time'] = date("Y-m-d");
            $data['code']  = uniqid();
            $data['prize'] = $res['yes'];
            $where['code'] = $data['code'];
            /********检查数据库是否存在该code***/
            $db_code = db('prize')->where($where)->find();
            if(!empty($db_code)){
                $data['code'] = 'code'.$data['code'];
            }
            /*********中奖更新到数据库*********/
            $db_res = db('prize')->insert($data);
            if($db_res){
                $this->return_msg(200,'恭喜您中奖',$data);
            }else{
                $this->return_msg(300,'谢谢惠顾');
            }
        }else{
            $data['code'] = 0;
            db('prize')->insert($data);
            $this->return_msg(300,'谢谢参与');
        }
    }
    /***
     * 根据概率获取奖项id
     * @param $proArr
     * @return int|string
     */
    private function  get_rand($proArr)
    {
        $result = '';
        //概率数组的总概率精度
        $proSum = array_sum($proArr);

        //概率数组循环
        foreach ($proArr as $key => $proCur)
        {
            $randNum = mt_rand(1, $proSum);
            if ($randNum <= $proCur){
                $result = $key;
                break;
            } else {
                $proSum -= $proCur;
            }
        }
        unset ($proArr);
        return $result;
    }
    /**
     * 中奖规则刷选
     */
    private function check_price($data)
    {
       /***********判断是否已经中过奖***********/
        $db_res = db('price')->where($data)->find();
        if($db_res['code']!==0){
            $this->return_msg(300,'谢谢参与');
        }
        /***********判断当天是否已经抽过***********/
        $data['time'] = date("Y-m-d");
        $data['code'] = 0;
        $res = db('prize')->where($data)->find();
        if(!empty($res)){
            $this->return_msg(300,'谢谢参与');
        }
    }
}