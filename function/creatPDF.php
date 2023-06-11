<?php


require_once("dompdf/vendor/autoload.php");
require_once("DBconfig.php");
require_once("usermeta.php");

$typ = $_GET["typ"];
$ID_PHP = $_GET["id"];
$user = $_GET["user"];


use Dompdf\Dompdf;
$dompdf = new Dompdf();

//Prüfung ob der Nutzer internes Login hat (wird bei status.php mitgegeben) -> gernell Prüfung ob die Berechtigung vorhanden ist.
if($user == "extern")
{
    if($ID_PHP != $_SESSION["ID_PW"]) //Siehe status.php
    {
       echo "Fehler bei der Zuordnung des Antrages. Berechtigung?";
       exit();
    }
    else
    $verifiziert = "true";
}
 if((empty($ID_PHP) || empty($_SESSION ["user_logged_in"])) && empty($_SESSION["passwort"])) //Prüfung ob die richtigen Informationen übergeben wurden
 {
    echo "Bei Ihrer Anfrage ist ein Fehler aufgetreten. Berechtigung?";
    exit();
 }
else
{
    //Anfrage an die Datenbank 
    $Anfrage = "SELECT kuerzel_tutor, ID_PHP 
                FROM antraege_beurlaubung
                WHERE ID_PHP = $ID_PHP
                ";
    $ergebnis = $db_link->query($Anfrage); //SQL Abfrage wird an die Datenbank übergeben 

    //Antwort der DB auf Korrektheit prüfen
    if(!$ergebnis)
        {
            echo "Ungültige ID! ";
            exit();
        }
    else
        {
            $daten = $ergebnis->fetch_array(); 
            $kuerzel_tutor = $daten[0];
            $ID_PHP_DB = $daten[1];

            if($ID_PHP_DB != $ID_PHP) //Prüfung ob die ID die die DB liefert der angefragte entspricht 
            {
                echo "Die Datenbank hat ein falsches Ergebnis geliefert.";
                exit();
            }

            if(empty($verifiziert)) //Prüfung ob von  status.php verfiziert siehe oben in dieser Datei
            {
            if(userSL() != "true") //Prüfung ob SL dann fällt Prüfung auf tutor zugriff weg
            {
                if($kuerzel_tutor != Kuerzel()) //Prüfung ob der Tutor zugreifen darf
                {
                    echo "Sie haben keine Berechtigung diese Datei herunterzuladen.";
                    exit();
                }
            }
        }
    }
}


function creatPDF($Inhalt, $Dateiname)
{
    global $dompdf, $ID_PHP;
    $dompdf->loadHtml($Inhalt);
    $dompdf->setPaper('A4', 'Portrait');
    $dompdf->render();
    $dompdf->stream($Dateiname, array('Attachment' => 0));

    insert_log("PDF zu Antrag: ".$ID_PHP." erfolgreich durch Nutzer erstellt");
}

if($typ != "beurlaubung" || empty($ID_PHP))
{
    echo "Zu wenig Argumente!";
    exit;
}
else if($typ == "beurlaubung")
{
    $Fehler = 0;
    $Anfrage = "SELECT * FROM bwshofheim.antraege_beurlaubung WHERE id_php = '$ID_PHP'"; //SQL Abfrage wird gebaut 
    $ergebnis = $db_link->query($Anfrage); //SQL Abfrage wird an die Datenbank übergeben 

    //Antwort der DB auf Korrektheit prüfen
    if(!$ergebnis){
        $Fehler = "1";
        echo'<div class ="status_fehler"> <p>Datenbankverbindung fehlgeschlagen, bitte kontaktieren Sie den Support unter ticket.bws-hofheim.de und nennen den Fehlercode:' . $Anfrage.'</p> </div>';
        exit;
    }

    $Anfrage ="SELECT Nutzer, Datum, Kuerzel FROM bwshofheim.antraege_beurlaubung_verlauf WHERE ID_PHP = '$ID_PHP' ORDER BY ID DESC";
    $ergebnis_verlauf = $db_link->query($Anfrage);

    if(!$ergebnis_verlauf)
    {
        $Fehler = "1";
        echo'<div class ="status_fehler"> <p>Datenbankverbindung fehlgeschlagen, bitte kontaktieren Sie den Support unter ticket.bws-hofheim.de und nennen den Fehlercode: ' . $Anfrage.'</p> </div>';
    }

    if(empty($Fehler)){

        //Daten aus der Datenbank werden geladen und in eigene Variablen geschrieben
        $daten = $ergebnis->fetch_array(); 
        $ID_DB = $daten[0];
        $ID_PHP = $daten[1];
        $ID_Art = $daten[2];
        $Datum_gestellt = $daten[3];
        $name_as = $daten[4];
        $email_as = $daten[5];
        $name_SuS = $daten[6];
        $Geb_Sus = $daten[7];
        $Wohnort_SuS = $daten[8];
        $Straße_SuS = $daten[9];
        $tel_Sus = $daten[10];
        $kuerzel_tut = $daten[11];
        $klasse_sus = $daten[12];
        $zeitraum_von = $daten[13];
        $zeitraum_bis = $daten[14];
        $grund_as = $daten[15];
        $Passwort = $daten[16];
        $status = $daten[17];
        $begruendung = $daten[18];

        $daten = $ergebnis_verlauf->fetch_array();
        $last_user = $daten[0];
        $last_datum = $daten[1];
        $last_kuerzel = $daten[2];

        if($ID_Art == "bis2Tage")
        {
            $ID_Art = " bis zu 2 Tagen";
        }
        else if($ID_Art == "ueber2Tage")
        {
            $ID_Art = " über 2 Tage oder vor den Ferien";
        }
        else
        {
            echo "Unbekannter Status";
            exit;
        }

        //Prüfe, wer letzter Bearbeiter -> Entscheidung 
        $Anfrage = "SELECT Tutor, Schulleitung FROM lehrende WHERE Kuerzel = '$last_kuerzel'"; //SQL Abfrage wird gebaut 
        $ergebnis = $db_link->query($Anfrage); //SQL Abfrage wird an die Datenbank übergeben 

    //Antwort der DB auf Korrektheit prüfen
    if(!$ergebnis)
    {
        $Header_ueberschrift = "Dieser Antrag wurde noch nicht final bearbeitet. Diese Datei belegt die Erfolgreiche Stellung des Antrages.";
        $showorhide = 0;
    }
    else
    {
        $daten = $ergebnis->fetch_array();
        $Tutor = $daten[0];
        $SL = $daten[1];
    }
    //Holt Name Tutor
    $Anfrage = "SELECT Nachname, Vorname, EMail FROM lehrende WHERE Kuerzel = '$kuerzel_tut'"; //SQL Abfrage wird gebaut 
    $ergebnis = $db_link->query($Anfrage); //SQL Abfrage wird an die Datenbank übergeben 

    //Antwort der DB auf Korrektheit prüfen
    if(!$ergebnis)
    {
    $Header_ueberschrift = "Dieser Antrag wurde noch nicht final bearbeitet. Diese Datei belegt die Erfolgreiche Stellung des Antrages.";
    $showorhide = 0;
    }
    else
    {
    $daten = $ergebnis->fetch_array();
    $Nachname_tut = $daten[0];
    $Vorname_tut = $daten[1];
    }





    if($SL == "true" && ($status == "genehmigt" || $status == "abgelehnt"))
    {
        $Header_ueberschrift = "Entscheidung durch die Schulleitung:";
        $showorhide = 1;
    }
    else if(($Tutor == "true" && $SL == "false") && ($status == "genehmigt" || $status == "abgelehnt"))
    {
        $Header_ueberschrift = "Entscheidung durch den Klassenlehrer/in oder Tutor/in:";
        $showorhide = 1;
    }
    else
    {
        $Header_ueberschrift = "Dieser Antrag wurde noch nicht final bearbeitet. Diese Datei belegt die erfolgreiche Stellung des Antrages. ";
        $showorhide = 0;
    }

    //Erstellt den Teil mit den Informationen zum Status des Antrages (gegehmigt/abgelehnt)
    if($showorhide == true)
    {
        $lastpart= "
        </br>Der Antrag auf Beurlaubung wird: </br> </br>

        ";
        if($status == "genehmigt")
        {
            $showstatus = " <u>genehmigt</u> </br></br>  Bitte den obrigen Zeitraum beachten! Dieser nennt den genehmigten Zeitraum und ist bindent.</br>";
        }
        else if ($status == "abgelehnt")
        {
            $showstatus = " <u>abgelehnt</u> </br></br> Bei Rückfragen wende dich an: ".$last_user." ()";
        }



    }

        $Dateiname = "Beurlaubungsantrag ".$ID_PHP;
        $Inhalt = '
            <head>
                <meta http-equiv="Content-Type" content="text/html; charset=utf-8"/>
            <style>
            body{
            }
            table, th, td {border: thin solid;}
        .Tabelle_eineSpalte {
            border-collapse: collapse;
            text-align: center;
            margin: 10px auto;
            width: 100%;
        }
        .Tabelle_zweiSpalten {
            border-collapse: collapse;
            text-align: center;
            margin: 10px auto;
            width: 100%;
        }
        .umbruch{
            white-space: pre-line;
        }
        .footer{
            position: fixed;
            left: 0;
            bottom: 0;
            width: 100%;
            text-align: center;
        }

        h1, h2, h3, h4 {text-align: center; margin: 0px; padding: 0px;}
        .center {text-align: center}
            </style>
            </head>
            </body>
            <img src="'.$header_bws.'">
            <h2>
            Antrag auf Beurlaubung von Schülerinnen und Schülern '.$ID_Art.'
        </h2>
        <h3>
                - zur Vorlage bei der/dem Klassenlehrer/in oder der/dem Tutor/in - </br>
                - Antrags ID: '.$ID_PHP.' -
        </h3>
        <table class="Tabelle_zweiSpalten"> 
        <tr> 
        <th>Name, Vorname der Erziehungesberechtigten (Antragsteller): <br /> '.$name_as.' </th>
        <th>Name/Vorname des/der Schülers/Schülerin: <br />'.$name_SuS.'</th>       
        </tr>

        <tr>
            <th>Anschrift und Telefon <br />'.$Straße_SuS."; ".$Wohnort_SuS." ".$tel_Sus.'</th>
            <th>Geburtsdatum <br /> '.$Geb_Sus.' </th>
        </tr>
        <tr>
            <th>Klassenlehrer/in<br />'.$Vorname_tut.' '.$Nachname_tut.' ('.$kuerzel_tut.')</th>
            <th>Klasse:<br />'.$klasse_sus.'</th>
        </tr>
        <tr>
            <th>Zeitraum der Beurlaubung: <br />Von '.$zeitraum_von.' bis '. $zeitraum_bis.'</th>
            <th>Bitte beachten Sie, dass sich der Zeitraum der Beurlaubung geändert haben kann.</th>
        </tr>
        </table>
        <table class="Tabelle_eineSpalte">
            <tr>
                <th> Es liegt folgender wichtiger Grund für eine Beurlaubung vor: <br /> <div class="umbruch"> '.$grund_as.' </div></th>
            </tr>
        </table> </br>
        <p class="center">
        Bitte beachten Sie, dass angehängte Datein zusätzlicher heruntergeladen werden müssen. 
        Mir ist bekannt, dass der versäumte Unterrichtsstoff nachgeholt werden muss. Von den Hinweisen auf der Webseite habe ich Kenntnis genommen.
        </p>
        <hr>
        <h2>
            '.$Header_ueberschrift.'
        </h2>
        <h3>
        '.$lastpart.$showstatus.'
        </h3>
        <p> 
        Der Antrag wurde zuletzt bearbeitet von: <br> 
        '.$last_user.' ('.$last_kuerzel.') <br>
        '.$last_datum.'
            <footer class="footer">
            <p> Dieses Dokument wurde am '.$uhrzeit_clean.' automatisch von '.$domain.' erstellt und ist ohne Unterschrift gültig. Die Gültigkeit des Dokumentes kann über '.$domain.'status.php jederzeit überpürft werden.
            </footer>
        ';
}
}
    creatPDF($Inhalt, $Dateiname);

?>