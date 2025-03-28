const fs = require("fs");
const path = require("path");

/**
 * @type {'attribute'|'class'}
 */
let SELECTOR_MODE = "attribute";
/**
 * @type {{[key:string]: Set<{file:string,update:boolean}>}}
 */
const PHP_FILE_LIBRARY = {};

/**
 *
 * @param {string} content
 * @this {{resourcePath: string}}
 * @returns
 */
module.exports = function (content) {
  // Set the selector mode from the options
  const options = this.getOptions();
  if (options.mode) {
    SELECTOR_MODE = options.mode;
  }
  // Make sure none of the scope rules override each other if they come from different resource paths
  const resourcePath = this.resourcePath;
  if (PHP_FILE_LIBRARY[resourcePath]) {
    PHP_FILE_LIBRARY[resourcePath].clear();
  } else {
    PHP_FILE_LIBRARY[resourcePath] = new Set();
  }

  const scopeRefMatches = content.match(
    /\/\*!\s*scope\s+with:[^\*]+\*\/[\s\S]+?\/\*!\s*end\s+scope\s*\*\//gi
  );
  if (scopeRefMatches) {
    for (let i = 0; i < scopeRefMatches.length; i++) {
      /**
       * @type {string}
       */
      const cssCode = scopeRefMatches[i];
      // Get the PHP (or perhaps other) file referenced in the scoping note
      const phpFilepath = cssCode
        .match(/\/\*!\s*scope\s+with:([^\*]+)\*\//i)[1]
        .trim();
      const hash = generateHash(phpFilepath);
      const scopingSelector =
        SELECTOR_MODE == "class" ? `.cls${hash}` : `[data-${hash}]`;
      // Scope the snippet of css code and remove the scoping note
      const scopedCSS = scopeCss(cssCode, scopingSelector).replace(
        /(\/\*!\s*scope\s+with:[^\*]+\*\/|\/\*!\s*end\s+scope\s*\*\/)/gi,
        ""
      );
      // Replace the old snippet of css with the new scoped version
      content = content.replace(cssCode, scopedCSS);
      // Add the filename to the reference so that the file can later be saved
      PHP_FILE_LIBRARY[resourcePath].add({
        file: phpFilepath,
        update: true,
      });
    }
  }
  // Remove any unused :scoped and :global modifiers
  return content.replace(/(:scoped|:global)/g, "");
};

class WPScopeBlocksPlugin {
  constructor({ path, disable = false, encoding = "utf8" }) {
    this.path = path;
    this.disable = disable;
    this.encoding = encoding;
  }

  apply(compiler) {
    if (this.disable) {
      return;
    }
    compiler.hooks.done.tap("WPScopeBlocksPlugin", (stats) => {
      for (const key in PHP_FILE_LIBRARY) {
        if (Object.hasOwnProperty.call(PHP_FILE_LIBRARY, key)) {
          const fileCollection = PHP_FILE_LIBRARY[key];
          fileCollection.forEach((filedata) => {
            const filename = filedata.file;
            if (
              filedata.update &&
              (filename.endsWith(".php") || filename.endsWith(".html"))
            ) {
              const filepath = path.resolve(this.path, filename);
              fs.readFile(filepath, "utf8", (err, data) => {
                if (err) return console.error(err);
                const hash = generateHash(filename);
                const newData = scopeMarkup(
                  data,
                  hash,
                  filename.endsWith(".php")
                );
                fs.writeFile(filepath, newData, (errWrite) => {
                  if (errWrite) {
                    console.error(errWrite);
                  }
                });
              });
            }
          });
        }
      }
    });
  }
}

module.exports.WPScopeBlocksPlugin = WPScopeBlocksPlugin;

function scopeCss(css, scopeSelector) {
  css = encryptStrings(css, scopeSelector).replace(/[^\{\};]+{/g, function (a) {
    if (a.includes("@") || a.trim().match(/^\d/)) return a;
    var selectors = a.replace("{", "").trimRight().split(",");
    var processed = [];
    for (var i = 0; i < selectors.length; i++) {
      var selector = selectors[i].trimRight();
      if (selector.includes(":scoped")) {
        selector = selector
          .replace(/:scoped?/g, scopeSelector)
          .replace(/:global/g, "");
      } else if (selector.includes(":global")) {
        selector = selector.replace(/:global/g, "");
      } else if (selector.match(/(:[:\w-]+)$/)) {
        selector = selector.replace(/(:[:\w-]+)$/, scopeSelector + "$1");
      } else {
        selector = selector + scopeSelector;
      }
      if (selector.includes("*" + scopeSelector)) {
        selector = selector.replace("*" + scopeSelector, scopeSelector);
      }
      processed.push(selector);
    }
    return processed.join() + "{";
  });
  return decryptStrings(css, scopeSelector);
}

/**
 *
 * @param {string} code
 * @param {string} hash
 * @param {boolean} isPHP
 */
function scopeMarkup(code, hash, isPHP = false) {
  code = code.replace(
    /new\s+FlexibleElement\(/g,
    `new FlexibleElementScoped(['hash'=>'${hash}','mode'=>'${SELECTOR_MODE}'], `
  );
  if (code.match(/\$SCOPE_HASH/)) {
    if (isPHP) {
      if (code.trim().startsWith("<?php")) {
        code = code.replace("<?php", `<?php\n$SCOPE_HASH='${hash}';`);
      } else {
        code = `<?php $SCOPE_HASH='${hash}'; ?>\n` + code;
      }
    } else {
      code = code.replace(/\$SCOPE_HASH/g, hash);
    }
  }
  if (SELECTOR_MODE == "class") {
    code = encryptMatches(code, /<\?(php|=)?[\s\S]+?\?>/g, hash + "php");
    code = encryptMatches(code, /class=['"][\s\S]+?['"]/g, hash + "class");
    code = encryptStrings(code, hash);
    code = code.replace(/<\w+[^>]*>/g, (m) => {
      m = decryptMatches(m, hash + "class");
      if (m.match(/[^\w]class=/)) {
        return m.replace(/([^\w\-_]class=['"].+?)(['"])/, `$1 cls${hash}$2`);
      } else {
        return m.replace(/<(\w+)(\s*)/, `<$1 class="cls${hash}"$2`);
      }
    });
    code = decryptMatches(code, hash + "class");
    code = decryptStrings(code, hash);
    code = decryptMatches(code, hash + "php");
    return code;
  } else {
    code = encryptMatches(code, /<\?(php|=)?[\s\S]+?\?>/g, hash + "php");
    code = encryptStrings(code, hash);
    code = code.replace(/<([\w-]+[\s\S]*?)>/g, `<$1 data-${hash}>`);
    code = decryptStrings(code, hash);
    code = decryptMatches(code, hash + "php");
    return code;
  }
}

/**
 *
 * @param {string} string
 */
function generateHash(string, length = 6) {
  var charset = "abcdefghijklmnopqrstuvwxyz0123456789";
  var num = string.length % charset.length;
  for (var i = 0; i < string.length; i++) {
    num += string.charCodeAt(i);
  }
  num = num % charset.length;
  var hash = "";
  for (var i = 0; i < length; i++) {
    var charCode = string.charCodeAt(
      Math.min(Math.round((i * string.length) / length), string.length - 1)
    );
    var char = charset[(charCode + num) % charset.length];
    hash += char;
    num =
      (num + charCode + char.charCodeAt(0) + i + string.length) %
      charset.length;
  }
  return hash;
}

function randomString(length) {
  length = length === undefined ? Math.floor(Math.random() * 16) + 8 : length;
  let result = "";
  const characters =
    "ABCDEFGHIJKLMNOPQRSTUVWXYZabcdefghijklmnopqrstuvwxyz0123456789";
  for (let i = 0; i < length; i++) {
    result += characters.charAt(Math.floor(Math.random() * characters.length));
  }
  return result;
}

/**
 * @type {Map<any, {[key: string]: string}}
 */
var stringEncryptions = new Map();

/**
 *
 * @param {string} code
 * @param {string} key
 */
function encryptStrings(code, key) {
  if (!stringEncryptions.has(key)) {
    stringEncryptions.set(key, {});
  }
  /**
   * @type {{[key: string]: string}}
   */
  var stringMap = stringEncryptions.get(key);
  var stringMatches = code.match(
    /'[^'\\]*(?:\\.[^'\\]*)*'|"[^"\\]*(?:\\.[^"\\]*)*"/gm
  );
  if (stringMatches) {
    for (var i = 0; i < stringMatches.length; i++) {
      var string = stringMatches[i];
      var index = "GSTR_MKEY" + randomString(8) + "GSTR_MKEY";
      stringMap[index] = string;
      code = code.replace(string, index);
    }
  }
  return code;
}

/**
 *
 * @param {string} code
 * @param {string} key
 */
function decryptStrings(code, key) {
  var stringMap = stringEncryptions.get(key);
  if (!stringMap) return code;
  for (var index in stringMap) {
    if (Object.hasOwnProperty.call(stringMap, index)) {
      var string = stringMap[index];
      if (code.includes(index)) {
        code = code.replace(index, string);
        delete stringMap[index];
      }
    }
  }
  if (!Object.values(stringMap).length) {
    stringEncryptions.delete(key);
  }
  return code;
}

/**
 *
 * @param {string} code
 * @param {RegExp} regex
 * @param {string} key
 */
function encryptMatches(code, regex, key) {
  if (!regex.flags.includes("g")) {
    throw new Error("Regular Expression must have a global (g) flag");
  }
  if (!stringEncryptions.has(key)) {
    stringEncryptions.set(key, {});
  }
  /**
   * @type {{[key: string]: string}}
   */
  var stringMap = stringEncryptions.get(key);
  var stringMatches = code.match(regex);
  if (stringMatches) {
    for (var i = 0; i < stringMatches.length; i++) {
      var string = stringMatches[i];
      var index = "GSTR_MKEY" + randomString(8) + "GSTR_MKEY";
      stringMap[index] = string;
      code = code.replace(string, index);
    }
  }
  return code;
}

/**
 *
 * @param {string} code
 * @param {string} key
 */
function decryptMatches(code, key) {
  var stringMap = stringEncryptions.get(key);
  if (!stringMap) return code;
  for (var index in stringMap) {
    if (Object.hasOwnProperty.call(stringMap, index)) {
      var string = stringMap[index];
      if (code.includes(index)) {
        code = code.replace(index, string);
        delete stringMap[index];
      }
    }
  }
  if (!Object.values(stringMap).length) {
    stringEncryptions.delete(key);
  }
  return code;
}
