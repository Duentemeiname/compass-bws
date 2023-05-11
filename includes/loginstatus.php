<!DOCTYPE html>
<head>
    <link rel="stylesheet" href="/bwshofheim/style.css">
</head>
<?php
session_start();
//<button class="abschicken" onclick="window.location.href=\'beurlaubung.php?akzeptiert=true\'"> Login für Lehrende </button>

function loggedin()
{
    $domain = "https://test-umgebung.duentetech.de/bwshofheim/";

    if(empty($_SESSION ["user_logged_in"]))
    {
        echo '<button class="button_loginstatus" onclick="window.location.href=\''.$domain.'login.php\'"> Login für Lehrende </button>';
    }
    if(isset($_SESSION ["user_logged_in"]))
    {
        echo '<button class="button_loginstatus" onclick="window.location.href=\''.$domain.'login.php?logout=true\'">Hallo '.$_SESSION["Name_voll"].', Logout?</button>';
        echo '<a class="logout_mobile" href="login.php?logout=true">Logout</a>';
    }
    
}



?>