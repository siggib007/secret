<?php
  /*
  Copyright © 2009,2015,2022  Siggi Bjarnason.
  Licensed under GNU GPL v3 and later. Check out LICENSE.TXT for details
  or see <https://www.gnu.org/licenses/gpl-3.0-standalone.html>

  Header file that gets inserted everywhere and handled the menu and header for each page
  */

  $PrivReq = 0;
  $iAdminCat = 0;

  require_once("DBCon.php");
  if(isset($_SERVER["HTTPS"]) and $strSecOpt =="prevent")
  {
    $strUnSecure = "http://$strHost$strScriptName";
    header("Location: $strUnSecure");
  }

  $iMenuID = 0;
  $iLastSlash = strrpos($strURI, "/");
  $strPagePath = substr($strURI, 0,$iLastSlash+1);
  $strPageNameParts = explode("/",$strURI);
  $FirstPart = "/$strPageNameParts[1]/";
  $iSubOfID = 0;
  $bChangePWD = 0;
  if($FirstPart != $ROOTPATH)
  {
    $ROOTPATH = "/";
  }
  $strURL = "http://" . $strHost . $ROOTPATH;
  $CSSName = $ROOTPATH . $CSSName;

  $HowMany = count($strPageNameParts);

  $LastIndex = $HowMany - 1;
  $strPageArgs = explode("?",$strPageNameParts[$LastIndex]);
  $strPageName = $strPageArgs[0];
  if($strPageName =="" and $strURI == $ROOTPATH)
  {
    $strPageName = "index.php";
  }
  if(($strPageName =="" or $strPageName =="index.php") and $strPagePath != $ROOTPATH)
  {
    $LastIndex = $HowMany - 2;
    $strPageArgs = explode("?",$strPageNameParts[$LastIndex]);
    $strPageName = $strPageArgs[0];
  }

  if(!isset($_SERVER["HTTPS"])and $strSecOpt =="force" and $bSecure == 1)
  {
    $strSecureHost = $ConfArray["SecureURL"];
    $strSecure = "https://$strSecureHost/$strScriptName";
    header("Location: $strSecure");
  }

  $strHeader = $HeadAdd . $dbHead;
  $ShowPort = strtolower($GLOBALS["ConfArray"]["ShowPort"]);
  if($_SERVER["SERVER_PORT"] != 80 and $_SERVER["SERVER_PORT"] != 443 and $ShowPort == "true")
  {
    $strHeader = "[p" . $_SERVER["SERVER_PORT"] . "] " . $strHeader;
  }

  header("Content-Type: text/html; charset=utf-8");
  print "<!DOCTYPE HTML PUBLIC \"-//W3C//DTD HTML 4.01 Transitional//EN\"\n\"http://www.w3.org/TR/html4/loose.dtd\">\n";
  print "<html>\n";
  print "<head>\n";
  print "<title>$strHeader</title>\n";

  print "<link rel=\"shortcut icon\" type=\"image/x-icon\" href=\"/favicon.ico\">\n";
  print "<link href=\"$CSSName\" rel=\"stylesheet\" type=\"text/css\">\n";
  print "</head>\n";
  print "<body>\n";
  print "<!-- Form a border for the page -->\n";
  print "<div id=\"left\"></div>\n";
  print "<div id=\"right\"></div>\n";
  print "<div id=\"top\"></div>\n";
  print "<div id=\"bottom\"></div>\n";
  print "<!-- End border start div for the header -->\n";
  print "<div class=\"BlacktblHead\">\n";
  if($strSiteLabel <> "")
  {
    print "<center><span class=SiteLabel>$strSiteLabel</span></center>\n";
  }

?>