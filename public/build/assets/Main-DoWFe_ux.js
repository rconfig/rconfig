import{C as _,o,v as y,w as g,c as i,n as v,u as n,a as x,t as b,l as S,E as F,aa as h,k as s,g as a,b as k,W as C,X as w,i as $,Q as U,H as N,ab as q,f as z,ac as B}from"./app-BZnJZNAx.js";/* empty css               */const d={__name:"Separator",props:{orientation:{type:String,required:!1},decorative:{type:Boolean,required:!1},asChild:{type:Boolean,required:!1},as:{type:null,required:!1},class:{type:null,required:!1},label:{type:String,required:!1}},setup(r){const t=r,e=_(()=>{const{class:u,...l}=t;return l});return(u,l)=>(o(),y(n(h),F(e.value,{class:n(x)("shrink-0 bg-border relative",t.orientation==="vertical"?"w-px h-full":"h-px w-full",t.class)}),{default:g(()=>[t.label?(o(),i("span",{key:0,class:v(n(x)("text-xs text-muted-foreground bg-background absolute top-1/2 left-1/2 -translate-x-1/2 -translate-y-1/2 flex justify-center items-center",t.orientation==="vertical"?"w-[1px] px-1 py-2":"h-[1px] py-1 px-2"))},b(t.label),3)):S("",!0)]),_:1},16,["class"]))}},I={__name:"SystemSettingsForm",setup(r){return(t,e)=>(o(),i("div",null,[e[0]||(e[0]=s("h3",{class:"text-lg font-medium"},"SystemSettingsForm",-1)),e[1]||(e[1]=s("p",{class:"text-sm text-muted-foreground"},"Update your account settings. Set your preferred language and timezone.",-1)),a(n(d),{class:"my-6"})]))}},L={__name:"SecurityForm",setup(r){return(t,e)=>(o(),i("div",null,[e[0]||(e[0]=s("h3",{class:"text-lg font-medium"},"Security",-1)),e[1]||(e[1]=s("p",{class:"text-sm text-muted-foreground"},"Update your account settings. Set your preferred language and timezone.",-1)),a(n(d),{class:"my-6"})]))}},V={__name:"AboutForm",setup(r){return(t,e)=>(o(),i("div",null,[e[0]||(e[0]=s("h3",{class:"text-lg font-medium"},"AboutForm",-1)),e[1]||(e[1]=s("p",{class:"text-sm text-muted-foreground"},"Update your account settings. Set your preferred language and timezone.",-1)),a(n(d),{class:"my-6"})]))}},A={__name:"LogsForm",setup(r){return(t,e)=>(o(),i("div",null,[e[0]||(e[0]=s("h3",{class:"text-lg font-medium"},"LogsForm",-1)),e[1]||(e[1]=s("p",{class:"text-sm text-muted-foreground"},"Update your account settings. Set your preferred language and timezone.",-1)),a(n(d),{class:"my-6"})]))}},M={__name:"UpgradeForm",setup(r){return(t,e)=>(o(),i("div",null,[e[0]||(e[0]=s("h3",{class:"text-lg font-medium"},"UpgradeForm",-1)),e[1]||(e[1]=s("p",{class:"text-sm text-muted-foreground"},"Update your account settings. Set your preferred language and timezone.",-1)),a(n(d),{class:"my-6"})]))}};function T(){const r=k("/settings/system"),t={"/settings/system":I,"/settings/security":L,"/settings/about":V,"/settings/logs":A,"/settings/upgrade":M};function e(l){r.value=l,localStorage.setItem("activeForm",l)}const u=_(()=>t[r.value]||null);return{activeForm:r,setForm:e,formComponents:t,activeFormComponent:u}}const j={class:"flex space-x-2 lg:flex-col lg:space-x-0 lg:space-y-1"},D={__name:"SidebarNav",props:{activeForm:String},emits:["nav"],setup(r,{emit:t}){const e=[{title:"System",href:"/settings/system"},{title:"Security",href:"/settings/security"},{title:"About",href:"/settings/about"},{title:"Logs",href:"/settings/logs"},{title:"Upgrade",href:"/settings/upgrade"}],u=t;function l(m){u("nav",m)}return(m,c)=>(o(),i("nav",j,[(o(),i(C,null,w(e,p=>a(n(U),{key:p.title,onClick:f=>l(p.href),variant:"ghost",class:v([[r.activeForm===p.href?"  bg-rcgray-600":"text-rcgray-300"],"inline-flex items-center justify-start w-full px-4 py-2 text-sm font-medium text-left transition-colors rounded-md whitespace-nowrap focus-visible:outline-none focus-visible:ring-1 focus-visible:ring-ring disabled:pointer-events-none disabled:opacity-50 hover:text-accent-foreground h-9 hover:bg-rcgray-800"])},{default:g(()=>[$(b(p.title),1)]),_:2},1032,["onClick","class"])),64))]))}},E={class:"pb-16 pl-10 space-y-6 md:block"},P={class:"space-y-0.5"},G={class:"flex items-center space-x-2 text-2xl font-bold tracking-tight"},H={class:"flex flex-col space-y-8 lg:flex-row lg:space-x-12 lg:space-y-0"},Q={class:"-mx-4 lg:w-[10%]"},W={class:"flex-1 lg:max-w-2xl"},X={class:"space-y-6"},O={__name:"Main",setup(r){const{activeForm:t,setForm:e,formComponents:u,activeFormComponent:l}=T();return N(()=>{const m=localStorage.getItem("activeForm");m&&(t.value=m)}),(m,c)=>{const p=z("Icon");return o(),i("div",E,[s("div",P,[s("h2",G,[a(p,{icon:"catppuccin:env",class:"w-6 h-6"}),c[1]||(c[1]=s("span",null,"Settings",-1))]),c[2]||(c[2]=s("p",{class:"text-muted-foreground"},"Manage your account settings and set e-mail preferences.",-1))]),a(n(d),{class:"my-6"}),s("div",H,[s("aside",Q,[a(D,{onNav:c[0]||(c[0]=f=>n(e)(f)),activeForm:n(t)},null,8,["activeForm"])]),s("div",W,[s("div",X,[a(q,{name:"fade",mode:"out-in"},{default:g(()=>[(o(),y(B(n(l)),{key:n(t)}))]),_:1})])])])])}}};export{O as default};
