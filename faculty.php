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
    </style>
</head>

<body>
    <?php require 'partials/_navbar.php'?>
    
    <div class="page-title">
        Faculty Dashboard
    </div>
    
    <div class="main-content">
        <!-- Statistics Summary Row -->
        <div class="row mb-4">
            <div class="col-md-3">
                <div class="stats-card">
                    <h3>Total Students</h3>
                    <div class="stat-number">124</div>
                    <small>Across all classes</small>
                </div>
            </div>
            <div class="col-md-3">
                <div class="stats-card" style="background: linear-gradient(45deg, #2196F3, #03A9F4);">
                    <h3>Today's Attendance</h3>
                    <div class="stat-number">87%</div>
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
                            <button class="btn btn-warning"><i class="fas fa-bell"></i> Send Alert</button>
                            <button class="btn btn-info"><i class="fas fa-calendar-alt"></i> Schedule</button>
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
                            <li class="list-group-item d-flex justify-content-between align-items-center">
                                BIT 2422 Advanced Network Concepts
                                <span class="badge badge-primary badge-pill">18 students</span>
                            </li>
                            <li class="list-group-item d-flex justify-content-between align-items-center">
                                BCS 2424 Auditing of IS
                                <span class="badge badge-primary badge-pill">24 students</span>
                            </li>
                            <li class="list-group-item d-flex justify-content-between align-items-center">
                                BCS 2421 Computer Security & Cryptography
                                <span class="badge badge-primary badge-pill">32 students</span>
                            </li>
                            <li class="list-group-item d-flex justify-content-between align-items-center">
                                BIT 2426 Information Resources
                                <span class="badge badge-primary badge-pill">15 students</span>
                            </li>
                        </ul>
                    </div>
                    <div class="card-footer">
                        <a href="#" class="btn btn-sm btn-outline-primary btn-block">View All Classes</a>
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
                                <select class="custom-select" id="classSelect" name="m_subject">
                                    <option selected>Choose class...</option>
                                    <option value="Advanced Network Concepts">BIT 2422 Advanced Network Concepts</option>
                                    <option value="Auditing of Information Systems">BCS 2424 Auditing of IS</option>
                                    <option value="Computer Security & Cryptography">BCS 2421 Computer Security & Cryptography</option>
                                    <option value="Information Resource Management">BIT 2426 Information Resources</option>
                                </select>
                            </div>
                            <div class="form-group">
                                <label>Session Date:</label>
                                <input type="date" name="m_date" class="form-control" value="<?php echo date('Y-m-d'); ?>">
                            </div>
                            <div class="form-group">
                                <label>Time Slot:</label>
                                <select class="custom-select" name="m_time">
                                    <option selected>Choose time slot...</option>
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
                                <select class="custom-select">
                                    <option selected>Select report type...</option>
                                    <option value="1">Daily Attendance</option>
                                    <option value="2">Weekly Summary</option>
                                    <option value="3">Monthly Analysis</option>
                                    <option value="4">Student Performance</option>
                                    <option value="5">Class Comparison</option>
                                </select>
                            </div>
                            <div class="form-group">
                                <label>Select Class:</label>
                                <select class="custom-select" name="r-subject">
                                    <option selected>All Classes</option>
                                    <option value="Advanced Network Concepts">Advanced Network Concepts</option>
									<option value="Auditiong of Information Systems">Auditing of Information Systems</option>
									<option value="Computer Security & Cryptography">Computer Security & Cryptography</option>
									<option value="Data Mining & Business Intelligence">Data Mining & Business Intelligence</option>
									<option value="Entrepreneurship & Innovation">Entrepreneurship & Innovation</option>
									<option value="Human Resource Management">Human Resource Management</option>
									<option value="Information Resource Management">Information Resource Management</option>
									<option value="Systems Project">Systems Project</option>
                                </select>
                            </div>
                            <div class="form-group">
                                <label>Date Range:</label>
                                <div class="row">
                                    <div class="col-md-6">
                                        <input type="date" class="form-control" name="r-date" placeholder="From">
                                    </div>
                                    <div class="col-md-6">
                                        <select name="r-time" class="form-control">
                                            <option value="1">Morning</option>
                                            <option value="2">Afternoon</option>
                                            <option value="3">Evening</option>
                                        </select>
                                    </div>
                                </div>
                            </div>
                            <div class="mt-3">
                                <button type="submit" class="btn btn-info btn-action mr-2">Generate</button>
                                <button type="button" class="btn btn-outline-info btn-action">Export PDF</button>
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
                            // Connect to the database
                            $conn = mysqli_connect("localhost", "root", "", "cpp");
                            
                            // Check connection
                            if (!$conn) {
                                die("Connection failed: " . mysqli_connect_error());
                            }
                            
                            // Query to get recent activity
                            $query = "SELECT DISTINCT subject, date, time FROM present_table ORDER BY date DESC LIMIT 4";
                            $result = mysqli_query($conn, $query);
                            
                            if (mysqli_num_rows($result) > 0) {
                                while ($row = mysqli_fetch_assoc($result)) {
                                    $time_slot = "";
                                    switch($row['time']) {
                                        case "1": $time_slot = "7:00 - 10:00 AM"; break;
                                        case "2": $time_slot = "10:00 - 1:00 PM"; break;
                                        case "3": $time_slot = "1:00 - 4:00 PM"; break;
                                        case "4": $time_slot = "4:00 - 7:00 PM"; break;
                                    }
                                    
                                    echo '<li class="list-group-item d-flex justify-content-between align-items-center">
                                        <div>
                                            <strong>' . $row['subject'] . ' Attendance Recorded</strong>
                                            <div class="small text-muted">' . $row['date'] . ', ' . $time_slot . '</div>
                                        </div>
                                        <span class="badge badge-success">Complete</span>
                                    </li>';
                                }
                            } else {
                                echo '<li class="list-group-item">No recent activity found</li>';
                            }
                            
                            mysqli_close($conn);
                            ?>
                        </ul>
                    </div>
                    <div class="card-footer">
                        <a href="reports.php" class="btn btn-sm btn-outline-dark btn-block">View All Activity</a>
                    </div>
                </div>
            </div>
            
            <div class="col-md-4">
                <div class="card">
                    <div class="card-header bg-warning text-dark">
                        <h4><i class="fas fa-exclamation-triangle"></i> Alerts</h4>
                    </div>
                    <div class="card-body p-0">
                        <ul class="list-group">
                            <li class="list-group-item d-flex justify-content-between align-items-center text-danger">
                                <div>
                                    <strong>5 students below 75% attendance</strong>
                                    <div class="small">BCS 2421 Computer Security</div>
                                </div>
                                <a href="#" class="btn btn-sm btn-outline-danger">View</a>
                            </li>
                            <li class="list-group-item d-flex justify-content-between align-items-center">
                                <div>
                                    <strong>Low class attendance rate (68%)</strong>
                                    <div class="small">BIT 2426 Information Resources</div>
                                </div>
                                <a href="#" class="btn btn-sm btn-outline-warning">View</a>
                            </li>
                            <li class="list-group-item d-flex justify-content-between align-items-center">
                                <div>
                                    <strong>Upcoming class not scheduled</strong>
                                    <div class="small">BCS 2424 Auditing of IS</div>
                                </div>
                                <a href="#" class="btn btn-sm btn-outline-warning">Schedule</a>
                            </li>
                        </ul>
                    </div>
                </div>
            </div>
        </div>
    </div>
    
    <script>
        // JavaScript for additional functionality
        $(document).ready(function(){
            // Initialize any plugins or custom behaviors
        });
    </script>
</body>
</html>
