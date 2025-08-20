<?php
require __DIR__ . '/../vendor/autoload.php';
$app = require __DIR__ . '/../bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use App\Models\Lesson;

$l = Lesson::create(['title'=>'Smoke Lesson','position'=>1]);
$l->topics()->create(['title'=>'Smoke Topic','video_url'=>'https://youtu.be/dQw4w9WgXcQ','description'=>'top desc','position'=>1]);

echo "Inserted: " . $l->id . "\n";
