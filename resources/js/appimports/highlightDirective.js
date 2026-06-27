import hljs from "highlight.js";

/**
 * Local replacement for the deprecated `vue3-highlightjs` directive.
 *
 * Highlights every `<code>` element inside the bound element using the current
 * highlight.js API (`highlightElement`) instead of the removed `highlightBlock`.
 * Re-highlights on update so reactive code content stays highlighted.
 */
function highlightNodes(el, binding) {
	const codeNodes = el.querySelectorAll("code");

	codeNodes.forEach((codeNode) => {
		if (typeof binding.value === "string") {
			codeNode.textContent = binding.value;
		}

		// Allow re-highlighting when the bound content changes reactively.
		delete codeNode.dataset.highlighted;

		hljs.highlightElement(codeNode);
	});
}

export const highlightDirective = {
	mounted: highlightNodes,
	updated: highlightNodes,
};
