<?php
  /*
  Copyright © 2009,2015,2022  Siggi Bjarnason.
  Licensed under GNU GPL v3 and later. Check out LICENSE.TXT for details
  or see <https://www.gnu.org/licenses/gpl-3.0-standalone.html>

  This file handles fetching secrets and environment variables to Doppler
  and assigning them to the proper variables.
  */

  $arrSecretValues = FetchDopplerStatic($DopplerProj,$DopplerConf);
  if(is_string($arrSecretValues))
  {
    Log_Array($arrSecretValues,"Unexpected reponse from FetchDopplerStatic");
    ShowErrHead();
  }
  if(array_key_exists("secrets",$arrSecretValues))
  {
    $DBServerName = $arrSecretValues["secrets"]["MYSQL_HOST"]["computed"];
    $DefaultDB = $arrSecretValues["secrets"]["MYSQL_DB"]["computed"];
    $UID = $arrSecretValues["secrets"]["MYSQL_USER"]["computed"];
    $PWD = $arrSecretValues["secrets"]["MYSQL_PASSWORD"]["computed"];
    $strEncrIV = $arrSecretValues["secrets"]["ENCR_IV"]["computed"];
  }
  else
  {
    if(array_key_exists("messages",$arrSecretValues))
    {
      $AccessKey = getenv("DOPPLERKEY");
      $strMsg = "There was an issue fetching the secrets from $DopplerProj - $DopplerConf. Key starts with '" . substr($AccessKey,0,12) ."'";
      foreach($arrSecretValues["messages"] as $msg)
      {
        $strMsg .= "$msg. ";
      }
      error_log($strMsg);
      ShowErrHead();
    }
    else
    {
      Log_Array($arrSecretValues,"Error reponse from FetchDopplerStatic: ");
      ShowErrHead();
    }
  }
?>