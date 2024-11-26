<?php
session_start();
require_once 'db.php';

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit;
}

// Handle form submission
$error = '';
$success = '';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    try {
        // Validate and sanitize input
        $donor_name = trim($_POST['donor_name']);
        $blood_type = trim($_POST['blood_type']);
        $quantity = floatval($_POST['quantity']);
        $donation_date = trim($_POST['donation_date']);

        // Basic validation
        if (empty($donor_name) || empty($blood_type) || empty($donation_date) || $quantity <= 0) {
            $error = 'Tous les champs sont obligatoires';
        } else {
            // Validate date format
            $date_obj = DateTime::createFromFormat('Y-m-d', $donation_date);
            if (!$date_obj || $date_obj > new DateTime()) {
                $error = 'Date de don invalide';
            } else {
                // Insert donation record
                $stmt = $pdo->prepare("INSERT INTO blood_donations 
                    (donor_name, blood_type, quantity, donation_date, status) 
                    VALUES (?, ?, ?, ?, 'pending')");
                
                $stmt->execute([$donor_name, $blood_type, $quantity, $donation_date]);

                // Update inventory (tentative)
                $update_stmt = $pdo->prepare("
                    INSERT INTO blood_inventory (blood_type, quantity, expiry_date) 
                    VALUES (?, ?, DATE_ADD(?, INTERVAL 42 DAY))
                    ON DUPLICATE KEY UPDATE quantity = quantity + ?
                ");
                $update_stmt->execute([
                    $blood_type, 
                    $quantity, 
                    $donation_date, 
                    $quantity
                ]);

                $success = 'Don de sang enregistré avec succès';
            }
        }
    } catch (PDOException $e) {
        $error = 'Erreur lors de l\'enregistrement du don : ' . $e->getMessage();
    }
}

// Fetch blood types for dropdown
$blood_types = ['A+', 'A-', 'B+', 'B-', 'AB+', 'AB-', 'O+', 'O-'];
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <title>Nouveau Don de Sang</title>
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
            min-height: 100vh;
            display: flex;
            justify-content: center;
            align-items: center;
            padding: 1rem;
        }

        .container {
            width: 100%;
            max-width: 500px;
            background: var(--card);
            padding: 2rem;
            border-radius: 1rem;
            backdrop-filter: blur(10px);
        }

        .form-group {
            margin-bottom: 1.5rem;
        }

        label {
            display: block;
            margin-bottom: 0.5rem;
            color: var(--text);
        }

        input, select {
            width: 100%;
            padding: 0.75rem;
            background: rgba(255,255,255,0.1);
            border: 1px solid var(--border);
            border-radius: 0.5rem;
            color: var(--text);
        }

        .btn {
            width: 100%;
            padding: 0.75rem;
            background: linear-gradient(to right, var(--primary), var(--secondary));
            color: white;
            border: none;
            border-radius: 0.5rem;
            cursor: pointer;
        }

        .error {
            background: rgba(239, 68, 68, 0.1);
            color: #fecaca;
            padding: 1rem;
            border-radius: 0.5rem;
            margin-bottom: 1rem;
        }

        .success {
            background: rgba(34, 197, 94, 0.1);
            color: #bbf7d0;
            padding: 1rem;
            border-radius: 0.5rem;
            margin-bottom: 1rem;
        }
    </style>
</head>
<body>
    <div class="container">
        <h2 class="mb-4">
            <i class="fas fa-hand-holding-medical mr-2"></i>
            Nouveau Don de Sang
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

        <form method="POST" action="">
            <div class="form-group">
                <label for="donor_name">Nom du Donneur</label>
                <input 
                    type="text" 
                    id="donor_name" 
                    name="donor_name" 
                    required 
                    placeholder="Nom complet du donneur"
                >
            </div>

            <div class="form-group">
                <label for="blood_type">Type de Sang</label>
                <select 
                    id="blood_type" 
                    name="blood_type" 
                    required
                >
                    <option value="">Sélectionner le type de sang</option>
                    <?php foreach ($blood_types as $type): ?>
                        <option value="<?php echo $type; ?>"><?php echo $type; ?></option>
                    <?php endforeach; ?>
                </select>
            </div>

            <div class="form-group">
                <label for="donation_date">Date du Don</label>
                <input 
                    type="date" 
                    id="donation_date" 
                    name="donation_date" 
                    required 
                    max="<?php echo date('Y-m-d'); ?>"
                >
            </div>

            <div class="form-group">
                <label for="quantity">Quantité (ml)</label>
                <input 
                    type="number" 
                    id="quantity" 
                    name="quantity" 
                    required 
                    min="350" 
                    max="500" 
                    placeholder="Quantité de sang (350-500 ml)"
                >
            </div>

            <button type="submit" class="btn">
                <i class="fas fa-plus-circle mr-2"></i>
                Enregistrer le Don
            </button>
        </form>
    </div>
</body>
</html>