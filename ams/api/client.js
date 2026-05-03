/**
 * AMS API Client
 * Provides utility methods for making AJAX calls to AMS API endpoints
 */

class AMSApi {
    constructor(baseUrl = '../api') {
        this.baseUrl = baseUrl;
    }

    /**
     * Make GET request to API
     */
    async get(endpoint, params = {}) {
        const url = new URL(`${this.baseUrl}/${endpoint}.php`, window.location.origin);
        Object.keys(params).forEach(key => url.searchParams.append(key, params[key]));
        
        try {
            const response = await fetch(url.toString());
            if (!response.ok) throw new Error(`HTTP ${response.status}`);
            return await response.json();
        } catch (error) {
            console.error(`GET ${endpoint}:`, error);
            return { error: error.message };
        }
    }

    /**
     * Make POST request to API
     */
    async post(endpoint, data = {}) {
        const formData = new FormData();
        Object.keys(data).forEach(key => {
            if (typeof data[key] === 'object') {
                formData.append(key, JSON.stringify(data[key]));
            } else {
                formData.append(key, data[key]);
            }
        });
        
        try {
            const response = await fetch(`${this.baseUrl}/${endpoint}.php`, {
                method: 'POST',
                body: formData
            });
            if (!response.ok) throw new Error(`HTTP ${response.status}`);
            return await response.json();
        } catch (error) {
            console.error(`POST ${endpoint}:`, error);
            return { error: error.message };
        }
    }

    // Classes API
    async getClasses() {
        return this.get('classes', { action: 'list' });
    }

    async getClassEnrollments(classId) {
        return this.get('classes', { action: 'enrollments', class_id: classId });
    }

    // Grades API
    async getGrades(classId) {
        return this.get('grades', { action: 'get', class_id: classId });
    }

    async saveGrade(enrollmentId, gradingPeriod, finalGrade, remarks = '') {
        return this.post('grades', {
            action: 'save',
            enrollment_id: enrollmentId,
            grading_period: gradingPeriod,
            final_grade: finalGrade,
            remarks: remarks
        });
    }

    async getStudentGrades(studentId) {
        return this.get('grades', { action: 'get_student', student_id: studentId });
    }

    // Attendance API
    async recordAttendance(enrollmentId, attendanceDate, status) {
        return this.post('attendance', {
            action: 'record',
            enrollment_id: enrollmentId,
            attendance_date: attendanceDate,
            status: status
        });
    }

    async getAttendance(classId, date = null) {
        const params = { action: 'get', class_id: classId };
        if (date) params.date = date;
        return this.get('attendance', params);
    }

    async getAttendanceSummary(studentId) {
        return this.get('attendance', { action: 'summary', student_id: studentId });
    }

    // Activities API
    async getActivities(classId) {
        return this.get('activities', { action: 'list', class_id: classId });
    }

    async createActivity(classId, activityName, maxScore, activityDate = null) {
        return this.post('activities', {
            action: 'create',
            class_id: classId,
            activity_name: activityName,
            max_score: maxScore,
            activity_date: activityDate || new Date().toISOString().split('T')[0]
        });
    }

    async getActivityScores(activityId) {
        return this.get('activities', { action: 'score_get', activity_id: activityId });
    }

    async saveActivityScore(activityId, enrollmentId, score) {
        return this.post('activities', {
            action: 'score_save',
            activity_id: activityId,
            enrollment_id: enrollmentId,
            score: score
        });
    }

    async getStudentActivities(studentId) {
        return this.get('activities', { action: 'student_activities', student_id: studentId });
    }

    // Students API
    async listStudents() {
        return this.get('students', { action: 'list' });
    }

    async searchStudents(query) {
        return this.get('students', { action: 'search', q: query });
    }

    async getStudent(studentId) {
        return this.get('students', { action: 'get', student_id: studentId });
    }

    async createStudent(firstName, lastName, lrn, dateOfBirth, gradeLevel, status = 'Active') {
        return this.post('students', {
            action: 'create',
            first_name: firstName,
            last_name: lastName,
            lrn: lrn,
            date_of_birth: dateOfBirth,
            grade_level: gradeLevel,
            status: status
        });
    }

    async updateStudent(studentId, firstName, lastName, lrn, dateOfBirth, gradeLevel, status = 'Active') {
        return this.post('students', {
            action: 'update',
            student_id: studentId,
            first_name: firstName,
            last_name: lastName,
            lrn: lrn,
            date_of_birth: dateOfBirth,
            grade_level: gradeLevel,
            status: status
        });
    }

    async deleteStudent(studentId) {
        return this.post('students', { action: 'delete', student_id: studentId });
    }

    // Enrollments API
    async listEnrollments() {
        return this.get('enrollments', { action: 'list' });
    }

    async enrollStudent(studentId, classId, enrollmentDate = null) {
        return this.post('enrollments', {
            action: 'create',
            student_id: studentId,
            class_id: classId,
            enrollment_date: enrollmentDate || new Date().toISOString().split('T')[0]
        });
    }

    async getStudentEnrollments(studentId) {
        return this.get('enrollments', { action: 'student_enrollments', student_id: studentId });
    }

    async deleteEnrollment(enrollmentId) {
        return this.post('enrollments', { action: 'delete', enrollment_id: enrollmentId });
    }

    // Subjects API
    async listSubjects() {
        return this.get('subjects', { action: 'list' });
    }

    async createSubject(subjectName, description = '') {
        return this.post('subjects', {
            action: 'create',
            subject_name: subjectName,
            description: description
        });
    }

    async updateSubject(subjectId, subjectName, description = '') {
        return this.post('subjects', {
            action: 'update',
            subject_id: subjectId,
            subject_name: subjectName,
            description: description
        });
    }

    async deleteSubject(subjectId) {
        return this.post('subjects', { action: 'delete', subject_id: subjectId });
    }

    // Medical API
    async getMedicalInfo(studentId) {
        return this.get('medical', { action: 'get', student_id: studentId });
    }

    async saveMedicalInfo(studentId, bloodType, height, weight, lastCheckup = '', medication = '', restrictions = '', allergies = []) {
        return this.post('medical', {
            action: 'save',
            student_id: studentId,
            blood_type: bloodType,
            height: height,
            weight: weight,
            last_checkup: lastCheckup,
            medication: medication,
            restrictions: restrictions,
            allergies: allergies
        });
    }
}

// Global instance
const api = new AMSApi();
