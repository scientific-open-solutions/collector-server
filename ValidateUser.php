<?php
header("Access-Control-Allow-Origin: *");

if(!isset($_POST) || !isset($_POST['email'])){
  echo "erm, what are you doing here?";
} else {
  //by starrychloe at oliveyou dot net @ https://www.php.net/manual/en/function.str-getcsv.php
  $ValidUsers = array_map('str_getcsv', file("../../NetworkMembers.csv"));
  array_walk($ValidUsers, function(&$a) use ($ValidUsers) {
    $a = array_combine($ValidUsers[0], $a);
  });
  array_shift($ValidUsers); # remove column header
  $user_email = strtolower($_POST['email']);
  $resolution_waiting = true;
  
  /*
  * checks here
  */
  if($user_email == ""){
    echo "You need to provide a valid e-mail address for this check";
  } else {
    foreach($ValidUsers as $ValidUser){
      if($resolution_waiting){
        if($ValidUser['type'] == "extension"){
          if(strpos($user_email, $ValidUser['email']) !== false){
            
            $user_scripts = [];
            $user_scripts ['registration_url'] = $ValidUser['registration_url'];
            $user_scripts ['storage_url']      = $ValidUser['storage_url'];
            echo json_encode($user_scripts);        
            $resolution_waiting = false;
          }
        } else if($ValidUser['type'] == "user"){
          if($ValidUser['email'] == $user_email){
            $user_scripts = [];
            $user_scripts ['registration_scripts'] = $ValidUser['registration_scripts'];
            $user_scripts ['storage_scripts']      = $ValidUser['storage_scripts'];
            echo json_encode($user_scripts);        
            $resolution_waiting = false;
          }          
        }
      } 
    }
    if($resolution_waiting){
      echo "You don't appear to be a member of the organisation hosting this server. Please contact whoever gave you the registration address to clarify this.";
    }
  }
}
?>