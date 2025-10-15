<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Faculty Login</title>
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
    <div class="container">
        <div class="login-container">
            <div class="login-header">
                <i class="fas fa-user-tie"></i>
                <h2>Faculty Login</h2>
                <p>Student Attendance Management System</p>
            </div>
            
            <?php
            // Initialize session
            session_start();
            
            // Check if form is submitted
            if ($_SERVER["REQUEST_METHOD"] == "POST") {
                // Database connection
                $servername = "localhost";
                $username = "root";
                $password = "";
                $dbname = "cpp";
                
                // Create connection
                $conn = new mysqli($servername, $username, $password, $dbname);
                
                // Check connection
                if ($conn->connect_error) {
                    die("Connection failed: " . $conn->connect_error);
                }
                
                // Get form data
                $email = $_POST['email'];
                $password = $_POST['password'];
                
                // Query to check if faculty exists
                $sql = "SELECT * FROM faculty WHERE email = ?";
                $stmt = $conn->prepare($sql);
                $stmt->bind_param("s", $email);
                $stmt->execute();
                $result = $stmt->get_result();
                
                if ($result->num_rows == 1) {
                    $row = $result->fetch_assoc();
                    
                    // Verify password
                    if (password_verify($password, $row['password']) || $password === $row['password']) {
                        // Password is correct, start a new session
                        $_SESSION['faculty_id'] = $row['f_id'];
                        $_SESSION['faculty_name'] = $row['name'];
                        $_SESSION['email'] = $row['email'];
                        $_SESSION['department'] = $row['department'];
                        
                        // Redirect to faculty dashboard
                        header("Location: faculty.php");
                        exit();
                    } else {
                        // Password is incorrect
                        $error = "Invalid password!";
                    }
                } else {
                    // Email not found
                    $error = "Email not found!";
                }
                
                $stmt->close();
                $conn->close();
            }
            ?>
            
            <?php if(isset($error)): ?>
            <div class="alert alert-danger" role="alert">
                <?php echo $error; ?>
            </div>
            <?php endif; ?>
            
            <form action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]); ?>" method="POST">
                <div class="form-group">
                    <label for="emailInput">Email Address</label>
                    <input type="email" class="form-control" id="emailInput" name="email" placeholder="Enter your email" required>
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
        </div>
    </div>
</body>
</html>
