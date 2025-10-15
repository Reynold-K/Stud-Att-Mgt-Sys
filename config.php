<?php
$conn = mysqli_connect("localhost", "root", "", "cpp");

if (!$conn) {
    die("Connection failed: " . mysqli_connect_error());
}
?>
