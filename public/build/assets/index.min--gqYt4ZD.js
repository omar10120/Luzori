var s=(e,i)=>()=>(i||e((i={exports:{}}).exports,i),i.exports);var u=s((f,d)=>{/** 
 * FormValidation (https://formvalidation.io)
 * The best validation library for JavaScript
 * (c) 2013 - 2023 Nguyen Huu Phuoc <me@phuoc.ng>
 *
 * @license https://formvalidation.io/license
 * @package @form-validation/validator-meid
 * @version 2.4.0
 */(function(e,i){typeof f=="object"&&typeof d<"u"?d.exports=i(require("@form-validation/core")):typeof define=="function"&&define.amd?define(["@form-validation/core"],i):((e=typeof globalThis<"u"?globalThis:e||self).FormValidation=e.FormValidation||{},e.FormValidation.validators=e.FormValidation.validators||{},e.FormValidation.validators.meid=i(e.FormValidation))})(void 0,function(e){var i=e.algorithms.luhn;return function(){return{validate:function(o){if(o.value==="")return{valid:!0};var t=o.value;if(/^[0-9A-F]{15}$/i.test(t)||/^[0-9A-F]{2}[- ][0-9A-F]{6}[- ][0-9A-F]{6}[- ][0-9A-F]$/i.test(t)||/^\d{19}$/.test(t)||/^\d{5}[- ]\d{5}[- ]\d{4}[- ]\d{4}[- ]\d$/.test(t)){var l=t.charAt(t.length-1).toUpperCase();if((t=t.replace(/[- ]/g,"")).match(/^\d*$/i))return{valid:i(t)};t=t.slice(0,-1);var n="",a=void 0;for(a=1;a<=13;a+=2)n+=(2*parseInt(t.charAt(a),16)).toString(16);var r=0;for(a=0;a<n.length;a++)r+=parseInt(n.charAt(a),16);return{valid:r%10==0?l==="0":l===(2*(10*Math.floor((r+10)/10)-r)).toString(16).toUpperCase()}}return/^[0-9A-F]{14}$/i.test(t)||/^[0-9A-F]{2}[- ][0-9A-F]{6}[- ][0-9A-F]{6}$/i.test(t)||/^\d{18}$/.test(t)||/^\d{5}[- ]\d{5}[- ]\d{4}[- ]\d{4}$/.test(t)?{valid:!0}:{valid:!1}}}}})});export default u();
