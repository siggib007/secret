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
  MailUser        # SMTP server Username
  MailPWD         # SMTP Password
  MailHost        # SMTP Server FQDN
  MailHostPort    # TCP port to use when connecting to SMTP server
  UseSSL          # Establish an encrypted connection with SMTP server
  UseStartTLS     # Switch to encrypted connection post connection
  $TwilioToken    # Twilio API Token for SMS sending
  $FromNumber     # What the SMS From should be
  $TwilioSID      # The SMS Sender Account SID


  Decide where and how secure you want to keep and access environment variables and secrets
  by including (aka requiring) the appropriate file
  secrets.php     : You want to hard code all your secrets in file, along with other environment values.
                    While this is a very convenient solution it is the least secure method.
                    Sometimes this is only feasible option though.
                    It is critical that you gitignore this file to keep it as secret as possible
                    and pay attention to the access rights at the operating system level.
  EnvVar.php      : You are storing all you secrets in OS level environment variables along with all other environment values
                    This is considered more secure than hard coding into a file but is still sub-optimal
  DopplerVar.php  : This is a highly secure and recommended approach. See https://infosechelp.net/secrets-management/
                    for how to work with Doppler if you are not familiar with Doppler.
                    Requires a single env variable of DopplerKEY for the API key to the appropriate Doppler configuration
                    Also needs to have what project and configuration to pull. Uses $DopplerProj and $DopplerConf for that
  AkeylessVar.php : Another highly secure and recommended approach. See https://infosechelp.net/secrets-management-a-key-less-edition
                    if you are not familiar with the Secret Management system from AKEYLESS.
                    Requires a two env variables: KEYLESSID and KEYLESSKEY for authenticating to the AKEYLESS API Secrets Vault
  */

  $DopplerProj = "phpdev";
  $DopplerConf = "prod";
  require("DopplerVar.php");
?>