import{a as d,j as e,H as l}from"./app-Bpz8nikY.js";import{D as r}from"./DataTable-DtiY8nmy.js";import{c as s,A as c}from"./AuthenticatedLayout-DoKZuJkF.js";import"./ApplicationLogo-Be3Fkh5S.js";import"./transition-CjMqA3eu.js";/**
 * @license lucide-react v1.21.0 - ISC
 *
 * This source code is licensed under the ISC license.
 * See the LICENSE file in the root directory of this source tree.
 */const g=[["rect",{x:"14",y:"3",width:"5",height:"18",rx:"1",key:"kaeet6"}],["rect",{x:"5",y:"3",width:"5",height:"18",rx:"1",key:"1wsw3u"}]],p=s("pause",g);/**
 * @license lucide-react v1.21.0 - ISC
 *
 * This source code is licensed under the ISC license.
 * See the LICENSE file in the root directory of this source tree.
 */const u=[["path",{d:"M5 5a2 2 0 0 1 3.008-1.728l11.997 6.998a2 2 0 0 1 .003 3.458l-12 7A2 2 0 0 1 5 19z",key:"10ikf1"}]],h=s("play",u);function f(){const{ads:a}=d().props,i=[{key:"name",label:"Campaign Name"},{key:"type",label:"Type"},{key:"status",label:"Status"},{key:"bidding_method",label:"Bidding Method"},{key:"campaign_budget",label:"Budget"},{key:"start_time",label:"Start Time"},{key:"end_time",label:"End Time"},{key:"roas_target",label:"Roas Target"},{key:"edit",label:"Action",render:t=>e.jsx("div",{className:"flex gap-2",children:e.jsx("button",{onClick:()=>n(t.id),className:`rounded px-3 py-1 text-white ${t.status==="ongoing"?"bg-red-500":"bg-green-500"}`,children:o[t.status]??null})})}],o={ongoing:e.jsx(p,{size:15}),paused:e.jsx(h,{size:15})},n=()=>{console.log("first")};return e.jsxs(c,{header:e.jsx("h2",{className:"text-xl font-semibold leading-tight text-gray-800 dark:text-gray-200",children:"Shopee Ads Configuration"}),children:[e.jsx(l,{title:"Shopee Ads"}),e.jsx("div",{className:"p-6",children:e.jsx(r,{columns:i,data:a.data,pagination:a,baseUrl:route("shopee.ads.index")})})]})}export{f as default};
