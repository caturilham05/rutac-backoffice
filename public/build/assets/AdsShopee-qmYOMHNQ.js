import{a as r,j as e,H as c,r as p}from"./app-xtoc6yI0.js";import{D as u}from"./DataTable-m9OAvvrO.js";import{c as d,A as g}from"./AuthenticatedLayout-B76TilBE.js";import"./ApplicationLogo-B1V47abS.js";import"./transition-DKgSZ0ja.js";/**
 * @license lucide-react v1.21.0 - ISC
 *
 * This source code is licensed under the ISC license.
 * See the LICENSE file in the root directory of this source tree.
 */const m=[["rect",{x:"14",y:"3",width:"5",height:"18",rx:"1",key:"kaeet6"}],["rect",{x:"5",y:"3",width:"5",height:"18",rx:"1",key:"1wsw3u"}]],h=d("pause",m);/**
 * @license lucide-react v1.21.0 - ISC
 *
 * This source code is licensed under the ISC license.
 * See the LICENSE file in the root directory of this source tree.
 */const x=[["path",{d:"M5 5a2 2 0 0 1 3.008-1.728l11.997 6.998a2 2 0 0 1 .003 3.458l-12 7A2 2 0 0 1 5 19z",key:"10ikf1"}]],y=d("play",x);function A(){const{ads:t}=r().props,n=[{key:"name",label:"Campaign Name"},{key:"type",label:"Type"},{key:"status",label:"Status"},{key:"bidding_method",label:"Bidding Method"},{key:"campaign_budget",label:"Budget"},{key:"start_time",label:"Start Time"},{key:"end_time",label:"End Time"},{key:"roas_target",label:"Roas Target"},{key:"edit",label:"Action",render:a=>s[a.status]&&e.jsx("div",{className:"flex gap-2",children:e.jsx("button",{onClick:()=>{o(a.marketplace_id,a.campaign_id,a.status)},className:`rounded px-3 py-1 text-white ${a.status==="ongoing"?"bg-red-500":"bg-green-500"}`,children:s[a.status]})})}],s={ongoing:e.jsx(h,{size:15}),paused:e.jsx(y,{size:15})},o=(a,l,i)=>{p.post(route("shopee.ads.edit",a),{campaign_id:l,edit_action:i==="ongoing"?"pause":i==="paused"?"resume":"",preserveScroll:!1})};return e.jsxs(g,{header:e.jsx("h2",{className:"text-xl font-semibold leading-tight text-gray-800 dark:text-gray-200",children:"Shopee Ads Configuration"}),children:[e.jsx(c,{title:"Shopee Ads"}),e.jsx("div",{className:"p-6",children:e.jsx(u,{columns:n,data:t.data,pagination:t,baseUrl:route("shopee.ads.index")})})]})}export{A as default};
