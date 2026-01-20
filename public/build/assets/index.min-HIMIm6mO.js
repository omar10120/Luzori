var l=(a,t)=>()=>(t||a((t={exports:{}}).exports,t),t.exports);var n=l((i,d)=>{/** 
 * FormValidation (https://formvalidation.io)
 * The best validation library for JavaScript
 * (c) 2013 - 2023 Nguyen Huu Phuoc <me@phuoc.ng>
 *
 * @license https://formvalidation.io/license
 * @package @form-validation/validator-imei
 * @version 2.4.0
 */(function(a,t){typeof i=="object"&&typeof d<"u"?d.exports=t(require("@form-validation/core")):typeof define=="function"&&define.amd?define(["@form-validation/core"],t):((a=typeof globalThis<"u"?globalThis:a||self).FormValidation=a.FormValidation||{},a.FormValidation.validators=a.FormValidation.validators||{},a.FormValidation.validators.imei=t(a.FormValidation))})(void 0,function(a){var t=a.algorithms.luhn;return function(){return{validate:function(e){if(e.value==="")return{valid:!0};switch(!0){case/^\d{15}$/.test(e.value):case/^\d{2}-\d{6}-\d{6}-\d{1}$/.test(e.value):case/^\d{2}\s\d{6}\s\d{6}\s\d{1}$/.test(e.value):return{valid:t(e.value.replace(/[^0-9]/g,""))};case/^\d{14}$/.test(e.value):case/^\d{16}$/.test(e.value):case/^\d{2}-\d{6}-\d{6}(|-\d{2})$/.test(e.value):case/^\d{2}\s\d{6}\s\d{6}(|\s\d{2})$/.test(e.value):return{valid:!0};default:return{valid:!1}}}}}})});export default n();
