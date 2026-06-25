import{b as l,j as t}from"./app-Dj1dCC4j.js";/**
 * @license lucide-react v1.21.0 - ISC
 *
 * This source code is licensed under the ISC license.
 * See the LICENSE file in the root directory of this source tree.
 */const y=(...r)=>r.filter((a,s,o)=>!!a&&a.trim()!==""&&o.indexOf(a)===s).join(" ").trim();/**
 * @license lucide-react v1.21.0 - ISC
 *
 * This source code is licensed under the ISC license.
 * See the LICENSE file in the root directory of this source tree.
 */const N=r=>r.replace(/([a-z0-9])([A-Z])/g,"$1-$2").toLowerCase();/**
 * @license lucide-react v1.21.0 - ISC
 *
 * This source code is licensed under the ISC license.
 * See the LICENSE file in the root directory of this source tree.
 */const w=r=>r.replace(/^([A-Z])|[\s-_]+(\w)/g,(a,s,o)=>o?o.toUpperCase():s.toLowerCase());/**
 * @license lucide-react v1.21.0 - ISC
 *
 * This source code is licensed under the ISC license.
 * See the LICENSE file in the root directory of this source tree.
 */const g=r=>{const a=w(r);return a.charAt(0).toUpperCase()+a.slice(1)};/**
 * @license lucide-react v1.21.0 - ISC
 *
 * This source code is licensed under the ISC license.
 * See the LICENSE file in the root directory of this source tree.
 */var u={xmlns:"http://www.w3.org/2000/svg",width:24,height:24,viewBox:"0 0 24 24",fill:"none",stroke:"currentColor",strokeWidth:2,strokeLinecap:"round",strokeLinejoin:"round"};/**
 * @license lucide-react v1.21.0 - ISC
 *
 * This source code is licensed under the ISC license.
 * See the LICENSE file in the root directory of this source tree.
 */const v=r=>{for(const a in r)if(a.startsWith("aria-")||a==="role"||a==="title")return!0;return!1},L=l.createContext({}),S=()=>l.useContext(L),_=l.forwardRef(({color:r,size:a,strokeWidth:s,absoluteStrokeWidth:o,className:i="",children:c,iconNode:h,...e},n)=>{const{size:d=24,strokeWidth:x=2,absoluteStrokeWidth:p=!1,color:b="currentColor",className:k=""}=S()??{},f=o??p?Number(s??x)*24/Number(a??d):s??x;return l.createElement("svg",{ref:n,...u,width:a??d??u.width,height:a??d??u.height,stroke:r??b,strokeWidth:f,className:y("lucide",k,i),...!c&&!v(e)&&{"aria-hidden":"true"},...e},[...h.map(([j,C])=>l.createElement(j,C)),...Array.isArray(c)?c:[c]])});/**
 * @license lucide-react v1.21.0 - ISC
 *
 * This source code is licensed under the ISC license.
 * See the LICENSE file in the root directory of this source tree.
 */const m=(r,a)=>{const s=l.forwardRef(({className:o,...i},c)=>l.createElement(_,{ref:c,iconNode:a,className:y(`lucide-${N(g(r))}`,`lucide-${r}`,o),...i}));return s.displayName=g(r),s};/**
 * @license lucide-react v1.21.0 - ISC
 *
 * This source code is licensed under the ISC license.
 * See the LICENSE file in the root directory of this source tree.
 */const A=[["path",{d:"m6 9 6 6 6-6",key:"qrunsl"}]],R=m("chevron-down",A);/**
 * @license lucide-react v1.21.0 - ISC
 *
 * This source code is licensed under the ISC license.
 * See the LICENSE file in the root directory of this source tree.
 */const $=[["path",{d:"m9 18 6-6-6-6",key:"mthhwq"}]],W=m("chevron-right",$);function D({columns:r=[],data:a=[],pagination:s=null}){const[o,i]=l.useState({}),c=e=>{i(n=>({...n,[e]:!n[e]}))},h=(e,n)=>e.key==="detail"||e.key==="action"?null:e.key==="name"?t.jsx("span",{className:"pl-6 text-gray-600 dark:text-gray-400",children:n.name}):e.key==="price"?t.jsxs("span",{className:"text-gray-600 dark:text-gray-400",children:["Rp ",Number(n.price).toLocaleString("id-ID")]}):e.key==="stock"?t.jsx("span",{className:"text-gray-600 dark:text-gray-400",children:n.stock}):n[e.key]??null;return t.jsxs("div",{className:"overflow-hidden rounded-xl border border-gray-200 dark:border-gray-700",children:[t.jsx("div",{className:"overflow-x-auto",children:t.jsxs("table",{className:"w-full text-sm text-left",children:[t.jsx("thead",{className:"bg-gray-50 dark:bg-gray-800 text-xs uppercase text-gray-600 dark:text-gray-300",children:t.jsx("tr",{children:r.map(e=>t.jsx("th",{className:"px-4 py-3 font-semibold",children:e.label},e.key))})}),t.jsx("tbody",{className:"divide-y divide-gray-200 dark:divide-gray-700 bg-white dark:bg-gray-900",children:a.length>0?a.map(e=>{var n;return t.jsxs(l.Fragment,{children:[t.jsx("tr",{className:"hover:bg-gray-50 dark:hover:bg-gray-800",children:r.map(d=>t.jsx("td",{className:"px-4 py-3",children:d.key==="detail"?t.jsx("button",{onClick:()=>c(e.id),className:"flex items-center justify-center",children:o[e.id]?t.jsx(R,{size:18}):t.jsx(W,{size:18})}):d.render?d.render(e):e[d.key]},d.key))}),o[e.id]&&((n=e.items)==null?void 0:n.map(d=>t.jsx("tr",{className:"bg-gray-50 dark:bg-gray-800/30 border-b border-gray-100 dark:border-gray-700/50 last:border-0",children:r.map(x=>t.jsx("td",{className:"px-4 py-2",children:h(x,d)},x.key))},`${e.id}-item-${d.id}`)))]},e.id)}):t.jsx("tr",{children:t.jsx("td",{colSpan:r.length,className:"text-center py-6 text-gray-400",children:"Tidak ada data"})})})]})}),s&&t.jsxs("div",{className:"p-3 border-t flex justify-between items-center text-sm",children:[t.jsxs("div",{className:"text-gray-500",children:["Page ",s.current_page," of ",s.last_page]}),t.jsx("div",{className:"flex gap-2",children:s.links.map((e,n)=>t.jsx("button",{disabled:!e.url,onClick:()=>e.url&&(window.location.href=e.url),className:`px-3 py-1 rounded border ${e.active?"bg-blue-500 text-white":"bg-white text-gray-600"}`,dangerouslySetInnerHTML:{__html:e.label}},n))})]})]})}export{D,m as c};
