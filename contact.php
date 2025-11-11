<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>SoloTech-Contact</title>
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
                    <li><a href="about.php"><!-- Add i frame icons if possible here --> About </a></li>
                    <li><a href="products.php"><!-- Add i frame icons if possible here --> Products </a></li>
                    <li><a href="#"><!-- Add i frame icons if possible here --> Contact </a></li>
                </ul>
                <div class="hamburger">
                    <span></span>
                    <span></span>
                    <span></span>
                </div>
            </nav>
        </header>

        <div class="content products">
            <h1>Contact-Us </h1>
            <p>Contact our store via social medias, leave a comment on our page or directly text/call us for your preoccupations and worries. We're available everyday to answer to your demands and worries so feel fre to approach us!</p>
            
            <div class="feat">
                <h3>Contact us via Social media</h3> <br>
                <div class="cta-buttons">
                    <a class="contact-btn" href="https://wa.me/237672738066"><img src="https://cdn.jsdelivr.net/npm/simple-icons@v11/icons/whatsapp.svg" alt="WhatsApp" style="width:18px; vertical-align:middle; margin-right:6px;"> Whatsapp</a>
                    <a class="contact-btn" href="mailto:landrysobjio@gmail.com"><img src="https://cdn.jsdelivr.net/npm/simple-icons@v11/icons/gmail.svg" alt="Email" style="width:18px; vertical-align:middle; margin-right:6px;"> GMail</a>
                    <a class="contact-btn" href="https://www.facebook.com/landry sobjio"><img src="https://cdn.jsdelivr.net/npm/simple-icons@v11/icons/facebook.svg" alt="Email" style="width:18px; vertical-align:middle; margin-right:6px;"> Facebook</a>
                    <a class="contact-btn" href="https://freelancer.com/u/landrys2"><img src="https://cdn.jsdelivr.net/npm/simple-icons@v11/icons/freelancer.svg" alt="Freelancer" style="width:18px; vertical-align:middle; margin-right:6px;"> Freelancer</a>
                    <a class="contact-btn" href="https://www.instagram.com/landry_sobjio/"><img src="https://cdn.jsdelivr.net/npm/simple-icons@v11/icons/instagram.svg" alt="Instagram" style="width:18px; vertical-align:middle; margin-right:6px;"> Instagram</a>
                </div>
            </div><br>

            <div class="message-form">

                <h2>Send us a direct message</h2>

                <form action="message.php" method="post">
                    <div class="message-form-group">
                        <label for="email">Email</label>
                        <input type="email" name="email" id="email" placeholder="Enter your email address" required>
                    </div>
                    <div class="message-form-group">
                        <label for="object">Object</label>
                        <input type="text" name="object" id="object" placeholder="Enter the object of your message" required>
                    </div>
                    <div class="message-form-group">
                        <label for="message">Message</label>
                        <textarea name="message" id="message" placeholder="Write your Message"></textarea>
                    </div>
                    <button type="submit" class="message-btn">Send Message</button>

                </form>
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