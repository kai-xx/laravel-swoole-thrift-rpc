<?php

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
