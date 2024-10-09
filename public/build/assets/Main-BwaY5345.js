import{d as be,o as P,c as T,r as Q,n as I,u as t,a as X,b as A,e as W,f as Y,g as e,w as b,_ as q,h as R,i as k,j as D,k as n,t as E,l as M,m as H,p as oe,I as V,q as _e,s as we,x as se,v as Z,y as ne,z as re,A as Se,B as $e,C as Ce,D as Le,E as ke,F as Pe,G as Ee,H as Oe}from"./app-7hO3kobU.js";import{u as Te}from"./useToaster-BYDoeTf1.js";/* empty css               */const ie=be({__name:"CardFooter",props:{class:{}},setup(r){const $=r;return(u,f)=>(P(),T("div",{class:I(t(X)("flex items-center p-6 pt-0",$.class))},[Q(u.$slots,"default")],2))}});function Ve(){const r=A([]),$=A([]),u=A([]),f=A(!1),m=A(!1),i=A(!1),{toastSuccess:a,toastError:o}=Te();async function d(s=!1,_={}){f.value=!0;try{const y=await W.get("/api/dashboard/sysinfo?clearcache="+s);r.value=y.data}catch(y){console.error("Error fetching sysinfo:",y),o("Error","Failed to fetch sysinfo.")}finally{f.value=!1}}async function x(s={}){m.value=!0;try{const _=await W.get("/api/dashboard/configinfo");$.value=_.data}catch(_){console.error("Error fetching configinfo:",_),o("Error","Failed to fetch configinfo.")}finally{m.value=!1}}async function v(s={}){i.value=!0;try{const _=await W.get("/api/dashboard/health-latest");u.value=_.data}catch(_){console.error("Error fetching healthLatest:",_),o("Error","Failed to fetch healthLatest.")}finally{i.value=!1}}return{fetchSysinfo:d,fetchConfiginfo:x,fetchHealth:v,sysinfo:r,configinfo:$,healthLatest:u,isLoadingSysinfo:f,isLoadingConfiginfo:m,isLoadingHealth:i,toastSuccess:a,toastError:o}}const S={__name:"Skeleton",props:{class:{type:null,required:!1}},setup(r){const $=r;return(u,f)=>(P(),T("div",{class:I(t(X)("animate-pulse rounded-md bg-primary/10",$.class))},null,2))}},Me={class:"grid gap-2 p-8 sm:gap-4 md:grid-cols-2 xl:gap-8 lg:grid-cols-4"},Ae={key:0},je={class:"text-2xl font-bold"},Ne={key:1,class:"flex items-center space-x-4"},Ie={class:"space-y-2"},qe={key:0},Re={class:"text-2xl font-bold"},De={key:1,class:"flex items-center space-x-4"},He={class:"space-y-2"},Be={key:0},Fe={class:"text-2xl font-bold"},ze={key:1,class:"flex items-center space-x-4"},Ue={class:"space-y-2"},Qe={key:0},Ye={class:"text-2xl font-bold"},Ge={key:1,class:"flex items-center space-x-4"},Je={class:"space-y-2"},Ke={__name:"ConfigInfoCards",props:{configinfo:{type:Object,required:!0},isLoadingConfiginfo:{type:Boolean,required:!0}},setup(r){return A(!1),($,u)=>{const f=Y("Icon");return P(),T("div",Me,[e(t(H),{class:"border shadow rounded-xl bg-card text-card-foreground"},{default:b(()=>[e(t(q),{class:"flex flex-row items-center justify-between pb-2 space-y-0"},{default:b(()=>[e(t(R),{class:"text-sm font-medium"},{default:b(()=>u[0]||(u[0]=[k("Device count")])),_:1}),e(f,{icon:"devicon:networkx",class:"w-4 h-4 text-muted-foreground"})]),_:1}),e(t(D),null,{default:b(()=>[r.configinfo.data?(P(),T("div",Ae,[n("div",je,E(r.configinfo.data.deviceCount),1),u[1]||(u[1]=n("p",{class:"text-xs text-muted-foreground"},"Total count of devices",-1))])):M("",!0),r.isLoadingConfiginfo?(P(),T("div",Ne,[e(t(S),{class:"w-12 h-12 rounded-full"}),n("div",Ie,[e(t(S),{class:"h-4 w-[250px]"}),e(t(S),{class:"h-4 w-[200px]"})])])):M("",!0)]),_:1})]),_:1}),e(t(H),null,{default:b(()=>[e(t(q),{class:"flex flex-row items-center justify-between pb-2 space-y-0"},{default:b(()=>[e(t(R),{class:"text-sm font-medium"},{default:b(()=>u[2]||(u[2]=[k("Devices down")])),_:1}),e(f,{icon:"fluent-color:cloud-dismiss-24",class:"w-4 h-4 text-muted-foreground"})]),_:1}),e(t(D),null,{default:b(()=>[r.configinfo.data?(P(),T("div",qe,[n("div",Re,E(r.configinfo.data.deviceDownCount),1),u[3]||(u[3]=n("p",{class:"text-xs text-muted-foreground"},"Total number of devices unreachable",-1))])):M("",!0),r.isLoadingConfiginfo?(P(),T("div",De,[e(t(S),{class:"w-12 h-12 rounded-full"}),n("div",He,[e(t(S),{class:"h-4 w-[250px]"}),e(t(S),{class:"h-4 w-[200px]"})])])):M("",!0)]),_:1})]),_:1}),e(t(H),null,{default:b(()=>[e(t(q),{class:"flex flex-row items-center justify-between pb-2 space-y-0"},{default:b(()=>[e(t(R),{class:"text-sm font-medium"},{default:b(()=>u[4]||(u[4]=[k("Total configs")])),_:1}),e(f,{icon:"fluent-color:checkbox-24",class:"w-4 h-4 text-muted-foreground"})]),_:1}),e(t(D),null,{default:b(()=>[r.configinfo.data?(P(),T("div",Be,[n("div",Fe,E(r.configinfo.data.configTotalCount),1),u[5]||(u[5]=n("p",{class:"text-xs text-muted-foreground"},"Total number of configs downloaded",-1))])):M("",!0),r.isLoadingConfiginfo?(P(),T("div",ze,[e(t(S),{class:"w-12 h-12 rounded-full"}),n("div",Ue,[e(t(S),{class:"h-4 w-[250px]"}),e(t(S),{class:"h-4 w-[200px]"})])])):M("",!0)]),_:1})]),_:1}),e(t(H),null,{default:b(()=>[e(t(q),{class:"flex flex-row items-center justify-between pb-2 space-y-0"},{default:b(()=>[e(t(R),{class:"text-sm font-medium"},{default:b(()=>u[6]||(u[6]=[k("Failed configs")])),_:1}),e(f,{icon:"fluent-color:calendar-cancel-24",class:"w-4 h-4 text-muted-foreground"})]),_:1}),e(t(D),null,{default:b(()=>[r.configinfo.data?(P(),T("div",Qe,[n("div",Ye,E(r.configinfo.data.configDownCount),1),u[7]||(u[7]=n("p",{class:"text-xs text-muted-foreground"},"Number of configuration download failures",-1))])):M("",!0),r.isLoadingConfiginfo?(P(),T("div",Ge,[e(t(S),{class:"w-12 h-12 rounded-full"}),n("div",Je,[e(t(S),{class:"h-4 w-[250px]"}),e(t(S),{class:"h-4 w-[200px]"})])])):M("",!0)]),_:1})]),_:1})])}}},We={class:"grid gap-0.5"},Xe={class:"flex items-center gap-1 ml-auto"},Ze={key:0,class:"flex items-start w-full space-x-4"},et={class:"space-y-2"},tt={key:1},ot={key:2,class:"grid gap-3"},st={class:"grid gap-3"},nt={class:"flex items-center justify-between"},rt={class:"text-muted-foreground"},it={class:"flex items-center gap-2"},at={class:"flex items-center justify-between"},lt={class:"text-muted-foreground"},ct={class:"flex items-center gap-2"},ut={class:"flex items-center justify-between"},dt={class:"text-muted-foreground"},ft={class:"flex items-center gap-2"},mt={class:"flex items-center justify-between"},pt={class:"text-muted-foreground"},yt={class:"flex items-center gap-2"},gt={class:"flex items-center justify-between"},vt={class:"text-muted-foreground"},ht={class:"flex items-center gap-2"},xt={class:"flex items-center justify-between"},bt={class:"text-muted-foreground"},_t={class:"flex items-center gap-2"},wt={class:"flex items-center justify-between"},St={class:"text-muted-foreground"},$t={class:"flex items-center gap-2"},Ct={class:"text-xs text-muted-foreground"},Lt={dateTime:"2023-11-23"},kt={__name:"HealthLatestCards",props:{healthLatest:{type:Object,required:!0},isLoadingHealth:{type:Boolean,required:!0},SystemUptime:{type:String}},emits:["refresh"],setup(r,{emit:$}){A({}),A({});const u=$;function f(){u("refresh")}return(m,i)=>{const a=Y("Button");return P(),T("div",null,[e(t(H),{class:"overflow-hidden"},{default:b(()=>[e(t(q),{class:"flex flex-row items-start bg-muted/50"},{default:b(()=>[n("div",We,[e(t(R),{class:"flex items-center gap-2 text-lg group"},{default:b(()=>i[1]||(i[1]=[k("Latest Health")])),_:1}),e(t(oe),null,{default:b(()=>i[2]||(i[2]=[k("Server health information")])),_:1})]),n("div",Xe,[e(a,{onClick:i[0]||(i[0]=o=>f()),size:"sm",variant:"outline",class:"gap-1 hover:bg-rcgray-800"},{default:b(()=>[e(t(V),{icon:"flat-color-icons:refresh",class:"hover:animate-pulse"})]),_:1})])]),_:1}),e(t(D),{class:"text-sm"},{default:b(()=>[r.isLoadingHealth?(P(),T("div",Ze,[e(t(S),{class:"w-12 h-12 rounded-full"}),n("div",et,[e(t(S),{class:"h-4 w-[250px]"}),e(t(S),{class:"w-[400px] h-4"}),e(t(S),{class:"w-[400px] h-4"}),e(t(S),{class:"w-[400px] h-4"}),e(t(S),{class:"w-[400px] h-4"}),e(t(S),{class:"w-[400px] h-4"}),e(t(S),{class:"w-[400px] h-4"}),e(t(S),{class:"w-[400px] h-4"}),e(t(S),{class:"w-[400px] h-4"})])])):M("",!0),r.healthLatest?M("",!0):(P(),T("div",tt,"asd")),r.healthLatest.data?(P(),T("div",ot,[n("dl",st,[n("div",nt,[n("dt",rt,E(r.healthLatest.data[0].label),1),n("dd",it,[e(t(V),{icon:r.healthLatest.data[0].status==="Ok"?"fluent-color:checkmark-circle-32":"fluent-color:dismiss-circle-32"},null,8,["icon"])])]),n("div",at,[n("dt",lt,E(r.healthLatest.data[1].label),1),n("dd",ct,[e(t(V),{icon:r.healthLatest.data[1].status==="Ok"?"fluent-color:checkmark-circle-32":"fluent-color:dismiss-circle-32"},null,8,["icon"])])]),n("div",ut,[n("dt",dt,E(r.healthLatest.data[3].label),1),n("dd",ft,[e(t(V),{icon:r.healthLatest.data[3].status==="Running"?"fluent-color:checkmark-circle-32":"fluent-color:dismiss-circle-32"},null,8,["icon"])])]),n("div",mt,[n("dt",pt,E(r.healthLatest.data[4].label),1),n("dd",yt,[e(t(V),{icon:r.healthLatest.data[4].status==="Reachable"?"fluent-color:checkmark-circle-32":"fluent-color:dismiss-circle-32"},null,8,["icon"])])]),n("div",gt,[n("dt",vt,E(r.healthLatest.data[6].label),1),n("dd",ht,[e(t(V),{icon:r.healthLatest.data[6].status==="Ok"?"fluent-color:checkmark-circle-32":"fluent-color:dismiss-circle-32"},null,8,["icon"])])]),n("div",xt,[n("dt",bt,E(r.healthLatest.data[7].label),1),n("dd",_t,E(r.healthLatest.data[7].status),1)]),n("div",wt,[n("dt",St,E(r.healthLatest.data[2].label),1),n("dd",$t,E(r.healthLatest.data[2].status),1)])])])):M("",!0)]),_:1}),e(t(ie),{class:"flex flex-row items-center px-6 py-3 border-t bg-muted/50"},{default:b(()=>[n("div",Ct,[i[3]||(i[3]=k(" System Uptime: ")),n("time",Lt,E(r.SystemUptime),1)])]),_:1})]),_:1})])}}};var ae={exports:{}};/*!
 * clipboard.js v2.0.11
 * https://clipboardjs.com/
 *
 * Licensed MIT © Zeno Rocha
 */(function(r,$){(function(f,m){r.exports=m()})(_e,function(){return function(){var u={686:function(i,a,o){o.d(a,{default:function(){return xe}});var d=o(279),x=o.n(d),v=o(370),s=o.n(v),_=o(817),y=o.n(_);function C(h){try{return document.execCommand(h)}catch{return!1}}var O=function(c){var l=y()(c);return C("cut"),l},L=O;function N(h){var c=document.documentElement.getAttribute("dir")==="rtl",l=document.createElement("textarea");l.style.fontSize="12pt",l.style.border="0",l.style.padding="0",l.style.margin="0",l.style.position="absolute",l.style[c?"right":"left"]="-9999px";var p=window.pageYOffset||document.documentElement.scrollTop;return l.style.top="".concat(p,"px"),l.setAttribute("readonly",""),l.value=h,l}var ee=function(c,l){var p=N(c);l.container.appendChild(p);var g=y()(p);return C("copy"),p.remove(),g},le=function(c){var l=arguments.length>1&&arguments[1]!==void 0?arguments[1]:{container:document.body},p="";return typeof c=="string"?p=ee(c,l):c instanceof HTMLInputElement&&!["text","search","url","tel","password"].includes(c==null?void 0:c.type)?p=ee(c.value,l):(p=y()(c),C("copy")),p},G=le;function F(h){"@babel/helpers - typeof";return typeof Symbol=="function"&&typeof Symbol.iterator=="symbol"?F=function(l){return typeof l}:F=function(l){return l&&typeof Symbol=="function"&&l.constructor===Symbol&&l!==Symbol.prototype?"symbol":typeof l},F(h)}var ce=function(){var c=arguments.length>0&&arguments[0]!==void 0?arguments[0]:{},l=c.action,p=l===void 0?"copy":l,g=c.container,w=c.target,j=c.text;if(p!=="copy"&&p!=="cut")throw new Error('Invalid "action" value, use either "copy" or "cut"');if(w!==void 0)if(w&&F(w)==="object"&&w.nodeType===1){if(p==="copy"&&w.hasAttribute("disabled"))throw new Error('Invalid "target" attribute. Please use "readonly" instead of "disabled" attribute');if(p==="cut"&&(w.hasAttribute("readonly")||w.hasAttribute("disabled")))throw new Error(`Invalid "target" attribute. You can't cut text from elements with "readonly" or "disabled" attributes`)}else throw new Error('Invalid "target" value, use a valid Element');if(j)return G(j,{container:g});if(w)return p==="cut"?L(w):G(w,{container:g})},ue=ce;function B(h){"@babel/helpers - typeof";return typeof Symbol=="function"&&typeof Symbol.iterator=="symbol"?B=function(l){return typeof l}:B=function(l){return l&&typeof Symbol=="function"&&l.constructor===Symbol&&l!==Symbol.prototype?"symbol":typeof l},B(h)}function de(h,c){if(!(h instanceof c))throw new TypeError("Cannot call a class as a function")}function te(h,c){for(var l=0;l<c.length;l++){var p=c[l];p.enumerable=p.enumerable||!1,p.configurable=!0,"value"in p&&(p.writable=!0),Object.defineProperty(h,p.key,p)}}function fe(h,c,l){return c&&te(h.prototype,c),l&&te(h,l),h}function me(h,c){if(typeof c!="function"&&c!==null)throw new TypeError("Super expression must either be null or a function");h.prototype=Object.create(c&&c.prototype,{constructor:{value:h,writable:!0,configurable:!0}}),c&&J(h,c)}function J(h,c){return J=Object.setPrototypeOf||function(p,g){return p.__proto__=g,p},J(h,c)}function pe(h){var c=ve();return function(){var p=z(h),g;if(c){var w=z(this).constructor;g=Reflect.construct(p,arguments,w)}else g=p.apply(this,arguments);return ye(this,g)}}function ye(h,c){return c&&(B(c)==="object"||typeof c=="function")?c:ge(h)}function ge(h){if(h===void 0)throw new ReferenceError("this hasn't been initialised - super() hasn't been called");return h}function ve(){if(typeof Reflect>"u"||!Reflect.construct||Reflect.construct.sham)return!1;if(typeof Proxy=="function")return!0;try{return Date.prototype.toString.call(Reflect.construct(Date,[],function(){})),!0}catch{return!1}}function z(h){return z=Object.setPrototypeOf?Object.getPrototypeOf:function(l){return l.__proto__||Object.getPrototypeOf(l)},z(h)}function K(h,c){var l="data-clipboard-".concat(h);if(c.hasAttribute(l))return c.getAttribute(l)}var he=function(h){me(l,h);var c=pe(l);function l(p,g){var w;return de(this,l),w=c.call(this),w.resolveOptions(g),w.listenClick(p),w}return fe(l,[{key:"resolveOptions",value:function(){var g=arguments.length>0&&arguments[0]!==void 0?arguments[0]:{};this.action=typeof g.action=="function"?g.action:this.defaultAction,this.target=typeof g.target=="function"?g.target:this.defaultTarget,this.text=typeof g.text=="function"?g.text:this.defaultText,this.container=B(g.container)==="object"?g.container:document.body}},{key:"listenClick",value:function(g){var w=this;this.listener=s()(g,"click",function(j){return w.onClick(j)})}},{key:"onClick",value:function(g){var w=g.delegateTarget||g.currentTarget,j=this.action(w)||"copy",U=ue({action:j,container:this.container,target:this.target(w),text:this.text(w)});this.emit(U?"success":"error",{action:j,text:U,trigger:w,clearSelection:function(){w&&w.focus(),window.getSelection().removeAllRanges()}})}},{key:"defaultAction",value:function(g){return K("action",g)}},{key:"defaultTarget",value:function(g){var w=K("target",g);if(w)return document.querySelector(w)}},{key:"defaultText",value:function(g){return K("text",g)}},{key:"destroy",value:function(){this.listener.destroy()}}],[{key:"copy",value:function(g){var w=arguments.length>1&&arguments[1]!==void 0?arguments[1]:{container:document.body};return G(g,w)}},{key:"cut",value:function(g){return L(g)}},{key:"isSupported",value:function(){var g=arguments.length>0&&arguments[0]!==void 0?arguments[0]:["copy","cut"],w=typeof g=="string"?[g]:g,j=!!document.queryCommandSupported;return w.forEach(function(U){j=j&&!!document.queryCommandSupported(U)}),j}}]),l}(x()),xe=he},828:function(i){var a=9;if(typeof Element<"u"&&!Element.prototype.matches){var o=Element.prototype;o.matches=o.matchesSelector||o.mozMatchesSelector||o.msMatchesSelector||o.oMatchesSelector||o.webkitMatchesSelector}function d(x,v){for(;x&&x.nodeType!==a;){if(typeof x.matches=="function"&&x.matches(v))return x;x=x.parentNode}}i.exports=d},438:function(i,a,o){var d=o(828);function x(_,y,C,O,L){var N=s.apply(this,arguments);return _.addEventListener(C,N,L),{destroy:function(){_.removeEventListener(C,N,L)}}}function v(_,y,C,O,L){return typeof _.addEventListener=="function"?x.apply(null,arguments):typeof C=="function"?x.bind(null,document).apply(null,arguments):(typeof _=="string"&&(_=document.querySelectorAll(_)),Array.prototype.map.call(_,function(N){return x(N,y,C,O,L)}))}function s(_,y,C,O){return function(L){L.delegateTarget=d(L.target,y),L.delegateTarget&&O.call(_,L)}}i.exports=v},879:function(i,a){a.node=function(o){return o!==void 0&&o instanceof HTMLElement&&o.nodeType===1},a.nodeList=function(o){var d=Object.prototype.toString.call(o);return o!==void 0&&(d==="[object NodeList]"||d==="[object HTMLCollection]")&&"length"in o&&(o.length===0||a.node(o[0]))},a.string=function(o){return typeof o=="string"||o instanceof String},a.fn=function(o){var d=Object.prototype.toString.call(o);return d==="[object Function]"}},370:function(i,a,o){var d=o(879),x=o(438);function v(C,O,L){if(!C&&!O&&!L)throw new Error("Missing required arguments");if(!d.string(O))throw new TypeError("Second argument must be a String");if(!d.fn(L))throw new TypeError("Third argument must be a Function");if(d.node(C))return s(C,O,L);if(d.nodeList(C))return _(C,O,L);if(d.string(C))return y(C,O,L);throw new TypeError("First argument must be a String, HTMLElement, HTMLCollection, or NodeList")}function s(C,O,L){return C.addEventListener(O,L),{destroy:function(){C.removeEventListener(O,L)}}}function _(C,O,L){return Array.prototype.forEach.call(C,function(N){N.addEventListener(O,L)}),{destroy:function(){Array.prototype.forEach.call(C,function(N){N.removeEventListener(O,L)})}}}function y(C,O,L){return x(document.body,C,O,L)}i.exports=v},817:function(i){function a(o){var d;if(o.nodeName==="SELECT")o.focus(),d=o.value;else if(o.nodeName==="INPUT"||o.nodeName==="TEXTAREA"){var x=o.hasAttribute("readonly");x||o.setAttribute("readonly",""),o.select(),o.setSelectionRange(0,o.value.length),x||o.removeAttribute("readonly"),d=o.value}else{o.hasAttribute("contenteditable")&&o.focus();var v=window.getSelection(),s=document.createRange();s.selectNodeContents(o),v.removeAllRanges(),v.addRange(s),d=v.toString()}return d}i.exports=a},279:function(i){function a(){}a.prototype={on:function(o,d,x){var v=this.e||(this.e={});return(v[o]||(v[o]=[])).push({fn:d,ctx:x}),this},once:function(o,d,x){var v=this;function s(){v.off(o,s),d.apply(x,arguments)}return s._=d,this.on(o,s,x)},emit:function(o){var d=[].slice.call(arguments,1),x=((this.e||(this.e={}))[o]||[]).slice(),v=0,s=x.length;for(v;v<s;v++)x[v].fn.apply(x[v].ctx,d);return this},off:function(o,d){var x=this.e||(this.e={}),v=x[o],s=[];if(v&&d)for(var _=0,y=v.length;_<y;_++)v[_].fn!==d&&v[_].fn._!==d&&s.push(v[_]);return s.length?x[o]=s:delete x[o],this}},i.exports=a,i.exports.TinyEmitter=a}},f={};function m(i){if(f[i])return f[i].exports;var a=f[i]={exports:{}};return u[i](a,a.exports,m),a.exports}return function(){m.n=function(i){var a=i&&i.__esModule?function(){return i.default}:function(){return i};return m.d(a,{a}),a}}(),function(){m.d=function(i,a){for(var o in a)m.o(a,o)&&!m.o(i,o)&&Object.defineProperty(i,o,{enumerable:!0,get:a[o]})}}(),function(){m.o=function(i,a){return Object.prototype.hasOwnProperty.call(i,a)}}(),m(686)}().default})})(ae);var Pt=ae.exports;const Et=we(Pt),Ot=r=>({toClipboard($,u){return new Promise((f,m)=>{const i=document.createElement("button"),a=new Et(i,{text:()=>$,action:()=>"copy",container:u!==void 0?u:document.body});a.on("success",o=>{a.destroy(),f(o)}),a.on("error",o=>{a.destroy(),m(o)}),document.body.appendChild(i),i.click(),document.body.removeChild(i)})}}),Tt={class:"grid gap-0.5"},Vt={class:"flex items-center gap-1 ml-auto"},Mt={key:0,class:"flex items-start w-full space-x-4"},At={class:"space-y-2"},jt={key:1,class:"grid gap-3"},Nt={class:"grid gap-3"},It={class:"flex items-center justify-between"},qt={class:"flex items-center gap-2"},Rt={class:"flex items-center justify-between"},Dt={class:"flex items-center gap-2"},Ht={class:"flex items-center justify-between"},Bt={class:"flex items-center gap-2"},Ft={class:"flex items-center justify-between"},zt={class:"flex items-center gap-2"},Ut={class:"flex items-center justify-between"},Qt={class:"flex items-center gap-2"},Yt={class:"flex items-center justify-between"},Gt={class:"flex items-center gap-2"},Jt={class:"flex items-center justify-between"},Kt={class:"flex items-center gap-2"},Wt={class:"flex items-center gap-2 text-xs text-muted-foreground"},Xt={__name:"SysinfoCards",props:{sysinfo:{type:Object,required:!0},isLoadingSysinfo:{type:Boolean,required:!0}},emits:["refresh"],setup(r,{emit:$}){const u=A({}),f=A({}),{toClipboard:m}=Ot(),i=$,a=async(v,s)=>{try{await m(JSON.stringify(s)),f.value[v]=!0,setTimeout(()=>{f.value[v]=!1},1500)}catch(_){console.error("Failed to copy:",_)}},o=v=>{u.value[v]=!0},d=v=>{u.value[v]=!1};function x(){i("refresh")}return(v,s)=>{const _=Y("Button");return P(),T("div",null,[e(t(H),{class:"overflow-hidden"},{default:b(()=>[e(t(q),{class:"flex flex-row items-start bg-muted/50"},{default:b(()=>[n("div",Tt,[e(t(R),{class:"flex items-center gap-2 text-lg group"},{default:b(()=>s[22]||(s[22]=[k("System Details")])),_:1}),e(t(oe),null,{default:b(()=>s[23]||(s[23]=[k("Useful system information for support")])),_:1})]),n("div",Vt,[e(_,{onClick:s[0]||(s[0]=y=>x()),size:"sm",variant:"outline",class:"gap-1 hover:bg-rcgray-800"},{default:b(()=>[e(t(V),{icon:"flat-color-icons:refresh",class:"hover:animate-pulse"})]),_:1})])]),_:1}),e(t(D),{class:"text-sm"},{default:b(()=>[r.isLoadingSysinfo?(P(),T("div",Mt,[e(t(S),{class:"w-12 h-12 rounded-full"}),n("div",At,[e(t(S),{class:"h-4 w-[250px]"}),e(t(S),{class:"w-[400px] h-4"}),e(t(S),{class:"w-[400px] h-4"}),e(t(S),{class:"w-[400px] h-4"}),e(t(S),{class:"w-[400px] h-4"}),e(t(S),{class:"w-[400px] h-4"}),e(t(S),{class:"w-[400px] h-4"}),e(t(S),{class:"w-[400px] h-4"}),e(t(S),{class:"w-[400px] h-4"})])])):M("",!0),r.isLoadingSysinfo?M("",!0):(P(),T("div",jt,[n("dl",Nt,[n("div",It,[s[24]||(s[24]=n("dt",{class:"text-muted-foreground"},"OSVersion",-1)),n("dd",qt,[k(E(r.sysinfo.OSVersion)+" ",1),e(t(V),{icon:f.value.OSVersion?"material-symbols:check-circle-outline":u.value.OSVersion?"material-symbols:content-copy":"material-symbols:content-copy-outline",class:I([f.value.OSVersion?"text-green-500":"text-gray-500","cursor-pointer hover:text-gray-700"]),onClick:s[1]||(s[1]=y=>a("OSVersion",r.sysinfo.OSVersion)),onMouseover:s[2]||(s[2]=y=>o("OSVersion")),onMouseleave:s[3]||(s[3]=y=>d("OSVersion"))},null,8,["icon","class"])])]),n("div",Rt,[s[25]||(s[25]=n("dt",{class:"text-muted-foreground"},"localIp",-1)),n("dd",Dt,[k(E(r.sysinfo.localIp)+" ",1),e(t(V),{icon:f.value.localIp?"material-symbols:check-circle-outline":u.value.localIp?"material-symbols:content-copy":"material-symbols:content-copy-outline",class:I([f.value.localIp?"text-green-500":"text-gray-500","cursor-pointer hover:text-gray-700"]),onClick:s[4]||(s[4]=y=>a("localIp",r.sysinfo.localIp)),onMouseover:s[5]||(s[5]=y=>o("localIp")),onMouseleave:s[6]||(s[6]=y=>d("localIp"))},null,8,["icon","class"])])]),n("div",Ht,[s[26]||(s[26]=n("dt",{class:"text-muted-foreground"},"PublicIP",-1)),n("dd",Bt,[k(E(r.sysinfo.PublicIP)+" ",1),e(t(V),{icon:f.value.PublicIP?"material-symbols:check-circle-outline":u.value.PublicIP?"material-symbols:content-copy":"material-symbols:content-copy-outline",class:I([f.value.PublicIP?"text-green-500":"text-gray-500","cursor-pointer hover:text-gray-700"]),onClick:s[7]||(s[7]=y=>a("PublicIP",r.sysinfo.PublicIP)),onMouseover:s[8]||(s[8]=y=>o("PublicIP")),onMouseleave:s[9]||(s[9]=y=>d("PublicIP"))},null,8,["icon","class"])])]),n("div",Ft,[s[27]||(s[27]=n("dt",{class:"text-muted-foreground"},"ServerName",-1)),n("dd",zt,[k(E(r.sysinfo.ServerName)+" ",1),e(t(V),{icon:f.value.ServerName?"material-symbols:check-circle-outline":u.value.ServerName?"material-symbols:content-copy":"material-symbols:content-copy-outline",class:I([f.value.ServerName?"text-green-500":"text-gray-500","cursor-pointer hover:text-gray-700"]),onClick:s[10]||(s[10]=y=>a("ServerName",r.sysinfo.ServerName)),onMouseover:s[11]||(s[11]=y=>o("ServerName")),onMouseleave:s[12]||(s[12]=y=>d("ServerName"))},null,8,["icon","class"])])]),n("div",Ut,[s[28]||(s[28]=n("dt",{class:"text-muted-foreground"},"PHPVersion",-1)),n("dd",Qt,[k(E(r.sysinfo.PHPVersion)+" ",1),e(t(V),{icon:f.value.PHPVersion?"material-symbols:check-circle-outline":u.value.PHPVersion?"material-symbols:content-copy":"material-symbols:content-copy-outline",class:I([f.value.PHPVersion?"text-green-500":"text-gray-500","cursor-pointer hover:text-gray-700"]),onClick:s[13]||(s[13]=y=>a("PHPVersion",r.sysinfo.PHPVersion)),onMouseover:s[14]||(s[14]=y=>o("PHPVersion")),onMouseleave:s[15]||(s[15]=y=>d("PHPVersion"))},null,8,["icon","class"])])]),n("div",Yt,[s[29]||(s[29]=n("dt",{class:"text-muted-foreground"},"RedisVersion",-1)),n("dd",Gt,[k(E(r.sysinfo.RedisVersion)+" ",1),e(t(V),{icon:f.value.RedisVersion?"material-symbols:check-circle-outline":u.value.RedisVersion?"material-symbols:content-copy":"material-symbols:content-copy-outline",class:I([f.value.RedisVersion?"text-green-500":"text-gray-500","cursor-pointer hover:text-gray-700"]),onClick:s[16]||(s[16]=y=>a("RedisVersion",r.sysinfo.RedisVersion)),onMouseover:s[17]||(s[17]=y=>o("RedisVersion")),onMouseleave:s[18]||(s[18]=y=>d("RedisVersion"))},null,8,["icon","class"])])]),n("div",Jt,[s[30]||(s[30]=n("dt",{class:"text-muted-foreground"},"MySQLVersion",-1)),n("dd",Kt,[k(E(r.sysinfo.MySQLVersion)+" ",1),e(t(V),{icon:f.value.MySQLVersion?"material-symbols:check-circle-outline":u.value.MySQLVersion?"material-symbols:content-copy":"material-symbols:content-copy-outline",class:I([f.value.MySQLVersion?"text-green-500":"text-gray-500","cursor-pointer hover:text-gray-700"]),onClick:s[19]||(s[19]=y=>a("MySQLVersion",r.sysinfo.MySQLVersion)),onMouseover:s[20]||(s[20]=y=>o("MySQLVersion")),onMouseleave:s[21]||(s[21]=y=>d("MySQLVersion"))},null,8,["icon","class"])])])])]))]),_:1}),e(t(ie),{class:"flex flex-row items-center px-6 py-3 border-t bg-muted/50"},{default:b(()=>[n("div",Wt,[e(t(V),{icon:"logos:laravel"}),k(" Laravel Version: "+E(r.sysinfo.LaravelVersion),1)])]),_:1})]),_:1})])}}},Zt={__name:"Popover",props:{defaultOpen:{type:Boolean,required:!1},open:{type:Boolean,required:!1},modal:{type:Boolean,required:!1}},emits:["update:open"],setup(r,{emit:$}){const m=se(r,$);return(i,a)=>(P(),Z(t(Se),ne(re(t(m))),{default:b(()=>[Q(i.$slots,"default")]),_:3},16))}},eo={__name:"PopoverTrigger",props:{asChild:{type:Boolean,required:!1},as:{type:null,required:!1}},setup(r){const $=r;return(u,f)=>(P(),Z(t($e),ne(re($)),{default:b(()=>[Q(u.$slots,"default")]),_:3},16))}},to=Object.assign({inheritAttrs:!1},{__name:"PopoverContent",props:{forceMount:{type:Boolean,required:!1},trapFocus:{type:Boolean,required:!1},side:{type:null,required:!1},sideOffset:{type:Number,required:!1,default:4},align:{type:null,required:!1,default:"center"},alignOffset:{type:Number,required:!1},avoidCollisions:{type:Boolean,required:!1},collisionBoundary:{type:null,required:!1},collisionPadding:{type:[Number,Object],required:!1},arrowPadding:{type:Number,required:!1},sticky:{type:String,required:!1},hideWhenDetached:{type:Boolean,required:!1},updatePositionStrategy:{type:String,required:!1},prioritizePosition:{type:Boolean,required:!1},asChild:{type:Boolean,required:!1},as:{type:null,required:!1},disableOutsidePointerEvents:{type:Boolean,required:!1},class:{type:null,required:!1}},emits:["escapeKeyDown","pointerDownOutside","focusOutside","interactOutside","openAutoFocus","closeAutoFocus"],setup(r,{emit:$}){const u=r,f=$,m=Ce(()=>{const{class:a,...o}=u;return o}),i=se(m,f);return(a,o)=>(P(),Z(t(Pe),null,{default:b(()=>[e(t(Le),ke({...t(i),...a.$attrs},{class:t(X)("z-50 w-72 rounded-md border bg-popover p-4 text-popover-foreground shadow-md outline-none data-[state=open]:animate-in data-[state=closed]:animate-out data-[state=closed]:fade-out-0 data-[state=open]:fade-in-0 data-[state=closed]:zoom-out-95 data-[state=open]:zoom-in-95 data-[side=bottom]:slide-in-from-top-2 data-[side=left]:slide-in-from-right-2 data-[side=right]:slide-in-from-left-2 data-[side=top]:slide-in-from-bottom-2",u.class)}),{default:b(()=>[Q(a.$slots,"default")]),_:3},16,["class"])]),_:3}))}}),oo={class:"border-t border-b topRow"},so={class:"sticky top-0 flex items-center h-12 gap-4 px-8"},no={class:"grid gap-4"},ro={class:"flex-col text-sm font-medium md:flex md:flex-row md:items-center md:text-sm"},io={href:"#",class:"flex items-center gap-2 px-4 py-2 font-semibold rounded-md text-md hover:bg-rcgray-800"},ao={href:"#",class:"flex items-center gap-2 px-4 py-2 font-semibold rounded-md text-md hover:bg-rcgray-800"},lo={href:"#",class:"flex items-center gap-2 px-4 py-2 font-semibold rounded-md text-md hover:bg-rcgray-800"},co={href:"#",class:"flex items-center gap-2 px-4 py-2 font-semibold rounded-md text-md hover:bg-rcgray-800"},uo={href:"#",class:"flex items-center gap-2 px-4 py-2 font-semibold rounded-md text-md hover:bg-rcgray-800"},fo={href:"#",class:"flex items-center gap-2 px-4 py-2 font-semibold rounded-md text-md hover:bg-rcgray-800"},mo={href:"#",class:"flex items-center gap-2 px-4 py-2 font-semibold rounded-md text-md hover:bg-rcgray-800"},po={href:"#",class:"flex items-center gap-2 px-4 py-2 font-semibold rounded-md text-md hover:bg-rcgray-800"},yo={__name:"DashboardActions",setup(r){const $=A(!1),u=()=>{$.value=!$.value};return(f,m)=>{const i=Y("Icon");return P(),T("div",oo,[n("header",so,[e(t(Zt),null,{default:b(()=>[e(t(eo),{"as-child":""},{default:b(()=>[n("button",{onClick:u,variant:"outline",class:"md:hidden"},[e(i,{icon:$.value?"material-symbols-light:menu-open":"material-symbols-light:menu",class:"w-6 h-6"},null,8,["icon"])])]),_:1}),e(t(to),{class:"p-2 w-80",align:"start"},{default:b(()=>[n("div",no,[n("nav",ro,[n("a",io,[e(i,{icon:"fluent-color:add-circle-28",class:""}),m[0]||(m[0]=n("span",null,"Create New Device",-1))]),n("a",ao,[e(i,{icon:"fluent-color:apps-24",class:""}),m[1]||(m[1]=k(" View Devices "))]),n("a",lo,[e(i,{icon:"fluent-color:search-visual-16",class:""}),m[2]||(m[2]=k(" Search Configs "))]),n("a",co,[e(i,{icon:"flat-color-icons:delete-database",class:""}),m[3]||(m[3]=k(" Purge Failed Configs "))])])])]),_:1})]),_:1}),n("nav",{class:I([{hidden:!$.value,flex:$.value},"flex-col hidden font-medium text-md md:flex md:flex-row md:items-center md:text-sm"])},[n("a",uo,[e(i,{icon:"fluent-color:add-circle-28",class:""}),m[4]||(m[4]=n("span",null,"Create New Device",-1))]),n("a",fo,[e(i,{icon:"fluent-color:apps-24",class:""}),m[5]||(m[5]=k(" View Devices "))]),n("a",mo,[e(i,{icon:"fluent-color:search-visual-16",class:""}),m[6]||(m[6]=k(" Search Configs "))]),n("a",po,[e(i,{icon:"flat-color-icons:delete-database",class:""}),m[7]||(m[7]=k(" Purge Failed Configs "))])],2)])])}}},go=Ee(yo,[["__scopeId","data-v-9d1a784e"]]),vo={class:"flex flex-col flex-1 gap-2 dark:bg-rcgray-900"},ho={class:"grid grid-cols-1 gap-2 px-8 lg:grid-cols-2 md:gap-4 xl:gap-8"},xo={class:"flex-1"},bo={class:"flex-1"},Co={__name:"Main",props:{},setup(r){const{fetchSysinfo:$,fetchConfiginfo:u,fetchHealth:f,sysinfo:m,configinfo:i,healthLatest:a,isLoadingSysinfo:o,isLoadingConfiginfo:d,isLoadingHealth:x,toastSuccess:v,toastError:s}=Ve();return Oe(()=>{$(),u(),f()}),(_,y)=>(P(),T("main",vo,[e(go),e(Ke,{configinfo:t(i),isLoadingConfiginfo:t(d)},null,8,["configinfo","isLoadingConfiginfo"]),n("div",ho,[n("div",xo,[e(kt,{healthLatest:t(a),isLoadingHealth:t(x),SystemUptime:t(m).SystemUptime},null,8,["healthLatest","isLoadingHealth","SystemUptime"])]),n("div",bo,[e(Xt,{onRefresh:y[0]||(y[0]=C=>t($)(!0)),sysinfo:t(m),isLoadingSysinfo:t(o)},null,8,["sysinfo","isLoadingSysinfo"])])])]))}};export{Co as default};