<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Semester 1 Present Attendance</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@4.1.3/dist/css/bootstrap.min.css">
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
            width: 25rem;
        }

        html {
            font-size: 62.5%; /* 10px */
        }

        .time-table {
            margin-left: 26rem;
        }

        .timetable-container {
            width: 90%;
            margin: 40px auto;
            background: white;
            padding: 30px;
            border-radius: 10px;
            box-shadow: 0px 0px 15px rgba(0, 0, 0, 0.15);
        }

        .timetable-container h2 {
            color: #003366;
        }

        .timetable-container table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 20px;
        }

        .timetable-container th, .timetable-container td {
            border: 1px solid #000;
            padding: 15px;
            text-align: left;
        }

        .timetable-container th {
            background: #003366;
            color: white;
        }

        .box-2 {
            margin-left: 26rem;
            border: 2px solid gray;
            padding: 1rem;
            margin-top: 5rem;
            width: 80%;
        }

        .at-title {
            border: 2px solid gray;
            height: 4rem;
            width: 100%;
            padding: 0.5rem;
            text-align: center;
        }

        .at-title:hover {
            color: white;
            background-color: var(--bgcolor-2);
            transition: 0.5s;
        }

        .refresh {
            text-align: center;
        }

        .refresh button {
            border: 2px solid;
            color: white;
            margin: 0.2rem;
            font-size: 1.3rem;
            font-weight: 500;
            padding: 0.7rem 4rem;
            border-radius: 0.8rem;
            background-color: var(--bgcolor-2);
        }

        .time-table-head {
            background-color: var(--bgcolor-2);
            color: white;
        }
    </style>
</head>
<body>
    <?php require 'partials/_navbar.php'; ?>

    <div class="page-title">
        Semester 1 Present Attendance
    </div>

    <div class="time-table">
        <div class="timetable-container">
            <h2>Multimedia University of Kenya</h2>
            <h3>Bachelor of Business Information Technology - 4<sup>th</sup> Year 1<sup>st</sup> Semester</h3>
            <h4>DURATION: SEPT-DEC 2024</h4>

            <table>
                <tr>
                    <th>Day</th>
                    <th>7:00 - 10:00 AM</th>
                    <th>10:00 - 1:00 PM</th>
                    <th>1:00 - 4:00 PM</th>
                    <th>4:00 - 7:00 PM</th>
                </tr>
                <tr>
                    <td>Monday</td>
                    <td></td>
                    <td>BAC 2412 ACCT INF SYST(B-01)<br><b>N Mariuki</b></td>
                    <td>BIT 2413 E-COMMERCE(C-04)<br><b>S Odoyo</b></td>
                    <td></td>
                </tr>
                <tr>
                    <td>Tuesday</td>
                    <td></td>
                    <td></td>
                    <td></td>
                    <td>BMM 2411 PROJ MGT(C-05)<br><b>M. Kibs</b></td>
                </tr>
                <tr>
                    <td>Wednesday</td>
                    <td>BMM 2413 INTRO TO PHILOSOPHY(C-01)<br><b>Dr. K Muga</b></td>
                    <td></td>
                    <td></td>
                    <td></td>
                </tr>
                <tr>
                    <td>Thursday</td>
                    <td>BCS 2414 COMP GRAPHICS Theory(B-04)<br><b>J Lasweti</b></td>
                    <td>BCS 2416 Multimedia Systems & Applications(B-01)<br><b>B Okaka</b></td>
                    <td>BCS 2414 COMP GRAPHICS Practical(LAB-J)<br><b>J Lasweti</b></td>
                    <td></td>
                </tr>
                <tr>
                    <td>Friday</td>
                    <td>BCS 2417 Cloud computing Theory(C-05)<br><b>R Omondi</b></td>
                    <td>BCS 2417 Cloud computing Practical(LAB-B)<br><b>R Omondi</b></td>
                    <td>BCS 2418 Systems project(C-05)<br><b>Dr. Ishmael</b></td>
                    <td></td>
                </tr>
            </table>
        </div>
    </div>

    <div class="box-2">
        <div class="at-title">
            <h2>Present Attendance for Semester 1</h2>
        </div>
        <div class="refresh">
            <form action="" method="POST">
                <button type="submit">Refresh</button>
            </form>
        </div>
        <div class="current-attendance-table mt-5 table-hover">
            <table class="table">
                <thead class="time-table-head">
                    <tr>
                        <th>Roll No</th>
                        <th>Enrollment</th>
                        <th>Name</th>
                        <th>Contact</th>
                        <th>Subject</th>
                        <th>Lecturer</th>
                        <th>Date</th>
                        <th>Time</th>
                        <th>Status</th>
                    </tr>
                </thead>
                <tbody>
                    <?php
                    $con = mysqli_connect("localhost", "root", "", "cpp");
                    if (!$con) {
                        die("Connection failed: " . mysqli_connect_error());
                    }

                    // Query to fetch students with "present" attendance in Semester 1
                    $query = "SELECT s.roll_no, s.enroll_no, s.name AS student_name, s.contact, 
                                     sub.subject_name, f.name AS faculty_name, 
                                     a.date, a.attendance_time, a.status 
                              FROM student s 
                              JOIN attendance a ON s.s_id = a.student_id 
                              JOIN subjects sub ON a.subject_id = sub.subject_id 
                              LEFT JOIN faculty f ON sub.faculty_id = f.f_id 
                              WHERE sub.semester = 1 
                              AND a.status = 'present' 
                              ORDER BY a.date DESC, a.attendance_time DESC";
                    $query_run = mysqli_query($con, $query);

                    if (mysqli_num_rows($query_run) > 0) {
                        while ($row = mysqli_fetch_assoc($query_run)) {
                            ?>
                            <tr>
                                <td><?= $row['roll_no'] ?></td>
                                <td><?= $row['enroll_no'] ?></td>
                                <td><?= $row['student_name'] ?></td>
                                <td><?= $row['contact'] ?></td>
                                <td><?= $row['subject_name'] ?></td>
                                <td><?= $row['faculty_name'] ?? 'Not Assigned' ?></td>
                                <td><?= $row['date'] ?></td>
                                <td><?= $row['attendance_time'] ?></td>
                                <td><?= $row['status'] ?></td>
                            </tr>
                            <?php
                        }
                    } else {
                        ?>
                        <tr>
                            <td colspan="9" class="text-center">
                                <h3>No Records Found</h3>
                            </td>
                        </tr>
                        <?php
                    }

                    mysqli_close($con);
                    ?>
                </tbody>
            </table>
        </div>
    </div>

    <script src="https://code.jquery.com/jquery-3.3.1.slim.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/popper.js@1.14.3/dist/umd/popper.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@4.1.3/dist/js/bootstrap.min.js"></script>
</body>
</html>