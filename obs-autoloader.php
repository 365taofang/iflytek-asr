<?php


$mapping = [
	'Iflytek\ASR' => __DIR__.'/Iflytek/ASR.php',
];


spl_autoload_register(function ($class) use ($mapping) {
    if (isset($mapping[$class])) {
        require $mapping[$class];
    }
}, true);
