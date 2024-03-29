<?php
  /*
  Copyright © 2009,2015,2022  Siggi Bjarnason.
  Licensed under GNU GPL v3 and later. Check out LICENSE.TXT for details
  or see <https://www.gnu.org/licenses/gpl-3.0-standalone.html>

  Share a secret main page

  */

  require("header.php");

  if (isset($_GET["id"]))
	{
    $strGUID = CleanSQLInput ($_GET["id"]);
	}
	else
	{
    $strGUID = "";
	}

  if (isset($_GET["pwd"]))
	{
    $strKey = $_GET["pwd"];
	}
	else
	{
    $strKey = "";
	}

  if (isset($_GET["btnSubmit"]))
	{
    $strBtn = $_GET["btnSubmit"];
	}
	else
	{
    $strBtn = "";
	}


  if ($strBtn != "Submit")
  {
    printPg("Click submit to see the secret that was sent to you","note");
    printPg("WARNING!!!! Once you view the secret it is deleted, the message will self destruct from your screen $RefreshMin minutes after viewing","alert");
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
    error_log("Secret: $strSecret");
    if($strSecret == "" or $strSecret == 0)
    {
      printPg("ID does not exist","error");
    }
    else
    {
      $strDecrypt = StringDecrypt($strSecret,$strKey);
      printPg("Here is your secret","h1");
      $strMsg = str_replace("\n","<br>\n",$strDecrypt);
      printPg("$strMsg","box");
      $strQuery = "DELETE FROM tblSecrets WHERE vcGUID = '$strGUID'";
      UpdateSQL($strQuery,"Delete");
      printPg("<br>This message will self destruct in <span id=\"time\">$RefreshMin</span> minutes, or as soon as you close this tab<br>\n","attn");
      printPg("Make sure you close this tab as soon as you are done capturing the secret to enhance the security of the secret.","attn");
    }
  }
  print "<script type=\"text/javascript\" src=\"/timer.js\"></script>";
  require("footer.php");
?>
