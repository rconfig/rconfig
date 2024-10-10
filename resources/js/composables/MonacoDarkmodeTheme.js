import * as monaco from 'monaco-editor';

export default function useMonacoDarkmodeTheme(language = 'javascript') {
  // reset the theme
  const theme = monaco.editor.setTheme('vs-dark');

  // let lineHighlightBackground = '#255ab2';

  // // get page url to check if we need to adjust parts of the theme
  // const url = window.location.href;

  // if (url.includes('templates') || url.includes('eoc-definitions') || url.includes('policy-definitions')) {
  //     lineHighlightBackground = '#ecf0f5';
  // }

  // console.log(language);
  // if (language == 'javascript' || language == 'typescript' || language == 'json' || language == 'yaml') {
  //     // const theme = monaco.editor.defineTheme('vs-dark', {
  //     //     base: 'vs',
  //     //     inherit: true,
  //     //     rules: [],
  //     //     colors: {
  //     //         'editor.background': '#0e1117'
  //     //         // 'editor.foreground': '#ffffff'
  //     //     }
  //     // });
  //     return;
  // }
  // if (language == 'rconfig') {
  //     const theme = monaco.editor.defineTheme('vs-dark', {
  //         base: 'vs',
  //         inherit: true,
  //         rules: [],
  //         colors: {
  //             'editor.background': '#0e1117',
  //             'editor.foreground': '#ffffff',
  //             'dropdown.background': '#0e1117',
  //             'dropdown.border': '#21262d',
  //             'dropdown.foreground': '#ffffff',
  //             'editor.lineHighlightBackground': '#21262d',
  //             'editor.selectionBackground': lineHighlightBackground,
  //             'editor.inactiveSelectionBackground': '#21262d',
  //             'editor.selectionHighlightBackground': '#21262d',
  //             'editorCursor.foreground': '#ffffff',
  //             'editor.findMatchBackground': '#21262d',
  //             'editorWidget.background': '#21262d',
  //             'editorWidget.foreground': '#fff',
  //             'editor.findMatchHighlightBackground': '#5936a3',
  //             'diffEditor.insertedTextBackground': '#3d914a',
  //             'diffEditor.removedTextBackground': '#a31515',
  //             'editorGutter.modifiedBackground': '#3d914a',
  //             'editorGutter.addedBackground': '#3d914a'
  //         }
  //     });
  // }

  return theme;
}
