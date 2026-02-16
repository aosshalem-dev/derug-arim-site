/**
 * research_orgs.js — Deep research profiles for 14 investigated organizations
 * Generated from deep_research_*.md files (2026-02-16)
 * Cross-referenced with org_city_chalatz_mapping.json (1820 orgs × 20 cities)
 * Batch 1: 7 orgs | Batch 2: 7 orgs
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
      "thesis": "ארגון זכויות אדם שעבר מסנגוריה ליברלית קלאסית לאימוץ מסגרת 'אפרטהייד', עם מימון מ-NIF, EU ו-Ford Foundation.",
      "key_findings": [
        "אימוץ מסגרת 'אפרטהייד' מ-2008 — שינוי מהותי מזכויות ליברליות",
        "תקציב 10.9M ₪, מימון ממשלתי אפסי (0.2%)",
        "שותפות עם NIF, EU, Ford Foundation",
        "36 עובדים — צוות משפטי, מחקר וסנגוריה"
      ],
      "risk": "high",
      "funding_sources": "NIF, EU, Ford Foundation, Naomi & Nehemia Cohen Foundation",
      "report_url": "research/deep_ACRI.md",
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
      "report_url": "research/deep_Hartman.md",
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
      "report_url": "research/deep_Matzmichim.md",
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
      "report_url": "research/deep_Tikkun.md",
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
      "report_url": "research/deep_Democratic_Institute.md",
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
      "report_url": "research/deep_Yedidut_Toronto.md",
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
      "report_url": "research/deep_Havatzelet.md",
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
      "report_url": "research/deep_Mind_Lab.md",
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
      "report_url": "research/deep_Yesodot_Dror.md",
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
      "report_url": "research/deep_Shaar_Shivion.md",
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
      "report_url": "research/deep_Alliance_Israel.md",
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
      "report_url": "research/deep_Maga_BeTiaum.md",
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
      "report_url": "research/deep_Meyda_Amin.md",
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
      "report_url": "research/deep_Gesher.md",
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

  /** City → org keys mapping for quick lookup in detail panels */
  "city_orgs": {
    "אריאל": ["Maga_BeTiaum"],
    "באר שבע": ["Hartman", "Democratic_Institute", "Yedidut_Toronto", "Matzmichim", "Mind_Lab", "Yesodot_Dror", "Shaar_Shivion", "Gesher"],
    "גבעת שמואל": ["Matzmichim", "Meyda_Amin"],
    "גבעתיים": ["Matzmichim", "Meyda_Amin"],
    "הוד השרון": ["Matzmichim", "Maga_BeTiaum", "Meyda_Amin", "Gesher"],
    "הרצליה": ["Matzmichim", "Mind_Lab", "Yesodot_Dror", "Maga_BeTiaum", "Meyda_Amin", "Gesher"],
    "חיפה": ["Hartman", "Yedidut_Toronto", "Matzmichim", "Mind_Lab", "Yesodot_Dror", "Shaar_Shivion", "Alliance_Israel", "Meyda_Amin", "Gesher"],
    "ירושלים": ["Hartman", "Democratic_Institute", "Yedidut_Toronto", "Mind_Lab", "Yesodot_Dror", "Shaar_Shivion", "Alliance_Israel", "Meyda_Amin", "Gesher"],
    "כרמיאל": ["Hartman", "Matzmichim", "Mind_Lab", "Yesodot_Dror", "Meyda_Amin"],
    "נתיבות": ["Matzmichim", "Alliance_Israel"],
    "עמנואל": [],
    "אפרת": [],
    "קרית אונו": ["Matzmichim", "Yesodot_Dror", "Meyda_Amin"],
    "קרית גת": ["Yedidut_Toronto", "Matzmichim", "Yesodot_Dror", "Shaar_Shivion", "Alliance_Israel", "Meyda_Amin"],
    "קרית שמונה": ["Matzmichim", "Meyda_Amin"],
    "ראש העין": ["Matzmichim", "Yesodot_Dror", "Maga_BeTiaum", "Meyda_Amin"],
    "רמת גן": ["ACRI", "Hartman", "Matzmichim", "Meyda_Amin"],
    "רמת השרון": ["ACRI", "Hartman", "Havatzelet", "Matzmichim", "Yesodot_Dror", "Maga_BeTiaum", "Meyda_Amin"],
    "רעננה": ["Democratic_Institute", "Matzmichim", "Yesodot_Dror", "Maga_BeTiaum", "Meyda_Amin"],
    "תל אביב-יפו": ["Hartman", "Matzmichim", "Mind_Lab", "Yesodot_Dror", "Shaar_Shivion", "Alliance_Israel", "Maga_BeTiaum", "Meyda_Amin"]
  }
};
