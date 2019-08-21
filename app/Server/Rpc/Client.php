<?php


namespace App\Server\Rpc;


use Thrift\Exception\TTransportException;
use Thrift\Protocol\TMultiplexedProtocol;

class Client
{
    private static $transport;
    private static $protocol;
    private function boot(){
        if (empty(self::$transport)){
            $host = "192.168.10.10";
            $port = 9999;
            try{
                $socket = new \Thrift\Transport\TSocket($host, $port);
                $socket->setRecvTimeout(3000);
                $socket->setDebug(true);
                self::$transport = new \Thrift\Transport\TFramedTransport($socket, 1024, 1024);
                self::$protocol = new \Thrift\Protocol\TBinaryProtocol(self::$transport, false, true);
                self::$transport->open();
            }catch (TTransportException $te){
                app('log')->error('客户端-连接失败 ', ['host' => sprintf("%s:%d", $host, $port), 'methodName' => __METHOD__, 'content' => $te->getMessage()]);
            }catch (\Exception $e){
                app('log')->error('客户端-连接失败 ', ['host' => sprintf("%s:%d", $host, $port), 'methodName' => __METHOD__, 'content' => $e->getMessage()]);
            }
        }
        return self::$transport;
    }

    /**
     * @param $service
     * @return mixed
     * @throws \ReflectionException
     */
    public function client($service)
    {
        // 初始化 客户端rpc连接
        $this->boot();
        $reflex = new \ReflectionClass($service);
        $reflexName = $reflex->getName();
        $serviceName = $reflex->getShortName();
        $className = substr($reflexName, 0, -2) . "Client";
        $thriftProtocol = new TMultiplexedProtocol(self::$protocol,$serviceName );
        return new $className($thriftProtocol);
    }
    private function __clone()
    {
    }

    public function __destruct()
    {
        self::$transport->close();
    }
}