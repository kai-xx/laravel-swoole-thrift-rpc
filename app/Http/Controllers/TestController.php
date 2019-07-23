<?php


namespace App\Http\Controllers;


use App\Server\Rpc\Client;
use App\Server\Rpc\Server;
use Rpc\server\Test;

class TestController extends Controller
{
    public function index(){
        (new Client())->client();
        return Test::t();
    }
}