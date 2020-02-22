<?php
/**
 * Created by PhpStorm.
 * User: chenjiahao
 * Date: 2019-09-11
 * Time: 15:23
 */

require_once  dirname(__DIR__ ). '/vendor/autoload.php';
use CJHRabbitmq\MQJob;
use CJHRabbitmq\AbstractConsume;

$config = [
    'default' =>   'first' ,
    'driver' => [
        'first' => [
            'host' =>  '127.0.0.1',
            'port' =>   5672 ,
            'vhost' =>   '/' ,
            'username' =>   'guest' ,
            'password' =>   'guest' ,


            'publish' => [
                'default' =>  '1',
                'driver' => [
                    '1' => [
                        'durable' => true,
                        'delayed' => true,
                        'queue' => [

                            'name' => 'aaaaa423',
                        ]
                    ],


                    '2' => [
                        'durable' => true,
                        'exchange' => [
                            'name' => '22222',
                            'type' => 'direct',
                            'durable' => true,
                            'routing_key' => '2222',
                        ],
                        'queue' => [
                            'durable' => true,
                            'name' => '',
                        ]
                    ],

                    '3' => [

                        'durable' => true,
                        'queue' => [
                            'durable' => true,
                            'name' => '3333333333333333',
                        ]
                    ],


                ],

            ],

            'consume' => [
                'default' =>   'first' ,
                'driver' => [
                    'first' => [
                        'max_count' => 5,
                        'durable' => true,
                        'consumer_tag' => '1322423',
                        'queue' => 'aaaaa423',
                        'timedelay'  => 100000,
                        'listener' => 'Test',
                        /// 'log_path' =>storage_path("logs/"  . "TestConsume.log"),
                        'arguments' => [
                            //  'x-message-ttl' => 100000,

                            'x-max-length'  => 10000,
                        ],


                    ],
                ],
            ],
        ]
    ],
];



$job = new MQJob($config );

 //$job->send('ssdsdsdsdsdss');
//return ;
 for ($i = 0 ;$i < 1;$i++)
  $job->send('shdsdss',false );
///
///

$job->transaction(function ($a){

   // for ($i = 0 ;$i < 50000;$i++)
      ///  $a->send('ssdsdsdsdsdss');



});