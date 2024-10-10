import{d as be,o as P,c as T,r as G,n as N,u as t,a as Z,b as M,e as Y,f as F,g as e,w as x,_ as q,h as R,i as E,j as D,k as s,t as O,l as I,m as H,p as oe,I as V,q as _e,s as we,x as ne,v as J,y as se,z as re,A as $e,B as Se,C as Ce,D as Le,E as ke,F as Pe,G as Ee,H as Oe}from"./app-D_qib-O2.js";import{u as Te}from"./useToaster-amSPtvaL.js";/* empty css               */const ie=be({__name:"CardFooter",props:{class:{}},setup(r){const S=r;return(u,m)=>(P(),T("div",{class:N(t(Z)("flex items-center p-6 pt-0",S.class))},[G(u.$slots,"default")],2))}});function Ve(){const r=M([]),S=M([]),u=M([]),m=M([]),p=M(!1),i=M(!1),a=M(!1),{toastSuccess:o,toastError:d}=Te();async function b(f=!1,_={}){p.value=!0;try{const L=await Y.get("/api/dashboard/sysinfo?clearcache="+f);r.value=L.data}catch(L){console.error("Error fetching sysinfo:",L),d("Error","Failed to fetch sysinfo.")}finally{p.value=!1}}async function g(f={}){i.value=!0;try{const _=await Y.get("/api/dashboard/configinfo");S.value=_.data}catch(_){console.error("Error fetching configinfo:",_),d("Error","Failed to fetch configinfo.")}finally{i.value=!1}}async function n(f={}){a.value=!0;try{const _=await Y.get("/api/dashboard/health-latest");u.value=_.data}catch(_){console.error("Error fetching healthLatest:",_),d("Error","Failed to fetch healthLatest.")}finally{a.value=!1}}async function C(){try{const f=await Y.get("/api/license-info");m.value=f.data}catch(f){console.error("Error fetching license info:",f),d("Error","Failed to fetch license info.")}}return{fetchSysinfo:b,fetchConfiginfo:g,fetchHealth:n,fetchLicenseInfo:C,sysinfo:r,configinfo:S,healthLatest:u,licenseInfo:m,isLoadingSysinfo:p,isLoadingConfiginfo:i,isLoadingHealth:a,toastSuccess:o,toastError:d}}const $={__name:"Skeleton",props:{class:{type:null,required:!1}},setup(r){const S=r;return(u,m)=>(P(),T("div",{class:N(t(Z)("animate-pulse rounded-md bg-primary/10",S.class))},null,2))}},Ie={class:"grid gap-2 p-8 sm:gap-4 md:grid-cols-2 xl:gap-8 lg:grid-cols-4"},Me={key:0},je={class:"text-2xl font-bold"},Ae={key:1,class:"flex items-center space-x-4"},Ne={class:"space-y-2"},qe={key:0},Re={class:"text-2xl font-bold"},De={key:1,class:"flex items-center space-x-4"},He={class:"space-y-2"},Be={key:0},Fe={class:"text-2xl font-bold"},ze={key:1,class:"flex items-center space-x-4"},Ue={class:"space-y-2"},Qe={key:0},Ye={class:"text-2xl font-bold"},Ge={key:1,class:"flex items-center space-x-4"},Je={class:"space-y-2"},Ke={__name:"ConfigInfoCards",props:{configinfo:{type:Object,required:!0},isLoadingConfiginfo:{type:Boolean,required:!0}},setup(r){return M(!1),(S,u)=>{const m=F("Icon");return P(),T("div",Ie,[e(t(H),{class:"border shadow rounded-xl bg-card text-card-foreground"},{default:x(()=>[e(t(q),{class:"flex flex-row items-center justify-between pb-2 space-y-0"},{default:x(()=>[e(t(R),{class:"text-sm font-medium"},{default:x(()=>u[0]||(u[0]=[E("Device count")])),_:1}),e(m,{icon:"devicon:networkx",class:"w-4 h-4 text-muted-foreground"})]),_:1}),e(t(D),null,{default:x(()=>[r.configinfo.data?(P(),T("div",Me,[s("div",je,O(r.configinfo.data.deviceCount),1),u[1]||(u[1]=s("p",{class:"text-xs text-muted-foreground"},"Total count of devices",-1))])):I("",!0),r.isLoadingConfiginfo?(P(),T("div",Ae,[e(t($),{class:"w-12 h-12 rounded-full"}),s("div",Ne,[e(t($),{class:"h-4 w-[250px]"}),e(t($),{class:"h-4 w-[200px]"})])])):I("",!0)]),_:1})]),_:1}),e(t(H),null,{default:x(()=>[e(t(q),{class:"flex flex-row items-center justify-between pb-2 space-y-0"},{default:x(()=>[e(t(R),{class:"text-sm font-medium"},{default:x(()=>u[2]||(u[2]=[E("Devices down")])),_:1}),e(m,{icon:"fluent-color:cloud-dismiss-24",class:"w-4 h-4 text-muted-foreground"})]),_:1}),e(t(D),null,{default:x(()=>[r.configinfo.data?(P(),T("div",qe,[s("div",Re,O(r.configinfo.data.deviceDownCount),1),u[3]||(u[3]=s("p",{class:"text-xs text-muted-foreground"},"Total number of devices unreachable",-1))])):I("",!0),r.isLoadingConfiginfo?(P(),T("div",De,[e(t($),{class:"w-12 h-12 rounded-full"}),s("div",He,[e(t($),{class:"h-4 w-[250px]"}),e(t($),{class:"h-4 w-[200px]"})])])):I("",!0)]),_:1})]),_:1}),e(t(H),null,{default:x(()=>[e(t(q),{class:"flex flex-row items-center justify-between pb-2 space-y-0"},{default:x(()=>[e(t(R),{class:"text-sm font-medium"},{default:x(()=>u[4]||(u[4]=[E("Total configs")])),_:1}),e(m,{icon:"fluent-color:checkbox-24",class:"w-4 h-4 text-muted-foreground"})]),_:1}),e(t(D),null,{default:x(()=>[r.configinfo.data?(P(),T("div",Be,[s("div",Fe,O(r.configinfo.data.configTotalCount),1),u[5]||(u[5]=s("p",{class:"text-xs text-muted-foreground"},"Total number of configs downloaded",-1))])):I("",!0),r.isLoadingConfiginfo?(P(),T("div",ze,[e(t($),{class:"w-12 h-12 rounded-full"}),s("div",Ue,[e(t($),{class:"h-4 w-[250px]"}),e(t($),{class:"h-4 w-[200px]"})])])):I("",!0)]),_:1})]),_:1}),e(t(H),null,{default:x(()=>[e(t(q),{class:"flex flex-row items-center justify-between pb-2 space-y-0"},{default:x(()=>[e(t(R),{class:"text-sm font-medium"},{default:x(()=>u[6]||(u[6]=[E("Failed configs")])),_:1}),e(m,{icon:"fluent-color:calendar-cancel-24",class:"w-4 h-4 text-muted-foreground"})]),_:1}),e(t(D),null,{default:x(()=>[r.configinfo.data?(P(),T("div",Qe,[s("div",Ye,O(r.configinfo.data.configDownCount),1),u[7]||(u[7]=s("p",{class:"text-xs text-muted-foreground"},"Number of configuration download failures",-1))])):I("",!0),r.isLoadingConfiginfo?(P(),T("div",Ge,[e(t($),{class:"w-12 h-12 rounded-full"}),s("div",Je,[e(t($),{class:"h-4 w-[250px]"}),e(t($),{class:"h-4 w-[200px]"})])])):I("",!0)]),_:1})]),_:1})])}}},We={class:"grid gap-0.5"},Xe={class:"flex items-center gap-1 ml-auto"},Ze={key:0,class:"flex items-start w-full space-x-4"},et={class:"space-y-2"},tt={key:1},ot={key:2,class:"grid gap-3"},nt={class:"grid gap-3"},st={class:"flex items-center justify-between"},rt={class:"text-muted-foreground"},it={class:"flex items-center gap-2"},at={class:"flex items-center justify-between"},lt={class:"text-muted-foreground"},ct={class:"flex items-center gap-2"},ut={class:"flex items-center justify-between"},dt={class:"text-muted-foreground"},ft={class:"flex items-center gap-2"},mt={class:"flex items-center justify-between"},pt={class:"text-muted-foreground"},yt={class:"flex items-center gap-2"},gt={class:"flex items-center justify-between"},vt={class:"text-muted-foreground"},ht={class:"flex items-center gap-2"},xt={class:"flex items-center justify-between"},bt={class:"text-muted-foreground"},_t={class:"flex items-center gap-2"},wt={class:"flex items-center justify-between"},$t={class:"text-muted-foreground"},St={class:"flex items-center gap-2"},Ct={class:"text-xs text-muted-foreground"},Lt={dateTime:"2023-11-23"},kt={__name:"HealthLatestCards",props:{healthLatest:{type:Object,required:!0},isLoadingHealth:{type:Boolean,required:!0},SystemUptime:{type:String}},emits:["refresh"],setup(r,{emit:S}){M({}),M({});const u=S;function m(){u("refresh")}return(p,i)=>{const a=F("Button");return P(),T("div",null,[e(t(H),{class:"overflow-hidden"},{default:x(()=>[e(t(q),{class:"flex flex-row items-start bg-muted/50"},{default:x(()=>[s("div",We,[e(t(R),{class:"flex items-center gap-2 text-lg group"},{default:x(()=>i[1]||(i[1]=[E("Latest Health")])),_:1}),e(t(oe),null,{default:x(()=>i[2]||(i[2]=[E("Server health information")])),_:1})]),s("div",Xe,[e(a,{onClick:i[0]||(i[0]=o=>m()),size:"sm",variant:"outline",class:"gap-1 hover:bg-rcgray-800"},{default:x(()=>[e(t(V),{icon:"flat-color-icons:refresh",class:"hover:animate-pulse"})]),_:1})])]),_:1}),e(t(D),{class:"text-sm"},{default:x(()=>[r.isLoadingHealth?(P(),T("div",Ze,[e(t($),{class:"w-12 h-12 rounded-full"}),s("div",et,[e(t($),{class:"h-4 w-[250px]"}),e(t($),{class:"w-[400px] h-4"}),e(t($),{class:"w-[400px] h-4"}),e(t($),{class:"w-[400px] h-4"}),e(t($),{class:"w-[400px] h-4"}),e(t($),{class:"w-[400px] h-4"}),e(t($),{class:"w-[400px] h-4"}),e(t($),{class:"w-[400px] h-4"}),e(t($),{class:"w-[400px] h-4"})])])):I("",!0),r.healthLatest?I("",!0):(P(),T("div",tt,"asd")),r.healthLatest.data?(P(),T("div",ot,[s("dl",nt,[s("div",st,[s("dt",rt,O(r.healthLatest.data[0].label),1),s("dd",it,[e(t(V),{icon:r.healthLatest.data[0].status==="Ok"?"fluent-color:checkmark-circle-32":"fluent-color:dismiss-circle-32"},null,8,["icon"])])]),s("div",at,[s("dt",lt,O(r.healthLatest.data[1].label),1),s("dd",ct,[e(t(V),{icon:r.healthLatest.data[1].status==="Ok"?"fluent-color:checkmark-circle-32":"fluent-color:dismiss-circle-32"},null,8,["icon"])])]),s("div",ut,[s("dt",dt,O(r.healthLatest.data[3].label),1),s("dd",ft,[e(t(V),{icon:r.healthLatest.data[3].status==="Running"?"fluent-color:checkmark-circle-32":"fluent-color:dismiss-circle-32"},null,8,["icon"])])]),s("div",mt,[s("dt",pt,O(r.healthLatest.data[4].label),1),s("dd",yt,[e(t(V),{icon:r.healthLatest.data[4].status==="Reachable"?"fluent-color:checkmark-circle-32":"fluent-color:dismiss-circle-32"},null,8,["icon"])])]),s("div",gt,[s("dt",vt,O(r.healthLatest.data[6].label),1),s("dd",ht,[e(t(V),{icon:r.healthLatest.data[6].status==="Ok"?"fluent-color:checkmark-circle-32":"fluent-color:dismiss-circle-32"},null,8,["icon"])])]),s("div",xt,[s("dt",bt,O(r.healthLatest.data[7].label),1),s("dd",_t,O(r.healthLatest.data[7].status),1)]),s("div",wt,[s("dt",$t,O(r.healthLatest.data[2].label),1),s("dd",St,O(r.healthLatest.data[2].status),1)])])])):I("",!0)]),_:1}),e(t(ie),{class:"flex flex-row items-center px-6 py-3 border-t bg-muted/50"},{default:x(()=>[s("div",Ct,[i[3]||(i[3]=E(" System Uptime: ")),s("time",Lt,O(r.SystemUptime),1)])]),_:1})]),_:1})])}}};var ae={exports:{}};/*!
 * clipboard.js v2.0.11
 * https://clipboardjs.com/
 *
 * Licensed MIT © Zeno Rocha
 */(function(r,S){(function(m,p){r.exports=p()})(_e,function(){return function(){var u={686:function(i,a,o){o.d(a,{default:function(){return xe}});var d=o(279),b=o.n(d),g=o(370),n=o.n(g),C=o(817),f=o.n(C);function _(h){try{return document.execCommand(h)}catch{return!1}}var L=function(c){var l=f()(c);return _("cut"),l},k=L;function A(h){var c=document.documentElement.getAttribute("dir")==="rtl",l=document.createElement("textarea");l.style.fontSize="12pt",l.style.border="0",l.style.padding="0",l.style.margin="0",l.style.position="absolute",l.style[c?"right":"left"]="-9999px";var y=window.pageYOffset||document.documentElement.scrollTop;return l.style.top="".concat(y,"px"),l.setAttribute("readonly",""),l.value=h,l}var ee=function(c,l){var y=A(c);l.container.appendChild(y);var v=f()(y);return _("copy"),y.remove(),v},le=function(c){var l=arguments.length>1&&arguments[1]!==void 0?arguments[1]:{container:document.body},y="";return typeof c=="string"?y=ee(c,l):c instanceof HTMLInputElement&&!["text","search","url","tel","password"].includes(c==null?void 0:c.type)?y=ee(c.value,l):(y=f()(c),_("copy")),y},K=le;function z(h){"@babel/helpers - typeof";return typeof Symbol=="function"&&typeof Symbol.iterator=="symbol"?z=function(l){return typeof l}:z=function(l){return l&&typeof Symbol=="function"&&l.constructor===Symbol&&l!==Symbol.prototype?"symbol":typeof l},z(h)}var ce=function(){var c=arguments.length>0&&arguments[0]!==void 0?arguments[0]:{},l=c.action,y=l===void 0?"copy":l,v=c.container,w=c.target,j=c.text;if(y!=="copy"&&y!=="cut")throw new Error('Invalid "action" value, use either "copy" or "cut"');if(w!==void 0)if(w&&z(w)==="object"&&w.nodeType===1){if(y==="copy"&&w.hasAttribute("disabled"))throw new Error('Invalid "target" attribute. Please use "readonly" instead of "disabled" attribute');if(y==="cut"&&(w.hasAttribute("readonly")||w.hasAttribute("disabled")))throw new Error(`Invalid "target" attribute. You can't cut text from elements with "readonly" or "disabled" attributes`)}else throw new Error('Invalid "target" value, use a valid Element');if(j)return K(j,{container:v});if(w)return y==="cut"?k(w):K(w,{container:v})},ue=ce;function B(h){"@babel/helpers - typeof";return typeof Symbol=="function"&&typeof Symbol.iterator=="symbol"?B=function(l){return typeof l}:B=function(l){return l&&typeof Symbol=="function"&&l.constructor===Symbol&&l!==Symbol.prototype?"symbol":typeof l},B(h)}function de(h,c){if(!(h instanceof c))throw new TypeError("Cannot call a class as a function")}function te(h,c){for(var l=0;l<c.length;l++){var y=c[l];y.enumerable=y.enumerable||!1,y.configurable=!0,"value"in y&&(y.writable=!0),Object.defineProperty(h,y.key,y)}}function fe(h,c,l){return c&&te(h.prototype,c),l&&te(h,l),h}function me(h,c){if(typeof c!="function"&&c!==null)throw new TypeError("Super expression must either be null or a function");h.prototype=Object.create(c&&c.prototype,{constructor:{value:h,writable:!0,configurable:!0}}),c&&W(h,c)}function W(h,c){return W=Object.setPrototypeOf||function(y,v){return y.__proto__=v,y},W(h,c)}function pe(h){var c=ve();return function(){var y=U(h),v;if(c){var w=U(this).constructor;v=Reflect.construct(y,arguments,w)}else v=y.apply(this,arguments);return ye(this,v)}}function ye(h,c){return c&&(B(c)==="object"||typeof c=="function")?c:ge(h)}function ge(h){if(h===void 0)throw new ReferenceError("this hasn't been initialised - super() hasn't been called");return h}function ve(){if(typeof Reflect>"u"||!Reflect.construct||Reflect.construct.sham)return!1;if(typeof Proxy=="function")return!0;try{return Date.prototype.toString.call(Reflect.construct(Date,[],function(){})),!0}catch{return!1}}function U(h){return U=Object.setPrototypeOf?Object.getPrototypeOf:function(l){return l.__proto__||Object.getPrototypeOf(l)},U(h)}function X(h,c){var l="data-clipboard-".concat(h);if(c.hasAttribute(l))return c.getAttribute(l)}var he=function(h){me(l,h);var c=pe(l);function l(y,v){var w;return de(this,l),w=c.call(this),w.resolveOptions(v),w.listenClick(y),w}return fe(l,[{key:"resolveOptions",value:function(){var v=arguments.length>0&&arguments[0]!==void 0?arguments[0]:{};this.action=typeof v.action=="function"?v.action:this.defaultAction,this.target=typeof v.target=="function"?v.target:this.defaultTarget,this.text=typeof v.text=="function"?v.text:this.defaultText,this.container=B(v.container)==="object"?v.container:document.body}},{key:"listenClick",value:function(v){var w=this;this.listener=n()(v,"click",function(j){return w.onClick(j)})}},{key:"onClick",value:function(v){var w=v.delegateTarget||v.currentTarget,j=this.action(w)||"copy",Q=ue({action:j,container:this.container,target:this.target(w),text:this.text(w)});this.emit(Q?"success":"error",{action:j,text:Q,trigger:w,clearSelection:function(){w&&w.focus(),window.getSelection().removeAllRanges()}})}},{key:"defaultAction",value:function(v){return X("action",v)}},{key:"defaultTarget",value:function(v){var w=X("target",v);if(w)return document.querySelector(w)}},{key:"defaultText",value:function(v){return X("text",v)}},{key:"destroy",value:function(){this.listener.destroy()}}],[{key:"copy",value:function(v){var w=arguments.length>1&&arguments[1]!==void 0?arguments[1]:{container:document.body};return K(v,w)}},{key:"cut",value:function(v){return k(v)}},{key:"isSupported",value:function(){var v=arguments.length>0&&arguments[0]!==void 0?arguments[0]:["copy","cut"],w=typeof v=="string"?[v]:v,j=!!document.queryCommandSupported;return w.forEach(function(Q){j=j&&!!document.queryCommandSupported(Q)}),j}}]),l}(b()),xe=he},828:function(i){var a=9;if(typeof Element<"u"&&!Element.prototype.matches){var o=Element.prototype;o.matches=o.matchesSelector||o.mozMatchesSelector||o.msMatchesSelector||o.oMatchesSelector||o.webkitMatchesSelector}function d(b,g){for(;b&&b.nodeType!==a;){if(typeof b.matches=="function"&&b.matches(g))return b;b=b.parentNode}}i.exports=d},438:function(i,a,o){var d=o(828);function b(C,f,_,L,k){var A=n.apply(this,arguments);return C.addEventListener(_,A,k),{destroy:function(){C.removeEventListener(_,A,k)}}}function g(C,f,_,L,k){return typeof C.addEventListener=="function"?b.apply(null,arguments):typeof _=="function"?b.bind(null,document).apply(null,arguments):(typeof C=="string"&&(C=document.querySelectorAll(C)),Array.prototype.map.call(C,function(A){return b(A,f,_,L,k)}))}function n(C,f,_,L){return function(k){k.delegateTarget=d(k.target,f),k.delegateTarget&&L.call(C,k)}}i.exports=g},879:function(i,a){a.node=function(o){return o!==void 0&&o instanceof HTMLElement&&o.nodeType===1},a.nodeList=function(o){var d=Object.prototype.toString.call(o);return o!==void 0&&(d==="[object NodeList]"||d==="[object HTMLCollection]")&&"length"in o&&(o.length===0||a.node(o[0]))},a.string=function(o){return typeof o=="string"||o instanceof String},a.fn=function(o){var d=Object.prototype.toString.call(o);return d==="[object Function]"}},370:function(i,a,o){var d=o(879),b=o(438);function g(_,L,k){if(!_&&!L&&!k)throw new Error("Missing required arguments");if(!d.string(L))throw new TypeError("Second argument must be a String");if(!d.fn(k))throw new TypeError("Third argument must be a Function");if(d.node(_))return n(_,L,k);if(d.nodeList(_))return C(_,L,k);if(d.string(_))return f(_,L,k);throw new TypeError("First argument must be a String, HTMLElement, HTMLCollection, or NodeList")}function n(_,L,k){return _.addEventListener(L,k),{destroy:function(){_.removeEventListener(L,k)}}}function C(_,L,k){return Array.prototype.forEach.call(_,function(A){A.addEventListener(L,k)}),{destroy:function(){Array.prototype.forEach.call(_,function(A){A.removeEventListener(L,k)})}}}function f(_,L,k){return b(document.body,_,L,k)}i.exports=g},817:function(i){function a(o){var d;if(o.nodeName==="SELECT")o.focus(),d=o.value;else if(o.nodeName==="INPUT"||o.nodeName==="TEXTAREA"){var b=o.hasAttribute("readonly");b||o.setAttribute("readonly",""),o.select(),o.setSelectionRange(0,o.value.length),b||o.removeAttribute("readonly"),d=o.value}else{o.hasAttribute("contenteditable")&&o.focus();var g=window.getSelection(),n=document.createRange();n.selectNodeContents(o),g.removeAllRanges(),g.addRange(n),d=g.toString()}return d}i.exports=a},279:function(i){function a(){}a.prototype={on:function(o,d,b){var g=this.e||(this.e={});return(g[o]||(g[o]=[])).push({fn:d,ctx:b}),this},once:function(o,d,b){var g=this;function n(){g.off(o,n),d.apply(b,arguments)}return n._=d,this.on(o,n,b)},emit:function(o){var d=[].slice.call(arguments,1),b=((this.e||(this.e={}))[o]||[]).slice(),g=0,n=b.length;for(g;g<n;g++)b[g].fn.apply(b[g].ctx,d);return this},off:function(o,d){var b=this.e||(this.e={}),g=b[o],n=[];if(g&&d)for(var C=0,f=g.length;C<f;C++)g[C].fn!==d&&g[C].fn._!==d&&n.push(g[C]);return n.length?b[o]=n:delete b[o],this}},i.exports=a,i.exports.TinyEmitter=a}},m={};function p(i){if(m[i])return m[i].exports;var a=m[i]={exports:{}};return u[i](a,a.exports,p),a.exports}return function(){p.n=function(i){var a=i&&i.__esModule?function(){return i.default}:function(){return i};return p.d(a,{a}),a}}(),function(){p.d=function(i,a){for(var o in a)p.o(a,o)&&!p.o(i,o)&&Object.defineProperty(i,o,{enumerable:!0,get:a[o]})}}(),function(){p.o=function(i,a){return Object.prototype.hasOwnProperty.call(i,a)}}(),p(686)}().default})})(ae);var Pt=ae.exports;const Et=we(Pt),Ot=r=>({toClipboard(S,u){return new Promise((m,p)=>{const i=document.createElement("button"),a=new Et(i,{text:()=>S,action:()=>"copy",container:u!==void 0?u:document.body});a.on("success",o=>{a.destroy(),m(o)}),a.on("error",o=>{a.destroy(),p(o)}),document.body.appendChild(i),i.click(),document.body.removeChild(i)})}}),Tt={class:"grid gap-0.5"},Vt={class:"flex items-center gap-1 ml-auto"},It={key:0,class:"flex items-start w-full space-x-4"},Mt={class:"space-y-2"},jt={key:1,class:"grid gap-3"},At={class:"grid gap-3"},Nt={class:"flex items-center justify-between"},qt={class:"flex items-center gap-2"},Rt={class:"flex items-center justify-between"},Dt={class:"flex items-center gap-2"},Ht={class:"flex items-center justify-between"},Bt={class:"flex items-center gap-2"},Ft={class:"flex items-center justify-between"},zt={class:"flex items-center gap-2"},Ut={class:"flex items-center justify-between"},Qt={class:"flex items-center gap-2"},Yt={class:"flex items-center justify-between"},Gt={class:"flex items-center gap-2"},Jt={class:"flex items-center justify-between"},Kt={class:"flex items-center gap-2"},Wt={class:"flex items-center gap-2 text-xs text-muted-foreground"},Xt={__name:"SysinfoCards",props:{sysinfo:{type:Object,required:!0},isLoadingSysinfo:{type:Boolean,required:!0}},emits:["refresh"],setup(r,{emit:S}){const u=M({}),m=M({}),{toClipboard:p}=Ot(),i=S,a=async(g,n)=>{try{await p(JSON.stringify(n)),m.value[g]=!0,setTimeout(()=>{m.value[g]=!1},1500)}catch(C){console.error("Failed to copy:",C)}},o=g=>{u.value[g]=!0},d=g=>{u.value[g]=!1};function b(){i("refresh")}return(g,n)=>{const C=F("Button");return P(),T("div",null,[e(t(H),{class:"overflow-hidden"},{default:x(()=>[e(t(q),{class:"flex flex-row items-start bg-muted/50"},{default:x(()=>[s("div",Tt,[e(t(R),{class:"flex items-center gap-2 text-lg group"},{default:x(()=>n[22]||(n[22]=[E("System Details")])),_:1}),e(t(oe),null,{default:x(()=>n[23]||(n[23]=[E("Useful system information for support")])),_:1})]),s("div",Vt,[e(C,{onClick:n[0]||(n[0]=f=>b()),size:"sm",variant:"outline",class:"gap-1 hover:bg-rcgray-800"},{default:x(()=>[e(t(V),{icon:"flat-color-icons:refresh",class:"hover:animate-pulse"})]),_:1})])]),_:1}),e(t(D),{class:"text-sm"},{default:x(()=>[r.isLoadingSysinfo?(P(),T("div",It,[e(t($),{class:"w-12 h-12 rounded-full"}),s("div",Mt,[e(t($),{class:"h-4 w-[250px]"}),e(t($),{class:"w-[400px] h-4"}),e(t($),{class:"w-[400px] h-4"}),e(t($),{class:"w-[400px] h-4"}),e(t($),{class:"w-[400px] h-4"}),e(t($),{class:"w-[400px] h-4"}),e(t($),{class:"w-[400px] h-4"}),e(t($),{class:"w-[400px] h-4"}),e(t($),{class:"w-[400px] h-4"})])])):I("",!0),r.isLoadingSysinfo?I("",!0):(P(),T("div",jt,[s("dl",At,[s("div",Nt,[n[24]||(n[24]=s("dt",{class:"text-muted-foreground"},"OSVersion",-1)),s("dd",qt,[E(O(r.sysinfo.OSVersion)+" ",1),e(t(V),{icon:m.value.OSVersion?"material-symbols:check-circle-outline":u.value.OSVersion?"material-symbols:content-copy":"material-symbols:content-copy-outline",class:N([m.value.OSVersion?"text-green-500":"text-gray-500","cursor-pointer hover:text-gray-700"]),onClick:n[1]||(n[1]=f=>a("OSVersion",r.sysinfo.OSVersion)),onMouseover:n[2]||(n[2]=f=>o("OSVersion")),onMouseleave:n[3]||(n[3]=f=>d("OSVersion"))},null,8,["icon","class"])])]),s("div",Rt,[n[25]||(n[25]=s("dt",{class:"text-muted-foreground"},"localIp",-1)),s("dd",Dt,[E(O(r.sysinfo.localIp)+" ",1),e(t(V),{icon:m.value.localIp?"material-symbols:check-circle-outline":u.value.localIp?"material-symbols:content-copy":"material-symbols:content-copy-outline",class:N([m.value.localIp?"text-green-500":"text-gray-500","cursor-pointer hover:text-gray-700"]),onClick:n[4]||(n[4]=f=>a("localIp",r.sysinfo.localIp)),onMouseover:n[5]||(n[5]=f=>o("localIp")),onMouseleave:n[6]||(n[6]=f=>d("localIp"))},null,8,["icon","class"])])]),s("div",Ht,[n[26]||(n[26]=s("dt",{class:"text-muted-foreground"},"PublicIP",-1)),s("dd",Bt,[E(O(r.sysinfo.PublicIP)+" ",1),e(t(V),{icon:m.value.PublicIP?"material-symbols:check-circle-outline":u.value.PublicIP?"material-symbols:content-copy":"material-symbols:content-copy-outline",class:N([m.value.PublicIP?"text-green-500":"text-gray-500","cursor-pointer hover:text-gray-700"]),onClick:n[7]||(n[7]=f=>a("PublicIP",r.sysinfo.PublicIP)),onMouseover:n[8]||(n[8]=f=>o("PublicIP")),onMouseleave:n[9]||(n[9]=f=>d("PublicIP"))},null,8,["icon","class"])])]),s("div",Ft,[n[27]||(n[27]=s("dt",{class:"text-muted-foreground"},"ServerName",-1)),s("dd",zt,[E(O(r.sysinfo.ServerName)+" ",1),e(t(V),{icon:m.value.ServerName?"material-symbols:check-circle-outline":u.value.ServerName?"material-symbols:content-copy":"material-symbols:content-copy-outline",class:N([m.value.ServerName?"text-green-500":"text-gray-500","cursor-pointer hover:text-gray-700"]),onClick:n[10]||(n[10]=f=>a("ServerName",r.sysinfo.ServerName)),onMouseover:n[11]||(n[11]=f=>o("ServerName")),onMouseleave:n[12]||(n[12]=f=>d("ServerName"))},null,8,["icon","class"])])]),s("div",Ut,[n[28]||(n[28]=s("dt",{class:"text-muted-foreground"},"PHPVersion",-1)),s("dd",Qt,[E(O(r.sysinfo.PHPVersion)+" ",1),e(t(V),{icon:m.value.PHPVersion?"material-symbols:check-circle-outline":u.value.PHPVersion?"material-symbols:content-copy":"material-symbols:content-copy-outline",class:N([m.value.PHPVersion?"text-green-500":"text-gray-500","cursor-pointer hover:text-gray-700"]),onClick:n[13]||(n[13]=f=>a("PHPVersion",r.sysinfo.PHPVersion)),onMouseover:n[14]||(n[14]=f=>o("PHPVersion")),onMouseleave:n[15]||(n[15]=f=>d("PHPVersion"))},null,8,["icon","class"])])]),s("div",Yt,[n[29]||(n[29]=s("dt",{class:"text-muted-foreground"},"RedisVersion",-1)),s("dd",Gt,[E(O(r.sysinfo.RedisVersion)+" ",1),e(t(V),{icon:m.value.RedisVersion?"material-symbols:check-circle-outline":u.value.RedisVersion?"material-symbols:content-copy":"material-symbols:content-copy-outline",class:N([m.value.RedisVersion?"text-green-500":"text-gray-500","cursor-pointer hover:text-gray-700"]),onClick:n[16]||(n[16]=f=>a("RedisVersion",r.sysinfo.RedisVersion)),onMouseover:n[17]||(n[17]=f=>o("RedisVersion")),onMouseleave:n[18]||(n[18]=f=>d("RedisVersion"))},null,8,["icon","class"])])]),s("div",Jt,[n[30]||(n[30]=s("dt",{class:"text-muted-foreground"},"MySQLVersion",-1)),s("dd",Kt,[E(O(r.sysinfo.MySQLVersion)+" ",1),e(t(V),{icon:m.value.MySQLVersion?"material-symbols:check-circle-outline":u.value.MySQLVersion?"material-symbols:content-copy":"material-symbols:content-copy-outline",class:N([m.value.MySQLVersion?"text-green-500":"text-gray-500","cursor-pointer hover:text-gray-700"]),onClick:n[19]||(n[19]=f=>a("MySQLVersion",r.sysinfo.MySQLVersion)),onMouseover:n[20]||(n[20]=f=>o("MySQLVersion")),onMouseleave:n[21]||(n[21]=f=>d("MySQLVersion"))},null,8,["icon","class"])])])])]))]),_:1}),e(t(ie),{class:"flex flex-row items-center px-6 py-3 border-t bg-muted/50"},{default:x(()=>[s("div",Wt,[e(t(V),{icon:"logos:laravel"}),E(" Laravel Version: "+O(r.sysinfo.LaravelVersion),1)])]),_:1})]),_:1})])}}},Zt={__name:"Popover",props:{defaultOpen:{type:Boolean,required:!1},open:{type:Boolean,required:!1},modal:{type:Boolean,required:!1}},emits:["update:open"],setup(r,{emit:S}){const p=ne(r,S);return(i,a)=>(P(),J(t($e),se(re(t(p))),{default:x(()=>[G(i.$slots,"default")]),_:3},16))}},eo={__name:"PopoverTrigger",props:{asChild:{type:Boolean,required:!1},as:{type:null,required:!1}},setup(r){const S=r;return(u,m)=>(P(),J(t(Se),se(re(S)),{default:x(()=>[G(u.$slots,"default")]),_:3},16))}},to=Object.assign({inheritAttrs:!1},{__name:"PopoverContent",props:{forceMount:{type:Boolean,required:!1},trapFocus:{type:Boolean,required:!1},side:{type:null,required:!1},sideOffset:{type:Number,required:!1,default:4},align:{type:null,required:!1,default:"center"},alignOffset:{type:Number,required:!1},avoidCollisions:{type:Boolean,required:!1},collisionBoundary:{type:null,required:!1},collisionPadding:{type:[Number,Object],required:!1},arrowPadding:{type:Number,required:!1},sticky:{type:String,required:!1},hideWhenDetached:{type:Boolean,required:!1},updatePositionStrategy:{type:String,required:!1},prioritizePosition:{type:Boolean,required:!1},asChild:{type:Boolean,required:!1},as:{type:null,required:!1},disableOutsidePointerEvents:{type:Boolean,required:!1},class:{type:null,required:!1}},emits:["escapeKeyDown","pointerDownOutside","focusOutside","interactOutside","openAutoFocus","closeAutoFocus"],setup(r,{emit:S}){const u=r,m=S,p=Ce(()=>{const{class:a,...o}=u;return o}),i=ne(p,m);return(a,o)=>(P(),J(t(Pe),null,{default:x(()=>[e(t(Le),ke({...t(i),...a.$attrs},{class:t(Z)("z-50 w-72 rounded-md border bg-popover p-4 text-popover-foreground shadow-md outline-none data-[state=open]:animate-in data-[state=closed]:animate-out data-[state=closed]:fade-out-0 data-[state=open]:fade-in-0 data-[state=closed]:zoom-out-95 data-[state=open]:zoom-in-95 data-[side=bottom]:slide-in-from-top-2 data-[side=left]:slide-in-from-right-2 data-[side=right]:slide-in-from-left-2 data-[side=top]:slide-in-from-bottom-2",u.class)}),{default:x(()=>[G(a.$slots,"default")]),_:3},16,["class"])]),_:3}))}}),oo={class:"border-t border-b topRow"},no={class:"sticky top-0 flex items-center h-12 gap-4 px-8"},so={class:"grid gap-4"},ro={class:"flex-col text-sm font-medium md:flex md:flex-row md:items-center md:text-sm"},io={href:"#",class:"flex items-center gap-2 px-4 py-2 font-semibold rounded-md text-md hover:bg-rcgray-800"},ao={href:"#",class:"flex items-center gap-2 px-4 py-2 font-semibold rounded-md text-md hover:bg-rcgray-800"},lo={href:"#",class:"flex items-center gap-2 px-4 py-2 font-semibold rounded-md text-md hover:bg-rcgray-800"},co={href:"#",class:"flex items-center gap-2 px-4 py-2 font-semibold rounded-md text-md hover:bg-rcgray-800"},uo={class:"flex justify-between w-full"},fo={href:"#",class:"flex items-center gap-2 px-4 py-2 font-semibold rounded-md text-md hover:bg-rcgray-800"},mo={href:"#",class:"flex items-center gap-2 px-4 py-2 font-semibold rounded-md text-md hover:bg-rcgray-800"},po={href:"#",class:"flex items-center gap-2 px-4 py-2 font-semibold rounded-md text-md hover:bg-rcgray-800"},yo={key:1,class:"flex items-center ml-auto"},go={class:"ml-2 text-muted-foreground"},vo={__name:"DashboardActions",props:{licenseInfo:Object},setup(r){const S=M(!1),u=()=>{S.value=!S.value};return(m,p)=>{const i=F("Icon"),a=F("router-link");return P(),T("div",oo,[s("header",no,[e(t(Zt),null,{default:x(()=>[e(t(eo),{"as-child":""},{default:x(()=>[s("button",{onClick:u,variant:"outline",class:"md:hidden"},[e(i,{icon:S.value?"material-symbols-light:menu-open":"material-symbols-light:menu",class:"w-6 h-6"},null,8,["icon"])])]),_:1}),e(t(to),{class:"p-2 w-80",align:"start"},{default:x(()=>[s("div",so,[s("nav",ro,[s("a",io,[e(i,{icon:"fluent-color:add-circle-28",class:""}),p[0]||(p[0]=s("span",null,"Create New Device",-1))]),s("a",ao,[e(i,{icon:"fluent-color:apps-24",class:""}),p[1]||(p[1]=E(" View Devices "))]),s("a",lo,[e(i,{icon:"fluent-color:search-visual-16",class:""}),p[2]||(p[2]=E(" Search Configs "))]),s("a",co,[e(i,{icon:"flat-color-icons:delete-database",class:""}),p[3]||(p[3]=E(" Purge Failed Configs "))])])])]),_:1})]),_:1}),s("div",uo,[s("nav",{class:N([{hidden:!S.value,flex:S.value},"flex-col hidden font-medium text-md md:flex md:flex-row md:items-center md:text-sm"])},[s("a",fo,[e(i,{icon:"fluent-color:add-circle-28",class:""}),p[4]||(p[4]=s("span",null,"Create New Device",-1))]),e(a,{to:{name:"inventory",params:{view:"devices"}},class:"flex items-center gap-2 px-4 py-2 font-semibold rounded-md text-md hover:bg-rcgray-800"},{default:x(()=>[e(i,{icon:"fluent-color:apps-24",class:""}),p[5]||(p[5]=E(" View Devices "))]),_:1}),s("a",mo,[e(i,{icon:"fluent-color:search-visual-16",class:""}),p[6]||(p[6]=E(" Search Configs "))]),s("a",po,[e(i,{icon:"flat-color-icons:delete-database",class:""}),p[7]||(p[7]=E(" Purge Failed Configs "))])],2),r.licenseInfo.data?I("",!0):(P(),J(t($),{key:0,class:"flex items-center ml-auto h-4 w-[50px]"})),r.licenseInfo.data?(P(),T("div",yo,[e(i,{icon:"ph:code-duotone",class:"text-muted-foreground"}),s("span",go,O(r.licenseInfo.data.version)+"-Core",1)])):I("",!0)])])])}}},ho=Ee(vo,[["__scopeId","data-v-c10ec258"]]),xo={class:"flex flex-col flex-1 gap-2 dark:bg-rcgray-900"},bo={class:"grid grid-cols-1 gap-2 px-8 lg:grid-cols-2 md:gap-4 xl:gap-8"},_o={class:"flex-1"},wo={class:"flex-1"},ko={__name:"Main",props:{},setup(r){const{fetchSysinfo:S,fetchConfiginfo:u,fetchHealth:m,fetchLicenseInfo:p,sysinfo:i,configinfo:a,healthLatest:o,licenseInfo:d,isLoadingSysinfo:b,isLoadingConfiginfo:g,isLoadingHealth:n,toastSuccess:C,toastError:f}=Ve();return Oe(()=>{S(),u(),m(),p()}),(_,L)=>(P(),T("main",xo,[e(ho,{licenseInfo:t(d)},null,8,["licenseInfo"]),e(Ke,{configinfo:t(a),isLoadingConfiginfo:t(g)},null,8,["configinfo","isLoadingConfiginfo"]),s("div",bo,[s("div",_o,[e(kt,{healthLatest:t(o),isLoadingHealth:t(n),SystemUptime:t(i).SystemUptime},null,8,["healthLatest","isLoadingHealth","SystemUptime"])]),s("div",wo,[e(Xt,{onRefresh:L[0]||(L[0]=k=>t(S)(!0)),sysinfo:t(i),isLoadingSysinfo:t(b)},null,8,["sysinfo","isLoadingSysinfo"])])])]))}};export{ko as default};