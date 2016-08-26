/*! formstone v0.6.1 [core.js] 2015-05-22 | MIT License | formstone.it */

var Formstone=this.Formstone=function(a,b,c,d){"use strict";function e(a){m.Plugins[a].initialized||(m.Plugins[a].methods._setup.call(c),m.Plugins[a].initialized=!0)}function f(a,b,c,d){var e,f={raw:{}};d=d||{};for(e in d)d.hasOwnProperty(e)&&("classes"===a?(f.raw[d[e]]=b+"-"+d[e],f[d[e]]="."+b+"-"+d[e]):(f.raw[e]=d[e],f[e]=d[e]+"."+b));for(e in c)c.hasOwnProperty(e)&&("classes"===a?(f.raw[e]=c[e].replace(/{ns}/g,b),f[e]=c[e].replace(/{ns}/g,"."+b)):(f.raw[e]=c[e].replace(/.{ns}/g,""),f[e]=c[e].replace(/{ns}/g,b)));return f}function g(){var a,b={transition:"transitionend",MozTransition:"transitionend",OTransition:"otransitionend",WebkitTransition:"webkitTransitionEnd"},d=["transition","-webkit-transition"],e={transform:"transform",MozTransform:"-moz-transform",OTransform:"-o-transform",msTransform:"-ms-transform",webkitTransform:"-webkit-transform"},f="transitionend",g="",h="",i=c.createElement("div");for(a in b)if(b.hasOwnProperty(a)&&a in i.style){f=b[a],m.support.transition=!0;break}o.transitionEnd=f+".{ns}";for(a in d)if(d.hasOwnProperty(a)&&d[a]in i.style){g=d[a];break}m.transition=g;for(a in e)if(e.hasOwnProperty(a)&&e[a]in i.style){m.support.transform=!0,h=e[a];break}m.transform=h}function h(){m.windowWidth=m.$window.width(),m.windowHeight=m.$window.height(),p=l.startTimer(p,q,i)}function i(){for(var a in m.ResizeHandlers)m.ResizeHandlers.hasOwnProperty(a)&&m.ResizeHandlers[a].callback.call(b,m.windowWidth,m.windowHeight)}function j(a,b){return parseInt(a.priority)-parseInt(b.priority)}var k=function(){this.Version="0.6.1",this.Plugins={},this.ResizeHandlers=[],this.window=b,this.$window=a(b),this.document=c,this.$document=a(c),this.$body=null,this.windowWidth=0,this.windowHeight=0,this.userAgent=b.navigator.userAgent||b.navigator.vendor||b.opera,this.isFirefox=/Firefox/i.test(this.userAgent),this.isChrome=/Chrome/i.test(this.userAgent),this.isSafari=/Safari/i.test(this.userAgent)&&!this.isChrome,this.isMobile=/Android|webOS|iPhone|iPad|iPod|BlackBerry/i.test(this.userAgent),this.isFirefoxMobile=this.isFirefox&&this.isMobile,this.transform=null,this.transition=null,this.support={file:!!(b.File&&b.FileList&&b.FileReader),history:!!(b.history&&b.history.pushState&&b.history.replaceState),matchMedia:!(!b.matchMedia&&!b.msMatchMedia),raf:!(!b.requestAnimationFrame||!b.cancelAnimationFrame),touch:!!("ontouchstart"in b||b.DocumentTouch&&c instanceof b.DocumentTouch),transition:!1,transform:!1}},l={killEvent:function(a,b){try{a.preventDefault(),a.stopPropagation(),b&&a.stopImmediatePropagation()}catch(c){}},startTimer:function(a,b,c,d){return l.clearTimer(a),d?setInterval(c,b):setTimeout(c,b)},clearTimer:function(a,b){a&&(b?clearInterval(a):clearTimeout(a),a=null)},sortAsc:function(a,b){return parseInt(b)-parseInt(a)},sortDesc:function(a,b){return parseInt(b)-parseInt(a)}},m=new k,n={base:"{ns}",element:"{ns}-element"},o={namespace:".{ns}",blur:"blur.{ns}",change:"change.{ns}",click:"click.{ns}",dblClick:"dblclick.{ns}",drag:"drag.{ns}",dragEnd:"dragend.{ns}",dragEnter:"dragenter.{ns}",dragLeave:"dragleave.{ns}",dragOver:"dragover.{ns}",dragStart:"dragstart.{ns}",drop:"drop.{ns}",error:"error.{ns}",focus:"focus.{ns}",focusIn:"focusin.{ns}",focusOut:"focusout.{ns}",input:"input.{ns}",keyDown:"keydown.{ns}",keyPress:"keypress.{ns}",keyUp:"keyup.{ns}",load:"load.{ns}",mouseDown:"mousedown.{ns}",mouseEnter:"mouseenter.{ns}",mouseLeave:"mouseleave.{ns}",mouseMove:"mousemove.{ns}",mouseOut:"mouseout.{ns}",mouseOver:"mouseover.{ns}",mouseUp:"mouseup.{ns}",resize:"resize.{ns}",scroll:"scroll.{ns}",select:"select.{ns}",touchCancel:"touchcancel.{ns}",touchEnd:"touchend.{ns}",touchLeave:"touchleave.{ns}",touchMove:"touchmove.{ns}",touchStart:"touchstart.{ns}"};k.prototype.Plugin=function(c,d){return m.Plugins[c]=function(c,d){function g(b){var f="object"===a.type(b);b=a.extend(!0,{},d.defaults||{},f?b:{});for(var g=this,h=0,j=g.length;j>h;h++){var k=g.eq(h);if(!i(k)){var l=k.data(c+"-options"),m=a.extend(!0,{$el:k},b,"object"===a.type(l)?l:{});k.addClass(d.classes.raw.element).data(s,m),e(c),d.methods._construct.apply(k,[m].concat(Array.prototype.slice.call(arguments,f?1:0)))}}return g}function h(a){d.functions.iterate.apply(this,[d.methods._destruct].concat(Array.prototype.slice.call(arguments,1))),this.removeClass(d.classes.raw.element).removeData(s)}function i(a){return a.data(s)}function k(b){if(this instanceof a){var c=d.methods[b];return"object"!==a.type(b)&&b?c&&0!==b.indexOf("_")?d.functions.iterate.apply(this,[c].concat(Array.prototype.slice.call(arguments,1))):this:g.apply(this,arguments)}}function p(c){var e=d.utilities[c]||d.utilities._initialize||!1;return e?e.apply(b,Array.prototype.slice.call(arguments,"object"===a.type(c)?0:1)):void 0}function q(b){d.defaults=a.extend(!0,d.defaults,b||{})}function r(b){for(var c=this,d=0,e=c.length;e>d;d++){var f=c.eq(d),g=i(f)||{};"undefined"!==a.type(g.$el)&&b.apply(f,[g].concat(Array.prototype.slice.call(arguments,1)))}return c}var s="fs-"+c;return d.initialized=!1,d.priority=d.priority||10,d.classes=f("classes",s,n,d.classes),d.events=f("events",c,o,d.events),d.functions=a.extend({getData:i,iterate:r},l,d.functions),d.methods=a.extend(!0,{_setup:a.noop,_construct:a.noop,_destruct:a.noop,_resize:!1,destroy:h},d.methods),d.utilities=a.extend(!0,{_initialize:!1,_delegate:!1,defaults:q},d.utilities),d.widget&&(a.fn[c]=k),a[c]=d.utilities._delegate||p,d.namespace=c,d.methods._resize&&(m.ResizeHandlers.push({namespace:c,priority:d.priority,callback:d.methods._resize}),m.ResizeHandlers.sort(j)),d}(c,d),m.Plugins[c]};var p=null,q=20;return m.$window.on("resize.fs",h),h(),a(function(){m.$body=a("body");for(var b in m.Plugins)m.Plugins.hasOwnProperty(b)&&e(b)}),o.clickTouchStart=o.click+" "+o.touchStart,g(),m}(jQuery,this,document);
