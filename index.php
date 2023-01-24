<?php
  /*
  Copyright © 2009,2015,2022  Siggi Bjarnason.
  Licensed under GNU GPL v3 and later. Check out LICENSE.TXT for details
  or see <https://www.gnu.org/licenses/gpl-3.0-standalone.html>

  Share a secret main page

  */

  require("header.php");

	if(isset($_POST["numExpiration"]))
	{
    $iExpiration = $_POST["numExpiration"];
	}
	else
	{
    $iExpiration ="";
	}
	if(isset($_POST["txtSecret"]))
	{
    $strSecret = $_POST["txtSecret"];
	}
	else
	{
    $strSecret ="";
	}
  if(isset($_POST["btnSubmit"]))
  {
    $strKey = PasswordGen(35);
    $strInterval = "+" . $iExpiration . " days";
    $strExpDate = date("Y-m-d",strtotime($strInterval));
    $strEncrypted = StringEncrypt($strSecret,$strKey);
    $strGUID = guid();
    $strQuery = "INSERT INTO tblSecrets (vcGUID, vcSecret, dtExpiration) VALUES ('$strGUID', '$strEncrypted', '$strExpDate');";
    $strFetchURL = $strProto . $strHost . "/fetch.php";
    $strFetchURLid = "$strFetchURL?id=$strGUID&pwd=$strKey";
    if (UpdateSQL($strQuery,"insert"))
    {
      printPg("Your secret has be stored securely. It can be fetch exactly one time before $strExpDate at","note");
      printPg("$strFetchURL<br>\nwith ID=$strGUID<br>\nand PWD=$strKey","box");
      printPg("After that date or after it has been viewed one time, it will be permanently deleted","note");
      printPg("It can also be fetched with the following single URL","note");
      printPg("WARNING!!! All messaging services that preview links will trigger a deletion on the URL below!!","alert");
      printPg("$strFetchURLid","center");
    }
    else
    {
      printPg("Saving to database failed, sorry about that. Please try again or contact support@supergeek.us");
    }
  }
  else
  {
    printpg("SuperGeek Secret Sharing","h1");
    printpg("To share something sensitive, enter it in the text box, select expiration, and click submit. " .
    "The text will be encrypted using strongest possible encryption and stored in a database. " .
    "You'll get URL back that allows someone to retrieve it exactly once then it is permanently deleted.","center");
    print "<div class=\"MainTextCenter\"><form method=\"POST\">\n";
    print "<textarea name=\"txtSecret\" rows=\"5\" cols=\"70\"></textarea>\n<br>\n";
    print "<label for=\"expiration\">Expires after number of days (between 1 and 14):</label>\n";
    print "<input type=\"number\" id=\"expiration\" name=\"numExpiration\" min=\"1\" max=\"14\" value=\"7\">\n";
    print "<input type=\"Submit\" value=\"Submit\" name=\"btnSubmit\"></div>\n";
    print "</form>\n";
  }

  require("footer.php");
?>
