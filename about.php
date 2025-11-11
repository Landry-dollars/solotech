<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>SoloTech-About</title>
    <link rel="stylesheet" href="style.css">
</head>
<body>
    <div class="container">
        <header>
            <nav class="navbar">
                <div class="logo">
                    <!-- <i class="fas fa-rocket logo-icon"></i> -->
                    Solo<span>Tech</span>
                </div>
                <ul class="nav-links">
                    <li><a href="home.php"><!-- Add i frame icons if possible here --> Home </a></li>
                    <li><a href="#"><!-- Add i frame icons if possible here --> About </a></li>
                    <li><a href="products.php"><!-- Add i frame icons if possible here --> Products </a></li>
                    <li><a href="contact.php"><!-- Add i frame icons if possible here --> Contact </a></li>
                </ul>
                <div class="hamburger">
                    <span></span> 
                    <span></span>
                    <span></span>
                </div>
            </nav>
        </header>

        <div class="content products">
            <h1>About SoloTech</h1>
            <p>SoloTech is a company made in place by Landry Sobjio (Also known in his Dev community as Solo) a young innovator who had as objectif to create a place where all fans and users of technological equipments can find what they need ad affordable prices. </p>
            
            <div class="container">
                <div class="about-grid">
                    <div class="about-card">
                        <!-- PLACEHOLDER: Here I'm going to introduce some articles-->
                        <div class="about-image" style="background-image: url('image.png')">
                            <div class="product-badge">Its Creation</div>
                        </div>
                        <div class="product-info">
                            <h3 class="product-title">How SoloTech was Created</h3>
                            <p class="product-description">SoloTech was created to provide high-quality products and services to our customers. Our goal is to meet the needs of our clients and exceed their expectations.
                                For this, Landry Sobjio (Solo), the founder, has worked hard to establish a reliable and efficient service that offers a wide range of products, from custom electronic devices to device maintenance. We are committed to delivering excellence in every aspect of our business, ensuring customer satisfaction and building long-term relationships with our clients.
                            </p>
                        </div>
                    </div>

                    <div class="about-card">
                        <div class="about-image" style="background-image: url('data:image/jpeg;base64,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')">
                            <div class="product-badge">Its Vision</div>
                        </div>
                        <div class="product-info">
                            <h3 class="product-title">How SoloTech Envisions the Future</h3>
                            <p class="product-description">At SoloTech, we envision a future where our products and services continue to evolve to meet the changing needs of our customers. We aim to be at the forefront of innovation, leveraging the latest technologies and trends to deliver exceptional value.
                                Our commitment to sustainability and ethical practices will guide our growth, ensuring that we not only serve our customers but also contribute positively to our community and the environment.
                            </p>
                        </div>
                    </div>

                    <div class="about-card">
                        <div class="about-image" style="background-image: url('data:image/jpeg;base64,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')">
                            <div class="product-badge">Its Mission</div>
                        </div>
                        <div class="product-info">
                            <h3 class="product-title">How SoloTech Defines Its Mission</h3>
                            <p class="product-description">SoloTech mission is to provide exceptional products and services that enhance the lives of our customers. We strive to create a positive impact in our community by promoting creativity, quality, and sustainability in everything we do.
                            </p>
                        </div>
                    </div>

                    <div class="about-card">
                        <div class="about-image" style="background-image: url('https://encrypted-tbn0.gstatic.com/images?q=tbn:ANd9GcQ4URQW2s9yXWXh5kMULqrVcriDIBg0T1oobw&s')">
                            <div class="product-badge">Our values</div>
                        </div>
                        <div class="product-info">
                            <h3 class="product-title">How SoloTech Defines Its Values</h3>
                            <p class="product-description">At SoloTech, we believe in integrity, quality, and customer satisfaction. Our values guide our actions and decisions, ensuring that we always put our customers first and strive for excellence in everything we do.
                            </p>
                        </div>
                    </div>
                </div>
            </div>
                
            </div>
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
    </script>

</body>
</html>