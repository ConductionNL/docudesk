"use strict";(self.webpackChunkdocudesk_docs=self.webpackChunkdocudesk_docs||[]).push([[952],{6446:(e,i,n)=>{n.r(i),n.d(i,{assets:()=>c,contentTitle:()=>d,default:()=>u,frontMatter:()=>o,metadata:()=>s,toc:()=>l});const s=JSON.parse('{"id":"features/document-reporting","title":"Document Reporting","description":"DocuDesk\'s document reporting feature provides comprehensive analysis of documents to identify sensitive information and assess potential privacy risks. This feature integrates with Microsoft Presidio to detect and report on personally identifiable information (PII) and other sensitive data within your documents.","source":"@site/docs/features/document-reporting.md","sourceDirName":"features","slug":"/features/document-reporting","permalink":"/docs/features/document-reporting","draft":false,"unlisted":false,"editUrl":"https://github.com/conductionnl/docudesk/tree/main/website/docs/features/document-reporting.md","tags":[],"version":"current","sidebarPosition":9,"frontMatter":{"sidebar_position":9},"sidebar":"tutorialSidebar","previous":{"title":"Text Extraction","permalink":"/docs/features/text-extraction"},"next":{"title":"Document Validation","permalink":"/docs/features/document-validation"}}');var r=n(4848),t=n(8453);const o={sidebar_position:9},d="Document Reporting",c={},l=[{value:"Overview",id:"overview",level:2},{value:"Key Features",id:"key-features",level:2},{value:"Supported Entity Types",id:"supported-entity-types",level:2},{value:"Risk Assessment",id:"risk-assessment",level:2},{value:"Using Document Reporting",id:"using-document-reporting",level:2},{value:"Integration with Presidio",id:"integration-with-presidio",level:2},{value:"Configuration",id:"configuration",level:2},{value:"Setting Up Presidio",id:"setting-up-presidio",level:2},{value:"Performance Considerations",id:"performance-considerations",level:2},{value:"Security and Privacy",id:"security-and-privacy",level:2},{value:"Compliance Use Cases",id:"compliance-use-cases",level:2},{value:"Limitations",id:"limitations",level:2}];function a(e){const i={a:"a",code:"code",h1:"h1",h2:"h2",header:"header",li:"li",ol:"ol",p:"p",pre:"pre",strong:"strong",ul:"ul",...(0,t.R)(),...e.components};return(0,r.jsxs)(r.Fragment,{children:[(0,r.jsx)(i.header,{children:(0,r.jsx)(i.h1,{id:"document-reporting",children:"Document Reporting"})}),"\n",(0,r.jsx)(i.p,{children:"DocuDesk's document reporting feature provides comprehensive analysis of documents to identify sensitive information and assess potential privacy risks. This feature integrates with Microsoft Presidio to detect and report on personally identifiable information (PII) and other sensitive data within your documents."}),"\n",(0,r.jsx)(i.h2,{id:"overview",children:"Overview"}),"\n",(0,r.jsx)(i.p,{children:"The document reporting system:"}),"\n",(0,r.jsxs)(i.ol,{children:["\n",(0,r.jsx)(i.li,{children:"Extracts text from various document formats"}),"\n",(0,r.jsx)(i.li,{children:"Analyzes the text for sensitive information using Presidio"}),"\n",(0,r.jsx)(i.li,{children:"Generates detailed reports with risk assessments"}),"\n",(0,r.jsx)(i.li,{children:"Stores reports for future reference and compliance purposes"}),"\n"]}),"\n",(0,r.jsx)(i.h2,{id:"key-features",children:"Key Features"}),"\n",(0,r.jsxs)(i.ul,{children:["\n",(0,r.jsxs)(i.li,{children:[(0,r.jsx)(i.strong,{children:"Entity Detection"}),": Identifies various types of sensitive information (names, emails, credit cards, etc.)"]}),"\n",(0,r.jsxs)(i.li,{children:[(0,r.jsx)(i.strong,{children:"Risk Scoring"}),": Calculates risk scores based on the type and quantity of sensitive data"]}),"\n",(0,r.jsxs)(i.li,{children:[(0,r.jsx)(i.strong,{children:"Detailed Reports"}),": Provides comprehensive reports with entity counts and risk levels"]}),"\n",(0,r.jsxs)(i.li,{children:[(0,r.jsx)(i.strong,{children:"Metadata Analysis"}),": Includes document metadata in the analysis"]}),"\n",(0,r.jsxs)(i.li,{children:[(0,r.jsx)(i.strong,{children:"Historical Tracking"}),": Maintains a history of document analyses for compliance"]}),"\n"]}),"\n",(0,r.jsx)(i.h2,{id:"supported-entity-types",children:"Supported Entity Types"}),"\n",(0,r.jsx)(i.p,{children:"The reporting system can detect various types of sensitive information, including:"}),"\n",(0,r.jsxs)(i.ul,{children:["\n",(0,r.jsx)(i.li,{children:"Personal names"}),"\n",(0,r.jsx)(i.li,{children:"Email addresses"}),"\n",(0,r.jsx)(i.li,{children:"Phone numbers"}),"\n",(0,r.jsx)(i.li,{children:"Credit card numbers"}),"\n",(0,r.jsx)(i.li,{children:"Bank account numbers"}),"\n",(0,r.jsx)(i.li,{children:"Social security numbers"}),"\n",(0,r.jsx)(i.li,{children:"Addresses and locations"}),"\n",(0,r.jsx)(i.li,{children:"Dates of birth"}),"\n",(0,r.jsx)(i.li,{children:"IP addresses"}),"\n",(0,r.jsx)(i.li,{children:"Medical license numbers"}),"\n",(0,r.jsx)(i.li,{children:"Passport numbers"}),"\n",(0,r.jsx)(i.li,{children:"Driver's license numbers"}),"\n"]}),"\n",(0,r.jsx)(i.h2,{id:"risk-assessment",children:"Risk Assessment"}),"\n",(0,r.jsx)(i.p,{children:"Each report includes a risk assessment with:"}),"\n",(0,r.jsxs)(i.ul,{children:["\n",(0,r.jsxs)(i.li,{children:[(0,r.jsx)(i.strong,{children:"Risk Score"}),": A numerical score (0-100) indicating the overall risk level"]}),"\n",(0,r.jsxs)(i.li,{children:[(0,r.jsx)(i.strong,{children:"Risk Level"}),": A categorical assessment (Low, Medium, High, Critical)"]}),"\n",(0,r.jsxs)(i.li,{children:[(0,r.jsx)(i.strong,{children:"Entity Counts"}),": Breakdown of detected entities by type"]}),"\n",(0,r.jsxs)(i.li,{children:[(0,r.jsx)(i.strong,{children:"Context Information"}),": Document metadata and processing details"]}),"\n"]}),"\n",(0,r.jsx)(i.h2,{id:"using-document-reporting",children:"Using Document Reporting"}),"\n",(0,r.jsx)(i.p,{children:"You can generate reports programmatically:"}),"\n",(0,r.jsx)(i.pre,{children:(0,r.jsx)(i.code,{className:"language-php",children:"// Example: Generate a report for a document\n$reportingService = \\OC::$server->get(OCA\\DocuDesk\\Service\\ReportingService::class);\n$report = $reportingService->generateReport('/path/to/document.pdf', 'doc-123', 'Important Contract');\n\n// Example: Retrieve a previously generated report\n$report = $reportingService->getReport('report-id');\n\n// Example: Get all reports for a document\n$reports = $reportingService->getReports('doc-123');\n"})}),"\n",(0,r.jsx)(i.h2,{id:"integration-with-presidio",children:"Integration with Presidio"}),"\n",(0,r.jsx)(i.p,{children:"The reporting feature integrates with Microsoft Presidio, an open-source PII detection service:"}),"\n",(0,r.jsxs)(i.ul,{children:["\n",(0,r.jsx)(i.li,{children:"Sends extracted text to Presidio for analysis"}),"\n",(0,r.jsx)(i.li,{children:"Configurable confidence threshold for entity detection"}),"\n",(0,r.jsx)(i.li,{children:"Customizable entity types and detection rules"}),"\n",(0,r.jsx)(i.li,{children:"Support for multiple languages (depending on Presidio configuration)"}),"\n"]}),"\n",(0,r.jsx)(i.h2,{id:"configuration",children:"Configuration"}),"\n",(0,r.jsx)(i.p,{children:"Configure the reporting feature in the DocuDesk admin settings:"}),"\n",(0,r.jsxs)(i.ol,{children:["\n",(0,r.jsxs)(i.li,{children:["Navigate to ",(0,r.jsx)(i.strong,{children:"Admin Settings"})," > ",(0,r.jsx)(i.strong,{children:"DocuDesk"})]}),"\n",(0,r.jsxs)(i.li,{children:["Set the ",(0,r.jsx)(i.strong,{children:"Presidio API URL"})," (default: ",(0,r.jsx)(i.a,{href:"http://presidio-api:8080/analyze",children:"http://presidio-api:8080/analyze"}),")"]}),"\n",(0,r.jsxs)(i.li,{children:["Adjust the ",(0,r.jsx)(i.strong,{children:"Confidence Threshold"})," (0.0-1.0) for entity detection sensitivity"]}),"\n",(0,r.jsx)(i.li,{children:"Enable or disable the reporting feature"}),"\n"]}),"\n",(0,r.jsx)(i.h2,{id:"setting-up-presidio",children:"Setting Up Presidio"}),"\n",(0,r.jsx)(i.p,{children:"To use the reporting feature, you need to set up Microsoft Presidio:"}),"\n",(0,r.jsxs)(i.ol,{children:["\n",(0,r.jsxs)(i.li,{children:["Deploy Presidio using Docker or Kubernetes (see ",(0,r.jsx)(i.a,{href:"https://microsoft.github.io/presidio/",children:"Presidio documentation"}),")"]}),"\n",(0,r.jsx)(i.li,{children:"Configure the analyzer service with appropriate recognition models"}),"\n",(0,r.jsx)(i.li,{children:"Update the DocuDesk settings with your Presidio API URL"}),"\n"]}),"\n",(0,r.jsx)(i.h2,{id:"performance-considerations",children:"Performance Considerations"}),"\n",(0,r.jsx)(i.p,{children:"Document reporting can be resource-intensive:"}),"\n",(0,r.jsxs)(i.ul,{children:["\n",(0,r.jsx)(i.li,{children:"Process large documents asynchronously"}),"\n",(0,r.jsx)(i.li,{children:"Consider batching multiple documents for analysis"}),"\n",(0,r.jsx)(i.li,{children:"Implement caching for frequently accessed reports"}),"\n",(0,r.jsx)(i.li,{children:"Monitor Presidio resource usage for large-scale deployments"}),"\n"]}),"\n",(0,r.jsx)(i.h2,{id:"security-and-privacy",children:"Security and Privacy"}),"\n",(0,r.jsx)(i.p,{children:"The reporting feature is designed with security in mind:"}),"\n",(0,r.jsxs)(i.ul,{children:["\n",(0,r.jsx)(i.li,{children:"All communication with Presidio is secured"}),"\n",(0,r.jsx)(i.li,{children:"Reports are stored securely within your Nextcloud instance"}),"\n",(0,r.jsx)(i.li,{children:"Access to reports can be restricted based on user permissions"}),"\n",(0,r.jsx)(i.li,{children:"No sensitive data is sent to external services beyond Presidio"}),"\n"]}),"\n",(0,r.jsx)(i.h2,{id:"compliance-use-cases",children:"Compliance Use Cases"}),"\n",(0,r.jsx)(i.p,{children:"Document reporting supports various compliance scenarios:"}),"\n",(0,r.jsxs)(i.ul,{children:["\n",(0,r.jsxs)(i.li,{children:[(0,r.jsx)(i.strong,{children:"GDPR Compliance"}),": Identify documents containing personal data"]}),"\n",(0,r.jsxs)(i.li,{children:[(0,r.jsx)(i.strong,{children:"PCI DSS"}),": Detect credit card information in documents"]}),"\n",(0,r.jsxs)(i.li,{children:[(0,r.jsx)(i.strong,{children:"HIPAA"}),": Identify documents with protected health information"]}),"\n",(0,r.jsxs)(i.li,{children:[(0,r.jsx)(i.strong,{children:"Data Minimization"}),": Support data minimization efforts by identifying unnecessary PII"]}),"\n",(0,r.jsxs)(i.li,{children:[(0,r.jsx)(i.strong,{children:"Data Mapping"}),": Help create data maps by identifying where sensitive data resides"]}),"\n"]}),"\n",(0,r.jsx)(i.h2,{id:"limitations",children:"Limitations"}),"\n",(0,r.jsx)(i.p,{children:"Be aware of these limitations:"}),"\n",(0,r.jsxs)(i.ul,{children:["\n",(0,r.jsx)(i.li,{children:"Detection accuracy depends on Presidio's recognition capabilities"}),"\n",(0,r.jsx)(i.li,{children:"Some context-specific PII may not be detected without custom recognizers"}),"\n",(0,r.jsx)(i.li,{children:"Very large documents may require additional processing time"}),"\n",(0,r.jsx)(i.li,{children:"Image-based documents require OCR before analysis (not included)"}),"\n"]})]})}function u(e={}){const{wrapper:i}={...(0,t.R)(),...e.components};return i?(0,r.jsx)(i,{...e,children:(0,r.jsx)(a,{...e})}):a(e)}},8453:(e,i,n)=>{n.d(i,{R:()=>o,x:()=>d});var s=n(6540);const r={},t=s.createContext(r);function o(e){const i=s.useContext(t);return s.useMemo((function(){return"function"==typeof e?e(i):{...i,...e}}),[i,e])}function d(e){let i;return i=e.disableParentContext?"function"==typeof e.components?e.components(r):e.components||r:o(e.components),s.createElement(t.Provider,{value:i},e.children)}}}]);