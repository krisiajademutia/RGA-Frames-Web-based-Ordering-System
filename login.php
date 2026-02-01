<?php
session_start();
if (isset($_SESSION['error'])) {
    echo '<div class="alert alert-danger alert-dismissible fade show" role="alert">
            ' . htmlspecialchars($_SESSION['error']) . '
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
          </div>';
    unset($_SESSION['error']);
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login - RGA Frames</title>

    <!-- Google Fonts -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Roboto:wght@400;500;700&family=Georgia:ital,wght@0,400;0,700;1,400&display=swap" rel="stylesheet">

    <!-- Bootstrap 5 -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-QWTKZyjpPEjISv5WaRU9OFeRpok6YctnYmDr5pNlyT2bRjXh0JMhjY6hW+ALEwIH" crossorigin="anonymous">

    <!-- Font Awesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">

    <style>
        :root {
            --brand-brown:    #4a2c18;
            --brand-gold:     #c19a5f;
            --brand-cream:    #fffdf7;
            --text-dark:      #1a0f09;
            --text-muted:     #4a3c32;
        }

        body {
            font-family: 'Roboto', Arial, sans-serif;
            background-color: var(--brand-cream);
            color: var(--text-dark);
            font-size: 1.125rem;           /* 18px – good readability */
            line-height: 1.65;
            padding-top: 80px;             /* Desktop / tablet */
        }

        @media (max-width: 991px) {
            body {
                padding-top: 140px;        /* Mobile – clears collapsed navbar */
            }
        }

        @media (max-width: 576px) {
            body {
                padding-top: 130px;
            }
        }

        /* Thinner navbar – same as other pages */
        .navbar {
            padding: 0.6rem 0 !important;
            min-height: 60px;
            border-bottom: 3px solid var(--brand-gold);
            background-color: white !important;
        }

        .navbar-brand {
            font-size: 1.9rem;
            font-weight: 700;
            color: var(--brand-brown);
            display: flex;
            align-items: center;
            gap: 0.6rem;
        }

        .navbar-toggler {
            padding: 0.35rem 0.65rem;
            border: none;
        }

        .nav-link, .btn {
            font-size: 1.1rem;
            padding: 0.45rem 1rem;
        }

        .btn-outline-brown {
            border: 2px solid var(--brand-brown);
            color: var(--brand-brown);
            font-weight: 600;
        }

        .btn-outline-brown:hover {
            background-color: var(--brand-brown);
            color: white;
        }

        h2 {
            font-family: 'Georgia', serif;
            color: var(--brand-brown);
            font-weight: 700;
        }

        .auth-card {
            background: white;
            border: 2px solid #e8d9c5;
            border-radius: 16px;
            padding: 2.5rem;
            max-width: 480px;
            margin: 2rem auto;
            box-shadow: 0 6px 20px rgba(0,0,0,0.08);
        }

        .form-label {
            font-size: 1.25rem;
            font-weight: 500;
            color: var(--text-dark);
            margin-bottom: 0.6rem;
        }

        .form-control,
        .form-control:focus {
            font-size: 1.2rem;
            padding: 0.9rem 1.3rem;
            border: 2px solid #d4c0a8;
            border-radius: 10px;
        }

        .form-control:focus {
            border-color: var(--brand-gold);
            box-shadow: 0 0 0 0.25rem rgba(193,154,95,0.25);
        }

        .btn-submit {
            font-size: 1.35rem;
            padding: 1rem 2rem;
            background-color: var(--brand-brown);
            color: white;
            border: none;
            border-radius: 12px;
            font-weight: 600;
            width: 100%;
            margin-top: 1.8rem;
        }

        .btn-submit:hover {
            background-color: #3a1f10;
        }
    </style>
</head>
<body>

    <!-- Thinner Navbar -->
    <nav class="navbar navbar-expand-lg fixed-top">
        <div class="container">
            <a class="navbar-brand" href="index.php">
                <i class="fas fa-box-open"></i>
                RGA Frames
            </a>

            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav">
                <span class="navbar-toggler-icon"></span>
            </button>

            <div class="collapse navbar-collapse justify-content-end" id="navbarNav">
                <ul class="navbar-nav align-items-center gap-4">
                    <li class="nav-item">
                        <a class="nav-link" href="index.php">
                            <i class="fas fa-home me-1"></i> Home
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link btn btn-outline-brown px-4 py-2" href="register.php">
                            <i class="fas fa-user-plus me-1"></i> Register
                        </a>
                    </li>
                </ul>
            </div>
        </div>
    </nav>

   <!-- Login Form -->
    <div class="container">
        <div class="auth-card">
            <h2 class="text-center mb-4">Welcome Back</h2>
            <p class="text-center text-muted mb-5" style="font-size: 1.2rem;">
                Please sign in to continue
            </p>

            <form action="login_process.php" method="POST">
                <div class="mb-4">
                    <label class="form-label">G-mail</label>
                    <input type="email" name="email" class="form-control" 
                        placeholder="example@gmail.com" required>
                </div>

                <div class="mb-4 position-relative">
                    <label class="form-label">Password</label>
                    <div class="input-group">
                        <input 
                            type="password" 
                            name="password" 
                            id="loginPassword" 
                            class="form-control pe-5" 
                            required 
                            placeholder="Enter your password"
                        >
                        <span 
                            class="input-group-text bg-white border-0 position-absolute end-0 top-50 translate-middle-y pe-3" 
                            style="z-index: 10; cursor: pointer; font-size: 1.3rem;" 
                            onclick="togglePassword('loginPassword', this)"
                        >
                            <i class="fas fa-eye"></i>
                        </span>
                    </div>
                </div>

                <button type="submit" name="login_btn" class="btn btn-submit">
                    Login
                </button>
            </form>

            <p class="text-center mt-4 mb-0">
                Don't have an account yet? 
                <a href="register.php" style="color: var(--brand-brown); font-weight: 500;">
                    Register here
                </a>
            </p>
        </div>
    </div>

    <!-- Password toggle script -->
    <script>
        function togglePassword(inputId, iconElement) {
            const input = document.getElementById(inputId);
            const icon = iconElement.querySelector('i');

            if (input.type === 'password') {
                input.type = 'text';
                icon.classList.remove('fa-eye');
                icon.classList.add('fa-eye-slash');
            } else {
                input.type = 'password';
                icon.classList.remove('fa-eye-slash');
                icon.classList.add('fa-eye');
            }
        }
    </script>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>