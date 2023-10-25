import{D as A}from"./DataTableToolbar-66f7472b.js";import{D as x}from"./DataTableSpinner-dedf6af0.js";import{D as F,a as O,u as z}from"./DeleteModal-b3198555.js";import{_ as M,e as E,f as I,i as P,y as N,r as u,o as n,c as s,a as o,b as B,w as S,B as j,F as b,k as m,q as T,t as d,h as i,l as R,j as _}from"./app-33ff2348.js";const q={components:{DataTableToolbar:A,DataTableSpinner:x,DataTableEmptyState:F,DataTablePaginate:O},props:{pagename:{type:String},tabledata:{type:Object,required:[]},searchInputDisabled:{type:Boolean,default:!0},newBtnEnabled:{type:Boolean,default:!0},backupDownloadBtnEnabled:{type:Boolean,default:!1},taskRunBtnEnabled:{type:Boolean,default:!1},editBtnEnabled:{type:Boolean,default:!0},rowViewBtnEnabled:{type:Boolean,default:!1}},emits:["openDrawer","deleteRow","pagechanged","showTaskRunConfirmModal","actionLink","viewAction"],setup(p,{emit:c}){const t=E({pageParams:{page:1,per_page:10,filters:null,sortby:null,sortOrder:"desc"},sortIcon:{base:"fas fa-arrows-alt-v",is:"fa-sort",up:"fas fa-long-arrow-alt-up",down:"fas fa-long-arrow-alt-down"}}),l=E({critical:"fas fa-exclamation-circle pf-u-danger-color-100",error:"fas fa-exclamation-circle pf-u-danger-color-100",warn:"fa fa-exclamation-triangle pf-u-warning-color-100",info:"fas fa-fw fa-info-circle pf-u-info-color-100",default:"fas fa-fw fa-info-circle pf-u-info-color-100"}),k=I(t.sortIcon.base),y=I(0);P("create-notification");const{setupResizableTable:g}=z(),h=P("formatTime");N(()=>{p.tabledata.isLoading||g()});function w(r,D=!1){c("openDrawer",{id:r,isClone:D})}function v(r){c("deleteRow",r)}function C(){t.pageParams.filters="",c("pagechanged",t.pageParams)}function e(r){t.pageParams.filters=r,c("pagechanged",t.pageParams)}function a(r){t.pageParams.page=r.page,t.pageParams.per_page=r.per_page,c("pagechanged",t.pageParams)}function f(r,D){k.value=t.pageParams.sortOrder==="desc"?t.sortIcon.up:t.sortIcon.down,y.value=D,t.pageParams.sortby=r,t.pageParams.sortOrder=t.pageParams.sortOrder==="desc"?"asc":"desc",c("pagechanged",t.pageParams)}function L(r){c("showTaskRunConfirmModal",r)}function V(r){c("viewAction",r),close()}return{activityLogIconTable:l,addFilters:e,clearFilters:C,data:t,deleteRow:v,emitShowTaskRunConfirmModal:L,formatTime:h,isSorted:y,openDrawer:w,pageChanged:a,sortBy:f,sortIcon:k,viewAction:V}}},K={class:"pf-c-drawer__content pf-m-no-background"},G={class:"pf-c-drawer__body pf-m-padding"},H={class:"pf-c-card"},J={class:"pf-c-table pf-m-compact pf-m-grid-lg",role:"grid",id:"resizeMe"},Q={role:"row",id:"headerRow"},U={key:0},W=["onClick"],X={class:"pf-c-table__button-content"},Y={class:"pf-c-table__text"},Z={class:"pf-c-table__sort-indicator"},$=o("th",{class:"pf-c-table__icon pf-m-fit-content",role:"columnheader",scope:"col"},"Actions",-1),tt={key:1,role:"rowgroup"},et=["data-label"],at={key:0},nt={key:0},ot={key:1},st={key:2},lt={key:1},it=["onClick"],ct={key:2},rt=["onClick"],dt={key:3,title:"View devices",alt:"View devices"},ft={class:"pf-c-chip__text pf-u-font-size-md"},_t={key:4,class:"pf-c-label pf-u-pr-md pf-u-pl-md pf-m-purple"},pt={class:"pf-c-label__content"},ut={key:5},bt={key:0},mt={key:0,class:"pf-u-danger-color-100",id:"form-help-textinfo-helper","aria-live":"polite"},kt=o("span",{class:"pf-u-danger-color-100"},[o("i",{class:"fas fa-exclamation-circle","aria-hidden":"true"})],-1),yt=o("a",{href:"/commands",class:"alink"},"Configure",-1),gt={role:"cell","data-label":"Actions",class:"pf-m-fit-content"},ht=["href"],wt=o("span",{class:"pf-c-button__icon pf-m-start"},[o("i",{class:"fa fa-download","aria-hidden":"true"})],-1),vt=["onClick"],Ct=o("span",{class:"pf-c-button__icon pf-m-start"},[o("i",{class:"fa fa-play-circle","aria-hidden":"true"})],-1),Dt=["onClick"],Bt=o("span",{class:"pf-c-button__icon pf-m-start"},[o("i",{class:"fas fa-edit","aria-hidden":"true"})],-1),Tt=["onClick"],Et=o("span",{class:"pf-c-button__icon pf-m-start"},[o("i",{class:"fas fa-search","aria-hidden":"true"})],-1),It=["onClick"],Pt=o("span",{class:"pf-c-button__icon pf-m-start"},[o("i",{class:"fas fa-trash","aria-hidden":"true"})],-1);function St(p,c,t,l,k,y){const g=u("data-table-toolbar"),h=u("data-table-spinner"),w=u("router-link"),v=u("data-table-empty-state"),C=u("data-table-paginate");return n(),s("div",K,[o("div",G,[o("div",H,[B(g,{pagename:t.pagename,onSearchInput:c[0]||(c[0]=e=>l.addFilters(e)),onOpenDrawer:c[1]||(c[1]=e=>l.openDrawer(e)),newBtnEnabled:t.newBtnEnabled,searchInputDisabled:t.searchInputDisabled},{customButtons:S(()=>[j(p.$slots,"customButtons")]),_:3},8,["pagename","newBtnEnabled","searchInputDisabled"]),o("table",J,[o("thead",null,[o("tr",Q,[(n(!0),s(b,null,m(t.tabledata.headers,(e,a)=>(n(),s("th",{key:e.name,class:T(["pf-m-truncate pf-c-table__sort pf-c-table__icon",l.isSorted===a?"pf-m-selected":""])},[e.sortable?i("",!0):(n(),s("span",U,d(e.label),1)),e.sortable?(n(),s("button",{key:1,class:"pf-c-table__button",onClick:f=>l.sortBy(e.key,a)},[o("div",X,[o("span",Y,d(e.label),1),o("span",Z,[o("i",{class:T(l.isSorted===a?l.sortIcon:l.data.sortIcon.base)},null,2)])])],8,W)):i("",!0)],2))),128)),$])]),t.tabledata.isLoading?(n(),R(h,{key:0})):i("",!0),t.tabledata.data.total>0||t.tabledata.isLoading?(n(),s("tbody",tt,[(n(!0),s(b,null,m(t.tabledata.data.data,e=>(n(),s("tr",{role:"row",key:e.name},[(n(!0),s(b,null,m(t.tabledata.headers,a=>(n(),s("td",{key:a.label,role:"cell","data-label":a.label},[a.key==="finished"?(n(),s("div",at,[e[a.key]?(n(),s("div",nt,d(e[a.key].last_started_at||" "),1)):i("",!0)])):i("",!0),a.isRelationShip===!0?(n(),s("div",ot,[(n(!0),s(b,null,m(e[a.key],f=>(n(),s("div",{key:f.id},d(f[a.relationshipKey]),1))),128))])):(n(),s("div",st,[a.hasActivityIcon?(n(),s("span",{key:0,class:T(l.activityLogIconTable[e[a.key]])}," ",2)):i("",!0),a.isActionLink?(n(),s("span",lt,[o("button",{class:"pf-c-button pf-m-link pf-m-inline",type:"button",onClick:f=>p.$emit("actionLink",e[a.key])},d(e[a.key]),9,it)])):i("",!0),a.isTasksActionLink?(n(),s("span",ct,[o("button",{class:"pf-c-button pf-m-link pf-m-inline",type:"button",onClick:f=>p.$emit("actionLink",e.id)},"view",8,rt)])):i("",!0),a.key==="device_count"?(n(),s("span",dt,[B(w,{type:"button",class:"pf-c-chip pf-m-overflow",to:"/devices/"+a.deviceCountType+"/"+e.id},{default:S(()=>[o("span",ft,d(e[a.key]),1)]),_:2},1032,["to"])])):i("",!0),a.key==="created_at"&&e[a.key]>0?(n(),s("span",_t,[o("span",pt,d(l.formatTime(e[a.key])),1)])):!a.hasEnabledIcon&&!["valid_results_count","invalid_results_count","method_failures_count","report_id","device_count","finished","viewDevices"].includes(a.key)?(n(),s("span",ut,[_(d(e[a.key])+" ",1),a.key=="categoryName"?(n(),s("div",bt,[e.command&&e.command.length<=0&&a.error?(n(),s("p",mt,[kt,_(" This category does not have any commands - "),yt])):i("",!0)])):i("",!0)])):i("",!0)]))],8,et))),128)),o("td",gt,[o("div",null,[t.backupDownloadBtnEnabled?(n(),s("a",{key:0,class:"pf-c-button pf-m-link pf-m-small",type:"button",href:e.url},[wt,_(" Download ")],8,ht)):i("",!0),t.taskRunBtnEnabled?(n(),s("button",{key:1,class:"pf-c-button pf-m-link pf-m-small",type:"button",onClick:a=>l.emitShowTaskRunConfirmModal(e.id),alt:"Start this task now!",title:"Start this task now!"},[Ct,_(" Start ")],8,vt)):i("",!0),t.editBtnEnabled?(n(),s("button",{key:2,class:"pf-c-button pf-m-link pf-m-small",type:"button",onClick:a=>l.openDrawer(e.id),alt:"Edit",title:"Edit"},[Bt,_(" Edit ")],8,Dt)):i("",!0),t.rowViewBtnEnabled?(n(),s("button",{key:3,class:"pf-c-button pf-m-link pf-m-small",type:"button",onClick:a=>l.viewAction(e.id),alt:"View Details",title:"View Details"},[Et,_(" View Details ")],8,Tt)):i("",!0),o("button",{class:"pf-c-button pf-m-link pf-m-danger pf-m-small",type:"button",onClick:a=>l.deleteRow(e.id),alt:"Delete",title:"Delete"},[Pt,_(" Delete ")],8,It)])])]))),128))])):t.tabledata.isLoading?i("",!0):(n(),R(v,{key:2,onClear:l.clearFilters},null,8,["onClear"]))]),B(C,{from:t.tabledata.data.from,to:t.tabledata.data.to,total:t.tabledata.data.total,current_page:t.tabledata.data.current_page,last_page:t.tabledata.data.last_page,onPagechanged:c[2]||(c[2]=e=>l.pageChanged(e))},null,8,["from","to","total","current_page","last_page"])])])])}const xt=M(q,[["render",St]]);export{xt as D};
