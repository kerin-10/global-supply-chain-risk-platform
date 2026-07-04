<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Daftar Akun – SupplyRisk Intelligence Platform</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
    <style>
        * { margin:0; padding:0; box-sizing:border-box; }
        body {
            font-family: 'Inter', sans-serif;
            min-height: 100vh;
            display: flex; align-items: center; justify-content: center;
            background: #0a0e1a; padding: 1.5rem; overflow-x: hidden;
        }
        body::before {
            content:''; position:fixed; inset:0; z-index:0;
            background:
                radial-gradient(ellipse at 20% 40%, rgba(139,92,246,0.12) 0%, transparent 50%),
                radial-gradient(ellipse at 80% 20%, rgba(59,130,246,0.10) 0%, transparent 45%),
                radial-gradient(ellipse at 60% 80%, rgba(6,182,212,0.07) 0%, transparent 40%);
        }
        .auth-container { position:relative; z-index:1; width:100%; max-width:460px; }
        .auth-card {
            background: rgba(16, 22, 40, 0.9);
            border: 1px solid rgba(139,92,246,0.25);
            border-radius: 20px; padding: 2.5rem 2rem;
            backdrop-filter: blur(20px);
            box-shadow: 0 25px 50px rgba(0,0,0,0.5);
        }
        .auth-logo { display:flex; align-items:center; gap:12px; margin-bottom:1.75rem; justify-content:center; }
        .logo-icon {
            width:52px; height:52px; border-radius:14px;
            background: linear-gradient(135deg, #8b5cf6, #3b82f6);
            display:flex; align-items:center; justify-content:center;
            font-size:1.4rem; color:white;
            box-shadow: 0 0 25px rgba(139,92,246,0.4);
        }
        .logo-text .name { font-size:1.3rem; font-weight:800; color:#f1f5f9; }
        .logo-text .sub  { font-size:0.68rem; color:#64748b; text-transform:uppercase; letter-spacing:1px; }
        h2 { font-size:1.4rem; font-weight:700; color:#f1f5f9; margin-bottom:0.3rem; }
        .auth-subtitle { color:#64748b; font-size:0.85rem; margin-bottom:1.5rem; }
        .form-label { color:#94a3b8; font-size:0.8rem; font-weight:500; margin-bottom:0.4rem; }
        .form-control {
            background:rgba(15,23,42,0.8); border:1px solid rgba(139,92,246,0.2);
            color:#f1f5f9; border-radius:10px; padding:0.65rem 1rem; font-size:0.88rem; transition:all 0.2s;
        }
        .form-control:focus {
            background:rgba(15,23,42,0.9); border-color:#8b5cf6; color:#f1f5f9;
            box-shadow:0 0 0 3px rgba(139,92,246,0.15); outline:none;
        }
        .form-control::placeholder { color:#475569; }
        .btn-register {
            width:100%;
            background: linear-gradient(135deg, #8b5cf6, #3b82f6);
            border:none; color:white; font-weight:700;
            padding:0.75rem; border-radius:10px; font-size:0.95rem;
            cursor:pointer; transition:all 0.25s;
            box-shadow:0 4px 15px rgba(139,92,246,0.35); margin-top:0.5rem;
        }
        .btn-register:hover { transform:translateY(-2px); box-shadow:0 8px 25px rgba(139,92,246,0.5); }
        .auth-link { color:#8b5cf6; text-decoration:none; font-weight:600; }
        .auth-link:hover { color:#a78bfa; }
        .alert-danger { background:rgba(239,68,68,0.1); border:1px solid rgba(239,68,68,0.3); color:#fca5a5; border-radius:10px; font-size:0.82rem; }
        .divider { text-align:center; color:#475569; font-size:0.8rem; margin:1rem 0; position:relative; }
        .divider::before,.divider::after { content:''; position:absolute; top:50%; width:42%; height:1px; background:rgba(139,92,246,0.15); }
        .divider::before { left:0; } .divider::after { right:0; }
    </style>
</head>
<body>
<div class="auth-container">
    <div class="auth-card">
        <div class="auth-logo">
            <div class="logo-icon"><i class="fas fa-globe-asia"></i></div>
            <div class="logo-text">
                <div class="name">SupplyRisk</div>
                <div class="sub">Intelligence Platform</div>
            </div>
        </div>

        <h2>Buat Akun Baru</h2>
        <p class="auth-subtitle">Daftar untuk mulai memantau risiko rantai pasok global</p>

        @if($errors->any())
            <div class="alert alert-danger mb-3">
                <i class="fas fa-exclamation-circle me-1"></i>{{ $errors->first() }}
            </div>
        @endif

        <form action="{{ route('register.post') }}" method="POST">
            @csrf
            <div class="mb-3">
                <label class="form-label"><i class="fas fa-user me-1"></i>Nama Lengkap</label>
                <input type="text" name="nama" class="form-control" placeholder="Masukkan nama lengkap Anda"
                       value="{{ old('nama') }}" required>
            </div>
            <div class="mb-3">
                <label class="form-label"><i class="fas fa-envelope me-1"></i>Email</label>
                <input type="email" name="email" class="form-control" placeholder="email@perusahaan.com"
                       value="{{ old('email') }}" required>
            </div>
            <div class="mb-3">
                <label class="form-label"><i class="fas fa-building me-1"></i>Departemen <span style="color:#475569;">(opsional)</span></label>
                <input type="text" name="departemen" class="form-control" placeholder="Contoh: Logistik & Pengadaan"
                       value="{{ old('departemen') }}">
            </div>
            <div class="mb-3">
                <label class="form-label"><i class="fas fa-lock me-1"></i>Kata Sandi</label>
                <input type="password" name="password" class="form-control" placeholder="Minimal 6 karakter" required>
            </div>
            <div class="mb-3">
                <label class="form-label"><i class="fas fa-lock me-1"></i>Konfirmasi Kata Sandi</label>
                <input type="password" name="password_confirmation" class="form-control" placeholder="Ulangi kata sandi" required>
            </div>
            <button type="submit" class="btn-register">
                <i class="fas fa-user-plus me-2"></i>Daftar Sekarang
            </button>
        </form>

        <div class="divider">atau</div>
        <p class="text-center" style="color:#64748b;font-size:0.83rem;">
            Sudah punya akun? <a href="{{ route('login') }}" class="auth-link">Masuk di sini</a>
        </p>
    </div>
</div>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
