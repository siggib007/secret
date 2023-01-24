<?php
  /*
  Copyright © 2009,2015,2022  Siggi Bjarnason.
  Licensed under GNU GPL v3 and later. Check out LICENSE.TXT for details
  or see <https://www.gnu.org/licenses/gpl-3.0-standalone.html>

  Share a secret main page

  */

  require("header.php");

  if (isset($_GET['id']))
	{
    $strGUID = CleanSQLInput ($_GET['id']);
	}
	else
	{
    $strGUID = "";
	}

  if (isset($_GET['pwd']))
	{
    $strKey = CleanSQLInput ($_GET['pwd']);
	}
	else
	{
    $strKey = "";
	}

  if ($strKey == "" || $strGUID == "")
  {
    printPg("Invalid link","error");
  }
  else
  {
    $strQuery = "SELECT vcSecret FROM tblSecrets WHERE vcGUID = '$strGUID'";
    $strSecret = GetSQLValue($strQuery);
    if($strSecret == "" || $strSecret == 0)
    {
      printPg("Link does not exist","error");
    }
    else
    {
      $strDecrypt = StringDecrypt($strSecret,$strKey);
      printPg("Here is your secret","h1");
      printPg("$strDecrypt","note");
    }
  }

  require("footer.php");
?>