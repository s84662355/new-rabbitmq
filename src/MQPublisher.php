<?php  

namespace CJHRabbitmq;
use PhpAmqpLib\Channel\AbstractChannel;
use PhpAmqpLib\Channel\AMQPChannel;

use PhpAmqpLib\Message\AMQPMessage;

class MQPublisher{

	private $channel = null;

	private static $instance = null;

	private $confirm_ask = true;

	public function __construct( AMQPChannel $channel ,$confirm = true  )
	{
		$this->channel = $channel;

        $this->confirm = $confirm;

		if($confirm)
        {
            $this->channel->confirm_select();
            $this->setHandler();
        }
	}


	public function setHandler()
    {
        $this->channel->set_ack_handler([$this,'ack_handler']);

        $this->channel->set_nack_handler([$this,'nack_handler']);

        $this->channel->set_return_listener([$this,'set_return_listener']);
    }


    public function ack_handler(AMQPMessage $message)
    {
        $this->confirm_ask = true;

    }


    public function set_return_listener($replyCode, $replyText, $exchange, $routingKey, AMQPMessage $message)
    {
         throw new MQException("routingKey : {$routingKey} 发送失败");
    }


    public function nack_handler(AMQPMessage $message)
    {
        throw new MQException('rabbitmq 发送失败');
    }


    public function getChannel() :  AMQPChannel
    {
         return $this->channel;
    }

	public function send(MQMessage $msg)
    {
        if($this->confirm)
        {
            $this->channel->basic_publish($msg->getAmqpMsg(), $msg->getExchange(), $msg->getRoutingKey(),true);
            $this->channel->wait_for_pending_acks_returns();
        }else{

            $this->channel->basic_publish($msg->getAmqpMsg(), $msg->getExchange(), $msg->getRoutingKey());
        }
    }

}
