<?php

require_once 'config.php';
require_once 'function.php';

class ReserveSlot
{
    public $user;
    public $slot;
    public $message = '';
    public $slot_id;

    public function __construct()
    {
        $this->user = requireLogin(database_data::$pdo);
        $this->slot_id = isset($_GET['slot_id']) ? (int)$_GET['slot_id'] : (isset($_POST['slot_id']) ? (int)$_POST['slot_id'] : 0);

        $stmt = database_data::$pdo->prepare("SELECT * FROM time_slots WHERE id = ?");
        $stmt->execute([$this->slot_id]);
        $this->slot = $stmt->fetch();

        if (!$this->slot) {
            $_SESSION['message'] = "<div class='alert error'>Time slot not found.</div>";
            header("Location: index.php");
            exit();
        }
    }

    public function process_reservation()
    {
        if ($_SERVER["REQUEST_METHOD"] == "POST") {
            $note = trim($_POST['note']);
            $csrf = $_POST['csrf_token'] ?? '';
            
            verifyCSRFToken($csrf);

            $slot_datetime = $this->slot['date'] . ' ' . $this->slot['start_time'];
            if (strtotime($slot_datetime) < time()) {
                $this->message = "<div class='alert error'>You cannot reserve a slot in the past.</div>";
                return;
            }

            $stmt = database_data::$pdo->prepare("SELECT id FROM reservations WHERE user_id = ? AND slot_id = ? AND status != 'cancelled'");
            $stmt->execute([$this->user['id'], $this->slot_id]);
            if ($stmt->rowCount() > 0) {
                $this->message = "<div class='alert error'>You have already reserved this time slot.</div>";
                return;
            }

            $remaining = getRemainingCapacity(database_data::$pdo, $this->slot_id, $this->slot['capacity']);
            if ($remaining <= 0) {
                $this->message = "<div class='alert error'>Sorry, this time slot is full.</div>";
                return;
            }

            $stmt = database_data::$pdo->prepare("INSERT INTO reservations (user_id, slot_id, note, status) VALUES (?, ?, ?, 'pending')");
            
            try {
                if ($stmt->execute([$this->user['id'], $this->slot_id, $note])) {
                    $_SESSION['message'] = "<div class='alert success'>Reservation submitted successfully! It is now pending admin approval.</div>";
                    header("Location: index.php");
                    exit();
                }
            } catch (PDOException $e) {
                $this->message = "<div class='alert error'>Error making reservation. You may have already booked this slot.</div>";
            }
        }
    }
}

$manager = new ReserveSlot();
$manager->process_reservation();

include 'header.php';
?>

<div class="container" style="max-width: 600px; margin-top: 60px; margin-bottom: 60px;">
    <div class="event-card" style="padding: 40px;">
        <h2 style="text-align: center; margin-bottom: 20px;">Book Event Hall</h2>
        
        <?php echo $manager->message; ?>

        <div style="background: var(--bg-color); padding: 20px; border-radius: 10px; border: 1px solid var(--border-color); margin-bottom: 30px;">
            <p><strong>📅 Date:</strong> <?php echo htmlspecialchars($manager->slot['date']); ?></p>
            <p><strong>⏰ Time:</strong> <?php echo date('g:i A', strtotime($manager->slot['start_time'])); ?> - <?php echo date('g:i A', strtotime($manager->slot['end_time'])); ?></p>
            <p><strong>👥 Available Seats:</strong> <?php echo getRemainingCapacity(database_data::$pdo, $manager->slot_id, $manager->slot['capacity']); ?> / <?php echo $manager->slot['capacity']; ?></p>
        </div>

        <form action="reserve.php" method="POST">
            <input type="hidden" name="csrf_token" value="<?php echo generateCSRFToken(); ?>">
            <input type="hidden" name="slot_id" value="<?php echo $manager->slot_id; ?>">
            
            <div class="form-group">
                <label>Event Details / Note (Optional):</label>
                <textarea name="note" rows="4" class="form-control" placeholder="E.g., Wedding reception for 50 people..."></textarea>
            </div>

            <button type="submit" class="btn btn-primary" style="width: 100%; margin-top: 10px;" <?php if(getRemainingCapacity(database_data::$pdo, $manager->slot_id, $manager->slot['capacity']) <= 0) echo 'disabled'; ?>>
                Confirm Reservation
            </button>
        </form>
    </div>
</div>

<?php include 'footer.php'; ?>