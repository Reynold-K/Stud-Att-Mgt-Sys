<?php
// student_attendance_report.php
session_start();

// Database connection
$con = mysqli_connect("localhost", "root", "", "cpp");
if (!$con) {
    die("Connection failed: " . mysqli_connect_error());
}

// Default date range (current month)
$from_date = isset($_GET['from_date']) ? $_GET['from_date'] : date('Y-m-01');
$to_date = isset($_GET['to_date']) ? $_GET['to_date'] : date('Y-m-d');

// Calculate overall attendance rate by student
$student_query = "SELECT 
                      st.s_id as student_id,
                      st.roll_no,
                      st.name,
                      COUNT(a.attendance_id) AS total_attendance,
                      (SELECT COUNT(DISTINCT subject_id) FROM attendance 
                       WHERE student_id = st.s_id AND date BETWEEN '$from_date' AND '$to_date') AS subject_count,
                      (SELECT COUNT(*) FROM subjects) AS total_subjects
                  FROM 
                      student st
                  LEFT JOIN 
                      attendance a ON st.s_id = a.student_id AND a.date BETWEEN '$from_date' AND '$to_date'
                  GROUP BY 
                      st.s_id
                  ORDER BY 
                      total_attendance DESC";

$student_result = mysqli_query($con, $student_query);

// Calculate total possible attendance days
$total_days_query = "SELECT COUNT(DISTINCT date) as total_days 
                     FROM attendance 
                     WHERE date BETWEEN '$from_date' AND '$to_date'";
$total_days_result = mysqli_query($con, $total_days_query);
$total_days_row = mysqli_fetch_assoc($total_days_result);
$total_days = $total_days_row['total_days'];

// Date range formatting for display
$formatted_from_date = date('M d, Y', strtotime($from_date));
$formatted_to_date = date('M d, Y', strtotime($to_date));

?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Student Attendance Analysis</title>
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
    </style>
</head>
<body>
    <!-- Include navbar -->
    <?php require __DIR__ . '/../partials/_navbar.php'; ?>

    <div class="report-header">
        <div class="container">
            <h1><i class="fas fa-user-graduate mr-3"></i>Student Attendance Analysis</h1>
            <p class="lead">Detailed attendance analysis by student for the period: <?php echo $formatted_from_date; ?> - <?php echo $formatted_to_date; ?></p>
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
        
        <!-- Summary Section -->
        <div class="report-card">
            <h2 class="section-title">Student Attendance Summary</h2>
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
            <h2 class="section-title">Student Attendance Details</h2>
            <div class="mb-4">
                <button class="btn btn-primary" onclick="exportTableToExcel('studentTable', 'Student_Attendance_<?php echo date('Y-m-d'); ?>')">
                    <i class="fas fa-file-excel mr-2"></i>Export to Excel
                </button>
            </div>
            <div class="table-responsive">
                <table class="table table-striped" id="studentTable">
                    <thead>
                        <tr>
                            <th>Roll No</th>
                            <th>Student Name</th>
                            <th>Total Attendance</th>
                            <th>Subjects Attended</th>
                            <th>Attendance Rate</th>
                            <th>Status</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php
                        $labels = [];
                        $attendanceData = [];
                        $attendanceRateData = [];
                        $backgroundColors = [];
                        
                        while ($row = mysqli_fetch_assoc($student_result)) {
                            $roll_no = $row['roll_no'];
                            $student_name = $row['name'];
                            $total_attendance = $row['total_attendance'];
                            $subjects_attended = $row['subject_count'];
                            $total_subjects = $row['total_subjects'];
                            
                            // Calculate attendance rate based on subjects attended
                            $attendance_rate = ($subjects_attended > 0 && $total_subjects > 0) ? 
                                              round(($subjects_attended / $total_subjects) * 100, 1) : 0;
                            
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
                            $labels[] = $roll_no;
                            $attendanceData[] = $total_attendance;
                            $attendanceRateData[] = $attendance_rate;
                            $backgroundColors[] = $bg_color;
                        ?>
                            <tr>
                                <td><?php echo $roll_no; ?></td>
                                <td><?php echo $student_name; ?></td>
                                <td><?php echo $total_attendance; ?></td>
                                <td><?php echo $subjects_attended . '/' . $total_subjects; ?></td>
                                <td><?php echo $attendance_rate; ?>%</td>
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
                    label: 'Total Attendance',
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
                        text: 'Total Attendance by Student',
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
            type: 'bar',
            data: {
                labels: <?php echo json_encode($labels); ?>,
                datasets: [{
                    label: 'Attendance Rate (%)',
                    data: <?php echo json_encode($attendanceRateData); ?>,
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
                        text: 'Attendance Rate by Student',
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
            const wb = XLSX.utils.table_to_book(table, {sheet: "Student Attendance"});
            XLSX.writeFile(wb, filename + '.xlsx');
        }
    </script>

    <!-- Include required libraries -->
    <script src="https://cdnjs.cloudflare.com/ajax/libs/xlsx/0.17.0/xlsx.full.min.js"></script>
    <script src="https://code.jquery.com/jquery-3.5.1.slim.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@4.6.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>