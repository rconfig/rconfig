import{D as M,a as F,b as U,u as N,c as V}from"./DeleteModal-BW7zlagY.js";import{D as W}from"./DataTableSpinner-DEUZ5Lyn.js";import{a6 as E,F as C,H as S,aa as R,ac as j,s as m,o as s,c as l,a as n,b as v,w as P,r as z,Z as k,$ as B,i as I,t as D,v as g,f as y,d as T,J as x,a8 as H}from"./app-D2xsl8rd.js";import{P as K}from"./PageHeader-jQFlwj8n.js";import{S as q}from"./SideDrawer-DOD6jbif.js";import{U as J}from"./UsersForm-CvUtivOO.js";import{u as Y}from"./ViewFunctions-C6cuRbX0.js";/* empty css               */import"./LoadingSpinner-CKi4qMsx.js";const Z={components:{DataTableToolbar:M,DataTableSpinner:W,DataTableEmptyState:F,DataTablePaginate:U},props:{pagename:{type:String},tabledata:{type:Object,required:[]},searchInputDisabled:{type:Boolean,default:!0},newBtnEnabled:{type:Boolean,default:!0},backupDownloadBtnEnabled:{type:Boolean,default:!1},taskRunBtnEnabled:{type:Boolean,default:!1},editBtnEnabled:{type:Boolean,default:!0},rowViewBtnEnabled:{type:Boolean,default:!1}},emits:["openDrawer","deleteRow","pagechanged","showTaskRunConfirmModal","actionLink","viewAction"],setup(c,{emit:e}){const t=C({pageParams:{page:1,per_page:10,filters:null,sortby:null,sortOrder:"desc"},sortIcon:{base:"fas fa-arrows-alt-v",is:"fa-sort",up:"fas fa-long-arrow-alt-up",down:"fas fa-long-arrow-alt-down"}}),a=C({critical:"fas fa-exclamation-circle pf-u-danger-color-100",error:"fas fa-exclamation-circle pf-u-danger-color-100",warn:"fa fa-exclamation-triangle pf-u-warning-color-100",info:"fas fa-fw fa-info-circle pf-u-info-color-100",default:"fas fa-fw fa-info-circle pf-u-info-color-100"}),p=S(t.sortIcon.base),f=S(0);R("create-notification");const{setupResizableTable:u}=N(),_=R("formatTime");j(()=>{c.tabledata.isLoading||u()});function b(d,h=!1){e("openDrawer",{id:d,isClone:h})}function w(d){e("deleteRow",d)}function o(){t.pageParams.filters="",e("pagechanged",t.pageParams)}function r(d){t.pageParams.filters=d,e("pagechanged",t.pageParams)}function i(d){t.pageParams.page=d.page,t.pageParams.per_page=d.per_page,e("pagechanged",t.pageParams)}function O(d,h){p.value=t.pageParams.sortOrder==="desc"?t.sortIcon.up:t.sortIcon.down,f.value=h,t.pageParams.sortby=d,t.pageParams.sortOrder=t.pageParams.sortOrder==="desc"?"asc":"desc",e("pagechanged",t.pageParams)}function A(d){e("showTaskRunConfirmModal",d)}function L(d){e("viewAction",d),close()}return{activityLogIconTable:a,addFilters:r,clearFilters:o,data:t,deleteRow:w,emitShowTaskRunConfirmModal:A,formatTime:_,isSorted:f,openDrawer:b,pageChanged:i,sortBy:O,sortIcon:p,viewAction:L}}},G={class:"pf-c-drawer__content pf-m-no-background"},Q={class:"pf-c-drawer__body pf-m-padding"},X={class:"pf-c-card"},$={class:"pf-c-table pf-m-compact pf-m-grid-lg",role:"grid",id:"resizeMe"},ee={role:"row",id:"headerRow"},ae={key:0},te=["onClick"],ne={class:"pf-c-table__button-content"},oe={class:"pf-c-table__text"},se={class:"pf-c-table__sort-indicator"},re={key:1,role:"rowgroup"},le=["data-label"],ie={key:0},de={key:1,class:"pf-c-label__content"},ce={role:"cell","data-label":"Actions",class:"pf-m-fit-content"},fe=["onClick"],me=["onClick"];function pe(c,e,t,a,p,f){const u=m("data-table-toolbar"),_=m("data-table-spinner"),b=m("data-table-empty-state"),w=m("data-table-paginate");return s(),l("div",G,[n("div",Q,[n("div",X,[v(u,{pagename:t.pagename,onSearchInput:e[0]||(e[0]=o=>a.addFilters(o)),onOpenDrawer:e[1]||(e[1]=o=>a.openDrawer(o)),newBtnEnabled:t.newBtnEnabled,searchInputDisabled:t.searchInputDisabled},{customButtons:P(()=>[z(c.$slots,"customButtons")]),_:3},8,["pagename","newBtnEnabled","searchInputDisabled"]),n("table",$,[n("thead",null,[n("tr",ee,[(s(!0),l(k,null,B(t.tabledata.headers,(o,r)=>(s(),l("th",{key:o.name,class:I(["pf-m-truncate pf-c-table__sort pf-c-table__icon",a.isSorted===r?"pf-m-selected":""])},[o.sortable?g("",!0):(s(),l("span",ae,D(o.label),1)),o.sortable?(s(),l("button",{key:1,class:"pf-c-table__button",onClick:i=>a.sortBy(o.key,r)},[n("div",ne,[n("span",oe,D(o.label),1),n("span",se,[n("i",{class:I(a.isSorted===r?a.sortIcon:a.data.sortIcon.base)},null,2)])])],8,te)):g("",!0)],2))),128)),e[3]||(e[3]=n("th",{class:"pf-c-table__icon pf-m-fit-content",role:"columnheader",scope:"col"},"Actions",-1))])]),t.tabledata.isLoading?(s(),y(_,{key:0})):g("",!0),t.tabledata.data.total>0||t.tabledata.isLoading?(s(),l("tbody",re,[(s(!0),l(k,null,B(t.tabledata.data.data,o=>(s(),l("tr",{role:"row",key:o.name},[(s(!0),l(k,null,B(t.tabledata.headers,r=>(s(),l("td",{key:r.label,role:"cell","data-label":r.label},[r.key==="created_at"?(s(),l("div",ie,D(a.formatTime(o[r.key])),1)):(s(),l("span",de,D(o[r.key]),1))],8,le))),128)),n("td",ce,[n("div",null,[t.editBtnEnabled?(s(),l("button",{key:0,class:"pf-c-button pf-m-link pf-m-small",type:"button",onClick:r=>a.openDrawer(o.id),alt:"Edit",title:"Edit"},e[4]||(e[4]=[n("span",{class:"pf-c-button__icon pf-m-start"},[n("i",{class:"fas fa-edit","aria-hidden":"true"})],-1),T(" Edit ")]),8,fe)):g("",!0),n("button",{class:"pf-c-button pf-m-link pf-m-danger pf-m-small",type:"button",onClick:r=>a.deleteRow(o.id),alt:"Delete",title:"Delete"},e[5]||(e[5]=[n("span",{class:"pf-c-button__icon pf-m-start"},[n("i",{class:"fas fa-trash","aria-hidden":"true"})],-1),T(" Delete ")]),8,me)])])]))),128))])):t.tabledata.isLoading?g("",!0):(s(),y(b,{key:2,onClear:a.clearFilters},null,8,["onClear"]))]),v(w,{from:t.tabledata.data.from,to:t.tabledata.data.to,total:t.tabledata.data.total,current_page:t.tabledata.data.current_page,last_page:t.tabledata.data.last_page,onPagechanged:e[2]||(e[2]=o=>a.pageChanged(o))},null,8,["from","to","total","current_page","last_page"])])])])}const ue=E(Z,[["render",pe]]),ge={props:{},setup(c){const e=S(0),t=S(!0);x(()=>{if(localStorage.getItem("hideAdminWarningBanner")){e.value=1,t.value=!1;return}a()});function a(){axios.get("/api/users?page=1&perPage=10&filter=admin@domain.com&sortCol=&sortOrd=desc").then(f=>{f.data.data.length>0?e.value=0:e.value=1,t.value=!1})}function p(){localStorage.setItem("hideAdminWarningBanner",!0),e.value=1}return{isLoading:t,warningStatus:e,hideAdminWarningBanner:p}}},_e={key:0},be={key:0,class:"pf-c-alert pf-m-warning pf-m-inline","aria-label":"Inline warning alert"},we={class:"pf-c-alert__action"};function ve(c,e,t,a,p,f){return a.isLoading?g("",!0):(s(),l("div",_e,[a.warningStatus===0?(s(),l("div",be,[e[2]||(e[2]=n("div",{class:"pf-c-alert__icon"},[n("i",{class:"fas fa-fw fa-exclamation-triangle","aria-hidden":"true"})],-1)),e[3]||(e[3]=n("p",{class:"pf-c-alert__title"},[n("span",{class:"pf-screen-reader"},"Warning:"),T(" You have not removed the default admin user ")],-1)),n("div",we,[n("button",{onClick:e[0]||(e[0]=u=>a.hideAdminWarningBanner()),class:"pf-c-button pf-m-plain",type:"button","aria-label":"Close success alert: Success alert title"},e[1]||(e[1]=[n("i",{class:"fas fa-times","aria-hidden":"true"},null,-1)]))])])):g("",!0)]))}const ye=E(ge,[["render",ve]]),De={components:{UsersForm:J,PageHeader:K,DataTableUsers:ue,SideDrawer:q,DeleteModal:V,DefaultAdminUserWarningBanner:ye},setup(){const c=C({editid:0,pagename:"Users",pagedesc:"rConfig system users",pagenamesingle:"User",modelName:"users",openDrawerState:!1,showDeleteModal:!1,sideDrawerComponentKey:1,pageOptionsState:{page:1,per_page:10},modelObject:{name:"",email:"",username:"",created_at:""}}),t=H().params.userId,{models:a,isLoading:p,dataTablePageChanged:f,openDrawer:u,closeDrawerState:_,deleteRow:b,formSubmitted:w,confirmDelete:o,destroyModel:r}=Y(c,c.modelName,c.modelObject);x(()=>{f(c.pageOptionsState),t&&u({id:t})});const i=C({headers:[{key:"name",label:"Name",sortable:!0},{key:"username",label:"Username",sortable:!0},{key:"email",label:"Email",sortable:!0},{key:"created_at",label:"Created",sortable:!1}],data:a,isLoading:p});return{viewstate:c,dataTablePageChanged:f,openDrawer:u,closeDrawerState:_,deleteRow:b,confirmDelete:o,table:i,destroyModel:r,formSubmitted:w}}},ke={class:"pf-c-page__main",tabindex:"-1"},Ce={class:"pf-c-page__main-section pf-m-no-padding"},Se={class:"pf-c-drawer__main"};function he(c,e,t,a,p,f){const u=m("default-admin-user-warning-banner"),_=m("page-header"),b=m("data-table-users"),w=m("users-form"),o=m("side-drawer"),r=m("delete-modal");return s(),l(k,null,[n("main",ke,[v(u),v(_,{pagename:a.viewstate.pagename,desc:a.viewstate.pagedesc},null,8,["pagename","desc"]),e[7]||(e[7]=n("div",{class:"pf-c-divider",role:"separator"},null,-1)),n("section",Ce,[n("div",{class:I(["pf-c-drawer",{"pf-m-expanded":a.viewstate.openDrawerState}])},[n("div",Se,[v(b,{pagename:a.viewstate.pagenamesingle,tabledata:a.table,onPagechanged:e[0]||(e[0]=i=>a.dataTablePageChanged(i)),onOpenDrawer:e[1]||(e[1]=i=>a.openDrawer(i)),onDeleteRow:e[2]||(e[2]=i=>a.deleteRow(i))},null,8,["pagename","tabledata"]),(s(),y(o,{pagename:a.viewstate.pagenamesingle,drawerState:a.viewstate.openDrawerState,editid:a.viewstate.editid,onCloseDrawer:e[4]||(e[4]=i=>a.viewstate.openDrawerState=!1),key:a.viewstate.sideDrawerComponentKey},{subtext:P(()=>e[6]||(e[6]=[n("div",{class:"pf-l-flex__item"},"Please complete all fields",-1)])),form:P(()=>[(s(),y(w,{viewstate:a.viewstate,onCloseDrawer:a.closeDrawerState,onFormsubmitted:e[3]||(e[3]=i=>a.formSubmitted(i)),key:a.viewstate.editid},null,8,["viewstate","onCloseDrawer"]))]),_:1},8,["pagename","drawerState","editid"]))])],2)])]),a.viewstate.showDeleteModal?(s(),y(r,{key:0,editid:a.viewstate.editid,onCloseModal:e[5]||(e[5]=i=>a.viewstate.showDeleteModal=!1),onConfirmDelete:a.confirmDelete},null,8,["editid","onConfirmDelete"])):g("",!0)],64)}const Le=E(De,[["render",he]]);export{Le as default};
