<?php
declare(strict_types=1);

define('ADMINAREA', true);

$init_file = __DIR__ . DIRECTORY_SEPARATOR . 'init.php';
if (!is_file($init_file)) {
    $init_file = dirname(__DIR__) . DIRECTORY_SEPARATOR . 'init.php';
}
if (!is_file($init_file)) {
    header('HTTP/1.1 503 Service Unavailable');
    header('Content-Type: text/plain; charset=utf-8');
    exit('Hata: init.php bulunamadı. Aranan: MegaPanel/init.php ve site kökü init.php');
}

try {
    require_once $init_file;
} catch (Throwable $e) {
    header('HTTP/1.1 500 Internal Server Error');
    header('Content-Type: text/plain; charset=utf-8');
    if (function_exists('logModuleCall')) {
        @logModuleCall('MediaManager', 'init', $init_file, $e->getMessage(), $e->getTraceAsString());
    } else {
        @error_log('Media Manager init: ' . $e->getMessage() . ' in ' . $e->getFile() . ':' . $e->getLine());
    }
    exit('Yapılandırma hatası. Sunucu PHP hata günlüğünü kontrol edin.');
}

if (!isset($_SESSION['adminid'])) {
    header('HTTP/1.1 403 Forbidden');
    exit('Yetkisiz erişim.');
}

// CSRF token (AJAX POST için zorunlu)
if (empty($_SESSION['media_csrf'])) {
    $_SESSION['media_csrf'] = bin2hex(random_bytes(16));
}

// Dil: Admin panel diline göre TR / EN
$admin_lang_raw = isset($_SESSION['adminlang']) ? strtolower((string) $_SESSION['adminlang']) : 'turkish';
$is_english = ($admin_lang_raw === 'english' || $admin_lang_raw === 'en' || strpos($admin_lang_raw, 'english') !== false);
$LANG_TR = [
    'title' => 'Medya Yöneticisi',
    'media' => 'Medya',
    'btn_upload' => 'Yükle',
    'btn_new_folder' => 'Yeni Klasör',
    'btn_rename' => 'Yeniden Adlandır',
    'btn_delete' => 'Sil',
    'btn_move' => 'Taşı',
    'btn_copy_path' => 'Yolu Kopyala',
    'sort_name' => 'Ada göre',
    'sort_date' => 'Tarihe göre',
    'sort_size' => 'Boyuta göre',
    'btn_path' => 'Yol',
    'btn_name' => 'Ad',
    'modal_rename' => 'Yeniden Adlandır',
    'new_name' => 'Yeni ad',
    'modal_new_folder' => 'Yeni Klasör',
    'folder_name' => 'Klasör adı',
    'modal_paths' => 'Yol bilgisi',
    'physical_path' => 'Fiziksel yol',
    'url' => 'URL',
    'copy' => 'Kopyala',
    'close' => 'Kapat',
    'cancel' => 'İptal',
    'ok' => 'Tamam',
    'create' => 'Oluştur',
    'loading' => 'Yükleniyor…',
    'empty_dir' => 'Bu dizin boş. Yükle veya yeni klasör oluştur.',
    'list_error' => 'Liste alınamadı',
    'path_copied' => 'Fiziksel yol kopyalandı',
    'url_copied' => 'URL kopyalandı',
    'copy_failed' => 'Kopyalama desteklenmiyor',
    'uploaded' => 'Yüklendi',
    'folder_created' => 'Klasör oluşturuldu',
    'renamed' => 'Yeniden adlandırıldı',
    'moved' => 'Taşındı',
    'deleted' => 'Silindi',
    'delete_failed' => 'Silinemedi',
    'upload_error' => 'Yükleme hatası',
    'confirm_delete_file' => 'Dosya silinecek',
    'confirm_delete_folder' => 'Klasör silinecek',
    'confirm_continue' => 'Devam?',
    'path_error' => 'Yol alınamadı',
    'rename_placeholder' => 'dosya-adi.jpg',
    'folder_placeholder' => 'klasor-adi',
    'folder_info' => 'Klasör bilgisi',
    'stats_path' => 'Konum',
    'stats_files' => 'Dosya',
    'stats_folders' => 'Klasör',
    'stats_total_size' => 'Toplam boyut',
    'stats_root' => 'Kök (medya)',
    'uploaded_count' => 'yüklendi',
    'uploaded_count_error' => 'yüklendi,',
    'upload_failed_count' => 'hata',
    'locale' => 'tr-TR',
    'err_invalid_dir' => 'Geçersiz dizin.',
    'err_invalid_mime' => 'Dosya içeriği türü kabul edilmedi.',
    'err_invalid_name' => 'Geçersiz dosya adı.',
    'err_type_not_allowed' => 'Bu dosya türüne izin verilmiyor.',
    'err_file_too_big' => 'Dosya boyutu çok büyük.',
    'err_save_failed' => 'Dosya kaydedilemedi.',
    'err_not_found' => 'Dosya veya dizin bulunamadı.',
    'err_invalid_new_name' => 'Geçersiz yeni ad.',
    'err_unauthorized' => 'Yetkisiz işlem.',
    'err_exists' => 'Bu isimde bir öğe zaten var.',
    'err_rename_failed' => 'Yeniden adlandırılamadı.',
    'err_cannot_delete_root' => 'Kök dizin silinemez.',
    'err_delete_failed' => 'Silinemedi.',
    'err_folder_name_empty' => 'Klasör adı boş olamaz.',
    'err_mkdir_failed' => 'Klasör oluşturulamadı.',
    'err_select_file' => 'Geçerli bir dosya seçin.',
    'err_unknown' => 'Bilinmeyen işlem.',
    'bulk_delete' => 'Seçilenleri Sil',
    'bulk_move' => 'Seçilenleri Taşı',
    'bulk_delete_confirm' => 'Seçilen öğeler silinecek.',
    'drop_here' => 'Dosyaları buraya bırakın',
    'move_to' => 'Buraya taşı',
    'select_count' => 'seçili',
];
$LANG_EN = [
    'title' => 'Media Manager',
    'media' => 'Media',
    'btn_upload' => 'Upload',
    'btn_new_folder' => 'New Folder',
    'btn_rename' => 'Rename',
    'btn_delete' => 'Delete',
    'btn_move' => 'Move',
    'btn_copy_path' => 'Copy Path',
    'sort_name' => 'By name',
    'sort_date' => 'By date',
    'sort_size' => 'By size',
    'btn_path' => 'Path',
    'btn_name' => 'Name',
    'modal_rename' => 'Rename',
    'new_name' => 'New name',
    'modal_new_folder' => 'New Folder',
    'folder_name' => 'Folder name',
    'modal_paths' => 'Path info',
    'physical_path' => 'Physical path',
    'url' => 'URL',
    'copy' => 'Copy',
    'close' => 'Close',
    'cancel' => 'Cancel',
    'ok' => 'OK',
    'create' => 'Create',
    'loading' => 'Loading…',
    'empty_dir' => 'This folder is empty. Upload files or create a new folder.',
    'list_error' => 'Failed to load list',
    'path_copied' => 'Physical path copied',
    'url_copied' => 'URL copied',
    'copy_failed' => 'Clipboard not supported',
    'uploaded' => 'Uploaded',
    'folder_created' => 'Folder created',
    'renamed' => 'Renamed',
    'moved' => 'Moved',
    'deleted' => 'Deleted',
    'delete_failed' => 'Delete failed',
    'upload_error' => 'Upload error',
    'confirm_delete_file' => 'File will be deleted',
    'confirm_delete_folder' => 'Folder will be deleted',
    'confirm_continue' => 'Continue?',
    'path_error' => 'Failed to get path',
    'rename_placeholder' => 'filename.jpg',
    'folder_placeholder' => 'folder-name',
    'folder_info' => 'Folder info',
    'stats_path' => 'Location',
    'stats_files' => 'Files',
    'stats_folders' => 'Folders',
    'stats_total_size' => 'Total size',
    'stats_root' => 'Root (media)',
    'uploaded_count' => 'uploaded',
    'uploaded_count_error' => 'uploaded,',
    'upload_failed_count' => 'failed',
    'locale' => 'en-US',
    'err_invalid_dir' => 'Invalid directory.',
    'err_invalid_mime' => 'File content type not allowed.',
    'err_invalid_name' => 'Invalid file name.',
    'err_type_not_allowed' => 'This file type is not allowed.',
    'err_file_too_big' => 'File size too large.',
    'err_save_failed' => 'Failed to save file.',
    'err_not_found' => 'File or folder not found.',
    'err_invalid_new_name' => 'Invalid new name.',
    'err_unauthorized' => 'Unauthorized.',
    'err_exists' => 'An item with this name already exists.',
    'err_rename_failed' => 'Rename failed.',
    'err_cannot_delete_root' => 'Root folder cannot be deleted.',
    'err_delete_failed' => 'Delete failed.',
    'err_folder_name_empty' => 'Folder name cannot be empty.',
    'err_mkdir_failed' => 'Failed to create folder.',
    'err_select_file' => 'Please select a file.',
    'err_unknown' => 'Unknown action.',
    'bulk_delete' => 'Delete Selected',
    'bulk_move' => 'Move Selected',
    'bulk_delete_confirm' => 'Selected items will be deleted.',
    'drop_here' => 'Drop files here',
    'move_to' => 'Move here',
    'select_count' => 'selected',
];
$L = $is_english ? $LANG_EN : $LANG_TR;

// --- Yapılandırma (ihtiyaca göre düzenleyin) ---
$MEDIA_CONFIG = [
    'base_url'        => 'https://www.website.com',  // Site adresi (WHMCS System URL) – tam URL buradan üretilir
    'base_dir'        => 'media/kb',
    'allowed_ext'     => ['jpg', 'jpeg', 'png', 'gif', 'webp', 'svg', 'pdf', 'zip', 'doc', 'docx', 'xls', 'xlsx'],
    'max_upload_mb'   => 10,
    'url_path_prefix' => 'media/kb',   // base_url sonrası yol (örn: https://www.website.com/media/kb/dosya.png)
];

$media_base_url = trim($MEDIA_CONFIG['base_url'] ?? '', '/') . '/' . trim($MEDIA_CONFIG['url_path_prefix'] ?? '', '/');
function build_media_url(string $relative_path): string {
    global $media_base_url;
    $path = trim(str_replace('\\', '/', $relative_path), '/');
    return $path === '' ? rtrim($media_base_url, '/') : $media_base_url . '/' . $path;
}

$whmcs_root = realpath(dirname(__DIR__));
if ($whmcs_root === false || !is_dir($whmcs_root)) {
    header('Content-Type: text/plain; charset=utf-8');
    exit('Hata: WHMCS kök dizini bulunamadı: ' . dirname(__DIR__));
}

$base_dir_full = realpath($whmcs_root . DIRECTORY_SEPARATOR . str_replace(['/', '\\'], DIRECTORY_SEPARATOR, $MEDIA_CONFIG['base_dir']));

if ($base_dir_full === false) {
    $base_dir_full = $whmcs_root . DIRECTORY_SEPARATOR . str_replace(['/', '\\'], DIRECTORY_SEPARATOR, $MEDIA_CONFIG['base_dir']);
    if (!is_dir($base_dir_full)) {
        @mkdir($base_dir_full, 0755, true);
    }
    $base_dir_full = realpath($base_dir_full);
}

if ($base_dir_full === false || !is_dir($base_dir_full)) {
    header('Content-Type: application/json; charset=utf-8');
    echo json_encode(['error' => 'Medya kök dizini oluşturulamadı: ' . $MEDIA_CONFIG['base_dir']]);
    exit;
}

$max_upload_bytes = (int) $MEDIA_CONFIG['max_upload_mb'] * 1024 * 1024;
$allowed_ext = array_map('strtolower', $MEDIA_CONFIG['allowed_ext']);


$allowed_mime = [
    'jpg' => ['image/jpeg'],
    'jpeg' => ['image/jpeg'],
    'png' => ['image/png'],
    'gif' => ['image/gif'],
    'webp' => ['image/webp'],
    'svg' => ['image/svg+xml', 'image/svg+xml; charset=utf-8'],
    'pdf' => ['application/pdf'],
    'zip' => ['application/zip', 'application/x-zip-compressed'],
    'doc' => ['application/msword'],
    'docx' => ['application/vnd.openxmlformats-officedocument.wordprocessingml.document'],
    'xls' => ['application/vnd.ms-excel'],
    'xlsx' => ['application/vnd.openxmlformats-officedocument.spreadsheetml.sheet'],
];


function resolve_path(string $base_full, string $relative): ?string {
    $relative = trim($relative, '/\\');
    $relative = str_replace(['../', '..\\'], '', $relative);
    if ($relative === '' || $relative === '.') {
        $resolved = $base_full;
    } else {
        $resolved = $base_full . DIRECTORY_SEPARATOR . str_replace(['/', '\\'], DIRECTORY_SEPARATOR, $relative);
    }
    $real = realpath($resolved);
    if ($real === false) {
        $parent = realpath(dirname($resolved));
        if ($parent === false || strpos($parent, $base_full) !== 0) {
            return null;
        }
        return $resolved;
    }
    if ($real !== $base_full && strpos($real, $base_full . DIRECTORY_SEPARATOR) !== 0) {
        return null;
    }
    return $real;
}


if (isset($_GET['ajax']) && $_GET['ajax'] === '1') {
    header('Content-Type: application/json; charset=utf-8');
    header('Cache-Control: no-store, no-cache, must-revalidate');
    header('X-Content-Type-Options: nosniff');

    $action = $_POST['action'] ?? $_GET['action'] ?? '';
    $rel = (string) ($_POST['path'] ?? $_GET['path'] ?? '');
    $rel = trim($rel, '/\\');

    if (in_array($action, ['upload', 'rename', 'delete', 'mkdir', 'move'], true)) {
        $token = $_POST['csrf'] ?? $_GET['csrf'] ?? '';
        if (!hash_equals((string) $_SESSION['media_csrf'], $token)) {
            echo json_encode(['success' => false, 'error' => $L['list_error'], 'data' => null]);
            exit;
        }
    }

    $response = ['success' => false, 'error' => '', 'data' => null];

    switch ($action) {
        case 'list':
            $dir = resolve_path($base_dir_full, $rel);
            if ($dir === null || !is_dir($dir)) {
                $response['error'] = $L['list_error'];
                echo json_encode($response);
                exit;
            }
            $sort_by = $_GET['sort'] ?? 'name';
            $sort_order = ($_GET['order'] ?? 'asc') === 'desc' ? -1 : 1;
            $items = [];
            $base_len = strlen($base_dir_full);
            foreach (scandir($dir) as $name) {
                if ($name === '.' || $name === '..') continue;
                $full = $dir . DIRECTORY_SEPARATOR . $name;
                $relative_path = trim(str_replace(DIRECTORY_SEPARATOR, '/', substr($full, $base_len)), '/');
                $is_dir = is_dir($full);
                $items[] = [
                    'name'   => $name,
                    'path'   => $relative_path,
                    'dir'    => $is_dir,
                    'size'   => $is_dir ? null : filesize($full),
                    'mtime'  => filemtime($full),
                    'ext'    => $is_dir ? null : strtolower(pathinfo($name, PATHINFO_EXTENSION)),
                    'url'    => $is_dir ? null : build_media_url($relative_path),
                ];
            }
            usort($items, function ($a, $b) use ($sort_by, $sort_order) {
                if ($a['dir'] !== $b['dir']) return ($a['dir'] ? -1 : 1) * $sort_order;
                $cmp = 0;
                switch ($sort_by) {
                    case 'date':
                        $cmp = ($a['mtime'] ?? 0) <=> ($b['mtime'] ?? 0);
                        break;
                    case 'size':
                        $sa = $a['size'] ?? 0;
                        $sb = $b['size'] ?? 0;
                        $cmp = $sa <=> $sb;
                        break;
                    default:
                        $cmp = strcasecmp($a['name'], $b['name']);
                }
                return $cmp * $sort_order;
            });
            $response['success'] = true;
            $response['data'] = ['items' => $items, 'path' => $rel];
            break;

        case 'upload':
            $target_dir = resolve_path($base_dir_full, $rel);
            if ($target_dir === null || !is_dir($target_dir)) {
                $response['error'] = $L['err_invalid_dir'];
                echo json_encode($response);
                exit;
            }
            if (empty($_FILES['file']['tmp_name']) || !is_uploaded_file($_FILES['file']['tmp_name'])) {
                $response['error'] = $L['err_save_failed'];
                echo json_encode($response);
                exit;
            }
            $original_name = basename($_FILES['file']['name']);
            if (strpos($original_name, "\0") !== false || strlen($original_name) > 255) {
                $response['error'] = $L['err_invalid_name'];
                echo json_encode($response);
                exit;
            }
            $ext = strtolower(pathinfo($original_name, PATHINFO_EXTENSION));
            if (!in_array($ext, $allowed_ext, true)) {
                $response['error'] = $L['err_type_not_allowed'];
                echo json_encode($response);
                exit;
            }
            if ($_FILES['file']['size'] > $max_upload_bytes) {
                $response['error'] = $L['err_file_too_big'];
                echo json_encode($response);
                exit;
            }
            if (isset($allowed_mime[$ext])) {
                $finfo = finfo_open(FILEINFO_MIME_TYPE);
                $detected = $finfo ? finfo_file($finfo, $_FILES['file']['tmp_name']) : '';
                if ($finfo) finfo_close($finfo);
                $allowed_mimes = $allowed_mime[$ext];
                if ($detected === false || !in_array($detected, $allowed_mimes, true)) {
                    $response['error'] = $L['err_invalid_mime'];
                    echo json_encode($response);
                    exit;
                }
            }
            $base_name = pathinfo($original_name, PATHINFO_FILENAME);
            $safe_name = preg_replace('/[^\w.\-]/', '_', $base_name);
            if (strlen($safe_name) > 200) $safe_name = substr($safe_name, 0, 200);
            $safe_name = $safe_name . '.' . $ext;
            $dest = $target_dir . DIRECTORY_SEPARATOR . $safe_name;
            $i = 0;
            while (file_exists($dest)) {
                $i++;
                $dest = $target_dir . DIRECTORY_SEPARATOR . pathinfo($safe_name, PATHINFO_FILENAME) . '-' . $i . '.' . $ext;
            }
            if (move_uploaded_file($_FILES['file']['tmp_name'], $dest)) {
                $base_len = strlen($base_dir_full);
                $relative_path = trim(str_replace(DIRECTORY_SEPARATOR, '/', substr($dest, $base_len)), '/');
                $response['success'] = true;
                $response['data'] = [
                    'name' => basename($dest),
                    'path' => $relative_path,
                    'url'  => build_media_url($relative_path),
                    'physical' => $dest,
                ];
            } else {
                $response['error'] = $L['err_save_failed'];
            }
            break;

        case 'rename':
            $old_path = resolve_path($base_dir_full, $rel);
            $new_name = isset($_POST['newname']) ? trim((string) $_POST['newname']) : '';
            if ($old_path === null || !file_exists($old_path)) {
                $response['error'] = $L['err_not_found'];
                echo json_encode($response);
                exit;
            }
            $new_name = str_replace(["\0", '..'], '', $new_name);
            $new_name = basename(preg_replace('/[^\w.\-\/]/', '_', $new_name));
            if ($new_name === '') {
                $response['error'] = $L['err_invalid_new_name'];
                echo json_encode($response);
                exit;
            }
            $parent = dirname($old_path);
            $new_full = $parent . DIRECTORY_SEPARATOR . $new_name;
            if (strlen($new_name) > 255) {
                $response['error'] = $L['err_invalid_new_name'];
                echo json_encode($response);
                exit;
            }
            $parent_real = realpath($parent);
            if ($parent_real === false || strpos($parent_real, $base_dir_full) !== 0) {
                $response['error'] = $L['err_unauthorized'];
                echo json_encode($response);
                exit;
            }
            if (file_exists($new_full)) {
                $response['error'] = $L['err_exists'];
                echo json_encode($response);
                exit;
            }
            if (rename($old_path, $new_full)) {
                $base_len = strlen($base_dir_full);
                $response['success'] = true;
                $response['data'] = [
                    'path' => trim(str_replace(DIRECTORY_SEPARATOR, '/', substr($new_full, $base_len)), '/'),
                    'name' => basename($new_full),
                ];
            } else {
                $response['error'] = $L['err_rename_failed'];
            }
            break;

        case 'delete':
            $paths_raw = $_POST['paths'] ?? $rel;
            $paths = is_array($paths_raw) ? $paths_raw : ($paths_raw !== '' ? [$paths_raw] : []);
            $all_ok = true;
            foreach ($paths as $p) {
                $p = trim((string) $p, '/\\');
                $target = resolve_path($base_dir_full, $p);
                if ($target === null) {
                    $response['error'] = $L['err_invalid_dir'];
                    $all_ok = false;
                    break;
                }
                if ($target === $base_dir_full) {
                    $response['error'] = $L['err_cannot_delete_root'];
                    $all_ok = false;
                    break;
                }
                if (!file_exists($target)) continue;
                $delete_ok = is_dir($target) ? delete_directory($target) : @unlink($target);
                if (!$delete_ok) {
                    $response['error'] = $L['err_delete_failed'];
                    $all_ok = false;
                    break;
                }
            }
            if ($all_ok) $response['success'] = true;
            break;

        case 'move':
            $paths_raw = $_POST['paths'] ?? null;
            $paths = is_array($paths_raw) ? $paths_raw : ($paths_raw ? [trim((string) $paths_raw)] : []);
            $target_rel = trim((string) ($_POST['target'] ?? ''), '/\\');
            $target_dir = resolve_path($base_dir_full, $target_rel);
            if ($target_dir === null || !is_dir($target_dir)) {
                $response['error'] = $L['err_invalid_dir'];
                echo json_encode($response);
                exit;
            }
            $moved = [];
            $target_rel_norm = str_replace('\\', '/', $target_rel);
            foreach ($paths as $p) {
                $p = trim((string) $p, '/\\');
                if ($p === '') continue;
                $src = resolve_path($base_dir_full, $p);
                if ($src === null || !file_exists($src) || $src === $base_dir_full) continue;
                $p_norm = str_replace('\\', '/', $p);
                if (is_dir($src) && $target_rel_norm !== '' && (strpos($target_rel_norm . '/', $p_norm . '/') === 0 || $p_norm === $target_rel_norm)) {
                    continue;
                }
                $name = basename($src);
                $dest = $target_dir . DIRECTORY_SEPARATOR . $name;
                if (file_exists($dest)) {
                    $base = pathinfo($name, PATHINFO_FILENAME);
                    $ext = pathinfo($name, PATHINFO_EXTENSION);
                    $i = 0;
                    do {
                        $i++;
                        $dest = $target_dir . DIRECTORY_SEPARATOR . $base . '-' . $i . ($ext ? '.' . $ext : '');
                    } while (file_exists($dest));
                }
                if (@rename($src, $dest)) {
                    $base_len = strlen($base_dir_full);
                    $moved[] = trim(str_replace(DIRECTORY_SEPARATOR, '/', substr($dest, $base_len)), '/');
                } else {
                    $response['error'] = $L['err_rename_failed'];
                    break;
                }
            }
            if (empty($response['error'])) {
                $response['success'] = true;
                $response['data'] = ['moved' => $moved];
            }
            break;

        case 'mkdir':
            $parent = resolve_path($base_dir_full, $rel);
            $folder_name = isset($_POST['name']) ? trim((string) $_POST['name']) : '';
            $folder_name = preg_replace('/[^\w.\-]/', '_', $folder_name);
            $folder_name = str_replace(["\0", '..'], '', $folder_name);
            if ($parent === null || !is_dir($parent)) {
                $response['error'] = $L['err_invalid_dir'];
                echo json_encode($response);
                exit;
            }
            if ($folder_name === '' || strlen($folder_name) > 255) {
                $response['error'] = $L['err_folder_name_empty'];
                echo json_encode($response);
                exit;
            }
            $new_dir = $parent . DIRECTORY_SEPARATOR . $folder_name;
            if (file_exists($new_dir)) {
                $response['error'] = $L['err_exists'];
                echo json_encode($response);
                exit;
            }
            if (@mkdir($new_dir, 0755)) {
                $base_len = strlen($base_dir_full);
                $response['success'] = true;
                $response['data'] = [
                    'path' => trim(str_replace(DIRECTORY_SEPARATOR, '/', substr($new_dir, $base_len)), '/'),
                    'name' => $folder_name,
                ];
            } else {
                $response['error'] = $L['err_mkdir_failed'];
            }
            break;

        case 'paths':
            $target = resolve_path($base_dir_full, $rel);
            if ($target === null || !file_exists($target) || is_dir($target)) {
                $response['error'] = $L['err_select_file'];
                echo json_encode($response);
                exit;
            }
            $base_len = strlen($base_dir_full);
            $relative_path = trim(str_replace(DIRECTORY_SEPARATOR, '/', substr($target, $base_len)), '/');
            $response['success'] = true;
            $response['data'] = [
                'physical' => $target,
                'url'      => build_media_url($relative_path),
                'relative' => $relative_path,
            ];
            break;

        default:
            $response['error'] = $L['err_unknown'];
    }

    echo json_encode($response);
    exit;
}

function delete_directory(string $dir): bool {
    $files = array_diff(scandir($dir), ['.', '..']);
    foreach ($files as $f) {
        $path = $dir . DIRECTORY_SEPARATOR . $f;
        if (is_dir($path)) {
            if (!delete_directory($path)) return false;
        } else {
            if (!@unlink($path)) return false;
        }
    }
    return @rmdir($dir);
}

$page_title = $L['title'];
header('X-Content-Type-Options: nosniff');
header('X-Frame-Options: SAMEORIGIN');
header('Referrer-Policy: strict-origin-when-cross-origin');
?>
<!DOCTYPE html>
<html lang="<?php echo $is_english ? 'en' : 'tr'; ?>">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title><?php echo htmlspecialchars($page_title); ?></title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        :root {
            --mm-bg: #0f1419;
            --mm-surface: #1a1f26;
            --mm-surface-hover: #232a33;
            --mm-border: #2d3748;
            --mm-text: #e2e8f0;
            --mm-text-muted: #94a3b8;
            --mm-accent: #3b82f6;
            --mm-accent-hover: #2563eb;
            --mm-danger: #ef4444;
            --mm-success: #22c55e;
            --mm-radius: 8px;
            --mm-card-size: 120px;
        }
        body { background: var(--mm-bg); color: var(--mm-text); font-family: 'Segoe UI', system-ui, sans-serif; min-height: 100vh; }
        .mm-wrapper { max-width: 1400px; margin: 0 auto; padding: 1.5rem; }
        .mm-header { display: flex; flex-wrap: wrap; align-items: center; gap: 1rem; margin-bottom: 1rem; }
        .mm-title { font-size: 1.5rem; font-weight: 600; margin: 0; }
        .mm-breadcrumb { display: flex; align-items: center; gap: 0.35rem; flex-wrap: wrap; font-size: 0.875rem; margin-bottom: 1rem; }
        .mm-breadcrumb a { color: var(--mm-text-muted); text-decoration: none; }
        .mm-breadcrumb a:hover { color: var(--mm-accent); }
        .mm-breadcrumb span { color: var(--mm-text-muted); }
        .mm-toolbar { display: flex; flex-wrap: wrap; align-items: center; gap: 0.5rem; margin-bottom: 1rem; }
        .mm-toolbar .btn { font-size: 0.8rem; padding: 0.4rem 0.75rem; }
        .mm-toolbar .btn-primary { background: var(--mm-accent); border-color: var(--mm-accent); }
        .mm-toolbar .btn-primary:hover { background: var(--mm-accent-hover); border-color: var(--mm-accent-hover); }
        .mm-toolbar .btn-outline-danger:hover { background: rgba(239,68,68,0.15); color: var(--mm-danger); }
        .mm-sort { display: flex; align-items: center; gap: 0.5rem; }
        .mm-sort select { background: var(--mm-surface); border: 1px solid var(--mm-border); color: var(--mm-text); padding: 0.35rem 0.6rem; border-radius: var(--mm-radius); font-size: 0.8rem; }
        .mm-drop-zone { min-height: 320px; border: 2px dashed var(--mm-border); border-radius: var(--mm-radius); background: var(--mm-surface); transition: all 0.2s; position: relative; }
        .mm-drop-zone.drag-over { border-color: var(--mm-accent); background: rgba(59,130,246,0.08); }
        .mm-drop-zone .drop-hint { position: absolute; inset: 0; display: none; align-items: center; justify-content: center; background: rgba(59,130,246,0.2); border-radius: var(--mm-radius); font-weight: 600; color: var(--mm-accent); z-index: 5; }
        .mm-drop-zone.drag-over .drop-hint { display: flex; }
        .mm-grid { display: grid; grid-template-columns: repeat(auto-fill, minmax(var(--mm-card-size), 1fr)); gap: 1rem; padding: 1rem 0; }
        .mm-item { width: 100%; aspect-ratio: 1; max-height: 140px; background: var(--mm-surface); border: 1px solid var(--mm-border); border-radius: var(--mm-radius); cursor: pointer; transition: all 0.15s; display: flex; flex-direction: column; overflow: hidden; position: relative; }
        .mm-item:hover { background: var(--mm-surface-hover); border-color: var(--mm-accent); box-shadow: 0 4px 12px rgba(0,0,0,0.3); }
        .mm-item.selected { border-color: var(--mm-accent); box-shadow: 0 0 0 2px rgba(59,130,246,0.4); }
        .mm-item.drag-over-target { border-color: var(--mm-success); background: rgba(34,197,94,0.15); }
        .mm-item.dragging { opacity: 0.5; }
        .mm-item .mm-check { position: absolute; top: 6px; left: 6px; width: 18px; height: 18px; background: var(--mm-surface); border: 2px solid var(--mm-border); border-radius: 4px; z-index: 2; display: flex; align-items: center; justify-content: center; }
        .mm-item.selected .mm-check { background: var(--mm-accent); border-color: var(--mm-accent); }
        .mm-item.selected .mm-check::after { content: '✓'; color: #fff; font-size: 11px; font-weight: bold; }
        .mm-item .mm-thumb { flex: 1; min-height: 0; display: flex; align-items: center; justify-content: center; background: var(--mm-bg); }
        .mm-item .mm-thumb img { width: 100%; height: 100%; object-fit: cover; }
        .mm-item .mm-thumb .mm-icon { font-size: 2.5rem; color: var(--mm-text-muted); }
        .mm-item .mm-label { padding: 0.4rem 0.5rem; font-size: 0.75rem; overflow: hidden; text-overflow: ellipsis; white-space: nowrap; background: var(--mm-surface); border-top: 1px solid var(--mm-border); }
        .mm-item .mm-meta { font-size: 0.65rem; color: var(--mm-text-muted); padding: 0 0.5rem 0.3rem; }
        .mm-sidebar { width: 260px; flex-shrink: 0; }
        .mm-sidebar .card { background: var(--mm-surface); border-color: var(--mm-border); color: var(--mm-text); }
        .mm-sidebar .card-header { background: transparent; border-color: var(--mm-border); font-weight: 600; padding: 0.65rem 1rem; }
        .mm-sidebar .card-body { padding: 1rem; font-size: 0.85rem; }
        .mm-sidebar .card-body .text-muted { color: var(--mm-text-muted) !important; }
        .mm-bulk-bar { position: fixed; bottom: 0; left: 0; right: 0; background: var(--mm-surface); border-top: 1px solid var(--mm-border); padding: 0.75rem 1.5rem; display: none; align-items: center; gap: 1rem; z-index: 1000; }
        .mm-bulk-bar.visible { display: flex; }
        .mm-bulk-bar .count { font-weight: 600; color: var(--mm-accent); }
        .toast-custom { position: fixed; bottom: 4rem; right: 1.5rem; z-index: 1100; }
        .modal-content .form-control, .modal-content .form-select { background: var(--mm-bg); border-color: var(--mm-border); color: var(--mm-text); }
        .modal-content .form-control:focus { background: var(--mm-bg); color: var(--mm-text); border-color: var(--mm-accent); }
        .list-group-item { background: var(--mm-surface); border-color: var(--mm-border); color: var(--mm-text); cursor: pointer; }
        .list-group-item:hover { background: var(--mm-surface-hover); }
    </style>
</head>
<body>
    <div class="mm-wrapper">
        <div class="d-flex flex-column flex-lg-row gap-4">
            <div class="flex-grow-1 min-w-0">
                <div class="mm-header">
                    <h1 class="mm-title"><?php echo htmlspecialchars($page_title); ?></h1>
                </div>
                <div class="mm-breadcrumb" id="breadcrumb">
                    <a href="#" data-path=""><?php echo htmlspecialchars($L['media']); ?></a>
                </div>
                <div class="mm-toolbar">
                    <button type="button" class="btn btn-primary" id="btnUpload"><?php echo htmlspecialchars($L['btn_upload']); ?></button>
                    <input type="file" id="fileInput" class="d-none" multiple accept="<?php echo implode(',', array_map(function ($e) { return '.' . $e; }, $allowed_ext)); ?>">
                    <button type="button" class="btn btn-outline-secondary" id="btnNewFolder"><?php echo htmlspecialchars($L['btn_new_folder']); ?></button>
                    <button type="button" class="btn btn-outline-secondary" id="btnRename" disabled><?php echo htmlspecialchars($L['btn_rename']); ?></button>
                    <button type="button" class="btn btn-outline-danger" id="btnDelete" disabled><?php echo htmlspecialchars($L['btn_delete']); ?></button>
                    <button type="button" class="btn btn-outline-secondary" id="btnMove" disabled><?php echo htmlspecialchars($L['btn_move']); ?></button>
                    <button type="button" class="btn btn-outline-secondary" id="btnCopyPath" disabled><?php echo htmlspecialchars($L['btn_copy_path']); ?></button>
                    <div class="mm-sort ms-auto">
                        <select id="sortSelect" title="Sıralama">
                            <option value="name_asc"><?php echo htmlspecialchars($L['sort_name']); ?> (A-Z)</option>
                            <option value="name_desc"><?php echo htmlspecialchars($L['sort_name']); ?> (Z-A)</option>
                            <option value="date_desc"><?php echo htmlspecialchars($L['sort_date']); ?> (<?php echo $is_english ? 'Newest' : 'Yeni'; ?>)</option>
                            <option value="date_asc"><?php echo htmlspecialchars($L['sort_date']); ?> (<?php echo $is_english ? 'Oldest' : 'Eski'; ?>)</option>
                            <option value="size_desc"><?php echo htmlspecialchars($L['sort_size']); ?> (<?php echo $is_english ? 'Largest' : 'Büyük'; ?>)</option>
                            <option value="size_asc"><?php echo htmlspecialchars($L['sort_size']); ?> (<?php echo $is_english ? 'Smallest' : 'Küçük'; ?>)</option>
                        </select>
                    </div>
                </div>
                <div class="mm-drop-zone" id="dropZone">
                    <div class="drop-hint" id="dropHint"><?php echo htmlspecialchars($L['drop_here']); ?></div>
                    <div class="mm-grid" id="fileList">
                        <div class="w-100 text-center text-muted py-5"><?php echo htmlspecialchars($L['loading']); ?></div>
                    </div>
                </div>
            </div>
            <div class="mm-sidebar d-none d-lg-block">
                <div class="card">
                    <div class="card-header"><?php echo htmlspecialchars($L['folder_info']); ?></div>
                    <div class="card-body">
                        <div class="mb-2"><span class="text-muted"><?php echo htmlspecialchars($L['stats_path']); ?>:</span><br><span id="folderInfoPath" class="text-break small">/</span></div>
                        <div class="mb-2"><span class="text-muted"><?php echo htmlspecialchars($L['stats_files']); ?>:</span> <span id="folderInfoFiles">0</span></div>
                        <div class="mb-2"><span class="text-muted"><?php echo htmlspecialchars($L['stats_folders']); ?>:</span> <span id="folderInfoFolders">0</span></div>
                        <div><span class="text-muted"><?php echo htmlspecialchars($L['stats_total_size']); ?>:</span> <span id="folderInfoSize">0 B</span></div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <div class="mm-bulk-bar" id="bulkBar">
        <span class="count" id="bulkCount">0 <?php echo htmlspecialchars($L['select_count']); ?></span>
        <button type="button" class="btn btn-sm btn-outline-danger" id="bulkDelete"><?php echo htmlspecialchars($L['bulk_delete']); ?></button>
        <button type="button" class="btn btn-sm btn-outline-primary" id="bulkMove"><?php echo htmlspecialchars($L['bulk_move']); ?></button>
    </div>

    <div class="modal fade" id="modalRename" tabindex="-1">
        <div class="modal-dialog">
            <div class="modal-content" style="background:var(--mm-surface);border-color:var(--mm-border);color:var(--mm-text);">
                <div class="modal-header">
                    <h5 class="modal-title"><?php echo htmlspecialchars($L['modal_rename']); ?></h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <label class="form-label"><?php echo htmlspecialchars($L['new_name']); ?></label>
                    <input type="text" class="form-control" id="renameInput" placeholder="<?php echo htmlspecialchars($L['rename_placeholder']); ?>">
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" id="modalRenameCancel" data-bs-dismiss="modal"><?php echo htmlspecialchars($L['cancel']); ?></button>
                    <button type="button" class="btn btn-primary" id="modalRenameOk"><?php echo htmlspecialchars($L['ok']); ?></button>
                </div>
            </div>
        </div>
    </div>
    <div class="modal fade" id="modalNewFolder" tabindex="-1">
        <div class="modal-dialog">
            <div class="modal-content" style="background:var(--mm-surface);border-color:var(--mm-border);color:var(--mm-text);">
                <div class="modal-header">
                    <h5 class="modal-title"><?php echo htmlspecialchars($L['modal_new_folder']); ?></h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <label class="form-label"><?php echo htmlspecialchars($L['folder_name']); ?></label>
                    <input type="text" class="form-control" id="newFolderInput" placeholder="<?php echo htmlspecialchars($L['folder_placeholder']); ?>">
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" id="modalNewFolderCancel" data-bs-dismiss="modal"><?php echo htmlspecialchars($L['cancel']); ?></button>
                    <button type="button" class="btn btn-primary" id="modalNewFolderOk"><?php echo htmlspecialchars($L['create']); ?></button>
                </div>
            </div>
        </div>
    </div>
    <div class="modal fade" id="modalMoveTarget" tabindex="-1" data-bs-backdrop="static">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content" style="background:var(--mm-surface);border-color:var(--mm-border);color:var(--mm-text);">
                <div class="modal-header border-secondary">
                    <h5 class="modal-title"><?php echo htmlspecialchars($L['btn_move']); ?></h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <p class="text-muted small mb-2" id="moveTargetPath">/</p>
                    <div id="moveTargetFolders" class="list-group" style="background:transparent;max-height:300px;overflow-y:auto;"></div>
                    <button type="button" class="btn btn-primary btn-sm mt-2" id="moveTargetSelectCurrent"><?php echo htmlspecialchars($L['move_to']); ?> (<?php echo htmlspecialchars($L['stats_root']); ?>)</button>
                </div>
            </div>
        </div>
    </div>
    <div class="modal fade" id="modalPaths" tabindex="-1">
        <div class="modal-dialog">
            <div class="modal-content" style="background:var(--mm-surface);border-color:var(--mm-border);color:var(--mm-text);">
                <div class="modal-header">
                    <h5 class="modal-title"><?php echo htmlspecialchars($L['modal_paths']); ?></h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <label class="form-label"><?php echo htmlspecialchars($L['physical_path']); ?></label>
                    <div class="input-group mb-2">
                        <input type="text" class="form-control form-control-sm" id="pathPhysical" readonly>
                        <button type="button" class="btn btn-outline-secondary btn-sm" id="copyPhysical"><?php echo htmlspecialchars($L['copy']); ?></button>
                    </div>
                    <label class="form-label"><?php echo htmlspecialchars($L['url']); ?></label>
                    <div class="input-group">
                        <input type="text" class="form-control form-control-sm" id="pathUrl" readonly>
                        <button type="button" class="btn btn-outline-secondary btn-sm" id="copyUrl"><?php echo htmlspecialchars($L['copy']); ?></button>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-primary" id="modalPathsClose" data-bs-dismiss="modal"><?php echo htmlspecialchars($L['close']); ?></button>
                </div>
            </div>
        </div>
    </div>
    <div class="toast align-items-center text-white bg-dark border-0 toast-custom" id="toast" role="alert">
        <div class="d-flex"><div class="toast-body" id="toastBody"></div></div>
    </div>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
    <script>
    var L = <?php echo json_encode($L); ?>;
    var MEDIA_CSRF = <?php echo json_encode($_SESSION['media_csrf']); ?>;
    var ALLOWED_EXT = <?php echo json_encode($allowed_ext); ?>;
    </script>
    <script>
(function() {
    const IMG_EXT = ['jpg','jpeg','png','gif','webp','svg'];
    let currentPath = '';
    let selectedPaths = [];
    let selectedIsDirMap = {};
    let sortBy = 'name', sortOrder = 'asc';
    let moveTargetPath = '';
    let modalRename, modalNewFolder, modalPaths, modalMoveTarget, toastEl;
    let isMoveMode = false;
    let dragSourcePath = null;
    let dragSourceIsDir = false;

    function ajax(data, formData) {
        if (formData) {
            formData.append('csrf', MEDIA_CSRF);
        } else {
            data.csrf = MEDIA_CSRF;
        }
        const body = formData || new URLSearchParams(data);
        return fetch('?ajax=1&sort=' + sortBy + '&order=' + sortOrder, { method: 'POST', body: body }).then(r => r.json());
    }

    function listParams() {
        const params = new URLSearchParams();
        params.set('action', 'list');
        params.set('path', currentPath);
        params.set('sort', sortBy);
        params.set('order', sortOrder);
        return params.toString();
    }

    function escapeAttr(s) { return String(s).replace(/&/g,'&amp;').replace(/"/g,'&quot;').replace(/</g,'&lt;'); }
    function escapeHtml(s) { return String(s).replace(/&/g,'&amp;').replace(/</g,'&lt;').replace(/>/g,'&gt;').replace(/"/g,'&quot;'); }

    function formatSize(bytes) {
        if (bytes == null) return '–';
        if (bytes < 1024) return bytes + ' B';
        if (bytes < 1024*1024) return (bytes/1024).toFixed(1) + ' KB';
        return (bytes/(1024*1024)).toFixed(1) + ' MB';
    }
    function formatDate(t) {
        if (!t) return '–';
        const d = new Date(t*1000);
        return d.toLocaleDateString(L.locale || 'tr-TR') + ' ' + d.toLocaleTimeString(L.locale || 'tr-TR', { hour: '2-digit', minute: '2-digit' });
    }
    function thumbIcon(ext) {
        const e = (ext || '').toLowerCase();
        if (e === 'pdf') return '📄';
        if (['doc','docx','xls','xlsx'].indexOf(e) >= 0) return '📋';
        if (e === 'zip') return '📦';
        return '📄';
    }
    function isImage(item) { return item.url && IMG_EXT.indexOf((item.ext || '').toLowerCase()) >= 0; }
    function hasValidExt(name) {
        const ext = (name.split('.').pop() || '').toLowerCase();
        return ALLOWED_EXT && ALLOWED_EXT.indexOf(ext) >= 0;
    }

    function renderBreadcrumb(path) {
        const parts = path ? path.split('/').filter(Boolean) : [];
        let html = '<a href="#" data-path="">' + escapeHtml(L.media) + '</a>';
        let acc = '';
        parts.forEach(function(p) {
            acc += (acc ? '/' : '') + p;
            html += ' <span>/</span> <a href="#" data-path="' + escapeAttr(acc) + '">' + escapeHtml(p) + '</a>';
        });
        document.getElementById('breadcrumb').innerHTML = html;
    }

    function updateFolderInfo(items) {
        document.getElementById('folderInfoPath').textContent = currentPath ? '/' + currentPath : L.stats_root;
        let fileCount = 0, folderCount = 0, totalSize = 0;
        (items || []).forEach(function(item) {
            if (item.dir) folderCount++; else { fileCount++; totalSize += item.size || 0; }
        });
        document.getElementById('folderInfoFiles').textContent = fileCount;
        document.getElementById('folderInfoFolders').textContent = folderCount;
        document.getElementById('folderInfoSize').textContent = formatSize(totalSize);
    }

    function updateSelectionUI() {
        const n = selectedPaths.length;
        const hasFiles = selectedPaths.some(p => !selectedIsDirMap[p]);
        const hasAny = n > 0;
        document.getElementById('btnRename').disabled = n !== 1;
        document.getElementById('btnDelete').disabled = !hasAny;
        document.getElementById('btnMove').disabled = !hasAny;
        document.getElementById('btnCopyPath').disabled = !(n === 1 && hasFiles);
        const bulkBar = document.getElementById('bulkBar');
        const bulkCount = document.getElementById('bulkCount');
        if (n > 1) {
            bulkBar.classList.add('visible');
            bulkCount.textContent = n + ' ' + L.select_count;
        } else {
            bulkBar.classList.remove('visible');
        }
    }

    function loadList() {
        const listEl = document.getElementById('fileList');
        listEl.innerHTML = '<div class="w-100 text-center py-5" style="color:var(--mm-text-muted)">' + escapeHtml(L.loading) + '</div>';
        fetch('?ajax=1&' + listParams()).then(r => r.json()).then(function(res) {
            if (!res.success) {
                listEl.innerHTML = '<div class="w-100 text-center py-5" style="color:var(--mm-danger)">' + escapeHtml(res.error || L.list_error) + '</div>';
                updateFolderInfo([]);
                return;
            }
            const items = res.data.items || [];
            updateFolderInfo(items);
            selectedPaths = [];
            selectedIsDirMap = {};
            updateSelectionUI();
            if (items.length === 0) {
                listEl.innerHTML = '<div class="w-100 text-center py-5" style="color:var(--mm-text-muted)">' + escapeHtml(L.empty_dir) + '</div>';
                renderBreadcrumb(currentPath);
                return;
            }
            listEl.innerHTML = items.map(function(item) {
                const path = escapeAttr(item.path);
                const dir = item.dir ? '1' : '0';
                const name = escapeAttr(item.name);
                let thumb = '';
                if (item.dir) {
                    thumb = '<div class="mm-thumb"><span class="mm-icon">📁</span></div>';
                } else if (isImage(item)) {
                    thumb = '<div class="mm-thumb"><img src="' + escapeAttr(item.url) + '" alt="" loading="lazy" onerror="this.parentElement.innerHTML=\'<span class=mm-icon>'+thumbIcon(item.ext)+'</span>\'"></div>';
                } else {
                    thumb = '<div class="mm-thumb"><span class="mm-icon">' + thumbIcon(item.ext) + '</span></div>';
                }
                const meta = item.dir ? '' : (formatSize(item.size) + ' · ' + formatDate(item.mtime));
                return '<div class="mm-item" data-path="' + path + '" data-dir="' + dir + '" data-name="' + name + '" draggable="true">' +
                    '<div class="mm-check"></div>' + thumb +
                    '<div class="mm-label">' + escapeHtml(item.name) + '</div>' +
                    (meta ? '<div class="mm-meta">' + escapeHtml(meta) + '</div>' : '') +
                    '</div>';
            }).join('');
            bindRowEvents();
            renderBreadcrumb(currentPath);
        });
    }

    function toggleSelect(path, isDir, card, additive) {
        if (additive) {
            const idx = selectedPaths.indexOf(path);
            if (idx >= 0) {
                selectedPaths.splice(idx, 1);
                delete selectedIsDirMap[path];
            } else {
                selectedPaths.push(path);
                selectedIsDirMap[path] = isDir;
            }
        } else {
            selectedPaths = [path];
            selectedIsDirMap = { [path]: isDir };
        }
        document.querySelectorAll('.mm-item').forEach(function(el) {
            el.classList.toggle('selected', selectedPaths.indexOf(el.getAttribute('data-path')) >= 0);
        });
        updateSelectionUI();
    }

    function bindRowEvents() {
        document.querySelectorAll('.mm-item').forEach(function(itemEl) {
            const path = itemEl.getAttribute('data-path');
            const isDir = itemEl.getAttribute('data-dir') === '1';
            const name = itemEl.getAttribute('data-name');

            itemEl.addEventListener('click', function(e) {
                if (e.target.closest('.mm-check')) return;
                if (isMoveMode) {
                    if (isDir && path !== currentPath) {
                        doMove(selectedPaths, path);
                        modalMoveTarget.hide();
                        isMoveMode = false;
                    }
                    return;
                }
                const additive = e.ctrlKey || e.metaKey;
                if (isDir && !additive) {
                    currentPath = path;
                    loadList();
                } else {
                    toggleSelect(path, isDir, itemEl, additive);
                }
            });

            itemEl.addEventListener('dragstart', function(e) {
                dragSourcePath = path;
                dragSourceIsDir = isDir;
                e.dataTransfer.setData('text/plain', path);
                e.dataTransfer.effectAllowed = 'move';
                itemEl.classList.add('dragging');
            });
            itemEl.addEventListener('dragend', function() { itemEl.classList.remove('dragging'); dragSourcePath = null; });

            if (isDir) {
                itemEl.addEventListener('dragover', function(e) {
                    e.preventDefault();
                    if (dragSourcePath && path !== dragSourcePath && !(path + '/').startsWith(dragSourcePath + '/')) {
                        itemEl.classList.add('drag-over-target');
                    }
                });
                itemEl.addEventListener('dragleave', function() { itemEl.classList.remove('drag-over-target'); });
                itemEl.addEventListener('drop', function(e) {
                    e.preventDefault();
                    itemEl.classList.remove('drag-over-target');
                    const src = e.dataTransfer.getData('text/plain') || dragSourcePath;
                    if (src && src !== path && !(path + '/').startsWith(src + '/')) doMove([src], path);
                });
            }
        });

        document.querySelectorAll('.mm-item .mm-check').forEach(function(chk) {
            chk.addEventListener('click', function(e) {
                e.stopPropagation();
                const itemEl = chk.closest('.mm-item');
                const path = itemEl.getAttribute('data-path');
                const isDir = itemEl.getAttribute('data-dir') === '1';
                toggleSelect(path, isDir, itemEl, true);
            });
        });
    }

    function doMove(paths, target) {
        const fd = new FormData();
        fd.append('action', 'move');
        fd.append('target', target);
        fd.append('csrf', MEDIA_CSRF);
        paths.forEach(p => fd.append('paths[]', p));
        fetch('?ajax=1', { method: 'POST', body: fd }).then(r => r.json()).then(function(res) {
            if (res.success) { toast(L.moved); loadList(); }
            else toast(res.error || L.list_error, true);
        });
    }

    document.getElementById('breadcrumb').addEventListener('click', function(e) {
        const a = e.target.closest('a[data-path]');
        if (!a) return;
        e.preventDefault();
        currentPath = a.getAttribute('data-path') || '';
        loadList();
    });

    document.getElementById('sortSelect').addEventListener('change', function() {
        const v = this.value;
        const [s, o] = v.split('_');
        sortBy = s;
        sortOrder = o || 'asc';
        loadList();
    });

    const dropZone = document.getElementById('dropZone');
    const dropHint = document.getElementById('dropHint');
    dropZone.addEventListener('dragover', function(e) {
        e.preventDefault();
        if (e.dataTransfer.types.indexOf('Files') >= 0) {
            dropZone.classList.add('drag-over');
        }
    });
    dropZone.addEventListener('dragleave', function(e) {
        if (!dropZone.contains(e.relatedTarget)) dropZone.classList.remove('drag-over');
    });
    dropZone.addEventListener('drop', function(e) {
        e.preventDefault();
        dropZone.classList.remove('drag-over');
        const files = Array.from(e.dataTransfer.files || []).filter(f => !f.name.startsWith('.') && hasValidExt(f.name));
        if (files.length === 0) return;
        let ok = 0, err = 0;
        function uploadNext(i) {
            if (i >= files.length) {
                toast(ok + ' ' + L.uploaded_count + (err ? ', ' + err + ' ' + L.upload_failed_count : ''), err > 0);
                loadList();
                return;
            }
            const fd = new FormData();
            fd.append('action', 'upload');
            fd.append('path', currentPath);
            fd.append('file', files[i]);
            fd.append('csrf', MEDIA_CSRF);
            fetch('?ajax=1', { method: 'POST', body: fd }).then(r => r.json()).then(function(res) {
                if (res.success) ok++; else err++;
                uploadNext(i + 1);
            }).catch(function() { err++; uploadNext(i + 1); });
        }
        uploadNext(0);
    });

    document.getElementById('btnUpload').addEventListener('click', function() { document.getElementById('fileInput').click(); });
    document.getElementById('fileInput').addEventListener('change', function() {
        const files = Array.from(this.files || []).filter(f => hasValidExt(f.name));
        if (files.length === 0) return;
        let ok = 0, err = 0;
        function doNext(i) {
            if (i >= files.length) {
                toast(ok + ' ' + L.uploaded_count + (err ? ', ' + err + ' ' + L.upload_failed_count : ''), err > 0);
                loadList();
                return;
            }
            const fd = new FormData();
            fd.append('action', 'upload');
            fd.append('path', currentPath);
            fd.append('file', files[i]);
            fd.append('csrf', MEDIA_CSRF);
            fetch('?ajax=1', { method: 'POST', body: fd }).then(r => r.json()).then(function(res) {
                if (res.success) ok++; else err++;
                doNext(i + 1);
            }).catch(function() { err++; doNext(i + 1); });
        }
        doNext(0);
        this.value = '';
    });

    modalRename = new bootstrap.Modal(document.getElementById('modalRename'));
    modalNewFolder = new bootstrap.Modal(document.getElementById('modalNewFolder'));
    modalPaths = new bootstrap.Modal(document.getElementById('modalPaths'));
    modalMoveTarget = new bootstrap.Modal(document.getElementById('modalMoveTarget'));
    toastEl = document.getElementById('toast');

    document.getElementById('btnNewFolder').addEventListener('click', function() {
        document.getElementById('newFolderInput').value = '';
        modalNewFolder.show();
        setTimeout(function() { document.getElementById('newFolderInput').focus(); }, 300);
    });
    document.getElementById('modalNewFolderOk').addEventListener('click', function() {
        const name = document.getElementById('newFolderInput').value.trim().replace(/[^\w.\-]/g, '_');
        if (!name) return;
        const fd = new FormData();
        fd.append('action', 'mkdir');
        fd.append('path', currentPath);
        fd.append('name', name);
        fd.append('csrf', MEDIA_CSRF);
        fetch('?ajax=1', { method: 'POST', body: fd }).then(r => r.json()).then(function(res) {
            if (res.success) { toast(L.folder_created); modalNewFolder.hide(); loadList(); }
            else toast(res.error || L.list_error, true);
        });
    });

    document.getElementById('btnRename').addEventListener('click', function() {
        if (selectedPaths.length !== 1) return;
        openRenameModal(selectedPaths[0], selectedPaths[0].split('/').pop());
    });
    function openRenameModal(path, name) {
        document.getElementById('renameInput').value = name;
        document.getElementById('renameInput').setAttribute('data-path', path);
        modalRename.show();
        setTimeout(function() { document.getElementById('renameInput').focus(); }, 300);
    }
    document.getElementById('modalRenameOk').addEventListener('click', function() {
        const path = document.getElementById('renameInput').getAttribute('data-path');
        const newname = document.getElementById('renameInput').value.trim();
        if (!path || !newname) return;
        const fd = new FormData();
        fd.append('action', 'rename');
        fd.append('path', path);
        fd.append('newname', newname);
        fd.append('csrf', MEDIA_CSRF);
        fetch('?ajax=1', { method: 'POST', body: fd }).then(r => r.json()).then(function(res) {
            if (res.success) { toast(L.renamed); modalRename.hide(); selectedPaths = []; loadList(); }
            else toast(res.error || L.list_error, true);
        });
    });

    document.getElementById('btnDelete').addEventListener('click', function() { doBulkDelete(); });
    document.getElementById('bulkDelete').addEventListener('click', function() { doBulkDelete(); });

    function doBulkDelete() {
        const paths = selectedPaths.length ? selectedPaths : (selectedPaths[0] ? [selectedPaths[0]] : []);
        if (paths.length === 0) return;
        const msg = paths.length > 1
            ? (paths.length + ' – ' + (L.bulk_delete_confirm || 'Seçilen öğeler silinecek') + ' ' + L.confirm_continue)
            : ((selectedIsDirMap[paths[0]] ? L.confirm_delete_folder : L.confirm_delete_file) + ': ' + paths[0].split('/').pop() + '\n' + L.confirm_continue);
        if (!confirm(msg)) return;
        const fd = new FormData();
        fd.append('action', 'delete');
        paths.forEach(p => fd.append('paths[]', p));
        fd.append('csrf', MEDIA_CSRF);
        fetch('?ajax=1', { method: 'POST', body: fd }).then(r => r.json()).then(function(res) {
            if (res.success) { toast(L.deleted); selectedPaths = []; loadList(); updateSelectionUI(); }
            else toast(res.error || L.delete_failed, true);
        });
    }

    document.getElementById('btnMove').addEventListener('click', function() { openMoveModal(); });
    document.getElementById('bulkMove').addEventListener('click', function() { openMoveModal(); });

    function openMoveModal() {
        if (selectedPaths.length === 0) return;
        moveTargetPath = currentPath;
        isMoveMode = true;
        document.getElementById('moveTargetPath').textContent = '/' + (moveTargetPath || L.stats_root);
        loadMoveTargetFolders(moveTargetPath);
        document.getElementById('moveTargetSelectCurrent').textContent = L.move_to + ' (' + (moveTargetPath ? moveTargetPath.split('/').pop() : L.stats_root) + ')';
        modalMoveTarget.show();
    }

    function loadMoveTargetFolders(path) {
        const folderList = document.getElementById('moveTargetFolders');
        folderList.innerHTML = '<div class="text-muted small py-2">' + escapeHtml(L.loading) + '</div>';
        fetch('?ajax=1&action=list&path=' + encodeURIComponent(path) + '&sort=name&order=asc').then(r => r.json()).then(function(res) {
            if (!res.success) {
                folderList.innerHTML = '<div class="text-danger small">' + escapeHtml(res.error) + '</div>';
                return;
            }
            const dirs = (res.data.items || []).filter(i => i.dir);
            folderList.innerHTML = dirs.length === 0
                ? '<div class="text-muted small">' + escapeHtml(L.empty_dir) + '</div>'
                : dirs.map(function(d) {
                    return '<a href="#" class="list-group-item list-group-item-action move-folder" data-path="' + escapeAttr(d.path) + '">📁 ' + escapeHtml(d.name) + '</a>';
                }).join('');
            folderList.querySelectorAll('.move-folder').forEach(function(a) {
                a.addEventListener('click', function(e) {
                    e.preventDefault();
                    moveTargetPath = a.getAttribute('data-path');
                    document.getElementById('moveTargetPath').textContent = '/' + moveTargetPath;
                    document.getElementById('moveTargetSelectCurrent').textContent = L.move_to + ' (' + moveTargetPath.split('/').pop() + ')';
                    loadMoveTargetFolders(moveTargetPath);
                });
            });
        });
    }

    document.getElementById('moveTargetSelectCurrent').addEventListener('click', function() {
        doMove(selectedPaths, moveTargetPath);
        modalMoveTarget.hide();
        isMoveMode = false;
    });

    document.getElementById('btnCopyPath').addEventListener('click', function() {
        if (selectedPaths.length !== 1 || selectedIsDirMap[selectedPaths[0]]) return;
        openPathsModal(selectedPaths[0]);
    });
    function openPathsModal(path) {
        fetch('?ajax=1&action=paths&path=' + encodeURIComponent(path)).then(r => r.json()).then(function(res) {
            if (!res.success) { toast(res.error || L.path_error, true); return; }
            document.getElementById('pathPhysical').value = res.data.physical;
            document.getElementById('pathUrl').value = res.data.url;
            modalPaths.show();
        });
    }

    document.getElementById('pathPhysical').addEventListener('focus', function() { this.select(); });
    document.getElementById('pathUrl').addEventListener('focus', function() { this.select(); });
    document.getElementById('copyPhysical').addEventListener('click', function() {
        const el = document.getElementById('pathPhysical');
        el.select();
        try { navigator.clipboard.writeText(el.value); toast(L.path_copied); } catch (e) { toast(L.copy_failed, true); }
    });
    document.getElementById('copyUrl').addEventListener('click', function() {
        const el = document.getElementById('pathUrl');
        el.select();
        try { navigator.clipboard.writeText(el.value); toast(L.url_copied); } catch (e) { toast(L.copy_failed, true); }
    });

    function toast(msg, isError) {
        toastEl.querySelector('#toastBody').textContent = msg;
        toastEl.classList.remove('bg-dark', 'bg-danger');
        toastEl.classList.add(isError ? 'bg-danger' : 'bg-dark', 'show');
        setTimeout(function() { toastEl.classList.remove('show'); }, 3500);
    }

    loadList();
})();
    </script>
</body>
</html>
