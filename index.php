<!DOCTYPE html>
<html>
<head>
    <title>MMU Login Portal</title>
    <!-- Link to Roboto Font -->
    <link href="https://fonts.googleapis.com/css2?family=Roboto&display=swap" rel="stylesheet">

    <style>
        body {
            font-family: 'Roboto', sans-serif;
            background-color: #f0f2f5;
            display: flex;
            align-items: center;
            justify-content: center;
            height: 100vh;
            margin: 0;
        }

        .login-container {
            background-color: white;
            padding: 40px;
            border-radius: 12px;
            box-shadow: 0px 0px 10px rgba(0,0,0,0.1);
            text-align: center;
            width: 300px;
        }

        h2 {
            margin-bottom: 20px;
            color: #333;
        }

        select, input[type="submit"] {
            width: 100%;
            padding: 10px;
            margin-top: 10px;
            border-radius: 8px;
            border: 1px solid #ccc;
            font-size: 16px;
        }

        input[type="submit"] {
            background-color: #007bff;
            color: white;
            border: none;
            cursor: pointer;
        }

        input[type="submit"]:hover {
            background-color: #0056b3;
        }

        p {
            margin-bottom: 10px;
            color: #555;
        }
    </style>
</head>
<body>
    <div class="login-container">
        <h2>Welcome to Multimedia University</h2>
        <p>Select your login category</p>
        
        <form method="post" action="">
            <select name="role" required>
                <option value="">-- Select Role --</option>
                <option value="admin">Admin</option>
                <option value="student">Student</option>
                <option value="faculty">Faculty</option>
            </select>
            <input type="submit" name="login" value="Go to Login">
        </form>

        <?php
        if (isset($_POST['login'])) {
            $role = $_POST['role'];

            // Redirect based on selected role
            if ($role == "admin") {
                header("Location: 1index.php");
                exit();
            } elseif ($role == "student") {
                header("Location: student_login.php");
                exit();
            } elseif ($role == "faculty") {
                header("Location: faculty-login.php");
                exit();
            } else {
                echo "<p style='color: red;'>Invalid role selected.</p>";
            }
        }
        ?>
    </div>
</body>
</html>