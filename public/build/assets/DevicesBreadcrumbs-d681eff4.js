import{_ as v,A as b,H as g,f as m,r as h,o as t,c as i,a,b as n,w as s,j as o,q as d,t as u,h as l}from"./app-33ff2348.js";const w={props:{devicename:{type:String,default:""},deviceId:{type:[Number,String],default:""}},setup(f){const _=b();g();const c=m(_.path),e=m(_.params.id);return{currentRoute:c,routeParam:e}}},k={class:"pf-c-breadcrumb","aria-label":"breadcrumb"},p={class:"pf-c-breadcrumb__list",style:{"padding-left":"0px","margin-left":"0px"}},y={class:"pf-c-breadcrumb__item",style:{"margin-top":"var(--pf-c-content--li--MarginTop)"}},P=a("span",{class:"pf-c-breadcrumb__item-divider"},[a("i",{class:"fas fa-angle-right","aria-hidden":"true"})],-1),R={key:0,class:"pf-c-breadcrumb__item"},x=a("span",{class:"pf-c-breadcrumb__item-divider"},[a("i",{class:"fas fa-angle-right","aria-hidden":"true"})],-1),N={key:1,class:"pf-c-breadcrumb__item"},V=a("span",{class:"pf-c-breadcrumb__item-divider"},[a("i",{class:"fas fa-angle-right","aria-hidden":"true"})],-1),B={key:2,class:"pf-c-breadcrumb__item"},C=a("span",{class:"pf-c-breadcrumb__item-divider"},[a("i",{class:"fas fa-angle-right","aria-hidden":"true"})],-1),D={key:3,class:"pf-c-breadcrumb__item"},I=a("span",{class:"pf-c-breadcrumb__item-divider"},[a("i",{class:"fas fa-angle-right","aria-hidden":"true"})],-1),S={key:4,class:"pf-c-breadcrumb__item"},A=a("span",{class:"pf-c-breadcrumb__item-divider"},[a("i",{class:"fas fa-angle-right","aria-hidden":"true"})],-1),E={key:5,class:"pf-c-breadcrumb__item"},T=a("span",{class:"pf-c-breadcrumb__item-divider"},[a("i",{class:"fas fa-angle-right","aria-hidden":"true"})],-1);function j(f,_,c,e,q,z){const r=h("router-link");return t(),i("nav",k,[a("ol",p,[a("li",y,[P,n(r,{type:"button",class:d(["pf-c-breadcrumb__link alink",e.currentRoute==="/devices"?"pf-m-current":""]),to:"/devices"},{default:s(()=>[o("All Devices")]),_:1},8,["class"])]),c.devicename&&e.currentRoute==="/device/view/"+e.routeParam||e.currentRoute==="/device/view/configs/"+e.routeParam?(t(),i("li",R,[x,n(r,{type:"button",class:d(["pf-c-breadcrumb__link alink",e.currentRoute==="/device/view/"+e.routeParam?"pf-m-current":""]),to:"/device/view/"+c.deviceId},{default:s(()=>[o(u(c.devicename),1)]),_:1},8,["to","class"])])):l("",!0),c.devicename&&e.currentRoute==="/device/view/configs/"+e.routeParam?(t(),i("li",N,[V,n(r,{type:"button",class:d(["pf-c-breadcrumb__link alink",e.currentRoute==="/device/view/configs/"+e.routeParam?"pf-m-current":""]),to:"/device/view/configs/"+e.routeParam},{default:s(()=>[o("configs")]),_:1},8,["to","class"])])):l("",!0),e.currentRoute==="/device/view/configs/view-config/"+e.routeParam?(t(),i("li",B,[C,n(r,{type:"button",class:d(["pf-c-breadcrumb__link alink",e.currentRoute==="/device/view/"+e.routeParam?"pf-m-current":""]),to:"/device/view/"+c.deviceId},{default:s(()=>[o(u(c.devicename),1)]),_:1},8,["to","class"])])):l("",!0),c.devicename&&e.currentRoute==="/device/view/configs/view-config/"+e.routeParam?(t(),i("li",D,[I,n(r,{type:"button",class:d(["pf-c-breadcrumb__link alink",e.currentRoute==="/device/view/configs/view-config/"+e.routeParam?"pf-m-current":""]),to:"/device/view/configs/"+e.routeParam},{default:s(()=>[o("view config")]),_:1},8,["to","class"])])):l("",!0),e.currentRoute==="/device/view/eventlog/"+e.routeParam?(t(),i("li",S,[A,n(r,{type:"button",class:d(["pf-c-breadcrumb__link",e.currentRoute==="/device/view/eventlog"+e.routeParam?"pf-m-current":""]),to:"/device/view/"+c.deviceId},{default:s(()=>[o(u(c.devicename),1)]),_:1},8,["to","class"])])):l("",!0),c.devicename&&e.currentRoute==="/device/view/eventlog/"+e.routeParam?(t(),i("li",E,[T,n(r,{type:"button",class:d(["pf-c-breadcrumb__link",e.currentRoute==="/device/view/eventlog/"+e.routeParam?"pf-m-current":""]),to:"/device/view/eventlog/"+e.routeParam},{default:s(()=>[o("View Events")]),_:1},8,["to","class"])])):l("",!0)])])}const M=v(w,[["render",j]]);export{M as D};
