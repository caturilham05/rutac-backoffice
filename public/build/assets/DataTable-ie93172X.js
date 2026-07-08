import{b as c,j as r,r as z}from"./app-BiBsONnb.js";/**
 * @license lucide-react v1.21.0 - ISC
 *
 * This source code is licensed under the ISC license.
 * See the LICENSE file in the root directory of this source tree.
 */const R=(...a)=>a.filter((n,l,d)=>!!n&&n.trim()!==""&&d.indexOf(n)===l).join(" ").trim();/**
 * @license lucide-react v1.21.0 - ISC
 *
 * This source code is licensed under the ISC license.
 * See the LICENSE file in the root directory of this source tree.
 */const H=a=>a.replace(/([a-z0-9])([A-Z])/g,"$1-$2").toLowerCase();/**
 * @license lucide-react v1.21.0 - ISC
 *
 * This source code is licensed under the ISC license.
 * See the LICENSE file in the root directory of this source tree.
 */const U=a=>a.replace(/^([A-Z])|[\s-_]+(\w)/g,(n,l,d)=>d?d.toUpperCase():l.toLowerCase());/**
 * @license lucide-react v1.21.0 - ISC
 *
 * This source code is licensed under the ISC license.
 * See the LICENSE file in the root directory of this source tree.
 */const D=a=>{const n=U(a);return n.charAt(0).toUpperCase()+n.slice(1)};/**
 * @license lucide-react v1.21.0 - ISC
 *
 * This source code is licensed under the ISC license.
 * See the LICENSE file in the root directory of this source tree.
 */var L={xmlns:"http://www.w3.org/2000/svg",width:24,height:24,viewBox:"0 0 24 24",fill:"none",stroke:"currentColor",strokeWidth:2,strokeLinecap:"round",strokeLinejoin:"round"};/**
 * @license lucide-react v1.21.0 - ISC
 *
 * This source code is licensed under the ISC license.
 * See the LICENSE file in the root directory of this source tree.
 */const B=a=>{for(const n in a)if(n.startsWith("aria-")||n==="role"||n==="title")return!0;return!1},Z=c.createContext({}),X=()=>c.useContext(Z),G=c.forwardRef(({color:a,size:n,strokeWidth:l,absoluteStrokeWidth:d,className:p="",children:u,iconNode:w,...x},b)=>{const{size:g=24,strokeWidth:k=2,absoluteStrokeWidth:i=!1,color:f="currentColor",className:y=""}=X()??{},C=d??i?Number(l??k)*24/Number(n??g):l??k;return c.createElement("svg",{ref:b,...L,width:n??g??L.width,height:n??g??L.height,stroke:a??f,strokeWidth:C,className:R("lucide",y,p),...!u&&!B(x)&&{"aria-hidden":"true"},...x},[...w.map(([m,S])=>c.createElement(m,S)),...Array.isArray(u)?u:[u]])});/**
 * @license lucide-react v1.21.0 - ISC
 *
 * This source code is licensed under the ISC license.
 * See the LICENSE file in the root directory of this source tree.
 */const h=(a,n)=>{const l=c.forwardRef(({className:d,...p},u)=>c.createElement(G,{ref:u,iconNode:n,className:R(`lucide-${H(D(a))}`,`lucide-${a}`,d),...p}));return l.displayName=D(a),l};/**
 * @license lucide-react v1.21.0 - ISC
 *
 * This source code is licensed under the ISC license.
 * See the LICENSE file in the root directory of this source tree.
 */const J=[["path",{d:"m6 9 6 6 6-6",key:"qrunsl"}]],F=h("chevron-down",J);/**
 * @license lucide-react v1.21.0 - ISC
 *
 * This source code is licensed under the ISC license.
 * See the LICENSE file in the root directory of this source tree.
 */const Q=[["path",{d:"m15 18-6-6 6-6",key:"1wnfg3"}]],V=h("chevron-left",Q);/**
 * @license lucide-react v1.21.0 - ISC
 *
 * This source code is licensed under the ISC license.
 * See the LICENSE file in the root directory of this source tree.
 */const Y=[["path",{d:"m9 18 6-6-6-6",key:"mthhwq"}]],E=h("chevron-right",Y);/**
 * @license lucide-react v1.21.0 - ISC
 *
 * This source code is licensed under the ISC license.
 * See the LICENSE file in the root directory of this source tree.
 */const K=[["path",{d:"m18 15-6-6-6 6",key:"153udz"}]],ee=h("chevron-up",K);/**
 * @license lucide-react v1.21.0 - ISC
 *
 * This source code is licensed under the ISC license.
 * See the LICENSE file in the root directory of this source tree.
 */const re=[["path",{d:"M10 20a1 1 0 0 0 .553.895l2 1A1 1 0 0 0 14 21v-7a2 2 0 0 1 .517-1.341L21.74 4.67A1 1 0 0 0 21 3H3a1 1 0 0 0-.742 1.67l7.225 7.989A2 2 0 0 1 10 14z",key:"sc7q7i"}]],te=h("funnel",re);/**
 * @license lucide-react v1.21.0 - ISC
 *
 * This source code is licensed under the ISC license.
 * See the LICENSE file in the root directory of this source tree.
 */const se=[["path",{d:"M18 6 6 18",key:"1bl5f8"}],["path",{d:"m6 6 12 12",key:"d8bk6v"}]],ae=h("x",se);function le({columns:a=[],data:n=[],pagination:l=null,filterConfig:d=[],filterValues:p={},sortColumn:u=null,sortDirection:w="asc",sortableColumns:x=[],baseUrl:b=null}){const[g,k]=c.useState({}),[i,f]=c.useState(p),[y,C]=c.useState(u),[m,S]=c.useState(w),[_,W]=c.useState(!1),I=e=>{k(t=>({...t,[e]:!t[e]}))},M=(e,t,o)=>{if(e.renderDetail)return e.renderDetail(t,o)},$=(e=i,t=y,o=m)=>{if(!b)return;const s=new URLSearchParams;Object.entries(e).forEach(([v,N])=>{N!==""&&N!==null&&N!==void 0&&s.set(v,N)}),t&&(s.set("sort",t),s.set("direction",o)),l!=null&&l.current_page&&l.current_page>1&&s.set("page",l.current_page),z.visit(`${b}?${s.toString()}`,{preserveScroll:!0,preserveState:!0,replace:!0})},j=(e,t)=>{const o={...i,[e]:t};f(o),$(o)},P=e=>{if(!x.includes(e))return;let t="asc";y===e&&m==="asc"&&(t="desc"),C(e),S(t),$(i,e,t)},O=()=>{const e={};d.forEach(t=>{e[t.key]=""}),f(e),$(e,null,"asc")},A=c.useMemo(()=>Object.values(i).some(e=>e!==""&&e!==null&&e!==void 0),[i]),T=e=>{var o;const t=i[e.key]||"";switch(e.type){case"select":return r.jsxs("select",{value:t,onChange:s=>j(e.key,s.target.value),className:"w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-lg bg-white dark:bg-gray-800 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500",children:[r.jsxs("option",{value:"",children:["All ",e.label]}),(o=e.options)==null?void 0:o.map(s=>r.jsx("option",{value:s.value,children:s.label},s.value))]});case"date":return r.jsx("input",{type:"date",value:t,onChange:s=>j(e.key,s.target.value),className:"w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-lg bg-white dark:bg-gray-800 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500"});case"number":return r.jsx("input",{type:"number",value:t,onChange:s=>j(e.key,s.target.value),className:"w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-lg bg-white dark:bg-gray-800 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500",placeholder:e.placeholder});default:return r.jsx("input",{type:"text",value:t,onChange:s=>j(e.key,s.target.value),className:"w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-lg bg-white dark:bg-gray-800 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500",placeholder:e.placeholder||`Search ${e.label}...`})}},q=e=>y!==e?null:m==="asc"?r.jsx(ee,{size:14,className:"text-blue-500"}):r.jsx(F,{size:14,className:"text-blue-500"});return r.jsxs("div",{className:"overflow-hidden rounded-xl border border-gray-200 dark:border-gray-700",children:[d.length>0&&r.jsx("div",{className:`border-b border-gray-200 dark:border-gray-700 bg-gray-50 dark:bg-gray-800/50 transition-all duration-300 ${_?"":"hidden"}`,children:r.jsx("div",{className:"p-4 space-y-4",children:r.jsxs("div",{className:"flex flex-col sm:flex-row gap-4 items-start sm:items-center",children:[r.jsx("div",{className:"flex gap-2 flex-wrap",children:d.map(e=>r.jsxs("div",{className:"flex items-center gap-2 min-w-[200px] flex-1 sm:min-w-0",children:[r.jsx("label",{className:"text-xs font-medium text-gray-600 dark:text-gray-300 whitespace-nowrap",children:e.label}),T(e)]},e.key))}),r.jsx("div",{className:"flex gap-2",children:A&&r.jsxs("button",{onClick:O,className:"px-3 py-2 text-sm text-gray-600 dark:text-gray-300 hover:text-red-500 flex items-center gap-1",children:[r.jsx(ae,{size:14})," Clear"]})})]})})}),d.length>0&&r.jsxs("button",{onClick:()=>W(!_),className:"w-full px-4 py-2 bg-gray-50 dark:bg-gray-800/50 border-b border-gray-200 dark:border-gray-700 flex items-center justify-between text-sm font-medium text-gray-700 dark:text-gray-300 hover:bg-gray-100 dark:hover:bg-gray-700/50",children:[r.jsxs("span",{className:"flex items-center gap-2",children:[r.jsx(te,{size:16}),"Filters",A&&r.jsx("span",{className:"px-2 py-0.5 bg-blue-100 dark:bg-blue-900/30 text-blue-700 dark:text-blue-300 rounded-full text-xs",children:Object.values(i).filter(e=>e).length})]}),r.jsx(F,{size:16,className:`${_?"rotate-180":""} transition-transform`})]}),r.jsx("div",{className:"overflow-x-auto",children:r.jsxs("table",{className:"w-full text-sm text-left",children:[r.jsx("thead",{className:"bg-gray-50 dark:bg-gray-800 text-xs uppercase text-gray-600 dark:text-gray-300",children:r.jsx("tr",{children:a.map(e=>r.jsx("th",{className:"px-4 py-3 font-semibold",style:{cursor:x.includes(e.key)?"pointer":"default"},onClick:()=>P(e.key),children:r.jsxs("div",{className:"flex items-center gap-1",children:[e.label,x.includes(e.key)&&q(e.key)]})},e.key))})}),r.jsx("tbody",{className:"divide-y divide-gray-200 dark:divide-gray-700 bg-white dark:bg-gray-900",children:n.length>0?n.map(e=>{var t;return r.jsxs(c.Fragment,{children:[r.jsx("tr",{className:"hover:bg-gray-50 dark:hover:bg-gray-800",children:a.map(o=>r.jsx("td",{className:"px-4 py-3",children:o.key==="detail"?r.jsx("button",{onClick:()=>I(e.id),className:"flex items-center justify-center",children:g[e.id]?r.jsx(F,{size:18}):r.jsx(E,{size:18})}):o.render?o.render(e):e[o.key]},o.key))}),g[e.id]&&((t=e.items)==null?void 0:t.map(o=>r.jsx("tr",{className:"bg-gray-50 dark:bg-gray-800/30 border-b border-gray-100 dark:border-gray-700/50 last:border-0",children:a.map(s=>r.jsx("td",{className:"py-2",children:r.jsx("span",{className:"pl-3 text-gray-600 dark:text-gray-400",children:M(s,o,e)})},s.key))},`${e.id}-item-${o.id}`)))]},e.id)}):r.jsx("tr",{children:r.jsx("td",{colSpan:a.length,className:"text-center py-6 text-gray-400",children:"Tidak ada data"})})})]})}),l&&r.jsxs("div",{className:"p-3 border-t flex justify-between items-center text-sm",children:[r.jsxs("div",{className:"text-gray-500",children:["Page ",l.current_page," of ",l.last_page]}),r.jsx("div",{className:"flex gap-2",children:l.links.map((e,t)=>{const o=t===0,s=t===l.links.length-1,v=o?r.jsx(V,{size:16}):s?r.jsx(E,{size:16}):null;return e.url===null?r.jsx("span",{className:"px-3 py-1 rounded border border-gray-200 text-gray-400 cursor-not-allowed flex items-center gap-1",children:v||r.jsx("span",{dangerouslySetInnerHTML:{__html:e.label}})},t):r.jsx("button",{onClick:()=>z.visit(e.url,{preserveScroll:!0}),className:`px-3 py-1 rounded border flex items-center gap-1 ${e.active?"bg-blue-500 text-white":"bg-white text-gray-600 hover:bg-gray-50"}`,children:v||r.jsx("span",{dangerouslySetInnerHTML:{__html:e.label}})},t)})})]})]})}export{le as D,h as c};
