<?php
// authentication_method_report.php
session_start();

// Database connection
$con = mysqli_connect("localhost", "root", "", "cpp");
if (!$con) {
    die("Connection failed: " . mysqli_connect_error());
}

// Default date range (current month)
$from_date = isset($_GET['from_date']) ? $_GET['from_date'] : date('Y-m-01');
$to_date = isset($_GET['to_date']) ? $_GET['to_date'] : date('Y-m-d');

// Get authentication method statistics
$auth_query = "SELECT 
                    CASE 
                        WHEN a.auth_method = ' ' THEN 'PIN'
                        WHEN a.auth_method = 'fingerprint' THEN 'Biometric'
                        WHEN a.auth_method = 'facial' THEN 'Iris'
                        ELSE a.auth_method
                    END as auth_method,
                    COUNT(a.attendance_id) as total_records,
                    COUNT(DISTINCT a.student_id) as unique_students,
                    COUNT(DISTINCT a.subject_id) as subjects_covered,
                    COUNT(DISTINCT a.date) as total_days,
                    GROUP_CONCAT(DISTINCT DAYNAME(a.date)) as days_used
                FROM 
                    attendance a
                WHERE 
                    a.date BETWEEN '$from_date' AND '$to_date'
                    AND WEEKDAY(a.date) < 5  -- Only include weekdays
                GROUP BY 
                    auth_method
                ORDER BY 
                    total_records DESC";

$auth_result = mysqli_query($con, $auth_query);

// Get daily distribution of authentication methods
$daily_dist_query = "SELECT 
                        DATE(a.date) as date,
                        CASE 
                            WHEN a.auth_method = ' ' THEN 'PIN'
                            WHEN a.auth_method = 'fingerprint' THEN 'Biometric'
                            WHEN a.auth_method = 'facial' THEN 'Iris'
                            ELSE a.auth_method
                        END as auth_method,
                        COUNT(a.attendance_id) as count
                    FROM 
                        attendance a
                    WHERE 
                        a.date BETWEEN '$from_date' AND '$to_date'
                        AND WEEKDAY(a.date) < 5  -- Only include weekdays
                    GROUP BY 
                        DATE(a.date), auth_method
                    ORDER BY 
                        date ASC, count DESC";

$daily_dist_result = mysqli_query($con, $daily_dist_query);

// Get total possible attendance for each method
$total_possible_query = "SELECT 
                            CASE 
                                WHEN auth_method = ' ' THEN 'PIN'
                                WHEN auth_method = 'fingerprint' THEN 'Biometric'
                                WHEN auth_method = 'facial' THEN 'Iris'
                                ELSE auth_method
                            END as auth_method,
                            COUNT(DISTINCT student_id) * COUNT(DISTINCT subject_id) as possible_attendance
                        FROM 
                            attendance
                        WHERE 
                            date BETWEEN '$from_date' AND '$to_date'
                            AND WEEKDAY(date) < 5  -- Only include weekdays
                        GROUP BY 
                            auth_method";

$total_possible_result = mysqli_query($con, $total_possible_query);
$possible_attendance = [];
while ($row = mysqli_fetch_assoc($total_possible_result)) {
    $possible_attendance[$row['auth_method']] = $row['possible_attendance'];
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
    <title>Authentication Method Analysis</title>
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
        
        .auth-badge {
            font-size: 1.2rem;
            padding: 0.5rem 1rem;
            border-radius: 2rem;
            font-weight: 500;
        }
        
        .auth-badge.fingerprint {
            background-color: rgba(74, 128, 245, 0.1);
            color: var(--accent-color);
        }
        
        .auth-badge.facial {
            background-color: rgba(40, 167, 69, 0.1);
            color: var(--success-color);
        }
        
        .auth-badge.pin {
            background-color: rgba(255, 193, 7, 0.1);
            color: var(--warning-color);
        }
        
        .auth-badge.biometric {
            background-color: rgba(74, 128, 245, 0.1);
            color: var(--accent-color);
        }
        
        .auth-badge.iris {
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

        .days-used {
            font-size: 1.2rem;
            color: #666;
        }
    </style>
</head>
<body>
    <!-- Include navbar -->
    <?php require '../partials/_navbar.php'; ?>
    
    <div class="report-header">
        <div class="container">
            <h1><i class="fas fa-fingerprint mr-3"></i>Authentication Method Analysis</h1>
            <p class="lead">Distribution of attendance records by authentication method for the period: <?php echo $formatted_from_date; ?> - <?php echo $formatted_to_date; ?></p>
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
            $auth_data = [];
            $total_records = 0;
            while ($row = mysqli_fetch_assoc($auth_result)) {
                $auth_data[$row['auth_method']] = $row;
                $total_records += $row['total_records'];
            }
            
            foreach ($auth_data as $method => $data) {
                $percentage = round(($data['total_records'] / $total_records) * 100, 1);
            ?>
            <div class="col-md-4">
                <div class="stat-card">
                    <h3><?php echo ucfirst($method); ?> Authentication</h3>
                    <div class="row">
                        <div class="col-6">
                            <div class="value"><?php echo $data['total_records']; ?></div>
                            <div class="text-muted">Total Records</div>
                        </div>
                        <div class="col-6">
                            <div class="value"><?php echo $percentage; ?>%</div>
                            <div class="text-muted">Usage Rate</div>
                        </div>
                    </div>
                </div>
            </div>
            <?php } ?>
        </div>
        
        <!-- Charts Section -->
        <div class="report-card">
            <h2 class="section-title">Authentication Method Distribution</h2>
            <div class="row">
                <div class="col-md-6">
                    <div class="chart-container">
                        <canvas id="methodDistributionChart"></canvas>
                    </div>
                </div>
                <div class="col-md-6">
                    <div class="chart-container">
                        <canvas id="methodTrendChart"></canvas>
                    </div>
                </div>
            </div>
        </div>
        
        <!-- Detail Table Section -->
        <div class="report-card">
            <h2 class="section-title">Authentication Method Details</h2>
            <div class="mb-4">
                <button class="btn btn-primary" onclick="exportTableToExcel('authTable', 'Auth_Method_Analysis_<?php echo date('Y-m-d'); ?>')">
                    <i class="fas fa-file-excel mr-2"></i>Export to Excel
                </button>
            </div>
            <div class="table-responsive">
                <table class="table table-striped" id="authTable">
                    <thead>
                        <tr>
                            <th>Method</th>
                            <th>Total Records</th>
                            <th>Unique Students</th>
                            <th>Subjects Covered</th>
                            <th>Total Days</th>
                            <th>Usage Rate</th>
                            <th>Days Used</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php
                        foreach ($auth_data as $method => $data) {
                            $percentage = round(($data['total_records'] / $total_records) * 100, 1);
                        ?>
                            <tr>
                                <td><span class="auth-badge <?php echo $method; ?>"><?php echo ucfirst($method); ?></span></td>
                                <td><?php echo $data['total_records']; ?></td>
                                <td><?php echo $data['unique_students']; ?></td>
                                <td><?php echo $data['subjects_covered']; ?></td>
                                <td><?php echo $data['total_days']; ?></td>
                                <td><?php echo $percentage; ?>%</td>
                                <td class="days-used"><?php echo $data['days_used']; ?></td>
                            </tr>
                        <?php } ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <script>
        // Chart.js configuration
        const distCtx = document.getElementById('methodDistributionChart').getContext('2d');
        const trendCtx = document.getElementById('methodTrendChart').getContext('2d');

        // Method Distribution Chart
        new Chart(distCtx, {
            type: 'pie',
            data: {
                labels: <?php echo json_encode(array_map('ucfirst', array_keys($auth_data))); ?>,
                datasets: [{
                    data: <?php echo json_encode(array_column($auth_data, 'total_records')); ?>,
                    backgroundColor: [
                        'rgba(255, 193, 7, 0.7)',  // PIN - Yellow
                        'rgba(74, 128, 245, 0.7)',  // Biometric - Blue
                        'rgba(40, 167, 69, 0.7)'    // Iris - Green
                    ],
                    borderColor: [
                        'rgba(255, 193, 7, 1)',
                        'rgba(74, 128, 245, 1)',
                        'rgba(40, 167, 69, 1)'
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
                        text: 'Authentication Method Distribution',
                        font: {
                            size: 16
                        }
                    },
                    legend: {
                        position: 'bottom'
                    }
                }
            }
        });

        // Method Trend Chart
        <?php
        $daily_data = [];
        while ($row = mysqli_fetch_assoc($daily_dist_result)) {
            if (!isset($daily_data[$row['date']])) {
                $daily_data[$row['date']] = [];
            }
            $daily_data[$row['date']][$row['auth_method']] = $row['count'];
        }
        ?>
        new Chart(trendCtx, {
            type: 'line',
            data: {
                labels: <?php echo json_encode(array_keys($daily_data)); ?>,
                datasets: [
                    <?php
                    foreach (array_keys($auth_data) as $method) {
                        $borderColor = $method == 'Biometric' ? 'rgba(74, 128, 245, 1)' : 
                                     ($method == 'Iris' ? 'rgba(40, 167, 69, 1)' : 'rgba(255, 193, 7, 1)');
                        $backgroundColor = $method == 'Biometric' ? 'rgba(74, 128, 245, 0.1)' : 
                                         ($method == 'Iris' ? 'rgba(40, 167, 69, 0.1)' : 'rgba(255, 193, 7, 0.1)');
                        $displayLabel = $method == 'pin' ? 'PIN' : $method;
                        echo "{
                            label: '" . $displayLabel . "',
                            data: " . json_encode(array_map(function($date) use ($daily_data, $method) {
                                return isset($daily_data[$date][$method]) ? $daily_data[$date][$method] : 0;
                            }, array_keys($daily_data))) . ",
                            borderColor: '" . $borderColor . "',
                            backgroundColor: '" . $backgroundColor . "',
                            borderWidth: 2,
                            fill: true,
                            tension: 0.4
                        },";
                    }
                    ?>
                ]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    title: {
                        display: true,
                        text: 'Daily Authentication Method Trends',
                        font: {
                            size: 16
                        }
                    },
                    legend: {
                        position: 'bottom',
                        labels: {
                            font: {
                                size: 14
                            }
                        }
                    }
                },
                scales: {
                    y: {
                        beginAtZero: true,
                        title: {
                            display: true,
                            text: 'Number of Records'
                        }
                    },
                    x: {
                        title: {
                            display: true,
                            text: 'Date'
                        }
                    }
                }
            }
        });

        // Excel Export Function
        function exportTableToExcel(tableID, filename = '') {
            const table = document.getElementById(tableID);
            const wb = XLSX.utils.table_to_book(table, {sheet: "Auth Method Analysis"});
            XLSX.writeFile(wb, filename + '.xlsx');
        }
    </script>

    <!-- Include required libraries -->
    <script src="https://cdnjs.cloudflare.com/ajax/libs/xlsx/0.17.0/xlsx.full.min.js"></script>
    <script src="https://code.jquery.com/jquery-3.5.1.slim.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@4.6.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
