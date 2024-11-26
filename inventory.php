<?php
session_start();
require_once 'db.php';

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit;
}

// Fetch blood inventory details
try {
    $inventory_query = $pdo->query("
        SELECT 
            blood_type, 
            SUM(quantity) as total_quantity, 
            MIN(expiration_date) as earliest_expiration,
            COUNT(*) as unique_donations
        FROM blood_inventory 
        GROUP BY blood_type 
        ORDER BY total_quantity DESC
    ");
    $inventory = $inventory_query->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    $inventory = [];
}
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <title>Stock de Sang - Banque de Sang</title>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <style>
        :root {
            --primary: #b22222;
            --primary-dark: #8b0000;
            --secondary: #dc143c;
            --text: #f8fafc;
            --text-muted: #cbd5e1;
            --background: #0f172a;
            --card: rgba(30, 41, 59, 0.7);
            --border: rgba(178, 34, 34, 0.2);
        }

        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: 'Inter', -apple-system, BlinkMacSystemFont, sans-serif;
            background: var(--background);
            color: var(--text);
            line-height: 1.6;
        }

        .inventory-container {
            max-width: 1200px;
            margin: 0 auto;
            padding: 2rem;
        }

        .header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 2rem;
            background: var(--card);
            padding: 1rem;
            border-radius: 1rem;
            backdrop-filter: blur(10px);
        }

        .inventory-table {
            width: 100%;
            border-collapse: separate;
            border-spacing: 0 1rem;
        }

        .inventory-table th, .inventory-table td {
            background: var(--card);
            padding: 1rem;
            text-align: center;
            border: 1px solid var(--border);
        }

        .inventory-table th {
            text-transform: uppercase;
            color: var(--text-muted);
            font-size: 0.875rem;
        }

        .inventory-table tr:hover {
            transform: translateY(-5px);
            transition: transform 0.3s ease;
        }

        .status-low {
            color: #ff4500;
            font-weight: bold;
        }

        .status-good {
            color: #32cd32;
        }

        .action-btn {
            background: linear-gradient(to right, var(--primary), var(--secondary));
            color: white;
            border: none;
            padding: 0.5rem 1rem;
            border-radius: 0.5rem;
            text-decoration: none;
            display: inline-block;
            margin-bottom: 1rem;
        }

        .inventory-summary {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
            gap: 1rem;
            margin-bottom: 2rem;
        }

        .summary-card {
            background: var(--card);
            border-radius: 1rem;
            padding: 1.5rem;
            text-align: center;
            border: 1px solid var(--border);
        }
    </style>
</head>
<body>
    <div class="inventory-container">
        <div class="header">
            <h1><i class="fas fa-warehouse"></i> Stock de Sang</h1>
            <a href="dashboard.php" class="action-btn">Retour au Tableau de Bord</a>
        </div>

        <div class="inventory-summary">
            <div class="summary-card">
                <h3>Total des Unités</h3>
                <h2><?php echo array_sum(array_column($inventory, 'total_quantity')); ?></h2>
            </div>
            <div class="summary-card">
                <h3>Types de Sang</h3>
                <h2><?php echo count($inventory); ?></h2>
            </div>
        </div>

        <table class="inventory-table">
            <thead>
                <tr>
                    <th>Type de Sang</th>
                    <th>Quantité Totale</th>
                    <th>Première Expiration</th>
                    <th>Nombre de Dons</th>
                    <th>Statut</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($inventory as $item): ?>
                <tr>
                    <td><?php echo htmlspecialchars($item['blood_type']); ?></td>
                    <td><?php echo $item['total_quantity']; ?> unités</td>
                    <td><?php echo date('d/m/Y', strtotime($item['earliest_expiration'])); ?></td>
                    <td><?php echo $item['unique_donations']; ?></td>
                    <td class="<?php 
                        echo ($item['total_quantity'] < 10) ? 'status-low' : 'status-good'; 
                    ?>">
                        <?php 
                        echo ($item['total_quantity'] < 10) ? 'Critique' : 'Suffisant'; 
                        ?>
                    </td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
</body>
</html>