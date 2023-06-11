<?php
session_start();
require_once ('DBconfig.php');


if(!empty($_SESSION ["user_logged_in"]))
{

//Lege Globale Variablen an
$Administrator = "0";
$Tutor = "0";
$Schulleitung = "0";
$Nachname = "0";
$Vorname = "0";
$Kuerzel = "0";
$EMail = "0";

//Abfrage der Daten aus DB
function getDBData():bool
{
    global $db_link, $Administrator, $Tutor, $Schulleitung, $Nachname, $Vorname, $Kuerzel, $EMail;

    $Anfrage = "SELECT * FROM bwshofheim.lehrende WHERE ID = '{$_SESSION['ID']}'"; //SQL Abfrage wird gebaut
    $ergebnis = $db_link->query($Anfrage); //SQL Abfrage wird an die Datenbank übergeben 

    if(!$ergebnis)
    {
        return 0;
        die;
    }
    else
    {
        $daten = $ergebnis->fetch_array(); 
        $Nachname = $daten[1];
        $Vorname = $daten[2];
        $Kuerzel = $daten[3];
        $EMail = $daten[4];
        $Administrator = $daten[6];
        $Tutor = $daten[7];
        $Schulleitung = $daten[10];
        return 1;
    }
}


function userAdmin()
{
    if(getDBData() == true)
    {
        global $Administrator;
        return $Administrator;
    }
    else 
    {
        return "false";
    }

}


function userTutor()
{
    if(getDBData() == true)
    {
        global $Tutor;
        return $Tutor;
    }
    else 
    {
        return "false";
    }
}

function userSL()
{
        if(getDBData() == true)
        {
            global $Schulleitung;
            return $Schulleitung;
        }
        else 
        {
            return "false";
        }
}

function Vorname()
{
        if(getDBData() == true)
        {
            global $Vorname;
            return $Vorname;
        }
        else 
        {
            return "false";
        }
}

function Nachname()
{
    if(getDBData() == true)
    {
        global $Nachname;
        return $Nachname;
    }
    else 
    {
        return "false";
    }
}

function Name()
{
    if(getDBData() == true)
    {
        global $Vorname;
        global $Nachname;
        return $Vorname." ".$Nachname;
    }
    else 
    {
        return "false";
    }
}

function Kuerzel()
{
    if(getDBData() == true)
    {
        global $Kuerzel;
        return $Kuerzel;
    }
    else 
    {
        return "false";
    }
}

function Email()
{
    if(getDBData() == true)
    {
        global $EMail;
        return $EMail;
    }
    else 
    {
        return "false";
    }
}



// echo "Vorname: ".Vorname()."<br>";
// echo "Nachname: ".Nachname()."<br>";
// echo "Kuerzel: ".Kuerzel()."<br>";
// echo "E-Mail: ".Email()."<br>";
// echo "Administrator: ".userAdmin()."<br>";
// echo "Tutor: ".userTutor()."<br>";
// echo "Schulleitung: ".userSL()."<br>";'


}

?>