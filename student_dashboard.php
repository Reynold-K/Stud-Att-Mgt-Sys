<?php
// Start session
session_start();

// Check if student is logged in
if (!isset($_SESSION['student_id'])) {
    // Redirect to login page if not logged in
    header("Location: student_login.php");
    exit();
}

// Include database connection
require 'config.php';

// Get student information from session
$student_id = $_SESSION['student_id'];
$student_name = $_SESSION['student_name'];
$enrollment = $_SESSION['enrollment'];

// Get student information from database
$query = "SELECT * FROM student WHERE s_id = '$student_id'";
$result = mysqli_query($conn, $query);
if (!$result) {
    die("Error fetching student info: " . mysqli_error($conn));
}
$student = mysqli_fetch_assoc($result);
?>

<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Student Dashboard</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@4.1.3/dist/css/bootstrap.min.css">
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
            width: 17rem;
        }

        .student-info {
            width: 80%;
            margin: 3rem auto;
            padding: 2rem;
            border: 2px solid #ccc;
            border-radius: 15px;
        }

        .info-card {
            background-color: #f9f9f9;
            padding: 2rem;
            border-radius: 10px;
            margin-bottom: 2rem;
        }

        .attendance-summary {
            width: 80%;
            margin: 3rem auto;
            padding: 2rem;
            border: 2px solid #ccc;
            border-radius: 15px;
        }

        .time-table-head {
            background-color: var(--bgcolor-2);
            color: white;
        }

        .logout-btn {
            background-color: #0f2557;
            color: white;
            border: none;
            font-size: 1.5rem;
            padding: 1rem 2rem;
            border-radius: 5px;
            cursor: pointer;
            margin-top: 2rem;
        }

        .quick-links {
            width: 80%;
            margin: 3rem auto;
            text-align: center;
        }

        .quick-links a {
            margin: 0 1rem;
            display: inline-block;
            padding: 1rem 2rem;
            background-color: var(--bgcolor-2);
            color: white;
            text-decoration: none;
            border-radius: 5px;
            font-size: 1.5rem;
        }
    </style>
</head>

<body>
    <nav class="navbar navbar-expand-lg navbar-dark bg-dark">
        <a class="navbar-brand" href="student_dashboard.php">MMU Biometric Attendance System</a>
        <button class="navbar-toggler" type="button" data-toggle="collapse" data-target="#navbarNav">
            <span class="navbar-toggler-icon"></span>
        </button>
        <div class="collapse navbar-collapse" id="navbarNav">
            <ul class="navbar-nav ml-auto">
                <li class="nav-item active">
                    <a class="nav-link" href="student_dashboard.php">Dashboard</a>
                </li>
                <li class="nav-item">
                    <a class="nav-link" href="student_attendance.php">My Attendance</a>
                </li>
                <li class="nav-item">
                    <a class="nav-link" href="student_logout.php">Logout</a>
                </li>
            </ul>
        </div>
    </nav>

    <div class="page-title">
        Student Dashboard
    </div>

    <div class="student-info">
        <div class="info-card">
            <h2>Welcome, <?php echo $student_name; ?></h2>
            <hr>
            <div class="row">
                <div class="col-md-6">
                    <p><strong>Student ID:</strong> <?php echo $student_id; ?></p>
                    <p><strong>Roll Number:</strong> <?php echo $student['roll_no']; ?></p>
                </div>
                <div class="col-md-6">
                    <p><strong>Enrollment Number:</strong> <?php echo $enrollment; ?></p>
                    <p><strong>Contact:</strong> <?php echo $student['contact']; ?></p>
                </div>
            </div>
        </div>
    </div>

    <div class="attendance-summary">
        <h3>Attendance Summary</h3>
        <table class="table table-bordered table-hover mt-4">
            <thead class="time-table-head">
                <tr>
                    <th>Subject</th>
                    <th>Faculty</th>
                    <th>Total Classes</th>
                    <th>Classes Attended</th>
                    <th>Attendance Percentage</th>
                </tr>
            </thead>
            <tbody>
                <?php
                // Get unique subjects for this student from the attendance table
                $subjects_query = "SELECT DISTINCT a.subject_id, s.subject_name, f.name AS faculty_name 
                                  FROM attendance a 
                                  JOIN subjects s ON a.subject_id = s.subject_id 
                                  LEFT JOIN faculty f ON s.faculty_id = f.f_id 
                                  WHERE a.student_id = '$student_id'";
                $subjects_result = mysqli_query($conn, $subjects_query);
                if (!$subjects_result) {
                    die("Error fetching subjects: " . mysqli_error($conn));
                }

                // Assume total classes per subject for the semester (adjust as needed)
                $total_classes_per_subject = 30; // Placeholder; replace with actual data if available

                while ($subject = mysqli_fetch_assoc($subjects_result)) {
                    $subject_id = $subject['subject_id'];
                    $subject_name = $subject['subject_name'];
                    $faculty_name = $subject['faculty_name'] ?? 'Not Assigned';

                    // Count classes attended by this student for this subject
                    $attended_query = "SELECT COUNT(*) as attended 
                                      FROM attendance 
                                      WHERE student_id = '$student_id' 
                                      AND subject_id = '$subject_id' 
                                      AND status = 'present'";
                    $attended_result = mysqli_query($conn, $attended_query);
                    if (!$attended_result) {
                        die("Error fetching attended classes: " . mysqli_error($conn));
                    }
                    $attended_data = mysqli_fetch_assoc($attended_result);
                    $classes_attended = $attended_data['attended'];

                    // Calculate attendance percentage
                    $attendance_percentage = ($total_classes_per_subject > 0) 
                        ? ($classes_attended / $total_classes_per_subject) * 100 
                        : 0;

                    echo "<tr>";
                    echo "<td>$subject_name</td>";
                    echo "<td>$faculty_name</td>";
                    echo "<td>$total_classes_per_subject</td>";
                    echo "<td>$classes_attended</td>";
                    echo "<td>" . number_format($attendance_percentage, 2) . "%</td>";
                    echo "</tr>";
                }

                // If no subjects found
                if (mysqli_num_rows($subjects_result) == 0) {
                    echo "<tr><td colspan='5'>No attendance records found.</td></tr>";
                }
                ?>
            </tbody>
        </table>
    </div>

    <div class="quick-links">
        <a href="student_attendance.php">View Detailed Attendance</a>
        <a href="student_logout.php">Logout</a>
    </div>

    <script src="https://code.jquery.com/jquery-3.3.1.slim.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/popper.js@1.14.3/dist/umd/popper.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@4.1.3/dist/js/bootstrap.min.js"></script>
</body>
</html>