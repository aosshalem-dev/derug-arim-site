/**
 * research_orgs.js — Deep research profiles for 7 investigated organizations
 * Generated from deep_research_*.md files (2026-02-16)
 * Cross-referenced with org_city_chalatz_mapping.json (1820 orgs × 20 cities)
 */
const RESEARCH_ORGS = {
  "orgs": {
    "ACRI": {
      "name_he": "האגודה לזכויות האזרח בישראל",
      "name_en": "Association for Civil Rights in Israel (ACRI)",
      "reg": "580011567",
      "revenue_nis": 10900000,
      "gov_pct": 0.2,
      "staff": 36,
      "neutrality": 2,
      "transparency": 3.5,
      "ideology_detected": true,
      "cities": ["רמת גן", "רמת השרון"],
      "school_count": 2,
      "program_budget": 8629,
      "thesis": "ארגון זכויות אדם שעבר מסנגוריה ליברלית קלאסית לאימוץ מסגרת 'אפרטהייד', עם מימון מ-NIF, EU ו-Ford Foundation.",
      "key_findings": [
        "אימוץ מסגרת 'אפרטהייד' מ-2008 — שינוי מהותי מזכויות ליברליות",
        "תקציב 10.9M ₪, מימון ממשלתי אפסי (0.2%)",
        "שותפות עם NIF, EU, Ford Foundation",
        "36 עובדים — צוות משפטי, מחקר וסנגוריה"
      ],
      "risk": "high",
      "funding_sources": "NIF, EU, Ford Foundation, Naomi & Nehemia Cohen Foundation"
    },
    "Hartman": {
      "name_he": "מכון שלום הרטמן",
      "name_en": "Shalom Hartman Institute",
      "reg": "511021156",
      "revenue_nis": 98400000,
      "gov_pct": 16,
      "staff": 282,
      "neutrality": 3,
      "transparency": 4,
      "ideology_detected": true,
      "cities": ["באר שבע", "חיפה", "ירושלים", "כרמיאל", "רמת גן", "רמת השרון", "תל אביב-יפו"],
      "school_count": 23,
      "program_budget": 182354,
      "thesis": "מכון מחקר ענק (~$42M) שמפעיל תוכנית 'לב אהרון' בצה\"ל. הפרדוקס של CLAWS — פלורליזם שיטתי שהופך לאידיאולוגיה.",
      "key_findings": [
        "תקציב משולב ~$42M (ישראל + ארה\"ב)",
        "תוכנית 'לב אהרון' — הכשרת מפקדים בצה\"ל",
        "282 עובדים, פרופסורים ומחנכים",
        "פרדוקס CLAWS: פלורליזם שהופך לערך עליון",
        "נוכחות ב-7 ערים במיפוי גפן"
      ],
      "risk": "medium",
      "funding_sources": "Jim Joseph Foundation, AVI CHAI, ממשלת ישראל (16%)"
    },
    "Matzmichim": {
      "name_he": "מצמיחים - המרכז להפחתת אלימות בבתי הספר",
      "name_en": "Matzmichim - Academy for Reducing Violence in Schools",
      "reg": "580419521",
      "revenue_nis": 12800000,
      "gov_pct": 0.8,
      "staff": 30,
      "neutrality": 5,
      "transparency": 3,
      "ideology_detected": false,
      "cities": ["באר שבע", "גבעת שמואל", "גבעתיים", "הוד השרון", "הרצליה", "חיפה", "כרמיאל", "נתיבות", "קרית אונו", "קרית גת", "קרית שמונה", "ראש העין", "רמת גן", "רמת השרון", "רעננה", "תל אביב-יפו"],
      "school_count": 85,
      "program_budget": 1832872,
      "thesis": "ארגון התנהגותי נקי — מתמקד בהפחתת אלימות בבתי ספר ללא סממנים אידיאולוגיים. פריסה רחבה ב-16 ערים.",
      "key_findings": [
        "אין אידיאולוגיה מזוהה — פוקוס התנהגותי טהור",
        "פריסה ב-16 מ-20 ערים — הארגון הנפוץ ביותר",
        "85 בתי ספר, תקציב תוכניות 1.8M ₪",
        "תקציב כולל 12.8M ₪, מימון ממשלתי מזערי (0.8%)"
      ],
      "risk": "low",
      "funding_sources": "קרנות פרטיות, שכר לימוד מבתי ספר"
    },
    "Tikkun": {
      "name_he": "תיקון - מרכז למפגש, חינוך ושינוי חברתי",
      "name_en": "Tikkun - Center for Encounter, Education and Social Change",
      "reg": "580334779",
      "revenue_nis": 5340000,
      "gov_pct": 49,
      "staff": 51,
      "neutrality": 2,
      "transparency": 3,
      "ideology_detected": true,
      "cities": [],
      "school_count": 0,
      "program_budget": 0,
      "thesis": "זרוע בוגרים של המחנות העולים — תנועת נוער מפ\"ם לשעבר. מימון ממשלתי גבוה (49%) חרף ביקורות.",
      "key_findings": [
        "זרוע בוגרים של 'המחנות העולים' (תנועת נוער מפ\"ם)",
        "49% מימון ממשלתי — תלות גבוהה בתקציב ציבורי",
        "51 עובדים, מיקוד ב'מפגש' בין-תרבותי",
        "לא נמצא במיפוי גפן — פעילות מחוץ לקטלוג"
      ],
      "risk": "medium-high",
      "funding_sources": "ממשלת ישראל (49%), NIF, קרנות שמאל"
    },
    "Democratic_Institute": {
      "name_he": "המכון הדמוקרטי - חברה וחינוך",
      "name_en": "The Democratic Institute - Society and Education",
      "reg": "580330660",
      "revenue_nis": 10600000,
      "gov_pct": 44,
      "staff": 69,
      "neutrality": 4,
      "transparency": 3,
      "ideology_detected": true,
      "cities": ["באר שבע", "ירושלים", "רעננה"],
      "school_count": 17,
      "program_budget": 944960,
      "thesis": "המכון של חוה הכט — מקדם 'דמוקרטיה' כאידיאולוגיה חינוכית. מימון ממשלתי 44% מעיד על שילוב מוסדי עמוק.",
      "key_findings": [
        "תנועת חוה הכט — 'דמוקרטיה' כערך עליון בחינוך",
        "44% מימון ממשלתי, 69 עובדים",
        "17 בתי ספר, תקציב תוכניות ~945K ₪",
        "פועל ב-3 ערים: באר שבע, ירושלים, רעננה"
      ],
      "risk": "medium",
      "funding_sources": "ממשלת ישראל (44%), קרנות חינוך"
    },
    "Yedidut_Toronto": {
      "name_he": "ידידות טורונטו",
      "name_en": "Yedidut Toronto (Keren Yedidut Toronto)",
      "reg": "580496123",
      "revenue_nis": 20900000,
      "gov_pct": 4,
      "staff": 9,
      "neutrality": 4,
      "transparency": 2,
      "ideology_detected": false,
      "cities": ["באר שבע", "חיפה", "ירושלים", "קרית גת"],
      "school_count": 23,
      "program_budget": 669165,
      "thesis": "קרן של תורם יחיד (אלברט פרידברג) — סיכון 'צוק תורמים'. שקיפות נמוכה, תקציב 20.9M ₪ מריכוז אחד.",
      "key_findings": [
        "תורם יחיד: אלברט פרידברג — סיכון צוק תורמים",
        "שקיפות 2/5 — מידע ציבורי מינימלי",
        "תקציב 20.9M ₪, 9 עובדים בלבד",
        "23 בתי ספר ב-4 ערים"
      ],
      "risk": "medium",
      "funding_sources": "Albert Friedberg (תורם יחיד), ממשלת ישראל (4%)"
    },
    "Havatzelet": {
      "name_he": "חבצלת מוסדות תרבות וחנוך של השומר הצעיר",
      "name_en": "Havatzelet - HaShomer HaTzair Cultural and Educational Institutions",
      "reg": "510490451",
      "revenue_nis": 49400000,
      "gov_pct": 67,
      "staff": 126,
      "neutrality": 1,
      "transparency": 2,
      "ideology_detected": true,
      "cities": ["רמת השרון"],
      "school_count": 1,
      "program_budget": 4900,
      "thesis": "האקוסיסטם הגדול ביותר — ~110M ₪ כולל ישויות קשורות. השומר הצעיר: 67% מימון ממשלתי לתנועה אידיאולוגית מובהקת.",
      "key_findings": [
        "האקוסיסטם הגדול ביותר: ~110M ₪ עם ישויות קשורות",
        "67% מימון ממשלתי — הגבוה מכל 7 הארגונים",
        "126 עובדים, תנועת השומר הצעיר",
        "שקיפות 2/5, ניטרליות 1/5 — הארגון הכי אידיאולוגי",
        "בית ספר אחד בלבד במיפוי גפן (רמת השרון)"
      ],
      "risk": "high",
      "funding_sources": "ממשלת ישראל (67%), הקיבוץ הארצי"
    }
  },

  /** City → org keys mapping for quick lookup in detail panels */
  "city_orgs": {
    "באר שבע": ["Hartman", "Democratic_Institute", "Yedidut_Toronto", "Matzmichim"],
    "גבעת שמואל": ["Matzmichim"],
    "גבעתיים": ["Matzmichim"],
    "הוד השרון": ["Matzmichim"],
    "הרצליה": ["Matzmichim"],
    "חיפה": ["Hartman", "Yedidut_Toronto", "Matzmichim"],
    "ירושלים": ["Hartman", "Democratic_Institute", "Yedidut_Toronto"],
    "כרמיאל": ["Hartman", "Matzmichim"],
    "נתיבות": ["Matzmichim"],
    "קרית אונו": ["Matzmichim"],
    "קרית גת": ["Yedidut_Toronto", "Matzmichim"],
    "קרית שמונה": ["Matzmichim"],
    "ראש העין": ["Matzmichim"],
    "רמת גן": ["ACRI", "Hartman", "Matzmichim"],
    "רמת השרון": ["ACRI", "Hartman", "Havatzelet", "Matzmichim"],
    "רעננה": ["Democratic_Institute", "Matzmichim"],
    "תל אביב-יפו": ["Hartman", "Matzmichim"]
  }
};
