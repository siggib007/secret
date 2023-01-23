<?php
  /*
  Copyright © 2009,2015,2022  Siggi Bjarnason.
  Licensed under GNU GPL v3 and later. Check out LICENSE.TXT for details
  or see <https://www.gnu.org/licenses/gpl-3.0-standalone.html>

  Central collection of various functions
  */

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


  function Log_Array($array, $msg)
  {
    $errMsg = json_encode($array);
    error_log("$msg: $errMsg");
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

  function PasswordGen($iLen)
  {
      return bin2hex(random_bytes($iLen));
  }

  function StringEncrypt($strInput,$strKey)
  {
    $strEncrIV = $GLOBALS["strEncrIV"];
    $CipherRing = "AES-256-CTR";
    $IVLenth = openssl_cipher_iv_length($CipherRing);
    $Options = 0;
    return openssl_encrypt($strInput,$CipherRing,$strKey,$Options,$strEncrIV);
  }
  function StringDecrypt($strInput,$strKey)
  {
    $strEncrIV = $GLOBALS["strEncrIV"];
    $CipherRing = "AES-256-CTR";
    $IVLenth = openssl_cipher_iv_length($CipherRing);
    $Options = 0;
    return openssl_decrypt($strInput,$CipherRing,$strKey,$Options,$strEncrIV);
  }
  function guid()
  {
    if (function_exists('com_create_guid') === true)
        return trim(com_create_guid(), '{}');

    $data = openssl_random_pseudo_bytes(16);
    $data[6] = chr(ord($data[6]) & 0x0f | 0x40);
    $data[8] = chr(ord($data[8]) & 0x3f | 0x80);
    return vsprintf('%s%s-%s-%s-%s-%s%s%s', str_split(bin2hex($data), 4));
  }

?>