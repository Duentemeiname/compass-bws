<!DOCTYPE html>
<head>

<title>Login - Compass </title>

</head>


<?php
session_start();

echo "PHP is kagge";

include 'includes/header.php';
require_once ('function/DBconfig.php');
require_once ('function/SMTPmail.php');

header('Cache-Control: no-cache, no-store, must-revalidate'); // HTTP 1.1
header('Pragma: no-cache'); // HTTP 1.0
header('Expires: 0'); // Proxies
$_SESSION["Fehler_login"] = "";
$_SESSION["email_reset"] = "";
$_SESSION["Fehler_ID"] = "";
$_SESSION["Fehler_ID_DB"] = "";

function check($variable){
    $variable = trim($variable);
    $variable = stripslashes($variable);
    $variable = htmlspecialchars($variable);
    if (empty($variable))
    {
        $_SESSION["Fehler_login"] = '<div class="fehler"><p>Füllen Sie bitte alle Felder aus!<p> </div> </br>';
    }
    return $variable;
}

$reset_passwort = $_GET["reset_passwort"];
$reset_pw_key = $_GET["reset_pw_key"];
$user_id = $_GET["user"];
$logout = $_GET["logout"];
$_SESSION["redirect"] = $_GET["redirect"];
$_SESSION["Email"] = check($_POST["email"]);
$Passwort_login = check($_POST["passwort"]);
$_SESSION["email_reset"] = check($_POST["email_reset"]);
$neuesPasswort1 = check($_POST["passwort_reset_1"]);
$neuesPasswort2 = check($_POST["passwort_reset_2"]);



echo '
<div class="background">
<div class="seiteninhalt">';

//Passwort wird gepürft und bei richtiger Eingabe wird der nuter angemeldet
if(empty($reset_passwort) && empty($reset_pw_key) && empty($user) && empty($logout))
{
    echo'<h1 class="header_willkommen"> Herzlich Willkommen, bitte melden Sie sich mit Ihrem Account an!</h1>';
    echo '<hr>';
    echo'<h1 class="center">Login ausschließlich für Tutoren und Tutorinnen der Brühlwiesenschule</h1>';
    echo'<div class="login">';
    if ($_SERVER['REQUEST_METHOD'] == "POST")
    {

        $Anfrage = "SELECT * FROM bwshofheim.lehrende WHERE EMail = '{$_SESSION['Email']}'"; //SQL Abfrage wird gebaut
        $ergebnis = $db_link->query($Anfrage); //SQL Abfrage wird an die Datenbank übergeben 

        //Antwort der DB auf Korrektheit prüfen
        if(!$ergebnis)
        {
            $_SESSION["Fehler_ID_DB"] = "1";
            echo '<div class ="status_fehler"> <p>Datenbankverbindung fehlgeschlagen, bitte kontaktieren Sie den Support unter ticket.bws-hofheim.de und nennen den Fehlercode: </p> </div>' . $db_link->connect_error;
            echo $db_link->connect_error;
        }

        //Prüfen ob die E-Mail in der Datenbank verhanden ist
        if($ergebnis->num_rows == 0){
            $_SESSION["Fehler_ID_DB"] = "1";
            $_SESSION["Info_login.php"] = '<div class ="status_fehler"> <p>Die von Ihnen eingegebene E-Mail exisitiert nicht. Bitte probieren Sie es erneut!</p> </div>';
            //IP-Adresse des Nutzers auslese
            $user_ip = $_SERVER['REMOTE_ADDR'];
                
            //Versuchte Anmeldung wird in der DB gespeichert
            $Anfrage = "INSERT INTO log_all(IP_ADDR, action_log, User, Datum, Userdevice) 
                        VALUES ('$user_ip', 'Login fehlgeschlagen - Falsche E-Mail Adresse', '{$_SESSION["Email"]}', '$uhrzeit', '$infos_user')";
                                                            
            $db_link->query($Anfrage);
        }
        if(empty($_SESSION["Fehler_ID_DB"]))
        {
        //Daten aus der Datenbank werden geladen und in eigene Variablen geschrieben    
            $daten = $ergebnis->fetch_array(); 
            $ID = $daten[0];
            $Nachname = $daten[1];
            $Vorname = $daten[2];
            $Kuerzel = $daten[3];
            $EMail = $daten[4];
            $Passwort = $daten[5];
            $Administrator = $daten[6];
            $Tutor = $daten[7];
            $Schulleitung = $daten[10];
        if(password_verify($Passwort_login, $Passwort))
        {
                      //Gloable Variablen für die Authentifizierung und Daten setzen.
                      $_SESSION ["user_logged_in"] = random_String();
                      $_SESSION["ID"] = $ID;
                      $_SESSION["Nachname"] = $Nachname;
                      $_SESSION["Vorname"] = $Vorname;
                      $_SESSION["Kuerzel"] = $Kuerzel;
                      $_SESSION["EMail"] = $EMail;
                      $_SESSION["Passwort"] = $Passwort;
                      $_SESSION["Administrator"] = $Administrator;
                      $_SESSION["Tutor"] = $Tutor;
                      $_SESSION["SL"] = $Schulleitung;
                      $_SESSION["Name_voll"] = $_SESSION["Vorname"] ." ". $_SESSION["Nachname"];
          
                      //E-Mail mit Login bestätigung senden
                      $mail->addAddress($_SESSION["EMail"], $_SESSION["Name_voll"]);
                          $mail->isHTML();
                          $mail->Subject = 'Neue Anmeldung an Ihrem Account!';
                          $mail->Body    = 'Sie haben sich soeben an Ihrem Compass - BWS Account angemldet. Wenn Sie das nicht waren, ändern Sie umgehen Ihr Passwort und wenden Sie sich an ticket.bws-hofheim.de';
                              if(!$mail->Send()) {
                                  $_SESSION["Fehler_EMAIL_Senden"] = '"Mailer Error: " '. $mail->ErrorInfo.'';
                              }

                        //IP-Adresse des Nutzers auslese
                        $user_ip = $_SERVER['REMOTE_ADDR'];
                
                        //Anmeldung wird in der DB gespeichert
                        $Anfrage = "INSERT INTO log_all(IP_ADDR, action_log, User, Datum, Userdevice) 
                                    VALUES ( '$user_ip', 'Nutzer angemeldet', '{$_SESSION["ID"]}', '$uhrzeit', '$infos_user')";
                                                
                        $db_link->query($Anfrage);
          
                          //Redirect zur vorherigen Seite
                          if(isset($_SESSION["redirect"]))
                          {
                              echo '<meta http-equiv="refresh" content="0; URL='.$domain.$_SESSION["redirect"].'">';
                              unset($_SESSION["redirect"]);
                          }
                          else 
                          {
                              echo '<meta http-equiv="refresh" content="0; URL='.$domain.'lehrende">';
                          }
  
        }
        else
        {
            echo '<div class ="status_fehler"> <p>Falsches Passwort eingegeben, bitte versuchen Sie es erneut! </p> </div>';
            
            //IP-Adresse des Nutzers auslese
            $user_ip = $_SERVER['REMOTE_ADDR'];
                
            //Versuchte Anmeldung wird in der DB gespeichert
            $Anfrage = "INSERT INTO log_all(IP_ADDR, action_log, User, Datum, Userdevice) 
                        VALUES ('$user_ip', 'Login fehlgeschlagen - Falsches Passwort', '{$_SESSION["Email"]}', '$uhrzeit', '$infos_user')";
                                                
            $db_link->query($Anfrage);
        }
    }
    }

    echo $_SESSION["Info_login.php"];
    unset($_SESSION["Info_login.php"]);
    echo '
    <form method="POST" class="login_form">
    <label> E-Mail-Adresse:</label>  </br>
    <input class="status_input" type="email" name="email" required> </br>
    <label> Passwort:</label> </br>
    <input class="status_input" type="password" name="passwort" required> </br>
    <button class="status_button" type="submit">Anmelden!</button>

    <button class="reset_passwort" onclick="window.location.href=\'login.php?reset_passwort=true\'"> Passwort vergessen/noch nicht gesetzt? </button>
    </form>

    <p> Sollten Sie Probleme beim Login haben, wenden Sie sich bitte an <a href="https://ticket.bws-hofheim.de">uns</a>.<p>
    <p> Geben Sie Ihre Logindaten niemals weiter. Achten Sie darauf, wo Sie sich anmelden und auf welche Links Sie klicken. Alle unsere Seiten enden mit "bws-hofheim.de" </p>';
        
}
//Sollte der Nutzer bereicht angemeldet sein weiterleitung
if(empty($logout))
{
    if(isset($_SESSION ["user_logged_in"])){
        if(isset($_SESSION["redirect"])){
            header('Location:'.$_SESSION["redirect"].'');
        }
        else{
            header('Location:'.$domain.'lehrende');
        }
    }
}
//Funktion Passwort vergessen hier!
if($reset_passwort == "true")
    {
        echo '<h1 class="header_antrag"> Passwort zurücksetzen </h1>';
        echo'<div class="login">';
        echo '<p> Bitte geben Sie Ihre E-Mail-Adresse ein, Sie werden eine E-Mail mit Informationen zum Zurücksetzen Ihres Passworts erhalten.';
        echo '<form method="POST" class="login_form">
        <label> E-Mail-Adresse:</label>  </br>
        <input class="status_input" type="email" name="email_reset" required> </br>
        <button class="status_button" type="submit">Passwort zurücksetzen</button>

        </form>';
    
        //Prüfung ob Formular abgesendet wurde
        if($_SERVER['REQUEST_METHOD'] == "POST")
        {
            $Anfrage = "SELECT * FROM bwshofheim.lehrende WHERE EMail = '{$_SESSION["email_reset"]}'"; //SQL Abfrage wird gebaut
            $ergebnis = $db_link->query($Anfrage); //SQL Abfrage wird an die Datenbank übergeben 

            //Antwort der DB auf Korrektheit prüfen
            if(!$ergebnis){
                $_SESSION["Fehler_ID"] = "1";
                echo '<div class ="status_fehler"> <p>Datenbankverbindung fehlgeschlagen, bitte kontaktieren Sie den Support unter ticket.bws-hofheim.de und nennen den Fehlercode: </p> </div>' . $db_link->connect_error;
                echo $db_link->connect_error;
            }

            //Prüfen ob die E-Mail in der Datenbank verhanden ist
            if($ergebnis->num_rows == 0){
                $_SESSION["Fehler_ID"] = "1";
                echo'<div class ="status_fehler"> <p>Die von Ihnen eingegebene E-Mail exisitiert nicht. Bitte probieren Sie es erneut!</p> </div>';
                //IP-Adresse des Nutzers auslese
                $user_ip = $_SERVER['REMOTE_ADDR'];
                        
                //Versuchtes zurücksetzen des Passworts wird gespeichert
                $Anfrage = "INSERT INTO log_all(IP_ADDR, action_log, User, Datum, Userdevice) 
                            VALUES ('$user_ip', 'Passwort zurücksetzen angefordert - E-Mail nicht vorhanden', '{$_SESSION["email_reset"]}', '$uhrzeit', '$infos_user')";
                                                                        
                $db_link->query($Anfrage);
            }

            //Wenn bei der DB keine Fehler sind wird Passwort zurücksetzen autorisiert
            if(empty($_SESSION["Fehler_ID"]))
            {
            //Daten aus DB laden
            $daten = $ergebnis->fetch_array(); 
            $ID = $daten[0];
            $Nachname = $daten[1];
            $Vorname = $daten[2];
            $EMail = $daten[4];

            $Name = $Vorname ." ".$Nachname;

            //Key zum zurücksetzen des Passwortes wird generiert und in die Datenbank geschrieben
            $reset_key = random_String();

            //reset_key für DB hashen
            //$reset_key_hash = password_hash($reset_key, PASSWORD_DEFAULT);

            //Schreibt den genrierten Link in Datenbank und die Uhrzeit zum Ablauf des Linkes in die Datenbank
            $Anfrage = "UPDATE lehrende
                        SET link_reset_passwort = '$reset_key', timer_pw = '$uhrzeit'
                        WHERE ID = '$ID'";
            $ergebnis = $db_link->query($Anfrage); //SQL Abfrage wird an die Datenbank übergeben 


                if(!$ergebnis){
                    $_SESSION["Fehler_ID"] = "1";
                    echo '<div class ="status_fehler"> <p>Datenbankverbindung fehlgeschlagen, bitte kontaktieren Sie den Support unter ticket.bws-hofheim.de und nennen den Fehlercode:</p> </div>' . $db_link->connect_error;
                    echo $db_link->connect_error;
                }
            
                if(empty($_SESSION["Fehler_ID"])){
                //Link zum Zurücksetzen bauen
                $Link_reset = $domain. "login.php?reset_pw_key=" .$reset_key. "&user=" .$ID;


                //EMail zum zurücksetzen bauen
                $mail->addAddress($EMail, $Name);
                $mail->isHTML();
                $mail->Subject = 'Passwort zurücksetzen';
                $mail->Body    = 'Sie haben soeben das zurücksetzen Ihres Passwortes angefordert. Bitte klicken Sie auf diesen Link um Ihr Passwort neu zu setzen. </br> '. $Link_reset.'';
                    if(!$mail->Send()) 
                    {
                        $_SESSION["Fehler_EMAIL_Senden"] = '"Mailer Error: " '. $mail->ErrorInfo.'';
                        echo $_SESSION["Fehler_EMAIL_Senden"];
                    }


                    //IP-Adresse des Nutzers auslese
                    $user_ip = $_SERVER['REMOTE_ADDR'];
                        
                    //Versuchte Anmeldung wird in der DB gespeichert
                    $Anfrage = "INSERT INTO log_all(IP_ADDR, action_log, User, Datum, Userdevice) 
                                VALUES ('$user_ip', 'Passwort zurücksetzen angefordert - E-Mail versendet', '{$_SESSION["email_reset"]}', '$uhrzeit', '$infos_user')";

                                                        
                    $ergebnis = $db_link->query($Anfrage);
                    //Medlung wird in Globale Session Variable geschrieben um diese danach auf der login.php default Seite anzuzeigen 
                    $_SESSION["Info_login.php"] = '<div class="status_success"><p>Sie haben eine E-Mail mit Informationen zum setzen des Passwortes erhalten.</p></div>';
                    echo '<meta http-equiv="refresh" content="0; URL='.$domain.'login.php">';

                }
            }
        }
    }

    //Prüfung ob die korrekten Parameter aus der E-Mail zum zurücksetzen übergeben wurden
    if(isset($reset_pw_key) && isset($user_id))
    {
        echo '<h1 class="header_antrag"> Neues Passwort vergeben: </h1>';
        echo'<div class="login">';
            

        $Anfrage = "SELECT EMail, link_reset_passwort, timer_pw FROM bwshofheim.lehrende WHERE ID = '$user_id'"; //SQL Abfrage wird gebaut
        $ergebnis = $db_link->query($Anfrage); //SQL Abfrage wird an die Datenbank übergeben 

        //Antwort der DB auf Korrektheit prüfen
        if(!$ergebnis){
            $_SESSION["Fehler_ID_DB"] = "1";
            echo '<div class ="status_fehler"> <p>Datenbankverbindung fehlgeschlagen, bitte kontaktieren Sie den Support unter ticket.bws-hofheim.de und nennen den Fehlercode:'. $db_link->connect_error.' </p> </div>';
        }

        //Prüfen ob die E-Mail in der Datenbank verhanden ist
        if($ergebnis->num_rows == 0){
            $_SESSION["Fehler_ID_DB"] = "1";
            echo'<div class ="status_fehler"> <p>Bei dem Abrufen Ihrer Daten ist etwas schief gelaufen, bitte probieren Sie es erneut oder wenden Sie sich an ticket.bws-hofheim.de</p> </div>';
        }
        if(empty($_SESSION["Fehler_ID_DB"])){
        //Daten aus der Datenbank werden geladen und in eigene Variablen geschrieben    
            $daten = $ergebnis->fetch_array(); 
            $email = $daten[0];
            $link_reset_passwort = $daten[1];
            $timer_pw = $daten[2];

            //Datum wird zu UNIX Stempel umgewandelt 
            $timer_pw = strtotime($timer_pw); 
            //Auf das Datum werden 30 Minuten gerechnet -> dauer des Links 
            $timer_pw = strtotime('+30 minutes', $timer_pw);  
            $aktuelles_DatumUhrzeit = strtotime($uhrzeit);

            //unterschied wird berechnet, Wert wird negativ wenn über 30 min
            $unterschied = $timer_pw - $aktuelles_DatumUhrzeit;

            if($unterschied > 0)
            {
                //Prüfung ob der aus der E-Mail übergeben key dem key aus der DB entspricht
                if($link_reset_passwort == $reset_pw_key)
                {
                    echo '<p> Bitte geben Sie Ihr neues Passwort ein und bestätigen Sie die Änderung.';
                    echo '<form method="POST" class="login_form">
                    <label> Neues Passwort:</label>  </br>
                    <input class="status_input" type="password" name="passwort_reset_1" required> </br>
                    <label> Bitte geben Sie Ihr Passwort erneut ein: </label> </br>
                    <input class="status_input" type="password" name="passwort_reset_2" required> </br>
                    <button class="status_button" type="submit">Passwort zurücksetzen</button>
            
                    </form>';
                }
                else{
                    echo'<div class ="status_fehler"> <p>Ihr Link ist ungültig, bitte probieren Sie es erneut oder wenden Sie sich an ticket.bws-hofheim.de</p> </div>';
                }
            }
            else{
                echo'<div class ="status_fehler"> <p>Ihr Link ist abgelaufen, bitte probieren Sie es erneut. Die Links sind nur 30 Minuten gültig.</p> </div>';
            }
        }

        //Es wird geprüft ob ein Formular über POST gesendet hat 
        if($_SERVER['REQUEST_METHOD'] == "POST")
        {

            //Prüfung ob bisher Fehler aufgetreten sind, ob die Passworter übereinstimmen und ob die 30 Minuten abgelaufen sind -> erneute Prüfung 
            //um zu verhindern dass der Link nache einmaligen Seitenladen "unendlich gültig ist.
            if(empty($_SESSION["Fehler_ID_DB"] && $link_reset_passwort == $reset_pw_key && $unterschied > 0))
            {

                // Prüfung ob beide Werte gesetzt sind
                if (isset($neuesPasswort1) && isset($neuesPasswort1))
                {

                    //Überprüfung ob die neuen gesetzten Passwörter übereinstimmen
                    if($neuesPasswort1 == $neuesPasswort2)
                    {

                        //Neues PW wird gehashd in die DB eingetragen
                        $pw_neu_hash = password_hash($neuesPasswort1, PASSWORD_DEFAULT);

                        $Anfrage = "UPDATE lehrende
                        SET Passwort = '$pw_neu_hash', link_reset_passwort = '0', timer_pw = '31.03.2023 - 11:03:41' #Werte werden in die DB geschreiben und der alte Link und die alte Zeit überschreiben
                        WHERE ID = '$user_id'";
                        $ergebnis = $db_link->query($Anfrage); //SQL Abfrage wird an die Datenbank übergeben 
                        
                        if(!$ergebnis)
                        {
                            $_SESSION["Fehler_ID"] = "1";
                            echo '<div class ="status_fehler"> <p>Datenbankverbindung fehlgeschlagen, bitte kontaktieren Sie den Support unter ticket.bws-hofheim.de und nennen den Fehlercode:</p> </div>' . $db_link->connect_error;
                            echo $db_link->connect_error;
                        }

                        //IP-Adresse des Nutzers auslese
                        $user_ip = $_SERVER['REMOTE_ADDR'];
                
                        $Anfrage = "INSERT INTO log_all(IP_ADDR, action_log, User, Datum, Userdevice) 
                                    VALUES ( '$user_ip', 'Passwort geändert', '$user_id', '$uhrzeit', '$infos_user')";
                        
                        $db_link->query($Anfrage);
            
                        if(empty($_SESSION["Fehler_ID"]))
                        {

                            //EMail zur bestätigen der PW änderung schicken
                            $mail->addAddress($email);
                            $mail->isHTML();
                            $mail->Subject = 'Passwort erfolgreich zurückgesetzt';
                            $mail->Body    = 'Sie haben soeben Ihr Passwortes zurückgesetzt.  </br>';
                                if(!$mail->Send()) 
                                {
                                    $_SESSION["Fehler_EMAIL_Senden"] = '"Mailer Error: " '. $mail->ErrorInfo.'';
                                    echo $_SESSION["Fehler_EMAIL_Senden"];
                                }
                                $_SESSION["Info_login.php"] = '<div class="status_success"><p>Sie haben Ihr Passwort erfolgreich gesetzt. Sie können sich nun mit Ihrem neuen Passwort anmelden.</p></div>';
                                echo '<meta http-equiv="refresh" content="0; URL='.$domain.'login.php">';
                        }

                    }
                    else 
                    {
                    echo'<div class ="status_fehler"> <p>Die Passwörter stimmen nicht überrein. Es ist keine Änderung erfolgt.</p> </div>';
                    }
                }
                else 
                {
                    echo '<div class ="status_fehler"> <p>Bitte füllen Sie beide Passwortfelder aus.</p> </div>';
                }
            }   
            else 
            {
                echo '<div class ="status_fehler"> <p>Ein unbekannter Fehler ist aufgetreten, bitte probieren Sie es erneut.</p> </div>';
            }
        }
    }

    if($logout == "true")
    {
        session_destroy();
        echo '<meta http-equiv="refresh" content="0; URL='.$domain.'login.php">';
    }

?>
</div>
</div>
</div>