<?php
require_once '../config/db.php';

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
    
    for ($i = 0; $i < $bulk_qty; $i++) {
        $slug = !empty($custom_slug) ? (($bulk_qty > 1) ? $custom_slug . "-" . ($i + 1) : $custom_slug) : generateRandomSlug(16);

        $final_domain = $domain_used;
        if ($domain_used === 'Random Domain') {
            $rand_query = "SELECT domain_name FROM domains WHERE is_active = 1 ORDER BY RAND() LIMIT 1";
            $rand_res = $conn->query($rand_query);
            if ($rand_res && $rand_res->num_rows > 0) {
                $final_domain = $rand_res->fetch_assoc()['domain_name'];
            } else {
                $final_domain = $_SERVER['HTTP_HOST'];
            }
        }

        $stmt = $conn->prepare("INSERT INTO links (slug, original_url, click_id, f_sub, lp, meta_title, meta_image, meta_description, domain_used, shortener_service) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");
        $stmt->bind_param("ssssssssss", $slug, $original_url, $click_id, $f_sub, $lp, $meta_title, $meta_image, $meta_description, $final_domain, $shortener_service);

        if ($stmt->execute()) {
            $generated_links[] = $protocol . $final_domain . "/" . $slug;
        }
        $stmt->close();
    }

    echo json_encode(['status' => 'success', 'links' => $generated_links]);
    exit;
}
?>
