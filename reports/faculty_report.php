<?php
// faculty_report.php
session_start();

// Database connection
$con = mysqli_connect("localhost", "root", "", "cpp");
if (!$con) {
    die("Connection failed: " . mysqli_connect_error());
}

// Default date range (current month)
$from_date = isset($_GET['from_date']) ? $_GET['from_date'] : date('Y-m-01');
$to_date = isset($_GET['to_date']) ? $_GET['to_date'] : date('Y-m-d');

// Get faculty attendance statistics
$faculty_query = "WITH faculty_stats AS (
    SELECT 
        f.f_id,
        f.name as faculty_name,
        f.email,
        f.department,
        COUNT(DISTINCT s.subject_id) as subjects_taught,
        COUNT(DISTINCT a.date) as days_conducted,
        COUNT(DISTINCT a.student_id) as students_taught,
        COUNT(a.attendance_id) as total_records,
        GROUP_CONCAT(DISTINCT s.subject_name) as subject_list,
        GROUP_CONCAT(DISTINCT s.subject_code) as subject_codes,
        (SELECT COUNT(*) FROM subjects) as total_subjects,
        (SELECT COUNT(*) FROM student) as total_students
    FROM 
        faculty f
    LEFT JOIN 
        subjects s ON f.f_id = s.faculty_id
    LEFT JOIN 
        attendance a ON s.subject_id = a.subject_id 
        AND a.date BETWEEN '$from_date' AND '$to_date'
    GROUP BY 
        f.f_id, f.name, f.email, f.department
)
SELECT 
    *,
    (students_taught * 100.0 / total_students) as student_coverage,
    (subjects_taught * 100.0 / total_subjects) as subject_coverage
FROM 
    faculty_stats
ORDER BY 
    total_records DESC";

$faculty_result = mysqli_query($con, $faculty_query);

// Get overall statistics
$stats_query = "SELECT 
    COUNT(DISTINCT f.f_id) as total_faculty,
    COUNT(DISTINCT s.subject_id) as total_subjects,
    COUNT(DISTINCT a.date) as total_days,
    COUNT(a.attendance_id) as total_records
FROM 
    faculty f
LEFT JOIN 
    subjects s ON f.f_id = s.faculty_id
LEFT JOIN 
    attendance a ON s.subject_id = a.subject_id 
    AND a.date BETWEEN '$from_date' AND '$to_date'
WHERE 
    WEEKDAY(a.date) < 5";

$stats_result = mysqli_query($con, $stats_query);
$stats = mysqli_fetch_assoc($stats_result);

// Get subject-wise statistics for each faculty
$subject_stats_query = "SELECT 
    f.f_id,
    s.subject_code,
    s.subject_name,
    COUNT(DISTINCT a.date) as days_conducted,
    COUNT(DISTINCT a.student_id) as students_taught,
    COUNT(a.attendance_id) as total_records,
    (COUNT(DISTINCT a.student_id) * 100.0 / (SELECT COUNT(*) FROM student)) as attendance_rate
FROM 
    faculty f
LEFT JOIN 
    subjects s ON f.f_id = s.faculty_id
LEFT JOIN 
    attendance a ON s.subject_id = a.subject_id 
    AND a.date BETWEEN '$from_date' AND '$to_date'
GROUP BY 
    f.f_id, s.subject_code, s.subject_name
ORDER BY 
    f.f_id, total_records DESC";

$subject_stats_result = mysqli_query($con, $subject_stats_query);

// Process subject statistics into an array
$subject_stats = array();
while ($row = mysqli_fetch_assoc($subject_stats_result)) {
    $subject_stats[$row['f_id']][] = $row;
}

// Format dates for display
$formatted_from_date = date('M d, Y', strtotime($from_date));
$formatted_to_date = date('M d, Y', strtotime($to_date));

?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Faculty Attendance Report</title>
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
            font-size: 62.5%;
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
        
        .coverage-badge {
            font-size: 1.2rem;
            padding: 0.5rem 1rem;
            border-radius: 2rem;
            font-weight: 500;
        }
        
        .coverage-badge.high {
            background-color: rgba(40, 167, 69, 0.1);
            color: var(--success-color);
        }
        
        .coverage-badge.medium {
            background-color: rgba(255, 193, 7, 0.1);
            color: var(--warning-color);
        }
        
        .coverage-badge.low {
            background-color: rgba(220, 53, 69, 0.1);
            color: var(--danger-color);
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
            height: 30rem;
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
        }

        .stat-card .sub-value {
            font-size: 1.4rem;
            color: #6c757d;
        }

        .subject-list {
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
            <h1><i class="fas fa-chalkboard-teacher mr-3"></i>Faculty Attendance Report</h1>
            <p class="lead">Attendance patterns and statistics by faculty member for the period: <?php echo $formatted_from_date; ?> - <?php echo $formatted_to_date; ?></p>
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
                    <h3>Total Faculty</h3>
                    <div class="value"><?php echo $stats['total_faculty']; ?></div>
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
        
        <!-- Faculty List -->
        <div class="report-card">
            <h2 class="section-title">Faculty Attendance Overview</h2>
            <div class="mb-4">
                <button class="btn btn-primary" onclick="exportTableToExcel('facultyTable', 'Faculty_Attendance_<?php echo date('Y-m-d'); ?>')">
                    <i class="fas fa-file-excel mr-2"></i>Export to Excel
                </button>
            </div>
            <div class="table-responsive">
                <table class="table table-striped" id="facultyTable">
                    <thead>
                        <tr>
                            <th>Faculty Name</th>
                            <th>Department</th>
                            <th>Email</th>
                            <th>Subjects Taught</th>
                            <th>Days Conducted</th>
                            <th>Students Taught</th>
                            <th>Total Records</th>
                            <th>Coverage</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php
                        while ($row = mysqli_fetch_assoc($faculty_result)) {
                            $student_coverage = round($row['student_coverage'], 1);
                            $subject_coverage = round($row['subject_coverage'], 1);
                            
                            // Set coverage status
                            if ($student_coverage >= 75) {
                                $status = 'High Coverage';
                                $badge_class = 'high';
                            } elseif ($student_coverage >= 50) {
                                $status = 'Medium Coverage';
                                $badge_class = 'medium';
                            } else {
                                $status = 'Low Coverage';
                                $badge_class = 'low';
                            }
                        ?>
                            <tr>
                                <td>
                                    <strong><?php echo $row['faculty_name']; ?></strong>
                                </td>
                                <td><?php echo $row['department']; ?></td>
                                <td><?php echo $row['email']; ?></td>
                                <td>
                                    <div class="subject-list" title="<?php echo $row['subject_list']; ?>">
                                        <?php echo $row['subject_list']; ?>
                                    </div>
                                </td>
                                <td><?php echo $row['days_conducted']; ?> days</td>
                                <td><?php echo $row['students_taught']; ?> students</td>
                                <td><?php echo $row['total_records']; ?> records</td>
                                <td>
                                    <span class="coverage-badge <?php echo $badge_class; ?>">
                                        <?php echo $status; ?>
                                    </span>
                                    <div class="small text-muted mt-1">
                                        Students: <?php echo $student_coverage; ?>%<br>
                                        Subjects: <?php echo $subject_coverage; ?>%
                                    </div>
                                </td>
                            </tr>
                        <?php
                        }
                        ?>
                    </tbody>
                </table>
            </div>
        </div>
        
        <!-- Subject-wise Analysis -->
        <div class="report-card">
            <h2 class="section-title">Subject-wise Analysis</h2>
            <div class="row">
                <?php
                mysqli_data_seek($faculty_result, 0);
                while ($faculty = mysqli_fetch_assoc($faculty_result)) {
                    if (isset($subject_stats[$faculty['f_id']])) {
                ?>
                    <div class="col-md-6 mb-4">
                        <div class="card">
                            <div class="card-header bg-primary text-white">
                                <h5 class="mb-0"><?php echo $faculty['faculty_name']; ?></h5>
                            </div>
                            <div class="card-body">
                                <div class="chart-container">
                                    <canvas id="chart_<?php echo $faculty['f_id']; ?>"></canvas>
                                </div>
                            </div>
                        </div>
                    </div>
                    <script>
                        new Chart(document.getElementById('chart_<?php echo $faculty['f_id']; ?>').getContext('2d'), {
                            type: 'bar',
                            data: {
                                labels: <?php echo json_encode(array_column($subject_stats[$faculty['f_id']], 'subject_name')); ?>,
                                datasets: [{
                                    label: 'Attendance Rate (%)',
                                    data: <?php echo json_encode(array_column($subject_stats[$faculty['f_id']], 'attendance_rate')); ?>,
                                    backgroundColor: 'rgba(74, 128, 245, 0.7)',
                                    borderColor: 'rgba(74, 128, 245, 1)',
                                    borderWidth: 1
                                }]
                            },
                            options: {
                                responsive: true,
                                maintainAspectRatio: false,
                                plugins: {
                                    title: {
                                        display: true,
                                        text: 'Subject-wise Attendance Rates',
                                        font: {
                                            size: 16
                                        }
                                    }
                                },
                                scales: {
                                    y: {
                                        beginAtZero: true,
                                        max: 100,
                                        title: {
                                            display: true,
                                            text: 'Attendance Rate (%)'
                                        }
                                    }
                                }
                            }
                        });
                    </script>
                <?php
                    }
                }
                ?>
            </div>
        </div>
    </div>

    <script>
        // Excel Export Function
        function exportTableToExcel(tableID, filename = '') {
            const table = document.getElementById(tableID);
            const wb = XLSX.utils.table_to_book(table, {sheet: "Faculty Attendance"});
            XLSX.writeFile(wb, filename + '.xlsx');
        }
    </script>

    <!-- Include required libraries -->
    <script src="https://cdnjs.cloudflare.com/ajax/libs/xlsx/0.17.0/xlsx.full.min.js"></script>
    <script src="https://code.jquery.com/jquery-3.5.1.slim.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@4.6.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
