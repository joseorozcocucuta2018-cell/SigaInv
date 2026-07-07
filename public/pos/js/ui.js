const UI = {
    formatoNumero(numero) {
        if (numero === null || numero === undefined) return '$0';
        return '$' + parseFloat(numero).toLocaleString('es-CO', {
            minimumFractionDigits: 0,
            maximumFractionDigits: 0,
        });
    },

    escapeHtml(texto) {
        if (!texto) return '';
        const div = document.createElement('div');
        div.textContent = String(texto);
        return div.innerHTML;
    },

    mostrarToast(mensaje, tipo = 'success') {
        const container = document.getElementById('toast-container');
        const toast = document.createElement('div');
        toast.className = 'toast align-items-center text-bg-' + tipo + ' border-0 mb-2';
        toast.role = 'alert';
        toast.innerHTML = `
            <div class="d-flex">
                <div class="toast-body">
                    ${this.escapeHtml(mensaje)}
                </div>
                <button type="button" class="btn-close btn-close-white me-2 m-auto" data-bs-dismiss="toast"></button>
            </div>
        `;
        container.appendChild(toast);
        const bsToast = new bootstrap.Toast(toast, { delay: 3000 });
        bsToast.show();
        toast.addEventListener('hidden.bs.toast', () => toast.remove());
    },

    mostrarError(mensaje) {
        this.mostrarToast(mensaje, 'danger');
    },
};