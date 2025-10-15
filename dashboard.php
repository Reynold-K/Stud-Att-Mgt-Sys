<!DOCTYPE html>
<html>
<head>
	<meta charset="utf-8">
	<meta name="viewport" content="width=device-width, initial-scale=1">
	<title>DASHBOARD</title>
	<?php require 'partials/_navbar.php' ?>
	<?php require 'config.php' ?>

	<style type="text/css">
		:root {
			--bgcolor-1: #150734;
			--bgcolor-2: #0F2557;
			--maincolor: #000000;
		}

		.page-title {
			margin-left:27rem;
			margin-top:6rem;
			font-size:2.2rem;
			color:var(--bgcolor-1);
			border-bottom: 2px solid var(--bgcolor-1);
			width:10rem;
		}
		.today {
    background-color: white;
    color: var(--bgcolor-1); /* Highlight color for the current day */
    padding: 0.3rem;
    font-weight: bold;
		}


		.calender {
			position: absolute;
			top: 20px;
			right: 20px;
			width: 400px;
			height: auto;
			padding: 1rem;
			font-size: 16px;
			color: white;
			background: #003366;
			border: 2px solid grey;
			box-sizing: border-box;
			z-index: 100;
			text-align: center;
		}

		table {
			width: 100%;
			border-collapse: collapse;
			background-color: #003366; /* Dark blue background */
			color: white;
			font-family: Arial, sans-serif;
		}
		th, td {
			padding: 10px;
			text-align: center;
			border: 1px solid white;
		}
		th {
			background-color: #004080;
		}

		.background {
			margin-top:8rem;
			margin-left:35rem;
			height: 60%;
			width: 55%;
			border:2px solid gray;
		}

		.background img {
			object-fit:cover;
			max-width: 100%;
			max-height: 110%;
		}

		.Footer {
			margin-top:1rem;
			margin-left:38rem;
			text align:left;
		}

		.Footer h2 {
			font-size:4rem;
			color:var(--bgcolor-1);
			font-weight:600;
		}
	</style>
</head>

<body>
	<div class="page-title">
		DASHBOARD
	</div>

	<div class="background">
		<img src="partials\Images\dashboard_bg.jpg">
	</div>

	<div class="Footer">
		<h2>Welcome To Multimedia University of Kenya</h2>
	</div>

	<!-- Calendar here -->
	<div class="calender">
	<?php
$month = date('m');
$year = date('Y');
$days_in_month = cal_days_in_month(CAL_GREGORIAN, $month, $year);
$first_day = date('w', strtotime("$year-$month-01"));
$current_day = date('d'); // Get the current day

echo "<table>";
echo "<tr><th>Sun</th><th>Mon</th><th>Tue</th><th>Wed</th><th>Thu</th><th>Fri</th><th>Sat</th></tr>";
echo "<tr>";

// Empty cells before the first day of the month
for ($i = 0; $i < $first_day; $i++) {
    echo "<td></td>";
}

// Display the days of the month
for ($day = 1; $day <= $days_in_month; $day++) {
    // Check if it's the current day
    if ($day == $current_day) {
        echo "<td class='today'>$day</td>"; // Highlight today's date
    } else {
        echo "<td>$day</td>";
    }

    // Start a new row after Saturday
    if (($day + $first_day) % 7 == 0) {
        echo "</tr><tr>";
    }
}

echo "</tr></table>";
?>

	</div>
</body>
</html>
