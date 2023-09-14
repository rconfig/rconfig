import{e as E,F as A,C as R}from"./ConfigFullScreenView-a0f0ae85.js";import{D as G}from"./DeviceViewDeviceDetailsDescr-e0cebb1c.js";import{_ as I,f as g,i as D,g as N,r as h,o as b,c as k,a as e,b as a,H,u as K,e as M,j as x,s as U,x as W,A as J,I as Q,w as X,q as Y,h as S,l as Z,F as ee}from"./app-31c82bbe.js";import{D as te}from"./DevicesBreadcrumbs-2f3e0675.js";import{L as oe}from"./LoadingSpinner-22db4602.js";import{P as ie}from"./PageHeader-267a914d.js";import{S as ne}from"./SideDrawer-37ee3521.js";import"./CopyLogo-4512f417.js";/* empty css            */const se={props:{configModel:{required:!0}},components:{DeviceViewDeviceDetailsDescr:G},setup(d){const o=g("Downloaded"),n=D("formatTime");N(()=>{d.configModel.download_status=="1"?o.value='<i class="fa fa-check-circle pf-u-success-color-100"></i><span class="pf-u-color-400">&nbsp; Good config</span>':(d.configModel.download_status=="0"||d.configModel.download_status=="2")&&(o.value='<i class="fa fa-exclamation-triangle pf-u-warning-color-100"></i><span class="pf-u-color-400">&nbsp; Config status unknown</span>')});function t(c){var p=["B","KB","MB","GB","TB"];if(c==0)return"0 Byte";var i=parseInt(Math.floor(Math.log(c)/Math.log(1024)));return Math.round(c/Math.pow(1024,i),2)+" "+p[i]}return{statusText:o,bytesToSize:t,formatTime:n}}},ce={class:"pf-c-card"},le=e("div",{class:"pf-c-card__title"},[e("h2",{class:"pf-c-title pf-m-lg"},"Config Details")],-1),ae={class:"pf-c-card__body"},de={class:"pf-c-description-list pf-m-horizontal pf-m-vertical-on-md pf-m-horizontal-on-lg pf-m-vertical-on-xl pf-m-horizontal-on-2xl"},re={class:"pf-c-description-list__group"},fe=e("dt",{class:"pf-c-description-list__term"},[e("span",{class:"pf-c-description-list__text"},"Status")],-1),pe={class:"pf-c-description-list__group"},_e=e("dt",{class:"pf-c-description-list__term"},[e("span",{class:"pf-c-description-list__text"},"Config ID")],-1),ue={class:"pf-c-description-list__group"},me=e("dt",{class:"pf-c-description-list__term"},[e("span",{class:"pf-c-description-list__text"},"Device ID")],-1),ge={class:"pf-c-description-list__group"},he=e("dt",{class:"pf-c-description-list__term"},[e("span",{class:"pf-c-description-list__text"},"Device Name")],-1),ve={class:"pf-c-description-list__group"},be=e("dt",{class:"pf-c-description-list__term"},[e("span",{class:"pf-c-description-list__text"},"Category")],-1),we={class:"pf-c-description-list__group"},ye=e("dt",{class:"pf-c-description-list__term"},[e("span",{class:"pf-c-description-list__text"},"Command")],-1),xe={class:"pf-c-description-list__group"},ke=e("dt",{class:"pf-c-description-list__term"},[e("span",{class:"pf-c-description-list__text"},"Filename")],-1),Ce={class:"pf-c-description-list__group"},Se=e("dt",{class:"pf-c-description-list__term"},[e("span",{class:"pf-c-description-list__text"},"Filesize")],-1),Me={class:"pf-c-description-list__group"},Ie=e("dt",{class:"pf-c-description-list__term"},[e("span",{class:"pf-c-description-list__text"},"Download Duration")],-1),De={class:"pf-c-description-list__group"},Ne=e("dt",{class:"pf-c-description-list__term"},[e("span",{class:"pf-c-description-list__text"},"Downloaded At")],-1);function Be(d,o,n,t,c,p){const i=h("device-view-device-details-descr");return b(),k("div",ce,[le,e("div",ae,[e("dl",de,[e("div",re,[fe,a(i,{html:t.statusText},null,8,["html"])]),e("div",pe,[_e,a(i,{text:n.configModel.id},null,8,["text"])]),e("div",ue,[me,a(i,{text:n.configModel.device_id},null,8,["text"])]),e("div",ge,[he,a(i,{text:n.configModel.device_name},null,8,["text"])]),e("div",ve,[be,a(i,{text:n.configModel.device_category},null,8,["text"])]),e("div",we,[ye,a(i,{text:n.configModel.command},null,8,["text"])]),e("div",xe,[ke,a(i,{text:n.configModel.config_filename},null,8,["text"])]),e("div",Ce,[Se,a(i,{text:t.bytesToSize(n.configModel.config_filesize)},null,8,["text"])]),e("div",Me,[Ie,a(i,{text:n.configModel.duration+" second"},null,8,["text"])]),e("div",De,[Ne,a(i,{text:t.formatTime(n.configModel.created_at)},null,8,["text"])])])])])}const Fe=I(se,[["render",Be]]);const Le={props:{config_id:{type:[Number,String],required:!0},viewstate:{type:Object,required:!0},configModel:{type:Object,required:!0}},components:{},emits:["showConfigFullScreen"],setup(d,{emit:o}){const n=g(""),t=g(!1),c=D("create-notification"),p=g("rconfig"),i=g(["Loading file..."].join(`
`));H();const{toClipboard:w}=K();let r=null;N(()=>{m(),B(),F(),v();const l=document.getElementById("code-editor");r=E.create(l,{value:i.value,language:p.value||"javascript",lineNumbers:_.value,roundedSelection:!1,readOnly:!1,theme:s.value,scrollBeyondLastLine:!0,wordWrap:"on",wrappingStrategy:"advanced",automaticLayout:!0,minimap:{enabled:!1}})});function v(){axios.get("/api/configs/view-config/"+d.config_id).then(l=>{n.value=l.data.data,r.getModel().setValue(l.data.data)}).catch(l=>{r.updateOptions({value:"Something went wrong - could not retrieve the configuration from the file system!"}),c({type:"danger",title:"Error",message:l.response})})}function y(){o("showConfigFullScreen",{code:n.value,filename:d.configModel.config_filename})}const s=g("vs");function m(){localStorage.getItem("rConfig.editordarkmode")===null?(s.value="vs",localStorage.setItem("rConfig.editordarkmode",s.value)):s.value=localStorage.getItem("rConfig.editordarkmode")}function T(l){l.target.checked?(s.value="vs-dark",localStorage.setItem("rConfig.editordarkmode",s.value)):(s.value="vs",localStorage.setItem("rConfig.editordarkmode",s.value)),E.setTheme(s.value)}const _=g("on");function B(){localStorage.getItem("rConfig.editorlineNumbers")===null?(_.value="on",localStorage.setItem("rConfig.editorlineNumbers",_.value)):_.value=localStorage.getItem("rConfig.editorlineNumbers")}function O(l){l.target.checked?(_.value="on",localStorage.setItem("rConfig.editorlineNumbers",_.value)):(_.value="off",localStorage.setItem("rConfig.editorlineNumbers",_.value)),r.updateOptions({lineNumbers:_.value})}const u=M({enabled:!0});function F(){localStorage.getItem("rConfig.editorMinimap")===null?(u.enabled=!1,localStorage.setItem("rConfig.editorMinimap",u.enabled)):u.enabled=localStorage.getItem("rConfig.editorMinimap")}function j(l){l.target.checked?(u.enabled=!0,localStorage.setItem("rConfig.editorMinimap",u.enabled)):(u.enabled=!1,localStorage.setItem("rConfig.editorMinimap",u.enabled)),r.updateOptions({minimap:{enabled:u.enabled}})}const L=async(l,C)=>{try{var $=typeof C=="string"?C:r.getValue();await w($),t.value=!0,setTimeout(()=>{t.value=!1},3e3),c({type:"success",title:"Copy Success",message:l+" copied to clipboard"})}catch(q){c({type:"danger",title:"Error",message:q})}},V=async l=>{L("Path",l)};function z(l=null){const C=new Blob([r.getValue()],{type:"text/plain;charset=utf-8"});A.saveAs(C,l??"template.yml")}function P(){r.focus(),r.getAction("actions.find").run()}return{checkDarkModeIsSet:m,checkLineNumbersIsSet:B,checkMiniMapIsSet:F,copy:L,copyPath:V,darkmode:s,download:z,lineNumbers:_,minimap:u,search:P,showConfigFullScreen:y,toggleEditorDarkMode:T,toggleEditorLineNumbers:O,toggleEditorMinimap:j}}},f=d=>(U("data-v-fd5c46cf"),d=d(),W(),d),Ee={class:"pf-c-card"},Te={class:"pf-c-card__header pf-l-flex"},Oe=f(()=>e("h2",{class:"pf-c-title pf-m-xl pf-l-flex__item"},"Config Output",-1)),je={class:"pf-c-check pf-l-flex__item pf-m-align-right"},Ve=["checked"],ze=f(()=>e("label",{class:"pf-c-check__label",style:{cursor:"default",color:"#6a6e73"}},[e("small",null,"Dark Mode")],-1)),Pe={class:"pf-c-check",style:{"align-content":"center"}},$e=["checked"],qe=f(()=>e("label",{class:"pf-c-check__label pf-u-hidden pf-u-display-inline-block-on-md",style:{cursor:"default",color:"#6a6e73"}},[e("small",null,"Line Numbers")],-1)),Ae={class:"pf-c-check",style:{"align-content":"center"}},Re=["checked"],Ge=f(()=>e("label",{class:"pf-c-check__label pf-u-hidden pf-u-display-inline-block-on-md",style:{cursor:"default",color:"#6a6e73"}},[e("small",null,"Minimap")],-1)),He={class:"pf-c-card__body"},Ke={class:"pf-c-code-editor"},Ue={class:"pf-c-code-editor__header"},We={class:"pf-c-code-editor__controls"},Je=f(()=>e("br",null,null,-1)),Qe=f(()=>e("span",{class:"pf-c-button__icon pf-m-start"},[e("i",{class:"fas fa-copy","aria-hidden":"true"})],-1)),Xe=f(()=>e("span",{class:"pf-c-button__icon pf-m-start"},[e("i",{class:"fas fa-copy","aria-hidden":"true"})],-1)),Ye=f(()=>e("span",{class:"pf-c-button__icon pf-m-start"},[e("i",{class:"fa fa-download","aria-hidden":"true"})],-1)),Ze=f(()=>e("span",{class:"pf-c-button__icon pf-m-start"},[e("i",{class:"fa fa-search","aria-hidden":"true"})],-1)),et=f(()=>e("i",{class:"fas fa-expand"},null,-1)),tt=[et],ot=f(()=>e("div",{class:"pf-c-code-editor__tab"},[e("span",{class:"pf-c-code-editor__tab-icon"},[e("i",{class:"fas fa-code"})]),e("span",{class:"pf-c-code-editor__tab-text"},"Configuration")],-1)),it=f(()=>e("div",{class:"pf-c-code-editor__main",id:"pf-c-code-editor__main"},[e("code",{class:"pf-c-code-editor__code"},[e("div",{class:"pf-c-code-editor__code-pre",id:"code-editor",style:{height:"80vh"}})])],-1));function nt(d,o,n,t,c,p){return b(),k("div",Ee,[e("div",Te,[Oe,e("div",je,[e("input",{class:"pf-c-check__input",type:"checkbox",id:"darkmode",name:"darkmode",onChange:o[0]||(o[0]=i=>t.toggleEditorDarkMode(i)),checked:t.darkmode=="vs-dark",style:{"margin-left":"0.5rem"}},null,40,Ve),ze]),e("div",Pe,[e("input",{class:"pf-c-check__input pf-u-hidden pf-u-display-inline-block-on-md",type:"checkbox",id:"lineNumbers",name:"lineNumbers",onChange:o[1]||(o[1]=i=>t.toggleEditorLineNumbers(i)),checked:t.lineNumbers=="on",style:{"margin-left":"0.5rem"}},null,40,$e),qe]),e("div",Ae,[e("input",{class:"pf-c-check__input pf-u-hidden pf-u-display-inline-block-on-md",type:"checkbox",id:"lineNumbers",name:"lineNumbers",onChange:o[2]||(o[2]=i=>t.toggleEditorMinimap(i)),checked:t.minimap.enabled=="true",style:{"margin-left":"0.5rem"}},null,40,Re),Ge])]),e("div",He,[e("div",Ke,[e("div",Ue,[e("div",We,[Je,e("button",{class:"pf-c-button pf-m-small pf-m-control",type:"button",label:"Copy to clipboard",title:"Copy to clipboard",onClick:o[3]||(o[3]=i=>t.copy("Config")),style:{"padding-left":"6px","padding-right":"6px"}},[Qe,x(" Config ")]),e("button",{class:"pf-c-button pf-m-small pf-m-control pf-u-hidden pf-u-display-inline-block-on-md",type:"button",label:"Copy to clipboard",title:"Copy to clipboard",onClick:o[4]||(o[4]=i=>t.copyPath(n.configModel.config_location)),style:{"padding-left":"6px","padding-right":"6px"}},[Xe,x(" Path ")]),e("button",{class:"pf-c-button pf-m-small pf-m-control pf-u-hidden pf-u-display-inline-block-on-md",type:"button",label:"Download config file",title:"Download config file",onClick:o[5]||(o[5]=i=>t.download(n.configModel.config_filename)),style:{"padding-left":"6px","padding-right":"6px"}},[Ye,x(" Download ")]),e("button",{class:"pf-c-button pf-m-small pf-m-control pf-u-hidden pf-u-display-inline-block-on-md",type:"button",label:"Search the config file",title:"Search the config file",onClick:o[6]||(o[6]=(...i)=>t.search&&t.search(...i)),style:{"padding-left":"6px","padding-right":"6px"}},[Ze,x(" Search ")]),e("button",{class:"pf-c-button pf-m-control pf-u-hidden pf-u-display-inline-block-on-md",type:"button",title:"full screen",alt:"full screen",onClick:o[7]||(o[7]=(...i)=>t.showConfigFullScreen&&t.showConfigFullScreen(...i))},tt)]),ot]),it])])])}const st=I(Le,[["render",nt],["__scopeId","data-v-fd5c46cf"]]),ct={props:{},components:{ConfigFullScreenView:R,DevicesBreadcrumbs:te,PageHeader:ie,LoadingSpinner:oe,MonacoCodePanel:st,DeviceViewConfigDetailsPanel:Fe,SideDrawer:ne},setup(d){const n=J().params.id,t=M({}),c=M({editid:0,pagename:"Configuration",pagedesc:"View your downloaded configuration file",pagenamesingle:"config",modelName:"configs",isLoading:!1,modelObject:{code:""}}),p=g(!1),i=D("create-notification");N(()=>{w(),window.addEventListener("wheel",v,{passive:!0})}),Q(()=>{window.removeEventListener("wheel",v)});function w(){c.isLoading=!0,axios.get("/api/configs/"+n).then(s=>{Object.assign(t,s.data),c.isLoading=!1}).catch(s=>{i({type:"danger",title:"Error",message:s.response}),c.isLoading=!1})}function r(s){c.modelObject.code=s.code,c.modelObject.filename=s.filename,c.modelObject.language="default",c.configFullScreen=!0}const v=s=>{document.getElementById("top_div").getBoundingClientRect().top<0?p.value=!0:p.value=!1};function y(){document.getElementById("top_div").scrollIntoView({behavior:"smooth"})}return{configModel:t,config_id:n,scrollToTop:y,showScrollBtn:p,showConfigFullScreen:r,viewstate:c}}},lt={class:"pf-c-page__main",tabindex:"-1"},at=e("div",{class:"pf-c-divider",role:"separator"},null,-1),dt={class:"pf-c-page__main-section pf-m-no-padding"},rt={class:"pf-c-drawer__main"},ft={class:"pf-c-drawer__content pf-m-no-background"},pt={class:"pf-c-drawer__body pf-m-padding"},_t={key:0,class:"pf-l-grid pf-m-gutter"},ut={class:"pf-l-grid__item pf-m-12-col pf-m-3-col-on-md"},mt={class:"pf-l-grid__item pf-m-12-col pf-m-9-col-on-md"},gt={key:1,class:"pf-c-back-to-top"},ht=e("span",{class:"pf-c-button__icon pf-m-end"},[e("i",{class:"fas fa-angle-up","aria-hidden":"true"})],-1);function vt(d,o,n,t,c,p){const i=h("devices-breadcrumbs"),w=h("page-header"),r=h("loading-spinner"),v=h("device-view-config-details-panel"),y=h("monaco-code-panel"),s=h("config-full-screen-view");return b(),k(ee,null,[e("main",lt,[a(w,{pagename:t.viewstate.pagename},{breadcrumbs:X(()=>[a(i,{devicename:t.configModel.device_name,deviceId:t.configModel.device_id},null,8,["devicename","deviceId"])]),_:1},8,["pagename"]),at,e("section",dt,[e("div",{class:Y(["pf-c-drawer",{"pf-m-expanded":t.viewstate.openDrawerState}]),id:"top_div"},[e("div",rt,[e("div",ft,[e("div",pt,[a(r,{showSpinner:t.viewstate.isLoading},null,8,["showSpinner"]),t.viewstate.isLoading?S("",!0):(b(),k("div",_t,[e("div",ut,[a(v,{configModel:t.configModel},null,8,["configModel"])]),e("div",mt,[a(y,{config_id:t.config_id,viewstate:t.viewstate,configModel:t.configModel,onShowConfigFullScreen:o[0]||(o[0]=m=>t.showConfigFullScreen(m))},null,8,["config_id","viewstate","configModel"])])])),t.showScrollBtn?(b(),k("div",gt,[e("button",{class:"pf-c-button pf-m-primary",onClick:o[1]||(o[1]=(...m)=>t.scrollToTop&&t.scrollToTop(...m))},[x(" Back to top "),ht])])):S("",!0)])])])],2)])]),t.viewstate.configFullScreen?(b(),Z(s,{key:0,onCloseModal:o[2]||(o[2]=m=>t.viewstate.configFullScreen=!1),editid:t.viewstate.editid,code:t.viewstate.modelObject.code,filename:t.viewstate.modelObject.filename,language:t.viewstate.modelObject.language},null,8,["editid","code","filename","language"])):S("",!0)],64)}const Dt=I(ct,[["render",vt]]);export{Dt as default};