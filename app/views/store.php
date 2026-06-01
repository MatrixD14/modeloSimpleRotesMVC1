<?php

use app\helpers\View;

View::layout('master', ['title' => 'store']); ?>
<h1 class="font-white">ola <?= isset($_GET['nome']) ? $_GET['nome'] : '' ?></h1>