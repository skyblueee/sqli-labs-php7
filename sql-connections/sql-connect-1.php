<?php

//including the Mysql connect parameters.
include("../sql-connections/db-creds.inc");
@error_reporting(0);
@$con1 = mysqli_connect($host,$dbuser,$dbpass);
// Check connection
if (!$con1)
{
    echo "Failed to connect to MySQL: " . mysqli_error($con1);
}


    @mysqli_select_db($con1,$dbname1) or die ( "Unable to connect to the database: $dbname1".mysqli_error($con1));






$sql_connect_1 = "SQL Connect included";

############################################
# For Challenge series--- Randomizing the Table names.

?>




 
