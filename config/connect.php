<?php
        // Connect to MySQL and select database
        require_once 'db_info.php';
        $conn = @mysqli_connect($host, $username, $password, $db_name) 
            or die("Connection failed: " . mysqli_connect_error());
?>