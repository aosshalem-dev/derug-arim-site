/**
 * Enriches data.js program_info with full data from gefen_metadata.json.
 * - Updates existing program_info entries with Gemini analysis
 * - Creates NEW program_info entries for gefen programs that have useful data
 * Run: node scripts/enrich_program_info.js
 */
var fs = require("fs");
var path = require("path");

var gefen = require(path.join(__dirname, "..", "data", "gefen_metadata.json"));
var byNumber = {};
gefen.data.forEach(function(p) { if (p.program_number) byNumber[p.program_number] = p; });

var dataPath = path.join(__dirname, "..", "data.js");
var dataCode = fs.readFileSync(dataPath, "utf8").replace(/^const DATA/, "var DATA");
eval(dataCode);

var stats = { updated: 0, created: 0, gemini: 0, people: 0, links: 0, ideology: 0, funding: 0 };

// Helper: enrich a program_info entry from a gefen record
function enrichFromGefen(info, gp) {
  // Full Gemini description
  if (gp.gemini_summary) {
    info.gefen_description = gp.gemini_summary;
    stats.gemini++;
  }

  // Basic gefen description (fallback if no gemini_summary)
  if (!info.gefen_description && gp.description) {
    info.gefen_description = gp.description;
  }

  // Parse gemini_analysis for structured data
  if (gp.gemini_analysis) {
    try {
      var a = typeof gp.gemini_analysis === "string" ? JSON.parse(gp.gemini_analysis) : gp.gemini_analysis;

      if (a.riskScore !== undefined) info.gemini_risk_score = a.riskScore;
      if (a.riskJustification) info.gemini_risk_justification = a.riskJustification;

      if (a.ideologyMarkers) {
        var markers = [];
        if (a.ideologyMarkers.highRisk) a.ideologyMarkers.highRisk.forEach(function(m) { markers.push({ level: "high", text: m }); });
        if (a.ideologyMarkers.mediumRisk) a.ideologyMarkers.mediumRisk.forEach(function(m) { markers.push({ level: "medium", text: m }); });
        if (a.ideologyMarkers.lowRisk) a.ideologyMarkers.lowRisk.forEach(function(m) { markers.push({ level: "low", text: m }); });
        if (markers.length > 0) { info.ideology_markers = markers; stats.ideology++; }
      }

      if (a.fundingSignals && a.fundingSignals.length > 0) {
        info.funding_signals = a.fundingSignals;
        stats.funding++;
      }

      if (a.partnerships && a.partnerships.length > 0) info.partnerships = a.partnerships;
      if (a.evidence && a.evidence.length > 0) info.gemini_evidence = a.evidence.slice(0, 5);
      if (a.sources && a.sources.length > 0) {
        info.gemini_sources = a.sources.slice(0, 5).map(function(s) {
          return { title: s.title, url: s.url };
        });
      }
    } catch(e) { /* skip malformed */ }
  }

  // Key people
  if (gp.key_people) {
    try {
      var people = typeof gp.key_people === "string" ? JSON.parse(gp.key_people) : gp.key_people;
      if (Array.isArray(people) && people.length > 0) {
        info.key_people = people.map(function(p) {
          return { name: p.name, role: p.role, suspicion: p.suspicion_reason || null };
        });
        stats.people++;
      }
    } catch(e) { /* skip */ }
  }

  // Website URL
  if (gp.links_json) {
    try {
      var links = typeof gp.links_json === "string" ? JSON.parse(gp.links_json) : gp.links_json;
      if (Array.isArray(links)) {
        for (var i = 0; i < links.length; i++) {
          if (links[i].url) { info.website_url = links[i].url; stats.links++; break; }
        }
      }
    } catch(e) { /* skip */ }
  }

  // Organization name from gefen
  if (gp.relevant_bodies && !info.org) {
    info.org = gp.relevant_bodies;
  }
}

// Phase 1: Update existing program_info entries
Object.keys(DATA.program_info).forEach(function(id) {
  var gp = byNumber[id];
  if (!gp) return;
  enrichFromGefen(DATA.program_info[id], gp);
  stats.updated++;
});

// Phase 2: Create NEW program_info entries for gefen programs with useful data
// (gemini_analysis, gemini_summary, description, or links)
gefen.data.forEach(function(gp) {
  if (!gp.program_number) return;
  if (DATA.program_info[gp.program_number]) return; // already exists

  // Only create if there's meaningful data
  var hasGemini = gp.gemini_analysis || gp.gemini_summary;
  var hasDesc = gp.description && gp.description.length > 20;
  var hasLinks = gp.links_json;
  if (!hasGemini && !hasDesc && !hasLinks) return;

  var info = {
    name: gp.program_name || ""
  };

  // Basic fields from gefen
  if (gp.relevant_bodies) info.org = gp.relevant_bodies;
  if (gp.description) info.summary = gp.description.length > 300 ? gp.description.substring(0, 300) : gp.description;

  enrichFromGefen(info, gp);

  DATA.program_info[gp.program_number] = info;
  stats.created++;
});

var output = "const DATA = " + JSON.stringify(DATA, null, 2) + ";\n";
fs.writeFileSync(dataPath, output, "utf8");

console.log("=== Enrichment complete ===");
console.log("Updated existing: " + stats.updated);
console.log("Created new:      " + stats.created);
console.log("Total program_info entries: " + Object.keys(DATA.program_info).length);
console.log("");
console.log("  Gemini descriptions: " + stats.gemini);
console.log("  Key people:          " + stats.people);
console.log("  Website URLs:        " + stats.links);
console.log("  Ideology markers:    " + stats.ideology);
console.log("  Funding signals:     " + stats.funding);
