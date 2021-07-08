<?php
/*  Collector (Garcia, Kornell, Kerr, Blake & Haffey)
    A program for running experiments on the web
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
$home_dir = dirname($_SERVER['DOCUMENT_ROOT']);
require_once "$home_dir/sqlCollector.php";

$conn = new_conn();

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

if(isset($_POST['trial_all']) == false){
	$_POST['trial_all'] = "all";
}


if(isset($_POST['study_location'])){
    $study_location = hash(
        "sha256",
        $_POST['study_location']
    );
	
    $users = array_map(
        'str_getcsv', 
        file('../../NetworkMembers.csv')
    );
  
    array_walk($users, function(&$a) use ($users) {
		$a = array_combine($users[0], $a);
	});
	array_shift($users); # remove column header

    
	$sql_query = mysqli_query($conn,"SELECT * FROM `view_project_users` WHERE `location`='$study_location'");
	
	//create an object of user scripts
	$scripts_obj = new stdClass;
	$user_emails = [];
	$valid_location = false;
	
	//echo "study location = $study_location but was ". $_POST['study_location'];
	
	while ($row = mysqli_fetch_array($sql_query)) {
		$valid_location = true;
		if($row['contributor_status'] == "v"){
        $proj_id = $row['project_id'];
        array_push($user_emails, $row['email']);
			//detect whether the exact e-mail is in the list
			foreach($users as $user){
				$this_script = $user['script'];
				if($row['email'] == $user['email']){
					if(isset($scripts_obj -> $this_script)){
						array_push($scripts_obj -> $this_script,$row['email']);
					} else {
						$scripts_obj -> $this_script = [$row['email']];
					}
				}
			}
		}
	}

	//code for saving on the trial level
	if($valid_location){
	    /*
	    * note the following options for server_status, storage_status and backup_status:
	    * e - error
	    * p - partial
	    * f - full
	    * d - deleted (should happen with server whenever there's a successful full file save on google cloud and backup)
	    */
	    
	    
	    /*
	    * server data
	    */
        $hashed_user_id = sha1($_POST['prehashed_code']);  //this should get the same hash every time.
	    try{
		    if(!is_dir("server_data")){
                mkdir("server_data");
            }

            if(!is_dir("server_data/$proj_id")) {
                mkdir("server_data/$proj_id");
            }

            if(!is_dir("server_data/$proj_id/$hashed_user_id")) {
				mkdir("server_data/$proj_id/$hashed_user_id");
			}
			if($_POST['trial_all'] == "trial"){
    			$server_success = "p";
    			file_put_contents(
    			    "server_data/$proj_id/$hashed_user_id/trial_". $_POST['trial_no'] . ".txt",
    				$_POST['encrypted_data']
    			);
			} else {
			    $server_success = "f";
			    file_put_contents(
    			    "server_data/$proj_id/$hashed_user_id/all_data.txt",
    				$_POST['encrypted_data']
    			);
			}
		        
	    } catch (Exception $e){
            echo "failed to save data on the server";
            $server_success = "e";
        }
			
			
            
      
		/*
		* update the ocollector mysql table
		*/

        $sql_query = "SELECT * FROM `data` WHERE `project_id`='$proj_id' AND `hashed_user_id`='$hashed_user_id'";
    	$result = $conn->query($sql_query);
    	
    	/*
	    * note the following options for server_status, storage_status and backup_status:
	    * e - error
	    * p - partial
	    * f - full
	    * d - deleted (should happen with server whenever there's a successful full file save on google cloud and backup)
	    */
	    
    	
        if($result -> num_rows == 0){
            /* create new row */
    	    
    	    $sql = "INSERT INTO `data`(
    	        `hashed_user_id`, 
    	        `project_id`,
    	        `date`, 
    	        `filesize`, 
    	        `trials`, 
    	        `server_status`
	        ) VALUES (
	            '$hashed_user_id',
	            '$proj_id',
	            '".date('Y-m-d H:i:s')."',
	            '".strlen($_POST['encrypted_data'])."',
	            '". $_POST['trial_no']. "',
	            '$server_success'
            )";
            
            if ($conn->query($sql) === TRUE) {
                // need to retrieve the newly created row to get the data_id
                $row = mysqli_fetch_array(mysqli_query($conn,$sql_query));
                echo "success";
            } else {
				echo  $conn->error;;
			}
        } else if($result -> num_rows == 1) {
            
            // select the row
            $row = mysqli_fetch_array(
                mysqli_query($conn,$sql_query)
            );
            
            // add filesize to the row
            $row['filesize'] += strlen($_POST['encrypted_data']);
            
            // update date
            $row['date'] = date('Y-m-d H:i:s');
            
            //check if participant has finished
            if($row["trials"] > 0){
                
                /* update the row in the mysql table */
                $update_sql = "UPDATE `data` SET 
                    `date`='".  $row['date'] . "',
                    `filesize`='" . $row['filesize'] . "',
                    `trials`= '" . $_POST['trial_no'] . "',
                    `server_status` = '$server_success'
                    WHERE `data_id` = '" . $row["data_id"] . "'";
            } else {
                /* update the row in the mysql table */
                $update_sql = "UPDATE `data` SET 
                    `filesize`='" . $row['filesize'] . "',
                    WHERE `data_id` = '" . $row["data_id"] . "'";
            }
            if ($conn->query($update_sql) === TRUE) {
                /*
                * all good: no need to do anything
                */
                echo "success";
            } else {
				echo  $conn->error;;
			}
			
        } else {
            return "error, please contact admin as there are multiple rows";
            /* something has gone wrong */
        }
            
            
           
           
           
		/*
		//check size of folder contents
		$pp_size = GetDirectorySize("../server_data/$proj_id/".$_POST['participant_id']);
		
		//if more than 25, then complain to the relevant researchers (and tell them they may not have received their data).
		if($pp_size > 25000000){
			echo "too big";
			$mail = new PHPMailer(true);
			try {
				//Server settings
				$mail = new PHPMailer(true);                          // Passing `true` enables exceptions
				$mail->SMTPDebug = 0;                                 // Enable verbose debug output
				$mail->Host = "$mailer_host";												  // smtp2.example.com, Specify main and backup SMTP servers
				$mail->SMTPAuth = true;                               // Enable SMTP authentication
				$mail->Username = "$mailer_user";											// SMTP username
				$mail->Password = "$mailer_password";                 // SMTP password
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
				$mail->isHTML(true);  

				$project_id 	 = $_POST['project_id'];

				$body_alt_body = "Hello, <br><br> Apologies if you get this e-mail a lot over the next few minutes. This is because you have a participant whose data (in encrypted form) takes more than 25 megabytes space for your experiment $project_id. This causes a variety of problems so please stop data collection until you have broken up your experiment into smaller chunks. <br><br> Best wishes, <br><br> $mailer_team_name.";

				$mail->isHTML(true);                  // Set email format to HTML
				$mail->Subject = "Collector - Please break up your experiment: $project_id, one participant is more than 25MBs!";
				$mail->Body    = $body_alt_body;
				$mail->AltBody = $body_alt_body;

				foreach($user_emails as $user_email){
					$mail->addAddress($user_email);     // Add a recipient
				}
				$mail->send();	

				//$mail->isHTML(true);                                  // Set email format to HTML

			} catch (Exception $e) {
				echo 'Message could not be sent. Mailer Error: ', $mail->ErrorInfo;
			}
			
		} else {
			echo "success";
		}
		*/
		
	}
}
?>