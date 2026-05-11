<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Crear Cuenta  La Mesa</title>

    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link href="https://fonts.googleapis.com/css2?family=Playfair+Display:wght@500;700&family=DM+Sans:wght@300;400;500;600&display=swap" rel="stylesheet">

    <style>
        :root {
            --navy: #000077;
            --navy-dark: #000055;
            --cyan: #00e5ff;
            --cyan-soft: #e9fbff;
            --text: #001b44;
            --muted: #005577;
            --border: rgba(0,188,212,.25);
        }

        * {
            box-sizing: border-box;
            margin: 0;
            padding: 0;
        }

        body {
            min-height: 100vh;
            font-family: 'DM Sans', sans-serif;
            background: linear-gradient(135deg, #0500d8 0%, #006dff 55%, #00e5ff 100%);
            display: flex;
            align-items: center;
            justify-content: center;
            color: var(--text);
            padding: 24px;
        }

        .auth-shell {
            width: min(900px, 100%);
            min-height: 560px;
            background: var(--cyan-soft);
            border-radius: 14px;
            overflow: hidden;
            box-shadow: 0 26px 60px rgba(0,0,80,.35);
        }

        .auth-header {
            height: 76px;
            background: var(--navy);
            display: flex;
            align-items: center;
            padding: 0 38px;
        }

        .auth-header h1 {
            font-family: 'Playfair Display', serif;
            color: var(--cyan);
            font-size: 1.45rem;
            letter-spacing: .3px;
        }

        .auth-header small {
            display: block;
            margin-top: 4px;
            color: rgba(0,229,255,.45);
            font-size: .58rem;
            letter-spacing: 4px;
            text-transform: uppercase;
        }

        .auth-body {
            min-height: 484px;
            display: flex;
            justify-content: center;
            align-items: center;
            padding: 44px 24px 56px;
        }

        .login-panel {
            width: 100%;
            max-width: 440px;
            text-align: center;
        }

        .logo-icon {
            font-size: 2.3rem;
            margin-bottom: 16px;
            filter: drop-shadow(0 10px 20px rgba(0,0,80,.22));
        }

        .brand h2 {
            font-family: 'Playfair Display', serif;
            font-size: 2.05rem;
            color: var(--navy-dark);
            margin-bottom: 8px;
        }

        .brand p {
            font-size: .67rem;
            letter-spacing: 2.5px;
            text-transform: uppercase;
            color: var(--muted);
            margin-bottom: 28px;
        }

        .tabs {
            height: 40px;
            background: rgba(0,85,119,.10);
            border-radius: 999px;
            display: grid;
            grid-template-columns: 1fr 1fr;
            margin-bottom: 26px;
            overflow: hidden;
        }

        .tab {
            display: flex;
            align-items: center;
            justify-content: center;
            color: var(--muted);
            font-size: .82rem;
            text-decoration: none;
            font-weight: 500;
        }

        .tab.active {
            background: var(--navy);
            color: var(--cyan);
            box-shadow: 0 10px 26px rgba(0,0,80,.28);
        }

        .alert-error,
        .alert-success {
            text-align: left;
            padding: 11px 14px;
            border-radius: 10px;
            font-size: .82rem;
            margin-bottom: 18px;
        }

        .alert-error {
            background: #ffebee;
            border: 1px solid #ef9a9a;
            color: #7f1d1d;
        }

        .form-group {
            margin-bottom: 18px;
            text-align: left;
        }

        label {
            display: block;
            color: var(--navy-dark);
            font-size: .72rem;
            font-weight: 700;
            text-transform: uppercase;
            letter-spacing: .8px;
            margin-bottom: 8px;
        }

        input,
        select {
            width: 100%;
            height: 44px;
            border-radius: 9px;
            border: 1px solid rgba(0,0,80,.18);
            padding: 0 14px;
            font-family: inherit;
            font-size: .92rem;
            background: white;
            color: var(--text);
            transition: .18s;
        }

        input:focus,
        select:focus {
            outline: none;
            border-color: var(--cyan);
            box-shadow: 0 0 0 4px rgba(0,229,255,.16);
        }

        .btn-submit {
            width: 100%;
            height: 46px;
            border: none;
            border-radius: 9px;
            margin-top: 6px;
            background: linear-gradient(135deg, var(--navy), #0066cc);
            color: var(--cyan);
            font-weight: 700;
            letter-spacing: 1px;
            cursor: pointer;
            box-shadow: 0 14px 30px rgba(0,0,80,.25);
            transition: .18s;
        }

        .btn-submit:hover {
            transform: translateY(-1px);
            background: linear-gradient(135deg, #000099, #008cff);
        }

        .register-link {
            text-align: center;
            margin-top: 22px;
            color: var(--muted);
            font-size: .84rem;
        }

        .register-link a {
            color: var(--navy);
            font-weight: 700;
            text-decoration: none;
        }

        .register-link a:hover {
            color: #008cff;
        }

        @media (max-width: 620px) {
            .auth-shell {
                min-height: auto;
            }

            .auth-header {
                padding: 0 24px;
            }

            .auth-body {
                padding: 34px 20px 42px;
            }
        }
    </style>
</head>

<body>
    <main class="auth-shell">
        <header class="auth-header">
            <div>
                <h1>La Mesa</h1>
                <small>Sistema de Gestión de Restaurante</small>
            </div>
        </header>

        <section class="auth-body">
            <div class="login-panel">
                <div class="logo-icon">🥩</div>

                <div class="brand">
                    <h2>Crear Cuenta</h2>
                    <p>Sistema de Gestión de Restaurante</p>
                </div>

                <div class="tabs">
                    <a href="{{ route('login') }}" class="tab">Iniciar Sesión</a>
                    <a href="{{ route('registro') }}" class="tab active">Crear Cuenta</a>
                </div>

                @if($errors->any())
                    <div class="alert-error">
                        @foreach($errors->all() as $e)
                            <div>{{ $e }}</div>
                        @endforeach
                    </div>
                @endif

                <form method="POST" action="{{ route('registro') }}">
                    @csrf

                    <div class="form-group">
                        <label for="nombre">Nombre de usuario</label>
                        <input
                            type="text"
                            id="nombre"
                            name="nombre"
                            value="{{ old('nombre') }}"
                            placeholder="Ej: Ana Garcia"
                            required
                        >
                    </div>

                    <div class="form-group">
                        <label for="password">Contraseña</label>
                        <input
                            type="password"
                            id="password"
                            name="password"
                            placeholder="Ej: ********"
                            required
                        >
                    </div>

                    <div class="form-group">
                        <label for="password_confirmation">Confirmar contraseña</label>
                        <input
                            type="password"
                            id="password_confirmation"
                            name="password_confirmation"
                            placeholder="Ej: ********"
                            required
                        >
                    </div>

                    <div class="form-group">
                        <label for="rol_id">Rol</label>
                        <select id="rol_id" name="rol_id" required>
                            <option value="">Seleccione un rol</option>
                            @foreach($roles as $rol)
                                <option value="{{ $rol->id_rol }}" {{ old('rol_id') == $rol->id_rol ? 'selected' : '' }}>
                                    {{ $rol->nombre }}
                                </option>
                            @endforeach
                        </select>
                    </div>

                    <button type="submit" class="btn-submit">
                        Crear cuenta
                    </button>
                </form>

                <div class="register-link">
                    Ya tienes cuenta? <a href="{{ route('login') }}">Inicia sesión</a>
                </div>
            </div>
        </section>
    </main>
</body>
</html>
