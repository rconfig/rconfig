import{D as f}from"./DataTableSpinner-z8HceaJB.js";import{e as l,k as m,s as u,c,a as s,q as g,j as r,t as o,d as a,l as v,p as b,o as e}from"./app-C9AuNpcX.js";/* empty css               */const x={props:{},components:{DataTableSpinner:f},setup(_){const t=m({isLoading:!1,error:null,info:{version:"",rconfig_sub_id:"",rconfig_sub_name:"",rconfig_sub_status:"",rconfig_sub_exiry:""}}),d=v("create-notification");u(()=>{t.isLoading=!0,i()});function i(){axios.get("/api/settings/support-info").then(n=>{Object.assign(t.info,n.data.data),t.isLoading=!1,t.error=null}).catch(n=>{d({type:"error",message:n.response.data.message})})}return{state:t}}},h={class:"pf-c-panel pf-m-raised"},y={class:"pf-c-panel__main"},L={class:"pf-c-panel__main-body"},N={key:0,class:"pf-l-flex",style:{"justify-content":"center"}},k={class:"pf-l-flex__item"},w={key:1,class:"pf-m-gutter"},S={class:"pf-c-card"},j={class:"pf-c-card__body"},B={class:"pf-c-description-list"},C={class:"pf-c-description-list__group"},D={class:"pf-c-description-list__description"},V={class:"pf-c-description-list__text"},I={class:"pf-c-description-list__group"},A={class:"pf-c-description-list__description"},E={class:"pf-c-description-list__text"},O={class:"pf-c-description-list__group"},T={class:"pf-c-description-list__description"},q={class:"pf-c-description-list__text"},H={class:"pf-c-description-list__group"},M={class:"pf-c-description-list__description"},z={class:"pf-c-description-list__text"},F={class:"pf-c-description-list__group"},G={class:"pf-c-description-list__description"},J={class:"pf-c-description-list__text"},K={class:"pf-c-card__footer"},P=["href"];function Q(_,t,d,i,n,R){const p=b("data-table-spinner");return e(),c("div",h,[t[11]||(t[11]=s("div",{class:"pf-c-panel__header"},"About",-1)),t[12]||(t[12]=s("hr",{class:"pf-c-divider"},null,-1)),s("div",y,[s("div",L,[i.state.isLoading?(e(),c("div",N,[s("div",k,[i.state.isLoading?(e(),g(p,{key:0})):r("",!0)])])):r("",!0),i.state.isLoading?r("",!0):(e(),c("div",w,[s("div",S,[t[9]||(t[9]=s("div",{class:"pf-c-card__title"},[s("h2",{class:"pf-c-title pf-m-xl"},"License and Support Information")],-1)),s("div",j,[s("dl",B,[s("div",C,[t[0]||(t[0]=s("dt",{class:"pf-c-description-list__term"},[s("span",{class:"pf-c-description-list__text"},"Version ")],-1)),s("dd",D,[s("div",V,o(i.state.info.version?i.state.info.version:"No information found!"),1)])]),s("div",I,[t[1]||(t[1]=s("dt",{class:"pf-c-description-list__term"},[s("span",{class:"pf-c-description-list__text"},"License ID")],-1)),s("dd",A,[s("div",E,o(i.state.info.rconfig_sub_id?i.state.info.rconfig_sub_id:"No information found!"),1)])]),s("div",O,[t[2]||(t[2]=s("dt",{class:"pf-c-description-list__term"},[s("span",{class:"pf-c-description-list__text"},"Licensee Name")],-1)),s("dd",T,[s("div",q,o(i.state.info.rconfig_sub_name?i.state.info.rconfig_sub_name:"No information found!"),1)])]),s("div",H,[t[3]||(t[3]=s("dt",{class:"pf-c-description-list__term"},[s("span",{class:"pf-c-description-list__text"},"License Status")],-1)),s("dd",M,[s("div",z,o(i.state.info.rconfig_sub_status?i.state.info.rconfig_sub_status:"No information found!"),1)])]),s("div",F,[t[4]||(t[4]=s("dt",{class:"pf-c-description-list__term"},[s("span",{class:"pf-c-description-list__text"},"License Expiry")],-1)),s("dd",G,[s("div",J,o(i.state.info.rconfig_sub_exiry?i.state.info.rconfig_sub_exiry:"No information found!"),1)])])])]),t[10]||(t[10]=s("hr",{class:"pf-c-divider"},null,-1)),s("div",K,[t[5]||(t[5]=s("a",{href:"https://www.rconfig.com/eula",target:"_blank"},"License",-1)),t[6]||(t[6]=a(" | ")),s("a",{href:"mailto:support@rconfig.com?subject=rConfig Support from "+i.state.info.rconfig_sub_name},"Contact Support",8,P),t[7]||(t[7]=a(" | ")),t[8]||(t[8]=s("a",{href:"https://www.rconfig.com/docs/5.1/getstarted/overview",target:"_blank"},"Online Help",-1))])])]))])])])}const Y=l(x,[["render",Q]]);export{Y as default};
