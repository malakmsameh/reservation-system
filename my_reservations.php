<?php

require_once 'config.php';
require_once 'function.php';

class MyReservations
{
	public $user;
	public $reservations = [];
	public $message = '';

	public function __construct()
	{
		$this->user = requireLogin(database_data::$pdo);

		if (isset($_SESSION['message'])) {
			$this->message = $_SESSION['message'];
			unset($_SESSION['message']);
		}
	}

	public function process_cancellation()
	{
		if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['cancel_id'])) {
			$reservation_id = (int)$_POST['cancel_id'];
			
			$stmt = database_data::$pdo->prepare("SELECT r.*, s.date, s.start_time FROM reservations r JOIN time_slots s ON r.slot_id = s.id WHERE r.id = ? AND r.user_id = ? AND r.status IN ('pending', 'confirmed')");
			$stmt->execute([$reservation_id, $this->user['id']]);
			$res = $stmt->fetch();

			if ($res) {
				$slot_datetime = $res['date'] . ' ' . $res['start_time'];
				if (strtotime($slot_datetime) < time()) {
					$this->message = "<div class='alert error'>You cannot cancel a reservation after the slot has started.</div>";
				} else {
					$update = database_data::$pdo->prepare("UPDATE reservations SET status = 'cancelled' WHERE id = ?");
					if ($update->execute([$reservation_id])) {
						$this->message = "<div class='alert success'>Reservation cancelled successfully.</div>";
					}
				}
			} else {
				$this->message = "<div class='alert error'>Reservation not found or cannot be cancelled.</div>";
			}
		}
	}

	public function load_reservations()
	{
		$stmt = database_data::$pdo->prepare("
			SELECT r.id, r.status, r.note, s.date, s.start_time, s.end_time 
			FROM reservations r 
			JOIN time_slots s ON r.slot_id = s.id 
			WHERE r.user_id = ? 
			ORDER BY s.date DESC, s.start_time DESC
		");
		$stmt->execute([$this->user['id']]);
		$this->reservations = $stmt->fetchAll();
	}
}

$manager = new MyReservations();
$manager->process_cancellation();
$manager->load_reservations();

include 'header.php';
?>

<div class="container" style="margin-top: 50px; margin-bottom: 50px;">
	<h2 class="section-title">My Bookings</h2>
	<?php echo $manager->message; ?>

	<div class="event-card" style="padding: 20px; overflow-x: auto;">
		<table style="width: 100%; border-collapse: collapse; min-width: 600px;">
			<thead>
				<tr style="border-bottom: 2px solid var(--border-color); text-align: left;">
					<th style="padding: 15px 10px;">Date</th>
					<th style="padding: 15px 10px;">Time</th>
					<th style="padding: 15px 10px;">Note</th>
					<th style="padding: 15px 10px;">Status</th>
					<th style="padding: 15px 10px;">Action</th>
				</tr>
			</thead>
			<tbody>
				<?php if (empty($manager->reservations)): ?>
					<tr><td colspan="5" style="padding: 20px; text-align: center; color: #888;">You have no reservations yet.</td></tr>
				<?php else: ?>
					<?php foreach ($manager->reservations as $res): ?>
						<tr style="border-bottom: 1px solid var(--border-color);">
							<td style="padding: 15px 10px;"><?php echo htmlspecialchars($res['date']); ?></td>
							<td style="padding: 15px 10px;"><?php echo date('g:i A', strtotime($res['start_time'])); ?> - <?php echo date('g:i A', strtotime($res['end_time'])); ?></td>
							<td style="padding: 15px 10px; max-width: 200px;"><?php echo htmlspecialchars($res['note']); ?></td>
							<td style="padding: 15px 10px;">
								<span class="status-<?php echo $res['status']; ?>" style="padding: 5px 10px; border-radius: 20px; font-size: 12px; font-weight: bold;">
									<?php echo ucfirst($res['status']); ?>
								</span>
							</td>
							<td style="padding: 15px 10px;">
								<?php if (in_array($res['status'], ['pending', 'confirmed'])): ?>
									<form action="my_reservations.php" method="POST" style="display:inline;">
										<input type="hidden" name="cancel_id" value="<?php echo $res['id']; ?>">
										<button type="submit" class="btn btn-outline" style="padding: 5px 15px; font-size: 12px; border-color: #dc3545; color: #dc3545;" onclick="return confirm('Are you sure you want to cancel this reservation?');">Cancel</button>
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