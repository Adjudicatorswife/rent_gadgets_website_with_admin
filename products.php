<?php
error_reporting(0);
ini_set('display_errors', 0);
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');

require_once '../php/config.php';
$action = $_GET['action'] ?? $_POST['action'] ?? '';

switch ($action) {
    case 'list':       listProducts();    break;
    case 'featured':   featuredProducts();break;
    case 'detail':     productDetail();   break;
    case 'search':     searchProducts();  break;
    case 'categories': getCategories();   break;
    case 'add':        requireAdmin(); addProduct();    break;
    case 'update':     requireAdmin(); updateProduct(); break;
    case 'delete':     requireAdmin(); deleteProduct(); break;
    default: jsonResponse(['error'=>'Invalid action'], 400);
}

function listProducts() {
    $db     = getDB();
    $cat    = intval($_GET['category'] ?? 0);
    $limit  = intval($_GET['limit']    ?? 20);
    $offset = intval($_GET['offset']   ?? 0);

    $where = 'WHERE p.is_available=1';
    if ($cat) $where .= " AND p.category_id=" . intval($cat);

    $stmt = $db->prepare("SELECT p.*, c.name as category_name
                          FROM products p LEFT JOIN categories c ON p.category_id=c.id
                          $where ORDER BY p.is_featured DESC, p.id DESC LIMIT ? OFFSET ?");
    $stmt->bind_param('ii', $limit, $offset);
    $stmt->execute();
    $rows = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
    foreach ($rows as &$r) {
        if ($r['specs'])  $r['specs']  = json_decode($r['specs'],  true);
        if ($r['images']) $r['images'] = json_decode($r['images'], true);
    }
    $total = $db->query("SELECT COUNT(*) as t FROM products WHERE is_available=1" . ($cat ? " AND category_id=$cat" : ''))->fetch_assoc()['t'];
    jsonResponse(['products'=>$rows, 'total'=>$total, 'limit'=>$limit, 'offset'=>$offset]);
}

function featuredProducts() {
    $db   = getDB();
    $stmt = $db->prepare("SELECT p.*, c.name as category_name FROM products p
                          LEFT JOIN categories c ON p.category_id=c.id
                          WHERE p.is_featured=1 AND p.is_available=1 LIMIT 6");
    $stmt->execute();
    $rows = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
    foreach ($rows as &$r) {
        if ($r['specs'])  $r['specs']  = json_decode($r['specs'],  true);
        if ($r['images']) $r['images'] = json_decode($r['images'], true);
    }
    jsonResponse(['products'=>$rows]);
}

function productDetail() {
    $id = intval($_GET['id'] ?? 0);
    if (!$id) jsonResponse(['error'=>'ID required'], 400);
    $db   = getDB();
    $stmt = $db->prepare("SELECT p.*, c.name as category_name FROM products p
                          LEFT JOIN categories c ON p.category_id=c.id WHERE p.id=?");
    $stmt->bind_param('i', $id); $stmt->execute();
    $product = $stmt->get_result()->fetch_assoc();
    if (!$product) jsonResponse(['error'=>'Product not found'], 404);
    if ($product['specs'])  $product['specs']  = json_decode($product['specs'],  true);
    if ($product['images']) $product['images'] = json_decode($product['images'], true);
    $rev = $db->prepare("SELECT r.*, u.full_name, u.avatar FROM reviews r
                         JOIN users u ON r.user_id=u.id WHERE r.product_id=? ORDER BY r.created_at DESC");
    $rev->bind_param('i', $id); $rev->execute();
    $product['reviews'] = $rev->get_result()->fetch_all(MYSQLI_ASSOC);
    $ratings = array_column($product['reviews'], 'rating');
    $product['avg_rating'] = count($ratings) ? array_sum($ratings)/count($ratings) : 0;
    jsonResponse(['product'=>$product]);
}

function searchProducts() {
    $q = sanitize($_GET['q'] ?? '');
    if (!$q) jsonResponse(['products'=>[]]);
    $db   = getDB();
    $like = "%$q%";
    $stmt = $db->prepare("SELECT p.*, c.name as category_name FROM products p
                          LEFT JOIN categories c ON p.category_id=c.id
                          WHERE p.is_available=1
                          AND (p.name LIKE ? OR p.brand LIKE ? OR p.model LIKE ? OR p.description LIKE ?)
                          LIMIT 20");
    $stmt->bind_param('ssss', $like, $like, $like, $like); $stmt->execute();
    $rows = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
    foreach ($rows as &$r) { if ($r['specs']) $r['specs'] = json_decode($r['specs'], true); }
    jsonResponse(['products'=>$rows]);
}

function getCategories() {
    $db   = getDB();
    $stmt = $db->prepare("SELECT c.*, COUNT(p.id) as product_count FROM categories c
                          LEFT JOIN products p ON c.id=p.category_id AND p.is_available=1
                          GROUP BY c.id ORDER BY c.id");
    $stmt->execute();
    jsonResponse(['categories'=>$stmt->get_result()->fetch_all(MYSQLI_ASSOC)]);
}

function addProduct() {
    $db    = getDB();
    $name  = sanitize($_POST['name']             ?? '');
    $brand = sanitize($_POST['brand']            ?? '');
    $model = sanitize($_POST['model']            ?? '');
    $desc  = sanitize($_POST['description']      ?? '');
    $cat   = intval($_POST['category_id']        ?? 0);
    $price = floatval($_POST['price_per_day']    ?? 0);
    $stock = intval($_POST['stock']              ?? 1);
    $cond  = sanitize($_POST['condition_rating'] ?? 'Good');
    $feat  = intval($_POST['is_featured']        ?? 0);
    $specs = $_POST['specs']                     ?? '{}';

    if (!$name || !$price) jsonResponse(['success'=>false, 'error'=>'Name and price are required'], 400);

    // Validate JSON specs
    json_decode($specs);
    if (json_last_error() !== JSON_ERROR_NONE) $specs = '{}';

    // Handle image upload
    $image = 'default_product.jpg';
    if (isset($_FILES['image']) && $_FILES['image']['error'] === UPLOAD_ERR_OK) {
        $ext     = strtolower(pathinfo($_FILES['image']['name'], PATHINFO_EXTENSION));
        $allowed = ['jpg','jpeg','png','webp','gif'];
        if (in_array($ext, $allowed)) {
            $image = uniqid('prod_') . '.' . $ext;
            $dest  = UPLOAD_PATH . 'products/' . $image;
            if (!move_uploaded_file($_FILES['image']['tmp_name'], $dest)) {
                $image = 'default_product.jpg'; // fallback silently
            }
        }
    }

    $stmt = $db->prepare("INSERT INTO products
        (category_id, name, brand, model, description, specs, price_per_day, stock, image, condition_rating, is_featured)
        VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");
    // i=int s=string d=double
    $stmt->bind_param('isssssdissi',
        $cat, $name, $brand, $model, $desc, $specs, $price, $stock, $image, $cond, $feat);

    if ($stmt->execute()) {
        jsonResponse(['success'=>true, 'id'=>$db->insert_id]);
    } else {
        jsonResponse(['success'=>false, 'error'=>'DB error: ' . $db->error], 500);
    }
}

function updateProduct() {
    $db = getDB();
    $id = intval($_POST['id'] ?? 0);
    if (!$id) jsonResponse(['success'=>false, 'error'=>'ID required'], 400);

    $allowed = ['name','brand','model','description','price_per_day',
                'stock','condition_rating','is_featured','is_available','category_id'];
    $fields = []; $types = ''; $vals = [];

    foreach ($allowed as $f) {
        if (array_key_exists($f, $_POST)) {
            $fields[] = "$f = ?";
            $types   .= 's';
            $vals[]   = sanitize($_POST[$f]);
        }
    }

    if (array_key_exists('specs', $_POST)) {
        $specs = $_POST['specs'];
        json_decode($specs);
        if (json_last_error() !== JSON_ERROR_NONE) $specs = '{}';
        $fields[] = "specs = ?";
        $types   .= 's';
        $vals[]   = $specs;
    }

    if (isset($_FILES['image']) && $_FILES['image']['error'] === UPLOAD_ERR_OK) {
        $ext     = strtolower(pathinfo($_FILES['image']['name'], PATHINFO_EXTENSION));
        $allowed_ext = ['jpg','jpeg','png','webp','gif'];
        if (in_array($ext, $allowed_ext)) {
            $image = uniqid('prod_') . '.' . $ext;
            if (move_uploaded_file($_FILES['image']['tmp_name'], UPLOAD_PATH . 'products/' . $image)) {
                $fields[] = "image = ?";
                $types   .= 's';
                $vals[]   = $image;
            }
        }
    }

    if (empty($fields)) jsonResponse(['success'=>false, 'error'=>'Nothing to update'], 400);

    $types .= 'i';
    $vals[] = $id;

    $stmt = $db->prepare("UPDATE products SET " . implode(', ', $fields) . " WHERE id=?");
    $stmt->bind_param($types, ...$vals);

    if ($stmt->execute()) {
        jsonResponse(['success'=>true]);
    } else {
        jsonResponse(['success'=>false, 'error'=>'Update failed: ' . $db->error], 500);
    }
}

function deleteProduct() {
    $id = intval($_POST['id'] ?? 0);
    if (!$id) jsonResponse(['success'=>false, 'error'=>'ID required'], 400);
    $db   = getDB();
    $stmt = $db->prepare("DELETE FROM products WHERE id=?");
    $stmt->bind_param('i', $id);
    if ($stmt->execute()) {
        jsonResponse(['success'=>true]);
    } else {
        jsonResponse(['success'=>false, 'error'=>$db->error], 500);
    }
}
