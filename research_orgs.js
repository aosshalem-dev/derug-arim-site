/**
 * research_orgs.js — Deep research profiles for 31 investigated organizations
 * Generated from deep_research_*.md files (2026-02-16/17)
 * Cross-referenced with org_city_chalatz_mapping.json (1820 orgs × 20 cities)
 * Batch 1: 7 orgs | Batch 2: 7 orgs | Batch 3: 15 orgs | Schools: 2 (Leyada, Kedma)
 *
 * Source data: https://github.com/aosshalem-dev/derug-arim-site
 * Methodology: Multi-agent deep research (4-6 parallel agents per org)
 * Data sources: GuideStar IL, obudget.org, news archives, GuideStar US, Charity Navigator
 */
const RESEARCH_ORGS = {
  "methodology": {
    "description": "כל ארגון נחקר על ידי 4-6 סוכני מחקר מקבילים, כל אחד מתמחה בתחום שונה (מבנה ארגוני, פיננסים, הנהגה, אידיאולוגיה, קשרי ממשל, מחלוקות). הממצאים אומתו ממקורות ציבוריים.",
    "data_sources": [
      {"name": "GuideStar Israel", "url": "https://www.guidestar.org.il", "use": "רישום, דוחות כספיים, דירקטורים"},
      {"name": "obudget.org", "url": "https://next.obudget.org", "use": "העברות ממשלתיות, תקציבים"},
      {"name": "GuideStar US", "url": "https://www.guidestar.org", "use": "ישויות 501(c)(3), דוחות 990"},
      {"name": "Charity Navigator", "url": "https://www.charitynavigator.org", "use": "דירוגי שקיפות אמריקאיים"},
      {"name": "Gefen Catalog", "url": "https://apps.education.gov.il/gefen", "use": "תוכניות העשרה בבתי ספר"},
      {"name": "News Archives", "url": null, "use": "TheMarker, Calcalist, Globes, Haaretz, Ynet, Israel Hayom"}
    ],
    "scoring": {
      "neutrality": "1 = אידיאולוגי מובהק, 5 = ניטרלי לחלוטין",
      "transparency": "1 = אטום, 5 = שקוף לחלוטין",
      "risk": "low / low-moderate / moderate / medium / medium-high / high"
    }
  },

  "orgs": {
    "ACRI": {
      "name_he": "האגודה לזכויות האזרח בישראל",
      "name_en": "Association for Civil Rights in Israel (ACRI)",
      "reg": "580011567",
      "entity_type": "עמותה",
      "founded": 1972,
      "revenue_nis": 10900000,
      "gov_pct": 0.2,
      "staff": 36,
      "neutrality": 2,
      "transparency": 3.5,
      "ideology_detected": true,
      "cities": ["רמת גן", "רמת השרון"],
      "school_count": 2,
      "program_budget": 8629,
      "thesis": "ארגון זכויות אדם שעבר מסנגוריה קלאסית לאימוץ מסגרת 'אפרטהייד', עם מימון מ-NIF, EU ו-Ford Foundation.",
      "key_findings": [
        "אימוץ מסגרת 'אפרטהייד' מ-2008 — שינוי מהותי מזכויות אדם קלאסיות",
        "תקציב 10.9M ₪, מימון ממשלתי אפסי (0.2%)",
        "שותפות עם NIF, EU, Ford Foundation",
        "36 עובדים — צוות משפטי, מחקר וסנגוריה"
      ],
      "risk": "high",
      "funding_sources": "NIF, EU, Ford Foundation, Naomi & Nehemia Cohen Foundation",
      "report_url": "research/deep_ACRI.html",
      "source_links": [
        {"label": "GuideStar IL", "url": "https://www.guidestar.org.il/organization/580011567"},
        {"label": "obudget.org", "url": "https://next.obudget.org/i/associations/association/580011567"},
        {"label": "אתר הארגון", "url": "https://www.acri.org.il"}
      ]
    },
    "Hartman": {
      "name_he": "מכון שלום הרטמן",
      "name_en": "Shalom Hartman Institute",
      "reg": "511021156",
      "entity_type": "חל\"צ",
      "founded": 1976,
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
      "funding_sources": "Jim Joseph Foundation, AVI CHAI, ממשלת ישראל (16%)",
      "report_url": "research/deep_Hartman.html",
      "source_links": [
        {"label": "GuideStar IL", "url": "https://www.guidestar.org.il/organization/511021156"},
        {"label": "obudget.org", "url": "https://next.obudget.org/i/associations/association/511021156"},
        {"label": "אתר הארגון", "url": "https://hartman.org.il"},
        {"label": "GuideStar US (SHI)", "url": "https://www.guidestar.org/profile/52-1313555"}
      ]
    },
    "Matzmichim": {
      "name_he": "מצמיחים - המרכז להפחתת אלימות בבתי הספר",
      "name_en": "Matzmichim - Academy for Reducing Violence in Schools",
      "reg": "580419521",
      "entity_type": "עמותה",
      "founded": 2001,
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
      "funding_sources": "קרנות פרטיות, שכר לימוד מבתי ספר",
      "report_url": "research/deep_Matzmichim.html",
      "source_links": [
        {"label": "GuideStar IL", "url": "https://www.guidestar.org.il/organization/580419521"},
        {"label": "obudget.org", "url": "https://next.obudget.org/i/associations/association/580419521"}
      ]
    },
    "Tikkun": {
      "name_he": "תיקון - מרכז למפגש, חינוך ושינוי חברתי",
      "name_en": "Tikkun - Center for Encounter, Education and Social Change",
      "reg": "580334779",
      "entity_type": "עמותה",
      "founded": 2003,
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
      "funding_sources": "ממשלת ישראל (49%), NIF, קרנות פרוגרסיביות",
      "report_url": "research/deep_Tikkun.html",
      "source_links": [
        {"label": "GuideStar IL", "url": "https://www.guidestar.org.il/organization/580334779"},
        {"label": "obudget.org", "url": "https://next.obudget.org/i/associations/association/580334779"}
      ]
    },
    "Democratic_Institute": {
      "name_he": "המכון הדמוקרטי - חברה וחינוך",
      "name_en": "The Democratic Institute - Society and Education",
      "reg": "580330660",
      "entity_type": "עמותה",
      "founded": 1998,
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
      "funding_sources": "ממשלת ישראל (44%), קרנות חינוך",
      "report_url": "research/deep_Democratic_Institute.html",
      "source_links": [
        {"label": "GuideStar IL", "url": "https://www.guidestar.org.il/organization/580330660"},
        {"label": "obudget.org", "url": "https://next.obudget.org/i/associations/association/580330660"}
      ]
    },
    "Yedidut_Toronto": {
      "name_he": "ידידות טורונטו",
      "name_en": "Yedidut Toronto (Keren Yedidut Toronto)",
      "reg": "580496123",
      "entity_type": "עמותה",
      "founded": 2003,
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
      "funding_sources": "Albert Friedberg (תורם יחיד), ממשלת ישראל (4%)",
      "report_url": "research/deep_Yedidut_Toronto.html",
      "source_links": [
        {"label": "GuideStar IL", "url": "https://www.guidestar.org.il/organization/580496123"},
        {"label": "obudget.org", "url": "https://next.obudget.org/i/associations/association/580496123"}
      ]
    },
    "Havatzelet": {
      "name_he": "חבצלת מוסדות תרבות וחנוך של השומר הצעיר",
      "name_en": "Havatzelet - HaShomer HaTzair Cultural and Educational Institutions",
      "reg": "510490451",
      "entity_type": "חל\"צ",
      "founded": 2013,
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
        "67% מימון ממשלתי — הגבוה מכל הארגונים הנחקרים",
        "126 עובדים, תנועת השומר הצעיר",
        "שקיפות 2/5, ניטרליות 1/5 — הארגון הכי אידיאולוגי",
        "בית ספר אחד בלבד במיפוי גפן (רמת השרון)"
      ],
      "risk": "high",
      "funding_sources": "ממשלת ישראל (67%), הקיבוץ הארצי",
      "report_url": "research/deep_Havatzelet.html",
      "source_links": [
        {"label": "GuideStar IL", "url": "https://www.guidestar.org.il/organization/510490451"},
        {"label": "obudget.org", "url": "https://next.obudget.org/i/associations/association/510490451"}
      ]
    },
    "Mind_Lab": {
      "name_he": "אשכולות חשיבה ישראל",
      "name_en": "Mind Lab / Accelium",
      "reg": "513099341",
      "entity_type": "חברה פרטית",
      "founded": 1994,
      "revenue_nis": 12000000,
      "gov_pct": 1,
      "staff": 75,
      "neutrality": 5,
      "transparency": 2,
      "ideology_detected": false,
      "cities": ["באר שבע", "הרצליה", "חיפה", "ירושלים", "כרמיאל", "תל אביב-יפו"],
      "school_count": 14,
      "program_budget": 595116,
      "thesis": "חברה עסקית למטרות רווח — הגדולה מסוגה בגפ\"ן. תוכן ניטרלי (משחקי חשיבה) אך שקיפות פיננסית אפסית. אימות אקדמי חלש.",
      "key_findings": [
        "חברה פרטית למטרות רווח — לא עמותה. אחת מבודדות בגפ\"ן",
        "4 ישויות קשורות: ישראל, ברזיל (10,000 בי\"ס), Accelium, Mind Lab Group",
        "אין אידיאולוגיה — משחקי אסטרטגיה וחשיבה בלבד",
        "דוח שנתי אחרון לרשם החברות: 2017 — פער רגולטורי של 9 שנים",
        "מחקר אקדמי חלש: חוקר מצוטט (Prof. Donald Green) הוא מדען מדיני, לא פסיכולוג חינוכי",
        "רכישת EduK ב-~$10M (2022) — התרחבות בברזיל"
      ],
      "risk": "low",
      "funding_sources": "הכנסות מסחריות מבתי ספר דרך Gefen",
      "report_url": "research/deep_Mind_Lab.html",
      "source_links": [
        {"label": "רשם החברות", "url": "https://ica.justice.gov.il/GenericCorporarionInfo/SearchCorporation?unit=8&id=513099341"},
        {"label": "אתר Accelium", "url": "https://www.accelium.com"},
        {"label": "אתר Mind Lab", "url": "https://www.mindlab.com"}
      ]
    },
    "Yesodot_Dror": {
      "name_he": "יסודות לצמיחה דרור",
      "name_en": "Yesodot LeTzmicha Dror (Dror Israel operating arm)",
      "reg": "580295533",
      "entity_type": "עמותה",
      "founded": 1997,
      "revenue_nis": 34800000,
      "gov_pct": 47,
      "staff": 988,
      "neutrality": 1,
      "transparency": 3,
      "ideology_detected": true,
      "cities": ["באר שבע", "הרצליה", "חיפה", "ירושלים", "כרמיאל", "קרית אונו", "קרית גת", "ראש העין", "רמת השרון", "רעננה", "תל אביב-יפו"],
      "school_count": 49,
      "program_budget": 784592,
      "thesis": "הזרוע המבצעית של דרור ישראל — 34.8M ₪, 148M ₪ מימון ממשלתי מצטבר. פדגוגיה ביקורתית של פאולו פריירה בכיתות. חלק מאקוסיסטם 234.6M ₪/שנה.",
      "key_findings": [
        "הקליפה התפעולית הראשית של תנועת דרור ישראל — אותם אנשים, אותה אידיאולוגיה",
        "תקציב 34.8M ₪, מימון ממשלתי 49.6M ₪ ב-3 שנים (כמעט הכל support, לא מכרזים)",
        "פדגוגיה ביקורתית (פאולו פריירה) — 'חינוך הוא פעולה פוליטית'",
        "14 קיבוצים עירוניים חינוכיים, 9+ בתי ספר חברתיים, 2 פנימיות",
        "988 'מתנדבים' — ייתכן שכר מתחת למינימום (700 ₪/חודש סטיפנד)",
        "אקוסיסטם דרור כולל: 234.6M ₪/שנה — הרשת הגדולה ביותר במיפוי"
      ],
      "risk": "high",
      "funding_sources": "משרד החינוך (108M ₪ מצטבר), קרנות בינ\"ל, תרומות פטורות ב-5 מדינות",
      "report_url": "research/deep_Yesodot_Dror.html",
      "source_links": [
        {"label": "GuideStar IL", "url": "https://www.guidestar.org.il/organization/580295533"},
        {"label": "obudget.org", "url": "https://next.obudget.org/i/associations/association/580295533"},
        {"label": "אתר דרור ישראל", "url": "https://www.drorisrael.org.il"},
        {"label": "דוח מחקר: רשת דרור ישראל", "url": "research/Dror_Israel_network.md"}
      ]
    },
    "Shaar_Shivion": {
      "name_he": "שער שוויון",
      "name_en": "Shaar Shivion / The Equalizer",
      "reg": "580558591",
      "entity_type": "עמותה",
      "founded": 2009,
      "revenue_nis": 13600000,
      "gov_pct": 11,
      "staff": 151,
      "neutrality": 3,
      "transparency": 3,
      "ideology_detected": false,
      "cities": ["באר שבע", "חיפה", "ירושלים", "קרית גת", "תל אביב-יפו"],
      "school_count": 41,
      "program_budget": 780495,
      "thesis": "ארגון ספורט-לשינוי-חברתי (לא ג'וינט, לא פמיניסטי כפי שסווג בטעות). 410+ קבוצות כדורגל/כדורסל בקהילות מוחלשות. דו-קיום יהודי-ערבי דרך ספורט.",
      "key_findings": [
        "תיקון סיווג: לא גוף ג'וינט, לא ממומן מחו\"ל ברובו (28.7% בלבד)",
        "410+ קבוצות, 10,000+ משתתפים, 500 מתנדבים, 151 עובדים",
        "כדורגל/כדורסל בקהילות — יהודים, ערבים, בדואים, דרוזים, עולים, אתיופים",
        "פרס UEFA, הכרת UNESCO",
        "מייסד: לירן גרסי (1985, בוגר האוניברסיטה העברית, יזם חברתי)",
        "הסיכון: דו-קיום מבוסס תיאוריית מגע — פוליטי מטבעו בהקשר הישראלי, אך לא אקטיביסטי"
      ],
      "risk": "moderate",
      "funding_sources": "Harris Philanthropies (>$1M), Revson Foundation ($80K), ממשלת ישראל (11%)",
      "report_url": "research/deep_Shaar_Shivion.html",
      "source_links": [
        {"label": "GuideStar IL", "url": "https://www.guidestar.org.il/organization/580558591"},
        {"label": "obudget.org", "url": "https://next.obudget.org/i/associations/association/580558591"},
        {"label": "אתר הארגון", "url": "https://www.theequalizer.org.il"}
      ]
    },
    "Alliance_Israel": {
      "name_he": "כל ישראל חברים / אליאנס",
      "name_en": "Alliance Israelite Universelle (Israel branch)",
      "reg": "580010890",
      "entity_type": "עמותה",
      "founded": 1860,
      "revenue_nis": 15000000,
      "gov_pct": 10,
      "staff": 50,
      "neutrality": 3,
      "transparency": 3,
      "ideology_detected": false,
      "cities": ["חיפה", "ירושלים", "נתיבות", "קרית גת", "תל אביב-יפו"],
      "school_count": 16,
      "program_budget": 377004,
      "thesis": "ארגון צרפתי-יהודי בן 166 שנה, מקורו בקולוניאליזם תרבותי. שליטה מפריז. בעלות משותפת על 3,300 דונם ליד ת\"א (מקוה ישראל) מוגנת בחוק כנסת 1976.",
      "key_findings": [
        "נוסד 1860 בפריז — אנטי-ציוני עד 1945. 'Mission Civilisatrice' יהודית",
        "שליטה מפריז: ועדה מרכזית עם דרישת 2/3 תושבי פריז",
        "3,300 דונם ליד ת\"א (מקוה ישראל) — חוק כנסת מ-1976 מגן על הבעלות",
        "US entity הכנסות קרסו מ-$25M ל-$2.1M (2018-2024) — סיבה לא ברורה",
        "תוכניות: סודקות (STEM לבנות, 40+ בי\"ס), מורשה (יהדות חברתית), כרם (הכשרת מורים)",
        "5 ישויות משפטיות: פריז, 2× ארה\"ב (EIN 98-6001112, 13-5626342), ישראל (580010890), מקוה ישראל (510151814)"
      ],
      "risk": "low-moderate",
      "funding_sources": "Alliance Paris HQ, Posen Foundation, Trump Foundation, ממשלת ישראל",
      "report_url": "research/deep_Alliance_Israel.html",
      "source_links": [
        {"label": "GuideStar IL", "url": "https://www.guidestar.org.il/organization/580010890"},
        {"label": "obudget.org", "url": "https://next.obudget.org/i/associations/association/580010890"},
        {"label": "GuideStar US (AIU)", "url": "https://www.guidestar.org/profile/98-6001112"},
        {"label": "אתר Alliance", "url": "https://www.aiu.org"},
        {"label": "כי\"ח ישראל", "url": "https://www.kiah.org.il"}
      ]
    },
    "Maga_BeTiaum": {
      "name_he": "מגע בתיאום בע\"מ",
      "name_en": "Maga BeTiaum Ltd. (Touch in Coordination)",
      "reg": "516711488",
      "entity_type": "חברה פרטית",
      "founded": 2022,
      "revenue_nis": 500000,
      "gov_pct": 0,
      "staff": 5,
      "neutrality": 1,
      "transparency": 1,
      "ideology_detected": true,
      "cities": ["אריאל", "הוד השרון", "הרצליה", "ראש העין", "רמת השרון", "רעננה", "תל אביב-יפו"],
      "school_count": 27,
      "program_budget": 453441,
      "thesis": "חברה פרטית למטרות רווח שנוסדה נובמבר 2022, מספקת חינוך מיני מגן ועד י\"ב. 'דלת מסתובבת' — מנהלת מקצועית לשעבר ממשרד החינוך. אין מנגנון הסכמת הורים.",
      "key_findings": [
        "נוסדה נובמבר 2022 — חברה חדשה מאוד, כבר ב-27 בתי ספר",
        "דלת מסתובבת: שירי בסין סביון — ממשרד החינוך (יחידת מיניות) לקבלנית חיצונית",
        "חינוך מיני מגיל גן — מסגרות הסכמה למבוגרים מוחלות על ילדים",
        "חברת SEI (הקמה מרץ 2023) — גוף מקצועי שחברי הארגון עצמם הקימו, מאשר את עצמו",
        "אין מנגנון opt-in להורים, שקיפות פיננסית אפסית כחברה פרטית",
        "רשת קשרים: מידע אמין על מין, חוש\"ן, תמורות, האגודה לחינוך מיני — אקוסיסטם שלם"
      ],
      "risk": "high",
      "funding_sources": "תשלומי בתי ספר דרך Gefen (תוכניות #2930, #3027)",
      "report_url": "research/deep_Maga_BeTiaum.html",
      "source_links": [
        {"label": "רשם החברות", "url": "https://ica.justice.gov.il/GenericCorporarionInfo/SearchCorporation?unit=8&id=516711488"},
        {"label": "דוח מחקר: אשכול חינוך מיני", "url": "research/Gender_sexuality_education_providers.md"}
      ]
    },
    "Meyda_Amin": {
      "name_he": "מידע אמין על מין (שלומית הברון)",
      "name_en": "Meyda Amin Al Min / Reliable Info About Sex",
      "reg": "עוסק מורשה",
      "entity_type": "עוסק מורשה",
      "founded": 2014,
      "revenue_nis": 1200000,
      "gov_pct": 0,
      "staff": 40,
      "neutrality": 1,
      "transparency": 1,
      "ideology_detected": true,
      "cities": ["גבעת שמואל", "גבעתיים", "הוד השרון", "הרצליה", "חיפה", "ירושלים", "כרמיאל", "קרית אונו", "קרית גת", "קרית שמונה", "ראש העין", "רמת גן", "רמת השרון", "רעננה", "תל אביב-יפו"],
      "school_count": 51,
      "program_budget": 1189257,
      "thesis": "לא עוסק יחיד — מרכז מסחרי עם ~40 מנחים הרשום כעוסק מורשה. 1.19M ₪ מכספי ציבור ללא חובת דיווח. מייסדת: אקטיביסטית פמיניסטית שהקימה 'מאגר אנסים'.",
      "key_findings": [
        "~40 מנחים מתחת לרישום עוסק מורשה — אפס שקיפות, אפס דיווח ציבורי",
        "1.19M ₪ מגפ\"ן — הספק הגדול ביותר בקטגוריית חינוך מיני, תוכנית #2437",
        "51 בתי ספר ב-15 ערים — הפריסה הגיאוגרפית הרחבה ביותר מכל ספק מיני",
        "תוכנית K-12 'ספירלית': נורמליזציה של אוננות מגיל 4-5, אישור LGBTQ+ לקטינים",
        "כלי 'רמזור' (Traffic Light) — מסגרת התנהגותית מגיל גן עד י\"ב",
        "מייסדת: שלומית הברון (1976) — הקימה 'אחת מתוך אחת' (2,000+ עדויות, מאגר מוצפן של 1,800 חשודים. נסגר 2019)",
        "שותפה: סנדי בשרתי קורדובה — מייסדת משותפת",
        "פועלת בקהילות מסורתיות (קרית גת, קרית שמונה, ירושלים) — התנגשות אפשרית עם ערכי משפחה"
      ],
      "risk": "high",
      "funding_sources": "תשלומי בתי ספר דרך Gefen, ללא מימון ממשלתי ישיר, ללא חובת דיווח כעוסק מורשה",
      "report_url": "research/deep_Meyda_Amin.html",
      "source_links": [
        {"label": "דוח מחקר: אשכול חינוך מיני", "url": "research/Gender_sexuality_education_providers.md"},
        {"label": "SEI (האגודה לחינוך מיני)", "url": "https://www.sei.org.il"}
      ]
    },
    "Gesher": {
      "name_he": "גשר - מפעלים חינוכיים",
      "name_en": "Gesher - Educational Enterprises",
      "reg": "580054062",
      "entity_type": "עמותה",
      "founded": 1969,
      "revenue_nis": 22800000,
      "gov_pct": 15,
      "staff": 80,
      "neutrality": 2,
      "transparency": 4,
      "ideology_detected": true,
      "cities": ["באר שבע", "הוד השרון", "הרצליה", "חיפה", "ירושלים"],
      "school_count": 11,
      "program_budget": 69181,
      "thesis": "גשר אסימטרי — הנהלה דתית-לאומית בלעדית (מייסד מישיבת מרכז הרב, עמד בראש ועדת מינויים של הבית היהודי). מזיז חילונים לכיוון מסורת, לא להפך.",
      "key_findings": [
        "מייסד הרב דניאל טרופר — בוגר ישיבת מרכז הרב (ישיבת הדגל של הציונות הדתית), יועץ שר החינוך זבולון האמר (מפד\"ל, 1979-1984)",
        "טרופר עמד בראש ועדת המינויים של מפלגת הבית היהודי — 3→12 מנדטים",
        "CEO מ-2011: אילן גאל-דור — מישיבה בקרני שומרון (התנחלות)",
        "תקציב אמיתי 22.8M ₪ — הגפ\"ן (69K) הוא 0.3% בלבד מהפעילות",
        "קרן גשר לקולנוע (ישות נפרדת, 580358190): 105M ₪ מימון ממשלתי מצטבר",
        "6,000+ חיילי צה\"ל/שנה בסמינרים, הכשרת אנשי תקשורת בכירים",
        "US entity: Charity Navigator 4/4 כוכבים (96%), $1.6M/שנה",
        "תוכנית AMI: גיוס מאות אלפי צעירים לסנגוריה ברשתות חברתיות (עם משרד התפוצות)"
      ],
      "risk": "medium-high",
      "funding_sources": "William Davidson Foundation ($500K), Ruderman Family ($126K), Maimonides Fund, ממשלת ישראל, US fundraising ($1.6M/yr)",
      "report_url": "research/deep_Gesher.html",
      "source_links": [
        {"label": "GuideStar IL", "url": "https://www.guidestar.org.il/organization/580054062"},
        {"label": "obudget.org", "url": "https://next.obudget.org/i/associations/association/580054062"},
        {"label": "GuideStar US", "url": "https://www.guidestar.org/profile/23-7029115"},
        {"label": "Charity Navigator", "url": "https://www.charitynavigator.org/ein/237029115"},
        {"label": "אתר גשר", "url": "https://www.gesher.co.il"},
        {"label": "קרן גשר לקולנוע (obudget)", "url": "https://next.obudget.org/i/associations/association/580358190"}
      ]
    }
  },
    "AIDS_Task_Force": {
      "name_he": "הוועד למלחמה באיידס (ע\"ר)",
      "name_en": "Israel AIDS Task Force (IATF)",
      "reg": "580104545",
      "entity_type": "עמותה",
      "founded": 1985,
      "revenue_nis": 3816413,
      "gov_pct": 0.03,
      "staff": 14,
      "neutrality": 2,
      "transparency": 3,
      "ideology_detected": true,
      "cities": ["תל אביב-יפו", "הרצליה", "חיפה", "ירושלים", "באר שבע"],
      "thesis": "ארגון בריאות שהתפתח מעבר למנדט HIV/איידס ל'מיניות בריאה' ותעמולת להט\"ב. מימון NIF מאז הייסוד, ניגוד עניינים עם גלעד סיינסס.",
      "key_findings": [
        "מונופול לאומי — הגוף הלא-ממשלתי היחיד בתחום האיידס",
        "מימון NIF מאז 1985, מיזוג עם 'בלה דואגת' (2002) הטמיע להט\"ב מוסדית",
        "פיבוט אסטרטגי 2019 מ-HIV ל'מיניות בריאה' — סחיפת משימה",
        "ניגוד עניינים: מימון מגלעד (יצרנית PrEP) תוך קידום נגישות PrEP",
        "תוכנית גפ\"ן #1102 (37K ₪) = נקודת גישה, לא מקור הכנסה"
      ],
      "risk": "medium",
      "funding_sources": "תרומות (55%), קרנות (12%), גלעד סיינסס, NIF ארוך-שנים",
      "report_url": "research/deep_AIDS_Task_Force.html",
      "source_links": [
        {"label": "GuideStar IL", "url": "https://www.guidestar.org.il/organization/580104545"},
        {"label": "obudget.org", "url": "https://next.obudget.org/i/org/association/580104545"},
        {"label": "אתר רשמי", "url": "https://www.aidsisrael.org.il/"}
      ]
    },
    "Yesod": {
      "name_he": "יסוד — העמותה לקידום זכויות יסוד ודמוקרטיה",
      "name_en": "Yesod — Basic Rights & Democracy",
      "reg": "580558997",
      "entity_type": "עמותה",
      "founded": 2012,
      "revenue_nis": 1778412,
      "gov_pct": 0,
      "staff": 5,
      "neutrality": 2,
      "transparency": 3,
      "ideology_detected": true,
      "cities": ["תל אביב-יפו", "הרצליה"],
      "thesis": "ארגון חינוך אזרחי ממומן ע\"י משה ינאי (פשט רגל 10/2025). ועדת היגוי פרוגרסיבית לחלוטין — דן מרידור, קולט אביטל, גבריאלה שלו.",
      "key_findings": [
        "סיכון קיומי: המממן העיקרי משה ינאי פשט רגל עם חובות 120M$+",
        "ועדת היגוי פרוגרסיבית: דן מרידור, קולט אביטל, גבריאלה שלו — אין ייצוג ימני",
        "5 עובדים בלבד אך טוען ל-76,000 תלמידים ב-250 בתי ספר",
        "אפס מימון NIF, EU או זר — פרויקט פילנתרופיה אישי"
      ],
      "risk": "medium",
      "funding_sources": "משה ינאי (פושט רגל). אפס מימון ממשלתי או זר.",
      "report_url": "research/deep_Yesod.html",
      "source_links": [
        {"label": "GuideStar IL", "url": "https://www.guidestar.org.il/organization/580558997"},
        {"label": "obudget.org", "url": "https://next.obudget.org/i/org/association/580558997"},
        {"label": "אתר רשמי", "url": "https://www.yesod.ngo/"}
      ]
    },
    "Rossing_Center": {
      "name_he": "מרכז רוסינג לחינוך ודיאלוג",
      "name_en": "Rossing Center for Education and Dialogue",
      "reg": "513816421",
      "entity_type": "חל\"צ",
      "founded": 2004,
      "revenue_nis": null,
      "gov_pct": null,
      "staff": null,
      "neutrality": 4,
      "transparency": 3,
      "ideology_detected": false,
      "cities": ["ירושלים"],
      "thesis": "ארגון דו-דתי ירושלמי. 'מחנכים לשינוי' = הכשרת מורים לדיאלוג, לא תאוריה ביקורתית של גזע. מימון כנסייתי אירופי, לא NIF.",
      "key_findings": [
        "מייסד למד בסמינר לותרני — DNA של דיאלוג נוצרי-יהודי",
        "מימון מכנסיות: Misereor (קתולי גרמני), DVHL, כנסיית שוודיה",
        "לא NIF — יוצא דופן לארגון בתחום הדו-קיום",
        "לא מופיע ב-NGO Monitor — פרופיל מחלוקת נמוך",
        "דוח 2024: 111 מקרי אלימות נגד נוצרים בישראל"
      ],
      "risk": "low-moderate",
      "funding_sources": "כנסיות אירופיות (Misereor, DVHL, שוודיה), שגרירות ארה\"ב ($8K), PEF Israel",
      "report_url": "research/deep_Rossing_Center.html",
      "source_links": [
        {"label": "GuideStar IL", "url": "https://www.guidestar.org.il/organization/513816421"},
        {"label": "אתר רשמי", "url": "https://rossingcenter.org/"}
      ]
    },
    "Ladaat": {
      "name_he": "לדעת — לבחור נכון",
      "name_en": "Ladaat — Choose Well",
      "reg": "580055911",
      "entity_type": "עמותה",
      "founded": 1976,
      "revenue_nis": 1345480,
      "gov_pct": 0,
      "staff": 30,
      "neutrality": 2,
      "transparency": 4,
      "ideology_detected": true,
      "cities": ["ירושלים"],
      "thesis": "ארגון חינוך מיני ותיק (1976). שקוף כעמותה, אך מנכ\"לית מובילה 'פורום 21' לביטול ועדות הפלה — שחקן פוליטי.",
      "key_findings": [
        "הוותיק ביותר בתחום (1976) — 48 שנות פעילות",
        "אדבוקסיה פוליטית: פורום 21 לביטול ועדות הפסקת הריון",
        "6,000+ פניות ייעוץ חינם מאז 2018, כולל קטינים ללא הסכמת הורים",
        "חבר SEI — אותה רשת כמו מידע אמין ומגע בתיאום",
        "צוות ערבי ייעודי — פועל בעברית וערבית"
      ],
      "risk": "medium",
      "funding_sources": "תרומות, PEF Israel (חסות פיסקלית), גפ\"ן. אין NIF.",
      "report_url": "research/deep_Ladaat.html",
      "source_links": [
        {"label": "GuideStar IL", "url": "https://www.guidestar.org.il/organization/580055911"},
        {"label": "obudget.org", "url": "https://next.obudget.org/i/org/association/580055911"},
        {"label": "אתר רשמי", "url": "https://ladaat.org.il/"}
      ]
    },
    "Todaa": {
      "name_he": "מהפך תודעתי (ע\"ר)",
      "name_en": "Todaa Association (Mahapach Todaati)",
      "reg": "580396380",
      "entity_type": "עמותה",
      "founded": 2002,
      "revenue_nis": 1359494,
      "gov_pct": 0.79,
      "staff": 5,
      "neutrality": 1,
      "transparency": 2,
      "ideology_detected": true,
      "cities": ["רמת גן", "תל אביב-יפו"],
      "thesis": "פדגוגיה ביקורתית פמיניסטית. 6/6 דגלי נושא — שיא. ספק יחיד ממשרד הרווחה. מנכ\"לית משלבת אקדמיה, JDC ולובי פמיניסטי.",
      "key_findings": [
        "6 דגלי נושא בגפ\"ן — שיא חסר תקדים",
        "ספק יחיד: חוזי משרד הרווחה ללא מכרז",
        "שקיפות 49% ב-GuideStar (2/5)",
        "מנכ\"לית חוקרת 'הנאה מינית' בקרב נערות — מיושם בתוכניות",
        "אקטיביזם כמטרת לימוד מפורשת"
      ],
      "risk": "medium-high",
      "funding_sources": "משרד הרווחה (79%), ספק יחיד 129K ₪ (2025), גפ\"ן 17.6K",
      "report_url": "research/deep_Todaa.html",
      "source_links": [
        {"label": "GuideStar IL", "url": "https://www.guidestar.org.il/organization/580396380"},
        {"label": "obudget.org", "url": "https://next.obudget.org/i/org/association/580396380"},
        {"label": "אתר רשמי", "url": "https://www.todaango.org.il/"}
      ]
    },
    "Or_Gelbard": {
      "name_he": "כל השאלות נכונות / אלפי גלברד",
      "name_en": "Kol HaShelot Nekhonot / Alfi Gelbard",
      "reg": null,
      "entity_type": "עוסק מורשה",
      "founded": 2014,
      "revenue_nis": 204668,
      "gov_pct": null,
      "staff": 1,
      "neutrality": 2,
      "transparency": 2,
      "ideology_detected": true,
      "cities": ["תל אביב-יפו", "רמת השרון", "רמת גן", "ירושלים", "באר שבע"],
      "thesis": "עוסק מורשה יחיד עם 204K ₪ מכספי ציבור. הוסמך ע\"י IPPF/דלת פתוחה. מערכת הסמכה עצמית.",
      "key_findings": [
        "204K ₪ = התקציב הגבוה ביותר בקטגוריית R4, ללא שקיפות ארגונית",
        "MA פסיכולוגיה + עבודה סוציאלית מ-TAU (תוכנית טאובמן)",
        "63+ מאמרי הארץ — נוכחות תקשורתית חריגה",
        "מאמר דעה נגד אבי מאוז — פעיל פוליטית",
        "הסמכה עצמית: IPPF → SEI → אותו מעגל"
      ],
      "risk": "moderate",
      "funding_sources": "גפ\"ן (204K ₪), ספרים, הרצאות פרטיות",
      "report_url": "research/deep_Or_Gelbard.html",
      "source_links": [
        {"label": "אתר רשמי", "url": "https://www.kolhashelot.org/"},
        {"label": "SEI", "url": "https://sei.org.il/"}
      ]
    },
    "Sexual_Assault_Centers": {
      "name_he": "איגוד מרכזי הסיוע לנפגעות תקיפה מינית (ARCCI)",
      "name_en": "Association of Rape Crisis Centers in Israel",
      "reg": "580173730",
      "entity_type": "עמותה",
      "founded": 1990,
      "revenue_nis": 16445238,
      "gov_pct": 0.01,
      "staff": 12,
      "neutrality": 3,
      "transparency": 4,
      "ideology_detected": false,
      "cities": ["תל אביב-יפו", "הרצליה", "חיפה", "ירושלים", "קרית שמונה", "באר שבע", "הוד השרון"],
      "thesis": "רשת 9 מרכזי סיוע + ארגון גג. 'יחד בהסכמה' (192K ₪) = חלק זעיר מפעילות משברית. תוכנית ממוקדת הסכמה, לא תאוריית מגדר.",
      "key_findings": [
        "רשת פדרטיבית: 9 מרכזים + ארגון גג, מחזור מצרפי 113M ₪",
        "תקציב גפ\"ן = פחות מ-0.5% מהפעילות",
        "מימון NIF היסטורי: NIF 'טיפח' את ARCCI",
        "200,000 בני נוער/שנה — תוכנית מניעה ארצית",
        "מנכ\"לית אורית סוליציאנו — דוברת שדולת הנשים לשעבר"
      ],
      "risk": "low",
      "funding_sources": "משרד הרווחה (למרכזים), NIF (היסטורי), Hadassah Foundation, UJA-Federation NY",
      "report_url": "research/deep_Sexual_Assault_Centers.html",
      "source_links": [
        {"label": "GuideStar (ARCCI)", "url": "https://www.guidestar.org.il/organization/580173730"},
        {"label": "NIF", "url": "https://www.nif.org/tag/association-of-rape-crisis-centers/"}
      ]
    },
    "Markam_Social_Space": {
      "name_he": "למרחב לשינוי חברתי — המרחב החברתי",
      "name_en": "The Social Space (LaMerhav LeShinu'i Chevrati)",
      "reg": "580354538",
      "entity_type": "עמותה",
      "founded": 2000,
      "revenue_nis": 3370629,
      "gov_pct": 0.05,
      "staff": 11,
      "neutrality": 2,
      "transparency": 2,
      "ideology_detected": true,
      "cities": ["תל אביב-יפו", "הרצליה", "ירושלים", "קרית אונו", "רמת השרון"],
      "thesis": "סיור אודיו בתוך מועדון חשפנות לשעבר ('הפוסיקט') בכיכר אתרים. הגוף המפעיל 580354538, לא 'מרקם' 580541910 — בלבול שמות בגפ\"ן.",
      "key_findings": [
        "בלבול זהות: ספק גפ\"ן 'מרקם' ≠ מרקם-רשת הקהילות",
        "תלמידים חווים מועדון חשפנות סגור עם אוזניות",
        "3.37M ₪, 95% מימון פרטי לא מפורט",
        "JTLV (נדל\"ן) תרמה הבניין",
        "סיורים נפרדים לגברים ונשים — גישה מגדרית"
      ],
      "risk": "moderate",
      "funding_sources": "JTLV (בניין), משרד הרווחה (162K), גפ\"ן (44K), 95% פרטי",
      "report_url": "research/deep_Markam.html",
      "source_links": [
        {"label": "GuideStar (580354538)", "url": "https://www.guidestar.org.il/organization/580354538"},
        {"label": "obudget.org", "url": "https://next.obudget.org/i/org/association/580354538"},
        {"label": "המרחב החברתי", "url": "https://www.socialspace.org.il/"}
      ]
    },
    "Chevruta": {
      "name_he": "חברותא — המרכז למודעות יהודית",
      "name_en": "Chevruta — Center for Jewish Awareness",
      "reg": "580479608",
      "entity_type": "עמותה",
      "founded": 2007,
      "revenue_nis": 9044412,
      "gov_pct": 0.56,
      "staff": 27,
      "neutrality": 1,
      "transparency": 2,
      "ideology_detected": true,
      "cities": ["תל אביב-יפו", "הרצליה", "חיפה", "ירושלים", "רמת גן", "רעננה", "ראש העין", "באר שבע"],
      "thesis": "גרעין תורני ציוני-דתי. עקיבא סמוטריץ' (אח שר האוצר) בדירקטוריון. קשור לתנועת קוממיות. 10.4M ₪ מימון ממשלתי מצטבר.",
      "key_findings": [
        "עקיבא סמוטריץ' (אח שר האוצר) בדירקטוריון",
        "מייסד יאיר קרטמן — חבר הנהלת 'קוממיות', מבית אל",
        "חקירת ידיעות אחרונות 2018: כספי התיישבות הועברו לת\"א",
        "6.3M ₪ ממשרד החינוך + 1.7M ממוסדות תורניים",
        "'מודעות יהודית' = קירוב דתי, לא פלורליזם"
      ],
      "risk": "high",
      "funding_sources": "משרד החינוך (6.3M), מוסדות תורניים (1.7M), התיישבות (1.26M), Charidy",
      "report_url": "research/deep_Chevruta.html",
      "source_links": [
        {"label": "GuideStar IL", "url": "https://www.guidestar.org.il/organization/580479608"},
        {"label": "obudget.org", "url": "https://next.obudget.org/i/org/association/580479608"},
        {"label": "חקירת ידיעות אחרונות", "url": "https://www.yediot.co.il/articles/0,7340,L-5148194,00.html"}
      ]
    },
    "Kehalim_Shluvim": {
      "name_he": "קהלים שלובים לשינוי חברתי (חל\"צ)",
      "name_en": "Kehalim Shluvim for Social Change",
      "reg": "515521748",
      "entity_type": "חל\"צ",
      "founded": 2017,
      "revenue_nis": 384143,
      "gov_pct": 0,
      "staff": 0,
      "neutrality": 2,
      "transparency": 2,
      "ideology_detected": true,
      "cities": ["תל אביב-יפו", "רמת השרון", "הרצליה", "הוד השרון", "ראש העין"],
      "thesis": "חברת גישור מסחרית (גומא גבים) יצרה חל\"צ ללא עובדים כדי לגשת לתקציבי חינוך. 7 דגלי נושא. נוסדה אחרי רצח רבין.",
      "key_findings": [
        "דפוס חברת קש: 0 עובדים, 0 מתנדבים מדווחים — שליטה מלאה ע\"י גומא גבים",
        "7 דגלי נושא — שיא בגפ\"ן",
        "מנדט מפורש ל'שינוי תרבות השיח' — נוסד אחרי רצח רבין",
        "אין הערכה עצמאית, אין מחקר אקדמי",
        "384K ₪ הכנסות עם 0 עובדים = סתירה מבנית"
      ],
      "risk": "moderate",
      "funding_sources": "גפ\"ן (70K ₪ דרך בתי ספר). תזרים אמיתי דרך גומא גבים.",
      "report_url": "research/deep_Kehalim_Shluvim.html",
      "source_links": [
        {"label": "GuideStar IL", "url": "https://www.guidestar.org.il/organization/515521748"},
        {"label": "גומא גבים", "url": "https://www.gomegevim.co.il/social/social_audience/"}
      ]
    },
    "Teva_HaAdam": {
      "name_he": "טבע האדם / כספי אלדד",
      "name_en": "Teva HaAdam / Eldad Kaspi",
      "reg": null,
      "entity_type": "עוסק מורשה",
      "founded": null,
      "revenue_nis": 256369,
      "gov_pct": null,
      "staff": 1,
      "neutrality": 4,
      "transparency": 2,
      "ideology_detected": false,
      "cities": ["כרמיאל", "קרית גת", "רמת גן", "רמת השרון"],
      "thesis": "עוסק מורשה — MSc רפואה (לא חינוך). פסיכולוגיה פופולרית, 'קבל את האחר'. מדריך מתארח גם בארגון ציוני-לאומי — ניטרלי אידיאולוגית.",
      "key_findings": [
        "תארים במדעי הרפואה — לא חינוך או פסיכולוגיה",
        "256K ₪ ל-10 בתי ספר = חריג לעוסק מורשה",
        "טביעת רגל דיגיטלית אפסית למרות טענה לעשרות אלפי תלמידים",
        "מדריך מתארח ב'דרך עמ\"י' (ארגון לאומי) — מוכר לשני הצדדים",
        "גישה אינדיבידואלית נגד דעות קדומות, לא ניתוח מבני"
      ],
      "risk": "low-moderate",
      "funding_sources": "גפ\"ן (256K ₪), סדנאות פרטיות ותאגידיות",
      "report_url": "research/deep_Teva_HaAdam.html",
      "source_links": [
        {"label": "אתר רשמי", "url": "https://tevahadam.co.il/"}
      ]
    },
    "CHOSHEN": {
      "name_he": "חוש\"ן — חינוך ושינוי",
      "name_en": "CHOSHEN (Hoshen) — LGBTQ Education",
      "reg": "513544494",
      "entity_type": "חל\"צ",
      "founded": 2004,
      "revenue_nis": null,
      "gov_pct": null,
      "staff": null,
      "neutrality": 1,
      "transparency": 2,
      "ideology_detected": true,
      "cities": [],
      "thesis": "ארגון חד-נושאי: כל 6 תוכניות גפ\"ן = נטייה מינית וזהות מגדרית. 250 מתנדבים להט\"ב. קיצוץ 85% ב-2024 ע\"י אבי מאוז.",
      "key_findings": [
        "6 תוכניות גפ\"ן כולן מתויגות 'מגדר'",
        "קיצוץ 85%: מ-3M ₪ ל-300K, 4,200 סדנאות בוטלו",
        "250 מתנדבים להט\"ב מספרים סיפורים אישיים מכיתה ז'",
        "מימון NIF ($15K) + A Wider Bridge (ארה\"ב)",
        "פער: בעברית 'אדבוקסיה', באנגלית 'חינוך ומידע'"
      ],
      "risk": "medium",
      "funding_sources": "משרד החינוך (קוצץ 85%), עיריית ת\"א, NIF, A Wider Bridge",
      "report_url": "research/deep_CHOSHEN.html",
      "source_links": [
        {"label": "GuideStar IL", "url": "https://www.guidestar.org.il/organization/513544494"},
        {"label": "אתר רשמי", "url": "https://www.hoshen.org/"}
      ]
    },
    "Yad_BYad": {
      "name_he": "יד ביד: המרכז לחינוך יהודי ערבי",
      "name_en": "Hand in Hand: Jewish-Arab Education",
      "reg": "580293710",
      "entity_type": "עמותה",
      "founded": 1997,
      "revenue_nis": 45095891,
      "gov_pct": 0,
      "staff": 270,
      "neutrality": 2,
      "transparency": 4,
      "ideology_detected": true,
      "cities": ["תל אביב-יפו", "חיפה", "ירושלים"],
      "thesis": "פרויקט הדו-קיום המרכזי: 6 בתי ספר דו-לשוניים, מודל נרטיב-כפול (נכבה ויום עצמאות). $9M/שנה מארה\"ב. 45M ₪.",
      "key_findings": [
        "45M ₪ + $9M מארה\"ב — אפס מימון ממשלתי ישיר",
        "501(c)(3) בפורטלנד — Charity Navigator 4/4 (98%)",
        "נרטיב-כפול: הנצחת נכבה לצד יום העצמאות — מחוץ לקונצנזוס",
        "הצתה 2014 ע\"י קיצוניים מ'להבה'",
        "דמוגרפיה 60/40 ערבים-יהודים, נטישת משפחות יהודיות"
      ],
      "risk": "moderate",
      "funding_sources": "USAID, NIF Australia, UJA-Federation NY, DEAR (שוויץ), Sternsinger (גרמניה), Pears (UK)",
      "report_url": "research/deep_Yad_BYad.html",
      "source_links": [
        {"label": "GuideStar IL", "url": "https://www.guidestar.org.il/organization/580293710"},
        {"label": "אתר (עברית)", "url": "https://www.hih.org.il"},
        {"label": "ProPublica 990", "url": "https://projects.propublica.org/nonprofits/organizations/931269590"},
        {"label": "Charity Navigator", "url": "https://www.charitynavigator.org/ein/931269590"}
      ]
    },
    "Efshar_Acheret": {
      "name_he": "אפשר אחרת",
      "name_en": "A New Way (Efshar Acheret)",
      "reg": "580316297",
      "entity_type": "עמותה",
      "founded": 1998,
      "revenue_nis": 1876658,
      "gov_pct": 0,
      "staff": 13,
      "neutrality": 4,
      "transparency": 2,
      "ideology_detected": false,
      "cities": ["תל אביב-יפו", "הוד השרון", "קרית אונו", "ראש העין", "רמת השרון"],
      "thesis": "דו-קיום יהודי-ערבי בנמוך-פרופיל. מודל תאומות בתי ספר. בגרות אזרחות משותפת ראשונה (2017). פרס אחדות ירושלים (מרכז-ימין).",
      "key_findings": [
        "מסגור אזרחי ('חיים משותפים') — לא פוליטי",
        "פרס אחדות ירושלים 2022 — קבלה ממסדית מימין",
        "בגרות אזרחות משותפת עם רשת אמל — יחידה בישראל",
        "מייסדת עברה לגור בכפר קאסם — מחויבות אישית",
        "אפס NIF, EU או מימון ממשלתי"
      ],
      "risk": "low",
      "funding_sources": "תרומות פרטיות, קרן IFA, גפ\"ן (47K). אפס מימון ממשלתי או זר.",
      "report_url": "research/deep_Efshar_Acheret.html",
      "source_links": [
        {"label": "GuideStar IL", "url": "https://www.guidestar.org.il/organization/580316297"},
        {"label": "אתר רשמי", "url": "https://www.anewway.org.il/"}
      ]
    },
    "Tehuda": {
      "name_he": "תהודה הכשרה והעשרה (חל\"צ)",
      "name_en": "Tehuda — Education, Impact, Community",
      "reg": "515191955",
      "entity_type": "חל\"צ",
      "founded": 2015,
      "revenue_nis": 1625488,
      "gov_pct": 0,
      "staff": 26,
      "neutrality": 2,
      "transparency": 2,
      "ideology_detected": true,
      "cities": ["תל אביב-יפו", "הרצליה"],
      "thesis": "מנכ\"לית יו\"ר ועדת ביקורת של SEI — לולאת הסמכה עצמית. הוסמכה IPPF/דלת פתוחה. BA קולנוע, לא קליני.",
      "key_findings": [
        "לולאת הסמכה: מנכ\"לית יו\"ר ביקורת SEI שמכיר בתוכנית שלה",
        "BA קולנוע והומניסטיקה — לא פסיכולוגיה או חינוך",
        "הסמכה IPPF דרך 'דלת פתוחה' — צינור בינלאומי",
        "~20 מנחי מיניות ארצית",
        "CSE פרוגרסיבי: זהות מגדרית ונטיה מינית"
      ],
      "risk": "moderate",
      "funding_sources": "גפ\"ן, חוזים עירוניים (מודיעין), הזמנות ישירות. אפס מימון ממשלתי.",
      "report_url": "research/deep_Tehuda.html",
      "source_links": [
        {"label": "GuideStar IL", "url": "https://www.guidestar.org.il/he/organization/515191955"},
        {"label": "מין חינוך שכזה", "url": "https://min-hinuch.co.il/"},
        {"label": "SEI", "url": "https://sei.org.il/"}
      ]
    },
    "Leyada": {
      "name_he": "בית הספר התיכון ליד האוניברסיטה העברית (ליד\"ה)",
      "name_en": "Hebrew University Secondary School (Leyada)",
      "reg": "510030695",
      "entity_type": "חל\"צ",
      "founded": 1935,
      "revenue_nis": 35943299,
      "gov_pct": 1.1,
      "staff": 212,
      "neutrality": 2,
      "transparency": 2,
      "ideology_detected": true,
      "cities": ["ירושלים"],
      "school_count": 1,
      "program_budget": null,
      "thesis": "בית ספר אליטיסטי סלקטיבי הצמוד לאוניברסיטה העברית, עם אוריינטציה פרוגרסיבית מובהקת — הזמנת דוברים פרוגרסיביים, הנפת דגל גאווה, כנסי 'הומניזם במלחמה'. מותקף שוב ושוב מימין.",
      "key_findings": [
        "נוסד 1935, חל\"צ עם הכנסה שנתית 36M ₪ — מימון ממשלתי 1.1% בלבד",
        "שקיפות 40% בגיידסטאר — נמוכה ביחס לגודל המוסד",
        "דגל גאווה מונף קבוע מאז רצח שירה בנקי (2015)",
        "הרצאות של איימן עודה, רולא דאוד (עומדים ביחד), שוברים שתיקה",
        "ערוץ 14 ניהל מסע נגד בית הספר (2025) — משרד החינוך חקר",
        "בוגרים: 2 פרסי נובל, 5 שופטי עליון, 20+ פרסי ישראל",
        "5 מ-14 חברי דירקטוריון הם סגל האוניברסיטה העברית"
      ],
      "risk": "high",
      "funding_sources": "שכר לימוד, תרומות (ניכוי מס בישראל, ארה\"ב, בריטניה, צרפת), קרנות אמריקאיות",
      "report_url": "research/deep_Leyada.html",
      "source_links": [
        {"label": "GuideStar IL", "url": "https://www.guidestar.org.il/organization/510030695"},
        {"label": "obudget.org", "url": "https://next.obudget.org/i/org/association/510030695"},
        {"label": "אתר בית הספר", "url": "https://www.leyada.net"}
      ]
    },
    "Kedma": {
      "name_he": "בית ספר תיכון קדמה ירושלים",
      "name_en": "Kedma School Jerusalem",
      "reg": "580320083",
      "entity_type": "עמותה",
      "founded": 1994,
      "revenue_nis": 2736947,
      "gov_pct": 0,
      "staff": 25,
      "neutrality": 2.5,
      "transparency": 3,
      "ideology_detected": true,
      "cities": ["ירושלים"],
      "school_count": 1,
      "program_budget": null,
      "thesis": "פרויקט חינוכי מזרחי-חברתי שיישם פדגוגיה ביקורתית בשכונת הקטמונים. מייסדים מהזרם הפרוגרסיבי-מזרחי (סבירסקי, שטרית, דהאן-כלב). מתוך 15 בתי ספר שתוכננו, רק אחד שרד.",
      "key_findings": [
        "נוסד 1994 כחלופה חילונית ל'אל המעיין' של ש\"ס",
        "~136 תלמידים, 25 מורים — בית ספר קטן וקהילתי",
        "שיעור בגרות 72-85% — מאפס בשכונה לפני הקמת בית הספר",
        "מייסדים: סבירסקי (מרכז אדוה), שטרית (הקשת המזרחית), דהאן-כלב (פמיניזם)",
        "מימון: קרן ירושלים, קרן מתנאל, ללא העברות ממשלתיות ישירות",
        "סניפים נסגרו: תל אביב (1999), קרית מלאכי (1996)",
        "פרס חינוך ארצי 2015 מטעם משרד החינוך"
      ],
      "risk": "medium",
      "funding_sources": "קרן ירושלים, קרן מתנאל, תקציב משרד החינוך לתלמיד",
      "report_url": "research/deep_Kadma.html",
      "source_links": [
        {"label": "GuideStar IL", "url": "https://www.guidestar.org.il/organization/580320083"},
        {"label": "אתר העמותה", "url": "https://kedma-edu.org.il"},
        {"label": "אתר בית הספר", "url": "https://kedmaschool.co.il"}
      ]
    }
  },

  /** City → org keys mapping for quick lookup in detail panels */
  "city_orgs": {
    "אריאל": ["Maga_BeTiaum"],
    "באר שבע": ["Hartman", "Democratic_Institute", "Yedidut_Toronto", "Matzmichim", "Mind_Lab", "Yesodot_Dror", "Shaar_Shivion", "Gesher", "AIDS_Task_Force", "Or_Gelbard", "Sexual_Assault_Centers", "Chevruta"],
    "גבעת שמואל": ["Matzmichim", "Meyda_Amin"],
    "גבעתיים": ["Matzmichim", "Meyda_Amin"],
    "הוד השרון": ["Matzmichim", "Maga_BeTiaum", "Meyda_Amin", "Gesher", "Sexual_Assault_Centers", "Kehalim_Shluvim", "Efshar_Acheret"],
    "הרצליה": ["Matzmichim", "Mind_Lab", "Yesodot_Dror", "Maga_BeTiaum", "Meyda_Amin", "Gesher", "AIDS_Task_Force", "Yesod", "Sexual_Assault_Centers", "Markam_Social_Space", "Chevruta", "Kehalim_Shluvim", "Tehuda"],
    "חיפה": ["Hartman", "Yedidut_Toronto", "Matzmichim", "Mind_Lab", "Yesodot_Dror", "Shaar_Shivion", "Alliance_Israel", "Meyda_Amin", "Gesher", "AIDS_Task_Force", "Sexual_Assault_Centers", "Chevruta", "Yad_BYad"],
    "ירושלים": ["Hartman", "Democratic_Institute", "Yedidut_Toronto", "Mind_Lab", "Yesodot_Dror", "Shaar_Shivion", "Alliance_Israel", "Meyda_Amin", "Gesher", "AIDS_Task_Force", "Rossing_Center", "Ladaat", "Or_Gelbard", "Sexual_Assault_Centers", "Markam_Social_Space", "Chevruta", "Yad_BYad", "Leyada", "Kedma"],
    "כרמיאל": ["Hartman", "Matzmichim", "Mind_Lab", "Yesodot_Dror", "Meyda_Amin", "Teva_HaAdam"],
    "נתיבות": ["Matzmichim", "Alliance_Israel"],
    "עמנואל": [],
    "אפרת": [],
    "קרית אונו": ["Matzmichim", "Yesodot_Dror", "Meyda_Amin", "Markam_Social_Space", "Efshar_Acheret"],
    "קרית גת": ["Yedidut_Toronto", "Matzmichim", "Yesodot_Dror", "Shaar_Shivion", "Alliance_Israel", "Meyda_Amin", "Teva_HaAdam"],
    "קרית שמונה": ["Matzmichim", "Meyda_Amin", "Sexual_Assault_Centers"],
    "ראש העין": ["Matzmichim", "Yesodot_Dror", "Maga_BeTiaum", "Meyda_Amin", "Chevruta", "Kehalim_Shluvim", "Efshar_Acheret"],
    "רמת גן": ["ACRI", "Hartman", "Matzmichim", "Meyda_Amin", "Todaa", "Or_Gelbard", "Chevruta", "Teva_HaAdam"],
    "רמת השרון": ["ACRI", "Hartman", "Havatzelet", "Matzmichim", "Yesodot_Dror", "Maga_BeTiaum", "Meyda_Amin", "Or_Gelbard", "Markam_Social_Space", "Kehalim_Shluvim", "Teva_HaAdam", "Efshar_Acheret"],
    "רעננה": ["Democratic_Institute", "Matzmichim", "Yesodot_Dror", "Maga_BeTiaum", "Meyda_Amin", "Chevruta"],
    "תל אביב-יפו": ["Hartman", "Matzmichim", "Mind_Lab", "Yesodot_Dror", "Shaar_Shivion", "Alliance_Israel", "Maga_BeTiaum", "Meyda_Amin", "AIDS_Task_Force", "Yesod", "Todaa", "Or_Gelbard", "Sexual_Assault_Centers", "Markam_Social_Space", "Chevruta", "Kehalim_Shluvim", "Yad_BYad", "Efshar_Acheret", "Tehuda"]
  }
};
