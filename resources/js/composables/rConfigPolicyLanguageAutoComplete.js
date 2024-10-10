import { reactive } from 'vue';

export default function userConfigPolicyLanguageAutoComplete(monaco) {
  return [
    {
      label: 'must_match_single_string',
      kind: monaco.languages.CompletionItemKind.Text,
      insertText: '[must_match_single_string]'
    },
    {
      label: 'must_not_match_single_string',
      kind: monaco.languages.CompletionItemKind.Text,
      insertText: '[must_not_match_single_string]'
    },
    {
      label: 'must_match_regex',
      kind: monaco.languages.CompletionItemKind.Text,
      insertText: '[must_match_regex]'
    },
    {
      label: 'can_match_any_from_array',
      kind: monaco.languages.CompletionItemKind.Text,
      insertText: '[can_match_any_from_array]'
    },
    {
      label: 'must_match_all_from_array',
      kind: monaco.languages.CompletionItemKind.Text,
      insertText: '[must_match_all_from_array]'
    },
    {
      label: 'must_not_match_all_from_array',
      kind: monaco.languages.CompletionItemKind.Text,
      insertText: '[must_not_match_all_from_array]'
    },
    {
      label: 'wildcard_match_all_from_array',
      kind: monaco.languages.CompletionItemKind.Text,
      insertText: '[wildcard_match_all_from_array]'
    },
    {
      label: 'wildcard_match_any_from_array',
      kind: monaco.languages.CompletionItemKind.Text,
      insertText: '[wildcard_match_any_from_array]'
    },
    {
      label: 'wildcard_match_single_string',
      kind: monaco.languages.CompletionItemKind.Text,
      insertText: '[wildcard_match_single_string]'
    },
    {
      label: 'match_code_block_from_array',
      kind: monaco.languages.CompletionItemKind.Text,
      insertText: '[match_code_block_from_array]'
    }
  ];
}
