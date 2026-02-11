// Generic DOM updaters for real-time events

window.addEventListener('job-order.created', (e) => {
    const payload = e.detail;

    // Only update when on job-orders index
    if (!window.location.pathname.startsWith('/job-orders')) return;

    const table = document.querySelector('table');
    if (!table) return;

    const tbody = table.querySelector('tbody');
    if (!tbody) return;

    // Remove empty state row if present
    const emptyRow = tbody.querySelector('td[colspan]');
    if (emptyRow) {
        tbody.innerHTML = '';
    }

    // Create a new row (keep simple and fill available data)
    const tr = document.createElement('tr');
    tr.className = 'hover:bg-gray-50 transition-colors';

    const fulfillmentTd = document.createElement('td');
    fulfillmentTd.className = 'px-5 py-4 text-sm font-medium whitespace-nowrap';
    fulfillmentTd.innerHTML = '<span class="inline-flex px-2.5 py-1 text-xs font-medium rounded-full bg-blue-100 text-blue-800 ring-1 ring-blue-200">New</span>';

    const joNumberTd = document.createElement('td');
    joNumberTd.className = 'px-5 py-4 text-sm font-mono text-gray-900 whitespace-nowrap';
    joNumberTd.textContent = payload.jo_number || '—';

    const dateNeededTd = document.createElement('td');
    dateNeededTd.className = 'px-5 py-4 text-sm text-gray-900 whitespace-nowrap';
    dateNeededTd.textContent = payload.created_at ? new Date(payload.created_at).toLocaleDateString() : '—';

    const poTd = document.createElement('td');
    poTd.className = 'px-5 py-4 text-sm text-gray-900 whitespace-nowrap';
    poTd.textContent = payload.po_number || '—';

    const productCodeTd = document.createElement('td');
    productCodeTd.className = 'px-5 py-4 text-sm font-mono text-gray-900 whitespace-nowrap';
    productCodeTd.textContent = payload.product || '—';

    const customerTd = document.createElement('td');
    customerTd.className = 'px-5 py-4 text-sm text-gray-900 whitespace-nowrap';
    customerTd.textContent = payload.customer || '—';

    const modelTd = document.createElement('td');
    modelTd.className = 'px-5 py-4 text-sm text-gray-900 whitespace-nowrap';
    modelTd.textContent = payload.product || '—';

    const descTd = document.createElement('td');
    descTd.className = 'px-5 py-4 text-sm text-gray-600 whitespace-nowrap';
    descTd.textContent = payload.description ? payload.description.substring(0, 35) : '—';

    const dimTd = document.createElement('td');
    dimTd.className = 'px-5 py-4 text-sm text-gray-900 whitespace-nowrap';
    dimTd.textContent = payload.dimension || '—';

    const qtyTd = document.createElement('td');
    qtyTd.className = 'px-5 py-4 text-sm text-gray-900 whitespace-nowrap font-semibold';
    qtyTd.textContent = payload.quantity ? Number(payload.quantity).toLocaleString() : '0';

    const uomTd = document.createElement('td');
    uomTd.className = 'px-5 py-4 text-sm text-gray-900 whitespace-nowrap';
    uomTd.textContent = payload.uom || 'pcs';

    const encodedTd = document.createElement('td');
    encodedTd.className = 'px-5 py-4 text-sm text-gray-900 whitespace-nowrap';
    encodedTd.textContent = payload.user || '—';

    const remarksTd = document.createElement('td');
    remarksTd.className = 'px-5 py-4 text-sm text-gray-600 whitespace-nowrap';
    remarksTd.textContent = payload.message || '';

    const actionsTd = document.createElement('td');
    actionsTd.className = 'px-5 py-4 text-sm whitespace-nowrap sticky right-0 bg-white shadow-[-6px_0_12px_-4px_rgba(0,0,0,0.08)] z-10';
    actionsTd.innerHTML = `<div class="flex items-center gap-3 px-2"></div>`;

    tr.appendChild(fulfillmentTd);
    tr.appendChild(joNumberTd);
    tr.appendChild(dateNeededTd);
    tr.appendChild(poTd);
    tr.appendChild(productCodeTd);
    tr.appendChild(customerTd);
    tr.appendChild(modelTd);
    tr.appendChild(descTd);
    tr.appendChild(dimTd);
    tr.appendChild(qtyTd);
    tr.appendChild(uomTd);
    tr.appendChild(encodedTd);
    tr.appendChild(remarksTd);
    tr.appendChild(actionsTd);

    // Insert at top
    tbody.insertBefore(tr, tbody.firstChild);

    // Optionally remove last row when paginated to keep length
    const rows = tbody.querySelectorAll('tr');
    if (rows.length > 16) {
        tbody.removeChild(tbody.lastChild);
    }
});

// Highlight row when status changes
window.addEventListener('job-order.status-changed', (e) => {
    const payload = e.detail;
    if (!window.location.pathname.startsWith('/job-orders')) return;
    const rows = document.querySelectorAll('tbody tr');
    for (const row of rows) {
        const joCell = row.querySelector('td:nth-child(2)');
        if (joCell && joCell.textContent.trim() === payload.jo_number) {
            row.classList.add('bg-yellow-50');
            setTimeout(() => row.classList.remove('bg-yellow-50'), 3000);
        }
    }
});

// When a transfer for a job order is created, update the job-order show page (if open)
window.addEventListener('transfer.created', (e) => {
    const payload = e.detail;
    // If on job-orders.show page (path like /job-orders/{id}) and the id matches
    const pathMatch = window.location.pathname.match(/^\/job-orders\/(\d+)/);
    if (!pathMatch) return;
    const currentId = Number(pathMatch[1]);
    if (currentId !== payload.jo_id) return;

    // If there is a transfers table, prepend the new transfer
    const transfersTable = document.querySelector('#transfers-table');
    if (!transfersTable) return;
    const tbody = transfersTable.querySelector('tbody');
    const tr = document.createElement('tr');
    tr.className = 'hover:bg-gray-50 transition-colors';
    tr.innerHTML = `
        <td class="px-5 py-4 text-sm text-gray-900 whitespace-nowrap">${new Date(payload.created_at).toLocaleString()}</td>
        <td class="px-5 py-4 text-sm text-gray-900 whitespace-nowrap">${payload.qty_received}</td>
        <td class="px-5 py-4 text-sm text-gray-900 whitespace-nowrap">${payload.message || ''}</td>
    `;
    tbody.insertBefore(tr, tbody.firstChild);

    // show a subtle highlight
    tr.classList.add('bg-green-50');
    setTimeout(() => tr.classList.remove('bg-green-50'), 3500);
});
