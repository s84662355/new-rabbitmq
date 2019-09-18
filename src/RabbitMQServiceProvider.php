<?php
/**
 * Created by PhpStorm.
 * User: chenjiahao
 * Date: 2019-05-29
 * Time: 09:54
 */

namespace CJHRabbitmq;

use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Blade;
use Illuminate\Support\ServiceProvider;
use CJHRabbitmq\MQJob;


class RabbitMQServiceProvider extends ServiceProvider
{
    /**
     * @var array
     */
    protected $commands = [
         RabbitMQCommand::class,
    ];


    public function boot()
    {
        if ($this->app->runningInConsole()) {
            $this->publishes([__DIR__ . '/../config' => config_path()], 'CJHRabbitMQ-config');
        }


        $this->app->singleton(
            'CJHRabbitMQJob',
            function (){
                $job = new MQJob(config('cjh_rabbitmq_job'));
                $job ->setRedis(app('redis'));
                return $job ;
            }
        );
    }



    /**
     * Register the service provider.
     *
     * @return void
     */
    public function register()
    {
        $this->commands($this->commands);
    }

}
