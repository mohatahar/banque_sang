<?php
session_start();
require_once 'db.php';

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit;
}

// Fetch key dashboard statistics
try {
    $stats_query = $pdo->query("SELECT 
        (SELECT COUNT(*) FROM blood_donations) as total_donations,
        (SELECT COUNT(*) FROM blood_requests) as total_requests,
        (SELECT COUNT(DISTINCT blood_type) FROM blood_inventory) as blood_types_available,
        (SELECT SUM(quantity) FROM blood_inventory) as total_blood_units
    ");
    $stats = $stats_query->fetch(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    $stats = [
        'total_donations' => 0,
        'total_requests' => 0,
        'blood_types_available' => 0,
        'total_blood_units' => 0
    ];
}
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <title>Tableau de Bord - Banque de Sang</title>
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

        .dashboard-container {
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

        .logo {
            display: flex;
            align-items: center;
            gap: 1rem;
        }

        .logo i {
            font-size: 2rem;
            color: var(--secondary);
        }

        .user-info {
            display: flex;
            align-items: center;
            gap: 1rem;
        }

        .stats-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
            gap: 1rem;
        }

        .stat-card {
            background: var(--card);
            border-radius: 1rem;
            padding: 1.5rem;
            text-align: center;
            border: 1px solid var(--border);
            backdrop-filter: blur(10px);
            transition: transform 0.3s ease;
        }

        .stat-card:hover {
            transform: translateY(-5px);
        }

        .stat-card i {
            font-size: 2rem;
            color: var(--secondary);
            margin-bottom: 1rem;
        }

        .stat-value {
            font-size: 2.5rem;
            font-weight: bold;
            color: var(--text);
            margin-bottom: 0.5rem;
        }

        .stat-label {
            color: var(--text-muted);
            text-transform: uppercase;
            font-size: 0.875rem;
        }

        .action-buttons {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            gap: 1rem;
            margin-top: 2rem;
        }

        .action-btn {
            background: linear-gradient(to right, var(--primary), var(--secondary));
            color: white;
            border: none;
            padding: 1rem;
            border-radius: 0.75rem;
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 0.5rem;
            text-decoration: none;
            transition: transform 0.3s ease;
        }

        .action-btn:hover {
            transform: translateY(-3px);
        }

        .quick-actions {
            margin-top: 2rem;
            background: var(--card);
            border-radius: 1rem;
            padding: 1.5rem;
            backdrop-filter: blur(10px);
        }
    </style>
</head>
<body>
    <div class="dashboard-container">
        <div class="header">
            <div class="logo">
                <i class="fas fa-tint"></i>
                <h1>Banque de Sang - EPH SOBHA</h1>
            </div>
            <div class="user-info">
                <span>Bienvenue, <?php echo htmlspecialchars($_SESSION['username']); ?></span>
                <a href="logout.php" class="action-btn">
                    <i class="fas fa-sign-out-alt"></i> Déconnexion
                </a>
            </div>
        </div>

        <div class="stats-grid">
            <div class="stat-card">
                <i class="fas fa-hand-holding-medical"></i>
                <div class="stat-value"><?php echo $stats['total_donations']; ?></div>
                <div class="stat-label">Dons Totaux</div>
            </div>
            <div class="stat-card">
                <i class="fas fa-clipboard-list"></i>
                <div class="stat-value"><?php echo $stats['total_requests']; ?></div>
                <div class="stat-label">Demandes de Sang</div>
            </div>
            <div class="stat-card">
                <i class="fas fa-flask"></i>
                <div class="stat-value"><?php echo $stats['blood_types_available']; ?></div>
                <div class="stat-label">Types de Sang</div>
            </div>
            <div class="stat-card">
                <i class="fas fa-archive"></i>
                <div class="stat-value"><?php echo $stats['total_blood_units']; ?></div>
                <div class="stat-label">Unités en Stock</div>
            </div>
        </div>

        <div class="action-buttons">
            <a href="new-donation.php" class="action-btn">
                <i class="fas fa-plus-circle"></i> Nouveau Don
            </a>
            <a href="blood-requests.php" class="action-btn">
                <i class="fas fa-medkit"></i> Demandes de Sang
            </a>
            <a href="inventory.php" class="action-btn">
                <i class="fas fa-warehouse"></i> Stock de Sang
            </a>
            <a href="donors.php" class="action-btn">
                <i class="fas fa-user-friends"></i> Donneurs
            </a>
        </div>

        <div class="quick-actions">
            <h2>Actions Rapides</h2>
            <!-- Additional quick action buttons or recent activity can be added here -->
        </div>
    </div>
</body>
</html>