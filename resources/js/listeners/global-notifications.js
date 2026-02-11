if (window.Echo) {
    // Job Orders
    window.Echo.channel('job-orders')
        .listen('.job-order-created', (e) => {
            window.showToast(
                `New Job Order: ${e.jo_number}`,
                'success',
                `by ${e.user} • ${e.product}`
            );
            // Dispatch a DOM event for page-specific listeners
            window.dispatchEvent(new CustomEvent('job-order.created', { detail: e }));
        })
        .listen('.job-order-status-changed', (e) => {
            window.showToast(`JO ${e.jo_number} → ${e.new_status}`, 'info');
            window.dispatchEvent(new CustomEvent('job-order.status-changed', { detail: e }));
        })
        .listen('.job-order-approved', (e) => {
            window.showToast(`JO ${e.jo_number} APPROVED`, 'success');
            window.dispatchEvent(new CustomEvent('job-order.approved', { detail: e }));
        });

    // Inventory / Actual Counts
    window.Echo.channel('inventory')
        .listen('.inventory-counted', (e) => {
            window.showToast(`New count: ${e.tag_number} • ${e.fg_qty} pcs`, 'warning');
            window.dispatchEvent(new CustomEvent('inventory.counted', { detail: e }));
        })
        .listen('.inventory-verified', (e) => {
            window.showToast(`Count verified: ${e.tag_number}`, 'success');
            window.dispatchEvent(new CustomEvent('inventory.verified', { detail: e }));
        });

    // Delivery Schedules (channel name fixed)
    window.Echo.channel('delivery-schedules')
        .listen('.delivery-scheduled', (e) => {
            window.showToast(`New delivery: ${e.schedule_date}`, 'info');
            window.dispatchEvent(new CustomEvent('delivery-scheduled', { detail: e }));
        })
        .listen('.delivery-marked', (e) => {
            window.showToast(`Delivery #${e.id} marked as delivered`, 'success');
            window.dispatchEvent(new CustomEvent('delivery.marked', { detail: e }));
        });

    // Transfers
    window.Echo.channel('transfers')
        .listen('.transfer-created', (e) => {
            window.showToast(`Transfer received for JO ${e.jo_number} • ${e.qty_received} pcs`, 'info');
            window.dispatchEvent(new CustomEvent('transfer.created', { detail: e }));
        });

    // Finished Goods
    window.Echo.channel('finished-goods')
        .listen('.finished-good-updated', (e) => {
            window.showToast(`Finished Good Updated: ${e.product}`,'info');
            window.dispatchEvent(new CustomEvent('finished-good.updated', { detail: e }));
        });

    // You can keep adding more channels here...
}