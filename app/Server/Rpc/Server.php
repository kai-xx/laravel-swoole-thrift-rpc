<?php


namespace App\Server\Rpc;


use App\Library\OrderServiceImpl;
use Rpc\server\OrderServiceProcessor;
use SwooleThrift\TSwooleServer;
use SwooleThrift\TSwooleServerTransport;
use Thrift\Exception\TException;
use Thrift\Factory\TBinaryProtocolFactory;
use Thrift\Factory\TTransportFactory;
use Thrift\TMultiplexedProcessor;

class Server
{
    public function server(){
        $hosts = "192.168.10.10";
        $port = 9999;
        try{
            $tThrift = new TTransportFactory();
            $bThrift = new TBinaryProtocolFactory();
            $processor = new TMultiplexedProcessor();

            $orderImpl = new OrderServiceImpl();
            $orderService = new OrderServiceProcessor($orderImpl);
            $processor->registerProcessor("OrderServiceIf", $orderService);

            $setting = [
                'daemonize' => false,
                'worker_num' => 2,
                'http_server_port' => 9998,
                'http_server_host' => $hosts,
                'log_file' => storage_path() . '/logs/swoole.log',
                'pid_file' => storage_path() . '/logs/thrift.pid',
            ];
            $socket = new TSwooleServerTransport($hosts, $port, $setting);
            $server = new TSwooleServer($processor, $socket, $tThrift, $tThrift, $bThrift, $bThrift);
            app('log')->info('服务连接成功 ', ['host' => sprintf("%s:%d", $hosts, $port), 'methodName' => __METHOD__]);

            $server->serve();
        }catch (TException $te){
            app('log')->error('服务连接失败 ', ['host' => sprintf("%s:%d", $hosts, $port), 'methodName' => __METHOD__, 'content' => $te->getMessage()]);
        }catch (\Exception $e){
            app('log')->error('服务连接失败 ', ['host' => sprintf("%s:%d", $hosts, $port), 'methodName' => __METHOD__, 'content' => $e->getMessage()]);
        }

    }
}