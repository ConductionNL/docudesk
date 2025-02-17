"use strict";(self.webpackChunkdocudesk_docs=self.webpackChunkdocudesk_docs||[]).push([[438],{1470:(e,t,n)=>{n.d(t,{A:()=>k});var a=n(8168),r=n(6540),o=n(53),i=n(3104),l=n(6347),u=n(7485),s=n(1682),c=n(9466);function m(e){return function(e){return r.Children.map(e,(e=>{if(!e||(0,r.isValidElement)(e)&&function(e){const{props:t}=e;return!!t&&"object"==typeof t&&"value"in t}(e))return e;throw new Error(`Docusaurus error: Bad <Tabs> child <${"string"==typeof e.type?e.type:e.type.name}>: all children of the <Tabs> component should be <TabItem>, and every <TabItem> should have a unique "value" prop.`)}))?.filter(Boolean)??[]}(e).map((e=>{let{props:{value:t,label:n,attributes:a,default:r}}=e;return{value:t,label:n,attributes:a,default:r}}))}function p(e){const{values:t,children:n}=e;return(0,r.useMemo)((()=>{const e=t??m(n);return function(e){const t=(0,s.X)(e,((e,t)=>e.value===t.value));if(t.length>0)throw new Error(`Docusaurus error: Duplicate values "${t.map((e=>e.value)).join(", ")}" found in <Tabs>. Every value needs to be unique.`)}(e),e}),[t,n])}function d(e){let{value:t,tabValues:n}=e;return n.some((e=>e.value===t))}function g(e){let{queryString:t=!1,groupId:n}=e;const a=(0,l.W6)(),o=function(e){let{queryString:t=!1,groupId:n}=e;if("string"==typeof t)return t;if(!1===t)return null;if(!0===t&&!n)throw new Error('Docusaurus error: The <Tabs> component groupId prop is required if queryString=true, because this value is used as the search param name. You can also provide an explicit value such as queryString="my-search-param".');return n??null}({queryString:t,groupId:n});return[(0,u.aZ)(o),(0,r.useCallback)((e=>{if(!o)return;const t=new URLSearchParams(a.location.search);t.set(o,e),a.replace({...a.location,search:t.toString()})}),[o,a])]}function f(e){const{defaultValue:t,queryString:n=!1,groupId:a}=e,o=p(e),[i,l]=(0,r.useState)((()=>function(e){let{defaultValue:t,tabValues:n}=e;if(0===n.length)throw new Error("Docusaurus error: the <Tabs> component requires at least one <TabItem> children component");if(t){if(!d({value:t,tabValues:n}))throw new Error(`Docusaurus error: The <Tabs> has a defaultValue "${t}" but none of its children has the corresponding value. Available values are: ${n.map((e=>e.value)).join(", ")}. If you intend to show no default tab, use defaultValue={null} instead.`);return t}const a=n.find((e=>e.default))??n[0];if(!a)throw new Error("Unexpected error: 0 tabValues");return a.value}({defaultValue:t,tabValues:o}))),[u,s]=g({queryString:n,groupId:a}),[m,f]=function(e){let{groupId:t}=e;const n=function(e){return e?`docusaurus.tab.${e}`:null}(t),[a,o]=(0,c.Dv)(n);return[a,(0,r.useCallback)((e=>{n&&o.set(e)}),[n,o])]}({groupId:a}),y=(()=>{const e=u??m;return d({value:e,tabValues:o})?e:null})();(0,r.useLayoutEffect)((()=>{y&&l(y)}),[y]);return{selectedValue:i,selectValue:(0,r.useCallback)((e=>{if(!d({value:e,tabValues:o}))throw new Error(`Can't select invalid tab value=${e}`);l(e),s(e),f(e)}),[s,f,o]),tabValues:o}}var y=n(2303);const b={tabList:"tabList__CuJ",tabItem:"tabItem_LNqP"};function w(e){let{className:t,block:n,selectedValue:l,selectValue:u,tabValues:s}=e;const c=[],{blockElementScrollPositionUntilNextRender:m}=(0,i.a_)(),p=e=>{const t=e.currentTarget,n=c.indexOf(t),a=s[n].value;a!==l&&(m(t),u(a))},d=e=>{let t=null;switch(e.key){case"Enter":p(e);break;case"ArrowRight":{const n=c.indexOf(e.currentTarget)+1;t=c[n]??c[0];break}case"ArrowLeft":{const n=c.indexOf(e.currentTarget)-1;t=c[n]??c[c.length-1];break}}t?.focus()};return r.createElement("ul",{role:"tablist","aria-orientation":"horizontal",className:(0,o.A)("tabs",{"tabs--block":n},t)},s.map((e=>{let{value:t,label:n,attributes:i}=e;return r.createElement("li",(0,a.A)({role:"tab",tabIndex:l===t?0:-1,"aria-selected":l===t,key:t,ref:e=>c.push(e),onKeyDown:d,onClick:p},i,{className:(0,o.A)("tabs__item",b.tabItem,i?.className,{"tabs__item--active":l===t})}),n??t)})))}function v(e){let{lazy:t,children:n,selectedValue:a}=e;const o=(Array.isArray(n)?n:[n]).filter(Boolean);if(t){const e=o.find((e=>e.props.value===a));return e?(0,r.cloneElement)(e,{className:"margin-top--md"}):null}return r.createElement("div",{className:"margin-top--md"},o.map(((e,t)=>(0,r.cloneElement)(e,{key:t,hidden:e.props.value!==a}))))}function h(e){const t=f(e);return r.createElement("div",{className:(0,o.A)("tabs-container",b.tabList)},r.createElement(w,(0,a.A)({},e,t)),r.createElement(v,(0,a.A)({},e,t)))}function k(e){const t=(0,y.A)();return r.createElement(h,(0,a.A)({key:String(t)},e))}},5680:(e,t,n)=>{n.d(t,{xA:()=>c,yg:()=>g});var a=n(6540);function r(e,t,n){return t in e?Object.defineProperty(e,t,{value:n,enumerable:!0,configurable:!0,writable:!0}):e[t]=n,e}function o(e,t){var n=Object.keys(e);if(Object.getOwnPropertySymbols){var a=Object.getOwnPropertySymbols(e);t&&(a=a.filter((function(t){return Object.getOwnPropertyDescriptor(e,t).enumerable}))),n.push.apply(n,a)}return n}function i(e){for(var t=1;t<arguments.length;t++){var n=null!=arguments[t]?arguments[t]:{};t%2?o(Object(n),!0).forEach((function(t){r(e,t,n[t])})):Object.getOwnPropertyDescriptors?Object.defineProperties(e,Object.getOwnPropertyDescriptors(n)):o(Object(n)).forEach((function(t){Object.defineProperty(e,t,Object.getOwnPropertyDescriptor(n,t))}))}return e}function l(e,t){if(null==e)return{};var n,a,r=function(e,t){if(null==e)return{};var n,a,r={},o=Object.keys(e);for(a=0;a<o.length;a++)n=o[a],t.indexOf(n)>=0||(r[n]=e[n]);return r}(e,t);if(Object.getOwnPropertySymbols){var o=Object.getOwnPropertySymbols(e);for(a=0;a<o.length;a++)n=o[a],t.indexOf(n)>=0||Object.prototype.propertyIsEnumerable.call(e,n)&&(r[n]=e[n])}return r}var u=a.createContext({}),s=function(e){var t=a.useContext(u),n=t;return e&&(n="function"==typeof e?e(t):i(i({},t),e)),n},c=function(e){var t=s(e.components);return a.createElement(u.Provider,{value:t},e.children)},m="mdxType",p={inlineCode:"code",wrapper:function(e){var t=e.children;return a.createElement(a.Fragment,{},t)}},d=a.forwardRef((function(e,t){var n=e.components,r=e.mdxType,o=e.originalType,u=e.parentName,c=l(e,["components","mdxType","originalType","parentName"]),m=s(n),d=r,g=m["".concat(u,".").concat(d)]||m[d]||p[d]||o;return n?a.createElement(g,i(i({ref:t},c),{},{components:n})):a.createElement(g,i({ref:t},c))}));function g(e,t){var n=arguments,r=t&&t.mdxType;if("string"==typeof e||r){var o=n.length,i=new Array(o);i[0]=d;var l={};for(var u in t)hasOwnProperty.call(t,u)&&(l[u]=t[u]);l.originalType=e,l[m]="string"==typeof e?e:r,i[1]=l;for(var s=2;s<o;s++)i[s]=n[s];return a.createElement.apply(null,i)}return a.createElement.apply(null,n)}d.displayName="MDXCreateElement"},6771:(e,t,n)=>{n.r(t),n.d(t,{assets:()=>c,contentTitle:()=>u,default:()=>g,frontMatter:()=>l,metadata:()=>s,toc:()=>m});var a=n(8168),r=(n(6540),n(5680)),o=n(1470),i=n(9365);const l={id:"workflow-automation",title:"Workflow Automation",sidebar_label:"Workflow Automation",sidebar_position:10,description:"Automate document workflows, compliance checking, and processing chains",keywords:["workflow","automation","process","routing","compliance","WCAG","GDPR"]},u="\u26a1 Workflow Automation",s={unversionedId:"features/workflow-automation",id:"features/workflow-automation",title:"Workflow Automation",description:"Automate document workflows, compliance checking, and processing chains",source:"@site/docs/features/workflow-automation.md",sourceDirName:"features",slug:"/features/workflow-automation",permalink:"/docudesk/docs/features/workflow-automation",draft:!1,editUrl:"https://github.com/conductionnl/docudesk/tree/main/website/docs/features/workflow-automation.md",tags:[],version:"current",sidebarPosition:10,frontMatter:{id:"workflow-automation",title:"Workflow Automation",sidebar_label:"Workflow Automation",sidebar_position:10,description:"Automate document workflows, compliance checking, and processing chains",keywords:["workflow","automation","process","routing","compliance","WCAG","GDPR"]},sidebar:"tutorialSidebar",previous:{title:"Document Validation",permalink:"/docudesk/docs/features/document-validation"},next:{title:"DocuDesk Documentation",permalink:"/docudesk/docs/intro"}},c={},m=[{value:"Overview",id:"overview",level:2},{value:"Features",id:"features",level:2},{value:"Document Monitoring",id:"document-monitoring",level:3},{value:"Compliance &amp; Privacy Features",id:"compliance--privacy-features",level:3},{value:"Workflow Capabilities",id:"workflow-capabilities",level:3},{value:"Quick Start",id:"quick-start",level:2},{value:"Use Cases",id:"use-cases",level:2}],p={toc:m},d="wrapper";function g(e){let{components:t,...l}=e;return(0,r.yg)(d,(0,a.A)({},p,l,{components:t,mdxType:"MDXLayout"}),(0,r.yg)("h1",{id:"-workflow-automation"},"\u26a1 Workflow Automation"),(0,r.yg)("h2",{id:"overview"},"Overview"),(0,r.yg)("p",null,"Create sophisticated document processing workflows that automatically handle document monitoring, compliance checking, anonymization, and notifications based on various triggers."),(0,r.yg)("p",null,(0,r.yg)("img",{alt:"Workflow Automation",src:n(9140).A,width:"2065",height:"1291"})),(0,r.yg)("h2",{id:"features"},"Features"),(0,r.yg)("h3",{id:"document-monitoring"},"Document Monitoring"),(0,r.yg)("ul",null,(0,r.yg)("li",{parentName:"ul"},"Multiple source monitoring:",(0,r.yg)("ul",{parentName:"li"},(0,r.yg)("li",{parentName:"ul"},"FTP folders"),(0,r.yg)("li",{parentName:"ul"},"SharePoint directories"),(0,r.yg)("li",{parentName:"ul"},"Office 365 locations"),(0,r.yg)("li",{parentName:"ul"},"Case Management Systems"))),(0,r.yg)("li",{parentName:"ul"},"Real-time file change detection"),(0,r.yg)("li",{parentName:"ul"},"Tag/label-based workflow triggers"),(0,r.yg)("li",{parentName:"ul"},"Automated compliance checking")),(0,r.yg)("h3",{id:"compliance--privacy-features"},"Compliance & Privacy Features"),(0,r.yg)("ul",null,(0,r.yg)("li",{parentName:"ul"},"WCAG compliance validation"),(0,r.yg)("li",{parentName:"ul"},"Language level assessment"),(0,r.yg)("li",{parentName:"ul"},"GDPR content detection"),(0,r.yg)("li",{parentName:"ul"},"Automated document anonymization"),(0,r.yg)("li",{parentName:"ul"},"Warning tag application"),(0,r.yg)("li",{parentName:"ul"},"Email notifications"),(0,r.yg)("li",{parentName:"ul"},"Dashboard alerts")),(0,r.yg)("h3",{id:"workflow-capabilities"},"Workflow Capabilities"),(0,r.yg)("ul",null,(0,r.yg)("li",{parentName:"ul"},"Visual workflow designer"),(0,r.yg)("li",{parentName:"ul"},"Conditional routing"),(0,r.yg)("li",{parentName:"ul"},"Multi-step processing"),(0,r.yg)("li",{parentName:"ul"},"Approval chains"),(0,r.yg)("li",{parentName:"ul"},"Status tracking"),(0,r.yg)("li",{parentName:"ul"},"Event triggers"),(0,r.yg)("li",{parentName:"ul"},"Integration hooks")),(0,r.yg)("h2",{id:"quick-start"},"Quick Start"),(0,r.yg)(o.A,{mdxType:"Tabs"},(0,r.yg)(i.A,{value:"monitor",label:"Setup Monitoring",default:!0,mdxType:"TabItem"},(0,r.yg)("pre",null,(0,r.yg)("code",{parentName:"pre",className:"language-php"},"// Configure document source monitoring\n$monitor = $workflowService->createMonitor([\n    'source' => [\n        'type' => 'sharepoint',\n        'config' => [\n            'site' => 'https://company.sharepoint.com/sites/docs',\n            'library' => 'Contracts'\n        ]\n    ],\n    'triggers' => [\n        'on_create' => true,\n        'on_update' => true,\n        'on_delete' => true,\n        'tags' => ['personal-info', 'confidential']\n    ]\n]);\n"))),(0,r.yg)(i.A,{value:"workflow",label:"Create Workflow",default:!0,mdxType:"TabItem"},(0,r.yg)("pre",null,(0,r.yg)("code",{parentName:"pre",className:"language-php"},"// Define an anonymization workflow\n$workflow = $workflowService->create([\n    'name' => 'Document Anonymization',\n    'steps' => [\n        [\n            'type' => 'gdpr_scan',\n            'config' => ['sensitivity' => 'high']\n        ],\n        [\n            'type' => 'anonymize',\n            'config' => [\n                'target' => 'new_file',\n                'elements' => ['names', 'addresses', 'ids']\n            ]\n        ],\n        [\n            'type' => 'compliance_check',\n            'config' => [\n                'wcag' => true,\n                'language_level' => 'B1',\n                'on_failure' => [\n                    'tag_document' => 'compliance-warning',\n                    'notify_email' => 'compliance@company.com'\n                ]\n            ]\n        ]\n    ]\n]);\n"))),(0,r.yg)(i.A,{value:"dashboard",label:"Dashboard Integration",mdxType:"TabItem"},(0,r.yg)("pre",null,(0,r.yg)("code",{parentName:"pre",className:"language-php"},"// Retrieve compliance warnings for dashboard\n$warnings = $workflowService->getWarnings([\n    'types' => ['wcag', 'language', 'gdpr'],\n    'status' => 'active',\n    'period' => 'last_30_days'\n]);\n")))),(0,r.yg)("admonition",{title:"Automation",type:"tip"},(0,r.yg)("p",{parentName:"admonition"},"Automatically protect privacy and ensure compliance across all documents with minimal manual intervention.")),(0,r.yg)("admonition",{title:"Monitoring",type:"info"},(0,r.yg)("p",{parentName:"admonition"},"Configure multiple document sources and trigger conditions to create comprehensive document handling workflows.")),(0,r.yg)("h2",{id:"use-cases"},"Use Cases"),(0,r.yg)("ul",null,(0,r.yg)("li",{parentName:"ul"},"Automated document anonymization"),(0,r.yg)("li",{parentName:"ul"},"GDPR compliance monitoring"),(0,r.yg)("li",{parentName:"ul"},"WCAG accessibility validation"),(0,r.yg)("li",{parentName:"ul"},"Language level assessment"),(0,r.yg)("li",{parentName:"ul"},"Multi-department document processing"),(0,r.yg)("li",{parentName:"ul"},"Compliance reporting and alerting")))}g.isMDXComponent=!0},9140:(e,t,n)=>{n.d(t,{A:()=>a});const a=n.p+"assets/images/workflow-automation-1924039427bcf494d992f419142329fa.svg"},9365:(e,t,n)=>{n.d(t,{A:()=>i});var a=n(6540),r=n(53);const o={tabItem:"tabItem_Ymn6"};function i(e){let{children:t,hidden:n,className:i}=e;return a.createElement("div",{role:"tabpanel",className:(0,r.A)(o.tabItem,i),hidden:n},t)}}}]);