<?php
// perfect_attendance.php
session_start();

// Database connection
$con = mysqli_connect("localhost", "root", "", "cpp");
if (!$con) {
    die("Connection failed: " . mysqli_connect_error());
}

// Default date range (current month)
$from_date = isset($_GET['from_date']) ? $_GET['from_date'] : date('Y-m-01');
$to_date = isset($_GET['to_date']) ? $_GET['to_date'] : date('Y-m-d');

// Get students with perfect attendance
$perfect_attendance_query = "WITH subject_days AS (
                                SELECT 
                                    subject_id,
                                    COUNT(DISTINCT date) as total_days
                                FROM 
                                    attendance
                                WHERE 
                                    date BETWEEN '$from_date' AND '$to_date'
                                    AND WEEKDAY(date) < 5  -- Only include weekdays
                                GROUP BY 
                                    subject_id
                            ),
                            student_attendance AS (
                                SELECT 
                                    s.s_id,
                                    s.roll_no,
                                    s.name,
                                    s.enroll_no,
                                    COUNT(DISTINCT a.subject_id) as subjects_attended,
                                    COUNT(DISTINCT a.date) as days_attended,
                                    COUNT(a.attendance_id) as total_records,
                                    GROUP_CONCAT(DISTINCT sub.subject_name) as subjects_list
                                FROM 
                                    student s
                                LEFT JOIN 
                                    attendance a ON s.s_id = a.student_id 
                                    AND a.date BETWEEN '$from_date' AND '$to_date'
                                LEFT JOIN 
                                    subjects sub ON a.subject_id = sub.subject_id
                                GROUP BY 
                                    s.s_id, s.roll_no, s.name, s.enroll_no
                            )
                            SELECT 
                                sa.*,
                                (SELECT COUNT(*) FROM subjects) as total_subjects,
                                (SELECT SUM(total_days) FROM subject_days) as total_possible_days
                            FROM 
                                student_attendance sa
                            WHERE 
                                sa.subjects_attended = (SELECT COUNT(*) FROM subjects)
                                AND sa.days_attended = (SELECT SUM(total_days) FROM subject_days)
                            ORDER BY 
                                sa.roll_no";

$perfect_attendance_result = mysqli_query($con, $perfect_attendance_query);

// Get overall statistics
$stats_query = "SELECT 
                    COUNT(DISTINCT student_id) as total_students,
                    COUNT(DISTINCT subject_id) as total_subjects,
                    COUNT(DISTINCT date) as total_days,
                    COUNT(attendance_id) as total_records
                FROM 
                    attendance
                WHERE 
                    date BETWEEN '$from_date' AND '$to_date'
                    AND WEEKDAY(date) < 5";

$stats_result = mysqli_query($con, $stats_query);
$stats = mysqli_fetch_assoc($stats_result);

// Get perfect attendance count
$perfect_count = mysqli_num_rows($perfect_attendance_result);

// Format dates for display
$formatted_from_date = date('M d, Y', strtotime($from_date));
$formatted_to_date = date('M d, Y', strtotime($to_date));

?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Perfect Attendance Report</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@4.6.0/dist/css/bootstrap.min.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.3/css/all.min.css">
    <style>
        :root {
            --bgcolor-1: #150734;
            --bgcolor-2: #0F2557;
            --accent-color: #4a80f5;
            --light-accent: #e7f0ff;
            --success-color: #28a745;
            --warning-color: #ffc107;
            --danger-color: #dc3545;
        }
        
        html {
            font-size: 62.5%; /* 10px */
        }
        
        body {
            font-size: 1.6rem;
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background-color: #f8f9fa;
        }
        
        .report-header {
            background-color: var(--bgcolor-1);
            color: white;
            padding: 2rem 0;
            margin-bottom: 3rem;
            margin-left: 250px;
            width: calc(100% - 250px);
            margin-top: 60px;
        }
        
        .report-card {
            background-color: white;
            border-radius: 0.8rem;
            box-shadow: 0 0.4rem 0.8rem rgba(0,0,0,0.1);
            padding: 2rem;
            margin-bottom: 2rem;
        }
        
        .section-title {
            color: var(--bgcolor-2);
            font-weight: 600;
            margin-bottom: 2rem;
            padding-bottom: 1rem;
            border-bottom: 1px solid #dee2e6;
        }
        
        .perfect-badge {
            font-size: 1.2rem;
            padding: 0.5rem 1rem;
            border-radius: 2rem;
            font-weight: 500;
            background-color: rgba(40, 167, 69, 0.1);
            color: var(--success-color);
        }
        
        .table-responsive {
            border-radius: 0.8rem;
            box-shadow: 0 0.4rem 0.8rem rgba(0,0,0,0.1);
        }
        
        .table th {
            background-color: var(--bgcolor-2);
            color: white;
            font-weight: 500;
        }
        
        .btn-primary {
            background-color: var(--bgcolor-2);
            border-color: var(--bgcolor-2);
        }
        
        .btn-primary:hover {
            background-color: var(--bgcolor-1);
            border-color: var(--bgcolor-1);
        }
        
        .date-filters {
            background-color: white;
            border-radius: 0.8rem;
            box-shadow: 0 0.4rem 0.8rem rgba(0,0,0,0.1);
            padding: 2rem;
            margin-bottom: 3rem;
        }
        
        .date-filters label {
            font-weight: 600;
            color: var(--bgcolor-2);
        }

        .container {
            margin-left: 250px;
            padding: 20px;
            width: calc(100% - 250px);
            margin-top: 20px;
        }

        .stat-card {
            background: white;
            border-radius: 0.8rem;
            padding: 2rem;
            margin-bottom: 2rem;
            box-shadow: 0 0.4rem 0.8rem rgba(0,0,0,0.1);
        }

        .stat-card h3 {
            color: var(--bgcolor-2);
            font-size: 1.8rem;
            margin-bottom: 1rem;
        }

        .stat-card .value {
            font-size: 2.4rem;
            font-weight: 600;
        }

        .stat-card .sub-value {
            font-size: 1.4rem;
            color: #6c757d;
        }

        .subjects-list {
            max-width: 300px;
            white-space: nowrap;
            overflow: hidden;
            text-overflow: ellipsis;
        }

        @media (max-width: 768px) {
            .container, .report-header {
                margin-left: 0;
                width: 100%;
            }
            .report-header {
                margin-top: 60px;
            }
        }
    </style>
</head>
<body>
    <!-- Include navbar -->
    <?php require '../partials/_navbar.php'; ?>
    
    <div class="report-header">
        <div class="container">
            <h1><i class="fas fa-award mr-3"></i>Perfect Attendance Report</h1>
            <p class="lead">Students with 100% attendance across all subjects for the period: <?php echo $formatted_from_date; ?> - <?php echo $formatted_to_date; ?></p>
        </div>
    </div>
    
    <div class="container">
        <!-- Date Filter Section -->
        <div class="date-filters">
            <form method="GET" action="" class="row align-items-end">
                <div class="col-md-4 form-group">
                    <label for="from_date">From Date:</label>
                    <input type="date" class="form-control" id="from_date" name="from_date" value="<?php echo $from_date; ?>">
                </div>
                <div class="col-md-4 form-group">
                    <label for="to_date">To Date:</label>
                    <input type="date" class="form-control" id="to_date" name="to_date" value="<?php echo $to_date; ?>">
                </div>
                <div class="col-md-4 form-group">
                    <button type="submit" class="btn btn-primary btn-block">Apply Filters</button>
                </div>
            </form>
        </div>
        
        <!-- Summary Statistics -->
        <div class="row">
            <div class="col-md-3">
                <div class="stat-card">
                    <h3>Perfect Attendance</h3>
                    <div class="value"><?php echo $perfect_count; ?></div>
                    <div class="sub-value">out of <?php echo $stats['total_students']; ?> students</div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="stat-card">
                    <h3>Total Subjects</h3>
                    <div class="value"><?php echo $stats['total_subjects']; ?></div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="stat-card">
                    <h3>Total Days</h3>
                    <div class="value"><?php echo $stats['total_days']; ?></div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="stat-card">
                    <h3>Total Records</h3>
                    <div class="value"><?php echo $stats['total_records']; ?></div>
                </div>
            </div>
        </div>
        
        <!-- Perfect Attendance Table -->
        <div class="report-card">
            <h2 class="section-title">Students with Perfect Attendance</h2>
            <div class="mb-4">
                <button class="btn btn-primary" onclick="exportTableToExcel('perfectTable', 'Perfect_Attendance_<?php echo date('Y-m-d'); ?>')">
                    <i class="fas fa-file-excel mr-2"></i>Export to Excel
                </button>
            </div>
            <div class="table-responsive">
                <table class="table table-striped" id="perfectTable">
                    <thead>
                        <tr>
                            <th>Roll No</th>
                            <th>Enrollment No</th>
                            <th>Student Name</th>
                            <th>Subjects Attended</th>
                            <th>Days Attended</th>
                            <th>Total Records</th>
                            <th>Status</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php
                        while ($row = mysqli_fetch_assoc($perfect_attendance_result)) {
                        ?>
                            <tr>
                                <td><?php echo $row['roll_no']; ?></td>
                                <td><?php echo $row['enroll_no']; ?></td>
                                <td><?php echo $row['name']; ?></td>
                                <td>
                                    <div class="subjects-list" title="<?php echo $row['subjects_list']; ?>">
                                        <?php echo $row['subjects_list']; ?>
                                    </div>
                                </td>
                                <td><?php echo $row['days_attended']; ?> days</td>
                                <td><?php echo $row['total_records']; ?> records</td>
                                <td><span class="perfect-badge">Perfect Attendance</span></td>
                            </tr>
                        <?php
                        }
                        ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <script>
        // Excel Export Function
        function exportTableToExcel(tableID, filename = '') {
            const table = document.getElementById(tableID);
            const wb = XLSX.utils.table_to_book(table, {sheet: "Perfect Attendance"});
            XLSX.writeFile(wb, filename + '.xlsx');
        }
    </script>

    <!-- Include required libraries -->
    <script src="https://cdnjs.cloudflare.com/ajax/libs/xlsx/0.17.0/xlsx.full.min.js"></script>
    <script src="https://code.jquery.com/jquery-3.5.1.slim.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@4.6.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
