# בקשות תיקונים ושיפורים למערכת

---

# סטטוס: כל התיקונים בוצעו ✅

---

## 1. תיקון "אכלס שמות גופים" ✅ מבוצע

**בעיה מקורית:**
- כפתור "אכלס שמות גופים" לא עובד
- שגיאה: `Uncaught TypeError: Cannot read properties of undefined (reading 'populateOrganizationNames')`

**מה נבדק ותוקן:**
- הפונקציה `populateOrganizationNames` מיוצאת נכון ב-`app.js` (שורה 703)
- `window.App` מוגדר בשורה 690
- `window.app = window.App` מוגדר כ-alias בשורה 710
- הכפתור ב-HTML קורא ל-`window.App.populateOrganizationNames()` (שורה 25 ב-index.html)
- הסקריפטים נטענים בסוף ה-body, אז הם זמינים לפני שהמשתמש יכול ללחוץ

**מיקום:**
- `public/assets/js/app.js` - שורות 614-647 (הפונקציה), 703, 710, 724 (exports)
- `public/index.html` - שורה 25 (הכפתור)

---

## 2. עריכת רלוונטיות inline ✅ מבוצע

**בקשה מקורית:**
- הוסף אפשרות לערוך "רלוונטיות" ישירות מהטבלה

**מה מומש:**
- תא editable עם class `editable-relevance` ב-`table.js` (שורות 207-221)
- Select dropdown עם אפשרויות 1-5
- צבע משתנה לפי הערך (getRelevanceColor)
- פונקציות ב-`inline-edit.js`:
  - `findRelevanceCell()` (שורות 101-114)
  - `activateRelevanceEdit()` (שורות 185-204)
  - `saveRelevanceInline()` (שורות 322-400)
- `closeAllEdits()` מעודכן לכלול relevance (שורות 234-244)
- `shouldIgnoreClick()` מעודכן לכלול `relevance-edit` (שורה 121)

**מיקום:**
- `public/assets/js/table.js` - שורות 207-221
- `public/assets/js/inline-edit.js` - שורות 49-56, 101-114, 121, 185-204, 234-244, 322-400

---

## 3. אפשרות טקסט חופשי לקטגוריה וסוג ארגון ✅ מבוצע

**בקשה מקורית:**
- אפשרות להזין טקסט חופשי, לא רק מתוך רשימה סגורה

**מה מומש:**
- שונה מ-`<select>` ל-`<input type="text" list="...">` עם `<datalist>`
- לסוג ארגון: `table.js` שורות 175-187
- לקטגוריה: `table.js` שורות 191-204
- שמירה על Enter או blur
- הצגת label אם קיים, אחרת הערך עצמו
- `inline-edit.js` מעודכן לטפל ב-input (שימוש ב-onblur ו-onkeypress)

**מיקום:**
- `public/assets/js/table.js` - שורות 173-204
- `public/assets/js/inline-edit.js` - `saveCategoryInline` ו-`saveOrganizationTypeInline`

---

## 4. שיפור עיצוב הטבלה ✅ מבוצע

**בקשה מקורית:**
- הקטנת רוחב תיאור, הגדלת רוחב URL, כפתור עריכה בתיאור

**מה מומש:**
- תא תיאור: `min-width: 400px; max-width: 500px;` (CSS שורה 209-211, table.js שורה 229)
- תא URL: `max-width: 150px; min-width: 120px; width: 150px;` (CSS שורות 189-192)
- כפתור "✏️ עריכה" בתוך תא התיאור שקורא ל-`window.Modals.openRecordModal(id)` (table.js שורות 248-250)

**מיקום:**
- `public/assets/css/main.css` - שורות 189-192 (url-cell), 209-216 (summary-cell)
- `public/assets/js/table.js` - שורה 229 (inline style), שורות 248-250 (כפתור)

---

## סיכום מבנה הפרויקט

המערכת היא אפליקציית PHP/JavaScript לניהול רשומות URL עם:
- **Frontend**: HTML + CSS + JavaScript מודולרי
- **Backend**: PHP API endpoints
- **Database**: MySQL עם טבלת `ranking_urls`

**קבצי JavaScript ראשיים:**
1. `api.js` - תקשורת עם השרת
2. `table.js` - הצגת טבלה ועיבוד נתונים
3. `inline-edit.js` - עריכה ישירה בטבלה
4. `modals.js` - חלונות קופצים
5. `app.js` - לוגיקה ראשית ואתחול

**API Endpoints:**
- `api/records.php` - CRUD לרשומות
- `api/summaries.php` - ניהול סיכומים
- `api/create_summary.php` - יצירת סיכום AI
- `api/ai/relevance.php` - דירוג רלוונטיות AI
- `api/populate_organization_names.php` - אכלוס שמות גופים
- `api/retry_extraction.php` - ניסיון חילוץ מחדש

---

---

# בעיות חדשות - 2026-01-09

## P1: סוג ארגון עדיין מציג dropdown ✅ תוקן
- שונה ל-input טקסט חופשי בלי datalist
- מיקום: `table.js` שורות 173-180

## P2: שמירה לא עובדת ✅ תוקן
- תוקן הקריאה לפונקציות: `window.InlineEdit.saveOrganizationTypeInline`
- שונה מ-onkeypress ל-onkeydown עם preventDefault
- מיקום: `table.js` ו-`inline-edit.js`

## P3: כפתור עריכה לא מספיק בולט ✅ תוקן
- הוסף כפתור "✏️ עריכה מלאה" בולט עם gradient סגול
- פותח מסך עריכה מלא עם כל הפרמטרים
- מיקום: `table.js` שורות 228-230

## P4: עריכת רלוונטיות עם מספרים 1-5 ✅ תוקן
- שונה מ-select dropdown ל-input type="number" עם min=1 max=5
- מיקום: `table.js` שורות 197-201, `inline-edit.js` שורות 374-387

## P5: יותר מקום ל-URL ✅ תוקן
- הוגדל רוחב מ-150px ל-250px
- URL מוצג עד 50 תווים במקום רק הדומיין
- מיקום: `main.css` שורות 189-205, `table.js` שורות 167-171

