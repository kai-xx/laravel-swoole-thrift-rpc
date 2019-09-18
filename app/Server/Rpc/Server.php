<?php


namespace App\Server\Rpc;


use App\Library\Kernel;
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
            $kernel = new Kernel();
            foreach ($kernel->getImpl() as $value){
                $reflexImpl = new \ReflectionClass($value);
                $impl = $reflexImpl->newInstance();
                $reflexName = $reflexImpl->getShortName();
                $str = substr($reflexName, 0, -4);
                $reflexService = new \ReflectionClass("\Rpc\server\\" . $str . "Processor");
                $service = $reflexService->newInstanceArgs([$impl]);
                $processor->registerProcessor($str . "If", $service);
            }
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
