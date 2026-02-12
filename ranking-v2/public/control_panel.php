<!DOCTYPE html>
<html lang="he" dir="rtl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>לוח בקרה - פעולות API</title>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }
        
        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            min-height: 100vh;
            padding: 20px;
            direction: rtl;
        }
        
        .container {
            max-width: 1200px;
            margin: 0 auto;
            background: white;
            border-radius: 12px;
            padding: 30px;
            box-shadow: 0 10px 40px rgba(0,0,0,0.2);
        }
        
        h1 {
            color: #333;
            margin-bottom: 10px;
            font-size: 28px;
        }
        
        .subtitle {
            color: #666;
            margin-bottom: 30px;
            font-size: 14px;
        }
        
        .api-list {
            background: #f8f9fa;
            border: 2px solid #e9ecef;
            border-radius: 8px;
            padding: 20px;
            margin-bottom: 30px;
        }
        
        .api-list h2 {
            color: #495057;
            font-size: 18px;
            margin-bottom: 15px;
            border-bottom: 2px solid #667eea;
            padding-bottom: 10px;
        }
        
        .api-item {
            background: white;
            padding: 12px 15px;
            margin-bottom: 10px;
            border-radius: 6px;
            border-left: 4px solid #667eea;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }
        
        .api-item:last-child {
            margin-bottom: 0;
        }
        
        .api-name {
            font-weight: 600;
            color: #333;
        }
        
        .api-path {
            font-family: 'Courier New', monospace;
            color: #666;
            font-size: 13px;
        }
        
        .controls-section {
            margin-bottom: 30px;
        }
        
        .controls-section h2 {
            color: #333;
            font-size: 22px;
            margin-bottom: 20px;
            border-bottom: 2px solid #667eea;
            padding-bottom: 10px;
        }
        
        .button-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(280px, 1fr));
            gap: 15px;
            margin-bottom: 30px;
        }
        
        .action-button {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            border: none;
            padding: 20px;
            border-radius: 8px;
            font-size: 16px;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.3s ease;
            display: flex;
            flex-direction: column;
            align-items: center;
            gap: 10px;
            box-shadow: 0 4px 15px rgba(102, 126, 234, 0.3);
        }
        
        .action-button:hover {
            transform: translateY(-2px);
            box-shadow: 0 6px 20px rgba(102, 126, 234, 0.4);
        }
        
        .action-button:active {
            transform: translateY(0);
        }
        
        .action-button:disabled {
            opacity: 0.6;
            cursor: not-allowed;
            transform: none;
        }
        
        .button-icon {
            font-size: 32px;
        }
        
        .button-label {
            font-size: 16px;
        }
        
        .button-desc {
            font-size: 12px;
            opacity: 0.9;
            font-weight: normal;
        }
        
        .results-section {
            margin-top: 30px;
        }
        
        .results-section h2 {
            color: #333;
            font-size: 22px;
            margin-bottom: 15px;
            border-bottom: 2px solid #667eea;
            padding-bottom: 10px;
        }
        
        .log-container {
            background: #1e1e1e;
            color: #d4d4d4;
            border-radius: 8px;
            padding: 20px;
            font-family: 'Courier New', monospace;
            font-size: 13px;
            max-height: 500px;
            overflow-y: auto;
            line-height: 1.6;
        }
        
        .log-entry {
            margin-bottom: 8px;
            padding: 5px;
            border-left: 3px solid transparent;
            padding-left: 10px;
        }
        
        .log-entry.success {
            border-left-color: #4CAF50;
            color: #4CAF50;
        }
        
        .log-entry.error {
            border-left-color: #f44336;
            color: #ff6b6b;
        }
        
        .log-entry.info {
            border-left-color: #2196F3;
            color: #64b5f6;
        }
        
        .log-entry.warning {
            border-left-color: #ff9800;
            color: #ffb74d;
        }
        
        .clear-log {
            background: #f44336;
            color: white;
            border: none;
            padding: 8px 16px;
            border-radius: 6px;
            cursor: pointer;
            margin-bottom: 10px;
            font-size: 14px;
        }
        
        .clear-log:hover {
            background: #d32f2f;
        }
        
        .status-indicator {
            display: inline-block;
            width: 12px;
            height: 12px;
            border-radius: 50%;
            margin-left: 8px;
        }
        
        .status-indicator.running {
            background: #ff9800;
            animation: pulse 1.5s infinite;
        }
        
        .status-indicator.success {
            background: #4CAF50;
        }
        
        .status-indicator.error {
            background: #f44336;
        }
        
        @keyframes pulse {
            0%, 100% { opacity: 1; }
            50% { opacity: 0.5; }
        }
    </style>
</head>
<body>
    <div class="container">
        <h1>🎛️ לוח בקרה - פעולות API</h1>
        <p class="subtitle">ניהול פעולות עיבוד נתונים דרך API endpoints</p>
        
        <!-- API Endpoints List -->
        <div class="api-list">
            <h2>📋 רשימת API Endpoints זמינים</h2>
            <div class="api-item">
                <div>
                    <div class="api-name">AI Relevance Rating (דירוג רלוונטיות AI)</div>
                    <div class="api-path">api/ai/relevance.php</div>
                </div>
                <span class="status-indicator success"></span>
            </div>
            <div class="api-item">
                <div>
                    <div class="api-name">Create Summary (יצירת סיכום)</div>
                    <div class="api-path">api/create_summary.php</div>
                </div>
                <span class="status-indicator success"></span>
            </div>
            <div class="api-item">
                <div>
                    <div class="api-name">Retry Metadata Extraction (ניסיון חילוץ מטא-דאטה)</div>
                    <div class="api-path">api/retry_extraction.php</div>
                </div>
                <span class="status-indicator success"></span>
            </div>
            <div class="api-item">
                <div>
                    <div class="api-name">Populate Organization Names (אכלס שמות גופים)</div>
                    <div class="api-path">api/populate_organization_names.php</div>
                </div>
                <span class="status-indicator success"></span>
            </div>
            <div class="api-item">
                <div>
                    <div class="api-name">Records CRUD (ניהול רשומות)</div>
                    <div class="api-path">api/records.php</div>
                </div>
                <span class="status-indicator success"></span>
            </div>
            <div class="api-item">
                <div>
                    <div class="api-name">Update Summary (עדכון סיכום)</div>
                    <div class="api-path">api/summaries.php</div>
                </div>
                <span class="status-indicator success"></span>
            </div>
            <div class="api-item">
                <div>
                    <div class="api-name">Populate Organizations Table (אוכלס טבלת ארגונים)</div>
                    <div class="api-path">api/populate_organizations_table.php</div>
                </div>
                <span class="status-indicator success"></span>
            </div>
            <div class="api-item">
                <div>
                    <div class="api-name">Populate Educational Programs (אוכלס תוכניות חינוכיות)</div>
                    <div class="api-path">api/populate_educational_programs.php</div>
                </div>
                <span class="status-indicator success"></span>
            </div>
            <div class="api-item">
                <div>
                    <div class="api-name">Populate Keywords (אוכלס מילות מפתח)</div>
                    <div class="api-path">api/populate_programs_keywords.php</div>
                </div>
                <span class="status-indicator success"></span>
            </div>
            <div class="api-item">
                <div>
                    <div class="api-name">Improve Organization Names Hebrew (שפר שמות ארגונים בעברית)</div>
                    <div class="api-path">api/improve_organization_names_hebrew.php</div>
                </div>
                <span class="status-indicator success"></span>
            </div>
        </div>
        
        <!-- Controls Section -->
        <div class="controls-section">
            <h2>🎮 פעולות זמינות</h2>
            <div class="button-grid">
                <!-- AI Relevance Rating -->
                <button class="action-button" onclick="startAIRating()" id="btnAIRating">
                    <span class="button-icon">⭐</span>
                    <span class="button-label">דירוג רלוונטיות AI</span>
                    <span class="button-desc">דירוג אוטומטי של רלוונטיות (1-5) לכל הרשומות</span>
                </button>
                
                <!-- Create Summaries -->
                <button class="action-button" onclick="createSummaries()" id="btnSummaries">
                    <span class="button-icon">📝</span>
                    <span class="button-label">יצירת סיכומים</span>
                    <span class="button-desc">יצירת סיכומים אוטומטיים לכל הרשומות</span>
                </button>
                
                <!-- Retry Metadata Extraction -->
                <button class="action-button" onclick="retryMetadataExtraction()" id="btnMetadata">
                    <span class="button-icon">🔄</span>
                    <span class="button-label">חילוץ מטא-דאטה</span>
                    <span class="button-desc">ניסיון חילוץ מטא-דאטה לרשומות שנכשלו</span>
                </button>
                
                <!-- Populate Organization Names -->
                <button class="action-button" onclick="populateOrganizations()" id="btnOrganizations">
                    <span class="button-icon">🏢</span>
                    <span class="button-label">אכלס שמות גופים</span>
                    <span class="button-desc">אוטומציה של שמות ארגונים מהמטא-דאטה</span>
                </button>
                
                <!-- Populate Organizations Table -->
                <button class="action-button" onclick="populateOrganizationsTable()" id="btnPopulateOrganizationsTable">
                    <span class="button-icon">📥</span>
                    <span class="button-label">אוכלס טבלת ארגונים</span>
                    <span class="button-desc">מילוי טבלת organizations מנתוני ranking_urls</span>
                </button>
                
                <!-- Populate Educational Programs -->
                <button class="action-button" onclick="populateEducationalPrograms()" id="btnPopulateEducationalPrograms">
                    <span class="button-icon">🎓</span>
                    <span class="button-label">אוכלס תוכניות חינוכיות</span>
                    <span class="button-desc">חילוץ תוכניות חינוכיות מ-URLs באמצעות AI</span>
                </button>
                
                <!-- Populate Keywords -->
                <button class="action-button" onclick="populateProgramsKeywords()" id="btnPopulateKeywords">
                    <span class="button-icon">🔑</span>
                    <span class="button-label">אוכלס מילות מפתח</span>
                    <span class="button-desc">חילוץ מילות מפתח לתוכניות חינוכיות באמצעות AI</span>
                </button>
                
                <!-- Improve Organization Names Hebrew -->
                <button class="action-button" onclick="improveOrganizationNamesHebrew()" id="btnImproveOrgNamesHebrew">
                    <span class="button-icon">✏️</span>
                    <span class="button-label">שפר שמות ארגונים בעברית</span>
                    <span class="button-desc">שיפור שמות ארגונים בעברית באמצעות AI (ויקיפדיה → ויקיפדיה, עיריות וכו')</span>
                </button>
                
                <!-- Add Organization Names to Organizations Table -->
                <button class="action-button" onclick="addOrgNamesToOrganizationsTable()" id="btnAddOrgNamesToTable">
                    <span class="button-icon">📥</span>
                    <span class="button-label">הוסף שמות לטבלת ארגונים</span>
                    <span class="button-desc">מוסיף את כל השמות מהרשימה לטבלת organizations</span>
                </button>
                
                <!-- Clear Organizations Table -->
                <button class="action-button" onclick="clearOrganizationsTable()" id="btnClearOrganizationsTable" style="background: linear-gradient(135deg, #f44336 0%, #d32f2f 100%);">
                    <span class="button-icon">🗑️</span>
                    <span class="button-label">נקה טבלת ארגונים</span>
                    <span class="button-desc">מוחק את כל הנתונים מטבלת organizations</span>
                </button>
            </div>
        </div>
        
        <!-- Organization Names Management Section -->
        <div class="controls-section" style="margin-top: 40px;">
            <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 15px;">
                <h2>📋 ניהול שמות ארגונים</h2>
                <button class="clear-log" onclick="loadOrganizationNames()" style="padding: 8px 16px; font-size: 14px;">🔄 רענון רשימה</button>
            </div>
            <!-- Filter Section -->
            <div style="margin-bottom: 20px; padding: 15px; background: #f8f9fa; border-radius: 8px; border: 2px solid #e9ecef;">
                <label for="orgNameFilter" style="display: block; margin-bottom: 8px; font-weight: 600; color: #495057;">
                    🔍 סינון לפי שם גוף:
                </label>
                <select id="orgNameFilter" onchange="filterOrganizationNames()" style="width: 100%; padding: 10px; border: 2px solid #667eea; border-radius: 6px; font-size: 14px; background: white; cursor: pointer;">
                    <option value="">כל השמות</option>
                </select>
            </div>
            <div style="background: #f8f9fa; border: 2px solid #e9ecef; border-radius: 8px; padding: 20px; max-height: 500px; overflow-y: auto;">
                <div id="organizationNamesList" style="display: grid; grid-template-columns: repeat(auto-fill, minmax(300px, 1fr)); gap: 12px;">
                    <div style="text-align: center; color: #666; padding: 20px;">טוען שמות ארגונים...</div>
                </div>
            </div>
            <div style="margin-top: 15px; padding: 15px; background: #fff3cd; border-radius: 6px; border: 1px solid #ffc107;">
                <strong>💡 הוראות:</strong>
                <ul style="margin: 10px 0 0 20px; line-height: 1.8;">
                    <li>ערוך שם בשדה - לחץ Enter או מחוץ לשדה כדי לשמור. כל הרשומות עם השם הישן יתעדכנו אוטומטית</li>
                    <li>לחץ על כפתור "הוסף שמות לטבלת ארגונים" כדי להעביר את כל השמות מהרשימה לטבלת organizations</li>
                    <li>השתמש ב-"נקה טבלת ארגונים" כדי למחוק את כל הנתונים מטבלת organizations</li>
                </ul>
            </div>
        </div>
        
        <!-- Results Section -->
        <div class="results-section">
            <div style="display: flex; justify-content: space-between; align-items: center;">
                <h2>📊 לוג פעולות</h2>
                <button class="clear-log" onclick="clearLog()">נקה לוג</button>
            </div>
            <div class="log-container" id="logContainer">
                <div class="log-entry info">מוכן לפעולה. בחר פעולה מהכפתורים למעלה.</div>
            </div>
        </div>
    </div>
    
    <script>
        let currentJobId = null;
        let pollingInterval = null;
        let allOrganizationNames = []; // Store all organization names for filtering
        
        function addLog(message, type = 'info') {
            const logContainer = document.getElementById('logContainer');
            const timestamp = new Date().toLocaleTimeString('he-IL');
            const logEntry = document.createElement('div');
            logEntry.className = `log-entry ${type}`;
            logEntry.textContent = `[${timestamp}] ${message}`;
            logContainer.appendChild(logEntry);
            logContainer.scrollTop = logContainer.scrollHeight;
        }
        
        function clearLog() {
            document.getElementById('logContainer').innerHTML = '';
            addLog('לוג נוקה', 'info');
        }
        
        function setButtonLoading(buttonId, loading) {
            const btn = document.getElementById(buttonId);
            if (loading) {
                btn.disabled = true;
                btn.querySelector('.button-icon').textContent = '⏳';
            } else {
                btn.disabled = false;
            }
        }
        
        // AI Relevance Rating
        async function startAIRating() {
            const btnId = 'btnAIRating';
            setButtonLoading(btnId, true);
            addLog('מתחיל דירוג רלוונטיות AI...', 'info');
            
            try {
                const response = await fetch('api/ai/relevance.php', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json'
                    },
                    body: JSON.stringify({
                        action: 'start',
                        only_unrated: true,
                        limit: 0
                    })
                });
                
                const text = await response.text();
                
                if (!response.ok) {
                    addLog(`HTTP ${response.status} Error`, 'error');
                    addLog(`תגובה מהשרת: ${text.substring(0, 1000)}`, 'error');
                    throw new Error(`HTTP ${response.status}: ${text.substring(0, 200)}`);
                }
                
                if (!text || text.trim().length === 0) {
                    addLog('תגובה ריקה מהשרת - בדוק את לוגי PHP', 'error');
                    throw new Error('תגובה ריקה מהשרת');
                }
                
                let data;
                try {
                    data = JSON.parse(text);
                } catch (e) {
                    addLog(`תגובה גולמית מהשרת (${text.length} תווים):`, 'error');
                    addLog(`${text.substring(0, 1000)}`, 'error');
                    addLog(`שגיאת JSON: ${e.message}`, 'error');
                    throw new Error('תגובה לא תקינה מהשרת: ' + e.message);
                }
                
                if (!data || !data.success) {
                    const errorMsg = data?.error || 'שגיאה לא ידועה';
                    const errorDetails = data?.file ? ` (קובץ: ${data.file}, שורה: ${data.line})` : '';
                    addLog(`שגיאה מהשרת: ${errorMsg}${errorDetails}`, 'error');
                    if (data?.details) {
                        addLog(`פרטים: ${JSON.stringify(data.details)}`, 'error');
                    }
                    throw new Error(errorMsg);
                }
                
                if (!data.job_id) {
                    throw new Error('לא התקבל מזהה עבודה מהשרת');
                }
                
                currentJobId = data.job_id;
                addLog(`דירוג AI התחיל. מזהה עבודה: ${currentJobId}`, 'success');
                addLog(`סה"כ רשומות לדירוג: ${data.total || 0}`, 'info');
                
                // Start polling
                startPolling();
                
            } catch (error) {
                addLog(`שגיאה: ${error.message}`, 'error');
                console.error('AI Rating Error:', error);
                setButtonLoading(btnId, false);
            }
        }
        
        function startPolling() {
            if (pollingInterval) clearInterval(pollingInterval);
            
            pollingInterval = setInterval(async () => {
                if (!currentJobId) return;
                
                try {
                    // First, trigger processing if needed
                    try {
                        const processResponse = await fetch(`api/ai/relevance.php?job_id=${currentJobId}&action=process`);
                        const processText = await processResponse.text();
                        
                        if (!processResponse.ok) {
                            addLog(`שגיאת process HTTP ${processResponse.status}: ${processText.substring(0, 200)}`, 'error');
                        } else if (processText) {
                            try {
                                const processData = JSON.parse(processText);
                                if (processData.success) {
                                    if (processData.last_url) {
                                        addLog(`מעבד: ${processData.last_url.substring(0, 50)}...`, 'info');
                                    }
                                    if (processData.completed) {
                                        addLog('עיבוד הושלם!', 'success');
                                    }
                                } else {
                                    addLog(`שגיאה ב-process: ${processData.error || 'שגיאה לא ידועה'}`, 'error');
                                }
                            } catch (e) {
                                addLog(`שגיאת JSON ב-process: ${e.message}. תגובה: ${processText.substring(0, 200)}`, 'error');
                            }
                        }
                    } catch (e) {
                        addLog(`שגיאה בקריאה ל-process: ${e.message}`, 'error');
                    }
                    
                    // Then check status
                    const response = await fetch(`api/ai/relevance.php?job_id=${currentJobId}`);
                    
                    if (!response.ok) {
                        const errorText = await response.text();
                        addLog(`שגיאת HTTP ${response.status}: ${errorText.substring(0, 200)}`, 'error');
                        return;
                    }
                    
                    const text = await response.text();
                    if (!text || text.trim().length === 0) {
                        addLog('תגובה ריקה מהשרת', 'error');
                        return;
                    }
                    
                    let data;
                    try {
                        data = JSON.parse(text);
                    } catch (e) {
                        addLog(`שגיאת JSON: ${e.message}. תגובה: ${text.substring(0, 200)}`, 'error');
                        return;
                    }
                    
                    if (!data.success) {
                        addLog(`שגיאה מהשרת: ${data.error || 'שגיאה לא ידועה'}`, 'error');
                        return;
                    }
                    
                    if (!data.progress) {
                        addLog(`תגובה לא תקינה: אין progress. תגובה: ${JSON.stringify(data).substring(0, 200)}`, 'error');
                        return;
                    }
                    
                    if (data.completed) {
                        clearInterval(pollingInterval);
                        pollingInterval = null;
                        addLog(`דירוג הושלם! דורגו: ${data.progress.done || 0}, דולגו: ${data.progress.skipped || 0}, שגיאות: ${data.progress.error || 0}`, 'success');
                        setButtonLoading('btnAIRating', false);
                        currentJobId = null;
                    } else if (data.cancelled) {
                        clearInterval(pollingInterval);
                        pollingInterval = null;
                        addLog('דירוג בוטל', 'warning');
                        setButtonLoading('btnAIRating', false);
                        currentJobId = null;
                    } else {
                        addLog(`מתקדם: ${data.progress.processed || 0}/${data.progress.total || 0} (דורגו: ${data.progress.done || 0}, דולגו: ${data.progress.skipped || 0})`, 'info');
                    }
                } catch (error) {
                    addLog(`שגיאה בבדיקת סטטוס: ${error.message}`, 'error');
                    console.error('Polling error:', error);
                }
            }, 2000);
        }
        
        // Create Summaries
        async function createSummaries() {
            const btnId = 'btnSummaries';
            setButtonLoading(btnId, true);
            addLog('מתחיל יצירת סיכומים...', 'info');
            
            try {
                // First, get all records without summaries
                const recordsResponse = await fetch('api/records.php?pageSize=10000');
                const recordsData = await recordsResponse.json();
                
                if (!recordsData.success) {
                    throw new Error('שגיאה בטעינת רשומות');
                }
                
                const recordsWithoutSummary = recordsData.records.filter(r => !r.short_summary || r.short_summary.trim().length === 0);
                
                if (recordsWithoutSummary.length === 0) {
                    addLog('כל הרשומות כבר יש להן סיכום!', 'info');
                    setButtonLoading(btnId, false);
                    return;
                }
                
                addLog(`נמצאו ${recordsWithoutSummary.length} רשומות ללא סיכום`, 'info');
                
                let successCount = 0;
                let failCount = 0;
                
                for (let i = 0; i < recordsWithoutSummary.length; i++) {
                    const record = recordsWithoutSummary[i];
                    addLog(`יוצר סיכום ${i + 1}/${recordsWithoutSummary.length} עבור רשומה ${record.id}...`, 'info');
                    
                    try {
                        const response = await fetch('api/create_summary.php', {
                            method: 'POST',
                            headers: {
                                'Content-Type': 'application/json'
                            },
                            body: JSON.stringify({ id: record.id })
                        });
                        
                        const data = await response.json();
                        
                        if (data.success) {
                            successCount++;
                            addLog(`✓ סיכום נוצר בהצלחה עבור רשומה ${record.id}`, 'success');
                        } else {
                            failCount++;
                            addLog(`✗ שגיאה ביצירת סיכום עבור רשומה ${record.id}: ${data.error}`, 'error');
                        }
                    } catch (error) {
                        failCount++;
                        addLog(`✗ שגיאה עבור רשומה ${record.id}: ${error.message}`, 'error');
                    }
                    
                    // Small delay to avoid overwhelming the API
                    await new Promise(resolve => setTimeout(resolve, 500));
                }
                
                addLog(`יצירת סיכומים הושלמה! הצלחה: ${successCount}, כשלונות: ${failCount}`, 'success');
                setButtonLoading(btnId, false);
                
            } catch (error) {
                addLog(`שגיאה: ${error.message}`, 'error');
                setButtonLoading(btnId, false);
            }
        }
        
        // Retry Metadata Extraction
        async function retryMetadataExtraction() {
            const btnId = 'btnMetadata';
            setButtonLoading(btnId, true);
            addLog('מתחיל חילוץ מטא-דאטה מחדש...', 'info');
            
            try {
                // Get all failed records
                const recordsResponse = await fetch('api/records.php?status=failed&pageSize=10000');
                const recordsData = await recordsResponse.json();
                
                if (!recordsData.success) {
                    throw new Error('שגיאה בטעינת רשומות');
                }
                
                const failedRecords = recordsData.records;
                
                if (failedRecords.length === 0) {
                    addLog('לא נמצאו רשומות שנכשלו בחילוץ מטא-דאטה', 'info');
                    setButtonLoading(btnId, false);
                    return;
                }
                
                addLog(`נמצאו ${failedRecords.length} רשומות שנכשלו`, 'info');
                
                let successCount = 0;
                let failCount = 0;
                
                for (let i = 0; i < failedRecords.length; i++) {
                    const record = failedRecords[i];
                    addLog(`מנסה חילוץ ${i + 1}/${failedRecords.length} עבור רשומה ${record.id}...`, 'info');
                    
                    try {
                        const response = await fetch('api/retry_extraction.php', {
                            method: 'POST',
                            headers: {
                                'Content-Type': 'application/json'
                            },
                            body: JSON.stringify({ id: record.id })
                        });
                        
                        const data = await response.json();
                        
                        if (data.success) {
                            successCount++;
                            addLog(`✓ חילוץ הצליח עבור רשומה ${record.id}`, 'success');
                        } else {
                            failCount++;
                            addLog(`✗ חילוץ נכשל עבור רשומה ${record.id}: ${data.error}`, 'error');
                        }
                    } catch (error) {
                        failCount++;
                        addLog(`✗ שגיאה עבור רשומה ${record.id}: ${error.message}`, 'error');
                    }
                    
                    await new Promise(resolve => setTimeout(resolve, 1000));
                }
                
                addLog(`חילוץ מטא-דאטה הושלם! הצלחה: ${successCount}, כשלונות: ${failCount}`, 'success');
                setButtonLoading(btnId, false);
                
            } catch (error) {
                addLog(`שגיאה: ${error.message}`, 'error');
                setButtonLoading(btnId, false);
            }
        }
        
        // Populate Organization Names
        async function populateOrganizations() {
            const btnId = 'btnOrganizations';
            setButtonLoading(btnId, true);
            addLog('מתחיל אכלס שמות גופים...', 'info');
            
            try {
                const response = await fetch('api/populate_organization_names.php');
                const text = await response.text();
                let data;
                try {
                    data = JSON.parse(text);
                } catch (e) {
                    throw new Error('תגובה לא תקינה מהשרת: ' + text.substring(0, 200));
                }
                
                if (!data.success) {
                    throw new Error(data.error || 'שגיאה באכלסה');
                }
                
                const result = data.result;
                addLog(`אכלסה הושלמה!`, 'success');
                addLog(`סה"כ: ${result.total}`, 'info');
                addLog(`עודכנו: ${result.updated}`, 'success');
                addLog(`דולגו: ${result.skipped}`, 'info');
                if (result.errors > 0) {
                    addLog(`שגיאות: ${result.errors}`, 'error');
                }
                
                setButtonLoading(btnId, false);
                
            } catch (error) {
                addLog(`שגיאה: ${error.message}`, 'error');
                setButtonLoading(btnId, false);
            }
        }
        
        // Populate Organizations Table
        async function populateOrganizationsTable() {
            const btnId = 'btnPopulateOrganizationsTable';
            setButtonLoading(btnId, true);
            addLog('מתחיל אוכלס טבלת organizations מנתוני ranking_urls...', 'info');
            
            try {
                const response = await fetch('api/populate_organizations_table.php');
                const text = await response.text();
                let data;
                try {
                    data = JSON.parse(text);
                } catch (e) {
                    throw new Error('תגובה לא תקינה מהשרת: ' + text.substring(0, 200));
                }
                
                if (!data.success) {
                    throw new Error(data.error || 'שגיאה באוכלס הטבלה');
                }
                
                const stats = data.stats || {};
                addLog(data.message, 'success');
                addLog(`נמצאו: ${stats.total_found || 0} ארגונים ייחודיים`, 'info');
                addLog(`נוספו: ${stats.inserted || 0}`, 'success');
                addLog(`עודכנו: ${stats.updated || 0}`, 'info');
                addLog(`דולגו: ${stats.skipped || 0}`, 'info');
                if (stats.errors > 0) {
                    addLog(`שגיאות: ${stats.errors}`, 'error');
                }
                
                setButtonLoading(btnId, false);
                
            } catch (error) {
                addLog(`שגיאה: ${error.message}`, 'error');
                setButtonLoading(btnId, false);
            }
        }
        
        // Populate Educational Programs
        async function populateEducationalPrograms() {
            const btnId = 'btnPopulateEducationalPrograms';
            setButtonLoading(btnId, true);
            addLog('מתחיל חילוץ תוכניות חינוכיות מ-URLs...', 'info');
            addLog('זה עשוי לקחת זמן - הקוד עובר על כל ה-URLs הרלוונטיים ומשתמש ב-AI לחילוץ תוכניות', 'info');
            
            try {
                const limit = prompt('הזן מספר מקסימלי של URLs לעיבוד (השאר ריק לכל ה-URLs):');
                const url = limit ? `api/populate_educational_programs.php?limit=${limit}` : 'api/populate_educational_programs.php';
                
                const response = await fetch(url);
                const text = await response.text();
                let data;
                try {
                    data = JSON.parse(text);
                } catch (e) {
                    throw new Error('תגובה לא תקינה מהשרת: ' + text.substring(0, 200));
                }
                
                if (!data.success) {
                    throw new Error(data.error || 'שגיאה באוכלס הטבלה');
                }
                
                const stats = data.stats || {};
                addLog(data.message, 'success');
                addLog(`סה"כ URLs שנבדקו: ${stats.total_urls || 0}`, 'info');
                addLog(`URLs שעובדו: ${stats.processed || 0}`, 'info');
                addLog(`תוכניות שנמצאו: ${stats.programs_found || 0}`, 'success');
                addLog(`תוכניות שנוספו: ${stats.programs_inserted || 0}`, 'success');
                if (stats.errors > 0) {
                    addLog(`שגיאות: ${stats.errors}`, 'error');
                }
                
                setButtonLoading(btnId, false);
                
            } catch (error) {
                addLog(`שגיאה: ${error.message}`, 'error');
                setButtonLoading(btnId, false);
            }
        }
        
        // Populate Keywords
        async function populateProgramsKeywords() {
            const btnId = 'btnPopulateKeywords';
            setButtonLoading(btnId, true);
            addLog('מתחיל חילוץ מילות מפתח לתוכניות חינוכיות...', 'info');
            addLog('זה עשוי לקחת זמן - הקוד עובר על כל התוכניות ומשתמש ב-AI לחילוץ מילות מפתח', 'info');
            
            try {
                const limit = prompt('הזן מספר מקסימלי של תוכניות לעיבוד (השאר ריק לכל התוכניות):');
                const all = confirm('לעדכן גם תוכניות שכבר יש להן מילות מפתח?');
                let url = 'api/populate_programs_keywords.php';
                if (limit) {
                    url += `?limit=${limit}`;
                    if (all) url += '&all=1';
                } else if (all) {
                    url += '?all=1';
                }
                
                const response = await fetch(url);
                const text = await response.text();
                let data;
                try {
                    data = JSON.parse(text);
                } catch (e) {
                    throw new Error('תגובה לא תקינה מהשרת: ' + text.substring(0, 200));
                }
                
                if (!data.success) {
                    throw new Error(data.error || 'שגיאה באוכלס מילות מפתח');
                }
                
                const stats = data.stats || {};
                addLog(data.message, 'success');
                addLog(`סה"כ תוכניות: ${stats.total || 0}`, 'info');
                addLog(`עובדו: ${stats.processed || 0}`, 'info');
                addLog(`עודכנו: ${stats.updated || 0}`, 'success');
                if (stats.errors > 0) {
                    addLog(`שגיאות: ${stats.errors}`, 'error');
                }
                
                setButtonLoading(btnId, false);
                
            } catch (error) {
                addLog(`שגיאה: ${error.message}`, 'error');
                setButtonLoading(btnId, false);
            }
        }
        
        // Improve Organization Names Hebrew
        async function improveOrganizationNamesHebrew() {
            const btnId = 'btnImproveOrgNamesHebrew';
            setButtonLoading(btnId, true);
            addLog('מתחיל שיפור שמות ארגונים בעברית...', 'info');
            addLog('זה עשוי לקחת זמן - הקוד עובר על כל הרשומות ומשתמש ב-AI לשיפור שמות (ויקיפדיה → ויקיפדיה, עיריות וכו)', 'info');
            
            try {
                const limit = prompt('הזן מספר מקסימלי של רשומות לעיבוד (השאר ריק לכל הרשומות):');
                const all = confirm('לעדכן גם רשומות שכבר יש להן שמות בעברית?');
                let url = 'api/improve_organization_names_hebrew.php';
                if (limit) {
                    url += `?limit=${limit}`;
                    if (all) url += '&all=1';
                } else if (all) {
                    url += '?all=1';
                }
                
                const response = await fetch(url);
                const text = await response.text();
                let data;
                try {
                    data = JSON.parse(text);
                } catch (e) {
                    throw new Error('תגובה לא תקינה מהשרת: ' + text.substring(0, 200));
                }
                
                if (!data.success) {
                    throw new Error(data.error || 'שגיאה בשיפור שמות ארגונים');
                }
                
                const stats = data.stats || {};
                addLog(data.message, 'success');
                addLog(`סה"כ רשומות: ${stats.total || 0}`, 'info');
                addLog(`עובדו: ${stats.processed || 0}`, 'info');
                addLog(`עודכנו: ${stats.updated || 0}`, 'success');
                addLog(`דולגו: ${stats.skipped || 0}`, 'info');
                if (stats.errors > 0) {
                    addLog(`שגיאות: ${stats.errors}`, 'error');
                }
                
                setButtonLoading(btnId, false);
                
            } catch (error) {
                addLog(`שגיאה: ${error.message}`, 'error');
                setButtonLoading(btnId, false);
            }
        }
        
        // Load Organization Names List
        async function loadOrganizationNames() {
            const listContainer = document.getElementById('organizationNamesList');
            const filterSelect = document.getElementById('orgNameFilter');
            if (!listContainer || !filterSelect) return;
            
            listContainer.innerHTML = '<div style="text-align: center; color: #666; padding: 20px;">טוען שמות ארגונים...</div>';
            
            try {
                const response = await fetch('api/organization_names_list.php');
                const text = await response.text();
                let data;
                try {
                    data = JSON.parse(text);
                } catch (e) {
                    throw new Error('תגובה לא תקינה מהשרת: ' + text.substring(0, 200));
                }
                
                if (!data.success) {
                    throw new Error(data.error || 'שגיאה בטעינת שמות');
                }
                
                if (!data.names || data.names.length === 0) {
                    listContainer.innerHTML = '<div style="text-align: center; color: #666; padding: 20px;">לא נמצאו שמות ארגונים</div>';
                    filterSelect.innerHTML = '<option value="">כל השמות</option>';
                    allOrganizationNames = [];
                    return;
                }
                
                // Store all names globally
                allOrganizationNames = data.names;
                
                // Populate filter dropdown
                const currentFilter = filterSelect.value || '';
                filterSelect.innerHTML = '<option value="">כל השמות</option>';
                data.names.forEach(item => {
                    const option = document.createElement('option');
                    option.value = item.name;
                    option.textContent = item.name;
                    if (item.name === currentFilter) {
                        option.selected = true;
                    }
                    filterSelect.appendChild(option);
                });
                
                // Apply current filter
                filterOrganizationNames();
                
            } catch (error) {
                listContainer.innerHTML = `<div style="text-align: center; color: #f44336; padding: 20px;">שגיאה: ${error.message}</div>`;
                addLog(`שגיאה בטעינת שמות ארגונים: ${error.message}`, 'error');
            }
        }
        
        // Filter Organization Names by selected name
        function filterOrganizationNames() {
            const listContainer = document.getElementById('organizationNamesList');
            const filterSelect = document.getElementById('orgNameFilter');
            if (!listContainer || !filterSelect || !allOrganizationNames) return;
            
            const selectedFilter = filterSelect.value || '';
            
            // Filter names based on selection
            const filteredNames = selectedFilter 
                ? allOrganizationNames.filter(item => item.name === selectedFilter)
                : allOrganizationNames;
            
            if (filteredNames.length === 0) {
                listContainer.innerHTML = '<div style="text-align: center; color: #666; padding: 20px;">לא נמצאו שמות ארגונים</div>';
                return;
            }
            
            listContainer.innerHTML = filteredNames.map(item => {
                const typesDisplay = item.types && item.types.length > 0 
                    ? item.types.filter(t => t).join(', ') 
                    : '-';
                
                return `
                    <div class="org-name-item" style="background: white; border: 1px solid #ddd; border-radius: 6px; padding: 12px; position: relative;">
                        <div style="display: flex; align-items: center; gap: 10px;">
                            <input type="text" 
                                   class="org-name-input" 
                                   data-original-name="${escapeHtml(item.name)}"
                                   value="${escapeHtml(item.name)}"
                                   style="flex: 1; padding: 6px 10px; border: 2px solid #667eea; border-radius: 4px; font-size: 14px;"
                                   onblur="updateOrganizationNameBulk('${escapeHtml(item.name).replace(/'/g, "\\'")}', this.value)"
                                   onkeydown="if(event.key === 'Enter') { event.preventDefault(); updateOrganizationNameBulk('${escapeHtml(item.name).replace(/'/g, "\\'")}', this.value); this.blur(); }">
                            <div style="display: flex; flex-direction: column; align-items: flex-end; gap: 4px; min-width: 80px;">
                                <span style="font-size: 11px; color: #666; white-space: nowrap;">${item.url_count} URLs</span>
                                <span style="font-size: 10px; color: #999; max-width: 100px; overflow: hidden; text-overflow: ellipsis;" title="${typesDisplay}">${typesDisplay}</span>
                            </div>
                        </div>
                    </div>
                `;
            }).join('');
        }
        
        function escapeHtml(text) {
            if (!text) return '';
            const div = document.createElement('div');
            div.textContent = text;
            return div.innerHTML;
        }
        
        // Update Organization Name in All Records
        async function updateOrganizationNameBulk(oldName, newName) {
            if (!oldName || !newName || oldName.trim() === newName.trim()) {
                return; // No change
            }
            
            const trimmedNewName = newName.trim();
            if (trimmedNewName === '') {
                alert('שם ארגון לא יכול להיות ריק');
                loadOrganizationNames(); // Reload to reset
                return;
            }
            
            addLog(`מעדכן שם: "${oldName}" → "${trimmedNewName}"...`, 'info');
            
            try {
                const response = await fetch('api/update_organization_name_bulk.php', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json'
                    },
                    body: JSON.stringify({
                        old_name: oldName,
                        new_name: trimmedNewName
                    })
                });
                
                const text = await response.text();
                let data;
                try {
                    data = JSON.parse(text);
                } catch (e) {
                    throw new Error('תגובה לא תקינה מהשרת: ' + text.substring(0, 200));
                }
                
                if (!data.success) {
                    throw new Error(data.error || 'שגיאה בעדכון שם');
                }
                
                const count = data.updated_count || 0;
                addLog(`עודכנו ${count} רשומות בהצלחה`, 'success');
                
                // Reload the list
                loadOrganizationNames();
                
            } catch (error) {
                addLog(`שגיאה בעדכון שם: ${error.message}`, 'error');
                alert('שגיאה בעדכון שם: ' + error.message);
                loadOrganizationNames(); // Reload to reset
            }
        }
        
        // Add Organization Names to Organizations Table
        async function addOrgNamesToOrganizationsTable() {
            const btnId = 'btnAddOrgNamesToTable';
            setButtonLoading(btnId, true);
            addLog('מתחיל הוספת שמות לטבלת organizations...', 'info');
            
            try {
                const response = await fetch('api/add_org_names_to_organizations.php', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json'
                    }
                });
                
                const text = await response.text();
                let data;
                try {
                    data = JSON.parse(text);
                } catch (e) {
                    throw new Error('תגובה לא תקינה מהשרת: ' + text.substring(0, 200));
                }
                
                if (!data.success) {
                    throw new Error(data.error || 'שגיאה בהוספת שמות');
                }
                
                const stats = data.stats || {};
                addLog(data.message, 'success');
                addLog(`סה"כ שמות: ${stats.total_found || 0}`, 'info');
                addLog(`נוספו: ${stats.inserted || 0}`, 'success');
                addLog(`עודכנו: ${stats.updated || 0}`, 'info');
                if (stats.errors > 0) {
                    addLog(`שגיאות: ${stats.errors}`, 'error');
                }
                
                setButtonLoading(btnId, false);
                
            } catch (error) {
                addLog(`שגיאה: ${error.message}`, 'error');
                setButtonLoading(btnId, false);
            }
        }
        
        // Clear Organizations Table
        async function clearOrganizationsTable() {
            if (!confirm('⚠️ האם אתה בטוח שברצונך למחוק את כל הנתונים מטבלת organizations?\nפעולה זו אינה הפיכה!')) {
                return;
            }
            
            if (!confirm('⚠️⚠️ אתה בטוח? כל הנתונים יימחקו לצמיתות!')) {
                return;
            }
            
            const btnId = 'btnClearOrganizationsTable';
            setButtonLoading(btnId, true);
            addLog('מתחיל מחיקת נתונים מטבלת organizations...', 'info');
            
            try {
                const response = await fetch('api/clear_organizations_table.php', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json'
                    }
                });
                
                const text = await response.text();
                let data;
                try {
                    data = JSON.parse(text);
                } catch (e) {
                    throw new Error('תגובה לא תקינה מהשרת: ' + text.substring(0, 200));
                }
                
                if (!data.success) {
                    throw new Error(data.error || 'שגיאה במחיקה');
                }
                
                addLog('טבלת organizations נוקתה בהצלחה!', 'success');
                addLog(`נמחקו ${data.deleted_count || 0} רשומות`, 'info');
                
                setButtonLoading(btnId, false);
                
            } catch (error) {
                addLog(`שגיאה: ${error.message}`, 'error');
                setButtonLoading(btnId, false);
            }
        }
        
        // Load organization names on page load
        document.addEventListener('DOMContentLoaded', function() {
            loadOrganizationNames();
        });
    </script>
</body>
</html>

