<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Student Login</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@4.1.3/dist/css/bootstrap.min.css">
    <script src="https://code.jquery.com/jquery-3.3.1.slim.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/popper.js@1.14.3/dist/umd/popper.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@4.1.3/dist/js/bootstrap.min.js"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.3/css/all.min.css">
    
    <style type="text/css">
        :root {
            --bgcolor-1: #150734;
            --bgcolor-2: #0F2557;
            --maincolor: #000000;
        }
        
        body {
            background-color: #f8f9fa;
        }
        
        .login-container {
            max-width: 450px;
            margin: 100px auto;
            padding: 30px;
            background-color: white;
            border-radius: 10px;
            box-shadow: 0 8px 16px rgba(0,0,0,0.1);
        }
        
        .login-header {
            text-align: center;
            margin-bottom: 30px;
            color: var(--bgcolor-1);
        }
        
        .login-header h2 {
            font-weight: bold;
            margin-bottom: 10px;
        }
        
        .login-header i {
            font-size: 60px;
            margin-bottom: 20px;
            color: var(--bgcolor-2);
        }
        
        .form-group label {
            font-weight: 600;
            color: var(--bgcolor-2);
        }
        
        .login-btn {
            background-color: var(--bgcolor-2);
            border: none;
            padding: 10px;
            font-weight: bold;
            letter-spacing: 1px;
            width: 100%;
            margin-top: 20px;
        }
        
        .login-btn:hover {
            background-color: var(--bgcolor-1);
        }
        
        .alert {
            border-radius: 5px;
        }
    </style>
</head>

<body>
    <nav class="navbar navbar-expand-lg navbar-dark bg-dark">
        <a class="navbar-brand" href="index.php">MMU Biometric Attendance System</a>
        <button class="navbar-toggler" type="button" data-toggle="collapse" data-target="#navbarNav">
            <span class="navbar-toggler-icon"></span>
        </button>
        <div class="collapse navbar-collapse" id="navbarNav">
            <ul class="navbar-nav ml-auto">
                <li class="nav-item">
                    <a class="nav-link" href="index.php">Home</a>
                </li>
                <li class="nav-item">
                    <a class="nav-link" href="1index.php">Admin</a>
                </li>
                <li class="nav-item active">
                    <a class="nav-link" href="student_login.php">Student Login</a>
                </li>
            </ul>
        </div>
    </nav>

    <div class="container">
        <div class="login-container">
            <div class="login-header">
                <i class="fas fa-user-graduate"></i>
                <h2>Student Login</h2>
                <p>Student Attendance Management System</p>
            </div>
            
            <?php
            // Initialize session
            session_start();
            
            // Check if form is submitted
            if ($_SERVER["REQUEST_METHOD"] == "POST") {
                // Include database connection
                require 'config.php';
                
                // Get form data
                $enrollment = mysqli_real_escape_string($conn, $_POST['enrollment']);
                $password = mysqli_real_escape_string($conn, $_POST['password']);
                
                // Query to check if student exists
                $sql = "SELECT * FROM student WHERE enroll_no = ?";
                $stmt = $conn->prepare($sql);
                $stmt->bind_param("s", $enrollment);
                $stmt->execute();
                $result = $stmt->get_result();
                
                if ($result->num_rows == 1) {
                    $student = $result->fetch_assoc();
                    
                    // Verify password (for now using the original logic, but should be updated)
                    $expected_password = 'std' . $student['s_id'];
                    
                    if ($password === $expected_password) {
                        // Login successful, start session and store student information
                        $_SESSION['student_id'] = $student['s_id'];
                        $_SESSION['student_name'] = $student['name'];
                        $_SESSION['enrollment'] = $student['enroll_no'];
                        
                        // Redirect to student dashboard
                        header("Location: student_dashboard.php");
                        exit();
                    } else {
                        // Password is incorrect
                        $error = "Invalid password!";
                    }
                } else {
                    // Enrollment not found
                    $error = "Enrollment number not found.";
                }
                
                $stmt->close();
            }
            ?>
            
            <?php if(isset($error)): ?>
            <div class="alert alert-danger" role="alert">
                <?php echo $error; ?>
            </div>
            <?php endif; ?>
            
            <form action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]); ?>" method="POST">
                <div class="form-group">
                    <label for="enrollmentInput">Enrollment Number</label>
                    <input type="text" class="form-control" id="enrollmentInput" name="enrollment" placeholder="Enter enrollment number" required>
                </div>
                <div class="form-group">
                    <label for="passwordInput">Password</label>
                    <input type="password" class="form-control" id="passwordInput" name="password" placeholder="Enter your password" required>
                </div>
                <button type="submit" class="btn btn-primary login-btn">LOGIN</button>
            </form>
            <div class="text-center mt-3">
                <small><a href="#" class="text-muted">Forgot Password?</a></small>
                </div>
                <div class="text-center mt-2">
                    <small><a href="index.php" class="text-muted">Admin Login</a></small>
                
            </div>
        </div>
    </div>
</body>
</html>
