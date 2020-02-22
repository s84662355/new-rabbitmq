<?php
/**
 * Created by PhpStorm.
 * User: chenjiahao
 * Date: 2019-09-11
 * Time: 15:22
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
                        'timedelay'  => 10000,
                        'listener' => 'Test',
                         'log_path' => "TestConsume.log" ,
                        'arguments' => [
                          //  'x-message-ttl' => 100000,

                            'x-max-length'  => 10000,
                        ],


                    ],

                    '2' => [
                        'max_count' => 5,
                        'durable' => true,
                        'consumer_tag' => '333333333',
                        'queue' => '3333333333333333',

                        'listener' => 'Test',
                        'log_path' => "TestConsume.log" ,
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


class Test extends AbstractConsume
{


    public function process_message( $body,$config)
    {
        echo $body;

        echo "sadsadsad";

        return AbstractConsume::ACK;
    }

}

$job = new MQJob($config );

$job->consume( );

