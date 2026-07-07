const App = {
    user: null,
    productosCache: [],
    stockCache: {},
    formasPago: [],
    memoriaActiva: 1,
    pagosCombinados: [],
    turnoActivoData: null,
    bodegaSeleccionada: null,
    cajaSeleccionada: null,
    clienteSeleccionado: null,

    init() {
        this.setupEventListeners();
        this.iniciarReloj();
        Auth.init();
    },

    showPOS() {
        Auth.hideLogin();
        this.cargarConfiguracionInicial();
        this.cargarProductos();
        this.cargarCarrito();
        this.cargarMemoriasUI();
        this.checkTurnoActivo();
    },

    // --- Configuración Inicial ---
    async cargarConfiguracionInicial() {
        try {
            const empresaRes = await API.getEmpresa();
            if (empresaRes.data?.razon_social) {
                document.getElementById('header-empresa').textContent = empresaRes.data.razon_social;
                if (empresaRes.data.logo_pos_url) {
                    document.getElementById('header-logo').src = empresaRes.data.logo_pos_url;
                } else if (empresaRes.data.logo_url) {
                    document.getElementById('header-logo').src = empresaRes.data.logo_url;
                }
            }
        } catch { }
        try {
            const fpRes = await API.getFormasPago();
            this.formasPago = fpRes.data || [];
        } catch { }
    },

    // --- Productos ---
    async cargarProductos(q = '') {
        try {
            const res = await API.buscarProductos(q, 200);
            this.productosCache = res.data || [];
            this.renderizarProductos(this.productosCache);
            if (!q) {
                this.cargarStockMasivo();
            }
        } catch (err) {
            UI.mostrarError(err.message);
        }
    },

    async cargarStockMasivo() {
        try {
            const res = await API.getStock(0, 0);
            const stocks = res.data || [];
            this.stockCache = {};
            stocks.forEach(s => {
                if (!this.stockCache[s.producto_id]) {
                    this.stockCache[s.producto_id] = 0;
                }
                this.stockCache[s.producto_id] += parseFloat(s.cantidad || 0);
            });
            this.renderizarProductos(this.productosCache);
        } catch { }
    },

    renderizarProductos(productos) {
        const container = document.getElementById('lista-productos');
        if (!productos.length) {
            container.innerHTML = '<div class="text-center text-muted p-4"><i class="fas fa-box-open fa-2x mb-2"></i><br>No hay productos</div>';
            return;
        }
        container.innerHTML = '';
        productos.forEach(p => {
            const stock = this.stockCache[p.id] || 0;
            const stockClass = stock <= (p.stock_minimo || 0) ? 'stock-low' : 'stock-ok';
            const card = document.createElement('div');
            card.className = 'product-card';
            card.innerHTML = `
                <span class="product-stock ${stockClass}">${UI.formatoNumero(stock)}</span>
                <div class="product-code">${UI.escapeHtml(p.codigo)}</div>
                <div class="product-name">${UI.escapeHtml(p.nombre || p.nombre_comun || '')}</div>
                <div class="product-price">${UI.formatoNumero(p.precio_venta)}</div>
            `;
            card.addEventListener('click', () => this.agregarProducto(p));
            container.appendChild(card);
        });
    },

    async agregarProducto(producto) {
        try {
            const carrito = API.agregarAlCarrito(producto, 1);
            this.cargarCarrito();
            UI.mostrarToast(`${UI.escapeHtml(producto.nombre)} agregado`, 'success');
        } catch (err) {
            UI.mostrarError(err.message);
        }
    },

    // --- Búsqueda ---
    debounceTimer: null,
    buscarProductosInput() {
        clearTimeout(this.debounceTimer);
        this.debounceTimer = setTimeout(() => {
            const q = document.getElementById('buscar-producto').value.trim();
            const filtrados = q
                ? this.productosCache.filter(p =>
                    (p.nombre && p.nombre.toLowerCase().includes(q.toLowerCase())) ||
                    (p.nombre_comun && p.nombre_comun.toLowerCase().includes(q.toLowerCase())) ||
                    (p.codigo && p.codigo.toLowerCase().includes(q.toLowerCase())) ||
                    (p.codigo_barras && p.codigo_barras.toLowerCase().includes(q.toLowerCase()))
                )
                : this.productosCache;
            this.renderizarProductos(filtrados);
            if (filtrados.length === 1 && q.length >= 3) {
                this.agregarProducto(filtrados[0]);
                document.getElementById('buscar-producto').value = '';
                this.renderizarProductos(this.productosCache);
            }
        }, 300);
    },

    // --- Carrito ---
    cargarCarrito() {
        const carrito = API.getLocalCarrito();
        this.clienteSeleccionado = carrito.cliente;
        this.actualizarUIcliente();
        this.renderizarCarrito(carrito);
    },

    renderizarCarrito(carrito) {
        const container = document.getElementById('carrito-items');
        const badge = document.getElementById('cart-count');
        const items = carrito.items || [];

        badge.textContent = items.length;

        if (!items.length) {
            container.innerHTML = '<div class="text-center text-muted p-4"><i class="fas fa-shopping-basket fa-2x mb-2"></i><br>Carrito vacío</div>';
            this.renderizarTotales(carrito);
            return;
        }

        container.innerHTML = '';
        items.forEach(item => {
            const ivaLabel = `IVA ${item.impuesto_porcentaje}%`;
            const div = document.createElement('div');
            div.className = 'cart-item';
            div.innerHTML = `
                <div class="d-flex justify-content-between align-items-start">
                    <div class="item-name">${UI.escapeHtml(item.nombre)}</div>
                    <button class="btn btn-sm btn-outline-danger border-0 py-0" onclick="App.eliminarItem(${item.producto_id})" title="Eliminar">
                        <i class="fas fa-times"></i>
                    </button>
                </div>
                <div class="item-meta">${UI.escapeHtml(item.codigo)} | ${ivaLabel}</div>
                <div class="d-flex justify-content-between align-items-center mt-1">
                    <div class="qty-controls">
                        <button class="btn btn-outline-secondary btn-sm" onclick="App.cambiarCantidad(${item.producto_id}, ${parseFloat((item.cantidad - 1).toFixed(4))})">−</button>
                        <input type="number" step="0.01" min="0.01" value="${item.cantidad}"
                               onchange="App.cambiarCantidad(${item.producto_id}, parseFloat(this.value) || 0.01)"
                               onfocus="this.select()">
                        <button class="btn btn-outline-secondary btn-sm" onclick="App.cambiarCantidad(${item.producto_id}, ${parseFloat((item.cantidad + 1).toFixed(4))})">+</button>
                    </div>
                    <div class="item-subtotal">${UI.formatoNumero(item.subtotal || 0)}</div>
                </div>
            `;
            container.appendChild(div);
        });
        this.renderizarTotales(carrito);
    },

    renderizarTotales(carrito) {
        const items = carrito.items || [];
        document.getElementById('tbl-subtotal').textContent = UI.formatoNumero(carrito.subtotal || 0);
        document.getElementById('tbl-descuento').textContent = UI.formatoNumero(carrito.descuento || 0);
        document.getElementById('tbl-impuestos').textContent = UI.formatoNumero(carrito.impuestos || 0);
        document.getElementById('tbl-total').textContent = UI.formatoNumero(carrito.total || 0);
        document.getElementById('descuento-global-input').value = carrito.descuentoGlobal || 0;
    },

    cambiarCantidad(productoId, cantidad) {
        if (cantidad <= 0) {
            return this.eliminarItem(productoId);
        }
        const carrito = API.actualizarCantidad(productoId, cantidad);
        this.renderizarCarrito(carrito);
    },

    eliminarItem(productoId) {
        const carrito = API.eliminarDelCarrito(productoId);
        this.renderizarCarrito(carrito);
    },

    vaciarCarrito() {
        if (!confirm('¿Cancelar la venta actual?')) return;
        const carrito = API.vaciarCarrito();
        this.clienteSeleccionado = null;
        this.actualizarUIcliente();
        this.renderizarCarrito(carrito);
    },

    aplicarDescuentoGlobal() {
        const input = document.getElementById('descuento-global-input');
        const val = parseFloat(input.value) || 0;
        const carrito = API.setDescuentoGlobal(val);
        this.renderizarTotales(carrito);
        UI.mostrarToast('Descuento aplicado', 'success');
    },

    // --- Cliente ---
    async buscarClientes() {
        const q = document.getElementById('buscar-cliente').value.trim();
        try {
            const res = await API.buscarClientes(q);
            const list = document.getElementById('lista-clientes');
            const clientes = res.data || [];
            if (!clientes.length) {
                list.innerHTML = '<div class="text-muted p-2 text-center">Sin resultados</div>';
                return;
            }
            list.innerHTML = '';
            clientes.forEach(c => {
                const item = document.createElement('div');
                item.className = 'list-group-item list-group-item-action';
                item.innerHTML = `
                    <strong>${UI.escapeHtml(c.nombre)}</strong>
                    <small class="d-block text-muted">${c.tipo_documento}: ${c.documento} | Tel: ${c.telefono || '-'}</small>
                `;
                item.addEventListener('click', () => this.seleccionarCliente(c));
                list.appendChild(item);
            });
        } catch (err) {
            UI.mostrarError(err.message);
        }
    },

    seleccionarCliente(cliente) {
        this.clienteSeleccionado = cliente;
        API.setCliente(cliente);
        this.actualizarUIcliente();
        bootstrap.Modal.getInstance(document.getElementById('modal-cliente')).hide();
        UI.mostrarToast(`Cliente: ${cliente.nombre}`, 'success');
        this.cargarCarrito();
    },

    actualizarUIcliente() {
        const btn = document.getElementById('btn-cliente');
        if (this.clienteSeleccionado) {
            btn.textContent = `${this.clienteSeleccionado.nombre.slice(0, 20)}`;
            btn.classList.remove('btn-outline-secondary');
            btn.classList.add('btn-outline-success');
        } else {
            btn.textContent = 'Consumidor Final';
            btn.classList.remove('btn-outline-success');
            btn.classList.add('btn-outline-secondary');
        }
    },

    async crearClienteRapido() {
        const data = {
            nombre: document.getElementById('nuevo-cliente-nombre').value.trim(),
            tipo_documento: document.getElementById('nuevo-cliente-tipo-doc').value,
            documento: document.getElementById('nuevo-cliente-documento').value.trim(),
            telefono: document.getElementById('nuevo-cliente-telefono').value.trim() || '0000000',
            email: document.getElementById('nuevo-cliente-email').value.trim() || 'sin-correo@local.test',
        };
        if (!data.nombre || !data.documento) {
            UI.mostrarError('Nombre y documento son requeridos');
            return;
        }
        try {
            const res = await API.crearCliente(data);
            const cliente = res.data;
            bootstrap.Modal.getInstance(document.getElementById('modal-nuevo-cliente')).hide();
            this.seleccionarCliente(cliente);
            document.getElementById('form-nuevo-cliente').reset();
        } catch (err) {
            UI.mostrarError(err.message);
        }
    },

    // --- Memorias ---
    cargarMemoriasUI() {
        for (let i = 1; i <= 4; i++) {
            const btn = document.getElementById(`memoria-${i}`);
            const memorias = API.getTodasLasMemorias();
            const tieneDatos = memorias[i] && memorias[i].items && memorias[i].items.length > 0;
            btn.classList.toggle('has-data', tieneDatos);
        }
        const activa = parseInt(localStorage.getItem('pos_memoria_activa') || '1', 10);
        this.activarMemoriaUI(activa);
    },

    cambiarMemoria(indice) {
        const carrito = API.cambiarMemoria(indice);
        this.memoriaActiva = indice;
        this.clienteSeleccionado = carrito.cliente;
        this.actualizarUIcliente();
        this.renderizarCarrito(carrito);
        this.cargarMemoriasUI();
        this.activarMemoriaUI(indice);
    },

    activarMemoriaUI(indice) {
        for (let i = 1; i <= 4; i++) {
            document.getElementById(`memoria-${i}`).classList.toggle('active', i === indice);
        }
    },

    guardarMemoriaActual() {
        const activa = parseInt(localStorage.getItem('pos_memoria_activa') || '1', 10);
        API.guardarMemoria(activa);
        this.cargarMemoriasUI();
        UI.mostrarToast(`Memoria P${activa} guardada`, 'success');
    },

    // --- Tipo Documento ---
    cambiarTipoDocumento(tipo) {
        API.setTipoDocumento(tipo);
        document.getElementById('btn-documento').textContent =
            tipo === 'factura' ? 'Factura' :
            tipo === 'remision' ? 'Remisión' : 'Cotización';
    },

    // --- Turno ---
    async checkTurnoActivo() {
        try {
            const res = await API.turnoActivo();
            const turnoData = res.data;
            this.turnoActivoData = turnoData;
            const indicator = document.getElementById('turno-indicator');
            const closeBtn = document.getElementById('btn-cerrar-turno');
            if (turnoData && turnoData.id) {
                indicator.innerHTML = `<i class="fas fa-cash-register me-1"></i> Turno #${turnoData.id} <span class="badge bg-success ms-1">Abierto</span>`;
                closeBtn.classList.remove('d-none');
                this.bodegaSeleccionada = turnoData.bodega_id || null;
                this.cajaSeleccionada = turnoData.caja_id || null;
            } else {
                indicator.innerHTML = `<i class="fas fa-cash-register me-1"></i> Sin turno`;
                closeBtn.classList.add('d-none');
                this.mostrarModalApertura();
            }
        } catch (err) {
            this.mostrarModalApertura();
        }
    },

    async mostrarModalApertura() {
        const modal = new bootstrap.Modal(document.getElementById('modal-apertura'), { backdrop: 'static', keyboard: false });
        try {
            const cajasRes = await API.getCajas();
            const bodegasRes = await API.getBodegas();
            const cajasSelect = document.getElementById('apertura-caja');
            const bodegasSelect = document.getElementById('apertura-bodega');

            cajasSelect.innerHTML = '<option value="">Seleccione caja</option>';
            (cajasRes.data || []).forEach(c => {
                cajasSelect.innerHTML += `<option value="${c.id}">${c.nombre}</option>`;
            });
            bodegasSelect.innerHTML = '<option value="">Seleccione bodega</option>';
            (bodegasRes.data || []).forEach(b => {
                bodegasSelect.innerHTML += `<option value="${b.id}">${b.nombre}</option>`;
            });
            modal.show();
        } catch (err) {
            UI.mostrarError('Error al cargar datos: ' + err.message);
        }
    },

    async abrirTurno() {
        const cajaId = parseInt(document.getElementById('apertura-caja').value, 10);
        const bodegaId = parseInt(document.getElementById('apertura-bodega').value, 10);
        const saldoInicial = parseFloat(document.getElementById('apertura-saldo').value) || 0;

        if (!cajaId) { UI.mostrarError('Seleccione una caja'); return; }

        try {
            const res = await API.aperturaTurno({
                caja_id: cajaId,
                bodega_id: bodegaId || null,
                saldo_inicial: saldoInicial,
            });
            bootstrap.Modal.getInstance(document.getElementById('modal-apertura')).hide();
            UI.mostrarToast('Turno iniciado correctamente', 'success');
            await this.checkTurnoActivo();
        } catch (err) {
            UI.mostrarError(err.message);
        }
    },

    async mostrarModalCierre() {
        if (!this.turnoActivoData) { UI.mostrarError('No hay turno activo'); return; }
        try {
            const res = await API.turnoResumen(this.turnoActivoData.id);
            const data = res.data;
            const r = data.resumen || {};

            let html = '<div class="cerrar-turno-summary">';
            html += `<div class="line"><span>Saldo Inicial</span><span>${UI.formatoNumero(r.saldo_inicial || 0)}</span></div>`;
            html += `<div class="line"><span>Ingresos del Turno</span><span>${UI.formatoNumero(r.ingresos_acumulados || 0)}</span></div>`;
            if (r.desglose_pagos) {
                Object.entries(r.desglose_pagos).forEach(([metodo, monto]) => {
                    html += `<div class="desglose-pago"><span>${metodo}</span><span>${UI.formatoNumero(monto)}</span></div>`;
                });
            }
            html += `<div class="line border-top border-secondary pt-2 mt-2"><strong>Saldo Esperado</strong><strong>${UI.formatoNumero(r.saldo_esperado_actual || 0)}</strong></div>`;
            html += '</div>';

            document.getElementById('cierre-resumen').innerHTML = html;
            document.getElementById('cierre-ventas-count').textContent = (r.ventas_count || 0) + ' ventas';
            document.getElementById('cierre-saldo-inicial').value = r.saldo_esperado_actual || 0;

            new bootstrap.Modal(document.getElementById('modal-cierre')).show();
        } catch (err) {
            UI.mostrarError(err.message);
        }
    },

    async cerrarTurno() {
        const saldoReal = parseFloat(document.getElementById('cierre-saldo-inicial').value) || 0;
        try {
            const res = await API.cerrarTurno({ saldo_final_real: saldoReal });
            bootstrap.Modal.getInstance(document.getElementById('modal-cierre')).hide();
            const diff = res.data?.diferencia || 0;
            const msg = diff === 0
                ? '✅ Turno cerrado sin diferencias'
                : `⚠️ Turno cerrado con diferencia de ${UI.formatoNumero(diff)}`;
            UI.mostrarToast(msg, diff === 0 ? 'success' : 'warning');
            this.turnoActivoData = null;
            document.getElementById('turno-indicator').innerHTML = '<i class="fas fa-cash-register me-1"></i> Sin turno';
            document.getElementById('btn-cerrar-turno').classList.add('d-none');
            setTimeout(() => Auth.logout(), 2000);
        } catch (err) {
            UI.mostrarError(err.message);
        }
    },

    // --- Pago ---
    mostrarModalPago() {
        const carrito = API.getLocalCarrito();
        if (!carrito.items.length) { UI.mostrarError('Carrito vacío'); return; }
        if (!this.turnoActivoData) { UI.mostrarError('Debe abrir un turno primero'); return; }
        if (!this.clienteSeleccionado) { UI.mostrarError('Debe seleccionar un cliente'); return; }

        this.pagosCombinados = [];
        document.getElementById('pago-total').textContent = UI.formatoNumero(carrito.total);
        document.getElementById('pago-total-label').textContent = UI.formatoNumero(carrito.total);

        const fpSelect = document.getElementById('pago-forma');
        fpSelect.innerHTML = '';
        this.formasPago.forEach(fp => {
            fpSelect.innerHTML += `<option value="${fp.id}">${fp.nombre}</option>`;
        });

        this.actualizarUIpagos();
        document.getElementById('pago-efectivo-section').classList.add('d-none');
        document.getElementById('pago-confirmar-btn').disabled = true;

        new bootstrap.Modal(document.getElementById('modal-pago')).show();
    },

    onCambioFormaPago() {
        const fpSelect = document.getElementById('pago-forma');
        const selectedId = parseInt(fpSelect.value, 10);
        const fp = this.formasPago.find(f => f.id === selectedId);
        const cashSection = document.getElementById('pago-efectivo-section');
        if (fp && fp.nombre.toLowerCase() === 'efectivo') {
            cashSection.classList.remove('d-none');
        } else {
            cashSection.classList.add('d-none');
        }
    },

    onEfectivoRecibidoChange() {
        const recibido = parseFloat(document.getElementById('pago-efectivo-recibido').value) || 0;
        const carrito = API.getLocalCarrito();
        const total = carrito.total || 0;
        const cambio = Math.max(0, recibido - total);
        document.getElementById('cambio-display').textContent = UI.formatoNumero(cambio);
    },

    agregarPago() {
        const fpSelect = document.getElementById('pago-forma');
        const fpId = parseInt(fpSelect.value, 10);
        const fp = this.formasPago.find(f => f.id === fpId);
        if (!fp) { UI.mostrarError('Seleccione forma de pago'); return; }

        let monto = 0;
        const carrito = API.getLocalCarrito();
        const total = carrito.total || 0;
        const pagado = this.pagosCombinados.reduce((s, p) => s + p.monto, 0);
        const faltante = total - pagado;

        if (fp.nombre.toLowerCase() === 'efectivo') {
            monto = faltante;
        } else {
            monto = parseFloat(document.getElementById('pago-monto').value) || 0;
            if (monto <= 0 || monto > faltante) {
                UI.mostrarError(`Monto inválido. Faltante: ${UI.formatoNumero(faltante)}`);
                return;
            }
        }

        this.pagosCombinados.push({ forma_pago_id: fpId, nombre: fp.nombre, monto: parseFloat(monto.toFixed(2)) });
        this.actualizarUIpagos();
        document.getElementById('pago-monto').value = '';
    },

    eliminarPago(index) {
        this.pagosCombinados.splice(index, 1);
        this.actualizarUIpagos();
    },

    actualizarUIpagos() {
        const carrito = API.getLocalCarrito();
        const total = carrito.total || 0;
        const pagado = this.pagosCombinados.reduce((s, p) => s + p.monto, 0);
        const faltante = Math.max(0, total - pagado);

        const list = document.getElementById('pago-lista');
        if (this.pagosCombinados.length === 0) {
            list.innerHTML = '<div class="text-muted text-center p-2 small">Sin pagos agregados</div>';
        } else {
            list.innerHTML = '';
            this.pagosCombinados.forEach((p, i) => {
                const div = document.createElement('div');
                div.className = 'payment-item';
                div.innerHTML = `
                    <span>${p.nombre}</span>
                    <span><strong>${UI.formatoNumero(p.monto)}</strong>
                        <button class="btn btn-sm text-danger border-0 py-0" onclick="App.eliminarPago(${i})"><i class="fas fa-times"></i></button>
                    </span>
                `;
                list.appendChild(div);
            });
        }

        document.getElementById('pago-total-pagado').textContent = UI.formatoNumero(pagado);
        document.getElementById('pago-faltante').textContent = UI.formatoNumero(faltante);
        document.getElementById('pago-faltante').className = faltante <= 0 ? 'text-success fw-bold' : 'text-danger fw-bold';
        document.getElementById('pago-confirmar-btn').disabled = faltante > 0.01;
    },

    async confirmarPago() {
        const carrito = API.getLocalCarrito();
        const items = carrito.items.map(i => ({
            producto_id: i.producto_id,
            cantidad: i.cantidad,
            descuento_unitario: i.descuento_unitario || 0,
        }));

        const payload = {
            cliente_id: this.clienteSeleccionado.id,
            bodega_id: this.bodegaSeleccionada || undefined,
            descuento_global: carrito.descuentoGlobal || 0,
            pagos: this.pagosCombinados.map(p => ({
                forma_pago_id: p.forma_pago_id,
                monto: p.monto,
            })),
            items,
        };

        try {
            let res;
            if (carrito.tipoDocumento === 'remision') {
                res = await API.crearRemision(payload);
            } else {
                res = await API.crearVenta(payload);
            }

            bootstrap.Modal.getInstance(document.getElementById('modal-pago')).hide();
            UI.mostrarToast(`${carrito.tipoDocumento === 'remision' ? 'Remisión' : 'Venta'} #${res.numero} creada`, 'success');

            const carritoVacio = API.vaciarCarrito();
            this.clienteSeleccionado = null;
            this.actualizarUIcliente();
            this.renderizarCarrito(carritoVacio);
            this.cargarProductos();
            await this.checkTurnoActivo();
        } catch (err) {
            UI.mostrarError(err.message);
        }
    },

    // --- Reloj ---
    iniciarReloj() {
        const actualizar = () => {
            const ahora = new Date();
            document.getElementById('footer-clock').textContent =
                ahora.toLocaleDateString('es-CO', { weekday: 'long', year: 'numeric', month: 'long', day: 'numeric' }) +
                ' | ' + ahora.toLocaleTimeString('es-CO');
        };
        actualizar();
        setInterval(actualizar, 1000);
    },

    // --- Event Listeners ---
    setupEventListeners() {
        document.getElementById('login-form').addEventListener('submit', async (e) => {
            e.preventDefault();
            const email = document.getElementById('login-email').value.trim();
            const password = document.getElementById('login-password').value;
            if (!email || !password) { UI.mostrarError('Ingrese credenciales'); return; }
            try {
                await Auth.login(email, password);
                await App.showPOS();
                document.getElementById('login-error').classList.add('d-none');
            } catch (err) {
                document.getElementById('login-error').textContent = err.message;
                document.getElementById('login-error').classList.remove('d-none');
            }
        });

        document.getElementById('btn-logout').addEventListener('click', () => Auth.logout());
        document.getElementById('buscar-producto').addEventListener('input', () => this.buscarProductosInput());
        document.getElementById('buscar-producto').addEventListener('keydown', (e) => {
            if (e.key === 'Enter') this.buscarProductosInput();
        });
        document.getElementById('btn-refresh').addEventListener('click', () => this.cargarProductos());
        document.getElementById('btn-vaciar').addEventListener('click', () => this.vaciarCarrito());
        document.getElementById('btn-pagar').addEventListener('click', () => this.mostrarModalPago());
        document.getElementById('btn-aplicar-descuento').addEventListener('click', () => this.aplicarDescuentoGlobal());
        document.getElementById('descuento-global-input').addEventListener('keydown', (e) => {
            if (e.key === 'Enter') this.aplicarDescuentoGlobal();
        });

        // Cliente modal
        document.getElementById('btn-cliente').addEventListener('click', () => {
            new bootstrap.Modal(document.getElementById('modal-cliente')).show();
            document.getElementById('buscar-cliente').value = '';
            document.getElementById('lista-clientes').innerHTML = '';
            setTimeout(() => document.getElementById('buscar-cliente').focus(), 300);
        });
        document.getElementById('buscar-cliente').addEventListener('input', () => {
            clearTimeout(this.debounceTimer);
            this.debounceTimer = setTimeout(() => this.buscarClientes(), 300);
        });
        document.getElementById('btn-nuevo-cliente').addEventListener('click', () => {
            bootstrap.Modal.getInstance(document.getElementById('modal-cliente')).hide();
            new bootstrap.Modal(document.getElementById('modal-nuevo-cliente')).show();
        });
        document.getElementById('btn-guardar-cliente').addEventListener('click', () => this.crearClienteRapido());

        // Memorias
        for (let i = 1; i <= 4; i++) {
            document.getElementById(`memoria-${i}`).addEventListener('click', () => this.cambiarMemoria(i));
        }
        document.getElementById('btn-guardar-memoria').addEventListener('click', () => this.guardarMemoriaActual());

        // Tipo documento
        document.getElementById('btn-documento').addEventListener('click', () => {
            const tipos = ['factura', 'remision'];
            const actual = API.getLocalCarrito().tipoDocumento || 'factura';
            const idx = tipos.indexOf(actual);
            const nuevo = tipos[(idx + 1) % tipos.length];
            this.cambiarTipoDocumento(nuevo);
        });

        // Turno
        document.getElementById('btn-abrir-turno').addEventListener('click', () => this.mostrarModalApertura());
        document.getElementById('btn-iniciar-turno').addEventListener('click', () => this.abrirTurno());
        document.getElementById('btn-cerrar-turno').addEventListener('click', () => this.mostrarModalCierre());
        document.getElementById('btn-cerrar-turno-confirmar').addEventListener('click', () => this.cerrarTurno());

        // Pago modal
        document.getElementById('pago-forma').addEventListener('change', () => this.onCambioFormaPago());
        document.getElementById('pago-efectivo-recibido').addEventListener('input', () => this.onEfectivoRecibidoChange());
        document.getElementById('btn-agregar-pago').addEventListener('click', () => this.agregarPago());
        document.getElementById('pago-confirmar-btn').addEventListener('click', () => this.confirmarPago());
    },
};