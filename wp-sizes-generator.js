(function () {
  var round = function (num, p) {
    p = p === undefined ? 1 : Math.pow(10, p);
    return Math.round(num * p) / p;
  };
  var sg = {};
  var data = null;
  var sd = null;
  sg.init = function (selector, dflt) {
    /**
     * @type {HTMLImageElement}
     */
    var el = document.querySelector(selector);
    if (!el)
      throw new Error(
        "Selector must return a valid Element upon query but null was returned."
      );
    data = {};
    sd = null;
    if (dflt) {
      data.dflt = dflt;
    }
    sg.capture = function (mode) {
      var rect = el.getBoundingClientRect();
      if (mode == "start") {
        sd = {};
        sd.screen = innerWidth;
        sd.width = rect.width;
      } else if (mode == "end" && sd) {
        if (innerWidth != sd.screen) {
          data[
            "(max-width:" +
              sd.screen +
              "px) and (min-width:" +
              innerWidth +
              "px)"
          ] =
            "calc((100vw - " +
            innerWidth +
            "px) / " +
            round((sd.screen - innerWidth) / (sd.width - rect.width), 4) +
            " + " +
            round(rect.width) +
            "px)";
          sd = null;
        }
      } else if (mode == "propotion" || mode == "p") {
        data["(max-width:" + innerWidth + "px)"] =
          round((rect.width / innerWidth) * 100, 4) + "vw";
      } else {
        data["(max-width:" + innerWidth + "px)"] = round(rect.width) + "px";
      }
    };
    document.body.append(tool);
    requestAnimationFrame(function () {
      tool.classList.add("visible");
    });
  };
  sg.out = function (r) {
    if (data) {
      var s = [];
      var dflt = "";
      var mqs = Object.keys(data);
      for (var i = 0; i < mqs.length; i++) {
        var mq = mqs[i];
        var sz = data[mq];
        if (mq == "dflt") {
          dflt = sz;
          continue;
        }
        s.push(mq + sz);
      }
      if (dflt) {
        s.push(dflt);
      }
      if (r) {
        return s.join();
      } else {
        console.log("'" + s.join() + "'");
      }
    } else {
      throw new Error("Size Generator must be initialized.");
    }
  };
  sg.reset = function () {
    var dflt = data.dflt;
    data = {};
    if (dflt) {
      data.dflt = dflt;
    }
    sd = null;
  };

  window.sizesGenerator = sg;

  var tool = document.createElement("div");
  tool.id = "sizes-generator-tool";
  tool.innerHTML =
    '<div id="sg-capture"><div id="sg-capture-menu-open"><div></div></div><div id="sg-capture-menu"><div id="sg-capture-standard"></div><div id="sg-capture-proportional"></div><div id="sg-capture-transitional"></div></div></div><div id="sg-reset"></div><div id="sg-out"><textarea></textarea></div><div id="sg-exit"></div>' +
    '<style>#sizes-generator-tool{position:fixed;bottom:10px;right:10px;z-index:200;background-color:#fff;padding:26px 12px 12px;box-shadow:0 0 7px 0 #0004;width:260px;border-radius:10px;font:600 20px/120% sans-serif;transition:opacity .2s linear;opacity:0}#sizes-generator-tool.visible{opacity:1}#sg-capture,#sg-capture-menu-open,#sg-capture-proportional,#sg-capture-standard,#sg-capture-transitional,#sg-out,#sg-reset{text-align:center;background-color:#476b9b;padding:8px 10px;color:#fff;border-radius:8px;box-sizing:border-box;cursor:pointer;transition:background-color .2s linear;margin-bottom:4px}#sg-capture-menu-open:hover,#sg-capture-proportional:hover,#sg-capture-standard:hover,#sg-capture-transitional:hover,#sg-capture:hover,#sg-out:hover,#sg-reset:hover{background-color:#6685ad}#sg-capture{position:relative;width:calc(100% - 42px);border-top-right-radius:0;border-bottom-right-radius:0}#sg-capture::after,#sg-capture::before{transition:opacity .2s linear}#sg-capture::before{content:"Capture"}.trans-capture #sg-capture::before{opacity:0}#sg-capture::after{content:"End Transition";position:absolute;width:100%;top:50%;left:50%;transform:translate(-50%,-50%);opacity:0}.trans-capture #sg-capture::after{opacity:1}#sg-capture>textarea{position:absolute;width:0;height:0;opacity:0;z-index:-1}#sg-capture-menu-open{width:41px;position:absolute;top:0;bottom:0;left:calc(100% + 1px);border-top-left-radius:0;border-bottom-left-radius:0;margin-bottom:0}#sg-capture-menu-open>div,#sg-capture-menu-open>div::after,#sg-capture-menu-open>div::before{width:26px;height:4px;border-radius:100px;background-color:#fff;position:absolute;left:calc(50% - 13px);top:calc(50% - 2px)}#sg-capture-menu-open>div::before{content:"";transform:translateY(-8px)}#sg-capture-menu-open>div::after{content:"";transform:translateY(8px)}#sg-capture-menu{position:absolute;width:calc(100% + 42px);bottom:calc(100% + 8px);left:0;z-index:20;padding:14px 8px;background-color:#fff;box-sizing:border-box;border-radius:8px;box-shadow:0 0 7px 0 #0004;font-size:16px;font-weight:500;opacity:0;transform:scaleY(0);transform-origin:bottom;transition:opacity 50ms linear .23s,transform .2s ease 80ms}#sg-capture-menu>*{opacity:0;transition:opacity .1s linear}.menu-open #sg-capture-menu{opacity:1;transform:scaleY(1);transition:opacity 50ms linear,transform .2s ease}.menu-open #sg-capture-menu>*{opacity:1;transition:opacity .1s linear .15s}#sg-capture-standard::before{content:"Standard Capture"}#sg-capture-proportional::before{content:"Proportional Capture"}#sg-capture-transitional{position:relative}#sg-capture-transitional::after,#sg-capture-transitional::before{transition:opacity .1s linear}#sg-capture-transitional::before{content:"Transitional Capture"}.trans-capture #sg-capture-transitional::before{opacity:0}#sg-capture-transitional::after{content:"End Transition";position:absolute;width:100%;top:50%;left:50%;transform:translate(-50%,-50%);opacity:0}.trans-capture #sg-capture-transitional::after{opacity:1}#sg-reset::before{content:"Reset"}#sg-out{position:relative;margin-bottom:0}#sg-out::after,#sg-out::before{transition:opacity .2s linear}#sg-out::before{content:"Copy Sizes"}.disable-out #sg-out::before{opacity:0}#sg-out::after{content:"Copied!";position:absolute;width:100%;top:50%;left:50%;transform:translate(-50%,-50%);opacity:0}.disable-out #sg-out::after{opacity:1}#sg-out>textarea{position:absolute;width:0;height:0;opacity:0;z-index:-1}#sg-exit{position:absolute;top:0;right:0;width:26px;height:26px;cursor:pointer}#sg-exit::after,#sg-exit::before{content:"";background-color:#476b9b;transition:background-color .2s linear;width:18px;height:4px;position:absolute;top:calc(50% - 2px);left:calc(50% - 9px);border-radius:1px}#sg-exit:hover::after,#sg-exit:hover::before{background-color:#6685ad}#sg-exit::before{transform:rotate(-45deg)}#sg-exit::after{transform:rotate(45deg)}</style>';
  var captureBtn = tool.querySelector("#sg-capture");
  var captureBtnStd = tool.querySelector("#sg-capture-standard");
  var captureBtnPro = tool.querySelector("#sg-capture-proportional");
  var captureBtnTrn = tool.querySelector("#sg-capture-transitional");
  var captureMenu = tool.querySelector("#sg-capture-menu-open");
  var captureMenuL = tool.querySelector("#sg-capture-menu");
  var outBtn = tool.querySelector("#sg-out");
  var resetBtn = tool.querySelector("#sg-reset");
  var exitBtn = tool.querySelector("#sg-exit");

  var mcf = function () {
    setTimeout(function () {
      tool.classList.remove("menu-open");
    }, 700);
    return true;
  };
  captureBtn.addEventListener("click", function () {
    mcf();
    if (tool.classList.contains("trans-capture")) {
      tool.classList.remove("trans-capture");
      data && sg.capture && sg.capture("end");
    } else {
      data && sg.capture && sg.capture();
    }
  });
  captureBtnStd.addEventListener("click", function () {
    mcf() && data && sg.capture && sg.capture();
  });
  captureBtnPro.addEventListener("click", function () {
    mcf() && data && sg.capture && sg.capture("p");
  });
  captureBtnTrn.addEventListener("click", function () {
    mcf();
    if (tool.classList.contains("trans-capture")) {
      tool.classList.remove("trans-capture");
      data && sg.capture && sg.capture("end");
    } else {
      tool.classList.add("trans-capture");
      data && sg.capture && sg.capture("start");
    }
  });
  captureMenu.addEventListener("click", function (e) {
    e.stopPropagation();
    tool.classList.toggle("menu-open");
  });
  captureMenuL.addEventListener("click", function (e) {
    e.stopPropagation();
  });
  resetBtn.addEventListener("click", function () {
    sg.reset();
  });
  outBtn.addEventListener("click", function () {
    if (!tool.classList.contains("disable-out")) {
      outBtn.firstElementChild.value = sg.out(true);
      outBtn.firstElementChild.select();
      document.execCommand("copy");
      tool.classList.add("disable-out");
      setTimeout(function () {
        tool.classList.remove("disable-out");
      }, 1400);
    }
  });
  exitBtn.addEventListener("click", function () {
    tool.classList.remove("visible");
    setTimeout(function () {
      tool.className = "";
      tool.remove();
    }, 200);
  });
})();
