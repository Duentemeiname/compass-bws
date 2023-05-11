<!DOCTYPE html>
<?php
include_once 'loginstatus.php';
?>
<head>
    <html lang="de">
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta http-equiv="Cache-Control" content="no-cache, no-store, must-revalidate">
    <meta http-equiv="Pragma" content="no-cache">
    <meta http-equiv="Expires" content="0">
    <link rel="stylesheet" href="/bwshofheim/style.css">
</head>
<body>
<div class="header">
        <a href="/bwshofheim/lehrende" class="header_schrift"> Compass - Brühlwiesenschule Hofheim - Lehrende</a> 
</div>
<div class ="menu">
    <button class="button_antragsstatus" onclick="window.location.href='/bwshofheim'"> Startseite </button>
    <?php loggedin() ;?>
</div>