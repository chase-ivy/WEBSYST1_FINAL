<?php
require_once __DIR__ . '/admin_nav.php';
require_once __DIR__ . '/../../login/auth.php';
require_special_admin();
require_once __DIR__ . '/../../config/config.php';

$schoolYears = [];
try {
    $stmt = $pdo->query("SELECT DISTINCT school_year FROM enrollments ORDER BY school_year DESC");
    $schoolYears = $stmt->fetchAll(PDO::FETCH_COLUMN);
} catch (Exception $e) {
    $schoolYears = [];
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Enrollment Queue | Admin Dashboard</title>
    <link rel="stylesheet" type="text/css" href="admin.css">
    <link href="https://fonts.googleapis.com/css2?family=Syne:wght@700;800&family=DM+Sans:wght@400;500;600&display=swap" rel="stylesheet">
    <style>
        /* ── Modal overlay ───────────────────────────────────── */
        .modal {
            display: none;
            position: fixed;
            inset: 0;
            background: rgba(0,0,0,0.45);
            z-index: 500;
            align-items: flex-start;
            justify-content: center;
            padding: 32px 16px;
            overflow-y: auto;
        }
        .modal.open { display: flex; }
        .modal-content {
            background: var(--surface);
            border-radius: var(--radius-lg);
            box-shadow: var(--shadow-md);
            width: 100%;
            max-width: 780px;
            display: flex;
            flex-direction: column;
            gap: 0;
            overflow: hidden;
            margin: auto;
        }
        .modal-header {
            display: flex;
            align-items: center;
            justify-content: space-between;
            padding: 18px 24px;
            border-bottom: 1px solid var(--border);
            background: var(--overlay);
        }
        .modal-header h2 {
            font-size: 16px;
            font-weight: 600;
            color: var(--text);
            margin: 0;
        }
        .modal-close {
            background: none;
            border: none;
            font-size: 20px;
            line-height: 1;
            cursor: pointer;
            color: var(--muted);
            padding: 4px 8px;
            border-radius: var(--radius-sm);
        }
        .modal-close:hover { background: var(--border); color: var(--text); }
        .modal-body { padding: 24px; display: flex; flex-direction: column; gap: 20px; }

        /* ── Review sections ─────────────────────────────────── */
        .review-section { display: flex; flex-direction: column; gap: 10px; }
        .review-section-title {
            font-size: 11px;
            font-weight: 600;
            letter-spacing: .06em;
            text-transform: uppercase;
            color: var(--muted);
            padding-bottom: 6px;
            border-bottom: 1px solid var(--border);
        }
        .review-grid {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(200px, 1fr));
            gap: 10px;
        }
        .review-field { display: flex; flex-direction: column; gap: 2px; }
        .review-field label {
            font-size: 11px;
            color: var(--muted);
            font-weight: 500;
        }
        .review-field span {
            font-size: 13px;
            color: var(--text);
        }
        .review-field.full { grid-column: 1 / -1; }

        /* ── Tag list (allergies, conditions, etc.) ──────────── */
        .tag-list { display: flex; flex-wrap: wrap; gap: 6px; margin-top: 2px; }
        .tag {
            background: var(--overlay);
            border: 1px solid var(--border);
            border-radius: var(--radius-full);
            padding: 2px 10px;
            font-size: 12px;
            color: var(--text-secondary);
        }
        .tag.none { color: var(--muted); font-style: italic; }

        /* ── Guardian cards ──────────────────────────────────── */
        .guardian-card {
            background: var(--overlay);
            border: 1px solid var(--border);
            border-radius: var(--radius-md);
            padding: 12px 14px;
        }
        .guardian-card .guardian-name {
            font-weight: 600;
            font-size: 13px;
            margin-bottom: 6px;
        }
        .guardian-card .guardian-meta {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(160px, 1fr));
            gap: 6px 12px;
            font-size: 12px;
            color: var(--text-secondary);
        }
        .guardian-card .guardian-meta span strong {
            color: var(--muted);
            font-weight: 500;
            display: block;
            font-size: 11px;
        }

        /* ── Status badge ────────────────────────────────────── */
        .badge {
            display: inline-block;
            padding: 2px 10px;
            border-radius: var(--radius-full);
            font-size: 11px;
            font-weight: 600;
            text-transform: uppercase;
            letter-spacing: .04em;
        }
        .badge-pending  { background: #fef9c3; color: #854d0e; border: 1px solid #fde68a; }
        .badge-verified { background: var(--success-bg); color: var(--success-text); border: 1px solid var(--success-border); }
        .badge-rejected { background: #fef2f2; color: #991b1b; border: 1px solid #fecaca; }

        /* ── Reject reason textarea ──────────────────────────── */
        .reject-reason-wrap { display: none; flex-direction: column; gap: 6px; }
        .reject-reason-wrap.visible { display: flex; }
        .reject-reason-wrap label { font-size: 12px; font-weight: 500; color: var(--text-secondary); }
        .reject-reason-wrap textarea {
            width: 100%;
            min-height: 72px;
            border: 1px solid var(--border);
            border-radius: var(--radius-md);
            padding: 8px 10px;
            font-size: 13px;
            font-family: inherit;
            resize: vertical;
            color: var(--text);
            background: var(--surface);
        }
        .reject-reason-wrap textarea:focus {
            outline: none;
            border-color: var(--brand);
            box-shadow: 0 0 0 3px rgba(78,3,3,.1);
        }

        /* ── Modal footer ────────────────────────────────────── */
        .modal-footer {
            padding: 16px 24px;
            border-top: 1px solid var(--border);
            display: flex;
            gap: 10px;
            flex-wrap: wrap;
            align-items: center;
        }
        .modal-footer .spacer { flex: 1; }
        #modalMessage { font-size: 13px; }

        /* ── Filter row ──────────────────────────────────────── */
        .filter-bar {
            display: flex;
            gap: 12px;
            flex-wrap: wrap;
            align-items: flex-end;
            margin-bottom: 16px;
        }
    </style>
</head>
<body>
<header class="topbar">
    <div class="topbar-brand">Gibraltar <span>AMS</span> Admin</div>
</header>
<div class="shell">
    <?php renderAdminSidebar('enrollment_queue'); ?>
    <main class="main">
        <div class="page-header">
            <h1>Enrollment Queue</h1>
            <p>Review, verify, or reject pending enrollment submissions.</p>
        </div>

        <section class="section">
            <div class="section-header">
                <h2>Pending Enrollments</h2>
                <p>Click <strong>Review</strong> to inspect a full submission before taking action.</p>
            </div>
            <div class="section-body">

                <div class="filter-bar">
                    <div class="form-group" style="margin:0;">
                        <label for="yearFilter">School Year</label>
                        <div class="select-wrap">
                            <select id="yearFilter">
                                <option value="">All school years</option>
                                <?php foreach ($schoolYears as $year): ?>
                                    <option value="<?php echo htmlspecialchars($year, ENT_QUOTES, 'UTF-8'); ?>">
                                        <?php echo htmlspecialchars($year, ENT_QUOTES, 'UTF-8'); ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                    </div>
                    <div class="form-group" style="margin:0;">
                        <label for="statusFilter">Status</label>
                        <div class="select-wrap">
                            <select id="statusFilter">
                                <option value="pending">Pending</option>
                                <option value="verified">Verified</option>
                                <option value="rejected">Rejected</option>
                                <option value="">All</option>
                            </select>
                        </div>
                    </div>
                    <button id="refreshQueue" class="btn btn-primary">Refresh</button>
                </div>

                <div id="queueMessage" class="message" style="display:none; margin-bottom:16px;"></div>

                <div class="table-wrap">
                    <table>
                        <thead>
                            <tr>
                                <th>#</th>
                                <th>Queue No.</th>
                                <th>Student</th>
                                <th>LRN</th>
                                <th>School Year</th>
                                <th>Grade Level</th>
                                <th>Status</th>
                                <th>Submitted</th>
                                <th>Action</th>
                            </tr>
                        </thead>
                        <tbody id="queueBody">
                            <tr class="empty-row"><td colspan="9">Loading enrollments…</td></tr>
                        </tbody>
                    </table>
                </div>

            </div>
        </section>
    </main>
</div>

<!-- ── Review Modal ──────────────────────────────────────────── -->
<div id="reviewModal" class="modal" role="dialog" aria-modal="true" aria-labelledby="modalTitle">
    <div class="modal-content">
        <div class="modal-header">
            <h2 id="modalTitle">Enrollment Review</h2>
            <button class="modal-close" id="modalClose" title="Close">&times;</button>
        </div>

        <div class="modal-body" id="modalBody">
            <p style="color:var(--muted);">Loading…</p>
        </div>

        <div class="modal-footer">
            <div id="modalMessage" class="message" style="display:none; flex:1;"></div>
            <div class="spacer"></div>
            <div id="rejectReasonWrap" class="reject-reason-wrap" style="flex:0 0 100%; order:-1;">
                <label for="rejectReasonText">Rejection reason <span style="color:var(--muted);">(optional)</span></label>
                <textarea id="rejectReasonText" placeholder="Describe why this enrollment is being rejected…"></textarea>
            </div>
            <button id="btnConfirmReject" class="btn btn-danger" style="display:none;">Confirm Rejection</button>
            <button id="btnReject"  class="btn btn-danger"   style="display:none;">Reject Enrollment</button>
            <button id="btnVerify"  class="btn btn-primary"  style="display:none;">Verify Enrollment</button>
            <button id="btnCancel"  class="btn btn-secondary" id="modalClose2">Close</button>
        </div>
    </div>
</div>

<script src="../../api/client.js?v=2"></script>
<script>
// ── State ────────────────────────────────────────────────────
let currentEnrollmentId = null;
let rejectPending = false;

// ── DOM refs ─────────────────────────────────────────────────
const queueBody       = document.getElementById('queueBody');
const queueMessage    = document.getElementById('queueMessage');
const yearFilter      = document.getElementById('yearFilter');
const statusFilter    = document.getElementById('statusFilter');
const refreshQueue    = document.getElementById('refreshQueue');
const modal           = document.getElementById('reviewModal');
const modalBody       = document.getElementById('modalBody');
const modalMessage    = document.getElementById('modalMessage');
const btnVerify       = document.getElementById('btnVerify');
const btnReject       = document.getElementById('btnReject');
const btnConfirmReject= document.getElementById('btnConfirmReject');
const btnCancel       = document.getElementById('btnCancel');
const modalClose      = document.getElementById('modalClose');
const rejectReasonWrap= document.getElementById('rejectReasonWrap');
const rejectReasonText= document.getElementById('rejectReasonText');

// ── Queue loading ────────────────────────────────────────────
function setQueueMessage(type, msg) {
    queueMessage.style.display = 'flex';
    queueMessage.className = 'message ' + (type === 'error' ? 'error' : 'success');
    queueMessage.textContent = msg;
}
function clearQueueMessage() {
    queueMessage.style.display = 'none';
}

async function loadQueue() {
    clearQueueMessage();
    queueBody.innerHTML = '<tr class="empty-row"><td colspan="9">Loading…</td></tr>';
    try {
        const year   = yearFilter.value || null;
        const status = statusFilter.value || null;
        const response = await API.enrollment.queue(year, status || 'pending');
        if (!response.success) throw new Error(response.error || 'Failed to load');
        const items = response.data || [];
        if (items.length === 0) {
            queueBody.innerHTML = '<tr class="empty-row"><td colspan="9">No enrollments found.</td></tr>';
            return;
        }
        queueBody.innerHTML = items.map((item, i) => `
            <tr>
                <td>${i + 1}</td>
                <td>${esc(item.queue_number ?? '—')}</td>
                <td class="td-primary">${esc(item.last_name)}, ${esc(item.first_name)}</td>
                <td>${esc(item.lrn || '—')}</td>
                <td>${esc(item.school_year)}</td>
                <td>${esc(item.grade_level)}</td>
                <td>${badge(item.enrollment_status)}</td>
                <td>${formatDate(item.created_at)}</td>
                <td class="td-actions">
                    <button class="btn btn-secondary btn-sm" onclick="openReview(${item.enrollment_id}, this)">Review</button>
                </td>
            </tr>
        `).join('');
    } catch (err) {
        queueBody.innerHTML = '<tr class="empty-row"><td colspan="9">Failed to load enrollments.</td></tr>';
        setQueueMessage('error', err.message);
    }
}

// ── Modal helpers ────────────────────────────────────────────
function openModal() { modal.classList.add('open'); }
function closeModal() {
    modal.classList.remove('open');
    currentEnrollmentId = null;
    rejectPending = false;
    rejectReasonWrap.classList.remove('visible');
    rejectReasonText.value = '';
    btnReject.style.display = 'none';
    btnVerify.style.display = 'none';
    btnConfirmReject.style.display = 'none';
    clearModalMessage();
}
function setModalMessage(type, msg) {
    modalMessage.style.display = 'flex';
    modalMessage.className = 'message ' + (type === 'error' ? 'error' : 'success');
    modalMessage.textContent = msg;
}
function clearModalMessage() {
    modalMessage.style.display = 'none';
}

// ── Build review body ────────────────────────────────────────
function buildModalBody(data) {
    const e  = data.enrollment  || {};
    const m  = data.medical_info || {};
    const addresses   = data.addresses        || [];
    const guardians   = data.guardians        || [];
    const allergies   = data.allergies        || [];
    const conditions  = data.conditions       || [];
    const surgeries   = data.surgeries        || [];
    const treatments  = data.treatments       || [];
    const famHistory  = data.family_history   || [];
    const disabilities= data.disabilities     || [];
    const returning   = data.returning_learner;

    // ── Enrollment info
    let html = `
    <div class="review-section">
        <div class="review-section-title">Enrollment Information</div>
        <div class="review-grid">
            <div class="review-field"><label>Status</label><span>${badge(e.enrollment_status)}</span></div>
            <div class="review-field"><label>School Year</label><span>${esc(val(e.school_year))}</span></div>
            <div class="review-field"><label>Grade Level</label><span>${esc(val(e.grade_level))}</span></div>
            <div class="review-field"><label>Queue No.</label><span>${esc(val(e.queue_number))}</span></div>
            <div class="review-field"><label>Submitted</label><span>${formatDate(e.created_at)}</span></div>
            ${e.enrollment_status === 'verified' ? `<div class="review-field"><label>Verified At</label><span>${formatDate(e.verified_at)}</span></div>` : ''}
            ${e.enrollment_status === 'rejected' ? `
            <div class="review-field"><label>Rejected At</label><span>${formatDate(e.rejected_at)}</span></div>
            <div class="review-field full"><label>Rejection Reason</label><span>${esc(val(e.rejection_reason, 'No reason provided'))}</span></div>
            ` : ''}
        </div>
    </div>

    <div class="review-section">
        <div class="review-section-title">Student Information</div>
        <div class="review-grid">
            <div class="review-field"><label>Last Name</label><span>${esc(val(e.last_name))}</span></div>
            <div class="review-field"><label>First Name</label><span>${esc(val(e.first_name))}</span></div>
            <div class="review-field"><label>Middle Name</label><span>${esc(val(e.middle_name))}</span></div>
            <div class="review-field"><label>Extension</label><span>${esc(val(e.extension_name))}</span></div>
            <div class="review-field"><label>Sex</label><span>${esc(val(e.sex))}</span></div>
            <div class="review-field"><label>Birth Date</label><span>${formatDate(e.birth_date)}</span></div>
            <div class="review-field"><label>Place of Birth</label><span>${esc(val(e.place_of_birth))}</span></div>
            <div class="review-field"><label>LRN</label><span>${esc(val(e.lrn))}</span></div>
            <div class="review-field"><label>PSA / BCN</label><span>${esc(val(e.psa_bcn))}</span></div>
            <div class="review-field"><label>4Ps Household ID</label><span>${esc(val(e.four_ps_household_id))}</span></div>
        </div>
    </div>`;

    // ── Addresses
    if (addresses.length > 0) {
        html += `<div class="review-section"><div class="review-section-title">Addresses</div>`;
        addresses.forEach(a => {
            html += `
            <div class="review-grid" style="margin-bottom:8px;">
                <div class="review-field"><label>Type</label><span>${esc(val(a.address_type))}</span></div>
                <div class="review-field full"><label>Address</label><span>${esc([a.house_no, a.street, a.barangay, a.municipality, a.province, a.region].filter(Boolean).join(', ') || '—')}</span></div>
            </div>`;
        });
        html += `</div>`;
    }

    // ── Guardians
    if (guardians.length > 0) {
        html += `<div class="review-section"><div class="review-section-title">Parent / Guardian</div>`;
        guardians.forEach(g => {
            html += `
            <div class="guardian-card">
                <div class="guardian-name">${esc(val(g.last_name))}, ${esc(val(g.first_name))} ${esc(val(g.middle_name, ''))} — <em style="font-weight:400;color:var(--muted);">${esc(val(g.relationship))}</em></div>
                <div class="guardian-meta">
                    <span><strong>Contact</strong>${esc(val(g.contact_number))}</span>
                    <span><strong>Occupation</strong>${esc(val(g.occupation))}</span>
                    <span><strong>Education</strong>${esc(val(g.educational_attainment))}</span>
                </div>
            </div>`;
        });
        html += `</div>`;
    }

    // ── Returning learner
    if (returning) {
        html += `
        <div class="review-section">
            <div class="review-section-title">Returning Learner</div>
            <div class="review-grid">
                <div class="review-field"><label>Last School Attended</label><span>${esc(val(returning.last_school_attended))}</span></div>
                <div class="review-field"><label>Last Grade / Year</label><span>${esc(val(returning.last_grade_level))}</span></div>
                <div class="review-field"><label>School Year</label><span>${esc(val(returning.school_year))}</span></div>
                <div class="review-field"><label>School ID</label><span>${esc(val(returning.school_id))}</span></div>
            </div>
        </div>`;
    }

    // ── Disabilities
    html += `
    <div class="review-section">
        <div class="review-section-title">Disability / Special Needs</div>
        <div class="tag-list">${
            disabilities.length === 0
                ? '<span class="tag none">None declared</span>'
                : disabilities.map(d => `<span class="tag">${esc(d.type_name || '—')}${d.subtype_name ? ' › ' + esc(d.subtype_name) : ''}</span>`).join('')
        }</div>
    </div>`;

    // ── Medical info
    html += `
    <div class="review-section">
        <div class="review-section-title">Medical Information</div>
        <div class="review-grid" style="margin-bottom:10px;">
            <div class="review-field"><label>Exposed to Smoke / Vape</label><span>${m.exposed_to_cigarette_vape_smoke == 1 ? 'Yes' : 'No'}</span></div>
            ${m.other_pertinent_information ? `<div class="review-field full"><label>Other Notes</label><span>${esc(m.other_pertinent_information)}</span></div>` : ''}
        </div>
        <div class="review-grid">
            <div class="review-field full"><label>Allergies</label><div class="tag-list">${tags(allergies, 'allergy_name')}</div></div>
            <div class="review-field full"><label>Medical Conditions</label><div class="tag-list">${tags(conditions, 'condition_name')}</div></div>
            <div class="review-field full"><label>Past Surgeries</label><div class="tag-list">${
                surgeries.length === 0
                    ? '<span class="tag none">None</span>'
                    : surgeries.map(s => `<span class="tag">${esc(s.body_part)} @ ${esc(s.hospital_name)} (${formatDate(s.surgery_date)})</span>`).join('')
            }</div></div>
            <div class="review-field full"><label>Current Treatments</label><div class="tag-list">${
                treatments.length === 0
                    ? '<span class="tag none">None</span>'
                    : treatments.map(t => `<span class="tag">${esc(t.treatment_medicine)} — ${esc(t.schedule_dosage)}</span>`).join('')
            }</div></div>
            <div class="review-field full"><label>Family Medical History</label><div class="tag-list">${tags(famHistory, 'family_history_name')}</div></div>
        </div>
    </div>`;

    return html;
}

// ── Open review modal ────────────────────────────────────────
async function openReview(enrollmentId, btn) {
    currentEnrollmentId = enrollmentId;
    rejectPending = false;
    rejectReasonWrap.classList.remove('visible');
    rejectReasonText.value = '';
    btnReject.style.display = 'none';
    btnVerify.style.display = 'none';
    btnConfirmReject.style.display = 'none';
    clearModalMessage();
    modalBody.innerHTML = '<p style="color:var(--muted);">Loading enrollment details…</p>';
    openModal();

    const origText = btn ? btn.textContent : null;
    if (btn) {
        btn.disabled = true;
        btn.textContent = 'Loading…';
    }

    try {
        const response = await API.enrollment.get(enrollmentId);
        if (!response.success) throw new Error(response.error || 'Failed to load');
        const data = response.data || {};
        modalBody.innerHTML = buildModalBody(data);

        const status = (data.enrollment || {}).enrollment_status;
        if (status === 'pending') {
            btnVerify.style.display = '';
            btnReject.style.display = '';
        }
    } catch (err) {
        modalBody.innerHTML = `<p style="color:#991b1b;">Error: ${esc(err.message)}</p>`;
    }
    finally {
        if (btn) {
            btn.disabled = false;
            btn.textContent = origText;
        }
    }
}

// ── Verify ───────────────────────────────────────────────────
btnVerify.addEventListener('click', async () => {
    if (!currentEnrollmentId) return;
    if (!confirm('Verify this enrollment? This will create permanent school and medical records.')) return;
    btnVerify.disabled = true;
    btnReject.disabled = true;
    clearModalMessage();
    try {
        const res = await API.enrollment.verify(currentEnrollmentId);
        if (res.success) {
            setModalMessage('success', 'Enrollment verified. Permanent records created.');
            btnVerify.style.display = 'none';
            btnReject.style.display = 'none';
            await loadQueue();
        } else {
            setModalMessage('error', res.error || 'Verification failed.');
        }
    } catch (err) {
        setModalMessage('error', err.message || 'Verification failed.');
    } finally {
        btnVerify.disabled = false;
        btnReject.disabled = false;
    }
});

// ── Reject (two-step) ─────────────────────────────────────────
btnReject.addEventListener('click', () => {
    rejectPending = true;
    rejectReasonWrap.classList.add('visible');
    btnConfirmReject.style.display = '';
    btnReject.style.display = 'none';
    btnVerify.style.display = 'none';
    rejectReasonText.focus();
});

btnConfirmReject.addEventListener('click', async () => {
    if (!currentEnrollmentId) return;
    const reason = rejectReasonText.value.trim() || null;
    btnConfirmReject.disabled = true;
    clearModalMessage();
    try {
        const res = await API.enrollment.reject(currentEnrollmentId, reason);
        if (res.success) {
            setModalMessage('success', 'Enrollment rejected.');
            btnConfirmReject.style.display = 'none';
            rejectReasonWrap.classList.remove('visible');
            await loadQueue();
        } else {
            setModalMessage('error', res.error || 'Rejection failed.');
        }
    } catch (err) {
        setModalMessage('error', err.message || 'Rejection failed.');
    } finally {
        btnConfirmReject.disabled = false;
    }
});

// ── Close handlers ───────────────────────────────────────────
modalClose.addEventListener('click', closeModal);
btnCancel.addEventListener('click', closeModal);
modal.addEventListener('click', e => { if (e.target === modal) closeModal(); });
document.addEventListener('keydown', e => { if (e.key === 'Escape') closeModal(); });

// ── Filter/refresh ───────────────────────────────────────────
yearFilter.addEventListener('change', loadQueue);
statusFilter.addEventListener('change', loadQueue);
refreshQueue.addEventListener('click', loadQueue);

document.addEventListener('DOMContentLoaded', loadQueue);
</script>
</body>
</html>