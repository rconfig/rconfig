import{h as t,v as e,C as M,U as n}from"./app-CJzaFoze.js";/**
 * @license lucide-vue-next v0.525.0 - ISC
 *
 * This source code is licensed under the ISC license.
 * See the LICENSE file in the root directory of this source tree.
 */const L=t("chart-column-increasing",[["path",{d:"M13 17V9",key:"1fwyjl"}],["path",{d:"M18 17V5",key:"sfb6ij"}],["path",{d:"M3 3v16a2 2 0 0 0 2 2h16",key:"c24i48"}],["path",{d:"M8 17v-3",key:"17ska0"}]]);/**
 * @license lucide-vue-next v0.525.0 - ISC
 *
 * This source code is licensed under the ISC license.
 * See the LICENSE file in the root directory of this source tree.
 */const b=t("chart-column",[["path",{d:"M3 3v16a2 2 0 0 0 2 2h16",key:"c24i48"}],["path",{d:"M18 17V9",key:"2bz60n"}],["path",{d:"M13 17V5",key:"1frdt8"}],["path",{d:"M8 17v-3",key:"17ska0"}]]);/**
 * @license lucide-vue-next v0.525.0 - ISC
 *
 * This source code is licensed under the ISC license.
 * See the LICENSE file in the root directory of this source tree.
 */const w=t("maximize-2",[["path",{d:"M15 3h6v6",key:"1q9fwt"}],["path",{d:"m21 3-7 7",key:"1l2asr"}],["path",{d:"m3 21 7-7",key:"tjx5ai"}],["path",{d:"M9 21H3v-6",key:"wtvkvv"}]]);/**
 * @license lucide-vue-next v0.525.0 - ISC
 *
 * This source code is licensed under the ISC license.
 * See the LICENSE file in the root directory of this source tree.
 */const E=t("minimize-2",[["path",{d:"m14 10 7-7",key:"oa77jy"}],["path",{d:"M20 10h-6V4",key:"mjg0md"}],["path",{d:"m3 21 7-7",key:"tjx5ai"}],["path",{d:"M4 14h6v6",key:"rmj7iw"}]]);/**
 * @license lucide-vue-next v0.525.0 - ISC
 *
 * This source code is licensed under the ISC license.
 * See the LICENSE file in the root directory of this source tree.
 */const C=t("settings-2",[["path",{d:"M14 17H5",key:"gfn3mx"}],["path",{d:"M19 7h-9",key:"6i9tg"}],["circle",{cx:"17",cy:"17",r:"3",key:"18b49y"}],["circle",{cx:"7",cy:"7",r:"3",key:"dfmy0x"}]]);/**
 * @license lucide-vue-next v0.525.0 - ISC
 *
 * This source code is licensed under the ISC license.
 * See the LICENSE file in the root directory of this source tree.
 */const j=t("trending-down",[["path",{d:"M16 17h6v-6",key:"t6n2it"}],["path",{d:"m22 17-8.5-8.5-5 5L2 7",key:"x473p"}]]);/**
 * @license lucide-vue-next v0.525.0 - ISC
 *
 * This source code is licensed under the ISC license.
 * See the LICENSE file in the root directory of this source tree.
 */const z=t("trending-up",[["path",{d:"M16 7h6v6",key:"box55l"}],["path",{d:"m22 7-8.5 8.5-5-5L2 17",key:"1t1m79"}]]);function D(){const f=e([]),d=e([]),y=e([]),p=e([]),r=e(!1),i=e(!1),c=e(!1),h=e(!1),{toastSuccess:u,toastError:s}=M();async function v(o=!1,a={}){r.value=!0;try{const l=await n.get("/api/dashboard/sysinfo?clearcache="+o);f.value=l.data}catch(l){console.error("Error fetching sysinfo:",l),s("Error","Failed to fetch sysinfo.")}finally{r.value=!1}}async function g(o={}){i.value=!0;try{const a=await n.get("/api/dashboard/configinfo");d.value=a.data}catch(a){console.error("Error fetching configinfo:",a),s("Error","Failed to fetch configinfo.")}finally{i.value=!1}}async function m(o={}){c.value=!0;try{const a=await n.get("/api/dashboard/health-latest");y.value=a.data}catch(a){console.error("Error fetching healthLatest:",a),s("Error","Failed to fetch healthLatest.")}finally{c.value=!1}}async function k(o={}){h.value=!0;try{const a=await n.get("/api/dashboard/latest-devices");p.value=a.data}catch(a){console.error("Error fetching latest devices:",a),s("Error","Failed to fetch latest devices.")}finally{h.value=!1}}return{fetchSysinfo:v,fetchConfiginfo:g,fetchHealth:m,fetchLatestDevices:k,sysinfo:f,configinfo:d,healthLatest:y,latestDevices:p,isLoadingSysinfo:r,isLoadingConfiginfo:i,isLoadingHealth:c,isLoadingLatestDevices:h,toastSuccess:u,toastError:s}}export{L as C,w as M,C as S,z as T,b as a,j as b,E as c,D as u};
