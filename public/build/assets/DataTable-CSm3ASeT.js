import{b as c,j as r,r as A}from"./app-BH7GS2Si.js";/**
 * @license lucide-react v1.21.0 - ISC
 *
 * This source code is licensed under the ISC license.
 * See the LICENSE file in the root directory of this source tree.
 */const W=(...a)=>a.filter((n,l,d)=>!!n&&n.trim()!==""&&d.indexOf(n)===l).join(" ").trim();/**
 * @license lucide-react v1.21.0 - ISC
 *
 * This source code is licensed under the ISC license.
 * See the LICENSE file in the root directory of this source tree.
 */const U=a=>a.replace(/([a-z0-9])([A-Z])/g,"$1-$2").toLowerCase();/**
 * @license lucide-react v1.21.0 - ISC
 *
 * This source code is licensed under the ISC license.
 * See the LICENSE file in the root directory of this source tree.
 */const B=a=>a.replace(/^([A-Z])|[\s-_]+(\w)/g,(n,l,d)=>d?d.toUpperCase():l.toLowerCase());/**
 * @license lucide-react v1.21.0 - ISC
 *
 * This source code is licensed under the ISC license.
 * See the LICENSE file in the root directory of this source tree.
 */const D=a=>{const n=B(a);return n.charAt(0).toUpperCase()+n.slice(1)};/**
 * @license lucide-react v1.21.0 - ISC
 *
 * This source code is licensed under the ISC license.
 * See the LICENSE file in the root directory of this source tree.
 */var F={xmlns:"http://www.w3.org/2000/svg",width:24,height:24,viewBox:"0 0 24 24",fill:"none",stroke:"currentColor",strokeWidth:2,strokeLinecap:"round",strokeLinejoin:"round"};/**
 * @license lucide-react v1.21.0 - ISC
 *
 * This source code is licensed under the ISC license.
 * See the LICENSE file in the root directory of this source tree.
 */const Z=a=>{for(const n in a)if(n.startsWith("aria-")||n==="role"||n==="title")return!0;return!1},X=c.createContext({}),G=()=>c.useContext(X),J=c.forwardRef(({color:a,size:n,strokeWidth:l,absoluteStrokeWidth:d,className:b="",children:x,iconNode:w,...y},j)=>{const{size:g=24,strokeWidth:f=2,absoluteStrokeWidth:u=!1,color:v="currentColor",className:p=""}=G()??{},C=d??u?Number(l??f)*24/Number(n??g):l??f;return c.createElement("svg",{ref:j,...F,width:n??g??F.width,height:n??g??F.height,stroke:a??v,strokeWidth:C,className:W("lucide",p,b),...!x&&!Z(y)&&{"aria-hidden":"true"},...y},[...w.map(([m,S])=>c.createElement(m,S)),...Array.isArray(x)?x:[x]])});/**
 * @license lucide-react v1.21.0 - ISC
 *
 * This source code is licensed under the ISC license.
 * See the LICENSE file in the root directory of this source tree.
 */const h=(a,n)=>{const l=c.forwardRef(({className:d,...b},x)=>c.createElement(J,{ref:x,iconNode:n,className:W(`lucide-${U(D(a))}`,`lucide-${a}`,d),...b}));return l.displayName=D(a),l};/**
 * @license lucide-react v1.21.0 - ISC
 *
 * This source code is licensed under the ISC license.
 * See the LICENSE file in the root directory of this source tree.
 */const Q=[["path",{d:"m6 9 6 6 6-6",key:"qrunsl"}]],L=h("chevron-down",Q);/**
 * @license lucide-react v1.21.0 - ISC
 *
 * This source code is licensed under the ISC license.
 * See the LICENSE file in the root directory of this source tree.
 */const V=[["path",{d:"m15 18-6-6 6-6",key:"1wnfg3"}]],Y=h("chevron-left",V);/**
 * @license lucide-react v1.21.0 - ISC
 *
 * This source code is licensed under the ISC license.
 * See the LICENSE file in the root directory of this source tree.
 */const K=[["path",{d:"m9 18 6-6-6-6",key:"mthhwq"}]],E=h("chevron-right",K);/**
 * @license lucide-react v1.21.0 - ISC
 *
 * This source code is licensed under the ISC license.
 * See the LICENSE file in the root directory of this source tree.
 */const ee=[["path",{d:"m18 15-6-6-6 6",key:"153udz"}]],R=h("chevron-up",ee);/**
 * @license lucide-react v1.21.0 - ISC
 *
 * This source code is licensed under the ISC license.
 * See the LICENSE file in the root directory of this source tree.
 */const re=[["path",{d:"M10 20a1 1 0 0 0 .553.895l2 1A1 1 0 0 0 14 21v-7a2 2 0 0 1 .517-1.341L21.74 4.67A1 1 0 0 0 21 3H3a1 1 0 0 0-.742 1.67l7.225 7.989A2 2 0 0 1 10 14z",key:"sc7q7i"}]],te=h("funnel",re);/**
 * @license lucide-react v1.21.0 - ISC
 *
 * This source code is licensed under the ISC license.
 * See the LICENSE file in the root directory of this source tree.
 */const se=[["path",{d:"M18 6 6 18",key:"1bl5f8"}],["path",{d:"m6 6 12 12",key:"d8bk6v"}]],ae=h("x",se);function le({columns:a=[],data:n=[],pagination:l=null,filterConfig:d=[],filterValues:b={},sortColumn:x=null,sortDirection:w="asc",sortableColumns:y=[],baseUrl:j=null}){const[g,f]=c.useState({}),[u,v]=c.useState(b),[p,C]=c.useState(x),[m,S]=c.useState(w),[_,I]=c.useState(!1),M=e=>{f(t=>({...t,[e]:!t[e]}))},P=(e,t,o)=>{if(e.renderDetail)return e.renderDetail(t,o)},$=(e=u,t=p,o=m)=>{if(!j)return;const i=new URLSearchParams;Object.entries(e).forEach(([s,N])=>{N!==""&&N!==null&&N!==void 0&&i.set(s,N)}),t&&(i.set("sort",t),i.set("direction",o)),l!=null&&l.current_page&&l.current_page>1&&i.set("page",l.current_page),A.visit(`${j}?${i.toString()}`,{preserveScroll:!0,preserveState:!0,replace:!0})},k=(e,t)=>{const o={...u,[e]:t};v(o),$(o)},O=e=>{if(!y.includes(e))return;let t="asc";p===e&&m==="asc"&&(t="desc"),C(e),S(t),$(u,e,t)},T=()=>{const e={};d.forEach(t=>{e[t.key]=""}),v(e),$(e,null,"asc")},z=c.useMemo(()=>Object.values(u).some(e=>e!==""&&e!==null&&e!==void 0),[u]),q=e=>{var o,i;const t=u[e.key]||"";switch(e.type){case"select":return r.jsxs("select",{value:t,onChange:s=>k(e.key,s.target.value),className:"w-full rounded-lg border border-gray-300 bg-white px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500 dark:border-gray-600 dark:bg-gray-800",children:[r.jsxs("option",{value:"",children:["All ",e.label]}),(o=e.options)==null?void 0:o.map(s=>r.jsx("option",{value:s.value,children:s.label},s.value))]});case"autocomplete":return r.jsxs(r.Fragment,{children:[r.jsx("input",{list:`${e.key}-options`,value:t,onChange:s=>k(e.key,s.target.value),className:"w-full rounded-lg border border-gray-300 bg-white px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500 dark:border-gray-600 dark:bg-gray-800",placeholder:e.placeholder||`Search ${e.label}...`}),r.jsx("datalist",{id:`${e.key}-options`,children:(i=e.options)==null?void 0:i.map(s=>r.jsx("option",{value:s.value,children:s.label},s.value))})]});case"date":return r.jsx("input",{type:"date",value:t,onChange:s=>k(e.key,s.target.value),className:"w-full rounded-lg border border-gray-300 bg-white px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500 dark:border-gray-600 dark:bg-gray-800"});case"number":return r.jsx("input",{type:"number",value:t,onChange:s=>k(e.key,s.target.value),className:"w-full rounded-lg border border-gray-300 bg-white px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500 dark:border-gray-600 dark:bg-gray-800",placeholder:e.placeholder});default:return r.jsx("input",{type:"text",value:t,onChange:s=>k(e.key,s.target.value),className:"w-full rounded-lg border border-gray-300 bg-white px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500 dark:border-gray-600 dark:bg-gray-800",placeholder:e.placeholder||`Search ${e.label}...`})}},H=e=>p!==e?r.jsx(R,{size:14,className:"text-gray-300 opacity-60 dark:text-gray-600"}):m==="asc"?r.jsx(R,{size:14,className:"text-blue-500"}):r.jsx(L,{size:14,className:"text-blue-500"});return r.jsxs("div",{className:"overflow-hidden rounded-xl border border-gray-200 dark:border-gray-700",children:[d.length>0&&r.jsx("div",{className:`border-b border-gray-200 bg-gray-50 transition-all duration-300 dark:border-gray-700 dark:bg-gray-800/50 ${_?"":"hidden"}`,children:r.jsx("div",{className:"space-y-4 p-4",children:r.jsxs("div",{className:"flex flex-col gap-4 sm:flex-row sm:items-center",children:[r.jsx("div",{className:"flex flex-wrap gap-2",children:d.map(e=>r.jsxs("div",{className:"flex min-w-[200px] flex-1 items-center gap-2 sm:min-w-0",children:[r.jsx("label",{className:"whitespace-nowrap text-xs font-medium text-gray-600 dark:text-gray-300",children:e.label}),q(e)]},e.key))}),r.jsx("div",{className:"flex gap-2",children:z&&r.jsxs("button",{onClick:T,className:"flex items-center gap-1 px-3 py-2 text-sm text-gray-600 hover:text-red-500 dark:text-gray-300",children:[r.jsx(ae,{size:14})," Clear"]})})]})})}),d.length>0&&r.jsxs("button",{onClick:()=>I(!_),className:"flex w-full items-center justify-between border-b border-gray-200 bg-gray-50 px-4 py-2 text-sm font-medium text-gray-700 hover:bg-gray-100 dark:border-gray-700 dark:bg-gray-800/50 dark:text-gray-300 dark:hover:bg-gray-700/50",children:[r.jsxs("span",{className:"flex items-center gap-2",children:[r.jsx(te,{size:16}),"Filters",z&&r.jsx("span",{className:"rounded-full bg-blue-100 px-2 py-0.5 text-xs text-blue-700 dark:bg-blue-900/30 dark:text-blue-300",children:Object.values(u).filter(e=>e).length})]}),r.jsx(L,{size:16,className:`${_?"rotate-180":""} transition-transform`})]}),r.jsx("div",{className:"overflow-x-auto",children:r.jsxs("table",{className:"w-full text-left text-sm",children:[r.jsx("thead",{className:"bg-gray-50 text-xs uppercase text-gray-600 dark:bg-gray-800 dark:text-gray-300",children:r.jsx("tr",{children:a.map(e=>{const t=y.includes(e.key);return r.jsx("th",{className:`px-4 py-3 font-semibold ${t?"cursor-pointer select-none hover:bg-gray-100 dark:hover:bg-gray-700":""}`,onClick:()=>t&&O(e.key),title:t?`Sort by ${e.label}`:void 0,children:r.jsxs("div",{className:"flex items-center gap-1",children:[e.label,t&&H(e.key)]})},e.key)})})}),r.jsx("tbody",{className:"divide-y divide-gray-200 bg-white dark:divide-gray-700 dark:bg-gray-900",children:n.length>0?n.map(e=>{var t;return r.jsxs(c.Fragment,{children:[r.jsx("tr",{className:"hover:bg-gray-50 dark:hover:bg-gray-800",children:a.map(o=>r.jsx("td",{className:"px-4 py-3",children:o.key==="detail"?r.jsx("button",{onClick:()=>M(e.id),className:"flex items-center justify-center",children:g[e.id]?r.jsx(L,{size:18}):r.jsx(E,{size:18})}):o.render?o.render(e):e[o.key]},o.key))}),g[e.id]&&((t=e.items)==null?void 0:t.map(o=>r.jsx("tr",{className:"border-b border-gray-100 bg-gray-50 last:border-0 dark:border-gray-700/50 dark:bg-gray-800/30",children:a.map(i=>r.jsx("td",{className:"py-2",children:r.jsx("span",{className:"pl-3 text-gray-600 dark:text-gray-400",children:P(i,o,e)})},i.key))},`${e.id}-item-${o.id}`)))]},e.id)}):r.jsx("tr",{children:r.jsx("td",{colSpan:a.length,className:"py-6 text-center text-gray-400",children:"Tidak ada data"})})})]})}),l&&r.jsxs("div",{className:"flex items-center justify-between border-t p-3 text-sm",children:[r.jsxs("div",{className:"text-gray-500",children:["Page ",l.current_page," of ",l.last_page]}),r.jsx("div",{className:"flex gap-2",children:l.links.map((e,t)=>{const o=t===0,i=t===l.links.length-1,s=o?r.jsx(Y,{size:16}):i?r.jsx(E,{size:16}):null;return e.url===null?r.jsx("span",{className:"flex cursor-not-allowed items-center gap-1 rounded border border-gray-200 px-3 py-1 text-gray-400",children:s||r.jsx("span",{dangerouslySetInnerHTML:{__html:e.label}})},t):r.jsx("button",{onClick:()=>A.visit(e.url,{preserveScroll:!0}),className:`flex items-center gap-1 rounded border px-3 py-1 ${e.active?"bg-blue-500 text-white":"bg-white text-gray-600 hover:bg-gray-50"}`,children:s||r.jsx("span",{dangerouslySetInnerHTML:{__html:e.label}})},t)})})]})]})}export{le as D,h as c};
