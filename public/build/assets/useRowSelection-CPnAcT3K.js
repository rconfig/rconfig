import{S as D,o as i,q as f,w as n,x as p,aJ as B,aK as S,e as t,b7 as R,c as y,n as v,z as m,av as T,b8 as j,v as P,d as r,b9 as O,ba as V,y as h,bb as M,bc as F,bd as I,be as N,bf as L,bg as H,s as K,D as U,g as _,t as q,i as g,b as w,U as G,V as J,W as b,j as x,X as W,bh as X,at as Q,r as A}from"./app-fY3sLAf_.js";const Y=D({__name:"DropdownMenuGroup",props:{asChild:{type:Boolean},as:{}},setup(e){const s=e;return(a,o)=>(i(),f(t(R),B(S(s)),{default:n(()=>[p(a.$slots,"default")]),_:3},16))}}),k=D({__name:"DropdownMenuShortcut",props:{class:{}},setup(e){const s=e;return(a,o)=>(i(),y("span",{class:v(t(m)("ml-auto text-xs tracking-widest opacity-60",s.class))},[p(a.$slots,"default")],2))}}),Z={__name:"AlertDialog",props:{open:{type:Boolean,required:!1},defaultOpen:{type:Boolean,required:!1}},emits:["update:open"],setup(e,{emit:s}){const u=T(e,s);return(c,l)=>(i(),f(t(j),B(S(t(u))),{default:n(()=>[p(c.$slots,"default")]),_:3},16))}},ee={__name:"AlertDialogContent",props:{forceMount:{type:Boolean,required:!1},trapFocus:{type:Boolean,required:!1},disableOutsidePointerEvents:{type:Boolean,required:!1},asChild:{type:Boolean,required:!1},as:{type:null,required:!1},class:{type:null,required:!1}},emits:["escapeKeyDown","pointerDownOutside","focusOutside","interactOutside","openAutoFocus","closeAutoFocus"],setup(e,{emit:s}){const a=e,o=s,u=P(()=>{const{class:l,...d}=a;return d}),c=T(u,o);return(l,d)=>(i(),f(t(M),null,{default:n(()=>[r(t(O),{class:"fixed inset-0 z-50 bg-black/80 data-[state=open]:animate-in data-[state=closed]:animate-out data-[state=closed]:fade-out-0 data-[state=open]:fade-in-0"}),r(t(V),h(t(c),{class:t(m)("fixed left-1/2 top-1/2 z-50 grid w-full max-w-lg -translate-x-1/2 -translate-y-1/2 gap-4 border bg-background p-6 shadow-lg duration-200 data-[state=open]:animate-in data-[state=closed]:animate-out data-[state=closed]:fade-out-0 data-[state=open]:fade-in-0 data-[state=closed]:zoom-out-95 data-[state=open]:zoom-in-95 data-[state=closed]:slide-out-to-left-1/2 data-[state=closed]:slide-out-to-top-[48%] data-[state=open]:slide-in-from-left-1/2 data-[state=open]:slide-in-from-top-[48%] sm:rounded-lg",a.class)}),{default:n(()=>[p(l.$slots,"default")]),_:3},16,["class"])]),_:3}))}},te={__name:"AlertDialogHeader",props:{class:{type:null,required:!1}},setup(e){const s=e;return(a,o)=>(i(),y("div",{class:v(t(m)("flex flex-col gap-y-2 text-center sm:text-left",s.class))},[p(a.$slots,"default")],2))}},se={__name:"AlertDialogTitle",props:{asChild:{type:Boolean,required:!1},as:{type:null,required:!1},class:{type:null,required:!1}},setup(e){const s=e,a=P(()=>{const{class:o,...u}=s;return u});return(o,u)=>(i(),f(t(F),h(a.value,{class:t(m)("text-lg font-semibold",s.class)}),{default:n(()=>[p(o.$slots,"default")]),_:3},16,["class"]))}},ae={__name:"AlertDialogDescription",props:{asChild:{type:Boolean,required:!1},as:{type:null,required:!1},class:{type:null,required:!1}},setup(e){const s=e,a=P(()=>{const{class:o,...u}=s;return u});return(o,u)=>(i(),f(t(I),h(a.value,{class:t(m)("text-sm text-muted-foreground",s.class)}),{default:n(()=>[p(o.$slots,"default")]),_:3},16,["class"]))}},le={__name:"AlertDialogFooter",props:{class:{type:null,required:!1}},setup(e){const s=e;return(a,o)=>(i(),y("div",{class:v(t(m)("flex flex-col-reverse sm:flex-row sm:justify-end sm:gap-x-2",s.class))},[p(a.$slots,"default")],2))}},ne={__name:"AlertDialogAction",props:{asChild:{type:Boolean,required:!1},as:{type:null,required:!1},class:{type:null,required:!1}},setup(e){const s=e,a=P(()=>{const{class:o,...u}=s;return u});return(o,u)=>(i(),f(t(L),h({onClick:u[0]||(u[0]=c=>o.$emit("action"))},a.value,{class:t(m)(t(N)(),s.class)}),{default:n(()=>[p(o.$slots,"default")]),_:3},16,["class"]))}},oe={__name:"AlertDialogCancel",props:{asChild:{type:Boolean,required:!1},as:{type:null,required:!1},class:{type:null,required:!1}},setup(e){const s=e,a=P(()=>{const{class:o,...u}=s;return u});return(o,u)=>(i(),f(t(H),h({onClick:u[0]||(u[0]=c=>o.$emit("cancel"))},a.value,{class:t(m)(t(N)({variant:"outline"}),"mt-2 sm:mt-0",s.class)}),{default:n(()=>[p(o.$slots,"default")]),_:3},16,["class"]))}},$e={__name:"ConfirmDeleteAlert",props:{ids:{type:Array,required:!0},showConfirmDelete:{type:Boolean,required:!0}},emits:["handleDelete","close"],setup(e,{emit:s}){const a=s;K(()=>{window.addEventListener("keydown",c=>{c.key==="Escape"&&u()})}),U(()=>{window.removeEventListener("keydown",c=>{c.key==="Escape"&&u()})});function o(){a("handleDelete")}function u(){a("close")}return(c,l)=>(i(),f(t(Z),{open:e.showConfirmDelete},{default:n(()=>[r(t(ee),null,{default:n(()=>[r(t(te),null,{default:n(()=>[r(t(se),null,{default:n(()=>l[2]||(l[2]=[_("Are you absolutely sure?")])),_:1}),r(t(ae),null,{default:n(()=>[_("This action cannot be undone. This will permanently delete the selected data (ID: "+q(e.ids)+").",1)]),_:1})]),_:1}),r(t(le),null,{default:n(()=>[r(t(oe),{onCancel:l[0]||(l[0]=d=>u())},{default:n(()=>l[3]||(l[3]=[_("Cancel")])),_:1}),r(t(ne),{onAction:l[1]||(l[1]=d=>o())},{default:n(()=>l[4]||(l[4]=[_("Continue")])),_:1})]),_:1})]),_:1})]),_:1},8,["open"]))}},re={class:"relative w-full overflow-auto"},be={__name:"Table",props:{class:{type:null,required:!1}},setup(e){const s=e;return(a,o)=>(i(),y("div",re,[g("table",{class:v(t(m)("w-full caption-bottom text-sm",s.class))},[p(a.$slots,"default")],2)]))}},xe={__name:"TableBody",props:{class:{type:null,required:!1}},setup(e){const s=e;return(a,o)=>(i(),y("tbody",{class:v(t(m)("[&_tr:last-child]:border-0",s.class))},[p(a.$slots,"default")],2))}},z={__name:"TableCell",props:{class:{type:null,required:!1}},setup(e){const s=e;return(a,o)=>(i(),y("td",{class:v(t(m)("p-2 align-middle [&:has([role=checkbox])]:pr-0 [&>[role=checkbox]]:translate-y-0.5",s.class))},[p(a.$slots,"default")],2))}},ke={__name:"TableHead",props:{class:{type:null,required:!1}},setup(e){const s=e;return(a,o)=>(i(),y("th",{class:v(t(m)("h-10 px-2 text-left align-middle font-medium text-muted-foreground [&:has([role=checkbox])]:pr-0 [&>[role=checkbox]]:translate-y-0.5",s.class))},[p(a.$slots,"default")],2))}},Pe={__name:"TableHeader",props:{class:{type:null,required:!1}},setup(e){const s=e;return(a,o)=>(i(),y("thead",{class:v(t(m)("[&_tr]:border-b",s.class))},[p(a.$slots,"default")],2))}},E={__name:"TableRow",props:{class:{type:null,required:!1}},setup(e){const s=e;return(a,o)=>(i(),y("tr",{class:v(t(m)("border-b transition-colors hover:bg-muted/50 data-[state=selected]:bg-muted",s.class))},[p(a.$slots,"default")],2))}},ue={class:"flex items-center justify-center text-sm text-muted-foreground"},he={__name:"Loading",props:{},setup(e){return(s,a)=>{const o=w("Icon");return i(),f(t(E),null,{default:n(()=>[r(t(z),{colspan:12,class:"h-24 text-center"},{default:n(()=>[g("div",ue,[a[0]||(a[0]=g("span",null,"Loading",-1)),r(o,{icon:"eos-icons:three-dots-loading",class:"ml-2"})])]),_:1})]),_:1})}}},qe={__name:"NoResults",props:{},setup(e){return(s,a)=>(i(),f(t(E),null,{default:n(()=>[r(t(z),{colspan:12,class:"h-24 text-center"},{default:n(()=>a[0]||(a[0]=[_(" No results. ")])),_:1})]),_:1}))}},ce={class:"flex items-center justify-end py-4 space-x-2"},ie={class:"flex items-center gap-2"},de={class:"flex items-center gap-2"},pe={class:"flex items-center gap-2"},fe={class:"flex items-center gap-2"},me={class:"flex items-center gap-2"},_e={class:"flex items-center gap-2"},ge={class:"flex-1 text-sm text-muted-foreground"},ye={class:"space-x-2"},Ce=D({__name:"Pagination",props:{currentPage:Number,lastPage:Number,perPage:Number},emits:["update:currentPage","update:perPage"],setup(e,{emit:s}){const a=s,o=c=>{a("update:currentPage",c)},u=c=>{a("update:perPage",c)};return(c,l)=>{const d=w("Icon"),C=w("Button");return i(),y("div",ce,[r(t(W),null,{default:n(()=>[r(t(G),{"as-child":""},{default:n(()=>[r(C,{variant:"outline"},{default:n(()=>[g("span",ie,[r(d,{icon:"fluent-color:pin-16"}),_(" "+q(e.perPage===1e5?"All":e.perPage+" per page"),1)])]),_:1})]),_:1}),r(t(J),{class:"w-56",align:"start"},{default:n(()=>[r(t(Y),null,{default:n(()=>[r(t(b),{onClick:l[0]||(l[0]=$=>u(5))},{default:n(()=>[g("span",de,[r(d,{icon:"fluent-color:pin-16"}),l[7]||(l[7]=_(" 5 per page "))]),r(t(k),null,{default:n(()=>[e.perPage===5?(i(),f(d,{key:0,icon:"flat-color-icons:checkmark",class:"ml-auto"})):x("",!0)]),_:1})]),_:1}),r(t(b),{onClick:l[1]||(l[1]=$=>u(10))},{default:n(()=>[g("span",pe,[r(d,{icon:"fluent-color:pin-16"}),l[8]||(l[8]=_(" 10 per page "))]),r(t(k),null,{default:n(()=>[e.perPage===10?(i(),f(d,{key:0,icon:"flat-color-icons:checkmark",class:"ml-auto"})):x("",!0)]),_:1})]),_:1}),r(t(b),{onClick:l[2]||(l[2]=$=>u(20))},{default:n(()=>[g("span",fe,[r(d,{icon:"fluent-color:pin-16"}),l[9]||(l[9]=_(" 20 per page "))]),r(t(k),null,{default:n(()=>[e.perPage===20?(i(),f(d,{key:0,icon:"flat-color-icons:checkmark",class:"ml-auto"})):x("",!0)]),_:1})]),_:1}),r(t(b),{onClick:l[3]||(l[3]=$=>u(50))},{default:n(()=>[g("span",me,[r(d,{icon:"fluent-color:pin-16"}),l[10]||(l[10]=_(" 50 per page "))]),r(t(k),null,{default:n(()=>[e.perPage===50?(i(),f(d,{key:0,icon:"flat-color-icons:checkmark",class:"ml-auto"})):x("",!0)]),_:1})]),_:1}),r(t(b),{onClick:l[4]||(l[4]=$=>u(1e5))},{default:n(()=>[g("span",_e,[r(d,{icon:"fluent-color:pin-16"}),l[11]||(l[11]=_(" All "))]),r(t(k),null,{default:n(()=>[e.perPage===1e5?(i(),f(d,{key:0,icon:"flat-color-icons:checkmark",class:"ml-auto"})):x("",!0)]),_:1})]),_:1})]),_:1})]),_:1})]),_:1}),g("div",ge,"Page "+q(e.currentPage)+" of "+q(e.lastPage),1),g("div",ye,[r(C,{onClick:l[5]||(l[5]=$=>o(Math.max(e.currentPage-1,1))),disabled:e.currentPage===1,variant:"outline",size:"sm",class:"py-1"},{default:n(()=>l[12]||(l[12]=[_(" Previous ")])),_:1},8,["disabled"]),r(C,{variant:"outline",size:"sm",class:"py-1",onClick:l[6]||(l[6]=$=>o(e.currentPage+1)),disabled:e.currentPage>=e.lastPage},{default:n(()=>l[13]||(l[13]=[_(" Next ")])),_:1},8,["disabled"])])])}}}),we=X("dialog",()=>{const e=Q({});function s(c){e[c]=!0}function a(c){e[c]=!1}function o(c){e[c]=!e[c]}function u(c){return e[c]||!1}return{dialogs:e,openDialog:s,closeDialog:a,toggleDialog:o,isDialogOpen:u}});function De(e){const s=A([]),a=A(!1);return{selectedRows:s,selectAll:a,toggleSelectAll:()=>{a.value=!a.value,a.value?s.value=e.value.data.map(c=>c.id):s.value=[]},toggleSelectRow:c=>{s.value.includes(c)?s.value=s.value.filter(l=>l!==c):s.value.push(c)}}}export{Pe as _,De as a,E as b,ke as c,xe as d,he as e,z as f,qe as g,be as h,Ce as i,$e as j,ee as k,te as l,se as m,ae as n,le as o,oe as p,ne as q,Z as r,k as s,Y as t,we as u};
