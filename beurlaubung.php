<!DOCTYPE html>
<head>

<title>Antrag auf Beurlaubung - Compass </title>

</head>


<?php
session_start();

include 'includes/header.php';
require_once ('function/DBconfig.php');
require_once ('function/SMTPmail.php');

header('Cache-Control: no-cache, no-store, must-revalidate'); // HTTP 1.1
header('Pragma: no-cache'); // HTTP 1.0
header('Expires: 0'); // Proxies

    if(isset($_SESSION["antrag_erfolgreich"]))
    {
        unset($_SESSION["antrag_erfolgreich"]);
        header('Location: beurlaubung.php'); //Leitet per GET auf beaurlaubung.php weiter
    }
    //Holt alle Variablen über GET
    $Akzeptiert = $_GET["akzeptiert"];

    //Funktion prüft alle Eingaben und verhindert INSERT attacken
    function check($variable)
    {
        $variable = trim($variable);
        $variable = stripslashes($variable);
        $variable = htmlspecialchars($variable);
        if (empty($variable))
        {
            $_SESSION["Fehler"] = '<div class="fehler"><p>Füllen Sie bitte alle Felder aus!<p> </div> </br>';
        }
        return $variable;
    }

    //Prüft alle Daten auf korrektheit 
    function validateDATUM ($datum_form)
    {
        $datum_format = "Y-m-d";
        $datum_objekt = DateTime::createFromFormat($datum_format, $datum_form); 
        if ($datum_objekt == false){
            $_SESSION["Fehler_Datumsform"] = '<div class="fehler"><p>Überprüfe Sie das Format der Daten!<p> </div> </br>';
        }
    }

    //Nach erfolgreichem absenden werden alle SESSION Variablen gelöscht
    function DeletSessionVariables ()
    {
        unset($_SESSION["name_as"]);
        unset($_SESSION["email_as"]);
        unset($_SESSION["name_sus"]);
        unset($_SESSION["geb_sus"]);
        unset($_SESSION["ort_sus"]);
        unset($_SESSION["straße_sus"]);
        unset($_SESSION["tel_sus"]);
        unset($_SESSION["name_tutor"]);
        unset($_SESSION["kuerzel_tutor"]);
        unset($_SESSION["klasse_sus"]);
        unset($_SESSION["zeitraum_von"]);
        unset($_SESSION["zeitraum_bis"]);
        unset($_SESSION["grund_as"]);
        unset($_SESSION["zeitstempel"]);
        unset($_SESSION["ID"]);
        unset($_SESSION["absenden"]);
        unset($_SESSION["Fehler_Allgemein"]);
        unset($_SESSION["Fehler_EMAIL"]);
        unset($_SESSION["Fehler_Datumsform"]);
        unset($_SESSION["Fehler_Zeitraum"] );
        unset($_SESSION["Fehler_Global"]);
        unset($_SESSION["Fehler_Gebdatum"]);
        unset($_SESSION["Fehler"]);
        unset($_SESSION["Fehler_upload"]);
        unset($_SESSION["E-Mail"]);
        unset($_SESSION["zieldatei"]);
        unset($_SESSION["dateigröße"]);
        unset($_SESSION["dateiformat"]);
        unset($_SESSION["zeitstempel"]);
        unset($_SESSION["id_platzhalter"]);
        unset($_SESSION["datum_beginn"]);
        unset($_SESSION["datum_ende"]);
        unset($_SESSION["aktuelles_datum"]);
        unset($_SESSION["geb_sus_string"]);
        unset($_SESSION["abstand"]); 
        unset($_SESSION["vergangenheit"]);
        unset($_SESSION["ungebohren"]); 
        unset($_SESSION["ID_hochzaehlen"]);
        unset($_SESSION["PW"]);
        unset($_SESSION["PW_hasd"]);
        unset($_SESSION["email_tutor"]);
        unset($_SESSION["name_tutor"]);
        unset($_SESSION["vorname_tutor"]);
        unset($_SESSION["ArtAntrag"]);
    }

            //Select wird gebaut (für Kuerzel auswahl):
            $kurzel_select = "";
            $SQL_Anfrage = "SELECT Kuerzel FROM bwshofheim.lehrende WHERE Tutor = 'true'";
            $Ergebnis = $db_link->query($SQL_Anfrage);
            if ($Ergebnis->num_rows > 0) {
                while ($row = $Ergebnis->fetch_assoc()) {
                    $kurzel_select .= '<option value="' . $row["Kuerzel"] . '">' . $row["Kuerzel"] . '</option>';
                }
            }
    

    // Funktion wird ausgelöst sobald POST-Anfrage kommt -> SESSION Variablen werden erst dann angelegt 
    if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST["absenden"]))
        {
        //Variable Fehler/weitere werden angelegt
        $_SESSION["Fehler_Allgemein"] = "";
        $_SESSION["Fehler_EMAIL"] = "";
        $_SESSION["Fehler_EMAIL_Senden"] = "";
        $_SESSION["Fehler_Datumsform"] = "";
        $_SESSION["Fehler_Zeitraum"] = "";
        $_SESSION["Fehler_Global"] = "";
        $_SESSION["Fehler_Gebdatum"] = "";
        $_SESSION["Fehler"] = "";
        $_SESSION["Fehler_upload"] ="";
        $_SESSION["Fehler_ID_DB"]="";
        $_SESSION["E-Mail"];
        $_SESSION["zieldatei"] = "";
        $_SESSION["dateigröße"] = "";
        $_SESSION["dateiformat"] = "";
        $_SESSION["ID"] = "";
        $_SESSION["PW"] = "";
        $_SESSION["PW_hasd"] = "";
        $_SESSION["email_tutor"]="";
        $_SESSION["nachname_tutor"]="";
        $_SESSION["vorname_tutor"]="";
        $_SESSION["name_tutor"]="";
        $_SESSION["ArtAntrag"]="";

        //Prüfung des Uploads
        if(isset($_FILES["upload"]) && $_FILES["upload"]["size"] > 0)
        {
            $zielordner = "/volume1/web/test-umgebung/bwshofheim/uploads/{$uhrzeit_datei}_";  //Standardverzeichnis wird angelegt 
            $_SESSION["zieldatei"] = $zielordner.basename($_FILES["upload"]["name"]); //Dtei wird gebaut aus uploadname und Speicherort //$_SESSION["zieldatei"] = $zielordner.basename($_FILES["upload"]["name"]);
            $_SESSION["Dateiname"] = basename($_FILES["upload"]["name"]); //Name der Datei wird abgespeichert
            $_SESSION["dateigröße"] = $_FILES["upload"]["size"];  //Größe der Datei wird bestimmt
            $_SESSION["dateiformat"] = strtolower(pathinfo($_SESSION["zieldatei"], PATHINFO_EXTENSION)); //Dateieindung wird ausgelesen

            if($_SESSION["dateigröße"] > 2000000)  //Prüfung der Uploadgröße
            {
                $_SESSION["Fehler_upload"] ='<div class="fehler"><p>Es ist ein maximaler Upload von 2MB erlaubt!<p> </div> </br>';
            }

            //Prüfung des Dateittyps
            if($_SESSION["dateiformat"] != "doc" && $_SESSION["dateiformat"] != "docx"  && $_SESSION["dateiformat"] != "pdf" && $_SESSION["dateiformat"] != "jpg" && $_SESSION["dateiformat"] != "jpeg" && $_SESSION["dateiformat"] != "png" && $_SESSION["dateiformat"] != "gif")
            {
            $_SESSION["Fehler_upload"] ='<div class="fehler"><p>Falsches Dateiformat! Bitte beachten Sie die erlaubten Dateiformate.<p> </div> </br>';
            }  

            //Prüfung ob Datei bereits existiert
            if(file_exists($_SESSION["zieldatei"]))
            {
            $_SESSION["Fehler_upload"] ='<div class="fehler"><p>Datei exisitiert bereits auf dem Server!<p> </div> </br>';
            }
                
        }

        //Übergabe Serverzeit und eindeutige ID
        $_SESSION["zeitstempel"] = $uhrzeit;
        $_SESSION["id"] = $random_id;

        //Ließt alle Variablen ein und prüft diese 
        $_SESSION["name_as"] = check($_POST["name_antragsteller"]);
        $_SESSION["email_as"] = check($_POST["email_antragsteller"]);
        $_SESSION["name_sus"] = check($_POST["name_sus"]);
        $_SESSION["geb_sus"] = check($_POST["geb_sus"]);
        $_SESSION["ort_sus"] = check($_POST["ort_sus"]);
        $_SESSION["straße_sus"] = check($_POST["straße_sus"]);
        $_SESSION["tel_sus"] = check($_POST["tel_sus"]);
        $_SESSION["kuerzel_tutor"] = check($_POST["kuerzel"]);
        $_SESSION["klasse_sus"] = check($_POST["klasse_sus"]);
        $_SESSION["zeitraum_von"] = check($_POST["date_von"]);
        $_SESSION["zeitraum_bis"] = check($_POST["date_bis"]);
        $_SESSION["grund_as"] = check($_POST["grund"]);
        $_SESSION["zeitstempel"] = check($_POST["zeitstempel"]);
        $_SESSION["id_platzhalter"] = check($_POST["ID"]);
        $_SESSION["absenden"] = check($_POST["absenden"]);

        //Prüft die eingegebenen Daten auf korrektheit
        validateDATUM($_SESSION["geb_sus"]);
        validateDATUM($_SESSION["zeitraum_von"]);
        validateDATUM($_SESSION["zeitraum_bis"]);

        //Prüfung E-Mail auf Korrektheit 
        if(filter_var($_SESSION["email_as"], FILTER_VALIDATE_EMAIL)){

        }
        else {
            $_SESSION["Fehler_EMAIL"] = '<div class="fehler"><p>Geben Sie eine gültige E-Mail-Adresse ein!<p> </div> </br>';
        }

        //Prüfung, ob die angegebenen Daten maximal 2 Tage auseiander liegen 
        $_SESSION["datum_beginn"] = strtotime($_SESSION["zeitraum_von"]);
        $_SESSION["datum_ende"] = strtotime($_SESSION["zeitraum_bis"]);
        $_SESSION["aktuelles_datum"] = strtotime($datum);
        $_SESSION["geb_sus_string"] = strtotime($_SESSION["geb_sus"]);

        //Prüft, ob die Daten in einen UNIX-Zeitstempel umgewandelt werden konnten
        if($_SESSION["datum_beginn"] == "false" || $_SESSION["datum_ende"] == "false"  || $_SESSION["aktuelles_datum"] == "false")
        {
            $_SESSION["Fehler_Allgemein"] = '<div class="fehler"><p>Bei Ihrer Anfrage ist ein Fehler aufgetreten, bitte wenden Sie sich an ticket.bws-hofheim.de und nennen Sie den Fehlercode: UNIX-strtotime<p> </div> </br>';
        }

        // Alle Daten werden auf korrektheit geprüft 
        $_SESSION["abstand"] = ($_SESSION["datum_ende"] - $_SESSION["datum_beginn"]);  //Berechnung Anzahl Tage für die der Antrag gestellt wird 
        $_SESSION["vergangenheit"] = ($_SESSION["datum_beginn"] - $_SESSION["aktuelles_datum"]);   //Berechnung Abstand aktuelles Datum und Beginn Beurlaubung
        $_SESSION["ungebohren"] = ($_SESSION["aktuelles_datum"] - $_SESSION["geb_sus_string"]);  //Kontrolle, dass das Geburtsdatum nicht in der Zukunft liegt                    


        if($_SESSION["abstand"] <= "172800"){
            $_SESSION["ArtAntrag"] = "bis2Tage";
        }
        else {
            $_SESSION["ArtAntrag"] = "ueber2Tage";
        }
        // Prüfung Zeitspanne 
        if($_SESSION["abstand"] < 0){
            $_SESSION["Fehler_Zeitraum"] = '<div class="fehler"><p>Das Enddatum liegt vor dem Startdatum.<p> </div> </br>';
        }

        if($_SESSION["vergangenheit"] < 0 ){
            $_SESSION["Fehler_Zeitraum"] = '<div class="fehler"><p>Das Startdatum darf nicht in der Vergangenheit liegen.<p> </div> </br>';
        } 

        if($_SESSION["ungebohren"] < 0){
            $_SESSION["Fehler_Gebdatum"] = '<div class="fehler"><p>Das Geburtsdatum liegt in der Zukunft.<p> </div> </br>';
        }

        //Daten des Tutors werden aus der DB geladen
        $Anfrage = "SELECT EMail FROM bwshofheim.lehrende WHERE Kuerzel = '{$_SESSION['kuerzel_tutor']}'"; //SQL Abfrage wird gebaut '{$_SESSION['ID']}'
        $ergebnis = $db_link->query($Anfrage); //SQL Abfrage wird an die Datenbank übergeben 

        //Antwort der DB auf Korrektheit prüfen
        if(!$ergebnis){
            $_SESSION["Fehler_ID_DB"] ='<div class ="status_fehler"> <p>Datenbankverbindung fehlgeschlagen, bitte kontaktieren Sie den Support unter ticket.bws-hofheim.de und nennen den Fehlercode: </p> </div>' . $db_link->connect_error;
            echo $db_link->connect_error;
        }
        if(empty($_SESSION["Fehler_ID_DB"])){
        //Daten aus der Datenbank werden geladen und in eigene Variablen geschrieben    
            $daten = $ergebnis->fetch_array(); 
            $_SESSION["email_tutor"] = $daten[0];
        }


        // Zusammenführen aller Fehlercodes
        $_SESSION["Fehler_Global"] .=  $_SESSION["Fehler_Allgemein"] . $_SESSION["Fehler_EMAIL"] . $_SESSION["Fehler_Datumsform"] . $_SESSION["Fehler_Zeitraum"] . $_SESSION["Fehler_Gebdatum"] . $_SESSION["Fehler"] . $_SESSION["Fehler_upload"] . $_SESSION["Fehler_ID_DB"];

        //Prüfung ob bisher Fehler aufgetreten sind
        if(empty($_SESSION["Fehler_Global"]))
        {
        
            //Wenn bisher keine Fehler Prüfung ob eine Datei hochgeladen wurde
            if(isset($_FILES["upload"]) && $_FILES["upload"]["size"] > 0)
            {
                    //Datei wird wenn alle Test erfolgreich waren neu abgespeichert
                    if(move_uploaded_file($_FILES["upload"]["tmp_name"], $zielordner . $_FILES["upload"]["name"]))
                    {
                    }
                        else
                        {
                        $_SESSION["Fehler_upload"] ='<div class="fehler"><p>Es ist ein unbekannter Fehler beim Dateiupload aufgetreten<p> </div> </br>';
                        switch ($_FILES['upload']['error']) {
                            case UPLOAD_ERR_INI_SIZE:
                                echo 'UPLOAAD_ERR_INI_SIZE';
                                break;
                            case UPLOAD_ERR_NO_FILE:
                                echo 'UPLOAD_ERR_NO_FILE';
                                break;
                            case UPLOAD_ERR_NO_TMP_DIR:
                                echo 'UPLOAD_ERR_NO_TMP_DIR';
                                break;
                            case UPLOAD_ERR_CANT_WRITE:
                                echo 'UPLOAD_ERR_CANT_WRITE';
                                break;
                            case UPLOAD_ERR_EXTENSION:
                                echo 'UPLOAD_ERR_EXTENSION';
                                break;  
                            // add more cases as needed
                        }
                        }
            }
        }


        // Wenn alle Prüfungen bestanden Eintrag DB; E-Mail senden; User redirecten
        if(empty($_SESSION["Fehler_Global"])&&empty($_SESSION["Fehler_upload"]))
                {

                //ID wird generiert 
                //Erfolg erst nachdem alle Test bestanden sind, um doppelbennenungen zu verhinern 
                $_SESSION["ID_hochzaehlen"] = file_get_contents("function/ID_Antraege.txt"); //Lädt den aktuellen Stand aus der TXT
                //Die eindeutige 8 stellige ID wird gebaut 
                $_SESSION["ID"] = $random_id . $_SESSION["ID_hochzaehlen"];
                //Zahl in der TXT wird hochgezählt
                $_SESSION["ID_hochzaehlen"] = $_SESSION["ID_hochzaehlen"] + 1;
                //Hochgezählter neuer Wert wird in die txt geschrieben 
                file_put_contents("function/ID_Antraege.txt", $_SESSION["ID_hochzaehlen"]); 

                //Passwort zur erweiterten Daten einsicht wird generiert 
                $_SESSION["PW"] = random_String(); //Für Ausgabe in E-Mail
                //Passwort wird für Datenbank gehashed 
                $_SESSION["PW_hasd"] = password_hash($_SESSION["PW"], PASSWORD_DEFAULT);

                $Anfrage = "INSERT INTO antraege_beurlaubung(ID_PHP, Art, Datum_gestellt, name_as, email_as, name_SuS, Geb_SuS, Wohnort_SuS, Straße_SuS, tel_SuS, kuerzel_tutor, klasse_sus, zeitraum_von, zeitraum_bis, grund_as, Passwort, status_antrag, begruendung) 
                VALUES ('{$_SESSION["ID"]}', '{$_SESSION["ArtAntrag"]}', '{$uhrzeit}','{$_SESSION["name_as"]}', '{$_SESSION["email_as"]}', '{$_SESSION["name_sus"]}', '{$_SESSION["geb_sus"]}', '{$_SESSION["ort_sus"]}', '{$_SESSION["straße_sus"]}', '{$_SESSION["tel_sus"]}', 
                 '{$_SESSION["kuerzel_tutor"]}', '{$_SESSION["klasse_sus"]}', '{$_SESSION["zeitraum_von"]}', '{$_SESSION["zeitraum_bis"]}', '{$_SESSION["grund_as"]}', '{$_SESSION["PW_hasd"]}', 'offen','')";
                
                                    if($db_link->query($Anfrage) === TRUE)
                                    {
                                        //Dateiupload wird in der DB vermerkt
                                        if(isset($_FILES["upload"]) && $_FILES["upload"]["size"] > 0)
                                        {
                                            $Anfrage ="INSERT INTO antraege_beurlaubung_dateiupload(ID_PHP, Dateiname, Dateityp, Groesse, Pfad, Zeitstempel)
                                                       VALUES ('{$_SESSION["ID"]}','{$_SESSION["Dateiname"]}','{$_SESSION["dateiformat"]}','{$_SESSION["dateigröße"]}','{$_SESSION["zieldatei"]}','{$uhrzeit}')";
                                            $ergebnis = $db_link->query($Anfrage);

                                            if(!$ergebnis)
                                            {
                                                echo'<div class ="status_fehler"> <p>Datenbankverbindung fehlgeschlagen, bitte kontaktieren Sie den Support unter ticket.bws-hofheim.de und teilen Sie uns folgendes mit: '.$Anfrage.'</p> </div>';
                                            }
                                    
                                        }
                                        

                                        $_SESSION["antrag_erfolgreich"] = "aspdi8ufgaduas+ßed8w321e";
                                        //E-Mail an Antragsteller wird gesendet
                                        $mail->addAddress($_SESSION["email_as"], $_SESSION["name_as"]);
                                        //$mail->addAttachment("");
                                        $mail->isHTML();
                                        $mail->Subject = 'Dein Antrag wurde erfolgreich gestellt!'.$_SESSION["ID"];
                                        $mail->Body    = 'Dies ist eine erste Testemail'.$_SESSION["PW"];
                                            if(!$mail->Send()) {
                                                $_SESSION["Fehler_EMAIL_Senden"] = '"Mailer Error: " '. $mail->ErrorInfo.'';
                                            }

                                        //Alle vorherigen gespeicherten Inhalte werden gelöscht
                                        $mail->clearAddresses();
                                        $mail->clearAllRecipients();
                                        $mail->clearAttachments();

                                        //Email an die Lehrkraft wird vorbereitet
                                        $mail->addAddress($_SESSION["email_tutor"], $_SESSION["name_tutor"]);
                                        //$mail->addAttachment("");
                                        $mail->isHTML();
                                        $mail->Subject = 'Neuer Antrag auf Beurlaubung';
                                        $mail->Body    = 'Dies ist eine erste Testemail an den Tutur. Antrag hat die ID:'.$_SESSION["ID"];
                                            if(!$mail->Send()) {
                                                $_SESSION["Fehler_EMAIL_Senden"] = '"Mailer Error: " '. $mail->ErrorInfo.'';
                                            }
                                        header("Cache-Control: no-cache, must-revalidate"); // setzt Cache-Control Header
                                        header("Expires: Sat, 26 Jul 1997 05:00:00 GMT"); // setzt Ablaufdatum in der Vergangenheit
                                        //echo '<meta http-equiv="refresh" content="0; URL='.$domain.'status.php?id='.$_SESSION["ID"].'">';
                                        DeletSessionVariables (); //Löscht alle Session-Variablen die für das Formular angelegt wurden
                                        exit;
                                    }
                            
                                else 
                                {
                                     echo '<div class="fehler"><p>Fehler beim Datenbankeintrag: '.$db_link->error.'</p></div></br>';
                                }

                    
        }
        else{
            echo $_SESSION["Fehler_upload"];
            echo $_SESSION["Fehler_Global"];
            $_SESSION["Fehler_Global"] = '<div class="fehler"><p>Bitte korrigieren Sie alle unten makierten Fehler! </br> Tipp: Ihre Daten bleiben solange gespeichert, bis Sie das Fenster schließen!<p> </div> </br>';
        }
    }
?>
        <div class="background">
            <div class="seiteninhalt">
            <?php

    
        if($Akzeptiert == "true")
        {
        echo '<h1 class="header_willkommen">Antrag auf Beurlaubung </h1>
        <hr>
        <h1 class="header_antrage">Bitte füllen Sie das Formular vollständig aus. </h1>
        <h2>Schritt 2 von 2</h2>';

        echo '
                <div>
                    <div class="antrag_formular">
                        <p> Vorlage erfolgt automatisch beim Klassenlehrer/in oder Tutor/in</p>
                        <div> <p>- Sie haben die Bedingungen für eine Beurlaubung bereits auf der vorherigen Seite akzeptiert. -<p/> </div>
                        
                        <div class="formular">
                        '.$_SESSION["Fehler_Global"].$_SESSION["Fehler_Allgemein"].$_SESSION["Fehler_Datumsform"].$_SESSION["Fehler"].'
                        <form method="POST" action="beurlaubung.php?akzeptiert=true" enctype="multipart/form-data">
                        
                            <label>Name, Vorname der Erziehungsberechtigten Antragsteller: </label> </br>
                            <input value="'.$_SESSION["name_as"].'"type="text" name="name_antragsteller" required> </br>

                            <label>E-Mail-Adresse des Antragstellers: </label> </br>
                            <input value="'.$_SESSION["email_as"].'" type="email" name="email_antragsteller" required> </br>
                            (Über diese Andresse erreichen wir Sie.) </br>

                            '.$_SESSION["Fehler_EMAIL"].'

                            <label>Name, Vorname des/der Schülers/Schülerin </label> </br>
                            <input value="'.$_SESSION["name_sus"].'" type="text" name="name_sus" required> </br>

                            <label>Geburtsdatum des/der Schülers/Schülerin </label> </br>
                            <input value="'.$_SESSION["geb_sus"].'" class="datum" type="date" name="geb_sus" required> </br>

                            '.$_SESSION["Fehler_Gebdatum"].'

                            <label>PLZ, Ort</label> </br>
                            <input value="'.$_SESSION["ort_sus"].'" type="text" name="ort_sus" required> </br>

                            <label>Straße, Hausnummer</label> </br>
                            <input value="'.$_SESSION["straße_sus"].'" type="text" name="straße_sus" required> </br>

                            <label>Telefonnummer</label> </br>
                            <input value="'.$_SESSION["tel_sus"].'" type="number" name="tel_sus" required> </br>

                            <label>Kürzel Tutor/in</label></br>
                            <select  class="kuerzel" name="kuerzel" required>
                                <option value=""selected disabled>- Bitte wählen -</option>'.
                                
                                $kurzel_select //Kuerzel aus DB werden in das Dropdownmenü geschrieben

                            .' </select> </br>

                            <label>Klasse</label> </br>
                            <input value="'.$_SESSION["klasse_sus"].'" type="text" name="klasse_sus"required> </br>

                            <label>Beurlaubung von - bis: </label> </br>
                            <input value="'.$_SESSION["zeitraum_von"].'" class="datum" type="date" name="date_von"required><input value="'.$_SESSION["zeitraum_bis"].'" class="datum" type="date" name="date_bis"required> </br>

                            '.$_SESSION["Fehler_Zeitraum"].'

                            <label>Es liegt folgender wichtiger Grund für eine Beurlaubung vor:</label> </br>
                            <textarea id="value" class="grund" maxlength="500"  name="grund"required>'.$_SESSION["grund_as"].' </textarea></br>
                            (maximal 500 Zeichen) </br>




                            <label>Optional: Datei hochladen: </label> </br>
                            <input type="file" id="upload" name="upload" accept=".doc, .docx, .pdf, image/*"> </br>
                            (maximale Dateigröße: 2 MB; Nur doc|docx|pdf|jpg|jpeg|png|gif Dateien.) </br>





                            </br>
                            <label>Unterschrift</label></br>
                            <canvas id="signature" class="unterschrift" name="unterschrift" required></canvas>
                            <br>
                            <button class="button_unterschrift" onclick="clearSignature(event)">Unterschrift löschen</button>
                            <script>
                                var canvas = document.getElementById("signature");
                                var context = canvas.getContext("2d");

                                canvas.addEventListener("mousedown", startDrawing);
                                canvas.addEventListener("mousemove", draw);
                                canvas.addEventListener("mouseup", stopDrawing);
                                canvas.addEventListener("touchstart", startDrawingTouch);
                                canvas.addEventListener("touchmove", drawTouch);
                                canvas.addEventListener("touchend", stopDrawingTouch);

                                var isDrawing = false;
                                var lastX, lastY;

                                function startDrawing(e) {
                                    e.preventDefault();
                                    isDrawing = true;
                                    lastX = e.offsetX;
                                    lastY = e.offsetY;
                                }

                                function draw(e) {
                                    e.preventDefault();
                                    if (!isDrawing) return;
                                    context.beginPath();
                                    context.moveTo(lastX, lastY);
                                    context.lineTo(e.offsetX, e.offsetY);
                                    context.stroke();
                                    lastX = e.offsetX;
                                    lastY = e.offsetY;
                                }

                                function stopDrawing() {
                                    isDrawing = false;
                                }

                                function startDrawingTouch(e) {
                                    e.preventDefault();
                                    var touch = e.touches[0];
                                    var mouseEvent = new MouseEvent("mousedown", {
                                        clientX: touch.clientX,
                                        clientY: touch.clientY
                                    });
                                    canvas.dispatchEvent(mouseEvent);
                                }

                                function drawTouch(e) {
                                    e.preventDefault();
                                    var touch = e.touches[0];
                                    var mouseEvent = new MouseEvent("mousemove", {
                                        clientX: touch.clientX,
                                        clientY: touch.clientY
                                    });
                                    canvas.dispatchEvent(mouseEvent);
                                }

                                function stopDrawingTouch() {
                                    e.preventDefault();
                                    var mouseEvent = new MouseEvent("mouseup", {});
                                    canvas.dispatchEvent(mouseEvent);
                                }

                               function clearSignature(event) {
                                    event.preventDefault();
                                    context.clearRect(0, 0, canvas.width, canvas.height);
                                }
                            </script>

                            </br>
                            <input type="hidden" name="zeitstempel" value="true">
                            <input type="hidden" name="ID" value="true">

                            <button type="submit"  class="abschicken" name="absenden" value="true">Antrag auf Beurlaubung abschicken.</button>
                            </form>
                            <script>
                        </div>
                        
                    </div>
            </div>
        </div>

        ';     
                            }   

    else{
        echo '<div> <p> Bitte akzeptieren Sie die Bedingungen auf der vorherigen Seite! <p/> </div>';
    }
?>
    </div>
</body>
