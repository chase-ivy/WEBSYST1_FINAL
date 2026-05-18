/**
 * AMS API Client Library
 * Handles AJAX requests to the API endpoints
 */

const API = {
    BASE: '/login/WEBSYST1_FINAL/ams/api',
    
    // Helper function for API calls
    call: async function(endpoint, action, data = null, method = 'GET') {
        const url = new URL(this.BASE + '/' + endpoint + '.php', window.location.origin);
        url.searchParams.append('action', action);
        
        const options = {
            method: method,
            headers: {
                'Content-Type': 'application/json'
            }
        };
        
        if (method === 'POST' && data) {
            options.body = JSON.stringify(data);
        } else if (method === 'GET' && data) {
            Object.keys(data).forEach(key => {
                url.searchParams.append(key, data[key]);
            });
        }
        
        try {
            const response = await fetch(url.toString(), options);
            const text = await response.text();
            const cleaned = text.replace(/^\uFEFF/, '');
            let result;
            try {
                result = JSON.parse(cleaned);
            } catch (parseError) {
                console.error('API Error: invalid JSON response', response.status, cleaned);
                throw new Error(`Invalid JSON response from API (${response.status})`);
            }

            if (!response.ok) {
                const message = result.error || `API request failed (${response.status})`;
                console.error('API Error:', message, result);
                throw new Error(message);
            }

            return result;
        } catch (error) {
            console.error('API Error:', error);
            throw error;
        }
    },
    
    // Students API
    students: {
        list: async function() {
            return API.call('students', 'list');
        },
        search: async function(query) {
            return API.call('students', 'search', { q: query });
        },
        get: async function(studentId) {
            return API.call('students', 'get', { student_id: studentId });
        },
        create: async function(data) {
            return API.call('students', 'create', data, 'POST');
        },
        update: async function(studentId, data) {
            data.student_id = studentId;
            return API.call('students', 'update', data, 'POST');
        },
        delete: async function(studentId) {
            return API.call('students', 'delete', { student_id: studentId }, 'POST');
        }
    },
    
    // Student Dashboard API
    studentDashboard: {
        get: async function() {
            return API.call('student_dashboard', 'me');
        }
    },

    // Users API
    users: {
        list: async function() {
            return API.call('users', 'list');
        },
        get: async function(userId) {
            return API.call('users', 'get', { user_id: userId });
        },
        create: async function(data) {
            return API.call('users', 'create', data, 'POST');
        },
        update: async function(userId, data) {
            data.user_id = userId;
            return API.call('users', 'update', data, 'POST');
        },
        delete: async function(userId) {
            return API.call('users', 'delete', { user_id: userId }, 'POST');
        }
    },
    
    // Classes API
    classes: {
        list: async function() {
            return API.call('classes', 'list');
        },
        getTeacherClasses: async function() {
            return API.call('classes', 'teacher_classes');
        },
        getClassStudents: async function(classId) {
            return API.call('classes', 'students', { class_id: classId });
        },
        create: async function(data) {
            return API.call('classes', 'create', data, 'POST');
        },
        update: async function(classId, data) {
            data.class_id = classId;
            return API.call('classes', 'update', data, 'POST');
        },
        delete: async function(classId) {
            return API.call('classes', 'delete', { class_id: classId }, 'POST');
        },
        remove: async function(classId) {
            return API.call('classes', 'delete', { class_id: classId }, 'POST');
        },
        assignStudent: async function(data) {
            return API.call('classes', 'assign_student', data, 'POST');
        },
        getSubjects: async function(classId) {
            return API.call('classes', 'subjects', { class_id: classId });
        },
        unassignSubject: async function(classSubjectId) {
            return API.call('classes', 'unassign_subject', { class_subject_id: classSubjectId }, 'POST');
        }
    },
    
    // Enrollments API
    enrollments: {
        list: async function() {
            return API.call('enrollments', 'list');
        },
        getStudentEnrollments: async function(studentId) {
            return API.call('enrollments', 'student', { student_id: studentId });
        },
        create: async function(data) {
            return API.call('enrollments', 'create', data, 'POST');
        },
        delete: async function(enrollmentId) {
            return API.call('enrollments', 'delete', { enrollment_id: enrollmentId }, 'POST');
        }
    },

    // Enrollment Form API
    enroll: {
        create: async function(data) {
            return API.call('enroll', 'create', data, 'POST');
        }
    },

    lookups: {
        listMotherTongues: async function() {
            return API.call('lookups', 'mother_tongues');
        },
        listIndigenousGroups: async function() {
            return API.call('lookups', 'indigenous_groups');
        },
        listAll: async function() {
            return API.call('lookups', 'all');
        }
    },
    
    // Subjects API
    subjects: {
        list: async function() {
            return API.call('subjects', 'list');
        },
        create: async function(data) {
            return API.call('subjects', 'create', data, 'POST');
        },
        update: async function(subjectId, data) {
            data.subject_id = subjectId;
            return API.call('subjects', 'update', data, 'POST');
        },
        delete: async function(subjectId) {
            return API.call('subjects', 'delete', { subject_id: subjectId }, 'POST');
        }
    },
    
    // Grades API
    grades: {
        getClassGrades: async function(classSubjectId) {
            return API.call('grades', 'class', { class_subject_id: classSubjectId });
        },
        getStudentGrades: async function(enrollmentId) {
            return API.call('grades', 'student', { enrollment_id: enrollmentId });
        },
        save: async function(data) {
            return API.call('grades', 'save', data, 'POST');
        }
    },
    
    // Attendance API
    attendance: {
        getClassAttendance: async function(classId, date) {
            return API.call('attendance', 'class', { class_id: classId, date: date });
        },
        record: async function(data) {
            return API.call('attendance', 'record', data, 'POST');
        },
        getStudentSummary: async function(enrollmentId) {
            return API.call('attendance', 'summary', { enrollment_id: enrollmentId });
        }
    },
    
    // Activities API
    activities: {
        listByClass: async function(classId) {
            return API.call('activities', 'list', { class_id: classId });
        },
        listByClassSubject: async function(classSubjectId) {
            return API.call('activities', 'list', { class_subject_id: classSubjectId });
        },
        create: async function(data) {
            return API.call('activities', 'create', data, 'POST');
        },
        getScores: async function(activityId) {
            return API.call('activities', 'scores', { activity_id: activityId });
        },
        saveScore: async function(data) {
            return API.call('activities', 'save_score', data, 'POST');
        }
    },

    // Teacher API
    teacher: {
        dashboard: async function() {
            return API.call('teacher', 'dashboard');
        },
        classes: async function() {
            return API.call('teacher', 'classes');
        },
        students: async function() {
            return API.call('teacher', 'students');
        },
        subjects: async function() {
            return API.call('teacher', 'subjects');
        },
        assignSubject: async function(data) {
            return API.call('teacher', 'assign_subject', data, 'POST');
        },
        getStudentAccount: async function(studentId) {
            return API.call('teacher', 'student_account', { student_id: studentId });
        },
        updateStudentAccount: async function(data) {
            return API.call('teacher', 'update_student_account', data, 'POST');
        },
        deleteStudent: async function(studentId) {
            return API.call('teacher', 'delete_student', { student_id: studentId }, 'POST');
        }
    },
    
    // Medical API
    medical: {
        getByEnrollment: async function(enrollmentId) {
            return API.call('medical', 'get', { enrollment_id: enrollmentId });
        },
        save: async function(data) {
            return API.call('medical', 'save', data, 'POST');
        }
    }
};

if (typeof window !== 'undefined') {
    window.API = API;
}

