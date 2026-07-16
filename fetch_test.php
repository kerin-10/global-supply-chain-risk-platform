<?php
require __DIR__.'/vendor/autoload.php';
$app = require_once __DIR__.'/bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

$data = Illuminate\Support\Facades\Http::withoutVerifying()->get('https://raw.githubusercontent.com/mledoze/countries/master/countries.json')->json();
print_r(array_slice($data, 0, 1));
