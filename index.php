<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<title>GADE Coordinate Generator</title>
<meta name="viewport" content="width=device-width, initial-scale=1">

<style>
  :root {
    color-scheme: light dark;
    --bg: #f5f5f5;
    --fg: #222;
    --accent: #0077cc;
    --card: #ffffff;
    --border: #dddddd;
    --error: #c0392b;
    --mono: "SF Mono", Menlo, Consolas, monospace;
    --sans: system-ui, -apple-system, BlinkMacSystemFont, "Segoe UI", sans-serif;
  }

  * { box-sizing: border-box; }

  body {
    margin: 0;
    font-family: var(--sans);
    background: var(--bg);
    color: var(--fg);
    padding: 1rem;
  }

  .page {
    max-width: 900px;
    margin: 0 auto;
  }

  h1 {
    font-size: 1.6rem;
    margin-bottom: 0.25rem;
  }

  p {
    margin: 0.25rem 0 0.5rem;
    line-height: 1.4;
  }

  .card {
    background: var(--card);
    border-radius: 0.75rem;
    border: 1px solid var(--border);
    padding: 1rem;
    margin-top: 1rem;
    box-shadow: 0 4px 10px rgba(0,0,0,0.04);
  }

  label {
    display: block;
    font-weight: 600;
    margin-bottom: 0.25rem;
  }

  input[type="text"],
  textarea {
    width: 100%;
    padding: 0.5rem 0.6rem;
    border-radius: 0.5rem;
    border: 1px solid var(--border);
    font-size: 0.95rem;
    font-family: var(--sans);
  }

  textarea {
    min-height: 4rem;
    resize: vertical;
  }

  button {
    display: inline-flex;
    align-items: center;
    justify-content: center;
    gap: 0.4rem;
    margin-top: 0.75rem;
    padding: 0.55rem 1.1rem;
    border-radius: 999px;
    border: none;
    background: var(--accent);
    color: #fff;
    font-weight: 600;
    cursor: pointer;
    font-size: 0.95rem;
  }

  button:active {
    transform: translateY(1px);
  }

  button:disabled {
    opacity: 0.6;
    cursor: default;
  }

  .output {
    font-family: var(--mono);
    font-size: 0.9rem;
    background: #fafafa;
    border-radius: 0.5rem;
    border: 1px dashed var(--border);
    padding: 0.5rem 0.6rem;
    white-space: pre-wrap;
    word-break: break-word;
  }

  .mapping-table {
    width: 100%;
    border-collapse: collapse;
    font-family: var(--mono);
    font-size: 0.85rem;
    margin-top: 0.25rem;
  }

  .mapping-table th,
  .mapping-table td {
    border: 1px solid var(--border);
    padding: 0.25rem 0.4rem;
    text-align: center;
  }

  .mapping-table th {
    background: #f0f0f0;
    font-weight: 600;
  }

  .error {
    color: var(--error);
    font-size: 0.9rem;
    margin-top: 0.4rem;
  }

  .hint {
    font-size: 0.8rem;
    opacity: 0.8;
    margin-top: 0.2rem;
  }

  .copy-row {
    display: flex;
    justify-content: flex-end;
    margin-bottom: 0.25rem;
  }

  .copy-btn {
    padding: 0.3rem 0.7rem;
    font-size: 0.8rem;
    border-radius: 999px;
    background: #444;
    color: #fff;
  }

  .checksum-list {
    font-family: var(--mono);
    font-size: 0.9rem;
    margin-top: 0.4rem;
  }

  .checksum-list div {
    margin-bottom: 0.2rem;
  }

  code {
    font-family: var(--mono);
    background: #eee;
    padding: 0.05rem 0.25rem;
    border-radius: 0.25rem;
  }

  @media (max-width: 600px) {
    h1 { font-size: 1.3rem; }
    .card { padding: 0.85rem; }
  }
</style>
</head>
<body>
<div class="page">
  <header>
    <h1>GADE Coordinate Generator</h1>
    <p>
      Enter your puzzle answer and a coordinate formula. Letters are converted to numbers (a=1…z=26),
      digits are sorted, missing 0–9 are appended, and variables (a–z) are mapped to digits.
      The formula is then substituted while keeping N/E/S/W as coordinate prefixes and printed with
      uppercase N/E/S/W. Checksums (digital root modulo 9) are calculated for each part and the full coordinate.
    </p>
  </header>

  <section class="card">
    <label for="answerInput">Input text (letters and digits)</label>
    <input id="answerInput" type="text" placeholder="Example: 23A3iK">

    <p class="hint">
      Letters are case-insensitive and converted to their alphanumeric value (a=1…z=26). Other characters are ignored.
    </p>

    <label for="formulaInput" style="margin-top:0.75rem;">Coordinate formula</label>
    <textarea id="formulaInput" placeholder="Example: N 55 AF.GEF E 012 IF.LHH"></textarea>
    <p class="hint">
      You can use any letters as variables. N/E/S/W must be present as direction prefixes (e.g. N 55, E 012).
      The formula is treated in lowercase internally, but N/E/S/W are printed uppercase in the final result.
    </p>

    <button id="generateBtn">Generate</button>
    <div id="errorBox" class="error"></div>
  </section>

  <section class="card">
    <div class="copy-row">
      <button class="copy-btn" id="copyMappingBtn">Copy mapping</button>
    </div>
    <h3>Variable mapping and steps</h3>
    <div id="mappingBox" class="output">No mapping yet.</div>
  </section>

  <section class="card">
    <div class="copy-row">
      <button class="copy-btn" id="copySubstitutedBtn">Copy substituted</button>
    </div>
    <h3>Substituted coordinate</h3>
    <div id="substitutedBox" class="output">Waiting…</div>
  </section>

  <section class="card">
    <div class="copy-row">
      <button class="copy-btn" id="copyResultBtn">Copy result</button>
    </div>
    <h3>Evaluated result (only if pure math)</h3>
    <div id="resultBox" class="output">Waiting…</div>
    <p class="hint">
      If the substituted formula is a pure math expression, it will be evaluated. If it contains coordinates
      (N/E/S/W, degrees, etc.), it will not be evaluated, but you can still copy the substituted result.
    </p>
  </section>

  <section class="card">
    <div class="copy-row">
      <button class="copy-btn" id="copyChecksumBtn">Copy checksums</button>
    </div>
    <h3>Checksums (digital root modulo 9)</h3>
    <div id="checksumBox" class="output">
      No checksums yet.
    </div>
  </section>
</div>

<script>
  // Convert input to digits: letters -> 1..26, digits kept
  // Also return grouped representation for display
  function extractDigitsFromAnswerDetailed(raw) {
    const s = (raw || "").toLowerCase();
    let digitsString = "";
    const groups = [];
    for (const ch of s) {
      if (ch >= "a" && ch <= "z") {
        const val = ch.charCodeAt(0) - 96; // a=1
        digitsString += String(val);
        groups.push(String(val));
      } else if (ch >= "0" && ch <= "9") {
        digitsString += ch;
        groups.push(ch);
      }
      // ignore everything else
    }
    return { lower: s, digitsString, groups };
  }

  // Sort digits and append missing 0–9
  function buildDigitSequence(digits) {
    const arr = digits.split("").sort((a, b) => a.localeCompare(b));
    for (let d = 0; d <= 9; d++) {
      const ch = String(d);
      if (!arr.includes(ch)) {
        arr.push(ch);
      }
    }
    return arr.join("");
  }

  // Generate variable names: single letters a–z, limited to length
  function generateVariableNames(count) {
    const alphabet = "abcdefghijklmnopqrstuvwxyz".split("");
    return alphabet.slice(0, Math.min(count, 26));
  }

  // Build mapping from variables to digits
  function buildMappingFromDigits(digitSeq) {
    const vars = generateVariableNames(digitSeq.length);
    const mapping = {};
    for (let i = 0; i < vars.length; i++) {
      mapping[vars[i]] = digitSeq[i];
    }
    return mapping;
  }

  function renderMappingSection(lower, groups, digitSeq, mapping) {
    const keys = Object.keys(mapping);
    let html = "";

    html += "Lowercased input: <code>" + (lower || "") + "</code>\n";
    html += "Letters/digits to numbers: <code>" + (groups.length ? groups.join(" ") : "-") + "</code>\n";
    html += "Sorted digits + missing 0–9: <code>" + (digitSeq || "") + "</code>\n\n";

    if (!keys.length) {
      html += "No mapping.";
      return html;
    }

    html += '<table class="mapping-table"><tr>';
    keys.forEach(k => html += "<th>" + k + "</th>");
    html += "</tr><tr>";
    keys.forEach(k => html += "<td>" + mapping[k] + "</td>");
    html += "</tr></table>";

    return html;
  }

  // Find protected prefixes: n/e/s/w followed by space + number
  function findProtectedPrefixes(formula) {
    const protectedPositions = new Set();
    const regex = /\b([nesw])\s+\d+/g;
    let match;
    while ((match = regex.exec(formula)) !== null) {
      protectedPositions.add(match.index);
    }
    return protectedPositions;
  }

  // Substitute variables with digits, protecting coordinate prefixes
  function substituteFormula(formula, mapping) {
    const f = (formula || "").toLowerCase();
    const protectedPositions = findProtectedPrefixes(f);
    let result = "";

    for (let i = 0; i < f.length; i++) {
      const ch = f[i];

      if (protectedPositions.has(i)) {
        result += ch; // keep n/e/s/w as prefix
        continue;
      }

      if (mapping[ch] !== undefined) {
        result += mapping[ch];
      } else {
        result += ch;
      }
    }

    return result;
  }

  // Uppercase N/E/S/W prefixes in final output
  function uppercasePrefixes(formula) {
    return formula.replace(/\b([nesw])(\s+\d)/g, (match, p1, p2) => {
      return p1.toUpperCase() + p2;
    });
  }

  // Evaluate only pure math expressions
  function safeEvaluate(expr) {
    const safePattern = /^[0-9+\-*/().\s]+$/;
    if (!safePattern.test(expr)) return null;
    try {
      // eslint-disable-next-line no-new-func
      const fn = new Function("return (" + expr + ");");
      return fn();
    } catch {
      return null;
    }
  }

  // Digital root modulo 9 (geocaching-style checksum)
  function digitalRoot9(digits) {
    if (!digits) return null;
    let sum = 0;
    for (const ch of digits) {
      sum += Number(ch);
    }
    if (sum === 0) return 0;
    while (sum > 9) {
      let s = 0;
      for (const ch of String(sum)) {
        s += Number(ch);
      }
      sum = s;
    }
    return sum;
  }

  // Extract N/S and E/W parts from a coordinate string
  function extractCoordinateParts(coord) {
    const s = coord.toLowerCase();
    // Pattern: prefix, then anything until next prefix
    const regex = /\b([nesw])\s*([^nesw]*?)\b([nesw])\s*(.*)/;
    const match = s.match(regex);
    if (!match) return null;

    const nsPart = match[1] + " " + match[2];
    const ewPart = match[3] + " " + match[4];
    return { nsPart, ewPart };
  }

  // Compute checksums for N/S, E/W, and full coordinate
  function computeChecksums(coord) {
    const parts = extractCoordinateParts(coord);
    if (!parts) return null;

    const nsDigits = (parts.nsPart.match(/\d/g) || []).join("");
    const ewDigits = (parts.ewPart.match(/\d/g) || []).join("");
    const allDigits = nsDigits + ewDigits;

    return {
      ns: digitalRoot9(nsDigits),
      ew: digitalRoot9(ewDigits),
      full: digitalRoot9(allDigits),
      nsDigits,
      ewDigits,
      allDigits
    };
  }

  const answerInput = document.getElementById("answerInput");
  const formulaInput = document.getElementById("formulaInput");
  const generateBtn = document.getElementById("generateBtn");
  const errorBox = document.getElementById("errorBox");
  const mappingBox = document.getElementById("mappingBox");
  const substitutedBox = document.getElementById("substitutedBox");
  const resultBox = document.getElementById("resultBox");
  const checksumBox = document.getElementById("checksumBox");

  const copyMappingBtn = document.getElementById("copyMappingBtn");
  const copySubstitutedBtn = document.getElementById("copySubstitutedBtn");
  const copyResultBtn = document.getElementById("copyResultBtn");
  const copyChecksumBtn = document.getElementById("copyChecksumBtn");

  generateBtn.addEventListener("click", () => {
    errorBox.textContent = "";
    checksumBox.textContent = "No checksums yet.";

    const raw = answerInput.value.trim();
    const formula = formulaInput.value.trim();

    if (!raw) {
      errorBox.textContent = "Please enter input text.";
      return;
    }

    const detail = extractDigitsFromAnswerDetailed(raw);
    if (!detail.digitsString) {
      errorBox.textContent = "No digits could be generated from the input.";
      return;
    }

    const digitSeq = buildDigitSequence(detail.digitsString);
    const mapping = buildMappingFromDigits(digitSeq);

    mappingBox.innerHTML = renderMappingSection(detail.lower, detail.groups, digitSeq, mapping);

    if (!formula) {
      substitutedBox.textContent = "No formula entered.";
      resultBox.textContent = "No formula entered.";
      return;
    }

    const substitutedLower = substituteFormula(formula, mapping);
    const finalOutput = uppercasePrefixes(substitutedLower);
    substitutedBox.textContent = finalOutput;

    const evalResult = safeEvaluate(substitutedLower);
    if (evalResult === null) {
      resultBox.textContent = "Not a pure math expression (likely coordinates).";
    } else {
      resultBox.textContent = String(evalResult);
    }

    const checks = computeChecksums(finalOutput);
    if (checks) {
      checksumBox.innerHTML =
        '<div class="checksum-list">' +
        '<div>NS digits: ' + (checks.nsDigits || "-") + ' → checksum: ' + (checks.ns ?? "-") + '</div>' +
        '<div>EW digits: ' + (checks.ewDigits || "-") + ' → checksum: ' + (checks.ew ?? "-") + '</div>' +
        '<div>All digits: ' + (checks.allDigits || "-") + ' → checksum: ' + (checks.full ?? "-") + '</div>' +
        '</div>';
    } else {
      checksumBox.textContent = "Could not detect N/E/S/W structure for checksums.";
    }
  });

  copyMappingBtn.addEventListener("click", () => {
    navigator.clipboard.writeText(mappingBox.innerText || "");
  });

  copySubstitutedBtn.addEventListener("click", () => {
    navigator.clipboard.writeText(substitutedBox.innerText || "");
  });

  copyResultBtn.addEventListener("click", () => {
    navigator.clipboard.writeText(resultBox.innerText || "");
  });

  copyChecksumBtn.addEventListener("click", () => {
    navigator.clipboard.writeText(checksumBox.innerText || "");
  });
</script>
</body>
</html>
