"use strict";(self.webpackChunkdocudesk_docs=self.webpackChunkdocudesk_docs||[]).push([[962],{1470:(e,t,n)=>{n.d(t,{A:()=>S});var r=n(8168),a=n(6540),i=n(53),l=n(3104),u=n(6347),o=n(7485),s=n(1682),c=n(9466);function g(e){return function(e){return a.Children.map(e,(e=>{if(!e||(0,a.isValidElement)(e)&&function(e){const{props:t}=e;return!!t&&"object"==typeof t&&"value"in t}(e))return e;throw new Error(`Docusaurus error: Bad <Tabs> child <${"string"==typeof e.type?e.type:e.type.name}>: all children of the <Tabs> component should be <TabItem>, and every <TabItem> should have a unique "value" prop.`)}))?.filter(Boolean)??[]}(e).map((e=>{let{props:{value:t,label:n,attributes:r,default:a}}=e;return{value:t,label:n,attributes:r,default:a}}))}function d(e){const{values:t,children:n}=e;return(0,a.useMemo)((()=>{const e=t??g(n);return function(e){const t=(0,s.X)(e,((e,t)=>e.value===t.value));if(t.length>0)throw new Error(`Docusaurus error: Duplicate values "${t.map((e=>e.value)).join(", ")}" found in <Tabs>. Every value needs to be unique.`)}(e),e}),[t,n])}function p(e){let{value:t,tabValues:n}=e;return n.some((e=>e.value===t))}function m(e){let{queryString:t=!1,groupId:n}=e;const r=(0,u.W6)(),i=function(e){let{queryString:t=!1,groupId:n}=e;if("string"==typeof t)return t;if(!1===t)return null;if(!0===t&&!n)throw new Error('Docusaurus error: The <Tabs> component groupId prop is required if queryString=true, because this value is used as the search param name. You can also provide an explicit value such as queryString="my-search-param".');return n??null}({queryString:t,groupId:n});return[(0,o.aZ)(i),(0,a.useCallback)((e=>{if(!i)return;const t=new URLSearchParams(r.location.search);t.set(i,e),r.replace({...r.location,search:t.toString()})}),[i,r])]}function f(e){const{defaultValue:t,queryString:n=!1,groupId:r}=e,i=d(e),[l,u]=(0,a.useState)((()=>function(e){let{defaultValue:t,tabValues:n}=e;if(0===n.length)throw new Error("Docusaurus error: the <Tabs> component requires at least one <TabItem> children component");if(t){if(!p({value:t,tabValues:n}))throw new Error(`Docusaurus error: The <Tabs> has a defaultValue "${t}" but none of its children has the corresponding value. Available values are: ${n.map((e=>e.value)).join(", ")}. If you intend to show no default tab, use defaultValue={null} instead.`);return t}const r=n.find((e=>e.default))??n[0];if(!r)throw new Error("Unexpected error: 0 tabValues");return r.value}({defaultValue:t,tabValues:i}))),[o,s]=m({queryString:n,groupId:r}),[g,f]=function(e){let{groupId:t}=e;const n=function(e){return e?`docusaurus.tab.${e}`:null}(t),[r,i]=(0,c.Dv)(n);return[r,(0,a.useCallback)((e=>{n&&i.set(e)}),[n,i])]}({groupId:r}),y=(()=>{const e=o??g;return p({value:e,tabValues:i})?e:null})();(0,a.useLayoutEffect)((()=>{y&&u(y)}),[y]);return{selectedValue:l,selectValue:(0,a.useCallback)((e=>{if(!p({value:e,tabValues:i}))throw new Error(`Can't select invalid tab value=${e}`);u(e),s(e),f(e)}),[s,f,i]),tabValues:i}}var y=n(2303);const v={tabList:"tabList__CuJ",tabItem:"tabItem_LNqP"};function b(e){let{className:t,block:n,selectedValue:u,selectValue:o,tabValues:s}=e;const c=[],{blockElementScrollPositionUntilNextRender:g}=(0,l.a_)(),d=e=>{const t=e.currentTarget,n=c.indexOf(t),r=s[n].value;r!==u&&(g(t),o(r))},p=e=>{let t=null;switch(e.key){case"Enter":d(e);break;case"ArrowRight":{const n=c.indexOf(e.currentTarget)+1;t=c[n]??c[0];break}case"ArrowLeft":{const n=c.indexOf(e.currentTarget)-1;t=c[n]??c[c.length-1];break}}t?.focus()};return a.createElement("ul",{role:"tablist","aria-orientation":"horizontal",className:(0,i.A)("tabs",{"tabs--block":n},t)},s.map((e=>{let{value:t,label:n,attributes:l}=e;return a.createElement("li",(0,r.A)({role:"tab",tabIndex:u===t?0:-1,"aria-selected":u===t,key:t,ref:e=>c.push(e),onKeyDown:p,onClick:d},l,{className:(0,i.A)("tabs__item",v.tabItem,l?.className,{"tabs__item--active":u===t})}),n??t)})))}function h(e){let{lazy:t,children:n,selectedValue:r}=e;const i=(Array.isArray(n)?n:[n]).filter(Boolean);if(t){const e=i.find((e=>e.props.value===r));return e?(0,a.cloneElement)(e,{className:"margin-top--md"}):null}return a.createElement("div",{className:"margin-top--md"},i.map(((e,t)=>(0,a.cloneElement)(e,{key:t,hidden:e.props.value!==r}))))}function w(e){const t=f(e);return a.createElement("div",{className:(0,i.A)("tabs-container",v.tabList)},a.createElement(b,(0,r.A)({},e,t)),a.createElement(h,(0,r.A)({},e,t)))}function S(e){const t=(0,y.A)();return a.createElement(w,(0,r.A)({key:String(t)},e))}},1806:(e,t,n)=>{n.r(t),n.d(t,{assets:()=>c,contentTitle:()=>o,default:()=>m,frontMatter:()=>u,metadata:()=>s,toc:()=>g});var r=n(8168),a=(n(6540),n(5680)),i=n(1470),l=n(9365);const u={id:"digital-signing",title:"Digital Signing",sidebar_label:"Digital Signing",sidebar_position:2,description:"Secure digital document signing and verification within your infrastructure",keywords:["signing","verification","eIDAS","digital signature"]},o="\u270d\ufe0f Digital Signing",s={unversionedId:"features/digital-signing",id:"features/digital-signing",title:"Digital Signing",description:"Secure digital document signing and verification within your infrastructure",source:"@site/docs/features/digital-signing.md",sourceDirName:"features",slug:"/features/digital-signing",permalink:"/docudesk/docs/features/digital-signing",draft:!1,editUrl:"https://github.com/conductionnl/docudesk/tree/main/website/docs/features/digital-signing.md",tags:[],version:"current",sidebarPosition:2,frontMatter:{id:"digital-signing",title:"Digital Signing",sidebar_label:"Digital Signing",sidebar_position:2,description:"Secure digital document signing and verification within your infrastructure",keywords:["signing","verification","eIDAS","digital signature"]},sidebar:"tutorialSidebar",previous:{title:"Document Generation",permalink:"/docudesk/docs/features/document-generation"},next:{title:"GDPR Anonymization",permalink:"/docudesk/docs/features/gdpr-anonymization"}},c={},g=[{value:"Overview",id:"overview",level:2},{value:"Features",id:"features",level:2},{value:"Signature Types",id:"signature-types",level:3},{value:"Quick Start",id:"quick-start",level:2},{value:"Use Cases",id:"use-cases",level:2},{value:"Legal Documents",id:"legal-documents",level:3},{value:"Compliance",id:"compliance",level:3}],d={toc:g},p="wrapper";function m(e){let{components:t,...n}=e;return(0,a.yg)(p,(0,r.A)({},d,n,{components:t,mdxType:"MDXLayout"}),(0,a.yg)("h1",{id:"\ufe0f-digital-signing"},"\u270d\ufe0f Digital Signing"),(0,a.yg)("h2",{id:"overview"},"Overview"),(0,a.yg)("p",null,"Transform your document signing workflow with secure, eIDAS-compliant digital signatures. All processing happens locally within your Nextcloud instance, ensuring maximum security and compliance."),(0,a.yg)("h2",{id:"features"},"Features"),(0,a.yg)("h3",{id:"signature-types"},"Signature Types"),(0,a.yg)("ul",null,(0,a.yg)("li",{parentName:"ul"},"Multiple signature types support",(0,a.yg)("ul",{parentName:"li"},(0,a.yg)("li",{parentName:"ul"},"Qualified Electronic Signatures (QES)"),(0,a.yg)("li",{parentName:"ul"},"Advanced Electronic Signatures (AES)"),(0,a.yg)("li",{parentName:"ul"},"Basic Electronic Signatures"))),(0,a.yg)("li",{parentName:"ul"},"Signature verification"),(0,a.yg)("li",{parentName:"ul"},"Audit trail"),(0,a.yg)("li",{parentName:"ul"},"Batch signing capabilities"),(0,a.yg)("li",{parentName:"ul"},"Integration with local identity providers")),(0,a.yg)("h2",{id:"quick-start"},"Quick Start"),(0,a.yg)(i.A,{mdxType:"Tabs"},(0,a.yg)(l.A,{value:"sign",label:"Sign Document",default:!0,mdxType:"TabItem"},(0,a.yg)("pre",null,(0,a.yg)("code",{parentName:"pre",className:"language-php"},"// Sign a document\n$signedDocument = $signingService->signDocument(\n    documentId: 123,\n    signatureType: 'qualified',\n    signerId: 'john.doe',\n    certificate: $certificate\n);\n"))),(0,a.yg)(l.A,{value:"verify",label:"Verify Signature",mdxType:"TabItem"},(0,a.yg)("pre",null,(0,a.yg)("code",{parentName:"pre",className:"language-php"},'// Verify a signature\n$verification = $signingService->verifySignature(\n    documentId: 123,\n    signatureId: \'abc-123\'\n);\n\nif ($verification->isValid()) {\n    echo "Signature is valid!";\n    echo "Signed by: " . $verification->getSignerName();\n    echo "Timestamp: " . $verification->getTimestamp();\n}\n')))),(0,a.yg)("h2",{id:"use-cases"},"Use Cases"),(0,a.yg)("h3",{id:"legal-documents"},"Legal Documents"),(0,a.yg)("ul",null,(0,a.yg)("li",{parentName:"ul"},"Contract signing"),(0,a.yg)("li",{parentName:"ul"},"Document approval workflows"),(0,a.yg)("li",{parentName:"ul"},"Multi-party agreements")),(0,a.yg)("h3",{id:"compliance"},"Compliance"),(0,a.yg)("ul",null,(0,a.yg)("li",{parentName:"ul"},"Regulatory compliance"),(0,a.yg)("li",{parentName:"ul"},"Internal authorizations"),(0,a.yg)("li",{parentName:"ul"},"Audit trail maintenance")),(0,a.yg)("admonition",{title:"Security First",type:"tip"},(0,a.yg)("p",{parentName:"admonition"},"All signing operations occur within your secure environment, ensuring private keys and sensitive data never leave your control.")),(0,a.yg)("admonition",{title:"eIDAS Compliance",type:"info"},(0,a.yg)("p",{parentName:"admonition"},"Our signing implementation follows eIDAS regulations, making it suitable for legal and regulatory requirements across the EU.")))}m.isMDXComponent=!0},5680:(e,t,n)=>{n.d(t,{xA:()=>c,yg:()=>m});var r=n(6540);function a(e,t,n){return t in e?Object.defineProperty(e,t,{value:n,enumerable:!0,configurable:!0,writable:!0}):e[t]=n,e}function i(e,t){var n=Object.keys(e);if(Object.getOwnPropertySymbols){var r=Object.getOwnPropertySymbols(e);t&&(r=r.filter((function(t){return Object.getOwnPropertyDescriptor(e,t).enumerable}))),n.push.apply(n,r)}return n}function l(e){for(var t=1;t<arguments.length;t++){var n=null!=arguments[t]?arguments[t]:{};t%2?i(Object(n),!0).forEach((function(t){a(e,t,n[t])})):Object.getOwnPropertyDescriptors?Object.defineProperties(e,Object.getOwnPropertyDescriptors(n)):i(Object(n)).forEach((function(t){Object.defineProperty(e,t,Object.getOwnPropertyDescriptor(n,t))}))}return e}function u(e,t){if(null==e)return{};var n,r,a=function(e,t){if(null==e)return{};var n,r,a={},i=Object.keys(e);for(r=0;r<i.length;r++)n=i[r],t.indexOf(n)>=0||(a[n]=e[n]);return a}(e,t);if(Object.getOwnPropertySymbols){var i=Object.getOwnPropertySymbols(e);for(r=0;r<i.length;r++)n=i[r],t.indexOf(n)>=0||Object.prototype.propertyIsEnumerable.call(e,n)&&(a[n]=e[n])}return a}var o=r.createContext({}),s=function(e){var t=r.useContext(o),n=t;return e&&(n="function"==typeof e?e(t):l(l({},t),e)),n},c=function(e){var t=s(e.components);return r.createElement(o.Provider,{value:t},e.children)},g="mdxType",d={inlineCode:"code",wrapper:function(e){var t=e.children;return r.createElement(r.Fragment,{},t)}},p=r.forwardRef((function(e,t){var n=e.components,a=e.mdxType,i=e.originalType,o=e.parentName,c=u(e,["components","mdxType","originalType","parentName"]),g=s(n),p=a,m=g["".concat(o,".").concat(p)]||g[p]||d[p]||i;return n?r.createElement(m,l(l({ref:t},c),{},{components:n})):r.createElement(m,l({ref:t},c))}));function m(e,t){var n=arguments,a=t&&t.mdxType;if("string"==typeof e||a){var i=n.length,l=new Array(i);l[0]=p;var u={};for(var o in t)hasOwnProperty.call(t,o)&&(u[o]=t[o]);u.originalType=e,u[g]="string"==typeof e?e:a,l[1]=u;for(var s=2;s<i;s++)l[s]=n[s];return r.createElement.apply(null,l)}return r.createElement.apply(null,n)}p.displayName="MDXCreateElement"},9365:(e,t,n)=>{n.d(t,{A:()=>l});var r=n(6540),a=n(53);const i={tabItem:"tabItem_Ymn6"};function l(e){let{children:t,hidden:n,className:l}=e;return r.createElement("div",{role:"tabpanel",className:(0,a.A)(i.tabItem,l),hidden:n},t)}}}]);