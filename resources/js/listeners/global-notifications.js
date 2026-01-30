if (window.Echo) {
    // Job Orders
    window.Echo.channel('job-orders')
        .listen('.job-order-created', (e) => {
            window.showToast(
                `New Job Order: ${e.jo_number}`,
                'success',
                `by ${e.user} • ${e.product}`
            );
        })
        .listen('.job-order-updated', (e) => {
            window.showToast(`JO ${e.jo_number} → ${e.status}`, 'info');
        })
        .listen('.job-order-approved', (e) => {
            window.showToast(`JO ${e.jo_number} APPROVED`, 'success');
        });

    // Inventory / Actual Counts
    window.Echo.channel('inventory')
        .listen('.inventory-counted', (e) => {
            window.showToast(`New count: ${e.tag_number} • ${e.fg_qty} pcs`, 'warning');
        })
        .listen('.inventory-verified', (e) => {
            window.showToast(`Count verified: ${e.tag_number}`, 'success');
        });

    // Delivery Schedules
    window.Echo.channel('deliveries')
        .listen('.delivery-scheduled', (e) => {
            window.showToast(`New delivery: ${e.schedule_date}`, 'info');
        })
        .listen('.delivery-marked', (e) => {
            window.showToast(`Delivery #${e.id} marked as delivered`, 'success');
        });

    // You can keep adding more channels here...
}