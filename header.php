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
		<a href="index.php" class="logo">✨ EventSpace</a>
		<div class="links">
			<a href="index.php">Home</a>
			<a href="#">Halls</a>
			<a href="#">About</a>
			<a href="#">Contact</a>
		</div>
		<div class="actions">
			<button onclick="toggleTheme()" class="btn btn-outline" style="padding: 8px 15px;">🌓 Mode</button>
			<?php if ($currentUser): ?>
				<?php if ($currentUser['role'] === 'admin'): ?>
					<a href="admin_dashboard.php" class="btn btn-primary">Admin Panel</a>
				<?php else: ?>
					<a href="my_reservations.php" class="btn btn-primary">My Bookings</a>
				<?php endif; ?>
				<a href="logout.php" class="btn btn-outline">Logout</a>
			<?php else: ?>
				<a href="login.php" class="btn btn-outline">Login</a>
				<a href="register.php" class="btn btn-primary">Register</a>
			<?php endif; ?>
		</div>
	</nav>
	<main>