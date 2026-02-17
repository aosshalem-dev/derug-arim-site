#!/usr/bin/env python3
"""
Google News RSS article scraper for city ranking project.
Scans 18 cities × 6 progressive topics, stores every article with full metadata.
Output: data/city_news_articles.json

Based on the V2 scanner methodology: municipality-anchored queries + title matching.
"""

import feedparser
import json
import time
import re
import urllib.parse
from datetime import datetime
from pathlib import Path

# 18 cities from data.js
CITIES = [
    {"name": "תל אביב-יפו", "key": "TEL_AVIV", "query_name": "תל אביב"},
    {"name": "רמת השרון", "key": "RAMAT_HASHARON", "query_name": "רמת השרון"},
    {"name": "הוד השרון", "key": "HOD_HASHARON", "query_name": "הוד השרון"},
    {"name": "גבעתיים", "key": "GIVATAYIM", "query_name": "גבעתיים"},
    {"name": "חיפה", "key": "HAIFA", "query_name": "חיפה"},
    {"name": "כרמיאל", "key": "KARMIEL", "query_name": "כרמיאל"},
    {"name": "באר שבע", "key": "BEER_SHEVA", "query_name": "באר שבע"},
    {"name": "רעננה", "key": "RAANANA", "query_name": "רעננה"},
    {"name": "הרצליה", "key": "HERZLIYA", "query_name": "הרצליה"},
    {"name": "קרית אונו", "key": "KIRYAT_ONO", "query_name": "קרית אונו"},
    {"name": "גבעת שמואל", "key": "GIVAT_SHMUEL", "query_name": "גבעת שמואל"},
    {"name": "קרית שמונה", "key": "KIRYAT_SHMONA", "query_name": "קרית שמונה"},
    {"name": "ראש העין", "key": "ROSH_HAAYIN", "query_name": "ראש העין"},
    {"name": "אריאל", "key": "ARIEL", "query_name": "אריאל עיר"},
    {"name": "קרית גת", "key": "KIRYAT_GAT", "query_name": "קרית גת"},
    {"name": "אפרת", "key": "EFRAT", "query_name": "אפרת מועצה"},
    {"name": "נתיבות", "key": "NETIVOT", "query_name": "נתיבות"},
    {"name": "עמנואל", "key": "EMANUEL", "query_name": "עמנואל מועצה"},
]

# 6 topic categories with Hebrew search terms
TOPICS = {
    "lgbt_municipal": {
        "label": "להט\"ב ומוניציפלי",
        "terms": ["להטב", "גאווה", "הומופוביה", "טרנס", "קהילה גאה"]
    },
    "gender_equality": {
        "label": "שוויון מגדרי",
        "terms": ["שוויון מגדרי", "נשים בהנהגה", "אלימות נגד נשים", "הטרדה מינית", "פמיניזם"]
    },
    "sustainability": {
        "label": "קיימות",
        "terms": ["קיימות", "אנרגיה ירוקה", "מיחזור", "אקלים", "סביבה עירונית"]
    },
    "social_justice": {
        "label": "צדק חברתי",
        "terms": ["צדק חברתי", "אי-שוויון", "דיור בר השגה", "עוני", "זכויות עובדים"]
    },
    "diversity_inclusion": {
        "label": "גיוון והכלה",
        "terms": ["גיוון", "הכלה", "רב-תרבותיות", "דו-קיום", "שילוב חברתי"]
    },
    "education_progressive": {
        "label": "חינוך פרוגרסיבי",
        "terms": ["חינוך דמוקרטי", "חינוך ביקורתי", "חינוך פלורליסטי", "חינוך לשוויון"]
    }
}

def fetch_google_news_rss(query, max_results=50):
    """Fetch articles from Google News RSS for a Hebrew query."""
    encoded = urllib.parse.quote(query)
    url = f"https://news.google.com/rss/search?q={encoded}&hl=iw&gl=IL&ceid=IL:he"

    feed = feedparser.parse(url)
    articles = []

    for entry in feed.entries[:max_results]:
        # Extract source from title (Google News format: "Title - Source")
        title = entry.get("title", "")
        source = ""
        if " - " in title:
            parts = title.rsplit(" - ", 1)
            title = parts[0]
            source = parts[1] if len(parts) > 1 else ""

        # Parse date
        published = entry.get("published", "")

        articles.append({
            "title": title,
            "url": entry.get("link", ""),
            "source": source,
            "published": published,
            "query": query
        })

    return articles


def title_matches_city(title, city_query_name):
    """Check if article title is relevant to the city (municipality-anchored)."""
    # The title must mention the city name to be relevant
    city_variants = [city_query_name]
    # Add common suffixes/variants
    if "תל אביב" in city_query_name:
        city_variants.extend(["תל אביב", "תל-אביב", "ת\"א"])
    if "באר שבע" in city_query_name:
        city_variants.extend(["באר שבע", "באר-שבע", "ב\"ש"])
    if "קרית" in city_query_name:
        city_variants.append(city_query_name.replace("קרית", "קריית"))

    for variant in city_variants:
        if variant in title:
            return True
    return False


def scan_city_topic(city, topic_key, topic_info):
    """Scan a single city × topic combination. Returns list of relevant articles."""
    city_name = city["query_name"]
    all_articles = []
    seen_urls = set()

    for term in topic_info["terms"]:
        query = f"{city_name} עירייה {term}"
        try:
            articles = fetch_google_news_rss(query)
            for article in articles:
                # Deduplicate by URL
                if article["url"] in seen_urls:
                    continue
                # Municipality-anchored: title must mention the city
                if title_matches_city(article["title"], city_name):
                    article["topic"] = topic_key
                    article["topic_label"] = topic_info["label"]
                    article["matched_term"] = term
                    all_articles.append(article)
                    seen_urls.add(article["url"])
        except Exception as e:
            print(f"  Error fetching {city_name}/{term}: {e}")

        # Rate limiting
        time.sleep(0.5)

    return all_articles


def main():
    output_path = Path(__file__).parent.parent / "data" / "city_news_articles.json"
    output_path.parent.mkdir(exist_ok=True)

    result = {
        "metadata": {
            "generated": datetime.now().isoformat(),
            "methodology": "Google News RSS + feedparser, municipality-anchored queries with title matching",
            "cities": len(CITIES),
            "topics": list(TOPICS.keys()),
            "source": "Google News Hebrew (news.google.com, hl=iw, gl=IL)"
        },
        "cities": {}
    }

    total_articles = 0

    for i, city in enumerate(CITIES):
        print(f"\n[{i+1}/{len(CITIES)}] Scanning {city['name']} ({city['key']})...")
        city_data = {
            "city_name": city["name"],
            "city_key": city["key"],
            "topics": {},
            "total_articles": 0
        }

        for topic_key, topic_info in TOPICS.items():
            print(f"  Topic: {topic_info['label']}...", end=" ", flush=True)
            articles = scan_city_topic(city, topic_key, topic_info)
            city_data["topics"][topic_key] = {
                "label": topic_info["label"],
                "count": len(articles),
                "articles": articles
            }
            print(f"{len(articles)} articles")
            city_data["total_articles"] += len(articles)

        result["cities"][city["key"]] = city_data
        total_articles += city_data["total_articles"]

        # Save incrementally after each city
        with open(output_path, "w", encoding="utf-8") as f:
            json.dump(result, f, ensure_ascii=False, indent=2)

        print(f"  Total for {city['name']}: {city_data['total_articles']} articles")

    result["metadata"]["total_articles"] = total_articles

    # Final save
    with open(output_path, "w", encoding="utf-8") as f:
        json.dump(result, f, ensure_ascii=False, indent=2)

    print(f"\n=== DONE ===")
    print(f"Total articles: {total_articles}")
    print(f"Output: {output_path}")


if __name__ == "__main__":
    main()
