<?php
  /*
  Copyright © 2009,2015,2022  Siggi Bjarnason.
  Licensed under GNU GPL v3 and later. Check out LICENSE.TXT for details
  or see <https://www.gnu.org/licenses/gpl-3.0-standalone.html>

  Dynamic Text page

  */

  require("header.php");
  printpg("SuperGeek Secret Sharing","h1");
  printpg("To share something sensitive, enter it in the text box, select expiration, and click submit. " .
          "The text will be encrypted using strongest possible encryption and stored in a database. " .
          "You'll get URL back that allows someone to retrieve it exactly once then it is permanently deleted.","normal");
	print "<div class=\"MainTextCenter\"><form method=\"POST\">\n";
  print "<textarea name=\"txtAnswer\" rows=\"5\" cols=\"70\"></textarea>\n<br>\n";
  print "<label for=\"expiration\">Expires after number of days (between 1 and 14):</label>\n";
  print "<input type=\"number\" id=\"expiration\" name=\"expiration\" min=\"1\" max=\"14\" value=\"7\">\n";
	print "<input type=\"Submit\" value=\"Submit\" name=\"btnSubmit\"></div>\n";
	print "</form>\n";

  require("footer.php");
?>
