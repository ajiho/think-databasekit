<?php

namespace ajiho\IlluminateDatabase;

require 'vendor/autoload.php';


$start = microtime(true);
$path = dirname(__DIR__) . '/vendor/illuminate';
(new PeaceMessenger($path))->run();
$end = microtime(true);
$elapsed = $end - $start;

echo "执行时间：{$elapsed} 秒\n";

