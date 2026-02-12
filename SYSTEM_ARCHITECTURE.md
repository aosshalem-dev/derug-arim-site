# ארכיטקטורת מערכת ניהול נתונים - דירוג ציונות

## סקירה כללית

מערכת ניהול נתונים לניהול וניתוח קישורי URL הקשורים לזהות ישראלית וציונות. המערכת מאפשרת חילוץ מטא-דאטה אוטומטי, יצירת סיכומים, וניהול נתונים מתקדם.

---

## מבנה המערכת

### 1. שכבות האפליקציה

```
┌─────────────────────────────────────────┐
│   Frontend Layer (HTML/CSS/JavaScript)  │
│   - index.html                          │
│   - assets/css/style.css                │
│   - assets/js/app.js                    │
└─────────────────────────────────────────┘
                    ↕
┌─────────────────────────────────────────┐
│   API Layer (PHP)                       │
│   - api/get_records.php                 │
│   - api/get_record.php                  │
│   - api/save_record.php                 │
│   - api/delete_record.php               │
│   - api/create_summary.php              │
│   - api/retry_extraction.php            │
└─────────────────────────────────────────┘
                    ↕
┌─────────────────────────────────────────┐
│   Business Logic Layer                  │
│   - lib/url_fetcher.php                 │
│   - extract_metadata.php                │
└─────────────────────────────────────────┘
                    ↕
┌─────────────────────────────────────────┐
│   Data Access Layer                     │
│   - config/database.php                  │
│   - ensure_metadata_columns.php         │
└─────────────────────────────────────────┘
                    ↕
┌─────────────────────────────────────────┐
│   Database Layer                        │
│   - ranking_urls table                  │
└─────────────────────────────────────────┘
```

---

## פסאודו קוד - זרימת נתונים מלאה

### 2.1 טעינת רשומות (Load Records)

```
FUNCTION loadRecords():
    // שלב 1: הכנת פרמטרים
    pageSize = GET value from "pageSize" dropdown
    sortBy = GET value from "sortBy" dropdown  
    sortOrder = GET value from "sortOrder" dropdown
    currentFilters = GET all filter values (URL search, status, orgType, topic, year)
    
    // שלב 2: בניית URL עם פרמטרים
    params = BUILD URLSearchParams({
        page: currentPage,
        pageSize: pageSize,
        sortBy: sortBy,
        sortOrder: sortOrder,
        ...currentFilters
    })
    
    // שלב 3: קריאה ל-API
    TRY:
        response = FETCH "api/get_records.php?" + params
        IF response.status != 200:
            THROW error
        
        data = PARSE JSON from response
        
        IF data.success == true:
            // שלב 4: הצגת נתונים
            displayRecords(data.records)
            updatePagination(data.pagination)
            updateStats(data.stats)
        ELSE:
            SHOW error message: data.error
            DISPLAY error in table
    CATCH error:
        LOG error to console
        SHOW error message to user
        DISPLAY error in table
END FUNCTION
```

### 2.2 הצגת רשומות בטבלה (Display Records)

```
FUNCTION displayRecords(records):
    tbody = GET element by ID "tableBody"
    
    IF records.length == 0:
        SET tbody.innerHTML = "<tr><td colspan='9'>לא נמצאו רשומות</td></tr>"
        RETURN
    END IF
    
    // עיבוד כל רשומה
    htmlRows = []
    FOR EACH record IN records:
        // בדיקת סיכום
        hasSummary = false
        summaryText = ""
        
        IF record.short_summary != null AND 
           record.short_summary != undefined AND 
           record.short_summary != "":
            summaryText = TRIM(record.short_summary)
            hasSummary = (summaryText.length > 0)
        END IF
        
        // בניית תא סיכום
        IF hasSummary:
            summaryCell = "<span class='summary-text'>" + 
                         TRUNCATE(summaryText, 50) + 
                         "</span>"
        ELSE:
            summaryCell = "<button class='btn-summarize' " +
                         "onclick='createSummary(" + record.id + ")' " +
                         "id='summary-btn-" + record.id + "'>" +
                         "צור תיאור" +
                         "</button>"
        END IF
        
        // בניית שורה בטבלה
        row = BUILD HTML row with:
            - record.id
            - record.url (as link)
            - status badge (record.metadata_status)
            - organization_type (translated)
            - topic_category (translated)
            - record.year or "-"
            - summaryCell
            - formatted created_at date
            - action buttons (view, edit, summarize, retry if failed, delete)
        
        ADD row to htmlRows
    END FOR
    
    SET tbody.innerHTML = JOIN htmlRows with ""
END FUNCTION
```

### 2.3 יצירת סיכום (Create Summary)

```
FUNCTION createSummary(recordId):
    // שלב 1: מציאת הכפתור ועדכון מצב
    btn = GET element by ID "summary-btn-" + recordId
    IF btn == null:
        RETURN
    END IF
    
    // שלב 2: עדכון UI למצב טעינה
    SET btn.disabled = true
    SET btn.innerHTML = "יוצר..."
    SET btn.title = "יוצר סיכום..."
    
    // שלב 3: קריאה ל-API
    TRY:
        response = FETCH "api/create_summary.php" WITH:
            method: POST
            headers: { "Content-Type": "application/json" }
            body: JSON.stringify({ id: recordId })
        
        IF response.status != 200:
            THROW error
        
        data = PARSE JSON from response
        
        IF data.success == true:
            // שלב 4: רענון הטבלה
            loadRecords()
            SHOW success message: data.message
        ELSE:
            SHOW error message: data.error
            // שלב 5: החזרת הכפתור למצב רגיל
            SET btn.disabled = false
            SET btn.innerHTML = "צור תיאור"
            SET btn.title = "צור תיאור"
        END IF
    CATCH error:
        LOG error to console
        SHOW error message to user
        // החזרת הכפתור למצב רגיל
        SET btn.disabled = false
        SET btn.innerHTML = "צור תיאור"
        SET btn.title = "צור תיאור"
    END TRY
END FUNCTION
```

### 2.4 API: יצירת סיכום (Backend)

```
FUNCTION create_summary.php:
    // שלב 1: קבלת נתונים
    input = PARSE JSON from request body
    recordId = input.id
    
    IF recordId is empty:
        RETURN error 400: "מזהה רשומה נדרש"
    END IF
    
    // שלב 2: חיבור למסד נתונים
    conn = GET database connection
    
    // שלב 3: קבלת רשומה
    query = "SELECT * FROM ranking_urls WHERE id = ?"
    record = EXECUTE query with recordId
    
    IF record not found:
        RETURN error 404: "רשומה לא נמצאה"
    END IF
    
    // שלב 4: משיכת תוכן מה-URL
    TRY:
        urlContent = fetchWebpageContent(record.url)
    CATCH error:
        urlContent = "[לא ניתן למשוך תוכן: " + error + "]"
    END TRY
    
    // שלב 5: בניית מטא-דאטה קיימת
    metadataParts = []
    IF record.source_type: ADD "סוג מקור: " + record.source_type
    IF record.year: ADD "שנה: " + record.year
    IF record.organization_type: ADD "סוג ארגון: " + record.organization_type
    ... (כל השדות הרלוונטיים)
    
    metadataString = JOIN metadataParts with ", "
    
    // שלב 6: בניית prompt ל-GPT-4o
    prompt = "על בסיס התוכן הבא והמטא-דאטה הקיימת, " +
             "צור סיכום קצר בעברית (2-3 משפטים) של המסמך.\n" +
             "התמקד בנושא המרכזי, נושאי מפתח, " +
             "והרלוונטיות לזהות ישראלית וציונות.\n\n" +
             "URL: " + record.url + "\n\n" +
             "תוכן הדף:\n" + SUBSTRING(urlContent, 0, 5000) + "\n\n" +
             "מטא-דאטה קיימת:\n" + metadataString + "\n\n" +
             "צור סיכום קצר ומדויק בעברית (2-3 משפטים בלבד)."
    
    // שלב 7: קריאה ל-OpenAI API
    apiRequest = {
        model: "gpt-4o",
        messages: [
            {
                role: "system",
                content: "אתה עוזר מקצועי ליצירת סיכומים בעברית..."
            },
            {
                role: "user",
                content: prompt
            }
        ],
        temperature: 0.5,
        max_tokens: 500
    }
    
    response = CALL OpenAI API with apiRequest
    
    IF response.error:
        THROW error: response.error
    END IF
    
    summary = TRIM(response.choices[0].message.content)
    summary = REMOVE markdown code blocks from summary
    
    // שלב 8: שמירה למסד נתונים
    updateQuery = "UPDATE ranking_urls SET short_summary = ? WHERE id = ?"
    EXECUTE updateQuery with (summary, recordId)
    
    // שלב 9: החזרת תשובה
    RETURN {
        success: true,
        message: "סיכום נוצר בהצלחה",
        summary: summary
    }
END FUNCTION
```

### 2.5 ניסיון חילוץ מחדש (Retry Extraction)

```
FUNCTION retryExtraction(recordId):
    // שלב 1: אישור משתמש
    IF NOT confirm("האם אתה בטוח שברצונך לנסות שוב?"):
        RETURN
    END IF
    
    // שלב 2: מציאת הכפתור ועדכון מצב
    btn = GET element by ID "retry-btn-" + recordId
    SET btn.disabled = true
    SET btn.innerHTML = "⏳"
    SET btn.title = "מנסה שוב..."
    
    // שלב 3: קריאה ל-API
    TRY:
        response = FETCH "api/retry_extraction.php" WITH:
            method: POST
            headers: { "Content-Type": "application/json" }
            body: JSON.stringify({ id: recordId })
        
        data = PARSE JSON from response
        
        IF data.success == true:
            loadRecords()
            SHOW success message: data.message
        ELSE:
            errorMsg = data.error
            IF data.failure_reason:
                errorMsg += "\n\nסיבת כשלון:\n" + data.failure_reason
            END IF
            ALERT errorMsg
        END IF
    CATCH error:
        SHOW error message
    FINALLY:
        SET btn.disabled = false
        SET btn.innerHTML = "🔄"
        SET btn.title = "נסה שוב"
    END TRY
END FUNCTION
```

### 2.6 API: ניסיון חילוץ מחדש (Backend)

```
FUNCTION retry_extraction.php:
    // שלב 1: קבלת נתונים
    input = PARSE JSON from request body
    recordId = input.id
    
    // שלב 2: קבלת רשומה
    record = GET record from database WHERE id = recordId
    
    // שלב 3: משיכת תוכן
    TRY:
        content = fetchWebpageContent(record.url)
        urlInfo = PARSE URL(record.url)
        domain = urlInfo.host
        path = urlInfo.path
    CATCH error:
        content = "[Error: " + error + "]"
    END TRY
    
    // שלב 4: חילוץ מטא-דאטה עם GPT-4o
    TRY:
        metadata = extractMetadataWithOpenAI(
            record.url, 
            domain, 
            path, 
            content, 
            existingContentTypes
        )
        
        IF metadata AND metadata.source_type:
            // שלב 5: עדכון מסד נתונים - הצלחה
            UPDATE ranking_urls SET:
                source_type = metadata.source_type
                year = metadata.year
                organization_type = metadata.organization_type
                ... (כל השדות)
                metadata_status = 'extracted'
                failure_reason = NULL
                metadata_extracted_at = NOW()
            WHERE id = recordId
            
            RETURN {
                success: true,
                message: "מטא-דאטה חולצה בהצלחה",
                metadata: metadata
            }
        ELSE:
            THROW error: "Failed to extract metadata"
        END IF
    CATCH error:
        // שלב 6: הסבר כשלון עם GPT-4o
        failureReason = explainFailure(record.url, content, error.message)
        
        // שלב 7: עדכון מסד נתונים - כשלון
        UPDATE ranking_urls SET:
            metadata_status = 'failed'
            failure_reason = failureReason
            metadata_extracted_at = NOW()
        WHERE id = recordId
        
        RETURN {
            success: false,
            message: "חילוץ מטא-דאטה נכשל",
            failure_reason: failureReason
        }
    END TRY
END FUNCTION
```

---

## מבנה מסד הנתונים

### 3.1 טבלת ranking_urls

```
TABLE ranking_urls:
    PRIMARY KEY: id (INT AUTO_INCREMENT)
    
    // שדות בסיסיים
    url (VARCHAR(2048) UNIQUE NOT NULL)
    created_at (TIMESTAMP DEFAULT CURRENT_TIMESTAMP)
    
    // מטא-דאטה בסיסי
    source_type (VARCHAR(100) NULL)
    year (INT NULL)
    metadata_extracted_at (TIMESTAMP NULL)
    metadata_status (ENUM: 'pending', 'extracted', 'failed')
    
    // הקשר מוסדי
    organization_type (ENUM: 'municipality', 'government_agency', ...)
    jurisdiction_level (ENUM: 'local', 'regional', 'national', 'international')
    geographic_scope (VARCHAR(200) NULL)
    
    // תחום תוכן
    topic_category (ENUM: 'education', 'culture', 'policy', ...)
    document_type (ENUM: 'report', 'article', 'policy_document', ...)
    target_audience (ENUM: 'general_public', 'educators', ...)
    content_type (VARCHAR(200) NULL)
    
    // אינדיקטורים אידיאולוגיים
    values_orientation (JSON NULL)
    cultural_focus (ENUM: 'hebrew_culture', 'jewish_heritage', ...)
    
    // זהות וציונות
    zionism_references (ENUM: 'explicit', 'implicit', 'none', 'unclear')
    identity_theme (JSON NULL)
    historical_periods (JSON NULL)
    
    // שקיפות ושפה
    language (ENUM: 'hebrew', 'english', 'arabic', ...)
    accessibility_level (ENUM: 'public', 'restricted', 'unclear')
    publication_format (ENUM: 'pdf', 'html', 'text', ...)
    
    // סמנים זמניים
    period_referenced (VARCHAR(100) NULL)
    temporal_scope (ENUM: 'current', 'historical', 'future', 'mixed')
    
    // איכות מקור
    completeness (ENUM: 'full_document', 'excerpt', 'summary', 'unclear')
    reliability_indicators (JSON NULL)
    
    // סיכום וכשלונות
    short_summary (TEXT NULL)  // סיכום קצר בעברית
    failure_reason (TEXT NULL)  // סיבת כשלון בחילוץ
END TABLE
```

---

## זרימת עבודה מלאה

### 4.1 תהליך יצירת סיכום (Complete Flow)

```
USER ACTION: Click "צור תיאור" button
    ↓
FRONTEND: createSummary(recordId)
    ↓
    - Disable button
    - Show "יוצר..." text
    ↓
API CALL: POST api/create_summary.php
    ↓
BACKEND: create_summary.php
    ↓
    STEP 1: Validate input (recordId)
    STEP 2: Get record from database
    STEP 3: Fetch URL content (fetchWebpageContent)
        - Use cURL to fetch webpage
        - Handle PDFs, binary files, errors
        - Clean HTML, limit to 8000 chars
    STEP 4: Build existing metadata string
    STEP 5: Build GPT-4o prompt
        - Include URL
        - Include content excerpt (5000 chars)
        - Include existing metadata
        - Request Hebrew summary (2-3 sentences)
    STEP 6: Call OpenAI API
        - Model: gpt-4o
        - Temperature: 0.5
        - Max tokens: 500
    STEP 7: Parse response
        - Extract summary text
        - Remove markdown formatting
    STEP 8: Save to database
        UPDATE ranking_urls SET short_summary = ?
    STEP 9: Return success response
    ↓
FRONTEND: Receive response
    ↓
    IF success:
        - Refresh table (loadRecords)
        - Show success message
    ELSE:
        - Show error message
        - Re-enable button
    ↓
RESULT: Summary appears in table, button replaced with text
```

### 4.2 תהליך ניסיון חילוץ מחדש (Complete Flow)

```
USER ACTION: Click "🔄 נסה שוב" button (only for failed records)
    ↓
FRONTEND: retryExtraction(recordId)
    ↓
    - Confirm action
    - Disable button
    - Show loading state
    ↓
API CALL: POST api/retry_extraction.php
    ↓
BACKEND: retry_extraction.php
    ↓
    STEP 1: Get record from database
    STEP 2: Parse URL (domain, path)
    STEP 3: Fetch URL content
    STEP 4: Get existing content types for context
    STEP 5: Call extractMetadataWithOpenAI
        - Build comprehensive prompt
        - Call GPT-4o API
        - Parse JSON response
        - Validate required fields
    ↓
    IF SUCCESS:
        STEP 6A: Update all metadata fields
        STEP 7A: Set status = 'extracted'
        STEP 8A: Clear failure_reason
        STEP 9A: Return success
    ELSE:
        STEP 6B: Call explainFailure (GPT-4o)
            - Build diagnostic prompt
            - Get failure explanation in Hebrew
        STEP 7B: Update status = 'failed'
        STEP 8B: Save failure_reason
        STEP 9B: Return failure with explanation
    ↓
FRONTEND: Receive response
    ↓
    IF success:
        - Refresh table
        - Show success message
        - Status badge changes to "חולץ"
    ELSE:
        - Show error with failure_reason
        - Status badge remains "נכשל"
    ↓
RESULT: Record updated, status changed, failure reason displayed
```

---

## פונקציות עזר

### 5.1 fetchWebpageContent (URL Fetcher)

```
FUNCTION fetchWebpageContent(url):
    // שלב 1: הכנת cURL request
    ch = INIT cURL session
    
    SET cURL options:
        - URL: url
        - RETURNTRANSFER: true
        - FOLLOWLOCATION: true
        - MAXREDIRS: 5
        - TIMEOUT: 30 seconds
        - USERAGENT: Mozilla/5.0...
        - SSL_VERIFYPEER: false
        - HEADER: true (to get headers)
    
    // שלב 2: ביצוע request
    response = EXECUTE cURL request
    httpCode = GET HTTP status code
    headerSize = GET header size
    contentType = GET Content-Type header
    error = GET cURL error if any
    
    CLOSE cURL session
    
    // שלב 3: בדיקת שגיאות
    IF error:
        THROW Exception: "cURL error: " + error
    END IF
    
    IF httpCode != 200:
        THROW Exception: "HTTP error: " + httpCode
    END IF
    
    // שלב 4: הפרדת headers מתוכן
    headers = SUBSTRING(response, 0, headerSize)
    content = SUBSTRING(response, headerSize)
    
    // שלב 5: בדיקת סוג קובץ
    IF contentType contains "application/pdf" OR content starts with "%PDF":
        RETURN "[PDF File] - Cannot extract text..."
    END IF
    
    IF content is not UTF-8:
        RETURN "[Binary File] - Cannot extract text..."
    END IF
    
    // שלב 6: ניקוי HTML
    content = STRIP_TAGS(content)
    content = HTML_ENTITY_DECODE(content)
    content = REPLACE multiple spaces with single space
    content = TRIM(content)
    
    // שלב 7: הגבלת אורך
    IF LENGTH(content) > 8000:
        content = SUBSTRING(content, 0, 8000) + "..."
    END IF
    
    RETURN content
END FUNCTION
```

### 5.2 extractMetadataWithOpenAI

```
FUNCTION extractMetadataWithOpenAI(url, domain, path, content, existingContentTypes):
    // שלב 1: בניית prompt מקיף
    prompt = BUILD comprehensive prompt with:
        - URL information
        - Content excerpt (5000 chars)
        - Instructions for all metadata fields
        - Existing content types for consistency
        - JSON format requirements
    
    // שלב 2: קריאה ל-OpenAI API
    apiRequest = {
        model: "gpt-4o",
        messages: [
            { role: "system", content: "..." },
            { role: "user", content: prompt }
        ],
        temperature: 0.3,
        max_tokens: 1500
    }
    
    response = CALL OpenAI API
    
    // שלב 3: פרסור תשובה
    apiContent = EXTRACT content from response
    apiContent = REMOVE markdown code blocks
    metadata = PARSE JSON(apiContent)
    
    // שלב 4: ולידציה
    IF metadata.source_type is missing:
        THROW Exception: "Missing source_type"
    END IF
    
    // שלב 5: מילוי שדות חסרים ב-null
    requiredFields = [
        'source_type', 'year', 'organization_type', 
        'jurisdiction_level', 'geographic_scope',
        'topic_category', 'document_type', 'target_audience',
        'content_type', 'values_orientation', 'cultural_focus',
        'zionism_references', 'identity_theme', 'historical_periods',
        'language', 'accessibility_level', 'publication_format',
        'period_referenced', 'temporal_scope', 'completeness',
        'reliability_indicators'
    ]
    
    FOR EACH field IN requiredFields:
        IF field not in metadata:
            SET metadata[field] = null
        END IF
    END FOR
    
    RETURN metadata
END FUNCTION
```

### 5.3 explainFailure

```
FUNCTION explainFailure(url, content, errorMessage):
    // בניית prompt להסבר כשלון
    prompt = "הסבר בעברית מדוע נכשל חילוץ המטא-דאטה:\n\n" +
             "URL: " + url + "\n" +
             "שגיאה: " + errorMessage + "\n\n" +
             "תוכן שנשלף:\n" + SUBSTRING(content, 0, 2000) + "\n\n" +
             "הסבר את הסיבה לכשלון בצורה ברורה ומקצועית בעברית (2-3 משפטים)."
    
    // קריאה ל-GPT-4o
    apiRequest = {
        model: "gpt-4o",
        messages: [
            { role: "system", content: "..." },
            { role: "user", content: prompt }
        ],
        temperature: 0.5,
        max_tokens: 300
    }
    
    TRY:
        response = CALL OpenAI API
        explanation = EXTRACT content from response
        RETURN TRIM(explanation)
    CATCH error:
        RETURN "לא ניתן לחלץ מטא-דאטה מהקישור. שגיאה: " + errorMessage
    END TRY
END FUNCTION
```

---

## מצבי UI והתנהגות

### 6.1 מצבי כפתור "צור תיאור"

```
STATE: Initial (No Summary)
    - Button visible: YES
    - Button text: "צור תיאור"
    - Button enabled: YES
    - Button color: Green (#4CAF50)
    - On click: createSummary()

STATE: Loading (Creating Summary)
    - Button visible: YES
    - Button text: "יוצר..."
    - Button enabled: NO
    - Button color: Gray (disabled)
    - On click: (disabled)

STATE: Success (Summary Created)
    - Button visible: NO
    - Summary text visible: YES
    - Summary text: Truncated summary (50 chars)
    - On hover: Full summary in tooltip

STATE: Error (Failed to Create)
    - Button visible: YES
    - Button text: "צור תיאור"
    - Button enabled: YES
    - Error message: Shown to user
    - User can retry
```

### 6.2 מצבי כפתור "נסה שוב"

```
STATE: Visible Condition
    - Only shown when: metadata_status == 'failed'
    - Position: Actions column

STATE: Initial
    - Button visible: YES (if failed)
    - Button icon: 🔄
    - Button enabled: YES
    - On click: retryExtraction()

STATE: Loading
    - Button visible: YES
    - Button icon: ⏳
    - Button enabled: NO
    - On click: (disabled)

STATE: After Retry
    - If success: Button disappears, status changes to "חולץ"
    - If failed: Button remains, failure_reason displayed
```

---

## טיפול בשגיאות

### 7.1 שגיאות נפוצות וטיפול

```
ERROR: Network Error
    FRONTEND: Show "שגיאה בחיבור לשרת"
    ACTION: User can retry

ERROR: API Returns 404
    FRONTEND: Show "רשומה לא נמצאה"
    ACTION: Refresh page

ERROR: API Returns 500
    FRONTEND: Show error message from API
    BACKEND: Log full error details
    ACTION: Check server logs

ERROR: OpenAI API Error
    BACKEND: Catch exception
    FRONTEND: Show "שגיאה ביצירת סיכום: [error message]"
    ACTION: User can retry

ERROR: Database Column Missing
    BACKEND: ensureMetadataColumns() creates it automatically
    FRONTEND: No user impact

ERROR: Invalid JSON Response
    BACKEND: Return error with details
    FRONTEND: Show error, log to console
    ACTION: Check API response format
```

---

## אופטימיזציות וביצועים

### 8.1 אופטימיזציות

```
1. Caching:
    - No caching for now (always fresh data)
    - Future: Cache summaries for X hours

2. Rate Limiting:
    - OpenAI API: Built-in rate limits
    - Add delay between requests (500ms)

3. Batch Operations:
    - Future: Batch summary creation
    - Future: Batch retry for failed records

4. Lazy Loading:
    - Load summaries on demand
    - Pagination reduces initial load

5. Error Recovery:
    - Automatic retry with exponential backoff
    - Queue failed operations for later
```

---

## אבטחה

### 9.1 אמצעי אבטחה

```
1. Input Validation:
    - All user inputs validated
    - SQL injection prevention (prepared statements)
    - XSS prevention (HTML escaping)

2. API Security:
    - No authentication required (internal use)
    - Future: Add API keys

3. Database Security:
    - Prepared statements only
    - No direct SQL concatenation
    - Column existence checks

4. OpenAI API:
    - API key stored securely
    - Not exposed to frontend
    - Rate limiting respected
```

---

## מבנה קבצים

```
/
├── index.html                          # עמוד ראשי
├── assets/
│   ├── css/
│   │   └── style.css                   # עיצוב
│   └── js/
│       └── app.js                      # לוגיקה frontend
├── api/
│   ├── get_records.php                 # קבלת רשומות
│   ├── get_record.php                  # קבלת רשומה בודדת
│   ├── save_record.php                 # שמירת רשומה
│   ├── delete_record.php               # מחיקת רשומה
│   ├── create_summary.php              # יצירת סיכום
│   └── retry_extraction.php            # ניסיון חילוץ מחדש
├── config/
│   ├── database.php                    # הגדרות מסד נתונים
│   └── api_key.php                     # מפתח OpenAI API
├── lib/
│   └── url_fetcher.php                 # משיכת תוכן מ-URL
├── database/
│   ├── schema.sql                      # סכמת מסד נתונים
│   ├── migrate_add_metadata_columns.php
│   └── migrate_add_summary_columns.php
├── ensure_metadata_columns.php         # וידוא עמודות קיימות
└── extract_metadata.php               # חילוץ מטא-דאטה (CLI)
```

---

## סיכום

מערכת זו מספקת:
1. ניהול נתונים מלא עם CRUD operations
2. חילוץ מטא-דאטה אוטומטי עם GPT-4o
3. יצירת סיכומים בעברית
4. ניסיון חילוץ מחדש עם הסבר כשלונות
5. ממשק משתמש אינטואיטיבי ונוח

כל הפונקציונליות מתועדת ומתוכננת לעבודה חלקה ויעילה.

