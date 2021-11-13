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
/******/ 	return __webpack_require__(__webpack_require__.s = "./jet-forms.action.js");
/******/ })
/************************************************************************/
/******/ ({

/***/ "./jet-forms.action.js":
/*!*****************************!*\
  !*** ./jet-forms.action.js ***!
  \*****************************/
/*! no static exports found */
/***/ (function(module, exports) {

eval("function ownKeys(object, enumerableOnly) { var keys = Object.keys(object); if (Object.getOwnPropertySymbols) { var symbols = Object.getOwnPropertySymbols(object); if (enumerableOnly) { symbols = symbols.filter(function (sym) { return Object.getOwnPropertyDescriptor(object, sym).enumerable; }); } keys.push.apply(keys, symbols); } return keys; }\n\nfunction _objectSpread(target) { for (var i = 1; i < arguments.length; i++) { var source = arguments[i] != null ? arguments[i] : {}; if (i % 2) { ownKeys(Object(source), true).forEach(function (key) { _defineProperty(target, key, source[key]); }); } else if (Object.getOwnPropertyDescriptors) { Object.defineProperties(target, Object.getOwnPropertyDescriptors(source)); } else { ownKeys(Object(source)).forEach(function (key) { Object.defineProperty(target, key, Object.getOwnPropertyDescriptor(source, key)); }); } } return target; }\n\nfunction _defineProperty(obj, key, value) { if (key in obj) { Object.defineProperty(obj, key, { value: value, enumerable: true, configurable: true, writable: true }); } else { obj[key] = value; } return obj; }\n\nfunction _slicedToArray(arr, i) { return _arrayWithHoles(arr) || _iterableToArrayLimit(arr, i) || _unsupportedIterableToArray(arr, i) || _nonIterableRest(); }\n\nfunction _nonIterableRest() { throw new TypeError(\"Invalid attempt to destructure non-iterable instance.\\nIn order to be iterable, non-array objects must have a [Symbol.iterator]() method.\"); }\n\nfunction _unsupportedIterableToArray(o, minLen) { if (!o) return; if (typeof o === \"string\") return _arrayLikeToArray(o, minLen); var n = Object.prototype.toString.call(o).slice(8, -1); if (n === \"Object\" && o.constructor) n = o.constructor.name; if (n === \"Map\" || n === \"Set\") return Array.from(o); if (n === \"Arguments\" || /^(?:Ui|I)nt(?:8|16|32)(?:Clamped)?Array$/.test(n)) return _arrayLikeToArray(o, minLen); }\n\nfunction _arrayLikeToArray(arr, len) { if (len == null || len > arr.length) len = arr.length; for (var i = 0, arr2 = new Array(len); i < len; i++) { arr2[i] = arr[i]; } return arr2; }\n\nfunction _iterableToArrayLimit(arr, i) { var _i = arr == null ? null : typeof Symbol !== \"undefined\" && arr[Symbol.iterator] || arr[\"@@iterator\"]; if (_i == null) return; var _arr = []; var _n = true; var _d = false; var _s, _e; try { for (_i = _i.call(arr); !(_n = (_s = _i.next()).done); _n = true) { _arr.push(_s.value); if (i && _arr.length === i) break; } } catch (err) { _d = true; _e = err; } finally { try { if (!_n && _i[\"return\"] != null) _i[\"return\"](); } finally { if (_d) throw _e; } } return _arr; }\n\nfunction _arrayWithHoles(arr) { if (Array.isArray(arr)) return arr; }\n\nvar _wp$components = wp.components,\n    TextControl = _wp$components.TextControl,\n    SelectControl = _wp$components.SelectControl;\nvar _wp$element = wp.element,\n    useState = _wp$element.useState,\n    useEffect = _wp$element.useEffect;\nvar _JetFBActions = JetFBActions,\n    addAction = _JetFBActions.addAction,\n    getFormFieldsBlocks = _JetFBActions.getFormFieldsBlocks,\n    withPlaceholder = _JetFBActions.Tools.withPlaceholder;\nvar _JetFBComponents = JetFBComponents,\n    ActionFieldsMap = _JetFBComponents.ActionFieldsMap,\n    WrapperRequiredControl = _JetFBComponents.WrapperRequiredControl;\nvar addFilter = wp.hooks.addFilter;\naddFilter('jet.fb.preset.editor.custom.condition', 'jet-form-builder', function (isVisible, customCondition, state) {\n  if ('cct_query_var' === customCondition) {\n    return 'custom_content_type' === state.from && 'query_var' === state.post_from;\n  }\n\n  return false;\n});\naddAction('insert_custom_content_type', function CCTAction(_ref) {\n  var settings = _ref.settings,\n      label = _ref.label,\n      help = _ref.help,\n      source = _ref.source,\n      onChangeSetting = _ref.onChangeSetting,\n      getMapField = _ref.getMapField,\n      setMapField = _ref.setMapField;\n\n  var _useState = useState([]),\n      _useState2 = _slicedToArray(_useState, 2),\n      cctFields = _useState2[0],\n      setCctFields = _useState2[1];\n\n  var _useState3 = useState([]),\n      _useState4 = _slicedToArray(_useState3, 2),\n      cctFieldsMap = _useState4[0],\n      setCctFieldsMap = _useState4[1];\n\n  var _useState5 = useState(false),\n      _useState6 = _slicedToArray(_useState5, 2),\n      isLoading = _useState6[0],\n      setLoading = _useState6[1];\n\n  var _useState7 = useState(function () {\n    var responseBlocks = {};\n    getFormFieldsBlocks().forEach(function (block) {\n      responseBlocks[block.value] = {\n        label: block.label\n      };\n    });\n    return Object.entries(responseBlocks);\n  }, []),\n      _useState8 = _slicedToArray(_useState7, 1),\n      formFieldsList = _useState8[0];\n\n  var fetchTypeFields = function fetchTypeFields(type) {\n    if (!type) {\n      return;\n    }\n\n    setLoading(true);\n    wp.apiFetch({\n      method: 'get',\n      path: source.fetch_path + '?type=' + type\n    }).then(function (response) {\n      if (response.success && response.fields) {\n        var typeFields = [];\n\n        for (var i = 0; i < response.fields.length; i++) {\n          if ('_ID' === response.fields[i].value) {\n            response.fields[i].label += ' (will update the item)';\n          }\n\n          typeFields.push(_objectSpread({}, response.fields[i]));\n        }\n\n        setCctFields(typeFields);\n      } else {\n        alert(response.notices[i].join('; ') + ';');\n      }\n\n      setLoading(false);\n    }).catch(function (e) {\n      setLoading(false);\n      alert(e);\n      console.log(e);\n    });\n  };\n\n  useEffect(function () {\n    fetchTypeFields(settings.type);\n  }, []);\n  useEffect(function () {\n    if (!settings.type) {\n      setCctFields([]);\n    }\n  }, [settings.type]);\n  useEffect(function () {\n    var cctMap = {};\n    cctFields.forEach(function (field) {\n      if ('_ID' !== field.value) {\n        cctMap[field.value] = {\n          label: field.label\n        };\n      }\n    });\n    setCctFieldsMap(Object.entries(cctMap));\n  }, [cctFields]);\n  return wp.element.createElement(React.Fragment, null, wp.element.createElement(SelectControl, {\n    label: label('type'),\n    labelPosition: \"side\",\n    value: settings.type,\n    onChange: function onChange(newValue) {\n      onChangeSetting(newValue, 'type');\n      fetchTypeFields(newValue);\n    },\n    options: withPlaceholder(source.types)\n  }), wp.element.createElement(SelectControl, {\n    label: label('status'),\n    labelPosition: \"side\",\n    value: settings.status,\n    onChange: function onChange(newValue) {\n      onChangeSetting(newValue, 'status');\n    },\n    options: withPlaceholder(source.statuses)\n  }), wp.element.createElement(\"div\", {\n    style: {\n      opacity: isLoading ? '0.5' : '1'\n    },\n    className: \"jet-control-full\"\n  }, wp.element.createElement(ActionFieldsMap, {\n    label: label('fields_map'),\n    fields: formFieldsList,\n    plainHelp: help('fields_map')\n  }, function (_ref2) {\n    var fieldId = _ref2.fieldId,\n        fieldData = _ref2.fieldData,\n        index = _ref2.index;\n    return wp.element.createElement(WrapperRequiredControl, {\n      field: [fieldId, fieldData]\n    }, wp.element.createElement(SelectControl, {\n      key: fieldId + index,\n      value: getMapField({\n        name: fieldId\n      }),\n      onChange: function onChange(value) {\n        return setMapField({\n          nameField: fieldId,\n          value: value\n        });\n      },\n      options: withPlaceholder(cctFields)\n    }));\n  }), 0 < cctFieldsMap.length && wp.element.createElement(ActionFieldsMap, {\n    label: label('default_fields'),\n    fields: cctFieldsMap,\n    plainHelp: help('default_fields')\n  }, function (_ref3) {\n    var fieldId = _ref3.fieldId,\n        fieldData = _ref3.fieldData,\n        index = _ref3.index;\n    return wp.element.createElement(WrapperRequiredControl, {\n      field: [fieldId, fieldData]\n    }, wp.element.createElement(TextControl, {\n      key: fieldId + index,\n      value: getMapField({\n        source: 'default_fields',\n        name: fieldId\n      }),\n      onChange: function onChange(value) {\n        return setMapField({\n          source: 'default_fields',\n          nameField: fieldId,\n          value: value\n        });\n      }\n    }));\n  })));\n});\n\n//# sourceURL=webpack:///./jet-forms.action.js?");

/***/ })

/******/ });