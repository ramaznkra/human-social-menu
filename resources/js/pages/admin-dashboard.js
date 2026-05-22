import { initAdminLiveOps } from '../admin-live-ops.js';

const root = document.getElementById('admin-live-ops');
if (root) {
    initAdminLiveOps({
        apiUrl: root.dataset.apiUrl,
        acknowledgeUrlTemplate: root.dataset.acknowledgeUrl,
        csrfToken: document.querySelector('meta[name="csrf-token"]')?.content || '',
    });
}
