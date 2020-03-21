<?php
namespace CJHRabbitmq ;
use PhpAmqpLib\Message\AMQPMessage;

class MQMessage{

	private  $durable = true;
	private  $amqp_msg= null;
    private  $body = [];
    private  $routing_key = '';
    private  $exchange = '';
    private  $queue = '';
    private  $config = [
        'content_type' => 'text/plain',
        'delivery_mode'=> AMQPMessage::DELIVERY_MODE_PERSISTENT,
    ];

    private  $str = 'abcsfsklfj345678843UBGYVCRDXZAQXWSCEDRVFf';

    /*
     expiration
    */
	public function __construct(string $body,array $config = [])
	{
		$this->body = $body;

        $this->iniConfig( $config );

        $data = [
            'body' => base64_encode($body) ,
            'config' => $config,
            'message_id' => $this->config['message_id']
        ];
 
		$this->amqp_msg = new AMQPMessage(json_encode($data,JSON_UNESCAPED_UNICODE),$this->config);
	}

    private function iniConfig(array $config = [])
    {
        ###持久化
        $this->config['delivery_mode'] = AMQPMessage::DELIVERY_MODE_PERSISTENT;

        if(empty($config['message_id'])){
            $this->config['message_id'] =  date('Ymdhis'). md5(rand(10000,100000).uniqid().str_shuffle($this->str)) ;
        }else{
            $this->config['message_id']  = $config['message_id'];
        }


        if(!empty($config['exchange'])){
            $this->exchange = $config['exchange'];
        }


        if(!empty($config['routing_key'])){
            $this->routing_key = $config['routing_key'];
        } 

        if(!empty($config['queue'])){
            $this->queue = $config['queue'];
        }
    }



	public function getDurable()
	{
		return $this->durable;
	} 

    public function getAmqpMsg()
    {
    	return $this->amqp_msg;
    }

    public function getBody()
    {
    	return $this->body;
    }
    
    public function getRoutingKey()
    {
        return !empty($this->routing_key) ?  $this->routing_key : $this->queue   ;
    }

    public function getExchange()
    {
    	return $this->exchange;
    }

}
