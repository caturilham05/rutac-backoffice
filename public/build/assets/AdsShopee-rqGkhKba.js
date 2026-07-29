import{a as r,j as t,H as c,r as p}from"./app-CI8_pXMk.js";import{D as u}from"./DataTable-CSnJvWxM.js";import{c as n,A as g}from"./AuthenticatedLayout-BaOAge49.js";import"./ApplicationLogo-D45QztJQ.js";import"./transition-BGTcHE2h.js";/**
 * @license lucide-react v1.21.0 - ISC
 *
 * This source code is licensed under the ISC license.
 * See the LICENSE file in the root directory of this source tree.
 */const m=[["rect",{x:"14",y:"3",width:"5",height:"18",rx:"1",key:"kaeet6"}],["rect",{x:"5",y:"3",width:"5",height:"18",rx:"1",key:"1wsw3u"}]],h=n("pause",m);/**
 * @license lucide-react v1.21.0 - ISC
 *
 * This source code is licensed under the ISC license.
 * See the LICENSE file in the root directory of this source tree.
 */const x=[["path",{d:"M5 5a2 2 0 0 1 3.008-1.728l11.997 6.998a2 2 0 0 1 .003 3.458l-12 7A2 2 0 0 1 5 19z",key:"10ikf1"}]],y=n("play",x);function A(){const{ads:a}=r().props,d=[{key:"name",label:"Campaign Name"},{key:"type",label:"Type"},{key:"status",label:"Status"},{key:"bidding_method",label:"Bidding Method"},{key:"campaign_budget",label:"Budget"},{key:"start_time",label:"Start Time"},{key:"end_time",label:"End Time"},{key:"roas_target",label:"Roas Target"},{key:"edit",label:"Action",render:e=>s[e.status]&&t.jsx("div",{className:"flex gap-2",children:t.jsx("button",{onClick:()=>{l(e.marketplace_id,e.campaign_id,e.status)},className:`rounded px-3 py-1 text-white ${e.status==="ongoing"?"bg-red-500":"bg-green-500"}`,children:s[e.status]})})}],s={ongoing:t.jsx(h,{size:15}),paused:t.jsx(y,{size:15})},l=(e,i,o)=>{console.log(e,i),p.post(route("shopee.ads.edit",e),{campaign_id:i,action:o==="ongoing"?"pause":o==="paused"?"resume":"",preserveScroll:!1})};return t.jsxs(g,{header:t.jsx("h2",{className:"text-xl font-semibold leading-tight text-gray-800 dark:text-gray-200",children:"Shopee Ads Configuration"}),children:[t.jsx(c,{title:"Shopee Ads"}),t.jsx("div",{className:"p-6",children:t.jsx(u,{columns:d,data:a.data,pagination:a,baseUrl:route("shopee.ads.index")})})]})}export{A as default};
