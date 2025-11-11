<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>SoloTech-Products</title>
    <link rel="stylesheet" href="style.css">
</head>
<body> 
<?php
session_start();
require_once __DIR__ . '/db.php';
require_once __DIR__ . '/csrf.php';

// Optional category filter from query string
$category = trim((string)($_GET['category'] ?? ''));

// Fetch products (use prepared statement when filtering)
$product = [];
if ($category !== '') {
    $stmt = $conn->prepare("SELECT id, name, category, price, image, rate, description FROM product WHERE category = ? ORDER BY id DESC");
    if ($stmt) {
        $stmt->bind_param('s', $category);
        $stmt->execute();
        $res = $stmt->get_result();
        while ($r = $res->fetch_assoc()) $product[] = $r;
        $stmt->close();
    }
} else {
    $res = $conn->query("SELECT id, name, category, price, image, rate, description FROM product ORDER BY id DESC");
    if ($res) {
        while ($r = $res->fetch_assoc()) $product[] = $r;
    }
}
?>

    <div class="container">
        <header>
            <nav class="navbar">
                <div class="logo">
                    <!-- <i class="fas fa-rocket logo-icon"></i> -->
                    Solo<span>Tech</span>
                </div>
                <ul class="nav-links">
                    <li><a href="home.php"><!-- Add i frame icons if possible here --> Home </a></li>
                    <li><a href="about.php"><!-- Add i frame icons if possible here --> About </a></li>
                    <li><a href="#"><!-- Add i frame icons if possible here --> Products </a></li>
                    <!-- <li><a href="#Services-index">Add i frame icons if possible here Services </a></li> -->
                    <li><a href="contact.php"><!-- Add i frame icons if possible here --> Contact </a></li>
                </ul>
                <div class="response">
                    
                    <div class="header-right">
                        <div class="cart-icon" onclick="openCartModal()">
                            🛒
                            <span class="cart-count" id="cartCount">0</span>
                        </div>
                    </div>
                   

                    <div class="hamburger">
                        <span></span>
                        <span></span>
                        <span></span>
                    </div>
                </div>    
            </nav>
        </header>

        <div class="content products">
            <h1>Products of our Store</h1>
            <p>SoloTech offers you products based on categories all for your self development in the Technological milieu. We propose
                products 100% present fashion and efficient which will accompany you as long as needed.
            </p>

            <div class="feat">
                <h3>Our products in categories</h3> <br>
                <div class="cta-buttons">
                        <a class="category-btn" href="?"><br> All</a>
                        <a class="category-btn" href="?category=bestseller"><br> Bestseller</a>
                        <a class="category-btn" href="?category=computer"><br> Computer</a>
                        <a class="category-btn" href="?category=phone"><br> Phone</a>
                        <a class="category-btn" href="?category=accessories"><br> Accessories</a>
                    </div>
            </div>

            
            <div class="features product-grid">
                <?php foreach ($product as $p):
                    $pid = (int)$p['id'];
                    $pname = htmlspecialchars($p['name'], ENT_QUOTES, 'UTF-8');
                    $pprice = htmlspecialchars($p['price'], ENT_QUOTES, 'UTF-8');
                    $pdesc = htmlspecialchars($p['description'], ENT_QUOTES, 'UTF-8');
                    $prate = (int)($p['rate'] ?? 0);
                    $pimage = !empty($p['image']) ? ('uploads/' . $p['image']) : '';
                ?>
                <div class="product-card" data-id="<?= $pid ?>">
                    <div class="product-image loading" <?php if ($pimage): ?>style="background-image: url('<?= $pimage ?>')"<?php endif; ?>>
                        <div class="product-badge"><?= htmlspecialchars($p['category'] ?? '') ?></div>
                    </div>
                    <div class="product-info">
                        <h3 class="product-title"><?= $pname ?></h3>
                        <p class="product-price"><?= $pprice ?> FCFA</p>
                        <p class="product-description"><?= $pdesc ?></p>
                        <div class="product-actions">
                            <button class="btn-add-cart" onclick="addToCart(<?= $pid ?>, '<?= $pname ?>', <?= htmlspecialchars($p['price'] ?: 0) ?>, '<?= $pimage ?>')">🛒 Add to Cart</button>
                            <button class="btn-like-product" data-id="<?= $pid ?>">💎 <span class="rate-value"><?= $prate ?></span></button>
                        </div>
                    </div>
                </div>
                <?php endforeach; ?>
            </div>
        </div>
    </div>

    <div id="cartModal" class="modal">
        <div class="modal-content">
            <span class="close" onclick="closeCartModal()">&times;</span>
            <h2>🛒 My Shopping Card</h2>
            <div id="cartItems">
                <p style="text-align: center; color: #666; padding: 2rem;">No product in the shopping Card</p>
            </div>
            <div class="cart-total" id="cartTotal" style="display: none;">
                Total: 0 FCFA
            </div>
            <button class="card-btn" id="checkoutBtn" style="width: 100%; margin-top: 1rem; display: none;" onclick="proceedToCheckout()">
                Place Order
            </button>
        </div>
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

    <!-- Script pour la bar de navigation -->

    <script>
        // CSRF token for AJAX calls
        const CSRF_TOKEN = <?= json_encode(csrf_token()) ?>;
        document.addEventListener('DOMContentLoaded', function() {
            const hamburger = document.querySelector('.hamburger')
            const navLinks = document.querySelector('.nav-links')

            //Open menu when clicking on the hamburger menu
            hamburger.addEventListener('click', function () {
                hamburger.classList.toggle('active')
                navLinks.classList.toggle('active')
            })

            //Close menu when clicking on the hamburger menu

            document.querySelectorAll('.nav-links a').forEach(link => {
                link.addEventListener('click', () => {
                    hamburger.classList.remove('active')
                    navLinks.classList.remove('active')
                })
            })

            // close menu when clicking outside

            document.addEventListener('click',function (event) {
                const isClickInsideNav = event.target.closest('.navbar');
                if (!isClickInsideNav && navLinks.classList.contains('active')) {
                    hamburger.classList.remove('active')
                    navLinks.classList.remove('active')
                }
            })
        })
    </script>


    <!-- script pour la gestion du panier -->
    <script>
        let currentProduct = { name: '', price: '' };
        let cart = [];

        function addToCart(id, name, price, image) {
            const existingItem = cart.find(item => item.id === id);

            if (existingItem) {
                existingItem.quantity += 1;
            } else {
                cart.push({
                    id: id,
                    name: name,
                    price: price,
                    image: image,
                    quantity: 1
                });
            }
            
            updateCartUI();
            showCartNotification();
        }

        function removeFromCart(index) {
            cart.splice(index, 1);
            updateCartUI();
        }

        function updateQuantity(index, change) {
            cart[index].quantity += change;
            if (cart[index].quantity <= 0) {
                removeFromCart(index);
            } else {
                updateCartUI();
            }
        }

        function updateCartUI() {
            const cartCount = document.getElementById('cartCount');
            const cartItems = document.getElementById('cartItems');
            const cartTotal = document.getElementById('cartTotal');
            const checkoutBtn = document.getElementById('checkoutBtn');
            
            const totalItems = cart.reduce((sum, item) => sum + item.quantity, 0);
            const totalPrice = cart.reduce((sum, item) => sum + (item.price * item.quantity), 0);
            
            cartCount.textContent = totalItems;
            cartCount.style.display = totalItems > 0 ? 'flex' : 'none';
            
            if (cart.length === 0) {
                cartItems.innerHTML = '<p style="text-align: center; color: #666; padding: 2rem;">🛒 Votre panier est vide</p>';
                cartTotal.style.display = 'none';
                checkoutBtn.style.display = 'none';
            } else {
                cartItems.innerHTML = cart.map((item, index) => `
                    <div class="cart-item">
                        <div style="width: 50px; height: 50px; background: url('${item.image}') center/cover; border-radius: 10px; margin-right: 1rem;"></div>
                        <div class="cart-item-info">
                            <div class="cart-item-title">${item.name}</div>
                            <div class="cart-item-price">${item.price.toLocaleString()} FCFA</div>
                        </div>
                        <div class="cart-item-actions">
                            <div class="quantity-control">
                                <button class="quantity-btn" onclick="updateQuantity(${index}, -1)">−</button>
                                <span>${item.quantity}</span>
                                <button class="quantity-btn" onclick="updateQuantity(${index}, 1)">+</button>
                            </div>
                            <button style="background: blue; color: white; border: none; padding: 0.3rem 0.5rem; border-radius: 5px; cursor: pointer; margin-left: 0.5rem;" onclick="removeFromCart(${index})">🗑️</button>
                        </div>
                    </div>
                `).join('');
                
                cartTotal.innerHTML = `💰 Total: ${totalPrice.toLocaleString()} FCFA`;
                cartTotal.style.display = 'block';
                checkoutBtn.style.display = 'block';
            }
        }

        function showCartNotification() {
            const notification = document.createElement('div');
            notification.style.cssText = `
                position: fixed;
                top: 100px;
                right: 20px;
                background: linear-gradient(45deg, blue, #0bbaef);
                color: white;
                padding: 1rem;
                border-radius: 10px;
                z-index: 3000;
                animation: slideInRight 0.5s ease-out;
                box-shadow: 0 8px 25px rgba(0,0,0,0.2);
            `;
            notification.innerHTML = '✅ Product added to Card!';
            document.body.appendChild(notification);
            
            setTimeout(() => {
                notification.remove();
            }, 3000);
        }

        function openCartModal() {
            updateCartUI();
            document.getElementById('cartModal').style.display = 'block';
        }

        function closeCartModal() {
            document.getElementById('cartModal').style.display = 'none';
        }

        function proceedToCheckout() {
            if (cart.length === 0) return;

            let message = `🛍️ *Card Order - SoloTech*\n\n`;
            message += `📦 *Articles ordered:*\n`;

            cart.forEach((item, index) => {
                message += `${index + 1}. ${item.name}\n`;
                message += `   Quantity: ${item.quantity}\n`;
                message += `   Unit Price : ${item.price.toLocaleString()} FCFA\n`;
                message += `   Sub-total: ${(item.price * item.quantity).toLocaleString()} FCFA\n\n`;
            });

            const totalPrice = cart.reduce((sum, item) => sum + (item.price * item.quantity), 0);
            message += `💰 *TOTAL PRICE: ${totalPrice.toLocaleString()} FCFA*\n\n`;
            message += `🏪 *Store:* Douala, Cameroon\n`;
            message += `🕒 *Order made on the:* ${new Date().toLocaleString('fr-FR')}\n\n`;
            message += `✅ *Thank you to confirm your order and your delivery address*`;

            // Try to save order server-side first, then request server to send WhatsApp message.
            const saveParams = new URLSearchParams({ cart: JSON.stringify(cart), total: totalPrice, message: message, csrf: CSRF_TOKEN });

            fetch('save_order.php', {
                method: 'POST',
                headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
                body: saveParams.toString()
            }).then(r => r.json()).then(saveData => {
                if (saveData && saveData.success) {
                    // saved order; now try to send WhatsApp (server-side)
                    const wwParams = new URLSearchParams({ message: message, order_id: saveData.order_id, csrf: CSRF_TOKEN });
                    return fetch('send_whatsapp.php', {
                        method: 'POST',
                        headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
                        body: wwParams.toString()
                    }).then(r => r.json()).then(wwData => {
                        if (wwData && wwData.success) {
                            showCartNotification();
                            closeCartModal();
                            setTimeout(() => { cart = []; updateCartUI(); }, 1000);
                        } else {
                            // fallback to opening wa.me
                            const whatsappUrl = `https://wa.me/237672738066?text=${encodeURIComponent(message)}`;
                            window.open(whatsappUrl, '_blank');
                            closeCartModal();
                            setTimeout(() => { cart = []; updateCartUI(); }, 2000);
                        }
                    });
                } else {
                    // saving failed -> fallback to wa.me
                    const whatsappUrl = `https://wa.me/237672738066?text=${encodeURIComponent(message)}`;
                    window.open(whatsappUrl, '_blank');
                    closeCartModal();
                    setTimeout(() => { cart = []; updateCartUI(); }, 2000);
                }
            }).catch(err => {
                console.error('save_order error', err);
                const whatsappUrl = `https://wa.me/237672738066?text=${encodeURIComponent(message)}`;
                window.open(whatsappUrl, '_blank');
                closeCartModal();
                setTimeout(() => { cart = []; updateCartUI(); }, 2000);
            });
        }

        window.onclick = function(event) {
            const cartModal = document.getElementById('cartModal');
            if (event.target === cartModal) {
                closeCartModal();
            }
        }


        document.addEventListener('DOMContentLoaded', function() {
            const images = document.querySelectorAll('.product-image');
            images.forEach(image => {
                image.addEventListener('load', function() {
                    this.classList.remove('loading');
                });
            });

            // Like buttons: increment rate via AJAX
            document.querySelectorAll('.btn-like-product').forEach(btn => {
                btn.addEventListener('click', function (e) {
                    const id = this.dataset.id;
                    const rateSpan = this.querySelector('.rate-value');
                    if (!id) return;
                    // disable while processing
                    this.disabled = true;
                    fetch('rate.php', {
                        method: 'POST',
                        headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
                        body: new URLSearchParams({ id: id, csrf: CSRF_TOKEN })
                    }).then(r => r.json()).then(data => {
                        if (data && data.success) {
                            if (rateSpan) rateSpan.textContent = data.rate;
                        } else {
                            // optional: show temporary error
                            console.error('Rate failed', data);
                        }
                    }).catch(err => console.error(err)).finally(() => {
                        this.disabled = false;
                    });
                });
            });
        });
        const style = document.createElement('style');
        style.textContent = `
            @keyframes slideInRight {
                from { transform: translateX(100%); opacity: 0; }
                to { transform: translateX(0); opacity: 1; }
            }
        `;
        document.head.appendChild(style);
    </script>
</body>
</html>