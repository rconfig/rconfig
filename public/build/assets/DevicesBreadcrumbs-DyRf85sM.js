import{_ as b,x as _,E as g,f,r as w,o as c,c as n,a,b as s,w as o,j as d,q as l,t as m,h as u}from"./app-CMvkN1B1.js";const k={props:{devicename:{type:String,default:""},deviceId:{type:[Number,String],default:""}},setup(v){const r=_();g();const i=f(r.path),e=f(r.params.id);return{currentRoute:i,routeParam:e}}},p={class:"pf-c-breadcrumb","aria-label":"breadcrumb"},y={class:"pf-c-breadcrumb__list",style:{"padding-left":"0px","margin-left":"0px"}},P={class:"pf-c-breadcrumb__item",style:{"margin-top":"var(--pf-c-content--li--MarginTop)"}},R={key:0,class:"pf-c-breadcrumb__item"},x={key:1,class:"pf-c-breadcrumb__item"},h={key:2,class:"pf-c-breadcrumb__item"},N={key:3,class:"pf-c-breadcrumb__item"},V={key:4,class:"pf-c-breadcrumb__item"},B={key:5,class:"pf-c-breadcrumb__item"};function C(v,r,i,e,D,I){const t=w("router-link");return c(),n("nav",p,[a("ol",y,[a("li",P,[r[1]||(r[1]=a("span",{class:"pf-c-breadcrumb__item-divider"},[a("i",{class:"fas fa-angle-right","aria-hidden":"true"})],-1)),s(t,{type:"button",class:l(["pf-c-breadcrumb__link alink",e.currentRoute==="/devices"?"pf-m-current":""]),to:"/devices"},{default:o(()=>r[0]||(r[0]=[d("All Devices")])),_:1},8,["class"])]),i.devicename&&e.currentRoute==="/device/view/"+e.routeParam||e.currentRoute==="/device/view/configs/"+e.routeParam?(c(),n("li",R,[r[2]||(r[2]=a("span",{class:"pf-c-breadcrumb__item-divider"},[a("i",{class:"fas fa-angle-right","aria-hidden":"true"})],-1)),s(t,{type:"button",class:l(["pf-c-breadcrumb__link alink",e.currentRoute==="/device/view/"+e.routeParam?"pf-m-current":""]),to:"/device/view/"+i.deviceId},{default:o(()=>[d(m(i.devicename),1)]),_:1},8,["to","class"])])):u("",!0),i.devicename&&e.currentRoute==="/device/view/configs/"+e.routeParam?(c(),n("li",x,[r[4]||(r[4]=a("span",{class:"pf-c-breadcrumb__item-divider"},[a("i",{class:"fas fa-angle-right","aria-hidden":"true"})],-1)),s(t,{type:"button",class:l(["pf-c-breadcrumb__link alink",e.currentRoute==="/device/view/configs/"+e.routeParam?"pf-m-current":""]),to:"/device/view/configs/"+e.routeParam},{default:o(()=>r[3]||(r[3]=[d("configs")])),_:1},8,["to","class"])])):u("",!0),e.currentRoute==="/device/view/configs/view-config/"+e.routeParam?(c(),n("li",h,[r[5]||(r[5]=a("span",{class:"pf-c-breadcrumb__item-divider"},[a("i",{class:"fas fa-angle-right","aria-hidden":"true"})],-1)),s(t,{type:"button",class:l(["pf-c-breadcrumb__link alink",e.currentRoute==="/device/view/"+e.routeParam?"pf-m-current":""]),to:"/device/view/"+i.deviceId},{default:o(()=>[d(m(i.devicename),1)]),_:1},8,["to","class"])])):u("",!0),i.devicename&&e.currentRoute==="/device/view/configs/view-config/"+e.routeParam?(c(),n("li",N,[r[7]||(r[7]=a("span",{class:"pf-c-breadcrumb__item-divider"},[a("i",{class:"fas fa-angle-right","aria-hidden":"true"})],-1)),s(t,{type:"button",class:l(["pf-c-breadcrumb__link alink",e.currentRoute==="/device/view/configs/view-config/"+e.routeParam?"pf-m-current":""]),to:"/device/view/configs/"+e.routeParam},{default:o(()=>r[6]||(r[6]=[d("view config")])),_:1},8,["to","class"])])):u("",!0),e.currentRoute==="/device/view/eventlog/"+e.routeParam?(c(),n("li",V,[r[8]||(r[8]=a("span",{class:"pf-c-breadcrumb__item-divider"},[a("i",{class:"fas fa-angle-right","aria-hidden":"true"})],-1)),s(t,{type:"button",class:l(["pf-c-breadcrumb__link",e.currentRoute==="/device/view/eventlog"+e.routeParam?"pf-m-current":""]),to:"/device/view/"+i.deviceId},{default:o(()=>[d(m(i.devicename),1)]),_:1},8,["to","class"])])):u("",!0),i.devicename&&e.currentRoute==="/device/view/eventlog/"+e.routeParam?(c(),n("li",B,[r[10]||(r[10]=a("span",{class:"pf-c-breadcrumb__item-divider"},[a("i",{class:"fas fa-angle-right","aria-hidden":"true"})],-1)),s(t,{type:"button",class:l(["pf-c-breadcrumb__link",e.currentRoute==="/device/view/eventlog/"+e.routeParam?"pf-m-current":""]),to:"/device/view/eventlog/"+e.routeParam},{default:o(()=>r[9]||(r[9]=[d("View Events")])),_:1},8,["to","class"])])):u("",!0)])])}const S=b(k,[["render",C]]);export{S as D};
