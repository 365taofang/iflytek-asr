<?php


$mapping = [
	'Iflytek\LfasrClient' => __DIR__ . '/Iflytek/LfasrClient.php',
];


spl_autoload_register(function ($class) use ($mapping) {
    if (isset($mapping[$class])) {
        require $mapping[$class];
    }
}, true);
