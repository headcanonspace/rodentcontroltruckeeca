<?php
session_start();

// Database path config
$db_path = __DIR__ . '/telemetry.sqlite';
try {
    $pdo = new PDO('sqlite:' . $db_path);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    
    // Create clicks table if it doesn't exist
    $pdo->exec('CREATE TABLE IF NOT EXISTS clicks (
        id INTEGER PRIMARY KEY AUTOINCREMENT,
        timestamp DATETIME DEFAULT CURRENT_TIMESTAMP,
        ip TEXT,
        city TEXT,
        region TEXT,
        postal TEXT,
        country TEXT,
        isp TEXT,
        page TEXT,
        cta TEXT,
        user_agent TEXT,
        referer TEXT
    )');
} catch (PDOException $e) {
    die("Database connection failed: " . $e->getMessage());
}

// Credentials
$username = 'admin';
$password_hash = '$2y$10$EjM8GKfWdfLrDDcSNICoCOnmODb7CVAvPj2wvGFq5/DMgDBggN3vy';

// Handle Logout
if (isset($_GET['action']) && $_GET['action'] === 'logout') {
    session_destroy();
    header("Location: /admin.php");
    exit;
}

// Generate CSRF Token
if (empty($_SESSION['csrf_token'])) {
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
}

// Handle Login Form Submission
$login_error = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['login'])) {
    $submitted_user = $_POST['user'] ?? '';
    $submitted_pass = $_POST['pass'] ?? '';
    
    if ($submitted_user === $username && password_verify($submitted_pass, $password_hash)) {
        session_regenerate_id(true);
        $_SESSION['logged_in'] = true;
        header("Location: /admin.php");
        exit;
    } else {
        $login_error = 'Invalid username or password.';
    }
}

// Check Authentication
$is_authenticated = $_SESSION['logged_in'] ?? false;

// Handle Delete Event Log Entry
if ($is_authenticated && $_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'delete') {
    $csrf_token = $_POST['csrf_token'] ?? '';
    if (!empty($_SESSION['csrf_token']) && hash_equals($_SESSION['csrf_token'], $csrf_token)) {
        $delete_id = isset($_POST['id']) ? (int)$_POST['id'] : -1;
        if ($delete_id > 0) {
            $stmt = $pdo->prepare('DELETE FROM clicks WHERE id = :id');
            $stmt->execute([':id' => $delete_id]);
            header("Location: /admin.php?msg=deleted");
            exit;
        }
    }
}

// Handle CSV Export
if ($is_authenticated && isset($_GET['action']) && $_GET['action'] === 'export') {
    header('Content-Type: text/csv');
    header('Content-Disposition: attachment; filename="call_telemetry_report_' . date('Y-m-d') . '.csv"');
    
    $output = fopen('php://output', 'w');
    fputcsv($output, ['ID', 'Timestamp (UTC)', 'IP Address', 'City', 'Region', 'ZIP', 'Country', 'ISP', 'Page Path', 'CTA Placement', 'User Agent', 'Referer']);
    
    $stmt = $pdo->query('SELECT * FROM clicks ORDER BY timestamp DESC');
    while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
        fputcsv($output, [
            $row['id'],
            $row['timestamp'],
            $row['ip'],
            $row['city'],
            $row['region'],
            $row['postal'],
            $row['country'],
            $row['isp'],
            $row['page'],
            $row['cta'],
            $row['user_agent'],
            $row['referer']
        ]);
    }
    fclose($output);
    exit;
}

// Helper: safe value display
function safe($val) {
    return htmlspecialchars($val ?? '', ENT_QUOTES, 'UTF-8');
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Rodent Control Truckee — Call Telemetry Dashboard</title>
  <script src="https://cdn.tailwindcss.com"></script>
  <link rel="preconnect" href="https://fonts.googleapis.com">
  <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
  <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&family=Outfit:wght@400;600;700;800&display=swap" rel="stylesheet">
  <script>
    tailwind.config = {
      theme: {
        extend: {
          fontFamily: {
            sans: ['Inter', 'sans-serif'],
            outfit: ['Outfit', 'sans-serif'],
          },
          colors: {
            emerald: {
              DEFAULT: '#a9df59',
              50: '#f4fbf0',
              100: '#e5f6d7',
              500: '#a9df59',
              600: '#8ec53f',
              900: '#1e3805',
            },
            ink: {
              light: '#1f2937',
              DEFAULT: '#111827',
              dark: '#030712'
            }
          }
        }
      }
    }
  </script>
  <style>
    body { font-family: 'Inter', sans-serif; }
    h1, h2, h3, h4 { font-family: 'Outfit', sans-serif; }
    .glass-card {
      background: rgba(31, 41, 55, 0.4);
      backdrop-filter: blur(16px);
      -webkit-backdrop-filter: blur(16px);
      border: 1px solid rgba(255, 255, 255, 0.05);
    }
  </style>
</head>
<body class="bg-slate-950 text-slate-100 min-h-screen flex flex-col">

  <!-- Header -->
  <header class="border-b border-slate-800 bg-slate-900/50 py-5 px-6 backdrop-blur-md sticky top-0 z-40">
    <div class="max-w-7xl mx-auto flex justify-between items-center">
      <div class="flex items-center gap-3">
        <div class="bg-emerald-500/10 p-2.5 rounded-xl border border-emerald-500/20 text-emerald-500">
          <svg class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
            <path stroke-linecap="round" stroke-linejoin="round" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10a2 2 0 002 2h2a2 2 0 002-2V5a2 2 0 00-2-2h-2a2 2 0 00-2 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z" />
          </svg>
        </div>
        <div>
          <h1 class="text-xl font-bold tracking-tight text-white">Call Telemetry Dashboard</h1>
          <p class="text-xs text-slate-400 font-medium uppercase tracking-widest mt-0.5">Rodent Control Truckee</p>
        </div>
      </div>
      <?php if ($is_authenticated): ?>
        <a href="?action=logout" class="bg-slate-800 hover:bg-red-900/40 hover:text-red-400 hover:border-red-500/30 text-slate-300 font-bold px-5 py-2.5 rounded-xl border border-slate-700 transition-all text-sm flex items-center gap-2">
          <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1" /></svg>
          Logout
        </a>
      <?php endif; ?>
    </div>
  </header>

  <!-- Main Content -->
  <main class="flex-grow max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8 w-full">

    <?php if (!$is_authenticated): ?>
      <!-- Login View -->
      <div class="max-w-md mx-auto my-12 md:my-20">
        <div class="glass-card p-8 rounded-2xl border border-slate-800 shadow-2xl relative overflow-hidden">
          <div class="absolute top-0 left-0 right-0 h-1 bg-gradient-to-r from-emerald-500 to-emerald-600"></div>
          
          <div class="text-center space-y-2 mb-8">
            <h2 class="text-2xl font-bold text-white tracking-tight">Admin Login</h2>
            <p class="text-slate-400 text-sm">Sign in to view real-time call click conversions</p>
          </div>

          <?php if (!empty($login_error)): ?>
            <div class="bg-red-900/30 border border-red-500/20 text-red-300 p-4 rounded-xl text-sm mb-6 flex items-start gap-2.5">
              <svg class="w-5 h-5 text-red-400 shrink-0 mt-0.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z" /></svg>
              <span><?php echo safe($login_error); ?></span>
            </div>
          <?php endif; ?>

          <form method="POST" class="space-y-6">
            <div class="space-y-1.5">
              <label for="user" class="text-xs font-bold text-slate-300 uppercase tracking-wider block">Username</label>
              <input type="text" name="user" id="user" required class="w-full bg-slate-900 border border-slate-700 focus:border-emerald-500 focus:ring-1 focus:ring-emerald-500 rounded-xl px-4 py-3 text-slate-100 text-sm outline-none transition-all placeholder:text-slate-600" placeholder="admin">
            </div>

            <div class="space-y-1.5">
              <label for="pass" class="text-xs font-bold text-slate-300 uppercase tracking-wider block">Password</label>
              <input type="password" name="pass" id="pass" required class="w-full bg-slate-900 border border-slate-700 focus:border-emerald-500 focus:ring-1 focus:ring-emerald-500 rounded-xl px-4 py-3 text-slate-100 text-sm outline-none transition-all placeholder:text-slate-600" placeholder="••••••••">
            </div>

            <button type="submit" name="login" class="w-full bg-emerald-500 hover:bg-emerald-600 active:scale-95 text-slate-950 font-extrabold py-3.5 rounded-xl transition-all shadow-lg hover:shadow-emerald-500/10 tracking-wide text-sm flex items-center justify-center gap-2 mt-4">
              Access Dashboard
            </button>
          </form>
        </div>
      </div>

    <?php else: ?>
      <!-- Dashboard View -->
      
      <?php
      // 1. Resolve Filter
      $filter = $_GET['filter'] ?? 'all';
      $search = $_GET['search'] ?? '';
      
      $where_clauses = [];
      $params = [];
      
      if ($filter === 'today') {
          $where_clauses[] = 'date(timestamp, "localtime") = date("now", "localtime")';
      } elseif ($filter === 'yesterday') {
          $where_clauses[] = 'date(timestamp, "localtime") = date("now", "-1 day", "localtime")';
      } elseif ($filter === '7days') {
          $where_clauses[] = 'date(timestamp, "localtime") >= date("now", "-6 days", "localtime")';
      } elseif ($filter === '30days') {
          $where_clauses[] = 'date(timestamp, "localtime") >= date("now", "-29 days", "localtime")';
      }
      
      if (!empty($search)) {
          $where_clauses[] = '(ip LIKE :search OR city LIKE :search OR region LIKE :search OR postal LIKE :search OR page LIKE :search OR cta LIKE :search OR isp LIKE :search)';
          $params[':search'] = '%' . $search . '%';
      }
      
      $where_str = '';
      if (!empty($where_clauses)) {
          $where_str = 'WHERE ' . implode(' AND ', $where_clauses);
      }
      
      // Calculate totals matching the filter
      $total_clicks = 0;
      $unique_callers = 0;
      $clicks_today = 0;
      $top_page = 'None';
      
      try {
          // Total clicks count
          $stmt = $pdo->prepare("SELECT COUNT(*) FROM clicks $where_str");
          $stmt->execute($params);
          $total_clicks = (int)$stmt->fetchColumn();
          
          // Unique callers count
          $stmt = $pdo->prepare("SELECT COUNT(DISTINCT ip) FROM clicks $where_str");
          $stmt->execute($params);
          $unique_callers = (int)$stmt->fetchColumn();
          
          // Clicks today (always today, regardless of filter)
          $stmt = $pdo->query("SELECT COUNT(*) FROM clicks WHERE date(timestamp, 'localtime') = date('now', 'localtime')");
          $clicks_today = (int)$stmt->fetchColumn();
          
          // Top page
          $stmt = $pdo->prepare("SELECT page, COUNT(*) as cnt FROM clicks $where_str GROUP BY page ORDER BY cnt DESC LIMIT 1");
          $stmt->execute($params);
          $res = $stmt->fetch(PDO::FETCH_ASSOC);
          $top_page = $res ? $res['page'] : 'None';
      } catch (PDOException $e) {
          echo "<div class='bg-red-900/30 border border-red-500/20 text-red-300 p-4 rounded-xl text-sm mb-6'>Stats calculation error: " . safe($e->getMessage()) . "</div>";
      }
      
      // 2. Fetch traffic components
      $page_counts = [];
      $cta_counts = [];
      $location_counts = [];
      
      try {
          // Traffic Pages
          $stmt = $pdo->prepare("SELECT page, COUNT(*) as count FROM clicks $where_str GROUP BY page ORDER BY count DESC LIMIT 5");
          $stmt->execute($params);
          $page_counts = $stmt->fetchAll(PDO::FETCH_ASSOC);
          
          // CTA Placements
          $stmt = $pdo->prepare("SELECT cta, COUNT(*) as count FROM clicks $where_str GROUP BY cta ORDER BY count DESC LIMIT 5");
          $stmt->execute($params);
          $cta_counts = $stmt->fetchAll(PDO::FETCH_ASSOC);
          
          // Geo Locations
          $stmt = $pdo->prepare("SELECT city, region, postal, COUNT(*) as count FROM clicks $where_str GROUP BY city, region, postal ORDER BY count DESC LIMIT 5");
          $stmt->execute($params);
          $location_counts = $stmt->fetchAll(PDO::FETCH_ASSOC);
      } catch (PDOException $e) {}
      
      // 3. Pagination & Logs
      $page_num = isset($_GET['page_num']) ? (int)$_GET['page_num'] : 1;
      if ($page_num < 1) $page_num = 1;
      $limit = 15;
      $offset = ($page_num - 1) * $limit;
      
      $logs = [];
      $total_pages = 1;
      try {
          $stmt = $pdo->prepare("SELECT COUNT(*) FROM clicks $where_str");
          $stmt->execute($params);
          $filtered_count = (int)$stmt->fetchColumn();
          $total_pages = ceil($filtered_count / $limit);
          if ($total_pages < 1) $total_pages = 1;
          
          $stmt = $pdo->prepare("SELECT * FROM clicks $where_str ORDER BY timestamp DESC LIMIT :limit OFFSET :offset");
          $stmt->bindValue(':limit', $limit, PDO::PARAM_INT);
          $stmt->bindValue(':offset', $offset, PDO::PARAM_INT);
          foreach ($params as $k => $v) {
              $stmt->bindValue($k, $v);
          }
          $stmt->execute();
          $logs = $stmt->fetchAll(PDO::FETCH_ASSOC);
      } catch (PDOException $e) {}
      ?>

      <!-- Controls & Filter Toolbar -->
      <div class="flex flex-col md:flex-row gap-4 justify-between items-start md:items-center mb-8">
        <!-- Date Filters -->
        <div class="flex flex-wrap gap-2">
          <?php
          $filters = ['all' => 'All Time', 'today' => 'Today', 'yesterday' => 'Yesterday', '7days' => 'Last 7 Days', '30days' => 'Last 30 Days'];
          foreach ($filters as $key => $label) {
              $active = ($filter === $key) ? 'bg-emerald-500 text-slate-950 font-bold border-emerald-500' : 'bg-slate-900 text-slate-300 border-slate-800 hover:bg-slate-800';
              echo '<a href="?filter=' . $key . '&search=' . urlencode($search) . '" class="px-4 py-2 rounded-xl text-xs font-semibold border transition-all ' . $active . '">' . $label . '</a>';
          }
          ?>
        </div>
        
        <!-- Search & Export -->
        <div class="flex flex-col sm:flex-row w-full md:w-auto gap-3 items-stretch sm:items-center">
          <form method="GET" class="relative flex items-center">
            <input type="hidden" name="filter" value="<?php echo safe($filter); ?>">
            <input type="text" name="search" value="<?php echo safe($search); ?>" placeholder="Search IP, CTA, page..." class="bg-slate-900 border border-slate-850 focus:border-emerald-500 rounded-xl pl-4 pr-10 py-2.5 text-xs text-slate-100 outline-none w-full sm:w-64 placeholder:text-slate-500">
            <button type="submit" class="absolute right-3 text-slate-500 hover:text-emerald-500">
              <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5"><path stroke-linecap="round" stroke-linejoin="round" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" /></svg>
            </button>
          </form>
          
          <a href="?action=export" class="bg-slate-800 hover:bg-slate-700 text-white font-bold px-5 py-2.5 rounded-xl border border-slate-700 transition-all text-xs flex items-center justify-center gap-2">
            <svg class="w-4 h-4 text-emerald-400" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5"><path stroke-linecap="round" stroke-linejoin="round" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4" /></svg>
            Export CSV
          </a>
        </div>
      </div>

      <!-- Stat Cards -->
      <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-5 mb-8">
        <!-- Total Clicks -->
        <div class="glass-card rounded-2xl p-6 flex items-center justify-between border-l-4 border-l-emerald-500 shadow-lg">
          <div class="space-y-1">
            <span class="text-xs text-slate-400 font-bold uppercase tracking-wider block">Dialer Clicks</span>
            <span class="text-3xl font-extrabold text-white block"><?php echo $total_clicks; ?></span>
          </div>
          <div class="bg-emerald-500/10 p-3.5 rounded-xl border border-emerald-500/20 text-emerald-400">
            <svg class="w-6 h-6" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M3 5a2 2 0 012-2h3.28a1 1 0 01.94.725l.548 2.2a1 1 0 01-.321.988l-1.305.98a10.582 10.582 0 004.872 4.872l.98-1.305a1 1 0 01.988-.321l2.2.548a1 1 0 01.725.94V19a2 2 0 01-2 2h-1C9.716 21 3 14.284 3 6V5z" /></svg>
          </div>
        </div>

        <!-- Unique Callers -->
        <div class="glass-card rounded-2xl p-6 flex items-center justify-between border-l-4 border-l-cyan-500 shadow-lg">
          <div class="space-y-1">
            <span class="text-xs text-slate-400 font-bold uppercase tracking-wider block">Unique Callers</span>
            <span class="text-3xl font-extrabold text-white block"><?php echo $unique_callers; ?></span>
          </div>
          <div class="bg-cyan-500/10 p-3.5 rounded-xl border border-cyan-500/20 text-cyan-400">
            <svg class="w-6 h-6" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z" /></svg>
          </div>
        </div>

        <!-- Clicks Today -->
        <div class="glass-card rounded-2xl p-6 flex items-center justify-between border-l-4 border-l-amber-500 shadow-lg">
          <div class="space-y-1">
            <span class="text-xs text-slate-400 font-bold uppercase tracking-wider block">Clicks Today</span>
            <span class="text-3xl font-extrabold text-white block"><?php echo $clicks_today; ?></span>
          </div>
          <div class="bg-amber-500/10 p-3.5 rounded-xl border border-amber-500/20 text-amber-400">
            <svg class="w-6 h-6" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" /></svg>
          </div>
        </div>

        <!-- Top converting page -->
        <div class="glass-card rounded-2xl p-6 flex items-center justify-between border-l-4 border-l-purple-500 shadow-lg">
          <div class="space-y-1">
            <span class="text-xs text-slate-400 font-bold uppercase tracking-wider block">Top Converter Page</span>
            <span class="text-base font-extrabold text-white block truncate max-w-[170px]" title="<?php echo safe($top_page); ?>"><?php echo safe($top_page); ?></span>
          </div>
          <div class="bg-purple-500/10 p-3.5 rounded-xl border border-purple-500/20 text-purple-400">
            <svg class="w-6 h-6" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M13.828 10.172a4 4 0 00-5.656 0l-4 4a4 4 0 105.656 5.656l1.102-1.101m-.758-4.899a4 4 0 005.656 0l4-4a4 4 0 00-5.656-5.656l-1.1 1.1" /></svg>
          </div>
        </div>
      </div>

      <!-- Component Breakdowns -->
      <div class="grid grid-cols-1 lg:grid-cols-3 gap-6 mb-8">
        <!-- Clicks By Page -->
        <div class="glass-card rounded-2xl p-6 border border-slate-800">
          <h3 class="text-base font-bold text-white mb-5 pb-2 border-b border-slate-800/80 flex items-center gap-2">
            <span class="w-2.5 h-2.5 rounded-full bg-emerald-400"></span>
            Traffic Source Pages
          </h3>
          <div class="space-y-4">
            <?php if (empty($page_counts)): ?>
              <p class="text-slate-500 text-xs">No conversions logged yet.</p>
            <?php else: ?>
              <?php foreach ($page_counts as $row): ?>
                <?php $pct = $total_clicks > 0 ? round(($row['count'] / $total_clicks) * 100) : 0; ?>
                <div class="space-y-1">
                  <div class="flex justify-between text-xs font-semibold">
                    <span class="text-slate-300 font-mono text-[11px] truncate max-w-[200px]"><?php echo safe($row['page']); ?></span>
                    <span class="text-slate-400"><?php echo $row['count']; ?> (<?php echo $pct; ?>%)</span>
                  </div>
                  <div class="w-full bg-slate-800/60 h-1.5 rounded-full overflow-hidden">
                    <div class="bg-gradient-to-r from-emerald-500 to-emerald-400 h-full rounded-full" style="width: <?php echo $pct; ?>%"></div>
                  </div>
                </div>
              <?php endforeach; ?>
            <?php endif; ?>
          </div>
        </div>

        <!-- Clicks By CTA -->
        <div class="glass-card rounded-2xl p-6 border border-slate-800">
          <h3 class="text-base font-bold text-white mb-5 pb-2 border-b border-slate-800/80 flex items-center gap-2">
            <span class="w-2.5 h-2.5 rounded-full bg-amber-400"></span>
            CTA Placements
          </h3>
          <div class="space-y-4">
            <?php if (empty($cta_counts)): ?>
              <p class="text-slate-500 text-xs">No conversions logged yet.</p>
            <?php else: ?>
              <?php foreach ($cta_counts as $row): ?>
                <?php $pct = $total_clicks > 0 ? round(($row['count'] / $total_clicks) * 100) : 0; ?>
                <div class="space-y-1">
                  <div class="flex justify-between text-xs font-semibold">
                    <span class="text-slate-300 truncate max-w-[200px]"><?php echo safe($row['cta']); ?></span>
                    <span class="text-slate-400"><?php echo $row['count']; ?> (<?php echo $pct; ?>%)</span>
                  </div>
                  <div class="w-full bg-slate-800/60 h-1.5 rounded-full overflow-hidden">
                    <div class="bg-gradient-to-r from-amber-500 to-amber-400 h-full rounded-full" style="width: <?php echo $pct; ?>%"></div>
                  </div>
                </div>
              <?php endforeach; ?>
            <?php endif; ?>
          </div>
        </div>

        <!-- Clicks By Location -->
        <div class="glass-card rounded-2xl p-6 border border-slate-800">
          <h3 class="text-base font-bold text-white mb-5 pb-2 border-b border-slate-800/80 flex items-center gap-2">
            <span class="w-2.5 h-2.5 rounded-full bg-purple-400"></span>
            Geo Locations (ZIP)
          </h3>
          <div class="space-y-4">
            <?php if (empty($location_counts)): ?>
              <p class="text-slate-500 text-xs">No conversions logged yet.</p>
            <?php else: ?>
              <?php foreach ($location_counts as $row): ?>
                <?php $pct = $total_clicks > 0 ? round(($row['count'] / $total_clicks) * 100) : 0; ?>
                <?php 
                $loc_str = $row['city'];
                if ($row['region']) $loc_str .= ', ' . $row['region'];
                if ($row['postal']) $loc_str .= ' (' . $row['postal'] . ')';
                ?>
                <div class="space-y-1">
                  <div class="flex justify-between text-xs font-semibold">
                    <span class="text-slate-300 truncate max-w-[200px]"><?php echo safe($loc_str); ?></span>
                    <span class="text-slate-400"><?php echo $row['count']; ?> (<?php echo $pct; ?>%)</span>
                  </div>
                  <div class="w-full bg-slate-800/60 h-1.5 rounded-full overflow-hidden">
                    <div class="bg-gradient-to-r from-purple-500 to-purple-400 h-full rounded-full" style="width: <?php echo $pct; ?>%"></div>
                  </div>
                </div>
              <?php endforeach; ?>
            <?php endif; ?>
          </div>
        </div>
      </div>

      <!-- Activity Logs Table -->
      <div class="glass-card rounded-2xl border border-slate-800 overflow-hidden shadow-xl mb-6">
        <div class="py-5 px-6 border-b border-slate-800/80 bg-slate-900/40">
          <h3 class="text-lg font-bold text-white">Call click Logs</h3>
          <p class="text-slate-400 text-xs mt-0.5">Real-time listing of visitor tap-to-call conversions</p>
        </div>
        
        <div class="overflow-x-auto">
          <table class="w-full text-left border-collapse text-sm">
            <thead>
              <tr class="bg-slate-900/60 text-slate-400 border-b border-slate-800/80 font-semibold uppercase tracking-wider text-[11px]">
                <th class="py-4 px-6">Timestamp</th>
                <th class="py-4 px-6">IP Address</th>
                <th class="py-4 px-6">Location</th>
                <th class="py-4 px-6">ISP</th>
                <th class="py-4 px-6">Origin Page</th>
                <th class="py-4 px-6">CTA Button</th>
                <th class="py-4 px-6 text-right">Action</th>
              </tr>
            </thead>
            <tbody class="divide-y divide-slate-800/40 text-slate-300">
              <?php if (empty($logs)): ?>
                <tr>
                  <td colspan="7" class="py-12 text-center text-slate-500 font-medium">
                    No conversion logs found.
                  </td>
                </tr>
              <?php else: ?>
                <?php foreach ($logs as $row): ?>
                  <tr class="hover:bg-slate-900/20 transition-colors">
                    <td class="py-4 px-6 text-slate-400 font-mono text-xs whitespace-nowrap">
                      <?php echo safe($row['timestamp'] ?? 'N/A'); ?>
                    </td>
                    <td class="py-4 px-6 font-mono text-xs font-semibold text-emerald-400">
                      <?php echo safe($row['ip'] ?? 'N/A'); ?>
                    </td>
                    <td class="py-4 px-6 font-medium whitespace-nowrap">
                      <?php 
                      $c = $row['city'] ?? '';
                      $r = $row['region'] ?? '';
                      $p = $row['postal'] ?? '';
                      $l = $c;
                      if ($r) $l .= ", $r";
                      if ($p) $l .= " ($p)";
                      echo safe($l ?: 'Unknown');
                      ?>
                    </td>
                    <td class="py-4 px-6 text-xs text-slate-400 whitespace-nowrap max-w-[120px] truncate" title="<?php echo safe($row['isp'] ?? 'Unknown'); ?>">
                      <?php echo safe($row['isp'] ?? 'Unknown'); ?>
                    </td>
                    <td class="py-4 px-6 font-mono text-xs text-amber-300 whitespace-nowrap">
                      <?php echo safe($row['page'] ?: '/'); ?>
                    </td>
                    <td class="py-4 px-6 whitespace-nowrap">
                      <span class="bg-slate-800 text-slate-300 border border-slate-700/60 text-[10px] font-bold px-2 py-1 rounded-lg uppercase tracking-wider">
                        <?php echo safe($row['cta'] ?: 'Unknown'); ?>
                      </span>
                    </td>
                    <td class="py-4 px-6 text-right whitespace-nowrap">
                      <form method="POST" class="inline" onsubmit="return confirm('Are you sure you want to delete this event?');">
                        <input type="hidden" name="action" value="delete">
                        <input type="hidden" name="id" value="<?php echo (int)$row['id']; ?>">
                        <input type="hidden" name="csrf_token" value="<?php echo safe($_SESSION['csrf_token']); ?>">
                        <button type="submit" class="text-red-500 hover:text-red-450 font-bold text-xs bg-red-500/10 hover:bg-red-500/20 px-3 py-1.5 rounded-lg border border-red-500/20 transition-all active:scale-95">
                          Delete
                        </button>
                      </form>
                    </td>
                  </tr>
                <?php endforeach; ?>
              <?php endif; ?>
            </tbody>
          </table>
        </div>
      </div>

      <!-- Pagination Footer -->
      <?php if ($total_pages > 1): ?>
        <div class="flex justify-between items-center bg-slate-900/30 p-4 border border-slate-850 rounded-2xl">
          <p class="text-xs text-slate-500">Page <?php echo $page_num; ?> of <?php echo $total_pages; ?></p>
          <div class="flex gap-2">
            <?php if ($page_num > 1): ?>
              <a href="?page_num=<?php echo $page_num - 1; ?>&filter=<?php echo safe($filter); ?>&search=<?php echo urlencode($search); ?>" class="bg-slate-800 border border-slate-700 text-slate-300 font-bold px-3 py-1.5 rounded-lg text-xs hover:bg-slate-700 transition-all">&larr; Previous</a>
            <?php endif; ?>
            <?php if ($page_num < $total_pages): ?>
              <a href="?page_num=<?php echo $page_num + 1; ?>&filter=<?php echo safe($filter); ?>&search=<?php echo urlencode($search); ?>" class="bg-slate-800 border border-slate-700 text-slate-300 font-bold px-3 py-1.5 rounded-lg text-xs hover:bg-slate-700 transition-all">Next &rarr;</a>
            <?php endif; ?>
          </div>
        </div>
      <?php endif; ?>

    <?php endif; ?>

  </main>

  <!-- Footer -->
  <footer class="border-t border-slate-900 py-6 px-6 bg-slate-950/80 text-center text-xs text-slate-600 mt-12">
    <div class="max-w-7xl mx-auto">
      &copy; 2026 Rodent Control Truckee. Call Conversion tracking.
    </div>
  </footer>

</body>
</html>
