import { describe, expect, it, vi } from "vitest";

// Stub the underlying UI modules so the registry exercises only its own re-export wiring.
vi.mock("@/pages/Shared/Badges/RcBadge.vue", () => ({ default: { name: "RcBadge" } }));
vi.mock("@/components/ui/alert", () => ({ Alert: {}, AlertDescription: {} }));
vi.mock("@/components/ui/badge", () => ({ Badge: {} }));
vi.mock("@/components/ui/button", () => ({ Button: {} }));
vi.mock("@/components/ui/checkbox", () => ({ Checkbox: {} }));
vi.mock("@/components/ui/input", () => ({ Input: {} }));
vi.mock("@/components/ui/input-password", () => ({ InputPassword: {} }));
vi.mock("@/components/ui/label", () => ({ Label: {} }));
vi.mock("@/components/ui/resizable", () => ({ ResizableHandle: {}, ResizablePanel: {}, ResizablePanelGroup: {} }));
vi.mock("@/components/ui/separator", () => ({ Separator: {} }));
vi.mock("@/components/ui/skeleton", () => ({ Skeleton: {} }));
vi.mock("@/components/ui/switch", () => ({ Switch: {} }));
vi.mock("@/components/ui/textarea", () => ({ Textarea: {} }));
vi.mock("@/components/ui/toast", () => ({ Toaster: {} }));
vi.mock("@/pages/Shared/Buttons/KeyboardShortcut.vue", () => ({ default: { name: "KeyboardShortcut" } }));

import * as uiImports from "../uiComponentImports";

const EXPECTED_KEYS = [
	"Alert",
	"AlertDescription",
	"Badge",
	"Button",
	"Checkbox",
	"Input",
	"InputPassword",
	"KeyboardShortcut",
	"Label",
	"RcBadge",
	"ResizableHandle",
	"ResizablePanel",
	"ResizablePanelGroup",
	"Separator",
	"Skeleton",
	"Switch",
	"Textarea",
	"Toaster",
];

describe("uiComponentImports", () => {
	it("re-exports the full set of UI components", () => {
		expect(Object.keys(uiImports).sort()).toEqual([...EXPECTED_KEYS].sort());
	});

	it("defines a value for each exported key", () => {
		for (const key of EXPECTED_KEYS) {
			expect(uiImports[key as keyof typeof uiImports]).toBeDefined();
		}
	});
});
