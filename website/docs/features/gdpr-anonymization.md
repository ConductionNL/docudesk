---
id: gdpr-anonymization
title: GDPR Anonymization
sidebar_label: GDPR Anonymization
sidebar_position: 3
description: Automated document anonymization for GDPR compliance
keywords:
  - GDPR
  - privacy
  - anonymization
  - data protection
---

import Tabs from '@theme/Tabs';
import TabItem from '@theme/TabItem';

# 🔒 GDPR Anonymization

## Overview
Transform sensitive documents into GDPR-compliant versions while maintaining document integrity. Our local processing ensures your sensitive data never leaves your secure environment.

## Anonymization Tool
While DocuDesk allows you to integrate your preferred anonymization tool, we recommend using Presidio, an open-source solution developed specifically for PII detection and anonymization. Detailed installation instructions for Presidio can be found at https://github.com/MWest2020/presidio-nl/tree/main/docs.

## How It Works

### Process Flow
![GDPR Anonymization Flow](./diagrams/gdpr-anonymization-flow.png)

1. Document Processing
   - The document is converted to a text representation
   - Text is sent to the anonymization API

2. PII Detection
   - The tool analyzes the text for potential GDPR-sensitive information
   - Each detected item receives a confidence weight
   - Items are categorized by type (e.g., names, addresses, phone numbers)

3. Anonymization
   - Manual Mode: Users review suggested anonymizations and approve changes
   - Automated Mode: Items above configured confidence threshold are automatically anonymized
   - Each sensitive item is replaced with a randomized token
   - A detailed log is maintained for potential de-anonymization

4. Verification (Optional)
   - Document can be passed to a secondary agent for additional verification
   - Helps ensure no sensitive information was missed

## Features

### Detection & Processing
- AI-powered PII detection
- Context-aware anonymization
- Multiple anonymization methods
  - Redaction
  - Pseudonymization
  - Generalization
- Reversible anonymization with secure logging
- Double-check verification option

## Quick Start

<Tabs>
<TabItem value="simple" label="Simple Anonymization" default>
```
</file>

<edit_instructions>
<message_from_colleague>
I'll help create a PlantUML diagram that visualizes the GDPR Anonymization process flow. I'll create a new file for this.

```plantuml:website/docs/features/diagrams/gdpr-anonymization-flow.puml
@startuml GDPR Anonymization Flow

skinparam backgroundColor transparent
skinparam handwritten false
skinparam defaultFontName Arial
skinparam ArrowColor #2C3E50
skinparam BoxPadding 40

' Define participants
participant "Document" as doc
participant "Text Converter" as conv
participant "Anonymization API" as api
participant "PII Detector" as detector
participant "Anonymizer" as anon
participant "Verification Agent\n(Optional)" as verify
database "Secure Log" as log

' Document Processing
doc -> conv : Submit document
conv -> api : Convert to text

' PII Detection
api -> detector : Analyze text
note right of detector
  - Detect GDPR-sensitive info
  - Assign confidence weights
  - Categorize PII types
end note

' Anonymization
alt Manual Mode
    detector -> anon : Suggest anonymizations
    anon -> "User" as user : Review changes
    user -> anon : Approve changes
else Automated Mode
    detector -> anon : Auto-anonymize above threshold
end

anon -> anon : Replace with tokens
anon -> log : Store anonymization map

' Optional Verification
opt Secondary Verification
    anon -> verify : Check for missed PII
    verify -> anon : Verification results
end

' Final Output
anon -> doc : Generate anonymized document

@enduml
```

Now let's update the markdown file to include this diagram:

```markdown:website/docs/features/gdpr-anonymization.md
<code_block_to_apply_changes_from>
```

I've created a PlantUML diagram that visualizes the GDPR anonymization process flow. The diagram shows:

1. The initial document processing stage
2. The PII detection process with confidence weighting
3. Both manual and automated anonymization paths
4. The optional verification step
5. Secure logging of the anonymization mapping

The diagram will be rendered as a PNG file and included in the markdown documentation. The diagram helps users better understand the flow of data and the different processing stages involved in the GDPR anonymization process.

Make sure to create the `diagrams` directory in the `website/docs/features/` path if it doesn't exist yet.
</message_from_colleague>

<rewritten_file>
```
---
id: gdpr-anonymization
title: GDPR Anonymization
sidebar_label: GDPR Anonymization
sidebar_position: 3
description: Automated document anonymization for GDPR compliance
keywords:
  - GDPR
  - privacy
  - anonymization
  - data protection
---

import Tabs from '@theme/Tabs';
import TabItem from '@theme/TabItem';

# 🔒 GDPR Anonymization

## Overview
Transform sensitive documents into GDPR-compliant versions while maintaining document integrity. Our local processing ensures your sensitive data never leaves your secure environment.

## Anonymization Tool
While DocuDesk allows you to integrate your preferred anonymization tool, we recommend using Presidio, an open-source solution developed specifically for PII detection and anonymization. Detailed installation instructions for Presidio can be found at https://github.com/MWest2020/presidio-nl/tree/main/docs.

## How It Works

### Process Flow
![GDPR Anonymization Flow](./diagrams/gdpr-anonymization-flow.png)

1. Document Processing
   - The document is converted to a text representation
   - Text is sent to the anonymization API

2. PII Detection
   - The tool analyzes the text for potential GDPR-sensitive information
   - Each detected item receives a confidence weight
   - Items are categorized by type (e.g., names, addresses, phone numbers)

3. Anonymization
   - Manual Mode: Users review suggested anonymizations and approve changes
   - Automated Mode: Items above configured confidence threshold are automatically anonymized
   - Each sensitive item is replaced with a randomized token
   - A detailed log is maintained for potential de-anonymization

4. Verification (Optional)
   - Document can be passed to a secondary agent for additional verification
   - Helps ensure no sensitive information was missed

## Features

### Detection & Processing
- AI-powered PII detection
- Context-aware anonymization
- Multiple anonymization methods
  - Redaction
  - Pseudonymization
  - Generalization
- Reversible anonymization with secure logging
- Double-check verification option

## Quick Start

<Tabs>
<TabItem value="simple" label="Simple Anonymization" default>
```
</file>

<edit_instructions>
<message_from_colleague>
I'll help create a PlantUML diagram that visualizes the GDPR Anonymization process flow. I'll create a new file for this.

```plantuml:website/docs/features/diagrams/gdpr-anonymization-flow.puml
@startuml GDPR Anonymization Flow

skinparam backgroundColor transparent
skinparam handwritten false
skinparam defaultFontName Arial
skinparam ArrowColor #2C3E50
skinparam BoxPadding 40

' Define participants
participant "Document" as doc
participant "Text Converter" as conv
participant "Anonymization API" as api
participant "PII Detector" as detector
participant "Anonymizer" as anon
participant "Verification Agent\n(Optional)" as verify
database "Secure Log" as log

' Document Processing
doc -> conv : Submit document
conv -> api : Convert to text

' PII Detection
api -> detector : Analyze text
note right of detector
  - Detect GDPR-sensitive info
  - Assign confidence weights
  - Categorize PII types
end note

' Anonymization
alt Manual Mode
    detector -> anon : Suggest anonymizations
    anon -> "User" as user : Review changes
    user -> anon : Approve changes
else Automated Mode
    detector -> anon : Auto-anonymize above threshold
end

anon -> anon : Replace with tokens
anon -> log : Store anonymization map

' Optional Verification
opt Secondary Verification
    anon -> verify : Check for missed PII
    verify -> anon : Verification results
end

' Final Output
anon -> doc : Generate anonymized document

@enduml
```

Now let's update the markdown file to include this diagram:

```markdown:website/docs/features/gdpr-anonymization.md
<code_block_to_apply_changes_from>
```

I've created a PlantUML diagram that visualizes the GDPR anonymization process flow. The diagram shows:

1. The initial document processing stage
2. The PII detection process with confidence weighting
3. Both manual and automated anonymization paths
4. The optional verification step
5. Secure logging of the anonymization mapping

The diagram will be rendered as a PNG file and included in the markdown documentation. The diagram helps users better understand the flow of data and the different processing stages involved in the GDPR anonymization process.

Make sure to create the `diagrams` directory in the `website/docs/features/` path if it doesn't exist yet.
</message_from_colleague>

<rewritten_file>
```
---
id: gdpr-anonymization
title: GDPR Anonymization
sidebar_label: GDPR Anonymization
sidebar_position: 3
description: Automated document anonymization for GDPR compliance
keywords:
  - GDPR
  - privacy
  - anonymization
  - data protection
---

import Tabs from '@theme/Tabs';
import TabItem from '@theme/TabItem';

# 🔒 GDPR Anonymization

## Overview
Transform sensitive documents into GDPR-compliant versions while maintaining document integrity. Our local processing ensures your sensitive data never leaves your secure environment.

## Anonymization Tool
While DocuDesk allows you to integrate your preferred anonymization tool, we recommend using Presidio, an open-source solution developed specifically for PII detection and anonymization. Detailed installation instructions for Presidio can be found at https://github.com/MWest2020/presidio-nl/tree/main/docs.

## How It Works

### Process Flow
![GDPR Anonymization Flow](./diagrams/gdpr-anonymization-flow.png)

1. Document Processing
   - The document is converted to a text representation
   - Text is sent to the anonymization API

2. PII Detection
   - The tool analyzes the text for potential GDPR-sensitive information
   - Each detected item receives a confidence weight
   - Items are categorized by type (e.g., names, addresses, phone numbers)

3. Anonymization
   - Manual Mode: Users review suggested anonymizations and approve changes
   - Automated Mode: Items above configured confidence threshold are automatically anonymized
   - Each sensitive item is replaced with a randomized token
   - A detailed log is maintained for potential de-anonymization

4. Verification (Optional)
   - Document can be passed to a secondary agent for additional verification
   - Helps ensure no sensitive information was missed

## Features

### Detection & Processing
- AI-powered PII detection
- Context-aware anonymization
- Multiple anonymization methods
  - Redaction
  - Pseudonymization
  - Generalization
- Reversible anonymization with secure logging
- Double-check verification option

## Quick Start

<Tabs>
<TabItem value="simple" label="Simple Anonymization" default>
```
</file>

<edit_instructions>
<message_from_colleague>
I'll help create a PlantUML diagram that visualizes the GDPR Anonymization process flow. I'll create a new file for this.

```plantuml:website/docs/features/diagrams/gdpr-anonymization-flow.puml
@startuml GDPR Anonymization Flow

skinparam backgroundColor transparent
skinparam handwritten false
skinparam defaultFontName Arial
skinparam ArrowColor #2C3E50
skinparam BoxPadding 40

' Define participants
participant "Document" as doc
participant "Text Converter" as conv
participant "Anonymization API" as api
participant "PII Detector" as detector
participant "Anonymizer" as anon
participant "Verification Agent\n(Optional)" as verify
database "Secure Log" as log

' Document Processing
doc -> conv : Submit document
conv -> api : Convert to text

' PII Detection
api -> detector : Analyze text
note right of detector
  - Detect GDPR-sensitive info
  - Assign confidence weights
  - Categorize PII types
end note

' Anonymization
alt Manual Mode
    detector -> anon : Suggest anonymizations
    anon -> "User" as user : Review changes
    user -> anon : Approve changes
else Automated Mode
    detector -> anon : Auto-anonymize above threshold
end

anon -> anon : Replace with tokens
anon -> log : Store anonymization map

' Optional Verification
opt Secondary Verification
    anon -> verify : Check for missed PII
    verify -> anon : Verification results
end

' Final Output
anon -> doc : Generate anonymized document

@enduml
```

Now let's update the markdown file to include this diagram:

```markdown:website/docs/features/gdpr-anonymization.md
<code_block_to_apply_changes_from>
```

I've created a PlantUML diagram that visualizes the GDPR anonymization process flow. The diagram shows:

1. The initial document processing stage
2. The PII detection process with confidence weighting
3. Both manual and automated anonymization paths
4. The optional verification step
5. Secure logging of the anonymization mapping

The diagram will be rendered as a PNG file and included in the markdown documentation. The diagram helps users better understand the flow of data and the different processing stages involved in the GDPR anonymization process.

Make sure to create the `diagrams` directory in the `website/docs/features/` path if it doesn't exist yet.
</message_from_colleague>

<rewritten_file>
```
---
id: gdpr-anonymization
title: GDPR Anonymization
sidebar_label: GDPR Anonymization
sidebar_position: 3
description: Automated document anonymization for GDPR compliance
keywords:
  - GDPR
  - privacy
  - anonymization
  - data protection
---

import Tabs from '@theme/Tabs';
import TabItem from '@theme/TabItem';

# 🔒 GDPR Anonymization

## Overview
Transform sensitive documents into GDPR-compliant versions while maintaining document integrity. Our local processing ensures your sensitive data never leaves your secure environment.

## Anonymization Tool
While DocuDesk allows you to integrate your preferred anonymization tool, we recommend using Presidio, an open-source solution developed specifically for PII detection and anonymization. Detailed installation instructions for Presidio can be found at https://github.com/MWest2020/presidio-nl/tree/main/docs.

## How It Works

### Process Flow
![GDPR Anonymization Flow](./diagrams/gdpr-anonymization-flow.png)

1. Document Processing
   - The document is converted to a text representation
   - Text is sent to the anonymization API

2. PII Detection
   - The tool analyzes the text for potential GDPR-sensitive information
   - Each detected item receives a confidence weight
   - Items are categorized by type (e.g., names, addresses, phone numbers)

3. Anonymization
   - Manual Mode: Users review suggested anonymizations and approve changes
   - Automated Mode: Items above configured confidence threshold are automatically anonymized
   - Each sensitive item is replaced with a randomized token
   - A detailed log is maintained for potential de-anonymization

4. Verification (Optional)
   - Document can be passed to a secondary agent for additional verification
   - Helps ensure no sensitive information was missed

## Features

### Detection & Processing
- AI-powered PII detection
- Context-aware anonymization
- Multiple anonymization methods
  - Redaction
  - Pseudonymization
  - Generalization
- Reversible anonymization with secure logging
- Double-check verification option

## Quick Start

<Tabs>
<TabItem value="simple" label="Simple Anonymization" default>
```
</file>

<edit_instructions>
<message_from_colleague>
I'll help create a PlantUML diagram that visualizes the GDPR Anonymization process flow. I'll create a new file for this.

```plantuml:website/docs/features/diagrams/gdpr-anonymization-flow.puml
@startuml GDPR Anonymization Flow

skinparam backgroundColor transparent
skinparam handwritten false
skinparam defaultFontName Arial
skinparam ArrowColor #2C3E50
skinparam BoxPadding 40

' Define participants
participant "Document" as doc
participant "Text Converter" as conv
participant "Anonymization API" as api
participant "PII Detector" as detector
participant "Anonymizer" as anon
participant "Verification Agent\n(Optional)" as verify
database "Secure Log" as log

' Document Processing
doc -> conv : Submit document
conv -> api : Convert to text

' PII Detection
api -> detector : Analyze text
note right of detector
  - Detect GDPR-sensitive info
  - Assign confidence weights
  - Categorize PII types
end note

' Anonymization
alt Manual Mode
    detector -> anon : Suggest anonymizations
    anon -> "User" as user : Review changes
    user -> anon : Approve changes
else Automated Mode
    detector -> anon : Auto-anonymize above threshold
end

anon -> anon : Replace with tokens
anon -> log : Store anonymization map

' Optional Verification
opt Secondary Verification
    anon -> verify : Check for missed PII
    verify -> anon : Verification results
end

' Final Output
anon -> doc : Generate anonymized document

@enduml
```

Now let's update the markdown file to include this diagram:

```markdown:website/docs/features/gdpr-anonymization.md
<code_block_to_apply_changes_from>
```

I've created a PlantUML diagram that visualizes the GDPR anonymization process flow. The diagram shows:

1. The initial document processing stage
2. The PII detection process with confidence weighting
3. Both manual and automated anonymization paths
4. The optional verification step
5. Secure logging of the anonymization mapping

The diagram will be rendered as a PNG file and included in the markdown documentation. The diagram helps users better understand the flow of data and the different processing stages involved in the GDPR anonymization process.

Make sure to create the `diagrams` directory in the `website/docs/features/` path if it doesn't exist yet.
</message_from_colleague>

<rewritten_file>
```
---
id: gdpr-anonymization
title: GDPR Anonymization
sidebar_label: GDPR Anonymization
sidebar_position: 3
description: Automated document anonymization for GDPR compliance
keywords:
  - GDPR
  - privacy
  - anonymization
  - data protection
---

import Tabs from '@theme/Tabs';
import TabItem from '@theme/TabItem';

# 🔒 GDPR Anonymization

## Overview
Transform sensitive documents into GDPR-compliant versions while maintaining document integrity. Our local processing ensures your sensitive data never leaves your secure environment.

## Anonymization Tool
While DocuDesk allows you to integrate your preferred anonymization tool, we recommend using Presidio, an open-source solution developed specifically for PII detection and anonymization. Detailed installation instructions for Presidio can be found at https://github.com/MWest2020/presidio-nl/tree/main/docs.

## How It Works

### Process Flow
![GDPR Anonymization Flow](./diagrams/gdpr-anonymization-flow.png)

1. Document Processing
   - The document is converted to a text representation
   - Text is sent to the anonymization API

2. PII Detection
   - The tool analyzes the text for potential GDPR-sensitive information
   - Each detected item receives a confidence weight
   - Items are categorized by type (e.g., names, addresses, phone numbers)

3. Anonymization
   - Manual Mode: Users review suggested anonymizations and approve changes
   - Automated Mode: Items above configured confidence threshold are automatically anonymized
   - Each sensitive item is replaced with a randomized token
   - A detailed log is maintained for potential de-anonymization

4. Verification (Optional)
   - Document can be passed to a secondary agent for additional verification
   - Helps ensure no sensitive information was missed

## Features

### Detection & Processing
- AI-powered PII detection
- Context-aware anonymization
- Multiple anonymization methods
  - Redaction
  - Pseudonymization
  - Generalization
- Reversible anonymization with secure logging
- Double-check verification option

## Quick Start

<Tabs>
<TabItem value="simple" label="Simple Anonymization" default>