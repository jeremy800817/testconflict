<?php
 include('config/db.php');
 
 $connection = mysqli_connect($hostname, $username, $password, $dbname) or die("Database connection not established.");


if(isset($_POST["occupationid"])){
    // Capture selected country
    $category = $_POST["occupationid"];
     
    // Define country and city array
        
    // Display city dropdown based on country name
  
    $sqli ="SELECT * FROM myoccupationsubcategory where osc_occupationcategoryid=" . $category;
    $query = mysqli_query($connection, $sqli);
    echo "<div class='dropdown'>";
    echo "<select name='subcategory' id='subcategory' class='dropdown' >";
    echo "<option value=''>Select</option>"; 
    while ($row = mysqli_fetch_array($query)) {
      echo '<option value=" '.$row['osc_id'].' ">'.$row['osc_code'].'</option>';
    }
    echo "</select>";
    echo "</div>";
}
?>