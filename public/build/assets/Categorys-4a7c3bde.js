import{a as O,u as L}from"./ViewFunctions-c2f77989.js";import{P}from"./PageHeader-681f21f0.js";import{D as V}from"./DataTable-b91c6858.js";import{S as q}from"./SideDrawer-b60c3507.js";import{b as F}from"./DeleteModal-b3198555.js";import{_ as x,f as D,g as M,r as m,o as i,c as g,b,a,n as u,v as h,t as C,h as v,p as T,F as N,e as S,q as j,l as w,w as k}from"./app-33ff2348.js";import{L as B}from"./LoadingSpinner-acc99a56.js";import"./DataTableToolbar-66f7472b.js";import"./DataTableSpinner-dedf6af0.js";/* empty css            */const R={props:{viewstate:{type:Object}},emits:["closeDrawer","formsubmitted"],components:{LoadingSpinner:B},setup(t,{emit:o}){const d=D(!1),e=D(t.viewstate.editid===0?"add":"edit"),{errors:c,model:n,clearModel:l,updateModel:r,getModel:p,storeModel:f,isLoading:_}=O(t.viewstate.modelName,t.viewstate.modelObject);M(()=>{p(t.viewstate.editid)});const s=async()=>{t.viewstate.editid!=0?await r(n):await f(n),c.value===""&&(o("formsubmitted",t.viewstate.pagenamesingle+" "+e.value+"ed!"),y())};function y(){o("closeDrawer")}return{showRoleOptions:d,close:y,errors:c,model:n,saveModels:s,clearModel:l,isLoading:_}}},K={key:0,novalidate:"",class:"pf-c-form"},U=["value"],z={class:"pf-c-form__group"},E=a("div",{class:"pf-c-form__group-label"},[a("label",{class:"pf-c-form__label",for:"form-demo-basic-name"},[a("span",{class:"pf-c-form__label-text"},"Category Name"),a("span",{class:"pf-c-form__label-required","aria-hidden":"true"},"*")])],-1),H={class:"pf-c-form__group-control"},I=["aria-invalid"],A={key:0,class:"pf-c-form__helper-text pf-m-error",id:"form-help-text-address-helper","aria-live":"polite"},G={class:"pf-c-form__group"},J=a("div",{class:"pf-c-form__group-label"},[a("label",{class:"pf-c-form__label",for:"form-demo-basic-name"},[a("span",{class:"pf-c-form__label-text"},"Description"),a("span",{class:"pf-c-form__label-required","aria-hidden":"true"},"*")])],-1),Q={class:"pf-c-form__group-control"},W=["aria-invalid"],X={key:0,class:"pf-c-form__helper-text pf-m-error",id:"form-help-text-address-helper","aria-live":"polite"},Y={class:"pf-c-form__group pf-m-action"},Z={class:"pf-c-form__group-control"},$={class:"pf-c-form__actions"};function ee(t,o,d,e,c,n){const l=m("loading-spinner");return i(),g(N,null,[b(l,{showSpinner:e.isLoading},null,8,["showSpinner"]),e.isLoading?v("",!0):(i(),g("form",K,[a("input",{id:"editid",name:"editid",type:"hidden",value:d.viewstate.editid},null,8,U),a("div",z,[E,a("div",H,[u(a("input",{class:"pf-c-form-control",required:"",type:"text",id:"categoryName",name:"categoryName","onUpdate:modelValue":o[0]||(o[0]=r=>e.model.categoryName=r),"aria-invalid":!!e.errors.categoryName,autocomplete:"off"},null,8,I),[[h,e.model.categoryName]]),e.errors.categoryName?(i(),g("p",A,C(e.errors.categoryName[0]),1)):v("",!0)])]),a("div",G,[J,a("div",Q,[u(a("input",{class:"pf-c-form-control",required:"",type:"text",id:"categoryDescription",name:"categoryDescription","onUpdate:modelValue":o[1]||(o[1]=r=>e.model.categoryDescription=r),"aria-invalid":!!e.errors.categoryDescription,autocomplete:"off"},null,8,W),[[h,e.model.categoryDescription]]),e.errors.categoryDescription?(i(),g("p",X,C(e.errors.categoryDescription[0]),1)):v("",!0)])]),a("div",Y,[a("div",Z,[a("div",$,[a("button",{class:"pf-c-button pf-m-primary",type:"submit",onClick:o[2]||(o[2]=T((...r)=>e.saveModels&&e.saveModels(...r),["prevent"]))},"Save"),a("button",{class:"pf-c-button pf-m-link",type:"button",onClick:o[3]||(o[3]=(...r)=>e.close&&e.close(...r))},"Cancel")])])])]))],64)}const ae=x(R,[["render",ee]]),oe={components:{CategorysForm:ae,PageHeader:P,DataTable:V,SideDrawer:q,DeleteModal:F},setup(){const t=S({editid:0,pagename:"Categorys",pagedesc:"rConfig system categorys",pagenamesingle:"Category",modelName:"categories",openDrawerState:!1,showDeleteModal:!1,sideDrawerComponentKey:1,pageOptionsState:{page:1,per_page:10},modelObject:{tagname:"",tagDescription:""}}),{models:o,isLoading:d,dataTablePageChanged:e,openDrawer:c,closeDrawerState:n,deleteRow:l,formSubmitted:r,confirmDelete:p,destroyModel:f}=L(t,t.modelName,t.modelObject);M(()=>{e(t.pageOptionsState)});const _=S({headers:[{key:"id",label:"ID",sortable:!0,error:"Can't be blank"},{key:"categoryName",label:"Category Name",sortable:!0,error:"Can't be blank"},{key:"categoryDescription",label:"Description",sortable:!1},{key:"device_count",label:"Devices Count",sortable:!1,deviceCountType:"category"}],data:o,isLoading:d});return{viewstate:t,dataTablePageChanged:e,openDrawer:c,closeDrawerState:n,deleteRow:l,confirmDelete:p,table:_,destroyModel:f,formSubmitted:r}}},te={class:"pf-c-page__main",tabindex:"-1"},re=a("div",{class:"pf-c-divider",role:"separator"},null,-1),se={class:"pf-c-page__main-section pf-m-no-padding"},ie={class:"pf-c-drawer__main"},ne=a("div",{class:"pf-l-flex__item"},"Please complete all fields",-1);function le(t,o,d,e,c,n){const l=m("page-header"),r=m("data-table"),p=m("categorys-form"),f=m("side-drawer"),_=m("delete-modal");return i(),g(N,null,[a("main",te,[b(l,{pagename:e.viewstate.pagename,desc:e.viewstate.pagedesc},null,8,["pagename","desc"]),re,a("section",se,[a("div",{class:j(["pf-c-drawer",{"pf-m-expanded":e.viewstate.openDrawerState}])},[a("div",ie,[b(r,{pagename:e.viewstate.pagenamesingle,tabledata:e.table,onPagechanged:e.dataTablePageChanged,onOpenDrawer:o[0]||(o[0]=s=>e.openDrawer(s)),onDeleteRow:o[1]||(o[1]=s=>e.deleteRow(s))},null,8,["pagename","tabledata","onPagechanged"]),(i(),w(f,{pagename:e.viewstate.pagenamesingle,drawerState:e.viewstate.openDrawerState,editid:e.viewstate.editid,onCloseDrawer:o[3]||(o[3]=s=>e.viewstate.openDrawerState=!1),key:e.viewstate.sideDrawerComponentKey},{subtext:k(()=>[ne]),form:k(()=>[(i(),w(p,{viewstate:e.viewstate,onCloseDrawer:e.closeDrawerState,onFormsubmitted:o[2]||(o[2]=s=>e.formSubmitted(s)),key:e.viewstate.editid},null,8,["viewstate","onCloseDrawer"]))]),_:1},8,["pagename","drawerState","editid"]))])],2)])]),e.viewstate.showDeleteModal?(i(),w(_,{key:0,editid:e.viewstate.editid,onCloseModal:o[4]||(o[4]=s=>e.viewstate.showDeleteModal=!1),onConfirmDelete:e.confirmDelete},null,8,["editid","onConfirmDelete"])):v("",!0)],64)}const ye=x(oe,[["render",le]]);export{ye as default};
