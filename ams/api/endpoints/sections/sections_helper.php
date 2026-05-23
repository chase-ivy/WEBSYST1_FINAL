<?php
function createSection(PDO $pdo, string $schoolYear, string $gradeLevel, string $name, int $isActive = 1, array $subjects = []): array {
    if ($schoolYear === '') {
        return ['success' => false, 'error' => 'school_year is required'];
    }
    if ($gradeLevel === '') {
        return ['success' => false, 'error' => 'grade_level is required'];
    }
    if ($name === '') {
        return ['success' => false, 'error' => 'name is required'];
    }

    try {
        $pdo->beginTransaction();

        $stmt = $pdo->prepare('INSERT INTO sections (school_year, grade_level, name, is_active) VALUES (?, ?, ?, ?)');
        $stmt->execute([$schoolYear, $gradeLevel, $name, $isActive]);
        $sectionId = intval($pdo->lastInsertId());

        if (!empty($subjects)) {
            $subjectStmt = $pdo->prepare('INSERT INTO section_subjects (section_id, subject_id, teacher_id) VALUES (?, ?, ?)');
            foreach ($subjects as $subject) {
                $subjectId = intval($subject['subject_id'] ?? 0);
                $teacherId = intval($subject['teacher_id'] ?? 0);
                if ($subjectId <= 0 || $teacherId <= 0) {
                    continue;
                }
                $subjectStmt->execute([$sectionId, $subjectId, $teacherId]);
            }
        }

        $pdo->commit();
        return [
            'success' => true,
            'section_id' => $sectionId,
            'school_year' => $schoolYear,
            'grade_level' => $gradeLevel,
            'name' => $name,
        ];
    } catch (Exception $e) {
        if ($pdo->inTransaction()) {
            $pdo->rollBack();
        }
        $message = $e->getMessage();
        if (str_contains($message, 'Duplicate entry')) {
            return ['success' => false, 'error' => 'A section with that name already exists for this grade level and school year'];
        }
        return ['success' => false, 'error' => $message];
    }
}
