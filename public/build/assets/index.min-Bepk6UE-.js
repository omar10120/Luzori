var c=(e,a)=>()=>(a||e((a={exports:{}}).exports,a),a.exports);var f=c((d,l)=>{/** 
 * FormValidation (https://formvalidation.io)
 * The best validation library for JavaScript
 * (c) 2013 - 2023 Nguyen Huu Phuoc <me@phuoc.ng>
 *
 * @license https://formvalidation.io/license
 * @package @form-validation/validator-between
 * @version 2.4.0
 */(function(e,a){typeof d=="object"&&typeof l<"u"?l.exports=a(require("@form-validation/core")):typeof define=="function"&&define.amd?define(["@form-validation/core"],a):((e=typeof globalThis<"u"?globalThis:e||self).FormValidation=e.FormValidation||{},e.FormValidation.validators=e.FormValidation.validators||{},e.FormValidation.validators.between=a(e.FormValidation))})(void 0,function(e){var a=e.utils.format,u=e.utils.removeUndefined;return function(){var s=function(n){return parseFloat("".concat(n).replace(",","."))};return{validate:function(n){var i=n.value;if(i==="")return{valid:!0};var t=Object.assign({},{inclusive:!0,message:""},u(n.options)),o=s(t.min),r=s(t.max);return t.inclusive?{message:a(n.l10n?t.message||n.l10n.between.default:t.message,["".concat(o),"".concat(r)]),valid:parseFloat(i)>=o&&parseFloat(i)<=r}:{message:a(n.l10n?t.message||n.l10n.between.notInclusive:t.message,["".concat(o),"".concat(r)]),valid:parseFloat(i)>o&&parseFloat(i)<r}}}}})});export default f();
