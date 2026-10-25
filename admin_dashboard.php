<?php

require_once 'config.php';
require_once 'function.php';

class AdminDashboard
{
	public $admin;
	public $message = '';
		
	public $total_reservations = 0;
	public $pending_reservations = 0;
	public $confirmed_reservations = 0;
	public $total_users = 0;
		
	public $chart_labels = [];
	public $chart_data = [];

	public function __construct()
	{
		$this->admin = requireAdmin(database_data::$pdo);

		if (isset($_SESSION['message'])) {
			$this->message = $_SESSION['message'];
			unset($_SESSION['message']);
		}
	}

	public function load_stats()
	{
		$stmt = database_data::$pdo->query("SELECT COUNT(*) FROM reservations");
		$this->total_reservations = $stmt->fetchColumn();

		$stmt = database_data::$pdo->query("SELECT COUNT(*) FROM reservations WHERE status = 'pending'");
		$this->pending_reservations = $stmt->fetchColumn();

		$stmt = database_data::$pdo->query("SELECT COUNT(*) FROM reservations WHERE status = 'confirmed'");
		$this->confirmed_reservations = $stmt->fetchColumn();

		$stmt = database_data::$pdo->query("SELECT COUNT(*) FROM users WHERE role = 'user'");
		$this->total_users = $stmt->fetchColumn();

		$stmt = database_data::$pdo->query("SELECT status, COUNT(*) as count FROM reservations GROUP BY status");
		$results = $stmt->fetchAll();

		$status_counts = ['pending' => 0, 'confirmed' => 0, 'rejected' => 0, 'cancelled' => 0];

		foreach ($results as $row) {
			$status_counts[$row['status']] = $row['count'];
		}

		$this->chart_labels = array_keys($status_counts);
		$this->chart_data = array_values($status_counts);
	}
}

$dashboard = new AdminDashboard();
$dashboard->load_stats();

include 'header.php';
?>

<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>

<div class="admin-layout">
	<!-- Sidebar Navigation -->
	<div class="sidebar">
		<a href="admin_dashboard.php" class="btn btn-primary">Dashboard</a>
		<a href="admin_reservations.php" class="btn btn-outline">Reservations</a>
		<a href="admin_users.php" class="btn btn-outline">Users</a>
		<a href="admin_slots.php" class="btn btn-outline">Halls</a>
	</div>

	<!-- Main Content -->
	<div class="admin-content">
		<h2 class="section-title">Admin Dashboard</h2>
		<?php echo $dashboard->message; ?>

		<!-- Stats Grid -->
		<div class="grid" style="grid-template-columns: repeat(4, 1fr); gap: 15px; margin-bottom: 30px;">
			<div class="event-card" style="padding: 15px; text-align: center;">
				<h3 style="color: #888; font-size: 12px; text-transform: uppercase; margin-bottom: 5px;">Total Bookings</h3>
				<p style="font-size: 24px; font-weight: 700; color: var(--primary-blue);"><?php echo $dashboard->total_reservations; ?></p>
			</div>
			<div class="event-card" style="padding: 15px; text-align: center;">
				<h3 style="color: #888; font-size: 12px; text-transform: uppercase; margin-bottom: 5px;">Pending</h3>
				<p style="font-size: 24px; font-weight: 700; color: #ffc107;"><?php echo $dashboard->pending_reservations; ?></p>
			</div>
			<div class="event-card" style="padding: 15px; text-align: center;">
				<h3 style="color: #888; font-size: 12px; text-transform: uppercase; margin-bottom: 5px;">Confirmed</h3>
				<p style="font-size: 24px; font-weight: 700; color: #28a745;"><?php echo $dashboard->confirmed_reservations; ?></p>
			</div>
			<div class="event-card" style="padding: 15px; text-align: center;">
				<h3 style="color: #888; font-size: 12px; text-transform: uppercase; margin-bottom: 5px;">Total Users</h3>
				<p style="font-size: 24px; font-weight: 700; color: var(--text-color);"><?php echo $dashboard->total_users; ?></p>
			</div>
		</div>

		<!-- Chart Section -->
		<div class="event-card" style="padding: 20px;">
			<h3 style="margin-bottom: 15px; font-size: 16px;">Reservations by Status</h3>
			<div style="position: relative; height: 250px; width: 100%;">
				<canvas id="statusChart"></canvas>
			</div>
		</div>
	</div>
</div>

<script>
	const labels = <?php echo json_encode($dashboard->chart_labels); ?>;
	const data = <?php echo json_encode($dashboard->chart_data); ?>;
		
	const isDark = document.documentElement.getAttribute('data-theme') === 'dark';
	const textColor = isDark ? '#FFFFFF' : '#666666';
	const gridColor = isDark ? 'rgba(255,255,255,0.1)' : 'rgba(0,0,0,0.1)';

	const ctx = document.getElementById('statusChart').getContext('2d');
	new Chart(ctx, {
		type: 'bar',
		data: {
			labels: labels,
			datasets: [{
				label: 'Number of Reservations',
				data: data,
				backgroundColor: [
					'rgba(255, 193, 7, 0.7)',
					'rgba(40, 167, 69, 0.7)',
					'rgba(220, 53, 69, 0.7)',
					'rgba(108, 117, 125, 0.7)'
				],
				borderColor: [
					'#ffc107', '#28a745', '#dc3545', '#6c757d'
				],
				borderWidth: 1,
				borderRadius: 6,
				barThickness: 50
			}]
		},
		options: {
			responsive: true,
			maintainAspectRatio: false,
			plugins: {
				legend: { display: false },
				tooltip: {
					backgroundColor: isDark ? '#333' : '#fff',
					titleColor: isDark ? '#fff' : '#000',
					bodyColor: isDark ? '#fff' : '#000',
					borderColor: isDark ? '#555' : '#ddd',
					borderWidth: 1
				}
			},
			scales: {
				y: {
					beginAtZero: true,
					ticks: { color: textColor, stepSize: 1 },
					grid: { color: gridColor, drawBorder: false }
				},
				x: {
					ticks: { color: textColor },
					grid: { display: false }
				}
			}
		}
	});
</script>

<?php include 'footer.php'; ?>