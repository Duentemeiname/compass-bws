<!DOCTYPE html>
<head>
<title>Antrag auf Beurlaubung - Compass </title>

</head>

<?php
session_start();


include 'includes/header.php';
require_once ('function/DBconfig.php');

//ID aus Browser auslesen: 
 $_SESSION["ID"] = $_GET["id"];

 if(isset($_GET["abmelden"]))
 {
    unset($_SESSION["passwort"]);
    session_destroy();
    header('Location: status.php');
 }

$_SESSION["passwort"] = $_POST["passwort"];

 $_SESSION["Fehler_ID"] = "";
//ID prüfen 

function eingabeID(){
    echo '<p>Bitte geben Sie die ID Ihres Antrags ein, um weitere Details anzeigen zu können!<p>
    <form method="GET">
    <input class="status_input" type="number" name="id" required> </br>
    <button class="status_button" type="submit">Mein Antrag anzeigen!</button>

    </form>';

}

function passwort(){
    echo'
    <form method="POST" ">
    <input class="status_input" type="password" name="passwort" required> </br>
    <button class="status_button" type="submit">Weitere Details anzeigen!</button>

    </form>
    ';
}

 echo '<head>
 <title>Status - Compass </title>
 </head>';
 ?>
 <div class="background">
            <div class="seiteninhalt">
                <h1 class="header_willkommen">Status Ihres Antrages:</h1>
                     <div class="status_infos">
 <?php

//Prüfung ob die ID eingegeben wurde 
 if(isset($_SESSION["ID"])){
    //Überprüng ob es ein INT-Wert ist und ob die Länge passt
    if (is_numeric($_SESSION["ID"])) {
        $_SESSION["ID"] = intval($_SESSION["ID"]);
        $input_length = strlen(strval($_SESSION["ID"]));
        if ($input_length <= 9) {
    
        } else {
            echo 'Sie haben eine zu große Zahl eingefügt';
            $_SESSION["Fehler_ID"] = "1";
        }
      } else {
            echo 'Die Eingabe darf nur aus Zahlen bestehen!';
            $_SESSION["Fehler_ID"] = "1";
      }

    if(empty($_SESSION["Fehler_ID"])) //Prüfung ob durch vorherigen Prüfungen ein Fehler entstanden ist
    {
    $Anfrage = "SELECT art, datum_gestellt, kuerzel_tutor, status_antrag FROM bwshofheim.antraege_beurlaubung WHERE id_php = '{$_SESSION['ID']}'"; //SQL Abfrage wird gebaut 
    $ergebnis = $db_link->query($Anfrage); //SQL Abfrage wird an die Datenbank übergeben 

    //Antwort der DB auf Korrektheit prüfen
    if(!$ergebnis){
        $_SESSION["Fehler_ID"] = "1";
        die('<div class ="status_fehler"> <p>Datenbankverbindung fehlgeschlagen, bitte kontaktieren Sie den Support unter ticket.bws-hofheim.de und nennen den Fehlercode: </p> </div>' . $db_link->connect_error);
    }

    //Überpüft, ob die ID gefunden wurde 
    if($ergebnis->num_rows == 0){
        $_SESSION["Fehler_ID"] = "1";
        echo'<div class ="status_fehler"> <p>Die von Ihnen eingegebene ID exisitiert nicht. Bitte probieren Sie es erneut!</p> </div>';
        eingabeID();
    }

    if(empty($_SESSION["Fehler_ID"])){
    //Daten aus der Datenbank werden geladen und in eigene Variablen geschrieben
    $daten = $ergebnis->fetch_array(); 
    $art = $daten[0];
    $datum_gestellt = $daten[1];
    $kuerzel_tutor = $daten[2];
    $status_antrag = $daten[3];

    //Anfrage zu Daten des Tutors
    $Anfrage = "SELECT Nachname, Vorname, EMail FROM bwshofheim.lehrende WHERE Kuerzel = '$kuerzel_tutor'"; //SQL Abfrage wird gebaut '{$_SESSION['ID']}'
        $ergebnis = $db_link->query($Anfrage); //SQL Abfrage wird an die Datenbank übergeben 

        //Antwort der DB auf Korrektheit prüfen
        if(!$ergebnis){
            $_SESSION["Fehler_ID_DB"] = "1";
            echo '<div class ="status_fehler"> <p>Datenbankverbindung fehlgeschlagen, bitte kontaktieren Sie den Support unter ticket.bws-hofheim.de und nennen den Fehlercode: </p> </div>' . $db_link->connect_error;
            echo $db_link->connect_error;
        }
        if(empty($_SESSION["Fehler_ID_DB"])){
        //Daten aus der Datenbank werden geladen und in eigene Variablen geschrieben    
            $daten = $ergebnis->fetch_array(); 
            $nachname_tutor = $daten[0];
            $vorname_tutor = $daten[1];
            $email_tutor = $daten[2];

            $name_tutor = $vorname_tutor.' '.$nachname_tutor;

        }

    echo "<h2> Details zu Ihrem Antrag:</h2>";
    echo '<div class="status_success"><p>Ihr Antrag auf Beurlaubung "'.$art.'" wurde am '.$datum_gestellt.' erfolgreich gestellt, befindet sich im Status '.$status_antrag.' und ist in Bearbeitung durch '.$name_tutor.'.</p></div>' ;
    echo '<button class="status_andereID" onclick="window.location.href=\'status.php\'"> Andere ID Nachverfolgen </button>';
   
   
    echo "<h2> Weitere Details Ihres Antrages:</h2>";

    if(empty($_SESSION["passwort"])){
    echo "<p> Um weitere Details zu Ihrem Antrag anzeigen zu können, geben Sie bitte das Passwort ein, dass Sie per E-Mail erhalten haben!";
    passwort();
    }
    if(isset($_SESSION["passwort"]))
    {
        //Daten für die Ausgabe Dateidownload wird geladen -> Pfad wird gebaut vor Prüfung auf recht zum Berarbeiten um doppelte implementierung zu verhindern.
        $Anfrage = "SELECT Dateiname FROM bwshofheim.antraege_beurlaubung_dateiupload WHERE ID_PHP = '{$_SESSION['ID']}'"; //SQL Abfrage wird gebaut 
        $ergebnis = $db_link->query($Anfrage); //SQL Abfrage wird an die Datenbank übergeben 

        //Antwort der DB auf Korrektheit prüfen
        if(!$ergebnis)
            {
                $_SESSION["Fehler_ID"] .= '<div class ="status_fehler"> <p>Datenbankverbindung fehlgeschlagen, bitte kontaktieren Sie den Support unter ticket.bws-hofheim.de und nennen den Fehlercodeund teilen Sie uns folgendes mit: '.$Anfrage.'</p> </div>';
            }
            
            if($ergebnis->num_rows == 1) 
            {
                $Antrags_ID = $_SESSION['ID'];
                $downloadlink = "<button class='button_datei' onclick=\"window.open('$domain"."function/download.php?id=$Antrags_ID&user=extern', '_blank')\">Datei anzeigen</button>";
                $downloadlinksmall= "<button class='button_datei_small' onclick=\"window.open('$domain"."function/download.php?id=$Antrags_ID&user=extern', '_blank')\">Datei anzeigen</button>";
            } 
            else {
                $downloadlink = "<p>Es wurde keine Datei hochgeladen.</p>";
                $downloadlinksmall = "<p>Es wurde keine Datei hochgeladen.</p>";
            }


        $Anfrage = "SELECT * FROM bwshofheim.antraege_beurlaubung WHERE id_php = '{$_SESSION['ID']}'"; //SQL Abfrage wird gebaut 
        $ergebnis = $db_link->query($Anfrage); //SQL Abfrage wird an die Datenbank übergeben 

        //Antwort der DB auf Korrektheit prüfen
        if(!$ergebnis){
            $_SESSION["Fehler_ID"] = "1";
            echo'<div class ="status_fehler"> <p>Datenbankverbindung fehlgeschlagen, bitte kontaktieren Sie den Support unter ticket.bws-hofheim.de und nennen den Fehlercode: </p> </div>' . $db_link->connect_error;
        }
        if(empty($_SESSION["Fehler_ID"])){

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
            $klasse_sus = $daten[12];
            $zeitraum_von = $daten[13];
            $zeitraum_bis = $daten[14];
            $grund_as = $daten[15];
            $Passwort = $daten[16];
            $status = $daten[17];
            $begruendung = $daten[18];


            if(password_verify($_SESSION["passwort"], $Passwort))
            {
                //Session[ID_PW] wird für authentifizierung bei Dateiuplaod genutzt. Enthält die ID des Antrages, allerdings NUR dann wenn das Login korrekt ist!
                $_SESSION["ID_PW"] = $_SESSION['ID'];


            echo '
            <table class="Tabelle"> 
                <tr> 
                    <th>Antrags ID:</th>  
                    <td>'.$ID_PHP.'</td>                       
                </tr>
                <tr> 
                <th>Antrags Art:</th>  
                <td>'.$art.'</td>                       
            </tr>
                <tr> 
                    <th>Name Antragstellers:</th>        
                    <td>'.$name_as.'</td>                    
                </tr>
                <tr>
                    <th>E-Mail-Adresse Antragstellers:</th>
                    <td>'.$email_as .'</td>
                </tr>
                <tr>
                    <th>Name des Schülers:</th>
                    <td>'.$name_SuS .'</td>
                </tr>
                <tr>
                    <th>Geburtsdatum des Schülers:</th>
                    <td>'.$Geb_Sus .'</td>
                </tr>
                <tr>
                    <th>PLZ, Ort:</th>
                    <td>'.$Wohnort_SuS .'</td>
                </tr>
                <tr>
                    <th>Straße, Hausnummer:</th>
                    <td>'.$Straße_SuS .'</td>
                </tr>
                <tr>
                    <th>Telefonnummer:</th>
                    <td>'. $tel_Sus.'</td>
                </tr>
                <tr> 
                    <th>Tutor:</th>        
                    <td>'.$name_tutor.'</td>                    
                </tr>
                <tr> 
                    <th>E-Mail Tutor:</th>        
                    <td>'.$email_tutor.'</td>                    
                 </tr>
                <tr>
                    <th>Klasse:</th>
                    <td>'.$klasse_sus.'</td>
                </tr>
                <tr>
                    <th>Beurlaubung von:</th>
                    <td>'.$zeitraum_von.'</td>
                </tr>
                <tr>
                    <th>Beurlaubung bis:</th>
                    <td>'.$zeitraum_bis.'</td>
                </tr>
                <tr>
                    <th>Begründung:</th>
                    <td>'.$grund_as.'</td>
                </tr>
                <tr>
                    <th>Datei:</th>
                    <td>'.$downloadlinksmall.' </td>
                </tr>
                <tr>
                    <th>Status:</th>
                    <td>'.$status.'</td>
                <tr>
                    <th>Begründung:</th>
                    <td>'.$begruendung.'</td>
                </tr>
                <tr>
                    <th>Letzte Bearbeitung am:</th>
                    <td></td>
                </tr>
                <tr>
                    <th>Letzte Bearbeitung durch:</th>
                    <td></td>
                </tr>
                </table>
                <p> Sie können diese Daten nicht mehr verändern. Liegt ein Fehler vor, wenden Sie sich per E-Mail an <a href="mailto:'.$email_tutor.'">'.$name_tutor.' </a></p>
            
                <button class="abschicken_status" onclick="window.location.href=\'status.php?abmelden=true\'"> Abmelden </button>
            
            ';



            }
            else {
                passwort();
                echo 'Ihr Passwort konnte Ihrer ID nicht zugeordnet werden, bitte überprügen Sie ob Sie das Passwort richtig eingegeben haben.';

            }
}
}
}
}
}
else{
    eingabeID();

}


?>
                    </div>
                </div>
            </div>
 </div>