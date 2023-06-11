<!DOCTYPE html>


<head>
<title>Anträge auf Beurlaubung - Lehrende - Compass </title>

</head>

<?php

session_start();
require_once ('../function/DBconfig.php');
require_once ('../function/SMTPmail.php');
require_once ('../function/usermeta.php');
//include ("../includes/footer.php");
    //Prüft Variablen auf korrektheit 
    function check($variable)
        {
            $variable = trim($variable);
            $variable = stripslashes($variable);
            $variable = htmlspecialchars($variable);
                if (empty($variable))
                {
                    $_SESSION["Fehler_ID"] .= '<div class="fehler"><p>Füllen Sie bitte alle Felder aus!<p> </div> </br>';
                }
            return $variable;
        }
        
    function check2($variable)
        {
            $variable = trim($variable);
            $variable = stripslashes($variable);
            $variable = htmlspecialchars($variable);
            return $variable;
        }
        
    function validateDATUM ($datum_form)
        {
            $datum_format = "Y-m-d";
            $datum_objekt = DateTime::createFromFormat($datum_format, $datum_form); 
            if ($datum_objekt == false)
            {
                $_SESSION["Fehler_ID"] .= '<div class="fehler"><p>Überprüfe Sie das Format der Daten!<p> </div> </br>';
            }
        }

//ID aus Browser auslesen: 
$Antrags_ID = check2($_GET["id"]);
$Status_Readonly = check2($_GET["read"]);
$suche_ID = check2($_GET["sucheID"]);
$suche_Name = check2($_GET["sucheName"]);
$suche_Klasse = check2($_GET["sucheKlasse"]);
$downloadtyp = check2($_GET["type"]);


if(empty($_SESSION["user_logged_in"]))
{
    if(!empty($Antrags_ID))
    {
        echo '<meta http-equiv="refresh" content="0; URL='.$domain.'login.php?redirectto=lehrende/beurlaubung.php?id='.$Antrags_ID.'">';
    }
    else
    {
        echo '<meta http-equiv="refresh" content="0; URL='.$domain.'login.php">';
    }
    exit;
}


echo '<body>';

if(isset($_SESSION["user_logged_in"]))
{


include ('../includes/header_lehrende.php');

//Daten für die Ausgabe Dateidownload wird geladen -> Pfad wird gebaut vor Prüfung auf recht zum Berarbeiten um doppelte implementierung zu verhindern.
$Anfrage = "SELECT Dateiname FROM bwshofheim.antraege_beurlaubung_dateiupload WHERE ID_PHP = '$Antrags_ID'"; //SQL Abfrage wird gebaut 
$ergebnis = $db_link->query($Anfrage); //SQL Abfrage wird an die Datenbank übergeben 

//Antwort der DB auf Korrektheit prüfen
if(!$ergebnis)
    {
        $_SESSION["Fehler_ID"] .= '<div class ="status_fehler"> <p>Datenbankverbindung fehlgeschlagen, bitte kontaktieren Sie den Support unter ticket.bws-hofheim.de und nennen den Fehlercodeund teilen Sie uns folgendes mit: '.$Anfrage.'</p> </div>';
    }
    
    if($ergebnis->num_rows == 1) 
    {
        $downloadlink = "<button type='button' class='button_datei' onclick=\"window.open('$domain"."function/download.php?id=$Antrags_ID', '_blank')\">Datei anzeigen</button>";
        $downloadlinksmall= "<button type='button' class='button_datei_small' onclick=\"window.open('$domain"."function/download.php?id=$Antrags_ID', '_blank')\">Datei anzeigen</button>";
    } 
    else {
        $downloadlink = "<p>Es wurde keine Datei hochgeladen.</p>";
        $downloadlinksmall = "<p>Es wurde keine Datei hochgeladen.</p>";
    }
    

if(empty($Antrags_ID))
{

if(userTutor() == "true" && userSL() == "false")
{
    $readonly ="&read=true";
}


// Informationen zu Anträgen auf Beurlaubung im Status offen:
//Datenbankabfrage wird gebaut: 
$Anfrage = "SELECT ID_PHP, Art, name_SuS, zeitraum_von, zeitraum_bis FROM bwshofheim.antraege_beurlaubung WHERE kuerzel_tutor = '" . Kuerzel() . "' AND status_antrag = 'offen' ORDER BY ID_Datenbank DESC"; //SQL Abfrage wird gebaut 
$ergebnis = $db_link->query($Anfrage); //SQL Abfrage wird an die Datenbank übergeben 

//Antwort der DB auf Korrektheit prüfen
if(!$ergebnis){
  echo'<div class ="status_fehler"> <p>Datenbankverbindung fehlgeschlagen, bitte kontaktieren Sie den Support unter ticket.bws-hofheim.de und teilen Sie uns folgendes mit: '.$Anfrage.' </p> </div>';
}
else
{
    if ($ergebnis->num_rows > 0) {
        while ($row = $ergebnis->fetch_assoc()) {
            $Ausgabe_Tabelle_offen .=  '<tr><td>'.$row["Art"].'</td>' . '<td>'.$row["name_SuS"].'</td>' . '<td>'.$row["zeitraum_von"].'</td>' . '<td>'.$row["zeitraum_bis"].'</td><td><a href="?id='.$row["ID_PHP"].'">Antrag bearbeiten</a></tr>';
        }
    }
    else{
        $Ausgabe_Tabelle_offen = '<td colspan="5">Sie haben aktuell keine Anträge auf Beurlaubung im Status offen.</td>';
        
    }
}


//Anfrage Daten im Status Schulleitung für Tutor
$Anfrage = "SELECT ID_PHP, Art, name_SuS, zeitraum_von, zeitraum_bis FROM bwshofheim.antraege_beurlaubung WHERE kuerzel_tutor = '" . Kuerzel() . "' AND status_antrag = 'schulleitung' ORDER BY ID_Datenbank DESC"; //SQL Abfrage wird gebaut 
$ergebnis = $db_link->query($Anfrage); //SQL Abfrage wird an die Datenbank übergeben 

//Antwort der DB auf Korrektheit prüfen
if(!$ergebnis){
  echo'<div class ="status_fehler"> <p>Datenbankverbindung fehlgeschlagen, bitte kontaktieren Sie den Support unter ticket.bws-hofheim.de und teilen Sie uns folgendes mit: '.$Anfrage.' </p> </div>';
}
else
{
    if ($ergebnis->num_rows > 0) 
    {
        while ($row = $ergebnis->fetch_assoc()) 
        {
            $Ausgabe_Tabelle_SL .=  '<tr><td>'.$row["Art"].'</td>' . '<td>'.$row["name_SuS"].'</td>' . '<td>'.$row["zeitraum_von"].'</td>' . '<td>'.$row["zeitraum_bis"].'</td><td><a href="?id='.$row["ID_PHP"].$readonly.'">Details anzeigen</a></td></tr>';
        }
    }
    else
    {
        $Ausgabe_Tabelle_SL = '<td colspan="5">Sie haben aktuell keine Anträge auf Beurlaubung im Status Schulleitung.</td>';
    }
}


//Anfrage Daten im Status Schulleitung für SL
$Anfrage = "SELECT ID_PHP, Art, name_SuS, zeitraum_von, zeitraum_bis FROM bwshofheim.antraege_beurlaubung WHERE  status_antrag = 'schulleitung' ORDER BY ID_Datenbank DESC"; //SQL Abfrage wird gebaut 
$ergebnis = $db_link->query($Anfrage); //SQL Abfrage wird an die Datenbank übergeben 

//Antwort der DB auf Korrektheit prüfen
if(!$ergebnis){
  echo'<div class ="status_fehler"> <p>Datenbankverbindung fehlgeschlagen, bitte kontaktieren Sie den Support unter ticket.bws-hofheim.de und teilen Sie uns folgendes mit: '.$Anfrage.' </p> </div>';
}
else
{
    if ($ergebnis->num_rows > 0) 
    {
        while ($row = $ergebnis->fetch_assoc()) 
        {
            $Ausgabe_Tabelle_SL_USER_SL .=  '<tr><td>'.$row["Art"].'</td>' . '<td>'.$row["name_SuS"].'</td>' . '<td>'.$row["zeitraum_von"].'</td>' . '<td>'.$row["zeitraum_bis"].'</td><td><a href="?id='.$row["ID_PHP"].'">Bearbeiten</a></td></tr>';
        }
    }
    else
    {
        $Ausgabe_Tabelle_SL_USER_SL = '<td colspan="5">Sie haben aktuell keine Anträge auf Beurlaubung im Status Schulleitung.</td>';
    }
}



//Anfrage Daten im Status Schulleitung für ALLE Anträge
$Anfrage = "SELECT ID_PHP, Art, name_SuS, kuerzel_tutor, zeitraum_von, zeitraum_bis, status_antrag FROM bwshofheim.antraege_beurlaubung ORDER BY ID_Datenbank DESC"; //SQL Abfrage wird gebaut 
$ergebnis = $db_link->query($Anfrage); //SQL Abfrage wird an die Datenbank übergeben 

//Antwort der DB auf Korrektheit prüfen
if(!$ergebnis){
  echo'<div class ="status_fehler"> <p>Datenbankverbindung fehlgeschlagen, bitte kontaktieren Sie den Support unter ticket.bws-hofheim.de und teilen Sie uns folgendes mit: '.$Anfrage.' </p> </div>';
}
else
{
    if ($ergebnis->num_rows > 0) 
    {
        while ($row = $ergebnis->fetch_assoc()) 
        {
            $Ausgabe_Tabelle_ALL_USER_SL .=  '<tr><td>'.$row["status_antrag"].'</td>' . '<td>'.$row["kuerzel_tutor"].'</td>'. '<td>'.$row["Art"].'</td>' . '<td>'.$row["name_SuS"].'</td>' . '<td>'.$row["zeitraum_von"].'</td>' . '<td>'.$row["zeitraum_bis"].'</td><td><a href="?id='.$row["ID_PHP"].'">Bearbeiten</a></td></tr>';
        }
    }
    else
    {
        $Ausgabe_Tabelle_ALL_USER_SL = '<td colspan="6">Es wurden noch keine Anträge gestellt.</td>';
    }
}


//Anfrage Daten im Status genehmigt
$Anfrage = "SELECT ID_PHP,Art, name_SuS, zeitraum_von, zeitraum_bis FROM bwshofheim.antraege_beurlaubung WHERE kuerzel_tutor = '" . Kuerzel() . "' AND status_antrag = 'genehmigt' ORDER BY ID_Datenbank DESC"; //SQL Abfrage wird gebaut 
$ergebnis = $db_link->query($Anfrage); //SQL Abfrage wird an die Datenbank übergeben 

//Antwort der DB auf Korrektheit prüfen
if(!$ergebnis){
  echo'<div class ="status_fehler"> <p>Datenbankverbindung fehlgeschlagen, bitte kontaktieren Sie den Support unter ticket.bws-hofheim.de und teilen Sie uns folgendes mit: '.$Anfrage.'</p> </div>';
}
else
{
    if ($ergebnis->num_rows > 0) {
        while ($row = $ergebnis->fetch_assoc()) {
            $Ausgabe_Tabelle_genehmigt .=  '<tr><td>'.$row["Art"].'</td>' . '<td>'.$row["name_SuS"].'</td>' . '<td>'.$row["zeitraum_von"].'</td>' . '<td>'.$row["zeitraum_bis"].'</td><td><a href="?id='.$row["ID_PHP"].$readonly.'">Details anzeigen</a></td></tr>';
        }
    }
    else{
        $Ausgabe_Tabelle_genehmigt = '<td colspan="5">Sie haben aktuell keine Anträge auf Beurlaubung im Status genehmigt.</td>';
    }
}

//Anfrage Daten im Status abgelehnt
$Anfrage = "SELECT ID_PHP, Art, name_SuS, zeitraum_von, zeitraum_bis FROM bwshofheim.antraege_beurlaubung WHERE kuerzel_tutor = '" . Kuerzel() . "' AND status_antrag = 'abgelehnt' ORDER BY ID_Datenbank DESC"; //SQL Abfrage wird gebaut 
$ergebnis = $db_link->query($Anfrage); //SQL Abfrage wird an die Datenbank übergeben 

//Antwort der DB auf Korrektheit prüfen
if(!$ergebnis){
  echo'<div class ="status_fehler"> <p>Datenbankverbindung fehlgeschlagen, bitte kontaktieren Sie den Support unter ticket.bws-hofheim.de und teilen Sie uns folgendes mit: '.$Anfrage.' </p> </div>';
}
else
{
    if ($ergebnis->num_rows > 0) {
        while ($row = $ergebnis->fetch_assoc()) {
            $Ausgabe_Tabelle_abgelehnt .=  '<tr><td>'.$row["Art"].'</td>' . '<td>'.$row["name_SuS"].'</td>' . '<td>'.$row["zeitraum_von"].'</td>' . '<td>'.$row["zeitraum_bis"].'</td><td><a href="?id='.$row["ID_PHP"].'">Antrag bearbeiten</a></td></tr>';
        }
    }
    else{
        $Ausgabe_Tabelle_abgelehnt = '<td colspan="5">Sie haben aktuell keine Anträge auf Beurlaubung im Status abgelehnt.</td>';
    }

}


 include('../includes/menu.php');
 echo'
 <div class="body_lehrende">';

 //Suchfunktion
echo'
<div class="suche_beurlaubung">
<details>
<summary><p>Anträge suchen:<p></summary>
 <form method="GET" class="suche_form">
 <h1 class="header_suche">Suche:</h1></br>
 <label>Antrags ID:</label></br>
 <input class="lehrende_beurlaubung_input" type="number" name="sucheID"> </br>
 <label>Name:</label></br>
 <input class="lehrende_beurlaubung_input" type="text" name="sucheName"> </br>
 <label>Klasse:</label></br>
 <input class="lehrende_beurlaubung_input" type="text" name="sucheKlasse"> </br>

 <button class="beurlaubung_search_button" type="submit">Suchen</button>
 </form>
 </details>
 </div>
 ';

 //Prüfung ob Suchfunktion ausgelöst wurde
 if(!empty($suche_ID) || !empty($suche_Name) || !empty($suche_Klasse))
 {
    $where = '';
    if(!empty($suche_ID))
    {
        $suche_ID = check($suche_ID);
        $where .= " ID_PHP LIKE '%$suche_ID%'";
    }
    
    if(!empty($suche_Name))
    {
        $$suche_Name = check($suche_Name);
        if(!empty($where))
        {
            $where .= "AND";
        }
        $where .= " name_SuS LIKE '%$suche_Name%'";
    }

    if(!empty($suche_Klasse))
    {
        $suche_Klasse = check($suche_Klasse);
        if(!empty($where))
        {
            $where .= "AND";
        }
        $where .= " klasse_sus LIKE '%$suche_Klasse%'";
    }

    if(userSL() != "true")
    {
        $where .= "AND kuerzel_tutor = '".Kuerzel()."' ";
    }

    $Anfrage = "SELECT * FROM bwshofheim.antraege_beurlaubung WHERE $where ORDER BY ID_Datenbank DESC"; //SQL Abfrage wird gebaut 
    $ergebnis = $db_link->query($Anfrage); //SQL Abfrage wird an die Datenbank übergeben 
    //Antwort der DB auf Korrektheit prüfen
    if(!$ergebnis)
        {
            $_SESSION["Fehler_ID"] = "1";
            echo'<div class ="status_fehler"> <p>Datenbankverbindung fehlgeschlagen, bitte kontaktieren Sie den Support unter ticket.bws-hofheim.de und teilen Sie uns folgendes mit: '.$Anfrage.'</p> </div>';
        }
    if ($ergebnis->num_rows > 0) {
        while ($row = $ergebnis->fetch_assoc()) 
        {
                $Ausgabe_Suche .=  '<tr><td>'.$row["Art"].'</td>' . '<td>'.$row["name_SuS"].'</td>' . '<td>'.$row["zeitraum_von"].'</td>' . '<td>'.$row["zeitraum_bis"].'</td><td><a href="?id='.$row["ID_PHP"].'&read=true">Details anzeigen</a></td></tr>';
        }
    }
    else{
        $Ausgabe_Suche = '<td colspan="5">Ihre Suche hat keine Ergebnisse.</td>';
    }
    echo '
    <h1 class="header_lehrende">Suchergebnisse für: '.$suche_ID .' '. $suche_Name.' '. $suche_Klasse.'</h1>
    <table class="Tabelle"> 
    <tr> 
        <th>Antrags Art:</th> 
        <th>Name SuS:</th>
        <th>Beurlaubung von:</th>
        <th>Beurlaubung bis:</th>
        <th>Details</th>
    </tr>
    <tr> 
        '.$Ausgabe_Suche.'
    </tr>
    </table>
    <div class="center_suche">
    <button class="button_reset_suche" onclick="window.location.href=\''.$domain.'lehrende/beurlaubung.php\'">Suche zurücksetzen</button>
    </div>
    ';
        
}

 if(userSL() == "true")
 {
    echo'
    <h1 class="header_lehrende">Anträge, die durch die Schulleitung genehmigt werden müssen:</h1>
    <table class="Tabelle"> 
        <tr> 
            <th>Antrags Art:</th> 
            <th>Name SuS:</th>
            <th>Beurlaubung von:</th>
            <th>Beurlaubung bis:</th>
            <th>Bearbeiten</th>
        </tr>
        <tr> 
            '.$Ausgabe_Tabelle_SL_USER_SL.'
        </tr>
        </table>
        <h1 class="header_lehrende">Alle Anträge:</h1>
        <table class="Tabelle"> 
        <tr> 
            <th>Status Antrag:</th>
            <th>Tutor</th>
            <th>Antrags Art:</th> 
            <th>Name SuS:</th>
            <th>Beurlaubung von:</th>
            <th>Beurlaubung bis:</th>
            <th>Bearbeiten</th>
         </tr>
        <tr> 
            '.$Ausgabe_Tabelle_ALL_USER_SL.'
        </tr>
               
    </table>
    <br>
';

 }
 echo '
 <h1 class="header_lehrende">Anträge auf Beurlaubung im Status offen:</h1>
 <table class="Tabelle"> 
    <tr> 
        <th>Antrags Art:</th> 
        <th>Name SuS:</th>
        <th>Beurlaubung von:</th>
        <th>Beurlaubung bis:</th>
        <th>Bearbeiten</th>
    </tr>
        <tr> 
            '.$Ausgabe_Tabelle_offen.'
        </tr>
               
</table>
<hr class="abstand">
<h1 class="header_lehrende">Anträge auf Beurlaubung im Status Schulleitung:</h1>
<table class="Tabelle"> 
    <tr> 
        <th>Antrags Art:</th> 
        <th>Name SuS:</th>
        <th>Beurlaubung von:</th>
        <th>Beurlaubung bis:</th>
        <th>Details</th>
    </tr>
        <tr> 
            '.$Ausgabe_Tabelle_SL.'
        </tr>
               
</table>
<h1 class="header_lehrende">Anträge auf Beurlaubung im Status genehmigt:</h1>
<table class="Tabelle"> 
    <tr> 
        <th>Antrags Art:</th> 
        <th>Name SuS:</th>
        <th>Beurlaubung von:</th>
        <th>Beurlaubung bis:</th>
        <th>Details</th>
    </tr>
        <tr> 
            '.$Ausgabe_Tabelle_genehmigt.'
        </tr>       
</table>
<h1 class="header_lehrende">Anträge auf Beurlaubung im Status abgelehnt:</h1>
<table class="Tabelle"> 
    <tr> 
        <th>Antrags Art:</th> 
        <th>Name SuS:</th>
        <th>Beurlaubung von:</th>
        <th>Beurlaubung bis:</th>
        <th>Berbeiten</th>
    </tr>
        <tr> 
            '.$Ausgabe_Tabelle_abgelehnt.'
        </tr>       
</table>
 <p class="center_hinweise"> Hinweise: Sie müssen alle Anträge, die gestellt werden, genehmigen oder ablehnen. Sollte ein Antrag auf Beurlaubung eine Zeitspanne über 2 Tage überschreiten, wird der Antrag automatisch nach Ihrer genehmigung an die Schulleitung weitergegeben, die den Antrag ebenfalls genemigen muss. Die entscheidung der Schulleitung wird Ihnen und dem SuS automatisch mitgeteilt. <br>
 Sobald ein Antrag im Status "Schulleitung" oder "genehmigt" ist, kann er durch Sie nicht mehr bearbeitet werden. Anträge im Status abgelehnt können bearbeitet werden und erneut freigegeben werden. Auch hier wird der Antrag an die Schulleitung weitergegeben, sobald er 2 Tage überschreitet.


 </div>
 
 ';

}

if(!empty($Antrags_ID))
{
        //Wichtige Details des Antrags werden geladen: 
        $Anfrage = "SELECT status_antrag FROM bwshofheim.antraege_beurlaubung WHERE ID_PHP = '$Antrags_ID' AND kuerzel_tutor = '".Kuerzel()."'"; //SQL Abfrage wird gebaut 
        $ergebnis = $db_link->query($Anfrage); //SQL Abfrage wird an die Datenbank übergeben 

        //Antwort der DB auf Korrektheit prüfen
        if(!$ergebnis)
        {
            $_SESSION["Fehler_ID"] .= '<div class ="status_fehler"> <p>Datenbankverbindung fehlgeschlagen, bitte kontaktieren Sie den Support unter ticket.bws-hofheim.de und teilen Sie uns folgendes mit: '.$Anfrage.' </p> </div>';
        }
        if($ergebnis->num_rows == 0)
        {
            $_SESSION["Fehler_ID"] .= '<div class ="status_fehler"> <p>Die ID exisitiert nicht. Bitte probieren Sie es erneut!</p> </div>';
        }
        if(empty($_SESSION["Fehler_ID"]))
        {
            //Daten aus der Datenbank werden geladen und in eigene Variablen geschrieben
            $daten = $ergebnis->fetch_array(); 
            $STATUS_aktueller_Antrag = $daten[0];
        }


        $_SESSION["Fehler_ID"] = "";

        


            if(($STATUS_aktueller_Antrag == "offen" && userTutor() == "true" || userSL() == "true") || ($STATUS_aktueller_Antrag == "abgelehnt" && userTutor() == "true" || userSL() == "true")||($STATUS_aktueller_Antrag == "SL" && userSL() == "true") || ($STATUS_aktueller_Antrag == "genehmigt" && userSL() == "true"))
            {
                
            

                //Abfrage wird modifiziert für SL (wenn SL fällt die Prüfung weg, ob der Nutzer bearbeiten darf)
                if(userSL() == "true")
                {
                    $Anfrage_DB = "ID_PHP = '{$Antrags_ID}'";
                }
                else 
                {
                    $Anfrage_DB = "ID_PHP = '{$Antrags_ID}' AND kuerzel_tutor = '".Kuerzel()."'";
                }

                        //Verarbeitung eingaben über Formular 
                        if ($_SERVER['REQUEST_METHOD'] == "POST")
                        {
                            
                            //Alle Daten aus dem Formular werden ausgelesen 
                            $name_as = check($_POST["name_antragsteller"]);
                            $email_as = check($_POST["email_antragsteller"]);
                            $name_sus = check($_POST["name_sus"]);
                            $geb_sus = check($_POST["geb_sus"]);
                            $ort_sus = check($_POST["ort_sus"]);
                            $straße_sus = check($_POST["straße_sus"]);
                            $tel_sus = check($_POST["tel_sus"]);
                            $klasse_sus = check($_POST["klasse_sus"]);
                            $zeitraum_von = check($_POST["date_von"]);
                            $zeitraum_bis = check($_POST["date_bis"]);
                            $status_Antrag = check($_POST["status"]);
                            $Anmerkung = check2($_POST["anmerkung"]);
                            $grenzt_an_Ferien = check2($_POST["Ferien"]);

                            if($status_Antrag != "genehmigt" && $status_Antrag != "abgelehnt")
                            {
                                $_SESSION["Fehler_ID"] .= '<div class="fehler"><p>Ungültiger Wert für den Status des Antrages.<p> </div> </br>';    
                            }

                            validateDATUM($geb_sus);
                            validateDATUM($zeitraum_von);
                            validateDATUM( $zeitraum_bis);

                            //Wandelt die Daten in UNIX-Zeitstempel um
                            $datum_beginn = strtotime($zeitraum_von);
                            $datum_ende = strtotime($zeitraum_bis);
                            $aktuelles_datum = strtotime($datum);
                            $geb_sus_string = strtotime($geb_sus);

                            if($datum_beginn == "false" || $datum_ende == "false"  || $aktuelles_datum == "false")
                            {
                                $_SESSION["Fehler_ID"] .= '<div class="fehler"><p>Bei Ihrer Anfrage ist ein Fehler aufgetreten, bitte wenden Sie sich an ticket.bws-hofheim.de und nennen Sie den Fehlercode UNIX-strtotime<p> </div> </br>';
                            }

                            //Berechnung, ob sich die Dauer verändert hat
                            $abstand = ($datum_ende - $datum_beginn);  //Berechnung Anzahl Tage für die der Antrag gestellt wird 
                            $ungebohren = ($aktuelles_datum - $geb_sus_string);  //Kontrolle, dass das Geburtsdatum nicht in der Zukunft liegt                    
                    
                            //Prüft Dauer des Antrages 
                            if($abstand <= "172800")
                            {
                                $ArtAntrag = "bis2Tage";
                            }
                            else 
                            {
                                $ArtAntrag = "ueber2Tage";
                            }

                            // Prüfung Zeitspanne 
                            if($abstand < 0)
                            {
                                $_SESSION["Fehler_ID"] .= '<div class="fehler"><p>Das Enddatum liegt vor dem Startdatum.<p> </div> </br>';
                            }

                            if($ungebohren < 0)
                            {
                                $_SESSION["Fehler_ID"] .= '<div class="fehler"><p>Das Geburtsdatum liegt in der Zukunft.<p> </div> </br>';
                            }

                            //Prüft ob Antrag relevant für SL ist
                            if(($status_Antrag == "genehmigt" && $ArtAntrag == "ueber2Tage" && userSL() == "false")||($grenzt_an_Ferien == "true" && userSL() == "false"))
                            {
                                $status_Antrag = "schulleitung";
                            }

                            if($status_Antrag == "genehmigt" && $ArtAntrag == "ueber2Tage" && userSL() == "true")
                            {
                                $status_Antrag = "genehmigt";
                            }



                            if(empty($_SESSION["Fehler_ID"]))
                            {

                                //Datenbank wird mit den neuen Informationen gefüllt
                                $Anfrage = "UPDATE bwshofheim.antraege_beurlaubung
                                            SET Art = '$ArtAntrag', name_as = '$name_as', email_as = '$email_as', name_SuS = '$name_sus', Geb_SuS = '$geb_sus', Wohnort_SuS = '$ort_sus', Straße_SuS = '$straße_sus', tel_SuS = '$tel_sus', klasse_sus = '$klasse_sus', zeitraum_von = '$zeitraum_von', zeitraum_bis = '$zeitraum_bis', status_antrag = '$status_Antrag', begruendung  = '$Anmerkung'
                                            WHERE $Anfrage_DB";
                                $ergebnis = $db_link->query($Anfrage); //SQL Abfrage wird an die Datenbank übergeben 


                                if(!$ergebnis)
                                {
                                    $_SESSION["Fehler_ID"] .=  '<div class ="status_fehler"> <p>Datenbankverbindung fehlgeschlagen, bitte kontaktieren Sie den Support unter ticket.bws-hofheim.de und und teilen Sie uns folgendes mit: '.$Anfrage.'</p> </div>';
                                }
                                
                                if(empty($_SESSION["Fehler_ID"]))
                                {
                                    $status_user = 'Dein Antrag wurde bearbeitet und befindet sich im Status '.$status_Antrag;
                                    $status_tutor = 'Antrag wurde bearbeitet und befindet sich im Status '.$status_Antrag;
                                    sendEmail($email_as, $name_as, "Aktueller Stand Deines Antrages.", $status_user);
                                    sendEmail(Email(),Vorname()." ".Nachname(), "Antrag wurde bearbeitet.", $status_tutor);
                                    insert_verlauf($Antrags_ID);

                                    echo '<meta http-equiv="refresh" content="0; URL='.$domain.'lehrende/beurlaubung.php">';
                                }
                            }
                            else 
                            {
                                echo $_SESSION["Fehler_ID"];
                                unset($_SESSION["Fehler_ID"]);
                            }
                        }

                        //Daten für die Ausgabe/Detailansicht wird geladen 
                        $Anfrage = "SELECT * FROM bwshofheim.antraege_beurlaubung WHERE $Anfrage_DB"; //SQL Abfrage wird gebaut 
                        $ergebnis = $db_link->query($Anfrage); //SQL Abfrage wird an die Datenbank übergeben 

                        //Antwort der DB auf Korrektheit prüfen
                        if(!$ergebnis)
                        {
                            $_SESSION["Fehler_ID"] .= '<div class ="status_fehler"> <p>Datenbankverbindung fehlgeschlagen, bitte kontaktieren Sie den Support unter ticket.bws-hofheim.de und nennen den Fehlercodeund teilen Sie uns folgendes mit: '.$Anfrage.'</p> </div>';
                        }
                        if($ergebnis->num_rows == 0)
                        {
                            $_SESSION["Fehler_ID"] .= '<div class ="status_fehler"> <p>Die ID exisitiert nicht. Bitte probieren Sie es erneut!</p> </div>';
                        }
                        if(empty($_SESSION["Fehler_ID"]))
                        {
                            //Daten aus der Datenbank werden geladen und in eigene Variablen geschrieben
                            $daten = $ergebnis->fetch_array(); 
                            $ID_PHP = $daten[1];
                            $Art = $daten[2];
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
                            $status = $daten[17];
                            $begruendung = $daten[18];
                        }

                        if($Art == "bis2Tage")
                        {
                            $maximale_Dauer = "von 2 Tagen";
                            $Pruefung_SL = "muss nicht";
                        }
                        else if ($Art == "ueber2Tage")
                        {
                            $maximale_Dauer = "von über 2 Tagen";
                            $Pruefung_SL = "muss";
                        }
                        else{
                            $maximale_Dauer = "Fehler beim Datenabruf";
                            $Pruefung_SL = "Fehler beim Datenabruf";
                        }

                        if($status == "genehmigt")
                        {
                            $option_value = '"<option value="genehmigt">genehmigen</option>"';
                            $option_value .= '"<option value="abgelehnt">ablehnen</option>"';
                        }
                        else if($status == "abgelehnt")
                        {
                            $option_value = '"<option value="abgelehnt">ablehnen</option>"';
                            $option_value .= '"<option value="genehmigt">genehmigen</option>"';
                        }
                        else
                        {
                            $option_value = '"<option value=""selected disabled>- Bitte wählen -</option>"';
                            $option_value .= '"<option value="genehmigt">genehmigen</option>"';
                            $option_value .= '"<option value="abgelehnt">ablehnen</option>"';
                        }

                        if(userSL() != "true")
                        {
                            $ferien_html = 
                            '<label class="checkbox">Der Antrag grenzt an Ferien.
                            <input type="checkbox" name="Ferien" value="true">
                            <span class="checkmark"></span>
                            </label>';
                        }
       
                    
                        echo'

                        <div class="details_beurlaubung">
                        <h1 class="header_lehrende">Detailansicht zu Antrag "'.$ID_PHP.'"</h1>
                    
                        <p class="center"> Dies ist ein Antrag mit maximaler Dauer <strong> '.$maximale_Dauer.' </strong>. Dieser Antrag <strong> '.$Pruefung_SL.' </strong> erneut von der Schulleitung geprüft werden. Bitte genehmigen Sie den Antrag oder lehnen Sie den Antrag ab. </p>
                        <div class="formular">
                                            <form method="POST" enctype="multipart/form-data"> 
                                            <p class="center"> Dieser Antrag wurde am: '.$Datum_gestellt.' gestellt.</p> </br>
                                            <button type="button" class="button_datei" onclick="window.open(\'' . $domain . 'function/creatPDF.php?typ=beurlaubung&id=' . $Antrags_ID . '\', \'_blank\')">Drucken</button>

                                            <hr>

                                            <h2 class="center"> Schülerdaten, Sie können diese Daten verändern. </h2>
                                        
                                            <label>Name, Vorname der Erziehungsberechtigten Antragsteller: </label> </br>
                                            <input value="'.$name_as.'"type="text" name="name_antragsteller" required> </br>

                                            <label>E-Mail-Adresse des Antragstellers: </label> </br>
                                            <input value="'.$email_as.'" type="email" name="email_antragsteller" required> </br>

                                            <label>Name, Vorname des/der Schülers/Schülerin </label> </br>
                                            <input value="'.$name_SuS.'" type="text" name="name_sus" required> </br>

                                            <label>Geburtsdatum des/der Schülers/Schülerin </label> </br>
                                            <input value="'.$Geb_Sus.'" class="datum" type="date" name="geb_sus" required> </br>

                                            <label>PLZ, Ort</label> </br>
                                            <input value="'.$Wohnort_SuS.'" type="text" name="ort_sus" required> </br>

                                            <label>Straße, Hausnummer</label> </br>
                                            <input value="'.$Straße_SuS.'" type="text" name="straße_sus" required> </br>

                                            <label>Telefonnummer</label> </br>
                                            <input value="'.$tel_Sus.'" type="number" name="tel_sus" required> </br>

                                            <label>Klasse</label> </br>
                                            <input value="'.$klasse_sus.'" type="text" name="klasse_sus"required> </br>

                                            <label>Beurlaubung von - bis: (bitte Zeitraum unten ändern) </label> </br>
                                            <input value="'.$zeitraum_von.'" class="datum" type="date"><input value="'.$zeitraum_bis.'" class="datum" type="date"> </br>

                                            <label>Es liegt folgender wichtiger Grund für eine Beurlaubung vor: (nicht veränderbar)</label> </br>
                                            <textarea id="laenge" oninput="autoResize()"  class="anmerkung" maxlength="500">'.$grund_as.' </textarea></br>


                                            <label>Vom Antragsteller hochgeladene Datei:</label></br>
                                            '.$downloadlink.'

                                            <hr>

                                            <h2 class="center"> Hier genehmigen oder lehnen Sie den Antrag ab. </h2> </br>
                                            <p class="center"> Hinweis: Möchten Sie den Zeitraum verkürzen, ändern Sie unten das Start und/oder das Enddatum und setzen den Antrag auf genehmigt. </p> </br>

                                            <label>Zeitraum bearbeiten: </label> </br>
                                            <input value="'.$zeitraum_von.'" class="datum" type="date" name="date_von"required><input value="'.$zeitraum_bis.'" class="datum" type="date" name="date_bis"required> </br>

                                            <label>Antrag annehmen oder ablehnen: </label></br>
                                            <label>Sie erkennen einen bestehenden Status an der Vorauswahl</label><br>
                                            <select  class="kuerzel" name="status" required>
                                                '.$option_value.'
                                            </select> </br>

                                            <label>Anmerkungen:</label> </br>
                                            <textarea id="laenge" oninput="autoResize()" class="anmerkung" maxlength="500"  name="anmerkung" > </textarea></br>
                                            '.$ferien_html.'

                                            <button type="submit"  class="abschicken" name="absenden" value="true">Speichern</button>
                                            </form>
                        
                        
                        
                        
                        
                        <script>
                        function autoResize() {
                            // den Textbereich und dessen Inhalt auswählen
                            const textarea = document.getElementById("laenge");
                            const content = textarea.value;
                        
                            // die Anzahl der Zeilen berechnen
                            const rows = content.split("\n").length;
                        
                            // die Höhe des Textbereichs basierend auf der Anzahl der Zeilen und der Zeilenhöhe berechnen
                            const lineHeight = parseFloat(getComputedStyle(textarea).lineHeight);
                            const height = rows * lineHeight;
                            textarea.style.height = `${height}px`;
                        
                            // das Padding basierend auf der Anzahl der Zeilen festlegen
                            const paddingBottom = (rows > 1) ? `${rows}em` : "1em";
                            textarea.style.paddingBottom = paddingBottom;
                        }
                        </script>

                        


                        ';


                
            }
            else
            {
                if($Status_Readonly == "true")
                {

                    $Anfrage = "SELECT * FROM bwshofheim.antraege_beurlaubung WHERE ID_PHP = '{$Antrags_ID}' AND kuerzel_tutor = '".Kuerzel()."'"; //SQL Abfrage wird gebaut 
                    $ergebnis = $db_link->query($Anfrage); //SQL Abfrage wird an die Datenbank übergeben 
            
                    //Antwort der DB auf Korrektheit prüfen
                    if(!$ergebnis)
                    {
                        $_SESSION["Fehler_ID"] = "1";
                        echo'<div class ="status_fehler"> <p>Datenbankverbindung fehlgeschlagen, bitte kontaktieren Sie den Support unter ticket.bws-hofheim.de und teilen Sie uns folgendes mit: '.$Anfrage.' </p> </div>';
                    }
                    if(empty($_SESSION["Fehler_ID"]))
                    {
            
                        //Daten aus der Datenbank werden geladen und in eigene Variablen geschrieben
                        $daten = $ergebnis->fetch_array(); 
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
                        $status = $daten[17];
                        $begruendung = $daten[18];
            
                        echo '
                        <h1 class="header_lehrende">Detailansicht zu Antrag "'.$ID_PHP.'"</h1>
                    
                        <p class="center">Sie können diesen Antrag aktuell nicht bearbeiten.</p>
                        <button type="button" class="button_datei_edit" onclick="window.open(\'' . $domain . 'function/creatPDF.php?typ=beurlaubung&id=' . $Antrags_ID . '\', \'_blank\')">Drucken</button>
                        <table class="Tabelle"> 
                            <tr> 
                                <th>Antrags ID:</th>  
                                <td>'.$ID_PHP.'</td>                       
                            </tr>
                            <tr> 
                            <th>Antrags Art:</th>  
                            <td>'.$ID_Art.'</td>                       
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
                        
                        ';
            
                    }
                }
                else
                {
                    if(!empty($Antrags_ID))
                    { 
                        echo '<div class="fehler"><p> Sie haben keine Berechtigung diese Inhalte zu bearbeiten. Sollten Sie Rückfragen haben, wenden Sie sich an ticket.bws-hofheim.de</p></div>';
                    }
                }
            }
        }  
        echo "</div>";
        echo "</div>";
        include ("../includes/footer.php");
}


?>
</body>

