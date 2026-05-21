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

window.API = API;
