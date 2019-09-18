- 参考文档

    - Thrift
    
        [Apache Thrift系列详解(一) - 概述与入门](https://juejin.im/post/5b290dbf6fb9a00e5c5f7aaa)

        [Apache Thrift系列详解(二) - 网络服务模型](https://juejin.im/post/5b290e225188252d9548fe15)

        [Apache Thrift系列详解(三) - 序列化机制](https://juejin.im/post/5b290e58518825748c1c6bfc)

    - php-thrift-swoole
    
        [php-thrift-swoole](https://packagist.org/packages/panus/php-thrift-swoole)

- 项目地址
````shell script
https://github.com/kai-xx/laravel-swoole-thrift-rpc.git
````
- 安装环境 
````shell script
1 Thrift
2 swoole
````
- 使用composer创建Laravel项目
````shell script
composer create-project --prefer-dist laravel/laravel blog
````
- 安装依赖包
````shell script
composer require panus/php-thrift-swoole
````
- 创建RPC目录
````
├── gen-php
│   └── Rpc
│       ├── server
│       └── struct
├── server
│   └── Order.thrift
└── struct
    └── Response.thrift
    #注意： 其中gen-php是thrift编译完之后的信息
````

- 创建Response.thrift
````
namespace php Rpc.struct  # php 表示编译语言  rpc.struct编译成.php文件后的命名空间
struct Response {
    1: i32 code;
    2: string message;
    3: string data;
}
````
- 创建Order.thrift
````
namespace php Rpc.server  # php 表示编译语言  Rpc.server编译成.php文件后的命名空间
include '../struct/Response.thrift'
service OrderService{
    Response.Response add(1: string params);
    Response.Response index(1: string params);
    Response.Response update(1: string params);
}
````
- thrift编译rpc目录下所有.thrift后缀文件
````shell script
cd rpc
thrift --gen php:server server/User.thrift
#注： 上边命令是基于当前目录生成的，如果想自定义可以执行如下命令
thrift -r -out ./xxxx/xxx --gen php:server server/User.thrift
````
- 在app/Library中创建OrderServiceImpl.php文件，实现order.thrift中逻辑，实际需要实现依据order.thrift编译生成的\Rpc\server\OrderServiceIf接口
```php
<?php
//路径为app/Library/OrderServiceImpl.php
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
````
- 在app/Library/Kernel.php中加入OrderServiceImpl对象
```php
<?php
namespace App\Library;
class Kernel
{
    private $impl = [
        \App\Library\OrderServiceImpl::class,
        \App\Library\UserServiceImpl::class
    ];
    public function getImpl(){
        return $this->impl;
    }
}
```


- 创建RPC服务，Server.php
```php
<?php
//路径为app/Server/Rpc/Server.php
namespace App\Server\Rpc;
use App\Library\OrderServiceImpl;
use Rpc\server\OrderServiceProcessor;
use SwooleThrift\TSwooleServer;
use SwooleThrift\TSwooleServerTransport;
use Thrift\Exception\TException;
use Thrift\Factory\TBinaryProtocolFactory;
use Thrift\Factory\TTransportFactory;
use Thrift\TMultiplexedProcessor;
use App\Library\Kernel;
class Server
{
    /**
    *swoole 多进程
    **/
    public function server(){
        $hosts = "192.168.10.10";  // 服务端对外IP地址
        $port = 9999;// 服务端对外端口
        try{
            $tThrift = new TTransportFactory();
            $bThrift = new TBinaryProtocolFactory();
            $processor = new TMultiplexedProcessor();
            // -------------------服务注册--------------------------//
            foreach ($kernel->getImpl() as $value){
                $reflexImpl = new \ReflectionClass($value);
                $impl = $reflexImpl->newInstance();
                $reflexName = $reflexImpl->getShortName();
                $str = substr($reflexName, 0, -4);
                $reflexService = new \ReflectionClass("\Rpc\server\\" . $str . "Processor");
                $service = $reflexService->newInstanceArgs([$impl]);
                $processor->registerProcessor($str . "If", $service); // 注意：OrderServiceIf -- servername需和客户端一致
            }
            // -------------------服务注册--------------------------//
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
````
- 创建客户端，Client.php
````php
<?php
//路径为app/Server/Rpc/Client.php
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
    private function __clone(){}
    public function __destruct()
    {
        self::$transport->close();
    }
}
````
- 创建rpc命令行文件，可使用 php artisan make:command RpcServer 创建
````php
<?php
//路径为app/Console/Commands/RpcServer.php
namespace App\Console\Commands;
use App\Server\Rpc\Server;
use Illuminate\Console\Command;
class RpcServer extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'server:rpc';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = '开始RPC';
    private static $server;
    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct(Server $server)
    {
        parent::__construct();
        self::$server = $server;
    }

    /**
     * Execute the console command.
     *
     * @return mixed
     */
    public function handle()
    {
        //
        self::$server->server();
    }
}
````
- 注册rpc到php artisan 
````php
protected $commands = [
    //
    RpcServer::class
];
````
- 创建可执行controller，TestController.php
```php
<?php
//路径为app/Http/Controllers/TestController.php
namespace App\Http\Controllers;
use App\Server\Rpc\Client;
use App\Server\Rpc\Server;
use Rpc\server\Test;

class TestController extends Controller
{
    public function index(){
        $client = new Client();
        $order = $client->client(OrderServiceIf::class);
        print_r($order->add(json_encode(["add",1,3,4])));
        print_r($order->index(json_encode(["index",1,3,4])));
        print_r($order->update(json_encode(["update",1,3,4])));
    }
}
````
- 在routes/web.php添加路由
````php
Route::get('/test/index', 'TestController@index');
````
- 在窗口A中，项目根目录下执行如下命令，保持窗口开启状态
````
php artisan server:rpc
````
- 在窗口B中，相对项目根目录的Public下执行如下命令，开启web服务，或者通过Nginx配置
````
php -S xx.xx.xx.xx:xxxx
````
- 请求路由，观察log日志，出现如下信息表示服务搭建成功
````shell script
[2019-07-23 21:21:27] local.INFO: 服务连接成功  {"host":"192.168.10.10:9999","methodName":"App\\Server\\Rpc\\Server::server"} 
[2019-07-23 21:21:32] local.ERROR: 客户端-连接成功  {"host":"192.168.10.10:9999","methodName":"App\\Server\\Rpc\\Client::client"} 
[2019-07-23 21:21:32] local.INFO: method为App\Library\OrderServiceImpl::add,数据为： ["add",1,3,4] 
[2019-07-23 21:21:32] local.INFO: method为App\Library\OrderServiceImpl::index,数据为： ["index",1,3,4] 
[2019-07-23 21:21:32] local.INFO: method为App\Library\OrderServiceImpl::update,数据为： ["update",1,3,4] 
````

####    注意事项：
    1.  克隆项目之后记得composer install
    2.  新增rpc脚本后
        - 方法一： cd rpc && thrift --gen php:server server/User.thrift,然后再项目根目录下执行composer dumpautoload
        - 方法二： composer.json中已经加入处理脚本，直接执行 composer update 即可
