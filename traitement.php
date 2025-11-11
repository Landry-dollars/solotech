<?php
// traitement.php - handle product add form (multipart)
// Security: requires login, validates CSRF, validates inputs, handles safe image upload,
// and inserts into `products` table using prepared statements.

require_once __DIR__ . '/auth.php'; // starts session and provides require_login()
require_once __DIR__ . '/csrf.php';
require_once __DIR__ . '/db.php';

// Only accept POST
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: dashboard.php');
    exit;
}

require_login();

$errors = [];

// Validate CSRF
$token = $_POST['csrf'] ?? $_POST['token'] ?? '';
if (!validate_csrf($token)) {
    $errors[] = 'Invalid CSRF token.';
}

// Collect and validate inputs
$name = trim($_POST['name'] ?? '');
$category = trim($_POST['category'] ?? '');
$description = trim($_POST['description'] ?? '');
$price_raw = $_POST['price'] ?? '';

if ($name === '') $errors[] = 'Product name is required.';
if ($category === '') $errors[] = 'Category is required.';
if ($description === '') $errors[] = 'Description is required.';

// Price numeric check
if ($price_raw === '') {
    $errors[] = 'Price is required.';
} else {
    // allow decimals, use float
    $price = filter_var($price_raw, FILTER_VALIDATE_FLOAT);
    if ($price === false || $price < 0) {
        $errors[] = 'Invalid price.';
    }
}

// Allowed categories (optional): keep in sync with front-end options
$allowed_categories = ['bestseller','computer','phone','accessories'];
if ($category !== '' && !in_array($category, $allowed_categories, true)) {
    // Accept any category but warn; or restrict strictly — here we restrict
    $errors[] = 'Invalid category.';
}

// Handle image upload (optional)
$image_filename = '';
if (!empty($_FILES['image']) && $_FILES['image']['error'] !== UPLOAD_ERR_NO_FILE) {
    $img = $_FILES['image'];

    if ($img['error'] !== UPLOAD_ERR_OK) {
        $errors[] = 'Image upload error.';
    } else {
        // Validate size (max 5MB)
        $maxBytes = 5 * 1024 * 1024;
        if ($img['size'] > $maxBytes) {
            $errors[] = 'Image too large (max 5MB).';
        } else {
            // Validate MIME using finfo
            $finfo = finfo_open(FILEINFO_MIME_TYPE);
            $mime = finfo_file($finfo, $img['tmp_name']);
            finfo_close($finfo);

            $ext = '';
            switch ($mime) {
                case 'image/jpeg': $ext = 'jpg'; break;
                case 'image/png': $ext = 'png'; break;
                case 'image/gif': $ext = 'gif'; break;
                case 'image/webp': $ext = 'webp'; break;
                default:
                    $errors[] = 'Unsupported image type.';
            }

            if ($ext !== '') {
                // Prepare uploads dir
                $uploadDir = __DIR__ . '/uploads';
                if (!is_dir($uploadDir)) {
                    @mkdir($uploadDir, 0755, true);
                }

                // Randomize name
                try {
                    $rand = bin2hex(random_bytes(12));
                } catch (Exception $e) {
                    $rand = uniqid('', true);
                }
                $safeName = $rand . '.' . $ext;
                $dest = $uploadDir . '/' . $safeName;

                if (!move_uploaded_file($img['tmp_name'], $dest)) {
                    $errors[] = 'Failed to save uploaded image.';
                } else {
                    // Optionally set restrictive permissions
                    @chmod($dest, 0644);
                    $image_filename = $safeName;

                    // Try to create resized image and thumbnail using GD
                    // resized: max 1200x1200, thumb: 300x300
                    $thumbDir = __DIR__ . '/uploads/thumbs';
                    if (!is_dir($thumbDir)) {
                        @mkdir($thumbDir, 0755, true);
                    }

                    // helper to load image by mime
                    $createFunc = null;
                    $saveFunc = null;
                    $quality = 85;
                    switch ($mime) {
                        case 'image/jpeg':
                            $createFunc = 'imagecreatefromjpeg';
                            $saveFunc = 'imagejpeg';
                            break;
                        case 'image/png':
                            $createFunc = 'imagecreatefrompng';
                            $saveFunc = 'imagepng';
                            break;
                        case 'image/gif':
                            $createFunc = 'imagecreatefromgif';
                            $saveFunc = 'imagegif';
                            break;
                        case 'image/webp':
                            $createFunc = 'imagecreatefromwebp';
                            $saveFunc = 'imagewebp';
                            break;
                    }

                    if ($createFunc && function_exists($createFunc)) {
                        try {
                            $srcImg = @$createFunc($dest);
                            if ($srcImg) {
                                $w = imagesx($srcImg);
                                $h = imagesy($srcImg);

                                // Resized image (max 1200)
                                $max = 1200;
                                $scale = min(1, $max / max($w, $h));
                                if ($scale < 1) {
                                    $nw = (int)($w * $scale);
                                    $nh = (int)($h * $scale);
                                    $resized = imagecreatetruecolor($nw, $nh);
                                    // preserve PNG/GIF transparency
                                    if ($mime === 'image/png' || $mime === 'image/gif') {
                                        imagecolortransparent($resized, imagecolorallocatealpha($resized, 0, 0, 0, 127));
                                        imagealphablending($resized, false);
                                        imagesavealpha($resized, true);
                                    }
                                    imagecopyresampled($resized, $srcImg, 0,0,0,0, $nw, $nh, $w, $h);
                                    $resizedPath = $dest; // overwrite original with resized
                                    if ($saveFunc === 'imagepng') {
                                        $saveFunc($resizedPath, $resized);
                                    } elseif ($saveFunc === 'imagejpeg') {
                                        $saveFunc($resizedPath, $resized, $quality);
                                    } elseif ($saveFunc === 'imagewebp') {
                                        $saveFunc($resizedPath, $resized, $quality);
                                    } else {
                                        $saveFunc($resizedPath, $resized);
                                    }
                                    imagedestroy($resized);
                                }

                                // Create thumbnail (300x300, fit inside)
                                $thumbW = 300; $thumbH = 300;
                                $ratio = min($thumbW / $w, $thumbH / $h);
                                $tw = (int)($w * $ratio);
                                $th = (int)($h * $ratio);
                                $thumb = imagecreatetruecolor($tw, $th);
                                if ($mime === 'image/png' || $mime === 'image/gif') {
                                    imagecolortransparent($thumb, imagecolorallocatealpha($thumb, 0, 0, 0, 127));
                                    imagealphablending($thumb, false);
                                    imagesavealpha($thumb, true);
                                }
                                imagecopyresampled($thumb, $srcImg, 0,0,0,0, $tw, $th, $w, $h);
                                $thumbPath = $thumbDir . '/thumb_' . $safeName;
                                if ($saveFunc === 'imagepng') {
                                    $saveFunc($thumbPath, $thumb);
                                } elseif ($saveFunc === 'imagejpeg') {
                                    $saveFunc($thumbPath, $thumb, $quality);
                                } elseif ($saveFunc === 'imagewebp') {
                                    $saveFunc($thumbPath, $thumb, $quality);
                                } else {
                                    $saveFunc($thumbPath, $thumb);
                                }
                                imagedestroy($thumb);
                                imagedestroy($srcImg);
                                @chmod($thumbPath, 0644);
                            }
                        } catch (Throwable $e) {
                            // non-fatal: leave original image
                            error_log('Image processing error: ' . $e->getMessage());
                        }
                    }
                }
            }
        }
    }
}

// If any errors, redirect back with message
if (!empty($errors)) {
    $msg = rawurlencode(implode(' | ', $errors));
    header('Location: dashboard.php?error=' . $msg);
    exit;
}

// Insert into DB
$sql = 'INSERT INTO product (name, category, description, price, image) VALUES (?, ?, ?, ?, ?)';
$stmt = $conn->prepare($sql);
if (!$stmt) {
    error_log('DB prepare error: ' . $conn->error);
    header('Location: dashboard.php?error=' . rawurlencode('DB prepare error'));
    exit;
}

$price_val = (float)$price; // ensured above
$stmt->bind_param('sssds', $name, $category, $description, $price_val, $image_filename);

if ($stmt->execute()) {
    $stmt->close();
    $conn->close();
    header('Location: dashboard.php?success=' . rawurlencode('Product added'));
    exit;
} else {
    error_log('DB execute error: ' . $stmt->error);
    $stmt->close();
    $conn->close();
    header('Location: dashboard.php?error=' . rawurlencode('DB execute error'));
    exit;
}

?>