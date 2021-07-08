<?php

ini_set('display_errors', '1');
ini_set('display_startup_errors', '1');
error_reporting(E_ALL);

function create_random_code($length){
    $characters = '0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ';
    $charactersLength = strlen($characters);        
    $new_code= '';
    for ($i = 0; $i < $length; $i++) {
        $new_code .= $characters[rand(0, $charactersLength - 1)];
    }
    return $new_code;
}

function insert_row(
    $email,
    $password,
    $conn,
    $email_hash_password,
    $admin = 0,
    $status = 'u'
){
	$salt = create_random_code(20);
	$pepper = create_random_code(20);	
	$prehashed_code = create_random_code(20);
	$hashed_password = password_hash($salt.$password.$pepper, PASSWORD_BCRYPT);
	$hashed_code = password_hash($prehashed_code, PASSWORD_BCRYPT);
	
	
	//print_r($conn);
	$sql = "INSERT INTO `users` (`email`, `password`,          `hashed_code`, `salt`, `pepper`, `account_status`,`max_server_space_mb`,`max_storage_space_gb`, `admin`) 
	                     VALUES(AES_ENCRYPT('$email', '$email_hash_password'), '$hashed_password', '$hashed_code','$salt','$pepper','$status',             '100',               '0', $admin);";
	
	if ($conn->query($sql) === TRUE) {			
		return "success";
	} else {      
		return "Error adding user: " . $conn->error;
	}
}

$email_hash_password = create_random_code(30);

$email    = $_POST["admin_email_input"];
$admin_password = $_POST["admin_password_input"];


/*
* try and catch code later?
*/

    
$home_dir = dirname($_SERVER['DOCUMENT_ROOT']);
$sql_location = "$home_dir/sqlCollector.php";

$sql_string = '<?php


$email_hash_password = "' . $email_hash_password . '";

// Create connection
function new_conn(){
    $this_conn = new mysqli(
        "' . $_POST['servername_input']        . '", 
        "' . $_POST['database_username_input'] . '",
        "' . $_POST['database_password_input'] . '",
        "' . $_POST['database_name_input']     . '"
    );
    return $this_conn;
}

//$conn = ;
/*
if($conn->connect_error){
    echo "failed to connect";
}
*/


?>';

file_put_contents($sql_location, $sql_string);

require_once($sql_location);



$collector_sql = file_get_contents("sqlQueries/collector.sql");
//echo $contributors_sql;

$multi_conn = new_conn();

if ($multi_conn->multi_query($collector_sql) === TRUE) {

    $sql="SELECT * FROM `users` WHERE `email`=AES_ENCRYPT('$email', '$email_hash_password');";
    
    $multi_conn->close();

    $single_conn = new_conn();

    $result = $single_conn->query($sql);
    
    $row = mysqli_fetch_array($result);
    if($result->num_rows > 0){
        /*
        * Check if a VERIFIED user with that email address exists or not
        */
		if($row['account_status'] == "V"){
			echo "user already exists";
		} else {
		    /*
		    * If the user exists, replace them if they're not verified
		    */
			$sql = "DELETE FROM `users` WHERE `email` = AES_ENCRYPT('$email', '$email_hash_password');";
			if ($single_conn->query($sql) === TRUE) {
			  
				echo insert_row(
				    $email, 
					$admin_password,
					$single_conn,
					$email_hash_password,
					1, // Admin
					'v'
				);
			
				
				
				
			} else {
				echo "Error deleting old version of the user: " . $conn->error;
			}
		}
    } else {
        echo insert_row(
            $email, 
			$admin_password, 
			$single_conn,
			$email_hash_password,
			1, // admin
			'v'
		);
    }
	
	
} else {
	echo  $single_conn->error;
}

?>