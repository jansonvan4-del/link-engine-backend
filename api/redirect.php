<?php
// Aktifkan error reporting sementara jika masih dalam masa testing/debugging
// ini_set('display_errors', 1);
// error_reporting(E_ALL);

// Bypass Ngrok Browser Warning Page
header('ngrok-skip-browser-warning: true');

require_once __DIR__ . '/../config/db.php';

$slug = isset($_GET['slug']) ? trim($_GET['slug']) : '';

if (empty($slug)) {
    header("Location: /");
    exit;
}

// Fetch data link dari Supabase REST API
$response = supabase_request("links?slug=eq." . urlencode($slug) . "&select=*");

if (!isset($response['code']) || $response['code'] !== 200 || empty($response['data'])) {
    http_response_code(404);
    die("<h3 style='color:white; text-align:center; padding-top:50px;'>404 - Link tidak ditemukan.</h3>");
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
$target_url = $linkData['original_url'] ?? '';
if (!empty($linkData['click_id'])) {
    $separator = (strpos($target_url, '?') !== false) ? '&' : '?';
    $target_url .= $separator . "click_id=" . urlencode($linkData['click_id']);
}

// Data Meta
$title = !empty($linkData['meta_title']) ? $linkData['meta_title'] : 'Live Session Today';
$desc  = !empty($linkData['meta_description']) ? $linkData['meta_description'] : 'Klik link untuk menuju ke halaman tujuan.';
$image = !empty($linkData['meta_image']) ? trim($linkData['meta_image']) : '';

// URL Link saat ini
$protocol = (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? "https" : "http") . "://";
$current_url = $protocol . $_SERVER['HTTP_HOST'] . $_SERVER['REQUEST_URI'];

// Normalisasi Nilai LP
$lp_mode = strtoupper(trim((string)($linkData['lp'] ?? 'OFF')));
?>
<!DOCTYPE html>
<html lang="id" prefix="og: https://ogp.me/ns#">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    
    <!-- Meta Standard -->
    <title><?= htmlspecialchars($title, ENT_QUOTES, 'UTF-8'); ?></title>
    <meta name="description" content="<?= htmlspecialchars($desc, ENT_QUOTES, 'UTF-8'); ?>">
    
    <!-- Open Graph / Facebook / WhatsApp -->
    <meta property="og:site_name" content="Link Engine">
    <meta property="og:type" content="website" />
    <meta property="og:url" content="<?= htmlspecialchars($current_url, ENT_QUOTES, 'UTF-8'); ?>" />
    <meta property="og:title" content="<?= htmlspecialchars($title, ENT_QUOTES, 'UTF-8'); ?>" />
    <meta property="og:description" content="<?= htmlspecialchars($desc, ENT_QUOTES, 'UTF-8'); ?>" />
    
    <?php if (!empty($image)): ?>
        <meta property="og:image" content="<?= htmlspecialchars($image, ENT_QUOTES, 'UTF-8'); ?>" />
        <meta property="og:image:secure_url" content="<?= htmlspecialchars($image, ENT_QUOTES, 'UTF-8'); ?>" />
        <meta property="og:image:type" content="image/jpeg" />
        <meta property="og:image:width" content="1200" />
        <meta property="og:image:height" content="630" />
    <?php endif; ?>

    <!-- Twitter Meta Tags -->
    <meta name="twitter:card" content="summary_large_image">
    <meta name="twitter:title" content="<?= htmlspecialchars($title, ENT_QUOTES, 'UTF-8'); ?>">
    <meta name="twitter:description" content="<?= htmlspecialchars($desc, ENT_QUOTES, 'UTF-8'); ?>">
    <?php if (!empty($image)): ?>
        <meta name="twitter:image" content="<?= htmlspecialchars($image, ENT_QUOTES, 'UTF-8'); ?>">
    <?php endif; ?>

    <?php if ($lp_mode === 'OFF' && !$is_crawler): ?>
        <meta http-equiv="refresh" content="2;url=<?= htmlspecialchars($target_url); ?>">
        <script>
            setTimeout(function() {
                window.location.href = "<?= addslashes($target_url); ?>";
            }, 1000);
        </script>
    <?php endif; ?>

    <script src="https://cdn.tailwindcss.com"></script>
    <style>
        body {
            background-color: #0b0d14;
            background-image: 
                linear-gradient(to right, rgba(255, 0, 128, 0.03) 1px, transparent 1px),
                linear-gradient(to bottom, rgba(255, 0, 128, 0.03) 1px, transparent 1px);
            background-size: 32px 32px;
        }

        @keyframes progressLoading {
            0% { width: 0%; }
            100% { width: 100%; }
        }

        .animate-progress {
            animation: progressLoading 2.5s ease-in-out forwards;
        }

        .btn-portal-cyan {
            background: linear-gradient(90deg, #00b4db 0%, #a855f7 100%);
        }

        .btn-portal-pink {
            background: linear-gradient(90deg, #ff007f 0%, #a855f7 100%);
            box-shadow: 0 0 20px rgba(255, 0, 127, 0.5);
        }
        .btn-portal-pink:hover {
            opacity: 0.92;
            box-shadow: 0 0 30px rgba(255, 0, 127, 0.8);
        }

        .glow-box-pink {
            border: 1.5px solid #ff007f;
            box-shadow: 
                0 0 15px rgba(255, 0, 127, 0.8),
                0 0 35px rgba(255, 0, 127, 0.5),
                0 0 65px rgba(255, 0, 127, 0.3),
                inset 0 0 20px rgba(255, 0, 127, 0.25);
        }
    </style>
</head>

<?php
switch ($lp_mode) {

    case '1':
        // ==================== LANDING PAGE 1 ====================
        ?>
        <body class="bg-slate-900 text-white min-h-screen flex items-center justify-center p-4">
            <div class="max-w-md w-full bg-slate-800 border border-slate-700 rounded-2xl p-6 text-center shadow-2xl space-y-6">
                <div class="space-y-2">
                    <div class="inline-block bg-cyan-500/10 text-cyan-400 text-xs font-bold px-3 py-1 rounded-full border border-cyan-500/20">
                        🔒 LANDING PAGE INTERSTITIAL
                    </div>
                    <h2 class="text-xl font-bold text-slate-100"><?= htmlspecialchars($title); ?></h2>
                    <p class="text-xs text-slate-400"><?= htmlspecialchars($desc); ?></p>
                </div>

                <?php if (!empty($image)): ?>
                    <div class="rounded-xl overflow-hidden border border-slate-700 max-h-48">
                        <img src="<?= htmlspecialchars($image); ?>" class="w-full h-full object-cover" alt="Preview Image">
                    </div>
                <?php endif; ?>

                <div class="bg-slate-900/60 p-4 rounded-xl border border-slate-700/50 space-y-2">
                    <span class="text-xs text-slate-400 uppercase tracking-wider block font-semibold">Mengalihkan Dalam</span>
                    <div class="text-4xl font-extrabold text-cyan-400" id="countdown">5</div>
                </div>

                <div>
                    <a id="redirect-btn" href="<?= htmlspecialchars($target_url); ?>" class="hidden block w-full bg-cyan-600 hover:bg-cyan-500 text-white font-bold py-3 rounded-xl transition-all shadow-lg shadow-cyan-600/30 text-sm">
                        Lanjutkan Ke Link Tujuan →
                    </a>
                    <p id="wait-msg" class="text-xs text-slate-500">Tombol akan muncul setelah hitungan mundur selesai...</p>
                </div>
            </div>

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
        </body>
        </html>
        <?php
        break;

    case '2':
        // ==================== LANDING PAGE 2 ====================
        ?>
        <body class="text-white min-h-screen flex items-center justify-center p-4">
            <div class="max-w-sm w-full bg-[#0d1322]/90 border border-slate-800 rounded-3xl p-6 text-center shadow-2xl relative backdrop-blur-sm space-y-5">
                <div class="flex justify-center">
                    <div class="inline-flex items-center gap-2 bg-red-950/40 text-red-400 text-[11px] font-semibold px-4 py-1.5 rounded-full border border-red-800/40 tracking-wider">
                        <span class="w-2 h-2 rounded-full bg-red-500 animate-pulse"></span>
                        LIVE UPDATE
                    </div>
                </div>

                <div class="space-y-1">
                    <h1 class="text-2xl font-extrabold text-white tracking-wide">Portal Konten</h1>
                    <p id="status-text" class="text-xs font-semibold text-cyan-400 transition-all duration-300">Mengecek Koneksi...</p>
                </div>

                <div class="w-full bg-slate-800/80 h-1.5 rounded-full overflow-hidden">
                    <div id="progress-bar" class="bg-gradient-to-r from-cyan-400 via-blue-500 to-purple-500 h-full w-0 animate-progress"></div>
                </div>

                <div class="py-1">
                    <p class="text-xs font-medium text-purple-300">
                        <span id="online-counter" class="font-bold">603</span> orang sedang online
                    </p>
                </div>

                <div>
                    <a href="<?= htmlspecialchars($target_url); ?>" id="action-btn" class="btn-portal-cyan block w-full text-white font-bold py-3.5 px-6 rounded-2xl shadow-lg text-sm transition-all duration-300 opacity-60 pointer-events-none">
                        Klik Untuk Melanjutkan &nbsp;&rarr;
                    </a>
                </div>

                <div class="pt-1">
                    <p class="text-[10px] text-slate-500 tracking-widest uppercase font-semibold">
                        KONEKSI CEPAT &bull; PORTAL AMAN
                    </p>
                </div>
            </div>

            <script>
                setTimeout(() => {
                    const statusText = document.getElementById('status-text');
                    const actionBtn = document.getElementById('action-btn');
                    if (statusText) statusText.innerText = "Koneksi Siap";
                    if (actionBtn) actionBtn.classList.remove('opacity-60', 'pointer-events-none');
                }, 2500);

                let currentOnline = 603;
                const counterEl = document.getElementById('online-counter');
                setInterval(() => {
                    const change = Math.floor(Math.random() * 10) - 4;
                    currentOnline += change;
                    if (currentOnline < 580) currentOnline = 580;
                    if (currentOnline > 650) currentOnline = 650;
                    if (counterEl) counterEl.innerText = currentOnline;
                }, 3000);
            </script>
        </body>
        </html>
        <?php
        break;

    case '3':
        // ==================== LANDING PAGE 3 ====================
        ?>
        <body class="text-white min-h-screen flex items-center justify-center p-4">
            <div class="max-w-sm w-full bg-[#120813]/95 rounded-3xl p-6 text-center relative backdrop-blur-md space-y-5 glow-box-pink">
                <div class="flex justify-center">
                    <div class="inline-flex items-center gap-2 bg-pink-950/50 text-pink-400 text-[11px] font-semibold px-4 py-1.5 rounded-full border border-pink-700/60 tracking-wider shadow-[0_0_12px_rgba(255,0,127,0.3)]">
                        <span class="w-2 h-2 rounded-full bg-pink-500 animate-pulse shadow-[0_0_8px_#ff007f]"></span>
                        LIVE UPDATE
                    </div>
                </div>

                <div class="space-y-1">
                    <h1 class="text-2xl font-extrabold text-white tracking-wide drop-shadow-[0_2px_12px_rgba(255,0,127,0.4)]">Content Portal</h1>
                    <p id="status-text-lp3" class="text-xs font-semibold text-pink-400 transition-all duration-300">Checking Connection...</p>
                </div>

                <div class="grid grid-cols-3 gap-2.5 my-2">
                    <div class="bg-pink-950/20 border border-pink-800/40 rounded-xl py-4 flex items-center justify-center shadow-inner">
                        <span class="text-[10px] font-bold tracking-wider text-pink-200 bg-pink-900/50 px-2 py-1 rounded-md flex items-center gap-1 border border-pink-700/50">
                            🔒 PRIVATE
                        </span>
                    </div>
                    <div class="bg-pink-950/20 border border-pink-800/40 rounded-xl py-4 flex items-center justify-center shadow-inner">
                        <span class="text-[10px] font-bold tracking-wider text-pink-200 bg-pink-900/50 px-2 py-1 rounded-md flex items-center gap-1 border border-pink-700/50">
                            🔒 PRIVATE
                        </span>
                    </div>
                    <div class="bg-pink-950/20 border border-pink-800/40 rounded-xl py-4 flex items-center justify-center shadow-inner">
                        <span class="text-[10px] font-bold tracking-wider text-pink-200 bg-pink-900/50 px-2 py-1 rounded-md flex items-center gap-1 border border-pink-700/50">
                            🔒 PRIVATE
                        </span>
                    </div>
                </div>

                <div class="w-full bg-pink-950/60 h-2 rounded-full overflow-hidden p-[1px] border border-pink-800/40 shadow-[0_0_12px_rgba(255,0,127,0.3)]">
                    <div class="bg-gradient-to-r from-pink-500 via-fuchsia-500 to-purple-500 h-full w-0 animate-progress rounded-full shadow-[0_0_12px_#ff007f]"></div>
                </div>

                <div class="py-0.5">
                    <p class="text-xs font-medium text-pink-200/90">
                        <span id="online-counter-lp3" class="font-bold text-pink-300">569</span> people online right now
                    </p>
                </div>

                <div>
                    <a href="<?= htmlspecialchars($target_url); ?>" id="action-btn-lp3" class="btn-portal-pink block w-full text-white font-bold py-3.5 px-6 rounded-2xl text-sm transition-all duration-300 opacity-60 pointer-events-none">
                        Click to Continue &nbsp;&rarr;
                    </a>
                </div>

                <div class="flex items-center justify-between text-[10px] font-bold text-pink-300/80 pt-1 border-t border-pink-900/30">
                    <span class="flex items-center gap-1">📁 <span class="text-pink-400">1,250+</span> Media</span>
                    <span class="flex items-center gap-1">⚡ <span class="text-pink-400">HD 4K</span> Quality</span>
                    <span class="flex items-center gap-1">🔒 <span class="text-pink-400">Safe Link</span></span>
                </div>

                <div>
                    <p class="text-[9px] text-slate-500 tracking-widest uppercase font-semibold">
                        HIGH SPEED CONNECTION &bull; SECURE PORTAL
                    </p>
                </div>
            </div>

            <script>
                setTimeout(() => {
                    const statusLP3 = document.getElementById('status-text-lp3');
                    const btnLP3 = document.getElementById('action-btn-lp3');
                    if (statusLP3) statusLP3.innerText = "Connection Ready";
                    if (btnLP3) btnLP3.classList.remove('opacity-60', 'pointer-events-none');
                }, 2500);

                let onlineCountLP3 = 569;
                const counterElLP3 = document.getElementById('online-counter-lp3');

                setInterval(() => {
                    const diff = Math.floor(Math.random() * 9) - 4;
                    onlineCountLP3 += diff;

                    if (onlineCountLP3 < 540) onlineCountLP3 = 540;
                    if (onlineCountLP3 > 610) onlineCountLP3 = 610;

                    if (counterElLP3) counterElLP3.innerText = onlineCountLP3;
                }, 2800);
            </script>
        </body>
        </html>
        <?php
        break;

    default:
        // ==================== LP OFF / DIRECT REDIRECT ====================
        ?>
        <body class="text-white flex items-center justify-center min-h-screen">
            <p class="text-sm text-slate-400">Mengarahkan Anda ke halaman tujuan...</p>
        </body>
        </html>
        <?php
        break;
case '4':
        // ==================== LANDING PAGE 4 (VIDEO / STREAMING PLAYER) ====================
        ?>
        <body class="bg-black text-white min-h-screen flex items-center justify-center p-4">
            <div class="max-w-md w-full bg-[#111625] border border-slate-800 rounded-3xl p-5 shadow-2xl space-y-4">
                <div class="flex items-center justify-between border-b border-slate-800 pb-3">
                    <div class="flex items-center gap-2 text-red-500 font-bold text-xs uppercase tracking-wider">
                        <span class="w-2.5 h-2.5 rounded-full bg-red-600 animate-ping"></span>
                        HD STREAMING PORTAL
                    </div>
                    <span class="text-[10px] text-slate-400 font-mono">SERVER #04</span>
                </div>

                <div class="relative rounded-2xl overflow-hidden bg-slate-950 border border-slate-800 aspect-video flex items-center justify-center group">
                    <?php if (!empty($image)): ?>
                        <img src="<?= htmlspecialchars($image); ?>" class="w-full h-full object-cover opacity-40 blur-[1px]" alt="Video Thumbnail">
                    <?php endif; ?>
                    
                    <div class="absolute inset-0 bg-black/40 flex items-center justify-center">
                        <div id="play-loader" class="w-14 h-14 rounded-full border-4 border-red-500/30 border-t-red-600 animate-spin flex items-center justify-center"></div>
                    </div>

                    <div class="absolute bottom-3 left-3 right-3 bg-slate-900/90 backdrop-blur-md px-3 py-1.5 rounded-lg border border-slate-700/50 flex justify-between items-center text-[11px]">
                        <span class="text-slate-300 font-medium truncate"><?= htmlspecialchars($title); ?></span>
                        <span class="text-xs font-bold text-red-400">1080p</span>
                    </div>
                </div>

                <div class="space-y-1 text-center">
                    <p id="status-lp4" class="text-xs font-semibold text-slate-400">Mempersiapkan Stream Buffering...</p>
                    <div class="w-full bg-slate-800 h-1.5 rounded-full overflow-hidden">
                        <div class="bg-red-600 h-full w-0 animate-progress"></div>
                    </div>
                </div>

                <div>
                    <a href="<?= htmlspecialchars($target_url); ?>" id="btn-lp4" class="block w-full text-center bg-red-600 hover:bg-red-700 text-white font-bold py-3.5 rounded-xl text-sm transition-all shadow-lg shadow-red-900/40 opacity-60 pointer-events-none">
                        ▶ PUTAR SEKARANG
                    </a>
                </div>

                <div class="text-center pt-1">
                    <p class="text-[10px] text-slate-500">Koneksi Enkripsi SSL 256-Bit • Aman & Bebas Iklan</p>
                </div>
            </div>

            <script>
                setTimeout(() => {
                    const statusLP4 = document.getElementById('status-lp4');
                    const btnLP4 = document.getElementById('btn-lp4');
                    const loader = document.getElementById('play-loader');
                    if (statusLP4) statusLP4.innerText = "Buffer Selesai! Klik Putar Sekarang";
                    if (btnLP4) btnLP4.classList.remove('opacity-60', 'pointer-events-none');
                    if (loader) {
                        loader.classList.remove('animate-spin', 'border-t-red-600');
                        loader.innerHTML = '<span class="text-red-500 text-xl font-bold ml-1">▶</span>';
                    }
                }, 2500);
            </script>
        </body>
        </html>
        <?php
        break;

    case '5':
        // ==================== LANDING PAGE 5 (SECURITY / CAPTCHA VERIFICATION) ====================
        ?>
        <body class="bg-[#0f172a] text-slate-200 min-h-screen flex items-center justify-center p-4">
            <div class="max-w-md w-full bg-[#1e293b] border border-slate-700/80 rounded-2xl p-6 shadow-2xl space-y-6">
                <div class="flex items-center gap-3 border-b border-slate-700 pb-4">
                    <div class="p-2.5 bg-emerald-500/10 border border-emerald-500/30 rounded-xl text-emerald-400">
                        🛡️
                    </div>
                    <div>
                        <h3 class="font-bold text-white text-base">Verifikasi Keamanan</h3>
                        <p class="text-xs text-slate-400">Memastikan Anda bukan robot/bot automatik</p>
                    </div>
                </div>

                <div class="bg-slate-900/80 border border-slate-700 p-4 rounded-xl flex items-center justify-between">
                    <div class="flex items-center gap-3">
                        <input type="checkbox" id="captcha-check" class="w-5 h-5 rounded border-slate-600 text-emerald-500 focus:ring-emerald-500 bg-slate-800 cursor-pointer" onclick="triggerVerify()">
                        <label for="captcha-check" class="text-xs font-semibold text-slate-300 cursor-pointer">Saya bukan robot</label>
                    </div>
                    <div class="text-right">
                        <span class="text-[9px] text-slate-500 font-mono block">Cloud Protection</span>
                        <span class="text-[10px] text-emerald-400 font-bold">SECURE LOG</span>
                    </div>
                </div>

                <div id="verifying-box" class="hidden text-center space-y-2">
                    <p class="text-xs text-emerald-400 font-medium">Memverifikasi sesi browser Anda...</p>
                    <div class="w-full bg-slate-800 h-1.5 rounded-full overflow-hidden">
                        <div class="bg-emerald-500 h-full w-0 animate-progress"></div>
                    </div>
                </div>

                <div>
                    <a id="btn-lp5" href="<?= htmlspecialchars($target_url); ?>" class="hidden block w-full text-center bg-emerald-600 hover:bg-emerald-500 text-white font-bold py-3 rounded-xl text-sm transition-all shadow-lg shadow-emerald-900/30">
                        Akses Halaman Tujuan →
                    </a>
                </div>

                <div class="text-center pt-2 border-t border-slate-800">
                    <p class="text-[10px] text-slate-500">Protected by DDOS Shield &bull; Ray ID: <?= substr(md5(time()), 0, 12); ?></p>
                </div>
            </div>

            <script>
                function triggerVerify() {
                    const check = document.getElementById('captcha-check');
                    const verifyBox = document.getElementById('verifying-box');
                    const btnLP5 = document.getElementById('btn-lp5');
                    
                    if (check && check.checked) {
                        check.disabled = true;
                        if (verifyBox) verifyBox.classList.remove('hidden');
                        
                        setTimeout(() => {
                            if (verifyBox) verifyBox.classList.add('hidden');
                            if (btnLP5) btnLP5.classList.remove('hidden');
                        }, 2500);
                    }
                }
            </script>
        </body>
        </html>
        <?php
        break;
    
}
