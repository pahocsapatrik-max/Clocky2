<?php
session_start();
require_once 'config.php';

// --- OKOS ROUTER & ÁTIRÁNYÍTÁS ---
$request_uri = $_SERVER['REQUEST_URI'];
$current_file = basename($_SERVER['PHP_SELF']);

$routes = [
    'roles.php' => 'munkakorok',
    'index.php' => 'bejelentkezes'
];

if (strpos($request_uri, '.php') !== false && !isset($_GET['api'])) {
    $pretty_name = $routes[$current_file] ?? 'munkakorok';
    header("HTTP/1.1 301 Moved Permanently");
    header("Location: " . $pretty_name);
    exit();
}

// --- JOGOSULTSÁG ELLENŐRZÉSE ---
if (!isset($_SESSION['logged_in']) || $_SESSION['role'] !== 1) {
    if (isset($_GET['api'])) {
        http_response_code(403);
        echo json_encode(['status' => 'error', 'message' => 'Nincs jogosultsága.']);
        exit();
    }
    header('Location: bejelentkezes');
    exit();
}

$success_msg = '';
$error_msg = '';

// --- MÓDOSÍTÁS KEZELÉSE (POST) ---
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['update_role'])) {
    $roleID = intval($_POST['roleID']);
    $role_name = trim($_POST['role_name']);
    $pph_huf = intval($_POST['pph_huf']);

    if (!empty($role_name) && $pph_huf > 0) {
        $stmt = $conn->prepare("UPDATE role SET role_name = ?, pph_huf = ? WHERE roleID = ?");
        $stmt->bind_param("sii", $role_name, $pph_huf, $roleID);
        
        $success = $stmt->execute();

        if (isset($_GET['api'])) {
            header('Content-Type: application/json');
            echo json_encode(['status' => $success ? 'success' : 'error']);
            exit();
        }

        if ($success) {
            $success_msg = "Munkakör sikeresen frissítve!";
        } else {
            $error_msg = "Hiba történt a mentés során.";
        }
        $stmt->close();
    } else {
        $error_msg = "Kérjük, töltsön ki minden mezőt megfelelően!";
    }
}

$page_title = 'Munkakörök Kezelése';
include_once 'header.php';
?>

<style>
    :root {
        --bg-color: #0f0f0f;
        --card-bg: #1a1a1a;
        --accent-color: #00ffe1;
        --text-secondary: #888;
        --input-bg: #252525;
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

    h2 { font-weight: 800; letter-spacing: -1px; margin-bottom: 30px; text-align: center; color: #fff; }

    /* SZERKESZTŐ PANEL */
    #editSection {
        display: none;
        background: rgba(0, 255, 225, 0.03);
        padding: 25px;
        border-radius: 16px;
        border: 1px solid rgba(0, 255, 225, 0.3);
        margin-bottom: 30px;
        animation: fadeIn 0.4s ease-out;
    }

    @keyframes fadeIn {
        from { opacity: 0; transform: translateY(-10px); }
        to { opacity: 1; transform: translateY(0); }
    }

    .form-group { margin-bottom: 15px; }
    label { font-size: 0.75rem; text-transform: uppercase; color: var(--text-secondary); margin-bottom: 8px; display: block; font-weight: 600; }
    
    input[type="text"], input[type="number"] {
        width: 100%;
        background: var(--input-bg);
        border: 1px solid rgba(255, 255, 255, 0.1);
        padding: 14px;
        border-radius: 12px;
        color: #fff;
        font-size: 1rem;
        outline: none;
        transition: 0.3s;
    }

    input:focus { border-color: var(--accent-color); box-shadow: 0 0 10px rgba(0, 255, 225, 0.1); }

    .btn-row { display: flex; gap: 12px; margin-top: 20px; }
    .btn-save { background: var(--accent-color); color: #000; border: none; padding: 14px; border-radius: 12px; font-weight: 800; cursor: pointer; flex: 2; transition: 0.3s; }
    .btn-save:hover { filter: brightness(1.1); transform: translateY(-2px); }
    .btn-cancel { background: #333; color: #fff; border: none; padding: 14px; border-radius: 12px; cursor: pointer; flex: 1; transition: 0.3s; }

    /* TÁBLÁZAT STÍLUS */
    .table-wrapper { width: 100%; overflow-x: auto; }
    .custom-table { width: 100%; border-collapse: separate; border-spacing: 0 10px; }
    .custom-table th { color: var(--text-secondary); text-transform: uppercase; font-size: 0.75rem; padding: 12px 15px; text-align: left; letter-spacing: 1px; }
    .custom-table td { background: rgba(255, 255, 255, 0.02); padding: 18px 15px; border: none; border-top: 1px solid rgba(255,255,255,0.03); }
    .custom-table tr td:first-child { border-radius: 15px 0 0 15px; }
    .custom-table tr td:last-child { border-radius: 0 15px 15px 0; }

    .btn-edit { 
        background: rgba(0, 255, 225, 0.1); 
        color: var(--accent-color); 
        border: 1px solid var(--accent-color); 
        padding: 8px 16px; 
        border-radius: 8px; 
        cursor: pointer; 
        font-weight: 600;
        transition: 0.3s;
    }
    .btn-edit:hover { background: var(--accent-color); color: #000; }

    /* MOBIL NÉZET */
    @media (max-width: 650px) {
        .custom-table thead { display: none; } 
        .custom-table tr { display: block; margin-bottom: 20px; }
        .custom-table td { 
            display: flex; 
            justify-content: space-between; 
            align-items: center;
            padding: 15px;
            border-radius: 0 !important;
            border-bottom: 1px solid rgba(255,255,255,0.05);
        }
        .custom-table td::before { 
            content: attr(data-label); 
            font-weight: 600; 
            color: var(--text-secondary);
            font-size: 0.7rem;
            text-transform: uppercase;
        }
        .custom-table tr td:first-child { border-radius: 15px 15px 0 0 !important; }
        .custom-table tr td:last-child { border-radius: 0 0 15px 15px !important; border-bottom: none; }
        .btn-edit { width: 100%; text-align: center; }
    }

    .status-msg { padding: 15px; border-radius: 12px; margin-bottom: 25px; text-align: center; font-weight: 600; }
    .success { background: rgba(0, 255, 225, 0.1); color: var(--accent-color); border: 1px solid var(--accent-color); }
    .error { background: rgba(255, 71, 87, 0.1); color: #ff4757; border: 1px solid #ff4757; }
</style>

<div class="main-container">
    <div class="glass-card">
        <h2>Munkakörök Kezelése</h2>

        <?php if ($success_msg): ?> <div class="status-msg success"><?= $success_msg ?></div> <?php endif; ?>
        <?php if ($error_msg): ?> <div class="status-msg error"><?= $error_msg ?></div> <?php endif; ?>

        <div id="editSection">
            <h3 style="margin-top: 0; margin-bottom: 20px; font-size: 1.1rem; color: var(--accent-color); display: flex; align-items: center; gap: 8px;">
                <i class="fas fa-edit"></i> Munkakör Módosítása
            </h3>
            <form method="POST">
                <input type="hidden" name="roleID" id="edit_roleID">
                <div class="form-group">
                    <label>Munkakör megnevezése</label>
                    <input type="text" name="role_name" id="edit_role_name" required placeholder="pl. Senior Webdesigner">
                </div>
                <div class="form-group">
                    <label>Órabér (HUF)</label>
                    <input type="number" name="pph_huf" id="edit_pph_huf" required placeholder="3500">
                </div>
                <div class="btn-row">
                    <button type="submit" name="update_role" class="btn-save">Változtatások Mentése</button>
                    <button type="button" class="btn-cancel" onclick="hideEdit()">Mégse</button>
                </div>
            </form>
        </div>

        <div class="table-wrapper">
            <table class="custom-table">
                <thead>
                    <tr>
                        <th>Munkakör Megnevezése</th>
                        <th>Órabér (Ft / óra)</th>
                        <th style="text-align: right;">Művelet</th>
                    </tr>
                </thead>
                <tbody>
                    <?php
                    $sql = "SELECT roleID, role_name, pph_huf FROM role ORDER BY role_name ASC";
                    $result = $conn->query($sql);

                    if ($result && $result->num_rows > 0):
                        while($row = $result->fetch_assoc()):
                            $safe_name = htmlspecialchars($row['role_name'], ENT_QUOTES);
                    ?>
                        <tr>
                            <td data-label="Munkakör"><strong><?= htmlspecialchars($row['role_name']) ?></strong></td>
                            <td data-label="Órabér"><?= number_format($row['pph_huf'], 0, ',', ' ') ?> Ft</td>
                            <td style="text-align: right;">
                                <button class="btn-edit" onclick="showEdit(<?= $row['roleID'] ?>, '<?= $safe_name ?>', <?= $row['pph_huf'] ?>)">
                                    Szerkesztés
                                </button>
                            </td>
                        </tr>
                    <?php 
                        endwhile;
                    else: 
                    ?>
                        <tr><td colspan="3" style="text-align:center;">Nincs rögzített munkakör.</td></tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<script>
function showEdit(id, name, pph) {
    const section = document.getElementById('editSection');
    section.style.display = 'block';
    document.getElementById('edit_roleID').value = id;
    document.getElementById('edit_role_name').value = name;
    document.getElementById('edit_pph_huf').value = pph;
    
    // Gördülés a szerkesztőhöz
    section.scrollIntoView({ behavior: 'smooth', block: 'center' });
}

function hideEdit() {
    document.getElementById('editSection').style.display = 'none';
}
</script>

<?php 
$conn->close(); 
// include 'footer.php'; // Opcionális
?>