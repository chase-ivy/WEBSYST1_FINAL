<?php
require_once __DIR__ . '/admin_nav.php';
require_once __DIR__ . '/../../login/auth.php';
require_special_admin();
require_once __DIR__ . '/../../config/config.php';

// Get available school years for filtering
$schoolYears = [];
try {
    $stmt = $pdo->query("SELECT DISTINCT school_year FROM enrollments ORDER BY school_year DESC");
    $schoolYears = $stmt->fetchAll(PDO::FETCH_COLUMN);
} catch (Exception $e) {
    $schoolYears = [];
}

// Get available sections for filtering
$sections = [];
try {
    $stmt = $pdo->query("SELECT section_id, section_name FROM sections ORDER BY section_name ASC");
    $sections = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (Exception $e) {
    $sections = [];
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Student Masterlist | Admin Dashboard</title>
    <link rel="stylesheet" type="text/css" href="admin.css">
    <link href="https://fonts.googleapis.com/css2?family=Syne:wght@700;800&family=DM+Sans:wght@400;500;600&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="../mobile-nav.css">
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

        .filter-controls {
            display: flex;
            gap: 16px;
            flex-wrap: wrap;
            align-items: center;
            margin-bottom: 24px;
        }

        .filter-controls > div {
            display: flex;
            gap: 8px;
            align-items: center;
        }

        .filter-controls label {
            font-weight: 600;
            color: var(--muted);
            text-transform: uppercase;
            font-size: 12px;
            letter-spacing: 0.5px;
        }

        .filter-controls select {
            padding: 8px 12px;
            border: 1px solid var(--border);
            border-radius: var(--radius-sm);
            background-color: var(--surface);
            color: var(--text);
            font-family: inherit;
            font-size: 13px;
            cursor: pointer;
        }

        .btn-export {
            padding: 10px 18px;
            background-color: #1a73e8;
            color: white;
            border: 1px solid rgba(255,255,255,0.8);
            border-radius: 999px;
            font-weight: 700;
            font-size: 13px;
            cursor: pointer;
            text-transform: uppercase;
            letter-spacing: 0.75px;
            min-width: 170px;
            transition: background-color 0.2s ease, transform 0.2s ease;
        }

        .btn-export:hover {
            background-color: #1664c0;
            transform: translateY(-1px);
        }

        .table-wrap {
            overflow-x: auto;
            border-radius: var(--radius-lg);
            box-shadow: var(--shadow-sm);
        }

        .data-table {
            width: 100%;
            border-collapse: collapse;
            background-color: var(--surface);
        }

        .data-table thead {
            background-color: var(--surface-low);
            border-bottom: 2px solid var(--border);
        }

        .data-table th {
            padding: 12px 16px;
            text-align: left;
            font-weight: 600;
            color: var(--muted);
            text-transform: uppercase;
            font-size: 11px;
            letter-spacing: 0.5px;
        }

        .data-table tbody tr {
            border-bottom: 1px solid var(--border);
        }

        .data-table tbody tr:hover {
            background-color: var(--surface-low);
        }

        .data-table td {
            padding: 12px 16px;
            font-size: 13px;
        }

        .td-primary {
            font-weight: 600;
            color: var(--primary);
        }

        .loading {
            text-align: center;
            padding: 24px;
            color: var(--muted);
        }

        .error {
            background-color: #fee;
            color: #a44;
            padding: 12px 16px;
            border-radius: var(--radius-sm);
            margin-bottom: 16px;
        }
    </style>
</head>
<body>
<header class="topbar">
    <button class="mob-menu-btn"
            aria-label="Open menu"
            aria-expanded="false"
            aria-controls="main-sidebar">
        <svg viewBox="0 0 24 24" aria-hidden="true">
            <line x1="3" y1="6"  x2="21" y2="6"/>
            <line x1="3" y1="12" x2="21" y2="12"/>
            <line x1="3" y1="18" x2="21" y2="18"/>
        </svg>
    </button>
    <div class="topbar-brand">Gibraltar <span>AMES</span></div>
    <span class="topbar-label">Admin Panel</span>
</header>
<div class="shell">
    <?php renderAdminSidebar('masterlist'); ?>
    <main class="main">
        <div class="page-header">
            <h1>Student Masterlist</h1>
            <p>View and export student information including LRN, names, sex, birth date, indigenous affiliation, and mother tongue.</p>
        </div>

        <section class="section">
            <div class="section-body">
                <div class="filter-controls">
                    <div>
                        <label for="schoolYearFilter">School Year</label>
                        <select id="schoolYearFilter">
                            <option value="">All</option>
                            <?php foreach ($schoolYears as $year): ?>
                                <option value="<?php echo htmlspecialchars($year); ?>"><?php echo htmlspecialchars($year); ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div>
                        <label for="sectionFilter">Section</label>
                        <select id="sectionFilter">
                            <option value="">All</option>
                            <?php foreach ($sections as $section): ?>
                                <option value="<?php echo intval($section['section_id']); ?>"><?php echo htmlspecialchars($section['section_name']); ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div>
                        <label for="sexFilter">Gender</label>
                        <select id="sexFilter">
                            <option value="">All</option>
                            <option value="Female">Female</option>
                            <option value="Male">Male</option>
                        </select>
                    </div>
                    <button type="button" class="btn-export" onclick="exportToExcel()">Download CSV</button>
                </div>

                <div id="messageContainer"></div>

                <div class="table-wrap">
                    <table class="data-table" id="masterlistTable">
                        <thead>
                            <tr>
                                <th>LRN</th>
                                <th>Last Name</th>
                                <th>First Name</th>
                                <th>Middle Name</th>
                                <th>Sex</th>
                                <th>Birth Date</th>
                                <th>Indigenous People</th>
                                <th>Mother Tongue</th>
                            </tr>
                        </thead>
                        <tbody id="masterlistBody">
                            <tr class="loading"><td colspan="8">Loading masterlist...</td></tr>
                        </tbody>
                    </table>
                </div>
            </div>
        </section>
    </main>
</div>

<script>
let currentMasterlistData = [];

async function loadMasterlist() {
    const schoolYear = document.getElementById('schoolYearFilter').value;
    const sectionId = document.getElementById('sectionFilter').value;
    const sex = document.getElementById('sexFilter').value;

    const url = new URL('../../api/endpoints/admin/get_masterlist.php', window.location.href);
    if (schoolYear) url.searchParams.set('school_year', schoolYear);
    if (sectionId) url.searchParams.set('section_id', sectionId);
    if (sex) url.searchParams.set('sex', sex);

    try {
        const response = await fetch(url);
        if (!response.ok) {
            const text = await response.text();
            throw new Error(`${response.status} ${response.statusText}${text ? ' - ' + text : ''}`);
        }

        const json = await response.json();
        if (!json.success) throw new Error(json.error || 'Unknown error');

        currentMasterlistData = json.data;
        renderMasterlist();
    } catch (error) {
        showMessage('error', 'Failed to load masterlist: ' + error.message);
        console.error(error);
    }
}

function renderMasterlist() {
    const tbody = document.getElementById('masterlistBody');
    if (currentMasterlistData.length === 0) {
        tbody.innerHTML = '<tr class="loading"><td colspan="8">No students found.</td></tr>';
        return;
    }

    tbody.innerHTML = currentMasterlistData.map(student => `
        <tr>
            <td class="td-primary">${escapeHtml(student.lrn || 'N/A')}</td>
            <td>${escapeHtml(student.last_name || '')}</td>
            <td>${escapeHtml(student.first_name || '')}</td>
            <td>${escapeHtml(student.middle_name || '')}</td>
            <td>${escapeHtml(student.sex || '')}</td>
            <td>${student.birth_date ? new Date(student.birth_date).toLocaleDateString() : 'N/A'}</td>
            <td>${escapeHtml(student.indigenous_people || 'Not Specified')}</td>
            <td>${escapeHtml(student.mother_tongue || 'Not Specified')}</td>
        </tr>
    `).join('');
}

function escapeHtml(text) {
    const map = {
        '&': '&amp;',
        '<': '&lt;',
        '>': '&gt;',
        '"': '&quot;',
        "'": '&#039;'
    };
    return String(text).replace(/[&<>"']/g, m => map[m]);
}

function showMessage(type, message) {
    const container = document.getElementById('messageContainer');
    container.className = type;
    container.textContent = message;
    if (type === 'success') {
        setTimeout(() => { container.textContent = ''; }, 3000);
    }
}

function exportToExcel() {
    if (currentMasterlistData.length === 0) {
        showMessage('error', 'No data to export.');
        return;
    }

    // Create CSV format
    const headers = ['LRN', 'Last Name', 'First Name', 'Middle Name', 'Sex', 'Birth Date', 'Indigenous People', 'Mother Tongue'];
    const rows = currentMasterlistData.map(student => [
        student.lrn || '',
        student.last_name || '',
        student.first_name || '',
        student.middle_name || '',
        student.sex || '',
        student.birth_date || '',
        student.indigenous_people || '',
        student.mother_tongue || ''
    ]);

    const csvContent = [
        headers.join(','),
        ...rows.map(row => row.map(cell => `"${cell}"`).join(','))
    ].join('\n');

    // Download as CSV
    const blob = new Blob([csvContent], { type: 'text/csv;charset=utf-8;' });
    const link = document.createElement('a');
    link.href = URL.createObjectURL(blob);
    link.download = `masterlist_${new Date().toISOString().split('T')[0]}.csv`;
    link.click();
}

// Event listeners
document.getElementById('schoolYearFilter').addEventListener('change', loadMasterlist);
document.getElementById('sectionFilter').addEventListener('change', loadMasterlist);
document.getElementById('sexFilter').addEventListener('change', loadMasterlist);

// Initial load
document.addEventListener('DOMContentLoaded', loadMasterlist);

</script>
<div class="mob-overlay" id="mob-overlay" aria-hidden="true"></div>
<script src="../mobile-nav.js"></script>
</body>
</html>
