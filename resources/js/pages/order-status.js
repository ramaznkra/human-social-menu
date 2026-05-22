import { initOrderStatusPoll } from '../order-status-poll.js';

const root = document.getElementById('order-status-root');
if (root) {
    initOrderStatusPoll({
        orderId: Number(root.dataset.orderId),
        initialStatus: root.dataset.initialStatus,
        initialStep: Number(root.dataset.initialStep) || 1,
        pollUrl: root.dataset.pollUrl,
    });
}
