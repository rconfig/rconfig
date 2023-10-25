import{_ as T,i as $,u as E,f as x,g as O,r as m,o as n,c as d,a as e,b as _,w as b,h as u,t as p,d as V,q as y,l as N,F,k as j,e as L,y as q,j as h,B as A,A as P,C as M}from"./app-33ff2348.js";import{D as I}from"./DeviceViewDeviceDetailsDescr-489148c6.js";import{D as B}from"./DevicesBreadcrumbs-d681eff4.js";import{D as R}from"./DevicesForm-7c18cb71.js";import{L as z}from"./LoadingSpinner-acc99a56.js";import{P as H}from"./PageHeader-681f21f0.js";import{S as W}from"./SideDrawer-b60c3507.js";import{a as K,u as U}from"./ViewFunctions-c2f77989.js";/* empty css            */import"./CopyLogo-40232a72.js";import"./DeviceCategoryField-f0a17dc6.js";import"./MultiSelect-098c9700.js";const G={props:{model:{type:Object,default:()=>({})}},components:{},setup(l,{emit:s}){const o=$("create-notification"),{toClipboard:t}=E(),c=x(!1),r=x(null);O(()=>{a()});function a(){axios.get("/api/app-dir-path").then(f=>{c.value=f.data})}function i(){r.value="Downloading...",o({type:"success",title:"Download Started",message:"Download started for device "+l.model.device_name}),axios.post("/api/device/download-now",{device_id:l.model.id}).then(f=>{r.value="Queued...",o({type:"success",title:"Download Started",message:"Download job for "+l.model.device_name+" was pushed to the queue."}),D()}).catch(f=>{o({type:"danger",title:"Error",message:f.response.data.message})})}function D(){const f=setInterval(function(){axios.get("/api/tracked-jobs/"+l.model.id).then(v=>{r.value=v.data.data.status}),r.value==="finished"&&(o({type:"success",title:"Download Finished",message:"Download finished for device "+l.model.device_name}),clearInterval(f),setTimeout(()=>{r.value=null},3e3))},2e3)}function C(){axios.post("/api/device/purge-failed-configs",{device_id:l.model.id}).then(f=>{o({type:"success",title:"Purge Successful",message:"Purge successful for device "+l.model.device_name})}).catch(f=>{o({type:"danger",title:"Error",message:f.response.data.message})})}function S(f){try{t(f),o({type:"success",message:"Copied to clipboard!",duration:3})}catch(v){o({type:"danger",title:"Error",message:v.response})}}function k(f,v=!1){s("openDrawer",{id:f,isClone:v})}return{appDirPath:c,copyDebug:S,downloadNow:i,downloadStatus:r,openDrawer:k,purgeFailedConfigs:C}}},J={class:"pf-c-card"},Q=e("div",{class:"pf-c-card__title"},[e("h2",{class:"pf-c-title pf-m-xl"},"Common Actions")],-1),X={class:"pf-c-menu"},Y={class:"pf-c-menu__content"},Z={class:"pf-c-menu__list"},ee={class:"pf-c-menu__list-item"},te=V('<span class="pf-c-menu__item-main"><span class="pf-c-menu__item-icon"><i class="fas fa-code" aria-hidden="true"></i></span><span class="pf-c-menu__item-text">Copy debug CLI command</span></span><span class="pf-c-menu__item-description pf-u-text-break-word">Copy command for CLI debug to clipboard</span>',2),oe=[te],se={class:"pf-c-menu__list-item"},ie=e("span",{class:"pf-c-menu__item-main"},[e("span",{class:"pf-c-menu__item-icon"},[e("i",{class:"pficon pf-icon-storage-domain","aria-hidden":"true"})]),e("span",{class:"pf-c-menu__item-text"},"View configuration downloads")],-1),ne=e("span",{class:"pf-c-menu__item-description"},"View configuration files for this device",-1),ae={class:"pf-c-menu__list-item"},ce=V('<span class="pf-c-menu__item-main"><span class="pf-c-menu__item-icon"><i class="pficon pf-icon-save" aria-hidden="true"></i></span><span class="pf-c-menu__item-text">Download now</span></span><span class="pf-c-menu__item-description pf-u-text-break-word" style="word-wrap:normal;">Start a download for this device</span>',2),le=[ce],de={key:0,class:"pf-c-menu__list-item",style:{cursor:"auto"}},re={class:"pf-c-menu__item"},_e={class:"pf-c-menu__item-main"},fe={class:"pf-c-menu__item-icon"},pe={key:0,class:"pf-c-spinner pf-m-md",role:"progressbar",viewBox:"0 0 100 100","aria-label":"Loading..."},me=e("circle",{class:"pf-c-spinner__path",cx:"50",cy:"50",r:"45",fill:"none"},null,-1),ue=[me],ve={key:1,class:"fa fa-check-circle pf-u-success-color-100","aria-hidden":"true"},ge={key:2,class:"fa fa-exclamation-circle pf-u-danger-color-100","aria-hidden":"true"},he=e("span",{class:"pf-c-menu__item-text"},"Download status",-1),we={class:"pf-c-menu__item-description pf-u-text-break-word",style:{"word-wrap":"normal"}},xe={class:"pf-c-menu__list-item"},be=V('<span class="pf-c-menu__item-main"><span class="pf-c-menu__item-icon"><i class="fas fa-copy" aria-hidden="true"></i></span><span class="pf-c-menu__item-text">Clone device</span></span><span class="pf-c-menu__item-description pf-u-text-break-word" style="word-wrap:normal;">Create a new device with similar configuration</span>',2),ye=[be],De={class:"pf-c-menu__list-item"},ke=V('<span class="pf-c-menu__item-main"><span class="pf-c-menu__item-icon"><i class="fas fa-trash" aria-hidden="true"></i></span><span class="pf-c-menu__item-text">Purge failed configs</span></span><span class="pf-c-menu__item-description pf-u-text-break-word" style="word-wrap:normal;">Purge all Failed Configs for this device</span>',2),Ce=[ke];function Se(l,s,o,t,c,r){const a=m("router-link");return n(),d("div",null,[e("div",J,[Q,e("div",X,[e("div",Y,[e("ul",Z,[e("li",ee,[e("button",{class:"pf-c-menu__item",type:"button",onClick:s[0]||(s[0]=i=>t.copyDebug("cd "+t.appDirPath+" && php artisan rconfig:download-device "+o.model.id+" -d"))},oe)]),e("li",se,[_(a,{to:{path:"/device/view/configs/"+o.model.id,query:{id:o.model.id,devicename:o.model.device_name,status:"all"}},class:"pf-c-menu__item",type:"button"},{default:b(()=>[ie,ne]),_:1},8,["to"])]),e("li",ae,[e("button",{class:"pf-c-menu__item",onClick:s[1]||(s[1]=i=>t.downloadNow())},le)]),t.downloadStatus?(n(),d("li",de,[e("button",re,[e("span",_e,[e("span",fe,[t.downloadStatus!="The job finished, check configs and logs for details."?(n(),d("svg",pe,ue)):u("",!0),t.downloadStatus==="The job finished, check configs and logs for details."?(n(),d("i",ve)):u("",!0),t.downloadStatus==="Failed"?(n(),d("i",ge)):u("",!0)]),he]),e("span",we,p(t.downloadStatus),1)])])):u("",!0),e("li",xe,[e("button",{class:"pf-c-menu__item",onClick:s[2]||(s[2]=i=>t.openDrawer(o.model.id,!0))},ye)]),e("li",De,[e("button",{class:"pf-c-menu__item",onClick:s[3]||(s[3]=i=>t.purgeFailedConfigs())},Ce)])])])])])])}const $e=T(G,[["render",Se]]),Te={props:{model:{type:Object,default:()=>({})}},emits:["openDrawer","deleteRow","pagechanged"],components:{DeviceViewDeviceDetailsDescr:I},setup(l,{emit:s}){const o=$("formatTime");function t(c,r=!1){s("openDrawer",{id:c,isClone:r})}return{openDrawer:t,formatTime:o}}},Ne={class:"pf-c-card"},Le=e("div",{class:"pf-c-card__title"},[e("h2",{class:"pf-c-title pf-m-xl"},"Device Details")],-1),Ve={class:"pf-c-card__body"},Fe={class:"pf-c-description-list pf-m-horizontal pf-m-vertical-on-md pf-m-horizontal-on-lg pf-m-vertical-on-xl pf-m-horizontal-on-2xl"},je={class:"pf-c-description-list__group"},Oe=e("dt",{class:"pf-c-description-list__term"},[e("span",{class:"pf-c-description-list__text"},"Device ID")],-1),Me={class:"pf-c-description-list__group"},Pe=e("dt",{class:"pf-c-description-list__term"},[e("span",{class:"pf-c-description-list__text"},"Hostname")],-1),Ee={class:"pf-c-description-list__group"},qe=e("dt",{class:"pf-c-description-list__term"},[e("span",{class:"pf-c-description-list__text"},"IP Address")],-1),Ae={class:"pf-c-description-list__group"},Ie=e("dt",{class:"pf-c-description-list__term"},[e("span",{class:"pf-c-description-list__text"},"Status")],-1),Be={class:"pf-l-flex pf-m-space-items-sm"},Re={class:"pf-l-flex__item"},ze={class:"pf-l-flex pf-m-column pf-m-space-items-none pf-m-flex-1"},He={class:"pf-l-flex__item"},We={class:"pf-l-flex__item"},Ke={class:"pf-u-color-400"},Ue={class:"pf-c-description-list__group"},Ge=e("dt",{class:"pf-c-description-list__term"},[e("span",{class:"pf-c-description-list__text"},"Category")],-1),Je={class:"pf-c-description-list__group"},Qe=e("dt",{class:"pf-c-description-list__term"},[e("span",{class:"pf-c-description-list__text"},"Vendor")],-1),Xe={class:"pf-c-description-list__group"},Ye=e("dt",{class:"pf-c-description-list__term"},[e("span",{class:"pf-c-description-list__text"},"Model")],-1),Ze={class:"pf-c-description-list__group"},et=e("dt",{class:"pf-c-description-list__term"},[e("span",{class:"pf-c-description-list__text"},"Template")],-1),tt={class:"pf-c-description-list__group"},ot=e("dt",{class:"pf-c-description-list__term"},[e("span",{class:"pf-c-description-list__text"},"Created")],-1),st={class:"pf-c-description-list__group"},it=e("dt",{class:"pf-c-description-list__term"},[e("span",{class:"pf-c-description-list__text"},"Tags")],-1),nt={class:"pf-c-description-list__description"},at=["alt","title"],ct=e("hr",{class:"pf-c-divider"},null,-1),lt={class:"pf-c-card__footer",style:{"padding-top":"15px","padding-bottom":"15px","padding-left":"9px"}};function dt(l,s,o,t,c,r){const a=m("device-view-device-details-descr");return n(),d("div",Ne,[Le,e("div",Ve,[e("dl",Fe,[e("div",je,[Oe,_(a,{text:o.model.id},null,8,["text"])]),e("div",Me,[Pe,_(a,{text:o.model.device_name},null,8,["text"])]),e("div",Ee,[qe,_(a,{text:o.model.device_ip},null,8,["text"])]),e("div",Ae,[Ie,e("div",Be,[e("div",Re,[e("i",{class:y(o.model.status=="1"?"fa fa-check-circle pf-u-success-color-100 ":"fa fa-exclamation-triangle pf-u-warning-color-100")},null,2)]),e("div",ze,[e("div",He,p(o.model.status=="1"?"Online":"Unreachable"),1),e("div",We,[e("span",Ke,"Last seen: "+p(o.model.last_seen?t.formatTime(o.model.last_seen):"-"),1)])])])]),e("div",Ue,[Ge,o.model.category.length!=0?(n(),N(a,{key:0,text:o.model.category[0].categoryName},null,8,["text"])):u("",!0)]),e("div",Je,[Qe,_(a,{text:o.model.vendor[0].vendorName},null,8,["text"])]),e("div",Xe,[Ye,_(a,{text:o.model.device_model},null,8,["text"])]),e("div",Ze,[et,o.model.template.length!=0?(n(),N(a,{key:0,text:o.model.template[0].templateName},null,8,["text"])):u("",!0)]),e("div",tt,[ot,o.model.template.length!=0?(n(),N(a,{key:0,text:t.formatTime(o.model.created_at)},null,8,["text"])):u("",!0)]),e("div",st,[it,e("dd",nt,[(n(!0),d(F,null,j(o.model.tag,i=>(n(),d("div",{class:"pf-c-chip",key:i.id},[e("span",{class:"pf-c-chip__text",alt:i.tagDescription,title:i.tagDescription},p(i.tagname),9,at)]))),128))])])])]),ct,e("div",lt,[e("button",{class:"pf-c-button pf-m-link",style:{float:"right"},type:"button",onClick:s[0]||(s[0]=i=>t.openDrawer(o.model.id)),alt:"Edit",title:"Edit"},"Edit Settings")])])}const rt=T(Te,[["render",dt]]),_t={props:{model:{type:Object,default:()=>({})}},components:{},setup(l){const s=L({}),o=x(!0),t=$("formatTime");O(()=>{c()});function c(){axios.get("/api/configs/latest-by-deviceid/"+l.model.id).then(r=>{Object.assign(s,r.data),o.value=!1}).catch(r=>{console.log(r)})}return q(()=>{l.model.config_good_count&&c()}),{formatTime:t,isLoading:o,latestConfigs:s}}},ft={class:"pf-c-card",style:{"margin-top":"18px"}},pt=V('<div class="pf-c-toolbar"><div class="pf-c-toolbar__content"><div class="pf-c-toolbar__content-section pf-m-nowrap"><div class="pf-c-toolbar__group pf-m-toggle-group pf-m-show-on-xl"><h2 class="pf-c-title pf-m-xl">Latest downloads</h2></div></div></div></div>',1),mt={class:"pf-c-table pf-m-compact pf-m-grid-lg",role:"grid"},ut=e("thead",null,[e("tr",{role:"row"},[e("th",{role:"columnheader",scope:"col"},"Command"),e("th",{role:"columnheader",scope:"col"},"Filename"),e("th",{role:"columnheader",scope:"col"},"Downloaded"),e("th",{class:"pf-c-table__icon",role:"columnheader",scope:"col"},"Status"),e("th",{role:"columnheader"})])],-1),vt={role:"rowgroup"},gt={lass:"pf-m-break-word",role:"columnheader","data-label":"Command"},ht={class:"pf-m-break-word",role:"cell","data-label":"Filename"},wt={class:"pf-m-break-word",role:"cell","data-label":"Downloaded"},xt={class:"pf-c-table__icon",role:"cell","data-label":"Status"},bt={role:"cell","data-label":"Action"},yt={class:"pf-c-card__footer",style:{"padding-top":"15px","padding-bottom":"15px","padding-left":"9px"}};function Dt(l,s,o,t,c,r){const a=m("router-link");return n(),d("div",ft,[pt,e("table",mt,[ut,e("tbody",vt,[(n(!0),d(F,null,j(t.latestConfigs.data,i=>(n(),d("tr",{role:"row",key:i.id},[e("th",gt,p(i.command),1),e("td",ht,p(i.config_filename),1),e("td",wt,p(t.formatTime(i.created_at)),1),e("td",xt,[e("i",{class:y(i.download_status=="0"?"fa fa-exclamation-circle pf-u-danger-color-100":"")},null,2),e("i",{class:y(i.download_status=="1"?"fa fa-check-circle pf-u-success-color-100 ":"")},null,2),e("i",{class:y(i.download_status=="2"?"fa fa-exclamation-triangle pf-u-warning-color-100":"")},null,2),e("i",{class:y(i.download_status===null?"fa fa-exclamation-triangle pf-u-warning-color-100":"")},null,2)]),e("td",bt,[_(a,{type:"button",class:"pf-c-button pf-m-link",to:"/device/view/configs/view-config/"+i.id},{default:b(()=>[h("View")]),_:2},1032,["to"])])]))),128))])]),e("div",yt,[_(a,{type:"button",class:"pf-c-button pf-m-link",style:{float:"right"},to:{path:"/device/view/configs/"+o.model.id,query:{id:o.model.id,devicename:o.model.device_name,status:"all"}}},{default:b(()=>[h("View all")]),_:1},8,["to"])])])}const kt=T(_t,[["render",Dt]]),Ct={props:{},setup(l){return{}}},St={class:"pf-c-tooltip pf-m-top-left",role:"tooltip",style:{"z-index":"999",position:"absolute",top:"26%"}},$t=e("div",{class:"pf-c-tooltip__arrow"},null,-1),Tt={class:"pf-c-tooltip__content",id:"tooltip-top-content"};function Nt(l,s,o,t,c,r){return n(),d("div",St,[$t,e("div",Tt,[A(l.$slots,"default")])])}const Lt=T(Ct,[["render",Nt]]),Vt={props:{model:{type:Object,default:()=>({})}},components:{Tooltip:Lt},setup(l){const s=x(!0),o=x(!1),t=x(!1),c=x(!1),r=L({}),a=L({}),i=x(!0);P();const D=$("create-notification"),C=$("formatTime"),S=L({default:{type:"default",color:"pf-u-default-color-200",notherColor:"pf-m-cyan",icon:"fas fa-info-circle"},info:{type:"default",color:"pf-u-default-color-200",notherColor:"pf-m-cyan",icon:"fas fa-info-circle"},warn:{type:"warning",color:"pf-u-warning-color-200",notherColor:"pf-m-orange",icon:"fas fa-exclamation-triangle"},error:{type:"danger",color:"pf-u-danger-color-200",notherColor:"pf-m-red",icon:"fas fa-exclamation-circle"}});O(()=>{f(),k()});function k(){M.get("/api/activitylogs/device-stats/"+l.model.id).then(g=>{Object.assign(a,g.data),i.value=!1}).catch(g=>{D({type:"danger",title:"Error",message:g.response.data.message})})}function f(){M.get("/api/activitylogs/last5/"+l.model.id).then(g=>{Object.assign(r,g.data),i.value=!1}).catch(g=>{D({type:"danger",title:"Error",message:g.response.data.message})})}function v(){s.value=!s.value}return{notificationResults:r,goodConfigsTooptip:o,badConfigsTooptip:t,formatTime:C,allConfigsTooptip:c,notificationStats:a,isHiddenNotifications:s,toggleNotifications:v,logLookup:S}}},Ft={class:"pf-c-card"},jt=e("div",{class:"pf-c-card__header"},[e("h2",{class:"pf-c-title pf-m-xl"},"Config Status")],-1),Ot={class:"pf-c-card__body"},Mt={class:"pf-l-grid pf-m-all-6-col-on-sm pf-m-all-3-col-on-lg pf-m-gutter"},Pt={class:"pf-l-grid__item"},Et={class:"pf-l-flex pf-m-space-items-sm"},qt=e("div",{class:"pf-l-flex__item"},[e("i",{class:"fas fa-check-circle pf-u-success-color-100","aria-hidden":"true"})],-1),At={class:"pf-l-flex pf-m-column pf-m-space-items-none pf-m-flex-1"},It=e("div",{class:"pf-l-flex__item"},[e("span",{class:"pf-u-color-400"},"Good Configs")],-1),Bt={class:"pf-l-grid__item"},Rt={class:"pf-l-flex pf-m-space-items-sm"},zt=e("div",{class:"pf-l-flex__item"},[e("i",{class:"fa fa-exclamation-triangle pf-u-warning-color-100","aria-hidden":"true"})],-1),Ht={class:"pf-l-flex pf-m-column pf-m-space-items-none pf-m-flex-1"},Wt=e("div",{class:"pf-l-flex__item"},[e("span",{class:"pf-u-color-400"},"Unknown Configs")],-1),Kt={class:"pf-l-grid__item"},Ut={class:"pf-l-flex pf-m-space-items-sm"},Gt=e("div",{class:"pf-l-flex__item"},[e("i",{class:"fas fa-exclamation-circle pf-u-danger-color-100","aria-hidden":"true"})],-1),Jt={class:"pf-l-flex pf-m-column pf-m-space-items-none pf-m-flex-1"},Qt={class:"pf-l-flex__item"},Xt=e("div",{class:"pf-l-flex__item"},[e("span",{class:"pf-u-color-400"},"Failed Configs")],-1),Yt={class:"pf-l-grid__item"},Zt={class:"pf-l-flex pf-m-space-items-sm"},eo=e("div",{class:"pf-l-flex__item"},[e("i",{class:"fas fa-check-circle pf-u-success-color-100","aria-hidden":"true"})],-1),to={class:"pf-l-flex pf-m-column pf-m-space-items-none pf-m-flex-1"},oo={key:0,class:"pf-l-flex__item"},so=e("div",{class:"pf-l-flex__item"},[e("span",{class:"pf-u-color-400"},"Last Download")],-1),io=e("hr",{class:"pf-c-divider"},null,-1),no={class:"pf-c-notification-drawer"},ao={class:"pf-c-notification-drawer__body"},co={class:"pf-c-notification-drawer__group pf-m-expanded"},lo=["disabled"],ro={class:"pf-c-notification-drawer__group-toggle-title"},_o={class:"pf-l-flex"},fo={class:"pf-c-notification-drawer__group-toggle-title"},po={class:"pf-l-flex pf-m-space-items-sm"},mo={class:"pf-l-flex__item pf-m-spacer-md"},uo={key:0},vo={key:0},go={class:"pf-c-label__content"},ho={class:"pf-c-label__icon"},wo={key:0,class:"pf-c-notification-drawer__group-toggle-icon",alt:"view recent",title:"view recent"},xo=e("i",{class:"fas fa-angle-right","aria-hidden":"true"},null,-1),bo=[xo],yo={key:0,class:"pf-c-notification-drawer__list"},Do={class:"pf-c-notification-drawer__list-item-header"},ko=e("span",{class:"pf-c-notification-drawer__list-item-header-icon"},[e("i",{class:"fas fa-exclamation-circle","aria-hidden":"true"})],-1),Co=e("span",{class:"pf-screen-reader"},"Danger notification:",-1),So={class:"pf-c-notification-drawer__list-item-action pf-u-font-size-sm pf-u-disabled-color-100"},$o={class:"pf-c-notification-drawer__list-item-description"},To={class:"pf-c-notification-drawer__list-item pf-m-hoverable",tabindex:"0"};function No(l,s,o,t,c,r){const a=m("router-link");return n(),d("div",Ft,[jt,e("div",Ot,[e("div",Mt,[e("div",Pt,[e("div",Et,[qt,e("div",At,[_(a,{class:"alink",to:{path:"/device/view/configs/"+o.model.id,query:{id:o.model.id,devicename:o.model.device_name,status:1}},onMouseover:s[0]||(s[0]=i=>t.goodConfigsTooptip=!0),onMouseleave:s[1]||(s[1]=i=>t.goodConfigsTooptip=!1)},{default:b(()=>[h(p(o.model.config_good_count),1)]),_:1},8,["to"]),It])])]),e("div",Bt,[e("div",Rt,[zt,e("div",Ht,[_(a,{class:"alink",to:{path:"/device/view/configs/"+o.model.id,query:{id:o.model.id,devicename:o.model.device_name,status:2}},onMouseover:s[2]||(s[2]=i=>t.goodConfigsTooptip=!0),onMouseleave:s[3]||(s[3]=i=>t.goodConfigsTooptip=!1)},{default:b(()=>[h(p(o.model.config_unknown_count),1)]),_:1},8,["to"]),Wt])])]),e("div",Kt,[e("div",Ut,[Gt,e("div",Jt,[e("div",Qt,[_(a,{to:{path:"/device/view/configs/"+o.model.id,query:{id:o.model.id,devicename:o.model.device_name,status:0}},onMouseover:s[4]||(s[4]=i=>t.badConfigsTooptip=!0),onMouseleave:s[5]||(s[5]=i=>t.badConfigsTooptip=!1)},{default:b(()=>[h(p(o.model.config_bad_count),1)]),_:1},8,["to"])]),Xt])])]),e("div",Yt,[e("div",Zt,[eo,e("div",to,[o.model.last_config?(n(),d("div",oo,[_(a,{to:{path:"/device/view/configs/"+o.model.id,query:{id:o.model.id,devicename:o.model.device_name,status:"all"}},append:"",onMouseover:s[6]||(s[6]=i=>t.allConfigsTooptip=!0),onMouseleave:s[7]||(s[7]=i=>t.allConfigsTooptip=!1)},{default:b(()=>[h(p(t.formatTime(o.model.last_config.created_at)),1)]),_:1},8,["to"]),h(" > ")])):u("",!0),so])])])])]),io,e("div",no,[e("div",ao,[e("section",co,[e("button",{class:"pf-c-notification-drawer__group-toggle","aria-expanded":"true",onClick:s[8]||(s[8]=(...i)=>t.toggleNotifications&&t.toggleNotifications(...i)),disabled:!t.notificationResults},[e("div",ro,[e("div",_o,[e("div",fo,[e("div",po,[e("div",mo,[e("span",null,[h("Notifications "),t.notificationResults?u("",!0):(n(),d("span",uo," clear"))])]),t.notificationStats?(n(),d("div",vo,[(n(!0),d(F,null,j(t.notificationStats,i=>(n(),d("span",{class:y(["pf-c-label",t.logLookup[i.log_name].notherColor]),key:i.total},[e("span",go,[e("span",ho,[e("i",{class:y(["fas fa-fw",t.logLookup[i.log_name].icon]),"aria-hidden":"true"},null,2)]),h(" "+p(i.total),1)])],2))),128))])):u("",!0)])])])]),t.notificationResults?(n(),d("span",wo,bo)):u("",!0)],8,lo),!t.isHiddenNotifications&&"hidden"?(n(),d("ul",yo,[(n(!0),d(F,null,j(t.notificationResults,i=>(n(),d("li",{class:y(["pf-c-notification-drawer__list-item pf-m-hoverable","pf-m-"+t.logLookup[i.log_name].type]),tabindex:"0",key:i.id},[e("div",Do,[ko,e("h2",{class:y(["pf-c-notification-drawer__list-item-header-title",t.logLookup[i.log_name].color])},[Co,h(" "+p(i.event_type.charAt(0).toUpperCase()+i.event_type.slice(1)),1)],2)]),e("div",So,p(t.formatTime(i.created_at)),1),e("div",$o,p(i.description),1)],2))),128)),e("li",To,[_(a,{to:{path:"/device/view/eventlog/"+o.model.id,query:{id:o.model.id,devicename:o.model.device_name}},class:"alink"},{default:b(()=>[h("View All")]),_:1},8,["to"])])])):u("",!0)])])])])}const Lo=T(Vt,[["render",No]]),Vo={components:{PageHeader:H,DevicesBreadcrumbs:B,DeviceViewDeviceDetails:rt,DeviceViewStatusPanel:Lo,DeviceViewLatestDownloads:kt,DeviceViewActionsMenu:$e,LoadingSpinner:z,SideDrawer:W,DevicesForm:R},setup(){const l=x("Device View"),s=x("Device details dashboard"),o=P(),t=$("create-notification"),c=L({editid:o.params.id,pagename:"Devices",pagedesc:"All devices",pagenamesingle:"Device",modelName:"devices",openDrawerState:!1,drawerOuterWidth:"pf-m-width-75-on-xl pf-m-width-100 ",showDeleteModal:!1,sideDrawerComponentKey:1,pageOptionsState:{page:1,per_page:10},modelObject:{device_name:"",device_ip:"",device_vendor:"",device_model:"",device_category_id:"",device_tags:"",device_username:"",device_password:"",device_template:"",device_main_prompt:""}}),{errors:r,model:a,getModel:i,isLoading:D}=K(c.modelName,c.modelObject),{isLoading:C,openDrawer:S,closeDrawerState:k}=U(c,c.modelName,c.modelObject);O(()=>{i(c.editid)});function f(w){t({type:"success",message:w,duration:3}),i(c.editid),c.openDrawerState=!1}function v(){i(c.editid)}function g(){k()}return{closeDrawer:g,closeDrawerState:k,downloadFinished:v,errors:r,formLoading:C,formSubmittedDeviceView:f,isLoading:D,model:a,openDrawer:S,pagedesc:s,pagename:l,viewstate:c}}},Fo={class:"pf-c-page__main",tabindex:"-1"},jo=e("div",{class:"pf-c-divider",role:"separator"},null,-1),Oo={class:"pf-c-page__main-section pf-m-no-padding"},Mo={class:"pf-c-drawer__main"},Po={class:"pf-c-drawer__content pf-m-no-background"},Eo={class:"pf-c-drawer__body pf-m-padding"},qo={key:0,class:"pf-l-grid pf-m-gutter"},Ao={class:"pf-l-grid__item pf-m-12-col pf-m-3-col-on-md"},Io={class:"pf-l-grid__item pf-m-12-col pf-m-6-col-on-md"};function Bo(l,s,o,t,c,r){const a=m("devices-breadcrumbs"),i=m("page-header"),D=m("loading-spinner"),C=m("device-view-device-details"),S=m("device-view-status-panel"),k=m("device-view-latest-downloads"),f=m("device-view-actions-menu"),v=m("devices-form"),g=m("side-drawer");return n(),d("main",Fo,[_(i,{pagename:t.pagename},{breadcrumbs:b(()=>[_(a,{devicename:t.model.device_name,deviceId:t.model.id},null,8,["devicename","deviceId"])]),_:1},8,["pagename"]),jo,e("section",Oo,[e("div",{class:y(["pf-c-drawer",{"pf-m-expanded":t.viewstate.openDrawerState}])},[e("div",Mo,[e("div",Po,[e("div",Eo,[_(D,{showSpinner:t.isLoading},null,8,["showSpinner"]),t.isLoading?u("",!0):(n(),d("div",qo,[e("div",Ao,[_(C,{model:t.model,onOpenDrawer:s[0]||(s[0]=w=>t.openDrawer(w))},null,8,["model"])]),e("div",Io,[_(S,{model:t.model},null,8,["model"]),_(k,{model:t.model},null,8,["model"])]),_(f,{class:"pf-l-grid__item pf-m-12-col pf-m-3-col-on-md",model:t.model,onOpenDrawer:s[1]||(s[1]=w=>t.openDrawer(w)),onDownloadFinished:s[2]||(s[2]=w=>t.downloadFinished())},null,8,["model"])]))])]),(n(),N(g,{pagename:t.viewstate.pagenamesingle,drawerState:t.viewstate.openDrawerState,outerWidth:t.viewstate.drawerOuterWidth,editid:t.viewstate.editid,isClone:t.viewstate.isClone,onCloseDrawer:s[5]||(s[5]=w=>t.closeDrawer()),key:t.viewstate.sideDrawerComponentKey},{form:b(()=>[(n(),N(v,{viewstate:t.viewstate,onCloseDrawer:s[3]||(s[3]=w=>t.closeDrawer()),onFormsubmitted:s[4]||(s[4]=w=>t.formSubmittedDeviceView(w)),key:t.viewstate.sideDrawerComponentKey},null,8,["viewstate"]))]),_:1},8,["pagename","drawerState","outerWidth","editid","isClone"]))])],2)])])}const es=T(Vo,[["render",Bo]]);export{es as default};
