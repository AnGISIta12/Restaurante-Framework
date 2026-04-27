<div style="max-width:700px;margin:40px auto;background:white;padding:30px;border-radius:16px;box-shadow:0 8px 24px rgba(0,0,0,.08);">
    <h1>📝 Nuevo Pedido</h1>
    <p>Desde este módulo el mesero registra una nueva comanda para un cliente.</p>

    <form>
        <label>Cliente</label><br>
        <select style="width:100%;padding:10px;margin:8px 0 16px;">
            <option>Seleccione un cliente</option>
        </select>

        <label>Plato</label><br>
        <select style="width:100%;padding:10px;margin:8px 0 16px;">
            <option>Seleccione un plato</option>
        </select>

        <label>Cantidad</label><br>
        <input type="number" value="1" min="1" style="width:100%;padding:10px;margin:8px 0 20px;">

        <button type="button" style="background:#7a4b2b;color:white;border:none;padding:12px 20px;border-radius:10px;">
            Registrar pedido
        </button>
    </form>

    <br>
    <a href="/dashboard">⬅ Volver</a>
</div>
