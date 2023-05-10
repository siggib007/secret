<?php
  /*
  Copyright © 2009,2015,2022  Siggi Bjarnason.
  Licensed under GNU GPL v3 and later. Check out LICENSE.TXT for details
  or see <https://www.gnu.org/licenses/gpl-3.0-standalone.html>

  Main connection file, handles DB connections and other initialization

  */


  require_once("functions.php");

  ini_set( "default_charset", "UTF-8" );
  set_time_limit(30);
  $DevEnvironment = getenv("DEVENV");
  $ROOTPATH = "/";
  $RefreshMin = 1;
  $RefreshSec = $RefreshMin * 60;
  $HeadImg  ="ShareASecret.png";
  $CSSName  = "SiteStyle.css";
  $ErrMsg   = "We seem to be experiencing some technical difficulties, " .
            "hopefully we'll have it resolved shortly.<br>" .
            "If you have any questions please contact us at support@supergeek.us";

  # All Environment and secret vars are specified in ExtVars.php
  # Follow instruction there on how to adjust.

  require("ExtVars.php");

  if($DBServerName == "" or $UID == "" or $PWD == "")
      {
        error_log("One or more of the required DB creds variable are blank");
        error_log("Make sure database connections conf in DBCon.php are correct.");
        ShowErrHead();
      }

  date_default_timezone_set("UTC");
  $strRemoteIP = $_SERVER["REMOTE_ADDR"];
  $strHost = $_SERVER["SERVER_NAME"];
  if($_SERVER["SERVER_PORT"] != 80 and $_SERVER["SERVER_PORT"] != 443)
  {
    $strHost .= ":".$_SERVER["SERVER_PORT"];
  }
  $strScriptName = $_SERVER["SCRIPT_NAME"];
  $strURI = $_SERVER["REQUEST_URI"];
  $HeadAdd = "";
  $strSiteLabel = "";
  $DBError = "false";
  $strHostNameParts = explode(".",$strHost);
  $HostnamePartCount = count($strHostNameParts);
  $OSEnv = "not used";

  if($HostnamePartCount == 1)
  {
    $SiteType = "a";
  }
  else
  {
    $SiteType = $strHostNameParts[0];
  }

  $strURL = "Localhost/";

  try
  {
    $dbh = new mysqli($DBServerName, $UID, $PWD, $DefaultDB);
  }
  catch(Exception $e)
  {
    error_log("Error while attempting to create a new mysqli client to $DBServerName.$DefaultDB using $UID and password that starts with "
              . substr($PWD,0,3) . " " . $e->getMessage());
    error_log("Make sure database connections in DBCon.php are correct.");
    ShowErrHead();
  }
  $dbh->set_charset("utf8");
  if($dbh->connect_errno)
  {
    error_log( "Failed to connect to $DBServerName.$DefaultDB using $UID and password that starts with " . substr($PWD,0,3) . " Error(" . $dbh->connect_errno . ") " . $dbh->connect_error);
    error_log("Make sure database connections in DBCon.php are correct.");
    $DBError = "true";
  }
  else
  {

  }
  if(isset($_SERVER["HTTP_REFERER"]))
  {
    $strReferer = $_SERVER["HTTP_REFERER"];
  }
  else
  {
    $strReferer = "";
  }
  if(isset($_SERVER["HTTP_USER_AGENT"]))
  {
    $strAgent = $_SERVER["HTTP_USER_AGENT"];
  }
  else
  {
    $strAgent = "";
  }

  if(isset($_SERVER["HTTPS"]))
  {
    $strProto = "https://";
  }
  else
  {
    $strProto = "http://";
  }
  $strPageURL = $strProto . $strHost . $strURI;
  $PostVarCount = count($_POST);
  $dtNow = date('Y-m-d H:i:s');
  $strPageName = $_SERVER['PHP_SELF'];
?>