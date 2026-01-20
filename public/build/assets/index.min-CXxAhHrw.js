var m=(e,a)=>()=>(a||e((a={exports:{}}).exports,a),a.exports);var g=m((f,u)=>{/** 
 * FormValidation (https://formvalidation.io)
 * The best validation library for JavaScript
 * (c) 2013 - 2023 Nguyen Huu Phuoc <me@phuoc.ng>
 *
 * @license https://formvalidation.io/license
 * @package @form-validation/validator-step
 * @version 2.4.0
 */(function(e,a){typeof f=="object"&&typeof u<"u"?u.exports=a(require("@form-validation/core")):typeof define=="function"&&define.amd?define(["@form-validation/core"],a):((e=typeof globalThis<"u"?globalThis:e||self).FormValidation=e.FormValidation||{},e.FormValidation.validators=e.FormValidation.validators||{},e.FormValidation.validators.step=a(e.FormValidation))})(void 0,function(e){var a=e.utils.format;return function(){var d=function(t,n){if(n===0)return 1;var i="".concat(t).split("."),r="".concat(n).split("."),c=(i.length===1?0:i[1].length)+(r.length===1?0:r[1].length);return function(p,v){var l,s=Math.pow(10,v),o=p*s;switch(!0){case o===0:l=0;break;case o>0:l=1;break;case o<0:l=-1}return o%1==.5*l?(Math.floor(o)+(l>0?1:0))/s:Math.round(o)/s}(t-n*Math.floor(t/n),c)};return{validate:function(t){if(t.value==="")return{valid:!0};var n=parseFloat(t.value);if(isNaN(n)||!isFinite(n))return{valid:!1};var i=Object.assign({},{baseValue:0,message:"",step:1},t.options),r=d(n-i.baseValue,i.step);return{message:a(t.l10n?i.message||t.l10n.step.default:i.message,"".concat(i.step)),valid:r===0||r===i.step}}}}})});export default g();
