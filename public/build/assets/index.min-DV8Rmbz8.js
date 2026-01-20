var r=(a,e)=>()=>(e||a((e={exports:{}}).exports,e),e.exports);var l=r((o,n)=>{/** 
 * FormValidation (https://formvalidation.io)
 * The best validation library for JavaScript
 * (c) 2013 - 2023 Nguyen Huu Phuoc <me@phuoc.ng>
 *
 * @license https://formvalidation.io/license
 * @package @form-validation/validator-issn
 * @version 2.4.0
 */(function(a,e){typeof o=="object"&&typeof n<"u"?n.exports=e():typeof define=="function"&&define.amd?define(e):((a=typeof globalThis<"u"?globalThis:a||self).FormValidation=a.FormValidation||{},a.FormValidation.validators=a.FormValidation.validators||{},a.FormValidation.validators.issn=e())})(void 0,function(){return function(){return{validate:function(a){if(a.value==="")return{valid:!0};if(!/^\d{4}-\d{3}[\dX]$/.test(a.value))return{valid:!1};var e=a.value.replace(/[^0-9X]/gi,"").split(""),d=e.length,i=0;e[7]==="X"&&(e[7]="10");for(var t=0;t<d;t++)i+=parseInt(e[t],10)*(8-t);return{valid:i%11==0}}}}})});export default l();
