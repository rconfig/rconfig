import { beforeEach, describe, expect, it, vi } from "vitest";
import { createPinia, setActivePinia } from "pinia";
import { mount } from "@vue/test-utils";
import { nextTick } from "vue";

/**
 * Regression cover for the stored XSS reported in rconfig/rconfig#368.
 *
 * `match.context` is raw device configuration text and is rendered through `v-html`.
 * Anyone who can set a banner, interface description or ACL remark on a monitored
 * device controls those bytes, so the dialog must render them as inert text.
 */

// Stand in for the shadcn/reka-ui primitives. They teleport their content out of the
// wrapper and add nothing to what is under test, so a pass-through keeps the assertions
// on the rendered <pre> rather than on the dialog chrome. Both factories are hoisted
// above the imports, so the helper has to be defined inside them.
vi.mock("@/components/ui/dialog", async () => {
	const { defineComponent, h } = await import("vue");
	const passThrough = (name: string) =>
		defineComponent({
			name,
			inheritAttrs: false,
			setup: (_props, { slots }) => () => h("div", slots.default?.()),
		});

	return {
		Dialog: passThrough("Dialog"),
		DialogContent: passThrough("DialogContent"),
		DialogDescription: passThrough("DialogDescription"),
		DialogFooter: passThrough("DialogFooter"),
		DialogHeader: passThrough("DialogHeader"),
		DialogTitle: passThrough("DialogTitle"),
		DialogTrigger: passThrough("DialogTrigger"),
	};
});

vi.mock("@/components/ui/select", async () => {
	const { defineComponent, h } = await import("vue");
	const passThrough = (name: string) =>
		defineComponent({
			name,
			inheritAttrs: false,
			setup: (_props, { slots }) => () => h("div", slots.default?.()),
		});

	return {
		Select: passThrough("Select"),
		SelectContent: passThrough("SelectContent"),
		SelectGroup: passThrough("SelectGroup"),
		SelectItem: passThrough("SelectItem"),
		SelectLabel: passThrough("SelectLabel"),
		SelectTrigger: passThrough("SelectTrigger"),
		SelectValue: passThrough("SelectValue"),
	};
});

// @ts-ignore — SFC, no type declarations
import PeekConfigSearchMatchesDialog from "../PeekConfigSearchMatchesDialog.vue";
// @ts-ignore — JS module, no type declarations
import { useDialogStore } from "@/stores/dialogActions";

const XSS_PAYLOAD = '<img src=x onerror="window.__xss_368=1">';
const EDIT_ID = 42;

async function mountPre(context: string[], searchTerms: string[] = ["hostname"]): Promise<HTMLElement> {
	useDialogStore().openDialog(`peek-config-search-matches-dialog-${EDIT_ID}`);

	const wrapper = mount(PeekConfigSearchMatchesDialog, {
		props: {
			editId: EDIT_ID,
			record: {
				file: "/var/lib/rconfig/data/sw1/show_run.txt",
				matches: [{ line_number: 1, context }],
			},
			searchTerms,
		},
		global: {
			stubs: { RcIcon: true, Button: true },
			directives: { highlightjs: {} },
		},
	});

	// `isLoading` is cleared in onMounted, so the <pre> only exists after a tick.
	await nextTick();

	return wrapper.find("pre").element as HTMLElement;
}

describe("PeekConfigSearchMatchesDialog", () => {
	beforeEach(() => {
		setActivePinia(createPinia());
		delete (window as unknown as Record<string, unknown>).__xss_368;
	});

	it("renders a poisoned config context as inert text, not live nodes", async () => {
		const pre = await mountPre(["hostname sw1", XSS_PAYLOAD, "interface Gi0/0"]);

		expect(pre.querySelector("img")).toBeNull();
		expect(pre.querySelector("script")).toBeNull();
		expect(pre.textContent).toContain(XSS_PAYLOAD);
		expect((window as unknown as Record<string, unknown>).__xss_368).toBeUndefined();
	});

	it("still highlights the matched term", async () => {
		const pre = await mountPre(["hostname sw1", XSS_PAYLOAD]);
		const marks = pre.querySelectorAll("span.highlightMatch");

		expect(marks.length).toBeGreaterThan(0);
		expect(marks[0].textContent).toBe("hostname");
	});

	it("escapes the payload when no search terms are supplied", async () => {
		// The pre-fix code returned this branch entirely unescaped.
		const pre = await mountPre([XSS_PAYLOAD], []);

		expect(pre.querySelector("img")).toBeNull();
		expect(pre.textContent).toContain(XSS_PAYLOAD);
	});

	it("escapes a search term that itself carries markup", async () => {
		const pre = await mountPre([`prefix ${XSS_PAYLOAD} suffix`], [XSS_PAYLOAD]);

		expect(pre.querySelector("img")).toBeNull();
		expect(pre.querySelector("span.highlightMatch")?.textContent).toBe(XSS_PAYLOAD);
	});

	it("preserves line breaks between context lines", async () => {
		const pre = await mountPre(["hostname sw1", "interface Gi0/0"]);

		expect(pre.querySelectorAll("br").length).toBe(1);
	});
});
