/**
 * Main Application Module
 * Initializes the app and handles main functionality
 */

console.log('=== APP.JS VERSION 5.0 - LOADED AT: ' + new Date().toISOString() + ' ===');

(function() {
    'use strict';
    
    // Global state
    let currentFilters = {};
    let currentAIRatingJobId = null;
    let airatingInterval = null;
    
    /**
     * Initialize application
     */
    function init() {
        console.log('=== APP INITIALIZED ===');
        
        // Initialize inline editing
        if (window.InlineEdit) {
            window.InlineEdit.init();
        }
        
        // Setup sortable headers
        setupSortableHeaders();
        
        // Load records
        loadRecords();
        
        // Setup form handlers
        setupFormHandlers();
    }
    
    /**
     * Setup sortable table headers - only updates visual indicators
     * Sorting is handled by onclick attributes in HTML calling sortByColumn()
     */
    function setupSortableHeaders() {
        const sortBySelect = document.getElementById('sortBy');
        const sortOrderSelect = document.getElementById('sortOrder');
        if (sortBySelect && sortOrderSelect) {
            updateSortIndicators(sortBySelect.value, sortOrderSelect.value);
        }
    }
    
    /**
     * Sort by column - called when clicking column header
     */
    function sortByColumn(column) {
        console.log('sortByColumn called:', column);

        const sortBySelect = document.getElementById('sortBy');
        const sortOrderSelect = document.getElementById('sortOrder');

        if (!sortBySelect || !sortOrderSelect) {
            console.error('Sort selects not found');
            return;
        }

        // If clicking the same column, toggle order
        if (sortBySelect.value === column) {
            sortOrderSelect.value = sortOrderSelect.value === 'ASC' ? 'DESC' : 'ASC';
        } else {
            // New column - set to this column, default to DESC
            sortBySelect.value = column;
            sortOrderSelect.value = 'DESC';
        }

        // Update visual indicators
        updateSortIndicators(column, sortOrderSelect.value);

        // Reload with new sort
        applyFilters();
    }

    /**
     * Update sort indicators in table headers
     */
    function updateSortIndicators(activeColumn, order) {
        const sortableHeaders = document.querySelectorAll('.sortable-header');

        sortableHeaders.forEach(header => {
            const indicator = header.querySelector('.sort-indicator');
            const column = header.getAttribute('data-sort');

            if (column === activeColumn) {
                // Show active sort indicator
                header.classList.add('active');
                if (indicator) {
                    indicator.textContent = order === 'ASC' ? ' ▲' : ' ▼';
                }
            } else {
                // Clear inactive headers
                header.classList.remove('active');
                if (indicator) {
                    indicator.textContent = '';
                }
            }
        });
    }
    
    /**
     * Setup form handlers
     */
    function setupFormHandlers() {
        // Record form
        const recordForm = document.getElementById('recordForm');
        if (recordForm) {
            recordForm.addEventListener('submit', async function(e) {
                e.preventDefault();
                await saveRecord();
            });
        }
        
        // Edit summary form
        const editSummaryForm = document.getElementById('editSummaryForm');
        if (editSummaryForm) {
            editSummaryForm.addEventListener('submit', async function(e) {
                e.preventDefault();
                await saveSummary();
            });
        }
    }
    
    /**
     * Load records with current filters
     */
    async function loadRecords() {
        console.log('=== LOAD RECORDS CALLED ===');
        
        const sortBy = document.getElementById('sortBy')?.value || 'id';
        const sortOrder = document.getElementById('sortOrder')?.value || 'DESC';
        
        const filters = {
            page: 1,
            pageSize: 10000, // Load all records
            sortBy: sortBy,
            sortOrder: sortOrder,
            ...currentFilters
        };
        
        try {
            const data = await window.API.getRecords(filters);
            
            if (data.success) {
                window.Table.displayRecords(data.records);
                updateStats(data.stats);
                
                // Update sort indicators
                const sortBySelect = document.getElementById('sortBy');
                const sortOrderSelect = document.getElementById('sortOrder');
                if (sortBySelect && sortOrderSelect) {
                    updateSortIndicators(sortBySelect.value, sortOrderSelect.value);
                }
            } else {
                const errorMsg = data.error || 'שגיאה בטעינת הנתונים';
                showError(errorMsg);
                const tbody = document.getElementById('tableBody');
                if (tbody) {
                    tbody.innerHTML = `<tr><td colspan="10" class="no-data" style="color: red;">שגיאה: ${errorMsg}</td></tr>`;
                }
            }
        } catch (error) {
            console.error('Error:', error);
            const errorMsg = 'שגיאה בחיבור לשרת: ' + error.message;
            showError(errorMsg);
            const tbody = document.getElementById('tableBody');
            if (tbody) {
                tbody.innerHTML = `<tr><td colspan="10" class="no-data" style="color: red;">${errorMsg}</td></tr>`;
            }
        }
    }
    
    /**
     * Apply filters
     */
    function applyFilters() {
        const searchUrl = document.getElementById('searchUrl')?.value.trim();
        const status = document.getElementById('filterStatus')?.value;
        const orgType = document.getElementById('filterOrgType')?.value;
        const topic = document.getElementById('filterTopic')?.value;
        const year = document.getElementById('filterYear')?.value.trim();
        const aiRelevance = document.getElementById('filterAIRelevance')?.value;
        const unrated = document.getElementById('filterUnrated')?.checked;
        
        currentFilters = {};
        
        if (searchUrl) currentFilters.searchUrl = searchUrl;
        if (status) currentFilters.status = status;
        if (orgType) currentFilters.orgType = orgType;
        if (topic) currentFilters.topic = topic;
        if (year) currentFilters.year = year;
        if (aiRelevance) currentFilters.aiRelevanceScore = aiRelevance;
        if (unrated) currentFilters.onlyUnrated = true;
        
        loadRecords();
    }
    
    /**
     * Clear filters
     */
    function clearFilters() {
        const searchUrl = document.getElementById('searchUrl');
        const filterStatus = document.getElementById('filterStatus');
        const filterOrgType = document.getElementById('filterOrgType');
        const filterTopic = document.getElementById('filterTopic');
        const filterYear = document.getElementById('filterYear');
        const filterAIRelevance = document.getElementById('filterAIRelevance');
        const filterUnrated = document.getElementById('filterUnrated');
        
        if (searchUrl) searchUrl.value = '';
        if (filterStatus) filterStatus.value = '';
        if (filterOrgType) filterOrgType.value = '';
        if (filterTopic) filterTopic.value = '';
        if (filterYear) filterYear.value = '';
        if (filterAIRelevance) filterAIRelevance.value = '';
        if (filterUnrated) filterUnrated.checked = false;
        
        currentFilters = {};
        loadRecords();
    }
    
    /**
     * Save record
     */
    async function saveRecord() {
        const form = document.getElementById('recordForm');
        if (!form) return;
        
        const formData = {
            id: document.getElementById('recordId')?.value || null,
            url: document.getElementById('url')?.value,
            source_type: document.getElementById('sourceType')?.value || null,
            year: document.getElementById('year')?.value ? parseInt(document.getElementById('year').value) : null,
            organization_name: document.getElementById('organizationName')?.value.trim() || null,
            organization_type: document.getElementById('organizationType')?.value || null,
            jurisdiction_level: document.getElementById('jurisdictionLevel')?.value || null,
            geographic_scope: document.getElementById('geographicScope')?.value || null,
            topic_category: document.getElementById('topicCategory')?.value || null,
            document_type: document.getElementById('documentType')?.value || null,
            target_audience: document.getElementById('targetAudience')?.value || null,
            cultural_focus: document.getElementById('culturalFocus')?.value || null,
            zionism_references: document.getElementById('zionismReferences')?.value || null,
            language: document.getElementById('language')?.value || null,
            accessibility_level: document.getElementById('accessibilityLevel')?.value || null,
            publication_format: document.getElementById('publicationFormat')?.value || null,
            short_summary: document.getElementById('shortSummary')?.value.trim() || null,
            manual_summary: document.getElementById('manualSummary')?.value.trim() || null,
            relevance_level: document.getElementById('relevanceLevel')?.value ? parseInt(document.getElementById('relevanceLevel').value) : null,
            ai_relevance_score: document.getElementById('aiRelevanceScore')?.value ? parseInt(document.getElementById('aiRelevanceScore').value) : null,
            ai_relevance_reason: document.getElementById('aiRelevanceReason')?.value.trim() || null,
            metadata_status: document.getElementById('metadataStatus')?.value || 'pending'
        };
        
        // Parse JSON fields
        try {
            const valuesOrientation = document.getElementById('valuesOrientation')?.value.trim();
            if (valuesOrientation) {
                formData.values_orientation = JSON.parse(valuesOrientation);
            }
        } catch (e) {
            showError('שגיאה בפורמט JSON של אוריינטציית ערכים');
            return;
        }
        
        try {
            const identityTheme = document.getElementById('identityTheme')?.value.trim();
            if (identityTheme) {
                formData.identity_theme = JSON.parse(identityTheme);
            }
        } catch (e) {
            showError('שגיאה בפורמט JSON של נושאי זהות');
            return;
        }
        
        try {
            await window.API.saveRecord(formData);
            window.Modals.closeRecordModal();
            loadRecords();
            showSuccess('רשומה נשמרה בהצלחה');
        } catch (error) {
            console.error('Error:', error);
            showError('שגיאה בשמירת הרשומה: ' + error.message);
        }
    }
    
    /**
     * Save summary
     */
    async function saveSummary() {
        const id = document.getElementById('editSummaryRecordId')?.value;
        const summary = document.getElementById('editSummaryText')?.value.trim();
        
        if (!id || !summary) {
            showError('נא למלא את כל השדות');
            return;
        }
        
        try {
            const response = await fetch('api/summaries.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json'
                },
                body: JSON.stringify({ id: id, summary: summary })
            });
            
            const data = await response.json();
            
            if (data.success) {
                window.Modals.closeEditSummaryModal();
                loadRecords();
                showSuccess('סיכום עודכן בהצלחה');
            } else {
                showError(data.error || 'שגיאה בעדכון הסיכום');
            }
        } catch (error) {
            console.error('Error:', error);
            showError('שגיאה בחיבור לשרת: ' + error.message);
        }
    }
    
    /**
     * Delete record
     */
    async function deleteRecord(id) {
        if (!confirm('האם אתה בטוח שברצונך למחוק רשומה זו?')) {
            return;
        }
        
        try {
            await window.API.deleteRecord(id);
            loadRecords();
            showSuccess('רשומה נמחקה בהצלחה');
        } catch (error) {
            console.error('Error:', error);
            showError('שגיאה במחיקת הרשומה: ' + error.message);
        }
    }
    
    /**
     * Retry extraction
     */
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
    
    /**
     * Create summary
     */
    async function createSummary(id) {
        if (!id) {
            alert('שגיאה: מזהה רשומה לא תקין');
            return;
        }
        
        try {
            const response = await fetch('api/create_summary.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json'
                },
                body: JSON.stringify({ id: id })
            });
            
            const data = await response.json();
            
            if (data.success) {
                loadRecords();
                alert('סיכום נוצר בהצלחה!\n\n' + (data.summary ? data.summary.substring(0, 200) : ''));
            } else {
                showError(data.error || 'שגיאה ביצירת סיכום');
            }
        } catch (error) {
            console.error('Error:', error);
            showError('שגיאה בחיבור לשרת: ' + error.message);
        }
    }
    
    /**
     * Create summary for all records
     */
    async function createSummaryForAll() {
        const btn = document.getElementById('createSummaryForAllBtn');
        if (!btn) return;
        
        if (!confirm('האם אתה בטוח שברצונך ליצור סיכום לכל הרשומות שאין להן סיכום?\nזה עלול לקחת זמן רב.')) {
            return;
        }
        
        btn.disabled = true;
        const originalText = btn.innerHTML;
        btn.innerHTML = '<span>⏳</span> יוצר סיכומים...';
        
        try {
            const data = await window.API.getRecords({ pageSize: 10000 });
            
            if (!data.success || !data.records) {
                throw new Error('שגיאה בטעינת הרשומות');
            }
            
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
                
                await new Promise(resolve => setTimeout(resolve, 500));
            }
            
            loadRecords();
            alert(`סיכומים נוצרו!\n\nהצלחה: ${successCount}\nכשלונות: ${failCount}`);
            
        } catch (error) {
            console.error('Error:', error);
            alert('שגיאה ביצירת סיכומים: ' + error.message);
        } finally {
            btn.disabled = false;
            btn.innerHTML = originalText;
        }
    }
    
    /**
     * Start AI rating
     */
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
        
        if (progressDiv) progressDiv.style.display = 'block';
        if (progressText) progressText.textContent = '0/0';
        if (progressLog) progressLog.textContent = 'מתחיל עבודה...';
        if (cancelBtn) cancelBtn.disabled = false;
        
        try {
            const response = await fetch('api/ai/relevance.php', {
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
            if (progressText) progressText.textContent = `0/${data.total}`;
            if (progressLog) progressLog.textContent = `עבודה התחילה. סה"כ: ${data.total} רשומות\n`;
            
            // Start polling for progress
            airatingInterval = setInterval(() => {
                processAIRatingStep();
            }, 2000);
            
            processAIRatingStep();
            
        } catch (error) {
            console.error('Error:', error);
            alert('שגיאה בהתחלת דירוג AI: ' + error.message);
            btn.disabled = false;
            btn.innerHTML = originalText;
            if (progressDiv) progressDiv.style.display = 'none';
        }
    }
    
    /**
     * Process AI rating step
     */
    async function processAIRatingStep() {
        if (!currentAIRatingJobId) return;
        
        try {
            const statusResponse = await fetch(`api/ai/relevance.php?job_id=${currentAIRatingJobId}`);
            const statusData = await statusResponse.json();
            
            if (!statusData.success) {
                throw new Error(statusData.error || 'שגיאה בבדיקת סטטוס');
            }
            
            const progress = statusData.progress;
            const progressText = document.getElementById('aiProgressText');
            const progressLog = document.getElementById('aiProgressLog');
            
            if (progressText) progressText.textContent = `${progress.processed}/${progress.total}`;
            
            if (statusData.cancelled) {
                clearInterval(airatingInterval);
                airatingInterval = null;
                if (progressLog) progressLog.textContent += '\nעבודה בוטלה';
                resetAIRatingUI();
                return;
            }
            
            if (statusData.completed) {
                clearInterval(airatingInterval);
                airatingInterval = null;
                if (progressLog) progressLog.textContent += `\n✅ הושלם! דורגו: ${progress.done}, דולגו: ${progress.skipped}, שגיאות: ${progress.error}`;
                resetAIRatingUI();
                loadRecords();
                return;
            }
            
            // Process one URL
            const processResponse = await fetch(`api/ai/relevance.php?job_id=${currentAIRatingJobId}&action=process`);
            const processData = await processResponse.json();
            
            if (!processData.success) {
                throw new Error(processData.error || 'שגיאה בעיבוד');
            }
            
            if (processData.completed) {
                clearInterval(airatingInterval);
                airatingInterval = null;
                if (progressLog) progressLog.textContent += `\n✅ הושלם! דורגו: ${processData.progress.done}, דולגו: ${processData.progress.skipped}, שגיאות: ${processData.progress.error}`;
                resetAIRatingUI();
                loadRecords();
                return;
            }
            
            if (processData.last_url && progressLog) {
                const url = processData.last_url.length > 50 ? processData.last_url.substring(0, 50) + '...' : processData.last_url;
                const result = processData.last_result;
                const status = result.rating !== null ? `דירוג: ${result.rating}` : `דולג: ${result.reason || 'לא בטוח'}`;
                progressLog.textContent += `\n${processData.progress.processed}. ${url} - ${status}`;
                progressLog.scrollTop = progressLog.scrollHeight;
            }
            
            if (progressText) progressText.textContent = `${processData.progress.processed}/${processData.progress.total}`;
            
        } catch (error) {
            console.error('Error processing AI rating:', error);
            const progressLog = document.getElementById('aiProgressLog');
            if (progressLog) progressLog.textContent += `\n❌ שגיאה: ${error.message}`;
        }
    }
    
    /**
     * Cancel AI rating
     */
    function cancelAIRating() {
        if (!currentAIRatingJobId) return;
        
        if (!confirm('האם אתה בטוח שברצונך לבטל את העבודה?')) {
            return;
        }
        
        fetch('api/ai/relevance.php', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json'
            },
            body: JSON.stringify({
                action: 'cancel',
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
    
    /**
     * Reset AI rating UI
     */
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
        
        setTimeout(() => {
            if (progressDiv) {
                progressDiv.style.display = 'none';
            }
        }, 5000);
    }
    
    /**
     * Populate organization names
     */
    async function populateOrganizationNames() {
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
            loadRecords();
            
        } catch (error) {
            console.error('Error:', error);
            alert('שגיאה באכלסה: ' + error.message);
        } finally {
            btn.disabled = false;
            btn.innerHTML = originalText;
        }
    }
    
    /**
     * Export data
     */
    function exportData() {
        window.location.href = 'export_to_json.php';
    }
    
    /**
     * Update stats
     */
    function updateStats(stats) {
        const totalRecords = document.getElementById('totalRecords');
        const pendingRecords = document.getElementById('pendingRecords');
        
        if (totalRecords) totalRecords.textContent = stats.total;
        if (pendingRecords) pendingRecords.textContent = stats.pending;
    }
    
    /**
     * Update displayed records count
     */
    function updateDisplayedCount(count) {
        const displayedRecords = document.getElementById('displayedRecords');
        if (displayedRecords) displayedRecords.textContent = count;
    }
    
    /**
     * Show error
     */
    function showError(message) {
        alert('שגיאה: ' + message);
    }
    
    /**
     * Show success
     */
    function showSuccess(message) {
        alert('הצלחה: ' + message);
    }
    
    /**
     * Switch between tabs
     */
    function switchTab(tabName) {
        // Hide all table sections (with null checks)
        const urlsSection = document.getElementById('table-section-urls');
        const orgsSection = document.getElementById('table-section-organizations');
        const programsSection = document.getElementById('table-section-programs');
        
        if (urlsSection) urlsSection.style.display = 'none';
        if (orgsSection) orgsSection.style.display = 'none';
        if (programsSection) programsSection.style.display = 'none';
        
        // Hide filters section for non-URLs tabs
        const filtersSection = document.querySelector('.filters-section');
        const statsBar = document.querySelector('.stats-bar');
        if (tabName === 'urls') {
            if (filtersSection) filtersSection.style.display = 'block';
            if (statsBar) statsBar.style.display = 'flex';
        } else {
            if (filtersSection) filtersSection.style.display = 'none';
            if (statsBar) statsBar.style.display = 'none';
        }
        
        // Update tab buttons
        document.querySelectorAll('.tab-btn').forEach(btn => {
            btn.style.background = '#e0e0e0';
            btn.style.color = '#666';
        });
        
        // Show selected tab section and update button
        if (tabName === 'urls') {
            if (urlsSection) urlsSection.style.display = 'block';
            const tabBtn = document.getElementById('tab-urls');
            if (tabBtn) {
                tabBtn.style.background = '#667eea';
                tabBtn.style.color = 'white';
            }
        } else if (tabName === 'organizations') {
            if (orgsSection) orgsSection.style.display = 'block';
            const tabBtn = document.getElementById('tab-organizations');
            if (tabBtn) {
                tabBtn.style.background = '#667eea';
                tabBtn.style.color = 'white';
            }
            loadOrganizations();
        } else if (tabName === 'programs') {
            if (programsSection) programsSection.style.display = 'block';
            const tabBtn = document.getElementById('tab-programs');
            if (tabBtn) {
                tabBtn.style.background = '#667eea';
                tabBtn.style.color = 'white';
            }
            loadEducationalPrograms();
        }
    }
    
    /**
     * Load organizations table
     */
    async function loadOrganizations() {
        const search = document.getElementById('searchOrganizations')?.value || '';
        const orgType = document.getElementById('filterOrgTypeTable')?.value || '';
        
        const params = new URLSearchParams();
        if (search) params.append('search', search);
        if (orgType) params.append('org_type', orgType);
        params.append('pageSize', '10000');
        
        try {
            const response = await fetch(`api/organizations.php?${params.toString()}`);
            const text = await response.text();
            if (!text) {
                throw new Error('תגובה ריקה מהשרת');
            }
            const data = JSON.parse(text);
            
            if (!data.success) {
                throw new Error(data.error || 'שגיאה בטעינת ארגונים');
            }
            
            const tbody = document.getElementById('organizationsTableBody');
            if (!tbody) return;
            
            if (data.organizations.length === 0) {
                tbody.innerHTML = '<tr><td colspan="8" class="no-data">לא נמצאו ארגונים</td></tr>';
                return;
            }
            
            tbody.innerHTML = data.organizations.map(org => `
                <tr>
                    <td>${org.org_id}</td>
                    <td><strong>${org.org_name || '-'}</strong></td>
                    <td>${org.org_type || '-'}</td>
                    <td>${org.country || '-'}</td>
                    <td>${org.city || '-'}</td>
                    <td>${org.ideological_domain || '-'}</td>
                    <td>${org.url_count || 0}</td>
                    <td style="max-width: 200px; overflow: hidden; text-overflow: ellipsis;">${org.notes ? org.notes.substring(0, 100) + '...' : '-'}</td>
                </tr>
            `).join('');
            
        } catch (error) {
            console.error('Error loading organizations:', error);
            const tbody = document.getElementById('organizationsTableBody');
            if (tbody) {
                tbody.innerHTML = `<tr><td colspan="8" class="no-data" style="color: red;">שגיאה: ${error.message}</td></tr>`;
            }
        }
    }
    
    /**
     * Load educational programs table
     */
    async function loadEducationalPrograms() {
        const search = document.getElementById('searchPrograms')?.value || '';
        const programType = document.getElementById('filterProgramType')?.value || '';
        const topicCategory = document.getElementById('filterProgramCategory')?.value || '';
        
        const params = new URLSearchParams();
        if (search) params.append('search', search);
        if (programType) params.append('program_type', programType);
        if (topicCategory) params.append('topic_category', topicCategory);
        params.append('pageSize', '10000');
        
        try {
            const response = await fetch(`api/educational_programs.php?${params.toString()}`);
            const text = await response.text();
            if (!text) {
                throw new Error('תגובה ריקה מהשרת');
            }
            const data = JSON.parse(text);
            
            if (!data.success) {
                throw new Error(data.error || 'שגיאה בטעינת תוכניות חינוכיות');
            }
            
            const tbody = document.getElementById('programsTableBody');
            if (!tbody) {
                console.error('programsTableBody not found!');
                return;
            }
            
            if (data.programs.length === 0) {
                tbody.innerHTML = '<tr><td colspan="11" class="no-data">לא נמצאו תוכניות חינוכיות</td></tr>';
                return;
            }
            
            console.log('Loading programs:', data.programs.length);
            if (data.programs.length > 0) {
                console.log('First program sample (FULL):', JSON.stringify(data.programs[0], null, 2));
                console.log('First program - keywords:', data.programs[0].keywords);
                console.log('First program - url:', data.programs[0].url);
                console.log('First program - all property names:', Object.keys(data.programs[0]));
            }
            
            tbody.innerHTML = data.programs.map((prog, idx) => {
                // Extract values safely - log first record for debugging
                if (idx === 0) {
                    console.log('Processing first program:', {
                        'prog.keywords': prog.keywords,
                        'prog.url': prog.url,
                        'prog.program_id': prog.program_id,
                        'prog.program_name': prog.program_name
                    });
                }
                
                const keywords = (prog.keywords && String(prog.keywords).trim()) || '';
                const url = (prog.url && String(prog.url).trim()) || '';
                const description = (prog.description && String(prog.description).trim()) || '';
                
                // Escape for HTML attributes
                const escapeAttr = (str) => {
                    if (!str) return '';
                    return String(str)
                        .replace(/&/g, '&amp;')
                        .replace(/"/g, '&quot;')
                        .replace(/'/g, '&#39;');
                };
                
                const safeDesc = escapeAttr(description);
                const safeKeywords = escapeAttr(keywords);
                const safeUrl = escapeAttr(url);
                
                // Escape for HTML content
                const escapeHtml = (str) => {
                    if (!str) return '';
                    return String(str)
                        .replace(/&/g, '&amp;')
                        .replace(/</g, '&lt;')
                        .replace(/>/g, '&gt;');
                };
                
                const displayDesc = description ? (description.length > 100 ? escapeHtml(description.substring(0, 100)) + '...' : escapeHtml(description)) : '-';
                const displayKeywords = keywords ? escapeHtml(keywords) : '<span style="color: #999;">-</span>';
                const displayUrl = url ? escapeHtml(url.length > 45 ? url.substring(0, 45) + '...' : url) : '-';
                
                return `
                <tr>
                    <td>${prog.program_id || '-'}</td>
                    <td><strong>${escapeHtml(prog.program_name || '-')}</strong></td>
                    <td style="max-width: 250px; overflow: hidden; text-overflow: ellipsis; white-space: nowrap; padding: 8px;" title="${safeDesc}">${displayDesc}</td>
                    <td>${escapeHtml(prog.organization_name || '-')}</td>
                    <td>${escapeHtml(prog.topic_category || '-')}</td>
                    <td>${escapeHtml(prog.target_audience || '-')}</td>
                    <td>${escapeHtml(prog.age_range || '-')}</td>
                    <td>${escapeHtml(prog.duration || '-')}</td>
                    <td>${escapeHtml(prog.program_type || '-')}</td>
                    <td style="max-width: 200px; overflow: hidden; text-overflow: ellipsis; white-space: normal; padding: 8px; font-size: 12px; line-height: 1.4;" title="${safeKeywords}">${displayKeywords}</td>
                    <td style="max-width: 250px; padding: 8px; word-break: break-all;"><a href="${url || '#'}" ${url ? 'target="_blank"' : ''} style="color: #2196F3; text-decoration: underline; font-size: 12px;" title="${safeUrl}">${displayUrl}</a></td>
                </tr>
            `;
            }).join('');
            
        } catch (error) {
            console.error('Error loading educational programs:', error);
            const tbody = document.getElementById('programsTableBody');
            if (tbody) {
                tbody.innerHTML = `<tr><td colspan="11" class="no-data" style="color: red;">שגיאה: ${error.message}</td></tr>`;
            }
        }
    }
    
    // Export to global scope
    window.App = {
        init,
        loadRecords,
        applyFilters,
        clearFilters,
        saveRecord,
        deleteRecord,
        editRecord: (id) => window.Modals ? window.Modals.openRecordModal(id) : null,
        retryExtraction,
        createSummary,
        createSummaryForAll,
        startAIRating,
        cancelAIRating,
        populateOrganizationNames,
        exportData,
        updateStats,
        updateDisplayedCount,
        setupSortableHeaders,
        updateSortIndicators,
        sortByColumn,
        switchTab,
        loadOrganizations,
        loadEducationalPrograms
    };
    
    // Export lowercase alias for backward compatibility
    window.app = window.App;
    
    // Export for backward compatibility
    window.loadRecords = loadRecords;
    window.applyFilters = applyFilters;
    window.clearFilters = clearFilters;
    window.deleteRecord = deleteRecord;
    window.editRecord = (id) => window.Modals ? window.Modals.openRecordModal(id) : null;
    window.openAddModal = () => window.Modals ? window.Modals.openRecordModal(null) : null;
    window.retryExtraction = retryExtraction;
    window.createSummary = createSummary;
    window.createSummaryForAll = createSummaryForAll;
    window.startAIRating = startAIRating;
    window.cancelAIRating = cancelAIRating;
    window.populateOrganizationNames = populateOrganizationNames;
    window.exportData = exportData;
    
    // Export switchTab globally for immediate access
    window.switchTab = function(tabName) {
        if (window.App && window.App.switchTab) {
            window.App.switchTab(tabName);
        } else {
            console.error('App.switchTab not yet loaded');
            // Retry after a short delay
            setTimeout(function() {
                if (window.App && window.App.switchTab) {
                    window.App.switchTab(tabName);
                } else {
                    alert('שגיאה: הקוד לא נטען. נא לרענן את הדף.');
                }
            }, 100);
        }
    };
    
    // Initialize on DOM ready
    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', init);
    } else {
        init();
    }
})();


