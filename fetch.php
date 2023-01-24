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
    $strKey = $_GET['pwd'];
	}
	else
	{
    $strKey = "";
	}

  if ($strKey == "" || $strGUID == "")
  {
    print "<div class=\"MainTextCenter\"><form method=\"get\">\n";
    print "<label for=\"txtID\">&nbsp;&nbsp;&nbsp;ID:</label>\n";
    print "<input type=\"text\" id=\"txtID\" value=\"$strGUID\" name=\"id\" size=\"75\" >\n<br>\n";
    print "<label for=\"txtKey\">PWD:</label>\n";
    print "<input type=\"text\" id=\"txtKey\" value=\"$strKey\" name=\"pwd\" size=\"75\" >\n<br>\n";
    print "<input type=\"Submit\" value=\"Submit\" name=\"btnSubmit\">";
    print "</form>\n</div>\n";
  }
  else
  {
    $strQuery = "SELECT vcSecret FROM tblSecrets WHERE vcGUID = '$strGUID'";
    $strSecret = GetSQLValue($strQuery);
    if($strSecret == "")
    {
      printPg("Link does not exist","error");
    }
    else
    {
      $strDecrypt = StringDecrypt($strSecret,$strKey);
      printPg("Here is your secret","h1");
      printPg("$strDecrypt","note");
      $strQuery = "DELETE FROM tblSecrets WHERE vcGUID = '$strGUID'";
      UpdateSQL($strQuery,"Delete");
    }
  }

  require("footer.php");
?>