<?php
require_once 'config.php';
require_once 'function.php';
$currentUser = getCurrentUser(database_data::$pdo);
?>
<!DOCTYPE html>
<html lang="en">
<head>
	<meta charset="UTF-8">
	<meta name="viewport" content="width=device-width, initial-scale=1.0">
	<title>Event Hall Booking</title>
	<link rel="stylesheet" href="assets/style.css">
</head>
<body>
	<nav>
		<a href="index.php" class="logo">EventSpace</a>
		
		<div class="links">
			<?php if (!$currentUser || $currentUser['role'] !== 'admin'): ?>
				<a href="index.php">Home</a>
				<a href="index.php#halls">Halls</a>
				<?php if ($currentUser): ?>
					<a href="my_reservations.php">My Bookings</a>
				<?php endif; ?>
			<?php endif; ?>
		</div>

		<div class="actions">
			<?php if ($currentUser): ?>
				<?php if ($currentUser['role'] === 'admin'): ?>
					<a href="admin_dashboard.php" class="btn btn-primary">Admin Panel</a>
				<?php endif; ?>
				<a href="logout.php" class="btn btn-outline">Logout</a>
			<?php else: ?>
				<a href="login.php" class="btn btn-outline">Login</a>
				<a href="register.php" class="btn btn-primary">Register</a>
			<?php endif; ?>
		</div>
	</nav>
	<main>