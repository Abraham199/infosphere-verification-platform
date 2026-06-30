<?php
require __DIR__.'/vendor/autoload.php';
$app = require __DIR__.'/bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

$pdo = DB::connection()->getPdo();
$data = [
    'laravel' => app()->version(),
    'php' => PHP_VERSION,
    'mysql' => DB::selectOne('select version() as version')->version,
    'database' => DB::connection()->getDatabaseName(),
    'tables' => count(DB::select('show tables')),
    'queue' => config('queue.default'),
    'cache' => config('cache.default'),
    'session' => config('session.driver'),
    'env' => app()->environment(),
    'debug' => config('app.debug'),
    'url' => config('app.url'),
    'users' => DB::table('users')->count(),
    'tenants' => DB::table('tenants')->count(),
    'roles' => DB::table('roles')->count(),
    'permissions' => DB::table('permissions')->count(),
    'migrations' => DB::table('migrations')->count(),
];
echo json_encode($data, JSON_PRETTY_PRINT), PHP_EOL;
