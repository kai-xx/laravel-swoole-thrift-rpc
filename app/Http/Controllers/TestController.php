<?php


namespace App\Http\Controllers;


use App\Server\Rpc\Client;
use Rpc\server\OrderServiceIf;
use Rpc\server\UserServiceIf;

class TestController extends Controller
{
    public function index(){
        $client = new Client();
        $order = $client->client(OrderServiceIf::class);
        print_r($order->add(json_encode(["add",1,3,4])));
        print_r($order->index(json_encode(["index",1,3,4])));
        print_r($order->update(json_encode(["update",1,3,4])));
    }

    public function user()
    {
        $client = new Client();
        $user = $client->client(UserServiceIf::class);
        print_r($user->uadd(json_encode(["add","张三"])));
        print_r($user->uindex(json_encode(["index","张三","张四"])));
        print_r($user->uupdate(json_encode(["update","驴得水"])));
    }
}