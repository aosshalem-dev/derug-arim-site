# Idea Map: derug-arim-site

## 1. THE CORE THESIS

```
Central claim:
Progressive ideology is entering Israeli schools through programs
that use DOUBLE LANGUAGE — terms with an innocent surface meaning
and a documented hidden meaning — delivered via OPACITY BY DESIGN.
```

This is not a conspiracy theory. The site's argument is:
- The hidden meanings are **documented by the practitioners themselves**
  (National Academy of Sciences, CASEL, Freire, Katakol-Ayali)
- The opacity is **observable** (paywalled content, no parent access)
- The funding is **traceable** (GuideStar, obudget.org, Gefen catalog)

The site asks: "If these are just normal programs, why the opacity?"


## 2. IDEA DEPENDENCY TREE

```
                    DOUBLE LANGUAGE (Rosetta Stone)
                           |
          +----------------+----------------+
          |                |                |
    CRITICAL PEDAGOGY   CSE/GENDER      SEL FRAMEWORK
    (Freire, Marx)     (UNESCO/IPPF)    (CASEL 2020+)
          |                |                |
          +-------+--------+--------+-------+
                  |                  |
           ACTIVIST PEDAGOGY    RIGHTS FRAMING
           (teachers as agents) (contested = universal)
                  |                  |
                  +--------+---------+
                           |
                   INSTITUTIONAL CAPTURE
                           |
              +------------+------------+
              |            |            |
         MUNICIPALITY   MINISTRY    CIVIL SOCIETY
         (protocols)   (Gefen/funding)  (NIF/NGOs)
              |            |            |
              +------------+------------+
                           |
                    OPACITY BY DESIGN
                    (the thing parents see)
                           |
                    THIS SITE EXISTS TO
                    MAKE IT VISIBLE
```


## 3. THE 7 CONCEPTS — How They Build on Each Other

### Layer 1: The Academic Foundation
```
CRITICAL PEDAGOGY ──────> basis for everything
  Source: Paulo Freire (Pedagogy of the Oppressed)
  Israeli adoption: National Academy of Sciences (2022) explicitly says:
    "critical pedagogy = Marxism, neo-Marxism, post-colonialism"
  NOT disputed — these are the proponents' own words
  Implemented by: Dror Israel (16+ programs, 234M NIS)
```

### Layer 2: The Delivery Mechanisms
```
CRITICAL PEDAGOGY branches into 3 delivery vehicles:

  SEL (Social-Emotional Learning)
    Surface: empathy, self-awareness, responsible decisions
    Hidden: CASEL 2020 "transformative SEL" = "dismantle oppressive structures"
    Israeli: National Academy (2020) says SEL requires choosing between
             "neoliberalism" and "critical democracy"
    Programs: SafeSchool (838 schools), Matzmichim (348 schools)

  CSE / SEXUALITY + GENDER
    Surface: healthy relationships, body safety, consent
    Hidden: IPPF/UNESCO framework teaching gender spectrum, identity
    Israeli: SEI (self-accrediting body) → Meyda Amin (sole proprietor,
             40 hidden facilitators, 51 schools, K-12 spiral curriculum)
    International backlash: Cass Review UK 2024, Safe Schools AU shut 2017

  ACTIVISM
    Surface: civic engagement, youth leadership
    Hidden: "teachers as agents of social change" (Katakol-Ayali 2020)
    Evidence: Same model published twice — 2020 "Activist Pedagogy"
              (English, academic), 2024 "Political Education in Crisis"
              (Hebrew, softened for Ministry)
    Quote: Michaeli: "I could organize a communist coup in a classroom
           and no one would feel it"
```

### Layer 3: The Framing Tools
```
RIGHTS
  Takes contested political positions → frames as universal human rights
  "If you oppose our program, you oppose human rights"
  ACRI adopted "apartheid" framing 2008 — shift from rights to politics

COEXISTENCE
  Surface: Jewish-Arab dialogue, shared society
  Hidden: "state of all its citizens" (post-national, de-Zionist)
  Yad BYad: $9M/year US funding, zero Israeli gov, dual narrative
  (Nakba + Independence Day in same curriculum)
```


## 4. EVIDENCE ARCHITECTURE — 5 Tiers

```
TIER A: HUMAN REVIEW (strongest)
  181 programs reviewed directly by researcher (Zvi)
  Each gets: rating (1-5), research_desc, research_notes
  Stored in: human_reviewed_programs.json → data.js worst_programs

TIER B: AI ANALYSIS (supporting)
  2,860 Gefen programs → Gemini analysis
  Each gets: riskScore (1-10), ideologyMarkers, fundingSignals,
             leadPeople, partnerships, evidence, sources
  99 with full analysis, 102 with website URLs
  Stored in: gefen_metadata.json → enriched into data.js program_info

TIER C: ORGANIZATIONAL RESEARCH (deep)
  31 organizations investigated
  48 deep research HTML reports
  Sources: GuideStar IL+US, obudget.org, news archives, org websites
  Each gets: risk level, thesis, key findings, funding sources,
             neutrality/transparency scores
  Stored in: research_orgs.js, research_data.js, research/*.html

TIER D: MUNICIPAL PROTOCOLS (institutional capture)
  537 protocols scanned across 20 cities
  23 verified findings (4.3% hit rate)
  Categories: gender committees, education budgets, coalition agreements
  Stored in: protocol_findings.js, protocols.js

TIER E: NEWS SIGNALS (weakest, contextual)
  5,095 articles across 18 cities on 6 topics
  Pre-computed keyword density scoring
  Stored in: data/news/*.json
```


## 5. CONTENT MAP — What Exists Where

### Research Reports (48 deep dives)
```
research/
  dror_israel_deep_research.html ──── the flagship report
  dror_batei_chinuch_deep_research.html
  hanoar_haoved_deep_research.html
  sei_deep_research.html ──── sex ed self-accrediting body
  meyda_amin_al_min_deep_research.html ──── sole proprietor, 40 facilitators
  maga_betiaum_deep_research.html ──── revolving door ministry→contractor
  choshen_deep_research.html ──── LGBTQ+ education
  acri_deep_research.html ──── rights→politics shift
  yad_byad_deep_research.html ──── $9M/year coexistence
  matzmichim_deep_research.html ──── violence prevention + SEL
  ... (48 total)
```

### External Linked Sites
```
progressive-rosetta-stone ──── decodes double meanings term by term
sel-research ──── deep dive on SEL framework
shefi-research ──── gender + sexuality in Ministry structure
```

### Data Files
```
data.js ──────────────── 2,856 programs, 20 cities, scores
research_orgs.js ──────── 31 investigated organizations
research_data.js ──────── research clusters + methodology
suspicious_concepts.js ── 7 concepts + evidence + program mapping
protocol_findings.js ──── 23 verified municipal findings
protocols.js ──────────── raw protocol data
data/gefen_metadata.json ─ 2,860 programs from Ministry catalog
data/news/*.json ────────── 5,095 news articles by city
data/human_reviewed_programs.json ── 181 human reviews
```


## 6. APP STRUCTURE — What the User Sees

```
TAB 1: RANKING TABLE (default)
  20 cities ranked by evidence score (0-100)
  Click city → expands:
    ├── Proven programs (Tier A+B data)
    │     Click program → expands:
    │       ├── Full Gefen description
    │       ├── Gemini risk score + justification
    │       ├── Ideology markers (colored badges)
    │       ├── Key people + suspicion reasons
    │       ├── Funding signals
    │       ├── Partnerships
    │       ├── Evidence + source links
    │       ├── Suspicion concept badges [SEL] [gender] ...
    │       │     Click badge → modal:
    │       │       ├── Surface meaning (green)
    │       │       ├── Hidden meaning (red)
    │       │       ├── Quotes with sources
    │       │       └── Links to Rosetta Stone / research sites
    │       ├── Suspicion dossier (org problems, NIF, missing data)
    │       └── Clearance section (how org can clear suspicion)
    ├── Org profiles (from research_orgs.js)
    ├── Protocol findings (verified municipal decisions)
    └── News signals (article counts by topic)

TAB 2: EDUCATION PROGRAMS
  All worst_programs sorted by rating
  Same card expansion as above

TAB 3: PROTOCOLS
  Browse municipal protocols + verified findings
  Category filter, city filter

TAB 4: RED FLAGS
  Keyword analysis of protocol language

TAB 5: RESEARCH
  Research clusters + deep dive reports
  Links to 48 HTML research reports

TAB 6: BACKGROUND
  Methodology explanation
  Key quotes (Michaeli, Katakol-Ayali)
  The Rosetta Stone concept explained
  Links to external research sites
```


## 7. WHAT'S NOT CONNECTED (Gaps)

### Ideas Not Yet on the Site
```
- The CONSCIOUSNESS ENGINEERING argument (Hagit Elkaim/NIF quote exists
  in dossier but not as standalone concept)
- The REVOLVING DOOR pattern (Ministry staff → program operators)
  documented in research reports but not surfaced as concept
- The SELF-ACCREDITATION loop (SEI certifies its own members)
  in research reports but not a suspicion concept
- The FOREIGN FUNDING dimension (NIF, EU, US foundations)
  partially in dossier but no systematic map
```

### Data That Exists but Isn't Rendered
```
- partnerships field: NOW RENDERED (just fixed)
- 48 research reports: linked from org profiles but not from program cards
- news/*.json: signal scores shown but individual articles not browsable
- protocol full text: exists in protocols.js but only summaries shown
- gefen description field (gp.description): 2,814 programs have it,
  but only shown if no gemini_summary exists (fallback)
```

### Philosophical Arguments That Need Evidence
```
- "Same model, different packaging" (Katakol-Ayali 2020 vs 2024):
  both documents should be linkable/viewable on site
- The NIF → org → school pipeline: needs visual flow diagram
- "Opacity by design" claim: needs comparison table
  (what parents see vs. what actually happens)
- Municipal capture timeline: when did each city adopt?
```


## 8. HOW IDEAS MAP TO SCORING

```
EVIDENCE SCORE (0-100) = weighted sum of:
  ├── Education pillar (proven programs)
  │     Based on: Tier A human reviews + Tier B org risk inheritance
  │     Weight: dominant factor
  ├── Protocol pillar (municipal decisions)
  │     Based on: Tier D verified findings
  │     Weight: significant for cities with protocols
  ├── Red flags (keyword density)
  │     Based on: protocol keyword scanning
  │     Weight: minor, contextual
  └── News signals
        Based on: Tier E article counts
        Weight: minor, contextual

PROGRAM RATING (1-5) = human assessment of:
  5 = explicit ideological content documented
  4 = strong indicators + org connection
  3 = moderate indicators
  2 = low but present
  1 = minimal concern

CONCEPT DETECTION = 3-layer:
  1. Explicit mapping (27 programs → concepts, from researcher)
  2. Name-based ID resolution (program name → program_info → mapping)
  3. Keyword regex fallback (7 patterns scan description text)
```
