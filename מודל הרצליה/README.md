# מערכת מיפוי קשרים - Knowledge Graph System

מערכת למיפוי קשרים בין ארגונים, אנשים, תוכניות, מושגים, רעיונות, אירועים וכתבות.

## תכונות עיקריות

- **ניהול צמתים (Nodes)**: ארגונים, אנשים, תוכניות, מושגים, רעיונות, אירועים, כתבות
- **ניהול קשרים (Edges)**: קשרים בין צמתים עם סוגי קשרים שונים
- **ראיות (Evidence)**: כל קשר חייב להיות מגובה בראיות (קישורים, ציטוטים, קבצים)
- **גרסאות (Versioning)**: שמירת גרסאות קודמות של כל תוכן
- **Audit Log**: מעקב אחר כל הפעולות (מי עשה מה ומתי)
- **ויזואליזציה**: גרף אינטראקטיבי עם Cytoscape.js
- **חיפוש ומסלולים**: חיפוש צמתים ומציאת מסלולים בין צמתים

## התקנה

### 1. יצירת טבלאות DB

הרץ את `setup.php` או `db/schema.php` פעם אחת:

```
http://localhost/setup.php
```

זה ייצור:
- טבלת `users` עם משתמש admin ראשוני
- כל הטבלאות הנדרשות (nodes, edges, evidence, audit_log, node_versions, edge_versions)

### 2. פרטי כניסה

**מייל**: `admin@gnostocracy.com`  
**סיסמה**: `gnostocracy7654`

### 3. מבנה קבצים

```
/
├── config.php              # הגדרות DB
├── setup.php               # התקנה ראשונית
├── db/
│   ├── connection.php      # חיבור DB
│   └── schema.php          # יצירת טבלאות
├── auth/
│   ├── login.php           # כניסה
│   ├── logout.php          # יציאה
│   └── check.php           # בדיקת הרשאות
├── models/
│   ├── Node.php            # מודל צמתים
│   ├── Edge.php           # מודל קשרים
│   └── Evidence.php       # מודל ראיות
├── api/
│   ├── nodes.php          # API צמתים
│   ├── edges.php          # API קשרים
│   ├── graph.php          # API גרף (ל-Cytoscape)
│   └── evidence.php       # API ראיות
├── pages/
│   ├── index.php          # דף ראשי - ויזואליזציה
│   ├── add_node.php       # הוספת צומת
│   ├── add_edge.php       # הוספת קשר
│   ├── view_node.php      # צפייה בפרטי צומת
│   └── search.php         # חיפוש ומסלולים
├── includes/
│   ├── header.php         # HTML header
│   └── footer.php         # HTML footer
└── assets/
    ├── style.css          # עיצוב
    └── main.js            # JavaScript כללי
```

## שימוש

### הוספת צומת חדש

1. לך ל-"הוסף צומת"
2. בחר סוג (ארגון, אדם, מושג, וכו')
3. הזן שם ותיאור
4. סמן תיוגים (בעייתי, מחשיד, וכו')
5. שמור

### הוספת קשר

1. לך ל-"הוסף קשר"
2. בחר מצומת ולצומת
3. בחר סוג קשר (PROMOTES, FUNDED_BY, וכו')
4. **חובה**: הוסף ראיה (קישור, ציטוט, וכו')
5. שמור

### סוגי צמתים

- `org` - ארגון
- `person` - אדם
- `program` - תוכנית/פרויקט
- `term` - מושג/מונח
- `concept` - רעיון/אידאולוגיה
- `doc` - מסמך
- `funding` - תקציב/מימון
- `event` - אירוע/מקרה
- `article` - כתבה/דיווח

### סוגי קשרים

- `FUNDED_BY` - מימן על ידי
- `PARTNERED_WITH` - שותפות עם
- `EMPLOYED_AT` - מועסק ב
- `PROMOTES` - מקדם
- `USES_TERM` - משתמש במושג
- `ADVOCATES` - תומך/מקדם
- `DEFINES` - מגדיר
- `INFLUENCED_BY` - הושפע מ
- `QUOTED` - ציטט
- `CONTAINS_TERM` - מכיל מושג
- `REPORTS_ON` - מדווח על
- `RESPONDED_TO` - הגיב ל
- `INVOLVED_IN` - מעורב ב
- `OCCURRED_AT` - קרה ב

## דוגמאות שימוש

### דוגמה: מורה קידם רעיונות בעייתיים

1. צור צומת `person`: "מורה X"
2. צור צומת `concept`: "רעיון בעייתי Y" עם flag `problematic`
3. צור צומת `event`: "מורה X קידם רעיונות בכיתה"
4. צור קשר: `מורה X --[PROMOTED]--> רעיון בעייתי Y`
5. צור קשר: `מורה X --[INVOLVED_IN]--> אירוע`
6. צור קשר: `רעיון בעייתי Y --[RELATED_TO]--> אירוע`
7. צור צומת `article`: "כותרת הכתבה"
8. צור קשר: `כתבה --[REPORTS_ON]--> אירוע`
9. צור צומת `org`: "עיריית X"
10. צור קשר: `עיריית X --[RESPONDED_TO]--> אירוע`

## אבטחה

- כל הדפים דורשים כניסה (חוץ מ-login)
- כל הפעולות נרשמות ב-audit log
- כל השינויים נשמרים בגרסאות
- Prepared statements למניעת SQL injection

## תחזוקה

### צפייה ב-audit log

```sql
SELECT * FROM audit_log ORDER BY created_at DESC LIMIT 100;
```

### צפייה בגרסאות

כל צומת וקשר שומרים גרסאות קודמות ב-`node_versions` ו-`edge_versions`.

## תמיכה

לשאלות או בעיות, בדוק את:
- `db/schema.php` - מבנה הטבלאות
- `models/` - לוגיקת המודלים
- `api/` - ה-APIs

