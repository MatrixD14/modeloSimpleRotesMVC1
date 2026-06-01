<?php

use app\helpers\View;

View::layout('master', ['title' => 'contact']); ?>
<h1 class="font-white">Cantact</h1>
<form action="/dados" method="get">
    <input type="text" name="nome" id="nome" placeholder="nome">
    <button type="submit">enviar</button>
</form>