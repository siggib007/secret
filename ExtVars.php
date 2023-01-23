<?php
  /*
  Copyright © 2009,2015,2022  Siggi Bjarnason.
  Licensed under GNU GPL v3 and later. Check out LICENSE.TXT for details
  or see <https://www.gnu.org/licenses/gpl-3.0-standalone.html>

  Specifies how secrets are handled. See below for details
  */

  /*
  Following secrets and environment variables are requried:

  DBServerName    # FQDN of the Database server
  DefaultDB       # Database Name
  UID             # Database Username
  PWD             # Database Password
  */

  $DopplerProj = "phpsecret";
  $DopplerConf = "dev";
  require("DopplerVar.php");
?>