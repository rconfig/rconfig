import{e as h,r as _,s as y,m as S,f as u,C as m,o as l,c as i,a,d as v,t as r,j as c,A as p,n as b}from"./app-C9AuNpcX.js";const g={props:{drawerState:{type:Boolean,default:!1},editid:{type:[Number,String]},isClone:{type:Boolean,default:!1},pagename:{type:String},outerWidth:{type:String,default:null},onClickoutSideEnabled:{type:Boolean,default:!0}},emits:["closeDrawer"],setup(t,{emit:d}){const e=_("Add"),s=_(null);y(()=>{}),S(()=>{t.isClone?e.value="Clone":t.editid!=0&&(e.value="Edit")}),t.onClickoutSideEnabled?u(s,w=>o()):u(s,w=>{});function o(){d("closeDrawer")}const f=m(()=>t.outerWidth!=null?t.outerWidth:""),n=m(()=>t.drawerState?"pf-c-drawer__panel":"pf-c-drawer");return{clickOutsidetarget:s,typetext:e,close:o,width:f,state:n}}},k=["hidden"],C={key:0,class:"pf-c-drawer__body"},x={class:"pf-l-flex pf-m-column"},B={class:"pf-l-flex__item"},D={class:"pf-c-drawer__head"},E={class:"pf-c-drawer__actions"},N={class:"pf-c-drawer__close"},O={class:"pf-c-title pf-m-lg",id:"primary-detail-panel-body-padding-drawer-label"},V={key:0},W={key:1,class:"pf-c-drawer__body"},A={class:"pf-l-flex pf-m-column pf-m-space-items-lg"};function j(t,d,e,s,o,f){return l(),i("div",{class:b([[s.width],"pf-c-drawer__panel"]),hidden:!e.drawerState,ref:"clickOutsidetarget"},[e.drawerState?(l(),i("div",C,[a("div",x,[a("div",B,[a("div",D,[a("div",E,[a("div",N,[a("button",{class:"pf-c-button pf-m-plain",type:"button","aria-label":"Close drawer panel",onClick:d[0]||(d[0]=(...n)=>s.close&&s.close(...n))},d[1]||(d[1]=[a("i",{class:"fas fa-times","aria-hidden":"true"},null,-1)]))])]),a("h2",O,[v(r(s.typetext)+" "+r(e.pagename)+" ",1),e.editid>0?(l(),i("span",V,"ID: "+r(e.editid),1)):c("",!0)])])]),p(t.$slots,"subtext")])])):c("",!0),e.drawerState?(l(),i("div",W,[a("div",A,[p(t.$slots,"form")])])):c("",!0)],10,k)}const I=h(g,[["render",j]]);export{I as S};
