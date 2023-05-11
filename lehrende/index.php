<?php
session_start();

require_once ('../function/DBconfig.php');
require_once ('../function/SMTPmail.php');

if(empty($_SESSION["user_logged_in"]))
{
    echo '<meta http-equiv="refresh" content="0; URL='.$domain.'login.php">';
    exit;
}
echo '<head>
<title>Lehrende - Compass </title>
</head>';

//Prüfung ob der User angemeldet ist
if(isset($_SESSION ["user_logged_in"]))
{
    include('../includes/header_lehrende.php');
    include('../includes/menu.php');

    //Daten für Schnellübersicht für Beurlaubung aus DB laden
    $Anfrage = "SELECT COUNT(*) FROM bwshofheim.antraege_beurlaubung WHERE kuerzel_tutor = '{$_SESSION["Kuerzel"]}' AND status_antrag = 'offen'"; //SQL Abfrage wird gebaut 
    $ergebnis = $db_link->query($Anfrage); //SQL Abfrage wird an die Datenbank übergeben 

    //Antwort der DB auf Korrektheit prüfen
    if(!$ergebnis){
      echo'<div class ="status_fehler"> <p>Datenbankverbindung fehlgeschlagen, bitte kontaktieren Sie den Support unter ticket.bws-hofheim.de und nennen den Fehlercode: </p> </div>' . $db_link->connect_error;
    }
    else
    {
        //Array wird gelesen und in Variable gespeichert
        $daten = $ergebnis->fetch_array(); 
        $Anzahl_Antraege_offen = $daten[0];
    }

    echo '<body>

    <div class="body_lehrende">
    <h1 class="header_willkommen">Herzlich Willkommen!</h1>
    <h1 class="header_lehrende">Sie befinden sich im Lehrendenbereich.</h1>
    <p class="center_p">Kurzübersicht:</p>

    <div class="kurzuebersicht">
    <div class="icon_row1">
    <p>Sie haben aktuell '.$Anzahl_Antraege_offen.' Antrag auf Beurlaubung im Status offen.</p> 
    </div>
    <div class="icon_row1">
    <p>Sie haben bereits 0 Anmeldungen zur Klassenfahrt erhalten.</p> 
    </div>
    <div class="icon_row1">
    <p>Sie haben aktuell 0 vom Unternehmen bestätigte Praktikas.</p> 
    </div>
    <div class="icon_row1">
    <p>Sie haben Ihr Passwort zuletzt am 10.03.2023 geändert.</p> 
    </div>
    </div>
    
    
    ';

 }
 else
 {
     echo '<meta http-equiv="refresh" content="0; URL='.$domain.'login.php">';
 }

// ?>




</body>