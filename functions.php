<?php
  /*
  Copyright © 2009,2015,2022  Siggi Bjarnason.
  Licensed under GNU GPL v3 and later. Check out LICENSE.TXT for details
  or see <https://www.gnu.org/licenses/gpl-3.0-standalone.html>

  Central collection of various functions
  */

  use PHPMailer\PHPMailer\PHPMailer;
  use PHPMailer\PHPMailer\Exception;

  function printPg($strMsg,$strType="normal")
  {
    switch(strtolower($strType))
    {
      case "error":
        print "<p class=\"Error\">$strMsg</p>\n";
        break;
      case "note":
        print "<p class=\"BlueNote\">$strMsg</p>\n";
        break;
      case "alert":
        print "<p class=\"LargeAttnCenter\">$strMsg</p>\n";
        break;
      case "attn":
        print "<p class=\"Attn\">$strMsg</p>\n";
        break;
      case "center":
        print "<p class=\"MainTextCenter\">$strMsg</p>\n";
        break;
      case "normal":
        print "<p class=\"MainText\">$strMsg</p>\n";
        break;
      case "h1":
        print "<p class=\"Header1\">$strMsg</p>\n";
        break;
      case "h2":
        print "<p class=\"Header2\">$strMsg</p>\n";
        break;
      case "tmh2":
        print "<p class=\"MMH2\">$strMsg</p>\n";
        break;
      default:
        error_log("unkown type $strType in printpg, printing as maintext");
        print "<p class=\"MainText\">$strMsg</p>\n";
    }
  }

  function ShowErrHead()
  {
    $ROOTPATH = $GLOBALS["ROOTPATH"];
    $HeadImg = $GLOBALS["HeadImg"];
    $CSSName = $GLOBALS["CSSName"];
    $ErrMsg = $GLOBALS["ErrMsg"];
    $ImgHeight = "150";
    $imgname = $ROOTPATH . $HeadImg;
    print "<!DOCTYPE HTML PUBLIC \"-//W3C//DTD HTML 4.01 Transitional//EN\"\n\"http://www.w3.org/TR/html4/loose.dtd\">";
    print "<HTML>\n<HEAD>\n<title>\nTechnical Difficulties\n</title>\n";
    print "<link href=\"$CSSName\" rel=\"stylesheet\" type=\"text/css\">\n</HEAD>\n";
    print "<body>\n";
    print "<div id=\"left\"></div>";
    print "<div id=\"right\"></div>";
    print "<div id=\"top\"></div>";
    print "<div id=\"bottom\"></div>";
    print "<div class=\"BlacktblHead\">";
    print "<TABLE border=\"0\" cellPadding=\"4\" cellSpacing=\"0\">\n";
    print "<TR>\n";
    print "<TD align=\"center\" vAlign=\"middle\">\n";
    print "<img border=\"0\" src=\"$imgname\" align=\"center\" height=\"$ImgHeight\">\n";
    print "</TD>\n";
    print "</TR>\n";
    print "</TABLE>\n</div>\n</div>\n";
    printPg("Technical Difficulties","h1");
    printPg("$ErrMsg</p>\n","attn");
    exit;
  }

  function Array2String($input)
  {
    return json_encode($input);
  }

  function QuerySQL($strQuery)
  {
    $dbh = $GLOBALS["dbh"];
    $arrReturn = array();
    $x = stripos($strQuery,"from");
    $y = stripos($strQuery,"where");
    $strFrom = substr($strQuery,$x,$y-$x);
    try
    {
      $Result = $dbh->query($strQuery);
    }
    catch(Throwable $e)
    {
      $eLine = $e->getLine();
      $eFile = $e->getFile();
      $eCode = $e->getCode();
      $errMsg = "Fatal error #$eCode when executing the query on line $eLine in $eFile. ";
      error_log($errMsg. $e->getMessage());
      error_log($strQuery);
      $errMsg = "Fatal error when executing the query. " . $e->getMessage();
      return [-1,$errMsg];
    }
    if(!$Result)
    {
      error_log("Failed to fetch data. Error (". $dbh->errno . ") " . $dbh->error);
      error_log($strQuery);
      $errMsg = "Failed to fetch data $strFrom";
      return [-1,$errMsg];
    }
    $rowcount=mysqli_num_rows($Result);
    while($Row = $Result->fetch_assoc())
    {
      $arrReturn[] = $Row;
    }
    return [$rowcount,$arrReturn];
  }

  function GetSQLValue($strQuery)
  {
    $QueryData = QuerySQL($strQuery);

    if($QueryData[0] == 1)
    {
      $arrTmp = array_values($QueryData[1][0]);
      return $arrTmp[0];
    }
    else
    {
      if($QueryData[0] == 0)
      {
        return 0;
      }
      else
      {
        $strMsg = Array2String($QueryData[1]);
        error_log("GetSQL Expected one row, that's not what I got. $strQuery Rowcount: $QueryData[0] Msg:$strMsg");
        return -15;
      }
    }
  }

  function UpdateSQL($strQuery,$type="call")
  {
    $DefaultDB = $GLOBALS["DefaultDB"];
    $dbh = $GLOBALS["dbh"];
    $SupportEmail = $GLOBALS["SupportEmail"];
    $FromEmail = $GLOBALS["FromEmail"];

    try
    {
      $Result = $dbh->query($strQuery);
    }
    catch(Throwable $e)
    {
      $eLine = $e->getLine();
      $eFile = $e->getFile();
      $eCode = $e->getCode();
      $errMsg = "Fatal error #$eCode when executing the query on line $eLine in $eFile. ";
      error_log($errMsg. $e->getMessage());
      error_log($strQuery);
      printPg("Database $type failed!","error");
      return false;
    }

    if($Result)
    {
      $NumAffected = $dbh->affected_rows;
      return TRUE;
    }
    else
    {
      $strError = "Database $type failed. Error (". $dbh->errno . ") " . $dbh->error . "\n";
      If($dbh->errno =="1451")
      {
        print "\n<p class=\"error\">Unable to delete the selected value as it is still in use in other parts of the system</p>\n";
      }
      else
      {
        printPg("Database $type failed!","error");
        error_log($strError);
        error_log("SQL: $strQuery");
        if(EmailText("$SupportEmail","Automatic Error Report","$strError\n$strQuery",$FromEmail))
        {
          printPg("We seem to be experiencing technical difficulties. We have been notified. Please try again later. Thank you.","error");
        }
        else
        {
          $strError = str_replace("\n","<br>\n",$strError);
          printPg("We seem to be experiencing technical difficulties. " .
                  "Please send us a message at $SupportEmail with information about " .
                  "what you were doing.","error");
        }
      }
      return FALSE;
    }
  }

  function CleanSQLInput($InVar)
  {
    $InVar = str_replace("\\","",$InVar);
    $InVar = str_replace("'","\'",$InVar);
    $InVar = str_replace(";","",$InVar);
    return $InVar;
  }

  function CleanReg($InVar)
  {
    $InVar = strip_tags($InVar);
    $InVar = str_replace("\\","",$InVar);
    $InVar = str_replace("=","",$InVar);
    $InVar = str_replace('"',"",$InVar);
    $InVar = str_replace("'","",$InVar);
    $InVar = str_replace(";","",$InVar);
    return $InVar;
  }

  function SpamDetect($InVar)
  {
    $dbh = $GLOBALS["dbh"];
    $SupportEmail = $GLOBALS["SupportEmail"];
    $FromEmail = $GLOBALS["FromEmail"];
    $strRemoteIP = $GLOBALS["strRemoteIP"];
    $strURLRegx = "#(http://)|(a href)#i";
    if(preg_match($strURLRegx,$InVar))
    {
      $InVar = str_replace("'","\'",$InVar);
      $strQuery = "INSERT INTO tblSpamLog (vcIPAddress, vcContent) VALUES ('$strRemoteIP', '$InVar');";
      UpdateSQL($strQuery);
      return TRUE;
    }
    else
    {
      return FALSE;
    }
  }

  function Log_Array($array, $msg)
  {
    $errMsg = json_encode($array);
    error_log("$msg: $errMsg");
  }

  function return_bytes($val)
  {
    $val = trim($val);
    $last = strtolower($val[strlen($val)-1]);
    $iValue = intval($val);
    switch($last)
    {
      case "g":
        $iValue *= 1024;
      case "m":
        $iValue *= 1024;
      case "k":
        $iValue *= 1024;
    }
    return $iValue;
  }

  function with_unit($val)
  {
    $Units[0] = "";
    $Units[1] = "KB";
    $Units[2] = "MB";
    $Units[3] = "GB";
    $Units[4] = "TB";
    $val = trim($val);
    $tmp = $val/1024;
    $i = 0;
    while($tmp > 1)
    {
      $tmp = $val/1024;
      if($tmp > 1)
      {
        $val = $tmp;
        $tmp = $val/1024;
        $i++;
      }
      else
      {
        break;
      }
    }
    return number_format($val, 2) . " " . $Units[$i];
  }

  function codeToMessage($code)
  {
    $MaxFileSize = ini_get("upload_max_filesize");
    switch($code)
    {
      case UPLOAD_ERR_INI_SIZE:
          $message = "The uploaded file exceeds file size limit of $MaxFileSize";
          break;
      case UPLOAD_ERR_FORM_SIZE:
          $message = "The uploaded file exceeds the MAX_FILE_SIZE directive that was specified in the HTML form";
          break;
      case UPLOAD_ERR_PARTIAL:
          $message = "The uploaded file was only partially uploaded";
          break;
      case UPLOAD_ERR_NO_FILE:
          $message = "No file was uploaded";
          break;
      case UPLOAD_ERR_NO_TMP_DIR:
          $message = "Missing a temporary folder";
          break;
      case UPLOAD_ERR_CANT_WRITE:
          $message = "Failed to write file to disk";
          break;
      case UPLOAD_ERR_EXTENSION:
          $message = "File upload stopped by extension";
          break;
      default:
          $message = "Unknown upload error # $code";
          break;
      }
      return $message;
  }

  function ValidateIntlPhoneNumber($phone)
  {
    $strMsg = "";
    if($phone == "")
    {
      return "";
    }
    $NumLen = strlen(preg_replace("/[^0-9]/", "", $phone));
    if($NumLen == 0)
    {
      return "Cell is not empty, yet it contains no digits. Please leave empty if you don't want to provide your number";
    }
    if($NumLen < 7)
    {
      $strMsg .= "That number only has $NumLen digits, seems awfully short for a valid number please double check.<br>\n "
                . "Please leave empty if you don't want to provide your number<br>\n";
    }
    if($NumLen > 15)
    {
      $strMsg .= "That number has $NumLen digits, seems awfully long for a valid number please double check. <br>\n"
                . "Please leave empty if you don't want to provide your number<br>\n";
    }
    if(substr($phone, 0,1) != "+")
    {
      $strMsg .= "Please format your phone number using the internation format of +123-555-1234-5678.<br>\n"
                . "Where 123 is the country code, 555 is the area code (where applicable) and the rest is the local number.<br>\n "
                . "Seperators are optional and can be whatever you want. Just start with a +<br>\n";
    }
    if(substr($phone, 0,1) == "0")
    {
      $strMsg .= "Please format your phone number using the internation format of +123-555-1234-5678<br>\n"
                . "Where 123 is the country code, 555 is the area code (where applicable) and the rest is the local number.<br>\n"
                . "Seperators are optional and can be whatever you want. Just start with a +<br>\n"
                . "Please leave off all leading zeros and any international call prefixes, such as 00 or 011, etc.<br>\n";
    }
    if($NumLen < 10 && substr($phone, 0,1) != "+")
    {
      $strMsg .= "That number only has $NumLen digits, did you forget the country code? ";
    }
    return $strMsg;
  }

  function format_phone_us($phone)
  {
    // note: strip out everything but numbers
    $phone = preg_replace("/[^0-9]/", "", $phone);
    $length = strlen($phone);
    switch($length)
    {
      case 7:
          return "Area code is required";
          break;
      case 11:
          $phone = substr($phone, 1);
      case 10:
          return preg_replace("/([0-9]{3})([0-9]{3})([0-9]{4})/", "+1 ($1) $2-$3", $phone);
          break;
      default:
          return "not a valid US phone number";
          break;
    }
  }

  function StripHTML($content)
  {
    $content = str_replace("<th>","|",$content);
    $content = str_replace("</th>","",$content);
    $content = str_replace("<td>","|",$content);
    $content = str_replace("</td>","",$content);
    $unwanted = ["style","script"];
    foreach( $unwanted as $tag )
    {
      $content = preg_replace( "/(<$tag>.*?<\/$tag>)/is", "", $content );
    }
    unset($tag);
    $content = str_replace("\r","",$content);
    $content = strip_tags($content);
    $content = preg_replace("/([\r\n]{4,}|[\n]{2,}|[\r]{2,})/", "\n", $content);
    return trim($content);
  }

  function SendHTMLAttach($strHTMLMsg, $FromEmail, $toEmail, $strSubject, $strFileName = "", $strAttach = "", $strAddHeader = "", $strFile2Attach = "")
  {

    require_once("PHPMailer/Exception.php");
    require_once("PHPMailer/PHPMailer.php");
    require_once("PHPMailer/SMTP.php");

    $strHTMLMsg = preg_replace("/(<script>.*?<\/script>)/is","",$strHTMLMsg);
    $strHTMLMsg = str_replace("\r","\n",$strHTMLMsg);
    $strHTMLMsg = str_replace("\n\n","\n",$strHTMLMsg);

    $ToParts   = explode("|",$toEmail);
    $FromParts = explode("|",$FromEmail);
    $strTxtMsg = StripHTML($strHTMLMsg);

    // create a new PHPMailer object
    $mail = new PHPMailer();

    // configure an SMTP Settings
    $mail->isSMTP();
    $mail->Host = $GLOBALS["MailHost"];
    $mail->Port = $GLOBALS["MailHostPort"];
    $mail->SMTPAuth = true;
    $mail->Username = $GLOBALS["MailUser"];
    $mail->Password = $GLOBALS["MailPWD"];
    if(strtolower($GLOBALS["UseSSL"])=="true")
    {
      $mail->SMTPSecure = PHPMailer::ENCRYPTION_SMTPS;
    }
    else
    {
      if(strtolower($GLOBALS["UseStartTLS"])=="true")
      {
        $mail->SMTPSecure = "tls";
      }
      else
      {
        $mail->SMTPSecure = "";
      }
    }

    // Construct email message
    $mail->CharSet="UTF-8";
    $mail->setFrom($FromParts[1], $FromParts[0]);
    $mail->addAddress($ToParts[1], $ToParts[0]);
    $mail->Subject = $strSubject;
    $mail->isHTML(TRUE);
    $mail->Body = $strHTMLMsg;
    $mail->AltBody = $strTxtMsg;

    // add string attachment
    if($strAttach != "" and $strFileName != "")
    {
      $mail->addStringAttachment($strAttach, $strFileName);
    }

    // Process any custom headers
    if(is_array($strAddHeader))
    {
      foreach($strAddHeader as $header)
      {
        $mail->addCustomHeader($header);
      }
    }
    else
    {
      if($strAddHeader != "")
      {
        $mail->addCustomHeader($strAddHeader);
      }
    }

    // Attach file attachment
    if($strFile2Attach != "")
    {
      $mail->addAttachment($strFile2Attach);
    }

    // send the message

    if(!$mail->send())
    {
      return "Message could not be sent. Mailer Error: " . $mail->ErrorInfo;
    }
    else
    {
      return "Message has been sent";
    }
  }

  function EmailText($to,$subject,$message,$from)
  {
    $strFileName = "";
    $strAttach = "";
    $from = str_replace("<", "|", $from);
    $from = str_replace(">", "", $from);
    $from = str_replace("From:", "", $from);
    if(stripos($from, "|")===FALSE)
    {
      $iPos = stripos($from, "@");
      $name = substr($from, 0,$iPos);
      $from = "$name|$from";
    }
    $to = str_replace("<", "|", $to);
    $to = str_replace(">", "", $to);
    if(stripos($to, "|")===FALSE)
    {
      $iPos = stripos($to, "@");
      $name = substr($to, 0,$iPos);
      $to = "$name|$to";
    }
    $message = str_replace("\n", "<br>\n", $message);
    $response = SendHTMLAttach($message, $from, $to, $subject, $strFileName, $strAttach);
    if($response == "Message has been sent")
    {
      return TRUE;
    }
    else
    {
      return FALSE;
    }
  }

  function FetchKeylessStatic($arrNames)
  {
    # $arrNames is an array of the secret names to be fetched
    # Returns an associated array with the secret name as key and the secret as the value
    # Requires AccessID and Accesskey as environment variables
    $AccessID = getenv("KEYLESSID");
    $AccessKey = getenv("KEYLESSKEY");
    $APIEndpoint = "https://api.akeyless.io";

    $PostData = array();
    $PostData["access-type"] = "access_key";
    $PostData["access-id"] = "$AccessID";
    $PostData["access-key"] = "$AccessKey";
    $jsonPostData = json_encode($PostData);

    $Service = "/auth";
    $url = $APIEndpoint.$Service;
    try
    {
      $curl = curl_init();
      curl_setopt($curl, CURLOPT_URL, $url);
      curl_setopt($curl, CURLOPT_POSTFIELDS, $jsonPostData);
      curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
      curl_setopt($curl, CURLOPT_POST, true);
      curl_setopt($curl, CURLOPT_HTTPHEADER, array("accept: application/json","Content-Type: application/json"));
      $response = curl_exec($curl);
      curl_close($curl);
      $arrResponse = json_decode($response, TRUE);
    }
    catch(Throwable $e)
    {
      $eLine = $e->getLine();
      $eFile = $e->getFile();
      $eCode = $e->getCode();
      $errMsg = "Fatal error #$eCode when attempting to initialize curl on line $eLine in $eFile. ";
      error_log($errMsg. $e->getMessage());
      $errMsg = "Fatal error when attempting to initialize curl. " . $e->getMessage();
      return $errMsg;
    }
    if(array_key_exists("error",$arrResponse))
    {
      error_log("Failed to authenticate to the AKEYLESS system. ".$arrResponse["error"]);
      return $arrResponse;
    }

    $token = $arrResponse["token"];

    $PostData = array();
    $PostData["token"] = $token;
    $PostData["names"] = $arrNames;
    $jsonPostData = json_encode($PostData);

    $Service = "/get-secret-value";
    $url = $APIEndpoint.$Service;
    try
    {
      $curl = curl_init();
      curl_setopt($curl, CURLOPT_URL, $url);
      curl_setopt($curl, CURLOPT_POSTFIELDS, $jsonPostData);
      curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
      curl_setopt($curl, CURLOPT_POST, true);
      curl_setopt($curl, CURLOPT_HTTPHEADER, array("accept: application/json","Content-Type: application/json"));
      $response = curl_exec($curl);
      curl_close($curl);
      return json_decode($response, TRUE);
    }
    catch(Throwable $e)
    {
      $eLine = $e->getLine();
      $eFile = $e->getFile();
      $eCode = $e->getCode();
      $errMsg = "Fatal error #$eCode when attempting to initialize curl on line $eLine in $eFile. ";
      error_log($errMsg. $e->getMessage());
      $errMsg = "Fatal error when attempting to initialize curl. " . $e->getMessage();
      return $errMsg;
    }

  }

  function FetchDopplerStatic($strProject,$strConfig)
  {
    # $strProject is a simple string with the name of the Doppler Project holding your secret
    # $strConfig is a simple string with the name of the configuration to use
    # Returns an associated array with top level key of success, indicating if the fetch was successful or not
    # If success = true, all secrets will be under a top level key of secrets
    # with the secret name as key and the secret as the value
    # If success = false, there will a array of messages under top level key of messages with error messages
    # Requires DopplerKEY as environment variables
    $AccessKey = getenv("DOPPLERKEY");
    $APIEndpoint = "https://api.doppler.com";
    $Service = "/v3/configs/config/secrets";
    $method = "GET";

    $Param = array();
    $Param["project"] = $strProject;
    $Param["config"] = $strConfig;

    $url = $APIEndpoint.$Service . "?" . http_build_query($Param);
    try
    {
      $curl = curl_init();
      curl_setopt($curl, CURLOPT_USERPWD, "$AccessKey:");
      curl_setopt($curl, CURLOPT_HTTPAUTH, CURLAUTH_BASIC);
      curl_setopt($curl, CURLOPT_URL, $url);
      curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
      curl_setopt($curl, CURLOPT_HTTPHEADER, array("accept: application/json"));
      curl_setopt($curl, CURLOPT_CUSTOMREQUEST, $method);
      $response = curl_exec($curl);
      curl_close($curl);
      $arrResponse = json_decode($response, TRUE);
      return json_decode($response, TRUE);
    }
    catch(Throwable $e)
    {
      $eLine = $e->getLine();
      $eFile = $e->getFile();
      $eCode = $e->getCode();
      $errMsg = "Fatal error #$eCode when attempting to initialize curl on line $eLine in $eFile. ";
      error_log($errMsg. $e->getMessage());
      $errMsg = "Fatal error when attempting to initialize curl. " . $e->getMessage();
      return $errMsg;
    }
  }

  function SendUserSMS($msg,$iUserID)
  {
    # SendUserSMS is a function that looks up users cell number
    # validates that the user has consented to receiving texts
    # then uses SendTwilioSMS to send the message via Twilio Service
    # $msg is a simple string with the message you wish to send
    # $iUserID is the ID number of the user to send it to
    # Returns a string indicating success or failure
    #    queued: Success
    #    failure/Not permitted: Unable to send

    $strQuery = "SELECT vcValue FROM tblUsrPrefValues WHERE iUserID = $iUserID AND iTypeID=1;";
    $QueryData = QuerySQL($strQuery);
    if($QueryData[0] > 0)
    {
      if(strtolower($QueryData[1][0]["vcValue"]) == "true")
      {
        $strQuery = "SELECT vcCell FROM tblUsers WHERE iUserID = $iUserID;";
        $QueryData = QuerySQL($strQuery);
        if($QueryData[0] > 0)
        {
          $response = SendTwilioSMS($msg,$QueryData[1][0]["vcCell"]);
          if($response[0])
          {
            return "queued";
          }
          else
          {
            $arrResponse = json_decode($response[1], TRUE);
            $errmsg = "";
            if(array_key_exists("message",$arrResponse))
            {
              $errmsg .= $arrResponse["message"];
            }
            if(array_key_exists("more_info",$arrResponse))
            {
              $errmsg .= " For more information see " . $arrResponse["more_info"];
            }
            return $errmsg;
          }

        }
        else
        {
          error_log("Error in SendUserSMS when fetching cell num. ".json_encode($QueryData));
          return "Failure";
        }
      }
      else
      {
        return "Not permitted";
      }
    }
    else
    {
      error_log("Error in SendUserSMS when fetching permission. ".json_encode($QueryData));
      return "Failure";
    }
  }

  function SendTwilioSMS($msg,$number)
  {
    # SendTwilioSMS is a function that send SMS message via Twilio Service
    # $msg is a simple string with the message you wish to send
    # $number is the phone number to send it to
    # Returns an array with first element true/false
    #    second element the actual response from the API

    $TwilioToken = $GLOBALS["TwilioToken"];
    $FromNumber = $GLOBALS["FromNumber"];
    $TwilioSID = $GLOBALS["TwilioSID"];

    $APIEndpoint = "https://api.twilio.com/";
    $Service = "2010-04-01/Accounts/$TwilioSID/Messages.json";
    $method = "POST";

    $Param = array();
    $Param["From"] = $FromNumber;
    $Param["Body"] = $msg;
    $Param["To"] = $number;

    $url = $APIEndpoint.$Service;
    $curl = curl_init();
    curl_setopt($curl, CURLOPT_USERPWD, "$TwilioSID:$TwilioToken");
    curl_setopt($curl, CURLOPT_HTTPAUTH, CURLAUTH_BASIC);
    curl_setopt($curl, CURLOPT_URL, $url);
    curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($curl, CURLOPT_ENCODING, "");
    curl_setopt($curl, CURLOPT_HTTPHEADER, array("accept: application/json","Content-Type: application/x-www-form-urlencoded"));
    curl_setopt($curl, CURLOPT_CUSTOMREQUEST, $method);
    curl_setopt($curl,CURLOPT_POSTFIELDS,http_build_query($Param));
    $response = curl_exec($curl);
    $httpcode = curl_getinfo($curl, CURLINFO_HTTP_CODE);
    // print "HTTP Response code is $httpcode<br>\n";
    curl_close($curl);
    $arrResponse = json_decode($response, TRUE);
    if($arrResponse["status"]=="queued")
    {
      return [TRUE,$response];
    }
    else
    {
      return [FALSE,$response];
    }
  }

  function GenerateRecovery($iUserID)
  {
    if(isset($GLOBALS["ConfArray"]["RecoverCodeLen"]) )
    {
      $iCodeLen = $GLOBALS["ConfArray"]["RecoverCodeLen"];
    }
    else
    {
      $iCodeLen = 8;
    }
    $RecovCode = $GLOBALS["TextArray"]["RecovCode"];
    $strRecoveryCode = chunk_split(bin2hex(random_bytes($iCodeLen/2)),4," ");
    printPg("$RecovCode","note");
    printPg("$strRecoveryCode","note");
    $strCleanRecovery = str_replace(" ","",$strRecoveryCode);
    $strECode = password_hash($strCleanRecovery, PASSWORD_DEFAULT);
    $strQuery = "update tblUsers set vcRecovery = '$strECode' where iUserID = $iUserID;";
    UpdateSQL($strQuery, "update");
  }

  function LoadMFAOptions($iUserID)
  {
    $bMFA_active = False;
    $bSMSemail = False;
    $strTOTP = "";
    $arrOptions = array();
    $strQuery = "SELECT vcMFASecret FROM tblUsers WHERE iUserID = $iUserID;";
    $QueryData = QuerySQL($strQuery);

    if($QueryData[0] > 0)
    {
      foreach($QueryData[1] as $Row)
      {
        $strTOTP = $Row["vcMFASecret"];
      }
    }
    if($strTOTP != "")
    {
      $bMFA_active = True;
      $arrOptions["totp"] = "TOTP MFA Authenticator";
    }
    $strQuery = "SELECT vcValue FROM tblUsrPrefValues WHERE iUserID = $iUserID AND iTypeID IN (2,3);";
    $QueryData = QuerySQL($strQuery);

    if($QueryData[0] > 0)
    {
      foreach($QueryData[1] as $Row)
      {
        if(strtolower($Row["vcValue"]) == "true")
        {
          $bSMSemail = True;
        }
      }
    }
    if($bSMSemail)
    {
      $bMFA_active = True;
      $arrOptions["smsemail"] = "SMS or Email MFA";
    }
    if($bMFA_active)
    {
      $arrOptions["recover"] = "Recovery Code";
    }
    $_SESSION["bMFA_active"] = $bMFA_active;
    $_SESSION["MFAOptions"] = $arrOptions;
    $_SESSION["bSMSemail"] = $bSMSemail;
    return $arrOptions;
  }

  function NotifyActivity($strActivity,$arrTypes)
  {
    $FromEmail = $GLOBALS["FromEmail"];
    $ProdName = $GLOBALS["ProdName"];
    $iUserID = $GLOBALS["iUserID"];
    $strTypeList = implode(",",array_values($arrTypes));

    $strQuery = "SELECT v.iTypeID, v.vcValue, u.vcEmail " .
                "FROM tblUsrPrefValues v JOIN tblUsers u ON v.iUserID = u.iUserID " .
                "WHERE v.iUserID = $iUserID AND v.iTypeID IN ($strTypeList);";
    $QueryData = QuerySQL($strQuery);
    $DataSet = array();
    if($QueryData[0] > 0)
    {
      foreach($QueryData[1] as $Row)
      {
        $iTypeID = $Row["iTypeID"];
        $strValue = $Row["vcValue"];
        $strEmail = $Row["vcEmail"];
        $DataSet[$iTypeID]=array("value"=>$strValue,"email"=>$strEmail);
      }
    }
    if(count($DataSet) > 0)
    {
      if(array_key_exists($arrTypes["email"],$DataSet)) # Receive email notification on each login is defined
      {
        if(strtolower($DataSet[$arrTypes["email"]]["value"]) == "true")
        {
          EmailText($DataSet[$arrTypes["email"]]["email"],"Successful $strActivity Notification","Your account on $ProdName had a successfully $strActivity",$FromEmail);
        }
      }
      if(array_key_exists($arrTypes["SMS"],$DataSet)) # Receive SMS notification on each login is defined
      {
        if(strtolower($DataSet[$arrTypes["SMS"]]["value"]) == "true")
        {
          SendUserSMS("Your account on $ProdName had a successfully $strActivity",$iUserID);
        }
      }
    }
  }

  function FileUpload($arrFiles,$DocRoot)
  {
    if(!is_dir($DocRoot))
    {
      mkdir($DocRoot);
    }
    if(is_array($arrFiles["name"]))
    {
      $FileList = "";
      $SizeTotal = 0;
      $arrMsg = array();
      $arrError = array();
      $arrFilesList = array();
      $FilesVarCount = count($arrFiles["name"]);
      for($i = 0; $i < $FilesVarCount; $i++)
      {
        $DocFileName = $arrFiles["name"][$i];
        $DocBaseName = basename($DocFileName);
        $newPath = $DocRoot . $DocBaseName;
        $tmpFile = $arrFiles["tmp_name"][$i];
        $Error = $arrFiles["error"][$i];
        $Size =  $arrFiles["size"][$i];
        $SizeUnit = with_unit($Size);
        if($Error == UPLOAD_ERR_OK)
        {
          if(move_uploaded_file($tmpFile, $newPath))
          {
            $arrMsg[] = "<div class=\"MainTextCenter\">File $DocBaseName uploaded successfully<br></div>";
            $FileList .= "$DocBaseName, Size: $SizeUnit<br>\n";
            $SizeTotal += $Size;
            $arrFilesList[] = $newPath;
          }
          else
          {
            $arrError[] = "<p class=\"Error\">Couldn't move file to $newPath</p>";
          }
        }
        else
        {
          $ErrMsg = codeToMessage($Error);
          $arrError[] =  "<p class=\"Error\">Error \"$ErrMsg\" while uploading $DocFileName</p>\n";
        }
      }
    }
    else
    {
      $FileList = "";
      $SizeTotal = 0;
      $arrMsg = array();
      $arrError = array();
      $arrFilesList = array();
      $DocFileName = $arrFiles["name"];
      $DocBaseName = basename($DocFileName);
      $newPath = $DocRoot . $DocBaseName;
      $tmpFile = $arrFiles["tmp_name"];
      $Error = $arrFiles["error"];
      $Size =  $arrFiles["size"];
      $SizeUnit = with_unit($Size);
      error_log("tmpfile: $tmpFile  -  newpath: $newPath  - Error: $Error");

      if($Error == UPLOAD_ERR_OK)
      {
        if(move_uploaded_file($tmpFile, $newPath))
        {
          $arrMsg[] = "<div class=\"MainTextCenter\">File $DocBaseName uploaded successfully<br></div>";
          $FileList .= "$DocBaseName, Size: $SizeUnit<br>\n";
          $arrFilesList[] = $newPath;
          $SizeTotal += $Size;
        }
        else
        {
          $arrError[] = "<p class=\"Error\">Couldn't move file to $newPath</p>";
        }
      }
      else
      {
        $ErrMsg = codeToMessage($Error);
        $arrError[] =  "<p class=\"Error\">Error \"$ErrMsg\" while uploading $DocFileName</p>\n";
      }

    }
    return array("msg"=>$arrMsg,"err"=>$arrError,"Files"=>$FileList,"size"=>$SizeTotal,"FileList"=>$arrFilesList);
  }
?>