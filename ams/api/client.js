/**
 * AMS API Client Library
 * Handles requests to table-based CRUD endpoints under /api/crud.
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
            headers: {
                'Content-Type': 'application/json'
            }
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

    crud: {
        endpoint: function(table, operation) {
            return `crud/${table}/${operation}_${table}`;
        },

        create: function(table, data) {
            return API.call(this.endpoint(table, 'c'), data, 'POST');
        },

        read: function(table, id = null) {
            return API.call(this.endpoint(table, 'r'), id ? { id } : null, 'GET');
        },

        update: function(table, id, data) {
            const payload = Object.assign({}, data, { id });
            return API.call(this.endpoint(table, 'u'), payload, 'POST');
        },

        delete: function(table, id) {
            return API.call(this.endpoint(table, 'd'), { id }, 'POST');
        },

        list: function(table) {
            return this.read(table, null);
        },

        table: function(table) {
            return {
                create: data => this.create(table, data),
                read: id => this.read(table, id),
                list: () => this.list(table),
                update: (id, data) => this.update(table, id, data),
                delete: id => this.delete(table, id)
            };
        }
    }
};

// convenience aliases for CRUD tables to support legacy code calling `API.<table>`
// (list generated from server-side CRUD folders)
(() => {
    const tables = [
        'disability_subtypes', 'disability_types', 'enrollments', 'enrollment_disabilities',
        'enrollment_family_medical_history', 'enrollment_medical_allergies', 'enrollment_medical_conditions',
        'enrollment_medical_information', 'enrollment_medical_surgeries', 'enrollment_medical_treatments',
        'enrollment_returning_learners', 'family_medical_history_types', 'indigenous_groups',
        'medical_allergy_types', 'medical_condition_types', 'mother_tongues', 'parent_guardian_types',
        'students', 'student_addresses', 'student_medical_records', 'student_parent_guardians',
        'student_school_records', 'users'
    ];

    tables.forEach(t => {
        API[t] = API.crud.table(t);
        // also provide a simple singular alias when the plural ends with 's'
        if (t.endsWith('s')) {
            const singular = t.slice(0, -1);
            if (!API[singular]) API[singular] = API.crud.table(t);
        }
    });

    // explicit historical aliases
    if (!API.enroll) API.enroll = API.crud.table('enrollments');
})();

window.API = API;
