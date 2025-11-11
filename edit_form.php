<?php
session_start();
require_once 'auth.php';
require_login();
require_once 'db.php';
require_once 'csrf.php';

$id = (int)($_GET['id'] ?? 0);
$type = $_GET['type'] ?? '';
$token = $_GET['token'] ?? '';

if (!validate_csrf($token)) {
    header('Location: dashboard.php?error=' . urlencode('Invalid security token'));
    exit;
}

// Fetch item to edit
$item = null;
if ($type === 'user') {
    $stmt = $conn->prepare("SELECT id, name, email, phone FROM user WHERE id = ? LIMIT 1");
    if ($stmt) {
        $stmt->bind_param('i', $id);
        $stmt->execute();
        $res = $stmt->get_result();
        $item = $res->fetch_assoc();
        $stmt->close();
    }
} elseif ($type === 'product') {
    $stmt = $conn->prepare("SELECT id, name, category, description, price, image FROM product WHERE id = ? LIMIT 1");
    if ($stmt) {
        $stmt->bind_param('i', $id);
        $stmt->execute();
        $res = $stmt->get_result();
        $item = $res->fetch_assoc();
        $stmt->close();
    }
}

if (!$item) {
    header('Location: dashboard.php?error=' . urlencode('Item not found'));
    exit;
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Edit <?= htmlspecialchars(ucfirst($type)) ?> - SoloTech Admin</title>
    <link rel="stylesheet" href="style.css">
</head>
<body>
    <div class="container">
        <header>
            <nav class="navbar">
                <div class="logo">Solo<span>Tech</span></div>
                <a href="dashboard.php" class="btn">Back to Dashboard</a>
            </nav>
        </header>

        <main class="content">
            <h1>Edit <?= htmlspecialchars(ucfirst($type)) ?></h1>
            
            <?php if ($type === 'user'): ?>
                <form action="actions.php" method="post" class="form-container">
                    <input type="hidden" name="csrf" value="<?= htmlspecialchars(csrf_token()) ?>">
                    <input type="hidden" name="action" value="update_user">
                    <input type="hidden" name="id" value="<?= $id ?>">
                    
                    <div class="form-group">
                        <label for="name">Name</label>
                        <input type="text" id="name" name="name" value="<?= htmlspecialchars($item['name']) ?>" required>
                    </div>
                    
                    <div class="form-group">
                        <label for="email">Email</label>
                        <input type="email" id="email" name="email" value="<?= htmlspecialchars($item['email']) ?>" required>
                    </div>
                    
                    <div class="form-group">
                        <label for="phone">Phone</label>
                        <input type="text" id="phone" name="phone" value="<?= htmlspecialchars($item['phone']) ?>">
                    </div>
                    
                    <button type="submit" class="btn btn-primary">Update User</button>
                </form>
            
            <?php elseif ($type === 'product'): ?>
                <form action="actions.php" method="post" enctype="multipart/form-data" class="form-container">
                    <input type="hidden" name="csrf" value="<?= htmlspecialchars(csrf_token()) ?>">
                    <input type="hidden" name="action" value="update_product">
                    <input type="hidden" name="id" value="<?= $id ?>">
                    
                    <div class="form-group">
                        <label for="name">Product Name</label>
                        <input type="text" id="name" name="name" value="<?= htmlspecialchars($item['name']) ?>" required>
                    </div>
                    
                    <div class="form-group">
                        <label for="category">Category</label>
                        <select name="category" id="category" required>
                            <option value="">Select a category</option>
                            <?php
                            $categories = ['bestseller', 'computer', 'phone', 'accessories'];
                            foreach ($categories as $cat) {
                                $selected = $cat === $item['category'] ? ' selected' : '';
                                echo '<option value="' . htmlspecialchars($cat) . '"' . $selected . '>' . 
                                     htmlspecialchars(ucfirst($cat)) . '</option>';
                            }
                            ?>
                        </select>
                    </div>
                    
                    <div class="form-group">
                        <label for="description">Description</label>
                        <textarea id="description" name="description" required><?= htmlspecialchars($item['description']) ?></textarea>
                    </div>
                    
                    <div class="form-group">
                        <label for="price">Price</label>
                        <input type="number" id="price" name="price" value="<?= htmlspecialchars($item['price']) ?>" required>
                    </div>
                    
                    <?php if (!empty($item['image'])): ?>
                        <div class="form-group">
                            <label>Current Image</label>
                            <img src="uploads/<?= htmlspecialchars($item['image']) ?>" alt="" style="max-width:200px">
                        </div>
                    <?php endif; ?>
                    
                    <div class="form-group">
                        <label for="image">New Image (optional)</label>
                        <input type="file" id="image" name="image">
                    </div>
                    
                    <button type="submit" class="btn btn-primary">Update Product</button>
                </form>
            <?php endif; ?>
        </main>
    </div>
</body>
</html>