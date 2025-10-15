<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Faculty Dashboard</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@4.1.3/dist/css/bootstrap.min.css">
    <script src="https://code.jquery.com/jquery-3.3.1.slim.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/popper.js@1.14.3/dist/umd/popper.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@4.1.3/dist/js/bootstrap.min.js"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.3/css/all.min.css">
    <link rel="stylesheet" href="styles.css">
    
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
            width: 15rem;
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
        
        .stats-card {
            background: linear-gradient(45deg, #4CAF50, #8BC34A);
            color: white;
            border-radius: 8px;
            padding: 20px;
            margin-bottom: 20px;
        }
        
        .quick-actions .btn {
            margin: 5px;
            border-radius: 50px;
            font-weight: bold;
        }
        
        .list-group-item {
            border-left: none;
            border-right: none;
        }
        
        .list-group-item:first-child {
            border-top: none;
        }
        
        .stat-number {
            font-size: 2rem;
            font-weight: bold;
        }
        
        .form-control, .custom-select {
            border-radius: 20px;
            padding: 10px 15px;
        }
        
        .btn-action {
            border-radius: 20px;
            padding: 8px 25px;
            font-weight: 600;
        }
        
        /* Mobile responsiveness */
        @media (max-width: 768px) {
            .page-title, .main-content {
                margin-left: 1rem;
                width: 95%;
            }
        }
        
        /* Tooltip styling */
        .tooltip-inner {
            max-width: 200px;
            padding: 8px 10px;
            background-color: var(--bgcolor-2);
            font-size: 14px;
        }
        
        /* Modal styling */
        .modal-header {
            background-color: var(--bgcolor-2);
            color: white;
            border-radius: 8px 8px 0 0;
        }
        
        /* Toast notifications */
        .toast {
            position: fixed;
            top: 20px;
            right: 20px;
            z-index: 9999;
        }
    </style>
</head>

<body>
    <?php
    // Include navbar
    require 'partials/_navbar.php';
    
    // Database connection
    $conn = mysqli_connect("localhost", "root", "", "cpp");
    
    // Check connection
    if (!$conn) {
        die("Connection failed: " . mysqli_connect_error());
    }
    
    // Get total student count
    $student_query = "SELECT COUNT(*) as total_students FROM student";
    $student_result = mysqli_query($conn, $student_query);
    $student_count = mysqli_fetch_assoc($student_result)['total_students'];
    
    // Get today's date in the appropriate format
    $today = date('d-m-Y');
    
    // Get today's attendance percentage
    $attendance_query = "SELECT 
                            COUNT(DISTINCT id) as attended, 
                            (SELECT COUNT(*) FROM student) as total_students 
                         FROM present_table 
                         WHERE date = '$today'";
    $attendance_result = mysqli_query($conn, $attendance_query);
    $attendance_data = mysqli_fetch_assoc($attendance_result);
    
    $attendance_percentage = 0;
    if ($attendance_data['total_students'] > 0) {
        $attendance_percentage = round(($attendance_data['attended'] / $attendance_data['total_students']) * 100);
    }
    
    // Get faculty ID (in a real application, this would come from session)
    $faculty_id = 1001; // Sample faculty ID for demo
    
    // Get courses taught by the faculty (placeholder query - adapt to your actual schema)
    $faculty_courses_query = "SELECT subject_name, subject_code FROM subjects WHERE subject_id IN 
                             (SELECT subject_id FROM faculty_subjects WHERE f_id = $faculty_id)";
    
    // The above query might need adjustment based on your actual database structure
    // For demo purposes, using a direct query to get subjects from the database
    $faculty_courses_query = "SELECT DISTINCT subject FROM present_table ORDER BY subject LIMIT 4";
    $faculty_courses_result = mysqli_query($conn, $faculty_courses_query);
    ?>
    
    <div class="page-title">
        Faculty Dashboard
    </div>
    
    <div class="main-content">
        <!-- Statistics Summary Row -->
        <div class="row mb-4">
            <div class="col-md-3">
                <div class="stats-card">
                    <h3>Total Students</h3>
                    <div class="stat-number"><?php echo $student_count; ?></div>
                    <small>Across all classes</small>
                </div>
            </div>
            <div class="col-md-3">
                <div class="stats-card" style="background: linear-gradient(45deg, #2196F3, #03A9F4);">
                    <h3>Today's Attendance</h3>
                    <div class="stat-number"><?php echo $attendance_percentage; ?>%</div>
                    <small>Average rate</small>
                </div>
            </div>
            <div class="col-md-6">
                <div class="card">
                    <div class="card-body">
                        <h5 class="card-title">Quick Actions</h5>
                        <div class="quick-actions">
                            <a href="manage-std.php" class="btn btn-primary"><i class="fas fa-clipboard-list"></i> Take Attendance</a>
                            <a href="reports.php" class="btn btn-success"><i class="fas fa-file-export"></i> Export Report</a>
                            <button class="btn btn-warning" data-toggle="modal" data-target="#alertModal"><i class="fas fa-bell"></i> Send Alert</button>
                            <button class="btn btn-info" data-toggle="modal" data-target="#scheduleModal"><i class="fas fa-calendar-alt"></i> Schedule</button>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Main Features Row -->
        <div class="row">
            <div class="col-md-4">
                <div class="card">
                    <div class="card-header bg-primary text-white">
                        <h4><i class="fas fa-chalkboard-teacher"></i> My Classes</h4>
                    </div>
                    <div class="card-body p-0">
                        <ul class="list-group">
                            <?php
                            // Get class data with student counts
                            $class_counts = [];
                            
                            // Simple demo data if faculty_courses_result is empty
                            if (mysqli_num_rows($faculty_courses_result) == 0) {
                                $class_data = [
                                    ["BIT 2422", "Advanced Network Concepts", 18],
                                    ["BCS 2424", "Auditing of IS", 24],
                                    ["BCS 2421", "Computer Security & Cryptography", 32],
                                    ["BIT 2426", "Information Resources", 15]
                                ];
                                
                                foreach ($class_data as $class) {
                                    echo '<li class="list-group-item d-flex justify-content-between align-items-center">
                                        ' . $class[0] . ' ' . $class[1] . '
                                        <span class="badge badge-primary badge-pill">' . $class[2] . ' students</span>
                                    </li>';
                                }
                            } else {
                                // Display actual classes from database
                                $student_counts = [18, 24, 32, 15]; // Sample counts
                                $i = 0;
                                
                                while ($row = mysqli_fetch_assoc($faculty_courses_result)) {
                                    $count = isset($student_counts[$i]) ? $student_counts[$i] : rand(15, 35);
                                    echo '<li class="list-group-item d-flex justify-content-between align-items-center">
                                        ' . $row['subject'] . '
                                        <span class="badge badge-primary badge-pill">' . $count . ' students</span>
                                    </li>';
                                    $i++;
                                }
                            }
                            ?>
                        </ul>
                    </div>
                    <div class="card-footer">
                        <a href="classes.php" class="btn btn-sm btn-outline-primary btn-block">View All Classes</a>
                    </div>
                </div>
            </div>
            
            <div class="col-md-4">
                <div class="card">
                    <div class="card-header bg-success text-white">
                        <h4><i class="fas fa-user-check"></i> Take Attendance</h4>
                    </div>
                    <div class="card-body">
                        <form action="manage-std.php" method="GET">
                            <div class="form-group">
                                <label for="classSelect">Select Class:</label>
                                <select class="custom-select" id="classSelect" name="m_subject" required>
                                    <option value="">Choose class...</option>
                                    <?php
                                    // Query to get available subjects
                                    $subjects_query = "SELECT DISTINCT subject FROM present_table ORDER BY subject";
                                    $subjects_result = mysqli_query($conn, $subjects_query);
                                    
                                    if (mysqli_num_rows($subjects_result) > 0) {
                                        while ($subject = mysqli_fetch_assoc($subjects_result)) {
                                            echo '<option value="' . htmlspecialchars($subject['subject']) . '">' . 
                                                htmlspecialchars($subject['subject']) . '</option>';
                                        }
                                    } else {
                                        // Fallback options if no subjects in database
                                        $default_subjects = [
                                            "Advanced Network Concepts",
                                            "Auditing of Information Systems",
                                            "Computer Security & Cryptography",
                                            "Information Resource Management"
                                        ];
                                        
                                        foreach ($default_subjects as $subject) {
                                            echo '<option value="' . htmlspecialchars($subject) . '">' . 
                                                htmlspecialchars($subject) . '</option>';
                                        }
                                    }
                                    ?>
                                </select>
                            </div>
                            <div class="form-group">
                                <label>Session Date:</label>
                                <input type="date" name="m_date" class="form-control" value="<?php echo date('Y-m-d'); ?>" required>
                            </div>
                            <div class="form-group">
                                <label>Time Slot:</label>
                                <select class="custom-select" name="m_time" required>
                                    <option value="">Choose time slot...</option>
                                    <option value="1">7:00 - 10:00 AM</option>
                                    <option value="2">10:00 - 1:00 PM</option>
                                    <option value="3">1:00 - 4:00 PM</option>
                                    <option value="4">4:00 - 7:00 PM</option>
                                </select>
                            </div>
                            <button type="submit" class="btn btn-success btn-action btn-block">Start Session</button>
                        </form>
                    </div>
                </div>
            </div>
            
            <div class="col-md-4">
                <div class="card">
                    <div class="card-header bg-info text-white">
                        <h4><i class="fas fa-chart-bar"></i> Reports</h4>
                    </div>
                    <div class="card-body">
                        <form action="reports.php" method="GET">
                            <div class="form-group">
                                <label>Report Type:</label>
                                <select class="custom-select" name="report_type">
                                    <option value="daily">Daily Attendance</option>
                                    <option value="weekly">Weekly Summary</option>
                                    <option value="monthly">Monthly Analysis</option>
                                    <option value="student">Student Performance</option>
                                    <option value="class">Class Comparison</option>
                                </select>
                            </div>
                            <div class="form-group">
                                <label>Select Class:</label>
                                <select class="custom-select" name="r-subject">
                                    <option value="">All Classes</option>
                                    <?php
                                    // Reset the subjects result pointer
                                    mysqli_data_seek($subjects_result, 0);
                                    
                                    if (mysqli_num_rows($subjects_result) > 0) {
                                        while ($subject = mysqli_fetch_assoc($subjects_result)) {
                                            echo '<option value="' . htmlspecialchars($subject['subject']) . '">' . 
                                                htmlspecialchars($subject['subject']) . '</option>';
                                        }
                                    }
                                    ?>
                                </select>
                            </div>
                            <div class="form-group">
                                <label>Date:</label>
                                <input type="date" class="form-control" name="r-date">
                            </div>
                            <div class="form-group">
                                <label>Time Slot:</label>
                                <select name="r-time" class="form-control">
                                    <option value="">All Time Slots</option>
                                    <option value="1">7:00 - 10:00 AM</option>
                                    <option value="2">10:00 - 1:00 PM</option>
                                    <option value="3">1:00 - 4:00 PM</option>
                                    <option value="4">4:00 - 7:00 PM</option>
                                </select>
                            </div>
                            <div class="mt-3">
                                <button type="submit" class="btn btn-info btn-action">Generate Report</button>
                                <button type="button" class="btn btn-outline-info btn-action export-pdf">Export PDF</button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
        
        <!-- Recent Activity Section -->
        <div class="row mt-4">
            <div class="col-md-8">
                <div class="card">
                    <div class="card-header bg-dark text-white">
                        <h4><i class="fas fa-history"></i> Recent Activity</h4>
                    </div>
                    <div class="card-body p-0">
                        <ul class="list-group">
                            <?php
                            // Query to get recent activity
                            $activity_query = "SELECT DISTINCT subject, date, time FROM present_table ORDER BY date DESC LIMIT 4";
                            $activity_result = mysqli_query($conn, $activity_query);
                            
                            if (mysqli_num_rows($activity_result) > 0) {
                                while ($row = mysqli_fetch_assoc($activity_result)) {
                                    $time_slot = "";
                                    switch($row['time']) {
                                        case "1": $time_slot = "7:00 - 10:00 AM"; break;
                                        case "2": $time_slot = "10:00 - 1:00 PM"; break;
                                        case "3": $time_slot = "1:00 - 4:00 PM"; break;
                                        case "4": $time_slot = "4:00 - 7:00 PM"; break;
                                        default: $time_slot = "Unknown time";
                                    }
                                    
                                    echo '<li class="list-group-item d-flex justify-content-between align-items-center">
                                        <div>
                                            <strong>' . htmlspecialchars($row['subject']) . ' Attendance Recorded</strong>
                                            <div class="small text-muted">' . htmlspecialchars($row['date']) . ', ' . $time_slot . '</div>
                                        </div>
                                        <span class="badge badge-success">Complete</span>
                                    </li>';
                                }
                            } else {
                                echo '<li class="list-group-item">No recent activity found</li>';
                            }
                            ?>
                        </ul>
                    </div>
                    <div class="card-footer">
                        <a href="activity.php" class="btn btn-sm btn-outline-dark btn-block">View All Activity</a>
                    </div>
                </div>
            </div>
            
            <div class="col-md-4">
                <div class="card">
                    <div class="card-header bg-warning text-dark">
                        <h4><i class="fas fa-exclamation-triangle"></i> Alerts</h4>
                    </div>
                    <div class="card-body p-0">
                        <ul class="list-group" id="alertsList">
                            <li class="list-group-item d-flex justify-content-between align-items-center text-danger">
                                <div>
                                    <strong>5 students below 75% attendance</strong>
                                    <div class="small">BCS 2421 Computer Security</div>
                                </div>
                                <button class="btn btn-sm btn-outline-danger view-alert" data-toggle="modal" data-target="#viewAlertModal" data-alert-id="1">View</button>
                            </li>
                            <li class="list-group-item d-flex justify-content-between align-items-center">
                                <div>
                                    <strong>Low class attendance rate (68%)</strong>
                                    <div class="small">BIT 2426 Information Resources</div>
                                </div>
                                <button class="btn btn-sm btn-outline-warning view-alert" data-toggle="modal" data-target="#viewAlertModal" data-alert-id="2">View</button>
                            </li>
                            <li class="list-group-item d-flex justify-content-between align-items-center">
                                <div>
                                    <strong>Upcoming class not scheduled</strong>
                                    <div class="small">BCS 2424 Auditing of IS</div>
                                </div>
                                <button class="btn btn-sm btn-outline-warning" data-toggle="modal" data-target="#scheduleModal">Schedule</button>
                            </li>
                        </ul>
                    </div>
                </div>
            </div>
        </div>
    </div>
    
    <!-- Alert Modal -->
    <div class="modal fade" id="alertModal" tabindex="-1" role="dialog" aria-labelledby="alertModalLabel" aria-hidden="true">
        <div class="modal-dialog" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="alertModalLabel">Send Alert</h5>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <div class="modal-body">
                    <form id="alertForm">
                        <div class="form-group">
                            <label>Recipients</label>
                            <select class="custom-select" name="alert_recipients">
                                <option value="all">All Students</option>
                                <option value="class">Specific Class</option>
                                <option value="individual">Individual Students</option>
                            </select>
                        </div>
                        <div class="form-group" id="classSelectGroup" style="display: none;">
                            <label>Select Class</label>
                            <select class="custom-select" name="alert_class">
                                <option value="">Choose class...</option>
                                <?php
                                // Reset the subjects result pointer
                                mysqli_data_seek($subjects_result, 0);
                                
                                if (mysqli_num_rows($subjects_result) > 0) {
                                    while ($subject = mysqli_fetch_assoc($subjects_result)) {
                                        echo '<option value="' . htmlspecialchars($subject['subject']) . '">' . 
                                            htmlspecialchars($subject['subject']) . '</option>';
                                    }
                                }
                                ?>
                            </select>
                        </div>
                        <div class="form-group">
                            <label>Alert Type</label>
                            <select class="custom-select" name="alert_type">
                                <option value="attendance">Attendance Warning</option>
                                <option value="reminder">Class Reminder</option>
                                <option value="cancellation">Class Cancellation</option>
                                <option value="custom">Custom Message</option>
                            </select>
                        </div>
                        <div class="form-group">
                            <label>Message</label>
                            <textarea class="form-control" name="alert_message" rows="4"></textarea>
                        </div>
                        <div class="form-group">
                            <label>Priority</label>
                            <select class="custom-select" name="alert_priority">
                                <option value="normal">Normal</option>
                                <option value="high">High</option>
                                <option value="urgent">Urgent</option>
                            </select>
                        </div>
                    </form>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">Cancel</button>
                    <button type="button" class="btn btn-primary" id="sendAlertBtn">Send Alert</button>
                </div>
            </div>
        </div>
    </div>
    
    <!-- Schedule Modal -->
    <div class="modal fade" id="scheduleModal" tabindex="-1" role="dialog" aria-labelledby="scheduleModalLabel" aria-hidden="true">
        <div class="modal-dialog" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="scheduleModalLabel">Schedule Class</h5>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <div class="modal-body">
                    <form id="scheduleForm">
                        <div class="form-group">
                            <label>Subject</label>
                            <select class="custom-select" name="schedule_subject" required>
                                <option value="">Choose subject...</option>
                                <?php
                                // Reset the subjects result pointer
                                mysqli_data_seek($subjects_result, 0);
                                
                                if (mysqli_num_rows($subjects_result) > 0) {
                                    while ($subject = mysqli_fetch_assoc($subjects_result)) {
                                        echo '<option value="' . htmlspecialchars($subject['subject']) . '">' . 
                                            htmlspecialchars($subject['subject']) . '</option>';
                                    }
                                }
                                ?>
                            </select>
                        </div>
                        <div class="form-group">
                            <label>Date</label>
                            <input type="date" class="form-control" name="schedule_date" required>
                        </div>
                        <div class="form-group">
                            <label>Time Slot</label>
                            <select class="custom-select" name="schedule_time" required>
                                <option value="">Choose time slot...</option>
                                <option value="1">7:00 - 10:00 AM</option>
                                <option value="2">10:00 - 1:00 PM</option>
                                <option value="3">1:00 - 4:00 PM</option>
                                <option value="4">4:00 - 7:00 PM</option>
                            </select>
                        </div>
                        <div class="form-group">
                            <label>Location</label>
                            <input type="text" class="form-control" name="schedule_location" placeholder="Building and Room Number">
                        </div>
                        <div class="form-group">
                            <label>Notes</label>
                            <textarea class="form-control" name="schedule_notes" placeholder="Any additional information"></textarea>
                        </div>
                        <div class="form-group">
                            <div class="custom-control custom-checkbox">
                                <input type="checkbox" class="custom-control-input" id="notifyStudents" name="notify_students">
                                <label class="custom-control-label" for="notifyStudents">Notify students of this schedule</label>
                            </div>
                        </div>
                    </form>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">Cancel</button>
                    <button type="button" class="btn btn-primary" id="saveScheduleBtn">Save Schedule</button>
                </div>
            </div>
        </div>
    </div>
    
    <!-- View Alert Modal -->
    <div class="modal fade" id="viewAlertModal" tabindex="-1" role="dialog" aria-labelledby="viewAlertModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-lg" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="viewAlertModalLabel">Alert Details</h5>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <div class="modal-body">
                    <div id="alertDetail"></div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">Close</button>
                    <button type="button" class="btn btn-primary" id="resolveAlertBtn">Mark as Resolved</button>
                </div>
            </div>
        </div>
    </div>
    
    <!-- Toast notification for actions -->
    <div class="toast" role="alert" aria-live="assertive" aria-atomic="true" data-delay="3000">
        <div class="toast-header">
            <strong class="mr-auto" id="toastTitle">Notification</strong>
            <button type="button" class="ml-2 mb-1 close" data-dismiss="toast" aria-label="Close">
                <span aria-hidden="true">&times;</span>
            </button>
        </div>
        <div class="toast-body" id="toastMessage">
            Action completed successfully.
        </div>
    </div>
    
    <script>
        $(document).ready(function(){
            // Initialize tooltips
            $('[data-toggle="tooltip"]').tooltip();
            
            // Handle recipients selection for alerts
            $('select[name="alert_recipients"]').change(function() {
                if ($(this).val() === 'class') {
                    $('#classSelectGroup').show();
                } else {
                    $('#classSelectGroup').hide();
                }
            });
            
            // View alert details
            $('.view-alert').click(function() {
                var alertId = $(this).data('alert-id');
                var alertContent = '';
                
                // For demo purposes, hardcoded sample data
                if (alertId === 1) {
                    alertContent = `
                        <h4 class="text-danger">Students Below 75% Attendance</h4>
                        <p class="mb-3">Course: BCS 2421 Computer Security</p>
                        <table class="table table-striped">
                            <thead>
                                <tr>
                                    <th>Student ID</th>
                                    <th>Name</th>
                                    <th>Current Attendance</th>
                                    <th>Last Present</th>
                                </tr>
                            </thead>
                            <tbody>
                                <tr>
                                    <td>3702</td>
                                    <td>Rodrick Rotich</td>
                                    <td>65%</td>
                                    <td>03-04-2025</td>
                                </tr>
                                <tr>
                                    <td>3705</td>
                                    <td>Matthew Mark</td>
                                    <td>60%</td>
                                    <td>01-04-2025</td>
                                </tr>
                                <tr>
                                    <td>3707</td>
                                    <td>Arnold Odhiambo</td>
                                    <td>70%</td>
                                    <td>02-04-2025</td>
                                </tr>
                                <tr>
                                    <td>3709</td>
                                    <td>Jeff Hardy</td>
                                    <td>72%</td>
                                    <td>28-03-2025