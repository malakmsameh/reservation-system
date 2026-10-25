<?php

require_once 'config.php';
require_once 'function.php';

class AdminSlots
{
	public $admin;
	public $message = '';
	public $slots = [];

	public function __construct()
	{
		$this->admin = requireAdmin(database_data::$pdo);
		
		if (isset($_SESSION['message'])) {
			$this->message = $_SESSION['message'];
			unset($_SESSION['message']);
		}
	}

	public function process_actions()
	{
		if ($_SERVER["REQUEST_METHOD"] == "POST") {
			if (isset($_POST['add_slot'])) {
				$date = trim($_POST['date']);
				$start = trim($_POST['start_time']);
				$end = trim($_POST['end_time']);
				$capacity = (int)$_POST['capacity'];

				if (empty($date) || empty($start) || empty($end) || $capacity <= 0) {
					$this->message = "<div class='alert error'>Please fill all fields correctly.</div>";
				} elseif ($end <= $start) {
					$this->message = "<div class='alert error'>End time must be after start time.</div>";
				} else {
					$stmt = database_data::$pdo->prepare("INSERT INTO time_slots (date, start_time, end_time, capacity) VALUES (?, ?, ?, ?)");
					if ($stmt->execute([$date, $start, $end, $capacity])) {
						$this->message = "<div class='alert success'>Event Hall successfully added to the schedule!</div>";
					} else {
						$this->message = "<div class='alert error'>Failed to add time slot.</div>";
					}
				}
			}

			if (isset($_POST['delete_slot'])) {
				$id = (int)$_POST['slot_id'];
				
				$stmt = database_data::$pdo->prepare("SELECT COUNT(*) FROM reservations WHERE slot_id = ? AND status IN ('pending', 'confirmed')");
				$stmt->execute([$id]);
				$active_count = $stmt->fetchColumn();

				if ($active_count > 0) {
					$this->message = "<div class='alert error'>Cannot delete slot. It has active (pending/confirmed) reservations.</div>";
				} else {
					$stmt = database_data::$pdo->prepare("DELETE FROM time_slots WHERE id = ?");
					if ($stmt->execute([$id])) {
						$this->message = "<div class='alert success'>Time slot deleted successfully.</div>";
					}
				}
			}
		}
	}

	public function load_slots()
	{
		$stmt = database_data::$pdo->query("SELECT * FROM time_slots ORDER BY date DESC, start_time DESC");
		$this->slots = $stmt->fetchAll();
	}
}

$manager = new AdminSlots();
$manager->process_actions();
$manager->load_slots();

include 'header.php';
?>

<div class="admin-layout">
	<!-- Sidebar Navigation -->
	<div class="sidebar">
		<a href="admin_dashboard.php" class="btn btn-outline">Dashboard</a>
		<a href="admin_reservations.php" class="btn btn-outline">Reservations</a>
		<a href="admin_users.php" class="btn btn-outline">Users</a>
		<a href="admin_slots.php" class="btn btn-primary">Halls</a>
	</div>

	<!-- Main Content -->
	<div class="admin-content">
		<h2 class="section-title">Manage Event Halls</h2>
		<?php echo $manager->message; ?>

		<div class="grid" style="grid-template-columns: 1fr 2fr; gap: 30px; align-items: start;">
			
			<!-- Add Slot Form -->
			<div class="event-card" style="padding: 30px;">
				<h3 style="margin-bottom: 20px;">Add New Hall</h3>
				<form action="admin_slots.php" method="POST">
					<div class="form-group">
						<label>Date:</label>
						<input type="date" name="date" required class="form-control">
					</div>
					<div class="form-group">
						<label>Start Time:</label>
						<input type="time" name="start_time" required class="form-control">
					</div>
					<div class="form-group">
						<label>End Time:</label>
						<input type="time" name="end_time" required class="form-control">
					</div>
					<div class="form-group">
						<label>Capacity (Seats):</label>
						<input type="number" name="capacity" min="1" required class="form-control">
					</div>
					<button type="submit" name="add_slot" class="btn btn-primary" style="width: 100%; margin-top: 10px;">Add Hall</button>
				</form>
			</div>

			<!-- Existing Slots Table -->
			<div class="event-card" style="padding: 20px; overflow-x: auto;">
				<h3 style="margin-bottom: 20px;">Existing Halls</h3>
				<table style="width: 100%; border-collapse: collapse; min-width: 500px;">
					<thead>
						<tr style="border-bottom: 2px solid var(--border-color); text-align: left;">
							<th style="padding: 15px 10px;">ID</th>
							<th style="padding: 15px 10px;">Date</th>
							<th style="padding: 15px 10px;">Time</th>
							<th style="padding: 15px 10px;">Capacity</th>
							<th style="padding: 15px 10px;">Actions</th>
						</tr>
					</thead>
					<tbody>
						<?php if (empty($manager->slots)): ?>
							<tr><td colspan="5" style="padding: 20px; text-align: center; color: #888;">No halls found.</td></tr>
						<?php else: ?>
							<?php foreach ($manager->slots as $slot): ?>
								<tr style="border-bottom: 1px solid var(--border-color);">
									<td style="padding: 15px 10px;">#<?php echo $slot['id']; ?></td>
									<td style="padding: 15px 10px;"><?php echo htmlspecialchars($slot['date']); ?></td>
									<td style="padding: 15px 10px;">
										<?php echo date('g:i A', strtotime($slot['start_time'])); ?> - <?php echo date('g:i A', strtotime($slot['end_time'])); ?>
									</td>
									<td style="padding: 15px 10px;"><?php echo $slot['capacity']; ?></td>
									<td style="padding: 15px 10px;">
										<form action="admin_slots.php" method="POST" style="display:inline;">
											<input type="hidden" name="slot_id" value="<?php echo $slot['id']; ?>">
											<button type="submit" name="delete_slot" class="btn btn-outline" style="padding: 5px 15px; font-size: 12px; border-color: #dc3545; color: #dc3545;" onclick="return confirm('Are you sure you want to delete this slot?');">Delete</button>
										</form>
									</td>
								</tr>
							<?php endforeach; ?>
						<?php endif; ?>
					</tbody>
				</table>
			</div>

		</div>
	</div>
</div>

<?php include 'footer.php'; ?>