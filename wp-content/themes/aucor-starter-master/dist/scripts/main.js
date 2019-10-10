!function(){"use strict";if("undefined"!=typeof window&&window.addEventListener){var e,t,n,v=Object.create(null),h=function(){clearTimeout(t),t=setTimeout(e,100)},g=function(){},y=function(e){function t(e){var t;return void 0!==e.protocol?t=e:(t=document.createElement("a")).href=e,t.protocol.replace(/:/g,"")+t.host}var n,i,o;return window.XMLHttpRequest&&(n=new XMLHttpRequest,i=t(location),o=t(e),n=void 0===n.withCredentials&&""!==o&&o!==i?XDomainRequest||void 0:XMLHttpRequest),n},w="http://www.w3.org/1999/xlink";e=function(){var e,t,n,i,o,r,a,s,l,d,u=0;function c(){var e;0===(u-=1)&&(g(),window.addEventListener("resize",h,!1),window.addEventListener("orientationchange",h,!1),g=window.MutationObserver?((e=new MutationObserver(h)).observe(document.documentElement,{childList:!0,subtree:!0,attributes:!0}),function(){try{e.disconnect(),window.removeEventListener("resize",h,!1),window.removeEventListener("orientationchange",h,!1)}catch(e){}}):(document.documentElement.addEventListener("DOMSubtreeModified",h,!1),function(){document.documentElement.removeEventListener("DOMSubtreeModified",h,!1),window.removeEventListener("resize",h,!1),window.removeEventListener("orientationchange",h,!1)}))}function f(e){return function(){!0!==v[e.base]&&(e.useEl.setAttributeNS(w,"xlink:href","#"+e.hash),e.useEl.hasAttribute("href")&&e.useEl.setAttribute("href","#"+e.hash))}}function p(i){return function(){var e,t=document.body,n=document.createElement("x");i.onload=null,n.innerHTML=i.responseText,(e=n.getElementsByTagName("svg")[0])&&(e.setAttribute("aria-hidden","true"),e.style.position="absolute",e.style.width=0,e.style.height=0,e.style.overflow="hidden",t.insertBefore(e,t.firstChild)),c()}}function m(e){return function(){e.onerror=null,e.ontimeout=null,c()}}for(g(),l=document.getElementsByTagName("use"),o=0;o<l.length;o+=1){try{t=l[o].getBoundingClientRect()}catch(e){t=!1}e=(s=(i=l[o].getAttribute("href")||l[o].getAttributeNS(w,"href")||l[o].getAttribute("xlink:href"))&&i.split?i.split("#"):["",""])[0],n=s[1],r=t&&0===t.left&&0===t.right&&0===t.top&&0===t.bottom,t&&0===t.width&&0===t.height&&!r?(l[o].hasAttribute("href")&&l[o].setAttributeNS(w,"xlink:href",i),e.length&&(!0!==(d=v[e])&&setTimeout(f({useEl:l[o],base:e,hash:n}),0),void 0===d&&void 0!==(a=y(e))&&(d=new a,(v[e]=d).onload=p(d),d.onerror=m(d),d.ontimeout=m(d),d.open("GET",e),d.send(),u+=1))):r?e.length&&v[e]&&setTimeout(f({useEl:l[o],base:e,hash:n}),0):void 0===v[e]?v[e]=!0:v[e].onload&&(v[e].abort(),delete v[e].onload,v[e]=!0)}l="",u+=1,c()},n=function(){window.removeEventListener("load",n,!1),t=setTimeout(e,0)},"complete"!==document.readyState?window.addEventListener("load",n,!1):n()}}(),function(){"use strict";if("undefined"!=typeof window){var e=window.navigator.userAgent.match(/Edge\/(\d{2})\./),i=!!e&&16<=parseInt(e[1],10);if("objectFit"in document.documentElement.style!=0&&!i)return window.objectFitPolyfill=function(){return!1};var d=function(e,t,n){var i,o,r,a,s;if((n=n.split(" ")).length<2&&(n[1]=n[0]),"x"===e)i=n[0],o=n[1],r="left",a="right",s=t.clientWidth;else{if("y"!==e)return;i=n[1],o=n[0],r="top",a="bottom",s=t.clientHeight}return i===r||o===r?void(t.style[r]="0"):i===a||o===a?void(t.style[a]="0"):"center"===i||"50%"===i?(t.style[r]="50%",void(t.style["margin-"+r]=s/-2+"px")):0<=i.indexOf("%")?void((i=parseInt(i))<50?(t.style[r]=i+"%",t.style["margin-"+r]=s*(i/-100)+"px"):(i=100-i,t.style[a]=i+"%",t.style["margin-"+a]=s*(i/-100)+"px")):void(t.style[r]=i)},o=function(e){var t=e.dataset?e.dataset.objectFit:e.getAttribute("data-object-fit"),n=e.dataset?e.dataset.objectPosition:e.getAttribute("data-object-position");t=t||"cover",n=n||"50% 50%";var i,o,r,a,s,l=e.parentNode;i=l,o=window.getComputedStyle(i,null),r=o.getPropertyValue("position"),a=o.getPropertyValue("overflow"),s=o.getPropertyValue("display"),r&&"static"!==r||(i.style.position="relative"),"hidden"!==a&&(i.style.overflow="hidden"),s&&"inline"!==s||(i.style.display="block"),0===i.clientHeight&&(i.style.height="100%"),-1===i.className.indexOf("object-fit-polyfill")&&(i.className=i.className+" object-fit-polyfill"),function(e){var t=window.getComputedStyle(e,null),n={"max-width":"none","max-height":"none","min-width":"0px","min-height":"0px",top:"auto",right:"auto",bottom:"auto",left:"auto","margin-top":"0px","margin-right":"0px","margin-bottom":"0px","margin-left":"0px"};for(var i in n)t.getPropertyValue(i)!==n[i]&&(e.style[i]=n[i])}(e),e.style.position="absolute",e.style.height="100%",e.style.width="auto","scale-down"===t&&(e.style.height="auto",e.clientWidth<l.clientWidth&&e.clientHeight<l.clientHeight?(d("x",e,n),d("y",e,n)):(t="contain",e.style.height="100%")),"none"===t?(e.style.width="auto",e.style.height="auto",d("x",e,n),d("y",e,n)):"cover"===t&&e.clientWidth>l.clientWidth||"contain"===t&&e.clientWidth<l.clientWidth?(e.style.top="0",e.style.marginTop="0",d("x",e,n)):"scale-down"!==t&&(e.style.width="100%",e.style.height="auto",e.style.left="0",e.style.marginLeft="0",d("y",e,n))},t=function(e){if(void 0===e)e=document.querySelectorAll("[data-object-fit]");else if(e&&e.nodeName)e=[e];else{if("object"!=typeof e||!e.length||!e[0].nodeName)return!1;e=e}for(var t=0;t<e.length;t++)if(e[t].nodeName){var n=e[t].nodeName.toLowerCase();"img"!==n||i?"video"===n&&(0<e[t].readyState?o(e[t]):e[t].addEventListener("loadedmetadata",function(){o(this)})):e[t].complete?o(e[t]):e[t].addEventListener("load",function(){o(this)})}return!0};document.addEventListener("DOMContentLoaded",function(){t()}),window.addEventListener("resize",function(){t()}),window.objectFitPolyfill=t}}(),function(e){if("object"==typeof exports&&"undefined"!=typeof module)module.exports=e();else if("function"==typeof define&&define.amd)define([],e);else{("undefined"!=typeof window?window:"undefined"!=typeof global?global:"undefined"!=typeof self?self:this).fitvids=e()}}(function(){return function r(a,s,l){function d(n,e){if(!s[n]){if(!a[n]){var t="function"==typeof require&&require;if(!e&&t)return t(n,!0);if(u)return u(n,!0);var i=new Error("Cannot find module '"+n+"'");throw i.code="MODULE_NOT_FOUND",i}var o=s[n]={exports:{}};a[n][0].call(o.exports,function(e){var t=a[n][1][e];return d(t||e)},o,o.exports,r,a,s,l)}return s[n].exports}for(var u="function"==typeof require&&require,e=0;e<l.length;e++)d(l[e]);return d}({1:[function(e,t,n){"use strict";var l=['iframe[src*="player.vimeo.com"]','iframe[src*="youtube.com"]','iframe[src*="youtube-nocookie.com"]','iframe[src*="kickstarter.com"][src*="video.html"]',"object"];function d(e,t){return"string"==typeof e&&(t=e,e=document),Array.prototype.slice.call(e.querySelectorAll(t))}function u(e){return"string"==typeof e?e.split(",").map(i).filter(c):(n=e,"[object Array]"===Object.prototype.toString.call(n)?(t=e.map(u).filter(c),[].concat.apply([],t)):e||[]);var t,n}function c(e){return 0<e.length}function i(e){return e.replace(/^\s+|\s+$/g,"")}t.exports=function(e,t){var n;t=t||{},n=e=e||"body","[object Object]"===Object.prototype.toString.call(n)&&(t=e,e="body"),t.ignore=t.ignore||"",t.players=t.players||"";var i=d(e);if(c(i)){var o;if(!document.getElementById("fit-vids-style"))(document.head||document.getElementsByTagName("head")[0]).appendChild(((o=document.createElement("div")).innerHTML='<p>x</p><style id="fit-vids-style">.fluid-width-video-wrapper{width:100%;position:relative;padding:0;}.fluid-width-video-wrapper iframe,.fluid-width-video-wrapper object,.fluid-width-video-wrapper embed {position:absolute;top:0;left:0;width:100%;height:100%;}</style>',o.childNodes[1]));var r=u(t.players)||[],a=u(t.ignore)||[],s=l.filter(function(t){if(t.length<1)return function(){return!0};return function(e){return-1===t.indexOf(e)}}(a)).concat(r).join();c(s)&&i.forEach(function(e){d(e,s).forEach(function(e){!function(e){if(/fluid-width-video-wrapper/.test(e.parentNode.className))return;var t=parseInt(e.getAttribute("width"),10),n=parseInt(e.getAttribute("height"),10),i=isNaN(t)?e.clientWidth:t,o=(isNaN(n)?e.clientHeight:n)/i;e.removeAttribute("width"),e.removeAttribute("height");var r=document.createElement("div");e.parentNode.insertBefore(r,e),r.className="fluid-width-video-wrapper",r.style.paddingTop=100*o+"%",r.appendChild(e)}(e)})})}}},{}]},{},[1])(1)});var aucor_navigation=function(d,e){var s,t=function(e,t){var n,i={};for(n in e)Object.prototype.hasOwnProperty.call(e,n)&&(i[n]=e[n]);for(n in t)Object.prototype.hasOwnProperty.call(t,n)&&(i[n]=t[n]);return i}({desktop_min_width:501,menu_toggle:"#menu-toggle"},e),n=t.desktop_min_width,i=document.querySelector(t.menu_toggle);function u(){return!(Math.max(document.documentElement.clientWidth,window.innerWidth||0)<n)}for(var o=function(e){if(u()){clearTimeout(s);for(var t=[],n=this.parentElement;!n.isEqualNode(d);)n.classList.contains("sub-menu")&&t.push(n),n=n.parentElement;for(var i=this.querySelectorAll(".sub-menu"),o=0;o<i.length;o++)t.push(i[o]);for(var r=d.querySelectorAll(".open"),a=0;a<r.length;a++)-1===t.indexOf(r[a])&&r[a].classList.remove("open");this.querySelector(".sub-menu")&&this.querySelector(".sub-menu").classList.add("open")}},r=function(e){var t=this;u()&&(s=setTimeout(function(){for(var e=t.parentElement;!e.isEqualNode(d);)e.classList.remove("open"),e=e.parentElement;t.querySelector(".open")&&t.querySelector(".open").classList.remove("open")},750))},a=function(e){for(var t=null,n=e.target.parentElement;!n.isEqualNode(d);){if(n.classList.contains("menu-item")){t=n;break}n=n.parentElement}t.querySelector(".sub-menu").classList.toggle("open"),u()||t.classList.toggle("active"),e.stopPropagation()},l=d.querySelectorAll(".menu-item-has-children"),c=0;c<l.length;c++){var f=l[c];f.addEventListener("mouseover",o),f.addEventListener("mouseleave",r);var p=f.querySelector(".js-menu-caret");p&&p.addEventListener("click",a)}for(var m,v,h=function(e){var t=e.target.parentElement.querySelector(".sub-menu");t&&t.classList.add("open");for(var n=e.target.parentElement;!n.isEqualNode(d);)n.classList.contains("sub-menu")&&n.classList.add("open"),n=n.parentElement},g=function(e){var t=e.target.parentElement.querySelector(".sub-menu");t&&t.classList.remove("open");for(var n=e.target.parentElement;!n.isEqualNode(d);)n.classList.contains("sub-menu")&&n.classList.remove("open"),n=n.parentElement},y=d.querySelectorAll("a"),w=0;w<y.length;w++){var b=y[w];b.addEventListener("focus",h),b.addEventListener("blur",g)}if(i.addEventListener("click",function(){var e;i.classList.contains("menu-toggle--active")?(i.classList.remove("menu-toggle--active"),i.setAttribute("aria-expanded","false"),d.classList.remove("active"),i.dispatchEvent(new Event("focus")),e="menu-active--"+d.getAttribute("id"),document.body.classList.remove(e)):(i.classList.add("menu-toggle--active"),i.setAttribute("aria-expanded","true"),d.classList.add("active"),e="menu-active--"+d.getAttribute("id"),document.body.classList.add(e))}),"ontouchstart"in window){var E=function(e,t){for(var n=e.querySelectorAll("."+t),i=0;i<n.length;i++)n[i].classList.remove(t)};v=function(e){d!==e.target&&!function(e){for(var t=!1;null!==(e=e.parentElement);)e.nodeType===Node.ELEMENT_NODE&&e.isEqualNode(d)&&(t=!0);return t}(e.target)&&u()&&(E(d,"open"),E(d,"tapped"),E(d,"active")),document.removeEventListener("ontouchstart",v,!1)},m=function(e){if(!u())return!1;var t,n=this.parentElement;if(n.classList.contains("tapped"))n.classList.remove("tapped"),E(d,"open");else{e.preventDefault();var i=[];for(t=n;!t.isEqualNode(d);)t.classList.contains("tapped")&&i.push(t),t=t.parentElement;for(var o=d.querySelectorAll(".tapped"),r=0;r<o.length;r++)-1===i.indexOf(o[r])&&o[r].classList.remove("tapped");n.classList.add("tapped");var a=[];for(t=n;!t.isEqualNode(d);)t.classList.contains("open")&&a.push(t),t=t.parentElement;for(var s=d.querySelectorAll(".open"),l=0;l<s.length;l++)-1===a.indexOf(s[l])&&s[l].classList.remove("open");for(n.querySelector(".sub-menu")&&n.querySelector(".sub-menu").classList.add("open"),t=this.parentElement;!t.isEqualNode(d);)t.classList.contains("sub-menu")&&t.classList.add("open"),t=t.parentElement;document.addEventListener("touchstart",v,!1)}};for(var L=d.querySelectorAll(".menu-item-has-children > a"),N=0;N<L.length;N++)L[N].addEventListener("touchstart",m,!1)}return this},responsive_tables_in_content=function(){var e=document.querySelectorAll(".wysiwyg .wp-block-table");if(e)for(var t=0;t<e.length;t++){e[t].classList.add("wp-block-table--responsive");var n=document.createElement("div");n.setAttribute("class",e[t].getAttribute("class")),e[t].removeAttribute("class"),e[t].parentNode.insertBefore(n,e[t]),n.appendChild(e[t])}};responsive_tables_in_content();var wrap_old_images_with_caption=function(){var e=document.querySelectorAll(".wysiwyg .wp-caption");if(e.length)for(i=0;i<e.length;i++)if(!e[i].parentNode.classList.contains("wp-block-image")){var t=document.createElement("div");t.setAttribute("class","wp-block-image"),e[i].parentNode.insertBefore(t,e[i]),t.appendChild(e[i])}};wrap_old_images_with_caption();var wrap_old_aligned_images=function(){var e,t=document.querySelectorAll(".wysiwyg img.alignleft, .wysiwyg img.alignright");if(t.length)for(i=0;i<t.length;i++){"P"===(e=t[i].parentNode).nodeName&&(e.parentNode.insertBefore(t[i],e),0===e.childNodes.length&&e.parentNode.removeChild(e));var n=t[i].classList.contains("alignleft")?"alignleft":"alignright";t[i].classList.remove(n);var o=document.createElement("figure");o.setAttribute("class",n),t[i].parentNode.insertBefore(o,t[i]),o.appendChild(t[i]);var r=document.createElement("div");r.setAttribute("class","wp-block-image"),o.parentNode.insertBefore(r,o),r.appendChild(o)}};wrap_old_aligned_images(),aucor_navigation(document.getElementById("primary-navigation"),{desktop_min_width:890,menu_toggle:"#menu-toggle"}),fitvids(),"function"==typeof objectFitPolyfill&&document.addEventListener("lazybeforeunveil",function(e){var t=e.target;objectFitPolyfill(),t.addEventListener("load",function(){objectFitPolyfill()})});
//# sourceMappingURL=main.js.map
