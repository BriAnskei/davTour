{{-- Audit Log Modal --}}
<div id="auditLogModal" class="fixed inset-0 z-50 hidden overflow-y-auto">
    <div class="flex items-center justify-center min-h-screen px-4 pt-4 pb-20 text-center sm:block sm:p-0">
        <div class="fixed inset-0 transition-opacity" aria-hidden="true" onclick="closeAuditLogModal()">
            <div class="absolute inset-0 bg-jungle-900/60 backdrop-blur-sm"></div>
        </div>

        <span class="hidden sm:inline-block sm:align-middle sm:h-screen" aria-hidden="true">&#8203;</span>

        <div class="inline-block align-bottom bg-white rounded-2xl text-left overflow-hidden shadow-xl transform transition-all sm:my-8 sm:align-middle sm:max-w-2xl sm:w-full border border-slate2">
            <div class="bg-white px-6 py-4 border-b border-slate2 flex items-center justify-between">
                <div>
                    <h3 class="text-lg font-display font-bold text-jungle-700">Activity History</h3>
                    <p class="text-xs text-gray-400">Showing last 50 global operations</p>
                </div>
                <button onclick="closeAuditLogModal()" class="text-gray-400 hover:text-gray-600 transition-colors">
                    <svg class="w-6 h-6" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                </button>
            </div>

            <div class="px-6 py-4 max-h-[60vh] overflow-y-auto" id="auditLogContent">
                {{-- Content will be loaded via AJAX --}}
            </div>

            <div class="bg-cream px-6 py-3 border-t border-slate2 text-right">
                <button onclick="closeAuditLogModal()" class="px-4 py-2 rounded-xl text-sm font-semibold text-gray-500 hover:bg-gray-100 transition-colors">Close</button>
            </div>
        </div>
    </div>
</div>

<script>
    function openAuditLogModal(type = 'all') {
        const modal = document.getElementById('auditLogModal');
        const content = document.getElementById('auditLogContent');
        modal.classList.remove('hidden');
        
        content.innerHTML = `
            <div class="flex items-center justify-center py-10">
                <svg class="animate-spin h-8 w-8 text-amber-400" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                    <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                    <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                </svg>
            </div>`;

        fetch(`/admin/audit-logs/${type}`)
            .then(response => response.json())
            .then(data => {
                if (data.length === 0) {
                    content.innerHTML = `<p class="text-center text-gray-400 py-10">No activity history found.</p>`;
                    return;
                }

                let html = '<div class="space-y-6">';
                data.forEach(log => {
                    const date = new Date(log.created_at).toLocaleString();
                    let details = '';
                    
                    if (log.action === 'updated' && log.new_values) {
                        details = '<div class="mt-2 text-[11px] space-y-1 bg-gray-50 p-3 rounded-xl border border-slate2">';
                        for (let key in log.new_values) {
                            let oldVal = log.old_values && log.old_values[key] !== undefined ? log.old_values[key] : '—';
                            let newVal = log.new_values[key];
                            
                            // Truncate long strings
                            if (typeof oldVal === 'string' && oldVal.length > 50) oldVal = oldVal.substring(0, 47) + '...';
                            if (typeof newVal === 'string' && newVal.length > 50) newVal = newVal.substring(0, 47) + '...';

                            details += `<div><span class="font-bold text-gray-600">${key}:</span> <span class="text-red-400 line-through">${oldVal}</span> <span class="text-green-600">→ ${newVal}</span></div>`;
                        }
                        details += '</div>';
                    }

                    const actionColor = log.action === 'created' ? 'text-green-600' : (log.action === 'deleted' ? 'text-red-600' : 'text-blue-600');

                    html += `
                        <div class="relative pl-8 pb-2 border-l-2 border-slate2 last:border-0 last:pb-0">
                            <div class="absolute -left-[11px] top-1 w-5 h-5 rounded-full border-4 border-white ${log.action === 'created' ? 'bg-green-500' : (log.action === 'deleted' ? 'bg-red-500' : 'bg-blue-500')} shadow-sm"></div>
                            <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-1 mb-1">
                                <div class="flex items-center gap-2">
                                    <span class="px-2 py-0.5 text-[10px] font-black uppercase tracking-wider ${actionColor}">${log.action}</span>
                                    <span class="font-bold text-jungle-700 text-sm">${log.auditable_name || 'Unnamed Record'}</span>
                                </div>
                                <span class="text-[10px] text-gray-400 font-medium">${date}</span>
                            </div>
                            <div class="text-[10px] text-gray-400 italic mb-1">Type: ${log.auditable_type.split('\\').pop()} • IP: ${log.ip_address || 'Unknown'}</div>
                            ${details}
                        </div>
                    `;
                });
                html += '</div>';
                content.innerHTML = html;
            })
            .catch(error => {
                content.innerHTML = `<p class="text-center text-red-400 py-10">Error loading history.</p>`;
            });
    }

    function closeAuditLogModal() {
        document.getElementById('auditLogModal').classList.add('hidden');
    }

    // Close on escape
    document.addEventListener('keydown', (e) => {
        if (e.key === 'Escape') closeAuditLogModal();
    });
</script>
