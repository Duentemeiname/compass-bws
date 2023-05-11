<!DOCTYPE html>
<?php
include_once 'loginstatus.php';
?>
<head>
    <link rel="stylesheet" href="/bwshofheim/style.css">
</head>


<nav class="navbar">
        <ul class="menu_lehrende">
        <h1 class="header_willkommen">Menü</h1>
        <hr>
            <li><a href="beurlaubung.php">Anträge auf Beurlaubung</a></li>
            <li><a href="klassenfahrt.php">Klassenfahrten</a></li>
            <li><a href="praktikum.php">Praktikas</a></li>
            <li><a href="account.php">Account bearbeiten</a></li>
        <hr>
        </ul>
</nav>


<div class="menu_mobile">
<button class="dropbtn">Menü</button>
  <div class="dropdown-content">
    <a href="/bwshofheim">Startseite</a>
    <a href="beurlaubung.php">Beurlaubung</a>
    <a href="klassenfahrt.php">Klassenfahrten</a>
    <a href="praktikum.php">Praktikas</a>
    <a href="account.php">Account</a>
    <?php loggedin() ?>
  </div>
</div>