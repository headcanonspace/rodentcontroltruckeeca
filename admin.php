<?php
session_start();

$username = 'admin';
$password = 'IdahoPestControl2026';

// Handle Logout
if (isset($_GET['action']) && $_GET['action'] === 'logout') {
    session_destroy();
    header("Location: /admin");
    exit;
}

// Handle Login Form Submission
$login_error = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['login'])) {
    $submitted_user = $_POST['user'] ?? '';
    $submitted_pass = $_POST['pass'] ?? '';
    
    if ($submitted_user === $username && $submitted_pass === $password) {
        $_SESSION['logged_in'] = true;
        header("Location: /admin");
        exit;
    } else {
        $login_error = 'Invalid username or password.';
    }
}

// Check Authentication
$is_authenticated = $_SESSION['logged_in'] ?? false;

// If authenticated, load data
$clicks = [];
if ($is_authenticated) {
    $db_file = __DIR__ . '/clicks_db.php';
    if (file_exists($db_file)) {
        $content = file_get_contents($db_file);
        $json_data = str_replace('<?php http_response_code(403); exit; ?>', '', $content);
        $clicks = json_decode($json_data, true);
        if (!$clicks) {
            $clicks = [];
        }
    }
}

// Handle Delete Event Log Entry
if ($is_authenticated && $_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'delete') {
    $delete_index = isset($_POST['index']) ? (int)$_POST['index'] : -1;
    if ($delete_index >= 0 && isset($clicks[$delete_index])) {
        unset($clicks[$delete_index]);
        $clicks = array_values($clicks);
        
        $db_file = __DIR__ . '/clicks_db.php';
        $new_content = '<?php http_response_code(403); exit; ?>' . json_encode($clicks, JSON_PRETTY_PRINT);
        file_put_contents($db_file, $new_content);
        
        header("Location: /admin");
        exit;
    }
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
  <title>North Idaho Pest Control — Call Telemetry Dashboard</title>
  <!-- Tailwind CSS -->
  <script src="https://cdn.tailwindcss.com"></script>
  <!-- Google Fonts Outfit & Inter -->
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
            forest: {
              DEFAULT: '#1b4332',
              50: '#f2f8f5',
              100: '#e2f0e8',
              500: '#2d6a4f',
              900: '#081c15',
            },
          }
        }
      }
    }
  </script>
  <style>
    body {
      font-family: 'Inter', sans-serif;
    }
    h1, h2, h3, h4 {
      font-family: 'Outfit', sans-serif;
    }
    .glass-card {
      background: rgba(30, 41, 59, 0.4);
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
        <div class="bg-emerald-500/10 p-2.5 rounded-xl border border-emerald-500/20 text-emerald-400">
          <svg class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
            <path stroke-linecap="round" stroke-linejoin="round" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10a2 2 0 002 2h2a2 2 0 002-2V5a2 2 0 00-2-2h-2a2 2 0 00-2 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z" />
          </svg>
        </div>
        <div>
          <h1 class="text-xl font-bold tracking-tight text-white">Call Telemetry Panel</h1>
          <p class="text-xs text-slate-400 font-medium uppercase tracking-widest mt-0.5">North Idaho Pest Control</p>
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
          <div class="absolute top-0 left-0 right-0 h-1 bg-gradient-to-r from-emerald-500 to-amber-500"></div>
          
          <div class="text-center space-y-2 mb-8">
            <h2 class="text-2xl font-bold text-white tracking-tight">Admin Authentication</h2>
            <p class="text-slate-400 text-sm">Please log in to view call telemetry analytics</p>
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
      // 1. Calculate Summary Metrics
      $total_clicks = count($clicks);
      
      $ips = [];
      $today_count = 0;
      $today_str = date('Y-m-d');
      $page_counts = [];
      $cta_counts = [];
      $city_counts = [];
      
      foreach ($clicks as $click) {
          $ip = $click['ip'] ?? '';
          if ($ip) {
              $ips[$ip] = true;
          }
          
          $timestamp = $click['timestamp'] ?? '';
          if (strpos($timestamp, $today_str) === 0) {
              $today_count++;
          }
          
          $page = $click['page'] ?? 'index';
          if ($page === '/' || $page === '') {
              $page = '/index';
          }
          $page_counts[$page] = ($page_counts[$page] ?? 0) + 1;
          
          $cta = $click['cta'] ?? 'Unknown';
          $cta_counts[$cta] = ($cta_counts[$cta] ?? 0) + 1;
          
          $city = $click['city'] ?? 'Unknown';
          $postal = $click['postal'] ?? '';
          $location_key = $city . ($postal ? " ($postal)" : "");
          $city_counts[$location_key] = ($city_counts[$location_key] ?? 0) + 1;
      }
      
      $unique_callers = count($ips);
      
      // Determine top source page
      arsort($page_counts);
      $top_page = $total_clicks > 0 ? array_keys($page_counts)[0] : 'None';
      
      // Sort other counts
      arsort($cta_counts);
      arsort($city_counts);
      ?>

      <!-- Stat Cards -->
      <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-5 mb-8">
        
        <!-- Total Calls -->
        <div class="glass-card rounded-2xl p-6 flex items-center justify-between border-l-4 border-l-emerald-500">
          <div class="space-y-1">
            <span class="text-xs text-slate-400 font-bold uppercase tracking-wider block">Total Dialer Clicks</span>
            <span class="text-3xl font-extrabold text-white block"><?php echo $total_clicks; ?></span>
          </div>
          <div class="bg-emerald-500/10 p-3.5 rounded-xl border border-emerald-500/20 text-emerald-400">
            <svg class="w-6 h-6" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M3 5a2 2 0 012-2h3.28a1 1 0 01.94.725l.548 2.2a1 1 0 01-.321.988l-1.305.98a10.582 10.582 0 004.872 4.872l.98-1.305a1 1 0 01.988-.321l2.2.548a1 1 0 01.725.94V19a2 2 0 01-2 2h-1C9.716 21 3 14.284 3 6V5z" /></svg>
          </div>
        </div>

        <!-- Unique Callers -->
        <div class="glass-card rounded-2xl p-6 flex items-center justify-between border-l-4 border-l-cyan-500">
          <div class="space-y-1">
            <span class="text-xs text-slate-400 font-bold uppercase tracking-wider block">Unique Prospects (IP)</span>
            <span class="text-3xl font-extrabold text-white block"><?php echo $unique_callers; ?></span>
          </div>
          <div class="bg-cyan-500/10 p-3.5 rounded-xl border border-cyan-500/20 text-cyan-400">
            <svg class="w-6 h-6" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z" /></svg>
          </div>
        </div>

        <!-- Calls Today -->
        <div class="glass-card rounded-2xl p-6 flex items-center justify-between border-l-4 border-l-amber-500">
          <div class="space-y-1">
            <span class="text-xs text-slate-400 font-bold uppercase tracking-wider block">Clicks Today</span>
            <span class="text-3xl font-extrabold text-white block"><?php echo $today_count; ?></span>
          </div>
          <div class="bg-amber-500/10 p-3.5 rounded-xl border border-amber-500/20 text-amber-400">
            <svg class="w-6 h-6" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" /></svg>
          </div>
        </div>

        <!-- Top Source Page -->
        <div class="glass-card rounded-2xl p-6 flex items-center justify-between border-l-4 border-l-purple-500">
          <div class="space-y-1">
            <span class="text-xs text-slate-400 font-bold uppercase tracking-wider block">Top Converting Page</span>
            <span class="text-base font-extrabold text-white block truncate max-w-[170px]"><?php echo safe($top_page); ?></span>
          </div>
          <div class="bg-purple-500/10 p-3.5 rounded-xl border border-purple-500/20 text-purple-400">
            <svg class="w-6 h-6" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M13.828 10.172a4 4 0 00-5.656 0l-4 4a4 4 0 105.656 5.656l1.102-1.101m-.758-4.899a4 4 0 005.656 0l4-4a4 4 0 00-5.656-5.656l-1.1 1.1" /></svg>
          </div>
        </div>

      </div>

      <!-- Stats Grid (Pages / CTAs / Geolocation) -->
      <div class="grid grid-cols-1 lg:grid-cols-3 gap-6 mb-8">
        
        <!-- Clicks By Page -->
        <div class="glass-card rounded-2xl p-6 border border-slate-800 flex flex-col justify-between">
          <div>
            <h3 class="text-lg font-bold text-white mb-5 pb-2 border-b border-slate-800/80 flex items-center gap-2">
              <span class="w-2.5 h-2.5 rounded-full bg-emerald-400"></span>
              Traffic Source Pages
            </h3>
            <div class="space-y-4">
              <?php if (empty($page_counts)): ?>
                <p class="text-slate-500 text-sm">No traffic data logged yet.</p>
              <?php else: ?>
                <?php foreach (array_slice($page_counts, 0, 5) as $page => $count): ?>
                  <?php $percentage = $total_clicks > 0 ? round(($count / $total_clicks) * 100) : 0; ?>
                  <div class="space-y-1">
                    <div class="flex justify-between text-xs font-semibold">
                      <span class="text-slate-300 font-mono text-[11px]"><?php echo safe($page); ?></span>
                      <span class="text-slate-400"><?php echo $count; ?> clicks (<?php echo $percentage; ?>%)</span>
                    </div>
                    <div class="w-full bg-slate-800/60 h-2 rounded-full overflow-hidden">
                      <div class="bg-gradient-to-r from-emerald-500 to-emerald-400 h-full rounded-full" style="width: <?php echo $percentage; ?>%"></div>
                    </div>
                  </div>
                <?php endforeach; ?>
              <?php endif; ?>
            </div>
          </div>
        </div>

        <!-- Clicks By CTA -->
        <div class="glass-card rounded-2xl p-6 border border-slate-800 flex flex-col justify-between">
          <div>
            <h3 class="text-lg font-bold text-white mb-5 pb-2 border-b border-slate-800/80 flex items-center gap-2">
              <span class="w-2.5 h-2.5 rounded-full bg-amber-400"></span>
              CTA Placements
            </h3>
            <div class="space-y-4">
              <?php if (empty($cta_counts)): ?>
                <p class="text-slate-500 text-sm">No CTA data logged yet.</p>
              <?php else: ?>
                <?php foreach (array_slice($cta_counts, 0, 5) as $cta => $count): ?>
                  <?php $percentage = $total_clicks > 0 ? round(($count / $total_clicks) * 100) : 0; ?>
                  <div class="space-y-1">
                    <div class="flex justify-between text-xs font-semibold">
                      <span class="text-slate-300"><?php echo safe($cta); ?></span>
                      <span class="text-slate-400"><?php echo $count; ?> clicks (<?php echo $percentage; ?>%)</span>
                    </div>
                    <div class="w-full bg-slate-800/60 h-2 rounded-full overflow-hidden">
                      <div class="bg-gradient-to-r from-amber-500 to-amber-400 h-full rounded-full" style="width: <?php echo $percentage; ?>%"></div>
                    </div>
                  </div>
                <?php endforeach; ?>
              <?php endif; ?>
            </div>
          </div>
        </div>

        <!-- Clicks By Location -->
        <div class="glass-card rounded-2xl p-6 border border-slate-800 flex flex-col justify-between">
          <div>
            <h3 class="text-lg font-bold text-white mb-5 pb-2 border-b border-slate-800/80 flex items-center gap-2">
              <span class="w-2.5 h-2.5 rounded-full bg-purple-400"></span>
              Geo Locations (ZIP)
            </h3>
            <div class="space-y-4">
              <?php if (empty($city_counts)): ?>
                <p class="text-slate-500 text-sm">No location data logged yet.</p>
              <?php else: ?>
                <?php foreach (array_slice($city_counts, 0, 5) as $location => $count): ?>
                  <?php $percentage = $total_clicks > 0 ? round(($count / $total_clicks) * 100) : 0; ?>
                  <div class="space-y-1">
                    <div class="flex justify-between text-xs font-semibold">
                      <span class="text-slate-300"><?php echo safe($location); ?></span>
                      <span class="text-slate-400"><?php echo $count; ?> clicks (<?php echo $percentage; ?>%)</span>
                    </div>
                    <div class="w-full bg-slate-800/60 h-2 rounded-full overflow-hidden">
                      <div class="bg-gradient-to-r from-purple-500 to-purple-400 h-full rounded-full" style="width: <?php echo $percentage; ?>%"></div>
                    </div>
                  </div>
                <?php endforeach; ?>
              <?php endif; ?>
            </div>
          </div>
        </div>

      </div>

      <!-- Raw Log Table -->
      <div class="glass-card rounded-2xl border border-slate-800 overflow-hidden shadow-xl">
        <div class="py-5 px-6 border-b border-slate-800/80 bg-slate-900/40">
          <h3 class="text-lg font-bold text-white">Detailed Call Clicks Activity Log</h3>
          <p class="text-slate-400 text-xs mt-0.5">Showing real-time telephone click events</p>
        </div>
        <div class="overflow-x-auto">
          <table class="w-full text-left border-collapse text-sm">
            <thead>
              <tr class="bg-slate-900/60 text-slate-400 border-b border-slate-800/80 font-semibold uppercase tracking-wider text-[11px]">
                <th class="py-4 px-6">Timestamp</th>
                <th class="py-4 px-6">IP Address</th>
                <th class="py-4 px-6">Location (ZIP)</th>
                <th class="py-4 px-6">Origin Page</th>
                <th class="py-4 px-6">CTA Element</th>
                <th class="py-4 px-6">Device / User Agent</th>
                <th class="py-4 px-6 text-right">Actions</th>
              </tr>
            </thead>
            <tbody class="divide-y divide-slate-800/40 text-slate-300">
              <?php if (empty($clicks)): ?>
                <tr>
                  <td colspan="7" class="py-12 text-center text-slate-500 font-medium">
                    No clicks recorded yet. Try clicking a call button on your site!
                  </td>
                </tr>
              <?php else: ?>
                <?php foreach ($clicks as $index => $click): ?>
                  <tr class="hover:bg-slate-900/20 transition-colors">
                    <td class="py-4 px-6 text-slate-400 font-mono text-xs whitespace-nowrap">
                      <?php echo safe($click['timestamp'] ?? 'N/A'); ?>
                    </td>
                    <td class="py-4 px-6 font-mono text-xs font-semibold text-emerald-400">
                      <?php echo safe($click['ip'] ?? $click['server_ip'] ?? 'N/A'); ?>
                    </td>
                    <td class="py-4 px-6 font-medium">
                      <?php 
                      $city = $click['city'] ?? '';
                      $region = $click['region'] ?? '';
                      $postal = $click['postal'] ?? '';
                      $loc_str = $city;
                      if ($region) $loc_str .= ", $region";
                      if ($postal) $loc_str .= " ($postal)";
                      echo safe($loc_str ?: 'Unknown');
                      ?>
                    </td>
                    <td class="py-4 px-6 font-mono text-xs text-amber-300">
                      <?php echo safe($click['page'] ?? 'N/A'); ?>
                    </td>
                    <td class="py-4 px-6">
                      <span class="bg-slate-800 text-slate-300 border border-slate-700/60 text-[11px] font-bold px-2.5 py-1.5 rounded-lg whitespace-nowrap uppercase tracking-wide">
                        <?php echo safe($click['cta'] ?? 'Unknown'); ?>
                      </span>
                    </td>
                    <td class="py-4 px-6 text-xs text-slate-500 max-w-[200px] truncate" title="<?php echo safe($click['user_agent'] ?? ''); ?>">
                      <?php echo safe($click['user_agent'] ?? 'N/A'); ?>
                    </td>
                    <td class="py-4 px-6 text-right whitespace-nowrap">
                      <form method="POST" style="display:inline;" onsubmit="return confirm('Are you sure you want to delete this log entry?');">
                        <input type="hidden" name="action" value="delete">
                        <input type="hidden" name="index" value="<?php echo $index; ?>">
                        <button type="submit" class="text-red-500 hover:text-red-400 font-semibold text-xs bg-red-500/10 hover:bg-red-500/20 px-3 py-1.5 rounded-lg border border-red-500/20 transition-all active:scale-95">
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

    <?php endif; ?>

  </main>

  <!-- Footer -->
  <footer class="border-t border-slate-900 py-6 px-6 bg-slate-950/80 text-center text-xs text-slate-600">
    <div class="max-w-7xl mx-auto">
      &copy; 2026 North Idaho Pest Control. All rights reserved. Lead Tracking Telemetry Portal.
    </div>
  </footer>

</body>
</html>
