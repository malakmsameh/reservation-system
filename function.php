<?php

function isEmailExists($pdo, $email) {
	$stmt = $pdo->prepare("SELECT id FROM users WHERE email = :email");
	$stmt->execute(['email' => $email]);
	return $stmt->rowCount() > 0;
}

function registerUser($pdo, $name, $email, $password) {
	$hashed_password = password_hash($password, PASSWORD_DEFAULT);
	$sql = "INSERT INTO users(name, email, password, role, is_active) VALUES(:name, :email, :password, 'user', 1)";
	$stmt = $pdo->prepare($sql);
	try {
		return $stmt->execute([
			'name' => $name,
			'email' => $email,
			'password' => $hashed_password
		]);
	} catch(PDOException $e) {
		return false;
	}
}

// ADDED: Authentication Helpers
function getUserByEmail($pdo, $email) {
	$stmt = $pdo->prepare("SELECT * FROM users WHERE email = ?");
	$stmt->execute([$email]);
	return $stmt->fetch();
}

function getCurrentUser($pdo) {
	if (empty($_SESSION['user_id'])) {
		return null;
	}
	$stmt = $pdo->prepare("SELECT * FROM users WHERE id = ? AND is_active = 1");
	$stmt->execute([$_SESSION['user_id']]);
	return $stmt->fetch();
}

// ADDED: Authorization Helpers
function requireLogin($pdo) {
	$user = getCurrentUser($pdo);
	if (!$user) {
		$_SESSION['message'] = "<div class='alert error'>Please log in first.</div>";
		header("Location: login.php");
		exit();
	}
	return $user;
}

function requireAdmin($pdo) {
	$user = requireLogin($pdo);
	if ($user['role'] !== 'admin') {
		$_SESSION['message'] = "<div class='alert error'>Access denied. Admins only.</div>";
		header("Location: index.php");
		exit();
	}
	return $user;
}

// ADDED: CSRF Protection
function generateCSRFToken() {
	if (empty($_SESSION['csrf_token'])) {
		$_SESSION['csrf_token'] = bin2hex(random_bytes(32));
	}
	return $_SESSION['csrf_token'];
}

function verifyCSRFToken($token) {
	if (empty($_SESSION['csrf_token']) || $token !== $_SESSION['csrf_token']) {
		die("CSRF token validation failed.");
	}
}

// ADDED: Helper to check remaining capacity for a slot
function getRemainingCapacity($pdo, $slot_id, $capacity) {
	$stmt = $pdo->prepare("SELECT COUNT(*) FROM reservations WHERE slot_id = ? AND status IN ('pending', 'confirmed')");
	$stmt->execute([$slot_id]);
	$taken = $stmt->fetchColumn();
	return $capacity - $taken;
}
?>