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

  header("Content-Type: text/html; charset=utf-8");
  print "<!DOCTYPE HTML PUBLIC \"-//W3C//DTD HTML 4.01 Transitional//EN\"\n\"http://www.w3.org/TR/html4/loose.dtd\">\n";
  print "<html>\n";
  print "<head>\n";
  print "<title>ShareASecret</title>\n";
  print "<link rel=\"shortcut icon\" type=\"image/x-icon\" href=\"favicon.ico\">\n";
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
  print"<div class=\"Header\" align=\"center\">\n";

  $imgname = $ROOTPATH . $HeadImg;
  print "<table border=\"0\" cellPadding=\"4\" cellSpacing=\"0\">\n";
  print "<tr>\n";
  print "<td align=\"center\" vAlign=\"middle\">\n";
  print "<img border=\"0\" src=\"$imgname\" align=\"center\" height=\"150\">\n";
  print "</td>\n";
  print "</tr>\n";
  print "</table>\n";
  print "</div>\n</div>\n";
?>