<?php
/**
 * Created by PhpStorm.
 * User: chenjiahao
 * Date: 2019-09-09
 * Time: 10:09
 */

namespace CJHRabbitmq ;
use PhpAmqpLib\Channel\AbstractChannel;
use PhpAmqpLib\Connection\AMQPStreamConnection;
use Closure;
use Throwable;
use PhpAmqpLib\Wire\AMQPTable;

class MQDriver
{
    protected $connection = null;

    protected $exchange_pool = [];

    protected $queue_pool = [];

    protected $redis = null;

    protected $connection_name = '';

    protected $config = null;

    protected $channel = null;

    protected $publisher_instance = [];

    protected $publish_driver_config = [];

    protected $default_publish ;

    protected $consume_driver_config = [];

    protected $default_consume ;

    public function __construct(array $config)
    {
        $this->connection = new AMQPStreamConnection($config['host'], $config['port'], $config['username'], $config['password'], $config['vhost']);

        $this->connection_name = $config['host'] . $config['port'] . $config['vhost'];

        $this->config = $config;

        $this->channel = $this->connection->channel();

        $this->publish_driver_config = $config['publish']['driver'];

        $this->default_publish = $config['publish']['default'];

        $this->consume_driver_config = $config['consume']['driver'];

        $this->default_consume = $config['consume']['default'];

    }


    public function exchange(string $name, $type = 'direct', $durable = true)
    {
        if (empty($this->exchange_pool[$name])) {
            $this->channel->exchange_declare($name, $type, false, $durable, false);
            $this->exchange_pool[$name] = true;
        }
        return $this;
    }

    public function queue(string $name,  $durable = true , array $config = [])
    {
        if (empty($this->queue_pool[$name])) {


            $table = false;
            if (!empty($config)) {
                $table = new AMQPTable();
                foreach ($config as $key => $value) {
                    $table->set($key, $value);
                }
            }

            if ($table) {
                $this->channel->queue_declare(
                    $name,
                    false,
                    $durable,
                    false,
                    false,
                    false,
                    $table);
            } else {
                $this->channel->queue_declare(
                    $name,
                    false,
                    $durable,
                    false,
                    false,
                    false);
            }
            $this->queue_pool[$name] = true;
        }
        return $this;

    }

    public function cache_queue(string $name,  $durable,string  $dead_ex, string  $dead_key,int $expires)
    {


        $table = [
            'x-dead-letter-exchange' => $dead_ex,
            'x-dead-letter-routing-key' => $dead_key,
            'x-message-ttl' => intval( $expires) ,
        ];

        return  $this->queue($name , $durable, $table);
    }

    public function QueueBind(string $queue, string $exchange, string $routing_key)
    {
        $this->channel->queue_bind($queue, $exchange, $routing_key);
        return $this;
    }

    public function setRedis($redis)
    {
        $this->redis = $redis;
    }

    public function getChannel() : AbstractChannel
    {
        return $this->channel;
    }

    public function __destruct()
    {
        $this->channel->close();
        $this->connection->close();
    }


    protected function publisher(string $type): MQPublisher
    {
        switch ($type) {
            case 'confirm':
                return $this->getPublisher($type);
            case 'transaction':
                return $this->getPublisher($type, false);
            case 'common' :
                return $this->getPublisher($type, false);
        }
    }

    public function getPublisher(string $type,$confirm = true) : MQPublisher
    {
        if (empty($this->publisher_instance[$type])) {
            $this->publisher_instance[$type] = new MQPublisher($this->connection->channel(),$confirm);
        }
        return $this->publisher_instance[$type];
    }

    public function send(string $body,  $msg_driver_name = false  ,  $confirm = true)
    {

        if($msg_driver_name ) {
            $config = $this->publish_driver_config[$msg_driver_name];
        }else{
            $config = $this->publish_driver_config[$this->default_publish];
        }


        if (  $confirm  )
            return $this->message($body, $config ,$this->publisher('confirm'));

        return $this->message($body, $config ,$this->publisher('common'));
    }


    public function transaction(Closure $callback)
    {
        $publisher = $this->publisher('transaction');
        try{
            $publisher->getChannel()->tx_select();
            $callback(new TxPublisher($this));
            $publisher->getChannel()->tx_commit();
        }catch (Throwable $throwable){
            $publisher->getChannel()->tx_rollback();
            throw  $throwable;
        }
    }

    public function tx_send(string $body, $msg_driver_name = false  )
    {
        if($msg_driver_name)
        {
            $config = $this->publish_driver_config[$msg_driver_name];
        }else{
            $config = $this->publish_driver_config[$this->default_publish];
        }

        return $this->message($body, $config ,$this->publisher('transaction'));


    }


    public function message(string $body, array $config , MQPublisher $publisher)
    {
        $msg_config = [];
        if(!empty($config['delayed']))
        {

             $msg_config['queue'] = 'cache_'.$config['queue']['name'];
        }else if(!empty($config['exchange'])){


            //// $this->exchange($config['exchange']['name'], $config['exchange']['type']  );


            #####################
           /// 交换机需要提前创建，代码里面不创建交换机
            #####################



            /*
            $this->QueueBind(
                $config['queue']['name'],
                $config['exchange']['name'],
                  $config['exchange']['routing_key']
            );
            */

            $msg_config = [
               'routing_key' => $config['exchange']['routing_key'],
                'exchange' => $config['exchange']['name'],
            ];


        }else if(!empty($config['queue'])){

            $msg_config = [
                'queue' => $config['queue']['name'],
            ];
        }
        return $publisher->send(new MQMessage($body,$msg_config));
    }



    public function consume($consume_config_name = false){
        if($consume_config_name )
        {
            $config = $this->consume_driver_config[$consume_config_name ];
        }else{
            $config = $this->consume_driver_config[$this->default_consume ];
        }

        $this->init_consume($config);

        $consume = new MQConsume(
            $this->channel,
            $config['queue'],
            empty($config['consumer_tag']) ? $config['listener'] :  $config['consumer_tag'] ,
            $config['listener']
        );


        if(empty($config['max_count'] )){
            $consume-> setRedis(
                $this->redis,
                $this->connection_name
            );
        }else{
            $consume-> setRedis(
                $this->redis,
                $this->connection_name,
                intval( $config['max_count'] )
            );
        }


        if(!empty($config['log_path'])){
            $consume->setLogPath($config['log_path']);
        }
        return $consume  ;
    }


    protected function init_consume(array  $config)
    {

        if(!empty($config['timedelay'])){

            $this->exchange(
                'dead-exchange',
                'direct' ,
                true);

            $this->cache_queue(
                'cache_'.$config['queue'],
                true ,
                'dead-exchange',
                'dead_'.$config['queue'].'_key',
                 intval($config['timedelay'])
            );

            $this->queue
            (
                $config['queue'],
                $config['durable'],
                $config['arguments']
            );

            $this->QueueBind(
                $config['queue'],
                'dead-exchange',
                'dead_'.$config['queue'].'_key'
            );
        }else{
            $this->queue
            (
                $config['queue'],
                $config['durable'],
                $config['arguments']
            );

            if(!empty($config['exchange'])){
                $this->QueueBind(
                    $config['queue'],
                    $config['exchange'],
                    $config['queue']
                );
            }
        }

    }

}