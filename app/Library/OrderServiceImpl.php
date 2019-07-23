<?php


namespace App\Library;


use Rpc\server\OrderServiceIf;
use Rpc\struct\Response;

class OrderServiceImpl implements OrderServiceIf
{
    public function add($params)
    {
//        $arr = json_decode($params, true);
        $response = new Response();
        $response->code = 200;
        $response->message = "order-add-success";
        $response->data = $params;
        app('log')->info("method为" . __METHOD__ . ",数据为：", json_decode($params, true));
        return $response;
    }
    public function index($params)
    {
//        $arr = json_decode($params, true);
        $response = new Response();
        $response->code = 200;
        $response->message = "order-index-success";
        $response->data = $params;
        app('log')->info("method为" . __METHOD__ . ",数据为：" , json_decode($params, true));

        return $response;
    }
    public function update($params)
    {
//        $arr = json_decode($params, true);
        $response = new Response();
        $response->code = 200;
        $response->message = "order-update-success";
        $response->data = $params;
        app('log')->info("method为" . __METHOD__ . ",数据为：", json_decode($params, true));
        return $response;
    }
}