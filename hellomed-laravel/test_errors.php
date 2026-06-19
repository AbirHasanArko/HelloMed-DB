<?php
require __DIR__.'/vendor/autoload.php';
$app = require_once __DIR__.'/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

$errors = \Illuminate\Support\Facades\DB::select("SELECT name, type, line, text FROM user_errors WHERE type LIKE '%PACKAGE%'");
echo json_encode($errors, JSON_PRETTY_PRINT);
