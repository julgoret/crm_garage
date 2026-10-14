/* Éditeur de plans journaliers — fond commun + zones persistées par jour via plan_api.php */

(function () {
  var NS = "http://www.w3.org/2000/svg";
  var svg = document.getElementById("plan");
  var W = 1000, H = 700;
  var YEAR = new Date().getFullYear();
  var dayIndex = dayOfYear(new Date());
  var zones = [];
  var savedDates = {};
  var selected = -1;
  var drag = null;
  var dirty = false;
  var el = function (id) { return document.getElementById(id); };

  // ---------- utilitaires dates ----------
  function dayOfYear(d) {
    var start = new Date(d.getFullYear(), 0, 1);
    return Math.floor((d - start) / 86400000);
  }
  function dateFromIndex(i) {
    return new Date(YEAR, 0, 1 + i);
  }
  function iso(d) {
    var m = String(d.getMonth() + 1).padStart(2, "0");
    var day = String(d.getDate()).padStart(2, "0");
    return d.getFullYear() + "-" + m + "-" + day;
  }
  function longLabel(d) {
    var days = ["dimanche", "lundi", "mardi", "mercredi", "jeudi", "vendredi", "samedi"];
    var months = ["janvier", "février", "mars", "avril", "mai", "juin", "juillet",
                  "août", "septembre", "octobre", "novembre", "décembre"];
    return days[d.getDay()] + " " + d.getDate() + " " + months[d.getMonth()] + " " + d.getFullYear();
  }

  // ---------- API ----------
  function api(action, opts) {
    var url = "./plan_api.php?action=" + action;
    if (opts && opts.qs) url += "&" + opts.qs;
    var init = { method: opts && opts.method || "GET", headers: {} };
    if (opts && opts.body) {
      init.headers["Content-Type"] = "application/json";
      init.body = JSON.stringify(opts.body);
    }
    return fetch(url, init).then(function (r) {
      return r.json().then(function (j) {
        if (!r.ok) throw new Error(j.error || ("HTTP " + r.status));
        return j;
      });
    });
  }

  function loadRange() {
    return api("range").then(function (j) {
      j.dates.forEach(function (d) { savedDates[d] = true; });
      paintSliderTicks();
    }).catch(function () {});
  }

  function loadDay(silent) {
    var date = iso(dateFromIndex(dayIndex));
    setStatus("pending", "chargement…");
    return api("get", { qs: "date=" + date }).then(function (j) {
      zones = j.zones || [];
      selected = -1;
      dirty = false;
      draw();
      if (!silent) setStatus("ok", zones.length + " bloc(s)");
      return zones;
    }).catch(function (e) {
      zones = [];
      selected = -1;
      draw();
      setStatus("err", e.message);
    });
  }

  function saveDay() {
    var date = iso(dateFromIndex(dayIndex));
    setStatus("pending", "enregistrement…");
    api("save", { method: "POST", body: { date: date, zones: zones } }).then(function (j) {
      savedDates[date] = true;
      paintSliderTicks();
      dirty = false;
      setStatus("ok", "✔ enregistré (" + j.count + " blocs)");
    }).catch(function (e) {
      setStatus("err", "✘ " + e.message);
    });
  }

  function copyDay(fromIso) {
    var date = iso(dateFromIndex(dayIndex));
    setStatus("pending", "copie…");
    api("copy", { method: "POST", body: { date: date, from: fromIso } }).then(function () {
      savedDates[date] = true;
      paintSliderTicks();
      return loadDay(true);
    }).then(function () {
      setStatus("ok", "✔ plan répliqué");
      dirty = true; // répliqué mais non modifié : rien à forcer, déjà sauvé
    }).catch(function (e) {
      setStatus("err", "✘ " + e.message);
    });
  }

  // ---------- barre de jour ----------
  function syncDayBar() {
    var d = dateFromIndex(dayIndex);
    el("dayLabel").textContent = longLabel(d);
    el("dayDate").value = iso(d);
    el("daySlider").value = dayIndex;
    document.title = "Plan du " + longLabel(d) + " — Garage Moderne";
  }

  function gotoIndex(i, force) {
    i = Math.max(0, Math.min(364, i));
    if (!force && i === dayIndex) return;
    if (dirty && !confirm("Des modifications non enregistrées pour ce jour seront perdues. Continuer ?")) return;
    dayIndex = i;
    syncDayBar();
    loadDay();
  }

  var sliderTimer = null;
  el("daySlider").addEventListener("input", function () {
    var i = parseInt(this.value, 10);
    // suivi fluide pendant le défilement : affiche le jour, charge en différé
    dayIndex = i;
    syncDayBar();
    clearTimeout(sliderTimer);
    sliderTimer = setTimeout(function () { loadDay(); }, 120);
  });
  el("daySlider").addEventListener("change", function () {
    clearTimeout(sliderTimer);
    loadDay();
  });

  el("dayPrev").addEventListener("click", function () { gotoIndex(dayIndex - 1); });
  el("dayNext").addEventListener("click", function () { gotoIndex(dayIndex + 1); });

  el("dayDate").addEventListener("change", function () {
    var d = new Date(this.value + "T12:00:00");
    if (isNaN(d) || d.getFullYear() !== YEAR) { setStatus("err", "date hors " + YEAR); return; }
    gotoIndex(dayOfYear(d));
  });

  function paintSliderTicks() {
    // le slider : marqueur visuel léger via la couleur du label
    var d = dateFromIndex(dayIndex);
    var has = savedDates[iso(d)];
    el("dayLabel").style.color = has ? "var(--accent)" : "var(--text)";
    el("dayLabel").title = has ? "Un plan est sauvegardé pour ce jour" : "Aucun plan sauvegardé pour ce jour";
  }

  function setStatus(kind, msg) {
    var s = el("saveStatus");
    s.className = "status " + kind;
    s.textContent = msg;
  }

  // ---------- dessin (fond commun identique à plan_editor.html) ----------
  function genericPlan() {
    var s = "";
    s += '<defs><pattern id="grid" width="25" height="25" patternUnits="userSpaceOnUse">';
    s += '<path d="M25 0 L0 0 0 25" fill="none" stroke="#eef1f5" stroke-width="1"/></pattern></defs>';
    s += '<rect width="' + W + '" height="' + H + '" fill="#fafbfd"/>';
    s += '<rect width="' + W + '" height="' + H + '" fill="url(#grid)"/>';
    s += '<rect x="20" y="20" width="' + (W - 40) + '" height="' + (H - 40) + '" fill="none" stroke="#9aa3af" stroke-width="6" stroke-linejoin="round"/>';
    s += '<line x1="440" y1="20" x2="440" y2="300" stroke="#c3cad4" stroke-width="3"/>';
    s += '<line x1="700" y1="20" x2="700" y2="360" stroke="#c3cad4" stroke-width="3"/>';
    s += '<line x1="20"  y1="360" x2="700" y2="360" stroke="#c3cad4" stroke-width="3"/>';
    s += '<line x1="440" y1="300" x2="980" y2="300" stroke="#c3cad4" stroke-width="3"/>';
    s += '<line x1="250" y1="360" x2="250" y2="680" stroke="#c3cad4" stroke-width="3"/>';
    s += '<rect x="770" y="300" width="120" height="10" fill="#fafbfd" stroke="#9aa3af" stroke-width="2"/>';
    s += '<text x="830" y="330" text-anchor="middle" font-size="16" fill="#6b7686">ENTRÉE</text>';
    return s;
  }

  function draw() {
    while (svg.firstChild) svg.removeChild(svg.firstChild);
    var g = document.createElementNS(NS, "g");
    g.innerHTML = genericPlan();
    svg.appendChild(g);

    zones.forEach(function (z, i) {
      var grp = document.createElementNS(NS, "g");
      var r = document.createElementNS(NS, "rect");
      r.setAttribute("x", z.x); r.setAttribute("y", z.y);
      r.setAttribute("width", z.w); r.setAttribute("height", z.h);
      r.setAttribute("fill", z.color);
      r.setAttribute("fill-opacity", "0.45");
      r.setAttribute("stroke", z.color);
      r.setAttribute("stroke-width", i === selected ? 2 : 1.5);
      r.setAttribute("class", "rect" + (i === selected ? " selected" : ""));
      grp.appendChild(r);

      var label = document.createElementNS(NS, "text");
      label.setAttribute("x", z.x + z.w / 2);
      label.setAttribute("y", z.y + z.h / 2 - 2);
      label.setAttribute("text-anchor", "middle");
      label.setAttribute("font-size", "14");
      label.setAttribute("font-weight", "600");
      label.setAttribute("fill", "#1f2733");
      label.setAttribute("pointer-events", "none");
      label.textContent = z.name;
      grp.appendChild(label);

      var dims = document.createElementNS(NS, "text");
      dims.setAttribute("x", z.x + z.w / 2);
      dims.setAttribute("y", z.y + z.h / 2 + 14);
      dims.setAttribute("text-anchor", "middle");
      dims.setAttribute("font-size", "11");
      dims.setAttribute("fill", "#445");
      dims.setAttribute("pointer-events", "none");
      dims.textContent = Math.round(z.w) + " × " + Math.round(z.h);
      grp.appendChild(dims);

      grp.addEventListener("mousedown", function (e) { startDrag(e, i, null); });
      grp.addEventListener("dblclick", function () { renameZone(i); });
      svg.appendChild(grp);
    });

    if (selected >= 0) {
      var z = zones[selected];
      [[z.x, z.y], [z.x + z.w, z.y], [z.x, z.y + z.h], [z.x + z.w, z.y + z.h]].forEach(function (p, k) {
        var h = document.createElementNS(NS, "rect");
        h.setAttribute("x", p[0] - 6); h.setAttribute("y", p[1] - 6);
        h.setAttribute("width", 12); h.setAttribute("height", 12);
        h.setAttribute("class", "handle");
        h.addEventListener("mousedown", function (e) { startDrag(e, selected, k); });
        svg.appendChild(h);
      });
    }
    syncPanel();
    renderList();
  }

  // ---------- interactions ----------
  function svgPoint(e) {
    var pt = svg.createSVGPoint();
    pt.x = e.clientX; pt.y = e.clientY;
    return pt.matrixTransform(svg.getScreenCTM().inverse());
  }

  function startDrag(e, i, corner) {
    e.preventDefault();
    e.stopPropagation();
    selected = i;
    var z = zones[i];
    var p = svgPoint(e);
    drag = { i: i, corner: corner, sx: p.x, sy: p.y, ox: z.x, oy: z.y, ow: z.w, oh: z.h };
    draw();
  }

  svg.addEventListener("mousedown", function (e) {
    if (e.target === svg || e.target.tagName === "image") { selected = -1; draw(); }
  });

  window.addEventListener("mousemove", function (e) {
    if (!drag) return;
    var z = zones[drag.i];
    var p = svgPoint(e);
    var dx = p.x - drag.sx, dy = p.y - drag.sy;
    if (drag.corner === null) {
      z.x = Math.round(drag.ox + dx);
      z.y = Math.round(drag.oy + dy);
    } else {
      var k = drag.corner;
      var x1 = drag.ox, y1 = drag.oy, x2 = drag.ox + drag.ow, y2 = drag.oy + drag.oh;
      if (k === 0) { x1 += dx; y1 += dy; }
      if (k === 1) { x2 += dx; y1 += dy; }
      if (k === 2) { x1 += dx; y2 += dy; }
      if (k === 3) { x2 += dx; y2 += dy; }
      z.x = Math.round(Math.min(x1, x2)); z.y = Math.round(Math.min(y1, y2));
      z.w = Math.round(Math.max(10, Math.abs(x2 - x1)));
      z.h = Math.round(Math.max(10, Math.abs(y2 - y1)));
    }
    dirty = true;
    draw();
  });

  window.addEventListener("mouseup", function () {
    if (drag) { drag = null; }
  });

  function renameZone(i) {
    var n = prompt("Nom du bloc :", zones[i].name);
    if (n !== null && n.trim() !== "") { zones[i].name = n.trim(); dirty = true; draw(); }
  }

  // ---------- panneau ----------
  function syncPanel() {
    var on = selected >= 0;
    ["zName", "zColor", "zX", "zY", "zW", "zH", "delRect"].forEach(function (id) { el(id).disabled = !on; });
    if (!on) { el("zName").value = ""; return; }
    var z = zones[selected];
    el("zName").value = z.name;
    el("zColor").value = z.color;
    el("zX").value = z.x; el("zY").value = z.y;
    el("zW").value = z.w; el("zH").value = z.h;
  }

  function renderList() {
    var ul = el("zoneList");
    ul.innerHTML = "";
    zones.forEach(function (z, i) {
      var li = document.createElement("li");
      if (i === selected) li.className = "active";
      li.innerHTML = '<span class="swatch" style="background:' + z.color + '"></span>' +
        '<span class="zname"></span><span class="zinfo">' + Math.round(z.w) + '×' + Math.round(z.h) + '</span>';
      li.querySelector(".zname").textContent = z.name;
      li.addEventListener("click", function () { selected = i; draw(); });
      ul.appendChild(li);
    });
  }

  ["zName", "zColor", "zX", "zY", "zW", "zH"].forEach(function (id) {
    el(id).addEventListener("input", function () {
      if (selected < 0) return;
      var z = zones[selected];
      if (id === "zName") z.name = el(id).value;
      if (id === "zColor") z.color = el(id).value;
      if (id === "zX") z.x = parseInt(el(id).value) || 0;
      if (id === "zY") z.y = parseInt(el(id).value) || 0;
      if (id === "zW") z.w = Math.max(10, parseInt(el(id).value) || 10);
      if (id === "zH") z.h = Math.max(10, parseInt(el(id).value) || 10);
      dirty = true;
      draw();
    });
  });

  el("addRect").addEventListener("click", function () {
    zones.push({ name: "Bloc " + (zones.length + 1), x: 380, y: 460, w: 120, h: 80, color: "#4da3ff" });
    selected = zones.length - 1;
    dirty = true;
    draw();
  });

  el("delRect").addEventListener("click", function () {
    if (selected < 0) return;
    zones.splice(selected, 1);
    selected = -1;
    dirty = true;
    draw();
  });

  el("savePlan").addEventListener("click", saveDay);
  el("copyPlan").addEventListener("click", function () {
    var from = el("copyFrom").value;
    if (!from) { setStatus("err", "choisis une date source"); return; }
    if (dirty && !confirm("Des modifications non enregistrées pour ce jour seront perdues. Continuer ?")) return;
    copyDay(from);
  });

  // raccourcis clavier : flèches gauche/droite pour changer de jour
  window.addEventListener("keydown", function (e) {
    if (["INPUT", "TEXTAREA", "SELECT"].indexOf(document.activeElement.tagName) >= 0) return;
    if (e.key === "ArrowLeft") gotoIndex(dayIndex - 1);
    if (e.key === "ArrowRight") gotoIndex(dayIndex + 1);
  });

  // ---------- init ----------
  syncDayBar();
  loadRange().then(function () { paintSliderTicks(); });
  loadDay();
})();
