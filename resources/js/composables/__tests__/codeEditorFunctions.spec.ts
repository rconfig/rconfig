import { afterEach, beforeEach, describe, expect, it, vi } from "vitest";
import { defineComponent, ref } from "vue";
import { mount } from "@vue/test-utils";

// The editor composable pulls in heavy, browser-only modules. Stub them all so
// the unit under test is just the settings + helper logic.
const { saveAs, darkModeTheme, copyItem } = vi.hoisted(() => ({
	saveAs: vi.fn(),
	darkModeTheme: vi.fn(),
	copyItem: vi.fn(),
}));
vi.mock("file-saver", () => ({ saveAs }));
vi.mock("@/composables/MonacoDarkmodeTheme.js", () => ({ default: darkModeTheme }));
vi.mock("@/composables/useCopy", () => ({
	useCopy: () => ({ copyItem, activeCopyIcon: ref({}) }),
}));

// Monaco language contributions are side-effect imports; neutralise them.
vi.mock("monaco-editor/esm/vs/basic-languages/javascript/javascript.contribution", () => ({}));
vi.mock("monaco-editor/esm/vs/basic-languages/yaml/yaml.contribution", () => ({}));
vi.mock("monaco-editor/esm/vs/basic-languages/xml/xml.contribution", () => ({}));

// @ts-ignore — composable is JS, no type declarations
import useCodeEditor from "../codeEditorFunctions";

const fakeMonaco = {
	editor: {
		setTheme: vi.fn(),
		create: vi.fn(),
		createDiffEditor: vi.fn(),
	},
};

/**
 * Mount the composable inside a throwaway component so lifecycle hooks
 * (onUnmounted) have an active instance, and return its public API.
 */
function setupEditor() {
	let api: any;
	const wrapper = mount(
		defineComponent({
			setup() {
				api = useCodeEditor(fakeMonaco);
				return () => null;
			},
		})
	);
	return { api, wrapper };
}

describe("useCodeEditor", () => {
	beforeEach(() => {
		localStorage.clear();
		vi.clearAllMocks();
	});

	afterEach(() => {
		vi.useRealTimers();
	});

	describe("dark mode", () => {
		it("defaults to the light theme and seeds localStorage when unset", () => {
			const { api } = setupEditor();

			expect(api.checkDarkModeIsSet()).toBe(false);
			expect(api.darkmode.value).toBe("vs");
			expect(localStorage.getItem("rConfig.editordarkmode")).toBe("vs");
		});

		it("reports dark mode when localStorage already holds vs-dark", () => {
			localStorage.setItem("rConfig.editordarkmode", "vs-dark");
			const { api } = setupEditor();

			expect(api.checkDarkModeIsSet()).toBe(true);
			expect(darkModeTheme).toHaveBeenCalled();
		});

		it("toggles from light to dark, applying the monaco theme", () => {
			const { api } = setupEditor();

			api.toggleEditorDarkMode();

			expect(api.darkmode.value).toBe("vs-dark");
			expect(localStorage.getItem("rConfig.editordarkmode")).toBe("vs-dark");
			expect(fakeMonaco.editor.setTheme).toHaveBeenCalledWith("vs-dark");
		});

		it("toggles back from dark to light", () => {
			localStorage.setItem("rConfig.editordarkmode", "vs-dark");
			const { api } = setupEditor();

			api.toggleEditorDarkMode();

			expect(api.darkmode.value).toBe("vs");
			expect(fakeMonaco.editor.setTheme).toHaveBeenCalledWith("vs");
		});
	});

	describe("line numbers", () => {
		it("defaults to on and seeds localStorage", () => {
			const { api } = setupEditor();

			expect(api.checkLineNumbersIsSet()).toBe(true);
			expect(localStorage.getItem("rConfig.editorlineNumbers")).toBe("on");
		});

		it("reads an existing off preference", () => {
			localStorage.setItem("rConfig.editorlineNumbers", "off");
			const { api } = setupEditor();

			expect(api.checkLineNumbersIsSet()).toBe(false);
		});
	});

	describe("minimap", () => {
		it("defaults to disabled and seeds localStorage", () => {
			const { api } = setupEditor();

			expect(api.checkMiniMapIsSet()).toBe(false);
			expect(localStorage.getItem("rConfig.editorMinimap")).toBe("false");
		});

		it("reads an enabled preference", () => {
			localStorage.setItem("rConfig.editorMinimap", "true");
			const { api } = setupEditor();

			expect(api.checkMiniMapIsSet()).toBe(true);
		});
	});

	describe("sticky scroll", () => {
		it("defaults to disabled and seeds localStorage", () => {
			const { api } = setupEditor();

			expect(api.checkStickyScrollIsSet()).toBe(false);
			expect(localStorage.getItem("rConfig.editorStickyScroll")).toBe("false");
		});

		it("reads an enabled preference", () => {
			localStorage.setItem("rConfig.editorStickyScroll", "true");
			const { api } = setupEditor();

			expect(api.checkStickyScrollIsSet()).toBe(true);
		});
	});

	describe("download + copy helpers", () => {
		it("downloadCompare saves a string blob under the given filename", () => {
			vi.useFakeTimers();
			const { api } = setupEditor();

			api.downloadCompare("hello world", "diff.txt", "k1");

			expect(saveAs).toHaveBeenCalledTimes(1);
			expect(saveAs.mock.calls[0][1]).toBe("diff.txt");
			expect(api.activeCopyIcon.value["k1"]).toBe(true);

			vi.advanceTimersByTime(2000);
			expect(api.activeCopyIcon.value["k1"]).toBe(false);
		});

		it("downloadCompare serialises object content and defaults the filename", () => {
			const { api } = setupEditor();

			api.downloadCompare({ a: 1 });

			expect(saveAs.mock.calls[0][1]).toBe("compare.txt");
		});

		it("copyContent and copyPath delegate to the clipboard helper", () => {
			const { api } = setupEditor();

			api.copyContent("running-config", "cfg");
			api.copyPath("/configs/r1.txt", "path");

			expect(copyItem).toHaveBeenCalledWith("cfg", "running-config");
			expect(copyItem).toHaveBeenCalledWith("path", "/configs/r1.txt");
		});
	});
});
