<?php

use app\helpers\View;
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="asseble/css/backgroud.css">
    <link rel="stylesheet" href="asseble/css/fonts.css">
    <title><?= View::e($title ?? "não definito ") ?></title>
</head>

<body class="back-black">

    <?php View::section('content');
    ?>

</body>

</html>