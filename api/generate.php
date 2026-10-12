<?php
require_once __DIR__ . '/../config/db.php';

header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $domain_used        = $_POST['domain_used'] ?? 'Random Domain';
    $original_url       = trim($_POST['original_url'] ?? '');
    $click_id           = trim($_POST['click_id'] ?? '');
    $f_sub              = $_POST['f_sub'] ?? 'OFF';
    $lp                 = $_POST['lp'] ?? 'OFF';
    $shortener_service  = $_POST['shortener_service'] ?? 'DEFAULT';
    
    $custom_slug        = trim($_POST['slug'] ?? '');
    $bulk_qty           = intval($_POST['bulk_qty'] ?? 1);
    $meta_title         = trim($_POST['meta_title'] ?? '');
    $meta_image         = trim($_POST['meta_image'] ?? '');
    $meta_description   = trim($_POST['meta_description'] ?? '');

    if (empty($original_url)) {
        echo json_encode(['status' => 'error', 'message' => 'URL Target wajib diisi']);
        exit;
    }

    function generateRandomSlug($length = 16) {
        return substr(str_shuffle("0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ"), 0, $length);
    }

    $generated_links = [];
    $protocol = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') ? "https://" : "http://";
    
    // Ambil daftar domain aktif jika memilih Random Domain
    $active_domains = [];
    if ($domain_used === 'Random Domain') {
        // Coba query dengan status numeric (is_active=1)
        $res = supabase_request("domains?is_active=eq.1&select=domain_name");
        
        // Jika tidak ada hasil, coba query dengan boolean (is_active=true)
        if ($res['code'] !== 200 || empty($res['data'])) {
            $res = supabase_request("domains?is_active=eq.true&select=domain_name");
        }

        if ($res['code'] === 200 && !empty($res['data'])) {
            $active_domains = $res['data'];
        }
    }

    for ($i = 0; $i < $bulk_qty; $i++) {
        $slug = !empty($custom_slug) ? (($bulk_qty > 1) ? $custom_slug . "-" . ($i + 1) : $custom_slug) : generateRandomSlug(16);

        $final_domain = $domain_used;
        
        // Logika Pemilihan Random Domain
        if ($domain_used === 'Random Domain') {
            if (!empty($active_domains)) {
                $randomIndex = array_rand($active_domains);
                $final_domain = $active_domains[$randomIndex]['domain_name'];
            } else {
                // Fallback jika tidak ada domain aktif di database
                $final_domain = $_SERVER['HTTP_HOST'];
            }
        }

        // Insert ke Supabase REST API
        $payload = [
            'slug'              => $slug,
            'original_url'      => $original_url,
            'click_id'          => $click_id,
            'f_sub'             => $f_sub,
            'lp'                => $lp,
            'meta_title'        => $meta_title,
            'meta_image'        => $meta_image,
            'meta_description'  => $meta_description,
            'domain_used'       => $final_domain,
            'shortener_service' => $shortener_service
        ];

        $insertRes = supabase_request("links", "POST", $payload);

        if ($insertRes['code'] === 201 || $insertRes['code'] === 200) {
            $generated_links[] = $protocol . $final_domain . "/" . $slug;
        }
    }

    echo json_encode(['status' => 'success', 'links' => $generated_links]);
    exit;
}
?>
