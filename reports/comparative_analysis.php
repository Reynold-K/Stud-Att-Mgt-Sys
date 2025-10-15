<?php
// comparative_analysis.php
session_start();

// Database connection
$con = mysqli_connect("localhost", "root", "", "cpp");
if (!$con) {
    die("Connection failed: " . mysqli_connect_error());
}

// Set date ranges
$current_month_start = date('Y-m-01');
$current_month_end = date('Y-m-d');
$last_month_start = date('Y-m-01', strtotime('-1 month'));
$last_month_end = date('Y-m-t', strtotime('-1 month'));

// Get comparative statistics
$comparison_query = "WITH current_month_stats AS (
                        SELECT 
                            COUNT(DISTINCT a.student_id) as students_present,
                            COUNT(DISTINCT a.subject_id) as subjects_conducted,
                            COUNT(DISTINCT a.date) as days_conducted,
                            COUNT(a.attendance_id) as total_records,
                            (SELECT COUNT(*) FROM student) as total_students,
                            (SELECT COUNT(*) FROM subjects) as total_subjects,
                            (COUNT(DISTINCT a.student_id) * 100.0 / (SELECT COUNT(*) FROM student)) as attendance_rate
                        FROM 
                            attendance a
                        WHERE 
                            a.date BETWEEN '$current_month_start' AND '$current_month_end'
                    ),
                    last_month_stats AS (
                        SELECT 
                            COUNT(DISTINCT a.student_id) as students_present,
                            COUNT(DISTINCT a.subject_id) as subjects_conducted,
                            COUNT(DISTINCT a.date) as days_conducted,
                            COUNT(a.attendance_id) as total_records,
                            (SELECT COUNT(*) FROM student) as total_students,
                            (SELECT COUNT(*) FROM subjects) as total_subjects,
                            (COUNT(DISTINCT a.student_id) * 100.0 / (SELECT COUNT(*) FROM student)) as attendance_rate
                        FROM 
                            attendance a
                        WHERE 
                            a.date BETWEEN '$last_month_start' AND '$last_month_end'
                    )
                    SELECT 
                        'Current Month' as period,
                        students_present,
                        subjects_conducted,
                        days_conducted,
                        total_records,
                        total_students,
                        total_subjects,
                        attendance_rate
                    FROM current_month_stats
                    UNION ALL
                    SELECT 
                        'Last Month' as period,
                        students_present,
                        subjects_conducted,
                        days_conducted,
                        total_records,
                        total_students,
                        total_subjects,
                        attendance_rate
                    FROM last_month_stats";

$comparison_result = mysqli_query($con, $comparison_query);

// Get subject-wise comparison
$subject_comparison_query = "WITH current_month_subjects AS (
                                SELECT 
                                    s.subject_id,
                                    s.subject_name,
                                    s.subject_code,
                                    COUNT(DISTINCT a.student_id) as students_present,
                                    COUNT(DISTINCT a.date) as days_conducted,
                                    COUNT(a.attendance_id) as total_records,
                                    (COUNT(DISTINCT a.student_id) * 100.0 / (SELECT COUNT(*) FROM student)) as attendance_rate
                                FROM 
                                    subjects s
                                LEFT JOIN 
                                    attendance a ON s.subject_id = a.subject_id 
                                    AND a.date BETWEEN '$current_month_start' AND '$current_month_end'
                                GROUP BY 
                                    s.subject_id, s.subject_name, s.subject_code
                            ),
                            last_month_subjects AS (
                                SELECT 
                                    s.subject_id,
                                    s.subject_name,
                                    s.subject_code,
                                    COUNT(DISTINCT a.student_id) as students_present,
                                    COUNT(DISTINCT a.date) as days_conducted,
                                    COUNT(a.attendance_id) as total_records,
                                    (COUNT(DISTINCT a.student_id) * 100.0 / (SELECT COUNT(*) FROM student)) as attendance_rate
                                FROM 
                                    subjects s
                                LEFT JOIN 
                                    attendance a ON s.subject_id = a.subject_id 
                                    AND a.date BETWEEN '$last_month_start' AND '$last_month_end'
                                GROUP BY 
                                    s.subject_id, s.subject_name, s.subject_code
                            )
                            SELECT 
                                c.subject_code,
                                c.subject_name,
                                c.students_present as current_students,
                                l.students_present as last_students,
                                c.days_conducted as current_days,
                                l.days_conducted as last_days,
                                c.attendance_rate as current_rate,
                                l.attendance_rate as last_rate,
                                (c.attendance_rate - l.attendance_rate) as rate_change
                            FROM 
                                current_month_subjects c
                            LEFT JOIN 
                                last_month_subjects l ON c.subject_id = l.subject_id
                            ORDER BY 
                                rate_change DESC";

$subject_comparison_result = mysqli_query($con, $subject_comparison_query);

// Format dates for display
$current_month_display = date('F Y');
$last_month_display = date('F Y', strtotime('-1 month'));

?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Attendance Comparative Analysis</title>
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
        
        .change-badge {
            font-size: 1.2rem;
            padding: 0.5rem 1rem;
            border-radius: 2rem;
            font-weight: 500;
        }
        
        .change-badge.positive {
            background-color: rgba(40, 167, 69, 0.1);
            color: var(--success-color);
        }
        
        .change-badge.negative {
            background-color: rgba(220, 53, 69, 0.1);
            color: var(--danger-color);
        }
        
        .change-badge.neutral {
            background-color: rgba(108, 117, 125, 0.1);
            color: #6c757d;
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

        .stat-card .change {
            font-size: 1.4rem;
            font-weight: 500;
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
            <h1><i class="fas fa-chart-line mr-3"></i>Attendance Comparative Analysis</h1>
            <p class="lead">Month-over-month comparison of attendance metrics: <?php echo $last_month_display; ?> vs <?php echo $current_month_display; ?></p>
        </div>
    </div>
    
    <div class="container">
        <!-- Overall Comparison -->
        <div class="report-card">
            <h2 class="section-title">Overall Attendance Comparison</h2>
            <div class="row">
                <div class="col-md-6">
                    <div class="chart-container">
                        <canvas id="overallComparisonChart"></canvas>
                    </div>
                </div>
                <div class="col-md-6">
                    <div class="chart-container">
                        <canvas id="attendanceRateChart"></canvas>
                    </div>
                </div>
            </div>
        </div>
        
        <!-- Subject-wise Comparison -->
        <div class="report-card">
            <h2 class="section-title">Subject-wise Comparison</h2>
            <div class="mb-4">
                <button class="btn btn-primary" onclick="exportTableToExcel('comparisonTable', 'Attendance_Comparison_<?php echo date('Y-m-d'); ?>')">
                    <i class="fas fa-file-excel mr-2"></i>Export to Excel
                </button>
            </div>
            <div class="table-responsive">
                <table class="table table-striped" id="comparisonTable">
                    <thead>
                        <tr>
                            <th>Subject Code</th>
                            <th>Subject Name</th>
                            <th>Current Month</th>
                            <th>Last Month</th>
                            <th>Change</th>
                            <th>Status</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php
                        while ($row = mysqli_fetch_assoc($subject_comparison_result)) {
                            $rate_change = round($row['rate_change'], 1);
                            
                            // Set change status
                            if ($rate_change > 5) {
                                $status = 'Significant Improvement';
                                $badge_class = 'positive';
                            } elseif ($rate_change < -5) {
                                $status = 'Significant Decline';
                                $badge_class = 'negative';
                            } else {
                                $status = 'Stable';
                                $badge_class = 'neutral';
                            }
                        ?>
                            <tr>
                                <td><?php echo $row['subject_code']; ?></td>
                                <td><?php echo $row['subject_name']; ?></td>
                                <td>
                                    <?php echo $row['current_students']; ?> students<br>
                                    <?php echo $row['current_days']; ?> days<br>
                                    <?php echo round($row['current_rate'], 1); ?>%
                                </td>
                                <td>
                                    <?php echo $row['last_students']; ?> students<br>
                                    <?php echo $row['last_days']; ?> days<br>
                                    <?php echo round($row['last_rate'], 1); ?>%
                                </td>
                                <td><?php echo $rate_change > 0 ? '+' : ''; ?><?php echo $rate_change; ?>%</td>
                                <td><span class="change-badge <?php echo $badge_class; ?>"><?php echo $status; ?></span></td>
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
        const ctx = document.getElementById('overallComparisonChart').getContext('2d');
        const rateCtx = document.getElementById('attendanceRateChart').getContext('2d');

        // Overall Comparison Chart
        new Chart(ctx, {
            type: 'bar',
            data: {
                labels: ['Students Present', 'Subjects Conducted', 'Days Conducted', 'Total Records'],
                datasets: [
                    {
                        label: '<?php echo $current_month_display; ?>',
                        data: [
                            <?php 
                            $current = mysqli_fetch_assoc($comparison_result);
                            echo $current['students_present'] . ', ' . 
                                 $current['subjects_conducted'] . ', ' . 
                                 $current['days_conducted'] . ', ' . 
                                 $current['total_records'];
                            ?>
                        ],
                        backgroundColor: 'rgba(74, 128, 245, 0.7)',
                        borderColor: 'rgba(74, 128, 245, 1)',
                        borderWidth: 1
                    },
                    {
                        label: '<?php echo $last_month_display; ?>',
                        data: [
                            <?php 
                            $last = mysqli_fetch_assoc($comparison_result);
                            echo $last['students_present'] . ', ' . 
                                 $last['subjects_conducted'] . ', ' . 
                                 $last['days_conducted'] . ', ' . 
                                 $last['total_records'];
                            ?>
                        ],
                        backgroundColor: 'rgba(108, 117, 125, 0.7)',
                        borderColor: 'rgba(108, 117, 125, 1)',
                        borderWidth: 1
                    }
                ]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    title: {
                        display: true,
                        text: 'Overall Attendance Metrics Comparison',
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
                            text: 'Count'
                        }
                    }
                }
            }
        });

        // Attendance Rate Chart
        new Chart(rateCtx, {
            type: 'line',
            data: {
                labels: ['<?php echo $last_month_display; ?>', '<?php echo $current_month_display; ?>'],
                datasets: [{
                    label: 'Attendance Rate (%)',
                    data: [
                        <?php echo round($last['attendance_rate'], 1); ?>,
                        <?php echo round($current['attendance_rate'], 1); ?>
                    ],
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
                        text: 'Attendance Rate Trend',
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
            const wb = XLSX.utils.table_to_book(table, {sheet: "Attendance Comparison"});
            XLSX.writeFile(wb, filename + '.xlsx');
        }
    </script>

    <!-- Include required libraries -->
    <script src="https://cdnjs.cloudflare.com/ajax/libs/xlsx/0.17.0/xlsx.full.min.js"></script>
    <script src="https://code.jquery.com/jquery-3.5.1.slim.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@4.6.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
