<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Manage Student Attendance</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@4.0.0/dist/css/bootstrap.min.css" integrity="sha384-Gn5384xqQ1aoWXA+058RXPxPg6fy4IWvTNh0E263XmFcJlSAwiGgFAW/dAiS6JXm" crossorigin="anonymous">
    <script src="https://code.jquery.com/jquery-3.2.1.slim.min.js" integrity="sha384-KJ3o2DKtIkvYIK3UENzmM7KCkRr/rE9/Qpg6aAZGJwFDMVNA/GpGFF93hXpG5KkN" crossorigin="anonymous"></script>
    <script src="https://cdn.jsdelivr.net/npm/popper.js@1.12.9/dist/umd/popper.min.js" integrity="sha384-ApNbgh9B+Y1QKtv3Rn7W3mgPxhU9K/ScQsAP7hUibX39j7fakFPskvXusvfa0b4Q" crossorigin="anonymous"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@4.0.0/dist/js/bootstrap.min.js" integrity="sha384-JZR6Spejh4U02d8jOt6vLEHfe/JQGiRRSQQxSfFWpi1MquVdAyjUar5+76PVCmYl" crossorigin="anonymous"></script>

    <style type="text/css">
        :root {
            --bgcolor-1: #150734;
            --bgcolor-2: #0F2557;
            --maincolor: #000000;
        }

        .page-title {
            margin-left: 27rem;
            margin-top: 6rem;
            font-size: 2.2rem;
            color: var(--bgcolor-1);
            border-bottom: 2px solid var(--bgcolor-1);
            width: 22rem;
        }

        .container {
            border: 2px solid gray;
            height: auto;
            margin-left: 28rem;
            margin-top: 2rem;
            font-size: 1.4rem;
            margin-bottom: 2rem;
            padding-bottom: 2rem;
        }

        .card-body {
            width: 100rem;
        }

        .row:nth-child(3) {
            width: 50%;
            margin: auto;
        }

        .row:nth-child(3) .btn-1 input {
            border-radius: 0.7rem;
            padding: 0.5rem 4rem;
        }

        .btn-primary {
            background-color: var(--bgcolor-2);
        }

        .alert {
            margin-top: 1rem;
        }

        .student-selector {
            margin-bottom: 1rem;
        }
    </style>
</head>

<body>
    <?php require 'partials/_navbar.php' ?>

    <div class="page-title">
        MANAGE ATTENDANCE
    </div>
    <div class="container">
        <div class="row justify-content-center">
            <div class="col-md-12">
                <div class="card mt-5">
                    <div class="card-header">
                        <h3>Record Student Attendance</h3>
                    </div>
                    <div class="card-body">
                        <?php
                        // Initialize variables for alerts
                        $success_message = '';
                        $error_message = '';
                        
                        // Connect to the database
                        $conn = new mysqli("localhost", "root", "", "cpp");
                        
                        // Check connection
                        if ($conn->connect_error) {
                            die("Connection failed: " . $conn->connect_error);
                        }
                        
                        // Get students for dropdown
                        $students_query = "SELECT s_id, roll_no, enroll_no, name FROM student ORDER BY name";
                        $students_result = $conn->query($students_query);
                        
                        // Get subjects for dropdown
                        $subjects_query = "SELECT subject_id, subject_code, subject_name FROM subjects ORDER BY subject_name";
                        $subjects_result = $conn->query($subjects_query);
                        
                        // Check if subjects table exists
                        if (!$subjects_result) {
                            // Subjects table might not exist, try to get subjects from present_table instead
                            $subjects_query = "SELECT DISTINCT subject FROM present_table ORDER BY subject";
                            $subjects_result = $conn->query($subjects_query);
                        }
                        
                        // If form is submitted for insert
                        if (isset($_POST['insert-btn'])) {
                            if (!empty($_POST['student_id']) && !empty($_POST['subject_id']) && !empty($_POST['m_date']) && !empty($_POST['m_day']) && !empty($_POST['m_time'])) {
                                // Set timezone
                                date_default_timezone_set('Africa/Nairobi');
                                
                                // Get form data
                                $student_id = $_POST["student_id"];
                                $subject_id = $_POST["subject_id"];
                                $date = $_POST["m_date"];
                                $day = $_POST["m_day"];
                                $time = $_POST["m_time"];
                                
                                // Format date for MySQL (YYYY-MM-DD)
                                $mysql_date = date("Y-m-d", strtotime($date));
                                
                                // Check if the attendance record already exists
                                $check_query = "SELECT attendance_id FROM attendance 
                                              WHERE student_id = ? AND subject_id = ? AND date = ? AND time_slot = ?";
                                $check_stmt = $conn->prepare($check_query);
                                $check_stmt->bind_param("iiss", $student_id, $subject_id, $mysql_date, $time);
                                $check_stmt->execute();
                                $check_result = $check_stmt->get_result();
                                
                                if ($check_result->num_rows > 0) {
                                    $error_message = "Attendance record already exists for this student, subject, date and time!";
                                } else {
                                    // Insert attendance record
                                    $insert_query = "INSERT INTO attendance (student_id, subject_id, date, time_slot, present) 
                                                VALUES (?, ?, ?, ?, 1)";
                                    $insert_stmt = $conn->prepare($insert_query);
                                    $insert_stmt->bind_param("iiss", $student_id, $subject_id, $mysql_date, $time);
                                    
                                    if ($insert_stmt->execute()) {
                                        $success_message = "Attendance recorded successfully!";
                                    } else {
                                        $error_message = "Error recording attendance: " . $insert_stmt->error;
                                    }
                                    
                                    $insert_stmt->close();
                                }
                                
                                $check_stmt->close();
                            } else {
                                $error_message = "Please fill all fields.";
                            }
                        }
                        
                        // If form is submitted for delete
                        if (isset($_POST['delete-btn'])) {
                            if (!empty($_POST['student_id']) && !empty($_POST['subject_id']) && !empty($_POST['m_date']) && !empty($_POST['m_time'])) {
                                // Set timezone
                                date_default_timezone_set('Africa/Nairobi');
                                
                                // Get form data
                                $student_id = $_POST["student_id"];
                                $subject_id = $_POST["subject_id"];
                                $date = $_POST["m_date"];
                                $time = $_POST["m_time"];
                                
                                // Format date for MySQL
                                $mysql_date = date("Y-m-d", strtotime($date));
                                
                                // Delete attendance record
                                $delete_query = "DELETE FROM attendance 
                                              WHERE student_id = ? AND subject_id = ? AND date = ? AND time_slot = ?";
                                $delete_stmt = $conn->prepare($delete_query);
                                $delete_stmt->bind_param("iiss", $student_id, $subject_id, $mysql_date, $time);
                                
                                if ($delete_stmt->execute()) {
                                    if ($delete_stmt->affected_rows > 0) {
                                        $success_message = "Attendance record deleted successfully!";
                                    } else {
                                        $error_message = "No matching attendance record found to delete.";
                                    }
                                } else {
                                    $error_message = "Error deleting attendance record: " . $delete_stmt->error;
                                }
                                
                                $delete_stmt->close();
                            } else {
                                $error_message = "Please select data to delete.";
                            }
                        }
                        ?>
                        
                        <?php if ($success_message): ?>
                            <div class="alert alert-success" role="alert">
                                <?php echo $success_message; ?>
                            </div>
                        <?php endif; ?>
                        
                        <?php if ($error_message): ?>
                            <div class="alert alert-danger" role="alert">
                                <?php echo $error_message; ?>
                            </div>
                        <?php endif; ?>

                        <form action="" method="POST">
                            <div class="row">
                                <div class="col-md-6">
                                    <div class="form-group student-selector">
                                        <label>Student</label>
                                        <select name="student_id" class="form-control">
                                            <option value="">-- Select Student --</option>
                                            <?php 
                                            if ($students_result && $students_result->num_rows > 0) {
                                                while($student = $students_result->fetch_assoc()) {
                                                    echo "<option value='" . $student["s_id"] . "'>" . 
                                                         $student["name"] . " (Roll: " . $student["roll_no"] . 
                                                         ", Enroll: " . $student["enroll_no"] . ")</option>";
                                                }
                                            }
                                            ?>
                                        </select>
                                    </div>
                                </div>

                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label>Subject</label>
                                        <select name="subject_id" class="form-control">
                                            <option value="">-- Select Subject --</option>
                                            <?php 
                                            if ($subjects_result && $subjects_result->num_rows > 0) {
                                                while($subject = $subjects_result->fetch_assoc()) {
                                                    if (isset($subject["subject_id"])) {
                                                        // Using subjects table
                                                        echo "<option value='" . $subject["subject_id"] . "'>" . 
                                                             $subject["subject_name"] . " (" . $subject["subject_code"] . ")</option>";
                                                    } else {
                                                        // Using present_table subjects
                                                        echo "<option value='" . $subject["subject"] . "'>" . 
                                                             $subject["subject"] . "</option>";
                                                    }
                                                }
                                            }
                                            ?>
                                        </select>
                                    </div>
                                </div>
                            </div>

                            <div class="row">
                                <div class="col-md-4">
                                    <div class="form-group">
                                        <label>Date</label>
                                        <input type="date" name="m_date" class="form-control">
                                    </div>
                                </div>

                                <div class="col-md-4">
                                    <div class="form-group">
                                        <label>Day</label>
                                        <select name="m_day" class="form-control">
                                            <option value="">-- Select Day --</option>
                                            <option>Monday</option>
                                            <option>Tuesday</option>
                                            <option>Wednesday</option>
                                            <option>Thursday</option>
                                            <option>Friday</option>
                                        </select>
                                    </div>
                                </div>

                                <div class="col-md-4">
                                    <div class="form-group">
                                        <label>Time</label>
                                        <select name="m_time" id="m_time" class="form-control">
                                            <option value="">-- Select Time Slot --</option>
                                            <option value="1">07:00am - 10:00am</option>
                                            <option value="2">10:00am - 1:00pm</option>
                                            <option value="3">01:00pm - 04:00pm</option>
                                            <option value="4">04:00pm - 07:00pm</option>
                                        </select>
                                    </div>
                                </div>
                            </div>

                            <div class="row mt-4">
                                <div class="col-md-12 text-center">
                                    <button type="submit" name="insert-btn" class="btn btn-primary mx-2" style="padding:.4rem 5rem; font-size:14px;">RECORD ATTENDANCE</button>
                                    <button type="submit" name="delete-btn" class="btn btn-danger mx-2" style="padding:.4rem 5rem; font-size:14px;">DELETE RECORD</button>
                                </div>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <?php
    // Close connection
    if (isset($conn)) {
        $conn->close();
    }
    ?>
</body>
</html>
