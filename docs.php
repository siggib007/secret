<?php
  /*
  Copyright © 2009,2015,2022  Siggi Bjarnason.
  Licensed under GNU GPL v3 and later. Check out LICENSE.TXT for details
  or see <https://www.gnu.org/licenses/gpl-3.0-standalone.html>

  Share a secret main page

  */

  require("header.php");

  printpg("Documenation","h1");
  printpg("This is a site to facilitate secret sharing in a very secure manner. " .
  "The data you entered is encrypted using AES-256-CTR encryption, which is one of the strongest encryptions available, " .
  "using a 35 byte password, then stored in a database. The password is displayed on the screen and not stored anywhere." .
  "The entry is stored using a GUID as the ID, making it unlikely that the ID will be guessed." .
  "To retrieve the secret you need to provide the GUID as well as the 35 byte password on the Fetch screen. " .
  "If the GUID and the password is correct the item will be fetched from the database, decrypted, displayed, then deleted. " .
  "This means the entry can only be retrieved once before it is lost.","largebox");
  printpg("You can also specify an expiration date. If the entry isn't retrieved within the specified number of days, " .
  "the entry is permently deleted","largebox");
  printpg("<br>The source code for this system is open source and can be retrieved here " .
  "<a href='https://github.com/siggib007/secret'>https://github.com/siggib007/secret</a>","largebox");

  printpg("<br>Feel free to reach out to <a href='mailto:$SupportEmail'>$SupportEmail</a> with any questions or concerns.","largebox");

  require("footer.php");
?>
