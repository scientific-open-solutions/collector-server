<?php
/*  Collector (Garcia, Kornell, Kerr, Blake & Haffey)
    A program for running projects on the web
    Copyright 2012-2016 Mikey Garcia & Nate Kornell


    This program is free software: you can redistribute it and/or modify
    it under the terms of the GNU General Public License version 3 as published by
    the Free Software Foundation.

    This program is distributed in the hope that it will be useful,
    but WITHOUT ANY WARRANTY; without even the implied warranty of
    MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
    GNU General Public License for more details.

    You should have received a copy of the GNU General Public License
    along with this program.  If not, see <http://www.gnu.org/licenses/>

		Kitten release (2019-21) author: Anthony Haffey on behalf of scientific-open-solutions
*/



header("Access-Control-Allow-Origin: *");

/*
ini_set('display_errors', '1');
ini_set('display_startup_errors', '1');
error_reporting(E_ALL);
*/

$home_dir = dirname($_SERVER['DOCUMENT_ROOT']);
require_once "$home_dir/sqlCollector.php";

function generateRandomString($length = 10) {
	return substr(str_shuffle(str_repeat($x='0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ', ceil($length/strlen($x)) )),1,$length);
}

//solution by Alph.Dev at https://stackoverflow.com/questions/478121/how-to-get-directory-size-in-php
function GetDirectorySize($path){
	$bytestotal = 0;
	$path = realpath($path);
	if($path!==false && $path!='' && file_exists($path)){
		foreach(new RecursiveIteratorIterator(new RecursiveDirectoryIterator($path, FilesystemIterator::SKIP_DOTS)) as $object){
			$bytestotal += $object->getSize();
		}
	}
	return $bytestotal;
}

$action 	= $_POST['action'];
if(isset($_POST['location'])){
    $location = $_POST['location'];
    $hashed_location = hash(
        "sha256", 
        $_POST['location']
    );
}

$email 		= $_POST['email'];
$password = $_POST['password'];

if($email == ""){
  echo "missing email";
  return;
}

function create_project(
    $hashed_location,
    $conn,
    $email,
    $email_hash_password
){
    $sql = "INSERT INTO `projects`(`location`) VALUES ('$hashed_location');";
	if ($conn->query($sql) === TRUE) {				
		$sql = "INSERT INTO `contributors` (`project_id`,`user_id`,`contributor_status`) VALUES(
			(SELECT `project_id` FROM `projects` WHERE `location` = '$hashed_location'), 
			(SELECT `user_id` FROM `users` WHERE `email`=AES_ENCRYPT('$email', '$email_hash_password')),
			('v'));";
		
		
		if ($conn->query($sql) === TRUE) {
			echo "success";
		} else {
			echo  $conn->error;;
		}		
	} else {
		echo  $conn->error;;
	}	
}

function create_random_code($length){
    $characters = '0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ';
    $charactersLength = strlen($characters);        
    $new_code= '';
    for ($i = 0; $i < $length; $i++) {
        $new_code .= $characters[rand(0, $charactersLength - 1)];
    }
    return $new_code;
}

function email_user(
    $email_type,
    $email,
    $email_confirm_code,
    $mailer_user,
	$mailer_password,
	$mailer_host,
	$mailer_from,
	$mailer_team_name
){
    
    return "email functionality not present yet";
    
    /*
    //send email using php
    $msg = "First line of text\nSecond line of text";

    // use wordwrap() if lines are longer than 70 characters
    $msg = wordwrap($msg,70);
    
    // send email
    mail("$email","My subject",$msg);
    
    return "email sent";
    
	/*
	$mail = new PHPMailer(true);          // Passing `true` enables exceptions
	$mail->SMTPDebug = 0;                 // Enable verbose debug output
	//$mail->isSMTP();                    // Set mailer to use SMTP
	$mail->Host = "$mailer_host";					// smtp2.example.com, Specify main and backup SMTP servers
	$mail->SMTPAuth = true;               // Enable SMTP authentication
	$mail->Username = "$mailer_user";			// SMTP username
	$mail->Password = "$mailer_password"; // SMTP password
	$mail->SMTPSecure = 'tls';
	$mail->SMTPOptions = array(
		'ssl' => array(
				'verify_peer' => false,
				'verify_peer_name' => false,
				'allow_self_signed' => true
		)
	);                            // Enable TLS encryption, `ssl` also accepted
	$mail->Port = 587;                                    // TCP port to connect to
	$mail->setFrom("$mailer_from", 'Collector');
	$mail->isHTML(true);                                  // Set email format to HTML
			
	//identify the website this is coming from
	$exploded_url = explode("/",$_SERVER['SERVER_NAME'].$_SERVER['PHP_SELF']);
	array_pop($exploded_url);
	$imploded_url = "https://".implode("/",$exploded_url);
	
	switch($email_type){		
    case "registration":
      $msg = "Dear $email <br><br>Thank you for registering with Collector hosted by Some Open Solutions. Before you can use your new profile, we need to confirm this is a valid address. Please proceed to the following link to confirm: <br> $imploded_url/confirm.php?email=$email&confirm_code=$email_confirm_code <br>Many thanks, <br><br>$mailer_team_name";

      // use wordwrap() if lines are longer than 70 characters
      //$msg = wordwrap($msg,70);

      // send email
			
			
			
			$mail->Subject = "Confirmation code for Registering with Collector";
			$mail->Body    = $msg;
			$mail->AltBody = $msg;

			$mail->addAddress($email);     // Add a recipient
			$mail->send();			
			
      break;
    case "forgot": 
			$msg = "Dear $email <br> <br>There has been a request to reset the password for your account. Please go to the following link to set your new password: <br> $imploded_url/UpdatePassword.php?email=$email&confirm_code=$email_confirm_code \nMany thanks, <br><br>$mailer_team_name";

			$mail->addAddress($email);     // Add a recipient
			$mail->Subject = "Resetting password with Collector";
			$mail->Body    = $msg;
			$mail->AltBody = $msg;
			$mail->send();			
			return "Request for password reset sent to $email";
      break;
  }
  */
}

function project_exists(
    $hashed_location,
    $conn,
    $email,
    $mailer_password,
    $mailer_user,
    $mailer_host,
    $mailer_from,
    $mailer_team_name,
    $email_hash_password
){
	$check_exists_sql = "SELECT * FROM `view_project_users` WHERE `location` = '$hashed_location'";
	
	$result = $conn->query($check_exists_sql);
	
    if($result -> num_rows == 0){
		
	    return create_project($hashed_location,$conn,$email,$email_hash_password);
	} else {		
  
        $row = mysqli_fetch_array($result);
        $initial_email = $row['email'];
    
		//check if combination of user and location exists
		$check_exists_sql = "SELECT * FROM `view_project_users` WHERE `location` = '$hashed_location' AND `email` = AES_ENCRYPT('$email', '$email_hash_password');";
		$result = $conn->query($check_exists_sql);
    		
    		
		if($result -> num_rows == 0){
			$sql = "INSERT INTO `contributors` (`project_id`,`user_id`,`contributor_status`) VALUES(
    				(SELECT `project_id` FROM `projects` WHERE `location` = '$hashed_location'), 
    				(SELECT `user_id` FROM `users` WHERE `email`=AES_ENCRYPT('$email', '$email_hash_password')),
    				('u'));";
    		
    		
			if ($conn->query($sql) === TRUE) {
			        
    			    /*
    				$mail = new PHPMailer(true);           // Passing `true` enables exceptions
    				$mail->SMTPDebug = 0;                  // Enable verbose debug output
    				//$mail->isSMTP();                     // Set mailer to use SMTP
    				$mail->Host = "$mailer_host";  				 // smtp2.example.com, Specify main and backup SMTP servers
    				$mail->SMTPAuth = true;                // Enable SMTP authentication
    				$mail->Username = "$mailer_user";			 // SMTP username
    				$mail->Password = "$mailer_password";  // SMTP password
    				$mail->SMTPSecure = 'tls';
    				$mail->SMTPOptions = array(
    					'ssl' => array(
    							'verify_peer' => false,
    							'verify_peer_name' => false,
    							'allow_self_signed' => true
    					)
    				);                            // Enable TLS encryption, `ssl` also accepted
    				$mail->Port = 587;                                    // TCP port to connect to
    				$mail->setFrom("$mailer_from", 'Collector');
    				$mail->isHTML(true);                                  // Set email format to HTML
    				
    				$exploded_url = explode("/",$_SERVER['SERVER_NAME'].$_SERVER['PHP_SELF']);
    				array_pop($exploded_url);
    				$imploded_url = "https://".implode("/",$exploded_url);
    				
    				$msg = "Dear " . $initial_email . " <br><br>$email wants to be a collaborator on your project at $location. If you are okay with this, please go to the following link: <br><br> $imploded_url/collaborator.php?email=$email&location=$location <br><br>Best wishes, <br>$mailer_team_name";
    
    				// use wordwrap() if lines are longer than 70 characters
    				//$msg = wordwrap($msg,70);
    
    				// send email
    				$mail->Subject = "Collector: $email wants to collaborate!";
    				$mail->Body    = $msg;
    				$mail->AltBody = $msg;
    
    				$mail->addAddress($initial_email);     // Add a recipient
    				$mail->send();	
    				
    				return "Your request to be a collaborator has been sent.";
    				*/
    			} else {
    				echo  $conn->error;;
    			}	
    		} else if($row['contributor_status'] == "u"){
          return "Still awaiting confirmation that you are a collaborator. Do contact your colleague who originally created this project";
        } else if($row['contributor_status'] == "v"){
          return "You are already registered with this project.";
        } else {
          return "error: something has gone wrong with the mysql databases, please contact your admin";
        }
	}
}

function insert_row(
    $email,
    $password,
    $conn,
	$captcha_secret,
	$mailer_user,
	$mailer_password,
	$mailer_host,
	$mailer_from,
	$mailer_team_name,
	$email_hash_password
){
	$salt = create_random_code(20);
	$pepper = create_random_code(20);	
	$prehashed_code = create_random_code(20);
	$hashed_password = password_hash($salt.$password.$pepper, PASSWORD_BCRYPT);
	$hashed_code = password_hash($prehashed_code, PASSWORD_BCRYPT);
	
	
	//print_r($conn);
	$sql = "INSERT INTO `users` (`email`, `password`,          `hashed_code`, `salt`, `pepper`, `account_status`,`max_server_space_mb`,`max_storage_space_gb`) 
	                     VALUES(AES_ENCRYPT('$email', '$email_hash_password'), '$hashed_password', '$hashed_code','$salt','$pepper','u',             '1000',               '150');";
	
	if ($conn->query($sql) === TRUE) {			
		$msg = "First line of text\nSecond line of text";

        // use wordwrap() if lines are longer than 70 characters
        //$msg = wordwrap($msg,70);
        
        // send email
        mail("anthony.haffey@gmail.com","My subject",$msg);
		
		/*	
		email_user("registration",
					$email,
					$prehashed_code,
					$mailer_user,
					$mailer_password,
					$mailer_host,
					$mailer_from,
					$mailer_team_name);
					*/
		return "You have just received a registration e-mail. Please check your spam box in case it has gone there. You won't be able to proceed until you've clicked on the link in the e-mail.";
	} else {      
		return "Error adding user: " . $conn->error;
	}
}

function utf8ize($d) {
    if (is_array($d)) {
        foreach ($d as $k => $v) {
            $d[$k] = utf8ize($v);
        }
    } else if (is_string ($d)) {
        return utf8_encode($d);
    }
    return $d;
}

function list_data(
    $conn,
    $email,
    $password,
    $email_hash_password
){
    
    $sql="SELECT * FROM `users` WHERE `email`=AES_ENCRYPT('$email', '$email_hash_password');";
    $result = $conn->query($sql);
	$row = mysqli_fetch_array($result);
    if($result->num_rows > 0){
        if (password_verify($row['salt'].$password.$row['pepper'], $row['password'])){
            /*
            * List all your data on this server
            */
            //$server_max_space  = $row['max_server_space_mb'];
            //$storage_max_space = $row['max_storage_space_gb'];
          
            $sql    = "SELECT * FROM `view_data_users` WHERE `email`=AES_ENCRYPT('$email', '$email_hash_password') AND `contributor_status`='v';";
            $result = $conn->query($sql);
          
            $exp_counts = array();
            //$vps_file_structure = [];
            $user_space = [];
            $user_space["participants"] = [];
            $user_space["max_server_space_mb"] = $row['max_server_space_mb'];
            //$user_space["max_storage_space_gb"] = $row['max_storage_space_gb'];
            while($row    = mysqli_fetch_array($result)){
                array_push($user_space["participants"],$row);
                //print_r($user_space);
                
            }
            echo json_encode(utf8ize($user_space));
        } else {
            echo "are you sure you typed in your password and e-mail correctly?";
        }
    } else {
        echo "are you sure you typed in your password and e-mail correctly?";
    }   
}

function unique_published_id($conn){
  $published_id = generateRandomString(16);
  //check that published_id doesn't already exist
  $sql = "SELECT * FROM `project` WHERE `published_id` = '$published_id'";
  $result = $conn->query($sql);
  if($result -> num_rows == 0){
	return $published_id;
  } else {
	unique_published_id($conn);
  }
}

$conn = new_conn();

if($_POST['action'] == "delete_server_data"){
  
    $location_folder = explode("_____",$_POST['this_folder']);
    $location        = $location_folder[0];
    $this_folder     = $location_folder[1];
    $exp_id          = explode("_", $location)[0];
    $sql = "SELECT * FROM `view_data_users` WHERE `project_id`='$exp_id' AND `email`=AES_ENCRYPT('$email', '$email_hash_password') AND `hashed_user_id`='$this_folder'";
    $result = $conn->query($sql);
    $row = mysqli_fetch_array($result);
    if (password_verify($row['salt'].$password.$row['pepper'], $row['password'])){
        
        
        
        array_map('unlink', glob("../server_data/$location/$this_folder/*.*"));
        rmdir("../server_data/$location/$this_folder");
        
        // update the relevant row in data table
        $sql = "UPDATE `view_data_users` SET `server_status`='d'
            WHERE `email`=AES_ENCRYPT('$email', '$email_hash_password') AND `hashed_user_id`='$this_folder'";
        $result = $conn->query($sql);
            
        
        echo "data_id = ".$row["data_id"];
        
        if ($conn->query($sql) === TRUE) {
            
            //nothing needed
            
        } else {
          echo "Error updating the table of data: " . $conn->error;
        }
        
        // delete the relevant rows in incomplete_data table
        
        echo "You succesfully deleted participant <b>$this_folder</b> from project <b>$location</b>";
    } else {
        echo "Your password was not accepted";
    }    
}

if($_POST["action"] == "download_server_data"){
    
    $location_folder = explode("_____",$_POST['this_folder']);
    $location        = $location_folder[0];
    $this_folder     = $location_folder[1];
    $exp_id = explode("_", $location)[0];
    $sql = "SELECT * FROM `view_data_users` WHERE `project_id`='$exp_id' AND `email`=AES_ENCRYPT('$email', '$email_hash_password')";
    
    $result = $conn->query($sql);
    $row = mysqli_fetch_array($result);
    if (password_verify($row['salt'].$password.$row['pepper'], $row['password'])){
        //echo "../temp_data/$location/$this_folder";
        $response_json = [];
        $files_to_send = array_diff(scandir("server_data/$location/$this_folder"), 
                                    array('.', '..'));
        foreach($files_to_send as $file_to_send){
          $response_json[$file_to_send] = file_get_contents("server_data/$location/$this_folder/$file_to_send");    
        }
        echo json_encode($response_json);
        //resume here
    } else {
        echo "verification of user and password failed";
    }
}

if($_POST["action"] == "download_storage_data"){
    if($_POST['storage_backup'] == "storage"){
        $server_storage = "collector-data-1";
    } else if($_POST['storage_backup'] == "backup"){
        $server_storage = "collector-data-backup-1";
    }
    $location_folder = explode("_____",$_POST['this_folder']);
    $location        = $location_folder[0];
    $this_folder     = $location_folder[1];
    $all_data        = $location_folder[2];
    $exp_id = explode("_", $location)[0];
    $sql = "SELECT * FROM `view_data_users` WHERE `project_id`='$exp_id' AND `email`=AES_ENCRYPT('$email', '$email_hash_password') AND `hashed_user_id`='$this_folder'";
    $result = $conn->query($sql);
	$row = mysqli_fetch_array($result);
    if (password_verify($row['salt'].$password.$row['pepper'], $row['password'])){
    
        $response_json = [];
        if($all_data == "complete"){
            $response_json["all_data"] = [
                gcs_read_file($server_storage,
                $email       . "/" .
                $location    . "/" .
                $this_folder . "_" .
                "all_data.txt")
            ];
        } else {
            
            $sql = "SELECT * FROM `incomplete_data` WHERE `data_id` = '" . $row["data_id"] . "';";
            
            $response_json["all_data"] = [];
            $result = $conn->query($sql);
            while($row    = mysqli_fetch_array($result)){
                $each_data = $this_folder . "_trial_" . $row["trial_no"] . ".txt";
                array_push(
                    $response_json["all_data"], 
                    gcs_read_file(
                        $server_storage,
                        $email       . "/" .
                        $location    . "/" .
                        $each_data
                    )
                );
            }
        }
        echo json_encode($response_json);
    }
}


if($_POST["action"] == "forgot_password"){
	$sql="SELECT * FROM users WHERE email='$email'";
  $result = $conn->query($sql);
	
	if($result->num_rows == 0){
    echo "You don't appear to have registered on this server";
  } if($result->num_rows > 1){
    echo "You appear to have registered multiple times on this server. Please contact admin";
  } else {
    
		
	//need to generate a new confirm code
	$prehashed_code = create_random_code(20);
	$hashed_code = password_hash($prehashed_code, PASSWORD_BCRYPT);
	
	$sql = "UPDATE `users` set `hashed_code` = '$hashed_code' WHERE `email` = AES_ENCRYPT('$email', '$email_hash_password');";
	if ($conn->query($sql) === TRUE) {
		echo email_user("forgot",
						$email,
						$prehashed_code,
						$mailer_user,
						$mailer_password,
    					$mailer_host,
    					$mailer_from,
    					$mailer_team_name);
						
	} else {
		echo "Failed to update you confirmation code. Please contact admin";
	}
  }
}



if($_POST["action"] == "list_data"){
   list_data(
        $conn,
        $email, 
        $password,
        $email_hash_password
    );
}

if($_POST["action"] == "unregister") {
  $sql="SELECT * FROM users WHERE email='$email'";
  $result = $conn->query($sql);
	$row = mysqli_fetch_array($result);
  if($result->num_rows > 0){
		
	if (password_verify($row['salt'].$password.$row['pepper'], $row['password'])){
		$user_id = $row['user_id'];
		echo "Identifying projects you contribute to:<br>";
		$sql    = "SELECT * FROM `view_project_users` WHERE `email`=AES_ENCRYPT('$email', '$email_hash_password');";
		$result = $conn->query($sql);
		
		$exp_counts = array();
		while($row    = mysqli_fetch_array($result)){
			$exp_id = $row['project_id'];
			$inner_sql = "SELECT * FROM `view_project_users` WHERE `project_id` = '$exp_id'";
			$inner_result = $conn->query($inner_sql);
			while($inner_row = mysqli_fetch_array($inner_result)){
				if(isset($exp_counts[$exp_id])){
					$exp_counts[$exp_id]++;
				} else {
					$exp_counts[$exp_id] = 1;
				}
			} 
		}
		foreach($exp_counts as $exp_id => $this_count){
			echo "$exp_counts -> $exp_id -> $this_count<br>";
			if($this_count == 1){
				//delete project from projects
				$sql = "DELETE FROM `projects` WHERE `project_id` = '$exp_id';";
				if ($conn->query($sql) === TRUE) {
					echo "Succesfully deleted an project you were the only contributor to<br>";
				} else {
					echo "Error deleting an project you were the only contributor to: " . $conn->error;
				}
			} 
			$sql = "DELETE FROM `contributors` WHERE `user_id` = '$user_id';";
			if ($conn->query($sql) === TRUE) {
				echo "Succesfully removed yourself as a contributor to an project<br>";
			} else {
				echo "Error removing you as a contributor from an project: " . $conn->error;
			}
		}
		$sql = "DELETE FROM `users` WHERE `email` = AES_ENCRYPT('$email', '$email_hash_password');";
		if ($conn->query($sql) === TRUE) {
			echo "Succesfully deleted your account";
		} else {
			echo "Error deleting user: " . $conn->error;
		}
	} else {
		echo "Wrong password. Click the <b>Forgot Password</b> to get an e-mail with to create a new one.";
	}
			
  }
}


//use switch statement instead??
if($_POST["action"] == "register") {
    
    
    $sql="SELECT * FROM `users` WHERE email=AES_ENCRYPT('$email', '$email_hash_password');";
    $result = $conn->query($sql);
    $row = mysqli_fetch_array($result);
    if($result->num_rows > 0){
		if($row['account_status'] == "V"){
			echo "user already exists";
		} else {
			$sql = "DELETE FROM `users` WHERE `email` = AES_ENCRYPT('$email', '$email_hash_password');";
			if ($conn->query($sql) === TRUE) {
			  
				$valid_extensions =  array_map('str_getcsv', file('$home_dir/ValidExtensions.csv'));
				array_walk($valid_extensions, function(&$a) use ($valid_extensions) {			
					$a = array_combine($valid_extensions[0], $a);			
				});
				array_shift($valid_extensions); # remove column header
				
				//check if a valid extension
				$valid_extension_found = false;
				foreach($valid_extensions as $valid_extension_row){
					if(substr_compare($email, $valid_extension_row['email'], -strlen($valid_extension_row['email'])) === 0){
		//			if (substr($email, -1) == $valid_extension_row['email']) {
						$valid_extension_found = true;
						echo insert_row($email, 
										$password, 
										$conn, 
										$captcha_secret, 	
										$mailer_user,
										$mailer_password,
					                    $mailer_host,
					                    $mailer_from,
            					        $mailer_team_name,
            					        $email_hash_password);
					}
				}
				if($valid_extension_found == false){
					echo "You cannot register $email on this server, because the extension associated with your address isn't registered with us. If you are from a University please e-mail team@someopen.solutions to discuss joining our network. Alternately, you could set-up your own server by cloning the repository at https://github.com/some-open-solutions/collector onto your own server. Do check the documentation for further guidance how to set up your own server. This will allow you to manage how data is stored/e-mailed from there.";
				}
				
				
			} else {
				echo "Error deleting old version of the user: " . $conn->error;
			}
		}
  } else {
		//read the csv with all the servers
		$valid_extensions =  array_map('str_getcsv', file('$home_dir/NetworkMembers.csv'));
		
		array_walk($valid_extensions, function(&$a) use ($valid_extensions) {			
			$a = array_combine($valid_extensions[0], $a);			
		});
		array_shift($valid_extensions); # remove column header
		
		//print_r($valid_extensions);
		
		//check if a valid extension
		$valid_extension_found = false;
		foreach($valid_extensions as $valid_extension_row){
			if(substr_compare($email, $valid_extension_row['email'], -strlen($valid_extension_row['email'])) === 0){
//			if (substr($email, -1) == $valid_extension_row['email']) {
				$valid_extension_found = true;
				echo insert_row($email, 
								$password, 
								$conn, 
								$captcha_secret, 	
								$mailer_user,
								$mailer_password,
            					$mailer_host,
            					$mailer_from,
            					$mailer_team_name,
            					$email_hash_password);
			}
		}
		if($valid_extension_found == false){
			echo "You cannot register $email on this server, because the extension associated with your address isn't registered with us. If you are from a University please e-mail team@someopen.solutions to discuss joining our network. Alternately, you could set-up your own server by cloning the repository at https://github.com/some-open-solutions/collector onto your own server. This will allow you to manage how data is stored/e-mailed from there.";
		}
  }
}

if($_POST['action'] == "update_password"){
	$sql = "SELECT * FROM users WHERE email='$email'";    
  $result = $conn->query($sql);
	
	if($result->num_rows == 0){
		echo "This account isn't on our database. Please go back and register it.";
	} else if($result->num_rows > 1){
		echo "There are more than 1 occurrences of this account. Please contact admin to fix this";
	} else {
		$row = mysqli_fetch_array($result);
    $user_id = $row['user_id'];
				
		if(strlen($password)>7){
			//update password
			//also update verification to verified
			
			$salt = create_random_code(20);
			$pepper = create_random_code(20);	
			$prehashed_code = create_random_code(20);
			$hashed_password = password_hash($salt.$password.$pepper, PASSWORD_BCRYPT);
			
			$sql = "UPDATE `users` SET `password`='$hashed_password', `salt`='$salt',`pepper`='$pepper',`account_status`='v' WHERE `email`= AES_ENCRYPT('$email', '$email_hash_password')";
			if ($conn->query($sql) === TRUE) {
				echo "Succesfully updated your password!";
			} else {
				echo "error:". $conn->error;
			}
		} else {
			echo "Your password should be at least 8 characters";
		}				
	
	}
}

if($_POST['action'] == "unregister_project"){
  $sql = "SELECT * FROM users WHERE email='$email'";    
  $result = $conn->query($sql);
	
	if($result->num_rows > 1){
		echo 'Please contact team@someopen.solutions -  there are multiple instances of this e-mail address registered.';
  } else if($result->num_rows == 1){
    $row = mysqli_fetch_array($result);
    $user_id = $row['user_id'];
    if($row['account_status'] == 'v'){  
      if (password_verify($row['salt'].$password.$row['pepper'], $row['password'])){
				
            $sql    = "SELECT * FROM `view_project_users` WHERE `location`='".$location."';";
            $result = $conn->query($sql);
            $row    = mysqli_fetch_array($result);
            $exp_id = $row['project_id'];
            //delete the project if the user is the only collaborator
            if($result->num_rows == 1){
				//confirm that this person is actually a contributor
				$confirm_sql = "SELECT * FROM `view_project_users` WHERE `location`='".$location."' AND `email` = AES_ENCRYPT('$email', '$email_hash_password');";
				$confirm_result = $conn->query($confirm_sql);
				if($confirm_result->num_rows == 1){
					$sql = "DELETE FROM `projects` WHERE `project_id` = '$exp_id';";
					if ($conn->query($sql) === TRUE) {
						echo "Succesfully deleted an project you were the only contributor to<br>";
					} else {
						echo "Error deleting an project you were the only contributor to: " . $conn->error;
					}								
				} 
            }
            
            //delete the user as a collaborator
            $sql = "DELETE FROM `contributors` WHERE `user_id` = '$user_id' AND `project_id`='$exp_id';";
            if ($conn->query($sql) === TRUE) {
              echo "You are (no longer) a collaborator on this project.<br>";
            } else {
              echo "Error removing you as a contributor from an project: " . $conn->error;
            }
         
      } else {
        echo 'Invalid e-mail address and/or password.';
      }						
    } else {
      echo "This account has been locked out. Please check your e-mails for a code to log you back in.";
    }    
  } else {
    echo 'Invalid e-mail address and/or password.';
	}
}



if($_POST["action"] == "update_project"){ 
    $sql = "SELECT * FROM `users` WHERE `email`=AES_ENCRYPT('$email', '$email_hash_password');";
    $result = $conn->query($sql);
	if($result->num_rows > 1){
		echo 'Please contact team@someopen.solutions -  there are multiple instances of this e-mail address registered.';
    } else if($result->num_rows == 1){
        $row = mysqli_fetch_array($result);
        if($row['account_status'] == 'v'){  
            if (password_verify($row['salt'].$password.$row['pepper'], $row['password'])){
			    echo project_exists(
			        $hashed_location,
                    $conn,
                    $email,
                    $mailer_password,
                    $mailer_user,
    				$mailer_host,
    				$mailer_from,
    				$mailer_team_name,
    				$email_hash_password
				);
      
      } else {
        echo 'Invalid e-mail address and/or password.';
      }						
    } else {
      echo "This account has been locked out. Please check your e-mails for a code to log you back in.";
    }    
  } else {
    echo 'Invalid e-mail address and/or password.';
	}
}
mysqli_close($conn);

?>