<?php
/**
 * Created by PhpStorm.
 * User: admin
 * Date: 2018/10/23
 * Time: 10:07
 */

namespace One\Swoole;


trait bindName
{
    /**
     * @var GlobalData|AsyncClient
     */
    protected $globalData = null;

    /**
     * @return null|AsyncClient
     */
    public function globalData()
    {
        if ($this->globalData === null) {
            if (isset(self::$conf['server']['global_data'])) {
                OneClient::setConfig(self::$conf['server']['global_data']);
                $this->globalData = OneClient::start();
            }
        }
        return $this->globalData;
    }

    public function set($key, $val, $time = 0)
    {
        return $this->globalData->set($key, $val, $time);
    }

    public function get($key)
    {
        return $this->globalData->get($key);
    }


    public function del($key)
    {
        return $this->globalData->del($key);
    }


    public function getAndDel($key)
    {
        return $this->globalData->getAndDel($key);
    }


    /**
     * 给fd绑定别名
     * @param $fd
     */
    public function bindName($fd, $name)
    {
        if ($this->globalData->connected !== 1) {
            echo 'WARN global server can not connect';
        }
        $this->globalData->bindName($fd, $name);
    }

    /**
     * 解除绑定
     * @param $fd
     */
    public function unBindFd($fd)
    {
        if ($this->globalData->connected !== 1) {
            echo 'WARN global server can not connect';
        }
        $this->globalData->unBindFd($fd);
    }

    /**
     * 解除绑定
     * @param $name
     */

    public function unBindName($name)
    {
        if ($this->globalData->connected !== 1) {
            echo 'WARN global server can not connect';
        }
        $this->globalData->unBindName($name);
    }

    /**
     * @param $name
     * @return array
     */
    public function getFdByName($name)
    {
        if ($this->globalData->connected !== 1) {
            echo 'WARN global server can not connect';
        }
        return $this->globalData->getFdByName($name);
    }


    public function sendByName($name, $data)
    {
        return $this->sendInfoByName($name, $data, 'send');
    }

    public function pushByName($name, $data)
    {
        return $this->sendInfoByName($name, $data, 'push');
    }

    public function sendOrPushByName($name, $data)
    {
        $fds = $this->getFdByName($name);
        if (!$fds) {
            return false;
        }
        foreach ($fds as $fd) {
            if ($this->exist($fd)) {
                $info = $this->getClientInfo($fd);
                if (isset($info['websocket_status'])) {
                    $this->push($fd, $data);
                } else if ($info) {
                    $this->send($fd, $data);
                }
            }
        }
    }

    private function sendInfoByName($name, $data, $action)
    {
        $fds = $this->getFdByName($name);
        if (!$fds) {
            return false;
        }
        foreach ($fds as $fd) {
            if ($this->exist($fd)) {
                return $this->$action($fd, $data);
            }
        }
    }

}