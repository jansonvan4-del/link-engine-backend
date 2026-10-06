<?php
ini_set('display_errors', 0);
error_reporting(0);

// Bypass Ngrok Browser Warning Page
header('ngrok-skip-browser-warning: true');

require_once __DIR__ . '/../config/db.php';

$slug = isset($_GET['slug']) ? trim($_GET['slug']) : '';

if (empty($slug)) {
    header("Location: /");
    exit;
}

// Fetch data link dari Supabase REST API (Tanpa Join ke Tabel Domains)
$response = supabase_request("links?slug=eq." . urlencode($slug) . "&select=*");

if ($response['code'] !== 200 || empty($response['data'])) {
    http_response_code(404);
    die("<h3>404 - Link tidak ditemukan.</h3>");
}

$linkData = $response['data'][0];

// Format nama domain
$linkData['domain_name'] = $linkData['domain_used'] ?? $_SERVER['HTTP_HOST'];

// Deteksi Bot / Crawler
$user_agent = $_SERVER['HTTP_USER_AGENT'] ?? '';
$is_crawler = preg_match('/(facebookexternalhit|Facebot|WhatsApp|Twitterbot|TelegramBot|Slackbot|LinkedInBot|OpenGraph|bot|crawler|spider|curl|fetch)/i', $user_agent);

// Hitung Klik (Increment clicks) HANYA jika pengunjung biasa
if (!$is_crawler) {
    $newClicks = ($linkData['clicks'] ?? 0) + 1;
    supabase_request("links?id=eq." . $linkData['id'], 'PATCH', ['clicks' => $newClicks]);
}

// Olah Target URL
$target_url = $linkData['original_url'];
if (!empty($linkData['click_id'])) {
    $separator = (strpos($target_url, '?') !== false) ? '&' : '?';
    $target_url .= $separator . "click_id=" . urlencode($linkData['click_id']);
}

// Data Meta
$title = !empty($linkData['meta_title']) ? $linkData['meta_title'] : 'Link Engine';
$desc  = !empty($linkData['meta_description']) ? $linkData['meta_description'] : 'Klik link untuk menuju ke halaman tujuan.';
$image = !empty($linkData['meta_image']) ? trim($linkData['meta_image']) : '';

$protocol = (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? "https" : "http") . "://";
$current_url = $protocol . $_SERVER['HTTP_HOST'] . $_SERVER['REQUEST_URI'];
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    
    <!-- Meta Standard -->
    <title><?= htmlspecialchars($title, ENT_QUOTES, 'UTF-8'); ?></title>
    <meta name="description" content="<?= htmlspecialchars($desc, ENT_QUOTES, 'UTF-8'); ?>">
    
    <!-- Open Graph / Facebook / WhatsApp -->
    <meta property="og:site_name" content="Link Engine">
    <meta property="og:type" content="website">
    <meta property="og:url" content="<?= htmlspecialchars($current_url, ENT_QUOTES, 'UTF-8'); ?>">
    <meta property="og:title" content="<?= htmlspecialchars($title, ENT_QUOTES, 'UTF-8'); ?>">
    <meta property="og:description" content="<?= htmlspecialchars($desc, ENT_QUOTES, 'UTF-8'); ?>">
    
    <?php if (!empty($image)): ?>
        <meta property="og:image" content="<?= htmlspecialchars($image, ENT_QUOTES, 'UTF-8'); ?>">
        <meta property="og:image:secure_url" content="<?= htmlspecialchars($image, ENT_QUOTES, 'UTF-8'); ?>">
    <?php endif; ?>

    <!-- Twitter Meta Tags -->
    <meta name="twitter:card" content="summary_large_image">
    <meta name="twitter:title" content="<?= htmlspecialchars($title, ENT_QUOTES, 'UTF-8'); ?>">
    <meta name="twitter:description" content="<?= htmlspecialchars($desc, ENT_QUOTES, 'UTF-8'); ?>">
    <?php if (!empty($image)): ?>
        <meta name="twitter:image" content="<?= htmlspecialchars($image, ENT_QUOTES, 'UTF-8'); ?>">
    <?php endif; ?>

    <?php if (($linkData['lp'] ?? 'OFF') === 'OFF' && !$is_crawler): ?>
        <meta http-equiv="refresh" content="2;url=<?= htmlspecialchars($target_url); ?>">
        <script>
            setTimeout(function() {
                window.location.href = "<?= addslashes($target_url); ?>";
            }, 1000);
        </script>
    <?php endif; ?>

    <script src="https://cdn.tailwindcss.com"></script>
</head>

<?php if (($linkData['lp'] ?? 'OFF') === 'OFF' && !$is_crawler): ?>
<body class="bg-slate-900 text-white flex items-center justify-center min-h-screen">
    <p class="text-sm text-slate-400">Mengarahkan Anda ke halaman tujuan...</p>
</body>
</html>
<?php exit; endif; ?>

<body class="bg-slate-900 text-white min-h-screen flex items-center justify-center p-4">
    <div class="max-w-md w-full bg-slate-800 border border-slate-700 rounded-2xl p-6 text-center shadow-2xl space-y-6">
        <div class="space-y-2">
            <h2 class="text-xl font-bold text-slate-100"><?= htmlspecialchars($title); ?></h2>
            <p class="text-xs text-slate-400"><?= htmlspecialchars($desc); ?></p>
        </div>

        <?php if (!empty($image)): ?>
            <div class="rounded-xl overflow-hidden border border-slate-700 max-h-48">
                <img src="<?= htmlspecialchars($image); ?>" class="w-full h-full object-cover" alt="Preview Image">
            </div>
        <?php endif; ?>

        <?php if (($linkData['lp'] ?? 'OFF') !== 'OFF'): ?>
        <div class="bg-slate-900/60 p-4 rounded-xl border border-slate-700/50 space-y-2">
            <span class="text-xs text-slate-400 uppercase tracking-wider block font-semibold">Mengalihkan Dalam</span>
            <div class="text-4xl font-extrabold text-cyan-400" id="countdown">5</div>
        </div>
        <?php endif; ?>

        <div>
            <a id="redirect-btn" href="<?= htmlspecialchars($target_url); ?>" class="<?= ($linkData['lp'] ?? 'OFF') === 'OFF' ? '' : 'hidden' ?> block w-full bg-cyan-600 hover:bg-cyan-500 text-white font-bold py-3 rounded-xl transition-all shadow-lg shadow-cyan-600/30 text-sm">
                Lanjutkan Ke Link Tujuan →
            </a>
            <?php if (($linkData['lp'] ?? 'OFF') !== 'OFF'): ?>
            <p id="wait-msg" class="text-xs text-slate-500">Tombol akan muncul setelah hitungan mundur selesai...</p>
            <?php endif; ?>
        </div>
    </div>

    <?php if (($linkData['lp'] ?? 'OFF') !== 'OFF'): ?>
    <script>
        let seconds = 5;
        const timerElement = document.getElementById('countdown');
        const btnElement = document.getElementById('redirect-btn');
        const waitMsg = document.getElementById('wait-msg');

        const interval = setInterval(() => {
            seconds--;
            if (timerElement) timerElement.innerText = seconds;
            if (seconds <= 0) {
                clearInterval(interval);
                if (btnElement) btnElement.classList.remove('hidden');
                if (waitMsg) waitMsg.classList.add('hidden');
                window.location.href = "<?= addslashes($target_url); ?>";
            }
        }, 1000);
    </script>
    <?php endif; ?>
</body>
</html>
