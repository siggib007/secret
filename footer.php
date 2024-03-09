<?php
  /*
  Copyright © 2009,2015,2022  Siggi Bjarnason.
  Licensed under GNU GPL v3 and later. Check out LICENSE.TXT for details
  or see <https://www.gnu.org/licenses/gpl-3.0-standalone.html>

  Code to be insterted at the bottom of every page.
  */

	print "<br>\n";
	print "<center>\n";

	print "<p class=\"footer\">\n|&nbsp;&nbsp;";
  print "&nbsp;&nbsp;Copyright &copy; 2023 <a href=\"$HomeURL\" target=\"_blank\">"
  . "$Copyright</a>&nbsp;&nbsp;|\n";
	print "&nbsp;&nbsp;Your IP is $strRemoteIP $strHost&nbsp;&nbsp;|\n";
	print "</p>\n";
	print "</center>\n";
	print "</body>\n";
	print "</html>\n";
?>