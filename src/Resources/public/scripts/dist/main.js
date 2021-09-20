var OnOfficeImport;(()=>{var e={564:(e,t,s)=>{"use strict";s.r(t),s.d(t,{OnOfficeImport:()=>o});class o{constructor(e){this.modules={};for(const t of document.querySelectorAll("[data-module]"))if(!t.classList.contains("disabled")){const s=t.dataset.module,o=t.querySelector('button[type="button"]'),n=t.querySelector(".settings"),r=t.querySelector('button[type="submit"]'),i=t.querySelector("form");this.modules[s]={name:s,hasSettings:!!n,data:null,elements:{module:t,buttonImport:r,formSettings:i,buttonSettings:o,containerSettings:n,progressBar:t.querySelector(".progress-value"),containerProgress:t.querySelector(".progress"),containerCounter:t.querySelector(".progress .current"),containerNumber:t.querySelector(".progress .count"),containerInfo:t.querySelector(".progress .info")}},r.addEventListener("click",(t=>{t.preventDefault(),confirm(e.texts.confirmMessage)&&(i.checkValidity()?this.start(s):(n.classList.add("open"),o.classList.add("open"),i.reportValidity()))})),o&&o.addEventListener("click",(e=>{e.preventDefault(),n.classList.toggle("open"),o.classList.toggle("open")}))}}start(e){const t=this.modules[e];t.elements.buttonImport.disabled=!0,t.elements.containerProgress.style.display="block",t.hasSettings&&(this.modules[e].data=new FormData(t.elements.formSettings),t.elements.buttonSettings.classList.remove("open"),t.elements.containerSettings.classList.remove("open")),this.fetch(e).then((t=>this.onFinish(e))).catch((t=>this.onError(e)))}async fetch(e){const t=this.modules[e],s=await fetch("/onoffice/fetch/"+e,{method:"POST",body:t.data}),o=await s.json();this.setStatus(e,o),o.task&&await this.import(e,o.task)}async import(e,t){console.log("Import:",t);const s=await fetch(t.action,{method:"POST",headers:{"Content-Type":"application/json"},body:JSON.stringify(t)}),o=await s.json();this.setStatus(e,o),o.task&&await this.import(o.task),console.log("Import",o)}setStatus(e,t){t.message&&(this.modules[e].elements.containerInfo.innerText=t.message),t.meta?.cntabsolute&&(this.modules[e].countAbsolute=t.meta.cntabsolute,this.modules[e].elements.containerNumber.innerText=t.meta.cntabsolute),t.count&&(this.modules[e].elements.containerCounter.innerText=t.count),this.modules[e].countAbsolute&&t.count&&this.setProgress(e,t.count/this.modules[e].countAbsolute*100)}setProgress(e,t){this.modules[e].elements.progressBar.style.width=t+"%"}onFinish(e){console.log("onFinish"),this.setProgress(e,100),this.modules[e].elements.containerInfo.classList.add("success")}onError(e){console.error(e)}}},36:(e,t,s)=>{const{OnOfficeImport:o}=s(564);e.exports=o}},t={};function s(o){var n=t[o];if(void 0!==n)return n.exports;var r=t[o]={exports:{}};return e[o](r,r.exports,s),r.exports}s.d=(e,t)=>{for(var o in t)s.o(t,o)&&!s.o(e,o)&&Object.defineProperty(e,o,{enumerable:!0,get:t[o]})},s.o=(e,t)=>Object.prototype.hasOwnProperty.call(e,t),s.r=e=>{"undefined"!=typeof Symbol&&Symbol.toStringTag&&Object.defineProperty(e,Symbol.toStringTag,{value:"Module"}),Object.defineProperty(e,"__esModule",{value:!0})};var o=s(36);OnOfficeImport=o})();