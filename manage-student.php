<!DOCTYPE html>
<html>
<head>
	<meta charset="utf-8">
	<meta name="viewport" content="width=device-width, initial-scale=1">
	<title></title>
	<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@4.0.0/dist/css/bootstrap.min.css" integrity="sha384-Gn5384xqQ1aoWXA+058RXPxPg6fy4IWvTNh0E263XmFcJlSAwiGgFAW/dAiS6JXm" crossorigin="anonymous">

	<script src="https://code.jquery.com/jquery-3.2.1.slim.min.js" integrity="sha384-KJ3o2DKtIkvYIK3UENzmM7KCkRr/rE9/Qpg6aAZGJwFDMVNA/GpGFF93hXpG5KkN" crossorigin="anonymous"></script>
<script src="https://cdn.jsdelivr.net/npm/popper.js@1.12.9/dist/umd/popper.min.js" integrity="sha384-ApNbgh9B+Y1QKtv3Rn7W3mgPxhU9K/ScQsAP7hUibX39j7fakFPskvXusvfa0b4Q" crossorigin="anonymous"></script>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@4.0.0/dist/js/bootstrap.min.js" integrity="sha384-JZR6Spejh4U02d8jOt6vLEHfe/JQGiRRSQQxSfFWpi1MquVdAyjUar5+76PVCmYl" crossorigin="anonymous"></script>

</head>
 <?php require 'partials/_navbar.php'?>

<style type="text/css">

		:root
		{
				--bgcolor-1: #150734;
				--bgcolor-2: #0F2557;
				--maincolor: #000000;

		}

		.page-title{
				margin-left:27rem;
				margin-top:6rem;
				font-size:2.2rem;
				color:var(--bgcolor-1);
				border-bottom: 2px solid var(--bgcolor-1);
				width:22rem;

			}

		.container
		{
			border:2px solid gray;
			height:30rem;
			margin-left:28rem;
			margin-top: 2rem;
			font-size: 1.4rem;

		}
		.card-body
		{
			width:100rem;
		
			
		}
        .row:nth-child(3){
        	width:50%;  	
      		margin:auto;
        }
        .row:nth-child(3) .btn-1 input{
        	border-radius:0.7rem;
        	padding:0.5rem 4rem;
        	
        	
        }
		.btn btn-primary
        {
            display: inline-block;
        }
	</style>
</head>

<body>
	<div class="page-title">
		MANAGE STUDENT
	</div>
	<div class="container">
		<div class="row justfy-content-center">
			<div class="col-md-12">
				<div class="card mt-5">
					<div class="card-header">
						<h3>Fill Neccessary Information to Insert Student Record.</h3>
					</div>
					<div class="card-body">
						<form action="" method="POST">
                               <div class="row">
                                   <div class="col-md-3">
                                       <div class="form-group">
                                           <label>ID</label>
                                           <input type="text" name="m_id" class="form-control">
                                       </div>
                                   </div>

                                   <div class="col-md-3">
                                       <div class="form-group">
                                           <label>Subject</label>
                                        <select name="m_subject" id="m_subject" class="form-control">
                                            <option value="MAN">MAN</option>
                                            <option value="CND">CND</option>
                                            <option value="MEC">MEC</option>
                                            <option value="VWV">VWV</option>
                                            <option value="ETE">ETE</option>
                                            <option value="Advanced Network Concepts">Advanced Network Concepts</option>
                                            <option value="Auditiong of Information Systems">Auditing of Information Systems</option>
                                            <option value="Computer Security & Cryptography">Computer Security & Cryptography</option>
                                            <option value="Data Mining & Business Intelligence">Data Mining & Business Intelligence</option>
                                            <option value="Entrepreneurship & Innovation">Entrepreneurship & Innovation</option>
                                            <option value="Human Resource Management">Human Resource Management</option>
                                            <option value="Information Resource Management">Information Resource Management</option>
                                            <option value="Systems Project">Systems Project</option>
                                            <!-- <option value="MGT">MGT</option> -->
                                        </select>
                                       </div>
                                   </div>

                                   <div class="col-md-3">
                                       <div class="form-group">
                                           <label>Date</label>
                                           <input type="date" name="m_date" class="form-control">
                                       </div>
                                   </div>
                              </div>
                            <div class="row">
                            	<div class="col-md-3">
                            		<div class="form-group">
                            			<label>DAY</label>
                            			<select name="m_day" class="form-control">
                            				<option>Monday</option>
                            				<option>Tuesday</option>
                            				<option>Wednesday</option>
                            				<option>Thursday</option>
                            				<option>Friday</option>

                            			</select>
                            		</div>
                            	</div>
                                <div class="col-md-3">
                                    <div class="form-group">
                                        <label>Time</label>
                                        <select name="m_time" id="m_time" class="form-control">
                                            <option value="1">07:00am - 10:00am</option>
                                            <option value="2">10:00am 1:00pm</option>
                                            <option value="3">01:00pm 04:00pm</option>
                                            <option value="4">04:00pm 07:00pm</option>
                                        </select>
                                    </div>
                                </div>
                  
                            </div>
                            <div class="row">
                                <div class="col-md-12">
                                     <center><button type="submit" name="insert-btn" class="btn btn-primary" style="padding:.4rem 5rem; font-size:14px;">INSERT</button>
                                   <button class="btn btn-primary" name="delete-btn" style="padding:.4rem 5rem; font-size:14px;">DELETE</button></center>
                                </div>
                            </div>
                        </form>
						
					</div>
					
				</div>
            </div>
        </div>
    </div>        
</body>
</html>
<?php

// check whether insert-btn ic clicked
if (isset($_POST['insert-btn'])) {
    // code...

// Check if the form is submitted
if ($_SERVER["REQUEST_METHOD"] == "POST") {

     date_default_timezone_set('Africa/Nairobi');

    // Get form data
    $id = $_POST["m_id"];
    $subject = $_POST["m_subject"];
    $date = $_POST["m_date"];
    $day = $_POST["m_day"];
    $time = $_POST["m_time"];

    $new_date = date("d-m-Y",strtotime($date));

    if (!empty($_POST['m_id']) && !empty($_POST['m_subject']) && !empty($_POST['m_date']) && !empty($_POST['m_day']) && !empty($_POST['m_time'])) {

    // Connect to the database
    $conn = new mysqli("localhost", "root", "", "cpp");

    // Check connection
    if ($conn->connect_error) {
        die("Connection failed: " . $conn->connect_error);
    }

        // Check if record already exists
        $check_sql = "SELECT * FROM present_table WHERE id = ? AND subject = ? AND date = ? AND time = ?";
        $check_stmt = $conn->prepare($check_sql);
        $check_stmt->bind_param("ssss", $id, $subject, $new_date, $time);
        $check_stmt->execute();
        $result = $check_stmt->get_result();
        
        if ($result->num_rows > 0) {
            echo "<script>";
            echo "alert('This attendance record already exists!')";
            echo "</script>";
        } else {

    // Insert data into the database
    $sql = "INSERT INTO `present_table`(`id`, `subject`, `date`, `day`, `time`) VALUES (?, ?, ?, ?, ?)";
    $stmt = $conn->prepare($sql);

     // Get the student's semester from the student table if possible
     $semester = 0; // Default value if not found
     $sem_sql = "SELECT semester FROM student WHERE s_id = ? OR roll_no = ?";
     if ($sem_stmt = $conn->prepare($sem_sql)) {
         $sem_stmt->bind_param("ss", $id, $id);
         $sem_stmt->execute();
         $sem_result = $sem_stmt->get_result();
         if ($sem_row = $sem_result->fetch_assoc()) {
             $semester = $sem_row['semester'];
         }
         $sem_stmt->close();
     }

    $stmt->bind_param("sssss", $semester $id, $subject, $new_date, $day, $time);

    if ($stmt->execute()) {

        echo "<script>";
        echo "alert('Student inserted successfully!')";
        echo "</script>";

    } else {
        
         echo "<script>";
         echo "alert('Error In Inserting Data.')";
         echo "</script>";
    }
    
     // Close connection
    $stmt->close();
    $conn->close();

} //empty() 
else {
         echo "<script>";
         echo "alert('Please fill all fields')";
         echo "</script>";
}
  
}

} //closing insert-btn functinality..


//check whether delete-btn ic clicked
if (isset($_POST['delete-btn'])) {
    // code...

// Check if the form is submitted
if ($_SERVER["REQUEST_METHOD"] == "POST") {

     date_default_timezone_set('Africa/Nairobi');

   if (!empty($_POST['m_id']) && !empty($_POST['m_subject']) && !empty($_POST['m_date']) && !empty($_POST['m_time'])) {  //check input fileds are filled or not
       
    // Get form data
    $id = $_POST["m_id"];
    $subject = $_POST["m_subject"];
    $date = $_POST["m_date"];
    $day = $_POST["m_day"];
    $time = $_POST["m_time"];

    $new_date = date("d-m-Y",strtotime($date));

    

    // Connect to the database
    $conn = mysqli_connect("localhost", "root", "", "cpp");

    // Check connection
    if (!$conn) {
        die("Connection failed: " . mysqli_connect_error());
    }

    // Insert data into the database
    $sql = " DELETE FROM present_table WHERE id='$id' AND subject='$subject' AND date='$new_date' AND time='$time' ";
    
    if (mysqli_query($conn,$sql)) {

        echo "<script>";
        echo "alert('Student Deleted successfully!')";
        echo "</script>";

    } else {
        
         echo "<script>";
         echo "alert('Error In Deleting Data.')";
         echo "</script>";
    }

     mysqli_close($conn);
    
 } //empty() 
 {
         echo "<script>";
         echo "alert('Please select data to delete.')";
         echo "</script>";
 }

    // Close connection
    
} //request method

}//closing delete-btn functinality..
?>