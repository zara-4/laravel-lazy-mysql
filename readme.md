# Lazy MySQL

Implements a lazy MySQL database connection for Laravel that only connects to the individual `read` or `write` databases when they are actually used - by [Zara 4](http://zara4.com)
Only tested using Laravel 5.1

This assumes a set up where you have configured Laravel to use separate `read` and `write` MySQL database connections; and the write database is slow to connect, most likely due to a high latency connection caused by distance.

Zara 4 is a globally distributed [image compression](http://zara4.com) service, and currently uses the `lazy-mysql` database driver.

You can view our accompanying blog post for [Laravel Lazy Read/Write Database Connection](https://blog.zara4.com/laravel-lazy-readwrite-database-connection/)


## Introduction

If you are deploying your Laravel application globally on multiple servers across the world, you will likely encounter issues with database connection latency.
You can speed up `read` database queries using local MySQL database read replicas; however this does not overcome the delay caused by connecting to the `write` database if you have a single master `write` database in a remote location.

![Database Replication Structure](https://blog.zara4.com/wp-content/uploads/2017/05/lazy-mysql-replication-setup.png)

The standard Laravel MySQL database driver connects to both the `read` and `write` databases specified in your configuration as soon as the connection is first used.
The result is an unnecessary delay when you only want to read data from the database, Laravel still connects to the `write` database even though it isn't used.

Unlike the standard Laravel MySQL database driver, Lazy MySQL does not connect to the individual `read` or `write` databases until they are actually used by a query.
A request that only reads data from your database (SELECT) will only connect to the `read` database. A request that only writes data to the database (INSERT, UPDATE, DELETE) will only connect to the `write` database.




## Installation

## Download
To install the Lazy MySQL driver into your laravel application run:
```
composer require zara-4/laravel-lazy-mysql
```

## Enable Service Provider
Add the LazyMySql service provider to your application `config/app.php`

```php
'providers' => [
  // ...

  Zara4\LazyMySql\ServiceProvider::class,

  // ...
],

```



## Configuration

The `lazy-mysql` driver reads database configuration in exactly the same way as the standard Laravel MySQL database driver.
Simply change the driver from `mysql` to `lazy-mysql`

```php
'mysql' => [
  'read' => [
    'database'  => env('DB_READ_DATABASE', env('DB_DATABASE', 'default-database-name-goes-here')),
    'host'      => env('DB_READ_HOST', env('DB_HOST', 'default-read-database-host-goes-here')),
  ],
  'write' => [
    'database'  => env('DB_WRITE_DATABASE', env('DB_DATABASE', 'default-database-name-goes-here')),
    'host'      => env('DB_WRITE_HOST', env('DB_HOST', 'default-write-database-host-goes-here')),
    'options'   => [
      PDO::ATTR_PERSISTENT => true,
    ],
  ],
  'driver'    => 'lazy-mysql',
  'username'  => env('DB_USERNAME', 'default-username-goes-here'),
  'password'  => env('DB_PASSWORD', 'default-password-goes-here'),
  'port'      => env('DB_PORT', 3306),
  'charset'   => 'utf8',
  'collation' => 'utf8_unicode_ci',
  'prefix'    => '',
  'strict'    => false,
],
```


You can achieve significant additional speed improvements by ensuring the `write` connection is persistent.
To do this ensure the connection has the `PDO::ATTR_PERSISTENT => true` option (as in the example above)

```php
'options'   => [
  PDO::ATTR_PERSISTENT => true,
],
```



## Results
Using the standard Laravel MySQL driver with a local MySQL read replica database and a remote MySQL write master database, took in excess of `500ms` to connect and read a single record.

By enabling persistent connections with the `PDO::ATTR_PERSISTENT => true` option for the standard Laravel MySQL driver; the time to connect and read a single record was reduced to around `100ms` to `150ms`

By switching the database driver to `lazy-mysql`, the time to connect and read a single record was reduced to around `7ms` to `15ms`


The `lazy-mysql` database driver cannot overcome the delay caused when writing data to the remote master `write` database; however for requests that only ever read from the local read replica database, it can deliver significant performance improvements.



## License

Lazy MySQL is open source and free - licensed under MIT.
