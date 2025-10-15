<?php
// attendance_heatmap.php
session_start();

// Database connection
$con = mysqli_connect("localhost", "root", "", "cpp");
if (!$con) {
    die("Connection failed: " . mysqli_connect_error());
}

// Default date range (current month)
$from_date = isset($_GET['from_date']) ? $_GET['from_date'] : date('Y-m-01');
$to_date = isset($_GET['to_date']) ? $_GET['to_date'] : date('Y-m-d');

// Get attendance data by day and hour
$heatmap_query = "SELECT 
                    DAYNAME(date) as day_name,
                    HOUR(attendance_time) as hour,
                    COUNT(DISTINCT student_id) as student_count,
                    COUNT(DISTINCT subject_id) as subject_count,
                    COUNT(attendance_id) as total_records
                FROM 
                    attendance
                WHERE 
                    date BETWEEN '$from_date' AND '$to_date'
                    AND WEEKDAY(date) < 5  -- Only include weekdays
                GROUP BY 
                    DAYNAME(date), HOUR(attendance_time)
                ORDER BY 
                    FIELD(DAYNAME(date), 'Monday', 'Tuesday', 'Wednesday', 'Thursday', 'Friday'),
                    HOUR(attendance_time)";

$heatmap_result = mysqli_query($con, $heatmap_query);

// Initialize heatmap data array
$heatmap_data = array();
$max_students = 0;
$max_subjects = 0;
$max_records = 0;

// Process heatmap data
while ($row = mysqli_fetch_assoc($heatmap_result)) {
    $day = $row['day_name'];
    $hour = $row['hour'];
    $students = $row['student_count'];
    $subjects = $row['subject_count'];
    $records = $row['total_records'];
    
    $heatmap_data[$day][$hour] = array(
        'students' => $students,
        'subjects' => $subjects,
        'records' => $records
    );
    
    // Track maximum values for color scaling
    $max_students = max($max_students, $students);
    $max_subjects = max($max_subjects, $subjects);
    $max_records = max($max_records, $records);
}

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

// Format dates for display
$formatted_from_date = date('M d, Y', strtotime($from_date));
$formatted_to_date = date('M d, Y', strtotime($to_date));

?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Attendance Heatmap</title>
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
        
        .heatmap-container {
            overflow-x: auto;
            margin: 2rem 0;
        }
        
        .heatmap {
            border-collapse: collapse;
            width: 100%;
            min-width: 800px;
        }
        
        .heatmap th {
            background-color: var(--bgcolor-2);
            color: white;
            padding: 1rem;
            text-align: center;
            font-weight: 500;
        }
        
        .heatmap td {
            padding: 1rem;
            text-align: center;
            border: 1px solid #dee2e6;
            position: relative;
        }
        
        .heatmap-cell {
            width: 100%;
            height: 100%;
            display: flex;
            flex-direction: column;
            justify-content: center;
            align-items: center;
            cursor: pointer;
            transition: transform 0.2s;
        }
        
        .heatmap-cell:hover {
            transform: scale(1.1);
            z-index: 1;
        }
        
        .heatmap-cell .value {
            font-weight: 600;
            margin-bottom: 0.5rem;
        }
        
        .heatmap-cell .sub-value {
            font-size: 1.2rem;
            color: #6c757d;
        }
        
        .legend {
            display: flex;
            align-items: center;
            justify-content: center;
            margin: 2rem 0;
        }
        
        .legend-item {
            display: flex;
            align-items: center;
            margin: 0 1rem;
        }
        
        .legend-color {
            width: 2rem;
            height: 2rem;
            margin-right: 0.5rem;
            border-radius: 0.4rem;
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
            <h1><i class="fas fa-calendar-alt mr-3"></i>Attendance Heatmap</h1>
            <p class="lead">Visual representation of attendance patterns by day and hour for the period: <?php echo $formatted_from_date; ?> - <?php echo $formatted_to_date; ?></p>
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
                    <h3>Total Students</h3>
                    <div class="value"><?php echo $stats['total_students']; ?></div>
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
        
        <!-- Heatmap Section -->
        <div class="report-card">
            <h2 class="section-title">Attendance Patterns by Day and Hour</h2>
            
            <!-- Legend -->
            <div class="legend">
                <div class="legend-item">
                    <div class="legend-color" style="background-color: rgba(40, 167, 69, 0.1);"></div>
                    <span>Low Attendance</span>
                </div>
                <div class="legend-item">
                    <div class="legend-color" style="background-color: rgba(40, 167, 69, 0.5);"></div>
                    <span>Medium Attendance</span>
                </div>
                <div class="legend-item">
                    <div class="legend-color" style="background-color: rgba(40, 167, 69, 0.9);"></div>
                    <span>High Attendance</span>
                </div>
            </div>
            
            <div class="heatmap-container">
                <table class="heatmap">
                    <thead>
                        <tr>
                            <th>Hour</th>
                            <?php
                            $days = array('Monday', 'Tuesday', 'Wednesday', 'Thursday', 'Friday');
                            foreach ($days as $day) {
                                echo "<th>$day</th>";
                            }
                            ?>
                        </tr>
                    </thead>
                    <tbody>
                        <?php
                        for ($hour = 8; $hour <= 17; $hour++) {
                            echo "<tr>";
                            echo "<th>" . sprintf("%02d:00", $hour) . "</th>";
                            
                            foreach ($days as $day) {
                                $data = isset($heatmap_data[$day][$hour]) ? $heatmap_data[$day][$hour] : array(
                                    'students' => 0,
                                    'subjects' => 0,
                                    'records' => 0
                                );
                                
                                // Calculate color intensity based on student count
                                $intensity = $max_students > 0 ? ($data['students'] / $max_students) : 0;
                                $color = "rgba(40, 167, 69, " . $intensity . ")";
                                
                                echo "<td>";
                                echo "<div class='heatmap-cell' style='background-color: $color;' 
                                      data-toggle='tooltip' 
                                      title='Students: {$data['students']}&#10;Subjects: {$data['subjects']}&#10;Records: {$data['records']}'>";
                                echo "<div class='value'>{$data['students']}</div>";
                                echo "<div class='sub-value'>{$data['subjects']} subjects</div>";
                                echo "</div>";
                                echo "</td>";
                            }
                            
                            echo "</tr>";
                        }
                        ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <script src="https://code.jquery.com/jquery-3.5.1.slim.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@4.6.0/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        $(function () {
            $('[data-toggle="tooltip"]').tooltip({
                html: true,
                placement: 'top'
            });
        });
    </script>
</body>
</html>
