<?php
require_once 'config.php';
require_once 'function.php';

// Check if logged in, but don't force it (so they can see the pretty landing page)
$currentUser = getCurrentUser(database_data::$pdo);

// Fetch available slots
$stmt = database_data::$pdo->prepare("SELECT * FROM time_slots WHERE date >= CURDATE() ORDER BY date ASC, start_time ASC");
$stmt->execute();
$slots = $stmt->fetchAll();

// Include the new header
include 'header.php';
?>

<!-- Hero Section -->
<section class="hero">
	<h1>Book The Perfect Space For Your Next Event</h1>
	<p>From weddings to corporate galas, find and reserve the best event halls in seconds.</p>
	<div>
		<a href="#halls" class="btn btn-primary">Explore Halls</a>
		<a href="#" class="btn btn-outline" style="border-color: white; color: white;">Learn More</a>
	</div>
</section>

<!-- Available Slots Section -->
<div class="container" id="halls">
	<h2 class="section-title">Available Event Halls</h2>
		
	<?php if (isset($_SESSION['message'])): ?>
		<?php echo $_SESSION['message']; unset($_SESSION['message']); ?>
	<?php endif; ?>

	<div class="grid">
		<?php if (empty($slots)): ?>
			<p>No available halls right now. Please check back later.</p>
		<?php else: ?>
			<?php foreach ($slots as $slot): 
				$remaining = getRemainingCapacity(database_data::$pdo, $slot['id'], $slot['capacity']);
			?>
				<div class="event-card">
					<!-- Placeholder image for halls -->
					<img src="https://images.unsplash.com/photo-1519167758481-83f550bb49b3?q=80&w=600&auto=format&fit=crop" class="event-img" alt="Event Hall">
					
					<div class="event-info">
						<h3>Grand Event Hall</h3>
						<div class="event-meta">
							<span>📅 <?php echo date('M j, Y', strtotime($slot['date'])); ?></span>
							<span>⏰ <?php echo date('g:i A', strtotime($slot['start_time'])); ?> - <?php echo date('g:i A', strtotime($slot['end_time'])); ?></span>
						</div>
						
						<p style="margin-bottom: 15px; color: #888; font-size: 14px;">
							Capacity: <?php echo $remaining; ?> / <?php echo $slot['capacity']; ?> seats available
						</p>

						<?php if ($remaining > 0): ?>
							<?php if ($currentUser): ?>
								<a href="reserve.php?slot_id=<?php echo $slot['id']; ?>" class="btn btn-primary">Book This Hall</a>
							<?php else: ?>
								<a href="login.php" class="btn btn-primary">Login to Book</a>
							<?php endif; ?>
						<?php else: ?>
							<button class="btn btn-outline" disabled style="width:100%; cursor:not-allowed;">Fully Booked</button>
						<?php endif; ?>
					</div>
				</div>
			<?php endforeach; ?>
		<?php endif; ?>
	</div>
</div>

<?php include 'footer.php'; ?>