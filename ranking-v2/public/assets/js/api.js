/**
 * API Module
 * Handles all API communication
 */

(function() {
    'use strict';
    
    const API_BASE = 'api/records.php';
    
    /**
     * Get all records with filters
     */
    async function getRecords(filters = {}) {
        const params = new URLSearchParams();
        
        if (filters.page) params.append('page', filters.page);
        if (filters.pageSize) params.append('pageSize', filters.pageSize);
        if (filters.sortBy) params.append('sortBy', filters.sortBy);
        if (filters.sortOrder) params.append('sortOrder', filters.sortOrder);
        if (filters.searchUrl) params.append('searchUrl', filters.searchUrl);
        if (filters.status) params.append('status', filters.status);
        if (filters.orgType) params.append('orgType', filters.orgType);
        if (filters.topic) params.append('topic', filters.topic);
        if (filters.year) params.append('year', filters.year);
        if (filters.aiRelevanceScore) params.append('aiRelevanceScore', filters.aiRelevanceScore);
        if (filters.onlyUnrated) params.append('only_unrated', '1');
        
        const response = await fetch(`${API_BASE}?${params.toString()}`);
        
        if (!response.ok) {
            const text = await response.text();
            throw new Error(`שגיאת HTTP ${response.status}: ${text.substring(0, 200)}`);
        }
        
        const text = await response.text();
        if (!text || text.trim().length === 0) {
            throw new Error('תגובה ריקה מהשרת');
        }
        
        let data;
        try {
            data = JSON.parse(text);
        } catch (e) {
            console.error('JSON Parse Error:', e);
            console.error('Response text:', text.substring(0, 500));
            throw new Error('שגיאה בפענוח JSON מהשרת: ' + e.message);
        }
        
        if (!data.success) {
            throw new Error(data.error || 'שגיאה בטעינת רשומות');
        }
        
        return data;
    }
    
    /**
     * Get single record by ID
     */
    async function getRecord(id) {
        let response;
        try {
            response = await fetch(`${API_BASE}?id=${id}`);
        } catch (networkError) {
            console.error('Network error fetching record:', networkError);
            throw new Error('שגיאת רשת: לא ניתן להתחבר לשרת');
        }

        if (!response.ok) {
            const text = await response.text();
            throw new Error(`שגיאת HTTP ${response.status}: ${text.substring(0, 200)}`);
        }

        const text = await response.text();
        if (!text || text.trim().length === 0) {
            throw new Error('תגובה ריקה מהשרת');
        }

        let data;
        try {
            data = JSON.parse(text);
        } catch (e) {
            console.error('JSON Parse Error:', e);
            console.error('Response text:', text.substring(0, 500));
            throw new Error('שגיאה בפענוח JSON מהשרת: ' + e.message);
        }

        if (!data.success) {
            throw new Error(data.error || 'שגיאה בטעינת רשומה');
        }

        if (!data.record) {
            throw new Error('הרשומה לא נמצאה בתגובת השרת');
        }

        return data.record;
    }
    
    /**
     * Save record (create or update)
     */
    async function saveRecord(record) {
        let response;
        try {
            response = await fetch(API_BASE, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json'
                },
                body: JSON.stringify(record)
            });
        } catch (networkError) {
            console.error('Network error saving record:', networkError);
            throw new Error('שגיאת רשת: לא ניתן להתחבר לשרת');
        }

        if (!response.ok) {
            const text = await response.text();
            throw new Error(`שגיאת HTTP ${response.status}: ${text.substring(0, 200)}`);
        }

        const text = await response.text();
        if (!text || text.trim().length === 0) {
            throw new Error('תגובה ריקה מהשרת');
        }

        let data;
        try {
            data = JSON.parse(text);
        } catch (e) {
            console.error('JSON Parse Error:', e);
            console.error('Response text:', text.substring(0, 500));
            throw new Error('שגיאה בפענוח JSON מהשרת: ' + e.message);
        }

        if (!data.success) {
            throw new Error(data.error || 'שגיאה בשמירת רשומה');
        }

        return data.record;
    }
    
    /**
     * Delete record
     */
    async function deleteRecord(id) {
        const response = await fetch(API_BASE, {
            method: 'DELETE',
            headers: {
                'Content-Type': 'application/json'
            },
            body: JSON.stringify({ id: id })
        });
        
        if (!response.ok) {
            const text = await response.text();
            throw new Error(`שגיאת HTTP ${response.status}: ${text.substring(0, 200)}`);
        }
        
        const text = await response.text();
        if (!text || text.trim().length === 0) {
            throw new Error('תגובה ריקה מהשרת');
        }
        
        let data;
        try {
            data = JSON.parse(text);
        } catch (e) {
            console.error('JSON Parse Error:', e);
            console.error('Response text:', text.substring(0, 500));
            throw new Error('שגיאה בפענוח JSON מהשרת: ' + e.message);
        }
        
        if (!data.success) {
            throw new Error(data.error || 'שגיאה במחיקת רשומה');
        }
        
        return data;
    }
    
    // Export API
    window.API = {
        getRecords,
        getRecord,
        saveRecord,
        deleteRecord
    };
})();


