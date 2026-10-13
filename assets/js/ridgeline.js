/**
 * RIDGELINE front end. The CRM request form is rendered and opened by the
 * TargetWeb CRM plugin; this file only provides the theme's own components.
 */
(function () {
	'use strict';

	var cfg = window.ridgelineConfig || {};
	var reduceMotion = window.matchMedia && window.matchMedia('(prefers-reduced-motion: reduce)').matches;

	function $(sel, root) { return (root || document).querySelector(sel); }
	function $$(sel, root) { return Array.prototype.slice.call((root || document).querySelectorAll(sel)); }
	function esc(str) {
		var div = document.createElement('div');
		div.textContent = str == null ? '' : String(str);
		return div.innerHTML;
	}
	function ajax(action, params) {
		var query = new URLSearchParams(Object.assign({ action: action, nonce: cfg.nonce }, params || {}));
		return fetch(cfg.ajaxUrl + '?' + query.toString(), { credentials: 'same-origin' }).then(function (res) {
			if (!res.ok) { throw new Error('HTTP ' + res.status); }
			return res.json();
		}).then(function (json) {
			if (!json || !json.success) { throw new Error('Request failed'); }
			return json.data;
		});
	}

	/* ---------- Dialog helper: focus trap, Esc, scroll lock ---------- */
	var openDialogs = [];
	var FOCUSABLE = 'a[href], button:not([disabled]), input:not([disabled]):not([type="hidden"]), select:not([disabled]), textarea:not([disabled]), [tabindex]:not([tabindex="-1"])';

	/* The shop filters stay in the page on desktop, so they open with a class instead of [hidden]. */
	function usesClass(el) { return el.hasAttribute('data-rdg-filters'); }
	function setOpen(el, on) {
		if (usesClass(el)) { el.classList.toggle('is-open', on); } else { el.hidden = !on; }
	}
	function openDialog(el, opener, focusEl) {
		if (!el || openDialogs.indexOf(el) > -1) { return; }
		setOpen(el, true);
		el._opener = opener || document.activeElement;
		openDialogs.push(el);
		document.documentElement.classList.add('rdg-locked');
		if (opener) { opener.setAttribute('aria-expanded', 'true'); }
		var target = focusEl || $(FOCUSABLE, el);
		if (target) { setTimeout(function () { target.focus(); }, 20); }
	}
	function closeDialog(el) {
		if (!el || openDialogs.indexOf(el) < 0) { return; }
		setOpen(el, false);
		openDialogs = openDialogs.filter(function (d) { return d !== el; });
		if (!openDialogs.length) { document.documentElement.classList.remove('rdg-locked'); }
		if (el._opener) {
			el._opener.setAttribute('aria-expanded', 'false');
			if (el._opener.focus) { el._opener.focus(); }
		}
	}
	document.addEventListener('keydown', function (e) {
		var top = openDialogs[openDialogs.length - 1];
		if (!top) { return; }
		if (e.key === 'Escape') {
			e.preventDefault();
			closeDialog(top);
			return;
		}
		if (e.key === 'Tab') {
			var items = $$(FOCUSABLE, top).filter(function (n) { return n.offsetParent !== null; });
			if (!items.length) { return; }
			var first = items[0], last = items[items.length - 1];
			if (e.shiftKey && document.activeElement === first) { e.preventDefault(); last.focus(); }
			else if (!e.shiftKey && document.activeElement === last) { e.preventDefault(); first.focus(); }
		}
	});

	/* ---------- Announcement bar ---------- */
	(function () {
		var bar = $('[data-rdg-announce]');
		if (!bar) { return; }
		var key = 'rdgAnnounce:' + bar.getAttribute('data-rdg-announce');
		try { if (localStorage.getItem(key)) { bar.hidden = true; return; } } catch (err) {}
		var btn = $('[data-rdg-announce-close]', bar);
		if (btn) {
			btn.addEventListener('click', function () {
				bar.hidden = true;
				try { localStorage.setItem(key, '1'); } catch (err) {}
			});
		}
	})();

	/* ---------- Drawer ---------- */
	(function () {
		var drawer = $('#rdg-drawer');
		if (!drawer) { return; }
		$$('[data-rdg-drawer-open]').forEach(function (btn) {
			btn.addEventListener('click', function () { openDialog(drawer, btn); });
		});
		$$('[data-rdg-drawer-close]', drawer).forEach(function (btn) {
			btn.addEventListener('click', function () { closeDialog(drawer); });
		});
	})();

	/* ---------- Mega menus ---------- */
	(function () {
		var toggles = $$('.rdg-nav__toggle');
		function closeAll(except) {
			toggles.forEach(function (t) {
				if (t === except) { return; }
				t.setAttribute('aria-expanded', 'false');
				var panel = document.getElementById(t.getAttribute('aria-controls'));
				if (panel) { panel.hidden = true; }
			});
		}
		toggles.forEach(function (t) {
			var panel = document.getElementById(t.getAttribute('aria-controls'));
			t.addEventListener('click', function () {
				var open = t.getAttribute('aria-expanded') === 'true';
				closeAll(t);
				t.setAttribute('aria-expanded', open ? 'false' : 'true');
				if (panel) { panel.hidden = open; }
			});
		});
		document.addEventListener('click', function (e) {
			if (!e.target.closest('.rdg-nav__item--mega')) { closeAll(); }
		});
		document.addEventListener('keydown', function (e) {
			if (e.key !== 'Escape') { return; }
			var open = toggles.filter(function (t) { return t.getAttribute('aria-expanded') === 'true'; })[0];
			if (open) { closeAll(); open.focus(); }
		});
	})();

	/* ---------- Search overlay + live results ---------- */
	(function () {
		var overlay = $('#rdg-search');
		if (!overlay) { return; }
		var input = $('#rdg-search-input', overlay);
		var clear = $('[data-rdg-search-clear]', overlay);
		var idle = $('[data-rdg-search-state="idle"]', overlay);
		var live = $('[data-rdg-search-live]', overlay);
		var timer = null;
		var seq = 0;

		$$('[data-rdg-search-open]').forEach(function (btn) {
			btn.addEventListener('click', function () { openDialog(overlay, btn, input); });
		});
		$$('[data-rdg-search-close]', overlay).forEach(function (btn) {
			btn.addEventListener('click', function () { closeDialog(overlay); });
		});
		document.addEventListener('keydown', function (e) {
			if (e.key === '/' && !e.target.closest('input, textarea, select, [contenteditable]') && overlay.hidden) {
				e.preventDefault();
				openDialog(overlay, null, input);
			}
		});

		function showIdle() {
			live.hidden = true;
			live.innerHTML = '';
			if (idle) { idle.hidden = false; }
		}
		function showLive(html) {
			if (idle) { idle.hidden = true; }
			live.hidden = false;
			live.innerHTML = html;
		}
		function skeleton() {
			var rows = '';
			for (var i = 0; i < 4; i++) { rows += '<li class="rdg-sr__item rdg-skel"><span></span><span></span></li>'; }
			return '<p class="screen-reader-text">Searching…</p><ul class="rdg-sr__list" aria-hidden="true">' + rows + '</ul>';
		}
		function render(term, data) {
			if (!data.products.length && !data.terms.length) {
				return '<div class="rdg-sr__empty"><p class="rdg-h3">No matches for “' + esc(term) + '”</p>' +
					'<p>Try a model number or a broader word' + (cfg.phone ? ', or call <a href="' + esc(cfg.tel) + '">' + esc(cfg.phone) + '</a>' : '') + '.</p></div>';
			}
			var products = data.products.map(function (p) {
				return '<li><a class="rdg-sr__item" href="' + esc(p.url) + '">' +
					(p.image ? '<img src="' + esc(p.image) + '" alt="" loading="lazy">' : '<span class="rdg-ph"></span>') +
					'<span class="rdg-sr__text">' +
					(p.brand ? '<span class="rdg-sr__brand">' + esc(p.brand) + '</span>' : '') +
					'<span class="rdg-sr__name">' + esc(p.name) + '</span>' +
					(p.sku ? '<span class="rdg-sr__sku">SKU ' + esc(p.sku) + '</span>' : '') +
					'</span><span class="rdg-sr__side"><span class="rdg-sr__price">' + esc(p.price) + '</span>' +
					'<span class="rdg-stock rdg-stock--' + esc(p.stockState) + '">' + esc(p.stock) + '</span></span></a></li>';
			}).join('');
			var terms = data.terms.map(function (t) {
				return '<li><a class="rdg-sr__term" href="' + esc(t.url) + '"><span>' + esc(t.name) + '</span><span class="rdg-sr__type">' + esc(t.type) + ' · ' + t.count + '</span></a></li>';
			}).join('');
			return '<div class="rdg-sr">' +
				'<div class="rdg-sr__col"><p class="rdg-eyebrow">Products</p>' +
				(products ? '<ul class="rdg-sr__list">' + products + '</ul>' : '<p>No products, but these may help.</p>') +
				(data.total ? '<a class="rdg-btn rdg-btn--brand" href="' + esc(data.url) + '">See all ' + data.total + ' result' + (data.total === 1 ? '' : 's') + '</a>' : '') +
				'</div>' +
				(terms ? '<div class="rdg-sr__col rdg-sr__col--side"><p class="rdg-eyebrow">Brands &amp; categories</p><ul class="rdg-sr__terms">' + terms + '</ul></div>' : '') +
				'</div>' +
				'<p class="screen-reader-text">' + data.total + ' products found.</p>';
		}
		function run() {
			var term = input.value.trim();
			if (term.length < 2) { showIdle(); return; }
			var mine = ++seq;
			showLive(skeleton());
			ajax('ridgeline_search', { term: term }).then(function (data) {
				if (mine === seq) { showLive(render(term, data)); }
			}).catch(function () {
				if (mine !== seq) { return; }
				showLive('<div class="rdg-sr__empty"><p class="rdg-h3">Search is not responding.</p><p>Check your connection and try again.</p><button type="button" class="rdg-btn rdg-btn--outline" data-rdg-search-retry>Try again</button></div>');
			});
		}

		input.addEventListener('input', function () {
			clear.hidden = !input.value;
			if (!cfg.suggestions) { return; }
			clearTimeout(timer);
			timer = setTimeout(run, 250);
		});
		clear.addEventListener('click', function () {
			input.value = '';
			clear.hidden = true;
			showIdle();
			input.focus();
		});
		live.addEventListener('click', function (e) {
			if (e.target.closest('[data-rdg-search-retry]')) { run(); }
		});
	})();

	/* ---------- Hero slider ---------- */
	(function () {
		var hero = $('[data-rdg-hero]');
		if (!hero) { return; }
		var slides = $$('.rdg-hero__slide', hero);
		if (slides.length < 2) { return; }
		var dots = $$('[data-rdg-hero-dot]', hero);
		var pause = $('[data-rdg-hero-pause]', hero);
		var delay = parseInt(hero.getAttribute('data-autoplay'), 10) || 0;
		var index = 0;
		var timer = null;
		var paused = reduceMotion || !delay;

		function go(n) {
			index = (n + slides.length) % slides.length;
			slides.forEach(function (s, i) {
				s.hidden = i !== index;
				s.classList.toggle('is-active', i === index);
			});
			dots.forEach(function (d, i) {
				if (i === index) { d.setAttribute('aria-current', 'true'); } else { d.removeAttribute('aria-current'); }
			});
		}
		function schedule() {
			clearInterval(timer);
			if (!paused) { timer = setInterval(function () { go(index + 1); }, delay); }
		}
		function setPaused(value) {
			paused = value;
			if (pause) {
				pause.setAttribute('aria-label', paused ? 'Play slideshow' : 'Pause slideshow');
				pause.innerHTML = paused
					? '<svg class="rdg-icon" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true"><path d="M7 5l12 7-12 7z"/></svg>'
					: '<svg class="rdg-icon" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true"><path d="M8 5v14M16 5v14"/></svg>';
			}
			schedule();
		}
		dots.forEach(function (d) {
			d.addEventListener('click', function () { go(parseInt(d.getAttribute('data-rdg-hero-dot'), 10)); schedule(); });
		});
		var prev = $('[data-rdg-hero-prev]', hero), next = $('[data-rdg-hero-next]', hero);
		if (prev) { prev.addEventListener('click', function () { go(index - 1); schedule(); }); }
		if (next) { next.addEventListener('click', function () { go(index + 1); schedule(); }); }
		if (pause) {
			if (!delay) { pause.hidden = true; }
			pause.addEventListener('click', function () { setPaused(!paused); });
		}
		hero.addEventListener('focusin', function () { clearInterval(timer); });
		hero.addEventListener('focusout', schedule);
		setPaused(paused);
	})();

	/* ---------- Homepage finder ---------- */
	(function () {
		var form = $('[data-rdg-finder]');
		if (!form) { return; }
		var cat = form.querySelector('[name="fcat[]"]');
		var brand = form.querySelector('[name="fbrand[]"]');
		var stock = form.querySelector('[name="fstock[]"]');
		var preview = $('[data-rdg-finder-preview]', form);
		var seq = 0;

		function selectedText(sel) {
			return sel && sel.value ? sel.options[sel.selectedIndex].text.replace(/\s*\(\d+\)$/, '') : '';
		}
		function resultsUrl() {
			var params = new URLSearchParams();
			if (cat && cat.value) { params.append('fcat[]', cat.value); }
			if (brand && brand.value) { params.append('fbrand[]', brand.value); }
			if (stock && stock.checked) { params.append('fstock[]', 'instock'); }
			var qs = params.toString();
			return form.action + (qs ? (form.action.indexOf('?') > -1 ? '&' : '?') + qs : '');
		}
		function update() {
			var any = (cat && cat.value) || (brand && brand.value) || (stock && stock.checked);
			if (!any) { preview.hidden = true; return; }
			var mine = ++seq;
			ajax('ridgeline_finder', { cat: cat ? cat.value : '', brand: brand ? brand.value : '', instock: stock && stock.checked ? 1 : '' }).then(function (data) {
				if (mine !== seq) { return; }
				var summary = [selectedText(brand), selectedText(cat), stock && stock.checked ? 'in stock' : ''].filter(Boolean).join(' · ');
				preview.innerHTML = '<strong>' + data.count + ' machine' + (data.count === 1 ? '' : 's') + ' match:</strong> ' + esc(summary) +
					' · <a href="' + esc(resultsUrl()) + '">View filtered results</a>';
				preview.hidden = false;
			}).catch(function () { preview.hidden = true; });
		}
		[cat, brand, stock].forEach(function (el) { if (el) { el.addEventListener('change', update); } });
		form.addEventListener('submit', function (e) {
			e.preventDefault();
			window.location.href = resultsUrl();
		});
	})();

	/* ---------- Shop filters, sort, view ---------- */
	(function () {
		var filters = $('[data-rdg-filters]');
		var desktop = window.matchMedia('(min-width: 1024px)');
		if (filters) {
			var form = $('[data-rdg-filter-form]', filters);
			$$('[data-rdg-filters-open]').forEach(function (btn) {
				btn.addEventListener('click', function () { openDialog(filters, btn); });
			});
			$$('[data-rdg-filters-close]', filters).forEach(function (btn) {
				btn.addEventListener('click', function () { closeDialog(filters); });
			});
			desktop.addEventListener('change', function () { if (desktop.matches) { closeDialog(filters); } });

			form.addEventListener('change', function (e) {
				if (desktop.matches && e.target.type === 'checkbox') { form.submit(); }
			});
			form.addEventListener('submit', function () {
				$$('input[type="number"]', form).forEach(function (n) { if (n.value === '') { n.disabled = true; } });
			});
			$$('[data-rdg-facet-more]', filters).forEach(function (btn) {
				btn.addEventListener('click', function () {
					var open = btn.getAttribute('aria-expanded') === 'true';
					$$('[data-rdg-more]', btn.parentNode).forEach(function (li) { li.hidden = open; });
					btn.setAttribute('aria-expanded', open ? 'false' : 'true');
					btn.textContent = open ? btn.textContent.replace('Show fewer', 'Show all') : 'Show fewer';
				});
			});
		}

		$$('.woocommerce-ordering select').forEach(function (sel) {
			sel.classList.add('rdg-select');
			sel.setAttribute('aria-label', 'Sort products');
		});

		var grid = $('[data-rdg-products]');
		$$('[data-rdg-view]').forEach(function (btn) {
			btn.addEventListener('click', function () {
				var view = btn.getAttribute('data-rdg-view');
				if (grid) { grid.classList.toggle('is-list', view === 'list'); }
				$$('[data-rdg-view]').forEach(function (b) { b.setAttribute('aria-pressed', b === btn ? 'true' : 'false'); });
				document.cookie = 'ridgeline_view=' + view + ';path=/;max-age=31536000;SameSite=Lax';
			});
		});
	})();

	/* ---------- Compare ---------- */
	(function () {
		if (!cfg.compare) { return; }
		var KEY = 'rdgCompare';
		var max = cfg.compareMax || 4;
		var bar = $('[data-rdg-cmpbar]');
		var modal = $('#rdg-compare');
		var maxMsg = bar && $('[data-rdg-cmp-max]', bar);

		function read() {
			try { return JSON.parse(localStorage.getItem(KEY)) || []; } catch (err) { return []; }
		}
		function write(items) {
			try { localStorage.setItem(KEY, JSON.stringify(items)); } catch (err) {}
			sync();
		}
		function has(items, id) { return items.some(function (i) { return i.id === id; }); }

		function sync() {
			var items = read();
			var full = items.length >= max;
			$$('[data-rdg-compare]').forEach(function (box) {
				var id = parseInt(box.getAttribute('data-rdg-compare'), 10);
				var on = has(items, id);
				var label = box.parentNode.querySelector('[data-rdg-compare-label]');
				box.checked = on;
				box.disabled = !on && full;
				if (label) { label.textContent = on ? 'Added to compare' : (full ? 'Compare (max ' + max + ' selected)' : 'Compare'); }
			});
			if (!bar) { return; }
			bar.hidden = !items.length;
			document.body.classList.toggle('rdg-has-cmpbar', items.length > 0);
			$('[data-rdg-cmp-count]', bar).textContent = '(' + items.length + '/' + max + ')';
			$('[data-rdg-cmp-items]', bar).innerHTML = items.map(function (i) {
				return '<li class="rdg-chip rdg-chip--dark">' + esc(i.name) + '<button type="button" data-rdg-cmp-remove="' + i.id + '" aria-label="Remove ' + esc(i.name) + ' from compare">×</button></li>';
			}).join('');
			var openBtn = $('[data-rdg-cmp-open]', bar);
			openBtn.disabled = items.length < 2;
			openBtn.textContent = items.length < 2 ? 'Add one more to compare' : 'Compare ' + items.length;
			if (maxMsg && !full) { maxMsg.hidden = true; }
		}

		document.addEventListener('change', function (e) {
			var box = e.target.closest('[data-rdg-compare]');
			if (!box) { return; }
			var id = parseInt(box.getAttribute('data-rdg-compare'), 10);
			var items = read();
			if (box.checked) {
				if (items.length >= max) {
					box.checked = false;
					if (maxMsg) { maxMsg.hidden = false; }
					return;
				}
				if (!has(items, id)) { items.push({ id: id, name: box.getAttribute('data-name') }); }
			} else {
				items = items.filter(function (i) { return i.id !== id; });
			}
			write(items);
		});

		if (bar) {
			bar.addEventListener('click', function (e) {
				var remove = e.target.closest('[data-rdg-cmp-remove]');
				if (remove) {
					var id = parseInt(remove.getAttribute('data-rdg-cmp-remove'), 10);
					write(read().filter(function (i) { return i.id !== id; }));
					return;
				}
				if (e.target.closest('[data-rdg-cmp-clear]')) { write([]); }
				var open = e.target.closest('[data-rdg-cmp-open]');
				if (open) { showCompare(open); }
			});
		}

		function bestIndex(values, mode) {
			var best = -1, bestVal = null;
			values.forEach(function (v, i) {
				if (v === null || v === undefined || isNaN(v)) { return; }
				if (bestVal === null || (mode === 'low' ? v < bestVal : v > bestVal)) { bestVal = v; best = i; }
			});
			var distinct = values.filter(function (v) { return v !== null && !isNaN(v); });
			return distinct.length > 1 && distinct.some(function (v) { return v !== bestVal; }) ? best : -1;
		}

		function table(products) {
			var specNames = [];
			products.forEach(function (p) {
				Object.keys(p.specs || {}).forEach(function (k) { if (specNames.indexOf(k) < 0) { specNames.push(k); } });
			});
			var priceBest = bestIndex(products.map(function (p) { return p.priceValue; }), 'low');
			var stockBest = bestIndex(products.map(function (p) { return p.stockQty; }), 'high');
			function cell(content, best) {
				return '<td' + (best ? ' class="is-best"' : '') + '>' + content + (best ? ' <span class="rdg-best">Best</span>' : '') + '</td>';
			}
			var head = '<tr><th scope="col"><span class="screen-reader-text">Product</span></th>' + products.map(function (p) {
				return '<th scope="col"><button type="button" class="rdg-cmp__remove" data-rdg-cmp-remove-modal="' + p.id + '" aria-label="Remove ' + esc(p.name) + '">×</button>' +
					(p.image ? '<img src="' + esc(p.image) + '" alt="">' : '<span class="rdg-ph"></span>') +
					'<span class="rdg-cmptable__brand">' + esc(p.brand) + '</span>' + esc(p.name) + '</th>';
			}).join('') + '</tr>';
			var rows = '<tr><th scope="row">Price</th>' + products.map(function (p, i) { return cell(esc(p.price), i === priceBest); }).join('') + '</tr>';
			rows += '<tr><th scope="row">Availability</th>' + products.map(function (p, i) {
				return cell('<span class="rdg-stock rdg-stock--' + esc(p.stockState) + '">' + esc(p.stock) + '</span>' + (p.stockQty ? ' ' + p.stockQty + (p.stockQty === 1 ? ' unit' : ' units') : ''), i === stockBest);
			}).join('') + '</tr>';
			rows += '<tr><th scope="row">SKU</th>' + products.map(function (p) { return cell(esc(p.sku || '—')); }).join('') + '</tr>';
			specNames.forEach(function (name) {
				rows += '<tr><th scope="row">' + esc(name) + '</th>' + products.map(function (p) { return cell(esc((p.specs || {})[name] || '—')); }).join('') + '</tr>';
			});
			rows += '<tr><th scope="row"><span class="screen-reader-text">Link</span></th>' + products.map(function (p) {
				return '<td><a class="rdg-btn rdg-btn--brand" href="' + esc(p.url) + '">View<span class="screen-reader-text"> ' + esc(p.name) + '</span></a></td>';
			}).join('') + '</tr>';
			return '<div class="rdg-tablewrap" tabindex="0" role="region" aria-label="Comparison table"><table class="rdg-cmptable rdg-cmptable--modal"><thead>' + head + '</thead><tbody>' + rows + '</tbody></table></div>' +
				'<p class="rdg-form__note">“Best” marks the lowest price and the most units in stock. Specs come from each product page.</p>';
		}

		function showCompare(opener) {
			if (!modal) { return; }
			var body = $('[data-rdg-cmp-body]', modal);
			var items = read();
			openDialog(modal, opener);
			if (!items.length) {
				body.innerHTML = '<div class="rdg-empty"><p class="rdg-h3">Nothing to compare yet</p><p>Tick “Compare” on up to ' + max + ' products.</p></div>';
				return;
			}
			body.innerHTML = '<p class="rdg-skel rdg-skel--block" aria-label="Loading comparison"></p>';
			ajax('ridgeline_compare', { ids: items.map(function (i) { return i.id; }).join(',') }).then(function (data) {
				body.innerHTML = data.products.length ? table(data.products) : '<div class="rdg-empty"><p class="rdg-h3">These products are no longer available.</p></div>';
			}).catch(function () {
				body.innerHTML = '<div class="rdg-empty"><p class="rdg-h3">We couldn’t load the comparison.</p><button type="button" class="rdg-btn rdg-btn--outline" data-rdg-cmp-retry>Try again</button></div>';
			});
		}

		if (modal) {
			modal.addEventListener('click', function (e) {
				if (e.target.closest('[data-rdg-modal-close]')) { closeDialog(modal); return; }
				if (e.target.closest('[data-rdg-cmp-retry]')) { closeDialog(modal); showCompare($('[data-rdg-cmp-open]')); return; }
				var remove = e.target.closest('[data-rdg-cmp-remove-modal]');
				if (remove) {
					var id = parseInt(remove.getAttribute('data-rdg-cmp-remove-modal'), 10);
					write(read().filter(function (i) { return i.id !== id; }));
					closeDialog(modal);
					if (read().length) { showCompare($('[data-rdg-cmp-open]')); }
				}
			});
		}

		window.addEventListener('storage', function (e) { if (e.key === KEY) { sync(); } });
		sync();
	})();

	/* ---------- Product gallery + lightbox ---------- */
	(function () {
		var gallery = $('[data-rdg-gallery]');
		if (!gallery) { return; }
		var slides = $$('.rdg-gallery__slide', gallery);
		var thumbs = $$('[data-rdg-gallery-thumb]', gallery);
		var counter = $('[data-rdg-gallery-index]', gallery);
		var lightbox = $('#rdg-lightbox');
		var lightImg = lightbox && $('[data-rdg-lightbox-img]', lightbox);
		var index = 0;

		function go(n) {
			if (!slides.length) { return; }
			index = (n + slides.length) % slides.length;
			slides.forEach(function (s, i) { s.hidden = i !== index; s.classList.toggle('is-active', i === index); });
			thumbs.forEach(function (t, i) { if (i === index) { t.setAttribute('aria-current', 'true'); } else { t.removeAttribute('aria-current'); } });
			if (counter) { counter.textContent = String(index + 1); }
			if (lightImg && !lightbox.hidden) { lightImg.src = slides[index].getAttribute('data-full') || ''; }
		}
		thumbs.forEach(function (t) {
			t.addEventListener('click', function () { go(parseInt(t.getAttribute('data-rdg-gallery-thumb'), 10)); });
		});
		var prev = $('[data-rdg-gallery-prev]', gallery), next = $('[data-rdg-gallery-next]', gallery);
		if (prev) { prev.addEventListener('click', function () { go(index - 1); }); }
		if (next) { next.addEventListener('click', function () { go(index + 1); }); }

		var start = null;
		var main = $('.rdg-gallery__main', gallery);
		main.addEventListener('touchstart', function (e) { start = e.touches[0].clientX; }, { passive: true });
		main.addEventListener('touchend', function (e) {
			if (start === null) { return; }
			var dx = e.changedTouches[0].clientX - start;
			if (Math.abs(dx) > 40) { go(index + (dx < 0 ? 1 : -1)); }
			start = null;
		});

		var full = $('[data-rdg-gallery-full]', gallery);
		if (full && lightbox) {
			full.addEventListener('click', function () {
				lightImg.src = slides[index].getAttribute('data-full') || '';
				openDialog(lightbox, full, $('[data-rdg-lightbox-close]', lightbox));
			});
			lightbox.addEventListener('click', function (e) {
				if (e.target.closest('[data-rdg-lightbox-close]') || e.target === lightbox) { closeDialog(lightbox); }
				if (e.target.closest('[data-rdg-lightbox-prev]')) { go(index - 1); }
				if (e.target.closest('[data-rdg-lightbox-next]')) { go(index + 1); }
			});
			lightbox.addEventListener('keydown', function (e) {
				if (e.key === 'ArrowLeft') { go(index - 1); }
				if (e.key === 'ArrowRight') { go(index + 1); }
			});
		}
	})();

	/* ---------- Quantity stepper ---------- */
	function enhanceQuantity(root) {
		$$('.quantity', root).forEach(function (wrap) {
			var input = $('input.qty', wrap);
			if (!input || input.type === 'hidden' || wrap.classList.contains('rdg-qty')) { return; }
			wrap.classList.add('rdg-qty');
			function step(dir) {
				var stepVal = parseFloat(input.step) || 1;
				var min = input.min !== '' ? parseFloat(input.min) : 0;
				var maxVal = input.max !== '' ? parseFloat(input.max) : Infinity;
				var value = (parseFloat(input.value) || 0) + dir * stepVal;
				input.value = String(Math.min(maxVal, Math.max(min, value)));
				input.dispatchEvent(new Event('change', { bubbles: true }));
			}
			var minus = document.createElement('button');
			minus.type = 'button';
			minus.className = 'rdg-qty__btn';
			minus.setAttribute('aria-label', 'Decrease quantity');
			minus.textContent = '−';
			minus.addEventListener('click', function () { step(-1); });
			var plus = minus.cloneNode(false);
			plus.setAttribute('aria-label', 'Increase quantity');
			plus.textContent = '+';
			plus.addEventListener('click', function () { step(1); });
			wrap.insertBefore(minus, input);
			wrap.appendChild(plus);
		});
	}
	enhanceQuantity(document);
	document.body.addEventListener('updated_cart_totals', function () { enhanceQuantity(document); });
	if (window.jQuery) {
		window.jQuery(document.body).on('updated_cart_totals updated_wc_div', function () { enhanceQuantity(document); });
	}

	/* ---------- Variation options as pressed buttons ---------- */
	(function () {
		var form = $('.variations_form');
		if (!form) { return; }
		$$('.variations select', form).forEach(function (select) {
			var group = document.createElement('div');
			group.className = 'rdg-options';
			group.setAttribute('role', 'group');
			var label = form.querySelector('label[for="' + select.id + '"]');
			if (label) { group.setAttribute('aria-label', label.textContent.trim()); }
			function build() {
				group.innerHTML = '';
				$$('option', select).forEach(function (opt) {
					if (!opt.value) { return; }
					var btn = document.createElement('button');
					btn.type = 'button';
					btn.className = 'rdg-option';
					btn.textContent = opt.textContent;
					btn.disabled = opt.disabled;
					btn.setAttribute('aria-pressed', select.value === opt.value ? 'true' : 'false');
					btn.addEventListener('click', function () {
						select.value = select.value === opt.value ? '' : opt.value;
						if (window.jQuery) { window.jQuery(select).trigger('change'); } else { select.dispatchEvent(new Event('change', { bubbles: true })); }
						build();
					});
					group.appendChild(btn);
				});
			}
			select.classList.add('rdg-visually-hidden');
			select.setAttribute('tabindex', '-1');
			select.setAttribute('aria-hidden', 'true');
			select.parentNode.insertBefore(group, select);
			build();
			if (window.jQuery) {
				window.jQuery(form).on('woocommerce_update_variation_values reset_data', function () { setTimeout(build, 0); });
			}
		});
	})();

	/* ---------- CRM request modal (plugin renders the form, theme owns open/close) ---------- */
	(function () {
		var popup = $('#tw-crm-popup.rdg-crm');
		if (!popup) { return; }
		function setState(on) {
			popup.classList.toggle('is-open', on);
			popup.setAttribute('aria-hidden', on ? 'false' : 'true');
		}
		document.addEventListener('click', function (e) {
			var trigger = e.target.closest('#tw-request-info-btn, [data-tw-crm-open]');
			if (trigger) {
				e.preventDefault();
				setState(true);
				openDialog(popup, trigger, $('#leads-form-div input:not([type="hidden"]), #leads-form-div select, #leads-form-div textarea', popup));
				return;
			}
			if (e.target.closest('[data-tw-crm-close]') && popup.contains(e.target)) {
				setState(false);
				closeDialog(popup);
			}
		});
		new MutationObserver(function () {
			if (popup.hidden && popup.classList.contains('is-open')) { setState(false); }
		}).observe(popup, { attributes: true, attributeFilter: ['hidden'] });
	})();

	/* ---------- Copy link ---------- */
	$$('[data-rdg-copy]').forEach(function (btn) {
		btn.addEventListener('click', function () {
			var msg = $('[data-rdg-copy-msg]', btn.parentNode);
			var url = btn.getAttribute('data-rdg-copy');
			var done = function () { if (msg) { msg.textContent = 'Link copied'; setTimeout(function () { msg.textContent = ''; }, 2500); } };
			if (navigator.clipboard && navigator.clipboard.writeText) {
				navigator.clipboard.writeText(url).then(done).catch(function () { window.prompt('Copy this link:', url); });
			} else {
				window.prompt('Copy this link:', url);
			}
		});
	});

	/* ---------- Stock-by-location widget: hide the fallback table once the widget fills ---------- */
	(function () {
		var widget = $('[data-rdg-stock-widget]');
		var fallback = $('[data-rdg-stock-fallback]');
		if (!widget || !fallback) { return; }
		function check() {
			if (widget.children.length || widget.textContent.trim()) { fallback.hidden = true; return true; }
			return false;
		}
		if (check()) { return; }
		var obs = new MutationObserver(function () { if (check()) { obs.disconnect(); } });
		obs.observe(widget, { childList: true, subtree: true, characterData: true });
	})();

	/* ---------- Theme forms (contact, service, newsletter) ---------- */
	$$('[data-rdg-form]').forEach(function (form) {
		var alert = $('[data-rdg-form-alert]', form);
		function show(type, text) {
			if (!alert) { return; }
			alert.className = 'rdg-form__alert rdg-form__alert--' + type + (form.classList.contains('rdg-footer__news-form') ? ' rdg-footer__news-msg' : '');
			alert.textContent = text;
			alert.hidden = false;
		}
		form.addEventListener('submit', function (e) {
			e.preventDefault();
			$$('[aria-invalid]', form).forEach(function (el) { el.removeAttribute('aria-invalid'); });
			var missing = $$('[required]', form).filter(function (el) {
				return !el.value.trim() || (el.type === 'email' && !/^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(el.value.trim()));
			});
			if (missing.length) {
				missing.forEach(function (el) { el.setAttribute('aria-invalid', 'true'); });
				show('error', 'Please check the highlighted fields.');
				missing[0].focus();
				return;
			}
			var submit = $('[type="submit"]', form);
			var label = submit ? submit.textContent : '';
			if (submit) { submit.disabled = true; submit.textContent = 'Sending…'; }
			var data = new FormData(form);
			data.append('page_url', window.location.href);
			fetch(form.action, { method: 'POST', body: data, credentials: 'same-origin' })
				.then(function (res) { return res.json(); })
				.then(function (json) {
					if (json && json.success) {
						show('success', json.data.message);
						form.reset();
						return;
					}
					var fields = (json && json.data && json.data.fields) || [];
					fields.forEach(function (name) {
						var el = form.querySelector('[name="' + name + '"]');
						if (el) { el.setAttribute('aria-invalid', 'true'); }
					});
					show('error', (json && json.data && json.data.message) || 'Something went wrong. Please call us.');
				})
				.catch(function () { show('error', 'Something went wrong. Please call us.'); })
				.finally(function () { if (submit) { submit.disabled = false; submit.textContent = label; } });
		});
	});
})();
