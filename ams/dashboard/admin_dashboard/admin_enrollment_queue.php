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
        body {
            font-family: 'DM Sans', sans-serif;
            background-image: url('hallway.png');
            background-size: cover;
            background-position: center;
            background-attachment: fixed;
            background-color: #2a1a1a;
            color: var(--text);
            min-height: 100vh;
            font-size: 14px;
            line-height: 1.5;
        }
        .detail-card {
            background: rgba(255,255,255,0.06);
            border: 1px solid rgba(255,255,255,0.08);
            padding: 18px;
            border-radius: 16px;
            margin-top: 20px;
        }
        .detail-card h3 {
            margin-bottom: 12px;
        }
        .detail-grid {
            display: grid;
            grid-template-columns: repeat(2, minmax(0, 1fr));
            gap: 16px;
        }
        .detail-grid div {
            background: rgba(255,255,255,0.04);
            padding: 12px;
            border-radius: 12px;
        }
        .detail-grid .full {
            grid-column: span 2;
        }
    </style>
</head>
<body>
<header class="topbar"><div class="topbar-brand">Gibraltar <span>AMS</span> Admin</div></header>
<div class="shell">
    <?php renderAdminSidebar('enrollment_queue'); ?>
    <main class="main">
        <div class="page-header">
            <h1>Enrollment Queue</h1>
            <p>Review pending enrollment submissions and approve or reject them directly from the admin dashboard.</p>
        </div>

        <section class="section">
            <div class="section-header">
                <h2>Pending Enrollments</h2>
                <p>Filter the queue by school year and review each submission.</p>
            </div>
            <div class="section-body">
                <div style="display:flex; gap:12px; flex-wrap:wrap; align-items:flex-end; margin-bottom:16px;">
                    <div>
                        <label for="yearFilter">School Year</label>
                        <div class="select-wrap">
                            <select id="yearFilter">
                                <option value="">All school years</option>
                                <?php foreach ($schoolYears as $year): ?>
                                    <option value="<?php echo htmlspecialchars($year, ENT_QUOTES, 'UTF-8'); ?>"><?php echo htmlspecialchars($year, ENT_QUOTES, 'UTF-8'); ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                    </div>
                    <button id="refreshQueue" class="btn btn-primary">Refresh Queue</button>
                </div>

                <div id="queueMessage" class="message" style="display:none; margin-bottom:16px;"></div>
                <div class="table-wrap">
                    <table>
                        <thead>
                            <tr>
                                <th>#</th>
                                <th>Student</th>
                                <th>LRN</th>
                                <th>School Year</th>
                                <th>Grade Level</th>
                                <th>Status</th>
                                <th>Action</th>
                            </tr>
                        </thead>
                        <tbody id="queueBody">
                            <tr class="empty-row"><td colspan="7">Loading pending enrollments…</td></tr>
                        </tbody>
                    </table>
                </div>

                <div id="enrollmentDetails" class="detail-card" style="display:none;">
                    <h3>Enrollment Details</h3>
                    <div class="detail-grid" id="detailGrid"></div>
                    <div style="display:flex; gap:12px; flex-wrap:wrap; margin-top:16px;">
                        <button id="verifyBtn" class="btn btn-primary">Verify Enrollment</button>
                        <button id="rejectBtn" class="btn btn-danger">Reject Enrollment</button>
                        <button id="closeDetails" class="btn btn-secondary">Close</button>
                    </div>
                </div>
            </div>
        </section>
    </main>
</div>

<script src="../../api/client.js"></script>
<script>
    const queueBody = document.getElementById('queueBody');
    const queueMessage = document.getElementById('queueMessage');
    const enrollmentDetails = document.getElementById('enrollmentDetails');
    const detailGrid = document.getElementById('detailGrid');
    const verifyBtn = document.getElementById('verifyBtn');
    const rejectBtn = document.getElementById('rejectBtn');
    const closeDetails = document.getElementById('closeDetails');
    const yearFilter = document.getElementById('yearFilter');
    const refreshQueue = document.getElementById('refreshQueue');

    let currentEnrollmentId = null;

    function setMessage(type, message) {
        queueMessage.style.display = 'flex';
        queueMessage.className = 'message';
        queueMessage.classList.add(type === 'error' ? 'error' : 'success');
        queueMessage.textContent = message;
    }

    function clearMessage() {
        queueMessage.style.display = 'none';
        queueMessage.textContent = '';
    }

    function clearDetails() {
        currentEnrollmentId = null;
        enrollmentDetails.style.display = 'none';
        detailGrid.innerHTML = '';
    }

    async function loadQueue() {
        clearMessage();
        clearDetails();
        queueBody.innerHTML = '<tr class="empty-row"><td colspan="7">Loading pending enrollments…</td></tr>';

        try {
            const year = yearFilter.value;
            const response = await API.enrollment.queue(year || null, 'pending');
            if (!response.success) {
                throw new Error(response.error || 'Unable to load enrollments');
            }

            const items = response.data || [];
            if (items.length === 0) {
                queueBody.innerHTML = '<tr class="empty-row"><td colspan="7">No pending enrollments found.</td></tr>';
                return;
            }

            queueBody.innerHTML = items.map((item, index) => {
                return `
                    <tr>
                        <td>${index + 1}</td>
                        <td class="td-primary">${escapeHtml(item.first_name)} ${escapeHtml(item.last_name)}</td>
                        <td>${escapeHtml(item.lrn || 'N/A')}</td>
                        <td>${escapeHtml(item.school_year)}</td>
                        <td>${escapeHtml(item.grade_level)}</td>
                        <td>${escapeHtml(item.enrollment_status)}</td>
                        <td>
                            <button class="btn btn-secondary" type="button" onclick="showEnrollmentDetails(${item.enrollment_id})">Review</button>
                        </td>
                    </tr>
                `;
            }).join('');
        } catch (error) {
            queueBody.innerHTML = '<tr class="empty-row"><td colspan="7">Unable to load pending enrollments.</td></tr>';
            setMessage('error', error.message || 'Failed to load queue');
            console.error(error);
        }
    }

    function escapeHtml(value) {
        return String(value || '').replace(/&/g, '&amp;').replace(/</g, '&lt;').replace(/>/g, '&gt;').replace(/"/g, '&quot;').replace(/'/g, '&#039;');
    }

    async function showEnrollmentDetails(enrollmentId) {
        clearMessage();
        try {
            const response = await API.enrollment.get(enrollmentId);
            if (!response.success) {
                throw new Error(response.error || 'Unable to load enrollment details');
            }

            const data = response.data || {};
            const enrollment = data.enrollment || {};
            const medical = data.medical_info || {};

            currentEnrollmentId = enrollmentId;
            enrollmentDetails.style.display = 'block';
            detailGrid.innerHTML = `
                <div><strong>Student</strong><br>${escapeHtml(enrollment.first_name)} ${escapeHtml(enrollment.last_name)}</div>
                <div><strong>LRN</strong><br>${escapeHtml(enrollment.lrn || 'N/A')}</div>
                <div><strong>School Year</strong><br>${escapeHtml(enrollment.school_year)}</div>
                <div><strong>Grade Level</strong><br>${escapeHtml(enrollment.grade_level)}</div>
                <div><strong>Status</strong><br>${escapeHtml(enrollment.enrollment_status)}</div>
                <div><strong>Submitted</strong><br>${escapeHtml(enrollment.created_at || 'N/A')}</div>
                <div class="full"><strong>Medical Notes</strong><br>${escapeHtml(medical.medical_conditions || medical.treatment_notes || 'N/A')}</div>
            `;
        } catch (error) {
            setMessage('error', error.message || 'Failed to load details');
            console.error(error);
        }
    }

    async function verifyEnrollment() {
        if (!currentEnrollmentId) {
            setMessage('error', 'No enrollment selected.');
            return;
        }
        if (!confirm('Verify and archive this enrollment?')) {
            return;
        }

        try {
            const response = await API.enrollment.verify(currentEnrollmentId);
            if (response.success) {
                setMessage('success', 'Enrollment verified successfully.');
                await loadQueue();
            } else {
                setMessage('error', response.error || 'Failed to verify enrollment');
            }
        } catch (error) {
            setMessage('error', error.message || 'Failed to verify enrollment');
            console.error(error);
        }
    }

    async function rejectEnrollment() {
        if (!currentEnrollmentId) {
            setMessage('error', 'No enrollment selected.');
            return;
        }

        const reason = prompt('Provide a rejection reason for this enrollment:');
        if (reason === null) {
            return;
        }

        try {
            const response = await API.enrollment.reject(currentEnrollmentId, reason.trim() || null);
            if (response.success) {
                setMessage('success', 'Enrollment rejected successfully.');
                await loadQueue();
            } else {
                setMessage('error', response.error || 'Failed to reject enrollment');
            }
        } catch (error) {
            setMessage('error', error.message || 'Failed to reject enrollment');
            console.error(error);
        }
    }

    yearFilter.addEventListener('change', loadQueue);
    refreshQueue.addEventListener('click', loadQueue);
    closeDetails.addEventListener('click', clearDetails);
    verifyBtn.addEventListener('click', verifyEnrollment);
    rejectBtn.addEventListener('click', rejectEnrollment);

    document.addEventListener('DOMContentLoaded', loadQueue);
</script>
</body>
</html>
