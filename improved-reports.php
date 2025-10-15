<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Student Attendance Reports</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@4.0.0/dist/css/bootstrap.min.css" integrity="sha384-Gn5384xqQ1aoWXA+058RXPxPg6fy4IWvTNh0E263XmFcJlSAwiGgFAW/dAiS6JXm" crossorigin="anonymous">

    <script src="https://code.jquery.com/jquery-3.2.1.slim.min.js" integrity="sha384-KJ3o2DKtIkvYIK3UENzmM7KCkRr/rE9/Qpg6aAZGJwFDMVNA/GpGFF93hXpG5KkN" crossorigin="anonymous"></script>
    <script src="https://cdn.jsdelivr.net/npm/popper.js@1.12.9/dist/umd/popper.min.js" integrity="sha384-ApNbgh9B+Y1QKtv3Rn7W3mgPxhU9K/ScQsAP7hUibX39j7fakFPskvXusvfa0b4Q" crossorigin="anonymous"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@4.0.0/dist/js/bootstrap.min.js" integrity="sha384-JZR6Spejh4U02d8jOt6vLEHfe/JQGiRRSQQxSfFWpi1MquVdAyjUar5+76PVCmYl" crossorigin="anonymous"></script>
    
    <!-- TableExport.js for Excel export functionality -->
    <script src="https://cdnjs.cloudflare.com/ajax/libs/TableExport/5.2.0/js/tableexport.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/xlsx/0.16.9/xlsx.core.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/FileSaver.js/2.0.5/FileSaver.min.js"></script>
    
    <style type="text/css">
        :root {
            --bgcolor-1: #150734;
            --bgcolor-2: #0F2557;
            --maincolor: #000000;
        }

        .page-title {
            margin-left: 27rem;
            margin-top: 6rem;
            font-size: 2.2rem;
            color: var(--bgcolor-1);
            border-bottom: 2px solid var(--bgcolor-1);
            width: 8rem;
        }
        
        .box-1 {
            margin-left: 26rem;
            border: 2px solid gray;
            width: 100%;
            margin-top: 3rem;
            height: 4rem;
        }
        
        .box-1 h2 {
            font-size: 1.8rem;
            font-weight: 500;
            padding: 1rem;
            color: var(--bgcolor-1);
        }
        
        .box-1 h2:hover {
            color: white;
            background-color: var(--bgcolor-2);
            padding-left: 5rem;
            transition: 0.5s;
        }

        .container {
            margin-left: 26rem;
        }
        
        .btn-1 {
            margin: auto;
        }
        
        .export {
            margin-top: 2rem;
        }
        
        .export button {
            background-color: #0f2557;
            color: white;
            border: none;
            font-size: 1.5rem;
            margin-top: 1.5rem;
            padding: .6rem 3rem;
            border-radius: 5px;
            cursor: pointer;
        }
        
        /* Mobile responsiveness improvements */
        @media screen and (max-width: 768px) {
            .page-title, .container, .box-1 {
                margin-left: 1rem;
                width: 90%;
            }
        }
    </style>
</head>

<body>
    <?php require 'partials/_navbar.php'?>
    
    <div class="page-title">
        REPORTS
    </div>

    <div class="container">
        <div class="row justify-content-center">
            <div class="col-md-12">
                <div class="card mt-5">
                    <div class="card-header">
                        <h3>Fill Necessary Information to Generate a Report</h3>
                    </div>

                    <div class="card-body">
                        <form action="" method="get">
                            <div class="row">
                                <div class="col-md-3">
                                    <div class="form-group">
                                        <label>Semester</label>
                                        <select name="r-class" id="r-class" class="form-control">
                                            <option value="0">All Semesters</option>
                                            <option value="1">Semester 1</option>
                                            <option value="2">Semester 2</option>
                                        </select>
                                    </div>
                                </div>

                                <div class="col-md-3">
                                    <div class="form-group">
                                        <label>Subject</label>
                                        <select name="r-subject" id="r-subject" class="form-control">
                                            <option value="">Select Subject</option>
                                            <option value="Systems Project">Systems Project</option>
                                            <option value="Computer Security & Cryptography">Computer Security & Cryptography</option>
                                            <option value="Auditing of Information Systems">Auditing of Information Systems</option>
                                            <option value="Data Mining & Business Intelligence">Data Mining & Business Intelligence</option>
                                            <option value="Human Resource Management">Human Resource Management</option>
                                            <option value="Advanced Network Concepts">Advanced Network Concepts</option>
                                            <option value="Information Resource Management">Information Resource Management</option>
                                            <option value="Entrepreneurship & Innovation">Entrepreneurship & Innovation</option>
                                        </select>
                                    </div>
                                </div>

                                <div class="col-md-3">
                                    <div class="form-group">
                                        <label>Date</label>
                                        <input type="date" name="r-date" class="form-control">
                                    </div>
                                </div>

                                <div class="col-md-3">
                                    <div class="form-group">
                                        <label>Time Slot</label>
                                        <select name="r-time" id="r-time" class="form-control">
                                            <option value="">Select Time</option>
                                            <option value="1">8:00 - 10:00</option>
                                            <option value="2">10:00 - 1:00</option>
                                            <option value="3">1:00 - 4:00</option>
                                            <option value="4">4:00 - 7:00</option>
                                        </select>
                                    </div>
                                </div>
                                
                                <div class="btn-1">
                                    <div class="col-md-4">
                                        <div class="form-group">
                                            <button type="submit" class="btn btn-primary">Generate Report</button>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </form>
                    </div>
                </div>
                
                <div class="card mt-5">
                    <div class="card-body">
                        <table class="table table-bordered table-striped" id="reportTable">
                            <thead>
                                <tr>
                                    <th>Sr. No</th>
                                    <th>Roll No</th>
                                    <th>Enrollment</th>
                                    <th>Name</th>   
                                    <th>Contact</th>
                                    <th>Subject</th>
                                    <th>Date</th>
                                    <th>Time</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php
                                // Set timezone
                                date_default_timezone_set('Africa/Nairobi');

                                // Database connection details
                                $servername = "localhost";
                                $username = "root";
                                $password = "";
                                $dbname = "cpp";

                                // Create connection
                                $con = mysqli_connect($servername, $username, $password, $dbname);

                                // Check connection
                                if ($con->connect_error) {
                                    die("Connection failed: " . $con->connect_error);
                                }

                                if(isset($_GET['r-subject']) || isset($_GET['r-date']) || isset($_GET['r-time'])) {
                                    $conditions = array();
                                    $params = array();
                                    
                                    // Build query conditions based on provided filters
                                    if (!empty($_GET['r-class'])) {
                                        $r_class = $_GET['r-class'];
                                        $conditions[] = "student.semester = ?";
                                        $params[] = $r_class;
                                    }
                                    
                                    if (!empty($_GET['r-subject'])) {
                                        $r_subject = $_GET['r-subject'];
                                        $conditions[] = "present_table.subject LIKE ?";
                                        $params[] = "%$r_subject%";
                                    }
                                    
                                    if (!empty($_GET['r-date'])) {
                                        $r_date = $_GET['r-date'];
                                        $new_date = date("d-m-Y", strtotime($r_date));
                                        $conditions[] = "present_table.date = ?";
                                        $params[] = $new_date;
                                    }
                                    
                                    if (!empty($_GET['r-time'])) {
                                        $r_time = $_GET['r-time'];
                                        $conditions[] = "present_table.time = ?";
                                        $params[] = $r_time;
                                    }
                                    
                                    // Construct the WHERE clause if conditions exist
                                    $where_clause = "";
                                    if (!empty($conditions)) {
                                        $where_clause = "WHERE " . implode(" AND ", $conditions);
                                    }
                                    
                                    // Prepare the SQL query
                                    $query = "SELECT student.s_id, student.roll_no, student.enroll_no, student.name, student.contact, 
                                            present_table.subject, present_table.date, present_table.time 
                                            FROM student 
                                            JOIN present_table ON student.s_id = present_table.id 
                                            $where_clause 
                                            ORDER BY student.s_id";
                                    
                                    // Prepare and execute the statement
                                    $stmt = $con->prepare($query);
                                    
                                    if (!empty($params)) {
                                        // Create parameter type string (all strings in this case)
                                        $types = str_repeat("s", count($params));
                                        $stmt->bind_param($types, ...$params);
                                    }
                                    
                                    $stmt->execute();
                                    $result = $stmt->get_result();
                                    
                                    if ($result->num_rows > 0) {
                                        $slot_time = ['8:00 to 10:00', '10:00 to 1:00', '1:00 to 4:00', '4:00 to 7:00'];
                                        $count = 1;
                                        
                                        while ($row = $result->fetch_assoc()) {
                                            $time_slot = $slot_time[$row['time'] - 1];
                                            ?>
                                            <tr>
                                                <td><?= $count++ ?></td>
                                                <td><?= $row['roll_no'] ?></td>
                                                <td><?= $row['enroll_no'] ?></td>
                                                <td><?= $row['name'] ?></td>
                                                <td><?= $row['contact'] ?></td>
                                                <td><?= $row['subject'] ?></td>
                                                <td><?= $row['date'] ?></td>
                                                <td><?= $time_slot ?></td>
                                            </tr>
                                            <?php
                                        }
                                    } else {
                                        ?>
                                        <tr>
                                            <td colspan="8" class="text-center">
                                                <h3>No Records Found!</h3>
                                            </td>
                                        </tr>
                                        <?php
                                    }
                                    
                                    $stmt->close();
                                }
                                
                                // Close the connection
                                $con->close();
                                ?>
                            </tbody>
                        </table>
                    </div>
                </div>

                <?php if(isset($_GET['r-subject']) || isset($_GET['r-date']) || isset($_GET['r-time'])): ?>
                <div class="export">
                    <button id="exportBtn">EXPORT TO EXCEL</button>
                </div>
                <?php endif; ?>
            </div>
        </div>
    </div>

    <script type="text/javascript">
        document.getElementById("exportBtn")?.addEventListener("click", function() {
            var table = document.getElementById("reportTable");
            if (table) {
                var today = new Date();
                var fileName = "attendance_report_" + today.toISOString().slice(0,10);
                
                TableExport(table, {
                    headers: true,
                    footers: true,
                    formats: ["xlsx"],
                    filename: fileName,
                    bootstrap: true,
                    exportButtons: false,
                    position: "bottom",
                    ignoreRows: null,
                    ignoreCols: null,
                    trimWhitespace: true,
                    RTL: false,
                    sheetname: "Attendance Report"
                });
                
                // Trigger click on the xlsx button
                document.querySelector(".xlsx").click();
            } else {
                alert("No table data to export!");
            }
        });
    </script>
</body>
</html>
