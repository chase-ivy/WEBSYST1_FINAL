# AMS API Documentation

## Overview
All API endpoints require session authentication. Return JSON responses with success/error fields.

## Endpoints

### Classes API (`/api/classes.php`)
- **list** - Get teacher's classes (teacher only)
  - Query: `?action=list`
  - Returns: Array of classes with subject_name, grade_level, section, school_year
  
- **enrollments** - Get class enrollments (teacher only)
  - Query: `?action=enrollments&class_id={id}`
  - Returns: Array of enrolled students

### Grades API (`/api/grades.php`)
- **get** - Get class grades (teacher/admin)
  - Query: `?action=get&class_id={id}`
  - Returns: Array of grades by student and period

- **save** - Save or update grade (teacher only)
  - POST: `?action=save`
  - Params: enrollment_id, grading_period, final_grade, remarks
  - Returns: Success message

- **get_student** - Get student's grades (parent only)
  - Query: `?action=get_student&student_id={id}`
  - Returns: Array of grades by subject and period

### Attendance API (`/api/attendance.php`)
- **record** - Save attendance (teacher only)
  - POST: `?action=record`
  - Params: enrollment_id, attendance_date, status (Present|Absent|Late|Excused)

- **get** - Get class attendance for date (teacher only)
  - Query: `?action=get&class_id={id}&date={YYYY-MM-DD}`
  - Returns: Array of students with attendance status

- **summary** - Get student attendance summary (parent/student)
  - Query: `?action=summary&student_id={id}`
  - Returns: Counts of Present, Absent, Late, Excused

### Activities API (`/api/activities.php`)
- **list** - Get class activities (teacher only)
  - Query: `?action=list&class_id={id}`
  - Returns: Array of activities

- **create** - Create activity (teacher only)
  - POST: `?action=create`
  - Params: class_id, activity_name, max_score, activity_date

- **score_get** - Get activity scores (teacher only)
  - Query: `?action=score_get&activity_id={id}`
  - Returns: Array of students with scores

- **score_save** - Save activity score (teacher only)
  - POST: `?action=score_save`
  - Params: activity_id, enrollment_id, score

- **student_activities** - Get student activities (parent/student)
  - Query: `?action=student_activities&student_id={id}`
  - Returns: Array of activities with student scores

### Students API (`/api/students.php`)
- **list** - List all students (admin only)
  - Query: `?action=list`
  
- **search** - Search students (admin/teacher)
  - Query: `?action=search&q={search_term}`
  
- **get** - Get student details (admin only)
  - Query: `?action=get&student_id={id}`
  
- **create** - Create student (admin only)
  - POST: `?action=create`
  - Params: first_name, last_name, lrn, date_of_birth, grade_level, status
  
- **update** - Update student (admin only)
  - POST: `?action=update`
  - Params: student_id, first_name, last_name, lrn, date_of_birth, grade_level, status
  
- **delete** - Delete student (admin only)
  - POST: `?action=delete`
  - Params: student_id

### Enrollments API (`/api/enrollments.php`)
- **list** - List all enrollments (admin only)
  - Query: `?action=list`
  
- **create** - Enroll student (admin only)
  - POST: `?action=create`
  - Params: student_id, class_id, enrollment_date
  
- **student_enrollments** - Get student enrollments (parent/student)
  - Query: `?action=student_enrollments&student_id={id}`
  
- **delete** - Delete enrollment (admin only)
  - POST: `?action=delete`
  - Params: enrollment_id

### Subjects API (`/api/subjects.php`)
- **list** - List all subjects (any authenticated user)
  - Query: `?action=list`
  
- **create** - Create subject (admin only)
  - POST: `?action=create`
  - Params: subject_name, description
  
- **update** - Update subject (admin only)
  - POST: `?action=update`
  - Params: subject_id, subject_name, description
  
- **delete** - Delete subject (admin only)
  - POST: `?action=delete`
  - Params: subject_id

### Medical API (`/api/medical.php`)
- **get** - Get medical info (parent/student)
  - Query: `?action=get&student_id={id}`
  - Returns: Medical information with allergies and conditions
  
- **save** - Save medical info (admin/teacher)
  - POST: `?action=save`
  - Params: student_id, blood_type, height, weight, last_checkup, medication, restrictions, allergies (JSON array)

## Error Handling
- 401: Unauthorized (not logged in)
- 400: Bad request (invalid action or params)
- 500: Server error (database exception)

All errors return JSON: `{"error": "message"}`
