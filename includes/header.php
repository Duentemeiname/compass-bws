<!DOCTYPE html>
<?php
include 'loginstatus.php';
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
    <a href="/bwshofheim" class="header_schrift"> Compass - Brühlwiesenschule Hofheim</a>
</div>
<div class ="menu">
    <button class="button_antragsstatus" onclick="window.location.href='status.php'"> Antragsstatus </button>
    <?php 
        if(isset($_SESSION ["user_logged_in"])){
            echo '<button class="button_antragsstatus" onclick="window.location.href=\'lehrende\'"> Lehrendenbereich </button>';
        }

    loggedin() ;
    ?>
    </div>

<div class="menu_mobile">
    <button class="dropbtn">Menü</button>
        <div class="dropdown-content">
            <a href="status.php">Antragsstatus</a>
            <?php 
            if(isset($_SESSION ["user_logged_in"]))
            {
            echo '<a href="lehrende">Lehrendenbereich</a>';
            }
            loggedin(); ?>
  </div>
</div>
