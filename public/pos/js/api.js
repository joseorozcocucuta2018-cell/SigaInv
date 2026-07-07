const API = {
    baseUrl: '/pos/api',
    timeoutMs: 15000,

    _getToken() {
        return localStorage.getItem('pos_token');
    },

    _getHeaders(isJson = true) {
        const headers = {};
        const token = this._getToken();
        if (token) {
            headers['Authorization'] = `Bearer ${token}`;
        }
        if (isJson) {
            headers['Content-Type'] = 'application/json';
            headers['Accept'] = 'application/json';
        }
        return headers;
    },

    async _fetch(url, options = {}) {
        const controller = new AbortController();
        const timeout = setTimeout(() => controller.abort(), this.timeoutMs);

        try {
            const response = await fetch(url, {
                ...options,
                signal: controller.signal,
                headers: { ...this._getHeaders(), ...options.headers },
            });

            clearTimeout(timeout);

            if (response.status === 401) {
                Auth.clear();
                Auth.showLogin();
                throw new Error('Sesión expirada');
            }

            if (response.status === 422) {
                const data = await response.json();
                const msgs = data.errors ? Object.values(data.errors).flat().join(', ') : (data.error || 'Error de validación');
                throw new Error(msgs);
            }

            if (!response.ok) {
                const data = await response.json().catch(() => ({}));
                throw new Error(data.error || `Error ${response.status}`);
            }

            return await response.json();
        } catch (err) {
            clearTimeout(timeout);
            if (err.name === 'AbortError') {
                throw new Error('La solicitud tardó demasiado. Intente de nuevo.');
            }
            throw err;
        }
    },

    get(endpoint) {
        return this._fetch(`${this.baseUrl}${endpoint}`);
    },

    post(endpoint, data) {
        return this._fetch(`${this.baseUrl}${endpoint}`, {
            method: 'POST',
            body: JSON.stringify(data),
        });
    },

    put(endpoint, data) {
        return this._fetch(`${this.baseUrl}${endpoint}`, {
            method: 'PUT',
            body: JSON.stringify(data),
        });
    },

    delete(endpoint) {
        return this._fetch(`${this.baseUrl}${endpoint}`, {
            method: 'DELETE',
        });
    },

    // Auth
    login(email, password) {
        return this.post('/auth/login', { email, password });
    },
    checkAuth() {
        return this.get('/auth/me');
    },
    logout() {
        return this.post('/auth/logout');
    },

    // Empresa
    getEmpresa() {
        return this.get('/empresa');
    },

    // Productos
    buscarProductos(q = '', limit = 200) {
        const params = new URLSearchParams();
        if (q) params.set('q', q);
        params.set('limit', String(limit));
        return this.get(`/productos?${params}`);
    },

    // Stock
    getStock(bodegaId = 0, productoId = 0) {
        const params = new URLSearchParams();
        if (bodegaId > 0) params.set('bodega_id', String(bodegaId));
        if (productoId > 0) params.set('producto_id', String(productoId));
        return this.get(`/stock?${params}`);
    },

    // Clientes
    buscarClientes(q = '', limit = 30) {
        const params = new URLSearchParams();
        if (q) params.set('q', q);
        params.set('limit', String(limit));
        return this.get(`/clientes?${params}`);
    },
    crearCliente(data) {
        return this.post('/clientes', data);
    },

    // Bodegas
    getBodegas() {
        return this.get('/bodegas');
    },

    // Formas de pago
    getFormasPago() {
        return this.get('/formas-pago');
    },

    // Turnos
    turnoActivo() {
        return this.get('/turnos/activo');
    },
    aperturaTurno(data) {
        return this.post('/turnos', data);
    },
    cerrarTurno(data) {
        return this.post('/turnos/cerrar', data);
    },
    turnoResumen(turnoId) {
        return this.get(`/turnos/${turnoId}/resumen`);
    },

    // Ventas
    crearVenta(data) {
        return this.post('/ventas', data);
    },
    getVenta(id) {
        return this.get(`/ventas/${id}`);
    },

    // Remisiones
    crearRemision(data) {
        return this.post('/remisiones', data);
    },
    getRemision(id) {
        return this.get(`/remisiones/${id}`);
    },

    // Cajas
    getCajas() {
        return this.get('/cajas');
    },
    getCaja(id) {
        return this.get(`/cajas/${id}`);
    },

    // Cart / Memory (localStorage)
    getLocalCarrito() {
        const memorias = this.getTodasLasMemorias();
        const activa = parseInt(localStorage.getItem('pos_memoria_activa') || '1', 10);
        return memorias[activa] || this._carritoVacio();
    },

    getTodasLasMemorias() {
        try {
            return JSON.parse(localStorage.getItem('pos_memorias') || '{}');
        } catch { return {}; }
    },

    saveLocalCarrito(carrito) {
        const activa = parseInt(localStorage.getItem('pos_memoria_activa') || '1', 10);
        const memorias = this.getTodasLasMemorias();
        memorias[activa] = carrito;
        localStorage.setItem('pos_memorias', JSON.stringify(memorias));
        localStorage.setItem('pos_carrito', JSON.stringify(carrito));
        localStorage.setItem('pos_memoria_' + activa + '_updated', Date.now().toString());
    },

    guardarMemoria(indice) {
        const carrito = this.getLocalCarrito();
        const memorias = this.getTodasLasMemorias();
        memorias[indice] = JSON.parse(JSON.stringify(carrito));
        localStorage.setItem('pos_memorias', JSON.stringify(memorias));
        localStorage.setItem('pos_memoria_' + indice + '_updated', Date.now().toString());
    },

    cambiarMemoria(indice) {
        const memorias = this.getTodasLasMemorias();
        if (!memorias[indice]) {
            memorias[indice] = this._carritoVacio();
            localStorage.setItem('pos_memorias', JSON.stringify(memorias));
        }
        localStorage.setItem('pos_memoria_activa', String(indice));
        const carrito = memorias[indice];
        localStorage.setItem('pos_carrito', JSON.stringify(carrito));
        return carrito;
    },

    _carritoVacio() {
        return { items: [], cliente: null, descuentoGlobal: 0, tipoDocumento: 'factura' };
    },

    agregarAlCarrito(producto, cantidad) {
        const carrito = this.getLocalCarrito();
        const existente = carrito.items.find(i => i.producto_id === producto.id);
        if (existente) {
            existente.cantidad = parseFloat((existente.cantidad + cantidad).toFixed(4));
        } else {
            const impuesto = producto.impuesto || { porcentaje: 0 };
            carrito.items.push({
                producto_id: producto.id,
                codigo: producto.codigo,
                nombre: producto.nombre,
                precio_venta: parseFloat(producto.precio_venta),
                cantidad: parseFloat(cantidad.toFixed(4)),
                impuesto_porcentaje: parseFloat(impuesto.porcentaje),
                descuento_unitario: 0,
                subtotal: 0,
            });
        }
        this.recalcularTotales(carrito);
        this.saveLocalCarrito(carrito);
        return carrito;
    },

    actualizarCantidad(productoId, cantidad) {
        const carrito = this.getLocalCarrito();
        const item = carrito.items.find(i => i.producto_id === productoId);
        if (item) {
            if (cantidad <= 0) {
                carrito.items = carrito.items.filter(i => i.producto_id !== productoId);
            } else {
                item.cantidad = parseFloat(cantidad.toFixed(4));
            }
            this.recalcularTotales(carrito);
            this.saveLocalCarrito(carrito);
        }
        return carrito;
    },

    eliminarDelCarrito(productoId) {
        const carrito = this.getLocalCarrito();
        carrito.items = carrito.items.filter(i => i.producto_id !== productoId);
        this.recalcularTotales(carrito);
        this.saveLocalCarrito(carrito);
        return carrito;
    },

    vaciarCarrito() {
        const carrito = this._carritoVacio();
        this.saveLocalCarrito(carrito);
        return carrito;
    },

    setDescuentoItem(productoId, descuento) {
        const carrito = this.getLocalCarrito();
        const item = carrito.items.find(i => i.producto_id === productoId);
        if (item) {
            item.descuento_unitario = parseFloat(descuento) || 0;
            this.recalcularTotales(carrito);
            this.saveLocalCarrito(carrito);
        }
        return carrito;
    },

    setDescuentoGlobal(descuento) {
        const carrito = this.getLocalCarrito();
        carrito.descuentoGlobal = parseFloat(descuento) || 0;
        this.recalcularTotales(carrito);
        this.saveLocalCarrito(carrito);
        return carrito;
    },

    setCliente(cliente) {
        const carrito = this.getLocalCarrito();
        carrito.cliente = { id: cliente.id, nombre: cliente.nombre, documento: cliente.documento, porcentaje_descuento: parseFloat(cliente.porcentaje_descuento || 0) };
        this.recalcularTotales(carrito);
        this.saveLocalCarrito(carrito);
        return carrito;
    },

    setTipoDocumento(tipo) {
        const carrito = this.getLocalCarrito();
        carrito.tipoDocumento = tipo;
        this.saveLocalCarrito(carrito);
        return carrito;
    },

    recalcularTotales(carrito) {
        let subtotal = 0;
        let impuestos = 0;
        carrito.items.forEach(item => {
            const precio = parseFloat(item.precio_venta) || 0;
            const desc = parseFloat(item.descuento_unitario) || 0;
            const cant = parseFloat(item.cantidad) || 0;
            const precioFinal = Math.max(0, precio - desc);
            const lineSubtotal = precioFinal * cant;
            item.subtotal = parseFloat(lineSubtotal.toFixed(2));
            subtotal += lineSubtotal;
            const iva = parseFloat(item.impuesto_porcentaje) || 0;
            impuestos += lineSubtotal * (iva / 100);
        });
        carrito.subtotal = parseFloat(subtotal.toFixed(2));
        carrito.impuestos = parseFloat(impuestos.toFixed(2));
        const descGlobal = parseFloat(carrito.descuentoGlobal) || 0;
        carrito.descuento = Math.min(descGlobal, subtotal);
        carrito.total = parseFloat(Math.max(0, subtotal - carrito.descuento + impuestos).toFixed(2));
        return carrito;
    },
};

const Auth = {
    init() {
        this.checkAuth();
    },

    checkAuth() {
        const token = localStorage.getItem('pos_token');
        const user = localStorage.getItem('pos_user');
        if (token && user) {
            API.checkAuth()
                .then(() => {
                    App.user = JSON.parse(user);
                    App.showPOS();
                })
                .catch(() => {
                    this.clear();
                    this.showLogin();
                });
        } else {
            this.showLogin();
        }
    },

    async login(email, password) {
        const res = await API.login(email, password);
        localStorage.setItem('pos_token', res.token);
        localStorage.setItem('pos_user', JSON.stringify(res.user));
        App.user = res.user;
        return res;
    },

    async logout() {
        try {
            await API.logout();
        } catch { }
        this.clear();
        this.showLogin();
    },

    clear() {
        localStorage.removeItem('pos_token');
        localStorage.removeItem('pos_user');
        localStorage.removeItem('pos_carrito');
        localStorage.removeItem('pos_memorias');
        localStorage.removeItem('pos_memoria_activa');
    },

    showLogin() {
        document.getElementById('login-view').classList.remove('d-none');
        document.getElementById('pos-wrapper').classList.add('d-none');
    },

    hideLogin() {
        document.getElementById('login-view').classList.add('d-none');
        document.getElementById('pos-wrapper').classList.remove('d-none');
    },
};