<?php
session_start();
require_once 'config.php';

// --- ROUTER & ÁTIRÁNYÍTÁS ---
$request_uri = $_SERVER['REQUEST_URI'];
$current_file = basename($_SERVER['PHP_SELF']);

$routes = [
    'list.php'  => 'lista',
    'index.php' => 'bejelentkezes'
];

if (strpos($request_uri, '.php') !== false && !isset($_GET['api'])) {
    $pretty_name = $routes[$current_file] ?? 'lista';
    header("HTTP/1.1 301 Moved Permanently");
    header("Location: " . $pretty_name);
    exit();
}

// --- JOGOSULTSÁG ELLENŐRZÉSE ---
if (!isset($_SESSION['logged_in']) || $_SESSION['logged_in'] !== true) {
    header('Location: bejelentkezes');
    exit;
}

$username = $_SESSION['username'];
$role = (int)($_SESSION['role'] ?? 0);

// --- ADATOK LEKÉRÉSE ---
$sql_base = "SELECT 
                e.name as dolgozo_neve, 
                r.role_name, 
                w.start_datetime, 
                w.end_datetime, 
                w.startbreak_time,
                w.endbreak_time,
                r.pph_HUF
            FROM worktime w
            JOIN emp e ON w.FK_empID = e.empID
            JOIN role r ON w.FK_roleID = r.roleID";

if ($role === 1) {
    $sql = $sql_base . " ORDER BY w.start_datetime DESC";
    $stmt = $conn->prepare($sql);
} else {
    $sql = $sql_base . " JOIN users u ON e.FK_userID = u.id 
            WHERE u.username = ?
            ORDER BY w.start_datetime DESC";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("s", $username);
}

$stmt->execute();
$result = $stmt->get_result();

include_once 'header.php'; 
?>

<style>
    :root {
        --accent-color: #00ffe1;
        --card-bg: #1a1a1a;
        --text-dim: #888;
        --input-bg: #252525;
        --break-color: #f1c40f;
        --error-color: #ff4757;
    }

    .container {
        width: 100%;
        max-width: 1200px;
        margin: 0 auto;
        padding: 20px;
    }

    .glass-card {
        background: var(--card-bg);
        border-radius: 20px;
        border: 1px solid rgba(255,255,255,0.05);
        padding: clamp(15px, 4vw, 30px);
        box-shadow: 0 10px 30px rgba(0,0,0,0.5);
    }

    h1.title {
        font-size: clamp(1.3rem, 5vw, 1.8rem);
        margin-bottom: 25px;
        display: flex;
        align-items: center;
        gap: 12px;
        font-weight: 800;
        color: white;
    }

    /* Szűrő Szekciók */
    .filter-grid {
        display: grid;
        grid-template-columns: repeat(3, 1fr);
        gap: 15px;
        margin-bottom: 25px;
    }

    .filter-card {
        background: var(--input-bg);
        padding: 15px;
        border-radius: 15px;
        text-align: center;
        cursor: pointer;
        border: 2px solid transparent;
        transition: 0.3s;
        opacity: 0.6;
    }

    .filter-card:hover { opacity: 1; transform: translateY(-3px); }
    .filter-card.active { 
        opacity: 1; 
        border-color: var(--accent-color); 
        background: rgba(0, 255, 225, 0.05);
    }

    .filter-card i { display: block; font-size: 1.2rem; margin-bottom: 8px; }
    .filter-card span { font-size: 0.75rem; font-weight: 800; text-transform: uppercase; color: #fff; }

    .search-container {
        margin-bottom: 25px;
        position: relative;
    }

    .search-input {
        width: 100%;
        background: var(--input-bg);
        border: 1px solid rgba(255,255,255,0.1);
        border-radius: 12px;
        padding: 15px 20px 15px 45px;
        color: white;
        font-size: 0.95rem;
        outline: none;
        transition: 0.3s;
    }

    .search-input:focus {
        border-color: var(--accent-color);
        box-shadow: 0 0 15px rgba(0, 255, 225, 0.1);
    }

    .search-icon {
        position: absolute;
        left: 18px;
        top: 50%;
        transform: translateY(-50%);
        color: var(--text-dim);
    }

    .responsive-table { width: 100%; border-collapse: collapse; }
    .responsive-table th {
        text-align: left;
        padding: 15px;
        color: var(--text-dim);
        font-size: 0.75rem;
        text-transform: uppercase;
        letter-spacing: 1px;
        border-bottom: 2px solid rgba(255,255,255,0.05);
    }

    .responsive-table td { 
        padding: 15px; 
        border-bottom: 1px solid rgba(255,255,255,0.03); 
        font-size: 0.95rem;
        color: #eee;
    }

    .search-name strong { color: var(--accent-color); }

    .badge { padding: 4px 10px; border-radius: 6px; font-size: 0.75rem; font-weight: 700; }
    .badge-inactive { background: rgba(255,255,255,0.05); color: #888; }
    .badge-active { background: rgba(0, 255, 225, 0.1); color: var(--accent-color); border: 1px solid var(--accent-color); }
    .badge-break { background: rgba(241, 196, 15, 0.1); color: var(--break-color); border: 1px solid var(--break-color); }
    .badge-duration { background: rgba(0, 255, 225, 0.1); color: var(--accent-color); border: 1px solid rgba(0, 255, 225, 0.2); }

    .money { font-family: 'Courier New', monospace; font-weight: bold; color: #fff; }

    @media screen and (max-width: 850px) {
        .responsive-table thead { display: none; }
        .responsive-table tr {
            display: block;
            margin-bottom: 15px;
            background: rgba(255,255,255,0.02);
            border: 1px solid rgba(255,255,255,0.05);
            border-radius: 15px;
            padding: 10px;
        }
        .responsive-table td {
            display: flex;
            justify-content: space-between;
            align-items: center;
            border-bottom: 1px solid rgba(255,255,255,0.05);
            text-align: right;
        }
        .responsive-table td::before {
            content: attr(data-label);
            font-weight: 600;
            color: var(--text-dim);
            font-size: 0.7rem;
            text-transform: uppercase;
        }
        .responsive-table td:last-child { border-bottom: none; }
    }
</style>

<div class="container">
    <div class="glass-card">
        <h1 class="title">
            <i class="fas fa-history" style="color: var(--accent-color);"></i> 
            Munkaidő Napló
        </h1>

        <div class="filter-grid">
            <div class="filter-card active" onclick="filterByStatus('all', this)">
                <i class="fas fa-border-all" style="color: #fff;"></i>
                <span>Mind</span>
            </div>
            <div class="filter-card" onclick="filterByStatus('working', this)">
                <i class="fas fa-running" style="color: var(--accent-color);"></i>
                <span>Folyamatban</span>
            </div>
            <div class="filter-card" onclick="filterByStatus('onbreak', this)">
                <i class="fas fa-coffee" style="color: var(--break-color);"></i>
                <span>Szüneten</span>
            </div>
        </div>

        <div class="search-container">
            <i class="fas fa-search search-icon"></i>
            <input type="text" id="logSearch" class="search-input" placeholder="Keresés név vagy munkakör alapján...">
        </div>

        <table class="responsive-table" id="workTable">
            <thead>
                <tr>
                    <th>Dolgozó</th>
                    <th>Státusz</th>
                    <th>Kezdés</th>
                    <th>Befejezés</th>
                    <th>Időtartam</th>
                    <th>Kereset</th>
                </tr>
            </thead>
            <tbody>
                <?php if ($result && $result->num_rows > 0): ?>
                    <?php while($row = $result->fetch_assoc()): ?>
                        <?php
                            $is_active = $row['end_datetime'] === null;
                            $is_on_break = ($row['startbreak_time'] !== null && $row['endbreak_time'] === null);
                            
                            $status_key = "inactive";
                            $status_label = "BEFEJEZETT";
                            $badge_class = "badge-inactive";

                            if ($is_on_break) {
                                $status_key = "onbreak";
                                $status_label = "SZÜNETEN";
                                $badge_class = "badge-break";
                            } elseif ($is_active) {
                                $status_key = "working";
                                $status_label = "DOLGOZIK";
                                $badge_class = "badge-active";
                            }

                            $start_dt = new DateTime($row['start_datetime']);
                            $duration_html = "---";
                            $money_val = "---";

                            if (!$is_active) {
                                $end_dt = new DateTime($row['end_datetime']);
                                $diff = $start_dt->diff($end_dt);
                                
                                // Pontosított időtartam formázás (napok kezelésével)
                                $parts = [];
                                if ($diff->d > 0) $parts[] = $diff->d . 'n';
                                if ($diff->h > 0) $parts[] = $diff->h . 'ó';
                                if ($diff->i > 0) $parts[] = $diff->i . 'p';
                                
                                $duration_str = empty($parts) ? '0p' : implode(' ', $parts);
                                $duration_html = '<span class="badge badge-duration">' . $duration_str . '</span>';
                                
                                // Pontos kereset számítás másodperc alapon
                                $sec = $end_dt->getTimestamp() - $start_dt->getTimestamp();
                                $money_val = number_format(($sec / 3600) * $row['pph_HUF'], 0, ',', ' ') . " Ft";
                            }
                        ?>
                        <tr class="log-row" data-status="<?php echo $status_key; ?>">
                            <td data-label="Dolgozó" class="search-name">
                                <strong><?php echo htmlspecialchars($row['dolgozo_neve']); ?></strong><br>
                                <small style="color:var(--text-dim)"><?php echo htmlspecialchars($row['role_name']); ?></small>
                            </td>
                            <td data-label="Státusz">
                                <span class="badge <?php echo $badge_class; ?>"><?php echo $status_label; ?></span>
                            </td>
                            <td data-label="Kezdés">
                                <?php echo $start_dt->format('Y.m.d. H:i'); ?>
                            </td>
                            <td data-label="Befejezés">
                                <?php echo $row['end_datetime'] ? date('Y.m.d. H:i', strtotime($row['end_datetime'])) : "---"; ?>
                            </td>
                            <td data-label="Időtartam">
                                <?php echo $duration_html; ?>
                            </td>
                            <td data-label="Kereset" class="money">
                                <?php echo $money_val; ?>
                            </td>
                        </tr>
                    <?php endwhile; ?>
                <?php else: ?>
                    <tr id="noDataRow">
                        <td colspan="6" style="text-align:center; padding: 40px; color: var(--text-dim);">
                            Nincs rögzített munkaidő.
                        </td>
                    </tr>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>

<script>
    let currentStatusFilter = 'all';

    function filterByStatus(status, element) {
        document.querySelectorAll('.filter-card').forEach(card => card.classList.remove('active'));
        element.classList.add('active');
        currentStatusFilter = status;
        applyFilters();
    }

    document.getElementById('logSearch').addEventListener('keyup', applyFilters);

    function applyFilters() {
        const filter = document.getElementById('logSearch').value.toLowerCase();
        const rows = document.querySelectorAll('.log-row');
        let anyVisible = false;

        rows.forEach(row => {
            const name = row.querySelector('.search-name').textContent.toLowerCase();
            const status = row.getAttribute('data-status');
            
            const matchesStatus = (currentStatusFilter === 'all' || status === currentStatusFilter);
            const matchesSearch = name.includes(filter);

            if (matchesStatus && matchesSearch) {
                row.style.display = "";
                anyVisible = true;
            } else {
                row.style.display = "none";
            }
        });

        const noData = document.getElementById('noDataRow');
        if(noData) noData.style.display = anyVisible ? "none" : "";
    }
</script>

</body>
</html>