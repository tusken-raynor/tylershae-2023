/* eslint-disable no-console */

const fs = require("fs");

class WPFunctionsPlugin {
  constructor({ path, disable = false, encoding = "utf8" }) {
    this.path = path;
    this.disable = disable;
    this.encoding = encoding;
  }

  apply(compiler) {
    if (this.disable) {
      return;
    }
    compiler.hooks.done.tap("WPFunctionsPlugin", (stats) => {
      fs.readFile(this.path, this.encoding, (errRead, data) => {
        if (errRead) {
          console.error(errRead);
        }

        const newData = data
          .replace("css/main.css", `main.${stats.hash}.css`)
          .replace("css/editor.css", `editor.${stats.hash}.css`)
          .replace("main.js", `main.${stats.hash}.js`)
          .replace("editor.js", `editor.${stats.hash}.js`);

        fs.writeFile(this.path, newData, (errWrite) => {
          if (errWrite) {
            console.error(errWrite);
          }
        });
      });
    });
  }
}

module.exports = WPFunctionsPlugin;
