import{_ as w,f,g as y,y as S,m as u,D as m,o,c as i,a,j as v,t as r,h as c,B as p,q as b}from"./app-33ff2348.js";const g={props:{drawerState:{type:Boolean,default:!1},editid:{type:[Number,String]},isClone:{type:Boolean,default:!1},pagename:{type:String},outerWidth:{type:String,default:null},onClickoutSideEnabled:{type:Boolean,default:!0}},emits:["closeDrawer"],setup(t,{emit:d}){const e=f("Add"),s=f(null);y(()=>{}),S(()=>{t.isClone?e.value="Clone":t.editid!=0&&(e.value="Edit")}),t.onClickoutSideEnabled?u(s,h=>l()):u(s,h=>{});function l(){d("closeDrawer")}const _=m(()=>t.outerWidth!=null?t.outerWidth:""),n=m(()=>t.drawerState?"pf-c-drawer__panel":"pf-c-drawer");return{clickOutsidetarget:s,typetext:e,close:l,width:_,state:n}}},k=["hidden"],C={key:0,class:"pf-c-drawer__body"},x={class:"pf-l-flex pf-m-column"},B={class:"pf-l-flex__item"},D={class:"pf-c-drawer__head"},E={class:"pf-c-drawer__actions"},N={class:"pf-c-drawer__close"},O=a("i",{class:"fas fa-times","aria-hidden":"true"},null,-1),V=[O],W={class:"pf-c-title pf-m-lg",id:"primary-detail-panel-body-padding-drawer-label"},j={key:0},q={key:1,class:"pf-c-drawer__body"},z={class:"pf-l-flex pf-m-column pf-m-space-items-lg"};function A(t,d,e,s,l,_){return o(),i("div",{class:b([[s.width],"pf-c-drawer__panel"]),hidden:!e.drawerState,ref:"clickOutsidetarget"},[e.drawerState?(o(),i("div",C,[a("div",x,[a("div",B,[a("div",D,[a("div",E,[a("div",N,[a("button",{class:"pf-c-button pf-m-plain",type:"button","aria-label":"Close drawer panel",onClick:d[0]||(d[0]=(...n)=>s.close&&s.close(...n))},V)])]),a("h2",W,[v(r(s.typetext)+" "+r(e.pagename)+" ",1),e.editid>0?(o(),i("span",j,"ID: "+r(e.editid),1)):c("",!0)])])]),p(t.$slots,"subtext")])])):c("",!0),e.drawerState?(o(),i("div",q,[a("div",z,[p(t.$slots,"form")])])):c("",!0)],10,k)}const M=w(g,[["render",A]]);export{M as S};
