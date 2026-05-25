<?php
// ============================================================
// endpoints/students/update.php
// Updates a student's profile, enrollment snapshot, addresses, and guardians.
// POST body: student_id, enrollment_id, plus optional fields
// Accessible by: staff, admin
// ============================================================

require_once __DIR__ . '/../../endpoint_base.php';

require_role(['staff', 'admin']);
requireMethod('POST');

$data = getJsonInput();
$studentId = intval($data['student_id'] ?? 0);
$enrollmentId = intval($data['enrollment_id'] ?? 0);

if ($studentId <= 0) {
    sendJson(['success' => false, 'error' => 'student_id is required'], 400);
}

$studentStmt = $pdo->prepare('SELECT student_id, user_id FROM students WHERE student_id = ? LIMIT 1');
$studentStmt->execute([$studentId]);
$student = $studentStmt->fetch();
if (!$student) {
    sendJson(['success' => false, 'error' => 'Student not found'], 404);
}

function normalizeString($value): ?string {
    $value = trim((string)($value ?? ''));
    return $value === '' ? null : $value;
}

try {
    $pdo->beginTransaction();

    $studentFields = [
        'lrn' => normalizeString($data['Learner_Reference_No'] ?? $data['lrn'] ?? null),
        'psa_bcn' => normalizeString($data['psa_bcn'] ?? null),
        'last_name' => normalizeString($data['Learner_Last_Name'] ?? $data['last_name'] ?? null),
        'first_name' => normalizeString($data['Learner_First_Name'] ?? $data['first_name'] ?? null),
        'middle_name' => normalizeString($data['Learner_Middle_Name'] ?? $data['middle_name'] ?? null),
        'extension_name' => normalizeString($data['Learner_Extension_Name'] ?? $data['extension_name'] ?? null),
        'birth_date' => normalizeString($data['birth_date'] ?? null),
        'sex' => normalizeString($data['sex'] ?? null),
        'place_of_birth' => normalizeString($data['place_of_birth'] ?? null),
    ];
    $studentUpdates = array_filter($studentFields, fn($value) => $value !== null || in_array($value, [null], true));
    if (!empty($studentUpdates)) {
        $set = [];
        $params = [];
        foreach ($studentUpdates as $key => $value) {
            $set[] = "$key = ?";
            $params[] = $value;
        }
        $params[] = $studentId;
        $pdo->prepare('UPDATE students SET ' . implode(', ', $set) . ' WHERE student_id = ?')->execute($params);
    }

    if ($enrollmentId > 0) {
        $enrollmentStmt = $pdo->prepare('SELECT enrollment_id FROM enrollments WHERE enrollment_id = ? LIMIT 1');
        $enrollmentStmt->execute([$enrollmentId]);
        if (!$enrollmentStmt->fetch()) {
            throw new Exception('Enrollment record not found');
        }

        $schoolYear = null;
        $yearStart = normalizeString($data['year_start'] ?? null);
        $yearEnd = normalizeString($data['year_end'] ?? null);
        if ($yearStart !== null && $yearEnd !== null) {
            $schoolYear = "$yearStart-$yearEnd";
        } elseif (normalizeString($data['school_year'] ?? null) !== null) {
            $schoolYear = normalizeString($data['school_year'] ?? null);
        }

        $enrollmentFields = [
            'school_year' => $schoolYear,
            'grade_level' => normalizeString($data['Grade_Level'] ?? null),
            'with_lrn' => isset($data['with_lrn']) ? intval($data['with_lrn']) : null,
            'is_returning_learner' => isset($data['returning']) ? intval($data['returning']) : null,
            'mother_tongue_id' => isset($data['Mother_Tongue']) ? intval($data['Mother_Tongue']) : null,
            'is_indigenous' => isset($data['ip']) ? intval($data['ip']) : null,
            'indigenous_group_id' => isset($data['IP_Group']) ? intval($data['IP_Group']) : null,
            'is_four_ps_beneficiary' => isset($data['fourps']) ? intval($data['fourps']) : null,
            'four_ps_household_id' => normalizeString($data['FourPs_Specify'] ?? null),
            'is_learner_with_disability' => isset($data['disability']) ? ($data['disability'] === 'Yes' ? 1 : 0) : null,
        ];

        $enrollmentUpdates = array_filter($enrollmentFields, fn($value) => $value !== null || $value === 0);
        if (!empty($enrollmentUpdates)) {
            $set = [];
            $params = [];
            foreach ($enrollmentUpdates as $key => $value) {
                $set[] = "$key = ?";
                $params[] = $value;
            }
            $params[] = $enrollmentId;
            $pdo->prepare('UPDATE enrollments SET ' . implode(', ', $set) . ' WHERE enrollment_id = ?')->execute($params);
        }

        $returning = [
            'last_grade_level_completed' => normalizeString($data['Returning_Grade_Level'] ?? null),
            'last_school_year_completed' => normalizeString($data['Last_School_Year_Completed'] ?? null),
            'last_school_attended' => normalizeString($data['Last_School_Attended'] ?? null),
        ];

        $returningStmt = $pdo->prepare('SELECT returning_learner_id FROM enrollment_returning_learners WHERE enrollment_id = ? LIMIT 1');
        $returningStmt->execute([$enrollmentId]);
        $returningExists = $returningStmt->fetch();
        if ($returningExists) {
            $pdo->prepare('UPDATE enrollment_returning_learners SET last_grade_level_completed = ?, last_school_year_completed = ?, last_school_attended = ? WHERE enrollment_id = ?')
                ->execute([$returning['last_grade_level_completed'], $returning['last_school_year_completed'], $returning['last_school_attended'], $enrollmentId]);
        } else {
            if ($returning['last_grade_level_completed'] || $returning['last_school_year_completed'] || $returning['last_school_attended']) {
                $pdo->prepare('INSERT INTO enrollment_returning_learners (enrollment_id, last_grade_level_completed, last_school_year_completed, last_school_attended) VALUES (?, ?, ?, ?)')
                    ->execute([$enrollmentId, $returning['last_grade_level_completed'], $returning['last_school_year_completed'], $returning['last_school_attended']]);
            }
        }

        foreach (['current', 'permanent'] as $type) {
            $prefix = $type === 'current' ? 'Current' : 'Permanent';
            $address = [
                'house_no' => normalizeString($data["{$prefix}_House_No"] ?? null),
                'street_name' => normalizeString($data["{$prefix}_Street_Name"] ?? null),
                'barangay' => normalizeString($data["{$prefix}_Barangay"] ?? null),
                'municipality_city' => normalizeString($data["{$prefix}_Municipality_City"] ?? null),
                'province' => normalizeString($data["{$prefix}_Province"] ?? null),
                'country' => normalizeString($data["{$prefix}_Country"] ?? 'Philippines'),
                'zip_code' => normalizeString($data["{$prefix}_Zip_Code"] ?? null),
                'ownership_type' => normalizeString($data["{$prefix}_Address_Status"] ?? null),
            ];
            $addressStmt = $pdo->prepare('SELECT address_id FROM student_addresses WHERE student_id = ? AND address_type = ? AND enrollment_id = ? LIMIT 1');
            $addressStmt->execute([$studentId, $type, $enrollmentId]);
            $existingAddress = $addressStmt->fetch();
            if ($existingAddress) {
                $set = [];
                $params = [];
                foreach ($address as $key => $value) {
                    $set[] = "$key = ?";
                    $params[] = $value;
                }
                $params[] = $existingAddress['address_id'];
                $pdo->prepare('UPDATE student_addresses SET ' . implode(', ', $set) . ' WHERE address_id = ?')->execute($params);
            } else {
                $pdo->prepare('INSERT INTO student_addresses (student_id, address_type, house_no, street_name, barangay, municipality_city, province, country, zip_code, enrollment_id, ownership_type) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)')
                    ->execute([$studentId, $type, $address['house_no'], $address['street_name'], $address['barangay'], $address['municipality_city'], $address['province'], $address['country'] ?? 'Philippines', $address['zip_code'], $enrollmentId, $address['ownership_type']]);
            }
        }

        $guardians = [
            ['type_id' => 1, 'prefix' => 'father'],
            ['type_id' => 2, 'prefix' => 'mother'],
            ['type_id' => 3, 'prefix' => 'guardian'],
        ];
        foreach ($guardians as $guardian) {
            $row = [
                'last_name' => normalizeString($data["{$guardian['prefix']}_last_name"] ?? null),
                'first_name' => normalizeString($data["{$guardian['prefix']}_first_name"] ?? null),
                'middle_name' => normalizeString($data["{$guardian['prefix']}_middle_name"] ?? null),
                'contact_number' => normalizeString($data["{$guardian['prefix']}_contact_number"] ?? null),
            ];
            $guardianStmt = $pdo->prepare('SELECT parent_guardian_id FROM student_parent_guardians WHERE student_id = ? AND parent_guardian_type_id = ? LIMIT 1');
            $guardianStmt->execute([$studentId, $guardian['type_id']]);
            $existingGuardian = $guardianStmt->fetch();
            if ($existingGuardian) {
                $pdo->prepare('UPDATE student_parent_guardians SET last_name = ?, first_name = ?, middle_name = ?, contact_number = ? WHERE parent_guardian_id = ?')
                    ->execute([$row['last_name'] ?? '', $row['first_name'] ?? '', $row['middle_name'] ?? '', $row['contact_number'] ?? '', $existingGuardian['parent_guardian_id']]);
            } elseif ($row['last_name'] || $row['first_name'] || $row['contact_number']) {
                $pdo->prepare('INSERT INTO student_parent_guardians (student_id, parent_guardian_type_id, last_name, first_name, middle_name, contact_number) VALUES (?, ?, ?, ?, ?, ?)')
                    ->execute([$studentId, $guardian['type_id'], $row['last_name'] ?? '', $row['first_name'] ?? '', $row['middle_name'] ?? '', $row['contact_number'] ?? '']);
            }
        }
    }

    $pdo->commit();
    sendJson(['success' => true, 'message' => 'Student updated successfully']);
} catch (Exception $e) {
    if ($pdo->inTransaction()) $pdo->rollBack();
    sendJson(['success' => false, 'error' => $e->getMessage()], 500);
}
