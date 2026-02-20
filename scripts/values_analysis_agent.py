#!/usr/bin/env python3
"""
Values Analysis Agent - Chapter 8
Analyzes organizations across 8 value axes using Claude Sonnet.
"""

import anthropic
import json
import sys
import os
from datetime import datetime

# Get API key from environment
api_key = os.environ.get("ANTHROPIC_API_KEY")
if not api_key:
    print("Error: ANTHROPIC_API_KEY not set in environment")
    sys.exit(1)

client = anthropic.Anthropic(api_key=api_key)

VALUE_AXES = [
    {
        "axis": "זהות יהודית ומורשת",
        "spectrum": "מחזק ↔ מחליש/מאוניברסל",
        "questions": [
            "האם הארגון מחזק את הזהות היהודית והמורשת היהודית?",
            "האם יש דגש על יהדות, תרבות יהודית, מסורת?",
            "או שמא יש נטייה לאוניברסליזם, השוואה בין כל התרבויות?"
        ]
    },
    {
        "axis": "זהות לאומית ציונית",
        "spectrum": "מאשר ↔ פוסט-לאומי/ביקורתי",
        "questions": [
            "האם הארגון מחזק את הזהות הלאומית הציונית?",
            "האם הוא מדבר על 'ישראל כמדינת כל אזרחיה', דו-קיום, שוויון בין ערבים ליהודים?",
            "האם יש ביקורת על הנרטיב הציוני או השימוש במושגים פוסט-ציוניים?"
        ]
    },
    {
        "axis": "מבנה משפחה וקהילה",
        "spectrum": "תומך ↔ מפרק",
        "questions": [
            "האם הארגון תומך במבני משפחה מסורתיים?",
            "האם יש דגש על 'העצמה אישית' מול 'קהילה ומשפחה'?",
            "האם יש דגש על פרוק מבנים מסורתיים?"
        ]
    },
    {
        "axis": "צה\"ל ושירות לאומי",
        "spectrum": "תומך ↔ ביקורתי/סרבנות מצפונית",
        "questions": [
            "האם הארגון תומך בשירות בצה\"ל ושירות לאומי?",
            "האם יש ביקורת על הצבא או קידום של סרבנות מצפונית?",
            "האם הוא מדבר על 'מיליטריזציה' או 'השפעת הצבא על החברה' באופן שלילי?"
        ]
    },
    {
        "axis": "ערכים דמוקרטיים",
        "spectrum": "דמוקרטיה פרוגרסיבית ↔ דמוקרטיה רדיקלית/אקטיביזם",
        "questions": [
            "האם הארגון פועל בתוך המסגרת הדמוקרטית המסורתית?",
            "או שמא הוא קורא לשינוי רדיקלי, אקטיביזם, מחאה?",
            "האם יש שימוש במונחים כמו 'צדק חברתי', 'equity', 'דה-קולוניזציה'?"
        ]
    },
    {
        "axis": "כלכלה ואוריינטציה כלכלית",
        "spectrum": "שוק חופשי ↔ סוציאליסטי/קולקטיביסטי",
        "questions": [
            "האם הארגון מקדם ערכים של יזמות, קפיטליזם, שוק חופשי?",
            "או שמא יש נטייה לשוויון כלכלי, חלוקה מחדש, ביקורת על 'ניאו-ליברליזם'?"
        ]
    },
    {
        "axis": "מגדר ומיניות",
        "spectrum": "מסורתי ↔ תיאוריית מגדר רדיקלית",
        "questions": [
            "האם הארגון מקדם חינוך מגדרי רדיקלי?",
            "האם יש דגש על 'זהות מגדרית', 'ביטוי מגדרי', 'פירוק בינריות מגדרית'?",
            "או שמא הוא מקדם שוויון בין גברים לנשים תוך שמירה על הבדלים ביולוגיים?"
        ]
    },
    {
        "axis": "השפעה זרה",
        "spectrum": "עצמאי ↔ מומן זר עם אג'נדה",
        "questions": [
            "מהם מקורות המימון של הארגון?",
            "האם יש מימון זר משמעותי (NIF, Ford Foundation, וכו')?",
            "האם הארגון שקוף לגבי מקורות המימון שלו?"
        ]
    }
]

def analyze_organization(org_name: str, org_data: str) -> dict:
    """
    Analyze an organization across 8 value axes.

    Args:
        org_name: Name of the organization in Hebrew
        org_data: Background information about the organization

    Returns:
        dict with analysis results
    """

    prompt = f"""אתה חוקר חינוכי ישראלי המנתח ארגונים חינוכיים לפי 8 צירי ערכים.

# ארגון לניתוח: {org_name}

# רקע על הארגון:
{org_data}

# המשימה שלך:
דרג את הארגון על כל אחד מ-8 צירי הערכים הבאים, תוך שימוש בסולם של 1-5:
- 1 = מאוד מהצד הפרוגרסיבי של הספקטרום
- 2 = פרוגרסיבי-מתון
- 3 = מרכז/ניטרלי
- 4 = ימני-מתון
- 5 = מאוד מימין הספקטרום

# צירי הערכים:

{json.dumps(VALUE_AXES, ensure_ascii=False, indent=2)}

# פורמט התשובה:
עבור כל ציר ערכים, ספק:
1. דירוג מספרי (1-5)
2. הסבר מפורט (2-3 משפטים)
3. ציטוט ישיר או עובדה ספציפית לתמיכה (אם זמין)
4. רמת הוודאות: גבוהה/בינונית/נמוכה

החזר JSON בפורמט הבא:
{{
  "organization": "{org_name}",
  "analysis_date": "{datetime.now().isoformat()}",
  "axes": [
    {{
      "axis_name": "זהות יהודית ומורשת",
      "rating": 3,
      "explanation": "...",
      "evidence": "...",
      "certainty": "בינונית"
    }},
    ...
  ],
  "overall_values_profile": "סיכום כולל של פרופיל הערכים של הארגון (3-4 משפטים)",
  "key_concerns": ["דאגה 1", "דאגה 2", ...],
  "sources_needed": ["מקור נוסף שצריך לחפש 1", ...]
}}

חשוב: היה אובייקטיבי. דרג על סמך עובדות ומקורות, לא על סמך הנחות.
"""

    message = client.messages.create(
        model="claude-sonnet-4-20250514",
        max_tokens=4000,
        temperature=0,
        messages=[
            {"role": "user", "content": prompt}
        ]
    )

    # Extract JSON from response
    response_text = message.content[0].text

    # Try to parse JSON from response
    try:
        # Look for JSON block
        if "```json" in response_text:
            json_start = response_text.find("```json") + 7
            json_end = response_text.find("```", json_start)
            json_text = response_text[json_start:json_end].strip()
        elif "{" in response_text and "}" in response_text:
            json_start = response_text.find("{")
            json_end = response_text.rfind("}") + 1
            json_text = response_text[json_start:json_end]
        else:
            json_text = response_text

        result = json.loads(json_text)
        return result
    except json.JSONDecodeError as e:
        print(f"Error parsing JSON: {e}")
        print(f"Response text: {response_text}")
        return {
            "organization": org_name,
            "error": "Failed to parse JSON",
            "raw_response": response_text
        }

def main():
    if len(sys.argv) < 3:
        print("Usage: python values_analysis_agent.py <org_name> <org_data_file>")
        sys.exit(1)

    org_name = sys.argv[1]
    org_data_file = sys.argv[2]

    # Read organization data
    with open(org_data_file, 'r', encoding='utf-8') as f:
        org_data = f.read()

    print(f"Analyzing {org_name}...\n")

    result = analyze_organization(org_name, org_data)

    # Save result
    output_file = f"values_analysis_{org_name.replace(' ', '_').replace('/', '_')}.json"
    with open(output_file, 'w', encoding='utf-8') as f:
        json.dump(result, f, ensure_ascii=False, indent=2)

    print(f"\n✓ Analysis complete. Results saved to: {output_file}")

    # Print summary
    if "axes" in result:
        print("\n=== Summary ===")
        for axis in result["axes"]:
            print(f"{axis['axis_name']}: {axis['rating']}/5 - {axis['certainty']}")

if __name__ == "__main__":
    main()
