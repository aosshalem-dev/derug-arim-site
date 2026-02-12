// ============================================
// COMPLETE OVERHAUL - VERSION 4.0
// ============================================

// CRITICAL: Define createSummary IMMEDIATELY at global scope
window.createSummary = async function(id) {
    console.log('=== createSummary CALLED ===', id);
    
    if (!id) {
        alert('שגיאה: מזהה רשומה לא תקין');
        return;
    }
    
    const btnId = `summary-btn-${id}`;
    const btn = document.getElementById(btnId);
    
    if (btn) {
        btn.disabled = true;
        btn.innerHTML = 'יוצר...';
    }
    
    try {
        console.log('Sending request to api/create_summary.php', { id: id });
        
        const response = await fetch('api/create_summary.php', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json'
            },
            body: JSON.stringify({ id: id })
        });
        
        console.log('Response received', {
            status: response.status,
            statusText: response.statusText,
            ok: response.ok
        });
        
        let data;
        try {
            const responseText = await response.text();
            console.log('Response text:', responseText.substring(0, 500));
            data = JSON.parse(responseText);
        } catch (parseError) {
            console.error('Failed to parse response as JSON:', parseError);
            throw new Error(`שגיאה בפענוח תגובת השרת (HTTP ${response.status}): ${parseError.message}`);
        }
        
        if (!response.ok) {
            // Build detailed error message
            let errorMsg = `שגיאת HTTP ${response.status}: `;
            if (data.error) {
                errorMsg += data.error;
            } else {
                errorMsg += response.statusText;
            }
            
            // Add details if available
            if (data.details) {
                if (typeof data.details === 'object') {
                    errorMsg += '\n\nפרטים:\n' + JSON.stringify(data.details, null, 2);
                } else {
                    errorMsg += '\n\nפרטים: ' + data.details;
                }
            }
            
            console.error('API Error:', {
                status: response.status,
                error: data.error,
                details: data.details
            });
            
            alert(errorMsg);
            
            if (btn) {
                btn.disabled = false;
                btn.innerHTML = 'צור סיכום';
            }
            return;
        }
        
        if (data.success) {
            console.log('Summary created successfully:', data.summary);
            loadRecords();
            alert('סיכום נוצר בהצלחה!\n\n' + (data.summary ? data.summary.substring(0, 200) : ''));
        } else {
            let errorMsg = 'שגיאה: ' + (data.error || 'שגיאה ביצירת סיכום');
            if (data.details) {
                if (typeof data.details === 'object') {
                    errorMsg += '\n\nפרטים:\n' + JSON.stringify(data.details, null, 2);
                } else {
                    errorMsg += '\n\nפרטים: ' + data.details;
                }
            }
            
            console.error('API returned success=false:', data);
            alert(errorMsg);
            
            if (btn) {
                btn.disabled = false;
                btn.innerHTML = 'צור סיכום';
            }
        }
    } catch (error) {
        console.error('Error in createSummary:', error);
        console.error('Error stack:', error.stack);
        
        let errorMsg = 'שגיאה בחיבור לשרת:\n' + error.message;
        if (error.stack) {
            console.error('Full error:', error);
        }
        
        alert(errorMsg);
        
        if (btn) {
            btn.disabled = false;
            btn.innerHTML = 'צור סיכום';
        }
    }
};

// Verify function is accessible
console.log('=== SCRIPT LOADED ===');
console.log('window.createSummary type:', typeof window.createSummary);
console.log('window.createSummary:', window.createSummary);

// Global state
// Pagination removed - all records loaded at once
let currentFilters = {};

// Initialize app
if (document.readyState === 'loading') {
document.addEventListener('DOMContentLoaded', function() {
    console.log('=== DOM CONTENT LOADED ===');
    initInlineCategoryEditing();
    loadRecords();
});
} else {
    console.log('=== DOM ALREADY LOADED ===');
    initInlineCategoryEditing();
    loadRecords();
}

// Load records with current filters
async function loadRecords() {
    console.log('=== LOAD RECORDS CALLED ===');
    
    const sortBy = document.getElementById('sortBy').value;
    const sortOrder = document.getElementById('sortOrder').value;
    
    const params = new URLSearchParams({
        page: 1,
        pageSize: 10000, // Load all records
        sortBy: sortBy,
        sortOrder: sortOrder,
        ...currentFilters
    });
    
    try {
        const response = await fetch(`api/get_records.php?${params}`);
        
        if (!response.ok) {
            throw new Error(`HTTP error! status: ${response.status}`);
        }
        
        const data = await response.json();
        
        if (data.success) {
            displayRecords(data.records);
            updateStats(data.stats);
        } else {
            const errorMsg = data.error || 'שגיאה בטעינת הנתונים';
            showError(errorMsg);
            const tbody = document.getElementById('tableBody');
            tbody.innerHTML = `<tr><td colspan="10" class="no-data" style="color: red;">שגיאה: ${errorMsg}</td></tr>`;
        }
    } catch (error) {
        console.error('Error:', error);
        const errorMsg = 'שגיאה בחיבור לשרת: ' + error.message;
        showError(errorMsg);
        const tbody = document.getElementById('tableBody');
        tbody.innerHTML = `<tr><td colspan="10" class="no-data" style="color: red;">${errorMsg}</td></tr>`;
    }
}

// Display records in table
function displayRecords(records) {
    console.log('=== DISPLAY RECORDS CALLED ===');
    console.log('Records count:', records.length);
    
    const tbody = document.getElementById('tableBody');
    
    if (!tbody) {
        console.error('ERROR: tableBody element not found!');
        return;
    }
    
    if (records.length === 0) {
            tbody.innerHTML = '<tr><td colspan="10" class="no-data">לא נמצאו רשומות</td></tr>';
        return;
    }
    
    // Build table rows
    const rows = records.map((record, index) => {
        const hasSummary = record.short_summary && String(record.short_summary).trim().length > 0;
        const summaryText = hasSummary ? String(record.short_summary).trim() : '';
        const hasManualSummary = record.manual_summary && String(record.manual_summary).trim().length > 0;
        const manualSummaryText = hasManualSummary ? String(record.manual_summary).trim() : '';
        
        // Debug: log organization_name
        if (index === 0) {
            console.log('First record organization_name:', record.organization_name);
        }
        
        return `
        <tr>
            <td>${record.id}</td>
            <td class="url-cell">
                <a href="${record.url}" target="_blank" title="${record.url}">
                    ${getDomainFromUrl(record.url)}
                </a>
            </td>
            <td class="organization-name-cell" style="min-width: 120px;">${record.organization_name || '-'}</td>
            <td class="editable-organization-type" data-record-id="${record.id}" data-current-value="${record.organization_type || ''}" style="cursor: pointer; padding: 8px;" title="לחץ לעריכה">
                <span class="org-type-display">${getLabel(record.organization_type, 'organizationType') || '-'}</span>
                <select class="org-type-edit" style="display: none; width: 100%;" onchange="saveOrganizationTypeInline(${record.id}, this.value)">
                    <option value="">בחר...</option>
                    <option value="municipality" ${record.organization_type === 'municipality' ? 'selected' : ''}>רשות מקומית</option>
                    <option value="government_agency" ${record.organization_type === 'government_agency' ? 'selected' : ''}>סוכנות ממשלתית</option>
                    <option value="media" ${record.organization_type === 'media' ? 'selected' : ''}>תקשורת</option>
                    <option value="educational_institution" ${record.organization_type === 'educational_institution' ? 'selected' : ''}>מוסד חינוכי</option>
                    <option value="ngo" ${record.organization_type === 'ngo' ? 'selected' : ''}>עמותה</option>
                    <option value="research_institution" ${record.organization_type === 'research_institution' ? 'selected' : ''}>מוסד מחקר</option>
                    <option value="other" ${record.organization_type === 'other' ? 'selected' : ''}>אחר</option>
                </select>
            </td>
            <td class="editable-category" data-record-id="${record.id}" data-current-value="${record.topic_category || ''}" style="cursor: pointer; padding: 8px;" title="לחץ לעריכה">
                <span class="category-display">${getLabel(record.topic_category, 'topicCategory') || '-'}</span>
                <select class="category-edit" style="display: none; width: 100%;" onchange="saveCategoryInline(${record.id}, this.value)">
                    <option value="">בחר...</option>
                    <option value="education" ${record.topic_category === 'education' ? 'selected' : ''}>חינוך</option>
                    <option value="culture" ${record.topic_category === 'culture' ? 'selected' : ''}>תרבות</option>
                    <option value="policy" ${record.topic_category === 'policy' ? 'selected' : ''}>מדיניות</option>
                    <option value="news" ${record.topic_category === 'news' ? 'selected' : ''}>חדשות</option>
                    <option value="research" ${record.topic_category === 'research' ? 'selected' : ''}>מחקר</option>
                    <option value="heritage" ${record.topic_category === 'heritage' ? 'selected' : ''}>מורשת</option>
                    <option value="community" ${record.topic_category === 'community' ? 'selected' : ''}>קהילה</option>
                    <option value="other" ${record.topic_category === 'other' ? 'selected' : ''}>אחר</option>
                </select>
            </td>
            <td>${record.year || '-'}</td>
            <td style="text-align: center;">
                ${record.relevance_level ? 
                    `<span style="font-weight: bold; color: ${getRelevanceColor(record.relevance_level)};">${record.relevance_level}</span>` : 
                    '-'
                }
            </td>
            <td style="text-align: center;">
                ${record.ai_relevance_score ? 
                    `<span style="font-weight: bold; color: ${getRelevanceColor(record.ai_relevance_score)};" title="${record.ai_relevance_reason || ''}">${record.ai_relevance_score}</span>` : 
                    '-'
                }
            </td>
            <td class="summary-cell" style="min-width: 700px;">
                ${hasManualSummary || hasSummary ? 
                    `<div style="display: flex; flex-direction: column; gap: 10px;">
                        ${hasManualSummary ? 
                            `<div class="summary-text-clickable" title="לחץ לצפייה בסיכום המלא" onclick="openViewSummaryModal(${record.id})" data-record-id="${record.id}" style="cursor: pointer; color: #4CAF50; text-decoration: underline; font-weight: 500; line-height: 1.6;">
                                <div style="font-weight: bold; margin-bottom: 6px;">📝 סיכום ידני:</div>
                                <div style="display: -webkit-box; -webkit-line-clamp: 5; -webkit-box-orient: vertical; overflow: hidden; line-height: 1.6;">${manualSummaryText}</div>
                            </div>` : ''
                        }
                        ${hasSummary ? 
                            `<div class="summary-text-clickable" title="לחץ לצפייה בסיכום המלא" onclick="openViewSummaryModal(${record.id})" data-record-id="${record.id}" style="cursor: pointer; color: #2196F3; text-decoration: underline; line-height: 1.6;">
                                <div style="font-weight: bold; margin-bottom: 6px;">${hasManualSummary ? '🤖' : '📝'} ${hasManualSummary ? 'סיכום אוטומטי' : 'סיכום'}:</div>
                                <div style="display: -webkit-box; -webkit-line-clamp: 5; -webkit-box-orient: vertical; overflow: hidden; line-height: 1.6;">${summaryText}</div>
                            </div>` : ''
                        }
                    </div>` : 
                    '<span class="no-summary-text">-</span>'
                }
            </td>
            <td class="actions-cell">
                <button class="btn-icon" onclick="editRecord(${record.id})" title="ערוך">
                    ✏️
                </button>
                ${record.metadata_status === 'failed' ? `
                <button class="btn-icon btn-retry" onclick="retryExtraction(${record.id})" title="נסה שוב" id="retry-btn-${record.id}">
                    🔄
                </button>
                ` : ''}
                <button class="btn-icon btn-danger" onclick="deleteRecord(${record.id})" title="מחק">
                    🗑️
                </button>
            </td>
        </tr>
    `;
    });
    
    const htmlContent = rows.join('');
    tbody.innerHTML = htmlContent;
    
    console.log('=== DISPLAY RECORDS COMPLETED ===');
    console.log('Total records:', records.length);
    
    // Re-initialize inline editing for newly created elements
    // (event delegation should handle it, but just to be safe)
    document.querySelectorAll('.editable-category, .editable-organization-type').forEach(cell => {
        if (!cell.hasAttribute('data-initialized')) {
            cell.setAttribute('data-initialized', 'true');
        }
    });
    
    // Update displayed records count
    updateDisplayedCount(records.length);
}

// Pagination removed - all records displayed in one scrollable table

// Update stats
function updateStats(stats) {
    document.getElementById('totalRecords').textContent = stats.total;
    document.getElementById('pendingRecords').textContent = stats.pending;
    // Displayed records will be updated after displayRecords is called
}

// Update displayed records count
function updateDisplayedCount(count) {
    document.getElementById('displayedRecords').textContent = count;
}

// Apply filters
function applyFilters() {
    const searchUrl = document.getElementById('searchUrl').value.trim();
    const status = document.getElementById('filterStatus').value;
    const orgType = document.getElementById('filterOrgType').value;
    const topic = document.getElementById('filterTopic').value;
    const year = document.getElementById('filterYear').value.trim();
    const aiRelevance = document.getElementById('filterAIRelevance').value;
    const unrated = document.getElementById('filterUnrated').checked;
    
    currentFilters = {};
    
    if (searchUrl) currentFilters.searchUrl = searchUrl;
    if (status) currentFilters.status = status;
    if (orgType) currentFilters.orgType = orgType;
    if (topic) currentFilters.topic = topic;
    if (year) currentFilters.year = year;
    if (aiRelevance) currentFilters.aiRelevanceMin = aiRelevance;
    if (unrated) currentFilters.unrated = '1';
    
    loadRecords();
}

// Clear filters
function clearFilters() {
    document.getElementById('searchUrl').value = '';
    document.getElementById('filterStatus').value = '';
    document.getElementById('filterOrgType').value = '';
    document.getElementById('filterTopic').value = '';
    document.getElementById('filterYear').value = '';
    document.getElementById('filterAIRelevance').value = '';
    document.getElementById('filterUnrated').checked = false;
    currentFilters = {};
    loadRecords();
}

// View record details
async function viewRecord(id) {
    try {
        const response = await fetch(`api/get_record.php?id=${id}`);
        const data = await response.json();
        
        if (data.success) {
            displayRecordDetails(data.record);
            document.getElementById('viewModal').style.display = 'block';
        } else {
            showError('שגיאה בטעינת הרשומה');
        }
    } catch (error) {
        console.error('Error:', error);
        showError('שגיאה בחיבור לשרת');
    }
}

// Display record details
function displayRecordDetails(record) {
    const body = document.getElementById('viewModalBody');
    
    body.innerHTML = `
        <div class="detail-section">
            <h3>פרטים בסיסיים</h3>
            <div class="detail-grid">
                <div><strong>מזהה:</strong> ${record.id}</div>
                <div><strong>URL:</strong> <a href="${record.url}" target="_blank">${record.url}</a></div>
                <div><strong>תאריך יצירה:</strong> ${formatDate(record.created_at)}</div>
            </div>
        </div>
        
        <div class="detail-section">
            <h3>הקשר מוסדי</h3>
            <div class="detail-grid">
                <div><strong>סוג מקור:</strong> ${record.source_type || '-'}</div>
                <div><strong>סוג ארגון:</strong> ${getLabel(record.organization_type, 'organizationType') || '-'}</div>
                <div><strong>רמת סמכות שיפוט:</strong> ${getLabel(record.jurisdiction_level, 'jurisdictionLevel') || '-'}</div>
                <div><strong>היקף גיאוגרפי:</strong> ${record.geographic_scope || '-'}</div>
            </div>
        </div>
        
        <div class="detail-section">
            <h3>תחום תוכן</h3>
            <div class="detail-grid">
                <div><strong>קטגוריית נושא:</strong> ${getLabel(record.topic_category, 'topicCategory') || '-'}</div>
                <div><strong>סוג מסמך:</strong> ${getLabel(record.document_type, 'documentType') || '-'}</div>
                <div><strong>קהל יעד:</strong> ${getLabel(record.target_audience, 'targetAudience') || '-'}</div>
                <div><strong>שנה:</strong> ${record.year || '-'}</div>
            </div>
        </div>
        
        <div class="detail-section">
            <h3>אינדיקטורים אידיאולוגיים</h3>
            <div class="detail-grid">
                <div><strong>מוקד תרבותי:</strong> ${getLabel(record.cultural_focus, 'culturalFocus') || '-'}</div>
                <div><strong>התייחסויות לציונות:</strong> ${getLabel(record.zionism_references, 'zionismReferences') || '-'}</div>
                <div><strong>אוריינטציית ערכים:</strong> ${formatJSON(record.values_orientation)}</div>
                <div><strong>נושאי זהות:</strong> ${formatJSON(record.identity_theme)}</div>
                <div><strong>תקופות היסטוריות:</strong> ${formatJSON(record.historical_periods)}</div>
            </div>
        </div>
        
        <div class="detail-section">
            <h3>שקיפות ושפה</h3>
            <div class="detail-grid">
                <div><strong>שפה:</strong> ${getLabel(record.language, 'language') || '-'}</div>
                <div><strong>רמת נגישות:</strong> ${getLabel(record.accessibility_level, 'accessibilityLevel') || '-'}</div>
                <div><strong>פורמט פרסום:</strong> ${getLabel(record.publication_format, 'publicationFormat') || '-'}</div>
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
            <button class="btn btn-primary" onclick="closeViewModal(); editRecord(${record.id})">ערוך רשומה</button>
            ${!record.short_summary ? `<button class="btn btn-secondary" onclick="closeViewModal(); window.createSummary(${record.id})">צור סיכום</button>` : ''}
            ${record.metadata_status === 'failed' ? `<button class="btn btn-secondary" onclick="closeViewModal(); retryExtraction(${record.id})">נסה שוב</button>` : ''}
        </div>
    `;
}

// Edit record
async function editRecord(id) {
    if (id) {
        try {
            const response = await fetch(`api/get_record.php?id=${id}`);
            const data = await response.json();
            
            if (data.success) {
                populateForm(data.record);
                document.getElementById('modalTitle').textContent = 'ערוך רשומה';
                document.getElementById('recordModal').style.display = 'block';
            } else {
                showError('שגיאה בטעינת הרשומה');
            }
        } catch (error) {
            console.error('Error:', error);
            showError('שגיאה בחיבור לשרת');
        }
    } else {
        document.getElementById('recordForm').reset();
        document.getElementById('recordId').value = '';
        document.getElementById('modalTitle').textContent = 'הוסף רשומה חדשה';
        document.getElementById('recordModal').style.display = 'block';
    }
}

// Populate form with record data
function populateForm(record) {
    document.getElementById('recordId').value = record.id;
    document.getElementById('url').value = record.url || '';
    document.getElementById('sourceType').value = record.source_type || '';
    document.getElementById('organizationName').value = record.organization_name || '';
    document.getElementById('organizationType').value = record.organization_type || '';
    document.getElementById('jurisdictionLevel').value = record.jurisdiction_level || '';
    document.getElementById('geographicScope').value = record.geographic_scope || '';
    document.getElementById('topicCategory').value = record.topic_category || '';
    document.getElementById('documentType').value = record.document_type || '';
    document.getElementById('targetAudience').value = record.target_audience || '';
    document.getElementById('year').value = record.year || '';
    document.getElementById('culturalFocus').value = record.cultural_focus || '';
    document.getElementById('zionismReferences').value = record.zionism_references || '';
    document.getElementById('valuesOrientation').value = record.values_orientation ? JSON.stringify(record.values_orientation, null, 2) : '';
    document.getElementById('identityTheme').value = record.identity_theme ? JSON.stringify(record.identity_theme, null, 2) : '';
    document.getElementById('language').value = record.language || '';
    document.getElementById('accessibilityLevel').value = record.accessibility_level || '';
    document.getElementById('publicationFormat').value = record.publication_format || '';
    document.getElementById('shortSummary').value = record.short_summary || '';
    document.getElementById('manualSummary').value = record.manual_summary || '';
    document.getElementById('relevanceLevel').value = record.relevance_level || '';
    document.getElementById('aiRelevanceScore').value = record.ai_relevance_score || '';
    document.getElementById('aiRelevanceReason').value = record.ai_relevance_reason || '';
    document.getElementById('metadataStatus').value = record.metadata_status || 'pending';
}

// Open add modal
function openAddModal() {
    editRecord(null);
}

// Close modal
function closeModal() {
    document.getElementById('recordModal').style.display = 'none';
    document.getElementById('recordForm').reset();
}

// Close view modal
function closeViewModal() {
    document.getElementById('viewModal').style.display = 'none';
}

// Store current summary ID for editing from view modal
let currentSummaryRecordId = null;

// Open view summary modal (shows full summary)
async function openViewSummaryModal(id) {
    try {
        // Fetch current summary
        const response = await fetch(`api/get_record.php?id=${id}`);
        const data = await response.json();
        
        if (data.success) {
            currentSummaryRecordId = id;
            const summary = data.record.short_summary || 'אין סיכום';
            document.getElementById('viewSummaryContent').textContent = summary;
            document.getElementById('viewSummaryModal').style.display = 'block';
        } else {
            showError('שגיאה בטעינת הסיכום');
        }
    } catch (error) {
        console.error('Error:', error);
        showError('שגיאה בחיבור לשרת');
    }
}

// Close view summary modal
function closeViewSummaryModal() {
    document.getElementById('viewSummaryModal').style.display = 'none';
    currentSummaryRecordId = null;
}

// Open edit summary modal from view modal
function openEditSummaryFromView() {
    if (currentSummaryRecordId) {
        closeViewSummaryModal();
        openEditSummaryModal(currentSummaryRecordId);
    }
}

// Open edit summary modal
async function openEditSummaryModal(id) {
    try {
        // Fetch current summary
        const response = await fetch(`api/get_record.php?id=${id}`);
        const data = await response.json();
        
        if (data.success) {
            document.getElementById('editSummaryRecordId').value = id;
            document.getElementById('editSummaryText').value = data.record.short_summary || '';
            document.getElementById('editSummaryModal').style.display = 'block';
        } else {
            showError('שגיאה בטעינת הסיכום');
        }
    } catch (error) {
        console.error('Error:', error);
        showError('שגיאה בחיבור לשרת');
    }
}

// Close edit summary modal
function closeEditSummaryModal() {
    document.getElementById('editSummaryModal').style.display = 'none';
    document.getElementById('editSummaryForm').reset();
}

// Save record
document.addEventListener('DOMContentLoaded', function() {
    const form = document.getElementById('recordForm');
    if (form) {
        form.addEventListener('submit', async function(e) {
            e.preventDefault();
            
            const formData = {
                id: document.getElementById('recordId').value || null,
                url: document.getElementById('url').value,
                source_type: document.getElementById('sourceType').value || null,
                year: document.getElementById('year').value ? parseInt(document.getElementById('year').value) : null,
                organization_name: document.getElementById('organizationName').value.trim() || null,
                organization_type: document.getElementById('organizationType').value || null,
                jurisdiction_level: document.getElementById('jurisdictionLevel').value || null,
                geographic_scope: document.getElementById('geographicScope').value || null,
                topic_category: document.getElementById('topicCategory').value || null,
                document_type: document.getElementById('documentType').value || null,
                target_audience: document.getElementById('targetAudience').value || null,
                cultural_focus: document.getElementById('culturalFocus').value || null,
                zionism_references: document.getElementById('zionismReferences').value || null,
                language: document.getElementById('language').value || null,
                accessibility_level: document.getElementById('accessibilityLevel').value || null,
                publication_format: document.getElementById('publicationFormat').value || null,
                short_summary: document.getElementById('shortSummary').value.trim() || null,
                manual_summary: document.getElementById('manualSummary').value.trim() || null,
                relevance_level: document.getElementById('relevanceLevel').value ? parseInt(document.getElementById('relevanceLevel').value) : null,
                ai_relevance_score: document.getElementById('aiRelevanceScore').value ? parseInt(document.getElementById('aiRelevanceScore').value) : null,
                ai_relevance_reason: document.getElementById('aiRelevanceReason').value.trim() || null,
                metadata_status: document.getElementById('metadataStatus').value || 'pending'
            };
            
            try {
                const valuesOrientation = document.getElementById('valuesOrientation').value.trim();
                if (valuesOrientation) {
                    formData.values_orientation = JSON.parse(valuesOrientation);
                }
            } catch (e) {
                showError('שגיאה בפורמט JSON של אוריינטציית ערכים');
                return;
            }
            
            try {
                const identityTheme = document.getElementById('identityTheme').value.trim();
                if (identityTheme) {
                    formData.identity_theme = JSON.parse(identityTheme);
                }
            } catch (e) {
                showError('שגיאה בפורמט JSON של נושאי זהות');
                return;
            }
            
            try {
                console.log('=== SAVE RECORD REQUEST START ===');
                console.log('Form data:', formData);
                
                const response = await fetch('api/save_record.php', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json'
                    },
                    body: JSON.stringify(formData)
                });
                
                console.log('Response received:', {
                    status: response.status,
                    statusText: response.statusText,
                    ok: response.ok,
                    headers: Object.fromEntries(response.headers.entries())
                });
                
                // Get response as text first
                const text = await response.text();
                console.log('Response text length:', text.length);
                console.log('Response text preview:', text.substring(0, 500));
                
                // Check if response is empty
                if (!text || text.trim().length === 0) {
                    console.error('✗ Empty response received');
                    showError('השרת החזיר תגובה ריקה. בדוק את לוגי השרת.');
                    return;
                }
                
                // Check if response is OK
                if (!response.ok) {
                    console.error('✗ HTTP Error:', response.status, text);
                    try {
                        const errorData = JSON.parse(text);
                        const errorMsg = errorData.error || 'שגיאה בשמירת הרשומה';
                        const details = errorData.details ? `\nפרטים: ${JSON.stringify(errorData.details)}` : '';
                        console.error('Parsed error data:', errorData);
                        showError(errorMsg + details);
                    } catch (e) {
                        console.error('✗ Failed to parse error response:', e);
                        console.error('Raw error response:', text);
                        showError('שגיאה בשמירת הרשומה: ' + text.substring(0, 200));
                    }
                    return;
                }
                
                // Parse JSON response
                let data;
                try {
                    data = JSON.parse(text);
                    console.log('✓ JSON parsed successfully:', data);
                } catch (e) {
                    console.error('✗ JSON parse error:', e);
                    console.error('Response text:', text);
                    console.error('Response length:', text.length);
                    console.error('Response first 500 chars:', text.substring(0, 500));
                    showError('שגיאה בפענוח תגובת השרת. התגובה לא תקינה: ' + text.substring(0, 200));
                    return;
                }
                
                // Check if data has success property
                if (typeof data.success === 'undefined') {
                    console.error('✗ Response missing success property:', data);
                    showError('תגובת השרת לא תקינה: חסר שדה success');
                    return;
                }
                
                if (data.success) {
                    console.log('✓ Save successful:', data);
                    closeModal();
                    loadRecords();
                    showSuccess(data.message);
                } else {
                    const errorMsg = data.error || 'שגיאה בשמירת הרשומה';
                    const details = data.details ? `\nפרטים: ${JSON.stringify(data.details)}` : '';
                    console.error('✗ Save failed:', data);
                    showError(errorMsg + details);
                }
            } catch (error) {
                console.error('✗ Exception in save request:', error);
                console.error('Error name:', error.name);
                console.error('Error message:', error.message);
                console.error('Error stack:', error.stack);
                showError('שגיאה בחיבור לשרת: ' + error.message);
            }
        });
    }
});

// Delete record
async function deleteRecord(id) {
    if (!confirm('האם אתה בטוח שברצונך למחוק רשומה זו?')) {
        return;
    }
    
    try {
        const response = await fetch('api/delete_record.php', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json'
            },
            body: JSON.stringify({ id: id })
        });
        
        const data = await response.json();
        
        if (data.success) {
            loadRecords();
            showSuccess(data.message);
        } else {
            showError(data.error || 'שגיאה במחיקת הרשומה');
        }
    } catch (error) {
        console.error('Error:', error);
        showError('שגיאה בחיבור לשרת');
    }
}

// Retry extraction
async function retryExtraction(id) {
    const btn = document.getElementById(`retry-btn-${id}`);
    if (!btn) return;
    
    if (!confirm('האם אתה בטוח שברצונך לנסות שוב לחלץ מטא-דאטה?')) {
        return;
    }
    
    btn.disabled = true;
    btn.innerHTML = '⏳';
    btn.title = 'מנסה שוב...';
    
    try {
        const response = await fetch('api/retry_extraction.php', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json'
            },
            body: JSON.stringify({ id: id })
        });
        
        const data = await response.json();
        
        if (data.success) {
            loadRecords();
            showSuccess(data.message);
        } else {
            let errorMsg = data.error || 'שגיאה בניסיון חילוץ מטא-דאטה';
            if (data.failure_reason) {
                errorMsg += '\n\nסיבת כשלון:\n' + data.failure_reason;
            }
            alert(errorMsg);
        }
    } catch (error) {
        console.error('Error:', error);
        showError('שגיאה בחיבור לשרת');
    } finally {
        btn.disabled = false;
        btn.innerHTML = '🔄';
        btn.title = 'נסה שוב';
    }
}

// Export data
function exportData() {
    window.location.href = 'export_to_json.php';
}

// AI Relevance Rating Functions
let currentAIRatingJobId = null;
let airatingInterval = null;

async function startAIRating() {
    const btn = document.getElementById('startAIRatingBtn');
    if (!btn) return;
    
    if (!confirm('האם אתה בטוח שברצונך להתחיל דירוג AI לכל הרשומות?\nזה עלול לקחת זמן רב.')) {
        return;
    }
    
    btn.disabled = true;
    const originalText = btn.innerHTML;
    btn.innerHTML = '<span>⏳</span> מתחיל...';
    
    const progressDiv = document.getElementById('aiRatingProgress');
    const progressText = document.getElementById('aiProgressText');
    const progressLog = document.getElementById('aiProgressLog');
    const cancelBtn = document.getElementById('cancelAIRatingBtn');
    
    progressDiv.style.display = 'block';
    progressText.textContent = '0/0';
    progressLog.textContent = 'מתחיל עבודה...';
    cancelBtn.disabled = false;
    
    try {
        const response = await fetch('api/ai/start_rate_relevance.php', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json'
            },
            body: JSON.stringify({
                only_unrated: true,
                limit: 0
            })
        });
        
        const data = await response.json();
        
        if (!data.success) {
            throw new Error(data.error || 'שגיאה בהתחלת עבודה');
        }
        
        currentAIRatingJobId = data.job_id;
        progressText.textContent = `0/${data.total}`;
        progressLog.textContent = `עבודה התחילה. סה"כ: ${data.total} רשומות\n`;
        
        // Start polling for progress
        airatingInterval = setInterval(() => {
            processAIRatingStep();
        }, 2000); // Poll every 2 seconds
        
        // Process first step immediately
        processAIRatingStep();
        
    } catch (error) {
        console.error('Error:', error);
        alert('שגיאה בהתחלת דירוג AI: ' + error.message);
        btn.disabled = false;
        btn.innerHTML = originalText;
        progressDiv.style.display = 'none';
    }
}

async function processAIRatingStep() {
    if (!currentAIRatingJobId) return;
    
    try {
        // Check status first
        const statusResponse = await fetch(`api/ai/rate-relevance-status.php?job_id=${currentAIRatingJobId}`);
        const statusData = await statusResponse.json();
        
        if (!statusData.success) {
            throw new Error(statusData.error || 'שגיאה בבדיקת סטטוס');
        }
        
        const progress = statusData.progress;
        const progressText = document.getElementById('aiProgressText');
        const progressLog = document.getElementById('aiProgressLog');
        
        progressText.textContent = `${progress.processed}/${progress.total}`;
        
        if (statusData.cancelled) {
            clearInterval(airatingInterval);
            airatingInterval = null;
            progressLog.textContent += '\nעבודה בוטלה';
            resetAIRatingUI();
            return;
        }
        
        if (statusData.completed) {
            clearInterval(airatingInterval);
            airatingInterval = null;
            progressLog.textContent += `\n✅ הושלם! דורגו: ${progress.done}, דולגו: ${progress.skipped}, שגיאות: ${progress.error}`;
            resetAIRatingUI();
            loadRecords(); // Refresh table
            return;
        }
        
        // Process one URL
        const processResponse = await fetch(`api/ai/process_rate_relevance.php?job_id=${currentAIRatingJobId}`);
        const processData = await processResponse.json();
        
        if (!processData.success) {
            throw new Error(processData.error || 'שגיאה בעיבוד');
        }
        
        if (processData.completed) {
            clearInterval(airatingInterval);
            airatingInterval = null;
            progressLog.textContent += `\n✅ הושלם! דורגו: ${processData.progress.done}, דולגו: ${processData.progress.skipped}, שגיאות: ${processData.progress.error}`;
            resetAIRatingUI();
            loadRecords(); // Refresh table
            return;
        }
        
        // Update log
        if (processData.last_url) {
            const url = processData.last_url.length > 50 ? processData.last_url.substring(0, 50) + '...' : processData.last_url;
            const result = processData.last_result;
            const status = result.rating !== null ? `דירוג: ${result.rating}` : `דולג: ${result.reason || 'לא בטוח'}`;
            progressLog.textContent += `\n${processData.progress.processed}. ${url} - ${status}`;
            progressLog.scrollTop = progressLog.scrollHeight;
        }
        
        progressText.textContent = `${processData.progress.processed}/${processData.progress.total}`;
        
    } catch (error) {
        console.error('Error processing AI rating:', error);
        const progressLog = document.getElementById('aiProgressLog');
        progressLog.textContent += `\n❌ שגיאה: ${error.message}`;
    }
}

function cancelAIRating() {
    if (!currentAIRatingJobId) return;
    
    if (!confirm('האם אתה בטוח שברצונך לבטל את העבודה?')) {
        return;
    }
    
    fetch('api/ai/cancel_rate_relevance.php', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json'
        },
        body: JSON.stringify({
            job_id: currentAIRatingJobId
        })
    }).then(() => {
        if (airatingInterval) {
            clearInterval(airatingInterval);
            airatingInterval = null;
        }
        resetAIRatingUI();
    }).catch(error => {
        console.error('Error cancelling:', error);
        alert('שגיאה בביטול עבודה');
    });
}

function resetAIRatingUI() {
    const btn = document.getElementById('startAIRatingBtn');
    const progressDiv = document.getElementById('aiRatingProgress');
    const cancelBtn = document.getElementById('cancelAIRatingBtn');
    
    if (btn) {
        btn.disabled = false;
        btn.innerHTML = '<span>⭐</span> AI: דירוג רלוונטיות (1-5)';
    }
    
    if (cancelBtn) {
        cancelBtn.disabled = true;
    }
    
    currentAIRatingJobId = null;
    
    // Hide progress after 5 seconds
    setTimeout(() => {
        if (progressDiv) {
            progressDiv.style.display = 'none';
        }
    }, 5000);
}

// Inline editing (initialize once)
let inlineEditingInitialized = false;
function initInlineCategoryEditing() {
    if (inlineEditingInitialized) {
        console.log('initInlineCategoryEditing: Already initialized, skipping');
        return;
    }
    inlineEditingInitialized = true;
    console.log('initInlineCategoryEditing: Initializing inline editing event listeners');
    
    // Use event delegation for dynamically created elements
    // Use capture phase (true) to ensure this runs before other event listeners
    document.addEventListener('click', function(e) {
        console.log('=== CLICK EVENT DETECTED (CAPTURE PHASE) ===', {
            target: e.target,
            targetTag: e.target.tagName,
            targetClass: e.target.className,
            targetId: e.target.id
        });
        
        // Handle category editing - check if click is on the cell or its contents
        // First, check if clicking directly on the cell
        let categoryCell = e.target.closest('.editable-category');
        
        // If not, check if clicking on the display span
        if (!categoryCell) {
            const displaySpan = e.target.closest('.category-display');
            if (displaySpan) {
                categoryCell = displaySpan.closest('.editable-category');
                console.log('Found category cell via display span');
            }
        }
        
        // If still not found, check if target itself is the display span
        if (!categoryCell && e.target.classList && e.target.classList.contains('category-display')) {
            categoryCell = e.target.closest('.editable-category');
            console.log('Found category cell via target class check');
        }
        
        // Additional fallback: check parent elements
        if (!categoryCell) {
            let parent = e.target.parentElement;
            if (parent && parent.classList && parent.classList.contains('editable-category')) {
                categoryCell = parent;
                console.log('Found category cell via parentElement');
            } else if (parent && parent.parentElement && parent.parentElement.classList && parent.parentElement.classList.contains('editable-category')) {
                categoryCell = parent.parentElement;
                console.log('Found category cell via parentElement.parentElement');
            }
        }
        
        if (categoryCell) {
            console.log('Category cell found:', {
                recordId: categoryCell.getAttribute('data-record-id'),
                currentValue: categoryCell.getAttribute('data-current-value')
            });
            
            // Don't activate if clicking on the select dropdown itself or its options
            if (e.target.closest('.category-edit') || e.target.tagName === 'SELECT' || e.target.tagName === 'OPTION') {
                console.log('Skipping category edit - clicking on select/option');
                return;
            }
            
            const edit = categoryCell.querySelector('.category-edit');
            const display = categoryCell.querySelector('.category-display');
            
            console.log('Category cell elements:', {
                hasEdit: !!edit,
                hasDisplay: !!display,
                editDisplay: edit ? edit.style.display : 'N/A',
                displayDisplay: display ? display.style.display : 'N/A'
            });
            
            if (edit && display && (edit.style.display === 'none' || !edit.style.display || edit.style.display === '')) {
                console.log('✓ Activating category edit for record:', categoryCell.getAttribute('data-record-id'));
                display.style.display = 'none';
                edit.style.display = 'block';
                edit.focus();
                e.stopPropagation();
                e.preventDefault();
                return; // Stop processing other handlers
            }
        }
        
        // Handle organization type editing - check if click is on the cell or its contents
        // First, check if clicking directly on the cell
        let orgTypeCell = e.target.closest('.editable-organization-type');
        
        // If not, check if clicking on the display span
        if (!orgTypeCell) {
            const displaySpan = e.target.closest('.org-type-display');
            if (displaySpan) {
                orgTypeCell = displaySpan.closest('.editable-organization-type');
                console.log('Found org type cell via display span');
            }
        }
        
        // If still not found, check if target itself is the display span
        if (!orgTypeCell && e.target.classList && e.target.classList.contains('org-type-display')) {
            orgTypeCell = e.target.closest('.editable-organization-type');
            console.log('Found org type cell via target class check');
        }
        
        // Additional fallback: check parent elements
        if (!orgTypeCell) {
            let parent = e.target.parentElement;
            if (parent && parent.classList && parent.classList.contains('editable-organization-type')) {
                orgTypeCell = parent;
                console.log('Found org type cell via parentElement');
            } else if (parent && parent.parentElement && parent.parentElement.classList && parent.parentElement.classList.contains('editable-organization-type')) {
                orgTypeCell = parent.parentElement;
                console.log('Found org type cell via parentElement.parentElement');
            }
        }
        
        if (orgTypeCell) {
            console.log('Organization type cell found:', {
                recordId: orgTypeCell.getAttribute('data-record-id'),
                currentValue: orgTypeCell.getAttribute('data-current-value')
            });
            
            // Don't activate if clicking on the select dropdown itself or its options
            if (e.target.closest('.org-type-edit') || e.target.tagName === 'SELECT' || e.target.tagName === 'OPTION') {
                console.log('Skipping org type edit - clicking on select/option');
                return;
            }
            
            const edit = orgTypeCell.querySelector('.org-type-edit');
            const display = orgTypeCell.querySelector('.org-type-display');
            
            console.log('Org type cell elements:', {
                hasEdit: !!edit,
                hasDisplay: !!display,
                editDisplay: edit ? edit.style.display : 'N/A',
                displayDisplay: display ? display.style.display : 'N/A'
            });
            
            if (edit && display && (edit.style.display === 'none' || !edit.style.display || edit.style.display === '')) {
                console.log('✓ Activating organization type edit for record:', orgTypeCell.getAttribute('data-record-id'));
                display.style.display = 'none';
                edit.style.display = 'block';
                edit.focus();
                e.stopPropagation();
                e.preventDefault();
                return; // Stop processing other handlers
            }
        }
        
        // Close on click outside
        if (!categoryCell && !orgTypeCell) {
            // Close category edits
            document.querySelectorAll('.category-edit').forEach(select => {
                if (select.style.display !== 'none') {
                    const cell = select.closest('.editable-category');
                    const display = cell.querySelector('.category-display');
                    if (display) {
                        display.style.display = '';
                        select.style.display = 'none';
                    }
                }
            });
            
            // Close organization type edits
            document.querySelectorAll('.org-type-edit').forEach(select => {
                if (select.style.display !== 'none') {
                    const cell = select.closest('.editable-organization-type');
                    const display = cell.querySelector('.org-type-display');
                    if (display) {
                        display.style.display = '';
                        select.style.display = 'none';
            }
        }
    }, true); // Use capture phase to run before other event listeners
}
    });
}

// Save organization type inline (global function for inline editing)
window.saveOrganizationTypeInline = async function(recordId, newValue) {
    const orgTypeCell = document.querySelector(`.editable-organization-type[data-record-id="${recordId}"]`);
    if (!orgTypeCell) return;
    
    const display = orgTypeCell.querySelector('.org-type-display');
    const edit = orgTypeCell.querySelector('.org-type-edit');
    
    // Show loading
    display.textContent = 'שומר...';
    display.style.display = '';
    edit.style.display = 'none';
    
    try {
        // Fetch current record to update
        const getResponse = await fetch(`api/get_record.php?id=${recordId}`);
        const getData = await getResponse.json();
        
        if (!getData.success) {
            throw new Error(getData.error || 'שגיאה בטעינת הרשומה');
        }
        
        // Update organization type
        const updateData = {
            ...getData.record,
            organization_type: newValue || null
        };
        
        const saveResponse = await fetch('api/save_record.php', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json'
            },
            body: JSON.stringify(updateData)
        });
        
        const saveData = await saveResponse.json();
        
        if (!saveData.success) {
            throw new Error(saveData.error || 'שגיאה בשמירה');
        }
        
        // Update display
        const newLabel = getLabel(newValue, 'organizationType') || '-';
        display.textContent = newLabel;
        orgTypeCell.setAttribute('data-current-value', newValue || '');
        
        // Update the select option
        edit.querySelectorAll('option').forEach(opt => {
            opt.selected = opt.value === newValue;
        });
        
    } catch (error) {
        console.error('Error saving organization type:', error);
        display.textContent = getLabel(orgTypeCell.getAttribute('data-current-value'), 'organizationType') || '-';
        alert('שגיאה בשמירת סוג הארגון: ' + error.message);
    }
};

// Save category inline (global function for inline editing)
window.saveCategoryInline = async function(recordId, newValue) {
    const categoryCell = document.querySelector(`.editable-category[data-record-id="${recordId}"]`);
    if (!categoryCell) return;
    
    const display = categoryCell.querySelector('.category-display');
    const edit = categoryCell.querySelector('.category-edit');
    
    // Show loading
    display.textContent = 'שומר...';
    display.style.display = '';
    edit.style.display = 'none';
    
    try {
        // Fetch current record to update
        const getResponse = await fetch(`api/get_record.php?id=${recordId}`);
        const getData = await getResponse.json();
        
        if (!getData.success) {
            throw new Error(getData.error || 'שגיאה בטעינת הרשומה');
        }
        
        // Update category
        const updateData = {
            ...getData.record,
            topic_category: newValue || null
        };
        
        const saveResponse = await fetch('api/save_record.php', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json'
            },
            body: JSON.stringify(updateData)
        });
        
        const saveData = await saveResponse.json();
        
        if (!saveData.success) {
            throw new Error(saveData.error || 'שגיאה בשמירה');
        }
        
        // Update display
        const newLabel = getLabel(newValue, 'topicCategory') || '-';
        display.textContent = newLabel;
        categoryCell.setAttribute('data-current-value', newValue || '');
        
        // Update the select option
        edit.querySelectorAll('option').forEach(opt => {
            opt.selected = opt.value === newValue;
        });
        
    } catch (error) {
        console.error('Error saving category:', error);
        display.textContent = getLabel(categoryCell.getAttribute('data-current-value'), 'topicCategory') || '-';
        alert('שגיאה בשמירת הקטגוריה: ' + error.message);
    }
};

// Create summary for all records without summary
async function createSummaryForAll() {
    const btn = document.getElementById('createSummaryForAllBtn');
    if (!btn) return;
    
    if (!confirm('האם אתה בטוח שברצונך ליצור סיכום לכל הרשומות שאין להן סיכום?\nזה עלול לקחת זמן רב.')) {
        return;
    }
    
    // Disable button and show loading
    btn.disabled = true;
    const originalText = btn.innerHTML;
    btn.innerHTML = '<span>⏳</span> יוצר סיכומים...';
    
    try {
        // Get all records without summary
        const response = await fetch('api/get_records.php?pageSize=10000');
        const data = await response.json();
        
        if (!data.success || !data.records) {
            throw new Error('שגיאה בטעינת הרשומות');
        }
        
        // Filter records without summary
        const recordsWithoutSummary = data.records.filter(record => {
            return !record.short_summary || String(record.short_summary).trim().length === 0;
        });
        
        if (recordsWithoutSummary.length === 0) {
            alert('כל הרשומות כבר יש להן סיכום!');
            btn.disabled = false;
            btn.innerHTML = originalText;
            return;
        }
        
        const totalRecords = recordsWithoutSummary.length;
        let successCount = 0;
        let failCount = 0;
        
        // Process records one by one with delay
        for (let i = 0; i < recordsWithoutSummary.length; i++) {
            const record = recordsWithoutSummary[i];
            btn.innerHTML = `<span>⏳</span> יוצר סיכום ${i + 1}/${totalRecords}...`;
            
            try {
                const summaryResponse = await fetch('api/create_summary.php', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json'
                    },
                    body: JSON.stringify({ id: record.id })
                });
                
                const summaryData = await summaryResponse.json();
                
                if (summaryData.success) {
                    successCount++;
                } else {
                    failCount++;
                    console.error(`Failed to create summary for record ${record.id}:`, summaryData.error);
                }
            } catch (error) {
                failCount++;
                console.error(`Error creating summary for record ${record.id}:`, error);
            }
            
            // Small delay to avoid overwhelming the server
            await new Promise(resolve => setTimeout(resolve, 500));
        }
        
        // Refresh table
        loadRecords();
        
        // Show results
        alert(`סיכומים נוצרו!\n\nהצלחה: ${successCount}\nכשלונות: ${failCount}`);
        
    } catch (error) {
        console.error('Error:', error);
        alert('שגיאה ביצירת סיכומים: ' + error.message);
    } finally {
        btn.disabled = false;
        btn.innerHTML = originalText;
    }
}

// Populate organization names (global function for HTML onclick)
window.populateOrganizationNames = async function() {
    const btn = document.getElementById('populateOrgNamesBtn');
    if (!btn) return;
    
    if (!confirm('האם אתה בטוח שברצונך לאכלס את שמות הגופים?\nזה עלול לקחת זמן רב.')) {
        return;
    }
    
    btn.disabled = true;
    const originalText = btn.innerHTML;
    btn.innerHTML = '<span>⏳</span> מאכלס...';
    
    try {
        const response = await fetch('api/populate_organization_names.php');
        const data = await response.json();
        
        if (!data.success) {
            throw new Error(data.error || 'שגיאה באכלסה');
        }
        
        const result = data.result;
        const message = `אכלסה הושלמה!\n\nסה"כ: ${result.total}\nעודכנו: ${result.updated}\nדולגו: ${result.skipped}\nשגיאות: ${result.errors}`;
        
        alert(message);
        
        // Refresh table
        loadRecords();
        
    } catch (error) {
        console.error('Error:', error);
        alert('שגיאה באכלסה: ' + error.message);
    } finally {
        btn.disabled = false;
        btn.innerHTML = originalText;
    }
};

// Utility functions
function truncateUrl(url, maxLength) {
    if (url.length <= maxLength) return url;
    return url.substring(0, maxLength - 3) + '...';
}

// Extract domain from URL and show with ellipsis
function getDomainFromUrl(url) {
    if (!url) return '';
    try {
        const urlObj = new URL(url);
        const hostname = urlObj.hostname;
        // Remove 'www.' if present
        const domain = hostname.replace(/^www\./, '');
        return domain + '...';
    } catch (e) {
        // If URL parsing fails, try to extract domain manually
        const match = url.match(/https?:\/\/(?:www\.)?([^\/]+)/);
        if (match && match[1]) {
            return match[1].replace(/^www\./, '') + '...';
        }
        // Fallback: return first part of URL
        return url.split('/')[2]?.replace(/^www\./, '') + '...' || url.substring(0, 20) + '...';
    }
}

function truncateText(text, maxLength) {
    if (!text) return '';
    if (text.length <= maxLength) return text;
    return text.substring(0, maxLength - 3) + '...';
}

function formatDate(dateString) {
    if (!dateString) return '-';
    const date = new Date(dateString);
    return date.toLocaleDateString('he-IL', {
        year: 'numeric',
        month: '2-digit',
        day: '2-digit',
        hour: '2-digit',
        minute: '2-digit'
    });
}

function formatJSON(value) {
    if (!value) return '-';
    if (Array.isArray(value)) {
        return value.join(', ');
    }
    return JSON.stringify(value);
}

function getStatusLabel(status) {
    const labels = {
        'pending': 'ממתין',
        'extracted': 'חולץ',
        'failed': 'נכשל'
    };
    return labels[status] || status;
}

function getRelevanceLabel(level) {
    const labels = {
        1: '(נמוך מאוד)',
        2: '(נמוך)',
        3: '(בינוני)',
        4: '(גבוה)',
        5: '(גבוה מאוד)'
    };
    return labels[level] || '';
}

function getRelevanceColor(level) {
    const colors = {
        1: '#f44336', // אדום - נמוך מאוד
        2: '#ff9800', // כתום - נמוך
        3: '#ffc107', // צהוב - בינוני
        4: '#4caf50', // ירוק - גבוה
        5: '#2196f3'  // כחול - גבוה מאוד
    };
    return colors[level] || '#666';
}

function getLabel(value, type) {
    if (!value) return null;
    
    const labels = {
        organizationType: {
            'municipality': 'רשות מקומית',
            'government_agency': 'סוכנות ממשלתית',
            'media': 'תקשורת',
            'educational_institution': 'מוסד חינוכי',
            'ngo': 'עמותה',
            'research_institution': 'מוסד מחקר',
            'other': 'אחר'
        },
        topicCategory: {
            'education': 'חינוך',
            'culture': 'תרבות',
            'policy': 'מדיניות',
            'news': 'חדשות',
            'research': 'מחקר',
            'heritage': 'מורשת',
            'community': 'קהילה',
            'other': 'אחר'
        },
        documentType: {
            'report': 'דוח',
            'article': 'מאמר',
            'policy_document': 'מסמך מדיניות',
            'curriculum': 'תכנית לימודים',
            'announcement': 'הודעה',
            'protocol': 'פרוטוקול',
            'plan': 'תכנית',
            'other': 'אחר'
        },
        targetAudience: {
            'general_public': 'ציבור כללי',
            'educators': 'מחנכים',
            'students': 'תלמידים',
            'policymakers': 'קובעי מדיניות',
            'researchers': 'חוקרים',
            'community_leaders': 'מנהיגי קהילה',
            'other': 'אחר'
        },
        culturalFocus: {
            'hebrew_culture': 'תרבות עברית',
            'jewish_heritage': 'מורשת יהודית',
            'israeli_identity': 'זהות ישראלית',
            'multicultural': 'רב-תרבותי',
            'universal': 'אוניברסלי',
            'mixed': 'מעורב',
            'unclear': 'לא ברור'
        },
        zionismReferences: {
            'explicit': 'מפורש',
            'implicit': 'מרומז',
            'none': 'אין',
            'unclear': 'לא ברור'
        },
        language: {
            'hebrew': 'עברית',
            'english': 'אנגלית',
            'arabic': 'ערבית',
            'mixed': 'מעורב',
            'other': 'אחר'
        },
        accessibilityLevel: {
            'public': 'ציבורי',
            'restricted': 'מוגבל',
            'unclear': 'לא ברור'
        },
        publicationFormat: {
            'pdf': 'PDF',
            'html': 'HTML',
            'text': 'טקסט',
            'image': 'תמונה',
            'video': 'וידאו',
            'other': 'אחר'
        },
        jurisdictionLevel: {
            'local': 'מקומי',
            'regional': 'אזורי',
            'national': 'לאומי',
            'international': 'בינלאומי'
        }
    };
    
    return labels[type]?.[value] || value;
}

function showError(message) {
    alert('שגיאה: ' + message);
}

function showSuccess(message) {
    alert('הצלחה: ' + message);
}

// Save summary handler
document.addEventListener('DOMContentLoaded', function() {
    const editSummaryForm = document.getElementById('editSummaryForm');
    if (editSummaryForm) {
        editSummaryForm.addEventListener('submit', async function(e) {
            e.preventDefault();
            
            const id = document.getElementById('editSummaryRecordId').value;
            const summary = document.getElementById('editSummaryText').value.trim();
            
            if (!id || !summary) {
                showError('נא למלא את כל השדות');
                return;
            }
            
            try {
                const response = await fetch('api/update_summary.php', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json'
                    },
                    body: JSON.stringify({ id: id, summary: summary })
                });
                
                const data = await response.json();
                
                if (data.success) {
                    closeEditSummaryModal();
                    loadRecords();
                    showSuccess('סיכום עודכן בהצלחה');
                } else {
                    showError(data.error || 'שגיאה בעדכון הסיכום');
                }
            } catch (error) {
                console.error('Error:', error);
                showError('שגיאה בחיבור לשרת: ' + error.message);
            }
        });
    }
});

// Close modals when clicking outside
window.onclick = function(event) {
    // Don't handle clicks on editable cells - let the inline editing handler deal with them
    if (event.target.closest('.editable-category') || event.target.closest('.editable-organization-type')) {
        return;
    }
    
    const recordModal = document.getElementById('recordModal');
    const viewModal = document.getElementById('viewModal');
    const viewSummaryModal = document.getElementById('viewSummaryModal');
    const editSummaryModal = document.getElementById('editSummaryModal');
    
    if (event.target === recordModal) {
        closeModal();
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
}
