# API Endpoints - כל נקודות הקצה של ה-API

## בסיס URL
כל הנתיבים מתחילים מ: `api/`

---

## 1. Nodes API (`api/nodes.php`)

### GET - קבלת צמתים
- `GET api/nodes.php?id={node_id}` - קבלת צומת ספציפי לפי ID
- `GET api/nodes.php?type={type}` - קבלת כל הצמתים לפי סוג (org, person, term, event, document)
- `GET api/nodes.php?search={query}&type={type}&flags={json_array}` - חיפוש צמתים
  - `query` - מילת חיפוש (חיפוש ב-label ו-description)
  - `type` - סוג (אופציונלי)
  - `flags` - מערך JSON של flags (אופציונלי)
- `GET api/nodes.php` - קבלת כל הצמתים

### POST - יצירת צומת חדש
- `POST api/nodes.php`
  ```json
  {
    "type": "org|person|term|event|document",
    "label": "שם הצומת",
    "description": "תיאור (אופציונלי)",
    "flags": ["flag1", "flag2"],
    "props": {"key": "value"},
    "canonical_key": "unique_key (אופציונלי)"
  }
  ```

### PUT - עדכון צומת
- `PUT api/nodes.php?id={node_id}`
  ```json
  {
    "type": "org",
    "label": "שם מעודכן",
    "description": "תיאור מעודכן",
    "flags": ["updated_flags"],
    "props": {"updated": "data"},
    "canonical_key": "updated_key"
  }
  ```

### DELETE - מחיקת צומת
- `DELETE api/nodes.php?id={node_id}`

---

## 2. Edges API (`api/edges.php`)

### GET - קבלת קשרים
- `GET api/edges.php?id={edge_id}` - קבלת קשר ספציפי לפי ID
- `GET api/edges.php?node_id={node_id}` - קבלת כל הקשרים של צומת מסוים
- `GET api/edges.php?path={from_id},{to_id}` - מציאת מסלול בין שני צמתים
  - מחזיר מערך של edge IDs שמרכיבים את המסלול
  - עומק מקסימלי: 5 קפיצות (ברירת מחדל)

### POST - יצירת קשר חדש
- `POST api/edges.php`
  ```json
  {
    "from_node_id": 1,
    "to_node_id": 2,
    "rel_type": "PROMOTES|FUNDED_BY|USES_TERM|RELATED_TO|...",
    "confidence": "high|medium|low",
    "start_date": "2023-01-01 (אופציונלי)",
    "end_date": "2023-12-31 (אופציונלי)",
    "props": {"additional": "data"}
  }
  ```

### PUT - עדכון קשר
- `PUT api/edges.php?id={edge_id}`
  ```json
  {
    "from_node_id": 1,
    "to_node_id": 2,
    "rel_type": "UPDATED_TYPE",
    "confidence": "high",
    "start_date": "2023-01-01",
    "end_date": null,
    "props": {"updated": "data"}
  }
  ```

### DELETE - מחיקת קשר
- `DELETE api/edges.php?id={edge_id}`

---

## 3. Graph API (`api/graph.php`)

### GET - קבלת נתוני גרף (Cytoscape.js format)
- `GET api/graph.php` - קבלת כל הצמתים והקשרים
- `GET api/graph.php?node_id={node_id}&depth={depth}` - קבלת צומת מרכזי וכל הקשורים אליו
  - `node_id` - ID של הצומת המרכזי
  - `depth` - עומק החיפוש (ברירת מחדל: 2)
- `GET api/graph.php?type={type}` - קבלת כל הצמתים והקשרים לפי סוג צומת

**תגובה (JSON):**
```json
{
  "nodes": [
    {
      "data": {
        "id": "1",
        "label": "שם הצומת",
        "type": "org",
        "flags": ["problematic"]
      },
      "style": {
        "background-color": "#d00"
      }
    }
  ],
  "edges": [
    {
      "data": {
        "id": "1",
        "source": "1",
        "target": "2",
        "label": "PROMOTES",
        "rel_type": "PROMOTES",
        "confidence": "medium"
      }
    }
  ]
}
```

---

## 4. Evidence API (`api/evidence.php`)

### GET - קבלת ראיות
- `GET api/evidence.php?edge_id={edge_id}` - קבלת כל הראיות של קשר מסוים
- `GET api/evidence.php?id={evidence_id}` - קבלת ראיה ספציפית לפי ID

### POST - הוספת ראיה לקשר
- `POST api/evidence.php`
  ```json
  {
    "edge_id": 1,
    "source_type": "url|pdf|document|quote",
    "source_ref": "https://example.com או שם מסמך",
    "quote_snippet": "ציטוט רלוונטי (אופציונלי)",
    "page": "עמוד במסמך (אופציונלי)",
    "line_range": "שורות 10-15 (אופציונלי)"
  }
  ```

### DELETE - מחיקת ראיה
- `DELETE api/evidence.php?id={evidence_id}`

---

## אימות (Authentication)

כל נקודות הקצה דורשות אימות דרך PHP Session:
1. יש להתחבר דרך `auth/login.php`
2. הסיסמה: `gnostocracy7654`
3. כל מייל מתקבל (המערכת יוצרת משתמש אוטומטית אם לא קיים)
4. לאחר התחברות, ה-session נשמר ומאפשר גישה ל-API

**בקשות לא מאומתות יקבלו:**
```json
{
  "error": "Unauthorized",
  "redirect": "../auth/login.php"
}
```
עם קוד סטטוס 401.

---

## דוגמאות שימוש

### דוגמה 1: יצירת צומת חדש
```javascript
fetch('api/nodes.php', {
  method: 'POST',
  headers: {
    'Content-Type': 'application/json'
  },
  body: JSON.stringify({
    type: 'org',
    label: 'יד הנדיב',
    description: 'קרן פילנתרופית',
    flags: ['problematic']
  })
})
.then(r => r.json())
.then(data => console.log('Created:', data));
```

### דוגמה 2: חיפוש צמתים
```javascript
fetch('api/nodes.php?search=חוסן&type=term')
  .then(r => r.json())
  .then(nodes => console.log('Found:', nodes));
```

### דוגמה 3: יצירת קשר עם ראיה
```javascript
// ראשית, יצירת הקשר
fetch('api/edges.php', {
  method: 'POST',
  headers: {'Content-Type': 'application/json'},
  body: JSON.stringify({
    from_node_id: 1,
    to_node_id: 2,
    rel_type: 'PROMOTES',
    confidence: 'high'
  })
})
.then(r => r.json())
.then(data => {
  // כעת הוספת ראיה
  return fetch('api/evidence.php', {
    method: 'POST',
    headers: {'Content-Type': 'application/json'},
    body: JSON.stringify({
      edge_id: data.id,
      source_type: 'url',
      source_ref: 'https://example.com/article',
      quote_snippet: 'הקרן מקדמת את המושג...'
    })
  });
})
.then(r => r.json())
.then(data => console.log('Evidence added:', data));
```

---

## סוגי קשרים (Relationship Types)
- `PROMOTES` - מקדם
- `FUNDED_BY` - ממומן על ידי
- `FUNDS` - מממן
- `USES_TERM` - משתמש במושג
- `RELATED_TO` - קשור ל
- `INVOLVED_IN` - מעורב ב
- `REPORTS_ON` - מדווח על
- `RESPONDED_TO` - הגיב ל
- `PROMOTED` - קודם על ידי
- ועוד...

## Flags נפוצים
- `problematic` - בעייתי/מחשיד (צבע אדום)
- `suspicious` - מחשיד (צבע כתום)
- `key_player` - שחקן מפתח
- `academic` - אקדמי
- `ideology` - אידאולוגי

