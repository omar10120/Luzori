var o=(a,e)=>()=>(e||a((e={exports:{}}).exports,e),e.exports);var t=o((r,i)=>{/** 
 * FormValidation (https://formvalidation.io)
 * The best validation library for JavaScript
 * (c) 2013 - 2023 Nguyen Huu Phuoc <me@phuoc.ng>
 *
 * @license https://formvalidation.io/license
 * @package @form-validation/validator-rtn
 * @version 2.4.0
 */(function(a,e){typeof r=="object"&&typeof i<"u"?i.exports=e():typeof define=="function"&&define.amd?define(e):((a=typeof globalThis<"u"?globalThis:a||self).FormValidation=a.FormValidation||{},a.FormValidation.validators=a.FormValidation.validators||{},a.FormValidation.validators.rtn=e())})(void 0,function(){return function(){return{validate:function(a){if(a.value==="")return{valid:!0};if(!/^\d{9}$/.test(a.value))return{valid:!1};for(var e=0,n=0;n<a.value.length;n+=3)e+=3*parseInt(a.value.charAt(n),10)+7*parseInt(a.value.charAt(n+1),10)+parseInt(a.value.charAt(n+2),10);return{valid:e!==0&&e%10==0}}}}})});export default t();
