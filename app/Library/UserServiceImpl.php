<?php


namespace App\Library;


use Rpc\server\UserServiceIf;
use Rpc\struct\Response;

class UserServiceImpl implements UserServiceIf
{
    public function uadd($params)
    {
//        $arr = json_decode($params, true);
        $response = new Response();
        $response->code = 200;
        $response->message = "user-add-success";
        $response->data = $params;
        app('log')->info("method为" . __METHOD__ . ",数据为：", json_decode($params, true));
        return $response;
    }
    public function uindex($params)
    {
//        $arr = json_decode($params, true);
        $response = new Response();
        $response->code = 200;
        $response->message = "user-index-success";
        $response->data = $params;
        app('log')->info("method为" . __METHOD__ . ",数据为：" , json_decode($params, true));

        return $response;
    }
    public function uupdate($params)
    {
//        $arr = json_decode($params, true);
        $response = new Response();
        $response->code = 200;
        $response->message = "user-update-success";
        $response->data = $params;
        app('log')->info("method为" . __METHOD__ . ",数据为：", json_decode($params, true));
        return $response;
    }
}