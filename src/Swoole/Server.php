<?php
/**
 * Created by PhpStorm.
 * User: tanszhe
 * Date: 2018/8/24
 * Time: 上午11:08
 */

namespace One\Swoole;

use One\Facades\Log;
use One\Protocol\ProtocolAbstract;

/**
 * Class Server
 * @package One\Swoole
 * @mixin OneServer
 */
class Server
{
    protected $conf = [];

    public $worker_id = 0;
    public $worker_pid = 0;
    public $is_task = false;

    /**
     * @var AsyncClient
     */
    public $globalData = null;


    /**
     * @var ProtocolAbstract
     */
    protected $protocol = null;

    /**
     * @var \swoole_websocket_server
     */
    protected $server = null;

    public function __construct(\swoole_server $server, array $conf)
    {
        $this->server = $server;
        $this->conf = $conf;
        if (isset($conf['protocol'])) {
            $this->protocol = $conf['protocol'];
        }
    }

    public function onStart(\swoole_server $server)
    {
    }

    public function onShutdown(\swoole_server $server)
    {

    }

    public function onWorkerStart(\swoole_server $server, $worker_id)
    {
        $this->worker_id = $worker_id;
        $this->worker_pid = $server->worker_pid;
        $this->is_task = $server->taskworker ? true : false;
        $this->globalData = $this->globalData();
        swoole_set_process_name(($this->is_task ? 'one_task':'one_worker').'_'.$worker_id);

    }

    public function onWorkerStop(\swoole_server $server, $worker_id)
    {
    }

    public function onWorkerExit(\swoole_server $server, $worker_id)
    {
    }

    public function onConnect(\swoole_server $server, $fd, $reactor_id)
    {

    }

    public function __receive(\swoole_server $server, $fd, $reactor_id, $data)
    {
        if ($this->protocol) {
            $data = $this->protocol::decode($data);
        }
        $this->onReceive($server, $fd, $reactor_id, $data);
    }

    public function send($fd, $data, $from_id = 0)
    {
        if ($this->protocol) {
            $data = $this->protocol::encode($data);
        }
        $this->server->send($fd, $data, $from_id);
    }

    public function onReceive(\swoole_server $server, $fd, $reactor_id, $data)
    {

    }

    public function onPacket(\swoole_server $server, $data, array $client_info)
    {
    }

    public function onClose(\swoole_server $server, $fd, $reactor_id)
    {
        if ($this->globalData && $this->globalData->connected === 1) {
            $this->unBindFd($fd);
        }
        Log::flushTraceId();
    }

    public function onBufferFull(\swoole_server $server, $fd)
    {
    }

    public function onBufferEmpty(\swoole_server $server, $fd)
    {

    }

    public function onTask(\swoole_server $server, $task_id, $src_worker_id, $data)
    {

    }

    public function onFinish(\swoole_server $server, $task_id, $data)
    {
    }

    public function onPipeMessage(\swoole_server $server, $src_worker_id, $message)
    {

    }

    public function onWorkerError(\swoole_server $server, $worker_id, $worker_pid, $exit_code, $signal)
    {

    }

    public function onManagerStart(\swoole_server $server)
    {
        swoole_set_process_name('one_manager');
    }

    public function onManagerStop(\swoole_server $server)
    {

    }

    public function __call($name, $arguments)
    {
        if (method_exists(OneServer::getServer(), $name)) {
            return OneServer::getServer()->$name(...$arguments);
        } else if(method_exists($this->server, $name)){
            return $this->server->$name(...$arguments);
        } else {
            throw new \Exception('方法不存在:' . $name);
        }

    }
}