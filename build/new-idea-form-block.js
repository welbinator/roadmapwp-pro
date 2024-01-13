(()=>{"use strict";const e=window.React,{registerBlockType:t}=wp.blocks,{InspectorControls:s,useBlockProps:a}=wp.blockEditor,{PanelBody:l,CheckboxControl:o}=wp.components,{useSelect:c}=wp.data;t("wp-roadmap-pro/new-idea-form",{title:"New Idea Form",category:"common",attributes:{selectedStatuses:{type:"object",default:{}},selectedTaxonomies:{type:"object",default:{}}},edit:({attributes:t,setAttributes:n})=>{const d=c((e=>e("core").getEntityRecords("taxonomy","status",{per_page:-1})),[]),r=c((e=>{const t=e("core").getTaxonomies();return t?t.filter((e=>e.types.includes("idea")&&"status"!==e.slug)):[]}),[]);return(0,e.createElement)("div",{...a()},(0,e.createElement)(s,null,(0,e.createElement)(l,{title:"Select Statuses for New Idea"},d&&d.map((s=>(0,e.createElement)(o,{key:s.id,label:s.name,checked:!!t.selectedStatuses[s.id],onChange:e=>((e,s)=>{const a={...t.selectedStatuses,[e]:s};n({selectedStatuses:a})})(s.id,e)})))),(0,e.createElement)(l,{title:"Idea Taxonomies"},r&&r.map((s=>(0,e.createElement)(o,{key:s.slug,label:s.name,checked:!!t.selectedTaxonomies[s.slug],onChange:e=>((e,s)=>{const a={...t.selectedTaxonomies,[e]:s};n({selectedTaxonomies:a})})(s.slug,e)}))))),(0,e.createElement)("p",null,"New Idea Form will be displayed here."))},save:({attributes:t})=>{const s=Object.keys(t.selectedStatuses).filter((e=>t.selectedStatuses[e])).join(","),a=Object.keys(t.selectedTaxonomies).filter((e=>t.selectedTaxonomies[e])).join(",");return(0,e.createElement)("div",{"data-selected-statuses":s,"data-selected-taxonomies":a},(0,e.createElement)("p",null,"New Idea Form will be displayed here."))}})})();