!function(e){var t={};function n(o){if(t[o])return t[o].exports;var i=t[o]={i:o,l:!1,exports:{}};return e[o].call(i.exports,i,i.exports,n),i.l=!0,i.exports}n.m=e,n.c=t,n.d=function(e,t,o){n.o(e,t)||Object.defineProperty(e,t,{enumerable:!0,get:o})},n.r=function(e){"undefined"!=typeof Symbol&&Symbol.toStringTag&&Object.defineProperty(e,Symbol.toStringTag,{value:"Module"}),Object.defineProperty(e,"__esModule",{value:!0})},n.t=function(e,t){if(1&t&&(e=n(e)),8&t)return e;if(4&t&&"object"==typeof e&&e&&e.__esModule)return e;var o=Object.create(null);if(n.r(o),Object.defineProperty(o,"default",{enumerable:!0,value:e}),2&t&&"string"!=typeof e)for(var i in e)n.d(o,i,function(t){return e[t]}.bind(null,i));return o},n.n=function(e){var t=e&&e.__esModule?function(){return e.default}:function(){return e};return n.d(t,"a",t),t},n.o=function(e,t){return Object.prototype.hasOwnProperty.call(e,t)},n.p="",n(n.s=0)}([function(e,t,n){"use strict";n(1)},function(e,t,n){"use strict";
/*!
 * @author Isis (igraziatto) Graziatto <isis.g@vanillaforums.com>
 * @copyright 2009-2018 Vanilla Forums Inc.
 * @license GPL-2.0-only
 */
/*!
 * @author Isis (igraziatto) Graziatto <isis.g@vanillaforums.com>
 * @copyright 2009-2018 Vanilla Forums Inc.
 * @license GPL-2.0-only
 */
n.r(t),$(()=>{(function(){const e="data-height",t="0px",n=document.querySelector("#menu-button"),o=document.querySelector("#navdrawer"),i=document.querySelector(".js-mobileMebox"),r=document.querySelector(".mobileMeBox-button"),u=document.querySelector(".mobileMebox-buttonClose"),l=document.querySelector("#MainHeader");function c(n){n.style.height===t?function(t){t.style.height=t.getAttribute(e)+"px"}(n):s(n)}function s(e){e.style.height=t}function a(t){t.style.visibility="hidden",t.style.height="auto";const n=t.getBoundingClientRect().height;t.setAttribute(e,n.toString()),s(t),t.style.visibility="initial"}a(i),a(o),n.addEventListener("click",()=>{n.classList.toggle("isToggled"),l.classList.toggle("hasOpenNavigation"),s(i),c(o)}),r.addEventListener("click",()=>{r.classList.toggle("isToggled"),l.classList.remove("hasOpenNavigation"),n.classList.remove("isToggled"),s(o),c(i)}),u.addEventListener("click",()=>{s(i)})})(),$("select").wrap('<div class="SelectWrapper"></div>')})}]);
//# sourceMappingURL=custom.js.map