<?php
require_once 'config/db.php';

// Ambil data domain dengan penanganan error aman
$domains_result = $conn->query("SELECT domain_name FROM domains WHERE is_active = 1");

// Jika query gagal atau tabel belum dibuat, set array kosong
if (!$domains_result) {
    $domains_result = [];
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Link Engine Generator</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <style>
        .result-box { white-space: pre-line; word-break: break-all; }
    </style>
</head>
<body class="bg-slate-100 text-slate-800 font-sans p-4 md:p-8">

    <div class="max-w-5xl mx-auto space-y-4">
        <!-- Status Indicator -->
        <div class="bg-cyan-50 border border-cyan-200 text-cyan-600 text-center text-xs font-bold py-2 rounded-lg tracking-wider uppercase">
            ⚡ LINK ENGINE ACTIVATED
        </div>

      <!-- KODE BARU (Tambahkan action & method) -->
        <form id="generator-form" action="api/generate.php" method="POST" class="grid grid-cols-1 md:grid-cols-2 gap-6">
            
            <!-- KOLOM KIRI: CORE LINK ENGINE -->
            <div class="bg-white p-6 rounded-2xl shadow-sm border border-slate-200 space-y-4 flex flex-col justify-between">
                <div class="space-y-4">
                    <div class="flex items-center gap-2 text-cyan-600 font-bold text-xs uppercase tracking-wider">
                        <span>⚡</span> LINK ENGINE
                    </div>

                    <!-- Domain Selector -->
                    <div>
                        <label class="block text-xs font-semibold text-slate-500 mb-1">DOMAIN</label>
                       <select name="domain_used" class="w-full bg-slate-50 border border-slate-200 rounded-lg p-2.5 text-sm focus:outline-none focus:border-cyan-500">
                           
                            <?php 
                            if ($domains_result && $domains_result->num_rows > 0) {
                                while($row = $domains_result->fetch_assoc()) {
                                    echo '<option value="' . htmlspecialchars($row['domain_name']) . '">' . htmlspecialchars($row['domain_name']) . '</option>';
                                }
                            }
                            ?>
                        </select>
                    </div>

                    <!-- Target URL -->
                    <div>
                        <label class="block text-xs font-semibold text-slate-500 mb-1">URL (MONETIZE / LOCKED)</label>
                        <textarea name="original_url" required rows="3" placeholder="https://target-monetisasi.com/..." class="w-full bg-slate-50 border border-slate-200 rounded-lg p-2.5 text-xs font-mono focus:outline-none focus:border-cyan-500"></textarea>
                    </div>

                    <!-- Customize Click ID -->
                    <div>
                        <label class="block text-xs font-semibold text-slate-500 mb-1">CUSTOMIZE CLICK ID</label>
                        <input type="text" name="click_id" placeholder="TERES21" class="w-full bg-slate-50 border border-slate-200 rounded-lg p-2.5 text-sm focus:outline-none focus:border-cyan-500">
                    </div>

                    <!-- Toggles & Generate Button -->
                    <div class="grid grid-cols-3 gap-2 pt-2">
                        <div class="flex items-center justify-between bg-slate-50 border border-slate-200 rounded-lg px-2">
                            <span class="text-xs font-bold text-slate-600">F-SUB:</span>
                            <select name="f_sub" class="bg-transparent text-xs font-bold text-cyan-600 focus:outline-none">
                                <option value="OFF">OFF</option>
                                <option value="ON">ON</option>
                            </select>
                        </div>

                        <div class="flex items-center justify-between bg-slate-50 border border-slate-200 rounded-lg px-2">
                            <span class="text-xs font-bold text-slate-600">LP:</span>
                           <select name="lp" class="bg-transparent text-xs font-bold text-cyan-600 focus:outline-none">
    <option value="OFF">OFF</option>
    <option value="1">LP 1</option>
    <option value="2">LP 2</option>
    <option value="3">LP 3</option>
    <option value="4">LP 4</option>
    <option value="5">LP 5</option>
</select>
                        </div>

                        <button type="submit" class="bg-cyan-600 hover:bg-cyan-700 text-white font-bold py-2 rounded-lg text-xs transition-colors flex items-center justify-center gap-1">
                            ⚡ GENERATE
                        </button>
                    </div>

                    <!-- RESULT AREA (Kotak Hasil yang Ditandai) -->
                    <div id="result-wrapper" class="hidden space-y-2 pt-2">
                        <div id="result-text" class="result-box w-full bg-slate-50 border border-slate-200 rounded-lg p-3 text-xs font-mono text-cyan-700 bg-cyan-50/30"></div>
                        
                        <div class="grid grid-cols-3 gap-2">
                            <button type="button" id="btn-copy" class="col-span-2 bg-cyan-600 hover:bg-cyan-700 text-white font-bold py-2 rounded-lg text-xs uppercase tracking-wider">
                                COPY ALL
                            </button>
                            <button type="button" id="btn-clear" class="bg-slate-200 hover:bg-slate-300 text-slate-700 font-bold py-2 rounded-lg text-xs uppercase tracking-wider">
                                CLEAR
                            </button>
                        </div>
                    </div>
                </div>

                <!-- Shortener Service Option -->
                <div class="pt-4 border-t border-slate-100">
                    <label class="block text-xs font-semibold text-slate-500 mb-1 text-center">SHORTENER SERVICE</label>
                    <div class="grid grid-cols-2 gap-2 text-center text-xs font-bold mb-2">
                        <label class="cursor-pointer">
                            <input type="radio" name="shortener_service" value="DEFAULT" checked class="peer hidden">
                            <div class="p-2 bg-slate-50 border border-slate-200 rounded-lg peer-checked:bg-cyan-600 peer-checked:text-white">DEFAULT</div>
                        </label>
                        <label class="cursor-pointer">
                            <input type="radio" name="shortener_service" value="IX.SK" class="peer hidden">
                            <div class="p-2 bg-slate-50 border border-slate-200 rounded-lg peer-checked:bg-cyan-600 peer-checked:text-white">IX.SK</div>
                        </label>
                    </div>
                    <input type="text" name="api_key" placeholder="Masukkan IX.SK API Key..." class="w-full bg-slate-50 border border-slate-200 rounded-lg p-2 text-xs focus:outline-none focus:border-cyan-500">
                </div>
            </div>

            <!-- KOLOM KANAN: METADATA & SOCIAL SHARING -->
            <div class="bg-white p-6 rounded-2xl shadow-sm border border-slate-200 space-y-4">
                <div class="flex items-center gap-2 text-cyan-600 font-bold text-xs uppercase tracking-wider">
                    <span>⚙️</span> METADATA & SOCIAL
                </div>

                <!-- Custom Slug & Jumlah -->
                <div class="grid grid-cols-3 gap-3">
                    <div class="col-span-2">
                        <label class="block text-xs font-semibold text-slate-500 mb-1">CUSTOM SLUG</label>
                        <input type="text" name="slug" placeholder="slug-viral" class="w-full bg-slate-50 border border-slate-200 rounded-lg p-2.5 text-sm focus:outline-none focus:border-cyan-500">
                    </div>
                    <div>
                        <label class="block text-xs font-semibold text-slate-500 mb-1">JUMLAH</label>
                        <input type="number" name="bulk_qty" value="1" min="1" class="w-full bg-slate-50 border border-slate-200 rounded-lg p-2.5 text-sm focus:outline-none focus:border-cyan-500">
                    </div>
                </div>

                <!-- Judul Meta -->
                <div>
                    <label class="block text-xs font-semibold text-slate-500 mb-1">JUDUL META</label>
                    <input type="text" id="meta_title" name="meta_title" placeholder="Input Judul Link..." class="w-full bg-slate-50 border border-slate-200 rounded-lg p-2.5 text-sm focus:outline-none focus:border-cyan-500">
                </div>

                <!-- Image URL -->
                <div>
                    <label class="block text-xs font-semibold text-slate-500 mb-1">IMAGE URL</label>
                    <input type="url" id="meta_image" name="meta_image" placeholder="https://image.url/..." class="w-full bg-slate-50 border border-slate-200 rounded-lg p-2.5 text-sm focus:outline-none focus:border-cyan-500">
                </div>

                <!-- Deskripsi Meta -->
                <div>
                    <label class="block text-xs font-semibold text-slate-500 mb-1">DESKRIPSI META</label>
                    <textarea id="meta_description" name="meta_description" rows="2" placeholder="Deskripsi link..." class="w-full bg-slate-50 border border-slate-200 rounded-lg p-2.5 text-sm focus:outline-none focus:border-cyan-500"></textarea>
                </div>

                <!-- Live Preview Section -->
                <div class="pt-2">
                    <label class="block text-xs font-semibold text-slate-500 mb-2">LIVE PREVIEW</label>
                    <div class="border border-slate-200 rounded-xl overflow-hidden bg-slate-50">
                       <img id="preview-img" 
             src="https://via.placeholder.com/600x315?text=No+Image+Available" 
             onerror="this.src='https://via.placeholder.com/600x315?text=Image+Load+Error';" 
             class="w-full h-48 object-cover rounded-t-xl" 
             alt="Preview Image">
                        <div class="p-3 bg-white border-t border-slate-100">
                            <span class="text-[10px] text-slate-400 font-bold uppercase block">YOUR DOMAIN</span>
                            <h4 id="preview-title" class="font-bold text-slate-800 text-sm truncate">Untitled Link</h4>
                            <p id="preview-desc" class="text-xs text-slate-500 line-clamp-2 mt-0.5">Provide a description to see it here...</p>
                        </div>
                    </div>
                </div>
            </div>

        </form>
    </div>

    <script src="assets/js/main.js"></script>
</body>
</html>
