import js from "@eslint/js";
import pluginVue from "eslint-plugin-vue";
import globals from "globals";
import tsParser from "@typescript-eslint/parser";

export default [
	{
		ignores: [
			"public/**",
			"bootstrap/**",
			"storage/**",
			"vendor/**",
			"node_modules/**",
			"coverage/**",
			"public/build/**",
			"resources/js/components/ui/**",
			"resources/js/components/ui/**/*",
			"**/resources/js/components/ui/**",
		],
	},

	js.configs.recommended,
	...pluginVue.configs["flat/recommended"],
	{
		files: ["resources/js/**/*.{js,vue}"],
		languageOptions: {
			ecmaVersion: "latest",
			sourceType: "module",
			parserOptions: {
				parser: tsParser,
			},
			globals: {
				...globals.browser,
				...globals.node,
			},
		},
		rules: {
			"no-unused-vars": ["warn", { argsIgnorePattern: "^_" }],
			"no-console": "off",
			"no-debugger": "warn",
			"no-useless-escape": "off",
			"vue/multi-word-component-names": "off",
			"vue/no-mutating-props": "warn",
			"vue/no-unused-components": "warn",
			"vue/require-default-prop": "off",
			"vue/html-self-closing": "off",
			"vue/html-indent": ["warn", "tab", { "attribute": 1, "baseIndent": 1, "closeBracket": 0, "alignAttributesVertically": true, "ignores": [] }],
		},
	},
];
