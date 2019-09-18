<?php

namespace CJHRabbitmq;
/*
 *    basicAck：成功消费，消息从队列中删除
   basicNack：requeue=true，消息重新进入队列，false被删除
   basicReject：等同于basicNack
   basicRecover：消息重入队列，requeue=true，发送给新的consumer，false发送给相同的consumer
 * */

abstract class AbstractConsume{

    public $message_id ;
    public $tries = 5;

    public $possible_repeat = false;

	const ACK = 200;
	const REJECT = 300;
	const CANCEL = 400;
	const REJECT_OUT = 500;
	const RECOVERTRUE = 600;
	const RECOVERFALSE = 700;


	abstract public function process_message( $body,$config) ;
}
