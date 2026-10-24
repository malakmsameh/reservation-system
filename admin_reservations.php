<?php

require_once 'config.php';
require_once 'function.php';

class AdminReservations
{
	public $admin;
	public $message = '';
	public $reservations = [];
		
	public $search = '';
	public $status_filter = '';

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
		if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['update_status'])) {
			$res_id = (int)$_POST['reservation_id'];
			$new_status = $_POST['new_status'];

			if (in_array($new_status, ['confirmed', 'rejected', 'cancelled'])) {
				$stmt = database_data::$pdo->prepare("UPDATE reservations SET status = ? WHERE id = ?");
				if ($stmt->execute([$new_status, $res_id])) {
					$this->message = "<div class='alert success'>Reservation marked as " . htmlspecialchars($new_status) . ".</div>";
				}
			} else {
				$this->message = "<div class='alert error'>Invalid status change.</div>";
			}
		}
	}

	public function load_reservations()
	{
		$this->search = isset($_GET['search']) ? trim($_GET['search']) : '';
		$this->status_filter = isset($_GET['status']) ? trim($_GET['status']) : '';

		$sql = "SELECT r.id, r.status, r.note, r.created_at, 
					   u.name AS user_name, u.email AS user_email, 
					   s.date, s.start_time, s.end_time 
				FROM reservations r 
				JOIN users u ON r.user_id = u.id 
				JOIN time_slots s ON r.slot_id = s.id 
				WHERE 1=1";
		
		$params = [];

		if (!empty($this->search)) {
			$sql .= " AND (u.name LIKE ? OR u.email LIKE ?)";
			$params[] = "%" . $this->search . "%";
			$params[] = "%" . $this->search . "%";
		}

		if (!empty($this->status_filter)) {
			$sql .= " AND r.status = ?";
			$params[] = $this->status_filter;
		}

		$sql .= " ORDER BY r.created_at DESC";

		$stmt = database_data::$pdo->prepare($sql);
		$stmt->execute($params);
		$this->reservations = $stmt->fetchAll();
	}
}

$manager = new AdminReservations();
$manager->process_status_update();
$manager->load_reservations();

include 'header.php';
?>

<div class="container" style="margin-top: 10px; margin-bottom: 50px;">
		<!-- Admin Navigation Tabs -->
		<!-- Admin Navigation Tabs -->
	<div style="display: flex; gap: 10px; margin-bottom: 20px; border-bottom: 1px solid var(--border-color); padding-bottom: 15px;">
		<a href="admin_dashboard.php" class="btn btn-outline">Dashboard</a>
		<a href="admin_reservations.php" class="btn btn-primary">Reservations</a> <!-- ACTIVE -->
		<a href="admin_users.php" class="btn btn-outline">Users</a>
		<a href="admin_slots.php" class="btn btn-outline">Halls</a>
	</div>
	<h2 class="section-title">Manage Reservations</h2>
	<?php echo $manager->message; ?>

	<!-- Search and Filter Form -->
	<!-- <div class="event-card" style="padding: 20px; margin-bottom: 30px; display: flex; gap: 15px; align-items: center; flex-wrap: wrap;">
		<form action="admin_reservations.php" method="GET" style="display: flex; gap: 15px; width: 100%; flex-wrap: wrap;">
			<input type="text" name="search" placeholder="Search name or email..." value="<?php echo htmlspecialchars($manager->search); ?>" class="form-control" style="max-width: 300px;">
			
			<select name="status" class="form-control" style="max-width: 200px;">
				<option value="">All Statuses</option>
				<option value="pending" <?php if($manager->status_filter == 'pending') echo 'selected'; ?>>Pending</option>
				<option value="confirmed" <?php if($manager->status_filter == 'confirmed') echo 'selected'; ?>>Confirmed</option>
				<option value="rejected" <?php if($manager->status_filter == 'rejected') echo 'selected'; ?>>Rejected</option>
				<option value="cancelled" <?php if($manager->status_filter == 'cancelled') echo 'selected'; ?>>Cancelled</option>
			</select>
			
			<button type="submit" class="btn btn-primary" style="padding: 10px 20px;">Search / Filter</button>
			<a href="admin_reservations.php" class="btn btn-outline" style="padding: 10px 20px;">Clear</a>
		</form>
	</div> -->
	<!-- Search and Filter Form -->
	<div class="event-card" style="padding: 20px; margin-bottom: 30px;">
		<form action="admin_reservations.php" method="GET" style="display: flex; gap: 10px; align-items: center; flex-wrap: wrap;">
			
			<input type="text" name="search" placeholder="Search name or email..." value="<?php echo htmlspecialchars($manager->search); ?>" class="form-control" style="flex: 1; min-width: 200px; max-width: 300px;">
			
			<select name="status" class="form-control" style="width: 180px;">
				<option value="">All Statuses</option>
				<option value="pending" <?php if($manager->status_filter == 'pending') echo 'selected'; ?>>Pending</option>
				<option value="confirmed" <?php if($manager->status_filter == 'confirmed') echo 'selected'; ?>>Confirmed</option>
				<option value="rejected" <?php if($manager->status_filter == 'rejected') echo 'selected'; ?>>Rejected</option>
				<option value="cancelled" <?php if($manager->status_filter == 'cancelled') echo 'selected'; ?>>Cancelled</option>
			</select>
			
			<button type="submit" class="btn btn-primary">Search</button>
			<a href="admin_reservations.php" class="btn btn-outline">Clear</a>
			
		</form>
	</div>
	<div class="event-card" style="padding: 20px; overflow-x: auto;">
		<table style="width: 100%; border-collapse: collapse; min-width: 800px;">
			<thead>
				<tr style="border-bottom: 2px solid var(--border-color); text-align: left;">
					<th style="padding: 15px 10px;">ID</th>
					<th style="padding: 15px 10px;">User</th>
					<th style="padding: 15px 10px;">Date & Time</th>
					<th style="padding: 15px 10px;">Note</th>
					<th style="padding: 15px 10px;">Status</th>
					<th style="padding: 15px 10px;">Actions</th>
				</tr>
			</thead>
			<tbody>
				<?php if (empty($manager->reservations)): ?>
					<tr><td colspan="6" style="padding: 20px; text-align: center; color: #888;">No reservations found.</td></tr>
				<?php else: ?>
					<?php foreach ($manager->reservations as $res): ?>
						<tr style="border-bottom: 1px solid var(--border-color);">
							<td style="padding: 15px 10px;">#<?php echo $res['id']; ?></td>
							<td style="padding: 15px 10px;">
								<strong><?php echo htmlspecialchars($res['user_name']); ?></strong><br>
								<small style="color: #888;"><?php echo htmlspecialchars($res['user_email']); ?></small>
							</td>
							<td style="padding: 15px 10px;">
								<?php echo htmlspecialchars($res['date']); ?><br>
								<small style="color: #888;"><?php echo date('g:i A', strtotime($res['start_time'])); ?> - <?php echo date('g:i A', strtotime($res['end_time'])); ?></small>
							</td>
							<td style="padding: 15px 10px; max-width: 200px;"><?php echo htmlspecialchars($res['note']); ?></td>
							<td style="padding: 15px 10px;">
								<span class="status-<?php echo $res['status']; ?>" style="padding: 5px 10px; border-radius: 20px; font-size: 12px; font-weight: bold;">
									<?php echo ucfirst($res['status']); ?>
								</span>
							</td>
							<td style="padding: 15px 10px;">
								<?php if ($res['status'] == 'pending'): ?>
									<form action="admin_reservations.php" method="POST" style="display:inline;">
										<input type="hidden" name="reservation_id" value="<?php echo $res['id']; ?>">
										<input type="hidden" name="new_status" value="confirmed">
										<button type="submit" name="update_status" class="btn btn-primary" style="padding: 5px 15px; font-size: 12px; background: #28a745;">Confirm</button>
									</form>
									<form action="admin_reservations.php" method="POST" style="display:inline;">
										<input type="hidden" name="reservation_id" value="<?php echo $res['id']; ?>">
										<input type="hidden" name="new_status" value="rejected">
										<button type="submit" name="update_status" class="btn btn-outline" style="padding: 5px 15px; font-size: 12px; border-color: #dc3545; color: #dc3545;">Reject</button>
									</form>
								<?php elseif ($res['status'] == 'confirmed'): ?>
									<form action="admin_reservations.php" method="POST" style="display:inline;">
										<input type="hidden" name="reservation_id" value="<?php echo $res['id']; ?>">
										<input type="hidden" name="new_status" value="cancelled">
										<button type="submit" name="update_status" class="btn btn-outline" style="padding: 5px 15px; font-size: 12px;" onclick="return confirm('Cancel this confirmed reservation?');">Cancel</button>
									</form>
								<?php else: ?>
									<span style="color: #888;">-</span>
								<?php endif; ?>
							</td>
						</tr>
					<?php endforeach; ?>
				<?php endif; ?>
			</tbody>
		</table>
	</div>
</div>


<?php include 'footer.php'; ?>