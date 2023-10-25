import{D as ie,a as ce,u as de,b as re}from"./DeleteModal-b3198555.js";import{D as _e}from"./DataTableSpinner-dedf6af0.js";import{_ as B,f as v,m as P,o as a,c as l,a as e,n as pe,v as fe,p as D,t as m,F as x,k as O,j as F,h as f,q as N,s as H,x as U,e as R,i as Q,y as ue,r as S,b as E,l as M,w as j,g as X,z as me,A as ge}from"./app-33ff2348.js";import{u as J}from"./MultiSelect-098c9700.js";import{D as ve}from"./DevicesBreadcrumbs-d681eff4.js";import{D as he}from"./DevicesForm-7c18cb71.js";import{P as be}from"./PageHeader-681f21f0.js";import{S as we}from"./SideDrawer-b60c3507.js";import{u as ye}from"./ViewFunctions-c2f77989.js";/* empty css            */import"./DeviceCategoryField-f0a17dc6.js";const ke={props:{pagename:{type:String,default:"rConfig"}},setup(_,{emit:s}){const n=v(null),t=v(""),C=v(null),p=v(!1),o=v(null),g=v(null),w=v(null),r=v(!1),h=v(null),i=v(null),c=v(!1),d=v(null),k=v(null),b=v(!1),I=v(null),{results:A,isLoading:K}=J("tags"),{results:z,isLoading:W}=J("categories"),{results:q,isLoading:Y}=J("vendors"),{results:Z,isLoading:$}=J("get-device-models");P(o,y=>p.value=!1),P(w,y=>r.value=!1),P(i,y=>c.value=!1),P(k,y=>b.value=!1);function ee(y,L){var T={[y]:L};y==="status"&&(C.value=L);let G=JSON.stringify(T);s("searchInput",G)}function te(y){s("openDrawer",y)}function se(y){s("searchInput",y.target.value)}function oe(){s("showEditColumns")}function ae(y,L,T){y==="category"&&(g.value=T,h.value=!1,d.value=!1,I.value=!1,p.value=!1),y==="tag"&&(h.value=T,g.value=!1,d.value=!1,I.value=!1,r.value=!1),y==="vendor"&&(d.value=T,g.value=!1,h.value=!1,I.value=!1,c.value=!1),y==="device_model"&&(I.value=T,g.value=!1,h.value=!1,d.value=!1,b.value=!1);var G={[y]:L};let ne=JSON.stringify(G);s("searchInput",ne)}function le(){I.value=!1,g.value=!1,h.value=!1,d.value=!1,C.value=null,t.value="",s("searchInput",t.value)}return{activeStatus:C,clear:le,filterSelect:ee,handleInput:se,openDrawer:te,search:n,searchInput:t,showEditColumns:oe,setFilter:ae,tags:A,tagsIsLoading:K,cats:z,catsIsLoading:W,vendors:q,vendorsIsLoading:Y,models:Z,modelsIsLoading:$,showCatFilteroptions:p,clickOutsidetarget1:o,selectedCatName:g,clickOutsidetarget2:w,showTagFilteroptions:r,selectedTagname:h,clickOutsidetarget3:i,showVendorFilteroptions:c,selectedVendorname:d,clickOutsidetarget4:k,showModelFilteroptions:b,selectedModelname:I}}},u=_=>(H("data-v-e5588839"),_=_(),U(),_),Ce={class:"pf-c-toolbar"},Se={class:"pf-c-toolbar__content"},De={class:"pf-c-toolbar__content-section pf-m-nowrap"},xe={class:"pf-c-toolbar__group pf-m-toggle-group pf-m-show"},Oe={class:"pf-c-toolbar__item pf-m-search-filter"},Ie={class:"pf-c-search-input"},Ne={class:"pf-c-search-input__bar"},Me={class:"pf-c-search-input__text"},Te=u(()=>e("span",{class:"pf-c-search-input__icon"},[e("i",{class:"fas fa-search fa-fw","aria-hidden":"true"})],-1)),Fe={class:"pf-c-search-input__utilities"},Ee={class:"pf-c-search-input__clear"},Pe=u(()=>e("i",{class:"fas fa-times fa-fw","aria-hidden":"true"},null,-1)),Ve=[Pe],Le=u(()=>e("hr",{class:"pf-c-divider pf-m-vertical pf-m-hidden pf-m-visible-on-lg"},null,-1)),Je={class:"pf-c-toolbar__group pf-m-filter-group pf-m-hidden pf-m-visible-on-lg"},Re={class:"pf-c-toolbar__item"},je={class:"pf-c-select pf-m-expanded",ref:"clickOutsidetarget1"},Be=u(()=>e("span",{hidden:""},"Choose one",-1)),Ae={class:"pf-c-select__toggle-wrapper"},Ke=["textContent"],ze=u(()=>e("span",{class:"pf-c-select__toggle-arrow"},[e("i",{class:"fas fa-caret-down","aria-hidden":"true"})],-1)),We={key:0,class:"pf-c-select__menu multi-select-dropdown-overflow",role:"listbox"},qe=["onClick"],Ge={key:0,class:"pf-c-select__menu-item-icon"},He=u(()=>e("i",{class:"fas fa-check","aria-hidden":"true"},null,-1)),Ue=[He],Qe={class:"pf-c-toolbar__item"},Xe={class:"pf-c-select pf-m-expanded",ref:"clickOutsidetarget2"},Ye=u(()=>e("span",{hidden:""},"Choose one",-1)),Ze={class:"pf-c-select__toggle-wrapper"},$e=["textContent"],et=u(()=>e("span",{class:"pf-c-select__toggle-arrow"},[e("i",{class:"fas fa-caret-down","aria-hidden":"true"})],-1)),tt={key:0,class:"pf-c-select__menu multi-select-dropdown-overflow",role:"listbox"},st=["onClick"],ot={key:0,class:"pf-c-select__menu-item-icon"},at=u(()=>e("i",{class:"fas fa-check","aria-hidden":"true"},null,-1)),lt=[at],nt={class:"pf-c-toolbar__item"},it={class:"pf-c-select pf-m-expanded",ref:"clickOutsidetarget3"},ct=u(()=>e("span",{hidden:""},"Choose one",-1)),dt={class:"pf-c-select__toggle-wrapper"},rt=["textContent"],_t=u(()=>e("span",{class:"pf-c-select__toggle-arrow"},[e("i",{class:"fas fa-caret-down","aria-hidden":"true"})],-1)),pt={key:0,class:"pf-c-select__menu multi-select-dropdown-overflow",role:"listbox"},ft=["onClick"],ut={key:0,class:"pf-c-select__menu-item-icon"},mt=u(()=>e("i",{class:"fas fa-check","aria-hidden":"true"},null,-1)),gt=[mt],vt={class:"pf-c-toolbar__item"},ht={class:"pf-c-select pf-m-expanded",ref:"clickOutsidetarget4"},bt=u(()=>e("span",{hidden:""},"Choose one",-1)),wt={class:"pf-c-select__toggle-wrapper"},yt=["textContent"],kt=u(()=>e("span",{class:"pf-c-select__toggle-arrow"},[e("i",{class:"fas fa-caret-down","aria-hidden":"true"})],-1)),Ct={key:0,class:"pf-c-select__menu multi-select-dropdown-overflow",role:"listbox"},St=["onClick"],Dt={key:0,class:"pf-c-select__menu-item-icon"},xt=u(()=>e("i",{class:"fas fa-check","aria-hidden":"true"},null,-1)),Ot=[xt],It=u(()=>e("hr",{class:"pf-c-divider pf-m-vertical"},null,-1)),Nt=u(()=>e("i",{class:"fas fa-columns","aria-hidden":"true"},null,-1)),Mt=u(()=>e("span",{class:"pf-u-display-none pf-u-display-inline-block-on-lg"},null,-1)),Tt=[Nt,Mt],Ft=u(()=>e("hr",{class:"pf-c-divider pf-m-vertical"},null,-1)),Et={class:"pf-c-toolbar__group pf-m-icon-button-group"},Pt={class:"pf-c-toolbar__item"},Vt={class:"pf-c-toolbar__item"},Lt={class:"pf-c-toolbar__item"},Jt={class:"pf-c-toolbar__item pf-m-pagination"},Rt={class:"pf-c-overflow-menu",id:"-overflow-menu"},jt={class:"pf-c-overflow-menu__content pf-u-display-none pf-u-display-flex-on-lg"},Bt={class:"pf-c-overflow-menu__group pf-m-button-group"},At={class:"pf-c-overflow-menu__item"},Kt=u(()=>e("div",{class:"pf-c-toolbar__expandable-content pf-m-hidden",id:"-expandable-content",hidden:""},null,-1)),zt={key:0,class:"pf-c-toolbar__content pf-m-chip-container"},Wt={class:"pf-c-toolbar__group"},qt={class:"pf-c-toolbar__item pf-m-chip-group"},Gt={class:"pf-c-chip-group pf-m-category"},Ht={class:"pf-c-chip-group__main"},Ut={class:"pf-c-chip-group__label","aria-hidden":"true"},Qt={key:0},Xt={key:1},Yt={key:2},Zt={key:3},$t={class:"pf-c-chip-group__list",role:"list"},es={key:0,class:"pf-c-chip-group__list-item"},ts={class:"pf-c-chip"},ss={class:"pf-c-chip__text"},os=u(()=>e("i",{class:"fas fa-times","aria-hidden":"true"},null,-1)),as=[os],ls={key:1,class:"pf-c-chip-group__list-item"},ns={class:"pf-c-chip"},is={class:"pf-c-chip__text"},cs=u(()=>e("i",{class:"fas fa-times","aria-hidden":"true"},null,-1)),ds=[cs],rs={key:2,class:"pf-c-chip-group__list-item"},_s={class:"pf-c-chip"},ps={class:"pf-c-chip__text"},fs=u(()=>e("i",{class:"fas fa-times","aria-hidden":"true"},null,-1)),us=[fs],ms={key:3,class:"pf-c-chip-group__list-item"},gs={class:"pf-c-chip"},vs={class:"pf-c-chip__text"},hs=u(()=>e("i",{class:"fas fa-times","aria-hidden":"true"},null,-1)),bs=[hs],ws={class:"pf-c-toolbar__item"};function ys(_,s,n,t,C,p){return a(),l("div",Ce,[e("div",Se,[e("div",De,[e("div",xe,[e("div",Oe,[e("div",Ie,[e("div",Ne,[e("span",Me,[Te,pe(e("input",{class:"pf-c-search-input__text-input",type:"text",placeholder:"Type to Search","aria-label":"Type to Search","onUpdate:modelValue":s[0]||(s[0]=o=>t.searchInput=o),onInput:s[1]||(s[1]=(...o)=>t.handleInput&&t.handleInput(...o)),ref:"search",autocomplete:"off"},null,544),[[fe,t.searchInput]])]),e("span",Fe,[e("span",Ee,[e("button",{class:"pf-c-button pf-m-plain",type:"button","aria-label":"Clear",onClick:s[2]||(s[2]=(...o)=>t.clear&&t.clear(...o))},Ve)])])])])])]),Le,e("div",Je,[e("div",Re,[e("div",je,[Be,e("button",{class:"pf-c-select__toggle",type:"button","aria-haspopup":"true","aria-expanded":"false",onClick:s[3]||(s[3]=D(o=>t.showCatFilteroptions=!t.showCatFilteroptions,["prevent"]))},[e("div",Ae,[e("span",{class:"pf-c-select__toggle-text",textContent:m(t.selectedCatName?t.selectedCatName:"Categories")},null,8,Ke)]),ze]),t.showCatFilteroptions||""?(a(),l("ul",We,[(a(!0),l(x,null,O(t.cats,o=>(a(),l("li",{role:"presentation",key:o},[e("button",{class:"pf-c-select__menu-item pf-m-selected",role:"option",onClick:D(g=>t.setFilter("category",o.id,o.categoryName),["prevent"])},[F(m(o.categoryName)+" ",1),t.selectedCatName===o.categoryName?(a(),l("span",Ge,Ue)):f("",!0)],8,qe)]))),128))])):f("",!0)],512)]),e("div",Qe,[e("div",Xe,[Ye,e("button",{class:"pf-c-select__toggle",type:"button","aria-haspopup":"true","aria-expanded":"false",onClick:s[4]||(s[4]=D(o=>t.showTagFilteroptions=!t.showTagFilteroptions,["prevent"]))},[e("div",Ze,[e("span",{class:"pf-c-select__toggle-text",textContent:m(t.selectedTagname?t.selectedTagname:"Tag")},null,8,$e)]),et]),t.showTagFilteroptions||""?(a(),l("ul",tt,[(a(!0),l(x,null,O(t.tags,o=>(a(),l("li",{role:"presentation",key:o},[e("button",{class:"pf-c-select__menu-item pf-m-selected",role:"option",onClick:D(g=>t.setFilter("tag",o.id,o.tagname),["prevent"])},[F(m(o.tagname)+" ",1),t.selectedTagname===o.tagname?(a(),l("span",ot,lt)):f("",!0)],8,st)]))),128))])):f("",!0)],512)]),e("div",nt,[e("div",it,[ct,e("button",{class:"pf-c-select__toggle",type:"button","aria-haspopup":"true","aria-expanded":"false",onClick:s[5]||(s[5]=D(o=>t.showVendorFilteroptions=!t.showVendorFilteroptions,["prevent"]))},[e("div",dt,[e("span",{class:"pf-c-select__toggle-text",textContent:m(t.selectedVendorname?t.selectedVendorname:"Vendor")},null,8,rt)]),_t]),t.showVendorFilteroptions||""?(a(),l("ul",pt,[(a(!0),l(x,null,O(t.vendors,o=>(a(),l("li",{role:"presentation",key:o},[e("button",{class:"pf-c-select__menu-item pf-m-selected",role:"option",onClick:D(g=>t.setFilter("vendor",o.id,o.vendorName),["prevent"])},[F(m(o.vendorName)+" ",1),t.selectedVendorname===o.vendorName?(a(),l("span",ut,gt)):f("",!0)],8,ft)]))),128))])):f("",!0)],512)]),e("div",vt,[e("div",ht,[bt,e("button",{class:"pf-c-select__toggle",type:"button","aria-haspopup":"true","aria-expanded":"false",onClick:s[6]||(s[6]=D(o=>t.showModelFilteroptions=!t.showModelFilteroptions,["prevent"]))},[e("div",wt,[e("span",{class:"pf-c-select__toggle-text",textContent:m(t.selectedModelname?t.selectedModelname:"Model")},null,8,yt)]),kt]),t.showModelFilteroptions||""?(a(),l("ul",Ct,[(a(!0),l(x,null,O(t.models,o=>(a(),l("li",{role:"presentation",key:o},[e("button",{class:"pf-c-select__menu-item pf-m-selected",role:"option",onClick:D(g=>t.setFilter("device_model",o,o),["prevent"])},[F(m(o)+" ",1),t.selectedModelname===o?(a(),l("span",Dt,Ot)):f("",!0)],8,St)]))),128))])):f("",!0)],512)])]),It,e("button",{class:"pf-c-button pf-m-control",type:"button",alt:"Edit Columns",title:"Edit Columns",onClick:s[7]||(s[7]=o=>t.showEditColumns())},Tt),Ft,e("div",Et,[e("div",Pt,[e("button",{class:"pf-c-button pf-m-plain",type:"button",onClick:s[8]||(s[8]=D(o=>t.clear(),["prevent"]))},[e("i",{class:N(["fas fa-expand-arrows-alt",t.activeStatus===null?"statusActive":"statusInactive"]),alt:"Show all devices",title:"Show all devices"},null,2)])]),e("div",Vt,[e("button",{class:"pf-c-button pf-m-plain",type:"button",onClick:s[9]||(s[9]=D(o=>t.filterSelect("status","1"),["prevent"]))},[e("i",{class:N(["fas fa-check-circle pf-u-success-color-100",t.activeStatus=="1"?"statusActive":"statusInactive"]),alt:"Show up devices",title:"Show up devices"},null,2)])]),e("div",Lt,[e("button",{class:"pf-c-button pf-m-plain",type:"button",onClick:s[10]||(s[10]=D(o=>t.filterSelect("status","0"),["prevent"]))},[e("i",{class:N(["fas fa-exclamation-triangle pf-u-warning-color-100",t.activeStatus=="0"?"statusActive":"statusInactive"]),alt:"Show down devices",title:"Show down devices"},null,2)])])]),e("div",Jt,[e("div",Rt,[e("div",jt,[e("div",Bt,[e("div",At,[e("button",{class:"pf-c-button pf-m-primary",type:"button",onClick:s[11]||(s[11]=o=>t.openDrawer(0))}," New "+m(n.pagename),1)])])])])])]),Kt]),t.selectedCatName||t.selectedModelname||t.selectedVendorname||t.selectedTagname?(a(),l("div",zt,[e("div",Wt,[e("div",qt,[e("div",Gt,[e("div",Ht,[e("span",Ut,[F("Filter "),t.selectedCatName?(a(),l("span",Qt,"category: ")):f("",!0),t.selectedModelname?(a(),l("span",Xt,"model: ")):f("",!0),t.selectedVendorname?(a(),l("span",Yt,"vendor: ")):f("",!0),t.selectedTagname?(a(),l("span",Zt,"tag: ")):f("",!0)]),e("ul",$t,[t.selectedCatName?(a(),l("li",es,[e("div",ts,[e("span",ss,m(t.selectedCatName),1),e("button",{class:"pf-c-button pf-m-plain",type:"button",onClick:s[12]||(s[12]=o=>t.clear())},as)])])):f("",!0),t.selectedModelname?(a(),l("li",ls,[e("div",ns,[e("span",is,m(t.selectedModelname),1),e("button",{class:"pf-c-button pf-m-plain",type:"button",onClick:s[13]||(s[13]=o=>t.clear())},ds)])])):f("",!0),t.selectedVendorname?(a(),l("li",rs,[e("div",_s,[e("span",ps,m(t.selectedVendorname),1),e("button",{class:"pf-c-button pf-m-plain",type:"button",onClick:s[14]||(s[14]=o=>t.clear())},us)])])):f("",!0),t.selectedTagname?(a(),l("li",ms,[e("div",gs,[e("span",vs,m(t.selectedTagname),1),e("button",{class:"pf-c-button pf-m-plain",type:"button",onClick:s[15]||(s[15]=o=>t.clear())},bs)])])):f("",!0)])])])])]),e("div",ws,[e("button",{class:"pf-c-button pf-m-link pf-m-inline",type:"button",onClick:s[16]||(s[16]=o=>t.clear())}," Clear all filters ")])])):f("",!0)])}const ks=B(ke,[["render",ys],["__scopeId","data-v-e5588839"]]);const Cs={components:{DevicesDataTableToolbar:ks,DataTableSpinner:_e,DataTableEmptyState:ie,DataTablePaginate:ce},props:{pagename:{type:String},tabledata:{type:Object,required:[]},newBtnEnabled:{type:Boolean,default:!0},editBtnEnabled:{type:Boolean,default:!0}},emits:["openDrawer","deleteRow","pagechanged","showEditColumnsModal"],setup(_,{emit:s}){const n=R({pageParams:{page:1,perpage:10,filters:null,sortby:null,sortOrder:"desc"},sortIcon:{base:"fas fa-arrows-alt-v",is:"fa-sort",up:"fas fa-long-arrow-alt-up",down:"fas fa-long-arrow-alt-down"}}),t=v(n.sortIcon.base),C=v(0),{setupResizableTable:p}=de(),o=Q("formatTime");ue(()=>{_.tabledata.isLoading||p()});function g(k,b=!1){s("openDrawer",{id:k,isClone:b})}function w(k){s("deleteRow",k)}function r(){n.pageParams.filters="",s("pagechanged",n.pageParams)}function h(k){n.pageParams.filters=k,s("pagechanged",n.pageParams)}function i(k){n.pageParams.page=k.page,n.pageParams.per_page=k.per_page,s("pagechanged",n.pageParams)}function c(k,b){t.value=n.pageParams.sortOrder==="desc"?n.sortIcon.up:n.sortIcon.down,C.value=b,n.pageParams.sortby=k,n.pageParams.sortOrder=n.pageParams.sortOrder==="desc"?"asc":"desc",s("pagechanged",n.pageParams)}function d(){s("showEditColumnsModal")}return{addFilters:h,clearFilters:r,data:n,deleteRow:w,formatTime:o,isSorted:C,openDrawer:g,pageChanged:i,showEditColumnsModal:d,sortBy:c,sortIcon:t}}},V=_=>(H("data-v-2be6d95c"),_=_(),U(),_),Ss={class:"pf-c-drawer__content pf-m-no-background"},Ds={class:"pf-c-drawer__body pf-m-padding"},xs={class:"pf-c-card"},Os={class:"pf-c-table pf-m-compact pf-m-grid-lg",role:"grid",id:"resizeMe"},Is={role:"row",id:"headerRow"},Ns={key:0},Ms=["onClick"],Ts={class:"pf-c-table__button-content"},Fs={class:"pf-c-table__text"},Es={class:"pf-c-table__sort-indicator"},Ps=V(()=>e("th",{class:"pf-c-table__icon"},"Actions",-1)),Vs={key:1,role:"rowgroup"},Ls=["data-label"],Js={key:0},Rs={key:1},js={key:0},Bs={key:0,class:"pf-u-default-color-300 pf-u-text-wrap"},As={key:1},Ks={key:1,class:"pf-u-text-break-word"},zs={key:2},Ws=["alt","title"],qs=["href"],Gs={key:3},Hs={role:"cell","data-label":"Actions",class:""},Us=V(()=>e("span",{class:"pf-c-button__icon pf-m-start"},[e("i",{class:"fas fa-search","aria-hidden":"true"})],-1)),Qs=["onClick"],Xs=V(()=>e("span",{class:"pf-c-button__icon pf-m-start"},[e("i",{class:"fas fa-edit","aria-hidden":"true"})],-1)),Ys=[Xs],Zs=["onClick"],$s=V(()=>e("span",{class:"pf-c-button__icon pf-m-start"},[e("i",{class:"fas fa-copy","aria-hidden":"true"})],-1)),eo=[$s],to=["onClick"],so=V(()=>e("span",{class:"pf-c-button__icon pf-m-start"},[e("i",{class:"fas fa-trash","aria-hidden":"true"})],-1)),oo=[so];function ao(_,s,n,t,C,p){const o=S("devices-data-table-toolbar"),g=S("data-table-spinner"),w=S("router-link"),r=S("data-table-empty-state"),h=S("data-table-paginate");return a(),l("div",Ss,[e("div",Ds,[e("div",xs,[E(o,{pagename:n.pagename,onSearchInput:s[0]||(s[0]=i=>t.addFilters(i)),onOpenDrawer:s[1]||(s[1]=i=>t.openDrawer(i)),onShowEditColumns:s[2]||(s[2]=i=>t.showEditColumnsModal()),newBtnEnabled:n.newBtnEnabled},null,8,["pagename","newBtnEnabled"]),e("table",Os,[e("thead",null,[e("tr",Is,[(a(!0),l(x,null,O(n.tabledata.selectedColumns,(i,c)=>(a(),l("th",{key:i.name,class:N(["pf-c-table__sort pf-c-table__icon",[t.isSorted===c?"pf-m-selected":"",i.hideOnSmall?"pf-m-hidden pf-m-visible-on-sm":""]])},[i.sortable?f("",!0):(a(),l("span",Ns,m(i.label),1)),i.sortable?(a(),l("button",{key:1,class:"pf-c-table__button",onClick:d=>t.sortBy(i.key,c)},[e("div",Ts,[e("span",Fs,m(i.label),1),e("span",Es,[e("i",{class:N(t.isSorted===c?t.sortIcon:t.data.sortIcon.base)},null,2)])])],8,Ms)):f("",!0)],2))),128)),Ps])]),n.tabledata.isLoading?(a(),M(g,{key:0})):f("",!0),n.tabledata.data.total>0||n.tabledata.isLoading?(a(),l("tbody",Vs,[(a(!0),l(x,null,O(n.tabledata.data.data,i=>(a(),l("tr",{role:"row",key:i.name},[(a(!0),l(x,null,O(n.tabledata.selectedColumns,c=>(a(),l("td",{key:c.label,role:"cell","data-label":c.label,class:N((c.hideOnSmall,c.key==="tag"?"pf-m-wrap":"pf-m-truncate"))},[c.isRelationShip===!0?(a(),l("div",Js,[(a(!0),l(x,null,O(i[c.key],d=>(a(),l("div",{key:d.id},m(d[c.relationshipKey]),1))),128))])):(a(),l("div",Rs,[c.isStatusIcon?(a(),l("div",js,[i.job_status!=null?(a(),l("span",Bs,m(i.job_status)+"...",1)):(a(),l("i",{key:1,class:N(i[c.key]=="1"?"fa fa-check-circle pf-u-success-color-100 ":"fa fa-exclamation-triangle pf-u-warning-color-100")},null,2))])):(a(),l("span",As,[c.isLink?(a(),M(w,{key:0,class:"Card__link alink",to:"/device/view/"+i.id},{default:j(()=>[F(m(i[c.key]),1)]),_:2},1032,["to"])):c.key==="last_config"&&i.last_config!=null?(a(),l("span",Ks,m(t.formatTime(i.last_config.created_at)),1)):c.key==="tag"?(a(),l("span",zs,[(a(!0),l(x,null,O(i[c.key],d=>(a(),l("div",{class:"pf-c-chip",key:d.id},[e("span",{class:"pf-c-chip__text",alt:d.tagDescription,title:d.tagDescription},[e("a",{href:/tags/+d.tagname},m(d.tagname),9,qs)],8,Ws)]))),128))])):["device_name","last_config"].includes(c.key)?f("",!0):(a(),l("span",Gs,m(i[c.key]),1))]))]))],10,Ls))),128)),e("td",Hs,[e("div",null,[E(w,{class:"pf-c-button pf-m-link pf-m-small",type:"button",to:"/device/view/"+i.id},{default:j(()=>[Us]),_:2},1032,["to"]),e("button",{class:"pf-c-button pf-m-link pf-m-small",type:"button",onClick:c=>t.openDrawer(i.id),alt:"Edit",title:"Edit"},Ys,8,Qs),e("button",{class:"pf-c-button pf-m-link pf-m-link-secondary pf-m-small",type:"button",onClick:c=>t.openDrawer(i.id,!0),alt:"Clone",title:"Clone"},eo,8,Zs),e("button",{class:"pf-c-button pf-m-link pf-m-danger pf-m-small",type:"button",onClick:c=>t.deleteRow(i.id),alt:"Delete",title:"Delete"},oo,8,to)])])]))),128))])):n.tabledata.isLoading?f("",!0):(a(),M(r,{key:2,onClear:t.clearFilters},null,8,["onClear"]))]),E(h,{from:n.tabledata.data.from,to:n.tabledata.data.to,total:n.tabledata.data.total,current_page:n.tabledata.data.current_page,last_page:n.tabledata.data.last_page,onPagechanged:s[3]||(s[3]=i=>t.pageChanged(i))},null,8,["from","to","total","current_page","last_page"])])])])}const lo=B(Cs,[["render",ao],["__scopeId","data-v-2be6d95c"]]),no={props:{rows:{type:Array,default:()=>[]}},emits:["close","saveColumns"],setup(_,{emit:s}){const n=v(null);Q("create-notification");const t=R(_.rows.filter(r=>r.columnSelected).map(r=>({...r})));P(n,r=>w()),X(()=>{});function C(r){var h=_.rows.map(c=>c.id).indexOf(r);if(_.rows[h].columnSelected=!_.rows[h].columnSelected,t.some(c=>c.id===r)){var i=t.map(c=>c.id).indexOf(r);i>=0&&t.splice(i,1)}else t.push(_.rows[r])}function p(){t.splice(0),_.rows.forEach(r=>{r.columnSelected=!0,t.push(r)})}function o(){g(),localStorage.setItem("rconfig.columnsOrig",JSON.stringify(_.rows)),localStorage.setItem("rconfig.columns",JSON.stringify(t)),s("saveColumns"),w()}function g(){t.sort((r,h)=>r.id-h.id)}const w=()=>{s("close")};return{selectAll:p,close:w,saveColumns:o,toggleColumn:C,selectedRows:t}}},io={class:"pf-c-backdrop"},co={class:"pf-l-bullseye"},ro={class:"pf-c-modal-box pf-m-sm",ref:"clickOutsidetarget"},_o=e("i",{class:"fas fa-times","aria-hidden":"true"},null,-1),po=[_o],fo={class:"pf-c-modal-box__header"},uo=e("h1",{class:"pf-c-modal-box__title",id:"modal-title"},"Manage columns",-1),mo={class:"pf-c-modal-box__description"},go={class:"pf-c-content"},vo=e("p",null,"Selected columns will be displayed in the table.",-1),ho={class:"pf-c-modal-box__body",id:"modal-description"},bo={class:"pf-c-data-list pf-m-compact pf-c-droppable",role:"list"},wo=["id"],yo={class:"pf-c-data-list__item-row"},ko={class:"pf-c-data-list__item-control"},Co={class:"pf-c-data-list__check"},So=["checked","onChange"],Do={class:"pf-c-data-list__item-content"},xo={class:"pf-c-data-list__cell"},Oo={id:"table-manage-columns-data-list-draggable-item-1"},Io={class:"pf-c-modal-box__footer"};function No(_,s,n,t,C,p){return a(),l("div",io,[e("div",co,[e("div",ro,[e("button",{class:"pf-c-button pf-m-plain",type:"button","aria-label":"Close dialog",onClick:s[0]||(s[0]=(...o)=>t.close&&t.close(...o))},po),e("header",fo,[uo,e("div",mo,[e("div",go,[vo,e("button",{class:"pf-c-button pf-m-link pf-m-inline",type:"button",onClick:s[1]||(s[1]=(...o)=>t.selectAll&&t.selectAll(...o))},"Select all")])])]),e("div",ho,[e("ul",bo,[(a(!0),l(x,null,O(n.rows,(o,g)=>(a(),l("li",{class:"pf-c-data-list__item pf-m-draggable",key:o.id,id:o.id},[e("div",yo,[e("div",ko,[e("div",Co,[e("input",{type:"checkbox",name:"table-manage-columns-data-list-draggable-check-action-check1",checked:o.columnSelected===!0,onChange:w=>t.toggleColumn(o.id)},null,40,So)])]),e("div",Do,[e("div",xo,[e("span",Oo,m(o.label),1)])])])],8,wo))),128))])]),e("footer",Io,[e("button",{class:"pf-c-button pf-m-primary",type:"button",onClick:s[2]||(s[2]=(...o)=>t.saveColumns&&t.saveColumns(...o))},"Save"),e("button",{class:"pf-c-button pf-m-link",type:"button",onClick:s[3]||(s[3]=(...o)=>t.close&&t.close(...o))},"Cancel")])],512)])])}const Mo=B(no,[["render",No]]),To={components:{DevicesForm:he,PageHeader:be,DevicesBreadcrumbs:ve,DataTableDevices:lo,SideDrawer:we,DeleteModal:re,ModalDeviceColumnSelect:Mo},setup(){const{globalState:_}=me(),s=ge(),n=v(s.path),t=v(s.params.id),C=v(!1),p=R({editid:0,isClone:!1,pagename:"Devices",pagedesc:"All devices",pagenamesingle:"Device",modelName:"devices",openDrawerState:!1,drawerOuterWidth:"pf-m-width-75-on-xl pf-m-width-100 ",showDeleteModal:!1,sideDrawerComponentKey:1,pageOptionsState:{page:1,per_page:10},modelObject:{device_name:"",device_ip:"",device_vendor:"",device_model:"",device_category_id:"",device_tags:"",device_username:"",device_password:"",device_cred_id:0,device_template:"",device_main_prompt:"",job_status:null}}),{models:o,isLoading:g,dataTablePageChanged:w,openDrawer:r,closeDrawerState:h,deleteRow:i,formSubmitted:c,confirmDelete:d,destroyModel:k}=ye(p,p.modelName,p.modelObject),b=R({selectedColumns:[],headers:[{id:0,key:"id",label:"ID",sortable:!0,columnSelected:!0},{id:1,key:"status",label:"Status",sortable:!0,isStatusIcon:!0,columnSelected:!0},{id:2,key:"device_name",label:"Device Name",sortable:!0,isLink:!0,columnSelected:!0},{id:3,key:"device_ip",label:"IP Address",sortable:!1,columnSelected:!0},{id:4,key:"vendor",label:"Vendor",sortable:!1,isRelationShip:!0,relationshipKey:"vendorName",columnSelected:!0},{id:5,key:"device_model",label:"Model",sortable:!1,columnSelected:!0},{id:6,key:"config_good_count",label:"Config Count",sortable:!1,hideOnSmall:!0,columnSelected:!0},{id:7,key:"config_bad_count",label:"Config Failures",sortable:!1,hideOnSmall:!0,columnSelected:!0},{id:8,key:"last_config",label:"Last Config",sortable:!1,hideOnSmall:!0,columnSelected:!0},{id:9,key:"tag",label:"Tags",sortable:!1,hideOnSmall:!0,columnSelected:!0}],data:o,isLoading:g});X(()=>{I(),n.value==="/devices/status/1"&&(p.pageOptionsState.filters=JSON.stringify({status:"1"})),n.value==="/devices/status/0"&&(p.pageOptionsState.filters=JSON.stringify({status:"0"})),n.value==="/devices/creds/"+t.value&&(p.pageOptionsState.filters=JSON.stringify({device_cred_id:t.value})),n.value==="/devices/tag/"+t.value&&(p.pageOptionsState.filters=JSON.stringify({tag:t.value})),n.value==="/devices/category/"+t.value&&(p.pageOptionsState.filters=JSON.stringify({category:t.value})),w(p.pageOptionsState)});function I(){localStorage.getItem("rconfig.columns")===null||localStorage.getItem("rconfig.columnsOrig")===null?(b.selectedColumns=b.headers,localStorage.getItem("rconfig.columnsOrig")!=null&&b.headers.length!=JSON.parse(localStorage.getItem("rconfig.columnsOrig")).length?(localStorage.setItem("rconfig.columnsOrig",JSON.stringify(b.headers)),localStorage.setItem("rconfig.columns",JSON.stringify(b.headers))):(A(),localStorage.setItem("rconfig.columns",JSON.stringify(b.selectedColumns)),localStorage.setItem("rconfig.columnsOrig",JSON.stringify(b.headers)))):(b.selectedColumns=JSON.parse(localStorage.getItem("rconfig.columns")||"[]"),b.headers=JSON.parse(localStorage.getItem("rconfig.columnsOrig")||"[]"))}function A(){b.selectedColumns.sort((W,q)=>W.id-q.id)}function K(){I(),w(p.pageOptionsState)}function z(){p.sideDrawerComponentKey++,h()}return{closeDrawer:z,closeDrawerState:h,confirmDelete:d,dataTablePageChanged:w,deleteRow:i,destroyModel:k,formSubmitted:c,globalState:_,openDrawer:r,saveColumns:K,showEditColumnsModal:C,table:b,viewstate:p}}},Fo={class:"pf-c-page__main",tabindex:"-1"},Eo=e("div",{class:"pf-c-divider",role:"separator"},null,-1),Po={class:"pf-c-page__main-section pf-m-no-padding"},Vo={class:"pf-c-drawer__main"};function Lo(_,s,n,t,C,p){const o=S("devices-breadcrumbs"),g=S("page-header"),w=S("data-table-devices"),r=S("devices-form"),h=S("side-drawer"),i=S("delete-modal"),c=S("modal-device-column-select");return a(),l(x,null,[e("main",Fo,[E(g,{pagename:t.viewstate.pagename},{breadcrumbs:j(()=>[E(o)]),_:1},8,["pagename"]),Eo,e("section",Po,[e("div",{class:N(["pf-c-drawer",{"pf-m-expanded":t.viewstate.openDrawerState}])},[e("div",Vo,[E(w,{pagename:t.viewstate.pagenamesingle,tabledata:t.table,onPagechanged:t.dataTablePageChanged,onOpenDrawer:s[0]||(s[0]=d=>t.openDrawer(d)),onDeleteRow:s[1]||(s[1]=d=>t.deleteRow(d)),onShowEditColumnsModal:s[2]||(s[2]=d=>t.showEditColumnsModal=!0)},null,8,["pagename","tabledata","onPagechanged"]),(a(),M(h,{pagename:t.viewstate.pagenamesingle,drawerState:t.viewstate.openDrawerState,outerWidth:t.viewstate.drawerOuterWidth,editid:t.viewstate.editid,onCloseDrawer:s[5]||(s[5]=d=>t.closeDrawer()),key:t.viewstate.sideDrawerComponentKey},{form:j(()=>[(a(),M(r,{viewstate:t.viewstate,onCloseDrawer:s[3]||(s[3]=d=>t.closeDrawer()),onFormsubmitted:s[4]||(s[4]=d=>t.formSubmitted(d)),key:t.viewstate.sideDrawerComponentKey},null,8,["viewstate"]))]),_:1},8,["pagename","drawerState","outerWidth","editid"]))])],2)])]),t.viewstate.showDeleteModal?(a(),M(i,{key:0,editid:t.viewstate.editid,onCloseModal:s[6]||(s[6]=d=>t.viewstate.showDeleteModal=!1),onConfirmDelete:t.confirmDelete},null,8,["editid","onConfirmDelete"])):f("",!0),t.showEditColumnsModal?(a(),M(c,{key:1,onClose:s[7]||(s[7]=d=>t.showEditColumnsModal=!1),rows:t.table.headers,onSaveColumns:t.saveColumns},null,8,["rows","onSaveColumns"])):f("",!0)],64)}const Uo=B(To,[["render",Lo]]);export{Uo as default};
