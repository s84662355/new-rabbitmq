<?php
/**
 * Created by PhpStorm.
 * User: chenjiahao
 * Date: 2019-08-23
 * Time: 16:35
 */

namespace CJHRabbitmq;
use PhpAmqpLib\Channel\AMQPChannel;
use PhpAmqpLib\Message\AMQPMessage;

class TxPublisher
{
    private $mq_driver = null;

    public function __construct(MQDriver &$mq_driver){
        $this->position = 0;
        $this->mq_driver = $mq_driver;
    }

    public function send(string  $body , $msg_driver_name = false){

        $this->mq_driver->tx_send($body,$msg_driver_name);

    }
}