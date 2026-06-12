<?php

$databaseUrl = env('DATABASE_URL');
$databaseConfig = [];

if ($databaseUrl) {
    $parsedUrl = parse_url($databaseUrl);

    if ($parsedUrl) {
        $driver = $parsedUrl['scheme'] ?? 'sqlite';

        if ($driver === 'postgres') {
            $driver = 'pgsql';
        }

        $databaseConfig = [
            'driver' => $driver,
            'host' => $parsedUrl['host'] ?? null,
            'port' => $parsedUrl['port'] ?? null,
            'database' => isset($parsedUrl['path']) ? ltrim($parsedUrl['path'], '/') : null,
            'username' => $parsedUrl['user'] ?? null,
            'password' => $parsedUrl['pass'] ?? null,
        ];
    }
}

$autoDriver = 'sqlite';
if (!$databaseConfig && env('MYSQLHOST')) {
    $autoDriver = 'mysql';
} elseif (!$databaseConfig && env('PGHOST')) {
    $autoDriver = 'pgsql';
}

return [
    'default' => env('DB_CONNECTION', $databaseConfig['driver'] ?? $autoDriver),

    /*
    |--------------------------------------------------------------------------
    | Default Database Connection Name
    |--------------------------------------------------------------------------
    |
    | Here you may specify which of the database connections below you wish
    | to use as your default connection for all database work. Of course
    | you may use many connections at once using the Database library.
    |
    */

    /*
    |--------------------------------------------------------------------------
    | Database Connections
    |--------------------------------------------------------------------------
    |
    | Here are each of the database connections setup for your application.
    | Of course, examples of configuring each database platform that is
    | supported by Laravel is shown below to make development simple.
    |
    |
    | All database work in Laravel is done through the PHP PDO facilities
    | so make sure you have the driver for your particular database of
    | choice installed on your machine before you begin development.
    |
    */

    'connections' => [

        'sqlite' => [
            'driver' => 'sqlite',
            'database' => env('DB_DATABASE', database_path('database.sqlite')),
            'prefix' => '',
        ],

        'mysql' => [
            'driver' => 'mysql',
            'host' => env('DB_HOST', env('MYSQLHOST', $databaseConfig['host'] ?? '127.0.0.1')),
            'port' => env('DB_PORT', env('MYSQLPORT', $databaseConfig['port'] ?? '3306')),
            'database' => env('DB_DATABASE', env('MYSQLDATABASE', $databaseConfig['database'] ?? 'forge')),
            'username' => env('DB_USERNAME', env('MYSQLUSER', $databaseConfig['username'] ?? 'forge')),
            'password' => env('DB_PASSWORD', env('MYSQLPASSWORD', $databaseConfig['password'] ?? '')),
            'unix_socket' => env('DB_SOCKET', ''),
            'charset' => 'utf8mb4',
            'collation' => 'utf8mb4_unicode_ci',
            'prefix' => '',
            'strict' => true,
            'engine' => null,
        ],

        'pgsql' => [
            'driver' => 'pgsql',
            'host' => env('DB_HOST', env('PGHOST', $databaseConfig['host'] ?? '127.0.0.1')),
            'port' => env('DB_PORT', env('PGPORT', $databaseConfig['port'] ?? '5432')),
            'database' => env('DB_DATABASE', env('PGDATABASE', $databaseConfig['database'] ?? 'forge')),
            'username' => env('DB_USERNAME', env('PGUSER', $databaseConfig['username'] ?? 'forge')),
            'password' => env('DB_PASSWORD', env('PGPASSWORD', $databaseConfig['password'] ?? '')),
            'charset' => 'utf8',
            'prefix' => '',
            'schema' => 'public',
            'sslmode' => 'prefer',
        ],

        'sqlsrv' => [
            'driver' => 'sqlsrv',
            'host' => env('DB_HOST', 'localhost'),
            'port' => env('DB_PORT', '1433'),
            'database' => env('DB_DATABASE', 'forge'),
            'username' => env('DB_USERNAME', 'forge'),
            'password' => env('DB_PASSWORD', ''),
            'charset' => 'utf8',
            'prefix' => '',
        ],

    ],

    /*
    |--------------------------------------------------------------------------
    | Migration Repository Table
    |--------------------------------------------------------------------------
    |
    | This table keeps track of all the migrations that have already run for
    | your application. Using this information, we can determine which of
    | the migrations on disk haven't actually been run in the database.
    |
    */

    'migrations' => 'migrations',

    /*
    |--------------------------------------------------------------------------
    | Redis Databases
    |--------------------------------------------------------------------------
    |
    | Redis is an open source, fast, and advanced key-value store that also
    | provides a richer set of commands than a typical key-value systems
    | such as APC or Memcached. Laravel makes it easy to dig right in.
    |
    */

    'redis' => [

        'client' => 'predis',

        'default' => [
            'host' => env('REDIS_HOST', '127.0.0.1'),
            'password' => env('REDIS_PASSWORD', null),
            'port' => env('REDIS_PORT', 6379),
            'database' => 0,
        ],

    ],

];
