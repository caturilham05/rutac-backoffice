import{b as l,j as t}from"./app-DldB2ZBN.js";/**
 * @license lucide-react v1.21.0 - ISC
 *
 * This source code is licensed under the ISC license.
 * See the LICENSE file in the root directory of this source tree.
 */const u=(...r)=>r.filter((a,s,n)=>!!a&&a.trim()!==""&&n.indexOf(a)===s).join(" ").trim();/**
 * @license lucide-react v1.21.0 - ISC
 *
 * This source code is licensed under the ISC license.
 * See the LICENSE file in the root directory of this source tree.
 */const N=r=>r.replace(/([a-z0-9])([A-Z])/g,"$1-$2").toLowerCase();/**
 * @license lucide-react v1.21.0 - ISC
 *
 * This source code is licensed under the ISC license.
 * See the LICENSE file in the root directory of this source tree.
 */const v=r=>r.replace(/^([A-Z])|[\s-_]+(\w)/g,(a,s,n)=>n?n.toUpperCase():s.toLowerCase());/**
 * @license lucide-react v1.21.0 - ISC
 *
 * This source code is licensed under the ISC license.
 * See the LICENSE file in the root directory of this source tree.
 */const m=r=>{const a=v(r);return a.charAt(0).toUpperCase()+a.slice(1)};/**
 * @license lucide-react v1.21.0 - ISC
 *
 * This source code is licensed under the ISC license.
 * See the LICENSE file in the root directory of this source tree.
 */var g={xmlns:"http://www.w3.org/2000/svg",width:24,height:24,viewBox:"0 0 24 24",fill:"none",stroke:"currentColor",strokeWidth:2,strokeLinecap:"round",strokeLinejoin:"round"};/**
 * @license lucide-react v1.21.0 - ISC
 *
 * This source code is licensed under the ISC license.
 * See the LICENSE file in the root directory of this source tree.
 */const w=r=>{for(const a in r)if(a.startsWith("aria-")||a==="role"||a==="title")return!0;return!1},_=l.createContext({}),A=()=>l.useContext(_),L=l.forwardRef(({color:r,size:a,strokeWidth:s,absoluteStrokeWidth:n,className:i="",children:c,iconNode:h,...e},o)=>{const{size:d=24,strokeWidth:x=2,absoluteStrokeWidth:p=!1,color:b="currentColor",className:j=""}=A()??{},f=n??p?Number(s??x)*24/Number(a??d):s??x;return l.createElement("svg",{ref:o,...g,width:a??d??g.width,height:a??d??g.height,stroke:r??b,strokeWidth:f,className:u("lucide",j,i),...!c&&!w(e)&&{"aria-hidden":"true"},...e},[...h.map(([k,C])=>l.createElement(k,C)),...Array.isArray(c)?c:[c]])});/**
 * @license lucide-react v1.21.0 - ISC
 *
 * This source code is licensed under the ISC license.
 * See the LICENSE file in the root directory of this source tree.
 */const y=(r,a)=>{const s=l.forwardRef(({className:n,...i},c)=>l.createElement(L,{ref:c,iconNode:a,className:u(`lucide-${N(m(r))}`,`lucide-${r}`,n),...i}));return s.displayName=m(r),s};/**
 * @license lucide-react v1.21.0 - ISC
 *
 * This source code is licensed under the ISC license.
 * See the LICENSE file in the root directory of this source tree.
 */const S=[["path",{d:"m6 9 6 6 6-6",key:"qrunsl"}]],$=y("chevron-down",S);/**
 * @license lucide-react v1.21.0 - ISC
 *
 * This source code is licensed under the ISC license.
 * See the LICENSE file in the root directory of this source tree.
 */const R=[["path",{d:"m9 18 6-6-6-6",key:"mthhwq"}]],W=y("chevron-right",R);function E({columns:r=[],data:a=[],pagination:s=null}){const[n,i]=l.useState({}),c=e=>{i(o=>({...o,[e]:!o[e]}))},h=(e,o,d)=>{if(e.renderDetail)return e.renderDetail(o,d)};return t.jsxs("div",{className:"overflow-hidden rounded-xl border border-gray-200 dark:border-gray-700",children:[t.jsx("div",{className:"overflow-x-auto",children:t.jsxs("table",{className:"w-full text-sm text-left",children:[t.jsx("thead",{className:"bg-gray-50 dark:bg-gray-800 text-xs uppercase text-gray-600 dark:text-gray-300",children:t.jsx("tr",{children:r.map(e=>t.jsx("th",{className:"px-4 py-3 font-semibold",children:e.label},e.key))})}),t.jsx("tbody",{className:"divide-y divide-gray-200 dark:divide-gray-700 bg-white dark:bg-gray-900",children:a.length>0?a.map(e=>{var o;return t.jsxs(l.Fragment,{children:[t.jsx("tr",{className:"hover:bg-gray-50 dark:hover:bg-gray-800",children:r.map(d=>t.jsx("td",{className:"px-4 py-3",children:d.key==="detail"?t.jsx("button",{onClick:()=>c(e.id),className:"flex items-center justify-center",children:n[e.id]?t.jsx($,{size:18}):t.jsx(W,{size:18})}):d.render?d.render(e):e[d.key]},d.key))}),n[e.id]&&((o=e.items)==null?void 0:o.map(d=>t.jsx("tr",{className:"bg-gray-50 dark:bg-gray-800/30 border-b border-gray-100 dark:border-gray-700/50 last:border-0",children:r.map(x=>t.jsx("td",{className:"py-2",children:t.jsx("span",{className:"pl-3 text-gray-600 dark:text-gray-400",children:h(x,d,e)})},x.key))},`${e.id}-item-${d.id}`)))]},e.id)}):t.jsx("tr",{children:t.jsx("td",{colSpan:r.length,className:"text-center py-6 text-gray-400",children:"Tidak ada data"})})})]})}),s&&t.jsxs("div",{className:"p-3 border-t flex justify-between items-center text-sm",children:[t.jsxs("div",{className:"text-gray-500",children:["Page ",s.current_page," of ",s.last_page]}),t.jsx("div",{className:"flex gap-2",children:s.links.map((e,o)=>t.jsx("button",{disabled:!e.url,onClick:()=>e.url&&(window.location.href=e.url),className:`px-3 py-1 rounded border ${e.active?"bg-blue-500 text-white":"bg-white text-gray-600"}`,dangerouslySetInnerHTML:{__html:e.label}},o))})]})]})}export{E as D,y as c};
