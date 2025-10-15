<?php
// low_attendance_alert.php
session_start();

// Database connection
$con = mysqli_connect("localhost", "root", "", "cpp");
if (!$con) {
    die("Connection failed: " . mysqli_connect_error());
}

// Default date range (current month)
$from_date = isset($_GET['from_date']) ? $_GET['from_date'] : date('Y-m-01');
$to_date = isset($_GET['to_date']) ? $_GET['to_date'] : date('Y-m-d');

// Get subjects with concerning attendance rates
$subject_query = "WITH subject_stats AS (
                    SELECT 
                        s.subject_id,
                        s.subject_name,
                        s.subject_code,
                        COUNT(DISTINCT a.student_id) as students_present,
                        (SELECT COUNT(*) FROM student) as total_students,
                        COUNT(DISTINCT a.date) as days_conducted,
                        COUNT(a.attendance_id) as total_records,
                        GROUP_CONCAT(DISTINCT DAYNAME(a.date)) as class_days,
                        (COUNT(DISTINCT a.student_id) * 100.0 / (SELECT COUNT(*) FROM student)) as attendance_rate
                    FROM 
                        subjects s
                    LEFT JOIN 
                        attendance a ON s.subject_id = a.subject_id 
                        AND a.date BETWEEN '$from_date' AND '$to_date'
                    GROUP BY 
                        s.subject_id, s.subject_name, s.subject_code
                )
                SELECT * FROM subject_stats 
                WHERE attendance_rate < 75
                ORDER BY attendance_rate ASC";

$subject_result = mysqli_query($con, $subject_query);

// Get overall statistics
$stats_query = "WITH subject_stats AS (
                    SELECT 
                        s.subject_id,
                        COUNT(DISTINCT a.student_id) as students_present,
                        (SELECT COUNT(*) FROM student) as total_students,
                        (COUNT(DISTINCT a.student_id) * 100.0 / (SELECT COUNT(*) FROM student)) as attendance_rate
                    FROM 
                        subjects s
                    LEFT JOIN 
                        attendance a ON s.subject_id = a.subject_id 
                        AND a.date BETWEEN '$from_date' AND '$to_date'
                    GROUP BY 
                        s.subject_id
                )
                SELECT 
                    COUNT(DISTINCT subject_id) as total_subjects,
                    COUNT(DISTINCT CASE WHEN attendance_rate < 75 THEN subject_id END) as concerning_subjects
                FROM subject_stats";

$stats_result = mysqli_query($con, $stats_query);
$stats = mysqli_fetch_assoc($stats_result);

// Date range formatting for display
$formatted_from_date = date('M d, Y', strtotime($from_date));
$formatted_to_date = date('M d, Y', strtotime($to_date));

?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Low Attendance Alert</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@4.6.0/dist/css/bootstrap.min.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.3/css/all.min.css">
    <script src="https://cdnjs.cloudflare.com/ajax/libs/Chart.js/3.7.0/chart.min.js"></script>
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
        
        .alert-badge {
            font-size: 1.2rem;
            padding: 0.5rem 1rem;
            border-radius: 2rem;
            font-weight: 500;
        }
        
        .alert-badge.critical {
            background-color: rgba(220, 53, 69, 0.1);
            color: var(--danger-color);
        }
        
        .alert-badge.warning {
            background-color: rgba(255, 193, 7, 0.1);
            color: var(--warning-color);
        }
        
        .alert-badge.info {
            background-color: rgba(74, 128, 245, 0.1);
            color: var(--accent-color);
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
        
        .chart-container {
            position: relative;
            height: 40rem;
            width: 100%;
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
            color: var(--danger-color);
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
            <h1><i class="fas fa-exclamation-triangle mr-3"></i>Low Attendance Alert</h1>
            <p class="lead">Subjects requiring attention due to concerning attendance rates for the period: <?php echo $formatted_from_date; ?> - <?php echo $formatted_to_date; ?></p>
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
            <div class="col-md-6">
                <div class="stat-card">
                    <h3>Concerning Subjects</h3>
                    <div class="value"><?php echo $stats['concerning_subjects']; ?></div>
                    <div class="text-muted">out of <?php echo $stats['total_subjects']; ?> total subjects</div>
                </div>
            </div>
            <div class="col-md-6">
                <div class="stat-card">
                    <h3>Alert Threshold</h3>
                    <div class="value">75%</div>
                    <div class="text-muted">Attendance rate threshold for alerts</div>
                </div>
            </div>
        </div>
        
        <!-- Detail Table Section -->
        <div class="report-card">
            <h2 class="section-title">Subjects Requiring Attention</h2>
            <div class="mb-4">
                <button class="btn btn-primary" onclick="exportTableToExcel('alertTable', 'Low_Attendance_Alert_<?php echo date('Y-m-d'); ?>')">
                    <i class="fas fa-file-excel mr-2"></i>Export to Excel
                </button>
            </div>
            <div class="table-responsive">
                <table class="table table-striped" id="alertTable">
                    <thead>
                        <tr>
                            <th>Subject Code</th>
                            <th>Subject Name</th>
                            <th>Students Present</th>
                            <th>Attendance Rate</th>
                            <th>Days Conducted</th>
                            <th>Class Days</th>
                            <th>Status</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php
                        while ($row = mysqli_fetch_assoc($subject_result)) {
                            $attendance_rate = round(($row['students_present'] / $row['total_students']) * 100, 1);
                            
                            // Set alert status based on attendance rate
                            if ($attendance_rate < 50) {
                                $status = 'Critical';
                                $badge_class = 'critical';
                            } elseif ($attendance_rate < 65) {
                                $status = 'Warning';
                                $badge_class = 'warning';
                            } else {
                                $status = 'Concerning';
                                $badge_class = 'info';
                            }
                        ?>
                            <tr>
                                <td><?php echo $row['subject_code']; ?></td>
                                <td><?php echo $row['subject_name']; ?></td>
                                <td><?php echo $row['students_present'] . '/' . $row['total_students']; ?></td>
                                <td><?php echo $attendance_rate; ?>%</td>
                                <td><?php echo $row['days_conducted']; ?></td>
                                <td><?php echo $row['class_days']; ?></td>
                                <td><span class="alert-badge <?php echo $badge_class; ?>"><?php echo $status; ?></span></td>
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
            const wb = XLSX.utils.table_to_book(table, {sheet: "Low Attendance Alert"});
            XLSX.writeFile(wb, filename + '.xlsx');
        }
    </script>

    <!-- Include required libraries -->
    <script src="https://cdnjs.cloudflare.com/ajax/libs/xlsx/0.17.0/xlsx.full.min.js"></script>
    <script src="https://code.jquery.com/jquery-3.5.1.slim.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@4.6.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
