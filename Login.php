<?php
session_start();
$errors = $_SESSION['register_errors'] ?? [];
$old = $_SESSION['old'] ?? [];
unset($_SESSION['register_errors'], $_SESSION['old']);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Create account</title>
    <style>
        @import url('https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700;800;900&display=swap');

        *{
            margin: 0;
            padding: 0;
            box-sizing: border-box;
            font-family: 'Poppins', sans-serif;
        }

        body{
            display: flex;
            justify-content: center;
            align-items: center;
            min-height: 100vh;
            background: linear-gradient(90deg, #e2e2e2, #c9d6ff);
        }

        .container{
            position: relative;
            width: 650px;
            height: 550px;
            background: #fff;
            border-radius: 30px;
            box-shadow: 0 0 30px rgba(0, 0, 0, .2);
            overflow: hidden;
            margin: 20px;
        }

        .form-box{
            position: absolute;
            right: 0;
            width: 50%;
            height: 100%;
            background: #fff;
            display: flex;
            align-items: center;
            color: #333;
            text-align: center;
            padding: 40px;
            z-index: 1;
            transition: .6s ease-in-out 1.2s, visibility 0s 1s;
        }

        .container.active .form-box{
            right: 50%;
        }

        .form-box.register{
            visibility: hidden;
        }

        .container.active .form-box.register{
            visibility: visible;
        }

        form{
            width: 100%;
        }

        .container h1{
            font-size: 30px;
            margin: -10px 0 ;
        }

        .input-box{
            position: relative;
            margin: 30px 0;
        }

        .input-box input{
            width: 100%;
            padding: 10px 50px 8px 20px;
            background: #eee;
            border-radius: 8px;
            border: none;
            outline: none;
            font-size: 16px;
            color: #333;
            font-weight: 500;
        }

        .input-box input::placeholder{
            color: #888;
            font-weight: 400;
        }

        .input-box i{
            position: absolute;
            right: 20px;
            top: 50%;
            transform: translateY(-50%);
            font-size: 20px;
            color: #888;
        }

        .forgot-link{
            margin: -15px 0 15px;
        }

        .forgot-link a{
            font-size: 14.5px;
            color: #333;
            text-decoration: none;
        }

        .btn{
            width: 100%;
            height: 40px;
            background: #7494ec;
            border-radius: 8px;
            box-shadow: 0 0 10px rgba(0, 0, 0, .1);
            border: none;
            cursor: pointer;
            font-size: 16px;
            color: #fff;
            font-weight: 600;
        }

        .container p{
            font-size: 14.5px;
            margin: 15px 0;
        }

        .social-icons{
            display: flex;
            justify-content: center;
        }
        .social-icons a{
            display: inline-flex;
            padding: 10px;
            border: 2px solid #ccc;
            border-radius: 8px;
            font-size: 24px;
            color: #333;
            text-decoration: none;
            margin: 0 8px;
        }

        .toggle-box{
            position: absolute;
            width: 100%;
            height: 100%;
        }

        .toggle-box::before{
            content: '';
            position: absolute;
            left: -250%;
            width: 300%;
            height: 100%;
            background: #7494ec;
            border-radius: 150px;
            z-index: 2;
            transition: 1.8s ease-in-out;
        }

        .container.active .toggle-box::before{
            left: 50%;
        }

        .toggle-panel{
            position: absolute;
            width: 50%;
            height: 100%;
            color: #fff;
            display: flex;
            flex-direction: column;
            justify-content: center;
            align-items: center;
            z-index: 2;
        }

        .toggle-panel.toggle-left{
            left: 0;
            transition-delay: 1.2s;
        }

        .container.active .toggle-panel.toggle-left{
            left: -50%;
            transition-delay: .6s;
        }

        .toggle-panel.toggle-right{
            right: -50%;
            transition-delay: .6s;
        }

        .container.active .toggle-panel.toggle-right{
            right: 0;
            transition-delay: 1.2s;
        }

        .toggle-panel p{
            margin-bottom: 20px;
        }

        .toggle-panel .btn{
            width: 160px;
            height: 30px;
            background: transparent;
            border: 2px solid #fff;
            box-shadow: none;
        }

        @media screen and (max-width: 600px) {
            .container{
                margin-top: 40px;
                width: 600px;
                height: calc(100vh + 65px);
            }

            .form-box{
                bottom: 0;
                width: 100%;
                height: 80%;
            }

            .container.active .form-box{
                right: 0;
                bottom: 20%;
            }

            .toggle-box::before{
                left: 0;
                top: -280%;
                width: 100%;
                height: 300%;
                border-radius: 20vw;
            }

            .container.active .toggle-box::before{
                left: 0;
                top: 80%;
            }
            .toggle-panel{
                width: 100%;
                height: 20%;
            }

            .toggle-panel.toggle-left{
                top: 0;
            }

            .container.active .toggle-panel.toggle-left{
                left: 0;
                top: -20%;
            }

            .toggle-panel.toggle-right{
                right: 0;
                bottom:  -20%;
            }

            .container.active .toggle-panel.toggle-right{
                bottom: 0;
            }
        }

        @media screen and (max-width: 400px) {
            .form-box{
                padding: 20px;
            }

            .toggle-panel h1{
                font-size: 25px;
            }
        }

        .close{
            position: absolute;
            top: 10px;
            right: 10px;
            width: 30px;
            height: 30px;
            background: #7494ec;
            border-radius: 50%;
            text-decoration: none;
            box-shadow: 0 5px 10px #7494ec;
            display: flex;
            justify-content: center;
            align-items: center;
        }

        .close i{
            font-size: 20px;
            color: #fff;
        }
    </style>
    <link rel="stylesheet" href="https://unpkg.com/boxicons@2.1.4/css/boxicons.min.css">
</head>
<body>
    
    <a href="index.php" class="close"><i class="fa-solid fa-xmark"></i></a>
    
    <?php if (isset($errors['global'])): ?>
        <div class="form-error"><?= htmlspecialchars($errors['global']) ?></div>
    <?php endif; ?>
    
    <div class="container">
        <div class="form-box login">
            <form action="log.php" method="post">
                <h1>Login</h1>
                <div class="input-box">
                    <input type="text" name="name" id="name" placeholder="Username" required>
                    <i class="bx bxs-user"></i>
                </div>
                <div class="input-box">
                    <input type="email" name="email" id="email" placeholder="Email" required>
                    <i class="bx bxs-enveloppe"></i>
                </div>
                <div class="input-box">
                    <input type="password" name="password" id="password" placeholder="Password" required>
                    <i class="bx bxs-lock-alt"></i>
                </div>
                <div class="forgot-link">
                    <a href="">Forgot password?</a>
                </div>
                <button type="submit" class="btn">Login</button>
                <p>or login with social platform</p>
                <div class="social-icons">
                    <a href=""><i class="bx bxl-google"></i></a>
                    <a href=""><i class="bx bxl-facebook"></i></a>
                    <a href=""><i class="bx bxl-github"></i></a>
                    <a href=""><i class="bx bxl-linkedin"></i></a>
                </div>
            </form>
        </div>
    
        <div class="form-box register">
            <form action="reg.php" method="post">
                <h1>Registration</h1>
                <div class="input-box">
                    <input type="text" name="name" id="name" placeholder="Username" required>
                    <i class="bx bxs-user"></i>
                </div>
                <div class="input-box">
                    <input type="email" name="email" id="email" placeholder="Email" required>
                    <i class="bx bxs-enveloppe"></i>
                </div>
                <div class="input-box">
                    <input type="tel" name="phone" id="phone" placeholder="Phone" required>
                    <i class="bx bxs-telephone"></i>
                </div>
                <div class="input-box">
                    <input type="password" name="password" id="password" placeholder="Password" required>
                    <i class="bx bxs-lock-alt"></i>
                </div>
                <button type="submit" class="btn">Register</button>
                <!-- <p>or login with social platform</p>
                <div class="social-icons">
                    <a href=""><i class="bx bxl-google"></i></a>
                    <a href=""><i class="bx bxl-facebook"></i></a>
                    <a href=""><i class="bx bxl-github"></i></a>
                    <a href=""><i class="bx bxl-linkedin"></i></a>
                </div> -->
            </form>
        </div>

        <div class="toggle-box">
            <div class="toggle-panel toggle-left">
                <h1>Hello, Welcome!</h1>
                <p>Don't have an account?</p>
                <button class="btn register-btn">Register</button>
            </div>
            <div class="toggle-panel toggle-right">
                <h1>Welcome Back!</h1>
                <p>Already have an account?</p>
                <button class="btn login-btn">Login</button>
            </div>
        </div>
    </div>

    <script>
        const container = document.querySelector('.container')
        const registerBtn = document.querySelector('.register-btn')
        const loginBtn = document.querySelector('.login-btn')

        registerBtn.addEventListener('click',() => {
            container.classList.add('active');
        });
        loginBtn.addEventListener('click',() => {
            container.classList.remove('active');
        });
    </script>
</body>
</html>