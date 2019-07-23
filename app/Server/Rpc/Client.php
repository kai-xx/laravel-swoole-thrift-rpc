<?php


namespace App\Server\Rpc;


use Rpc\server\OrderServiceClient;
use Thrift\Exception\TTransportException;
use Thrift\Protocol\TMultiplexedProtocol;

class Client
{
    private static $transport;
    public function client()
    {

        $host = "192.168.10.10";
        $port = 9999;
        try{
            $socket = new \Thrift\Transport\TSocket($host, $port);
            $socket->setRecvTimeout(3000);
            $socket->setDebug(true);
            self::$transport = new \Thrift\Transport\TFramedTransport($socket, 1024, 1024);
            $protocol = new \Thrift\Protocol\TBinaryProtocol(self::$transport, false, true);
            self::$transport->open();
            app('log')->error('客户端-连接成功 ', ['host' => sprintf("%s:%d", $host, $port), 'methodName' => __METHOD__]);

            $thriftProtocol = new TMultiplexedProtocol($protocol, "OrderServiceIf");
            $client = new OrderServiceClient($thriftProtocol);
            $client->add(json_encode(["add",1,3,4]));
            $client->index(json_encode(["index",1,3,4]));
            $client->update(json_encode(["update",1,3,4]));
        }catch (TTransportException $te){
            app('log')->error('客户端-连接失败 ', ['host' => sprintf("%s:%d", $host, $port), 'methodName' => __METHOD__, 'content' => $te->getMessage()]);
        }catch (\Exception $e){
            app('log')->error('客户端-连接失败 ', ['host' => sprintf("%s:%d", $host, $port), 'methodName' => __METHOD__, 'content' => $e->getMessage()]);
        }

    }

    public function __destruct()
    {
        self::$transport->close();
    }
}