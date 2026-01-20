var V=(e,a)=>()=>(a||e((a={exports:{}}).exports,a),a.exports);var y=V((z,v)=>{/** 
 * FormValidation (https://formvalidation.io)
 * The best validation library for JavaScript
 * (c) 2013 - 2023 Nguyen Huu Phuoc <me@phuoc.ng>
 *
 * @license https://formvalidation.io/license
 * @package @form-validation/validator-email-address
 * @version 2.4.0
 */(function(e,a){typeof z=="object"&&typeof v<"u"?v.exports=a(require("@form-validation/core")):typeof define=="function"&&define.amd?define(["@form-validation/core"],a):((e=typeof globalThis<"u"?globalThis:e||self).FormValidation=e.FormValidation||{},e.FormValidation.validators=e.FormValidation.validators||{},e.FormValidation.validators.emailAddress=a(e.FormValidation))})(void 0,function(e){var a=e.utils.removeUndefined,Z=/^(([^<>()[\]\\.,;:\s@"]+(\.[^<>()[\]\\.,;:\s@"]+)*)|(".+"))@[a-zA-Z0-9](?:[a-zA-Z0-9-]{0,61}[a-zA-Z0-9])?(?:\.[a-zA-Z0-9](?:[a-zA-Z0-9-]{0,61}[a-zA-Z0-9])?)*$/,c=/^(([^<>()[\]\\.,;:\s@"]+(\.[^<>()[\]\\.,;:\s@"]+)*)|(".+"))@[a-zA-Z0-9](?:[a-zA-Z0-9-]{0,61}[a-zA-Z0-9])?(?:\.[a-zA-Z0-9](?:[a-zA-Z0-9-]{0,61}[a-zA-Z0-9])?)+$/;return function(){return{validate:function(o){if(o.value==="")return{valid:!0};var l=Object.assign({},{multiple:!1,requireGlobalDomain:!1,separator:/[,;]/},a(o.options)),p=l.requireGlobalDomain?c:Z;if(l.multiple===!0||"".concat(l.multiple)==="true"){for(var h=l.separator||/[,;]/,m=function(g,F){for(var d=g.split(/"/),A=d.length,n=[],i="",t=0;t<A;t++)if(t%2==0){var r=d[t].split(F),s=r.length;if(s===1)i+=r[0];else{n.push(i+r[0]);for(var u=1;u<s-1;u++)n.push(r[u]);i=r[s-1]}}else i+='"'+d[t],t<A-1&&(i+='"');return n.push(i),n}(o.value,h),b=m.length,f=0;f<b;f++)if(!p.test(m[f]))return{valid:!1};return{valid:!0}}return{valid:p.test(o.value)}}}}})});export default y();
