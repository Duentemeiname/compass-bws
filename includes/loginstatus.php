<!DOCTYPE html>
<head>
    <link rel="stylesheet" href="/bwshofheim/style.css">
</head>
<?php
session_start();
if(file_exists('../function/usermeta.php'))
    require_once('../function/usermeta.php');

if(file_exists('function/usermeta.php'))
    require_once('function/usermeta.php');

if(file_exists('../function/DBconfig.php'))
    require_once('../function/DBconfig.php');

if(file_exists('function/DBconfig.php'))
    require_once('function/DBconfig.php');


function loggedin()
{
    global $domain;

    if(empty($_SESSION ["user_logged_in"]))
    {
        echo '<button class="button_loginstatus" onclick="window.location.href=\''.$domain.'login.php\'"> Login für Lehrende </button>';
    }
    if(isset($_SESSION ["user_logged_in"]))
    {
        echo '<button class="button_loginstatus" onclick="window.location.href=\''.$domain.'login.php?logout=true\'">Hallo '.Vorname()." ".Nachname().', Logout?</button>';
        echo '<a class="logout_mobile" href="login.php?logout=true">Logout</a>';
    }
    
}



?>