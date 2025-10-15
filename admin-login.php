<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Administrator Login</title>
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
                <i class="fas fa-user-shield"></i>
                <h2>Administrator Login</h2>
                <p>Multimedia University of Kenya</p>
            </div>
            
            <?php
            $servername = "localhost";
            $username = "root";
            $password = "";
            $dbname = "cpp";

            // Create connection
            $conn = mysqli_connect($servername, $username, $password, $dbname);
            // Check connection
            if (!$conn) {
                die("Connection failed: " . mysqli_connect_error());
            }

            // Check if form is submitted
            if ($_SERVER["REQUEST_METHOD"] == "POST") {
                // Validate user input
                if (isset($_POST['username']) && isset($_POST['password'])) {
                    $username = $_POST['username'];
                    $password = $_POST['password'];

                    // Check if username and password match database
                    $query = "SELECT * FROM login WHERE username='$username' AND password='$password'";
                    $result = mysqli_query($conn, $query);

                    if (mysqli_num_rows($result) == 1) {
                        // Redirect to protected page
                        header("Location: dashboard.php");
                        exit();
                    } else {
                        // Login unsuccessful, display error message
                        echo '<div class="alert alert-danger" role="alert">';
                        echo 'Invalid username or password.';
                        echo '</div>';
                    }
                }
            }
            mysqli_close($conn);
            ?>
            
            <form action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]); ?>" method="POST">
                <div class="form-group">
                    <label for="usernameInput">Username</label>
                    <input type="text" class="form-control" id="usernameInput" name="username" placeholder="Enter your username" required>
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
            <div class="mt-2">
                    <small><a href="student_login.php" class="text-muted">Student Login</a></small>
            </div>
        </div>
    </div>
    
    <div class="container mt-5">
        <div class="row justify-content-center">
            <div class="col-md-4">
                <div class="card">
                    <div class="card-body text-center">
                        <h5 class="card-title">Student Portal</h5>
                        <p class="card-text">Students can login to view their attendance records</p>
                        <a href="student_login.php" class="btn btn-primary">Student Login</a>
                    </div>
                </div>
            </div>
            <!-- Other cards for faculty, etc. -->
        </div>
    </div>
</body>
</html>