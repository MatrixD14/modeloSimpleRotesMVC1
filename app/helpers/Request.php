<?php

namespace app\helpers;

class Request
{
    public static function get()
    {
        return strtolower($_SERVER['REQUEST_METHOD']);
    }
}
