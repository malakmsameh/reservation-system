<?php

require_once 'config.php';
require_once 'function.php';

class RegisterHandler
{
	public $name = '';
	public $email = '';
	public $message = '';

	public function process_registration()
	{
		if ($_SERVER["REQUEST_METHOD"] == "POST") {
			$this->name = trim($_POST['name']);
			$this->email = trim($_POST['email']);
			$password = $_POST['password'];
			$csrf = $_POST['csrf_token'] ?? '';

			verifyCSRFToken($csrf);

			if (empty($this->name) || empty($this->email) || empty($password)) {
				$this->message = "<div class='alert error'>Please fill all inputs.</div>";
				return;
			}

			if (!filter_var($this->email, FILTER_VALIDATE_EMAIL)) {
				$this->message = "<div class='alert error'>Please fill a valid email.</div>";
				return;
			}

			if (strlen($password) < 6) {
				$this->message = "<div class='alert error'>Password must be at least 6 characters.</div>";
				return;
			}

			if (isEmailExists(database_data::$pdo, $this->email)) {
				$this->message = "<div class='alert error'>This email is already used.</div>";
				return;
			}

			if (registerUser(database_data::$pdo, $this->name, $this->email, $password)) {
				$user = getUserByEmail(database_data::$pdo, $this->email);
				$_SESSION['user_id'] = $user['id'];
				session_regenerate_id(true);

				header("Location: index.php");
				exit();
			} else {
				$this->message = "<div class='alert error'>Error registering. Try again later.</div>";
			}
		}
	}
}

$register_manager = new RegisterHandler();
$register_manager->process_registration();

include 'header.php';
?>

<div class="container" style="max-width: 450px; margin-top: 80px; margin-bottom: 80px;">
	<div class="event-card" style="padding: 40px;">
		<h2 style="text-align: center; margin-bottom: 30px;">Create an Account</h2>
		
		<?php echo $register_manager->message; ?>

		<form action="register.php" method="POST">
			<input type="hidden" name="csrf_token" value="<?php echo generateCSRFToken(); ?>">
			
			<div class="form-group">
				<label>Name :</label>
				<input type="text" name="name" required value="<?php echo htmlspecialchars($register_manager->name); ?>" class="form-control">
			</div>

			<div class="form-group">
				<label>Email :</label>
				<input type="email" name="email" required value="<?php echo htmlspecialchars($register_manager->email); ?>" class="form-control">
			</div>

			<div class="form-group">
				<label>Password :</label>
				<input type="password" name="password" required class="form-control">
			</div>

			<button type="submit" class="btn btn-primary" style="width: 100%; margin-top: 10px;">Register</button>
		</form>

		<br>
		<p style="text-align: center; color: #888;">Already have an account? <a href="login.php" style="color: var(--primary-blue); font-weight: bold;">Login here</a></p>
	</div>
</div>

<?php include 'footer.php'; ?>