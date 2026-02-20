// suspicious_concepts.js — Concept-driven flagging for program cards
// Each concept has documented double meaning with evidence chain

const SUSPICIOUS_CONCEPTS = {

  concepts: {
    sexuality: {
      name_he: 'מיניות',
      short_label: 'מיניות',
      color: '#e91e63',
      surface_meaning: 'חינוך מיני מותאם גיל: הגנה מפני פגיעה, גבולות גוף, הסכמה, התפתחות בריאה.',
      hidden_meaning: 'חינוך מיני מקיף (CSE) הכולל ספקטרום מגדרי, זהויות מיניות, "מיניות חיובית" — לעיתים מגיל צעיר מאוד. מסגרת IPPF/UNESCO עם תכנים שהורים רבים לא מאשרים.',
      evidence: [
        {
          source: 'UNESCO/IPPF',
          text: 'המדריך הבינלאומי לחינוך מיני (2018) מהווה את הבסיס ל-CSE בעשרות מדינות. IPPF מסמיך את הסניף הישראלי "דלת פתוחה", שבוגריו הקימו את SEI — האגודה שמסמיכה מחנכים מיניים בגפ"ן.',
          url: 'https://www.unfpa.org/publications/international-technical-guidance-sexuality-education'
        },
        {
          source: 'דוח קאס (בריטניה, 2024)',
          text: 'קבע כי אין בסיס מספיק להוראת "זהות מגדרית" בבתי ספר. תוכנית RSE החובה עוררה מחאות הורים נרחבות.',
          url: 'https://cass.independent-review.uk/'
        }
      ],
      rosetta_url: 'https://aosshalem-dev.github.io/progressive-rosetta-stone/',
      research_url: 'https://aosshalem-dev.github.io/shefi-research/'
    },

    gender: {
      name_he: 'מגדר',
      short_label: 'מגדר',
      color: '#9c27b0',
      surface_meaning: 'שוויון בין המינים, מניעת אלימות מגדרית, כבוד הדדי, העצמה.',
      hidden_meaning: 'ספקטרום מגדרי, זהות מגדרית כבחירה, "פירוק בינאריות מגדרית". תוכניות Safe Schools (אוסטרליה) נסגרו ב-2017 לאחר חשיפת תכנים קיצוניים.',
      evidence: [
        {
          source: 'Safe Schools (אוסטרליה)',
          text: 'תוכנית Safe Schools נסגרה ב-2017 לאחר שנחשף שהכילה תכני מגדר קיצוניים לגילאי בית ספר.',
          url: null
        },
        {
          source: 'דוח קאס (בריטניה, 2024)',
          text: 'הביקורת המקצועית הרחבה ביותר עד היום — קבעה שאין בסיס ראייתי להוראת "זהות מגדרית" בבתי ספר.',
          url: 'https://cass.independent-review.uk/'
        }
      ],
      rosetta_url: 'https://aosshalem-dev.github.io/progressive-rosetta-stone/',
      research_url: 'https://aosshalem-dev.github.io/shefi-research/'
    },

    sel: {
      name_he: 'למידה רגשית-חברתית (SEL)',
      short_label: 'SEL',
      color: '#1565c0',
      surface_meaning: 'כלים רגשיים-חברתיים: מודעות עצמית, אמפתיה, ניהול רגשות, קבלת החלטות אחראית.',
      hidden_meaning: 'מסגרת להחדרת תכנים אידיאולוגיים. CASEL הנחה מ-2020 "SEL טרנספורמטיבי" הכולל "פירוק מבנים מדכאים". האקדמיה הלאומית למדעים (ישראל) מבהירה שביסוד SEL נדרשת הכרעה ערכית בין "ניאו-ליברליזם" ל"דמוקרטיה ביקורתית".',
      evidence: [
        {
          source: 'CASEL — SEL טרנספורמטיבי',
          text: 'ארגון CASEL הנחה מ-2020 "SEL טרנספורמטיבי" הכולל "פירוק מבנים מדכאים". מדינות פלורידה וטקסס אסרו תכנים אלה מבתי ספר.',
          url: 'https://casel.org'
        },
        {
          source: 'האקדמיה הלאומית למדעים (ישראל, 2020)',
          text: 'המלצות SEL — המסמך מבהיר שביסוד SEL נדרשת הכרעה ערכית בין "ניאו-ליברליזם" ל"דמוקרטיה ביקורתית" — כלומר עדשה מרקסיסטית.',
          url: 'https://education.academy.ac.il/SystemFiles/23425.pdf'
        },
        {
          source: 'ד"ר ניר מיכאלי — ערוץ משרד החינוך (02:25)',
          text: '\u201Fכל פעם שדברים על ביג דאטה, קופצת לי הטראומה של פרסום נתוני המיצ"ב... ואנחנו צריכים להיות מאוד מאוד זהירים באיזה מידע אנחנו אוספים ואיזה מידע אנחנו לא אוספים\u201E',
          url: 'https://www.youtube.com/watch?v=WUhBXXgCVsQ&t=145'
        }
      ],
      rosetta_url: 'https://aosshalem-dev.github.io/progressive-rosetta-stone/',
      research_url: 'https://aosshalem-dev.github.io/sel-research/'
    },

    activism: {
      name_he: 'אקטיביזם',
      short_label: 'אקטיביזם',
      color: '#e65100',
      surface_meaning: 'מעורבות אזרחית, שינוי חברתי חיובי, מנהיגות צעירה, ערכי דמוקרטיה.',
      hidden_meaning: 'הכשרת תלמידים ומורים כ"סוכני שינוי חברתי" — פדגוגיה אקטיביסטית. קטקו-איילי ומיכאלי מגדירים זאת כ-Activist Pedagogy (2020), ובמקביל מסתירים את האופי הפוליטי.',
      evidence: [
        {
          source: 'ד"ר ניר מיכאלי — הרצאה בקדמה (55:00)',
          text: '\u201Fאני הרבה פעמים הייתה לי תחושה כמורה שאני יכול לארגן הפיכה קומוניסטית בכיתה ואף אחד לא בכלל לא ירגיש וזה לא יזיז לאף אחד והם אפילו לא ידעו בדיוק להגיד בבית על מה בדיוק דיברנו בכיתה\u201E',
          url: 'https://www.youtube.com/watch?v=lTtg0gbU-ZU&t=3300'
        },
        {
          source: 'קטקו-איילי — מאמר 2020 (אנגלית) מול דו"ח 2024 (עברית)',
          text: 'אותו מודל פדגוגי בשני מסמכים: 2020 — "Activist Pedagogy" (שפה אקטיביסטית גלויה, מורים כסוכני שינוי). 2024 — "חינוך פוליטי בעת משבר לאומי" (שפה מרוככת למשרד החינוך). המודל זהה, האריזה הפוכה.',
          url: null
        }
      ],
      rosetta_url: 'https://aosshalem-dev.github.io/progressive-rosetta-stone/',
      research_url: null
    },

    rights: {
      name_he: 'זכויות',
      short_label: 'זכויות',
      color: '#00838f',
      surface_meaning: 'חינוך לזכויות אדם, אמנת זכויות הילד, חשיבה ביקורתית, דמוקרטיה.',
      hidden_meaning: 'מסגרת "זכויות" משמשת לקידום עמדות פוליטיות ספציפיות. ארגונים כמו האגודה לזכויות האזרח משתמשים ב"שפת זכויות" להצדקת עמדות שנויות במחלוקת כאילו הן אוניברסליות.',
      evidence: [
        {
          source: 'האגודה לזכויות האזרח בישראל',
          text: 'מפעילה תוכניות חינוך בבתי ספר תחת מסגרת "זכויות", תוך קידום עמדות פוליטיות ספציפיות שמוצגות כזכויות אוניברסליות.',
          url: null
        }
      ],
      rosetta_url: 'https://aosshalem-dev.github.io/progressive-rosetta-stone/',
      research_url: null
    },

    critical_pedagogy: {
      name_he: 'פדגוגיה ביקורתית',
      short_label: 'ביקורתית',
      color: '#b71c1c',
      surface_meaning: 'חשיבה ביקורתית, לימוד דרך שאילת שאלות, העצמת תלמידים, דיאלוג פתוח.',
      hidden_meaning: 'Critical Pedagogy — גישה מרקסיסטית מבוססת פאולו פריירה. האקדמיה הלאומית (2022) מבהירה: פדגוגיה ביקורתית = מרקסיזם, ניאו-מרקסיזם ופוסט-קולוניאליזם. אלה לא טענות של מבקרים — אלה המילים של המקדמים עצמם.',
      evidence: [
        {
          source: 'האקדמיה הלאומית למדעים — חינוך ערכי (2022)',
          text: 'מכניס מפורשות את המושג "פדגוגיה ביקורתית", מייחס אותו למרקסיסט פאולו פריירה, ומבהיר שמשמעותו מרקסיזם, ניאו-מרקסיזם ופוסט-קולוניאליזם.',
          url: 'https://education.academy.ac.il/SystemFiles/Values%20-%20Full%20report%202211021.pdf'
        },
        {
          source: 'דרור ישראל',
          text: 'ארגון המפעיל 16+ תוכניות בגפ"ן, מבוסס על פדגוגיה ביקורתית, מימון זר (NIF), גישה פוסט-לאומית. תלונות הורים על גיוס כיתתי.',
          url: null
        }
      ],
      rosetta_url: 'https://aosshalem-dev.github.io/progressive-rosetta-stone/',
      research_url: null
    },

    coexistence: {
      name_he: 'דו-קיום',
      short_label: 'דו-קיום',
      color: '#2e7d32',
      surface_meaning: 'שותפויות יהודים-ערבים, היכרות בין-תרבותית, דיאלוג, הבנה הדדית.',
      hidden_meaning: 'תוכניות "דו-קיום" שמקדמות נרטיב "מדינת כל אזרחיה" במסגור של שלום ושיתוף. ארגונים כמו מצמיחים משתמשים בשפת "הפחתת אלימות" אך מקדמים תפיסה פוסט-לאומית.',
      evidence: [
        {
          source: 'מצמיחים',
          text: 'דירוג 5, ~1,823,000 \u20AA, 348 בתי ספר. "מדינת כל אזרחיה" במסגור של מניעת אלימות.',
          url: null
        }
      ],
      rosetta_url: 'https://aosshalem-dev.github.io/progressive-rosetta-stone/',
      research_url: null
    }
  },

  // Explicit program_id → concept_id[] mapping (from research_desc analysis)
  program_concepts: {
    // מיניות
    '1878': ['sexuality', 'gender'],
    '1102': ['sexuality', 'gender'],
    '5974': ['sexuality', 'gender'],
    '2437': ['sexuality'],
    '22604': ['sexuality', 'gender'],
    '13785': ['sexuality', 'gender'],
    '13483': ['sexuality', 'gender'],
    '2930': ['sexuality', 'gender'],

    // מגדר
    '26307': ['gender'],
    '1502': ['gender'],
    '17502': ['gender'],
    '1160': ['gender'],
    '1121': ['gender'],
    '31655': ['gender'],
    '11683': ['gender'],
    '4664': ['gender'],

    // זכויות
    '10661': ['rights'],
    '5915': ['rights'],

    // פדגוגיה ביקורתית / דרור ישראל
    '1545': ['critical_pedagogy', 'activism'],
    '15286': ['critical_pedagogy', 'activism'],
    '1592': ['critical_pedagogy', 'gender', 'activism'],
    '3040': ['critical_pedagogy', 'activism'],
    '1382': ['critical_pedagogy'],

    // אקטיביזם / פוליטי
    '2039': ['activism'],

    // SEL
    '971': ['sel'],

    // פלורליזם / דו-קיום
    '865': ['coexistence'],

    // מצמיחים (הפחתת אלימות + דו-קיום)
    '927': ['coexistence']
  },

  // Fallback regex patterns for auto-detecting concepts from description/summary
  keyword_rules: [
    { pattern: /SEL|למידה רגשית.חברתית|רגשית[\s-]חברתית|social.emotional/i, concept: 'sel' },
    { pattern: /מיניות|חינוך מיני|מיני מקיף|CSE|sexuality/i, concept: 'sexuality' },
    { pattern: /מגדר|gender|ספקטרום|טרנס|זהות מינית/i, concept: 'gender' },
    { pattern: /אקטיביז|סוכני שינוי|שינוי חברתי|activist/i, concept: 'activism' },
    { pattern: /זכויות אדם|זכויות האזרח|זכויות ילד|זכויות יסוד|human rights/i, concept: 'rights' },
    { pattern: /פדגוגיה ביקורתית|critical pedagogy|פריירה|פוסט.לאומי/i, concept: 'critical_pedagogy' },
    { pattern: /דו.קיום|coexistence|שותפות ערבית.יהודית|חיים משותפים/i, concept: 'coexistence' }
  ],

  // How orgs can clear suspicion
  clearance: {
    title: 'איך לנקות חשד?',
    intro: 'ארגון שמפעיל תוכנית חשודה יכול לנקות את החשד על ידי עמידה בתנאים הבאים:',
    conditions: [
      'פתיחת כל חומרי הלימוד לעיון ציבורי (לא רק תקצירים)',
      'פרסום תכני ההכשרה שהמנחים עוברים',
      'מתן אפשרות להורים לצפות בפעילות בזמן אמת',
      'הצהרה כתובה שהתוכנית אינה מקדמת עמדה פוליטית',
      'גילוי מלא של כל מקורות המימון'
    ],
    footer: 'אם ארגון שלכם מופיע כאן ואתם עומדים בתנאים — צרו קשר ונעדכן.'
  }
};
