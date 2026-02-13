<?php
session_start();
require_once 'config.php';

// --- JOGOSULTSÁG ELLENŐRZÉSE ---
if (!isset($_SESSION['logged_in']) || $_SESSION['role'] !== 1) {
    header('Location: bejelentkezes');
    exit();
}

// --- VISSZAÁLLÍTÁS KEZELÉSE ---
if (isset($_GET['restore_id'])) {
    $id = intval($_GET['restore_id']);
    $stmt = $conn->prepare("UPDATE emp SET active = 1 WHERE empID = ?");
    $stmt->bind_param("i", $id);
    
    if ($stmt->execute()) {
        // A .htaccess szabályod alapján irányítunk vissza az archivum-ra
        header('Location: /clocky/archivum?restored=1');
        exit();
    }
}

// --- INAKTÍV ADATOK LEKÉRÉSE ---
$sql = "SELECT * FROM emp WHERE active = 0 ORDER BY name ASC";
$result = $conn->query($sql);

$page_title = 'Archívum';
include_once 'header.php';
?>

<style>
    :root {
        --bg-color: #0f0f0f;
        --card-bg: #1a1a1a;
        --accent-color: #00ffe1;
        --text-secondary: #888;
        --danger-color: #ff4757;
    }

    .main-container {
        width: 100%;
        max-width: 1000px;
        margin: 20px auto;
        padding: 0 15px;
        box-sizing: border-box;
    }

    .glass-card {
        background: var(--card-bg);
        padding: clamp(20px, 5vw, 40px);
        border-radius: 24px;
        border: 1px solid rgba(255, 255, 255, 0.05);
        box-shadow: 0 25px 50px -12px rgba(0,0,0,0.5);
    }

    .header-row {
        display: flex;
        justify-content: space-between;
        align-items: center;
        margin-bottom: 30px;
    }

    h1 {
        font-weight: 800;
        letter-spacing: -1px;
        margin: 0;
        font-size: clamp(1.5rem, 4vw, 2.2rem);
    }

    .back-link {
        color: var(--text-secondary);
        text-decoration: none;
        font-size: 0.9rem;
        transition: 0.3s;
        display: flex;
        align-items: center;
        gap: 8px;
    }

    .back-link:hover { color: var(--accent-color); }

    /* TÁBLÁZAT */
    .table-wrapper { width: 100%; overflow-x: auto; }
    .custom-table { width: 100%; border-collapse: separate; border-spacing: 0 10px; }
    .custom-table th { color: var(--text-secondary); text-transform: uppercase; font-size: 0.75rem; padding: 12px; text-align: left; }
    .custom-table td { background: rgba(255, 255, 255, 0.02); padding: 15px; border: none; }
    
    .custom-table tr td:first-child { border-radius: 12px 0 0 12px; }
    .custom-table tr td:last-child { border-radius: 0 12px 12px 0; }

    .btn-restore { 
        color: var(--accent-color); 
        text-decoration: none; 
        border: 1px solid var(--accent-color); 
        padding: 8px 16px; 
        border-radius: 8px; 
        font-size: 0.85rem;
        font-weight: 600;
        transition: 0.3s;
        display: inline-block;
    }
    .btn-restore:hover { background: var(--accent-color); color: #000; }

    .status-alert {
        background: rgba(0, 255, 225, 0.1);
        color: var(--accent-color);
        padding: 15px;
        border-radius: 12px;
        margin-bottom: 20px;
        border: 1px solid var(--accent-color);
        text-align: center;
    }

    /* MOBIL NÉZET */
    @media (max-width: 650px) {
        .custom-table thead { display: none; }
        .custom-table tr { display: block; margin-bottom: 15px; }
        .custom-table td { 
            display: flex; 
            justify-content: space-between; 
            text-align: right; 
            padding: 12px 15px;
            border-bottom: 1px solid rgba(255,255,255,0.05);
        }
        .custom-table td::before { 
            content: attr(data-label); 
            font-weight: 600; 
            color: var(--text-secondary);
            font-size: 0.7rem;
            text-transform: uppercase;
        }
        .custom-table tr td:first-child { border-radius: 12px 12px 0 0; }
        .custom-table tr td:last-child { border-radius: 0 0 12px 12px; border-bottom: none; }
    }
</style>

<div class="main-container">
    <div class="glass-card">
        <div class="header-row">
            <h1>Archívum</h1>
            <a href="dolgozok" class="back-link">
                <i class="fas fa-arrow-left"></i> Vissza a dolgozókhoz
            </a>
        </div>

        <?php if (isset($_GET['restored'])): ?>
            <div class="status-alert">
                <i class="fas fa-check-circle"></i> Dolgozó sikeresen visszaállítva!
            </div>
        <?php endif; ?>

        <div class="table-wrapper">
            <table class="custom-table">
                <thead>
                    <tr>
                        <th>Dolgozó neve</th>
                        <th>Email cím</th>
                        <th style="text-align: right;">Művelet</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if ($result && $result->num_rows > 0): ?>
                        <?php while($row = $result->fetch_assoc()): ?>
                            <tr>
                                <td data-label="Név"><strong><?= htmlspecialchars($row['name']) ?></strong></td>
                                <td data-label="Email"><?= htmlspecialchars($row['email']) ?></td>
                                <td data-label="Művelet" style="text-align: right;">
                                    <a href="archivum/visszaallit/<?= $row['empID'] ?>" class="btn-restore">
                                        <i class="fas fa-undo-alt"></i> Visszaállítás
                                    </a>
                                </td>
                            </tr>
                        <?php endwhile; ?>
                    <?php else: ?>
                        <tr>
                            <td colspan="3" style="text-align:center; padding: 40px; color: var(--text-secondary);">
                                Az archívum üres.
                            </td>
                        </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<?php 
$conn->close();
?>