import{az as q,o as i,z as u,w as d,C as p,aG as c,aH as f,f as t,b8 as _,B as b,aB as g,e as h,b9 as B,D as C,E as v,ba as P,bb as N,p as w,r as z}from"./app-Cmnl06IG.js";const $={__name:"HoverCard",props:{defaultOpen:{type:Boolean,required:!1},open:{type:Boolean,required:!1},openDelay:{type:Number,required:!1},closeDelay:{type:Number,required:!1}},emits:["update:open"],setup(s,{emit:e}){const a=q(s,e);return(o,n)=>(i(),u(t(_),c(f(t(a))),{default:d(()=>[p(o.$slots,"default")]),_:3},16))}},x={__name:"HoverCardContent",props:{forceMount:{type:Boolean,required:!1},side:{type:null,required:!1},sideOffset:{type:Number,required:!1,default:4},align:{type:null,required:!1},alignOffset:{type:Number,required:!1},avoidCollisions:{type:Boolean,required:!1},collisionBoundary:{type:null,required:!1},collisionPadding:{type:[Number,Object],required:!1},arrowPadding:{type:Number,required:!1},sticky:{type:String,required:!1},hideWhenDetached:{type:Boolean,required:!1},updatePositionStrategy:{type:String,required:!1},prioritizePosition:{type:Boolean,required:!1},asChild:{type:Boolean,required:!1},as:{type:null,required:!1},class:{type:null,required:!1}},setup(s){const e=s,r=b(()=>{const{class:a,...o}=e;return o}),l=g(r);return(a,o)=>(i(),u(t(P),null,{default:d(()=>[h(t(B),C(t(l),{class:t(v)("z-50 w-64 rounded-md border bg-popover p-4 text-popover-foreground shadow-md outline-none data-[state=open]:animate-in data-[state=closed]:animate-out data-[state=closed]:fade-out-0 data-[state=open]:fade-in-0 data-[state=closed]:zoom-out-95 data-[state=open]:zoom-in-95 data-[side=bottom]:slide-in-from-top-2 data-[side=left]:slide-in-from-right-2 data-[side=right]:slide-in-from-left-2 data-[side=top]:slide-in-from-bottom-2",e.class)}),{default:d(()=>[p(a.$slots,"default")]),_:3},16,["class"])]),_:3}))}},D={__name:"HoverCardTrigger",props:{asChild:{type:Boolean,required:!1},as:{type:null,required:!1}},setup(s){const e=s;return(r,l)=>(i(),u(t(N),c(f(e)),{default:d(()=>[p(r.$slots,"default")]),_:3},16))}};function O(){const{text:s,copy:e,copied:r,isSupported:l}=w(),a=z({});return{copyItem:async(n,m)=>{try{e(JSON.stringify(m)),a.value[n]=!0,setTimeout(()=>{a.value[n]=!1},1500)}catch(y){console.error("Failed to copy:",y)}},activeCopyIcon:a}}export{D as _,x as a,$ as b,O as u};