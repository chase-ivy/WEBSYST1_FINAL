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
            padding: 8px 16px;
            background-color: var(--primary);
            color: white;
            border: none;
            border-radius: var(--radius-sm);
            font-weight: 600;
            font-size: 13px;
            cursor: pointer;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }

        .btn-export:hover {
            background-color: var(--primary-dark);
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
<header class="topbar"><div class="topbar-brand">Gibraltar <span>AMS</span> Admin</div></header>
<div class="shell">
    <?php renderAdminSidebar('masterlist'); ?>
    <main class="main">
        <div class="page-header">
            <h1>Student Masterlist</h1>
            <p>View and export student information including LRN, names, sex, birth date, indigenous affiliation, and mother tongue.</p>
        </div>

        <section class="section">
            <div class="section-header">
                <h2>Filter & Export</h2>
                <p>Filter the masterlist by school year or section, then export as needed.</p>
            </div>
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
                    <button class="btn-export" onclick="exportToExcel()">Export to Excel</button>
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

    const url = new URL('../../api/endpoints/admin/get_masterlist.php', window.location.href);
    if (schoolYear) url.searchParams.set('school_year', schoolYear);
    if (sectionId) url.searchParams.set('section_id', sectionId);

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

// Initial load
document.addEventListener('DOMContentLoaded', loadMasterlist);
</script>
</body>
</html>
