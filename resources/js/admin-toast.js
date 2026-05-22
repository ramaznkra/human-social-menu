/**
 * Admin panel toast bildirimleri (Hazırlanıyor, sipariş alındı vb.)
 */
export function showAdminToast({ title, message, hint = '', type = 'success', durationMs = 4200 }) {
    let host = document.getElementById('adminToastHost');
    if (!host) {
        host = document.createElement('div');
        host.id = 'adminToastHost';
        host.className = 'admin-toast-host';
        host.setAttribute('aria-live', 'polite');
        document.body.appendChild(host);
    }

    const icons = {
        success: '👨‍🍳',
        info: '⚡',
        error: '⚠️',
    };

    const el = document.createElement('div');
    el.className = `admin-toast admin-toast--${type}`;
    el.innerHTML = `
        <div class="admin-toast__icon" aria-hidden="true">${icons[type] || '✓'}</div>
        <div class="admin-toast__body">
            <p class="admin-toast__title">${title}</p>
            ${message ? `<p class="admin-toast__message">${message}</p>` : ''}
            ${hint ? `<p class="admin-toast__hint">${hint}</p>` : ''}
        </div>
        <button type="button" class="admin-toast__close" aria-label="Kapat">✕</button>
    `;

    const dismiss = () => {
        el.classList.add('admin-toast--out');
        setTimeout(() => el.remove(), 280);
    };

    el.querySelector('.admin-toast__close')?.addEventListener('click', dismiss);
    host.appendChild(el);
    requestAnimationFrame(() => el.classList.add('admin-toast--in'));

    const timer = setTimeout(dismiss, durationMs);
    el.addEventListener('mouseenter', () => clearTimeout(timer));
}
