<?php
// Start session to access lecturer information
session_start();

// Check if lecturer is logged in
if (!isset($_SESSION['faculty_id'])) {
    // Redirect to login page if not logged in
    header("Location: login.php");
    exit();
}

// Database connection
$servername = "localhost";
$username = "root";
$password = "";
$dbname = "cpp";

// Create connection
$conn = new mysqli($servername, $username, $password, $dbname);

// Check connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Get lecturer information
$faculty_id = $_SESSION['faculty_id'];
$sql = "SELECT * FROM faculty WHERE f_id = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $faculty_id);
$stmt->execute();
$result = $stmt->get_result();
$lecturer = $result->fetch_assoc();

// Get lecturer's courses
$sql_courses = "SELECT DISTINCT subject FROM present_table WHERE id IN 
                (SELECT DISTINCT id FROM present_table WHERE subject IN 
                    (SELECT DISTINCT subject FROM present_table))";
$result_courses = $conn->query($sql_courses);

// Count students
$sql_students = "SELECT COUNT(DISTINCT id) as total_students FROM present_table";
$result_students = $conn->query($sql_students);
$row_students = $result_students->fetch_assoc();
$total_students = $row_students['total_students'];

// Count classes today
$today = date("d-m-Y");
$sql_today = "SELECT COUNT(DISTINCT subject) as today_classes FROM present_table WHERE date = ?";
$stmt_today = $conn->prepare($sql_today);
$stmt_today->bind_param("s", $today);
$stmt_today->execute();
$result_today = $stmt_today->get_result();
$row_today = $result_today->fetch_assoc();
$today_classes = $row_today['today_classes'];

// Get recent attendance records
$sql_recent = "SELECT p.*, s.name as student_name 
               FROM present_table p 
               JOIN student s ON p.id = s.s_id 
               ORDER BY p.record_id DESC LIMIT 5";
$result_recent = $conn->query($sql_recent);
?>

<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Lecturer Profile: <?php echo $lecturer['name']; ?></title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@4.1.3/dist/css/bootstrap.min.css">
    <script src="https://code.jquery.com/jquery-3.3.1.slim.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/popper.js@1.14.3/dist/umd/popper.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@4.1.3/dist/js/bootstrap.min.js"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.3/css/all.min.css">
    
    <style type="text/css">
        :root {
            --bgcolor-1: #150734;
            --bgcolor-2: #0F2557;
            --maincolor: #000000;
        }
        
        .page-title{
            margin-left: 27rem;
            margin-top: 6rem;
            font-size: 2.2rem;
            color: var(--bgcolor-1);
            border-bottom: 2px solid var(--bgcolor-1);
            width: auto;
            display: inline-block;
        }
        
        /* Main content positioning fix */
        .main-content {
            margin-left: 26rem;
            padding: 20px;
            width: calc(100% - 26rem);
        }
        
        /* Card styling */
        .card {
            margin-bottom: 20px;
            box-shadow: 0 4px 8px rgba(0,0,0,0.1);
            transition: all 0.3s ease;
            border-radius: 8px;
        }
        
        .card:hover {
            transform: translateY(-5px);
            box-shadow: 0 8px 16px rgba(0,0,0,0.2);
        }
        
        .card-header {
            font-weight: bold;
            border-radius: 8px 8px 0 0 !important;
        }
        
        .profile-header {
            background: linear-gradient(45deg, var(--bgcolor-1), var(--bgcolor-2));
            color: white;
            padding: 30px;
            border-radius: 8px;
            margin-bottom: 20px;
        }
        
        .profile-img {
            width: 120px;
            height: 120px;
            border-radius: 50%;
            background-color: white;
            padding: 5px;
            margin-right: 30px;
        }
        
        .profile-details h2 {
            font-size: 24px;
            font-weight: bold;
            margin-bottom: 5px;
        }
        
        .profile-details p {
            font-size: 16px;
            margin-bottom: 0;
            opacity: 0.9;
        }
        
        .stats-card {
            background: linear-gradient(45deg, #4CAF50, #8BC34A);
            color: white;
            border-radius: 8px;
            padding: 20px;
            margin-bottom: 20px;
        }
        
        .stat-number {
            font-size: 2rem;
            font-weight: bold;
        }
        
        .quick-actions .btn {
            margin: 5px;
            border-radius: 50px;
            font-weight: bold;
        }
    </style>
</head>

<body>
    <?php require 'partials/_navbar.php'?>
    
    <div class="page-title">
        Lecturer Profile
    </div>
    
    <div class="main-content">
        <!-- Profile Header -->
        <div class="profile-header d-flex align-items-center">
            <div class="profile-img d-flex align-items-center justify-content-center">
                <i class="fas fa-user-tie fa-4x text-primary"></i>
            </div>
            <div class="profile-details">
                <h2><?php echo $lecturer['name']; ?></h2>
                <p><i class="fas fa-envelope mr-2"></i><?php echo $lecturer['email']; ?></p>
                <p><i class="fas fa-building mr-2"></i><?php echo $lecturer['department']; ?></p>
                <p><i class="fas fa-id-badge mr-2"></i>Faculty ID: <?php echo $lecturer['f_id']; ?></p>
            </div>
        </div>
        
        <!-- Statistics Summary Row -->
        <div class="row mb-4">
            <div class="col-md-3">
                <div class="stats-card">
                    <h3>Total Students</h3>
                    <div class="stat-number"><?php echo $total_students; ?></div>
                    <small>Across all classes</small>
                </div>
            </div>
            <div class="col-md-3">
                <div class="stats-card" style="background: linear-gradient(45deg, #2196F3, #03A9F4);">
                    <h3>Today's Classes</h3>
                    <div class="stat-number"><?php echo $today_classes; ?></div>
                    <small><?php echo date("d M Y"); ?></small>
                </div>
            </div>
            <div class="col-md-6">
                <div class="card">
                    <div class="card-body">
                        <h5 class="card-title">Quick Actions</h5>
                        <div class="quick-actions">
                            <a href="take_attendance.php" class="btn btn-primary"><i class="fas fa-clipboard-list"></i> Take Attendance</a>
                            <a href="reports.php" class="btn btn-success"><i class="fas fa-file-export"></i> Generate Report</a>
                            <a href="manage-std.php" class="btn btn-warning"><i class="fas fa-user-edit"></i> Manage Students</a>
                            <a href="class-2.php" class="btn btn-info"><i class="fas fa-calendar-alt"></i> Schedule</a>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Main Content Row -->
        <div class="row">
            <div class="col-md-6">
                <div class="card">
                    <div class="card-header bg-primary text-white">
                        <h4><i class="fas fa-chalkboard-teacher"></i> My Courses</h4>
                    </div>
                    <div class="card-body p-0">
                        <ul class="list-group">
                            <?php 
                            if ($result_courses->num_rows > 0) {
                                while($course = $result_courses->fetch_assoc()) {
                                    // Count students in this course
                                    $subject = $course['subject'];
                                    $sql_count = "SELECT COUNT(DISTINCT id) as student_count FROM present_table WHERE subject = ?";
                                    $stmt_count = $conn->prepare($sql_count);
                                    $stmt_count->bind_param("s", $subject);
                                    $stmt_count->execute();
                                    $result_count = $stmt_count->get_result();
                                    $row_count = $result_count->fetch_assoc();
                                    $student_count = $row_count['student_count'];
                                    ?>
                                    <li class="list-group-item d-flex justify-content-between align-items-center">
                                        <?php echo $course['subject']; ?>
                                        <span class="badge badge-primary badge-pill"><?php echo $student_count; ?> students</span>
                                    </li>
                                    <?php
                                }
                            } else {
                                echo '<li class="list-group-item">No courses found</li>';
                            }
                            ?>
                        </ul>
                    </div>
                    <div class="card-footer">
                        <a href="manage-std.php" class="btn btn-sm btn-outline-primary btn-block">Manage Courses</a>
                    </div>
                </div>
            </div>
            
            <div class="col-md-6">
                <div class="card">
                    <div class="card-header bg-info text-white">
                        <h4><i class="fas fa-calendar-week"></i> Upcoming Schedule</h4>
                    </div>
                    <div class="card-body p-0">
                        <ul class="list-group">
                            <?php
                            // Get upcoming classes (for the next 7 days)
                            $today = date("d-m-Y");
                            $next_week = date("d-m-Y", strtotime("+7 days"));
                            
                            $sql_schedule = "SELECT * FROM present_table 
                                             WHERE date BETWEEN ? AND ? 
                                             GROUP BY subject, date, time 
                                             ORDER BY date, time 
                                             LIMIT 5";
                            $stmt_schedule = $conn->prepare($sql_schedule);
                            $stmt_schedule->bind_param("ss", $today, $next_week);
                            $stmt_schedule->execute();
                            $result_schedule = $stmt_schedule->get_result();
                            
                            if ($result_schedule->num_rows > 0) {
                                while($schedule = $result_schedule->fetch_assoc()) {
                                    // Convert time code to readable format
                                    $time_slots = [
                                        "1" => "07:00 - 10:00 AM",
                                        "2" => "10:00 - 01:00 PM",
                                        "3" => "01:00 - 04:00 PM",
                                        "4" => "04:00 - 07:00 PM"
                                    ];
                                    $time_display = isset($time_slots[$schedule['time']]) ? $time_slots[$schedule['time']] : $schedule['time'];
                                    
                                    // Format date
                                    $date_obj = DateTime::createFromFormat('d-m-Y', $schedule['date']);
                                    $formatted_date = $date_obj ? $date_obj->format('D, M d, Y') : $schedule['date'];
                                    ?>
                                    <li class="list-group-item">
                                        <div class="d-flex justify-content-between align-items-center">
                                            <div>
                                                <strong><?php echo $schedule['subject']; ?></strong>
                                                <div class="small text-muted"><?php echo $formatted_date; ?>, <?php echo $time_display; ?></div>
                                            </div>
                                            <a href="take_attendance.php?subject=<?php echo urlencode($schedule['subject']); ?>&date=<?php echo urlencode($schedule['date']); ?>&time=<?php echo urlencode($schedule['time']); ?>" class="btn btn-sm btn-outline-info">Take Attendance</a>
                                        </div>
                                    </li>
                                    <?php
                                }
                            } else {
                                echo '<li class="list-group-item">No upcoming classes scheduled</li>';
                            }
                            ?>
                        </ul>
                    </div>
                    <div class="card-footer">
                        <a href="schedule.php" class="btn btn-sm btn-outline-info btn-block">View Full Schedule</a>
                    </div>
                </div>
            </div>
        </div>
        
        <!-- Recent Activity Section -->
        <div class="row mt-4">
            <div class="col-md-12">
                <div class="card">
                    <div class="card-header bg-dark text-white">
                        <h4><i class="fas fa-history"></i> Recent Attendance Records</h4>
                    </div>
                    <div class="card-body p-0">
                        <div class="table-responsive">
                            <table class="table table-hover">
                                <thead>
                                    <tr>
                                        <th>ID</th>
                                        <th>Student</th>
                                        <th>Subject</th>
                                        <th>Date</th>
                                        <th>Day</th>
                                        <th>Time Slot</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php
                                    if ($result_recent->num_rows > 0) {
                                        while($record = $result_recent->fetch_assoc()) {
                                            // Convert time code to readable format
                                            $time_slots = [
                                                "1" => "07:00 - 10:00 AM",
                                                "2" => "10:00 - 01:00 PM",
                                                "3" => "01:00 - 04:00 PM",
                                                "4" => "04:00 - 07:00 PM"
                                            ];
                                            $time_display = isset($time_slots[$record['time']]) ? $time_slots[$record['time']] : $record['time'];
                                            ?>
                                            <tr>
                                                <td><?php echo $record['id']; ?></td>
                                                <td><?php echo isset($record['student_name']) ? $record['student_name'] : "N/A"; ?></td>
                                                <td><?php echo $record['subject']; ?></td>
                                                <td><?php echo $record['date']; ?></td>
                                                <td><?php echo $record['day']; ?></td>
                                                <td><?php echo $time_display; ?></td>
                                            </tr>
                                            <?php
                                        }
                                    } else {
                                        echo '<tr><td colspan="6" class="text-center">No recent records found</td></tr>';
                                    }
                                    ?>
                                </tbody>
                            </table>
                        </div>
                    </div>
                    <div class="card-footer">
                        <a href="reports.php" class="btn btn-sm btn-outline-dark btn-block">View All Records</a>
                    </div>
                </div>
            </div>
        </div>
    </div>
    
    <script>
        $(document).ready(function(){
            // Initialize tooltips
            $('[data-toggle="tooltip"]').tooltip();
        });
    </script>
</body>
</html>

<?php
// Close connections
$stmt->close();
if (isset($stmt_today)) $stmt_today->close();
if (isset($stmt_schedule)) $stmt_schedule->close();
$conn->close();
?>
