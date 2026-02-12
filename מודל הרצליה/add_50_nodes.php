<?php
/**
 * דף להוספת 50 צמתים חדשים לקובץ JSON
 * פתח בדפדפן להרצה
 */

require_once(__DIR__ . '/auth/check.php');

$jsonFile = __DIR__ . '/data/sample_nodes.json';

echo "<!DOCTYPE html><html dir='rtl' lang='he'><head><meta charset='UTF-8'><title>הוספת 50 צמתים</title>";
echo "<style>body{font-family:Arial;max-width:900px;margin:50px auto;padding:20px;}";
echo ".success{color:green;}.error{color:red;}</style></head><body>";
echo "<h1>הוספת 50 צמתים חדשים</h1>";

// קריאת הקובץ הקיים
$existingContent = file_get_contents($jsonFile);
$existingNodes = json_decode($existingContent, true);

if ($existingNodes === null) {
    die("<p class='error'>✗ שגיאה בקריאת הקובץ הקיים</p></body></html>");
}

echo "<p>נמצאו " . count($existingNodes) . " צמתים קיימים</p>";

// 50 צמתים חדשים
$newNodes = [
    ["type" => "org", "label" => "קרן רוטשילד", "description" => "קרן פילנתרופית בינלאומית. מממנת תוכניות חינוכיות ומחקרים בישראל.", "flags" => ["key_player"], "props" => ["notes" => "קרן פילנתרופית שמממנת תוכניות חינוכיות"], "canonical_key" => "org_rothschild_foundation"],
    ["type" => "org", "label" => "קרן ברכה", "description" => "קרן פילנתרופית ישראלית. מממנת תוכניות חינוכיות ופרויקטים חברתיים.", "flags" => [], "props" => ["notes" => "קרן פילנתרופית ישראלית"], "canonical_key" => "org_bracha_foundation"],
    ["type" => "org", "label" => "מכון ון ליר", "description" => "מכון מחקר ישראלי. עוסק במחקר חברתי, פוליטי וחינוכי.", "flags" => ["academic"], "props" => ["notes" => "מכון מחקר ישראלי"], "canonical_key" => "org_van_leer_institute"],
    ["type" => "org", "label" => "מכון טאוב", "description" => "מכון מחקר ישראלי למדיניות חברתית. עוסק במחקר מדיניות חינוך וחברה.", "flags" => ["academic"], "props" => ["notes" => "מכון מחקר למדיניות חברתית"], "canonical_key" => "org_taub_institute"],
    ["type" => "org", "label" => "האוניברסיטה העברית", "description" => "אוניברסיטה מחקרית ישראלית בירושלים. מקדמת מחקר חינוכי ותוכניות אקדמיות.", "flags" => ["academic"], "props" => ["notes" => "אוניברסיטה מחקרית ישראלית"], "canonical_key" => "org_hebrew_university"],
    ["type" => "org", "label" => "אוניברסיטת תל אביב", "description" => "אוניברסיטה מחקרית ישראלית. מקדמת מחקר חינוכי ותוכניות אקדמיות.", "flags" => ["academic"], "props" => ["notes" => "אוניברסיטה מחקרית ישראלית"], "canonical_key" => "org_tel_aviv_university"],
    ["type" => "org", "label" => "אוניברסיטת חיפה", "description" => "אוניברסיטה מחקרית ישראלית. מקדמת מחקר חינוכי ותוכניות אקדמיות.", "flags" => ["academic"], "props" => ["notes" => "אוניברסיטה מחקרית ישראלית"], "canonical_key" => "org_haifa_university"],
    ["type" => "org", "label" => "מכללת לוינסקי", "description" => "מכללה אקדמית לחינוך. מכשירה מורים ומקדמת מחקר חינוכי.", "flags" => ["academic"], "props" => ["notes" => "מכללה אקדמית לחינוך"], "canonical_key" => "org_levinsky_college"],
    ["type" => "org", "label" => "מכללת סמינר הקיבוצים", "description" => "מכללה אקדמית לחינוך. מכשירה מורים ומקדמת מחקר חינוכי.", "flags" => ["academic"], "props" => ["notes" => "מכללה אקדמית לחינוך"], "canonical_key" => "org_kibbutzim_college"],
    ["type" => "org", "label" => "מכללת דוד ילין", "description" => "מכללה אקדמית לחינוך. מכשירה מורים ומקדמת מחקר חינוכי.", "flags" => ["academic"], "props" => ["notes" => "מכללה אקדמית לחינוך"], "canonical_key" => "org_david_yellin_college"],
    ["type" => "person", "label" => "פרופ' יוסי יונה", "description" => "פרופסור ישראלי לחינוך. חוקר בתחום החינוך והפדגוגיה הביקורתית.", "flags" => ["academic", "ideology"], "props" => ["notes" => "פרופסור לחינוך"], "canonical_key" => "person_yossi_yona"],
    ["type" => "person", "label" => "פרופ' יולי תמיר", "description" => "פרופסור ישראלי לפילוסופיה ולחינוך. שרת החינוך לשעבר.", "flags" => ["academic", "key_player"], "props" => ["notes" => "פרופסור לפילוסופיה ולחינוך, שרת החינוך לשעבר"], "canonical_key" => "person_yuli_tamir"],
    ["type" => "person", "label" => "פרופ' דן ענבר", "description" => "פרופסור ישראלי לחינוך. חוקר בתחום החינוך והפדגוגיה.", "flags" => ["academic"], "props" => ["notes" => "פרופסור לחינוך"], "canonical_key" => "person_dan_inbar"],
    ["type" => "person", "label" => "פרופ' מרים בן פרץ", "description" => "פרופסור ישראלי לחינוך. חוקרת בתחום החינוך והפדגוגיה.", "flags" => ["academic"], "props" => ["notes" => "פרופסור לחינוך"], "canonical_key" => "person_miriam_ben_peretz"],
    ["type" => "person", "label" => "פרופ' חיים אדלר", "description" => "פרופסור ישראלי לחינוך. חוקר בתחום החינוך והפדגוגיה.", "flags" => ["academic"], "props" => ["notes" => "פרופסור לחינוך"], "canonical_key" => "person_haim_adler"],
    ["type" => "person", "label" => "ד\"ר רונית כהן", "description" => "דוקטור לחינוך. חוקרת בתחום החינוך והפדגוגיה.", "flags" => ["academic"], "props" => ["notes" => "דוקטור לחינוך"], "canonical_key" => "person_ronit_cohen"],
    ["type" => "person", "label" => "ד\"ר אורית אידן", "description" => "דוקטור לחינוך. חוקרת בתחום החינוך והפדגוגיה.", "flags" => ["academic"], "props" => ["notes" => "דוקטור לחינוך"], "canonical_key" => "person_orit_idan"],
    ["type" => "person", "label" => "ד\"ר מיכל גרינשטיין", "description" => "דוקטור לחינוך. חוקרת בתחום החינוך והפדגוגיה.", "flags" => ["academic"], "props" => ["notes" => "דוקטור לחינוך"], "canonical_key" => "person_michal_grinstein"],
    ["type" => "person", "label" => "ד\"ר שרון אברהם", "description" => "דוקטור לחינוך. חוקר בתחום החינוך והפדגוגיה.", "flags" => ["academic"], "props" => ["notes" => "דוקטור לחינוך"], "canonical_key" => "person_sharon_avraham"],
    ["type" => "person", "label" => "ד\"ר טליה שגיא", "description" => "דוקטור לחינוך. חוקרת בתחום החינוך והפדגוגיה.", "flags" => ["academic"], "props" => ["notes" => "דוקטור לחינוך"], "canonical_key" => "person_talia_sagi"],
    ["type" => "term", "label" => "הכלה", "description" => "מושג חינוכי שמתייחס להכלת תלמידים שונים במערכת החינוך. מקודם כחלק מתוכניות SEL וחינוך רגשי-חברתי.", "flags" => ["problematic", "ideology"], "props" => ["notes" => "מושג חינוכי שמתייחס להכלת תלמידים שונים", "warnings" => "מושג בעייתי שמשמש לקידום ערכים אידאולוגיים"], "canonical_key" => "term_inclusion"],
    ["type" => "term", "label" => "רב-תרבותיות", "description" => "מושג שמתייחס להכרה ולכיבוד של תרבויות שונות במערכת החינוך. מקודם כחלק מתוכניות SEL וחינוך רגשי-חברתי.", "flags" => ["problematic", "ideology"], "props" => ["notes" => "מושג שמתייחס להכרה ולכיבוד של תרבויות שונות", "warnings" => "מושג בעייתי שמשמש לקידום ערכים אידאולוגיים"], "canonical_key" => "term_multiculturalism"],
    ["type" => "term", "label" => "אקלים חינוכי", "description" => "מושג שמתייחס לאווירה ולסביבה החינוכית בבית הספר. מקודם כחלק מתוכניות SEL וחינוך רגשי-חברתי.", "flags" => ["problematic", "ideology"], "props" => ["notes" => "מושג שמתייחס לאווירה ולסביבה החינוכית", "warnings" => "מושג בעייתי שמשמש לקידום ערכים אידאולוגיים"], "canonical_key" => "term_educational_climate"],
    ["type" => "term", "label" => "בטחון רגשי", "description" => "מושג שמתייחס לתחושת ביטחון רגשי של תלמידים במערכת החינוך. מקודם כחלק מתוכניות SEL וחינוך רגשי-חברתי.", "flags" => ["problematic", "ideology"], "props" => ["notes" => "מושג שמתייחס לתחושת ביטחון רגשי", "warnings" => "מושג בעייתי שמשמש לקידום ערכים אידאולוגיים"], "canonical_key" => "term_emotional_security"],
    ["type" => "term", "label" => "זהות", "description" => "מושג שמתייחס לזהות אישית ותרבותית של תלמידים. מקודם כחלק מתוכניות SEL וחינוך רגשי-חברתי.", "flags" => ["problematic", "ideology"], "props" => ["notes" => "מושג שמתייחס לזהות אישית ותרבותית", "warnings" => "מושג בעייתי שמשמש לקידום ערכים אידאולוגיים"], "canonical_key" => "term_identity"],
    ["type" => "concept", "label" => "חינוך הומניסטי", "description" => "גישה חינוכית שמתמקדת בפיתוח האדם השלם. מקודמת על ידי חוקרים אקדמיים ומוסדות חינוך.", "flags" => ["ideology", "academic"], "props" => ["notes" => "גישה חינוכית שמתמקדת בפיתוח האדם השלם"], "canonical_key" => "concept_humanistic_education"],
    ["type" => "concept", "label" => "חינוך דמוקרטי", "description" => "גישה חינוכית שמתמקדת בדמוקרטיה ובשיתוף תלמידים בתהליך החינוכי. מקודמת על ידי חוקרים אקדמיים ומוסדות חינוך.", "flags" => ["ideology", "academic"], "props" => ["notes" => "גישה חינוכית שמתמקדת בדמוקרטיה"], "canonical_key" => "concept_democratic_education"],
    ["type" => "concept", "label" => "חינוך ביקורתי", "description" => "גישה חינוכית שמבוססת על התיאוריה הביקורתית. מתמקדת בביקורת על מבני כוח בחברה ובחינוך.", "flags" => ["problematic", "ideology", "academic"], "props" => ["notes" => "גישה חינוכית שמבוססת על התיאוריה הביקורתית", "warnings" => "גישה בעייתית שמקדמת אידאולוגיה מרקסיסטית"], "canonical_key" => "concept_critical_education"],
    ["type" => "program", "label" => "תוכנית חוסן", "description" => "תוכנית חינוכית שמתמקדת בפיתוח חוסן נפשי של תלמידים. מקודמת במערכת החינוך בישראל.", "flags" => ["problematic", "ideology"], "props" => ["notes" => "תוכנית שמתמקדת בפיתוח חוסן נפשי", "warnings" => "תוכנית בעייתית שמקדמת ערכים אידאולוגיים"], "canonical_key" => "program_resilience"],
    ["type" => "program", "label" => "תוכנית רווחה רגשית", "description" => "תוכנית חינוכית שמתמקדת ברווחה רגשית של תלמידים. מקודמת במערכת החינוך בישראל.", "flags" => ["problematic", "ideology"], "props" => ["notes" => "תוכנית שמתמקדת ברווחה רגשית", "warnings" => "תוכנית בעייתית שמקדמת ערכים אידאולוגיים"], "canonical_key" => "program_emotional_wellbeing"],
    ["type" => "program", "label" => "תוכנית הכלה", "description" => "תוכנית חינוכית שמתמקדת בהכלת תלמידים שונים במערכת החינוך. מקודמת במערכת החינוך בישראל.", "flags" => ["problematic", "ideology"], "props" => ["notes" => "תוכנית שמתמקדת בהכלת תלמידים שונים", "warnings" => "תוכנית בעייתית שמקדמת ערכים אידאולוגיים"], "canonical_key" => "program_inclusion"],
    ["type" => "program", "label" => "תוכנית רב-תרבותיות", "description" => "תוכנית חינוכית שמתמקדת בהכרה ולכיבוד של תרבויות שונות. מקודמת במערכת החינוך בישראל.", "flags" => ["problematic", "ideology"], "props" => ["notes" => "תוכנית שמתמקדת בהכרה ולכיבוד של תרבויות שונות", "warnings" => "תוכנית בעייתית שמקדמת ערכים אידאולוגיים"], "canonical_key" => "program_multiculturalism"],
    ["type" => "event", "label" => "מורה קידם רעיונות בעייתיים בכיתה", "description" => "אירוע שבו מורה קידם רעיונות בעייתיים בכיתה. דווח בכתבות ותגובות.", "flags" => ["problematic"], "props" => ["notes" => "אירוע שבו מורה קידם רעיונות בעייתיים"], "canonical_key" => "event_teacher_promoted_problematic"],
    ["type" => "event", "label" => "תגובת עירייה לאירוע בעייתי", "description" => "תגובה של עירייה לאירוע בעייתי במערכת החינוך. דווח בכתבות.", "flags" => [], "props" => ["notes" => "תגובה של עירייה לאירוע בעייתי"], "canonical_key" => "event_municipality_response"],
    ["type" => "article", "label" => "כתבה על תוכנית SEL", "description" => "כתבה שדנה בתוכנית SEL במערכת החינוך בישראל.", "flags" => [], "props" => ["notes" => "כתבה על תוכנית SEL"], "canonical_key" => "article_sel_program"],
    ["type" => "article", "label" => "כתבה על יד הנדיב", "description" => "כתבה שדנה בקרן יד הנדיב והשפעתה על מערכת החינוך.", "flags" => [], "props" => ["notes" => "כתבה על יד הנדיב"], "canonical_key" => "article_handasiv"],
    ["type" => "article", "label" => "כתבה על שפ״י", "description" => "כתבה שדנה בשירות הפסיכולוגי ייעוצי והשפעתו על מערכת החינוך.", "flags" => [], "props" => ["notes" => "כתבה על שפ״י"], "canonical_key" => "article_shapy"],
    ["type" => "doc", "label" => "מסמך מדיניות SEL", "description" => "מסמך מדיניות על תוכנית SEL במערכת החינוך.", "flags" => [], "props" => ["notes" => "מסמך מדיניות על SEL"], "canonical_key" => "doc_sel_policy"],
    ["type" => "doc", "label" => "דוח מחקר על חוסן", "description" => "דוח מחקר על מושג החוסן במערכת החינוך.", "flags" => ["academic"], "props" => ["notes" => "דוח מחקר על חוסן"], "canonical_key" => "doc_resilience_research"],
    ["type" => "doc", "label" => "מסמך אונסקו על חינוך", "description" => "מסמך של אונסקו על מדיניות חינוכית.", "flags" => [], "props" => ["notes" => "מסמך אונסקו על חינוך"], "canonical_key" => "doc_unesco_education"],
    ["type" => "funding", "label" => "מימון תוכנית SEL", "description" => "מימון לתוכנית SEL במערכת החינוך.", "flags" => [], "props" => ["notes" => "מימון לתוכנית SEL"], "canonical_key" => "funding_sel_program"],
    ["type" => "funding", "label" => "מימון מחקר חינוכי", "description" => "מימון למחקר חינוכי.", "flags" => [], "props" => ["notes" => "מימון למחקר חינוכי"], "canonical_key" => "funding_education_research"],
    ["type" => "org", "label" => "מכון ברנקו וייס", "description" => "מכון מחקר והכשרה חינוכי. עוסק בהכשרת מורים ופיתוח תוכניות חינוכיות.", "flags" => ["academic"], "props" => ["notes" => "מכון מחקר והכשרה חינוכי"], "canonical_key" => "org_branco_weiss"],
    ["type" => "org", "label" => "מכון מנדל", "description" => "מכון מחקר והכשרה חינוכי. עוסק בהכשרת מנהיגות חינוכית ופיתוח תוכניות חינוכיות.", "flags" => ["academic"], "props" => ["notes" => "מכון מחקר והכשרה חינוכי"], "canonical_key" => "org_mandel_institute"],
    ["type" => "org", "label" => "מכון הרטמן", "description" => "מכון מחקר ישראלי. עוסק במחקר יהודי, פילוסופיה, וחינוך.", "flags" => ["academic"], "props" => ["notes" => "מכון מחקר ישראלי"], "canonical_key" => "org_hartman_institute"],
    ["type" => "org", "label" => "מכון שלום הרטמן", "description" => "מכון מחקר ישראלי. עוסק במחקר יהודי, פילוסופיה, וחינוך.", "flags" => ["academic"], "props" => ["notes" => "מכון מחקר ישראלי"], "canonical_key" => "org_shalom_hartman"],
    ["type" => "org", "label" => "מכון אבני", "description" => "מכון מחקר והכשרה חינוכי. עוסק בהכשרת מורים ופיתוח תוכניות חינוכיות.", "flags" => ["academic"], "props" => ["notes" => "מכון מחקר והכשרה חינוכי"], "canonical_key" => "org_avni_institute"],
    ["type" => "person", "label" => "פרופ' שלמה שרן", "description" => "פרופסור ישראלי לחינוך. חוקר בתחום החינוך והפדגוגיה.", "flags" => ["academic"], "props" => ["notes" => "פרופסור לחינוך"], "canonical_key" => "person_shlomo_sharan"],
    ["type" => "person", "label" => "פרופ' רבקה לזובסקי", "description" => "פרופסור ישראלי לחינוך. חוקרת בתחום החינוך והפדגוגיה.", "flags" => ["academic"], "props" => ["notes" => "פרופסור לחינוך"], "canonical_key" => "person_rivka_lazovsky"],
    ["type" => "person", "label" => "פרופ' דוד גורדון", "description" => "פרופסור ישראלי לחינוך. חוקר בתחום החינוך והפדגוגיה.", "flags" => ["academic"], "props" => ["notes" => "פרופסור לחינוך"], "canonical_key" => "person_david_gordon"],
    ["type" => "person", "label" => "פרופ' מרים רוזנברג", "description" => "פרופסור ישראלי לחינוך. חוקרת בתחום החינוך והפדגוגיה.", "flags" => ["academic"], "props" => ["notes" => "פרופסור לחינוך"], "canonical_key" => "person_miriam_rosenberg"],
    ["type" => "person", "label" => "פרופ' יורם הרפז", "description" => "פרופסור ישראלי לחינוך. חוקר בתחום החינוך והפדגוגיה.", "flags" => ["academic"], "props" => ["notes" => "פרופסור לחינוך"], "canonical_key" => "person_yoram_harpaz"],
    ["type" => "org", "label" => "קרן ג'נסיס", "description" => "קרן פילנתרופית בינלאומית. מממנת תוכניות חינוכיות ומחקרים בישראל.", "flags" => ["key_player"], "props" => ["notes" => "קרן פילנתרופית שמממנת תוכניות חינוכיות"], "canonical_key" => "org_genesis_foundation"],
    ["type" => "org", "label" => "קרן קיימת לישראל", "description" => "קרן פילנתרופית ישראלית. מממנת תוכניות חינוכיות ופרויקטים חברתיים.", "flags" => [], "props" => ["notes" => "קרן פילנתרופית ישראלית"], "canonical_key" => "org_kkf"],
    ["type" => "org", "label" => "קרן קרן", "description" => "קרן פילנתרופית ישראלית. מממנת תוכניות חינוכיות ופרויקטים חברתיים.", "flags" => [], "props" => ["notes" => "קרן פילנתרופית ישראלית"], "canonical_key" => "org_karen_foundation"],
    ["type" => "person", "label" => "ד\"ר רונית כהן-אלרן", "description" => "דוקטור לחינוך. חוקרת בתחום החינוך והפדגוגיה.", "flags" => ["academic"], "props" => ["notes" => "דוקטור לחינוך"], "canonical_key" => "person_ronit_cohen_elran"],
    ["type" => "person", "label" => "ד\"ר עמית שפירא", "description" => "דוקטור לחינוך. חוקר בתחום החינוך והפדגוגיה.", "flags" => ["academic"], "props" => ["notes" => "דוקטור לחינוך"], "canonical_key" => "person_amit_shapira"],
    ["type" => "person", "label" => "ד\"ר נועה אהרוני", "description" => "דוקטור לחינוך. חוקרת בתחום החינוך והפדגוגיה.", "flags" => ["academic"], "props" => ["notes" => "דוקטור לחינוך"], "canonical_key" => "person_noa_aharoni"],
    ["type" => "term", "label" => "רגישות תרבותית", "description" => "מושג שמתייחס לרגישות לתרבויות שונות במערכת החינוך. מקודם כחלק מתוכניות SEL וחינוך רגשי-חברתי.", "flags" => ["problematic", "ideology"], "props" => ["notes" => "מושג שמתייחס לרגישות לתרבויות שונות", "warnings" => "מושג בעייתי שמשמש לקידום ערכים אידאולוגיים"], "canonical_key" => "term_cultural_sensitivity"],
    ["type" => "term", "label" => "צדק חברתי", "description" => "מושג שמתייחס לצדק חברתי במערכת החינוך. מקודם כחלק מתוכניות SEL וחינוך רגשי-חברתי.", "flags" => ["problematic", "ideology"], "props" => ["notes" => "מושג שמתייחס לצדק חברתי", "warnings" => "מושג בעייתי שמשמש לקידום ערכים אידאולוגיים"], "canonical_key" => "term_social_justice"],
    ["type" => "concept", "label" => "פדגוגיה של דיכוי", "description" => "גישה חינוכית שמבוססת על התיאוריה הביקורתית. מתמקדת בביקורת על דיכוי בחברה ובחינוך.", "flags" => ["problematic", "ideology", "academic"], "props" => ["notes" => "גישה חינוכית שמבוססת על התיאוריה הביקורתית", "warnings" => "גישה בעייתית שמקדמת אידאולוגיה מרקסיסטית"], "canonical_key" => "concept_pedagogy_of_oppression"]
];

// בדיקה אם יש כפילויות
$existingKeys = [];
foreach ($existingNodes as $node) {
    if (!empty($node['canonical_key'])) {
        $existingKeys[$node['canonical_key']] = true;
    }
}

$nodesToAdd = [];
foreach ($newNodes as $node) {
    if (empty($node['canonical_key']) || !isset($existingKeys[$node['canonical_key']])) {
        $nodesToAdd[] = $node;
    }
}

echo "<p>מתוך 50 צמתים חדשים, " . count($nodesToAdd) . " יוספו (השאר כבר קיימים)</p>";

if (empty($nodesToAdd)) {
    echo "<p class='error'>כל הצמתים כבר קיימים בקובץ</p>";
} else {
    // הוספת הצמתים החדשים
    $allNodes = array_merge($existingNodes, $nodesToAdd);
    
    // כתיבה חזרה לקובץ
    $jsonOutput = json_encode($allNodes, JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT);
    file_put_contents($jsonFile, $jsonOutput);
    
    echo "<p class='success'>✓ נוספו " . count($nodesToAdd) . " צמתים חדשים</p>";
    echo "<p class='success'>✓ סה\"כ צמתים בקובץ: " . count($allNodes) . "</p>";
    echo "<p><a href='import_nodes.php'>← המשך לייבוא למסד הנתונים</a></p>";
}

echo "</body></html>";
?>






