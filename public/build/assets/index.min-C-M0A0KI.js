var r=(a,i)=>()=>(i||a((i={exports:{}}).exports,i),i.exports);var l=r((e,o)=>{/** 
 * FormValidation (https://formvalidation.io)
 * The best validation library for JavaScript
 * (c) 2013 - 2023 Nguyen Huu Phuoc <me@phuoc.ng>
 *
 * @license https://formvalidation.io/license
 * @package @form-validation/validator-ean
 * @version 2.4.0
 */(function(a,i){typeof e=="object"&&typeof o<"u"?o.exports=i():typeof define=="function"&&define.amd?define(i):((a=typeof globalThis<"u"?globalThis:a||self).FormValidation=a.FormValidation||{},a.FormValidation.validators=a.FormValidation.validators||{},a.FormValidation.validators.ean=i())})(void 0,function(){return function(){return{validate:function(a){if(a.value==="")return{valid:!0};if(!/^(\d{8}|\d{12}|\d{13}|\d{14})$/.test(a.value))return{valid:!1};for(var i=a.value.length,t=0,d=i===8?[3,1]:[1,3],n=0;n<i-1;n++)t+=parseInt(a.value.charAt(n),10)*d[n%2];return{valid:"".concat(t=(10-t%10)%10)===a.value.charAt(i-1)}}}}})});export default l();
