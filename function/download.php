<?php
session_start();
require_once ('DBconfig.php');
require_once ('usermeta.php');

$ID_PHP = $_GET["id"];
$user = $_GET["user"];

//Prüfung ob der Nutzer uinternes Login hat (wird bei status.php mitgegeben)
if($user == "extern")
{
    if($ID_PHP != $_SESSION["ID_PW"]) //Siehe status.php
    {
       echo "Fehler bei der Zuordnung des Antrages. Berechtigung?";
       exit();
    }
    $verifiziert = "true";
}
 if((empty($ID_PHP) || empty($_SESSION ["user_logged_in"])) && empty($_SESSION["passwort"])) //Prüfung ob die richtigen Informationen übergeben wurden
 {
    echo "Bei Ihrer Anfrage ist ein Fehler aufgetreten.";
    exit();
 }

 else
 {
    //Anfrage an die Datenbank - JOIN zwischen Antrage und Dateiuploads um berechtigung prüfen zu können.
    $Anfrage = "SELECT antraege_beurlaubung.ID_PHP, antraege_beurlaubung.kuerzel_tutor, antraege_beurlaubung_dateiupload.Dateiname 
                FROM antraege_beurlaubung 
                INNER JOIN antraege_beurlaubung_dateiupload ON antraege_beurlaubung.ID_PHP = antraege_beurlaubung_dateiupload.ID_PHP 
                WHERE antraege_beurlaubung.ID_PHP = $ID_PHP
                ";
    $ergebnis = $db_link->query($Anfrage); //SQL Abfrage wird an die Datenbank übergeben 

    //Antwort der DB auf Korrektheit prüfen
    if(!$ergebnis)
        {
            echo "Ungültige ID! ";
            exit();
        }
    if($ergebnis->num_rows == 1)
        {
            $daten = $ergebnis->fetch_array(); 
            $ID_PHP_DB = $daten[0];
            $kuerzel_tutor = $daten[1];
            $Dateiname = $daten[2];

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
            $filepath = '../uploads/'.$Dateiname;

            if (!(file_exists($filepath))) 
            {
                echo "Dateizugriff gescheitert. Code: file_exist ";
                echo $filepath;
                exit();
            }
            if(!(is_readable($filepath)))
            {
                echo "Dateizugriff gescheitert. Code: is_readable ";
                echo $filepath;
                exit();
            }
            header('Content-Type: application/pdf');
            header('Content-Disposition: inline; filename="' . basename($filepath) . '"');
            readfile($filepath);
            exit();
        }
    else
        {
            echo "Es ist ein Fehler beim Datenbankabruf aufgetreten (Datei nicht eindeutig). ";
            exit();   
        }
 }
?>