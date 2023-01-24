#!/bin/bash
mysql -u expirescript -pAnotherStrongPassword!! -e "DELETE FROM phpSecrets.tblSecrets WHERE dtExpiration < now();"