<?php

require_once 'config.php';
require_once 'function.php';

class AdminUsers
{
	public $admin;
	public $message = '';
	public $users = [];

	public function __construct()
	{
		$this->admin = requireAdmin(database_data::$pdo);

		if (isset($_SESSION['message'])) {
			$this->message = $_SESSION['message'];
			unset($_SESSION['message']);
		}
	}

	public function process_status_update()
	{
		if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['toggle_status'])) {
			$user_id = (int)$_POST['user_id'];
			$new_status = (int)$_POST['new_status']; 

			if ($user_id === (int)$this->admin['id']) {
				$this->message = "<div class='alert error'>You cannot deactivate your own account.</div>";
				return;
			}

			$stmt = database_data::$pdo->prepare("UPDATE users SET is_active = ? WHERE id = ? AND role != 'admin'");
			
			if ($stmt->execute([$new_status, $user_id])) {
				$status_text = $new_status == 1 ? 'Activated' : 'Deactivated';
				$this->message = "<div class='alert success'>User successfully " . $status_text . ".</div>";
			} else {
				$this->message = "<div class='alert error'>Failed to update user status.</div>";
			}
		}
	}

	public function load_users()
	{
		$stmt = database_data::$pdo->query("SELECT * FROM users ORDER BY created_at DESC");
		$this->users = $stmt->fetchAll();
	}
}

$manager = new AdminUsers();
$manager->process_status_update();
$manager->load_users();

include 'header.php';
?>

<div class="admin-layout">
	<!-- Sidebar Navigation -->
	<div class="sidebar">
		<a href="admin_dashboard.php" class="btn btn-outline">Dashboard</a>
		<a href="admin_reservations.php" class="btn btn-outline">Reservations</a>
		<a href="admin_users.php" class="btn btn-primary">Users</a>
		<a href="admin_slots.php" class="btn btn-outline">Halls</a>
	</div>

	<!-- Main Content -->
	<div class="admin-content">
		<h2 class="section-title">Manage Users</h2>
		<?php echo $manager->message; ?>

		<div class="event-card" style="padding: 20px; overflow-x: auto;">
			<table style="width: 100%; border-collapse: collapse; min-width: 700px;">
				<thead>
					<tr style="border-bottom: 2px solid var(--border-color); text-align: left;">
						<th style="padding: 15px 10px;">ID</th>
						<th style="padding: 15px 10px;">Name</th>
						<th style="padding: 15px 10px;">Email</th>
						<th style="padding: 15px 10px;">Role</th>
						<th style="padding: 15px 10px;">Status</th>
						<th style="padding: 15px 10px;">Actions</th>
					</tr>
				</thead>
				<tbody>
					<?php if (empty($manager->users)): ?>
						<tr><td colspan="6" style="padding: 20px; text-align: center; color: #888;">No users found.</td></tr>
					<?php else: ?>
						<?php foreach ($manager->users as $user): ?>
							<tr style="border-bottom: 1px solid var(--border-color);">
								<td style="padding: 15px 10px;">#<?php echo $user['id']; ?></td>
								<td style="padding: 15px 10px;"><?php echo htmlspecialchars($user['name']); ?></td>
								<td style="padding: 15px 10px;"><?php echo htmlspecialchars($user['email']); ?></td>
								<td style="padding: 15px 10px;">
									<?php if ($user['role'] === 'admin'): ?>
										<span style="color: var(--primary-blue); font-weight: bold;">Admin</span>
									<?php else: ?>
										User
									<?php endif; ?>
								</td>
								<td style="padding: 15px 10px;">
									<span style="padding: 5px 10px; border-radius: 20px; font-size: 12px; font-weight: bold; 
										<?php echo $user['is_active'] ? 'background: rgba(40, 167, 69, 0.2); color: #28a745;' : 'background: rgba(220, 53, 69, 0.2); color: #dc3545;'; ?>">
										<?php echo $user['is_active'] ? 'Active' : 'Deactivated'; ?>
									</span>
								</td>
								<td style="padding: 15px 10px;">
									<?php if ($user['role'] !== 'admin'): ?>
										<form action="admin_users.php" method="POST" style="display:inline;">
											<input type="hidden" name="user_id" value="<?php echo $user['id']; ?>">
											<input type="hidden" name="new_status" value="<?php echo $user['is_active'] ? 0 : 1; ?>">
											
											<?php if ($user['is_active']): ?>
												<button type="submit" name="toggle_status" class="btn btn-outline" style="padding: 5px 15px; font-size: 12px; border-color: #dc3545; color: #dc3545;" onclick="return confirm('Are you sure you want to deactivate this user?');">Deactivate</button>
											<?php else: ?>
												<button type="submit" name="toggle_status" class="btn btn-primary" style="padding: 5px 15px; font-size: 12px; background: #28a745;">Activate</button>
											<?php endif; ?>
										</form>
									<?php else: ?>
										<span style="color: #888; font-size: 12px;"><em>Cannot modify Admin</em></span>
									<?php endif; ?>
								</td>
							</tr>
						<?php endforeach; ?>
					<?php endif; ?>
				</tbody>
			</table>
		</div>
	</div>
</div>

<?php include 'footer.php'; ?>