/**
 * AMS API Client Library
 *
 * Two layers:
 *   API.crud.*      — generic CRUD for simple lookup tables
 *   API.enrollment  — enrollment form, verify, reject, get
 *   API.students    — register (user + student in one transaction)
 *   API.sections    — section management and student assignment
 */

const clientScript = document.currentScript;
const computedBase = clientScript && clientScript.src
    ? new URL('.', clientScript.src).href.replace(/\/$/, '')
    : window.location.origin + window.location.pathname.split('/').slice(0, 3).join('/') + '/api';

const API = {
    BASE: computedBase,

    call: async function(endpoint, data = null, method = 'GET') {
        const url = new URL(this.BASE + '/' + endpoint + '.php', window.location.origin);
        const options = {
            method: method,
            credentials: 'same-origin',
            headers: { 'Content-Type': 'application/json' }
        };

        if (method === 'GET' && data) {
            Object.keys(data).forEach(key => {
                if (data[key] !== undefined && data[key] !== null) {
                    url.searchParams.append(key, data[key]);
                }
            });
        }

        if (method === 'POST') {
            options.body = JSON.stringify(data || {});
        }

        const response = await fetch(url.toString(), options);
        const text = await response.text();
        const cleaned = text.replace(/^\uFEFF/, '');

        let result;
        try {
            result = text ? JSON.parse(cleaned) : null;
        } catch (parseError) {
            const snippet = cleaned.length > 1000 ? cleaned.slice(0, 1000) + '... (truncated)' : cleaned;
            console.error('Invalid JSON response body from API:', cleaned);
            throw new Error(`Invalid JSON response from API (${response.status}): ${snippet}`);
        }

        if (!response.ok) {
            let message = result?.error || `API request failed (${response.status})`;
            if (!message && Array.isArray(result?.errors)) {
                message = result.errors.join(' ');
            }
            throw new Error(message);
        }

        return result;
    },

    // ── Generic CRUD (lookup tables only) ────────────────────
    crud: {
        endpoint: function(table, operation) {
            return `crud/${table}/${operation}_${table}`;
        },
        create: function(table, data)     { return API.call(this.endpoint(table, 'c'), data, 'POST'); },
        read:   function(table, id=null)  { return API.call(this.endpoint(table, 'r'), id ? { id } : null, 'GET'); },
        update: function(table, id, data) { return API.call(this.endpoint(table, 'u'), Object.assign({}, data, { id }), 'POST'); },
        delete: function(table, id)       { return API.call(this.endpoint(table, 'd'), { id }, 'POST'); },
        list:   function(table)           { return this.read(table, null); },
        table:  function(table) {
            return {
                create: data     => this.create(table, data),
                read:   id       => this.read(table, id),
                list:   ()       => this.list(table),
                update: (id, d)  => this.update(table, id, d),
                delete: id       => this.delete(table, id),
            };
        }
    },

    // ── Enrollment endpoints ──────────────────────────────────
    enrollment: {
        // Submit the full enrollment form (pending)
        submit: function(data) {
            return API.call('endpoints/enrollment/submit', data, 'POST');
        },
        // Verify a pending enrollment → creates school + medical records
        verify: function(enrollmentId) {
            return API.call('endpoints/enrollment/verify', { enrollment_id: enrollmentId }, 'POST');
        },
        // Reject a pending enrollment
        reject: function(enrollmentId, reason = null) {
            return API.call('endpoints/enrollment/reject', { enrollment_id: enrollmentId, rejection_reason: reason }, 'POST');
        },
        // Get a single enrollment with all child data (for verify form)
        get: function(enrollmentId) {
            return API.call('endpoints/enrollment/get', { id: enrollmentId }, 'GET');
        },
        // Get enrollment by student + school year
        getByStudent: function(studentId, schoolYear = null) {
            const params = { student_id: studentId };
            if (schoolYear) params.school_year = schoolYear;
            return API.call('endpoints/enrollment/get', params, 'GET');
        },
        // Get queue list (pending enrollments for a school year)
        queue: function(schoolYear, status = 'pending') {
            return API.call('endpoints/enrollment/get', { school_year: schoolYear, status }, 'GET');
        },
    },

    // ── Backwards-compat shim for older code using plural `enrollments` ──
    enrollments: {
        // Return raw list (compatible with previous code that expects an array or { enrollments: [] })
        list: function() { return API.call('endpoints/enrollment/get', null, 'GET'); },
        read: function(id) { return API.enrollment.get(id); },
        // Create (legacy alias) -> use canonical submit endpoint
        create: function(data) { return API.enrollment.submit(data); },
        // Update: fallback shim. A proper server-side `endpoints/enrollment/update.php` is recommended.
        update: function(id, data) {
            const payload = Object.assign({}, data || {}, { enrollment_id: id });
            return API.call('endpoints/enrollment/update', payload, 'POST');
        }
    },

    // ── Student endpoints ─────────────────────────────────────
    students: {
        // Create user + student profile in one transaction
        // is_active defaults to 0 for public (guest) registration
        register: function(data) {
            return API.call('endpoints/students/register', data, 'POST');
        },
        // Create minimal user+student record (admin/staff)
        create: function(data) {
            return API.call('endpoints/students/create', data, 'POST');
        },
        // Admin convenience: create full student + enrollment + school_record + assign to section
        createFull: function(data) {
            return API.call('endpoints/admin/create_student_full', data, 'POST');
        },
        get: function(studentId) {
            return API.call('endpoints/students/get', { id: studentId }, 'GET');
        },
        list: function() {
            return API.call('endpoints/students/get', null, 'GET');
        },
        update: function(studentId, data) {
            return API.call('endpoints/students/update', Object.assign({}, data, { student_id: studentId }), 'POST');
        },
        delete: function(studentId) {
            return API.call('endpoints/students/delete', { student_id: studentId }, 'POST');
        },
    },

    // ── Record endpoints ──────────────────────────────────────
    records: {
        get: function(params) {
            return API.call('endpoints/records/get', params, 'GET');
        },
        getByStudent: function(studentId) {
            return this.get({ student_id: studentId });
        },
        getBySchoolRecord: function(schoolRecordId) {
            return this.get({ school_record_id: schoolRecordId });
        }
    },

    // ── Section endpoints ─────────────────────────────────────
    sections: {
        // Assign a verified student to a section
        assignStudent: function(schoolRecordId, sectionId) {
            return API.call('endpoints/sections/assign_student', {
                school_record_id: schoolRecordId,
                section_id: sectionId,
            }, 'POST');
        },
        create: function(data) {
            return API.call('endpoints/sections/create', data, 'POST');
        },
        list: function(filters = {}) {
            return API.call('endpoints/sections/get', filters, 'GET');
        },
    },

    // ── Teacher helpers (compat shim for dashboard JS) ─────────
    teacher: {
        // Returns sections assigned to the logged-in teacher
        classes: function() {
            return API.call('endpoints/teacher/classes', null, 'GET');
        },
        getStudentAccount: function(studentId) {
            return API.call('endpoints/students/get', { id: studentId }, 'GET');
        },
        updateStudentAccount: function(data) {
            return API.call('endpoints/teacher/update_student_account', data, 'POST');
        },
        assignSubject: function(data) {
            return API.call('endpoints/teacher/assign_subject', data, 'POST');
        }
    },

    // ── Classes helpers (section subjects / students) ──────────
    classes: {
        getTeacherClasses: function() {
            return API.teacher.classes();
        },
        getSubjects: function(classId) {
            return API.call('endpoints/classes/get_subjects', { section_id: classId }, 'GET');
        },
        getClassStudents: function(classId) {
            return API.call('endpoints/classes/get_class_students', { section_id: classId }, 'GET');
        },
        assignStudent: function(data) {
            return API.call('endpoints/classes/assign_student', data, 'POST');
        },
        unassignSubject: function(classSubjectId) {
            return API.call('endpoints/classes/unassign_subject', { class_subject_id: classSubjectId }, 'POST');
        }
    },

    // ── Attendance endpoints ───────────────────────────────────
    attendance: {
        getClassAttendance: function(classId, date) {
            return API.call('endpoints/attendance/get_class_attendance', { section_id: classId, date }, 'GET');
        },
        record: function(data) {
            return API.call('endpoints/attendance/record', data, 'POST');
        }
    },

    // ── Activities endpoints ──────────────────────────────────
    activities: {
        listByClassSubject: function(classSubjectId) {
            return API.call('endpoints/activities/list_by_class_subject', { class_subject_id: classSubjectId }, 'GET');
        },
        create: function(data) {
            return API.call('endpoints/activities/create', data, 'POST');
        },
        getScores: function(activityId) {
            return API.call('endpoints/activities/get_scores', { activity_id: activityId }, 'GET');
        },
        saveScore: function(data) {
            return API.call('endpoints/activities/save_scores', data, 'POST');
        }
    },

    // ── Grade endpoints ─────────────────────────────────────
    grades: {
        getClassGrades: function(classSubjectId) {
            return API.call('endpoints/grades/get_class_grades', { class_subject_id: classSubjectId }, 'GET');
        },
        save: function(data) {
            return API.call('endpoints/grades/save', data, 'POST');
        }
    },

    // ── Medical endpoints ───────────────────────────────────
    medical: {
        getByEnrollment: function(enrollmentId) {
            return API.call('endpoints/medical/get_by_enrollment', { enrollment_id: enrollmentId }, 'GET');
        },
        save: function(data) {
            return API.call('endpoints/medical/save', data, 'POST');
        }
    },

    // ── Student dashboard endpoints ──────────────────────────
    studentDashboard: {
        get: function() {
            return API.call('endpoints/student_dashboard/get', null, 'GET');
        }
    },
};

// ── CRUD table aliases (lookup tables only) ───────────────────
(() => {
    const lookupTables = [
        'disability_subtypes', 'disability_types',
        'family_medical_history_types', 'indigenous_groups',
        'medical_allergy_types', 'medical_condition_types',
        'mother_tongues', 'parent_guardian_types',
        'subjects',
    ];

    lookupTables.forEach(t => {
        API[t] = API.crud.table(t);
        if (t.endsWith('s')) {
            const singular = t.slice(0, -1);
            if (!API[singular]) API[singular] = API.crud.table(t);
        }
    });
})();

window.API = API;