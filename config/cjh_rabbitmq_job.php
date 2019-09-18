<?php

return [
      'default' => env('CJHRABBITMQ_DRIVER', 'first'),
      'driver' => [
          'first' => [
              'host' =>  env('CJHRABBITMQ_HOST', '127.0.0.1'),
              'port' =>  env('CJHRABBITMQ_PORT', 5672),
              'vhost' => env('CJHRABBITMQ_VHOST', '/'),
              'username' => env('CJHRABBITMQ_LOGIN', 'guest'),
              'password' => env('CJHRABBITMQ_PASSWORD', 'guest'),


              'publish' => [
                 'default' => env('CJHRABBITMQ_MSG_DRIVER', '1'),
                 'driver' => [
                     '1' => [
                         'durable' => true,
                         'queue' => [
                            'durable' => true,
                            'name' => '1322423',
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

                     'delayed' => [
                         'delayed' => true,
                         'durable' => true,
                         'queue' => [
                             'durable' => true,
                             'name' => 'delayed',
                         ]
                     ],


                  ],
              ],

              'consume' => [
                   'default' => env('CJHRABBITMQ_QUEUE_DRIVER', 'first'),
                   'driver' => [
                       'first' => [
                           'max_count' => 5,
                           'durable' => true,
                           'consumer_tag' => '1322423',
                           'queue' => '1322423',
                           'timedelay'  => 5000,
                           'listener' => 'App\TestConsume',
                           'log_path' =>storage_path("logs/"  . "TestConsume.log"),
                           'arguments' => [
                                 'x-message-ttl' => 100000,
                                 'x-expires'     => 100000,
                                 'x-max-length'  => 10000,
                           ],


                        ],
                    ],
              ],
          ]
      ],
];
