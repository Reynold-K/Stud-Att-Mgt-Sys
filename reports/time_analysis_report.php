<?php
// time_analysis_report.php
session_start();

// Database connection
$con = mysqli_connect("localhost", "root", "", "cpp");
if (!$con) {
    die("Connection failed: " . mysqli_connect_error());
}

// Default date range (current month)
$from_date = isset($_GET['from_date']) ? $_GET['from_date'] : date('Y-m-01');
$to_date = isset($_GET['to_date']) ? $_GET['to_date'] : date('Y-m-d');

// Get time-based attendance statistics
$time_query = "SELECT 
                    CASE 
                        WHEN TIME(a.attendance_time) < '12:00:00' THEN 'Morning'
                        ELSE 'Afternoon'
                    END as time_period,
                    COUNT(DISTINCT a.student_id) as students_present,
                    COUNT(DISTINCT a.subject_id) as subjects_conducted,
                    COUNT(a.attendance_id) as total_records,
                    (SELECT COUNT(*) FROM student) as total_students,
                    (SELECT COUNT(*) FROM subjects) as total_subjects,
                    GROUP_CONCAT(DISTINCT DAYNAME(a.date)) as days_conducted
                FROM 
                    attendance a
                WHERE 
                    a.date BETWEEN '$from_date' AND '$to_date'
                    AND WEEKDAY(a.date) < 5  -- Only include weekdays (0-4 represents Monday-Friday)
                GROUP BY 
                    time_period
                ORDER BY 
                    time_period";

$time_result = mysqli_query($con, $time_query);

// Get hourly distribution
$hourly_query = "SELECT 
                    HOUR(attendance_time) as hour,
                    COUNT(attendance_id) as attendance_count
                FROM 
                    attendance
                WHERE 
                    date BETWEEN '$from_date' AND '$to_date'
                    AND WEEKDAY(date) < 5  -- Only include weekdays
                GROUP BY 
                    HOUR(attendance_time)
                ORDER BY 
                    hour";

$hourly_result = mysqli_query($con, $hourly_query);

// Get total possible attendance for each time period
$total_possible_query = "SELECT 
                            CASE 
                                WHEN TIME(attendance_time) < '12:00:00' THEN 'Morning'
                                ELSE 'Afternoon'
                            END as time_period,
                            COUNT(DISTINCT student_id) * COUNT(DISTINCT subject_id) as possible_attendance
                        FROM 
                            attendance
                        WHERE 
                            date BETWEEN '$from_date' AND '$to_date'
                            AND WEEKDAY(date) < 5  -- Only include weekdays
                        GROUP BY 
                            time_period";

$total_possible_result = mysqli_query($con, $total_possible_query);
$possible_attendance = [];
while ($row = mysqli_fetch_assoc($total_possible_result)) {
    $possible_attendance[$row['time_period']] = $row['possible_attendance'];
}

// Date range formatting for display
$formatted_from_date = date('M d, Y', strtotime($from_date));
$formatted_to_date = date('M d, Y', strtotime($to_date));

?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Time-based Attendance Analysis</title>
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

        .time-period {
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
            <h1><i class="fas fa-clock mr-3"></i>Time-based Attendance Analysis</h1>
            <p class="lead">Analysis of attendance patterns by time of day for the period: <?php echo $formatted_from_date; ?> - <?php echo $formatted_to_date; ?></p>
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
            <?php
            $time_data = [];
            while ($row = mysqli_fetch_assoc($time_result)) {
                $time_data[$row['time_period']] = $row;
            }
            
            foreach ($time_data as $period => $data) {
                $possible_attendance_count = isset($possible_attendance[$period]) ? $possible_attendance[$period] : 0;
                $attendance_rate = ($possible_attendance_count > 0) ? 
                                  round(($data['total_records'] / $possible_attendance_count) * 100, 1) : 0;
            ?>
            <div class="col-md-6">
                <div class="stat-card">
                    <h3><?php echo $period; ?> Classes</h3>
                    <div class="row">
                        <div class="col-6">
                            <div class="value"><?php echo $data['students_present']; ?></div>
                            <div class="text-muted">Students Present</div>
                        </div>
                        <div class="col-6">
                            <div class="value"><?php echo $attendance_rate; ?>%</div>
                            <div class="text-muted">Attendance Rate</div>
                        </div>
                    </div>
                </div>
            </div>
            <?php } ?>
        </div>
        
        <!-- Charts Section -->
        <div class="report-card">
            <h2 class="section-title">Time-based Attendance Patterns</h2>
            <div class="row">
                <div class="col-md-6">
                    <div class="chart-container">
                        <canvas id="timeComparisonChart"></canvas>
                    </div>
                </div>
                <div class="col-md-6">
                    <div class="chart-container">
                        <canvas id="hourlyDistributionChart"></canvas>
                    </div>
                </div>
            </div>
        </div>
        
        <!-- Detail Table Section -->
        <div class="report-card">
            <h2 class="section-title">Time Period Details</h2>
            <div class="mb-4">
                <button class="btn btn-primary" onclick="exportTableToExcel('timeTable', 'Time_Analysis_<?php echo date('Y-m-d'); ?>')">
                    <i class="fas fa-file-excel mr-2"></i>Export to Excel
                </button>
            </div>
            <div class="table-responsive">
                <table class="table table-striped" id="timeTable">
                    <thead>
                        <tr>
                            <th>Time Period</th>
                            <th>Students Present</th>
                            <th>Subjects Conducted</th>
                            <th>Total Records</th>
                            <th>Attendance Rate</th>
                            <th>Days Conducted</th>
                            <th>Status</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php
                        foreach ($time_data as $period => $data) {
                            $possible_attendance_count = isset($possible_attendance[$period]) ? $possible_attendance[$period] : 0;
                            $attendance_rate = ($possible_attendance_count > 0) ? 
                                              round(($data['total_records'] / $possible_attendance_count) * 100, 1) : 0;
                            
                            // Set attendance status and color based on rate
                            if ($attendance_rate >= 75) {
                                $status = 'High';
                                $badge_class = 'high';
                            } elseif ($attendance_rate >= 50) {
                                $status = 'Medium';
                                $badge_class = 'medium';
                            } else {
                                $status = 'Low';
                                $badge_class = 'low';
                            }
                        ?>
                            <tr>
                                <td><?php echo $period; ?></td>
                                <td><?php echo $data['students_present'] . '/' . $data['total_students']; ?></td>
                                <td><?php echo $data['subjects_conducted'] . '/' . $data['total_subjects']; ?></td>
                                <td><?php echo $data['total_records']; ?></td>
                                <td><?php echo $attendance_rate; ?>%</td>
                                <td class="days-conducted"><?php echo $data['days_conducted']; ?></td>
                                <td><span class="attendance-badge <?php echo $badge_class; ?>"><?php echo $status; ?></span></td>
                            </tr>
                        <?php } ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <script>
        // Chart.js configuration
        const timeCtx = document.getElementById('timeComparisonChart').getContext('2d');
        const hourlyCtx = document.getElementById('hourlyDistributionChart').getContext('2d');

        // Time Period Comparison Chart
        new Chart(timeCtx, {
            type: 'bar',
            data: {
                labels: <?php echo json_encode(array_keys($time_data)); ?>,
                datasets: [{
                    label: 'Attendance Records',
                    data: <?php echo json_encode(array_column($time_data, 'total_records')); ?>,
                    backgroundColor: [
                        'rgba(74, 128, 245, 0.7)',
                        'rgba(255, 193, 7, 0.7)'
                    ],
                    borderColor: [
                        'rgba(74, 128, 245, 1)',
                        'rgba(255, 193, 7, 1)'
                    ],
                    borderWidth: 1
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    title: {
                        display: true,
                        text: 'Attendance by Time Period',
                        font: {
                            size: 16
                        }
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

        // Hourly Distribution Chart
        <?php
        $hourly_data = [];
        while ($row = mysqli_fetch_assoc($hourly_result)) {
            $hourly_data[$row['hour']] = $row['attendance_count'];
        }
        ?>
        new Chart(hourlyCtx, {
            type: 'line',
            data: {
                labels: <?php echo json_encode(array_keys($hourly_data)); ?>,
                datasets: [{
                    label: 'Attendance Distribution',
                    data: <?php echo json_encode(array_values($hourly_data)); ?>,
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
                        text: 'Hourly Attendance Distribution',
                        font: {
                            size: 16
                        }
                    }
                },
                scales: {
                    y: {
                        beginAtZero: true,
                        title: {
                            display: true,
                            text: 'Number of Attendance Records'
                        }
                    },
                    x: {
                        title: {
                            display: true,
                            text: 'Hour of Day'
                        }
                    }
                }
            }
        });

        // Excel Export Function
        function exportTableToExcel(tableID, filename = '') {
            const table = document.getElementById(tableID);
            const wb = XLSX.utils.table_to_book(table, {sheet: "Time Analysis"});
            XLSX.writeFile(wb, filename + '.xlsx');
        }
    </script>

    <!-- Include required libraries -->
    <script src="https://cdnjs.cloudflare.com/ajax/libs/xlsx/0.17.0/xlsx.full.min.js"></script>
    <script src="https://code.jquery.com/jquery-3.5.1.slim.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@4.6.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
