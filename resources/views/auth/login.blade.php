<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login - Nurse Call Admin</title>
    <!-- Bootstrap 5 CSS -->
    <link href="{{ asset('assets/vendor/bootstrap/bootstrap.min.css') }}" rel="stylesheet">
    <!-- Local FontAwesome -->
    <link href="{{ asset('assets/vendor/fontawesome/all.min.css') }}" rel="stylesheet">
    
    <style>
        :root {
            --primary: #4f46e5;
            --primary-hover: #4338ca;
            --accent: #06b6d4;
            --bg-light: #f8fafc;
        }

        body {
            font-family: system-ui, -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, "Helvetica Neue", Arial, sans-serif;
            background-color: var(--bg-light);
            height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            margin: 0;
            overflow: hidden;
            position: relative;
        }

        /* Decorative background spheres (soft pastel for light mode) */
        .sphere {
            position: absolute;
            border-radius: 50%;
            filter: blur(80px);
            z-index: 1;
            opacity: 0.2;
        }
        .sphere-1 {
            width: 350px;
            height: 350px;
            background: linear-gradient(135deg, var(--primary), var(--accent));
            top: -10%;
            left: -5%;
        }
        .sphere-2 {
            width: 300px;
            height: 300px;
            background: linear-gradient(135deg, #ec4899, #8b5cf6);
            bottom: -5%;
            right: -5%;
        }

        .login-container {
            position: relative;
            z-index: 10;
            width: 100%;
            max-width: 440px;
            padding: 20px;
        }

        .glass-card {
            background: rgba(255, 255, 255, 0.7);
            backdrop-filter: blur(16px);
            -webkit-backdrop-filter: blur(16px);
            border: 1px solid rgba(255, 255, 255, 0.8);
            border-radius: 24px;
            box-shadow: 0 20px 40px rgba(15, 23, 42, 0.05);
            overflow: hidden;
            transition: transform 0.3s ease, box-shadow 0.3s ease;
        }

        .glass-card:hover {
            transform: translateY(-2px);
            box-shadow: 0 24px 48px rgba(15, 23, 42, 0.08);
        }

        .brand-header {
            text-align: center;
            margin-bottom: 2rem;
        }

        .brand-icon-wrapper {
            width: 70px;
            height: 70px;
            background: linear-gradient(135deg, rgba(79, 70, 229, 0.08), rgba(6, 182, 212, 0.08));
            border: 1px solid rgba(79, 70, 229, 0.15);
            border-radius: 20px;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            margin-bottom: 1.2rem;
            box-shadow: 0 10px 20px rgba(79, 70, 229, 0.05);
        }

        .brand-icon-wrapper i {
            font-size: 32px;
            background: linear-gradient(135deg, var(--primary), var(--accent));
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
        }

        .brand-title {
            color: #0f172a;
            font-weight: 700;
            font-size: 1.75rem;
            letter-spacing: -0.5px;
            margin-bottom: 0.25rem;
        }

        .brand-subtitle {
            color: #64748b;
            font-size: 0.925rem;
        }

        .form-label {
            color: #475569;
            font-weight: 500;
            font-size: 0.9rem;
            margin-bottom: 0.5rem;
        }

        .input-group-custom {
            position: relative;
            margin-bottom: 1.5rem;
        }

        .input-group-custom i {
            position: absolute;
            left: 16px;
            top: 50%;
            transform: translateY(-50%);
            color: #64748b;
            font-size: 1.1rem;
            transition: color 0.3s ease;
            z-index: 5;
        }

        .form-control-custom {
            width: 100%;
            background-color: rgba(255, 255, 255, 0.8) !important;
            border: 1px solid rgba(0, 0, 0, 0.08);
            color: #0f172a !important;
            border-radius: 14px;
            padding: 14px 16px 14px 46px;
            font-size: 0.95rem;
            outline: none;
            transition: all 0.3s ease;
        }

        .form-control-custom:focus {
            border-color: var(--primary);
            box-shadow: 0 0 0 4px rgba(79, 70, 229, 0.12);
            background-color: #ffffff !important;
        }

        .form-control-custom:focus + i {
            color: var(--primary);
        }

        .btn-custom {
            background: linear-gradient(135deg, var(--primary), #6366f1);
            border: none;
            color: white;
            font-weight: 600;
            padding: 14px;
            border-radius: 14px;
            font-size: 1rem;
            letter-spacing: 0.5px;
            box-shadow: 0 4px 12px rgba(79, 70, 229, 0.2);
            transition: all 0.3s ease;
            cursor: pointer;
        }

        .btn-custom:hover {
            background: linear-gradient(135deg, var(--primary-hover), var(--primary));
            box-shadow: 0 6px 20px rgba(79, 70, 229, 0.3);
            transform: translateY(-1px);
        }

        .btn-custom:active {
            transform: translateY(1px);
        }

        .alert-custom {
            background-color: rgba(239, 68, 68, 0.08);
            border: 1px solid rgba(239, 68, 68, 0.2);
            border-radius: 14px;
            color: #b91c1c;
            font-size: 0.9rem;
            padding: 14px 16px;
            margin-bottom: 1.5rem;
        }

        .alert-custom ul {
            padding-left: 1.25rem;
        }

        /* Subtle shake animation on load if there are errors */
        @keyframes shake {
            0%, 100% { transform: translateX(0); }
            25% { transform: translateX(-6px); }
            75% { transform: translateX(6px); }
        }
        .has-errors {
            animation: shake 0.4s ease-in-out;
        }
    </style>
</head>
<body>

<!-- Spheres Background -->
<div class="sphere sphere-1"></div>
<div class="sphere sphere-2"></div>

<div class="login-container">
    <div class="glass-card @if($errors->any()) has-errors @endif">
        <div class="card-body p-4 p-sm-5">
            
            <!-- Brand Header -->
            <div class="brand-header">
                <div class="brand-icon-wrapper">
                    <i class="fas fa-user-nurse"></i>
                </div>
                <h3 class="brand-title">Welcome Back</h3>
                <p class="brand-subtitle">Sign in to Nurse Call Administrator</p>
            </div>
            
            <!-- Alerts -->
            @if ($errors->any())
                <div class="alert alert-custom">
                    <ul class="mb-0">
                        @foreach ($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
            @endif

            <!-- Form -->
            <form action="{{ url('/ip-call/login') }}" method="POST">
                @csrf
                
                <div class="mb-3">
                    <label for="username" class="form-label">Username</label>
                    <div class="input-group-custom">
                        <input type="text" class="form-control-custom" id="username" name="username" placeholder="Enter your username" required autofocus>
                        <i class="fas fa-user"></i>
                    </div>
                </div>
                
                <div class="mb-4">
                    <label for="password" class="form-label">Password</label>
                    <div class="input-group-custom">
                        <input type="password" class="form-control-custom" id="password" name="password" placeholder="••••••••" required>
                        <i class="fas fa-lock"></i>
                    </div>
                </div>
                
                <div class="d-grid">
                    <button type="submit" class="btn-custom">Sign In <i class="fas fa-arrow-right ms-2"></i></button>
                </div>
            </form>
        </div>
    </div>
</div>

</body>
</html>
