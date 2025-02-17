"use strict";(self.webpackChunkdocudesk_docs=self.webpackChunkdocudesk_docs||[]).push([[469],{1470:(e,t,a)=>{a.d(t,{A:()=>k});var n=a(8168),r=a(6540),o=a(53),i=a(3104),l=a(6347),u=a(7485),s=a(1682),c=a(9466);function d(e){return function(e){return r.Children.map(e,(e=>{if(!e||(0,r.isValidElement)(e)&&function(e){const{props:t}=e;return!!t&&"object"==typeof t&&"value"in t}(e))return e;throw new Error(`Docusaurus error: Bad <Tabs> child <${"string"==typeof e.type?e.type:e.type.name}>: all children of the <Tabs> component should be <TabItem>, and every <TabItem> should have a unique "value" prop.`)}))?.filter(Boolean)??[]}(e).map((e=>{let{props:{value:t,label:a,attributes:n,default:r}}=e;return{value:t,label:a,attributes:n,default:r}}))}function m(e){const{values:t,children:a}=e;return(0,r.useMemo)((()=>{const e=t??d(a);return function(e){const t=(0,s.X)(e,((e,t)=>e.value===t.value));if(t.length>0)throw new Error(`Docusaurus error: Duplicate values "${t.map((e=>e.value)).join(", ")}" found in <Tabs>. Every value needs to be unique.`)}(e),e}),[t,a])}function p(e){let{value:t,tabValues:a}=e;return a.some((e=>e.value===t))}function f(e){let{queryString:t=!1,groupId:a}=e;const n=(0,l.W6)(),o=function(e){let{queryString:t=!1,groupId:a}=e;if("string"==typeof t)return t;if(!1===t)return null;if(!0===t&&!a)throw new Error('Docusaurus error: The <Tabs> component groupId prop is required if queryString=true, because this value is used as the search param name. You can also provide an explicit value such as queryString="my-search-param".');return a??null}({queryString:t,groupId:a});return[(0,u.aZ)(o),(0,r.useCallback)((e=>{if(!o)return;const t=new URLSearchParams(n.location.search);t.set(o,e),n.replace({...n.location,search:t.toString()})}),[o,n])]}function v(e){const{defaultValue:t,queryString:a=!1,groupId:n}=e,o=m(e),[i,l]=(0,r.useState)((()=>function(e){let{defaultValue:t,tabValues:a}=e;if(0===a.length)throw new Error("Docusaurus error: the <Tabs> component requires at least one <TabItem> children component");if(t){if(!p({value:t,tabValues:a}))throw new Error(`Docusaurus error: The <Tabs> has a defaultValue "${t}" but none of its children has the corresponding value. Available values are: ${a.map((e=>e.value)).join(", ")}. If you intend to show no default tab, use defaultValue={null} instead.`);return t}const n=a.find((e=>e.default))??a[0];if(!n)throw new Error("Unexpected error: 0 tabValues");return n.value}({defaultValue:t,tabValues:o}))),[u,s]=f({queryString:a,groupId:n}),[d,v]=function(e){let{groupId:t}=e;const a=function(e){return e?`docusaurus.tab.${e}`:null}(t),[n,o]=(0,c.Dv)(a);return[n,(0,r.useCallback)((e=>{a&&o.set(e)}),[a,o])]}({groupId:n}),y=(()=>{const e=u??d;return p({value:e,tabValues:o})?e:null})();(0,r.useLayoutEffect)((()=>{y&&l(y)}),[y]);return{selectedValue:i,selectValue:(0,r.useCallback)((e=>{if(!p({value:e,tabValues:o}))throw new Error(`Can't select invalid tab value=${e}`);l(e),s(e),v(e)}),[s,v,o]),tabValues:o}}var y=a(2303);const b={tabList:"tabList__CuJ",tabItem:"tabItem_LNqP"};function g(e){let{className:t,block:a,selectedValue:l,selectValue:u,tabValues:s}=e;const c=[],{blockElementScrollPositionUntilNextRender:d}=(0,i.a_)(),m=e=>{const t=e.currentTarget,a=c.indexOf(t),n=s[a].value;n!==l&&(d(t),u(n))},p=e=>{let t=null;switch(e.key){case"Enter":m(e);break;case"ArrowRight":{const a=c.indexOf(e.currentTarget)+1;t=c[a]??c[0];break}case"ArrowLeft":{const a=c.indexOf(e.currentTarget)-1;t=c[a]??c[c.length-1];break}}t?.focus()};return r.createElement("ul",{role:"tablist","aria-orientation":"horizontal",className:(0,o.A)("tabs",{"tabs--block":a},t)},s.map((e=>{let{value:t,label:a,attributes:i}=e;return r.createElement("li",(0,n.A)({role:"tab",tabIndex:l===t?0:-1,"aria-selected":l===t,key:t,ref:e=>c.push(e),onKeyDown:p,onClick:m},i,{className:(0,o.A)("tabs__item",b.tabItem,i?.className,{"tabs__item--active":l===t})}),a??t)})))}function h(e){let{lazy:t,children:a,selectedValue:n}=e;const o=(Array.isArray(a)?a:[a]).filter(Boolean);if(t){const e=o.find((e=>e.props.value===n));return e?(0,r.cloneElement)(e,{className:"margin-top--md"}):null}return r.createElement("div",{className:"margin-top--md"},o.map(((e,t)=>(0,r.cloneElement)(e,{key:t,hidden:e.props.value!==n}))))}function w(e){const t=v(e);return r.createElement("div",{className:(0,o.A)("tabs-container",b.tabList)},r.createElement(g,(0,n.A)({},e,t)),r.createElement(h,(0,n.A)({},e,t)))}function k(e){const t=(0,y.A)();return r.createElement(w,(0,n.A)({key:String(t)},e))}},5680:(e,t,a)=>{a.d(t,{xA:()=>c,yg:()=>f});var n=a(6540);function r(e,t,a){return t in e?Object.defineProperty(e,t,{value:a,enumerable:!0,configurable:!0,writable:!0}):e[t]=a,e}function o(e,t){var a=Object.keys(e);if(Object.getOwnPropertySymbols){var n=Object.getOwnPropertySymbols(e);t&&(n=n.filter((function(t){return Object.getOwnPropertyDescriptor(e,t).enumerable}))),a.push.apply(a,n)}return a}function i(e){for(var t=1;t<arguments.length;t++){var a=null!=arguments[t]?arguments[t]:{};t%2?o(Object(a),!0).forEach((function(t){r(e,t,a[t])})):Object.getOwnPropertyDescriptors?Object.defineProperties(e,Object.getOwnPropertyDescriptors(a)):o(Object(a)).forEach((function(t){Object.defineProperty(e,t,Object.getOwnPropertyDescriptor(a,t))}))}return e}function l(e,t){if(null==e)return{};var a,n,r=function(e,t){if(null==e)return{};var a,n,r={},o=Object.keys(e);for(n=0;n<o.length;n++)a=o[n],t.indexOf(a)>=0||(r[a]=e[a]);return r}(e,t);if(Object.getOwnPropertySymbols){var o=Object.getOwnPropertySymbols(e);for(n=0;n<o.length;n++)a=o[n],t.indexOf(a)>=0||Object.prototype.propertyIsEnumerable.call(e,a)&&(r[a]=e[a])}return r}var u=n.createContext({}),s=function(e){var t=n.useContext(u),a=t;return e&&(a="function"==typeof e?e(t):i(i({},t),e)),a},c=function(e){var t=s(e.components);return n.createElement(u.Provider,{value:t},e.children)},d="mdxType",m={inlineCode:"code",wrapper:function(e){var t=e.children;return n.createElement(n.Fragment,{},t)}},p=n.forwardRef((function(e,t){var a=e.components,r=e.mdxType,o=e.originalType,u=e.parentName,c=l(e,["components","mdxType","originalType","parentName"]),d=s(a),p=r,f=d["".concat(u,".").concat(p)]||d[p]||m[p]||o;return a?n.createElement(f,i(i({ref:t},c),{},{components:a})):n.createElement(f,i({ref:t},c))}));function f(e,t){var a=arguments,r=t&&t.mdxType;if("string"==typeof e||r){var o=a.length,i=new Array(o);i[0]=p;var l={};for(var u in t)hasOwnProperty.call(t,u)&&(l[u]=t[u]);l.originalType=e,l[d]="string"==typeof e?e:r,i[1]=l;for(var s=2;s<o;s++)i[s]=a[s];return n.createElement.apply(null,i)}return n.createElement.apply(null,a)}p.displayName="MDXCreateElement"},7767:(e,t,a)=>{a.r(t),a.d(t,{assets:()=>c,contentTitle:()=>u,default:()=>f,frontMatter:()=>l,metadata:()=>s,toc:()=>d});var n=a(8168),r=(a(6540),a(5680)),o=a(1470),i=a(9365);const l={id:"document-validation",title:"Document Validation",sidebar_label:"Document Validation",sidebar_position:9,description:"Automated quality control and validation of documents",keywords:["validation","quality control","compliance","verification"]},u="\u2705 Document Validation",s={unversionedId:"features/document-validation",id:"features/document-validation",title:"Document Validation",description:"Automated quality control and validation of documents",source:"@site/docs/features/document-validation.md",sourceDirName:"features",slug:"/features/document-validation",permalink:"/docudesk/docs/features/document-validation",draft:!1,editUrl:"https://github.com/conductionnl/docudesk/tree/main/website/docs/features/document-validation.md",tags:[],version:"current",sidebarPosition:9,frontMatter:{id:"document-validation",title:"Document Validation",sidebar_label:"Document Validation",sidebar_position:9,description:"Automated quality control and validation of documents",keywords:["validation","quality control","compliance","verification"]},sidebar:"tutorialSidebar",previous:{title:"Document Classification",permalink:"/docudesk/docs/features/document-classification"},next:{title:"Workflow Automation",permalink:"/docudesk/docs/features/workflow-automation"}},c={},d=[{value:"Overview",id:"overview",level:2},{value:"Features",id:"features",level:2},{value:"Validation Capabilities",id:"validation-capabilities",level:3},{value:"Quick Start",id:"quick-start",level:2},{value:"Use Cases",id:"use-cases",level:2}],m={toc:d},p="wrapper";function f(e){let{components:t,...a}=e;return(0,r.yg)(p,(0,n.A)({},m,a,{components:t,mdxType:"MDXLayout"}),(0,r.yg)("h1",{id:"-document-validation"},"\u2705 Document Validation"),(0,r.yg)("h2",{id:"overview"},"Overview"),(0,r.yg)("p",null,"Ensure document quality and compliance through automated validation checks, all processed securely within your local environment."),(0,r.yg)("h2",{id:"features"},"Features"),(0,r.yg)("h3",{id:"validation-capabilities"},"Validation Capabilities"),(0,r.yg)("ul",null,(0,r.yg)("li",{parentName:"ul"},"Structure validation"),(0,r.yg)("li",{parentName:"ul"},"Content completeness checks"),(0,r.yg)("li",{parentName:"ul"},"Format compliance"),(0,r.yg)("li",{parentName:"ul"},"Required field verification"),(0,r.yg)("li",{parentName:"ul"},"Custom validation rules"),(0,r.yg)("li",{parentName:"ul"},"Quality scoring")),(0,r.yg)("h2",{id:"quick-start"},"Quick Start"),(0,r.yg)(o.A,{mdxType:"Tabs"},(0,r.yg)(i.A,{value:"validate",label:"Validate Document",default:!0,mdxType:"TabItem"},(0,r.yg)("pre",null,(0,r.yg)("code",{parentName:"pre",className:"language-php"},"// Validate a document against rules\n$validation = $validationService->validate(\n    documentId: 123,\n    ruleSet: 'contract_requirements',\n    options: [\n        'strictMode' => true,\n        'autoFix' => false\n    ]\n);\n"))),(0,r.yg)(i.A,{value:"custom",label:"Custom Validation",mdxType:"TabItem"},(0,r.yg)("pre",null,(0,r.yg)("code",{parentName:"pre",className:"language-php"},"// Define custom validation rules\n$rules = [\n    'required_sections' => ['introduction', 'terms', 'signatures'],\n    'field_formats' => [\n        'date' => 'Y-m-d',\n        'amount' => '/^\\d+(\\.\\d{2})?$/'\n    ]\n];\n\n$result = $validationService->validateWithRules(\n    documentId: 123,\n    rules: $rules\n);\n")))),(0,r.yg)("admonition",{title:"Quality Assurance",type:"tip"},(0,r.yg)("p",{parentName:"admonition"},"Automated validation ensures consistent document quality across your organization.")),(0,r.yg)("admonition",{title:"Compliance",type:"info"},(0,r.yg)("p",{parentName:"admonition"},"Built-in rules for common compliance requirements with support for custom validation logic.")),(0,r.yg)("h2",{id:"use-cases"},"Use Cases"),(0,r.yg)("ul",null,(0,r.yg)("li",{parentName:"ul"},"Contract validation"),(0,r.yg)("li",{parentName:"ul"},"Form completeness checking"),(0,r.yg)("li",{parentName:"ul"},"Regulatory compliance"),(0,r.yg)("li",{parentName:"ul"},"Quality assurance"),(0,r.yg)("li",{parentName:"ul"},"Standard enforcement")))}f.isMDXComponent=!0},9365:(e,t,a)=>{a.d(t,{A:()=>i});var n=a(6540),r=a(53);const o={tabItem:"tabItem_Ymn6"};function i(e){let{children:t,hidden:a,className:i}=e;return n.createElement("div",{role:"tabpanel",className:(0,r.A)(o.tabItem,i),hidden:a},t)}}}]);