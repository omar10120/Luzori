var u=(a,t)=>()=>(t||a((t={exports:{}}).exports,t),t.exports);var o=u((r,s)=>{/** 
 * FormValidation (https://formvalidation.io)
 * The best validation library for JavaScript
 * (c) 2013 - 2023 Nguyen Huu Phuoc <me@phuoc.ng>
 *
 * @license https://formvalidation.io/license
 * @package @form-validation/validator-isbn
 * @version 2.4.0
 */(function(a,t){typeof r=="object"&&typeof s<"u"?s.exports=t():typeof define=="function"&&define.amd?define(t):((a=typeof globalThis<"u"?globalThis:a||self).FormValidation=a.FormValidation||{},a.FormValidation.validators=a.FormValidation.validators||{},a.FormValidation.validators.isbn=t())})(void 0,function(){return function(){return{validate:function(a){if(a.value==="")return{meta:{type:null},valid:!0};var t;switch(!0){case/^\d{9}[\dX]$/.test(a.value):case(a.value.length===13&&/^(\d+)-(\d+)-(\d+)-([\dX])$/.test(a.value)):case(a.value.length===13&&/^(\d+)\s(\d+)\s(\d+)\s([\dX])$/.test(a.value)):t="ISBN10";break;case/^(978|979)\d{9}[\dX]$/.test(a.value):case(a.value.length===17&&/^(978|979)-(\d+)-(\d+)-(\d+)-([\dX])$/.test(a.value)):case(a.value.length===17&&/^(978|979)\s(\d+)\s(\d+)\s(\d+)\s([\dX])$/.test(a.value)):t="ISBN13";break;default:return{meta:{type:null},valid:!1}}var e,d,l=a.value.replace(/[^0-9X]/gi,"").split(""),i=l.length,n=0;switch(t){case"ISBN10":for(n=0,e=0;e<i-1;e++)n+=parseInt(l[e],10)*(10-e);return(d=11-n%11)===11?d=0:d===10&&(d="X"),{meta:{type:t},valid:"".concat(d)===l[i-1]};case"ISBN13":for(n=0,e=0;e<i-1;e++)n+=e%2==0?parseInt(l[e],10):3*parseInt(l[e],10);return(d=10-n%10)===10&&(d="0"),{meta:{type:t},valid:"".concat(d)===l[i-1]}}}}}})});export default o();
