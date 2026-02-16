# Data Manifest — דירוג ערים

Last updated: 2026-02-12

## RAW SOURCE DATA

### 1. Gefen Marketplace (Ministry of Education)
| File | Location | Size | Contents |
|------|----------|------|----------|
| `gefen_data.json` | `../דירוג ערים קודם/PROOCOLS/` | 83 MB | Full Gefen dump — 26,619 programs |
| `gefen_programs_excel.json` | `../דירוג ערים קודם/PROOCOLS/` | 8.5 MB | Parsed Excel — org names, summaries, categories |
| `gefen_programs.json` | `../דירוג ערים קודם/gefen3/gefen_test/` | ~5 MB | Per-city deployment stats for 200 programs across 20 cities |

### 2. Municipal Protocol PDFs
| City | Location | Files | Scanned |
|------|----------|-------|---------|
| באר שבע | `../דירוג ערים קודם/PROOCOLS/BEER_SHEVA/protocols/` | 1,027 | 97 |
| גבעתיים | `../דירוג ערים קודם/PROOCOLS/GIVATAYIM/protocols/` | 668 | 76 |
| אריאל | `../דירוג ערים קודם/PROOCOLS/ariel/protocols/` | 196 | 69 |
| אפרת | `../דירוג ערים קודם/PROOCOLS/EFRAT/protocols/` | 162 | 49 |
| כרמיאל | `../דירוג ערים קודם/PROOCOLS/KARMIEL/protocols/` | 162 | 49 |
| + 10 more cities | `../דירוג ערים קודם/PROOCOLS/{CITY}/protocols/` | varies | varies |

### 3. Policy Documents (Word)
| File | Location | Contents |
|------|----------|----------|
| `מודל הרצליה - תמצית מנהלים.docx` | `../דירוג ערים קודם/USED/` | Herzliya model methodology |
| `תכניות חינוכיות.docx` | `../דירוג ערים קודם/USED/` | Educational programs analysis |
| `שיתופי פעולה עירוניים.docx` | `../דירוג ערים קודם/USED/` | Municipal collaborations |
| `שקיפות.docx` | `../דירוג ערים קודם/USED/` | Transparency research |

### 4. WhatsApp Chat Exports
| Chat | Location | Date Range | Status |
|------|----------|------------|--------|
| יאיר, צבי ותובנות פילוספיות | `../whatsapp/imports/philosophy_chat.txt` | 4/11/23 → present | IMPORTED |
| OpenClaw self-chat | `../whatsapp/imports/openclaw_chat.txt` | 6/2/2026 → present | IMPORTED |
| מיגור מרקסיזם אנטישמי | NOT YET EXPORTED | ? | **PENDING EXPORT** |

### 5. External Links (Pending Access)
| Source | Link | Status |
|--------|------|--------|
| נווה מכון יכין — Google Drive | `drive.google.com/drive/folders/1L5WZI...` | **BLOCKED** — needs manual download |
| נווה מכון יכין — Looker Studio | `lookerstudio.google.com/...e17a2ce8...` | **BLOCKED** — requires auth |

---

## PROCESSED DATA

### 6. Program Scoring & Metadata
| File | Location | Contents |
|------|----------|----------|
| `manual_metadata.json` | `./` | 173 programs, dual-agent scored 1-5 |
| `gefen_metadata.json` | `./` | 4.8 MB, AI-generated summaries (Gemini) |
| `programs_subset.json` | `./` | 25 featured programs with rich data |
| `program_lookup.json` | `./` | 906 KB, program→city→school→budget index |

### 7. City-Level Analysis
| File | Location | Contents |
|------|----------|----------|
| `city_orientation_results.json` | `../דירוג ערים קודם/PROOCOLS/` | Pole A/B/red flag scores per city |
| `city_details.json` | `./` | Per-city protocol analysis details |
| `city_exposure_scores.json` | `../EDUCATIONAL_PROGRAMS/` | Budget-weighted exposure per city |
| `merged_city_ranking.json` | `./` | Final combined 0-100 ranking |

### 8. Organization Research
| File | Location | Contents |
|------|----------|----------|
| `acri_research.json` | `./` | ACRI deep dive (funding, leadership) |
| `research_efshar_acheret.json` | `./` | אפשר אחרת research |
| `research_lens.json` | `./` | 9-chapter framework + suspect clusters |

---

## ANALYSIS PRODUCTS

### 9. Research Reports (7 clusters)
| Report | Location | Risk |
|--------|----------|------|
| `Dror_Israel_network.md` | `./research_reports/` | 5/5 |
| `Gender_sexuality_education_providers.md` | `./research_reports/` | 4/5 |
| `Political-activist_orgs_in_education.md` | `./research_reports/` | 5/5 |
| `Hartman_Institute_network.md` | `./research_reports/` | 3/5 |
| `Foreign-funded_coexistence_pluralism.md` | `./research_reports/` | 4/5 |
| `Feminist_gender-agenda_orgs.md` | `./research_reports/` | 5/5 |
| `Child_protection_with_ideological_overlay.md` | `./research_reports/` | 4/5 |

### 10. Web Deliverables
| File | Location | Deploy |
|------|----------|--------|
| `index.html` | `./site/` | gh-pages: aosshalem-dev/derug-arim |
| `data.js` | `./site/` | 210 KB — rankings + 46 programs |
| `research_data.js` | `./site/` | 13 KB — 7 clusters + mappings |

---

## ANALYSIS SCRIPTS

| Script | Purpose | Input → Output |
|--------|---------|----------------|
| `merge_scores.py` | Combine protocols + education → ranking | city_orientation + exposure → merged_ranking + HTML |
| `research_suspicious.py` | Deep-dive org research (9 chapters) | research_lens.json → research_reports/*.md |
| `build_research_data.py` | Extract findings → JS data | research_reports/*.md → research_data.js |
| `values_analysis_agent.py` | 8-axis values rating | org name → 8 spectrum ratings |
| `city_orientation_scanner.py` | Protocol keyword analysis | protocol PDFs → city_orientation_results.json |
