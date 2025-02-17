"use strict";(self.webpackChunkdocudesk_docs=self.webpackChunkdocudesk_docs||[]).push([[500],{1470:(e,t,n)=>{n.d(t,{A:()=>k});var r=n(8168),a=n(6540),o=n(53),i=n(3104),l=n(6347),s=n(7485),u=n(1682),c=n(9466);function p(e){return function(e){return a.Children.map(e,(e=>{if(!e||(0,a.isValidElement)(e)&&function(e){const{props:t}=e;return!!t&&"object"==typeof t&&"value"in t}(e))return e;throw new Error(`Docusaurus error: Bad <Tabs> child <${"string"==typeof e.type?e.type:e.type.name}>: all children of the <Tabs> component should be <TabItem>, and every <TabItem> should have a unique "value" prop.`)}))?.filter(Boolean)??[]}(e).map((e=>{let{props:{value:t,label:n,attributes:r,default:a}}=e;return{value:t,label:n,attributes:r,default:a}}))}function d(e){const{values:t,children:n}=e;return(0,a.useMemo)((()=>{const e=t??p(n);return function(e){const t=(0,u.X)(e,((e,t)=>e.value===t.value));if(t.length>0)throw new Error(`Docusaurus error: Duplicate values "${t.map((e=>e.value)).join(", ")}" found in <Tabs>. Every value needs to be unique.`)}(e),e}),[t,n])}function m(e){let{value:t,tabValues:n}=e;return n.some((e=>e.value===t))}function f(e){let{queryString:t=!1,groupId:n}=e;const r=(0,l.W6)(),o=function(e){let{queryString:t=!1,groupId:n}=e;if("string"==typeof t)return t;if(!1===t)return null;if(!0===t&&!n)throw new Error('Docusaurus error: The <Tabs> component groupId prop is required if queryString=true, because this value is used as the search param name. You can also provide an explicit value such as queryString="my-search-param".');return n??null}({queryString:t,groupId:n});return[(0,s.aZ)(o),(0,a.useCallback)((e=>{if(!o)return;const t=new URLSearchParams(r.location.search);t.set(o,e),r.replace({...r.location,search:t.toString()})}),[o,r])]}function g(e){const{defaultValue:t,queryString:n=!1,groupId:r}=e,o=d(e),[i,l]=(0,a.useState)((()=>function(e){let{defaultValue:t,tabValues:n}=e;if(0===n.length)throw new Error("Docusaurus error: the <Tabs> component requires at least one <TabItem> children component");if(t){if(!m({value:t,tabValues:n}))throw new Error(`Docusaurus error: The <Tabs> has a defaultValue "${t}" but none of its children has the corresponding value. Available values are: ${n.map((e=>e.value)).join(", ")}. If you intend to show no default tab, use defaultValue={null} instead.`);return t}const r=n.find((e=>e.default))??n[0];if(!r)throw new Error("Unexpected error: 0 tabValues");return r.value}({defaultValue:t,tabValues:o}))),[s,u]=f({queryString:n,groupId:r}),[p,g]=function(e){let{groupId:t}=e;const n=function(e){return e?`docusaurus.tab.${e}`:null}(t),[r,o]=(0,c.Dv)(n);return[r,(0,a.useCallback)((e=>{n&&o.set(e)}),[n,o])]}({groupId:r}),b=(()=>{const e=s??p;return m({value:e,tabValues:o})?e:null})();(0,a.useLayoutEffect)((()=>{b&&l(b)}),[b]);return{selectedValue:i,selectValue:(0,a.useCallback)((e=>{if(!m({value:e,tabValues:o}))throw new Error(`Can't select invalid tab value=${e}`);l(e),u(e),g(e)}),[u,g,o]),tabValues:o}}var b=n(2303);const v={tabList:"tabList__CuJ",tabItem:"tabItem_LNqP"};function y(e){let{className:t,block:n,selectedValue:l,selectValue:s,tabValues:u}=e;const c=[],{blockElementScrollPositionUntilNextRender:p}=(0,i.a_)(),d=e=>{const t=e.currentTarget,n=c.indexOf(t),r=u[n].value;r!==l&&(p(t),s(r))},m=e=>{let t=null;switch(e.key){case"Enter":d(e);break;case"ArrowRight":{const n=c.indexOf(e.currentTarget)+1;t=c[n]??c[0];break}case"ArrowLeft":{const n=c.indexOf(e.currentTarget)-1;t=c[n]??c[c.length-1];break}}t?.focus()};return a.createElement("ul",{role:"tablist","aria-orientation":"horizontal",className:(0,o.A)("tabs",{"tabs--block":n},t)},u.map((e=>{let{value:t,label:n,attributes:i}=e;return a.createElement("li",(0,r.A)({role:"tab",tabIndex:l===t?0:-1,"aria-selected":l===t,key:t,ref:e=>c.push(e),onKeyDown:m,onClick:d},i,{className:(0,o.A)("tabs__item",v.tabItem,i?.className,{"tabs__item--active":l===t})}),n??t)})))}function h(e){let{lazy:t,children:n,selectedValue:r}=e;const o=(Array.isArray(n)?n:[n]).filter(Boolean);if(t){const e=o.find((e=>e.props.value===r));return e?(0,a.cloneElement)(e,{className:"margin-top--md"}):null}return a.createElement("div",{className:"margin-top--md"},o.map(((e,t)=>(0,a.cloneElement)(e,{key:t,hidden:e.props.value!==r}))))}function w(e){const t=g(e);return a.createElement("div",{className:(0,o.A)("tabs-container",v.tabList)},a.createElement(y,(0,r.A)({},e,t)),a.createElement(h,(0,r.A)({},e,t)))}function k(e){const t=(0,b.A)();return a.createElement(w,(0,r.A)({key:String(t)},e))}},5680:(e,t,n)=>{n.d(t,{xA:()=>c,yg:()=>f});var r=n(6540);function a(e,t,n){return t in e?Object.defineProperty(e,t,{value:n,enumerable:!0,configurable:!0,writable:!0}):e[t]=n,e}function o(e,t){var n=Object.keys(e);if(Object.getOwnPropertySymbols){var r=Object.getOwnPropertySymbols(e);t&&(r=r.filter((function(t){return Object.getOwnPropertyDescriptor(e,t).enumerable}))),n.push.apply(n,r)}return n}function i(e){for(var t=1;t<arguments.length;t++){var n=null!=arguments[t]?arguments[t]:{};t%2?o(Object(n),!0).forEach((function(t){a(e,t,n[t])})):Object.getOwnPropertyDescriptors?Object.defineProperties(e,Object.getOwnPropertyDescriptors(n)):o(Object(n)).forEach((function(t){Object.defineProperty(e,t,Object.getOwnPropertyDescriptor(n,t))}))}return e}function l(e,t){if(null==e)return{};var n,r,a=function(e,t){if(null==e)return{};var n,r,a={},o=Object.keys(e);for(r=0;r<o.length;r++)n=o[r],t.indexOf(n)>=0||(a[n]=e[n]);return a}(e,t);if(Object.getOwnPropertySymbols){var o=Object.getOwnPropertySymbols(e);for(r=0;r<o.length;r++)n=o[r],t.indexOf(n)>=0||Object.prototype.propertyIsEnumerable.call(e,n)&&(a[n]=e[n])}return a}var s=r.createContext({}),u=function(e){var t=r.useContext(s),n=t;return e&&(n="function"==typeof e?e(t):i(i({},t),e)),n},c=function(e){var t=u(e.components);return r.createElement(s.Provider,{value:t},e.children)},p="mdxType",d={inlineCode:"code",wrapper:function(e){var t=e.children;return r.createElement(r.Fragment,{},t)}},m=r.forwardRef((function(e,t){var n=e.components,a=e.mdxType,o=e.originalType,s=e.parentName,c=l(e,["components","mdxType","originalType","parentName"]),p=u(n),m=a,f=p["".concat(s,".").concat(m)]||p[m]||d[m]||o;return n?r.createElement(f,i(i({ref:t},c),{},{components:n})):r.createElement(f,i({ref:t},c))}));function f(e,t){var n=arguments,a=t&&t.mdxType;if("string"==typeof e||a){var o=n.length,i=new Array(o);i[0]=m;var l={};for(var s in t)hasOwnProperty.call(t,s)&&(l[s]=t[s]);l.originalType=e,l[p]="string"==typeof e?e:a,i[1]=l;for(var u=2;u<o;u++)i[u]=n[u];return r.createElement.apply(null,i)}return r.createElement.apply(null,n)}m.displayName="MDXCreateElement"},5693:(e,t,n)=>{n.r(t),n.d(t,{assets:()=>c,contentTitle:()=>s,default:()=>f,frontMatter:()=>l,metadata:()=>u,toc:()=>p});var r=n(8168),a=(n(6540),n(5680)),o=n(1470),i=n(9365);const l={id:"document-comparison",title:"Document Comparison",sidebar_label:"Document Comparison",sidebar_position:7,description:"Compare different versions of documents and track changes",keywords:["comparison","diff","version control","track changes"]},s="\ud83d\udd0d Document Comparison",u={unversionedId:"features/document-comparison",id:"features/document-comparison",title:"Document Comparison",description:"Compare different versions of documents and track changes",source:"@site/docs/features/document-comparison.md",sourceDirName:"features",slug:"/features/document-comparison",permalink:"/docudesk/docs/features/document-comparison",draft:!1,editUrl:"https://github.com/conductionnl/docudesk/tree/main/website/docs/features/document-comparison.md",tags:[],version:"current",sidebarPosition:7,frontMatter:{id:"document-comparison",title:"Document Comparison",sidebar_label:"Document Comparison",sidebar_position:7,description:"Compare different versions of documents and track changes",keywords:["comparison","diff","version control","track changes"]},sidebar:"tutorialSidebar",previous:{title:"External Integration",permalink:"/docudesk/docs/features/external-integration"},next:{title:"Document Classification",permalink:"/docudesk/docs/features/document-classification"}},c={},p=[{value:"Overview",id:"overview",level:2},{value:"Features",id:"features",level:2},{value:"Comparison Capabilities",id:"comparison-capabilities",level:3},{value:"Quick Start",id:"quick-start",level:2},{value:"Use Cases",id:"use-cases",level:2}],d={toc:p},m="wrapper";function f(e){let{components:t,...n}=e;return(0,a.yg)(m,(0,r.A)({},d,n,{components:t,mdxType:"MDXLayout"}),(0,a.yg)("h1",{id:"-document-comparison"},"\ud83d\udd0d Document Comparison"),(0,a.yg)("h2",{id:"overview"},"Overview"),(0,a.yg)("p",null,"Compare different versions of documents to track changes, identify modifications, and ensure document integrity, all within your secure local environment."),(0,a.yg)("h2",{id:"features"},"Features"),(0,a.yg)("h3",{id:"comparison-capabilities"},"Comparison Capabilities"),(0,a.yg)("ul",null,(0,a.yg)("li",{parentName:"ul"},"Version-to-version comparison"),(0,a.yg)("li",{parentName:"ul"},"Multi-format support (PDF, Word, HTML)"),(0,a.yg)("li",{parentName:"ul"},"Visual diff highlighting"),(0,a.yg)("li",{parentName:"ul"},"Change tracking and annotation"),(0,a.yg)("li",{parentName:"ul"},"Metadata comparison"),(0,a.yg)("li",{parentName:"ul"},"Batch comparison support")),(0,a.yg)("h2",{id:"quick-start"},"Quick Start"),(0,a.yg)(o.A,{mdxType:"Tabs"},(0,a.yg)(i.A,{value:"compare",label:"Compare Documents",default:!0,mdxType:"TabItem"},(0,a.yg)("pre",null,(0,a.yg)("code",{parentName:"pre",className:"language-php"},"// Compare two versions of a document\n$comparison = $comparisonService->compare(\n    originalId: 123,\n    revisedId: 124,\n    options: [\n        'highlightChanges' => true,\n        'trackMetadata' => true\n    ]\n);\n"))),(0,a.yg)(i.A,{value:"report",label:"Generate Report",mdxType:"TabItem"},(0,a.yg)("pre",null,(0,a.yg)("code",{parentName:"pre",className:"language-php"},"// Generate detailed comparison report\n$report = $comparisonService->generateReport(\n    comparisonId: $comparison->getId(),\n    format: 'pdf',\n    includeAnnotations: true\n);\n")))),(0,a.yg)("admonition",{title:"Local Processing",type:"tip"},(0,a.yg)("p",{parentName:"admonition"},"All comparison operations happen locally, ensuring sensitive content never leaves your secure environment.")),(0,a.yg)("admonition",{title:"AI-Powered",type:"info"},(0,a.yg)("p",{parentName:"admonition"},"Uses advanced AI to identify even subtle changes while maintaining context.")),(0,a.yg)("h2",{id:"use-cases"},"Use Cases"),(0,a.yg)("ul",null,(0,a.yg)("li",{parentName:"ul"},"Contract revision tracking"),(0,a.yg)("li",{parentName:"ul"},"Document version control"),(0,a.yg)("li",{parentName:"ul"},"Compliance verification"),(0,a.yg)("li",{parentName:"ul"},"Legal document review"),(0,a.yg)("li",{parentName:"ul"},"Content validation")))}f.isMDXComponent=!0},9365:(e,t,n)=>{n.d(t,{A:()=>i});var r=n(6540),a=n(53);const o={tabItem:"tabItem_Ymn6"};function i(e){let{children:t,hidden:n,className:i}=e;return r.createElement("div",{role:"tabpanel",className:(0,a.A)(o.tabItem,i),hidden:n},t)}}}]);