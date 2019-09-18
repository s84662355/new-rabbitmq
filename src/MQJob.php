<?php
/**
 * Created by PhpStorm.
 * User: chenjiahao
 * Date: 2019-09-09
 * Time: 14:19
 */

namespace CJHRabbitmq ;
use Closure ;
use Throwable;
class MQJob
{

    private $config_pool = [];
    private $factory_pool = [];
    private $default_driver = '';
    private $select_driver = '';
    private $config = [];
    private $redis = null;
    private $retry_count = 0;

    public function __construct(array $config )
    {
        $this->config = $config;
        $this->default_driver = $config['default'];//config('rabbitmq_job.driver.default');
        $this->select_driver = $this->default_driver;
    }

    public function setRedis($redis)
    {
        $this->redis = $redis;
        return $this;
    }

    ###切换连接
    public function select($driver = false) :  MQJob
    {
        $this->select_driver = $this->default_driver;
        if(!empty($driver )) $this->select_driver = $driver;
        $this->driver();
        return $this;
    }

    private function driver($driver = false) : MQDriver
    {
        if(!$driver)  $driver = $this->select_driver;
        if(empty($this->config_pool[$driver]))
        {
            $this->config_pool[$driver]  = $this->config['driver'][$driver];  ;//config('rabbitmq_job.driver.'.$driver);
            $this->factory_pool[$driver] = new MQDriver($this->config_pool[$driver]);
            $this->factory_pool[$driver]->setRedis($this->redis);
        }
        return  $this->factory_pool[$driver];
    }

    private function driver_config($driver = false) : array
    {
        if(!$driver) $driver = $this->select_driver;
        if(empty($this->config_pool[$driver]))$rabbit_driver =  $this->driver();
        return $this->config_pool[$driver];
    }


    ##$confirm true使用确认机制
    public function send(string $body,$msg_driver_name = false,$confirm = true ) : MQJob
    {
        $this
            ->driver()
            ->send(
                $body,
                $msg_driver_name,
                $confirm
            );

        return $this;
    }


    ##事务
    public function transaction(Closure $callback)
    {
       $this
           ->driver()
           ->transaction(
               $callback
           );
       return $this;
    }



    public function consume($consume_config_name = false)
    {
            $this
            ->driver()
            ->consume($consume_config_name )
            ->basic_consume();
    }


}