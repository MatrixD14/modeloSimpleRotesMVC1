<?php

namespace app\controllers;


class HomeController extends Controller
{
    public function index()
    {
        return self::view("home");
    }
}
