import{h as t,x as e,E as k,W as h}from"./app-CTnbTVu7.js";/**
 * @license lucide-vue-next v0.525.0 - ISC
 *
 * This source code is licensed under the ISC license.
 * See the LICENSE file in the root directory of this source tree.
 */const v=t("badge-check",[["path",{d:"M3.85 8.62a4 4 0 0 1 4.78-4.77 4 4 0 0 1 6.74 0 4 4 0 0 1 4.78 4.78 4 4 0 0 1 0 6.74 4 4 0 0 1-4.77 4.78 4 4 0 0 1-6.75 0 4 4 0 0 1-4.78-4.77 4 4 0 0 1 0-6.76Z",key:"3c2336"}],["path",{d:"m9 12 2 2 4-4",key:"dzmm74"}]]);/**
 * @license lucide-vue-next v0.525.0 - ISC
 *
 * This source code is licensed under the ISC license.
 * See the LICENSE file in the root directory of this source tree.
 */const L=t("chart-column-increasing",[["path",{d:"M13 17V9",key:"1fwyjl"}],["path",{d:"M18 17V5",key:"sfb6ij"}],["path",{d:"M3 3v16a2 2 0 0 0 2 2h16",key:"c24i48"}],["path",{d:"M8 17v-3",key:"17ska0"}]]);/**
 * @license lucide-vue-next v0.525.0 - ISC
 *
 * This source code is licensed under the ISC license.
 * See the LICENSE file in the root directory of this source tree.
 */const w=t("lock",[["rect",{width:"18",height:"11",x:"3",y:"11",rx:"2",ry:"2",key:"1w4ew1"}],["path",{d:"M7 11V7a5 5 0 0 1 10 0v4",key:"fwvmzm"}]]);/**
 * @license lucide-vue-next v0.525.0 - ISC
 *
 * This source code is licensed under the ISC license.
 * See the LICENSE file in the root directory of this source tree.
 */const C=t("shield",[["path",{d:"M20 13c0 5-3.5 7.5-7.66 8.95a1 1 0 0 1-.67-.01C7.5 20.5 4 18 4 13V6a1 1 0 0 1 1-1c2 0 4.5-1.2 6.24-2.72a1.17 1.17 0 0 1 1.52 0C14.51 3.81 17 5 19 5a1 1 0 0 1 1 1z",key:"oel41y"}]]);/**
 * @license lucide-vue-next v0.525.0 - ISC
 *
 * This source code is licensed under the ISC license.
 * See the LICENSE file in the root directory of this source tree.
 */const E=t("trending-up",[["path",{d:"M16 7h6v6",key:"box55l"}],["path",{d:"m22 7-8.5 8.5-5-5L2 17",key:"1t1m79"}]]);function M(){const f=e([]),l=e([]),d=e([]),s=e(!1),r=e(!1),n=e(!1),{toastSuccess:y,toastError:o}=k();async function u(c=!1,a={}){s.value=!0;try{const i=await h.get("/api/dashboard/sysinfo?clearcache="+c);f.value=i.data}catch(i){console.error("Error fetching sysinfo:",i),o("Error","Failed to fetch sysinfo.")}finally{s.value=!1}}async function p(c={}){r.value=!0;try{const a=await h.get("/api/dashboard/configinfo");l.value=a.data}catch(a){console.error("Error fetching configinfo:",a),o("Error","Failed to fetch configinfo.")}finally{r.value=!1}}async function g(c={}){n.value=!0;try{const a=await h.get("/api/dashboard/health-latest");d.value=a.data}catch(a){console.error("Error fetching healthLatest:",a),o("Error","Failed to fetch healthLatest.")}finally{n.value=!1}}return{fetchSysinfo:u,fetchConfiginfo:p,fetchHealth:g,sysinfo:f,configinfo:l,healthLatest:d,isLoadingSysinfo:s,isLoadingConfiginfo:r,isLoadingHealth:n,toastSuccess:y,toastError:o}}export{v as B,L as C,w as L,C as S,E as T,M as u};
