<?php

$time = time();

echo "Time: $time".PHP_EOL."Hash: ".sha1($argv[1].$time.'sh! No se lo cuentes a nadie!').PHP_EOL;