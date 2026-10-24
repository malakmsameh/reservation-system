<?php

require_once 'config.php';
require_once 'function.php';

class user_login_info
{
	public $email = '';
	public $message = '';
		
	public function login_process()
	{
		if (isset($_SESSION['message'])) {
			$this->message = $_SESSION['message'];
			unset($_SESSION['message']);
		}

		if ($_SERVER["REQUEST_METHOD"] == "POST") {
			$this->email = trim($_POST['email']);
			$password = $_POST['password'];

			if (empty($this->email) || empty($password)) {
				$this->message = "<div class='alert error'>Please fill all inputs</div>";
			} else {
				$user = getUserByEmail(database_data::$pdo, $this->email);

				if ($user && password_verify($password, $user['password'])) {
					if ($user['is_active'] == 0) {
						$this->message = "<div class='alert error'>Account deactivated. Contact admin.</div>";
					} else {
						$_SESSION['user_id'] = $user['id'];
						session_regenerate_id(true);
						
						if ($user['role'] === 'admin') {
							header("Location: admin_dashboard.php");
						} else {
							header("Location: index.php");
						}
						exit();
					}
				} else {
					$this->message = "<div class='alert error'>Invalid email or password</div>";
				}
			}
		}
	}
}

$login_manager = new user_login_info();
$login_manager->login_process(); // Only call it once

include 'header.php';
?>

<div class="container" style="max-width: 450px; margin-top: 80px; margin-bottom: 80px;">
	<div class="event-card" style="padding: 40px;">
		<h2 style="text-align: center; margin-bottom: 30px;">Welcome Back</h2>
		
		<?php echo $login_manager->message; ?>

		<form action="login.php" method="POST">
			<div class="form-group">
				<label>Email :</label>
				<input type="email" name="email" required value="<?php echo htmlspecialchars($login_manager->email); ?>" class="form-control">
			</div>

			<div class="form-group">
				<label>Password :</label>
				<input type="password" name="password" required class="form-control">
			</div>

			<button type="submit" class="btn btn-primary" style="width: 100%; margin-top: 10px;">Login</button>
		</form>

		<br>
		<p style="text-align: center; color: #888;">Don't have an account? <a href="register.php" style="color: var(--primary-blue); font-weight: bold;">Register here</a></p>
	</div>
</div>

<?php include 'footer.php'; ?>