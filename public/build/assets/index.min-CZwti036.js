var p=(t,n)=>()=>(n||t((n={exports:{}}).exports,n),n.exports);var b=p((v,u)=>{/** 
 * FormValidation (https://formvalidation.io)
 * The best validation library for JavaScript
 * (c) 2013 - 2023 Nguyen Huu Phuoc <me@phuoc.ng>
 *
 * @license https://formvalidation.io/license
 * @package @form-validation/validator-string-length
 * @version 2.4.0
 */(function(t,n){typeof v=="object"&&typeof u<"u"?u.exports=n(require("@form-validation/core")):typeof define=="function"&&define.amd?define(["@form-validation/core"],n):((t=typeof globalThis<"u"?globalThis:t||self).FormValidation=t.FormValidation||{},t.FormValidation.validators=t.FormValidation.validators||{},t.FormValidation.validators.stringLength=n(t.FormValidation))})(void 0,function(t){var n=t.utils.format,h=t.utils.removeUndefined;return function(){return{validate:function(a){var e=Object.assign({},{message:"",trim:!1,utf8Bytes:!1},h(a.options)),m=e.trim===!0||"".concat(e.trim)==="true"?a.value.trim():a.value;if(m==="")return{valid:!0};var r=e.min?"".concat(e.min):"",i=e.max?"".concat(e.max):"",g=e.utf8Bytes?function(f){for(var d=f.length,l=f.length-1;l>=0;l--){var s=f.charCodeAt(l);s>127&&s<=2047?d++:s>2047&&s<=65535&&(d+=2),s>=56320&&s<=57343&&l--}return d}(m):m.length,c=!0,o=a.l10n?e.message||a.l10n.stringLength.default:e.message;switch((r&&g<parseInt(r,10)||i&&g>parseInt(i,10))&&(c=!1),!0){case(!!r&&!!i):o=n(a.l10n?e.message||a.l10n.stringLength.between:e.message,[r,i]);break;case!!r:o=n(a.l10n?e.message||a.l10n.stringLength.more:e.message,"".concat(parseInt(r,10)));break;case!!i:o=n(a.l10n?e.message||a.l10n.stringLength.less:e.message,"".concat(parseInt(i,10)))}return{message:o,valid:c}}}}})});export default b();
