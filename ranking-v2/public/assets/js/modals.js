/**
 * Modals Module
 * Handles all modal operations
 */

(function() {
    'use strict';
    
    let currentSummaryRecordId = null;
    
    /**
     * Format JSON for display
     */
    function formatJSON(value) {
        if (!value) return '-';
        if (typeof value === 'string') {
            try {
                value = JSON.parse(value);
            } catch (e) {
                return value;
            }
        }
        if (Array.isArray(value)) {
            return value.join(', ');
        }
        if (typeof value === 'object') {
            return JSON.stringify(value, null, 2);
        }
        return String(value);
    }
    
    /**
     * Format date
     */
    function formatDate(dateString) {
        if (!dateString) return null;
        try {
            const date = new Date(dateString);
            return date.toLocaleDateString('he-IL');
        } catch (e) {
            return dateString;
        }
    }
    
    /**
     * Get status label
     */
    function getStatusLabel(status) {
        const labels = {
            'pending': 'ממתין',
            'extracted': 'חולץ',
            'failed': 'נכשל'
        };
        return labels[status] || status;
    }
    
    /**
     * Get relevance label
     */
    function getRelevanceLabel(level) {
        return `(${level}/5)`;
    }
    
    /**
     * Display record details in view modal
     */
    async function displayRecordDetails(id) {
        try {
            const record = await window.API.getRecord(id);
            
            const modalContent = document.getElementById('viewModalContent');
            if (!modalContent) return;
            
            modalContent.innerHTML = `
        <div class="detail-section">
            <h3>מידע בסיסי</h3>
            <div class="detail-grid">
                <div><strong>מזהה:</strong> ${record.id}</div>
                <div><strong>URL:</strong> <a href="${record.url}" target="_blank">${record.url}</a></div>
                <div><strong>תאריך יצירה:</strong> ${formatDate(record.created_at) || '-'}</div>
            </div>
        </div>
        
        <div class="detail-section">
            <h3>הקשר מוסדי</h3>
            <div class="detail-grid">
                <div><strong>סוג מקור:</strong> ${record.source_type || '-'}</div>
                <div><strong>סוג ארגון:</strong> ${record.organization_type || '-'}</div>
                <div><strong>רמת סמכות שיפוט:</strong> ${window.getLabel ? window.getLabel(record.jurisdiction_level, 'jurisdictionLevel') : record.jurisdiction_level || '-'}</div>
                <div><strong>היקף גיאוגרפי:</strong> ${record.geographic_scope || '-'}</div>
                <div><strong>שם גוף:</strong> ${record.organization_name || '-'}</div>
            </div>
        </div>
        
        <div class="detail-section">
            <h3>תחום תוכן</h3>
            <div class="detail-grid">
                <div><strong>קטגוריית נושא:</strong> ${window.getLabel ? window.getLabel(record.topic_category, 'topicCategory') : record.topic_category || '-'}</div>
                <div><strong>סוג מסמך:</strong> ${window.getLabel ? window.getLabel(record.document_type, 'documentType') : record.document_type || '-'}</div>
                <div><strong>קהל יעד:</strong> ${window.getLabel ? window.getLabel(record.target_audience, 'targetAudience') : record.target_audience || '-'}</div>
                <div><strong>שנה:</strong> ${record.year || '-'}</div>
            </div>
        </div>
        
        <div class="detail-section">
            <h3>אינדיקטורים אידיאולוגיים</h3>
            <div class="detail-grid">
                <div><strong>מוקד תרבותי:</strong> ${window.getLabel ? window.getLabel(record.cultural_focus, 'culturalFocus') : record.cultural_focus || '-'}</div>
                <div><strong>התייחסויות לציונות:</strong> ${window.getLabel ? window.getLabel(record.zionism_references, 'zionismReferences') : record.zionism_references || '-'}</div>
                <div><strong>אוריינטציית ערכים:</strong> ${formatJSON(record.values_orientation)}</div>
                <div><strong>נושאי זהות:</strong> ${formatJSON(record.identity_theme)}</div>
                <div><strong>תקופות היסטוריות:</strong> ${formatJSON(record.historical_periods)}</div>
            </div>
        </div>
        
        <div class="detail-section">
            <h3>שקיפות ושפה</h3>
            <div class="detail-grid">
                <div><strong>שפה:</strong> ${window.getLabel ? window.getLabel(record.language, 'language') : record.language || '-'}</div>
                <div><strong>רמת נגישות:</strong> ${window.getLabel ? window.getLabel(record.accessibility_level, 'accessibilityLevel') : record.accessibility_level || '-'}</div>
                <div><strong>פורמט פרסום:</strong> ${window.getLabel ? window.getLabel(record.publication_format, 'publicationFormat') : record.publication_format || '-'}</div>
            </div>
        </div>
        
        <div class="detail-section">
            <h3>מטא-דאטה</h3>
            <div class="detail-grid">
                <div><strong>סטטוס:</strong> <span class="status-badge status-${record.metadata_status}">${getStatusLabel(record.metadata_status)}</span></div>
                <div><strong>תאריך חילוץ:</strong> ${formatDate(record.metadata_extracted_at) || '-'}</div>
                ${record.failure_reason ? `<div style="grid-column: 1 / -1;"><strong>סיבת כשלון:</strong> ${record.failure_reason}</div>` : ''}
            </div>
        </div>
        
        ${record.manual_summary ? `
        <div class="detail-section">
            <h3>סיכום ידני</h3>
            <div class="summary-full">${record.manual_summary}</div>
        </div>
        ` : ''}
        
        ${record.short_summary ? `
        <div class="detail-section">
            <h3>סיכום קצר (AI)</h3>
            <div class="summary-full">${record.short_summary}</div>
        </div>
        ` : ''}
        
        ${record.relevance_level ? `
        <div class="detail-section">
            <h3>דירוג</h3>
            <div class="detail-grid">
                <div><strong>רמת רלוונטיות:</strong> ${record.relevance_level} ${getRelevanceLabel(record.relevance_level)}</div>
            </div>
        </div>
        ` : ''}
        
        <div class="detail-actions">
            <button class="btn btn-primary" onclick="window.Modals.closeViewModal(); window.App.editRecord(${record.id})">ערוך רשומה</button>
            ${!record.short_summary ? `<button class="btn btn-secondary" onclick="window.Modals.closeViewModal(); window.App.createSummary(${record.id})">צור סיכום</button>` : ''}
            ${record.metadata_status === 'failed' ? `<button class="btn btn-secondary" onclick="window.Modals.closeViewModal(); window.App.retryExtraction(${record.id})">נסה שוב</button>` : ''}
        </div>
    `;
            
            document.getElementById('viewModal').style.display = 'block';
        } catch (error) {
            console.error('Error:', error);
            alert('שגיאה בטעינת הרשומה: ' + error.message);
        }
    }
    
    /**
     * Open view summary modal
     */
    async function openViewSummaryModal(id) {
        try {
            const record = await window.API.getRecord(id);
            currentSummaryRecordId = id;
            
            const content = document.getElementById('viewSummaryContent');
            if (content) {
                const summary = record.short_summary || record.manual_summary || 'אין סיכום';
                content.textContent = summary;
            }
            
            const modal = document.getElementById('viewSummaryModal');
            if (modal) {
                modal.style.display = 'block';
            }
        } catch (error) {
            console.error('Error:', error);
            alert('שגיאה בטעינת הסיכום: ' + error.message);
        }
    }
    
    /**
     * Close view summary modal
     */
    function closeViewSummaryModal() {
        const modal = document.getElementById('viewSummaryModal');
        if (modal) {
            modal.style.display = 'none';
        }
        currentSummaryRecordId = null;
    }
    
    /**
     * Open edit summary modal
     */
    async function openEditSummaryModal(id) {
        try {
            const record = await window.API.getRecord(id);
            
            const recordIdInput = document.getElementById('editSummaryRecordId');
            const textInput = document.getElementById('editSummaryText');
            const modal = document.getElementById('editSummaryModal');
            
            if (recordIdInput) recordIdInput.value = id;
            if (textInput) textInput.value = record.short_summary || '';
            if (modal) modal.style.display = 'block';
        } catch (error) {
            console.error('Error:', error);
            alert('שגיאה בטעינת הסיכום: ' + error.message);
        }
    }
    
    /**
     * Close edit summary modal
     */
    function closeEditSummaryModal() {
        const modal = document.getElementById('editSummaryModal');
        if (modal) {
            modal.style.display = 'none';
        }
    }
    
    /**
     * Open add/edit record modal
     */
    async function openRecordModal(id) {
        const modal = document.getElementById('recordModal');
        const title = document.getElementById('modalTitle');
        const form = document.getElementById('recordForm');
        
        if (!modal || !title || !form) return;
        
        if (id) {
            try {
                const record = await window.API.getRecord(id);
                populateForm(record);
                title.textContent = 'ערוך רשומה';
            } catch (error) {
                console.error('Error:', error);
                alert('שגיאה בטעינת הרשומה: ' + error.message);
                return;
            }
        } else {
            form.reset();
            document.getElementById('recordId').value = '';
            title.textContent = 'הוסף רשומה חדשה';
        }
        
        modal.style.display = 'block';
    }
    
    /**
     * Populate form with record data
     */
    function populateForm(record) {
        const fields = {
            'recordId': record.id,
            'url': record.url || '',
            'sourceType': record.source_type || '',
            'organizationName': record.organization_name || '',
            'organizationType': record.organization_type || '',
            'jurisdictionLevel': record.jurisdiction_level || '',
            'geographicScope': record.geographic_scope || '',
            'topicCategory': record.topic_category || '',
            'documentType': record.document_type || '',
            'targetAudience': record.target_audience || '',
            'year': record.year || '',
            'culturalFocus': record.cultural_focus || '',
            'zionismReferences': record.zionism_references || '',
            'valuesOrientation': record.values_orientation ? JSON.stringify(record.values_orientation, null, 2) : '',
            'identityTheme': record.identity_theme ? JSON.stringify(record.identity_theme, null, 2) : '',
            'language': record.language || '',
            'accessibilityLevel': record.accessibility_level || '',
            'publicationFormat': record.publication_format || '',
            'shortSummary': record.short_summary || '',
            'manualSummary': record.manual_summary || '',
            'relevanceLevel': record.relevance_level || '',
            'aiRelevanceScore': record.ai_relevance_score || '',
            'aiRelevanceReason': record.ai_relevance_reason || '',
            'metadataStatus': record.metadata_status || 'pending'
        };
        
        for (const [fieldId, value] of Object.entries(fields)) {
            const element = document.getElementById(fieldId);
            if (element) {
                element.value = value;
            }
        }
    }
    
    /**
     * Close record modal
     */
    function closeRecordModal() {
        const modal = document.getElementById('recordModal');
        const form = document.getElementById('recordForm');
        
        if (modal) modal.style.display = 'none';
        if (form) form.reset();
    }
    
    /**
     * Close view modal
     */
    function closeViewModal() {
        const modal = document.getElementById('viewModal');
        if (modal) modal.style.display = 'none';
    }
    
    // Close modals when clicking outside
    window.onclick = function(event) {
        // Don't handle clicks on editable cells - let the inline editing handler deal with them
        if (event.target.closest('.editable-category') || event.target.closest('.editable-organization-type') || event.target.closest('.editable-relevance')) {
            return;
        }
        
        const recordModal = document.getElementById('recordModal');
        const viewModal = document.getElementById('viewModal');
        const viewSummaryModal = document.getElementById('viewSummaryModal');
        const editSummaryModal = document.getElementById('editSummaryModal');
        
        if (event.target === recordModal) {
            closeRecordModal();
        }
        if (event.target === viewModal) {
            closeViewModal();
        }
        if (event.target === viewSummaryModal) {
            closeViewSummaryModal();
        }
        if (event.target === editSummaryModal) {
            closeEditSummaryModal();
        }
    };
    
    /**
     * Open edit summary from view modal
     */
    function openEditSummaryFromView() {
        if (currentSummaryRecordId) {
            closeViewSummaryModal();
            openEditSummaryModal(currentSummaryRecordId);
        }
    }
    
    /**
     * View record (opens view modal)
     */
    async function viewRecord(id) {
        await displayRecordDetails(id);
    }
    
    // Export to global scope
    window.Modals = {
        displayRecordDetails,
        viewRecord,
        openViewSummaryModal,
        closeViewSummaryModal,
        openEditSummaryModal,
        closeEditSummaryModal,
        openEditSummaryFromView,
        openRecordModal,
        openAddModal: () => openRecordModal(null),
        closeRecordModal,
        closeViewModal,
        closeModal: closeRecordModal,
        populateForm
    };
    
    // Export lowercase alias for backward compatibility
    window.modals = window.Modals;
    
    // Export for backward compatibility
    window.openViewSummaryModal = openViewSummaryModal;
    window.closeViewSummaryModal = closeViewSummaryModal;
    window.closeViewModal = closeViewModal;
    window.closeModal = closeRecordModal;
    window.openAddModal = () => openRecordModal(null);
    window.editRecord = (id) => openRecordModal(id);
})();


