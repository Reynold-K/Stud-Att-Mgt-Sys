<?php
session_start();

if (!isset($_SESSION['student_id'])) {
    header("Location: student_login.php");
    exit();
}

require 'config.php';

if (!$conn || $conn->connect_error) {
    die("Database connection failed: " . ($conn ? $conn->connect_error : "Connection not established"));
}

$student_id = $_SESSION['student_id'];
$student_name = $_SESSION['student_name'];
$enrollment = $_SESSION['enrollment'];

// Fetch subjects and their associated faculty for the dropdown, excluding subjects with attendance marked today
$subjects_query = "SELECT s.subject_id, s.subject_code, s.subject_name, f.f_id, f.name AS faculty_name 
                   FROM subjects s 
                   LEFT JOIN faculty f ON s.faculty_id = f.f_id 
                   WHERE s.semester = (SELECT semester FROM student WHERE s_id = '$student_id')
                   AND s.subject_id NOT IN (
                       SELECT subject_id 
                       FROM attendance 
                       WHERE student_id = '$student_id' 
                       AND date = CURDATE()
                   )";
$subjects_result = mysqli_query($conn, $subjects_query);
if (!$subjects_result) {
    die("Error fetching subjects: " . mysqli_error($conn));
}

// Fetch subjects for the filter section
$filter_subjects_query = "SELECT DISTINCT subject_id FROM attendance WHERE student_id = '$student_id'";
$filter_subjects_result = mysqli_query($conn, $filter_subjects_query);
if (!$filter_subjects_result) {
    die("Error fetching filter subjects: " . mysqli_error($conn));
}

$filter_subject = isset($_GET['subject']) ? $_GET['subject'] : '';
$filter_month = isset($_GET['month']) ? $_GET['month'] : date('m');
$filter_year = isset($_GET['year']) ? $_GET['year'] : date('Y');

$webauthn_registered = false;
$check_query = "SELECT webauthn_credential FROM student WHERE s_id = '$student_id'";
$check_result = mysqli_query($conn, $check_query);
if ($check_result === false) {
    die("Error executing query: " . mysqli_error($conn));
}
$user = mysqli_fetch_assoc($check_result);
if ($user && $user['webauthn_credential']) {
    $webauthn_registered = true;
}
?>

<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>My Attendance</title>
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
            width: 15rem;
        }

        .mark-attendance {
            width: 80%;
            margin: 3rem auto;
            padding: 2rem;
            border: 2px solid #ccc;
            border-radius: 15px;
            background-color: #f9f9f9;
            text-align: center;
        }

        .mark-attendance select, .mark-attendance button {
            padding: 0.5rem 1rem;
            margin: 0 0.5rem;
            border-radius: 5px;
        }

        .mark-attendance button {
            background-color: var(--bgcolor-2);
            color: white;
            border: none;
            cursor: pointer;
        }

        .filter-section {
            width: 80%;
            margin: 3rem auto;
            padding: 2rem;
            border: 2px solid #ccc;
            border-radius: 15px;
            background-color: #f9f9f9;
        }

        .filter-form {
            display: flex;
            justify-content: space-between;
            align-items: center;
        }

        .filter-form select, .filter-form button {
            padding: 0.5rem;
            margin: 0 0.5rem;
        }

        .filter-form button {
            background-color: var(--bgcolor-2);
            color: white;
            border: none;
            border-radius: 5px;
            cursor: pointer;
        }

        .attendance-records {
            width: 80%;
            margin: 3rem auto;
            padding: 2rem;
            border: 2px solid #ccc;
            border-radius: 15px;
        }

        .time-table-head {
            background-color: var(--bgcolor-2);
            color: white;
        }

        .present {
            background-color: #d4edda;
        }

        .absent {
            background-color: #f8d7da;
        }

        .back-btn {
            text-align: center;
            margin: 3rem 0;
        }

        .back-btn a {
            display: inline-block;
            padding: 1rem 2rem;
            background-color: var(--bgcolor-2);
            color: white;
            text-decoration: none;
            border-radius: 5px;
            font-size: 1.5rem;
        }

        .error-message {
            color: red;
            margin-top: 10px;
        }
    </style>
</head>

<body>
    <nav class="navbar navbar-expand-lg navbar-dark bg-dark">
        <a class="navbar-brand" href="student_dashboard.php">MMU Biometric Attendance System</a>
        <button class="navbar-toggler" type="button" data-toggle="collapse" data-target="#navbarNav">
            <span class="navbar-toggler-icon"></span>
        </button>
        <div class="collapse navbar-collapse" id="navbarNav">
            <ul class="navbar-nav ml-auto">
                <li class="nav-item">
                    <a class="nav-link" href="student_dashboard.php">Dashboard</a>
                </li>
                <li class="nav-item active">
                    <a class="nav-link" href="student_attendance.php">My Attendance</a>
                </li>
                <li class="nav-item">
                    <a class="nav-link" href="student_logout.php">Logout</a>
                </li>
            </ul>
        </div>
    </nav>

    <div class="page-title">
        My Attendance
    </div>

    <div class="mark-attendance">
        <h3>Mark Attendance</h3>
        <div>
            <?php if (!$webauthn_registered): ?>
                <button id="registerBtn">Register with Windows Hello</button>
            <?php else: ?>
                <select id="subjectSelect" name="subject_id">
                    <option value="">Select a Subject</option>
                    <?php
                    while ($subject = mysqli_fetch_assoc($subjects_result)) {
                        $faculty_info = $subject['faculty_name'] ? " (Taught by: {$subject['faculty_name']})" : '';
                        echo "<option value='{$subject['subject_id']}' data-faculty-id='{$subject['f_id']}'>{$subject['subject_code']} - {$subject['subject_name']}{$faculty_info}</option>";
                    }
                    ?>
                </select>
                <button id="markBtn" disabled>Mark Attendance</button>
            <?php endif; ?>
        </div>
        <div id="message" style="margin: 20px;"></div>
        <div id="browserError" class="error-message"></div>
    </div>

    <div class="filter-section">
        <h3>Filter Attendance Records</h3>
        <form class="filter-form" method="get" action="">
            <div>
                <label for="subject">Subject:</label>
                <select name="subject" id="subject">
                    <option value="">All Subjects</option>
                    <?php
                    mysqli_data_seek($filter_subjects_result, 0);
                    while ($subject = mysqli_fetch_assoc($filter_subjects_result)) {
                        $selected = ($filter_subject == $subject['subject_id']) ? 'selected' : '';
                        echo "<option value='{$subject['subject_id']}' $selected>{$subject['subject_id']}</option>";
                    }
                    ?>
                </select>
            </div>
            <div>
                <label for="month">Month:</label>
                <select name="month" id="month">
                    <?php
                    for ($i = 1; $i <= 12; $i++) {
                        $month_name = date('F', mktime(0, 0, 0, $i, 1));
                        $selected = ($filter_month == $i) ? 'selected' : '';
                        echo "<option value='$i' $selected>$month_name</option>";
                    }
                    ?>
                </select>
            </div>
            <div>
                <label for="year">Year:</label>
                <select name="year" id="year">
                    <?php
                    $current_year = date('Y');
                    for ($i = $current_year; $i >= $current_year - 2; $i--) {
                        $selected = ($filter_year == $i) ? 'selected' : '';
                        echo "<option value='$i' $selected>$i</option>";
                    }
                    ?>
                </select>
            </div>
            <button type="submit">Apply Filter</button>
        </form>
    </div>

    <div class="attendance-records">
        <h3>Attendance Records</h3>
        <table class="table table-bordered table-hover mt-4">
            <thead class="time-table-head">
                <tr>
                    <th>Date</th>
                    <th>Day</th>
                    <th>Subject ID</th>
                    <th>Faculty ID</th>
                    <th>Time</th>
                    <th>Status</th>
                </tr>
            </thead>
            <tbody>
                <?php
                $query = "SELECT * FROM attendance WHERE student_id = '$student_id'";
                
                if (!empty($filter_subject)) {
                    $query .= " AND subject_id = '$filter_subject'";
                }
                
                if (!empty($filter_month) && !empty($filter_year)) {
                    $query .= " AND MONTH(date) = '$filter_month' AND YEAR(date) = '$filter_year'";
                }
                
                $query .= " ORDER BY date DESC, attendance_time ASC";
                
                $result = mysqli_query($conn, $query);
                if (!$result) {
                    die("Error fetching attendance records: " . mysqli_error($conn));
                }
                
                if (mysqli_num_rows($result) > 0) {
                    while ($row = mysqli_fetch_assoc($result)) {
                        $day = date('l', strtotime($row['date']));
                        $class = ($row['status'] === 'present') ? 'present' : 'absent';
                        
                        echo "<tr class='$class'>";
                        echo "<td>{$row['date']}</td>";
                        echo "<td>$day</td>";
                        echo "<td>{$row['subject_id']}</td>";
                        echo "<td>".($row['faculty_id'] ?? 'N/A')."</td>";
                        echo "<td>{$row['attendance_time']}</td>";
                        echo "<td>{$row['status']}</td>";
                        echo "</tr>";
                    }
                } else {
                    echo "<tr><td colspan='7'>No attendance records found for the selected filters.</td></tr>";
                }
                ?>
            </tbody>
        </table>
    </div>

    <div class="back-btn">
        <a href="student_dashboard.php">Back to Dashboard</a>
    </div>

    <script src="https://code.jquery.com/jquery-3.3.1.slim.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/popper.js@1.14.3/dist/umd/popper.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@4.1.3/dist/js/bootstrap.min.js"></script>
    <script>
        console.log("Script loaded");

        // Utility functions to encode/decode base64url
        function base64urlToArrayBuffer(base64url) {
            const base64 = base64url.replace(/-/g, '+').replace(/_/g, '/');
            const binary = atob(base64);
            const buffer = new Uint8Array(binary.length);
            for (let i = 0; i < binary.length; i++) {
                buffer[i] = binary.charCodeAt(i);
            }
            return buffer.buffer;
        }

        function arrayBufferToBase64url(buffer) {
            if (!(buffer instanceof ArrayBuffer)) {
                throw new Error('Expected an ArrayBuffer, but got: ' + typeof buffer);
            }
            const binary = String.fromCharCode.apply(null, new Uint8Array(buffer));
            const base64 = btoa(binary);
            return base64.replace(/\+/g, '-').replace(/\//g, '_').replace(/=+$/, '');
        }

        // Enable/disable mark button based on subject selection
        const subjectSelect = document.getElementById('subjectSelect');
        const markBtn = document.getElementById('markBtn');
        if (subjectSelect && markBtn) {
            subjectSelect.addEventListener('change', () => {
                markBtn.disabled = subjectSelect.value === '';
            });
        }

        // Register with Windows Hello
        const registerBtn = document.getElementById('registerBtn');
        if (registerBtn) {
            console.log("Register button found");
            registerBtn.addEventListener('click', async () => {
                const messageDiv = document.getElementById('message');
                messageDiv.innerHTML = 'Registering with Windows Hello...';
                console.log("Register button clicked");

                try {
                    const response = await fetch('webauthn_register.php');
                    if (!response.ok) {
                        throw new Error('Failed to fetch registration options: ' + response.statusText);
                    }
                    const options = await response.json();
                    console.log("Registration options:", options);

                    if (!options.success && options.message) {
                        throw new Error(options.message);
                    }

                    options.challenge = base64urlToArrayBuffer(options.challenge);
                    options.user.id = base64urlToArrayBuffer(options.user.id);

                    const credential = await navigator.credentials.create({
                        publicKey: options
                    });
                    console.log("Credential created:", credential);

                    if (!credential.response || !credential.response.clientDataJSON) {
                        throw new Error('Invalid WebAuthn credential: clientDataJSON missing');
                    }

                    const credentialResponse = {
                        id: credential.id,
                        rawId: arrayBufferToBase64url(credential.rawId),
                        type: credential.type,
                        response: {
                            clientDataJSON: arrayBufferToBase64url(credential.response.clientDataJSON),
                            attestationObject: arrayBufferToBase64url(credential.response.attestationObject)
                        }
                    };
                    console.log("Credential response to send:", credentialResponse);

                    const storeResponse = await fetch('webauthn_authenticate.php', {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json'
                        },
                        body: JSON.stringify(credentialResponse)
                    });
                    if (!storeResponse.ok) {
                        throw new Error('Failed to store credential: ' + storeResponse.statusText);
                    }

                    const storeResult = await storeResponse.json();
                    console.log("Store result:", storeResult);
                    messageDiv.innerHTML = storeResult.message || 'Registration successful! Please reload the page.';
                    if (storeResult.success) {
                        window.location.reload();
                    }
                } catch (error) {
                    console.error("Registration error:", error);
                    messageDiv.innerHTML = 'Registration Error: ' + error.message;
                }
            });
        }

        // Mark attendance with Windows Hello
        if (markBtn) {
            console.log("Mark button found");
            markBtn.addEventListener('click', async () => {
                const messageDiv = document.getElementById('message');
                messageDiv.innerHTML = 'Authenticating with Windows Hello...';
                console.log("Mark button clicked");

                try {
                    const response = await fetch('webauthn_authenticate.php');
                    if (!response.ok) {
                        throw new Error('Failed to fetch authentication options: ' + response.statusText);
                    }
                    const options = await response.json();
                    console.log("Authentication options:", options);

                    if (!options.success && options.message) {
                        throw new Error(options.message);
                    }

                    options.challenge = base64urlToArrayBuffer(options.challenge);
                    options.allowCredentials = options.allowCredentials.map(cred => ({
                        ...cred,
                        id: base64urlToArrayBuffer(cred.id)
                    }));

                    const assertion = await navigator.credentials.get({
                        publicKey: options
                    });
                    console.log("Assertion received:", assertion);
                    console.log("Assertion response structure:", assertion.response);

                    if (!assertion.response) {
                        throw new Error('Invalid WebAuthn response: response object missing');
                    }

                    // Log each field to debug
                    console.log("clientDataJSON exists:", 'clientDataJSON' in assertion.response);
                    console.log("authenticatorData exists:", 'authenticatorData' in assertion.response);
                    console.log("signature exists:", 'signature' in assertion.response);
                    console.log("userHandle exists:", 'userHandle' in assertion.response);

                    if (!assertion.response.clientDataJSON) {
                        throw new Error('Invalid WebAuthn response: clientDataJSON missing');
                    }
                    if (!assertion.response.authenticatorData) {
                        throw new Error('Invalid WebAuthn response: authenticatorData missing');
                    }
                    if (!assertion.response.signature) {
                        throw new Error('Invalid WebAuthn response: signature missing');
                    }

                    const selectedOption = subjectSelect.options[subjectSelect.selectedIndex];
                    const facultyId = selectedOption.getAttribute('data-faculty-id');

                    const assertionResponse = {
                        id: assertion.id,
                        rawId: arrayBufferToBase64url(assertion.rawId),
                        type: assertion.type,
                        subject_id: subjectSelect.value,
                        faculty_id: facultyId || null,
                        response: {
                            clientDataJSON: arrayBufferToBase64url(assertion.response.clientDataJSON),
                            authenticatorData: arrayBufferToBase64url(assertion.response.authenticatorData),
                            signature: arrayBufferToBase64url(assertion.response.signature),
                            userHandle: assertion.response.userHandle ? arrayBufferToBase64url(assertion.response.userHandle) : null
                        }
                    };
                    console.log("Assertion response to send:", assertionResponse);

                    const markResponse = await fetch('mark_attendance_webauthn.php', {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json'
                        },
                        body: JSON.stringify(assertionResponse)
                    });
                    if (!markResponse.ok) {
                        throw new Error('Failed to mark attendance: ' + markResponse.statusText);
                    }

                    const markResult = await markResponse.json();
                    console.log("Mark result:", markResult);
                    messageDiv.innerHTML = markResult.message;
                    if (markResult.success) {
                        window.location.reload();
                    }
                } catch (error) {
                    console.error("Authentication error:", error);
                    messageDiv.innerHTML = 'Authentication Error: ' + error.message;
                    // Prevent further requests if there's an error
                    return;
                }
            });
        }

        // Check WebAuthn support
        document.addEventListener('DOMContentLoaded', () => {
            console.log("DOM loaded");
            const messageDiv = document.getElementById('browserError');
            if (!window.PublicKeyCredential) {
                console.log("WebAuthn not supported");
                messageDiv.innerHTML = 'Error: WebAuthn is not supported in this browser. Please use a compatible browser like Microsoft Edge, Chrome, or Firefox.';
                document.getElementById('registerBtn')?.setAttribute('disabled', 'true');
                document.getElementById('markBtn')?.setAttribute('disabled', 'true');
            }
        });
    </script>
</body>
</html>