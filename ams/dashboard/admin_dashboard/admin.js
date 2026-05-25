(function () {
    // Shared admin utilities
    window.escapeHtml = function (text) {
        const div = document.createElement('div');
        div.textContent = text || '';
        return div.innerHTML;
    };

    window.esc = function (v) {
        return String(v ?? '').replace(/&/g,'&amp;').replace(/</g,'&lt;').replace(/>/g,'&gt;').replace(/"/g,'&quot;').replace(/'/g,'&#039;');
    };

    window.showAlert = function (type, message) {
        const existing = document.querySelector('.alert');
        if (existing) existing.remove();
        const alert = document.createElement('div');
        alert.className = 'alert ' + (type === 'success' ? 'alert-success' : 'alert-error');
        alert.textContent = message;
        const header = document.querySelector('.page-header');
        if (header) header.insertAdjacentElement('afterend', alert);
        setTimeout(() => alert.remove(), 5000);
    };

    window.formatDate = function (s) {
        if (!s) return '—';
        const d = new Date(s);
        return isNaN(d) ? s : d.toLocaleDateString('en-PH', { year:'numeric', month:'short', day:'numeric' });
    };

    window.badge = function (status) {
        const map = { pending: 'badge-pending', verified: 'badge-verified', rejected: 'badge-rejected' };
        return `<span class="badge ${map[status] || ''}">${escapeHtml(status)}</span>`;
    };

    window.tags = function (arr, labelKey) {
        if (!arr || arr.length === 0) return '<span class="tag none">None recorded</span>';
        return arr.map(i => `<span class="tag">${escapeHtml(i[labelKey] || JSON.stringify(i))}</span>`).join('');
    };

    window.serializeForm = function (form) {
        const formData = new FormData(form);
        const data = {};
        for (const [name, value] of formData.entries()) {
            if (name.includes('[')) {
                const arrayName = name.replace('[]', '');
                if (!data[arrayName]) data[arrayName] = [];
                data[arrayName].push(value);
                continue;
            }
            if (data[name] !== undefined) {
                if (!Array.isArray(data[name])) data[name] = [data[name]];
                data[name].push(value);
            } else {
                data[name] = value;
            }
        }
        return data;
    };

    window.showModal = function (contentHtml) {
        const modalContainer = document.getElementById('modalContainer') || document.getElementById('reviewModal') || document.body;
        if (!modalContainer) return;
        // If page has modalContainer, use it; else inject a minimal modal
        if (document.getElementById('modalContainer')) {
            modalContainer.innerHTML = `
                <div class="modal" role="dialog" aria-modal="true">
                    <div class="modal-content">
                        <div class="modal-header">
                            ${contentHtml.header}
                            <button class="modal-close" type="button" onclick="closeModal()">×</button>
                        </div>
                        <div class="modal-body">${contentHtml.body}</div>
                    </div>
                </div>
            `;
        } else if (document.getElementById('reviewModal')) {
            // For enrollment queue specific modal
            const modal = document.getElementById('reviewModal');
            const body = document.getElementById('modalBody');
            if (body) body.innerHTML = contentHtml.body || '';
            modal.classList.add('open');
        }
    };

    window.closeModal = function () {
        const modalContainer = document.getElementById('modalContainer');
        if (modalContainer) modalContainer.innerHTML = '';
        const review = document.getElementById('reviewModal');
        if (review) review.classList.remove('open');
    };

    window.getRoleBadgeClass = function (role) {
        const map = { admin: 'badge-admin', teacher: 'badge-teacher', staff: 'badge-staff' };
        return map[(role || '').toLowerCase()] || 'badge-default';
    };

    window.adminSectionsInit = function () {
        const hasAPI = typeof window.API !== 'undefined';
        const form = document.querySelector('form.form-grid');
        if (form) {
            form.addEventListener('submit', async function (e) {
                if (!hasAPI) return;
                e.preventDefault();
                const action = form.querySelector('input[name="action"]').value;
                const name = (form.querySelector('input[name="name"]').value || '').trim();
                const idInput = form.querySelector('input[name="id"]');
                try {
                    if (action === 'create') {
                        await API.call('endpoints/sections/create', { school_year: form.school_year.value, grade_level: form.grade_level.value, name, is_active: parseInt(form.is_active.value, 10) || 0 }, 'POST');
                        window.location.reload();
                    } else if (action === 'update' && idInput) {
                        await API.call('endpoints/sections/update', { id: parseInt(idInput.value, 10), school_year: form.school_year.value, grade_level: form.grade_level.value, name, is_active: parseInt(form.is_active.value, 10) || 0 }, 'POST');
                        window.location.href = 'admin_sections.php';
                    }
                } catch (err) {
                    showAlert('error', err.message || 'Operation failed');
                }
            });
        }

        document.querySelectorAll('form[method="post"]').forEach(f => {
            const actionInput = f.querySelector('input[name="action"]');
            if (!actionInput || actionInput.value !== 'delete') return;
            f.addEventListener('submit', async function (e) {
                if (!hasAPI) return;
                e.preventDefault();
                if (!confirm('Delete this section?')) return;
                const idInput = f.querySelector('input[name="id"]');
                if (!idInput) return;
                try {
                    await API.call('endpoints/sections/delete', { id: parseInt(idInput.value, 10) }, 'POST');
                    f.closest('tr')?.remove();
                } catch (err) {
                    showAlert('error', err.message || 'Delete failed');
                }
            });
        });
    };

    window.adminSubjectsInit = function () {
        const hasAPI = typeof window.API !== 'undefined';
        const form = document.querySelector('form.form-grid');
        if (form) {
            form.addEventListener('submit', async function (e) {
                if (!hasAPI) return;
                e.preventDefault();
                const action = form.querySelector('input[name="action"]').value;
                const name = (form.querySelector('input[name="name"]').value || '').trim();
                const idInput = form.querySelector('input[name="subject_id"]');
                try {
                    if (action === 'create') {
                        await API.crud.table('subjects').create({ name });
                        window.location.reload();
                    } else if (action === 'update' && idInput) {
                        await API.crud.table('subjects').update(idInput.value, { name });
                        window.location.href = 'admin_subjects.php';
                    }
                } catch (err) {
                    showAlert('error', err.message || 'Operation failed');
                }
            });
        }

        document.querySelectorAll('form[method="post"]').forEach(f => {
            const actionInput = f.querySelector('input[name="action"]');
            if (!actionInput || actionInput.value !== 'delete') return;
            f.addEventListener('submit', async function (e) {
                if (!hasAPI) return;
                e.preventDefault();
                if (!confirm('Delete this subject?')) return;
                const idInput = f.querySelector('input[name="subject_id"]');
                if (!idInput) return;
                try {
                    await API.crud.table('subjects').delete(idInput.value);
                    f.closest('tr')?.remove();
                } catch (err) {
                    showAlert('error', err.message || 'Delete failed');
                }
            });
        });
    };

    window.adminLookupsInit = function (tableName, primaryKey) {
        const hasAPI = typeof window.API !== 'undefined';
        const form = document.querySelector('form.form-grid');
        if (form) {
            form.addEventListener('submit', async function (e) {
                const actionInput = form.querySelector('input[name="action"]');
                const action = actionInput ? actionInput.value : '';
                if (!hasAPI) return;
                e.preventDefault();
                const nameInput = form.querySelector('input[name="name"]');
                const idInput = form.querySelector('input[name="' + primaryKey + '"]');
                const payload = { name: (nameInput?.value || '').trim() };
                try {
                    if (action === 'create') {
                        await API.crud.table(tableName).create(payload);
                        window.location.reload();
                    } else if (action === 'update' && idInput) {
                        await API.crud.table(tableName).update(idInput.value, payload);
                        window.location.href = 'admin_lookups.php?table=' + encodeURIComponent(tableName);
                    }
                } catch (err) {
                    showAlert('error', err.message || 'Operation failed');
                }
            });
        }

        document.querySelectorAll('form[method="post"]').forEach(f => {
            const actionInput = f.querySelector('input[name="action"]');
            if (!actionInput || actionInput.value !== 'delete') return;
            f.addEventListener('submit', async function (e) {
                if (!hasAPI) return;
                e.preventDefault();
                if (!confirm('Delete this item?')) return;
                const idInput = f.querySelector('input[name="' + primaryKey + '"]');
                if (!idInput) return;
                try {
                    await API.crud.table(tableName).delete(idInput.value);
                    f.closest('tr')?.remove();
                } catch (err) {
                    showAlert('error', err.message || 'Delete failed');
                }
            });
        });
    };

    window.adminUsersInit = function () {
        const pageMessage = document.getElementById('page-message');
        const modal = document.getElementById('toggleModal');
        const modalTitle = document.getElementById('modalTitle');
        const modalDesc = document.getElementById('modalDesc');
        const modalConfirm = document.getElementById('modalConfirm');
        const modalCancel = document.getElementById('modalCancel');
        let pendingToggle = null;
        const hasAPI = typeof window.API !== 'undefined';

        const showMsg = function (text, isError = false) {
            if (!pageMessage) return;
            pageMessage.className = 'alert ' + (isError ? 'alert-error' : 'alert-success');
            pageMessage.textContent = text;
            pageMessage.style.display = 'block';
            setTimeout(() => { pageMessage.style.display = 'none'; }, 4000);
        };

        const attachToggleButtons = function () {
            document.querySelectorAll('.toggle-btn').forEach(btn => {
                btn.addEventListener('click', () => {
                    const userId = btn.dataset.userId;
                    const isActive = btn.dataset.isActive === '1';
                    const action = isActive ? 'deactivate' : 'activate';
                    pendingToggle = { userId, newState: isActive ? 0 : 1, btn };
                    if (modalTitle) modalTitle.textContent = (isActive ? 'Deactivate' : 'Activate') + ' account?';
                    if (modalDesc) modalDesc.textContent = 'This will ' + action + ' the account. The user will ' + (isActive ? 'no longer be able to log in.' : 'regain access.');
                    if (modal) modal.classList.add('open');
                });
            });
        };

        if (modalCancel) {
            modalCancel.addEventListener('click', () => {
                modal?.classList.remove('open');
                pendingToggle = null;
            });
        }

        if (modal) {
            modal.addEventListener('click', e => {
                if (e.target === modal) {
                    modal.classList.remove('open');
                    pendingToggle = null;
                }
            });
        }

        if (modalConfirm) {
            modalConfirm.addEventListener('click', async () => {
                if (!pendingToggle) return;
                modal?.classList.remove('open');
                const { userId, newState, btn } = pendingToggle;
                pendingToggle = null;
                try {
                    if (hasAPI) {
                        await API.crud.table('users').update(userId, { is_active: newState ? 1 : 0 });
                        const badge = document.getElementById('status-badge-' + userId);
                        if (badge) {
                            badge.className = 'badge ' + (newState === 1 ? 'badge-success' : 'badge-danger');
                            badge.textContent = newState === 1 ? 'Active' : 'Inactive';
                        }
                        if (btn) {
                            btn.textContent = newState === 1 ? 'Deactivate' : 'Activate';
                            btn.dataset.isActive = newState ? '1' : '0';
                        }
                        showMsg('Account status updated.');
                    } else {
                        const res = await fetch('admin_users.php', {
                            method: 'POST',
                            headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
                            body: new URLSearchParams({ action: 'toggle_active', user_id: userId, is_active: newState })
                        });
                        if (res.ok) {
                            showMsg('Account status updated.');
                            window.location.reload();
                        } else {
                            showMsg('Failed to update status.', true);
                        }
                    }
                } catch (err) {
                    showMsg(err.message || 'Request failed.', true);
                }
            });
        }

        attachToggleButtons();

        const createForm = document.querySelector('form input[name="action"][value="create"]')?.closest('form');
        if (createForm) {
            createForm.addEventListener('submit', async function (e) {
                if (!hasAPI) return;
                e.preventDefault();
                const form = e.currentTarget;
                const data = {
                    username: form.username.value.trim(),
                    email: form.email.value.trim(),
                    password: form.password.value,
                    role: 'staff'
                };
                try {
                    await API.crud.table('users').create(data);
                    showMsg('Staff account created. Reloading...');
                    setTimeout(() => window.location.reload(), 800);
                } catch (err) {
                    showMsg(err.message || 'Create failed', true);
                }
            });
        }

        const updateForm = document.querySelector('form input[name="action"][value="update"]')?.closest('form');
        if (updateForm) {
            updateForm.addEventListener('submit', async function (e) {
                if (!hasAPI) return;
                e.preventDefault();
                const f = e.currentTarget;
                const userId = f.user_id.value;
                const payload = {
                    username: f.username.value.trim(),
                    email: f.email.value.trim(),
                    role: 'staff'
                };
                if (f.password && f.password.value) payload.password = f.password.value;
                if (f.grade_level && f.grade_level.value) payload.grade_level = f.grade_level.value;
                if (f.section_id && f.section_id.value) payload.section_id = f.section_id.value;
                if (f.is_active && f.is_active.value !== undefined) payload.is_active = parseInt(f.is_active.value, 10);
                try {
                    await API.crud.table('users').update(userId, payload);
                    showMsg('Staff updated. Reloading...');
                    setTimeout(() => window.location.reload(), 800);
                } catch (err) {
                    showMsg(err.message || 'Update failed', true);
                }
            });
        }

        document.querySelectorAll('form.inline').forEach(form => {
            form.addEventListener('submit', async function (e) {
                const hiddenAction = form.querySelector('input[name="action"]').value || '';
                if (hiddenAction !== 'delete' || !hasAPI) return;
                e.preventDefault();
                if (!confirm('Are you sure you want to delete this staff member? This action is permanent.')) return;
                const userId = form.querySelector('input[name="user_id"]').value;
                try {
                    await API.crud.table('users').delete(userId);
                    const row = document.getElementById('row-' + userId);
                    if (row) row.remove();
                    showMsg('Staff deleted.');
                } catch (err) {
                    showMsg(err.message || 'Delete failed', true);
                }
            });
        });
    };

    window.adminDashboardInit = async function () {
        const tbody = document.getElementById('staff-tbody');
        const count = document.getElementById('staff-count');
        const error = document.getElementById('staff-error');
        const message = document.getElementById('staff-error-msg');
        if (!tbody || !count) return;
        try {
            const response = await API.crud.list('users');
            if (!response || !response.success) throw new Error(response?.error || 'Unable to load staff list.');
            const rows = (response.data || []).filter(user => (user.role || '').toLowerCase() !== 'admin');
            count.textContent = rows.length;
            if (rows.length === 0) {
                if (tbody.querySelector('tr:not(.empty-row)')) return;
                tbody.innerHTML = '<tr class="empty-row"><td colspan="4">No staff accounts found.</td></tr>';
                return;
            }
            tbody.innerHTML = rows.map(staff => {
                const role = staff.role || 'Unassigned';
                const badgeClass = getRoleBadgeClass(role);
                return `
                    <tr>
                        <td class="td-primary">${escapeHtml(staff.username)}</td>
                        <td>${escapeHtml(staff.email)}</td>
                        <td><span class="badge ${badgeClass}">${escapeHtml(role)}</span></td>
                        <td>${escapeHtml(staff.created_at)}</td>
                    </tr>
                `;
            }).join('');
        } catch (error) {
            console.error(error);
            if (message) {
                message.textContent = error?.message || 'Unable to load staff list.';
            }
            const container = document.getElementById('staff-error');
            if (container) {
                container.style.display = 'flex';
            }
        }
    };
})();
