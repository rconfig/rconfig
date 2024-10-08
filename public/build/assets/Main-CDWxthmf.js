import{d as ve,o as P,c as T,r as pe,n as N,u as o,a as Z,b as A,e as X,f as z,g as n,w,_ as R,h as H,i as E,j as D,k as s,t as C,l as M,m as F,p as ee,I as O,q as ge,s as he,v as xe}from"./app-Bx3zayJ3.js";import{u as be}from"./useToaster-lWiG7Bcj.js";/* empty css               */const te=ve({__name:"CardFooter",props:{class:{}},setup(r){const V=r;return(l,f)=>(P(),T("div",{class:N(o(Z)("flex items-center p-6 pt-0",V.class))},[pe(l.$slots,"default")],2))}});function we(){const r=A([]),V=A([]),l=A([]),f=A(!1),L=A(!1),u=A(!1),{toastSuccess:c,toastError:e}=be();async function d(t={}){f.value=!0;try{const h=await X.get("/api/dashboard/sysinfo");r.value=h.data}catch(h){console.error("Error fetching sysinfo:",h),e("Error","Failed to fetch sysinfo.")}finally{f.value=!1}}async function g(t={}){L.value=!0;try{const h=await X.get("/api/dashboard/configinfo");V.value=h.data}catch(h){console.error("Error fetching configinfo:",h),e("Error","Failed to fetch configinfo.")}finally{L.value=!1}}async function v(t={}){u.value=!0;try{const h=await X.get("/api/dashboard/health-latest");l.value=h.data}catch(h){console.error("Error fetching healthLatest:",h),e("Error","Failed to fetch healthLatest.")}finally{u.value=!1}}return{fetchSysinfo:d,fetchConfiginfo:g,fetchHealth:v,sysinfo:r,configinfo:V,healthLatest:l,isLoadingSysinfo:f,isLoadingConfiginfo:L,isLoadingHealth:u,toastSuccess:c,toastError:e}}const _={__name:"Skeleton",props:{class:{type:null,required:!1}},setup(r){const V=r;return(l,f)=>(P(),T("div",{class:N(o(Z)("animate-pulse rounded-md bg-primary/10",V.class))},null,2))}},_e={class:"grid gap-4 p-8 md:grid-cols-2 md:gap-8 lg:grid-cols-4"},Se={key:0},$e={class:"text-2xl font-bold"},Le={key:1,class:"flex items-center space-x-4"},Ce={class:"space-y-2"},ke={key:0},Ee={class:"text-2xl font-bold"},Pe={key:1,class:"flex items-center space-x-4"},Te={class:"space-y-2"},Ve={key:0},Oe={class:"text-2xl font-bold"},Me={key:1,class:"flex items-center space-x-4"},je={class:"space-y-2"},Ae={key:0},Ie={class:"text-2xl font-bold"},Ne={key:1,class:"flex items-center space-x-4"},Re={class:"space-y-2"},He={__name:"ConfigInfoCards",props:{configinfo:{type:Object,required:!0},isLoadingConfiginfo:{type:Boolean,required:!0}},setup(r){return A(!1),(V,l)=>{const f=z("Icon");return P(),T("div",_e,[n(o(F),{class:"border shadow rounded-xl bg-card text-card-foreground"},{default:w(()=>[n(o(R),{class:"flex flex-row items-center justify-between pb-2 space-y-0"},{default:w(()=>[n(o(H),{class:"text-sm font-medium"},{default:w(()=>l[0]||(l[0]=[E("Device count")])),_:1}),n(f,{icon:"devicon:networkx",class:"w-4 h-4 text-muted-foreground"})]),_:1}),n(o(D),null,{default:w(()=>[r.configinfo.data?(P(),T("div",Se,[s("div",$e,C(r.configinfo.data.deviceCount),1),l[1]||(l[1]=s("p",{class:"text-xs text-muted-foreground"},"Total count of devices",-1))])):M("",!0),r.isLoadingConfiginfo?(P(),T("div",Le,[n(o(_),{class:"w-12 h-12 rounded-full"}),s("div",Ce,[n(o(_),{class:"h-4 w-[250px]"}),n(o(_),{class:"h-4 w-[200px]"})])])):M("",!0)]),_:1})]),_:1}),n(o(F),null,{default:w(()=>[n(o(R),{class:"flex flex-row items-center justify-between pb-2 space-y-0"},{default:w(()=>[n(o(H),{class:"text-sm font-medium"},{default:w(()=>l[2]||(l[2]=[E("Devices down")])),_:1}),n(f,{icon:"fluent-color:cloud-dismiss-24",class:"w-4 h-4 text-muted-foreground"})]),_:1}),n(o(D),null,{default:w(()=>[r.configinfo.data?(P(),T("div",ke,[s("div",Ee,C(r.configinfo.data.deviceDownCount),1),l[3]||(l[3]=s("p",{class:"text-xs text-muted-foreground"},"Total number of devices unreachable",-1))])):M("",!0),r.isLoadingConfiginfo?(P(),T("div",Pe,[n(o(_),{class:"w-12 h-12 rounded-full"}),s("div",Te,[n(o(_),{class:"h-4 w-[250px]"}),n(o(_),{class:"h-4 w-[200px]"})])])):M("",!0)]),_:1})]),_:1}),n(o(F),null,{default:w(()=>[n(o(R),{class:"flex flex-row items-center justify-between pb-2 space-y-0"},{default:w(()=>[n(o(H),{class:"text-sm font-medium"},{default:w(()=>l[4]||(l[4]=[E("Total configs")])),_:1}),n(f,{icon:"fluent-color:checkbox-24",class:"w-4 h-4 text-muted-foreground"})]),_:1}),n(o(D),null,{default:w(()=>[r.configinfo.data?(P(),T("div",Ve,[s("div",Oe,C(r.configinfo.data.configTotalCount),1),l[5]||(l[5]=s("p",{class:"text-xs text-muted-foreground"},"Total number of configs downloaded",-1))])):M("",!0),r.isLoadingConfiginfo?(P(),T("div",Me,[n(o(_),{class:"w-12 h-12 rounded-full"}),s("div",je,[n(o(_),{class:"h-4 w-[250px]"}),n(o(_),{class:"h-4 w-[200px]"})])])):M("",!0)]),_:1})]),_:1}),n(o(F),null,{default:w(()=>[n(o(R),{class:"flex flex-row items-center justify-between pb-2 space-y-0"},{default:w(()=>[n(o(H),{class:"text-sm font-medium"},{default:w(()=>l[6]||(l[6]=[E("Failed configs")])),_:1}),n(f,{icon:"fluent-color:calendar-cancel-24",class:"w-4 h-4 text-muted-foreground"})]),_:1}),n(o(D),null,{default:w(()=>[r.configinfo.data?(P(),T("div",Ae,[s("div",Ie,C(r.configinfo.data.configDownCount),1),l[7]||(l[7]=s("p",{class:"text-xs text-muted-foreground"},"Number of configuration download failures",-1))])):M("",!0),r.isLoadingConfiginfo?(P(),T("div",Ne,[n(o(_),{class:"w-12 h-12 rounded-full"}),s("div",Re,[n(o(_),{class:"h-4 w-[250px]"}),n(o(_),{class:"h-4 w-[200px]"})])])):M("",!0)]),_:1})]),_:1})])}}},De={class:"grid gap-0.5"},Fe={class:"flex items-center gap-1 ml-auto"},Be={key:0,class:"flex items-start space-x-4"},Qe={class:"space-y-2"},Ue={key:1},qe={key:2,class:"grid gap-3"},ze={class:"grid gap-3"},Ye={class:"flex items-center justify-between"},Ge={class:"text-muted-foreground"},Je={class:"flex items-center gap-2"},Xe={class:"flex items-center justify-between"},Ke={class:"text-muted-foreground"},We={class:"flex items-center gap-2"},Ze={class:"flex items-center justify-between"},et={class:"text-muted-foreground"},tt={class:"flex items-center gap-2"},nt={class:"flex items-center justify-between"},ot={class:"text-muted-foreground"},st={class:"flex items-center gap-2"},rt={class:"flex items-center justify-between"},it={class:"text-muted-foreground"},at={class:"flex items-center gap-2"},lt={class:"flex items-center justify-between"},ct={class:"text-muted-foreground"},ut={class:"flex items-center gap-2"},dt={class:"flex items-center justify-between"},ft={class:"text-muted-foreground"},mt={class:"flex items-center gap-2"},yt={class:"text-xs text-muted-foreground"},vt={dateTime:"2023-11-23"},pt={__name:"HealthLatestCards",props:{healthLatest:{type:Object,required:!0},isLoadingHealth:{type:Boolean,required:!0},SystemUptime:{type:String}},emits:["refresh"],setup(r,{emit:V}){A({}),A({});const l=V;function f(){l("refresh")}return(L,u)=>{const c=z("Button");return P(),T("div",null,[n(o(F),{class:"overflow-hidden"},{default:w(()=>[n(o(R),{class:"flex flex-row items-start bg-muted/50"},{default:w(()=>[s("div",De,[n(o(H),{class:"flex items-center gap-2 text-lg group"},{default:w(()=>u[1]||(u[1]=[E("Latest Health")])),_:1}),n(o(ee),null,{default:w(()=>u[2]||(u[2]=[E("Server health information")])),_:1})]),s("div",Fe,[n(c,{onClick:u[0]||(u[0]=e=>f()),size:"sm",variant:"none",class:"gap-1"},{default:w(()=>[n(o(O),{icon:"flat-color-icons:refresh",class:"hover:animate-pulse"})]),_:1})])]),_:1}),n(o(D),{class:"text-sm"},{default:w(()=>[r.isLoadingHealth?(P(),T("div",Be,[n(o(_),{class:"w-12 h-12 rounded-full"}),s("div",Qe,[n(o(_),{class:"h-4 w-[250px]"}),n(o(_),{class:"h-4 w-[250px]"}),n(o(_),{class:"h-4 w-[250px]"}),n(o(_),{class:"h-4 w-[250px]"}),n(o(_),{class:"h-4 w-[250px]"}),n(o(_),{class:"h-4 w-[250px]"}),n(o(_),{class:"h-4 w-[250px]"})])])):M("",!0),r.healthLatest?M("",!0):(P(),T("div",Ue,"asd")),r.healthLatest.data?(P(),T("div",qe,[s("dl",ze,[s("div",Ye,[s("dt",Ge,C(r.healthLatest.data[0].label),1),s("dd",Je,[n(o(O),{icon:r.healthLatest.data[0].status==="Ok"?"fluent-color:checkmark-circle-32":"fluent-color:dismiss-circle-32"},null,8,["icon"])])]),s("div",Xe,[s("dt",Ke,C(r.healthLatest.data[1].label),1),s("dd",We,[n(o(O),{icon:r.healthLatest.data[1].status==="Ok"?"fluent-color:checkmark-circle-32":"fluent-color:dismiss-circle-32"},null,8,["icon"])])]),s("div",Ze,[s("dt",et,C(r.healthLatest.data[2].label),1),s("dd",tt,C(r.healthLatest.data[2].status),1)]),s("div",nt,[s("dt",ot,C(r.healthLatest.data[3].label),1),s("dd",st,[n(o(O),{icon:r.healthLatest.data[3].status==="Running"?"fluent-color:checkmark-circle-32":"fluent-color:dismiss-circle-32"},null,8,["icon"])])]),s("div",rt,[s("dt",it,C(r.healthLatest.data[4].label),1),s("dd",at,[n(o(O),{icon:r.healthLatest.data[4].status==="Reachable"?"fluent-color:checkmark-circle-32":"fluent-color:dismiss-circle-32"},null,8,["icon"])])]),s("div",lt,[s("dt",ct,C(r.healthLatest.data[6].label),1),s("dd",ut,[n(o(O),{icon:r.healthLatest.data[6].status==="Ok"?"fluent-color:checkmark-circle-32":"fluent-color:dismiss-circle-32"},null,8,["icon"])])]),s("div",dt,[s("dt",ft,C(r.healthLatest.data[7].label),1),s("dd",mt,C(r.healthLatest.data[7].status),1)])])])):M("",!0)]),_:1}),n(o(te),{class:"flex flex-row items-center px-6 py-3 border-t bg-muted/50"},{default:w(()=>[s("div",yt,[u[3]||(u[3]=E(" Laravel Version ")),s("time",vt,C(r.SystemUptime),1)])]),_:1})]),_:1})])}}};var ne={exports:{}};/*!
 * clipboard.js v2.0.11
 * https://clipboardjs.com/
 *
 * Licensed MIT © Zeno Rocha
 */(function(r,V){(function(f,L){r.exports=L()})(ge,function(){return function(){var l={686:function(u,c,e){e.d(c,{default:function(){return ye}});var d=e(279),g=e.n(d),v=e(370),t=e.n(v),h=e(817),x=e.n(h);function S(p){try{return document.execCommand(p)}catch{return!1}}var k=function(a){var i=x()(a);return S("cut"),i},$=k;function I(p){var a=document.documentElement.getAttribute("dir")==="rtl",i=document.createElement("textarea");i.style.fontSize="12pt",i.style.border="0",i.style.padding="0",i.style.margin="0",i.style.position="absolute",i.style[a?"right":"left"]="-9999px";var m=window.pageYOffset||document.documentElement.scrollTop;return i.style.top="".concat(m,"px"),i.setAttribute("readonly",""),i.value=p,i}var K=function(a,i){var m=I(a);i.container.appendChild(m);var y=x()(m);return S("copy"),m.remove(),y},oe=function(a){var i=arguments.length>1&&arguments[1]!==void 0?arguments[1]:{container:document.body},m="";return typeof a=="string"?m=K(a,i):a instanceof HTMLInputElement&&!["text","search","url","tel","password"].includes(a==null?void 0:a.type)?m=K(a.value,i):(m=x()(a),S("copy")),m},Y=oe;function Q(p){"@babel/helpers - typeof";return typeof Symbol=="function"&&typeof Symbol.iterator=="symbol"?Q=function(i){return typeof i}:Q=function(i){return i&&typeof Symbol=="function"&&i.constructor===Symbol&&i!==Symbol.prototype?"symbol":typeof i},Q(p)}var se=function(){var a=arguments.length>0&&arguments[0]!==void 0?arguments[0]:{},i=a.action,m=i===void 0?"copy":i,y=a.container,b=a.target,j=a.text;if(m!=="copy"&&m!=="cut")throw new Error('Invalid "action" value, use either "copy" or "cut"');if(b!==void 0)if(b&&Q(b)==="object"&&b.nodeType===1){if(m==="copy"&&b.hasAttribute("disabled"))throw new Error('Invalid "target" attribute. Please use "readonly" instead of "disabled" attribute');if(m==="cut"&&(b.hasAttribute("readonly")||b.hasAttribute("disabled")))throw new Error(`Invalid "target" attribute. You can't cut text from elements with "readonly" or "disabled" attributes`)}else throw new Error('Invalid "target" value, use a valid Element');if(j)return Y(j,{container:y});if(b)return m==="cut"?$(b):Y(b,{container:y})},re=se;function B(p){"@babel/helpers - typeof";return typeof Symbol=="function"&&typeof Symbol.iterator=="symbol"?B=function(i){return typeof i}:B=function(i){return i&&typeof Symbol=="function"&&i.constructor===Symbol&&i!==Symbol.prototype?"symbol":typeof i},B(p)}function ie(p,a){if(!(p instanceof a))throw new TypeError("Cannot call a class as a function")}function W(p,a){for(var i=0;i<a.length;i++){var m=a[i];m.enumerable=m.enumerable||!1,m.configurable=!0,"value"in m&&(m.writable=!0),Object.defineProperty(p,m.key,m)}}function ae(p,a,i){return a&&W(p.prototype,a),i&&W(p,i),p}function le(p,a){if(typeof a!="function"&&a!==null)throw new TypeError("Super expression must either be null or a function");p.prototype=Object.create(a&&a.prototype,{constructor:{value:p,writable:!0,configurable:!0}}),a&&G(p,a)}function G(p,a){return G=Object.setPrototypeOf||function(m,y){return m.__proto__=y,m},G(p,a)}function ce(p){var a=fe();return function(){var m=U(p),y;if(a){var b=U(this).constructor;y=Reflect.construct(m,arguments,b)}else y=m.apply(this,arguments);return ue(this,y)}}function ue(p,a){return a&&(B(a)==="object"||typeof a=="function")?a:de(p)}function de(p){if(p===void 0)throw new ReferenceError("this hasn't been initialised - super() hasn't been called");return p}function fe(){if(typeof Reflect>"u"||!Reflect.construct||Reflect.construct.sham)return!1;if(typeof Proxy=="function")return!0;try{return Date.prototype.toString.call(Reflect.construct(Date,[],function(){})),!0}catch{return!1}}function U(p){return U=Object.setPrototypeOf?Object.getPrototypeOf:function(i){return i.__proto__||Object.getPrototypeOf(i)},U(p)}function J(p,a){var i="data-clipboard-".concat(p);if(a.hasAttribute(i))return a.getAttribute(i)}var me=function(p){le(i,p);var a=ce(i);function i(m,y){var b;return ie(this,i),b=a.call(this),b.resolveOptions(y),b.listenClick(m),b}return ae(i,[{key:"resolveOptions",value:function(){var y=arguments.length>0&&arguments[0]!==void 0?arguments[0]:{};this.action=typeof y.action=="function"?y.action:this.defaultAction,this.target=typeof y.target=="function"?y.target:this.defaultTarget,this.text=typeof y.text=="function"?y.text:this.defaultText,this.container=B(y.container)==="object"?y.container:document.body}},{key:"listenClick",value:function(y){var b=this;this.listener=t()(y,"click",function(j){return b.onClick(j)})}},{key:"onClick",value:function(y){var b=y.delegateTarget||y.currentTarget,j=this.action(b)||"copy",q=re({action:j,container:this.container,target:this.target(b),text:this.text(b)});this.emit(q?"success":"error",{action:j,text:q,trigger:b,clearSelection:function(){b&&b.focus(),window.getSelection().removeAllRanges()}})}},{key:"defaultAction",value:function(y){return J("action",y)}},{key:"defaultTarget",value:function(y){var b=J("target",y);if(b)return document.querySelector(b)}},{key:"defaultText",value:function(y){return J("text",y)}},{key:"destroy",value:function(){this.listener.destroy()}}],[{key:"copy",value:function(y){var b=arguments.length>1&&arguments[1]!==void 0?arguments[1]:{container:document.body};return Y(y,b)}},{key:"cut",value:function(y){return $(y)}},{key:"isSupported",value:function(){var y=arguments.length>0&&arguments[0]!==void 0?arguments[0]:["copy","cut"],b=typeof y=="string"?[y]:y,j=!!document.queryCommandSupported;return b.forEach(function(q){j=j&&!!document.queryCommandSupported(q)}),j}}]),i}(g()),ye=me},828:function(u){var c=9;if(typeof Element<"u"&&!Element.prototype.matches){var e=Element.prototype;e.matches=e.matchesSelector||e.mozMatchesSelector||e.msMatchesSelector||e.oMatchesSelector||e.webkitMatchesSelector}function d(g,v){for(;g&&g.nodeType!==c;){if(typeof g.matches=="function"&&g.matches(v))return g;g=g.parentNode}}u.exports=d},438:function(u,c,e){var d=e(828);function g(h,x,S,k,$){var I=t.apply(this,arguments);return h.addEventListener(S,I,$),{destroy:function(){h.removeEventListener(S,I,$)}}}function v(h,x,S,k,$){return typeof h.addEventListener=="function"?g.apply(null,arguments):typeof S=="function"?g.bind(null,document).apply(null,arguments):(typeof h=="string"&&(h=document.querySelectorAll(h)),Array.prototype.map.call(h,function(I){return g(I,x,S,k,$)}))}function t(h,x,S,k){return function($){$.delegateTarget=d($.target,x),$.delegateTarget&&k.call(h,$)}}u.exports=v},879:function(u,c){c.node=function(e){return e!==void 0&&e instanceof HTMLElement&&e.nodeType===1},c.nodeList=function(e){var d=Object.prototype.toString.call(e);return e!==void 0&&(d==="[object NodeList]"||d==="[object HTMLCollection]")&&"length"in e&&(e.length===0||c.node(e[0]))},c.string=function(e){return typeof e=="string"||e instanceof String},c.fn=function(e){var d=Object.prototype.toString.call(e);return d==="[object Function]"}},370:function(u,c,e){var d=e(879),g=e(438);function v(S,k,$){if(!S&&!k&&!$)throw new Error("Missing required arguments");if(!d.string(k))throw new TypeError("Second argument must be a String");if(!d.fn($))throw new TypeError("Third argument must be a Function");if(d.node(S))return t(S,k,$);if(d.nodeList(S))return h(S,k,$);if(d.string(S))return x(S,k,$);throw new TypeError("First argument must be a String, HTMLElement, HTMLCollection, or NodeList")}function t(S,k,$){return S.addEventListener(k,$),{destroy:function(){S.removeEventListener(k,$)}}}function h(S,k,$){return Array.prototype.forEach.call(S,function(I){I.addEventListener(k,$)}),{destroy:function(){Array.prototype.forEach.call(S,function(I){I.removeEventListener(k,$)})}}}function x(S,k,$){return g(document.body,S,k,$)}u.exports=v},817:function(u){function c(e){var d;if(e.nodeName==="SELECT")e.focus(),d=e.value;else if(e.nodeName==="INPUT"||e.nodeName==="TEXTAREA"){var g=e.hasAttribute("readonly");g||e.setAttribute("readonly",""),e.select(),e.setSelectionRange(0,e.value.length),g||e.removeAttribute("readonly"),d=e.value}else{e.hasAttribute("contenteditable")&&e.focus();var v=window.getSelection(),t=document.createRange();t.selectNodeContents(e),v.removeAllRanges(),v.addRange(t),d=v.toString()}return d}u.exports=c},279:function(u){function c(){}c.prototype={on:function(e,d,g){var v=this.e||(this.e={});return(v[e]||(v[e]=[])).push({fn:d,ctx:g}),this},once:function(e,d,g){var v=this;function t(){v.off(e,t),d.apply(g,arguments)}return t._=d,this.on(e,t,g)},emit:function(e){var d=[].slice.call(arguments,1),g=((this.e||(this.e={}))[e]||[]).slice(),v=0,t=g.length;for(v;v<t;v++)g[v].fn.apply(g[v].ctx,d);return this},off:function(e,d){var g=this.e||(this.e={}),v=g[e],t=[];if(v&&d)for(var h=0,x=v.length;h<x;h++)v[h].fn!==d&&v[h].fn._!==d&&t.push(v[h]);return t.length?g[e]=t:delete g[e],this}},u.exports=c,u.exports.TinyEmitter=c}},f={};function L(u){if(f[u])return f[u].exports;var c=f[u]={exports:{}};return l[u](c,c.exports,L),c.exports}return function(){L.n=function(u){var c=u&&u.__esModule?function(){return u.default}:function(){return u};return L.d(c,{a:c}),c}}(),function(){L.d=function(u,c){for(var e in c)L.o(c,e)&&!L.o(u,e)&&Object.defineProperty(u,e,{enumerable:!0,get:c[e]})}}(),function(){L.o=function(u,c){return Object.prototype.hasOwnProperty.call(u,c)}}(),L(686)}().default})})(ne);var gt=ne.exports;const ht=he(gt),xt=r=>({toClipboard(V,l){return new Promise((f,L)=>{const u=document.createElement("button"),c=new ht(u,{text:()=>V,action:()=>"copy",container:l!==void 0?l:document.body});c.on("success",e=>{c.destroy(),f(e)}),c.on("error",e=>{c.destroy(),L(e)}),document.body.appendChild(u),u.click(),document.body.removeChild(u)})}}),bt={class:"grid gap-0.5"},wt={class:"flex items-center gap-1 ml-auto"},_t={key:0,class:"flex items-start space-x-4"},St={class:"space-y-2"},$t={key:1,class:"grid gap-3"},Lt={class:"grid gap-3"},Ct={class:"flex items-center justify-between"},kt={class:"flex items-center gap-2"},Et={class:"flex items-center justify-between"},Pt={class:"flex items-center gap-2"},Tt={class:"flex items-center justify-between"},Vt={class:"flex items-center gap-2"},Ot={class:"flex items-center justify-between"},Mt={class:"flex items-center gap-2"},jt={class:"flex items-center justify-between"},At={class:"flex items-center gap-2"},It={class:"flex items-center justify-between"},Nt={class:"flex items-center gap-2"},Rt={class:"flex items-center justify-between"},Ht={class:"flex items-center gap-2"},Dt={class:"flex items-center gap-2 text-xs text-muted-foreground"},Ft={__name:"SysinfoCards",props:{sysinfo:{type:Object,required:!0},isLoadingSysinfo:{type:Boolean,required:!0}},emits:["refresh"],setup(r,{emit:V}){const l=A({}),f=A({}),{toClipboard:L}=xt(),u=V,c=async(v,t)=>{try{await L(JSON.stringify(t)),f.value[v]=!0,setTimeout(()=>{f.value[v]=!1},1500)}catch(h){console.error("Failed to copy:",h)}},e=v=>{l.value[v]=!0},d=v=>{l.value[v]=!1};function g(){u("refresh")}return(v,t)=>{const h=z("Button");return P(),T("div",null,[n(o(F),{class:"overflow-hidden"},{default:w(()=>[n(o(R),{class:"flex flex-row items-start bg-muted/50"},{default:w(()=>[s("div",bt,[n(o(H),{class:"flex items-center gap-2 text-lg group"},{default:w(()=>t[22]||(t[22]=[E("System Details")])),_:1}),n(o(ee),null,{default:w(()=>t[23]||(t[23]=[E("Useful system information for support")])),_:1})]),s("div",wt,[n(h,{onClick:t[0]||(t[0]=x=>g()),size:"sm",variant:"none",class:"h-8 gap-1"},{default:w(()=>[n(o(O),{icon:"flat-color-icons:refresh",class:"hover:animate-pulse"})]),_:1})])]),_:1}),n(o(D),{class:"text-sm"},{default:w(()=>[r.isLoadingSysinfo?(P(),T("div",_t,[n(o(_),{class:"w-12 h-12 rounded-full"}),s("div",St,[n(o(_),{class:"h-4 w-[250px]"}),n(o(_),{class:"h-4 w-[250px]"}),n(o(_),{class:"h-4 w-[250px]"}),n(o(_),{class:"h-4 w-[250px]"}),n(o(_),{class:"h-4 w-[250px]"}),n(o(_),{class:"h-4 w-[250px]"}),n(o(_),{class:"h-4 w-[250px]"})])])):M("",!0),r.isLoadingSysinfo?M("",!0):(P(),T("div",$t,[s("dl",Lt,[s("div",Ct,[t[24]||(t[24]=s("dt",{class:"text-muted-foreground"},"OSVersion",-1)),s("dd",kt,[E(C(r.sysinfo.OSVersion)+" ",1),n(o(O),{icon:f.value.OSVersion?"material-symbols:check-circle-outline":l.value.OSVersion?"material-symbols:content-copy":"material-symbols:content-copy-outline",class:N([f.value.OSVersion?"text-green-500":"text-gray-500","cursor-pointer hover:text-gray-700"]),onClick:t[1]||(t[1]=x=>c("OSVersion",r.sysinfo.OSVersion)),onMouseover:t[2]||(t[2]=x=>e("OSVersion")),onMouseleave:t[3]||(t[3]=x=>d("OSVersion"))},null,8,["icon","class"])])]),s("div",Et,[t[25]||(t[25]=s("dt",{class:"text-muted-foreground"},"localIp",-1)),s("dd",Pt,[E(C(r.sysinfo.localIp)+" ",1),n(o(O),{icon:f.value.localIp?"material-symbols:check-circle-outline":l.value.localIp?"material-symbols:content-copy":"material-symbols:content-copy-outline",class:N([f.value.localIp?"text-green-500":"text-gray-500","cursor-pointer hover:text-gray-700"]),onClick:t[4]||(t[4]=x=>c("localIp",r.sysinfo.localIp)),onMouseover:t[5]||(t[5]=x=>e("localIp")),onMouseleave:t[6]||(t[6]=x=>d("localIp"))},null,8,["icon","class"])])]),s("div",Tt,[t[26]||(t[26]=s("dt",{class:"text-muted-foreground"},"PublicIP",-1)),s("dd",Vt,[E(C(r.sysinfo.PublicIP)+" ",1),n(o(O),{icon:f.value.PublicIP?"material-symbols:check-circle-outline":l.value.PublicIP?"material-symbols:content-copy":"material-symbols:content-copy-outline",class:N([f.value.PublicIP?"text-green-500":"text-gray-500","cursor-pointer hover:text-gray-700"]),onClick:t[7]||(t[7]=x=>c("PublicIP",r.sysinfo.PublicIP)),onMouseover:t[8]||(t[8]=x=>e("PublicIP")),onMouseleave:t[9]||(t[9]=x=>d("PublicIP"))},null,8,["icon","class"])])]),s("div",Ot,[t[27]||(t[27]=s("dt",{class:"text-muted-foreground"},"ServerName",-1)),s("dd",Mt,[E(C(r.sysinfo.ServerName)+" ",1),n(o(O),{icon:f.value.ServerName?"material-symbols:check-circle-outline":l.value.ServerName?"material-symbols:content-copy":"material-symbols:content-copy-outline",class:N([f.value.ServerName?"text-green-500":"text-gray-500","cursor-pointer hover:text-gray-700"]),onClick:t[10]||(t[10]=x=>c("ServerName",r.sysinfo.ServerName)),onMouseover:t[11]||(t[11]=x=>e("ServerName")),onMouseleave:t[12]||(t[12]=x=>d("ServerName"))},null,8,["icon","class"])])]),s("div",jt,[t[28]||(t[28]=s("dt",{class:"text-muted-foreground"},"PHPVersion",-1)),s("dd",At,[E(C(r.sysinfo.PHPVersion)+" ",1),n(o(O),{icon:f.value.PHPVersion?"material-symbols:check-circle-outline":l.value.PHPVersion?"material-symbols:content-copy":"material-symbols:content-copy-outline",class:N([f.value.PHPVersion?"text-green-500":"text-gray-500","cursor-pointer hover:text-gray-700"]),onClick:t[13]||(t[13]=x=>c("PHPVersion",r.sysinfo.PHPVersion)),onMouseover:t[14]||(t[14]=x=>e("PHPVersion")),onMouseleave:t[15]||(t[15]=x=>d("PHPVersion"))},null,8,["icon","class"])])]),s("div",It,[t[29]||(t[29]=s("dt",{class:"text-muted-foreground"},"RedisVersion",-1)),s("dd",Nt,[E(C(r.sysinfo.RedisVersion)+" ",1),n(o(O),{icon:f.value.RedisVersion?"material-symbols:check-circle-outline":l.value.RedisVersion?"material-symbols:content-copy":"material-symbols:content-copy-outline",class:N([f.value.RedisVersion?"text-green-500":"text-gray-500","cursor-pointer hover:text-gray-700"]),onClick:t[16]||(t[16]=x=>c("RedisVersion",r.sysinfo.RedisVersion)),onMouseover:t[17]||(t[17]=x=>e("RedisVersion")),onMouseleave:t[18]||(t[18]=x=>d("RedisVersion"))},null,8,["icon","class"])])]),s("div",Rt,[t[30]||(t[30]=s("dt",{class:"text-muted-foreground"},"MySQLVersion",-1)),s("dd",Ht,[E(C(r.sysinfo.MySQLVersion)+" ",1),n(o(O),{icon:f.value.MySQLVersion?"material-symbols:check-circle-outline":l.value.MySQLVersion?"material-symbols:content-copy":"material-symbols:content-copy-outline",class:N([f.value.MySQLVersion?"text-green-500":"text-gray-500","cursor-pointer hover:text-gray-700"]),onClick:t[19]||(t[19]=x=>c("MySQLVersion",r.sysinfo.MySQLVersion)),onMouseover:t[20]||(t[20]=x=>e("MySQLVersion")),onMouseleave:t[21]||(t[21]=x=>d("MySQLVersion"))},null,8,["icon","class"])])])])]))]),_:1}),n(o(te),{class:"flex flex-row items-center px-6 py-3 border-t bg-muted/50"},{default:w(()=>[s("div",Dt,[n(o(O),{icon:"logos:laravel"}),E(" Laravel Version: "+C(r.sysinfo.LaravelVersion),1)])]),_:1})]),_:1})])}}},Bt={class:"border-t border-b topRow"},Qt={class:"sticky top-0 flex items-center h-12 gap-4 px-8"},Ut={class:"flex-col hidden font-medium text-md md:flex md:flex-row md:items-center md:text-sm"},qt={href:"#",class:"flex items-center gap-2 px-4 py-2 font-semibold rounded-md text-md hover:bg-rcgray-800"},zt={href:"#",class:"flex items-center gap-2 px-4 py-2 font-semibold rounded-md text-md hover:bg-rcgray-800"},Yt={href:"#",class:"flex items-center gap-2 px-4 py-2 font-semibold rounded-md text-md hover:bg-rcgray-800"},Gt={href:"#",class:"flex items-center gap-2 px-4 py-2 font-semibold rounded-md text-md hover:bg-rcgray-800"},Jt={__name:"DashboardActions",props:{},setup(r){return(V,l)=>{const f=z("Icon");return P(),T("div",Bt,[s("header",Qt,[s("nav",Ut,[s("a",qt,[n(f,{icon:"fluent-color:add-circle-28",class:""}),l[0]||(l[0]=s("span",null,"Create New Device",-1))]),s("a",zt,[n(f,{icon:"fluent-color:apps-24",class:""}),l[1]||(l[1]=E(" View Devices "))]),s("a",Yt,[n(f,{icon:"fluent-color:search-visual-16",class:""}),l[2]||(l[2]=E(" Search Configs "))]),s("a",Gt,[n(f,{icon:"flat-color-icons:delete-database",class:""}),l[3]||(l[3]=E(" Purge Failed Configs "))])])])])}}},Xt={class:"flex flex-col flex-1 gap-2 dark:bg-rcgray-900"},Kt={class:"flex flex-row gap-8 px-8"},Wt={class:"flex-1"},Zt={class:"flex-1"},sn={__name:"Main",props:{},setup(r){const{fetchSysinfo:V,fetchConfiginfo:l,fetchHealth:f,sysinfo:L,configinfo:u,healthLatest:c,isLoadingSysinfo:e,isLoadingConfiginfo:d,isLoadingHealth:g,toastSuccess:v,toastError:t}=we();return xe(()=>{V(),l(),f()}),(h,x)=>(P(),T("main",Xt,[n(Jt),n(He,{configinfo:o(u),isLoadingConfiginfo:o(d)},null,8,["configinfo","isLoadingConfiginfo"]),s("div",Kt,[s("div",Wt,[n(pt,{healthLatest:o(c),isLoadingHealth:o(g),SystemUptime:o(L).SystemUptime},null,8,["healthLatest","isLoadingHealth","SystemUptime"])]),s("div",Zt,[n(Ft,{onRefresh:x[0]||(x[0]=S=>o(V)()),sysinfo:o(L),isLoadingSysinfo:o(e)},null,8,["sysinfo","isLoadingSysinfo"])])])]))}};export{sn as default};
