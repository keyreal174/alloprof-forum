/******/ (function(modules) { // webpackBootstrap
/******/ 	// The module cache
/******/ 	var installedModules = {};
/******/
/******/ 	// The require function
/******/ 	function __webpack_require__(moduleId) {
/******/
/******/ 		// Check if module is in cache
/******/ 		if(installedModules[moduleId]) {
/******/ 			return installedModules[moduleId].exports;
/******/ 		}
/******/ 		// Create a new module (and put it into the cache)
/******/ 		var module = installedModules[moduleId] = {
/******/ 			i: moduleId,
/******/ 			l: false,
/******/ 			exports: {}
/******/ 		};
/******/
/******/ 		// Execute the module function
/******/ 		modules[moduleId].call(module.exports, module, module.exports, __webpack_require__);
/******/
/******/ 		// Flag the module as loaded
/******/ 		module.l = true;
/******/
/******/ 		// Return the exports of the module
/******/ 		return module.exports;
/******/ 	}
/******/
/******/
/******/ 	// expose the modules object (__webpack_modules__)
/******/ 	__webpack_require__.m = modules;
/******/
/******/ 	// expose the module cache
/******/ 	__webpack_require__.c = installedModules;
/******/
/******/ 	// define getter function for harmony exports
/******/ 	__webpack_require__.d = function(exports, name, getter) {
/******/ 		if(!__webpack_require__.o(exports, name)) {
/******/ 			Object.defineProperty(exports, name, { enumerable: true, get: getter });
/******/ 		}
/******/ 	};
/******/
/******/ 	// define __esModule on exports
/******/ 	__webpack_require__.r = function(exports) {
/******/ 		if(typeof Symbol !== 'undefined' && Symbol.toStringTag) {
/******/ 			Object.defineProperty(exports, Symbol.toStringTag, { value: 'Module' });
/******/ 		}
/******/ 		Object.defineProperty(exports, '__esModule', { value: true });
/******/ 	};
/******/
/******/ 	// create a fake namespace object
/******/ 	// mode & 1: value is a module id, require it
/******/ 	// mode & 2: merge all properties of value into the ns
/******/ 	// mode & 4: return value when already ns object
/******/ 	// mode & 8|1: behave like require
/******/ 	__webpack_require__.t = function(value, mode) {
/******/ 		if(mode & 1) value = __webpack_require__(value);
/******/ 		if(mode & 8) return value;
/******/ 		if((mode & 4) && typeof value === 'object' && value && value.__esModule) return value;
/******/ 		var ns = Object.create(null);
/******/ 		__webpack_require__.r(ns);
/******/ 		Object.defineProperty(ns, 'default', { enumerable: true, value: value });
/******/ 		if(mode & 2 && typeof value != 'string') for(var key in value) __webpack_require__.d(ns, key, function(key) { return value[key]; }.bind(null, key));
/******/ 		return ns;
/******/ 	};
/******/
/******/ 	// getDefaultExport function for compatibility with non-harmony modules
/******/ 	__webpack_require__.n = function(module) {
/******/ 		var getter = module && module.__esModule ?
/******/ 			function getDefault() { return module['default']; } :
/******/ 			function getModuleExports() { return module; };
/******/ 		__webpack_require__.d(getter, 'a', getter);
/******/ 		return getter;
/******/ 	};
/******/
/******/ 	// Object.prototype.hasOwnProperty.call
/******/ 	__webpack_require__.o = function(object, property) { return Object.prototype.hasOwnProperty.call(object, property); };
/******/
/******/ 	// __webpack_public_path__
/******/ 	__webpack_require__.p = "";
/******/
/******/
/******/ 	// Load entry module and return exports
/******/ 	return __webpack_require__(__webpack_require__.s = "./src/js/index.js");
/******/ })
/************************************************************************/
/******/ ({

/***/ "./src/js/header.js":
/*!**************************!*\
  !*** ./src/js/header.js ***!
  \**************************/
/*! no static exports found */
/***/ (function(module, exports, __webpack_require__) {

"use strict";
eval("\n\nObject.defineProperty(exports, \"__esModule\", {\n    value: true\n});\nexports.setupHeader = setupHeader;\n\nvar _utility = __webpack_require__(/*! ./utility.js */ \"./src/js/utility.js\");\n\n/**\n * Call this event on the window in order to collapse default collapsing elements.\n *\n * fireEvent(window, EVENT_COLLAPSE_DEFAULTS);\n */\nvar EVENT_COLLAPSE_DEFAULTS = \"vanilla_collapse_defaults\";\n\n// Strings to represent the current state in a data-attribute\n/*!\n * @author Isis (igraziatto) Graziatto <isis.g@vanillaforums.com>\n * @copyright 2009-2018 Vanilla Forums Inc.\n * @license GPL-2.0-only\n */\n\nvar STATE_CLOSED = \"CLOSED\";\nvar STATE_OPEN = \"OPEN\";\nvar RESIZE_THROTTLE_DURATION = 200;\n\nfunction setupHeader() {\n    //initHeader();\n\n    // Watch for window resizing and throttle the event listener\n    var resizeTimer;\n    $(window).resize(function () {\n        clearTimeout(resizeTimer);\n        resizeTimer = setTimeout(initHeader, 250);\n\n        // Check if masonry is on the page and reload it\n        var $tiles = $('body.Section-BestOf .masonry');\n        if ($tiles.length > 0) {\n            $tiles.masonry('reload');\n        }\n    });\n}\n\nfunction initHeader() {\n    resetNavigation();\n    initNavigationDropdown();\n    initCategoriesModule();\n    (0, _utility.fireEvent)(window, EVENT_COLLAPSE_DEFAULTS);\n    initNavigationVisibility();\n}\n\nfunction initNavigationListeners() {\n    var $navigation = $('#navdrawer');\n    var className = 'isStuck';\n\n    var setupListener = function setupListener() {\n        var offset = $navigation.offset().top;\n\n        $(window).on('scroll', function () {\n            window.requestAnimationFrame(function () {\n                if (!$navigation.hasClass(className) && $(window).scrollTop() >= offset) {\n                    $navigation.addClass(className);\n                } else if ($navigation.hasClass(className)) {\n                    $navigation.removeClass(className);\n                }\n            });\n        });\n    };\n}\n\n/**\n * Initialize the mobile menu open/close listeners\n */\nfunction initNavigationDropdown() {\n    var $menuButton = $(\"#menu-button\");\n    var $menuList = $(\"#navdrawer\");\n    setupBetterHeightTransitions($menuList, $menuButton, true);\n}\n\n/**\n * Initialize the listeners for the accordian style categories module\n */\nfunction initCategoriesModule() {\n    var $children = $(\".CategoriesModule-children\");\n    var $chevrons = $(\".CategoriesModule-chevron\");\n\n    $chevrons.each(function (index, chevron) {\n        var $chevron = $(chevron);\n        var $childList = $chevron.parent().parent().find(\".CategoriesModule-children\").first();\n        setupBetterHeightTransitions($childList, $chevron, true);\n    });\n}\n\n/**\n * Hide the navigation menu so that it's not in the way as we calculate the sizes\n */\nfunction resetNavigation() {\n    var $nav = $(\"#navdrawer\");\n    resetBetterHeightTransition($nav);\n\n    var $toggles = $(\"#menu-button.isToggled, #navdrawer .isToggled\");\n    $toggles.removeClass('isToggled');\n\n    var $children = $(\".CategoriesModule-children\");\n    $children.each(function (index, child) {\n        resetBetterHeightTransition($(child));\n    });\n}\n\n/**\n * Show the navigation menu\n */\nfunction initNavigationVisibility() {\n    var $nav = $(\"#navdrawer\");\n    $nav.css({ position: \"relative\", visibility: \"visible\" });\n    $nav.addClass('isReadyToTransition');\n}\n\n/**\n * Measure approximate real heights of an element and store/use it\n * to have a more accurate max-height transition.\n *\n * @param {any} $elementToMeasure\n * @param {any} toState\n */\nfunction applyNewElementMeasurements($elementToMeasure, toState) {\n    var trueHeight = $elementToMeasure.outerHeight() + \"px\";\n    var previouslyCalculatedOldHeight = $elementToMeasure.attr(\"data-true-height\");\n\n    if (!previouslyCalculatedOldHeight) {\n        $elementToMeasure.attr(\"data-true-height\", trueHeight);\n    }\n\n    $elementToMeasure.attr(\"data-valid-open-state\", false);\n\n    if (toState === STATE_CLOSED) {\n        $elementToMeasure.attr(\"data-valid-open-state\", false);\n        $elementToMeasure.css(\"overflow\", \"hidden\");\n        $elementToMeasure.css(\"max-height\", \"0px\");\n    } else if (toState === STATE_OPEN) {\n        $elementToMeasure.attr(\"data-valid-open-state\", true);\n        $elementToMeasure.css(\"max-height\", $elementToMeasure.attr(\"data-true-height\"));\n        $elementToMeasure.on(\"transitionend\", function handler() {\n            if ($elementToMeasure.attr(\"data-valid-open-state\") === \"true\") {\n                $elementToMeasure.css(\"overflow\", \"visible\");\n                $elementToMeasure.off(\"transitionend\", handler);\n            }\n        });\n    }\n\n    $elementToMeasure.attr(\"data-state\", toState);\n}\n\nfunction resetBetterHeightTransition($element) {\n    $element.removeClass('isReadyToTransition');\n    $element.removeAttr('style');\n    $element.removeAttr(\"data-true-height\");\n    $element.removeAttr(\"data-valid-open-state\");\n    $element.removeAttr(\"data-state\");\n}\n\n/**\n * Setup a more accurate max-height transition on an element to be triggered by another element.\n *\n * @param {jquery.element} $elementToMeasure The jquery element to measure\n * @param {jquery.element} $triggeringElement The jquery element that triggers the transition\n * @param {boolean} collapseByDefault whether or not to collapse the element by default. This will happen after everything has been measured and you fire the EVENT_COLLAPSE_DEFAULTS from the window\n */\nfunction setupBetterHeightTransitions($elementToMeasure, $triggeringElement, collapseByDefault) {\n    applyNewElementMeasurements($elementToMeasure, STATE_OPEN);\n\n    // Clear existing click listeners and then set them\n    $triggeringElement.off();\n    $triggeringElement.on(\"click\", function () {\n        var elementState = $elementToMeasure.attr(\"data-state\");\n\n        if (elementState === STATE_CLOSED) {\n            $triggeringElement.toggleClass(\"isToggled\");\n            applyNewElementMeasurements($elementToMeasure, STATE_OPEN);\n        } else if (elementState === STATE_OPEN) {\n            $triggeringElement.toggleClass(\"isToggled\");\n            applyNewElementMeasurements($elementToMeasure, STATE_CLOSED);\n        }\n    });\n\n    if (collapseByDefault) {\n        window.addEventListener(EVENT_COLLAPSE_DEFAULTS, function () {\n            applyNewElementMeasurements($elementToMeasure, STATE_CLOSED);\n        });\n    }\n}//# sourceURL=[module]\n//# sourceMappingURL=data:application/json;charset=utf-8;base64,eyJ2ZXJzaW9uIjozLCJzb3VyY2VzIjpbIndlYnBhY2s6Ly8vLi9zcmMvanMvaGVhZGVyLmpzP2NhNzUiXSwibmFtZXMiOlsic2V0dXBIZWFkZXIiLCJFVkVOVF9DT0xMQVBTRV9ERUZBVUxUUyIsIlNUQVRFX0NMT1NFRCIsIlNUQVRFX09QRU4iLCJSRVNJWkVfVEhST1RUTEVfRFVSQVRJT04iLCJyZXNpemVUaW1lciIsIiQiLCJ3aW5kb3ciLCJyZXNpemUiLCJjbGVhclRpbWVvdXQiLCJzZXRUaW1lb3V0IiwiaW5pdEhlYWRlciIsIiR0aWxlcyIsImxlbmd0aCIsIm1hc29ucnkiLCJyZXNldE5hdmlnYXRpb24iLCJpbml0TmF2aWdhdGlvbkRyb3Bkb3duIiwiaW5pdENhdGVnb3JpZXNNb2R1bGUiLCJpbml0TmF2aWdhdGlvblZpc2liaWxpdHkiLCJpbml0TmF2aWdhdGlvbkxpc3RlbmVycyIsIiRuYXZpZ2F0aW9uIiwiY2xhc3NOYW1lIiwic2V0dXBMaXN0ZW5lciIsIm9mZnNldCIsInRvcCIsIm9uIiwicmVxdWVzdEFuaW1hdGlvbkZyYW1lIiwiaGFzQ2xhc3MiLCJzY3JvbGxUb3AiLCJhZGRDbGFzcyIsInJlbW92ZUNsYXNzIiwiJG1lbnVCdXR0b24iLCIkbWVudUxpc3QiLCJzZXR1cEJldHRlckhlaWdodFRyYW5zaXRpb25zIiwiJGNoaWxkcmVuIiwiJGNoZXZyb25zIiwiZWFjaCIsImluZGV4IiwiY2hldnJvbiIsIiRjaGV2cm9uIiwiJGNoaWxkTGlzdCIsInBhcmVudCIsImZpbmQiLCJmaXJzdCIsIiRuYXYiLCJyZXNldEJldHRlckhlaWdodFRyYW5zaXRpb24iLCIkdG9nZ2xlcyIsImNoaWxkIiwiY3NzIiwicG9zaXRpb24iLCJ2aXNpYmlsaXR5IiwiYXBwbHlOZXdFbGVtZW50TWVhc3VyZW1lbnRzIiwiJGVsZW1lbnRUb01lYXN1cmUiLCJ0b1N0YXRlIiwidHJ1ZUhlaWdodCIsIm91dGVySGVpZ2h0IiwicHJldmlvdXNseUNhbGN1bGF0ZWRPbGRIZWlnaHQiLCJhdHRyIiwiaGFuZGxlciIsIm9mZiIsIiRlbGVtZW50IiwicmVtb3ZlQXR0ciIsIiR0cmlnZ2VyaW5nRWxlbWVudCIsImNvbGxhcHNlQnlEZWZhdWx0IiwiZWxlbWVudFN0YXRlIiwidG9nZ2xlQ2xhc3MiLCJhZGRFdmVudExpc3RlbmVyIl0sIm1hcHBpbmdzIjoiOzs7OztRQW9CZ0JBLFcsR0FBQUEsVzs7QUFkaEI7O0FBRUE7Ozs7O0FBS0EsSUFBTUMsMEJBQTBCLDJCQUFoQzs7QUFFQTtBQWZBOzs7Ozs7QUFnQkEsSUFBTUMsZUFBZSxRQUFyQjtBQUNBLElBQU1DLGFBQWEsTUFBbkI7QUFDQSxJQUFNQywyQkFBMkIsR0FBakM7O0FBRU8sU0FBU0osV0FBVCxHQUF1QjtBQUMxQjs7QUFFQTtBQUNBLFFBQUlLLFdBQUo7QUFDQUMsTUFBRUMsTUFBRixFQUFVQyxNQUFWLENBQWlCLFlBQU07QUFDbkJDLHFCQUFhSixXQUFiO0FBQ0FBLHNCQUFjSyxXQUFXQyxVQUFYLEVBQXVCLEdBQXZCLENBQWQ7O0FBRUE7QUFDQSxZQUFNQyxTQUFTTixFQUFFLDhCQUFGLENBQWY7QUFDQSxZQUFJTSxPQUFPQyxNQUFQLEdBQWdCLENBQXBCLEVBQXVCO0FBQ25CRCxtQkFBT0UsT0FBUCxDQUFlLFFBQWY7QUFDSDtBQUNKLEtBVEQ7QUFVSDs7QUFFRCxTQUFTSCxVQUFULEdBQXNCO0FBQ2xCSTtBQUNBQztBQUNBQztBQUNBLDRCQUFVVixNQUFWLEVBQWtCTix1QkFBbEI7QUFDQWlCO0FBQ0g7O0FBRUQsU0FBU0MsdUJBQVQsR0FBbUM7QUFDL0IsUUFBTUMsY0FBY2QsRUFBRSxZQUFGLENBQXBCO0FBQ0EsUUFBTWUsWUFBWSxTQUFsQjs7QUFFQSxRQUFNQyxnQkFBZ0IsU0FBU0EsYUFBVCxHQUF5QjtBQUMzQyxZQUFNQyxTQUFTSCxZQUFZRyxNQUFaLEdBQXFCQyxHQUFwQzs7QUFFQWxCLFVBQUVDLE1BQUYsRUFBVWtCLEVBQVYsQ0FBYSxRQUFiLEVBQXVCLFlBQU07QUFDekJsQixtQkFBT21CLHFCQUFQLENBQTZCLFlBQU07QUFDL0Isb0JBQUks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QUNIO0FBQ0osS0FWRDs7QUFZQSxRQUFJNkQsaUJBQUosRUFBdUI7QUFDbkJ4RCxlQUFPMkQsZ0JBQVAsQ0FBd0JqRSx1QkFBeEIsRUFBaUQsWUFBTTtBQUNuRGtELHdDQUE0QkMsaUJBQTVCLEVBQStDbEQsWUFBL0M7QUFDSCxTQUZEO0FBR0g7QUFDSiIsImZpbGUiOiIuL3NyYy9qcy9oZWFkZXIuanMuanMiLCJzb3VyY2VzQ29udGVudCI6WyIvKiFcbiAqIEBhdXRob3IgSXNpcyAoaWdyYXppYXR0bykgR3JhemlhdHRvIDxpc2lzLmdAdmFuaWxsYWZvcnVtcy5jb20+XG4gKiBAY29weXJpZ2h0IDIwMDktMjAxOCBWYW5pbGxhIEZvcnVtcyBJbmMuXG4gKiBAbGljZW5zZSBHUEwtMi4wLW9ubHlcbiAqL1xuXG5pbXBvcnQgeyBmaXJlRXZlbnQgfSBmcm9tIFwiLi91dGlsaXR5LmpzXCI7XG5cbi8qKlxuICogQ2FsbCB0aGlzIGV2ZW50IG9uIHRoZSB3aW5kb3cgaW4gb3JkZXIgdG8gY29sbGFwc2UgZGVmYXVsdCBjb2xsYXBzaW5nIGVsZW1lbnRzLlxuICpcbiAqIGZpcmVFdmVudCh3aW5kb3csIEVWRU5UX0NPTExBUFNFX0RFRkFVTFRTKTtcbiAqL1xuY29uc3QgRVZFTlRfQ09MTEFQU0VfREVGQVVMVFMgPSBcInZhbmlsbGFfY29sbGFwc2VfZGVmYXVsdHNcIjtcblxuLy8gU3RyaW5ncyB0byByZXByZXNlbnQgdGhlIGN1cnJlbnQgc3RhdGUgaW4gYSBkYXRhLWF0dHJpYnV0ZVxuY29uc3QgU1RBVEVfQ0xPU0VEID0gXCJDTE9TRURcIjtcbmNvbnN0IFNUQVRFX09QRU4gPSBcIk9QRU5cIjtcbmNvbnN0IFJFU0laRV9USFJPVFRMRV9EVVJBVElPTiA9IDIwMDtcblxuZXhwb3J0IGZ1bmN0aW9uIHNldHVwSGVhZGVyKCkge1xuICAgIC8vaW5pdEhlYWRlcigpO1xuXG4gICAgLy8gV2F0Y2ggZm9yIHdpbmRvdyByZXNpemluZyBhbmQgdGhyb3R0bGUgdGhlIGV2ZW50IGxpc3RlbmVyXG4gICAgdmFyIHJlc2l6ZVRpbWVyO1xuICAgICQod2luZG93KS5yZXNpemUoKCkgPT4ge1xuICAgICAgICBjbGVhclRpbWVvdXQocmVzaXplVGltZXIpO1xuICAgICAgICByZXNpemVUaW1lciA9IHNldFRpbWVvdXQoaW5pdEhlYWRlciwgMjUwKTtcblxuICAgICAgICAvLyBDaGVjayBpZiBtYXNvbnJ5IGlzIG9uIHRoZSBwYWdlIGFuZCByZWxvYWQgaXRcbiAgICAgICAgY29uc3QgJHRpbGVzID0gJCgnYm9keS5TZWN0aW9uLUJlc3RPZiAubWFzb25yeScpO1xuICAgICAgICBpZiAoJHRpbGVzLmxlbmd0aCA+IDApIHtcbiAgICAgICAgICAgICR0aWxlcy5tYXNvbnJ5KCdyZWxvYWQnKTtcbiAgICAgICAgfVxuICAgIH0pO1xufVxuXG5mdW5jdGlvbiBpbml0SGVhZGVyKCkge1xuICAgIHJlc2V0TmF2aWdhdGlvbigpO1xuICAgIGluaXROYXZpZ2F0aW9uRHJvcGRvd24oKTtcbiAgICBpbml0Q2F0ZWdvcmllc01vZHVsZSgpO1xuICAgIGZpcmVFdmVudCh3aW5kb3csIEVWRU5UX0NPTExBUFNFX0RFRkFVTFRTKTtcbiAgICBpbml0TmF2aWdhdGlvblZpc2liaWxpdHkoKTtcbn1cblxuZnVuY3Rpb24gaW5pdE5hdmlnYXRpb25MaXN0ZW5lcnMoKSB7XG4gICAgY29uc3QgJG5hdmlnYXRpb24gPSAkKCcjbmF2ZHJhd2VyJyk7XG4gICAgY29uc3QgY2xhc3NOYW1lID0gJ2lzU3R1Y2snO1xuXG4gICAgY29uc3Qgc2V0dXBMaXN0ZW5lciA9IGZ1bmN0aW9uIHNldHVwTGlzdGVuZXIoKSB7XG4gICAgICAgIGNvbnN0IG9mZnNldCA9ICRuYXZpZ2F0aW9uLm9mZnNldCgpLnRvcDtcblxuICAgICAgICAkKHdpbmRvdykub24oJ3Njcm9sbCcsICgpID0+IHtcbiAgICAgICAgICAgIHdpbmRvdy5yZXF1ZXN0QW5pbWF0aW9uRnJhbWUoKCkgPT4ge1xuICAgICAgICAgICAgICAgIGlmICghJG5hdmlnYXRpb24uaGFzQ2xhc3MoY2xhc3NOYW1lKSAmJiAkKHdpbmRvdykuc2Nyb2xsVG9wKCkgPj0gb2Zmc2V0KSB7XG4gICAgICAgICAgICAgICAgICAgICRuYXZpZ2F0aW9uLmFkZENsYXNzKGNsYXNzTmFtZSk7XG4gICAgICAgICAgICAgICAgfSBlbHNlIGlmICgkbmF2aWdhdGlvbi5oYXNDbGFzcyhjbGFzc05hbWUpKSB7XG4gICAgICAgICAgICAgICAgICAgICRuYXZpZ2F0aW9uLnJlbW92ZUNsYXNzKGNsYXNzTmFtZSk7XG4gICAgICAgICAgICAgICAgfVxuICAgICAgICAgICAgfSlcbiAgICAgICAgfSlcbiAgICB9XG59XG5cbi8qKlxuICogSW5pdGlhbGl6ZSB0aGUgbW9iaWxlIG1lbnUgb3Blbi9jbG9zZSBsaXN0ZW5lcnNcbiAqL1xuZnVuY3Rpb24gaW5pdE5hdmlnYXRpb25Ecm9wZG93bigpIHtcbiAgICBjb25zdCAkbWVudUJ1dHRvbiA9ICQoXCIjbWVudS1idXR0b25cIik7XG4gICAgY29uc3QgJG1lbnVMaXN0ID0gJChcIiNuYXZkcmF3ZXJcIik7XG4gICAgc2V0dXBCZXR0ZXJIZWlnaHRUcmFuc2l0aW9ucygkbWVudUxpc3QsICRtZW51QnV0dG9uLCB0cnVlKTtcbn1cblxuLyoqXG4gKiBJbml0aWFsaXplIHRoZSBsaXN0ZW5lcnMgZm9yIHRoZSBhY2NvcmRpYW4gc3R5bGUgY2F0ZWdvcmllcyBtb2R1bGVcbiAqL1xuZnVuY3Rpb24gaW5pdENhdGVnb3JpZXNNb2R1bGUoKSB7XG4gICAgY29uc3QgJGNoaWxkcmVuID0gJChcIi5DYXRlZ29yaWVzTW9kdWxlLWNoaWxkcmVuXCIpO1xuICAgIGNvbnN0ICRjaGV2cm9ucyA9ICQoXCIuQ2F0ZWdvcmllc01vZHVsZS1jaGV2cm9uXCIpO1xuXG4gICAgJGNoZXZyb25zLmVhY2goKGluZGV4LCBjaGV2cm9uKSA9PiB7XG4gICAgICAgIGNvbnN0ICRjaGV2cm9uID0gJChjaGV2cm9uKTtcbiAgICAgICAgY29uc3QgJGNoaWxkTGlzdCA9ICRjaGV2cm9uXG4gICAgICAgICAgICAucGFyZW50KClcbiAgICAgICAgICAgIC5wYXJlbnQoKVxuICAgICAgICAgICAgLmZpbmQoXCIuQ2F0ZWdvcmllc01vZHVsZS1jaGlsZHJlblwiKVxuICAgICAgICAgICAgLmZpcnN0KCk7XG4gICAgICAgIHNldHVwQmV0dGVySGVpZ2h0VHJhbnNpdGlvbnMoJGNoaWxkTGlzdCwgJGNoZXZyb24sIHRydWUpO1xuICAgIH0pO1xufVxuXG4vKipcbiAqIEhpZGUgdGhlIG5hdmlnYXRpb24gbWVudSBzbyB0aGF0IGl0J3Mgbm90IGluIHRoZSB3YXkgYXMgd2UgY2FsY3VsYXRlIHRoZSBzaXplc1xuICovXG5mdW5jdGlvbiByZXNldE5hdmlnYXRpb24oKSB7XG4gICAgY29uc3QgJG5hdiA9ICQoXCIjbmF2ZHJhd2VyXCIpO1xuICAgIHJlc2V0QmV0dGVySGVpZ2h0VHJhbnNpdGlvbigkbmF2KTtcblxuICAgIGNvbnN0ICR0b2dnbGVzID0gJChcIiNtZW51LWJ1dHRvbi5pc1RvZ2dsZWQsICNuYXZkcmF3ZXIgLmlzVG9nZ2xlZFwiKTtcbiAgICAkdG9nZ2xlcy5yZW1vdmVDbGFzcygnaXNUb2dnbGVkJyk7XG5cbiAgICBjb25zdCAkY2hpbGRyZW4gPSAkKFwiLkNhdGVnb3JpZXNNb2R1bGUtY2hpbGRyZW5cIik7XG4gICAgJGNoaWxkcmVuLmVhY2goKGluZGV4LCBjaGlsZCkgPT4ge1xuICAgICAgICByZXNldEJldHRlckhlaWdodFRyYW5zaXRpb24oJChjaGlsZCkpO1xuICAgIH0pXG59XG5cbi8qKlxuICogU2hvdyB0aGUgbmF2aWdhdGlvbiBtZW51XG4gKi9cbmZ1bmN0aW9uIGluaXROYXZpZ2F0aW9uVmlzaWJpbGl0eSgpIHtcbiAgICBjb25zdCAkbmF2ID0gJChcIiNuYXZkcmF3ZXJcIik7XG4gICAgJG5hdi5jc3MoeyBwb3NpdGlvbjogXCJyZWxhdGl2ZVwiLCB2aXNpYmlsaXR5OiBcInZpc2libGVcIiB9KTtcbiAgICAkbmF2LmFkZENsYXNzKCdpc1JlYWR5VG9UcmFuc2l0aW9uJyk7XG59XG5cbi8qKlxuICogTWVhc3VyZSBhcHByb3hpbWF0ZSByZWFsIGhlaWdodHMgb2YgYW4gZWxlbWVudCBhbmQgc3RvcmUvdXNlIGl0XG4gKiB0byBoYXZlIGEgbW9yZSBhY2N1cmF0ZSBtYXgtaGVpZ2h0IHRyYW5zaXRpb24uXG4gKlxuICogQHBhcmFtIHthbnl9ICRlbGVtZW50VG9NZWFzdXJlXG4gKiBAcGFyYW0ge2FueX0gdG9TdGF0ZVxuICovXG5mdW5jdGlvbiBhcHBseU5ld0VsZW1lbnRNZWFzdXJlbWVudHMoJGVsZW1lbnRUb01lYXN1cmUsIHRvU3RhdGUpIHtcbiAgICBjb25zdCB0cnVlSGVpZ2h0ID0gJGVsZW1lbnRUb01lYXN1cmUub3V0ZXJIZWlnaHQoKSArIFwicHhcIjtcbiAgICBjb25zdCBwcmV2aW91c2x5Q2FsY3VsYXRlZE9sZEhlaWdodCA9ICRlbGVtZW50VG9NZWFzdXJlLmF0dHIoXG4gICAgICAgIFwiZGF0YS10cnVlLWhlaWdodFwiXG4gICAgKTtcblxuICAgIGlmICghcHJldmlvdXNseUNhbGN1bGF0ZWRPbGRIZWlnaHQpIHtcbiAgICAgICAgJGVsZW1lbnRUb01lYXN1cmUuYXR0cihcImRhdGEtdHJ1ZS1oZWlnaHRcIiwgdHJ1ZUhlaWdodCk7XG4gICAgfVxuXG4gICAgJGVsZW1lbnRUb01lYXN1cmUuYXR0cihcImRhdGEtdmFsaWQtb3Blbi1zdGF0ZVwiLCBmYWxzZSk7XG5cbiAgICBpZiAodG9TdGF0ZSA9PT0gU1RBVEVfQ0xPU0VEKSB7XG4gICAgICAgICRlbGVtZW50VG9NZWFzdXJlLmF0dHIoXCJkYXRhLXZhbGlkLW9wZW4tc3RhdGVcIiwgZmFsc2UpO1xuICAgICAgICAkZWxlbWVudFRvTWVhc3VyZS5jc3MoXCJvdmVyZmxvd1wiLCBcImhpZGRlblwiKTtcbiAgICAgICAgJGVsZW1lbnRUb01lYXN1cmUuY3NzKFwibWF4LWhlaWdodFwiLCBcIjBweFwiKTtcbiAgICB9IGVsc2UgaWYgKHRvU3RhdGUgPT09IFNUQVRFX09QRU4pIHtcbiAgICAgICAgJGVsZW1lbnRUb01lYXN1cmUuYXR0cihcImRhdGEtdmFsaWQtb3Blbi1zdGF0ZVwiLCB0cnVlKTtcbiAgICAgICAgJGVsZW1lbnRUb01lYXN1cmUuY3NzKFxuICAgICAgICAgICAgXCJtYXgtaGVpZ2h0XCIsXG4gICAgICAgICAgICAkZWxlbWVudFRvTWVhc3VyZS5hdHRyKFwiZGF0YS10cnVlLWhlaWdodFwiKVxuICAgICAgICApO1xuICAgICAgICAkZWxlbWVudFRvTWVhc3VyZS5vbihcInRyYW5zaXRpb25lbmRcIiwgZnVuY3Rpb24gaGFuZGxlcigpIHtcbiAgICAgICAgICAgIGlmICgkZWxlbWVudFRvTWVhc3VyZS5hdHRyKFwiZGF0YS12YWxpZC1vcGVuLXN0YXRlXCIpID09PSBcInRydWVcIikge1xuICAgICAgICAgICAgICAgICRlbGVtZW50VG9NZWFzdXJlLmNzcyhcIm92ZXJmbG93XCIsIFwidmlzaWJsZVwiKTtcbiAgICAgICAgICAgICAgICAkZWxlbWVudFRvTWVhc3VyZS5vZmYoXCJ0cmFuc2l0aW9uZW5kXCIsIGhhbmRsZXIpO1xuICAgICAgICAgICAgfVxuICAgICAgICB9KTtcbiAgICB9XG5cbiAgICAkZWxlbWVudFRvTWVhc3VyZS5hdHRyKFwiZGF0YS1zdGF0ZVwiLCB0b1N0YXRlKTtcbn1cblxuZnVuY3Rpb24gcmVzZXRCZXR0ZXJIZWlnaHRUcmFuc2l0aW9uKCRlbGVtZW50KSB7XG4gICAgJGVsZW1lbnQucmVtb3ZlQ2xhc3MoJ2lzUmVhZHlUb1RyYW5zaXRpb24nKTtcbiAgICAkZWxlbWVudC5yZW1vdmVBdHRyKCdzdHlsZScpO1xuICAgICRlbGVtZW50LnJlbW92ZUF0dHIoXCJkYXRhLXRydWUtaGVpZ2h0XCIpO1xuICAgICRlbGVtZW50LnJlbW92ZUF0dHIoXCJkYXRhLXZhbGlkLW9wZW4tc3RhdGVcIik7XG4gICAgJGVsZW1lbnQucmVtb3ZlQXR0cihcImRhdGEtc3RhdGVcIik7XG59XG5cbi8qKlxuICogU2V0dXAgYSBtb3JlIGFjY3VyYXRlIG1heC1oZWlnaHQgdHJhbnNpdGlvbiBvbiBhbiBlbGVtZW50IHRvIGJlIHRyaWdnZXJlZCBieSBhbm90aGVyIGVsZW1lbnQuXG4gKlxuICogQHBhcmFtIHtqcXVlcnkuZWxlbWVudH0gJGVsZW1lbnRUb01lYXN1cmUgVGhlIGpxdWVyeSBlbGVtZW50IHRvIG1lYXN1cmVcbiAqIEBwYXJhbSB7anF1ZXJ5LmVsZW1lbnR9ICR0cmlnZ2VyaW5nRWxlbWVudCBUaGUganF1ZXJ5IGVsZW1lbnQgdGhhdCB0cmlnZ2VycyB0aGUgdHJhbnNpdGlvblxuICogQHBhcmFtIHtib29sZWFufSBjb2xsYXBzZUJ5RGVmYXVsdCB3aGV0aGVyIG9yIG5vdCB0byBjb2xsYXBzZSB0aGUgZWxlbWVudCBieSBkZWZhdWx0LiBUaGlzIHdpbGwgaGFwcGVuIGFmdGVyIGV2ZXJ5dGhpbmcgaGFzIGJlZW4gbWVhc3VyZWQgYW5kIHlvdSBmaXJlIHRoZSBFVkVOVF9DT0xMQVBTRV9ERUZBVUxUUyBmcm9tIHRoZSB3aW5kb3dcbiAqL1xuZnVuY3Rpb24gc2V0dXBCZXR0ZXJIZWlnaHRUcmFuc2l0aW9ucyhcbiAgICAkZWxlbWVudFRvTWVhc3VyZSxcbiAgICAkdHJpZ2dlcmluZ0VsZW1lbnQsXG4gICAgY29sbGFwc2VCeURlZmF1bHRcbikge1xuICAgIGFwcGx5TmV3RWxlbWVudE1lYXN1cmVtZW50cygkZWxlbWVudFRvTWVhc3VyZSwgU1RBVEVfT1BFTik7XG5cbiAgICAvLyBDbGVhciBleGlzdGluZyBjbGljayBsaXN0ZW5lcnMgYW5kIHRoZW4gc2V0IHRoZW1cbiAgICAkdHJpZ2dlcmluZ0VsZW1lbnQub2ZmKCk7XG4gICAgJHRyaWdnZXJpbmdFbGVtZW50Lm9uKFwiY2xpY2tcIiwgKCkgPT4ge1xuICAgICAgICBjb25zdCBlbGVtZW50U3RhdGUgPSAkZWxlbWVudFRvTWVhc3VyZS5hdHRyKFwiZGF0YS1zdGF0ZVwiKTtcblxuICAgICAgICBpZiAoZWxlbWVudFN0YXRlID09PSBTVEFURV9DTE9TRUQpIHtcbiAgICAgICAgICAgICR0cmlnZ2VyaW5nRWxlbWVudC50b2dnbGVDbGFzcyhcImlzVG9nZ2xlZFwiKTtcbiAgICAgICAgICAgIGFwcGx5TmV3RWxlbWVudE1lYXN1cmVtZW50cygkZWxlbWVudFRvTWVhc3VyZSwgU1RBVEVfT1BFTik7XG4gICAgICAgIH0gZWxzZSBpZiAoZWxlbWVudFN0YXRlID09PSBTVEFURV9PUEVOKSB7XG4gICAgICAgICAgICAkdHJpZ2dlcmluZ0VsZW1lbnQudG9nZ2xlQ2xhc3MoXCJpc1RvZ2dsZWRcIik7XG4gICAgICAgICAgICBhcHBseU5ld0VsZW1lbnRNZWFzdXJlbWVudHMoJGVsZW1lbnRUb01lYXN1cmUsIFNUQVRFX0NMT1NFRCk7XG4gICAgICAgIH1cbiAgICB9KTtcblxuICAgIGlmIChjb2xsYXBzZUJ5RGVmYXVsdCkge1xuICAgICAgICB3aW5kb3cuYWRkRXZlbnRMaXN0ZW5lcihFVkVOVF9DT0xMQVBTRV9ERUZBVUxUUywgKCkgPT4ge1xuICAgICAgICAgICAgYXBwbHlOZXdFbGVtZW50TWVhc3VyZW1lbnRzKCRlbGVtZW50VG9NZWFzdXJlLCBTVEFURV9DTE9TRUQpO1xuICAgICAgICB9KTtcbiAgICB9XG59XG4iXSwic291cmNlUm9vdCI6IiJ9\n//# sourceURL=webpack-internal:///./src/js/header.js\n");

/***/ }),

/***/ "./src/js/index.js":
/*!*************************!*\
  !*** ./src/js/index.js ***!
  \*************************/
/*! no static exports found */
/***/ (function(module, exports, __webpack_require__) {

"use strict";
eval("\n\nvar _mobileNavigation = __webpack_require__(/*! ./mobileNavigation */ \"./src/js/mobileNavigation.js\");\n\nvar _header = __webpack_require__(/*! ./header */ \"./src/js/header.js\");\n\n/*!\n * @author Isis (igraziatto) Graziatto <isis.g@vanillaforums.com>\n * @copyright 2009-2018 Vanilla Forums Inc.\n * @license GPL-2.0-only\n */\n\n$(function () {\n  (0, _header.setupHeader)();\n  (0, _mobileNavigation.setupMobileNavigation)();\n\n  $(\"select\").wrap('<div class=\"SelectWrapper\"></div>');\n});//# sourceURL=[module]\n//# sourceMappingURL=data:application/json;charset=utf-8;base64,eyJ2ZXJzaW9uIjozLCJzb3VyY2VzIjpbIndlYnBhY2s6Ly8vLi9zcmMvanMvaW5kZXguanM/N2JhNSJdLCJuYW1lcyI6WyIkIiwid3JhcCJdLCJtYXBwaW5ncyI6Ijs7QUFNQTs7QUFDQTs7QUFQQTs7Ozs7O0FBU0FBLEVBQUUsWUFBTTtBQUNKO0FBQ0E7O0FBRUFBLElBQUUsUUFBRixFQUFZQyxJQUFaLENBQWlCLG1DQUFqQjtBQUNILENBTEQiLCJmaWxlIjoiLi9zcmMvanMvaW5kZXguanMuanMiLCJzb3VyY2VzQ29udGVudCI6WyIvKiFcbiAqIEBhdXRob3IgSXNpcyAoaWdyYXppYXR0bykgR3JhemlhdHRvIDxpc2lzLmdAdmFuaWxsYWZvcnVtcy5jb20+XG4gKiBAY29weXJpZ2h0IDIwMDktMjAxOCBWYW5pbGxhIEZvcnVtcyBJbmMuXG4gKiBAbGljZW5zZSBHUEwtMi4wLW9ubHlcbiAqL1xuXG5pbXBvcnQgeyBzZXR1cE1vYmlsZU5hdmlnYXRpb24gfSBmcm9tIFwiLi9tb2JpbGVOYXZpZ2F0aW9uXCI7XG5pbXBvcnQgeyBzZXR1cEhlYWRlciB9IGZyb20gXCIuL2hlYWRlclwiO1xuXG4kKCgpID0+IHtcbiAgICBzZXR1cEhlYWRlcigpO1xuICAgIHNldHVwTW9iaWxlTmF2aWdhdGlvbigpO1xuXG4gICAgJChcInNlbGVjdFwiKS53cmFwKCc8ZGl2IGNsYXNzPVwiU2VsZWN0V3JhcHBlclwiPjwvZGl2PicpO1xufSk7XG4iXSwic291cmNlUm9vdCI6IiJ9\n//# sourceURL=webpack-internal:///./src/js/index.js\n");

/***/ }),

/***/ "./src/js/mobileNavigation.js":
/*!************************************!*\
  !*** ./src/js/mobileNavigation.js ***!
  \************************************/
/*! no static exports found */
/***/ (function(module, exports, __webpack_require__) {

"use strict";
eval("\n\nObject.defineProperty(exports, \"__esModule\", {\n    value: true\n});\nexports.setupMobileNavigation = setupMobileNavigation;\n/*!\n * @author Isis (igraziatto) Graziatto <isis.g@vanillaforums.com>\n * @copyright 2009-2018 Vanilla Forums Inc.\n * @license GPL-2.0-only\n */\n\nfunction setupMobileNavigation() {\n\n    var $menuButton = $(\"#menu-button\"),\n        $navdrawer = $(\"#navdrawer\");\n\n    $menuButton.on(\"click\", function () {\n        $menuButton.toggleClass(\"isToggled\");\n        $navdrawer.toggleClass(\"isOpen\");\n    });\n}//# sourceURL=[module]\n//# sourceMappingURL=data:application/json;charset=utf-8;base64,eyJ2ZXJzaW9uIjozLCJzb3VyY2VzIjpbIndlYnBhY2s6Ly8vLi9zcmMvanMvbW9iaWxlTmF2aWdhdGlvbi5qcz9mN2JlIl0sIm5hbWVzIjpbInNldHVwTW9iaWxlTmF2aWdhdGlvbiIsIiRtZW51QnV0dG9uIiwiJCIsIiRuYXZkcmF3ZXIiLCJvbiIsInRvZ2dsZUNsYXNzIl0sIm1hcHBpbmdzIjoiOzs7OztRQU1nQkEscUIsR0FBQUEscUI7QUFOaEI7Ozs7OztBQU1PLFNBQVNBLHFCQUFULEdBQWlDOztBQUVwQyxRQUFJQyxjQUFjQyxFQUFFLGNBQUYsQ0FBbEI7QUFBQSxRQUNJQyxhQUFhRCxFQUFFLFlBQUYsQ0FEakI7O0FBR0FELGdCQUFZRyxFQUFaLENBQWUsT0FBZixFQUF3QixZQUFNO0FBQzFCSCxvQkFBWUksV0FBWixDQUF3QixXQUF4QjtBQUNBRixtQkFBV0UsV0FBWCxDQUF1QixRQUF2QjtBQUNILEtBSEQ7QUFJSCIsImZpbGUiOiIuL3NyYy9qcy9tb2JpbGVOYXZpZ2F0aW9uLmpzLmpzIiwic291cmNlc0NvbnRlbnQiOlsiLyohXG4gKiBAYXV0aG9yIElzaXMgKGlncmF6aWF0dG8pIEdyYXppYXR0byA8aXNpcy5nQHZhbmlsbGFmb3J1bXMuY29tPlxuICogQGNvcHlyaWdodCAyMDA5LTIwMTggVmFuaWxsYSBGb3J1bXMgSW5jLlxuICogQGxpY2Vuc2UgR1BMLTIuMC1vbmx5XG4gKi9cblxuZXhwb3J0IGZ1bmN0aW9uIHNldHVwTW9iaWxlTmF2aWdhdGlvbigpIHtcblxuICAgIHZhciAkbWVudUJ1dHRvbiA9ICQoXCIjbWVudS1idXR0b25cIiksXG4gICAgICAgICRuYXZkcmF3ZXIgPSAkKFwiI25hdmRyYXdlclwiKTtcblxuICAgICRtZW51QnV0dG9uLm9uKFwiY2xpY2tcIiwgKCkgPT4ge1xuICAgICAgICAkbWVudUJ1dHRvbi50b2dnbGVDbGFzcyhcImlzVG9nZ2xlZFwiKTtcbiAgICAgICAgJG5hdmRyYXdlci50b2dnbGVDbGFzcyhcImlzT3BlblwiKTtcbiAgICB9KTtcbn1cbiJdLCJzb3VyY2VSb290IjoiIn0=\n//# sourceURL=webpack-internal:///./src/js/mobileNavigation.js\n");

/***/ }),

/***/ "./src/js/utility.js":
/*!***************************!*\
  !*** ./src/js/utility.js ***!
  \***************************/
/*! no static exports found */
/***/ (function(module, exports, __webpack_require__) {

"use strict";
eval("\n\nObject.defineProperty(exports, \"__esModule\", {\n    value: true\n});\nexports.fireEvent = fireEvent;\nexports.toggleScroll = toggleScroll;\nexports.disableScroll = disableScroll;\nexports.enableScroll = enableScroll;\n/*!\n * @author Isis (igraziatto) Graziatto <isis.g@vanillaforums.com>\n * @copyright 2009-2018 Vanilla Forums Inc.\n * @license GPL-2.0-only\n */\n\nfunction fireEvent(element, eventName, options) {\n    var event = document.createEvent(\"CustomEvent\");\n    event.initCustomEvent(eventName, true, true, options);\n    element.dispatchEvent(event);\n}\n\nfunction toggleScroll() {\n    if ($(document.body)[0].style.overflow) {\n        enableScroll();\n    } else {\n        disableScroll();\n    }\n}\n\nfunction disableScroll() {\n    $(document.body).addClass(\"NoScroll\");\n}\n\nfunction enableScroll() {\n    $(document.body).removeClass(\"NoScroll\");\n}\n\n/**\n * Provides requestAnimationFrame in a cross browser way.\n */\n\nif (!window.requestAnimationFrame) {\n    window.requestAnimationFrame = function () {\n        return window.webkitRequestAnimationFrame || window.mozRequestAnimationFrame || window.oRequestAnimationFrame || window.msRequestAnimationFrame || function (\n        /* function FrameRequestCallback */callback,\n        /* DOMElement Element */element) {\n            window.setTimeout(callback, 1000 / 60);\n        };\n    }();\n}//# sourceURL=[module]\n//# sourceMappingURL=data:application/json;charset=utf-8;base64,eyJ2ZXJzaW9uIjozLCJzb3VyY2VzIjpbIndlYnBhY2s6Ly8vLi9zcmMvanMvdXRpbGl0eS5qcz8yZjY4Il0sIm5hbWVzIjpbImZpcmVFdmVudCIsInRvZ2dsZVNjcm9sbCIsImRpc2FibGVTY3JvbGwiLCJlbmFibGVTY3JvbGwiLCJlbGVtZW50IiwiZXZlbnROYW1lIiwib3B0aW9ucyIsImV2ZW50IiwiZG9jdW1lbnQiLCJjcmVhdGVFdmVudCIsImluaXRDdXN0b21FdmVudCIsImRpc3BhdGNoRXZlbnQiLCIkIiwiYm9keSIsInN0eWxlIiwib3ZlcmZsb3ciLCJhZGRDbGFzcyIsInJlbW92ZUNsYXNzIiwid2luZG93IiwicmVxdWVzdEFuaW1hdGlvbkZyYW1lIiwid2Via2l0UmVxdWVzdEFuaW1hdGlvbkZyYW1lIiwibW96UmVxdWVzdEFuaW1hdGlvbkZyYW1lIiwib1JlcXVlc3RBbmltYXRpb25GcmFtZSIsIm1zUmVxdWVzdEFuaW1hdGlvbkZyYW1lIiwiY2FsbGJhY2siLCJzZXRUaW1lb3V0Il0sIm1hcHBpbmdzIjoiOzs7OztRQU1nQkEsUyxHQUFBQSxTO1FBTUFDLFksR0FBQUEsWTtRQVFBQyxhLEdBQUFBLGE7UUFJQUMsWSxHQUFBQSxZO0FBeEJoQjs7Ozs7O0FBTU8sU0FBU0gsU0FBVCxDQUFtQkksT0FBbkIsRUFBNEJDLFNBQTVCLEVBQXVDQyxPQUF2QyxFQUFnRDtBQUNuRCxRQUFJQyxRQUFRQyxTQUFTQyxXQUFULENBQXFCLGFBQXJCLENBQVo7QUFDQUYsVUFBTUcsZUFBTixDQUFzQkwsU0FBdEIsRUFBaUMsSUFBakMsRUFBdUMsSUFBdkMsRUFBNkNDLE9BQTdDO0FBQ0FGLFlBQVFPLGFBQVIsQ0FBc0JKLEtBQXRCO0FBQ0g7O0FBRU0sU0FBU04sWUFBVCxHQUF3QjtBQUMzQixRQUFJVyxFQUFFSixTQUFTSyxJQUFYLEVBQWlCLENBQWpCLEVBQW9CQyxLQUFwQixDQUEwQkMsUUFBOUIsRUFBd0M7QUFDcENaO0FBQ0gsS0FGRCxNQUVPO0FBQ0hEO0FBQ0g7QUFDSjs7QUFFTSxTQUFTQSxhQUFULEdBQXlCO0FBQzVCVSxNQUFFSixTQUFTSyxJQUFYLEVBQWlCRyxRQUFqQixDQUEwQixVQUExQjtBQUNIOztBQUVNLFNBQVNiLFlBQVQsR0FBd0I7QUFDM0JTLE1BQUVKLFNBQVNLLElBQVgsRUFBaUJJLFdBQWpCLENBQTZCLFVBQTdCO0FBQ0g7O0FBRUQ7Ozs7QUFJQSxJQUFJLENBQUNDLE9BQU9DLHFCQUFaLEVBQW1DO0FBQy9CRCxXQUFPQyxxQkFBUCxHQUFnQyxZQUFXO0FBQ3ZDLGVBQ0lELE9BQU9FLDJCQUFQLElBQ0FGLE9BQU9HLHdCQURQLElBRUFILE9BQU9JLHNCQUZQLElBR0FKLE9BQU9LLHVCQUhQLElBSUE7QUFDSSwyQ0FBb0NDLFFBRHhDO0FBRUksZ0NBQXlCcEIsT0FGN0IsRUFHRTtBQUNFYyxtQkFBT08sVUFBUCxDQUFrQkQsUUFBbEIsRUFBNEIsT0FBTyxFQUFuQztBQUNILFNBVkw7QUFZSCxLQWI4QixFQUEvQjtBQWNIIiwiZmlsZSI6Ii4vc3JjL2pzL3V0aWxpdHkuanMuanMiLCJzb3VyY2VzQ29udGVudCI6WyIvKiFcbiAqIEBhdXRob3IgSXNpcyAoaWdyYXppYXR0bykgR3JhemlhdHRvIDxpc2lzLmdAdmFuaWxsYWZvcnVtcy5jb20+XG4gKiBAY29weXJpZ2h0IDIwMDktMjAxOCBWYW5pbGxhIEZvcnVtcyBJbmMuXG4gKiBAbGljZW5zZSBHUEwtMi4wLW9ubHlcbiAqL1xuXG5leHBvcnQgZnVuY3Rpb24gZmlyZUV2ZW50KGVsZW1lbnQsIGV2ZW50TmFtZSwgb3B0aW9ucykge1xuICAgIHZhciBldmVudCA9IGRvY3VtZW50LmNyZWF0ZUV2ZW50KFwiQ3VzdG9tRXZlbnRcIik7XG4gICAgZXZlbnQuaW5pdEN1c3RvbUV2ZW50KGV2ZW50TmFtZSwgdHJ1ZSwgdHJ1ZSwgb3B0aW9ucyk7XG4gICAgZWxlbWVudC5kaXNwYXRjaEV2ZW50KGV2ZW50KTtcbn1cblxuZXhwb3J0IGZ1bmN0aW9uIHRvZ2dsZVNjcm9sbCgpIHtcbiAgICBpZiAoJChkb2N1bWVudC5ib2R5KVswXS5zdHlsZS5vdmVyZmxvdykge1xuICAgICAgICBlbmFibGVTY3JvbGwoKTtcbiAgICB9IGVsc2Uge1xuICAgICAgICBkaXNhYmxlU2Nyb2xsKCk7XG4gICAgfVxufVxuXG5leHBvcnQgZnVuY3Rpb24gZGlzYWJsZVNjcm9sbCgpIHtcbiAgICAkKGRvY3VtZW50LmJvZHkpLmFkZENsYXNzKFwiTm9TY3JvbGxcIik7XG59XG5cbmV4cG9ydCBmdW5jdGlvbiBlbmFibGVTY3JvbGwoKSB7XG4gICAgJChkb2N1bWVudC5ib2R5KS5yZW1vdmVDbGFzcyhcIk5vU2Nyb2xsXCIpO1xufVxuXG4vKipcbiAqIFByb3ZpZGVzIHJlcXVlc3RBbmltYXRpb25GcmFtZSBpbiBhIGNyb3NzIGJyb3dzZXIgd2F5LlxuICovXG5cbmlmICghd2luZG93LnJlcXVlc3RBbmltYXRpb25GcmFtZSkge1xuICAgIHdpbmRvdy5yZXF1ZXN0QW5pbWF0aW9uRnJhbWUgPSAoZnVuY3Rpb24oKSB7XG4gICAgICAgIHJldHVybiAoXG4gICAgICAgICAgICB3aW5kb3cud2Via2l0UmVxdWVzdEFuaW1hdGlvbkZyYW1lIHx8XG4gICAgICAgICAgICB3aW5kb3cubW96UmVxdWVzdEFuaW1hdGlvbkZyYW1lIHx8XG4gICAgICAgICAgICB3aW5kb3cub1JlcXVlc3RBbmltYXRpb25GcmFtZSB8fFxuICAgICAgICAgICAgd2luZG93Lm1zUmVxdWVzdEFuaW1hdGlvbkZyYW1lIHx8XG4gICAgICAgICAgICBmdW5jdGlvbihcbiAgICAgICAgICAgICAgICAvKiBmdW5jdGlvbiBGcmFtZVJlcXVlc3RDYWxsYmFjayAqLyBjYWxsYmFjayxcbiAgICAgICAgICAgICAgICAvKiBET01FbGVtZW50IEVsZW1lbnQgKi8gZWxlbWVudFxuICAgICAgICAgICAgKSB7XG4gICAgICAgICAgICAgICAgd2luZG93LnNldFRpbWVvdXQoY2FsbGJhY2ssIDEwMDAgLyA2MCk7XG4gICAgICAgICAgICB9XG4gICAgICAgICk7XG4gICAgfSkoKTtcbn1cbiJdLCJzb3VyY2VSb290IjoiIn0=\n//# sourceURL=webpack-internal:///./src/js/utility.js\n");

/***/ })

/******/ });