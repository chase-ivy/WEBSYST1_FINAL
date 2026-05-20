# AMS API Documentation

## Overview
This API is now structured as table-based CRUD endpoints under `ams/api/crud/`.
All endpoints return JSON and require session authentication.

## Base URL
`/WEBSYST1_FINAL/ams/api/`

## CRUD Endpoint Pattern
Each table has four endpoint files in `ams/api/crud/<table>/`:

- `c_<table>.php` — Create a new record
- `r_<table>.php` — Read records or a single record
- `u_<table>.php` — Update an existing record
- `d_<table>.php` — Delete a record

### Usage Examples
- List all students:
  - GET `/WEBSYST1_FINAL/ams/api/crud/students/r_students.php`
- Get a student by ID:
  - GET `/WEBSYST1_FINAL/ams/api/crud/students/r_students.php?id=1`
- Create a student:
  - POST `/WEBSYST1_FINAL/ams/api/crud/students/c_students.php`
  - JSON body: `{ ...fields... }`
- Update a student:
  - POST `/WEBSYST1_FINAL/ams/api/crud/students/u_students.php`
  - JSON body: `{ id: 1, ...fields... }`
- Delete a student:
  - POST `/WEBSYST1_FINAL/ams/api/crud/students/d_students.php`
  - JSON body: `{ id: 1 }`

## Tables Supported
- disability_subtypes
- disability_types
- enrollments
- enrollment_disabilities
- enrollment_family_medical_history
- enrollment_medical_allergies
- enrollment_medical_conditions
- enrollment_medical_information
- enrollment_medical_surgeries
- enrollment_medical_treatments
- enrollment_returning_learners
- family_medical_history_types
- indigenous_groups
- medical_allergy_types
- medical_condition_types
- mother_tongues
- parent_guardian_types
- student_addresses
- student_medical_records
- student_parent_guardians
- student_school_records
- students
- users

## Authentication
All CRUD endpoints require an active session. Unauthorized requests return HTTP 401.

## Deprecated API Endpoints
The original endpoint files have been moved to `ams/api/deprecated/` for reference and are not part of the new CRUD revision.
