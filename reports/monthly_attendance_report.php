<?php
// monthly_attendance_report.php
session_start();

// Database connection
$con = mysqli_connect("localhost", "root", "", "cpp");
if (!$con) {
    die("Connection failed: " . mysqli_connect_error());
}

// Default date range (current academic year)
$current_month = date('m');
$current_year = date('Y');
$academic_year_start = ($current_month >= 7) ? $current_year : ($current_year - 1);
$from_date = isset($_GET['from_date']) ? $_GET['from_date'] : date($academic_year_start . '-07-01');
$to_date = isset($_GET['to_date']) ? $_GET['to_date'] : date('Y-m-d');

// Get monthly attendance statistics
$monthly_query = "SELECT 
                    DATE_FORMAT(a.date, '%Y-%m') as month_year,
                    DATE_FORMAT(a.date, '%M %Y') as month_name,
                    COUNT(DISTINCT a.student_id) as students_present,
                    COUNT(DISTINCT a.subject_id) as subjects_conducted,
                    COUNT(a.attendance_id) as total_records,
                    (SELECT COUNT(*) FROM student) as total_students,
                    (SELECT COUNT(*) FROM subjects) as total_subjects,
                    GROUP_CONCAT(DISTINCT DAYNAME(a.date)) as days_conducted,
                    COUNT(DISTINCT a.date) as total_days
                FROM 
                    attendance a
                WHERE 
                    a.date BETWEEN '$from_date' AND '$to_date'
                    AND WEEKDAY(a.date) < 5  -- Only include weekdays
                GROUP BY 
                    month_year
                ORDER BY 
                    month_year DESC";

$monthly_result = mysqli_query($con, $monthly_query);

// Get total possible attendance for each month
$total_possible_query = "SELECT 
                            DATE_FORMAT(date, '%Y-%m') as month_year,
                            COUNT(DISTINCT student_id) * COUNT(DISTINCT subject_id) as possible_attendance
                        FROM 
                            attendance
                        WHERE 
                            date BETWEEN '$from_date' AND '$to_date'
                            AND WEEKDAY(date) < 5  -- Only include weekdays
                        GROUP BY 
                            month_year";

$total_possible_result = mysqli_query($con, $total_possible_query);
$possible_attendance = [];
while ($row = mysqli_fetch_assoc($total_possible_result)) {
    $possible_attendance[$row['month_year']] = $row['possible_attendance'];
}

// Get academic year summary
$academic_year_query = "SELECT 
                            COUNT(DISTINCT student_id) as total_students,
                            COUNT(DISTINCT subject_id) as total_subjects,
                            COUNT(DISTINCT date) as total_days,
                            COUNT(attendance_id) as total_records
                        FROM 
                            attendance
                        WHERE 
                            date BETWEEN '$from_date' AND '$to_date'
                            AND WEEKDAY(date) < 5";

$academic_year_result = mysqli_query($con, $academic_year_query);
$academic_year_stats = mysqli_fetch_assoc($academic_year_result);

// Date range formatting for display
$formatted_from_date = date('M d, Y', strtotime($from_date));
$formatted_to_date = date('M d, Y', strtotime($to_date));

?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Monthly Attendance Analysis</title>
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
        
        .high-attendance {
            color: var(--success-color);
        }
        
        .medium-attendance {
            color: var(--warning-color);
        }
        
        .low-attendance {
            color: var(--danger-color);
        }
        
        .attendance-badge {
            font-size: 1.2rem;
            padding: 0.5rem 1rem;
            border-radius: 2rem;
            font-weight: 500;
        }
        
        .attendance-badge.high {
            background-color: rgba(40, 167, 69, 0.1);
            color: var(--success-color);
        }
        
        .attendance-badge.medium {
            background-color: rgba(255, 193, 7, 0.1);
            color: var(--warning-color);
        }
        
        .attendance-badge.low {
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

        @media (max-width: 768px) {
            .container, .report-header {
                margin-left: 0;
                width: 100%;
            }
            .report-header {
                margin-top: 60px;
            }
        }

        .month-name {
            font-size: 1.4rem;
            color: #666;
        }

        .days-conducted {
            font-size: 1.2rem;
            color: #666;
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
            color: var(--accent-color);
        }
    </style>
</head>
<body>
    <!-- Include navbar -->
    <?php require '../partials/_navbar.php'; ?>
    
    <div class="report-header">
        <div class="container">
            <h1><i class="fas fa-calendar-alt mr-3"></i>Monthly Attendance Analysis</h1>
            <p class="lead">Monthly attendance patterns and comparison across the academic year: <?php echo $formatted_from_date; ?> - <?php echo $formatted_to_date; ?></p>
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
        
        <!-- Academic Year Summary -->
        <div class="row">
            <div class="col-md-3">
                <div class="stat-card">
                    <h3>Total Students</h3>
                    <div class="value"><?php echo $academic_year_stats['total_students']; ?></div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="stat-card">
                    <h3>Total Subjects</h3>
                    <div class="value"><?php echo $academic_year_stats['total_subjects']; ?></div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="stat-card">
                    <h3>Total Days</h3>
                    <div class="value"><?php echo $academic_year_stats['total_days']; ?></div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="stat-card">
                    <h3>Total Records</h3>
                    <div class="value"><?php echo $academic_year_stats['total_records']; ?></div>
                </div>
            </div>
        </div>
        
        <!-- Charts Section -->
        <div class="report-card">
            <h2 class="section-title">Monthly Attendance Patterns</h2>
            <div class="row">
                <div class="col-md-6">
                    <div class="chart-container">
                        <canvas id="attendanceChart"></canvas>
                    </div>
                </div>
                <div class="col-md-6">
                    <div class="chart-container">
                        <canvas id="attendanceRateChart"></canvas>
                    </div>
                </div>
            </div>
        </div>
        
        <!-- Detail Table Section -->
        <div class="report-card">
            <h2 class="section-title">Monthly Attendance Details</h2>
            <div class="mb-4">
                <button class="btn btn-primary" onclick="exportTableToExcel('monthlyTable', 'Monthly_Attendance_<?php echo date('Y-m-d'); ?>')">
                    <i class="fas fa-file-excel mr-2"></i>Export to Excel
                </button>
            </div>
            <div class="table-responsive">
                <table class="table table-striped" id="monthlyTable">
                    <thead>
                        <tr>
                            <th>Month</th>
                            <th>Students Present</th>
                            <th>Subjects Conducted</th>
                            <th>Total Records</th>
                            <th>Total Days</th>
                            <th>Attendance Rate</th>
                            <th>Days Conducted</th>
                            <th>Status</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php
                        $labels = [];
                        $attendanceData = [];
                        $attendanceRateData = [];
                        $backgroundColors = [];
                        
                        while ($row = mysqli_fetch_assoc($monthly_result)) {
                            $month_year = $row['month_year'];
                            $month_name = $row['month_name'];
                            $students_present = $row['students_present'];
                            $subjects_conducted = $row['subjects_conducted'];
                            $total_records = $row['total_records'];
                            $total_days = $row['total_days'];
                            $total_students = $row['total_students'];
                            $total_subjects = $row['total_subjects'];
                            $days_conducted = $row['days_conducted'];
                            
                            // Calculate attendance rate
                            $possible_attendance_count = isset($possible_attendance[$month_year]) ? $possible_attendance[$month_year] : 0;
                            $attendance_rate = ($possible_attendance_count > 0) ? 
                                              round(($total_records / $possible_attendance_count) * 100, 1) : 0;
                            
                            // Set attendance status and color based on rate
                            if ($attendance_rate >= 75) {
                                $status = 'High';
                                $badge_class = 'high';
                                $bg_color = 'rgba(40, 167, 69, 0.7)';
                            } elseif ($attendance_rate >= 50) {
                                $status = 'Medium';
                                $badge_class = 'medium';
                                $bg_color = 'rgba(255, 193, 7, 0.7)';
                            } else {
                                $status = 'Low';
                                $badge_class = 'low';
                                $bg_color = 'rgba(220, 53, 69, 0.7)';
                            }
                            
                            // Store data for chart
                            $labels[] = $month_name;
                            $attendanceData[] = $total_records;
                            $attendanceRateData[] = $attendance_rate;
                            $backgroundColors[] = $bg_color;
                        ?>
                            <tr>
                                <td><?php echo $month_name; ?></td>
                                <td><?php echo $students_present . '/' . $total_students; ?></td>
                                <td><?php echo $subjects_conducted . '/' . $total_subjects; ?></td>
                                <td><?php echo $total_records; ?></td>
                                <td><?php echo $total_days; ?></td>
                                <td><?php echo $attendance_rate; ?>%</td>
                                <td class="days-conducted"><?php echo $days_conducted; ?></td>
                                <td><span class="attendance-badge <?php echo $badge_class; ?>"><?php echo $status; ?></span></td>
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
        // Chart.js configuration
        const ctx = document.getElementById('attendanceChart').getContext('2d');
        const rateCtx = document.getElementById('attendanceRateChart').getContext('2d');

        // Attendance Chart
        new Chart(ctx, {
            type: 'bar',
            data: {
                labels: <?php echo json_encode($labels); ?>,
                datasets: [{
                    label: 'Total Attendance Records',
                    data: <?php echo json_encode($attendanceData); ?>,
                    backgroundColor: <?php echo json_encode($backgroundColors); ?>,
                    borderColor: <?php echo json_encode($backgroundColors); ?>,
                    borderWidth: 1
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    title: {
                        display: true,
                        text: 'Monthly Attendance Records',
                        font: {
                            size: 16
                        }
                    },
                    legend: {
                        display: false
                    }
                },
                scales: {
                    y: {
                        beginAtZero: true,
                        title: {
                            display: true,
                            text: 'Number of Attendance Records'
                        }
                    }
                }
            }
        });

        // Attendance Rate Chart
        new Chart(rateCtx, {
            type: 'line',
            data: {
                labels: <?php echo json_encode($labels); ?>,
                datasets: [{
                    label: 'Attendance Rate (%)',
                    data: <?php echo json_encode($attendanceRateData); ?>,
                    borderColor: 'rgba(74, 128, 245, 1)',
                    backgroundColor: 'rgba(74, 128, 245, 0.1)',
                    borderWidth: 2,
                    fill: true,
                    tension: 0.4
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    title: {
                        display: true,
                        text: 'Monthly Attendance Rate Trend',
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

        // Excel Export Function
        function exportTableToExcel(tableID, filename = '') {
            const table = document.getElementById(tableID);
            const wb = XLSX.utils.table_to_book(table, {sheet: "Monthly Attendance"});
            XLSX.writeFile(wb, filename + '.xlsx');
        }
    </script>

    <!-- Include required libraries -->
    <script src="https://cdnjs.cloudflare.com/ajax/libs/xlsx/0.17.0/xlsx.full.min.js"></script>
    <script src="https://code.jquery.com/jquery-3.5.1.slim.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@4.6.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
