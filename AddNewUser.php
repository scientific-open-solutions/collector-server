<?php

print_r($_POST);

ini_set('display_errors', '1');
ini_set('display_startup_errors', '1');
error_reporting(E_ALL);

/*
* check if the user is a valid admin user
*/

$home_dir = dirname($_SERVER['DOCUMENT_ROOT']);
$sql_location = "$home_dir/sqlCollector.php";

require_once $sql_location;

$single_conn = new_conn();

$sql="SELECT * FROM `users` WHERE `email`=AES_ENCRYPT('". $_POST["admin_email_input"]  ."', '$email_hash_password')  AND `account_status` = 'v';";

$result = $single_conn->query($sql);
    
//print_r($result);
    

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



if($result->num_rows > 0){
    $row = mysqli_fetch_array($result);
    
    /*
    * verify the password here
    */
    //$hashed_password = password_hash($salt.$password.$pepper, PASSWORD_BCRYPT);
    
    print_r($row);
    if(password_verify($row['salt'].$_POST["admin_password_input"].$row['pepper'], $row['password'])){
        
        echo "you're good to go";    
        echo insert_row(
			$_POST["new_email_input"], 
			$_POST["new_password_input"],
			$single_conn,
			$email_hash_password,
			0, // Admin
		    'v'
		);
        
        
        
    } else {
        echo "password not accepted";
    }
    
    
    
    
} else {
    echo "you're not good to go";
}

?>