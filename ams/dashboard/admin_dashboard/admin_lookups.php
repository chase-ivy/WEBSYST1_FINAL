<?php
require_once __DIR__ . '/admin_nav.php';
require_once __DIR__ . '/../../login/auth.php';
require_once __DIR__ . '/../../config/config.php';
require_special_admin();

$allowedTables = [
    'disability_types' => 'Disability Types',
    'disability_subtypes' => 'Disability Subtypes',
    'family_medical_history_types' => 'Family Medical History Types',
    'indigenous_groups' => 'Indigenous Groups',
    'medical_allergy_types' => 'Medical Allergy Types',
    'medical_condition_types' => 'Medical Condition Types',
    'mother_tongues' => 'Mother Tongues',
    'parent_guardian_types' => 'Parent/Guardian Types',
    'subjects' => 'Subjects',
];

$table = $_POST['table'] ?? $_GET['table'] ?? 'disability_types';
if (!array_key_exists($table, $allowedTables)) {
    $table = 'disability_types';
}

function getPrimaryKey(PDO $pdo, string $table): ?string {
    $stmt = $pdo->prepare('SELECT COLUMN_NAME FROM information_schema.columns WHERE table_schema = DATABASE() AND table_name = ? AND COLUMN_KEY = ? LIMIT 1');
    $stmt->execute([$table, 'PRI']);
    $row = $stmt->fetch(PDO::FETCH_ASSOC);
    return $row ? $row['COLUMN_NAME'] : null;
}

function sanitizeTable(string $table, array $allowedTables): string {
    return array_key_exists($table, $allowedTables) ? $table : 'disability_types';
}

$table = sanitizeTable($table, $allowedTables);
$primaryKey = getPrimaryKey($pdo, $table);
if ($primaryKey === null) {
    die('Unable to determine primary key for table: ' . htmlspecialchars($table, ENT_QUOTES, 'UTF-8'));
}

$displayField = 'name';

$errors = [];
$success = '';
$editItem = null;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? '';
    $itemName = trim($_POST['name'] ?? '');
    $itemId = intval($_POST[$primaryKey] ?? 0);

    if ($action === 'create') {
        if ($itemName === '') {
            $errors[] = 'Name is required.';
        }
        if (empty($errors)) {
            $insertStmt = $pdo->prepare("INSERT INTO $table ($displayField) VALUES (?)");
            $insertStmt->execute([$itemName]);
            $success = 'Item created successfully.';
        }
    }

    if ($action === 'update') {
        if ($itemId <= 0) {
            $errors[] = 'Valid record ID is required.';
        }
        if ($itemName === '') {
            $errors[] = 'Name is required.';
        }
        if (empty($errors)) {
            $updateStmt = $pdo->prepare("UPDATE $table SET $displayField = ? WHERE $primaryKey = ?");
            $updateStmt->execute([$itemName, $itemId]);
            $success = 'Item updated successfully.';
        }
    }

    if ($action === 'delete') {
        if ($itemId <= 0) {
            $errors[] = 'Valid record ID is required.';
        }
        if (empty($errors)) {
            $deleteStmt = $pdo->prepare("DELETE FROM $table WHERE $primaryKey = ?");
            $deleteStmt->execute([$itemId]);
            $success = 'Item deleted successfully.';
        }
    }
}

if (!empty($_GET['edit_id'])) {
    $editId = intval($_GET['edit_id']);
    if ($editId > 0) {
        $stmt = $pdo->prepare("SELECT * FROM $table WHERE $primaryKey = ? LIMIT 1");
        $stmt->execute([$editId]);
        $editItem = $stmt->fetch(PDO::FETCH_ASSOC) ?: null;
        if (!$editItem) {
            $errors[] = 'Item not found.';
        }
    }
}

$stmt = $pdo->query("SELECT * FROM $table ORDER BY $displayField ASC");
$items = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Lookup Tables | Admin Dashboard</title>
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
    </style>
</head>
<body>
<header class="topbar"><div class="topbar-brand">Gibraltar <span>AMS</span> Admin</div></header>
<div class="shell">
    <?php renderAdminSidebar('lookups'); ?>
    <main class="main">
        <div class="page-header">
            <h1>Lookup Table Management</h1>
            <p>Manage canonical lookup data used throughout enrollment and student records.</p>
        </div>

        <section class="section">
            <div class="section-header">
                <h2>Lookup Selector</h2>
                <p>Choose which lookup table to manage.</p>
            </div>
            <div class="section-body">
                <form method="get" style="display:flex; gap:12px; flex-wrap:wrap; align-items:center;">
                    <label for="table">Table</label>
                    <div class="select-wrap">
                        <select id="table" name="table" onchange="this.form.submit()">
                            <?php foreach ($allowedTables as $tableKey => $label): ?>
                                <option value="<?php echo htmlspecialchars($tableKey, ENT_QUOTES, 'UTF-8'); ?>" <?php echo $tableKey === $table ? 'selected' : ''; ?>><?php echo htmlspecialchars($label, ENT_QUOTES, 'UTF-8'); ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                </form>
            </div>
        </section>

        <section class="section">
            <div class="section-header">
                <h2><?php echo htmlspecialchars($allowedTables[$table], ENT_QUOTES, 'UTF-8'); ?></h2>
                <p>Create, update, or delete values in the <?php echo htmlspecialchars($allowedTables[$table], ENT_QUOTES, 'UTF-8'); ?> table.</p>
            </div>
            <div class="section-body">
                <?php if ($success !== ''): ?>
                    <div class="alert alert-success"><?php echo htmlspecialchars($success, ENT_QUOTES, 'UTF-8'); ?></div>
                <?php endif; ?>
                <?php if (!empty($errors)): ?>
                    <div class="alert alert-error"><ul><?php foreach ($errors as $error) echo '<li>' . htmlspecialchars($error, ENT_QUOTES, 'UTF-8') . '</li>'; ?></ul></div>
                <?php endif; ?>

                <form method="post" class="form-grid">
                    <input type="hidden" name="action" value="<?php echo $editItem ? 'update' : 'create'; ?>">
                    <input type="hidden" name="table" value="<?php echo htmlspecialchars($table, ENT_QUOTES, 'UTF-8'); ?>">
                    <?php if ($editItem): ?>
                        <input type="hidden" name="<?php echo htmlspecialchars($primaryKey, ENT_QUOTES, 'UTF-8'); ?>" value="<?php echo intval($editItem[$primaryKey]); ?>">
                    <?php endif; ?>

                    <div class="form-group">
                        <label for="name">Name</label>
                        <input id="name" name="name" type="text" value="<?php echo $editItem ? htmlspecialchars($editItem[$displayField], ENT_QUOTES, 'UTF-8') : ''; ?>" required>
                    </div>

                    <div class="form-actions full">
                        <button type="submit" class="btn-primary"><?php echo $editItem ? 'Save Changes' : 'Create Item'; ?></button>
                        <?php if ($editItem): ?>
                            <a href="admin_lookups.php?table=<?php echo htmlspecialchars($table, ENT_QUOTES, 'UTF-8'); ?>" class="btn-secondary">Cancel</a>
                        <?php endif; ?>
                    </div>
                </form>

                <div class="table-wrap" style="margin-top:24px;">
                    <table class="data-table">
                        <thead>
                            <tr>
                                <th>ID</th>
                                <th>Name</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if (empty($items)): ?>
                                <tr class="empty-row"><td colspan="3">No records found.</td></tr>
                            <?php else: ?>
                                <?php foreach ($items as $item): ?>
                                    <tr>
                                        <td><?php echo intval($item[$primaryKey]); ?></td>
                                        <td class="td-primary"><?php echo htmlspecialchars($item[$displayField], ENT_QUOTES, 'UTF-8'); ?></td>
                                        <td class="td-actions">
                                            <a class="btn-small btn-primary" href="admin_lookups.php?table=<?php echo htmlspecialchars($table, ENT_QUOTES, 'UTF-8'); ?>&edit_id=<?php echo intval($item[$primaryKey]); ?>">Edit</a>
                                            <form method="post" style="display:inline-block; margin-left:6px;" onsubmit="return confirm('Delete this item?');">
                                                <input type="hidden" name="action" value="delete">
                                                <input type="hidden" name="table" value="<?php echo htmlspecialchars($table, ENT_QUOTES, 'UTF-8'); ?>">
                                                <input type="hidden" name="<?php echo htmlspecialchars($primaryKey, ENT_QUOTES, 'UTF-8'); ?>" value="<?php echo intval($item[$primaryKey]); ?>">
                                                <button type="submit" class="btn-small btn-danger">Delete</button>
                                            </form>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </section>
    </main>
</div>
<script>
document.addEventListener('DOMContentLoaded', function () {
    adminLookupsInit('<?php echo htmlspecialchars($table, ENT_QUOTES, 'UTF-8'); ?>', '<?php echo htmlspecialchars($primaryKey, ENT_QUOTES, 'UTF-8'); ?>');
});
</script>
</body>
</html>
