<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Iniciar Sesión — La Mesa</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link href="https://fonts.googleapis.com/css2?family=Playfair+Display:wght@400;700&family=DM+Sans:wght@300;400;500&display=swap" rel="stylesheet">
    <style>
        :root { --gold:#C9A84C; --dark:#1A1A18; --cream:#FAF7F2; --border:#E2DDD4; --rust:#8B3A2A; }
        * { box-sizing: border-box; margin: 0; padding: 0; }
        body { font-family: 'DM Sans', sans-serif; background: var(--dark); min-height: 100vh; display: flex; align-items: center; justify-content: center; }
        .bg-pattern {
            position: fixed; inset: 0; opacity: .05;
            background-image: repeating-linear-gradient(45deg, var(--gold) 0, var(--gold) 1px, transparent 0, transparent 50%);
            background-size: 20px 20px;
        }
        .login-card {
            background: var(--cream); border-radius: 16px; padding: 48px 40px;
            width: 100%; max-width: 400px; position: relative; z-index: 1;
            box-shadow: 0 32px 64px rgba(0,0,0,.4);
        }
        .brand { text-align: center; margin-bottom: 36px; }
        .brand h1 { font-family: 'Playfair Display', serif; font-size: 2.2rem; color: var(--dark); }
        .brand p { color: #888; font-size: .85rem; margin-top: 6px; }
        .gold-line { width: 48px; height: 3px; background: var(--gold); margin: 12px auto; border-radius: 2px; }
        .form-group { margin-bottom: 18px; }
        .form-group label { display: block; font-size: .82rem; font-weight: 500; color: var(--dark); margin-bottom: 6px; }
        .form-control {
            width: 100%; padding: 11px 14px; border: 1.5px solid var(--border); border-radius: 8px;
            font-family: inherit; font-size: .9rem; background: #fff; color: var(--dark); transition: border-color .15s;
        }
        .form-control:focus { outline: none; border-color: var(--gold); box-shadow: 0 0 0 3px rgba(201,168,76,.15); }
        .btn-submit {
            width: 100%; padding: 12px; background: var(--gold); color: var(--dark); border: none;
            border-radius: 8px; font-family: inherit; font-size: .95rem; font-weight: 600;
            cursor: pointer; transition: background .15s; margin-top: 8px;
        }
        .btn-submit:hover { background: #B8942E; }
        .alert-error { background: #FEF2F2; border: 1px solid #FCA5A5; color: #7F1D1D; padding: 10px 14px; border-radius: 7px; font-size: .82rem; margin-bottom: 18px; }
        .register-link { text-align: center; margin-top: 20px; font-size: .82rem; color: #888; }
        .register-link a { color: var(--gold); text-decoration: none; font-weight: 500; }
        .register-link a:hover { text-decoration: underline; }
    </style>
</head>
<body>
    <div class="bg-pattern"></div>
    <div class="login-card">
        <div class="brand">
            <h1>La Mesa</h1>
            <div class="gold-line"></div>
            <p>Sistema de Gestión de Restaurante</p>
        </div>

        @if($errors->any())
            <div class="alert-error">
                @foreach($errors->all() as $e){{ $e }}<br>@endforeach
            </div>
        @endif

        @if(session('success'))
            <div style="background:#EDFAF0;border:1px solid #6DC97A;color:#1A6130;padding:10px 14px;border-radius:7px;font-size:.82rem;margin-bottom:18px;">
                {{ session('success') }}
            </div>
        @endif

        <form method="POST" action="{{ route('login') }}">
            @csrf
            <div class="form-group">
                <label for="nombre">Usuario</label>
                <input type="text" id="nombre" name="nombre" class="form-control"
                       value="{{ old('nombre') }}" placeholder="Tu nombre de usuario" required autofocus>
            </div>
            <div class="form-group">
                <label for="password">Contraseña</label>
                <input type="password" id="password" name="password" class="form-control"
                       placeholder="••••••••" required>
            </div>
            <button type="submit" class="btn-submit">Ingresar</button>
        </form>

        <div class="register-link">
            ¿No tienes cuenta? <a href="{{ route('registro') }}">Regístrate</a>
        </div>
    </div>
</body>
</html>