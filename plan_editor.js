/* Éditeur de plans journaliers — fond commun + zones persistées par jour via plan_api.php */

(function () {
  var NS = "http://www.w3.org/2000/svg";
  var svg = document.getElementById("plan");
  var W = 1000, H = 405; // proportions du fond de plan définitif (4000×1621)
  var YEAR = new Date().getFullYear();
  var dayIndex = dayOfYear(new Date());
  // jour initial depuis l'URL (?date=YYYY-MM-DD ou plan/YYYY-MM-DD via rewrite)
  if (window.PLAN_INIT_DATE) {
    var d0 = new Date(window.PLAN_INIT_DATE + "T12:00:00");
    if (!isNaN(d0)) {
      YEAR = d0.getFullYear();
      dayIndex = dayOfYear(d0);
      syncSliderBounds();
    }
  }
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
  function syncSliderBounds() {
    var slider = el("daySlider");
    slider.min = 0;
    slider.max = daysInYear(YEAR) - 1;
  }

  function daysInYear(y) {
    return ((y % 4 === 0 && y % 100 !== 0) || y % 400 === 0) ? 366 : 365;
  }

  function syncUrl() {
    var date = iso(dateFromIndex(dayIndex));
    if (window.history && window.history.replaceState) {
      window.history.replaceState(null, "", "./plan.php?date=" + date);
    }
  }

  function syncDayBar() {
    var d = dateFromIndex(dayIndex);
    el("dayLabel").textContent = longLabel(d);
    el("dayDate").value = iso(d);
    el("daySlider").value = dayIndex;
    document.title = "Plan du " + longLabel(d) + " — Garage Moderne";
    syncUrl();
  }

  function gotoIndex(i, force) {
    i = Math.max(0, Math.min(daysInYear(YEAR) - 1, i));
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
    if (isNaN(d)) { setStatus("err", "date invalide"); return; }
    YEAR = d.getFullYear();
    syncSliderBounds();
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

  // ---------- dessin (fond de plan définitif : fond_plan.png, commun à tous les jours) ----------
  function drawBackground() {
    var img = document.createElementNS(NS, "image");
    img.setAttribute("href", "./fond_plan.png");
    img.setAttribute("x", 0); img.setAttribute("y", 0);
    img.setAttribute("width", W); img.setAttribute("height", H);
    img.setAttribute("preserveAspectRatio", "none");
    svg.appendChild(img);
  }

  function draw() {
    while (svg.firstChild) svg.removeChild(svg.firstChild);
    drawBackground();

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
    ["zName", "zColor", "delRect"].forEach(function (id) { el(id).disabled = !on; });
    if (!on) { el("zName").value = ""; return; }
    var z = zones[selected];
    el("zName").value = z.name;
    el("zColor").value = z.color;
  }

  ["zName", "zColor"].forEach(function (id) {
    el(id).addEventListener("input", function () {
      if (selected < 0) return;
      var z = zones[selected];
      if (id === "zName") z.name = el(id).value;
      if (id === "zColor") z.color = el(id).value;
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

  // décale la scène sous la barre fixe (recalculé au resize)
  function layout() {
    var bar = document.getElementById("topbar");
    var scene = document.getElementById("svgWrap");
    scene.style.top = bar.offsetHeight + "px";
  }
  window.addEventListener("resize", layout);

  // ---------- init ----------
  layout();
  syncSliderBounds();
  syncDayBar();
  loadRange().then(function () { paintSliderTicks(); });
  loadDay();
})();
