<?php namespace Zara4\LazyMySql;

use Illuminate\Database\Eloquent\Model;


class ServiceProvider extends \Illuminate\Support\ServiceProvider {

  /**
   * Bootstrap the application events.
   *
   * @return void
   */
  public function boot() {
    Model::setConnectionResolver($this->app['db']);
    Model::setEventDispatcher($this->app['events']);
  }


  /**
   * Register the service provider.
   *
   * @return void
   */
  public function register() {

    // Add database driver.
    $this->app->resolving('db', function ($db) {
      $db->extend('lazy-mysql', function ($config, $name) {
        $config['name'] = $name;
        return new Connection($config);
      });
    });


    // Add connector for queue support.
    $this->app->resolving('queue', function ($queue) {
      $queue->addConnector('lazy-mysql', function () {
        return new Connector($this->app['db']);
      });
    });

  }


}