/* Éditeur de plans journaliers — fond commun + zones persistées par jour via plan_api.php */

(function () {
  var NS = "http://www.w3.org/2000/svg";
  var svg = document.getElementById("plan");
  var W = 1000, H = 495.75; // proportions exactes du fond de plan définitif (4006×1987), jamais rogné ni déformé
  var YEAR = new Date().getFullYear();
  var dayIndex = dayOfYear(new Date());
  var zones = [];
  var savedDates = {};
  var selected = -1;
  var drag = null;
  var dirty = false;
  var el = function (id) { return document.getElementById(id); };
  // jour initial depuis l'URL (?date=YYYY-MM-DD) — après la définition de el
  if (window.PLAN_INIT_DATE) {
    var d0 = new Date(window.PLAN_INIT_DATE + "T12:00:00");
    if (!isNaN(d0)) {
      YEAR = d0.getFullYear();
      dayIndex = dayOfYear(d0);
    }
  }

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

  function copyDay(fromIso, untilIso) {
    setStatus("pending", "copie…");
    var body = { date: fromIso, to: untilIso, zones: zones };
    api("copy", { method: "POST", body: body }).then(function (j) {
      var d = new Date(fromIso + "T12:00:00");
      var end = new Date(untilIso + "T12:00:00");
      while (d <= end) {
        savedDates[iso(d)] = true;
        d = new Date(d.getTime() + 86400000);
      }
      paintSliderTicks();
      return j;
    }).then(function (j) {
      var msg = j.count > 1 ? ("✔ plan répliqué sur " + j.count + " jours") : "✔ plan répliqué";
      setStatus("ok", msg);
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

  // découpe un texte en lignes tenant dans maxWidth (px unités viewBox) et
  // l'écrit dans l'élément <text> sous forme de tspan, centré verticalement
  function wrapText(textEl, str, maxWidth, cx, cy) {
    var fontSize = 13;
    var lines = [];
    var words = String(str).split(/\s+/);
    var line = "";
    // canvas de mesure hors écran
    var meas = document.createElement("canvas").getContext("2d");
    meas.font = fontSize + "px sans-serif";
    words.forEach(function (w) {
      var test = line ? line + " " + w : w;
      if (meas.measureText(test).width > maxWidth && line) {
        lines.push(line);
        line = w;
      } else {
        line = test;
      }
    });
    if (line) lines.push(line);
    // un mot seul plus large que le bloc : on le garde tel quel
    textEl.innerHTML = "";
    lines.forEach(function (ln, k) {
      var t = document.createElementNS(NS, "tspan");
      t.setAttribute("x", textEl.getAttribute("x"));
      t.setAttribute("y", cy + 4.5 - (lines.length - 1) * 8 + k * 16);
      t.textContent = ln;
      textEl.appendChild(t);
    });
  }

  // ---------- dessin (fond de plan définitif : fond_plan.png, commun à tous les jours) ----------
  function drawBackground() {
    var img = document.createElementNS(NS, "image");
    img.setAttribute("href", "./fond_plan.png");
    img.setAttribute("x", 0); img.setAttribute("y", 0);
    img.setAttribute("width", W); img.setAttribute("height", H);
    img.setAttribute("preserveAspectRatio", "xMidYMid meet");
    svg.appendChild(img);
  }

  function draw() {
    while (svg.firstChild) svg.removeChild(svg.firstChild);
    drawBackground();
    zones.forEach(function (z, i) {
      var grp = document.createElementNS(NS, "g");
      if (z.type === "line") {
        var ln = document.createElementNS(NS, "line");
        ln.setAttribute("x1", z.x1); ln.setAttribute("y1", z.y1);
        ln.setAttribute("x2", z.x2); ln.setAttribute("y2", z.y2);
        ln.setAttribute("stroke", z.color);
        ln.setAttribute("stroke-width", i === selected ? (z.thick || 3) + 1.5 : (z.thick || 3));
        ln.setAttribute("stroke-linecap", "round");
        ln.setAttribute("class", "rect" + (i === selected ? " selected" : ""));
        grp.appendChild(ln);
        var llabel = document.createElementNS(NS, "text");
        llabel.setAttribute("x", (z.x1 + z.x2) / 2);
        llabel.setAttribute("y", (z.y1 + z.y2) / 2 - 8);
        llabel.setAttribute("text-anchor", "middle");
        llabel.setAttribute("font-size", "13");
        llabel.setAttribute("font-weight", "400");
        llabel.setAttribute("fill", "#1f2733");
        llabel.setAttribute("pointer-events", "none");
        llabel.textContent = z.name;
        grp.appendChild(llabel);
      } else {
        var r = document.createElementNS(NS, "rect");
        r.setAttribute("x", z.x); r.setAttribute("y", z.y);
        r.setAttribute("width", z.w); r.setAttribute("height", z.h);
        r.setAttribute("fill", z.color);
        r.setAttribute("fill-opacity", "0.45");
        r.setAttribute("stroke", z.color);
        r.setAttribute("stroke-width", i === selected ? 1.5 : 0.75);
        r.setAttribute("class", "rect" + (i === selected ? " selected" : ""));
        grp.appendChild(r);
        var label = document.createElementNS(NS, "text");
        label.setAttribute("x", z.x + z.w / 2);
        label.setAttribute("text-anchor", "middle");
        label.setAttribute("font-size", "13");
        label.setAttribute("font-weight", "400");
        label.setAttribute("fill", "#1f2733");
        label.setAttribute("pointer-events", "none");
        wrapText(label, z.name, z.w - 12, z.x + z.w / 2, z.y + z.h / 2);
        grp.appendChild(label);
      }
      grp.addEventListener("mousedown", function (e) { startDrag(e, i, null); });
      grp.addEventListener("dblclick", function () { renameZone(i); });
      svg.appendChild(grp);
    });
    if (selected >= 0) {
      var z = zones[selected];
      var pts;
      if (z.type === "line") {
        pts = [[z.x1, z.y1, 0], [z.x2, z.y2, 1]];
      } else {
        pts = [[z.x, z.y, 0], [z.x + z.w, z.y, 1], [z.x, z.y + z.h, 2], [z.x + z.w, z.y + z.h, 3]];
      }
      pts.forEach(function (p) {
        var h = document.createElementNS(NS, "rect");
        h.setAttribute("x", p[0] - 6); h.setAttribute("y", p[1] - 6);
        h.setAttribute("width", 12); h.setAttribute("height", 12);
        h.setAttribute("class", "handle");
        h.addEventListener("mousedown", function (e) { startDrag(e, selected, p[2]); });
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
    if (z.type === "line") {
      drag = { i: i, corner: corner, sx: p.x, sy: p.y, ox1: z.x1, oy1: z.y1, ox2: z.x2, oy2: z.y2 };
    } else {
      drag = { i: i, corner: corner, sx: p.x, sy: p.y, ox: z.x, oy: z.y, ow: z.w, oh: z.h };
    }
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
    if (z.type === "line") {
      if (drag.corner === null) {
        z.x1 = Math.max(0, Math.min(W, Math.round(drag.ox1 + dx)));
        z.y1 = Math.max(0, Math.min(H, Math.round(drag.oy1 + dy)));
        z.x2 = Math.max(0, Math.min(W, Math.round(drag.ox2 + dx)));
        z.y2 = Math.max(0, Math.min(H, Math.round(drag.oy2 + dy)));
      } else if (drag.corner === 0) {
        z.x1 = Math.max(0, Math.min(W, Math.round(drag.ox1 + dx)));
        z.y1 = Math.max(0, Math.min(H, Math.round(drag.oy1 + dy)));
      } else {
        z.x2 = Math.max(0, Math.min(W, Math.round(drag.ox2 + dx)));
        z.y2 = Math.max(0, Math.min(H, Math.round(drag.oy2 + dy)));
      }
      dirty = true;
      draw();
      return;
    }
    if (drag.corner === null) {
      z.x = Math.max(0, Math.min(W - z.w, Math.round(drag.ox + dx)));
      z.y = Math.max(0, Math.min(H - z.h, Math.round(drag.oy + dy)));
    } else {
      var k = drag.corner;
      var x1 = drag.ox, y1 = drag.oy, x2 = drag.ox + drag.ow, y2 = drag.oy + drag.oh;
      if (k === 0) { x1 += dx; y1 += dy; }
      if (k === 1) { x2 += dx; y1 += dy; }
      if (k === 2) { x1 += dx; y2 += dy; }
      if (k === 3) { x2 += dx; y2 += dy; }
      z.x = Math.max(0, Math.round(Math.min(x1, x2)));
      z.y = Math.max(0, Math.round(Math.min(y1, y2)));
      z.w = Math.min(W - z.x, Math.round(Math.max(10, Math.abs(x2 - x1))));
      z.h = Math.min(H - z.y, Math.round(Math.max(10, Math.abs(y2 - y1))));
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
    // nouveau bloc au centre du plan, aux proportions du fond (1000×405)
    var w = 120, h = 60;
    var x = Math.round((W - w) / 2), y = Math.round((H - h) / 2);
    zones.push({ name: "Bloc " + (zones.length + 1), x: x, y: y, w: w, h: h, color: "#4da3ff" });
    selected = zones.length - 1;
    dirty = true;
    draw();
  });

  el("addLine").addEventListener("click", function () {
    // nouvelle ligne horizontale au centre du plan
    var y = Math.round(H / 2);
    zones.push({ type: "line", name: "Ligne " + (zones.length + 1), x1: 380, y1: y, x2: 620, y2: y, thick: 3, color: "#e8590c" });
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

  // enregistrement automatique : toutes les 20 s si des modifications sont en attente
  window.setInterval(function () {
    if (dirty) {
      saveDay();
      setStatus("ok", "✔ enregistré automatiquement");
    }
  }, 20000);
  el("copyPlan").addEventListener("click", function () {
    var from = el("copyFrom").value;
    var until = el("copyUntil").value;
    if (!from) { setStatus("err", "choisis la date de début de la plage"); return; }
    if (!until) { setStatus("err", "choisis la date de fin de la plage"); return; }
    if (zones.length === 0) { setStatus("err", "aucun bloc sur la page courante à répliquer"); return; }
    var d1 = new Date(from + "T12:00:00");
    var d2 = new Date(until + "T12:00:00");
    var nb = Math.round((d2 - d1) / 86400000) + 1;
    if (nb < 1) { setStatus("err", "date de fin avant la date de début"); return; }
    var msg = "Répliquer les " + zones.length + " bloc(s) de ce plan sur " + nb + " jour" + (nb > 1 ? "s" : "") + " ?";
    msg += "\n(du " + from + " au " + until + ")";
    if (nb > 1 && !confirm(msg)) return;
    if (nb === 1 && !confirm(msg)) return;
    copyDay(from, until);
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
    var scene = document.getElementById("scene");
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

// ---------- export PNG du plan du jour ----------
(function () {
  var btn = document.getElementById("exportPng");
  if (!btn) return;

  // charge le fond de plan et le convertit en dataURL (nécessaire pour dessiner
  // sur canvas sans taint : une URL relative dans un SVG blob ne se résout pas)
  var fondDataUrl = null;
  function loadFond() {
    return fetch("./fond_plan.png")
      .then(function (r) { return r.blob(); })
      .then(function (b) {
        return new Promise(function (resolve, reject) {
          var fr = new FileReader();
          fr.onload = function () { resolve(fr.result); };
          fr.onerror = reject;
          fr.readAsDataURL(b);
        });
      });
  }

  btn.addEventListener("click", function () {
    setStatus("pending", "export…");
    loadFond().then(function (dataUrl) {
      fondDataUrl = dataUrl;
      var s = '<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 ' + W + ' ' + H + '" width="' + W + '" height="' + H + '">';
      s += '<image href="' + fondDataUrl + '" x="0" y="0" width="' + W + '" height="' + H + '"/>';
      zones.forEach(function (z) {
        if (z.type === "line") {
          s += '<line x1="' + z.x1 + '" y1="' + z.y1 + '" x2="' + z.x2 + '" y2="' + z.y2 +
               '" stroke="' + z.color + '" stroke-width="' + (z.thick || 3) + '" stroke-linecap="round"/>';
          s += '<text x="' + ((z.x1 + z.x2) / 2) + '" y="' + ((z.y1 + z.y2) / 2 - 8) + '" text-anchor="middle" font-family="Segoe UI, Arial" font-size="13" fill="#1f2733">' + z.name + '</text>';
        } else {
          s += '<rect x="' + z.x + '" y="' + z.y + '" width="' + z.w + '" height="' + z.h +
               '" fill="' + z.color + '" fill-opacity="0.45" stroke="' + z.color + '" stroke-width="0.75" rx="4"/>';
          s += '<text x="' + (z.x + z.w / 2) + '" y="' + (z.y + z.h / 2) + '" text-anchor="middle" font-family="Segoe UI, Arial" font-size="13" fill="#1f2733">' + z.name + '</text>';
        }
      });
      s += "</svg>";

      var img = new Image();
      img.onload = function () {
        var cv = document.createElement("canvas");
        cv.width = W * 2; cv.height = H * 2;
        var ctx = cv.getContext("2d");
        ctx.fillStyle = "#fff"; ctx.fillRect(0, 0, cv.width, cv.height);
        ctx.drawImage(img, 0, 0, cv.width, cv.height);
        var a = document.createElement("a");
        a.href = cv.toDataURL("image/png");
        a.download = "plan_" + iso(dateFromIndex(dayIndex)) + ".png";
        document.body.appendChild(a); a.click(); a.remove();
        setStatus("ok", "✔ PNG téléchargé");
      };
      img.onerror = function () { setStatus("err", "export PNG : rendu impossible"); };
      img.src = "data:image/svg+xml;charset=utf-8," + encodeURIComponent(s);
    }).catch(function () {
      setStatus("err", "export PNG : fond de plan introuvable");
    });
  });
})();
