/**
 * HTML-safe search-term highlighting.
 *
 * Device configuration text is untrusted: rConfig stores whatever a device returns,
 * and anyone able to set a banner, interface description or ACL remark controls those
 * bytes. Any component that renders config text through `v-html` must escape it here
 * first, then let this module insert the highlight markup.
 */

/**
 * Escape the five characters that give text meaning inside HTML markup.
 */
export function escapeHtml(value: unknown): string {
	return String(value ?? "")
		.replaceAll("&", "&amp;")
		.replaceAll("<", "&lt;")
		.replaceAll(">", "&gt;")
		.replaceAll('"', "&quot;")
		.replaceAll("'", "&#39;");
}

/**
 * Escape regular expression metacharacters so a search term matches literally.
 */
export function escapeRegExp(value: unknown): string {
	return String(value ?? "").replace(/[.*+?^${}()|[\]\\]/g, "\\$&");
}

/**
 * Normalise a caller supplied term list: strings only, trimmed, empties dropped,
 * de-duplicated, and ordered longest first so a longer term wins over a prefix of itself.
 */
function normaliseTerms(terms: unknown): string[] {
	const list = Array.isArray(terms) ? terms : [];

	return [...new Set(list.map((term) => String(term ?? "").trim()).filter(Boolean))].sort((left, right) => right.length - left.length);
}

/**
 * `tag` is the element wrapped around each matched term; `className` is the class applied to it.
 */
type HighlightOptions = {
	tag?: string;
	className?: string;
};

/**
 * Return `sourceText` as HTML-escaped markup with every occurrence of `terms`
 * wrapped in a highlight element.
 *
 * Every emitted segment, the matched text included, is escaped. The only markup in the
 * result is the wrapper this function adds, so the output is always safe for `v-html`.
 * Newlines are left untouched, so a caller may convert them to `<br>` afterwards.
 */
export function highlightTerms(sourceText: unknown, terms: unknown, options: HighlightOptions = {}): string {
	const { tag = "mark", className = "" } = options;
	const text = String(sourceText ?? "");
	const normalisedTerms = normaliseTerms(terms);

	if (text.length === 0 || normalisedTerms.length === 0) {
		return escapeHtml(text);
	}

	const pattern = new RegExp(normalisedTerms.map(escapeRegExp).join("|"), "gi");
	const openTag = className ? `<${tag} class="${className}">` : `<${tag}>`;
	let highlighted = "";
	let lastIndex = 0;

	for (const match of text.matchAll(pattern)) {
		const matchText = match[0];
		const matchIndex = match.index ?? 0;

		highlighted += escapeHtml(text.slice(lastIndex, matchIndex));
		highlighted += `${openTag}${escapeHtml(matchText)}</${tag}>`;
		lastIndex = matchIndex + matchText.length;
	}

	if (lastIndex === 0) {
		return escapeHtml(text);
	}

	highlighted += escapeHtml(text.slice(lastIndex));

	return highlighted;
}
