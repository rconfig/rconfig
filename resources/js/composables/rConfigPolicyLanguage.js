import { reactive } from 'vue';

export default function userConfigPolicyLanguage() {
  return {
    // keywords: ['Building', 'configuration...', 'configuration :', 'service', 'enable secret', 'enable', 'disable', 'secret', 'platform', 'clock', 'timezone', 'no', 'aaa', 'version', 'hostname', 'boot-start-marker', 'boot-end-marker', 'snmp-server', 'abstract', 'continue', 'for', 'new', 'switch', 'assert', 'goto', 'do', 'if', 'private', 'this', 'break', 'protected', 'throw', 'else', 'public', 'enum', 'return', 'catch', 'try', 'interface', 'static', 'class', 'finally', 'const', 'super', 'while', 'true', 'false'],

    // typeKeywords: ['boolean', 'double', 'byte', 'int', 'short', 'char', 'void', 'long', 'float'],

    // operators: ['=', '>', '<', '!', '~', '?', ':', '==', '<=', '>=', '!=', '&&', '||', '++', '--', '+', '-', '*', '/', '&', '|', '^', '%', '<<', '>>', '>>>', '+=', '-=', '*=', '/=', '&=', '|=', '^=', '%=', '<<=', '>>=', '>>>='],

    // we include these common regular expressions
    // symbols: /[=><!~?:&|+\-*\/\^%]+/,

    // C# style strings
    // escapes: /\\(?:[abfnrtv\\"']|x[0-9A-Fa-f]{1,4}|u[0-9A-Fa-f]{4}|U[0-9A-Fa-f]{8})/,

    // The main tokenizer for our languages
    tokenizer: {
      root: [
        [/^\/\/.*/, 'comment'],
        [/^#\[.*$/, 'variable']
      ]
    }
  };
}
