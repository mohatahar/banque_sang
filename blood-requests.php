<?php
session_start();
require_once 'db.php';

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit;
}

// Handle request actions
$error = '';
$success = '';

// Process request status update
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    try {
        if (isset($_POST['action']) && $_POST['action'] == 'update_status') {
            $request_id = intval($_POST['request_id']);
            $status = trim($_POST['status']);

            $stmt = $pdo->prepare("UPDATE blood_requests SET status = ? WHERE id = ?");
            $stmt->execute([$status, $request_id]);

            $success = 'Statut de la demande mis à jour';
        }
    } catch (PDOException $e) {
        $error = 'Erreur lors de la mise à jour : ' . $e->getMessage();
    }
}

// Fetch blood requests
try {
    $stmt = $pdo->query("SELECT * FROM blood_requests ORDER BY request_date DESC");
    $requests = $stmt->fetchAll();
} catch (PDOException $e) {
    $requests = [];
    $error = 'Impossible de récupérer les demandes';
}

$blood_types = ['A+', 'A-', 'B+', 'B-', 'AB+', 'AB-', 'O+', 'O-'];
$statuses = ['pending', 'fulfilled', 'rejected'];
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <title>Demandes de Sang</title>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <style>
        :root {
            --primary: #b22222;
            --secondary: #dc143c;
            --background: #0f172a;
            --card: rgba(30, 41, 59, 0.7);
            --text: #f8fafc;
            --border: rgba(178, 34, 34, 0.2);
        }

        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: 'Inter', sans-serif;
            background: var(--background);
            color: var(--text);
            padding: 2rem;
        }

        .container {
            max-width: 1200px;
            margin: 0 auto;
            background: var(--card);
            border-radius: 1rem;
            padding: 2rem;
            backdrop-filter: blur(10px);
        }

        .table {
            width: 100%;
            border-collapse: separate;
            border-spacing: 0;
        }

        .table th, .table td {
            padding: 1rem;
            text-align: left;
            border-bottom: 1px solid var(--border);
            color: var(--text);
        }

        .table th {
            background: rgba(220, 20, 60, 0.1);
            font-weight: bold;
        }

        .status-badge {
            padding: 0.25rem 0.5rem;
            border-radius: 0.25rem;
            font-size: 0.75rem;
            text-transform: uppercase;
        }

        .status-pending {
            background: rgba(255, 165, 0, 0.2);
            color: orange;
        }

        .status-fulfilled {
            background: rgba(34, 197, 94, 0.2);
            color: green;
        }

        .status-rejected {
            background: rgba(239, 68, 68, 0.2);
            color: red;
        }

        .form-group {
            margin-bottom: 1rem;
        }

        .btn {
            background: linear-gradient(to right, var(--primary), var(--secondary));
            color: white;
            border: none;
            padding: 0.5rem 1rem;
            border-radius: 0.5rem;
            cursor: pointer;
        }

        .error, .success {
            padding: 1rem;
            margin-bottom: 1rem;
            border-radius: 0.5rem;
        }

        .error {
            background: rgba(239, 68, 68, 0.1);
            color: #fecaca;
        }

        .success {
            background: rgba(34, 197, 94, 0.1);
            color: #bbf7d0;
        }
    </style>
</head>
<body>
    <div class="container">
        <h2 class="mb-4">
            <i class="fas fa-medkit mr-2"></i>
            Demandes de Sang
        </h2>

        <?php if ($error): ?>
            <div class="error">
                <i class="fas fa-exclamation-circle mr-2"></i>
                <?php echo htmlspecialchars($error); ?>
            </div>
        <?php endif; ?>

        <?php if ($success): ?>
            <div class="success">
                <i class="fas fa-check-circle mr-2"></i>
                <?php echo htmlspecialchars($success); ?>
            </div>
        <?php endif; ?>

        <table class="table">
            <thead>
                <tr>
                    <th>Patient</th>
                    <th>Type de Sang</th>
                    <th>Quantité</th>
                    <th>Date</th>
                    <th>Statut</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($requests as $request): ?>
                    <tr>
                        <td><?php echo htmlspecialchars($request['patient_name']); ?></td>
                        <td><?php echo htmlspecialchars($request['blood_type']); ?></td>
                        <td><?php echo number_format($request['quantity'], 2); ?> ml</td>
                        <td><?php echo date('d/m/Y', strtotime($request['request_date'])); ?></td>
                        <td>
                            <span class="status-badge status-<?php echo htmlspecialchars($request['status']); ?>">
                                <?php echo htmlspecialchars($request['status']); ?>
                            </span>
                        </td>
                        <td>
                            <form method="POST" action="">
                                <input type="hidden" name="action" value="update_status">
                                <input type="hidden" name="request_id" value="<?php echo $request['id']; ?>">
                                <select name="status" onchange="this.form.submit()" class="btn">
                                    <?php foreach ($statuses as $status): ?>
                                        <option 
                                            value="<?php echo $status; ?>"
                                            <?php echo ($request['status'] == $status) ? 'selected' : ''; ?>
                                        >
                                            <?php echo ucfirst($status); ?>
                                        </option>
                                    <?php endforeach; ?>
                                </select>
                            </form>
                        </td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
</body>
</html>