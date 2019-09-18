# cjh-rabbitmq


composer require chenjiahao/new-rabbitmq

在配置文件app.php加入


    'providers' => [
        
         CJHRabbitmq\RabbitMQServiceProvider::class,
         .
         .
         ..
         .
    ],


php artisan vendor:publish 
选择
CJHRabbitmq\RabbitMQServiceProvider

 

设置进程名称eeeee
 php artisan CJHRabbitMQCommand eeeee


 app('CJHRabbitMQJob')->send('sfs');