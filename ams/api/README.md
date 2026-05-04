# AMS API Documentation

## Overview
All API endpoints require session authentication and return JSON responses.

## Base URL
`/WEBSYST1_FINAL/ams/api/`

## Endpoints

### Students API (`/api/students.php`)
- **list** - Get all students
  - Method: GET
  - Query: `?action=list`
  - Returns: Array of students

- **search** - Search students
  - Method: GET
  - Query: `?action=search&q={query}`
  - Returns: Array of matching students

- **get** - Get student details
  - Method: GET
  - Query: `?action=get&student_id={id}`
  - Returns: Student object

- **create** - Create new student
  - Method: POST
  - JSON Body: `{ lrn, first_name, last_name, middle_name, birth_date, sex, place_of_birth }`
  - Returns: { success: true, student_id: {id} }

- **update** - Update student
  - Method: POST
  - JSON Body: `{ student_id, lrn, first_name, last_name, middle_name, birth_date, sex, place_of_birth }`
  - Returns: { success: true }

- **delete** - Delete student
  - Method: POST
  - JSON Body: `{ student_id }`
  - Returns: { success: true }

### Classes API (`/api/classes.php`)
- **list** - Get all classes
  - Method: GET
  - Query: `?action=list`
  - Returns: Array of classes

- **teacher_classes** - Get teacher's classes (requires teacher role)
  - Method: GET
  - Query: `?action=teacher_classes`
  - Returns: Array of classes taught by logged-in teacher

- **students** - Get students in a class
  - Method: GET
  - Query: `?action=students&class_id={id}`
  - Returns: Array of class students

- **create** - Create new class
  - Method: POST
  - JSON Body: `{ school_year, grade_level, section, adviser_id }`
  - Returns: { success: true, class_id: {id} }

- **update** - Update class
  - Method: POST
  - JSON Body: `{ class_id, school_year, grade_level, section, adviser_id }`
  - Returns: { success: true }

### Users API (`/api/users.php`)
- **list** - Get all teacher and parent accounts
  - Method: GET
  - Query: `?action=list`
  - Returns: Array of user accounts

- **get** - Get user details
  - Method: GET
  - Query: `?action=get&user_id={id}`
  - Returns: User object

- **create** - Create a new staff account
  - Method: POST
  - JSON Body: `{ username, email, password, role }`
  - Returns: `{ success: true, message }`

- **update** - Update staff account
  - Method: POST
  - JSON Body: `{ user_id, username, email, role, password? }`
  - Returns: `{ success: true, message }`

- **delete** - Delete staff account
  - Method: POST
  - JSON Body: `{ user_id }`
  - Returns: `{ success: true, message }`

### Enrollments API (`/api/enrollments.php`)
- **list** - Get all enrollments (admin only)
  - Method: GET
  - Query: `?action=list`
  - Returns: Array of enrollments

- **student** - Get student's enrollments
  - Method: GET
  - Query: `?action=student&student_id={id}`
  - Returns: Array of enrollments

- **create** - Create new enrollment
  - Method: POST
  - JSON Body: `{ student_id, school_year, grade_level, with_lrn, age, mother_tongue }`
  - Returns: { success: true, enrollment_id: {id} }

- **delete** - Delete enrollment
  - Method: POST
  - JSON Body: `{ enrollment_id }`
  - Returns: { success: true }

### Subjects API (`/api/subjects.php`)
- **list** - Get all subjects
  - Method: GET
  - Query: `?action=list`
  - Returns: Array of subjects

- **create** - Create new subject
  - Method: POST
  - JSON Body: `{ name }`
  - Returns: { success: true, subject_id: {id} }

- **update** - Update subject
  - Method: POST
  - JSON Body: `{ subject_id, name }`
  - Returns: { success: true }

- **delete** - Delete subject
  - Method: POST
  - JSON Body: `{ subject_id }`
  - Returns: { success: true }

### Grades API (`/api/grades.php`)
- **class** - Get class grades (teacher/admin)
  - Method: GET
  - Query: `?action=class&class_id={id}`
  - Returns: Array of grades by student and subject

- **student** - Get student's grades (parent/student)
  - Method: GET
  - Query: `?action=student&enrollment_id={id}`
  - Returns: Array of grades by subject and period

- **save** - Save/update grade (teacher only)
  - Method: POST
  - JSON Body: `{ class_student_id, class_subject_id, grading_period, grade }`
  - Returns: { success: true }

### Attendance API (`/api/attendance.php`)
- **class** - Get class attendance for date (teacher only)
  - Method: GET
  - Query: `?action=class&class_id={id}&date={YYYY-MM-DD}`
  - Returns: Array of students with attendance status

- **record** - Record attendance (teacher only)
  - Method: POST
  - JSON Body: `{ class_student_id, date, status (present|absent|late|excused) }`
  - Returns: { success: true }

- **summary** - Get student attendance summary
  - Method: GET
  - Query: `?action=summary&enrollment_id={id}`
  - Returns: { present, absent, late_count, excused }

### Activities API (`/api/activities.php`)
- **list** - Get class activities (teacher only)
  - Method: GET
  - Query: `?action=list&class_subject_id={id}`
  - Returns: Array of activities

- **create** - Create activity (teacher only)
  - Method: POST
  - JSON Body: `{ class_subject_id, title, description, max_score, due_date }`
  - Returns: { success: true, activity_id: {id} }

- **scores** - Get activity scores (teacher only)
  - Method: GET
  - Query: `?action=scores&activity_id={id}`
  - Returns: Array of students with scores

- **save_score** - Save activity score (teacher only)
  - Method: POST
  - JSON Body: `{ activity_id, class_student_id, score }`
  - Returns: { success: true }

### Medical API (`/api/medical.php`)
- **get** - Get medical information
  - Method: GET
  - Query: `?action=get&enrollment_id={id}`
  - Returns: Medical information object with conditions and allergies

- **save** - Save medical information (admin/teacher only)
  - Method: POST
  - JSON Body: `{ enrollment_id, exposed_to_cigarette_vape_smoke, other_pertinent_information }`
  - Returns: { success: true, medical_id: {id} }

## Authentication
All endpoints require an active session. If no session exists, returns HTTP 401 with error.

## Error Handling
- Invalid action: HTTP 400 with `{ error: "Invalid action" }`
- Unauthorized: HTTP 401 with `{ error: "Unauthorized" }`
- Server error: HTTP 500 with `{ error: "Error message" }`
