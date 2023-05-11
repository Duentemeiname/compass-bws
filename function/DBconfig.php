<?php
// Damit alle Fehler angezeigt werden
error_reporting(E_ALL);

//  Server 
define ( 'MYSQL_HOST', 'localhost' );

define ( 'MYSQL_BENUTZER',  'user_bws' );

define ( 'MYSQL_KENNWORT',  'kQ4(DcsICd' );

define ( 'MYSQL_DATENBANK', 'bwshofheim' );

$db_link = new mysqli  (MYSQL_HOST, 
                        MYSQL_BENUTZER, 
                        MYSQL_KENNWORT, 
                        MYSQL_DATENBANK);

// Verbindung prüfen
if ($db_link->connect_error) {
    die('Datenbankverbindung fehlgeschlagen, bitte kontaktieren Sie den Support unter ticket.bws-hofheim.de und nennen den Fehlercode: ' . $db_link->connect_error);
}

//Globale DOMAIN
//WICHTIG -> Muss ebenfalls manuell unter /includes/loginstatus.php geändert werden!!!!
$domain = "https://test-umgebung.duentetech.de/bwshofheim/";

//Erzeugt einen Random string mit 15 Zeichen
function random_String()
{
    $chars = "ABCDEFGHIJKLMNOPQRSTUVWXYZabcdefghijklmnopqrstuvwxyz123456789!?";
    $var_size = strlen($chars);
    $random_str = '';
    for($x = 0; $x < 15; $x++) {
      $random_str .= $chars[rand(0, $var_size - 1)];
    }
    return $random_str;
  }

    //Ließt die genaue Uhrzeit und Datum des Servers ab. 
    $timestamp = time();
    $uhrzeit = date("Y.m.d H:i:s", $timestamp); //$uhrzeit = date("d.m.Y H:i:s", $timestamp);
    $uhrzeit_datei = date("dmYHis", $timestamp);
    $datum = date("d.m.Y", $timestamp);
    $random_id = rand(1000, 9999);   //Baut die ersten 4 Stellen der ID Random <- für beurlaubung.php

// Browser-Agent des Clients auslesen
  $user_agent = $_SERVER['HTTP_USER_AGENT'];

// Funktion zum Identifizieren des Browsers und des Betriebssystems
    $browser = "Unbekannter Browser";
    $os = "Unbekanntes Betriebssystem";
    
    // Browser identifizieren
    if (preg_match('/MSIE/i', $user_agent) && !preg_match('/Opera/i', $user_agent)) {
        $browser = 'Internet Explorer';
    } elseif (preg_match('/Firefox/i', $user_agent)) {
        $browser = 'Mozilla Firefox';
    } elseif (preg_match('/Chrome/i', $user_agent)) {
        $browser = 'Google Chrome';
    } elseif (preg_match('/Safari/i', $user_agent)) {
        $browser = 'Apple Safari';
    } elseif (preg_match('/Opera/i', $user_agent)) {
        $browser = 'Opera';
    } elseif (preg_match('/Netscape/i', $user_agent)) {
        $browser = 'Netscape';
    }
    
    // Betriebssystem identifizieren
    if (preg_match('/Windows/i', $user_agent)) {
        $os = 'Windows';
    } elseif (preg_match('/Mac/i', $user_agent)) {
        $os = 'Mac OS X';
    } elseif (preg_match('/Linux/i', $user_agent)) {
        $os = 'Linux';
    } elseif (preg_match('/Unix/i', $user_agent)) {
        $os = 'Unix';
    }
    
    $infos_user = "Betriebssystem: ".$os." Browser: ".$browser;
?>