import { reactive } from 'vue'


export default function userConfigLanguage() {
    function rConfigLagnDef() {
      return  {
            keywords: [
              'Building', 'configuration...', 'configuration :', 'service', 'enable secret',
              'enable', 'disable', 'secret', 'platform', 'clock', 'timezone', 'no', 'aaa', 'version',
              'hostname', 'boot-start-marker', 'boot-end-marker', 'snmp-server',
               'abstract', 'continue', 'for', 'new', 'switch', 'assert', 'goto', 'do',
              'if', 'private', 'this', 'break', 'protected', 'throw', 'else', 'public',
              'enum', 'return', 'catch', 'try', 'interface', 'static', 'class',
              'finally', 'const', 'super', 'while', 'true', 'false'
            ],
          
            typeKeywords: [
              'boolean', 'double', 'byte', 'int', 'short', 'char', 'void', 'long', 'float'
            ],
          
            operators: [
              '=', '>', '<', '!', '~', '?', ':', '==', '<=', '>=', '!=',
              '&&', '||', '++', '--', '+', '-', '*', '/', '&', '|', '^', '%',
              '<<', '>>', '>>>', '+=', '-=', '*=', '/=', '&=', '|=', '^=',
              '%=', '<<=', '>>=', '>>>='
            ],
          
            // we include these common regular expressions
            symbols:  /[=><!~?:&|+\-*\/\^%]+/,
          
            // C# style strings
            escapes: /\\(?:[abfnrtv\\"']|x[0-9A-Fa-f]{1,4}|u[0-9A-Fa-f]{4}|U[0-9A-Fa-f]{8})/,
          
            // The main tokenizer for our languages
            tokenizer: {
              root: [
                // identifiers and keywords
                [/[a-z_$][\w$]*/, { cases: { '@typeKeywords': 'keyword',
                                             '@keywords': 'keyword',
                                             '@default': 'identifier' } }],
                [/[A-Z][\w\$]*/, 'type.identifier' ],  // to show class names nicely
          
                // whitespace
                { include: '@whitespace' },
          
                // delimiters and operators
                [/[{}()\[\]]/, '@brackets'],
                [/[<>](?!@symbols)/, '@brackets'],
                [/@symbols/, { cases: { '@operators': 'operator',
                                        '@default'  : '' } } ],
          
                // @ annotations.
                // As an example, we emit a debugging log message on these tokens.
                // Note: message are supressed during the first load -- change some lines to see them.
                [/@\s*[a-zA-Z_\$][\w\$]*/, { token: 'annotation', log: 'annotation token: $0' }],
          
                // numbers
                [/\d*\.\d+([eE][\-+]?\d+)?/, 'number.float'],
                [/0[xX][0-9a-fA-F]+/, 'number.hex'],
                [/\d+/, 'number'],
          
                // delimiter: after number because of .\d floats
                [/[;,.]/, 'delimiter'],
          
                // strings
                [/"([^"\\]|\\.)*$/, 'string.invalid' ],  // non-teminated string
                [/"/,  { token: 'string.quote', bracket: '@open', next: '@string' } ],
          
                // characters
                [/'[^\\']'/, 'string'],
                [/(')(@escapes)(')/, ['string','string.escape','string']],
                [/'/, 'string.invalid']
              ],
          
              comment: [
                [/[^\/*]+/, 'comment' ],
                [/\/\*/,    'comment', '@push' ],    // nested comment
                ["\\*/",    'comment', '@pop'  ],
                [/[\/*]/,   'comment' ]
              ],
          
              string: [
                [/[^\\"]+/,  'string'],
                [/@escapes/, 'string.escape'],
                [/\\./,      'string.escape.invalid'],
                [/"/,        { token: 'string.quote', bracket: '@close', next: '@pop' } ]
              ],
          
              whitespace: [
                [/[ \t\r\n]+/, 'white'],
                [/\/\*/,       'comment', '@comment' ],
                [/\/\/.*$/,    'comment'],
              ],
            },
          };
    }
  
    return {
        rConfigLagnDef
    };
  };
  