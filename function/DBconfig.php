<?php

require_once("usermeta.php");

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
$domain = "https://test-umgebung.duentetech.de/bwshofheim/";

//Header BWS für PDFs
$header_bws ="/var/services/web/test-umgebung/bwshofheim/includes/images/header_bws.jpg";

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
    $uhrzeit_clean = date("d.m.Y H:i:s", $timestamp);
    $uhrzeit_datei = date("dmYHis", $timestamp);
    $datum = date("d.m.Y", $timestamp);
    $random_id = rand(1000, 9999);   //Baut die ersten 4 Stellen der ID Random <- für beurlaubung.php

// IP-Adresse User
$user_ip = $_SERVER['REMOTE_ADDR'];


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



    function insert_verlauf($ID_PHP)
    {
        global $db_link, $uhrzeit, $user_ip, $infos_user;
    
        $Anfrage = "INSERT INTO antraege_beurlaubung_verlauf(ID_PHP, Nutzer, Datum, IP_ADDR, Userdevice, Kuerzel) 
        VALUES ( '$ID_PHP', '".Name()."', '$uhrzeit', '$user_ip', '$infos_user', '".Kuerzel()."')";

        $Ergebnis = $db_link->query($Anfrage);

        if($Ergebnis != true)
        {
            $Fehlermeldung = $db_link->connect_error;
            $Anfrage = "INSERT INTO log_all(IP_ADDR, action_log, User, Datum, Userdevice) 
            VALUES ('$user_ip', 'Fehler beim Datenbankeintrag (insert_verlauf): $Fehlermeldung', '".Kuerzel()."', '$uhrzeit', '$infos_user')";
    
            $db_link->query($Anfrage);
        }
        return true;
    }

    function insert_verlauf_steller($ID_PHP, $User)
    {
        global $db_link, $uhrzeit, $user_ip, $infos_user;
    
        $Anfrage = "INSERT INTO antraege_beurlaubung_verlauf(ID_PHP, Nutzer, Datum , IP_ADDR, Userdevice, Kuerzel) 
        VALUES ('$ID_PHP', '$User', '$uhrzeit','$user_ip', '$infos_user', 'extern')";
        $Ergebnis = $db_link->query($Anfrage);
    
        if($Ergebnis != true)
        {
            $Fehlermeldung = $db_link->connect_error;
            $Anfrage = "INSERT INTO log_all(IP_ADDR, action_log, User, Datum, Userdevice) 
            VALUES ('$user_ip', 'Fehler beim Datenbankeintrag (insert_verlauf_steller): $Fehlermeldung', '$User', '$uhrzeit', '$infos_user')";
    
            $db_link->query($Anfrage);
        }
        return true;
    }

    function insert_log($aktion)
    {
        global $db_link, $uhrzeit, $user_ip, $infos_user;

        $Anfrage = "INSERT INTO log_all(IP_ADDR, action_log, User, Datum, Userdevice) 
        VALUES ( '$user_ip', '$aktion', '".Kuerzel()."', '$uhrzeit', '$infos_user')";

        $db_link->query($Anfrage);
    }
    
?>