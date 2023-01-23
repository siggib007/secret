<?php  
  echo "current user: ".get_current_user();
  echo "<br>script was executed under user: ".exec('whoami');
  phpInfo();  
?>
