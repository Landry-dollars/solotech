<!DOCTYPE html>
<?php
session_start();
require_once 'auth.php';
require_login();
require_once 'db.php';
require_once 'csrf.php';
// Optional: simple admin check - adjust to your auth/session scheme
// if (!isset($_SESSION['user']) || ($_SESSION['role'] ?? '') !== 'admin') {
//     header('Location: index.php');
//     exit;
// }
?>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>SoloTech-Administrator-Dashboard</title>
    <link rel="stylesheet" href="style.css">
    <link rel="stylesheet" href="admin-styles.css">
</head>
<body>
    <div class="container">
    <header>
        <nav class="navbar">
            <div class="logo">
                <!-- <i class="fas fa-rocket logo-icon"></i> -->
                Solo<span>Tech</span>
            </div>
            <ul class="nav-links ">
                <li><a href="#users">Users</a></li>
                <li><a href="#orders">Orders</a></li>
                <li><a href="#products">Products</a></li>
                <li><a href="home.php" class="login-admin"><!-- Add i frame icons if possible here --> Go to Home </a></li>
                <li><a href="index.php" class="login-admin"><!-- Add i frame icons if possible here --> Log-out</a></li>
            </ul>
            <div class="hamburger">
                <span></span> 
                <span></span>
                <span></span>
            </div>
        </nav>
    </header>
    
    <div class="content">
        <?php if (!empty($_GET['success'])): ?>
            <div class="alert alert-success">
                <?php
                $message = match($_GET['success']) {
                    'product_updated' => 'Product updated successfully',
                    'user_updated' => 'User updated successfully',
                    'deleted' => 'Item deleted successfully',
                    default => htmlspecialchars($_GET['success'])
                };
                echo $message;
                ?>
            </div>
        <?php endif; ?>

        <?php if (!empty($_GET['error'])): ?>
            <div class="alert alert-error">
                <?php
                $message = match($_GET['error']) {
                    'csrf' => 'Invalid security token',
                    'db' => 'Database error occurred',
                    'delete' => 'Error deleting item',
                    'missing_fields' => 'Please fill in all required fields',
                    'invalid_file' => 'Invalid file type uploaded',
                    'update_failed' => 'Failed to update item',
                    default => htmlspecialchars($_GET['error'])
                };
                echo $message;
                ?>
            </div>
        <?php endif; ?>
        <main>

            <section id="users" class="admin-section">

                <h2>Users </h2>

                <div class="table-responsive">
                    <table>
                        <thead>
                            <tr>
                                <th>User Id</th>
                                <th>Name</th>
                                <th>Email</th>
                                <th>Phone N0</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php
                            $sql = "SELECT * FROM user";
                            $result = $conn->query($sql);
                            if ($result === false) {
                                error_log('DB error: ' . $conn->error);
                                // show safe message
                                echo '<tr><td colspan="5">Database error. Please try again later.</td></tr>';
                            }
                            if ($result):
                                while ($row = $result->fetch_assoc()):
                                    $id = (int)$row['id'];
                                    $name = htmlspecialchars($row['name'], ENT_QUOTES, 'UTF-8');
                                    $email = htmlspecialchars($row['email'], ENT_QUOTES, 'UTF-8');
                                    $phone = htmlspecialchars($row['phone'], ENT_QUOTES, 'UTF-8');
                                    $token = htmlspecialchars(csrf_token(), ENT_QUOTES, 'UTF-8');
                            ?>
<tr>
  <td><?= $id ?></td>
  <td><?= $name ?></td>
  <td><?= $email ?></td>
  <td><?= $phone ?></td>
  <td>
    <a href="edit_form.php?id=<?= $id ?>&type=user&token=<?= $token ?>" 
       class="btn btn-edit">Edit</a>
    <form action="actions.php" method="post" style="display:inline">
      <input type="hidden" name="action" value="delete">
      <input type="hidden" name="id" value="<?= $id ?>">
      <input type="hidden" name="csrf" value="<?= $token ?>">
      <button class="btn" onclick="return confirm('Delete this User ?');">Delete</button>
    </form>
  </td>
</tr>
<?php
                                endwhile;
                            else:
                                echo '<tr><td colspan="5">No users or query error.</td></tr>';
                            endif;
                            ?>
                        </tbody>
                    </table>
                </div>
            </section>

            <section id="orders" class="admin-section">

                <h2>Recent Orders</h2>

                <div class="table-responsive">
                    <table>
                        <thead>
                            <tr>
                                <th>Order ID</th>
                                <th>User ID</th>
                                <th>Total</th>
                                <th>Created</th>
                                <th>Action</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php
                            // Fetch latest 20 orders
                            $orders_sql = "SELECT id, user_id, total_price, created_at FROM orders ORDER BY id DESC LIMIT 20";
                            $orders_result = $conn->query($orders_sql);
                            if ($orders_result && $orders_result->num_rows > 0) {
                                while ($or = $orders_result->fetch_assoc()) {
                                    $oid = (int)$or['id'];
                                    $ouser = htmlspecialchars($or['user_id'] ?? '', ENT_QUOTES, 'UTF-8');
                                    $ototal = htmlspecialchars($or['total_price'] ?? '', ENT_QUOTES, 'UTF-8');
                                    $ocreated = htmlspecialchars($or['created_at'] ?? '', ENT_QUOTES, 'UTF-8');
                                    echo '<tr>' .
                                        '<td>' . $oid . '</td>' .
                                        '<td>' . $ouser . '</td>' .
                                        '<td>' . $ototal . '</td>' .
                                        '<td>' . $ocreated . '</td>' .
                                        '<td><a href="admin/orders.php?id=' . urlencode($oid) . '">View</a></td>' .
                                        '</tr>';
                                }
                            } else {
                                echo '<tr><td colspan="5">No orders found.</td></tr>';
                            }
                            ?>
                        </tbody>
                    </table>
                </div>

                <p style="margin-top:0.5rem;"><a href="admin/orders.php?export=csv">Export all orders (CSV)</a></p>

            </section>

            <section id="messages" class="admin-section">
                <h2>Contact Messages</h2>

                <div class="table-responsive">
                    <table>
                        <thead>
                            <tr>
                                <th>ID</th>
                                <th>Email</th>
                                <th>Object</th>
                                <th>Message</th>
                                <th>Date</th>
                                <th>Status</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php
                            $msg_sql = "SELECT * FROM message ORDER BY id DESC";
                            $msg_result = $conn->query($msg_sql);
                            
                            if ($msg_result && $msg_result->num_rows > 0) {
                                while ($msg = $msg_result->fetch_assoc()) {
                                    $msg_id = (int)$msg['id'];
                                    $msg_email = htmlspecialchars($msg['email'], ENT_QUOTES, 'UTF-8');
                                    $msg_object = htmlspecialchars($msg['object'], ENT_QUOTES, 'UTF-8');
                                    $msg_content = htmlspecialchars(mb_substr($msg['message'], 0, 100) . '...', ENT_QUOTES, 'UTF-8');
                                    $msg_date = htmlspecialchars($msg['created_at'] ?? date('Y-m-d H:i:s'), ENT_QUOTES, 'UTF-8');
                                    $msg_status = htmlspecialchars($msg['status'] ?? 'unread', ENT_QUOTES, 'UTF-8');
                                    $token = htmlspecialchars(csrf_token(), ENT_QUOTES, 'UTF-8');
                                    
                                    echo '<tr>' .
                                        '<td>' . $msg_id . '</td>' .
                                        '<td>' . $msg_email . '</td>' .
                                        '<td>' . $msg_object . '</td>' .
                                        '<td>' . $msg_content . '</td>' .
                                        '<td>' . $msg_date . '</td>' .
                                        '<td><span class="status-' . $msg_status . '" id="status-' . $msg_id . '">' . ucfirst($msg_status) . '</span></td>' .
                                        '<td class="actions">' .
                                        '<button class="btn btn-view" onclick="viewMessage(' . $msg_id . ')">View</button> ' .
                                        '<button onclick="toggleMessageStatus(' . $msg_id . ', \'' . $msg_status . '\', \'' . $token . '\')" ' .
                                        'class="btn btn-primary" title="Toggle read status" id="toggle-' . $msg_id . '">' .
                                        ($msg_status === 'unread' ? '✓' : '○') . '</button> ' .
                                        '<a href="actions.php?action=delete&type=message&id=' . $msg_id . '&token=' . $token . '" ' .
                                        'class="btn btn-delete" onclick="return confirm(\'Delete this message?\');">🗑️</a>' .
                                        '</td></tr>';
                                }
                            } else {
                                echo '<tr><td colspan="7">No messages found.</td></tr>';
                            }
                            ?>
                        </tbody>
                    </table>
                </div>

                <!-- Message View Modal -->
                <div id="messageModal" class="modal" style="display: none;">
                    <div class="modal-content">
                        <span class="close">&times;</span>
                        <h3>Message Details</h3>
                        <div id="messageDetails"></div>
                    </div>
                </div>
            </section>

            <section id="products" class="admin-section">
                        
                <h2>Add Some Products</h2>

                <div class="form-container">
                    <form action="traitement.php" enctype="multipart/form-data" method="post">
                        <input type="hidden" name="csrf" value="<?php echo htmlspecialchars(csrf_token(), ENT_QUOTES, 'UTF-8'); ?>">
                        <input type="text" name="name" class="form-control" placeholder="New Produit Name" required>
                        <select name="category" id="category" placeholder="Select a category">
                            <option value="">Select a Product Category</option>
                            <option value="bestseller">Bestseller</option>
                            <option value="computer">Computer</option>
                            <option value="phone">Phone</option>
                            <option value="accessories">Accessories</option>
                        </select>
                        <textarea name="description" id="" class="form-control" placeholder="Product Description" required></textarea>
                        <input type="number" name="price" class="form-control" placeholder=" Enter Product Price" id="" required>
                        <input type="file" name="image" class="form-control" id="">
                        <button type="submit" name="submit" class="btn btn-primary"> Ajouter </button>
                    </form>
                </div>

                <h2> Products List </h2>
                <div class="table-responsive">
                    <table>
                        <thead>
                            <tr>
                                <th> ID </th>
                                <th> Name </th>
                                <th> Category </th>
                                <th> Price </th>
                                <th> Image </th>
                                <th> Rate </th>
                                <th> Action </th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php
                            $sql = "SELECT * FROM product";
                            $result = $conn->query($sql);

                            while ($row = $result->fetch_assoc()) {
                                $id = (int) $row['id'];
                                $name = htmlspecialchars($row['name'], ENT_QUOTES, 'UTF-8');
                                $category = htmlspecialchars($row['category'], ENT_QUOTES, 'UTF-8');
                                $price = htmlspecialchars($row['price'], ENT_QUOTES, 'UTF-8');
                                $rate = htmlspecialchars($row['rate'], ENT_QUOTES, 'UTF-8');
                                $token = htmlspecialchars(csrf_token(), ENT_QUOTES, 'UTF-8');

                                // Build image HTML: prefer thumbnail if available
                                $raw_image = $row['image'] ?? '';
                                $img_html = '';
                                if (!empty($raw_image)) {
                                    $thumb_path = 'uploads/thumb_' . $raw_image;
                                    $orig_path = 'uploads/' . $raw_image;
                                    if (is_file(__DIR__ . '/' . $thumb_path)) {
                                        $img_html = '<img src="' . $thumb_path . '" alt="" style="max-width:80px; max-height:80px;">';
                                    } elseif (is_file(__DIR__ . '/' . $orig_path)) {
                                        $img_html = '<img src="' . $orig_path . '" alt="" style="max-width:80px; max-height:80px;">';
                                    }
                                }

                                $edit_link = '<a href="edit_form.php?id=' . $id . '&type=product&token=' . $token . '" class="btn btn-edit">Edit</a>';
                                $delete_link = '<a href="actions.php?action=delete&id=' . $id . '&type=product&token=' . $token . '" class="btn btn-delete" onclick="return confirm(\'Delete this Product?\');">Delete</a>';

                                echo '<tr>' .
                                    '<td>' . $id . '</td>' .
                                    '<td>' . $name . '</td>' .
                                    '<td>' . $category . '</td>' .
                                    '<td>' . $price . '</td>' .
                                    '<td>' . $img_html . '</td>' .
                                    '<td>' . $rate . '</td>' .
                                    '<td>' . $edit_link . ' ' . $delete_link . '</td>' .
                                    '</tr>';
                            }
                            ?>
                        </tbody>
                    </table>
                </div>
            
            </section>
        </main>
        
    </div>
    <footer>

        <p class="email center"><img src="https://cdn.jsdelivr.net/npm/simple-icons@v11/icons/gmail.svg" alt="Email" style="width:18px; vertical-align:middle; margin-right:6px;"><a href="mailto:SoloTech@gmail.com">SoloTech@gmail.com</a></p>
        <ul>
            <li>
                <p class="right">Info line : +237 672 738 066 <br>
                    You can meet us on social medias... <br>
                    <a href="https://wa.me/237672738066"><img src="https://cdn.jsdelivr.net/npm/simple-icons@v11/icons/whatsapp.svg" alt="Whatsapp" style="width:18px; vertical-align:middle; margin-right:6px;"></a>
                    <a href="https://www.facebook.com/landry sobjio"><img src="https://cdn.jsdelivr.net/npm/simple-icons@v11/icons/facebook.svg" alt="Facebook" style="width:18px; vertical-align:middle; margin-right:6px;"></a>
                    <a href="https://www.instagram.com/landry_sobjio/"><img src="https://cdn.jsdelivr.net/npm/simple-icons@v11/icons/instagram.svg" alt="Instagram" style="width:18px; vertical-align:middle; margin-right:6px;"></a>
                    <a href="https://freelancer.com/u/landrys2"><img src="https://cdn.jsdelivr.net/npm/simple-icons@v11/icons/freelancer.svg" alt="Freelancer" style="width:18px; vertical-align:middle; margin-right:6px;"></a>
                </p>    
            </li>
            <li>
                <p class="left">Douala - Cameroon // SoloTech <br>
                    Available 24h/24, 7days/7 and all over the country <br>

                </p>

            </li>
        </ul>
        <ul class="footer-links">
            <li><a href="">Privacy Policy</a></li>
            <li><a href="">Terms of Service</a></li>
            <li><a href="">Help</a></li>
        </ul>
        <p class="center">© SoloTech, All right reserved.... Online services --2025
            <br>Done By : Landry- Solo (info sur le createur par un lien)</p>
    </footer>

    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const hamburger = document.querySelector('.hamburger');
            const navLinks = document.querySelector('.nav-links');
            
            hamburger.addEventListener('click', function() {
                hamburger.classList.toggle('active');
                navLinks.classList.toggle('active');
            });
            
            // Close menu when clicking on a link
            document.querySelectorAll('.nav-links a').forEach(link => {
                link.addEventListener('click', () => {
                    hamburger.classList.remove('active');
                    navLinks.classList.remove('active');
                });
            });
            
            // Close menu when clicking outside
            document.addEventListener('click', function(event) {
                const isClickInsideNav = event.target.closest('.navbar');
                if (!isClickInsideNav && navLinks.classList.contains('active')) {
                    hamburger.classList.remove('active');
                    navLinks.classList.remove('active');
                }
            });
        });

        // Message modal functionality
        const messageModal = document.getElementById('messageModal');
        const messageDetails = document.getElementById('messageDetails');
        const closeBtn = messageModal.querySelector('.close');

        window.viewMessage = function(id) {
            fetch('actions.php?action=view_message&id=' + id)
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        messageDetails.innerHTML = `
                            <p><strong>From:</strong> ${data.email}</p>
                            <p><strong>Subject:</strong> ${data.object}</p>
                            <p><strong>Date:</strong> ${data.created_at}</p>
                            <div class="message-content">
                                <strong>Message:</strong><br>
                                ${data.message.replace(/\n/g, '<br>')}
                            </div>
                        `;
                        messageModal.style.display = 'block';
                    }
                })
                .catch(error => console.error('Error:', error));
        };

        closeBtn.onclick = function() {
            messageModal.style.display = 'none';
        };

        window.onclick = function(event) {
            if (event.target == messageModal) {
                messageModal.style.display = 'none';
            }
        };

        // Handle message status toggle
        function toggleMessageStatus(id, currentStatus, token) {
            const newStatus = currentStatus === 'unread' ? 'read' : 'unread';
            const button = document.getElementById('toggle-' + id);
            const statusSpan = document.getElementById('status-' + id);
            
            const formData = new FormData();
            formData.append('action', 'mark_message_status');
            formData.append('id', id);
            formData.append('status', newStatus);
            formData.append('token', token);

            fetch('ajax_actions.php', {
                method: 'POST',
                body: formData
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    // Update the status display
                    statusSpan.className = 'status-' + newStatus;
                    statusSpan.textContent = newStatus.charAt(0).toUpperCase() + newStatus.slice(1);
                    
                    // Update the toggle button
                    button.innerHTML = newStatus === 'unread' ? '✓' : '○';
                    button.setAttribute('onclick', `toggleMessageStatus(${id}, '${newStatus}', '${token}')`);
                    
                    // Optional: Show a success message
                    const alert = document.createElement('div');
                    alert.className = 'alert alert-success';
                    alert.textContent = 'Status updated successfully';
                    alert.style.position = 'fixed';
                    alert.style.top = '20px';
                    alert.style.right = '20px';
                    alert.style.zIndex = '1000';
                    document.body.appendChild(alert);
                    
                    setTimeout(() => alert.remove(), 3000);
                }
            })
            .catch(error => {
                console.error('Error:', error);
                // Show error message
                const alert = document.createElement('div');
                alert.className = 'alert alert-error';
                alert.textContent = 'Failed to update status';
                alert.style.position = 'fixed';
                alert.style.top = '20px';
                alert.style.right = '20px';
                alert.style.zIndex = '1000';
                document.body.appendChild(alert);
                
                setTimeout(() => alert.remove(), 3000);
            });
        }
    </script>

    <style>
    /* Modal styles */
    .modal {
        display: none;
        position: fixed;
        z-index: 1000;
        left: 0;
        top: 0;
        width: 100%;
        height: 100%;
        background-color: rgba(0,0,0,0.4);
    }

    .modal-content {
        background-color: #fefefe;
        margin: 15% auto;
        padding: 20px;
        border: 1px solid #888;
        width: 80%;
        max-width: 600px;
        border-radius: 8px;
        position: relative;
    }

    .close {
        color: #aaa;
        float: right;
        font-size: 28px;
        font-weight: bold;
        cursor: pointer;
    }

    .close:hover,
    .close:focus {
        color: black;
        text-decoration: none;
    }

    .message-content {
        margin-top: 1rem;
        white-space: pre-wrap;
    }

    .status-unread {
        background-color: #fff3cd;
        color: #856404;
        padding: 0.25rem 0.5rem;
        border-radius: 0.25rem;
    }

    .status-read {
        background-color: #d4edda;
        color: #155724;
        padding: 0.25rem 0.5rem;
        border-radius: 0.25rem;
    }
    </style>
</body>
</html>
