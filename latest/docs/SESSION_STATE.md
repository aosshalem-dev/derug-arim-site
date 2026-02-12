# Session State — דירוג ערים
Last saved: 2026-02-12

---

## COMPLETED THIS SESSION

### 1. Program Data Enrichment
- Enriched 20/20 programs in `site/data.js` with full GaPaN summaries from `gefen_programs_excel.json`
- Script: `/private/tmp/enrich_programs_v2.py`
- Cache bumped v=8 → v=9 in `site/index.html`
- Committed: `7029ef9`
- Deployed to GitHub Pages: aosshalem-dev/derug-arim

### 2. Documentation Created
- **`DATA_MANIFEST.md`** — Complete inventory of all raw data, processed data, analysis products, and scripts
- **`METHODOLOGY.md`** — Full analysis framework: combined score formula, program rating rubric, Pole A/B keywords, 9-chapter framework, 8-axis values spectrum, known gaps

### 3. Statistical Computations (saved to `/private/tmp/methodology_computations.json`)
- **Inter-rater reliability**: 172 program pairs, Pearson r=0.6748, Cohen's weighted κ=0.6283 (substantial), 58% exact agreement, 91% within-1-point
- **Normalization ceilings**: Pole B recommended ceiling 1.947 (P95×1.5), Red Flag ceiling 0.149 (P95×1.5) — close to current 3.0 and 0.15
- **Coverage analysis**: 4 high (>30%), 3 medium (15-30%), 8 low (<15%) confidence cities
- **Sensitivity analysis**: Rankings robust — no city moves >2 positions across 4 weight schemes (current 50/30/20, equal 33/33/33, education-heavy 70/20/10, protocol-heavy 30/50/20)

### 4. WhatsApp Chat Downloaded
- Email from shalem1492@gmail.com with "צ'אט WhatsApp עם מיגור מרקסיזם אנטישמי"
- Zip saved to: `~/Library/CloudStorage/Dropbox/persistent-team/projects/whatsapp/imports/‏צ'אט WhatsApp עם מיגור מרקסיזם אנטישמי.zip` (15.7MB)
- Contains: chat text (~528KB), images (JPGs), voice notes (OPUS), a PDF, 2 VCF contacts
- **NOT YET EXTRACTED** — user rejected the unzip step; needs to be done next session

---

## PENDING TASKS (from task tracker #16-#21)

### Task #16: Inter-rater reliability — COMPUTED, needs integration into METHODOLOGY.md
### Task #17: Normalization ceilings — COMPUTED, needs integration into METHODOLOGY.md
### Task #18: Qual→quant conversion formula (8-axis → 1-5 risk score) — NOT YET DONE
### Task #19: Coverage-adjusted confidence for city scores — COMPUTED, needs integration
### Task #20: Weighting justification (why 50/30/20) — NOT YET WRITTEN (sensitivity analysis supports it)
### Task #21: Update METHODOLOGY.md with all computed results — NOT YET DONE (blocked by above)

### User Request: Source Names Registry
- User said: "keep list of sources names to make sure they don't escape from existing data revenue"
- Need to create a comprehensive list of all data source names (orgs, platforms, groups, government sources)
- Purpose: tracking checklist so no source is missed in future analysis
- NOT YET STARTED

---

## KEY FILES

| File | Status | Notes |
|------|--------|-------|
| `site/data.js` | Updated | 46 programs, all enriched, cache v=9 |
| `site/index.html` | Updated | Cache v=9, tabbed navigation |
| `site/research_data.js` | Stable | 7 research clusters |
| `DATA_MANIFEST.md` | New | Complete data inventory |
| `METHODOLOGY.md` | New, needs update | Has gaps marked; computed results ready to integrate |
| `research_lens.json` | Stable | 9-chapter framework + 7 suspect clusters |
| `merge_scores.py` | Stable | Combined score formula (50/30/20) |
| `manual_metadata.json` | Stable | 173 programs, dual-agent scored |
| `/private/tmp/methodology_computations.json` | Temporary! | IRR, ceilings, coverage, sensitivity — COPY TO PROJECT DIR |

### IMPORTANT: `/private/tmp/methodology_computations.json` is in /tmp and will be lost on reboot!
Copy it to the project directory on next session start.

---

## BLOCKED ITEMS

- **נווה מכון יכין data**: Google Drive folder + Looker Studio dashboard — require authentication, manual download needed
- **WhatsApp extraction**: User rejected unzip; ask before proceeding next time

---

## DEPLOY INFO

- **GitHub Pages**: aosshalem-dev/derug-arim
- **Deploy dir**: `/private/tmp/derug-arim-deploy/` (also in /tmp — will be lost!)
- **Deploy flow**: Edit `site/` files → sync to deploy dir → `cd deploy-dir && git add . && git commit && git push`
- **Last commit on gh-pages**: enriched 20 programs with GaPaN summaries

---

## NEXT SESSION PRIORITIES

1. **URGENT**: Copy `/private/tmp/methodology_computations.json` to project dir before it's lost
2. Extract WhatsApp zip (ask user first)
3. Create sources registry (user request)
4. Integrate computed results into METHODOLOGY.md (tasks #16-#21)
5. Write qual→quant conversion formula (#18)
6. Write weighting justification (#20)
