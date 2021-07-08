<link rel="stylesheet" type="text/css" href="libraries/bootstrapCollector.css">
<script src="libraries/jquery.min.js"></script>
<script src="libraries/bootstrap.min.js"></script>

<?php

/*
* Do we have a table with a valid administrator?
*/

// where is the public_html folder, use this for the relative location of the sqlCollector.php file

/*
ini_set('display_errors', '1');
ini_set('display_startup_errors', '1');
error_reporting(E_ALL);
*/

$home_dir = dirname($_SERVER['DOCUMENT_ROOT']);
$sql_location = "$home_dir/sqlCollector.php";

if(file_exists($sql_location)){
    //require_once $sql_location;
    
    
?>


<main>
  <div class="container py-4">
    <header class="pb-3 mb-4 border-bottom">
      <a href="/" class="d-flex align-items-center text-dark text-decoration-none">
        <span class="fs-4">Admin - Add user</span>
      </a>
    </header>

    <div class="p-5 mb-4 bg-light rounded-3">
      <div class="container-fluid py-5">
        <form action="AddNewUser.php" method="post">
            <table>
                <tr>
                    <td>Your email address</td>
                    <td><input type="text" id="admin_email_input" name="admin_email_input" placeholder=""/></td>
                </tr>
                <tr>
                    <td>Your password</td>
                    <td><input type="text" id="admin_password_input" name="admin_password_input" placeholder=""/></td> 
                </tr>
                <tr>
                    <td>New user email address</td>
                    <td><input type="text" id="new_email_input" name="new_email_input" placeholder=""/></td>
                </tr>
                <tr>
                    <td>New user password</td>
                    <td><input type="text" id="new_password_input" name="new_password_input" placeholder=""/></td> 
                </tr>
            </table>
                
            <button class="btn btn-primary btn-lg" type="submit">Submit</button>
            
        </form>
      </div>
    </div>
    Give instructions how to set up a mysql database and get the following information
    

    
    <footer class="pt-3 mt-4 text-muted border-top">
      SOS
    </footer>
  </div>
</main>



<?php
    
    /*
    $collector_sql = file_get_contents("sqlQueries/collector.sql");
    //echo $contributors_sql;
    
    if ($conn->multi_query($collector_sql) === TRUE) {
		echo "success";
	} else {
		echo  $conn->error;
	}
	*/
} else {
    
?>


<main>
  <div class="container py-4">
    <header class="pb-3 mb-4 border-bottom">
      <a href="/" class="d-flex align-items-center text-dark text-decoration-none">
        <span class="fs-4">Collector - Admin Set Up</span>
      </a>
    </header>

    <div class="p-5 mb-4 bg-light rounded-3">
      <div class="container-fluid py-5">
        <h1 class="display-5 fw-bold">Instructions</h1>
        <p class="col-md-8 fs-4">We'll explain how to complete the form below here. Promise.</p>
        <form action="sqlConnectCreate.php" method="post">
            <table>
                <tr>
                    <td>Database servername</td>
                    <td><input type="text" name="servername_input"  id="servername_input" placeholder="e.g. localhost or IP address"/></td>
                </tr>
                <tr>
                    <td>Database username</td>
                    <td><input type="text" name="database_username_input" id="database_username_input" placeholder=""/></td>
                </tr>
                <tr>
                    <td>Database password</td>
                    <td><input type="text" id="database_password_input" name="database_password_input" placeholder=""/></td>
                </tr>
                <tr>
                    <td>Database name</td>
                    <td><input type="text" id="database_name_input" name="database_name_input" placeholder=""/></td>
                </tr>
                <tr>
                    <td>Your email address</td>
                    <td><input type="text" id="admin_email_input" name="admin_email_input" placeholder=""/></td>
                </tr>
                <tr>
                    <td>Your password</td>
                    <td><input type="text" id="admin_password_input" name="admin_password_input" placeholder=""/></td> 
                </tr>
            </table>
                
            <button class="btn btn-primary btn-lg" type="submit">Submit</button>
            
        </form>
      </div>
    </div>
    Give instructions how to set up a mysql database and get the following information
    

    
    <footer class="pt-3 mt-4 text-muted border-top">
      SOS
    </footer>
  </div>
</main>




<?php
}






// If not, make it

// put the admin password in the level above it


?>