/**
 * @preserve
 * @author    ThemePunch <info@themepunch.com>
 * @link      http://www.themepunch.com/
 * @copyright 2026 ThemePunch
 */
/*
╔══════════════════════════════════════════════════════════════════════════════╗
║  PLUGIN:   SLIDER REVOLUTION 7                                                ║
║  MODULE:   SEO BRIDGE  —  a module's text for the Yoast / Rank Math analysis  ║
║  AUTHOR:   ThemePunch                                                         ║
╚══════════════════════════════════════════════════════════════════════════════╝

  Both plugins hand the post content through a modification point before they analyse it. Every module the
  content references, by [sr7] / [rev_slider] shortcode or by its block, is fetched once (RevSliderSEO) and
  appended there, and the analysis is asked to run again when the text arrives.
*/
(() => {
	"use strict";
	const cfg = window.SR7SEO;
	if (!cfg) return;

	const cache = {};				//alias -> html, '' for a module with nothing to read
	const pending = new Set();
	const refreshers = [];
	//the alias alone, decoded the way RevSliderBuilders::parse_shortcode does: a builder may store the
	//brackets and quotes as entities. SR7.Block.parseShortcode is not on the classic editor screen.
	const decode = s => String(s || "").replace(/&#0*91;/g, "[").replace(/&#0*93;/g, "]").replace(/&quot;/g, '"').replace(/&#0*39;|&apos;/g, "'");
	const RX_SHORTCODE = /\[(?:sr7|rev_slider)\s[^\]]*?\balias\s*=\s*["']?([^"'\]\s]+)/gi;
	const RX_BLOCK = /<!--\s*wp:themepunch\/revslider\s+(\{[\s\S]*?\})\s*\/?-->/g;

	const aliases = content => {
		const out = new Set();
		content = decode(content);
		let m;
		RX_SHORTCODE.lastIndex = 0;
		while ((m = RX_SHORTCODE.exec(content))) out.add(m[1]);
		RX_BLOCK.lastIndex = 0;
		while ((m = RX_BLOCK.exec(content))) {
			try { const a = JSON.parse(m[1]).alias; if (a) out.add(a); } catch {}
		}
		return [...out];
	};

	const refresh = () => refreshers.forEach(f => { try { f(); } catch {} });

	//plain jQuery.post: the classic editor screen has no _tpt, and the call needs nothing _tpt.ajax adds
	const fetchMissing = list => {
		const need = list.filter(a => cache[a] === undefined && !pending.has(a));
		if (!need.length) return;
		need.forEach(a => pending.add(a));
		const done = mods => { need.forEach(a => { cache[a] = mods[a] ?? ""; pending.delete(a); }); refresh(); };
		jQuery.post(cfg.ajaxurl, {action: "rs_ajax_action", client_action: "slider.get.text", nonce: cfg.nonce, version: 7, data: {aliases: need}}, null, "json")
			.done(res => done(res?.modules || {}))
			.fail(() => done({}));
	};

	const append = content => {
		const list = aliases(content);
		fetchMissing(list);
		const text = list.map(a => cache[a] || "").join("");
		return text ? content + "\n" + text : content;
	};

	if (cfg.yoast) {
		const yoast = () => {
			const app = window.YoastSEO?.app;
			if (!app?.registerPlugin) return;
			app.registerPlugin("sr7", {status: "ready"});
			app.registerModification("content", append, "sr7", 5);
			refreshers.push(() => app.pluginReloaded("sr7"));
		};
		if (window.YoastSEO?.app) yoast(); else jQuery(window).on("YoastSEO:ready", yoast);
	}

	if (cfg.rankmath && window.wp?.hooks) {
		wp.hooks.addFilter("rank_math_content", "sr7", append);
		refreshers.push(() => window.rankMathEditor?.refresh?.("content"));
	}
})();
