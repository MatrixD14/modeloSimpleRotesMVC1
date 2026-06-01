<?php

return [
    'get' => [
        '/' => ['HomeController', 'index'],
        '/contact' => ['ContactController', 'index'],
        '/dados' => ['ContactController', 'store']
    ],

    'post' => [],
];
