// jQuery Promise Migration Example
// OLD: Callback-based approach
function loadMembers_old() {
    $('#loading').show();
    $.ajax({
        url: '/ksp_samosir/api?action=get_members',
        method: 'POST',
        data: { status: 'aktif' },
        success: function(response) {
            if (response.success) {
                renderMembers(response.data);
                $('#loading').hide();
            } else {
                showError(response.message);
                $('#loading').hide();
            }
        },
        error: function(xhr, status, error) {
            showError('Failed to load members');
            $('#loading').hide();
        }
    });
}

// NEW: Promise-based approach with async/await
async function loadMembers() {
    const loadingEl = $('#loading');
    loadingEl.show();

    try {
        // Use new RESTful API with promises
        const members = await KSP.api.getMembers({ status: 'aktif' });
        renderMembers(members);
    } catch (error) {
        KSP.showError(error.message || 'Failed to load members');
    } finally {
        loadingEl.hide();
    }
}

// Usage in event handlers
$(document).ready(function() {
    $('#refresh-members-btn').on('click', async function() {
        await loadMembers();
        KSP.showSuccess('Members refreshed successfully');
    });

    // Load initial data
    loadMembers();
});
