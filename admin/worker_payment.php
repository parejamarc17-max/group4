<?php
require_once '../config/auth.php';
require_once '../config/database.php';
checkAuth();

// 1. Kuhaon ang listahan sa workers para sa dropdown
$workers = $pdo->query("SELECT * FROM workers ORDER BY name ASC")->fetchAll();

// 2. Process sa Payment (Insert to Database)
if(isset($_POST['pay'])) {
    try {
        $stmt = $pdo->prepare("INSERT INTO worker_payments (worker_id, amount, payment_date, notes, status) VALUES (?, ?, ?, ?, 'paid')");
        $stmt->execute([
            $_POST['worker_id'],
            $_POST['amount'],
            $_POST['payment_date'],
            $_POST['notes']
        ]);

        // Redirect balik sa page nga naay success flag
        header("Location: worker_payment.php?success=1");
        exit();
    } catch (PDOException $e) {
        $error = "Error: " . $e->getMessage();
    }
}

// 3. Kuhaon ang Recent Payments para i-display sa table
$payments = $pdo->query("
    SELECT wp.*, w.name as worker_name 
    FROM worker_payments wp 
    JOIN workers w ON wp.worker_id = w.id 
    ORDER BY wp.created_at DESC 
    LIMIT 10
")->fetchAll();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Worker Payments</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
</head>
<body class="bg-light">

<div class="container mt-5">
    <div class="row justify-content-center">
        <div class="col-md-8">
            
            <div class="card shadow-sm mb-5">
                <div class="card-header bg-primary text-white">
                    <h4 class="mb-0">Pay Worker</h4>
                </div>
                <div class="card-body">
                    <form method="POST">
                        <div class="mb-3">
                            <label class="form-label">Select Worker</label>
                            <select name="worker_id" class="form-select" required>
                                <option value="">-- Choose Worker --</option>
                                <?php foreach($workers as $w): ?>
                                    <option value="<?= $w['id'] ?>"><?= htmlspecialchars($w['name']) ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>

                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label class="form-label">Amount</label>
                                <div class="input-group">
                                    <span class="input-group-text">₱</span>
                                    <input type="number" step="0.01" name="amount" class="form-control" placeholder="0.00" required>
                                </div>
                            </div>
                            <div class="col-md-6 mb-3">
                                <label class="form-label">Payment Date</label>
                                <input type="date" name="payment_date" class="form-control" value="<?= date('Y-m-d') ?>" required>
                            </div>
                        </div>

                        <div class="mb-3">
                            <label class="form-label">Notes (Optional)</label>
                            <textarea name="notes" class="form-control" rows="2" placeholder="Ex: Overtime pay, Bonus..."></textarea>
                        </div>

                        <div class="d-grid">
                            <button name="pay" class="btn btn-success btn-lg">Submit Payment</button>
                        </div>
                    </form>
                </div>
            </div>

            <div class="card shadow-sm">
                <div class="card-header bg-dark text-white">
                    <h5 class="mb-0">Recent Payments</h5>
                </div>
                <div class="card-body p-0">
                    <table class="table table-hover mb-0">
                        <thead class="table-secondary">
                            <tr>
                                <th>Worker</th>
                                <th>Amount</th>
                                <th>Date</th>
                                <th>Status</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if(empty($payments)): ?>
                                <tr><td colspan="4" class="text-center p-3">No payments recorded yet.</td></tr>
                            <?php else: ?>
                                <?php foreach($payments as $p): ?>
                                    <tr>
                                        <td><?= htmlspecialchars($p['worker_name']) ?></td>
                                        <td><strong>₱<?= number_format($p['amount'], 2) ?></strong></td>
                                        <td><?= date('M d, Y', strtotime($p['payment_date'])) ?></td>
                                        <td><span class="badge bg-success"><?= ucfirst($p['status']) ?></span></td>
                                    </tr>
                                <?php endforeach; ?>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>

        </div>
    </div>
</div>

<?php if(isset($_GET['success'])): ?>
<script>
    Swal.fire({
        title: 'Success!',
        text: 'Ang payment malampuson nga narekord.',
        icon: 'success',
        confirmButtonColor: '#0d6efd'
    });
</script>
<?php endif; ?>

<?php if(isset($error)): ?>
<script>
    Swal.fire({
        title: 'Error!',
        text: '<?= $error ?>',
        icon: 'error'
    });
</script>
<?php endif; ?>

</body>
</html>