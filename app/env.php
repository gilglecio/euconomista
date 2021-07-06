<?php

$host = exec('hostname');
$user = exec('whoami');

$filename = __DIR__ . "/../{$user}@{$host}.local";

if (! file_exists($filename)) {
    $dist = __DIR__ . '/../env.dist';

    $open = fopen($filename, 'w+');

    fwrite($open, file_get_contents($dist));
    fclose($open);
}

return (array) json_decode(file_get_contents($filename));
