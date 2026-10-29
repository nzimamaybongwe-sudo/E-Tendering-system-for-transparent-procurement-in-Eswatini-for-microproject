# A digital E-Tendering platform for transparent procurement in Eswatini for Microprojects

**PROJECT SUBMITTED IN PARTIAL FULFILLMENT OF THE REQUIREMENT FOR THE DEGREE OF**

### COMPUTER SCIENCE

**By**
* **Author Name:** Nzima Mayibongwe
* **Student Number:** 202280218

**UNDER THE GUIDANCE**
* **Guide Name:** Mr Makhubu
* **Guides Affiliation:** Supervisor

**DEPARTMENT OF COMPUTER SCIENCE ESWATINI**  
**MEDICAL CHRISTIAN UNIVERSITY**  
**MBABANE, ESWATINI**  

---


ABSTRACT
Public procurement within Eswatini is hindered by systemic opacity, institutional bias, and structural barriers that restrict equitable access to government contracts. Consequently, small and medium-sized enterprises (SMEs) frequently struggle to participate on a level playing field, as tender information remains decentralized, administrative processes are heavily paper-bound, and allegations of predetermined contract awards persist. These systemic deficiencies erode public trust in regulatory bodies, disincentivize competitive bidding, and ultimately diminish institutional accountability.

To resolve these structural bottlenecks, this project proposes the architecture and implementation of a centralized digital e-tendering platform designed specifically for the Eswatini context. The system consolidates tender publication and vendor registration while securing bid submissions through hash-based integrity protocols and stringent role-based access controls. To systematically enhance transparency, the architecture integrates public-facing dashboards, automated notification pipelines, and explicitly published scoring rubrics. Furthermore, the platform actively capacitates SMEs by integrating portfolio showcasing tools and structured bid-preparation tutorials. Administrative oversight is supported via analytics modules for prototype-level anomaly detection and document management systems that track credential expiry and facilitate manual compliance audits.

By actively dismantling the technical and administrative barriers inherent in the current manual system, this platform seeks to rehabilitate institutional trust, enforce procedural fairness, and broaden commercial participation in Eswatini’s public procurement landscape, thereby cultivating a more rigorously accountable and economically competitive business ecosystem.

Contents
ABSTRACT	2
CHAPTER 1	13
1.1 Introduction and background	13
1.2 Problem statement	15
1.3 Aim/s of the study	15
1.3.1 Aim	15
1.4 Objectives of the study	15
1.5 Research questions	16
1.6 Significance of the study	16
1.7 Scope and limitations	17
CHAPTER 2	18
2.0 Literature Review	18
CHAPTER 3	22
METHODOLOGY	22
3.2 Research Approach	22
3.3 Research Design	22
3.3.1 System Development Methodology	23
3.3.2 System Analysis	23
3.3.3 System Workflow	24
3.3.4 System Interaction	26
3.4 Target Population	28
3.5 Sampling	29
3.5.1 Sample Size	29
3.6 Data Collection Instruments	30
3.7 Data Analysis	31
3.8 Ethical Considerations	31
CHAPTER 4	33
System design and implementation.	33
4.1 Introduction	33
4.2 Overall System Structure	33
4.3 System Architecture	33
4.4 Database Design	34
4.5 User Roles and What Each Can Do	35
4.5.1 Contractor	35
4.5.2 Administrator	36
4.5.3 Evaluator	36
4.5.4 Procurement Officer	36
4.6 Main System Modules	36
4.6.1 Contractor Registration and Document Management	36
4.6.2 Tender Creation and Publication	36
4.6.3 Tender Browsing and Search	37
4.6.4 Bid Submission	37
4.6.5 Bid Evaluation	37
4.6.6 Award and Notification	37
4.7 Document Handling	37
4.8 Automatic Tender Closing	38
4.9 Protecting Bid Integrity	38
4.10 Audit Trail	38
4.11 Security Measures	39
4.12 Automation Features	39
4.13 Technologies Used	40
4.14 Chapter Summary	41
CHAPTER 5	42
System testing, Results and demonstration	42
5.1 Introduction	42
5.2 System Interface Demonstration	42
5.2.1 The Landing Page	42
5.2.2 Available Tenders	43
5.2.3 Public Tender Statistics	43
5.2.4 Procurement Guidance Centre.	44
5.2.5 Contractor Registration.	45
5.2.6 Contractor Dashboard.	45
5.2.7 Tender Details and Bid Submission	46
5.2.8 Bid Submission Confirmation	47
5.2.9 Administrator Dashboard	48
5.2.10 Procurement Officer Dashboard	48
5.2.11 Evaluator Dashboard	49
5.3 System Testing	49
5.3.1 Functional Testing	49
5.4 Tender Closing Automation Test	50
5.5 Bid Integrity Test	51
5.6 Document Upload Test	51
5.7 Role-Based Access Test	51
5.8 Usability Testing	52
5.9 Summary of Test Results	52
5.10 Chapter Summary	52
CHAPTER 6	53
Conclusion and Recommendations	53
6.1 Introduction	53
6.2 Project Summary	53
6.3 Achievement of Objectives	53
6.4 Contributions of the System	54
6.5 Limitations of the System	54
6.6 Recommendations	55
6.6.1 Integration with External Systems	55
6.6.2 Mobile Application	55
6.6.3 Enhanced Document Verification	55
6.6.4 Notification Services	55
6.6.5 Local Language Support	55
6.6.6 Mobile Optimisation	56
6.7 Future Development	56
6.8 Final Conclusion	56
References	57

CHAPTER 1

1.1 Introduction and background  
In Eswatini, the state's capacity to deliver essential infrastructure and public services hinges critically on the efficiency of its public procurement mechanisms, overseen by bodies such as Microprojects and the Eswatini Public Procurement Regulatory Authority (ESPPRA). This matters because procurement is not merely an administrative function; it is a primary lever for national economic development. Despite the existence of these regulatory frameworks, Eswatini's procurement ecosystem remains constrained by profound operational bottlenecks. Primary among these are a pervasive lack of transparency, an entrenched reliance on archaic paper-based procedures, and lingering public perceptions of procedural inequity—factors that collectively degrade institutional trust and actively stifle market competition.  

For many local commercial entities, particularly small and medium enterprises (SMEs), navigating the procurement landscape is prohibitively difficult. Tender documentation is frequently siloed, forcing business owners to rely on physical bulletin boards and manual document deliveries. This administrative friction generates costly delays, heightens the risk of misplaced dossiers, and imposes disproportionate financial burdens on both vendors and the state. Furthermore, persistent allegations that certain contracts are informally allocated prior to public advertisement severely undermine the legitimacy of the process. Consequently, highly capable local enterprises often self-select out of the bidding process, which artificially constricts the supplier pool, stifles innovation, and retards domestic economic expansion.  

Recognizing the unsustainability of these manual processes, progressive administrations globally are adopting digital infrastructure to modernize state purchasing. The transition to electronic platforms is critical because these systems democratize access to information, enforce cryptographically secure bid submissions, and generate immutable digital audit trails that compel administrative accountability. While international bodies such as the World Bank and the African Development Bank heavily advocate for these digital transitions as global standards for good governance, the empirical evidence from regional peers is particularly instructive. Jurisdictions like Kenya, South Africa, and Rwanda have demonstrated that shifting procurement to digital ecosystems drastically curtails opportunities for graft, accelerates processing times, and decisively lowers the barrier to entry for marginalized enterprises. What distinguishes Eswatini's case is the immediate opportunity to leapfrog legacy technological phases by adopting a bespoke, secure platform tailored to its specific regulatory environment.  

For Eswatini, transitioning to a centralized e-tendering architecture represents a pragmatic structural intervention. A unified portal would enable vendors to execute a single, comprehensive registration, digitize compliance documentation, and subscribe to automated tender alerts. Conversely, state procuring entities could seamlessly publish solicitations with immutable deadlines and transparent evaluation metrics, while bids are processed within a cryptographically secure environment. This paradigm shift would systematically eliminate redundant paperwork, enforce procedural visibility, and render the entire lifecycle of public spending rigorously auditable.  

This study, therefore, is dedicated to the design and deployment of a contextualized e-tendering solution for Eswatini. By eradicating analogue inefficiencies and democratizing access to tender data, the proposed architecture aims to embed fairness and transparency into the state's purchasing operations, thereby catalyzing inclusive economic growth and capacitating domestic enterprise.  

1.2 Problem statement  
In Eswatini, businesses often face limited access to tender opportunities due to manual processes, poor transparency and perceived favouritism. Many small and medium enterprises report that tenders are issued with predetermined outcomes, undermining trust and discouraging fair competition. Eswatini Public Procurement Agency assessment confirms systemic weakness in procurement, including fragmented communication and lack of digital infrastructure. A secure, centralized e-tendering platform could help address these challenges by promoting openness, accountability and inclusive participation.  

1.3 Aim/s of the study  

1.3.1 Aim 
To design and implement a secure, user-friendly digital E-Tendering platform for Microproject that enhances transparency, accessibility and accountability in Eswatini's procurement processes, while empowering small and medium enterprises (SMEs) to participate fairly in the tender opportunities.  

1.4 Objectives of the study
  
•	To develop a centralized online system for tender publication, advertisement and business registration  
•	To implement secure bid submission mechanisms using hash-based integrity verification and role-based access control.  
•	To integrate business credential verification and portfolio showcasing features for registered companies.  
•	To provide real time transparency through dashboards, automated notification, and public scoring rubrics  
•	To design tutorials and guidelines that assist SMEs in preparing for competitive bids  
•	To incorporate performance tracking that help businesses monitor their bidding history  
•	To include prototype-level pattern monitoring features that flag suspicious bidding patterns for reviews  
  
1.5 Research questions 
•	How can a centralized digital platform improve access to tender opportunities for businesses in Eswatini?  
•	What security features are most effective in ensuring authentic and secure bid submissions?  
•	How can business credential verification and portfolio showcasing enhance the evaluator's confidence in SMEs?  
•	In what way do transparency tools like dashboards and scoring rubrics influence fairness and trust in procurement?  
•	How can tutorials and guidelines support SMEs in preparing competitive and compliant bids?  
•	How can performance tracking tools help the business to improve its bidding strategies?  
•	What methods can be used to detect and prevent fraudulent or biased bidding patterns?  
  
1.6 Significance of the study  
•	Improve transparency in project approval and tendering.  
•	Reduce delays and loss of important documents.  
•	Provide a practical model for digital transformation in public sector projects.  
•	Provide equal access to tender opportunities, encouraging small and medium businesses to participate and grow.  
•	Introduces fraud detection and document verification, showcasing how emerging technologies can solve real-world problems  
 
1.7 Scope and limitations  
•	It relies on internet connectivity, which may be limited in some rural communities.  
•	The project uses simulated data rather than live government data; therefore, it may not meet full government-grade standards.  
•	The system is a prototype and may not represent all real-world policies and legal requirements of Microprojects Eswatini.  
•	User adoption may be slow due to varying levels of digital literacy.  

CHAPTER 2

2.0 Literature Review  
Within developing economies, small, medium, and micro enterprises (SMMEs) are fundamentally acknowledged as the primary engines of structural economic transformation, driving robust employment generation and contributing significantly to national gross domestic product. In the specific context of Eswatini, however, SMMEs—particularly those embedded within the construction sector—chronically underperform relative to their market potential. Clinton [1] traces this systemic underutilization to intersecting structural barriers: formidable obstacles in contract acquisition, a deficit of targeted state facilitation, and restricted engagement from public procuring entities. These intertwined constraints actively suffocate the expansion and competitive viability of indigenous construction firms.  

Because state procurement is financed through the public fiscus, there is a fundamental civic expectation that these funds are disbursed with maximum efficiency and unimpeachable fairness. Recognizing this imperative, the government of Eswatini initiated comprehensive regulatory reforms in 2016 designed to optimize procurement efficacy [2]. The macroeconomic stakes of this optimization are profound: in developing contexts similar to Eswatini, public procurement routinely consumes between 15% and 20% of aggregate government expenditure [3]. This capital funds critical state functions ranging from civil infrastructure to healthcare provisioning. Consequently, the strategic governance of these funds directly dictates the trajectory of national development and fundamentally shapes the citizenry's trust in state apparatuses.  

Despite these reforms, embedding genuine equity and transparency into procurement operations remains a persistent global challenge, and Eswatini is no outlier. Enduring friction exists regarding the equitable dissemination of tender intelligence; numerous enterprises report systemic marginalization, while suspicions persist that a select cohort of firms benefits from asymmetric information access. This dynamic aligns tightly with broader public administration literature, which consistently flags procurement as one of the state functions most vulnerable to malfeasance. This vulnerability stems directly from the fact that legacy, analogue tendering frameworks inherently obscure the administrative trail, making it virtually impossible to audit exactly who viewed specific documents and at what time [4]. The downstream economic effect of this opacity is severe: agile, emerging businesses routinely forfeit lucrative opportunities simply due to structural information asymmetry.  

The friction generated by analogue procurement is particularly debilitating across the African continent, notably in Eswatini. Corporate leadership is frequently compelled to undertake expensive, time-consuming physical travel merely to acquire foundational tender dossiers [5]. This introduces a regressive financial and temporal tax that heavily capitalized, incumbent firms can easily absorb, but which routinely bankrupts the profit margins of smaller entrants. Analytically, this means the state artificially restricts its own supply chain; a diminished bidder pool invariably degrades competitive tension, forcing the government to absorb inflated contract premiums that a truly open market would aggressively correct.  

In an effort to mitigate these structural flaws, Eswatini instituted the Public Procurement Regulatory Agency to function as an independent oversight mechanism [6]. Nevertheless, despite this vital legislative milestone, the operational reality of tendering remains heavily analogue. Domestic media consistently reports on supplier grievances regarding opaque and erratic adjudication processes [7]. This indicates a critical policy gap: statutory reform, while necessary, is wholly insufficient without the concurrent modernization of the underlying operational infrastructure.  

In light of these persistent systemic failures, contemporary academic consensus increasingly advocates for e-procurement as the most viable structural remedy for the endemic inefficiencies of manual processing. By digitizing document routing and administrative authorizations, transactional latency is drastically compressed, yielding significant operational dividends for both state agencies and commercial bidders.  

Crucially, the digitalization of procurement is not merely an administrative upgrade; it is a mechanism for market optimization. When tender portals drastically lower the search costs for businesses, state agencies inherently attract a broader, more aggressive cohort of suppliers, thereby driving superior fiscal outcomes [8]. The economic impact of this accessibility is highly quantifiable: empirical analysis demonstrates that the introduction of every marginal bidder into a tender process reliably compresses the final contract valuation by roughly 5% [9]. This clearly illustrates that digital inclusivity directly engineers substantial public savings. The transformative potential of this approach is starkly evident in South Korea’s national e-procurement architecture, KONEPS, which has successfully migrated nearly the entirety of the state’s purchasing into a unified digital environment [10]. Beyond merely automating tasks, KONEPS fundamentally re-engineered the state's market interactions, generating billions in documented fiscal savings and radically compressing procurement cycles, while simultaneously achieving unprecedented satisfaction metrics from both the public sector and private suppliers.  

This paradigm of digital efficiency is similarly validated in the Latin American context, where the Chilean government’s ChileCompra framework has established a benchmark for regional procurement modernization [11]. The analytical takeaway from these implementations is that digital systems compel procedural discipline that manual systems simply cannot enforce.  

Furthermore, integrating digital tendering with legacy state databases actively eliminates administrative redundancies and significantly alleviates bureaucratic friction for civil servants [12]. More importantly, this deep systemic integration allows digital portals to systematically onboard the SMMEs that analogue bureaucracies have structurally marginalized. Rwanda provides a compelling continental case study: its aggressively deployed e-procurement infrastructure now facilitates over 90% of all state acquisitions [13]. The Rwandan model demonstrates that digitalizing procurement does more than streamline purchasing; by allowing the public to trace state expenditure in real-time, the system actively rehabilitates the social contract and fortifies civic trust in government integrity.  

The Kenyan deployment of e-procurement further underscores the strategic necessity of embedding these platforms directly into the state's broader financial management architecture [14]. Prior to this integration, the Kenyan supplier ecosystem was characterized by severe information asymmetry. By centralizing all procurement data on a unified digital portal, the state effectively democratized market access, particularly empowering provincial enterprises that were previously geographically locked out of the capital's procurement loops [15]. While this drastically elevated systemic transparency, the Kenyan rollout offers a critical implementation caveat: software deployment must be coupled with rigorous human capital development. Extensive capacity building was required for both civil servants and vendors to navigate the new digital reality. Additionally, comparative analyses stress that robust cryptographic security is non-negotiable; given the high-stakes financial data inherent in tendering, platforms must be architected to guarantee data integrity without sacrificing user accessibility [16].  

In the context of Eswatini and the broader continent, platform accessibility must be viewed primarily through a mobile-first lens [17]. Given that cellular penetration vastly outstrips traditional broadband and desktop ownership across Africa, digital procurement architectures must be natively optimized for mobile interfaces. This structural choice ensures that vendors operating in deep rural peripheries can monitor solicitations and lodge secure bids without being tethered to physical corporate infrastructure.  

Consolidating disparate functions—such as vendor vetting, digital disbursements, and contract lifecycle management—into a singular, cohesive ecosystem also drastically curtails bureaucratic duplication across disparate ministries [18]. Encouragingly, Eswatini has already laid the necessary legislative groundwork; the Public Procurement Act explicitly codified the modern rules of engagement and birthed ESPPRA to enforce them [19]. Yet, the critical failure has been execution; the Act has not catalyzed the required technological leap, leaving Eswatini's procurement deeply entrenched in the era of physical paper trails.  

Empirical data from domestic supplier surveys rigorously validates these systemic bottlenecks, mapping the chronic administrative exhaustion experienced by vendors attempting to engage with the state [20]. From a policy perspective, there is clear governmental intent to rectify this; national strategic frameworks explicitly target technological procurement modernization as a vital developmental priority [21]. However, bridging the gap between policy intent and operational reality requires meticulous architectural planning. E-procurement platforms cannot exist in a vacuum; they must be seamlessly interoperable with existing state infrastructure, including revenue authorities, corporate registries, and central financial systems [22]. This interoperability is paramount because it eradicates the need for manual data reconciliation and the associated human errors [23]. Furthermore, successful adoption is inextricably linked to continuous user support and intuitive interface design that accommodates vastly different tiers of digital literacy [24]. Finally, the foundational advantage of digital procurement, as emphasized across the literature, is absolute auditability. Because a digital system permanently logs every user interaction, post-hoc investigations are mathematically precise, and the mere presence of this immutable ledger acts as a powerful psychological deterrent against procedural manipulation [25]. It is precisely this synthesis of deep system integration, hyper-accessibility, and cryptographically enforced accountability that will engineer the transparency and trust this project seeks to achieve.


CHAPTER 3
METHODOLOGY 
3.1 Introduction 
This chapter outlines the procedures and techniques employed to gather data and construct the proposed E-Tendering System for Microprojects in Eswatini. It details the overarching research paradigm, design framework, data collection strategies, target demographics, sampling methodologies, data analysis procedures, and the ethical protocols observed during the investigation. Furthermore, the chapter delineates the software engineering principles applied to architect and implement the system. To elucidate the proposed system's architecture, Unified Modeling Language (UML) diagrams are incorporated, mapping out system requirements, operational workflows, and user interactions. 

3.2 Research Approach 
To capture a comprehensive view of the procurement landscape, this investigation utilized a hybrid methodology, integrating both qualitative and quantitative analytical strategies. The qualitative dimension facilitated a deep dive into the prevailing tendering workflows, bringing to light operational bottlenecks and capturing critical user requirements via semi-structured interviews and direct field observations. Conversely, the quantitative aspect was deployed to aggregate measurable data via structured questionnaires, enabling the statistical analysis of prevailing attitudes and the identification of macro-trends among the respondents. 
Fusing these two approaches yielded a multifaceted perspective on the existing procurement mechanisms, which proved instrumental in outlining the foundational specifications for the newly proposed electronic tendering platform. 

3.3 Research Design 
An exploratory and descriptive framework underpinned this research endeavor. The primary objective of this design was to critically evaluate the manual tendering procedures currently utilized by the Microprojects Unit, highlight the operational frictions encountered by various stakeholders, and establish the technical prerequisites for the digital tendering solution. 
Rather than validating a pre-existing theoretical model, the inquiry centered on dissecting the practical realities of the current procurement lifecycle. The insights derived from this exploration directly informed the architectural planning and functional development of the software. 

3.3.1 System Development Methodology 
The engineering of the proposed E-Tendering System was guided by Agile software development principles. Agile was deemed optimal because it facilitates the iterative construction of the platform in manageable increments, thereby streamlining the testing of individual modules and allowing for continuous refinement based on iterative stakeholder feedback. 
The developmental lifecycle encompassed the following phases: 
•	Requirements engineering 
•	Systems analysis 
•	Architectural design 
•	Software implementation 
•	Rigorous testing protocols 
•	Iterative refinement 
This incremental strategy mitigated developmental vulnerabilities and guaranteed that each functional component was fully operational and validated prior to advancing to subsequent phases. 

3.3.2 System Analysis 
The functional specifications of the envisioned system were mapped out utilizing a Use Case Diagram. 
 
Figure 1: Use Case Diagram for the Proposed E-Tendering System.
The Use Case Diagram delineates the dynamic engagements among the four primary system actors: the Contractor, Procurement Officer, Evaluator, and Administrator. It maps out the core functionalities the platform delivers and allocates specific administrative and operational duties to each role. This visual model was pivotal in crystallizing the functional parameters that steered the software's coding and integration. 

3.3.3 System Workflow 
The operational logic and sequential processes of the proposed platform were conceptualized via an Activity Diagram. 
 
Figure 2: Activity Diagram for the Proposed E-Tendering System.
The Activity Diagram charts the progression of actions spanning contractor onboarding, credential verification, tender broadcasting, bid lodgment, and bid appraisal, culminating in the tender allocation and project finalization. Furthermore, it highlights key automated triggers built into the system, such as the enforcement of tender deadlines, the cryptographic locking of submitted bids, and the automated dispatch of alerts to evaluators once the tender unsealing threshold is reached. 

3.3.4 System Interaction 
The technical dialogue between system users and the underlying infrastructure was articulated using a Sequence Diagram. 
  
Figure 3: Sequence Diagram for the Proposed E-Tendering System.
The Sequence Diagram visually traces the data trajectory across users, the application interface, and the backend database throughout the tendering lifecycle. It chronologically maps the execution of events, initiating from user inputs and concluding with the corresponding system outputs. Additionally, the diagram underscores the system's capacity to automate critical junctures, thereby enhancing procedural transparency, data integrity, and overall accountability. 

3.4 Target Population 
The focal demographic for this inquiry comprised professionals and entities intimately engaged in the procurement and bidding ecosystems. This cohort included: 
•	Procurement officers 
•	Project administrators 
•	Contracting firms and construction enterprises actively bidding on tenders 
•	System administrators tasked with the oversight of digital procurement registries 
These specific groups were targeted as they possess firsthand empirical knowledge of the manual tendering landscape, positioning them to offer critical critiques regarding its operational deficits and to propose viable technical enhancements. 

3.5 Sampling 
This investigation leveraged purposive sampling, a targeted non-probability selection technique. This specific sampling strategy was adopted because the validity of the findings relied on extracting nuanced insights from individuals possessing specialized, firsthand expertise in navigating and managing the Microprojects procurement and tendering pipelines. 

Sample Size 
The investigation engaged a consolidated sample of 15 participants, deliberately drawn from the broader target demographic. 

Sample breakdown: 
 
Category | Population Sample | Percentage 
---|---|---
Microprojects Staff | 4 | 27% 
Procurement Officers | 3 | 20% 
Contractors | 6 | 40% 
System Administrators | 2 | 13%
Total | 15 | 100% 

Table 1: Selected participant sample from the target population.
This curated sample was deemed robust as it encapsulated the core cross-section of procurement stakeholders. These individuals provided comprehensive data through both in-depth interviews and structured questionnaires related to the procurement domain. 

Sampling Frame 
The sampling matrix encompassed specialized procurement personnel, active contractors, project administrators, and internal Microprojects staff members who were routinely executing and overseeing tender management operations. 

3.6 Data Collection Instruments 
The following tools were deployed to amass data: 
Questionnaires - These instruments were circulated among contractors, procurement officials, and internal staff. They incorporated a blend of open-ended and closed-ended items engineered to extract concrete data regarding operational bottlenecks in the manual system and technical expectations for the digital platform. 
Interviews – Structured dialogues were held with designated procurement officers and Microprojects personnel to extract granular insights into institutional procurement protocols and specific software requirements. 
Observation - The investigator conducted active observations of the traditional paper-dependent procurement cycle to map out the physical workflows and pinpoint specific administrative bottlenecks primed for digitization. 
Records and Literature Review - Pertinent procurement archives, institutional reports, regulatory policies, academic journals, monographs, and prior case studies on e-procurement implementations were critically evaluated to establish a robust theoretical and contextual foundation for the project. 

3.7 Data Analysis 
This research synthesized both qualitative and quantitative analytical frameworks. 
Quantitative data harvested via the questionnaires were processed and visualized utilizing statistical percentages, structured tables, and graphical charts. 
Qualitative insights derived from the interviews and observational sessions were systematically coded and analyzed manually. This involved categorizing narrative responses into thematic clusters and isolating prevalent patterns, operational concerns, and shared stakeholder perspectives. 
The synthesis of these dual analytical streams yielded a holistic and grounded understanding of the prevailing procurement deficiencies and the precise technical requirements of the proposed system. 

3.8 Ethical Considerations 
Strict adherence to academic research ethics was maintained throughout the data collection process, specifically tailored to the sensitivities of procurement research in Eswatini. 
Informed Consent - Prior to any data collection, all selected contractors, procurement officers, and Microprojects staff were explicitly briefed on the project's digital transformation objectives. It was made clear that their participation was strictly voluntary and that they possessed the unconditional right to withdraw their insights at any stage without facing professional or institutional repercussions. 
Confidentiality - Given that procurement operations often involve sensitive financial and operational data, all shared insights were treated with strict confidentiality and restricted exclusively to academic analysis. To alleviate concerns regarding professional backlash, interviews with procurement officers and contractors were conducted in private settings, ensuring candid discussions about the flaws in the current tendering system. 
Anonymity - To safeguard the participants, particularly contractors who might fear bias in future tender awards, no identifying markers or institutional affiliations were included in the final documentation. All responses were aggregated and anonymized to ensure individual identities remained untraceable. 
Voluntary Participation - No undue pressure or institutional leverage was applied to compel participation from the Microprojects staff or external bidders; engagement was entirely self-determined. 
Data Protection - All raw survey data and interview transcripts were secured in encrypted, password-protected digital repositories accessible solely to the lead researcher, thereby neutralizing the risk of unauthorized access or data leakage. 
These tailored ethical protocols were critical in fostering a secure environment that protected the professional standing, privacy, and dignity of all contributing stakeholders.


CHAPTER 4
System Design and Implementation

4.1 Introduction
This chapter outlines the architectural design and development of the E-Tendering System. It details the overarching system framework, database schema, the four primary user roles, fundamental modules, and the implemented security and automation mechanisms. The primary objective was to engineer a practical solution for its intended end-users: contractors submitting proposals, procurement officers administrating tenders, and evaluators assessing submissions. The final graphical user interfaces are illustrated in Chapter Five.

4.2 Overall System Structure
The system functions as a web-based platform structured around four distinct and separated user designations: Contractor, Procurement Officer, Evaluator, and Administrator. This role segregation represents the most critical design decision within the architecture. Each role corresponds directly to a real-world function within the Microproject tendering workflow, ensuring that no single individual can exert disproportionate control over the process. For instance, a procurement officer is restricted from evaluating bids, and an evaluator lacks the authority to award a tender. This segregation adheres to best practices and mitigates the risk of bias and interference that the digital system aims to eliminate.
Additionally, the platform incorporates a public-facing interface. Unregistered visitors can explore published tender notices and review high-level procurement metrics. This feature promotes the transparency mandate without exposing sensitive submission or evaluation data to the general public.

4.3 System Architecture
The application employs a layered client-server architecture. Users interact with the platform via a standard web browser, while the server processes requests and delivers outcomes. The architectural layers comprise:
Presentation Layer: The responsive web interfaces that facilitate user interaction, ensuring compatibility across both desktop and mobile devices.
Application Layer: The PHP-based logic that executes business rules—such as deadline verification, score computation, role permission enforcement, and access control determination.
Data Access Layer: The intermediary responsible for database communication. Isolating this from the application logic centralizes database queries, thereby enhancing code maintainability and minimizing SQL injection vulnerabilities.
Database Layer: A MySQL database tasked with storing structured data, including user profiles, tenders, bids, evaluation scores, documentation, appeals, and audit trails.
File Storage: A dedicated server-side directory housing physical uploaded files (e.g., PDFs, images). The database retains only the file paths, maintaining optimal database performance by preventing bloat.
Authentication Layer: A session-based access control mechanism that verifies the logged-in user's role during every page request prior to rendering content.
This separation of concerns significantly streamlined the debugging process during testing, as errors in the presentation layer were isolated from the database operations.

4.4 Database Design
The database was architected in MySQL utilizing a relational model. The primary design principle was to accurately model the real-world associations between entities in the procurement lifecycle: a contractor submits multiple bids, a tender attracts numerous bids, and an evaluator assesses various submissions. The implementation of foreign keys to enforce these constraints ensures that the database inherently rejects invalid data inputs without requiring supplementary validation logic.
The database tables and their respective data entities include:
users - Login credentials, role designations, and account statuses for all participants.
contractors - Corporate identities, registration numbers, contact information, and Swazi ownership percentages.
tenders - Titles, descriptions, categories, regional locations, budget estimates, operational dates, and current statuses.
tender_documents - Reference files uploaded by procurement officers (e.g., technical specifications, architectural drawings, bills of quantities).
contractor_documents - Statutory compliance files uploaded by contractors (e.g., certificates of incorporation, tax clearances, CIC registrations, police clearances).
bids - Financial proposals, technical submissions, timestamp data, and statuses for respective contractor-tender interactions.
evaluations - Records linking specific evaluators to the bids they are mandated to review.
evaluation_scores - Individual metric scores and qualitative remarks inputted by evaluators during assessments.
site_visits - Scheduled physical site inspections associated with specific tenders, including attendance logs.
awards - Finalized records connecting a tender to the successful contractor.
appeals - Formal contractor grievances regarding award decisions, containing rationales, submission dates, and resolution statuses.
audit_logs - Timestamped chronologies of critical system events: authentication attempts, file uploads, bid submissions, tender status modifications, and award declarations.

4.5 User Roles and Access Permissions
Role-based access control (RBAC) is uniformly enforced across the application. Upon authentication, a user's role is stored within their session data. Every system page validates this role prior to initialization; unauthorized access attempts are automatically redirected. This architecture prevents users from accessing restricted interfaces via URL manipulation.

4.5.1 Contractor
Contractors utilize the platform to discover tenders, submit proposals, monitor outcomes, and lodge appeals if necessary. Their operational workflow entails: creating a company profile and uploading statutory documents → awaiting administrative verification → querying active tenders → submitting proposals with supporting documentation → receiving submission receipts → reviewing outcomes post-closure.

4.5.2 Administrator
The system administrator is responsible for user account and contractor compliance management. They evaluate documents provided during the registration phase to either authorize or decline contractor accounts. Furthermore, administrators possess the capability to suspend accounts, reset credentials, allocate roles, and scrutinize the audit log to oversee system operations.

4.5.3 Evaluator
Evaluators are designated to specific tenders by the procurement officer. Their visibility is restricted solely to the bids assigned for their review. The evaluation interface presents the pre-configured scoring matrix for the respective tender, allowing the evaluator to assign scores and qualitative feedback for each criterion. If a conflict of interest arises with a specific bidder, the evaluator can declare it and subsequently be recused from that particular assessment.

4.5.4 Procurement Officer
Procurement officers coordinate the entire tender lifecycle. Their responsibilities include drafting tenders (defining titles, descriptions, categories, budgets, deadlines, and evaluation matrices), publishing them, coordinating site visits, analyzing aggregated evaluation scores, and executing the final award decisions. Importantly, they are prohibited from participating in the scoring of bids.

4.6 Main System Modules
4.6.1 Contractor Registration and Document Management
The registration protocol requires contractors to supply their corporate name, registration identifier, contact details, Swazi ownership proportion, and statutory compliance documents. The platform supports PDF and image formats, archiving them alongside the contractor's profile. Administrators evaluate these submissions prior to account activation. Once verified, contractors are exempt from re-uploading persistent documents for subsequent tenders. Only documents with expiration dates (e.g., monthly tax clearances or quarterly police clearances) require periodic updates.

4.6.2 Tender Creation and Publication
Procurement officers utilize a structured form to generate new tenders. This interface captures the tender title, detailed description, required CIC grading, regional location, estimated budget, submission deadline, and the evaluation rubric (criteria and associated weightings). Upon saving, the tender remains in a draft status until formally published, at which point it becomes accessible to contractors and visible on the public listings interface.

4.6.3 Tender Browsing and Search
The tender directory is visible to all platform visitors. Authenticated contractors benefit from advanced filtering capabilities, enabling them to refine searches by category, region, and CIC grade. Each listing presents the tender title, closing deadline, and direct access to comprehensive specification documents. A dynamic countdown timer visually indicates the remaining duration before the submission window closes.

4.6.4 Bid Submission
The bid submission interface is exclusively available to verified contractors for active tenders. This form mandates the entry of the financial bid and technical proposal, alongside any tender-specific auxiliary documents. Prior to submission, the system validates that all mandatory fields are populated and that the deadline has not elapsed. Upon successful processing, the system issues a unique tracking identifier, serving as formal proof of submission.

4.6.5 Bid Evaluation
Evaluators are presented with a dashboard of their assigned tenders. Selecting a specific tender reveals the bids pending assessment. The scoring interface replicates the rubric established by the procurement officer, providing input fields for numerical scores and qualitative comments per criterion. These values are iteratively saved to the evaluation_scores database table. Once all designated evaluators have completed their assessments for a bid, the system computes the aggregated average score, which is utilized for contractor ranking.

4.6.6 Award and Notification
Following the completion of all evaluations, the procurement officer accesses a dynamically ranked results matrix. Upon reviewing the final scores, the officer can formally award the tender to the highest-ranking contractor. This action generates a record in the awards table, updates the tender status to "Awarded," and initiates automated email notifications to all participating contractors regarding the final decision.

4.7 Document Handling
Uploaded files are securely archived in a designated server directory situated outside the public web root, thereby preventing unauthorized direct URL access. The database retains metadata for each document, including the file name, MIME type, upload timestamp, and expiration date. This metadata enables the system to proactively identify documents nearing expiration and generate alerts on the contractor's dashboard, ensuring compliance renewals are completed before account suspension occurs.
For testing and demonstration purposes within this project, synthetic specimen documents were utilized to validate the upload, storage, and retrieval pipelines without compromising actual contractor data confidentiality.

4.8 Automatic Tender Closing
Each tender record is associated with a definitive closing timestamp. The system enforces this deadline through a dual-mechanism approach. Firstly, the client-side bid submission interface dynamically validates the server time; if the deadline has passed, the submission functionality is disabled and replaced with a closure notification. Secondly, a server-side validation routine executes whenever a tender page is accessed; if the database status remains "Open" despite the deadline having elapsed, the system autonomously updates the status to "Closed."
This automated workflow eliminates the necessity for manual administrative intervention and mitigates the risk of illicit late submissions due to human error.

4.9 Protecting Bid Integrity
To ensure the immutability of submitted bids, the platform generates a cryptographic hash of the bid's core data at the exact moment of submission. This hash encapsulates the financial bid, the technical proposal text, and the filenames of all attached documents, and is stored securely in the bids table.
Any subsequent unauthorized modification to the bid data—whether via direct database manipulation or file substitution—would result in a mismatched hash value upon recalculation. This cryptographic verification serves as a tamper-evident control, providing definitive proof of data integrity during audits or formal disputes.

4.10 Audit Trail
The audit_logs table maintains a comprehensive registry of pivotal system interactions. Each log entry captures the executing user's ID, the specific action performed (e.g., authentication, file upload, bid submission, tender publication, award execution), the targeted record ID, and a precise timestamp. Crucially, this immutable log is inaccessible for modification via the standard user interface.
This audit mechanism serves dual functions: it enables administrators to proactively monitor system operations for anomalous activity, and it furnishes an irrefutable, chronologically ordered account of events for resolving disputes or investigating complaints.

4.11 Security Measures
The application implements a robust suite of security protocols:
Password Storage: User credentials are cryptographically hashed prior to database insertion, ensuring plain-text passwords are never retained.
Role-Based Access Control: Session-based role validation is strictly enforced across all interfaces, precluding unauthorized access attempts, including URL manipulation.
Input Validation: Comprehensive server-side validation and the mandatory use of prepared SQL statements neutralize SQL injection vulnerabilities.
File Upload Restrictions: Upload capabilities are strictly limited to PDF and image formats, with server-side verification of MIME types overriding user-supplied file extensions.
Session Timeout: Automated session expiration mitigates the risk of unauthorized access via unattended workstations.
Bid Integrity Hash: As detailed in Section 4.9, cryptographic hashing provides tamper-evident security for all submitted proposals.
Audit Logging: As detailed in Section 4.10, immutable event logging facilitates transparent security investigations.
These comprehensive measures effectively mitigate the most prevalent vulnerabilities associated with sensitive web-based procurement platforms.

4.12 Automation Features
The following operational workflows execute autonomously:
Tender Closing: Tenders are automatically transitioned to a closed status at the precise deadline.
Bid Lockout: Submission capabilities are instantaneously revoked upon deadline expiration.
Score Aggregation: The system autonomously calculates average scores and ranks contractors upon the completion of all evaluator assessments.
Document Expiry Tracking: Impending statutory document expirations trigger automated alerts on contractor dashboards.
Bid Hash Generation: Cryptographic hashes are algorithmically generated and recorded concurrently with bid submissions.
Email Notifications: The PHPMailer module dispatches automated communication for submission receipts and final award declarations.

4.13 Technologies Used
The ensuing table details the technological stack deployed for this system and the rationale for each selection.

Technology | Purpose in This System
PHP (no framework) | Executes all server-side logic, including session management, database interactions, and access controls. Vanilla PHP was selected to maintain a lightweight codebase and prevent framework dependency for a localized deployment.
MySQL | Serves as the relational database engine for users, tenders, bids, documents, and logs. Selected for its robust foreign-key constraint capabilities and efficient handling of complex JOIN operations across interrelated tables.
Bootstrap 5 | Provides front-end structural layout and responsive design elements, ensuring cross-device compatibility without extensive custom CSS development.
JavaScript (vanilla) | Handles client-side form validation, dynamic interface updates, and countdown timers. Implemented without external libraries (e.g., jQuery) to minimize architectural dependencies.
HTML5 | Dictates the semantic structure and content organization across all web interfaces.
Laravel Herd | Functioned as the local development environment, providing an optimized PHP/MySQL server architecture without the overhead of traditional WAMP/XAMPP configurations. Utilized exclusively during the development phase.
PHPMailer | Manages automated email dispatch for submission confirmations and award notifications, chosen for its robust support of SMTP authentication protocols required by contemporary mail infrastructure.
Table 2: Technology stack and justification for each component

4.14 Chapter Summary
This chapter delineated the architectural and functional development of the e-tendering platform. The implementation of a layered architecture ensures modularity and separation of concerns. The relational database schema accurately mirrors the intricate associations inherent in the procurement lifecycle. The establishment of four distinct user roles aligns with real-world administrative boundaries, safeguarding against monopolized control. Furthermore, critical automated mechanisms—including deadline enforcement, cryptographic bid hashing, comprehensive audit logging, and stringent access controls—directly resolve the transparency and integrity vulnerabilities identified in preceding chapters.
Chapter Five will visually present the finalized system via interface screenshots and document the outcomes of the rigorous testing protocols.


CHAPTER 5
System Testing, Results, and Demonstration

5.1 Introduction
This chapter showcases the graphical user interface of the finalized application and documents the comprehensive testing protocols executed to verify its operational integrity. Screenshots are incorporated to illustrate the primary interfaces corresponding to each distinct user role. The testing analysis details the systematic validation of core functionalities, automated workflows, and embedded security frameworks. The overarching objective of this testing phase was to ensure the platform's reliability and its successful fulfillment of the criteria established in prior chapters.

5.2 System Interface Demonstration
The subsequent visual exhibits represent the core interfaces of the application. Each layout was meticulously engineered to cater to the specific operational requirements of its target user demographic.

5.2.1 The Landing Page
Figure 4: System Landing Page
The landing page operates as the primary public gateway to the platform. It offers immediate access to active tenders, aggregated procurement statistics, the Procurement Guidance Centre, and authentication portals. The interface is optimized for intuitive navigation and accessibility.

5.2.2 Available Tenders
Figure 5: Available Tenders Page
This module presents a comprehensive directory of published tenders. Contractors are equipped with dynamic search and filtering tools—encompassing category, region, and CIC grade parameters. This utility significantly expedites the identification of pertinent opportunities compared to manual browsing methods.

5.2.3 Public Tender Statistics
Figure 6: Public Tender Statistics
The statistics dashboard provides a transparent overview of the platform's procurement dynamics. It highlights key performance indicators, including the volume of active tenders, the registered contractor base, and the total awarded contracts. This public-facing data fosters institutional transparency by democratizing access to procurement metrics.

5.2.4 Procurement Guidance Centre
Figure 7: Procurement Guidance Centre
Designed as an educational repository, the Guidance Centre hosts instructional resources for contractors, featuring video tutorials and comprehensive PDF manuals. These assets demystify the digital tendering workflow, assisting novel users in navigating registration, document compliance, and bid submission procedures.

5.2.5 Contractor Registration
Figure 8: Contractor Registration Page
This interface facilitates the onboarding of new contractors, requiring the submission of essential corporate data. During this phase, contractors must upload mandatory statutory documentation. The system securely archives these files within the contractor's digital profile for subsequent administrative auditing and future bid utilization.

5.2.6 Contractor Dashboard
Figure 9: Contractor Dashboard
Functioning as the operational nucleus for contractors, this dashboard consolidates all accessible platform tools. From this centralized hub, contractors can update corporate profiles, monitor active tenders, manage statutory documents, execute bid submissions, and track their historical tendering performance.

5.2.7 Tender Details and Bid Submission
Figure 10: Tender Details and Bid Submission Page
This detailed view outlines the comprehensive specifications of a selected tender. Contractors are permitted to review overarching project mandates and upload requisite technical files prior to formalizing their bid. Robust validation logic ensures all mandatory parameters and documents are fulfilled before processing the submission.

5.2.8 Bid Submission Confirmation
Figure 11: Bid Submission Confirmation
Following a validated bid submission, the platform generates a formal confirmation interface. This receipt includes a unique tracking reference and a cryptographic hash value. This hash functions as an immutable digital signature for the submitted data, providing an auditable mechanism to verify that the proposal remains unaltered post-submission.

5.2.9 Administrator Dashboard
Figure 12: Administrator Dashboard
The administrator console provides a suite of administrative utilities for platform governance. Key functionalities encompass the verification of contractor profiles, overarching user management, compliance document auditing, and general system health monitoring. The immutable audit log is exclusively accessible from this secure interface.

5.2.10 Procurement Officer Dashboard
Figure 13: Procurement Officer Dashboard
This dashboard is dedicated to the administration of the tender lifecycle. Procurement officers utilize this interface to author and broadcast new tenders, monitor ongoing submission metrics, analyze aggregated evaluator feedback, and officially finalize tender awards.

5.2.11 Evaluator Dashboard
Figure 14: Evaluator Dashboard
The evaluator interface isolates and displays only the specific tenders assigned to the authenticated user. Evaluators can scrutinize submitted proposals, assign numerical scores via the pre-defined rubric, and append qualitative justifications for their assessments.

5.3 System Testing
Rigorous testing methodologies were applied to isolate potential defects and guarantee that the software operates in alignment with the specified requirements. This validation effort prioritized the most critical transactional processes within the tendering lifecycle.

5.3.1 Functional Testing
The subsequent matrix details the functional test cases executed and their corresponding outcomes.

Test | Expected Result | Actual Result | Status
Contractor Registration | Contractor account is successfully provisioned | Account created successfully | Pass
Contractor Login | User is authenticated and routed to the correct dashboard | Correct dashboard displayed | Pass
Tender Creation | Tender data is committed to the database and publicly displayed | Tender displayed successfully | Pass
Tender Search | Queries return accurate, filtered tender listings | Matching results displayed | Pass
Document Upload | System accepts and archives valid file formats | Document uploaded successfully | Pass
Bid Submission | Bid parameters are securely recorded in the database | Bid recorded successfully | Pass
Late Bid Submission | System rejects submissions past the defined deadline | Submission actively prevented | Pass
Bid Evaluation | Evaluators can successfully commit rubric scores | Scores recorded successfully | Pass
Tender Award | Final award decision is registered in the system | Award recorded successfully | Pass
Public Statistics | Aggregated procurement data is accurately rendered | Statistics displayed correctly | Pass
Table 3: Functional testing results

5.4 Tender Closing Automation Test
The efficacy of the automated deadline enforcement mechanism was rigorously evaluated. A test tender was configured with a specific expiration timestamp. The system successfully permitted bid submissions from eligible contractors prior to this deadline. Upon the exact expiration time, the system autonomously transitioned the tender's status to "Closed" and actively blocked any further submission attempts.
This validation was critical, as the inadvertent acceptance of late submissions would fundamentally undermine the equity of the procurement framework. The module successfully passed all test parameters.

5.5 Bid Integrity Test
The cryptographic hashing protocol was audited to ensure data immutability. A controlled bid was submitted, prompting the system to generate and store a corresponding hash value.
A subsequent verification routine recalculated the hash against the stored data. Furthermore, manual modifications were injected into the test bid data, which immediately resulted in a hash mismatch. This outcome verified the algorithm's capability to detect unauthorized alterations. The system passed this integrity validation.

5.6 Document Upload Test
The document management architecture was tested utilizing a suite of synthetic compliance files, specifically encompassing:
- CIC Registration Certificate
- Tax Clearance Certificate
- Certificate of Incorporation
- ENPF Compliance Document
- Police Clearance
- Tender Security
The platform successfully processed, validated, and accurately linked each file to the designated contractor profile, confirming the operational reliability of the document handling module.

5.7 Role-Based Access Test
The RBAC framework was subjected to penetration testing by systematically authenticating as distinct user roles and attempting to access restricted URIs. The findings conclusively demonstrated that:
- Contractors were barred from accessing evaluation modules.
- Evaluators were unable to access tender creation or publication interfaces.
- Procurement officers were restricted from modifying user account data.
- Administrators were actively prevented from participating in the bid submission process.
These outcomes validate the structural integrity of the application's access control mechanisms.

5.8 Usability Testing
Fundamental usability heuristics were evaluated by simulating typical user workflows across all role designations. The assessment prioritized:
- Intuitive navigation pathways between modules
- The clarity and semantic accuracy of interface elements
- The responsiveness of tender search and filtering utilities
- The friction associated with document upload protocols
- The overall logical flow of the bid submission pipeline
- The accessibility of instructional materials
The heuristic evaluation confirmed that the user interfaces are highly intuitive, facilitating the efficient completion of primary tasks. Minor friction points identified during this phase were systematically remediated.

5.9 Summary of Test Results
The comprehensive testing phase validated the operational stability of the system's primary components. Contractors demonstrated the ability to register, manage documentation, browse opportunities, and submit proposals seamlessly. Evaluators successfully analyzed and scored submissions. Procurement officers effectively administered the tender lifecycle and award processes, while administrators maintained robust oversight capabilities.
Crucially, all automated routines—including deadline enforcement and cryptographic integrity checks—functioned with precision. The RBAC architecture effectively neutralized unauthorized access vectors. Furthermore, usability evaluations affirmed the platform's accessible and user-centric design.

5.10 Chapter Summary
This chapter visually documented the implemented architecture and detailed the rigorous testing protocols applied to its core functionalities. The interface demonstrations highlighted the specialized environments engineered for each user persona. The testing data verified that the application operates reliably, securely, and in full compliance with its foundational objectives. These results substantiate the platform's viability as a functional, secure prototype for modernizing electronic procurement workflows.


CHAPTER 6
Conclusion and Recommendations

6.1 Introduction
This concluding chapter synthesizes the project's outcomes, analyzing how the deployed architecture resolves the initial problem statements, and formulates strategic recommendations for subsequent iterations. Furthermore, the inherent limitations of the current prototype are transparently acknowledged.

6.2 Project Summary
This research endeavor concentrated on the engineering of an E-Tendering System tailored for Microprojects Eswatini. The primary objective was to modernize and optimize the methodologies surrounding tender publication, accessibility, and lifecycle management.
The resulting application delivers a centralized digital ecosystem enabling contractors to register, upload statutory documentation, discover opportunities, and execute electronic bids. Simultaneously, it provisions specialized administrative, evaluative, and procurement consoles to streamline backend operations.
Critical innovations include automated deadline enforcement, secure electronic bid transmission, cryptographic data integrity validation, immutable audit logging, and the public democratization of procurement statistics. Collectively, these features engineer a procurement workflow characterized by heightened efficiency, security, and institutional transparency.

6.3 Achievement of Objectives
The foundational project objectives served as the blueprint for development. The subsequent table delineates the fulfillment of each defined goal.

Objective | How It Was Achieved
To develop an electronic platform for tender publication and bidding | A comprehensive web application was deployed, enabling digital tender broadcasting by procurement officers and online bid submissions by contractors.
To reduce the use of paper-based tender processes | The platform mandates electronic document uploads for both statutory compliance and technical proposals, drastically mitigating physical paper reliance.
To improve transparency in the tendering process | The integration of public statistics dashboards and immutable audit trails provides unprecedented visibility into procurement operations.
To improve the security and integrity of tender submissions | Stringent role-based access controls coupled with cryptographic bid hashing safeguard data against unauthorized access and tampering.
To improve access to tender opportunities | A centralized, easily searchable online directory eliminates geographical and logistical barriers to accessing tender information.
To provide guidance to contractors | The embedded Procurement Guidance Centre supplies multimedia educational resources to assist users in navigating the digital workflow.
Table 4: Achievement of project objectives

The empirical outcomes confirm the successful realization of all primary objectives.

6.4 Contributions of the System
The deployed application introduces several paradigm-shifting benefits:
For Contractors: The platform democratizes access to procurement opportunities while significantly reducing the logistical friction associated with physical submissions. Contractors can now participate in the tendering process remotely and securely.
For Microprojects: The architecture provides a unified, highly organized data repository for tender data, contractor profiles, evaluation metrics, and final awards, thereby optimizing administrative efficiency and data retrieval.
For the Public: The exposition of high-level procurement metrics and active tender data fosters an environment of public accountability and transparency previously unattainable via manual processes.
In summation, the system successfully transitions critical procurement phases from analog to digital paradigms, yielding a more accessible, structured, and transparent ecosystem.

6.5 Limitations of the System
While the prototype successfully executes its core mandates, the following constraints must be acknowledged:
1. Academic Scope: The application was engineered as a proof-of-concept within an academic framework and does not yet reflect the requisite infrastructure of a fully deployed national procurement grid.
2. Test Data Constraints: To uphold data privacy and security standards, all testing was conducted utilizing synthetic documentation rather than authentic governmental or financial records.
3. Lack of External Integration: The current iteration operates independently and lacks API integrations with external state or financial databases. Consequently, compliance verification relies on internal logic rather than external authoritative sources.
4. Infrastructure and Security Maturity: Transitioning this prototype into a production environment would necessitate advanced penetration testing and the deployment of enterprise-grade server infrastructure.

6.6 Recommendations
The following strategic enhancements are proposed for subsequent developmental phases:

6.6.1 Integration with External Systems
The architecture should prioritize API integrations with pertinent governmental and financial registries. This would facilitate the automated, real-time verification of statutory documents (e.g., tax clearances) and streamline financial transactions, thereby enhancing efficiency and mitigating fraudulent submissions.

6.6.2 Mobile Application Development
The deployment of a dedicated mobile application would significantly augment contractor accessibility, allowing for seamless tender browsing, document management, and push-notification alerts directly via mobile devices.

6.6.3 Enhanced Document Verification
Future iterations should incorporate advanced Optical Character Recognition (OCR) and machine learning algorithms to autonomously parse and validate uploaded compliance documents, identifying potential alterations or forgeries.

6.6.4 Notification Services
The integration of a robust SMS and email notification gateway is recommended to proactively alert contractors regarding newly published tenders, deadline modifications, evaluation outcomes, and final award determinations.

6.6.5 Local Language Support
To maximize inclusivity, the user interface should be internationalized to support siSwati alongside English, reducing language barriers for local enterprise participants.

6.6.6 Mobile Optimisation
While the current platform utilizes responsive design, further optimization is necessary to ensure performant rendering and functionality across devices with constrained processing power or limited bandwidth capabilities.

6.7 Future Development
The foundational architecture presents substantial opportunities for horizontal and vertical scaling. Future iterations could encompass sophisticated automated document auditing, seamless e-payment gateways, predictive procurement analytics, and granular communication modules.
Furthermore, the platform's modular design renders it highly adaptable; with the establishment of requisite inter-agency agreements, the system could scale to service a broader spectrum of governmental procurement entities.

6.8 Final Conclusion
This research project culminated in the successful engineering of a functional E-Tendering System prototype for Microprojects Eswatini. The application delivers a cohesive digital environment managing the entire procurement lifecycle—from tender publication and contractor onboarding to bid submission, evaluation, and awarding.
The architecture actively remediates systemic inefficiencies inherent in traditional manual tendering, notably the reliance on physical documentation, geographical access barriers, and opaque data management.
While recognizing the necessity for infrastructure scaling and external integrations prior to enterprise deployment, this prototype unequivocally proves that the digital transformation of local procurement workflows is both highly feasible and exceptionally advantageous. The project stands as a testament to the transformative potential of e-procurement solutions.




## References

[1]	Thwala, W. D. and Mvubu, M., "Problems facing small and medium size contractors in Swaziland," African Journal of Business Management, vol. 2, pp. 093-098, 2009.   
[2]	L. Hlophe, "An analysis of impediments to procurement in selected public procurement entities in the Kingdom of Eswatini," Journal of Business and Management (IORS-JBM), vol. volume 22, pp. PP 47-59, May,2020.   
[3]	International Monetary Fund, "Government Procurement in Developing Economies," Fiscal Affairs Department, IMF, Washington, 2021.  
[4]	Transparency International, "Topic Guide on Public Procurement," Knowledge Hub, Transparency International, Berlin, 2023.  
[5]	African Development Bank, "Public Procurement Technical Assistance Project, Eswatini (PSZ-KZ0-001)," AfDB, Abidjan, 2022.  
[6]	Eswatini Public Procurement Regulatory Agency, "About ESPPRA: Mandate and Establishment under the Public Procurement Act No. 7 of 2011," ESPPRA, Mbabane, 2013.  
[7]	World Bank, "Enhancing Transparency: The Impact of Blockchain-Based Audit Trails on Public Financial Management," Governance for Development, World Bank Blogs, Washington, 2025.   
[8]	OECD, "The Korean Public Procurement Service," OECD Public Governance Reviews, Paris, 2016.  
[9]	World Bank, "Improving Public Procurement Outcomes: Review of Tools and the State of the Evidence Base," Policy Research Working Paper 9690, Washington, 2021.  
[10]	Public Procurement Service of Korea, "Achievements: KONEPS Performance and International Recognition," Government of the Republic of Korea, Seoul, 2023.  
[11]	Red Interamericana de Compras Gubernamentales, "Launch of the Observatory on ChileCompra: Small Business Participation Results," RICG, Santiago, 2015.  
[12]	F. Castro and C. Lopes, "The Unaccounted Effects of Digital Transformation: Implications for Accounting, Auditing and Accountability Research," Accounting, Auditing & Accountability Journal, vol. 38, no. 3, pp. 765-790, 2025.   
[13]	World Bank, "How Rwanda Became the First African Country with an Electronic Procurement System," World Bank Feature Story, Washington, 2018.  
[14]	National Treasury of Kenya, "eGP Kenya: Integrated Financial Management Information System," Government of Kenya, Nairobi, 2019.  
[15]	S. K. Mutangili, "Digital Transformation of Kenya's Procurement Systems: Evaluating the Implementation and Effectiveness of the E-Procurement Platform IFMIS," Journal of Procurement & Supply Chain, vol. 9, no. 1, 2025.   
[16]	World Bank, "Firm-Level Surveys Can Inform Reform Efforts to Promote More SME Participation in Public Procurement," World Bank Blogs, Washington, 2022.  
[17]	"Mobile Internet Usage Patterns," GSMA Africa, London, 2023.  
[18]	World Bank, "Using Technology to Improve Public Procurement: Evidence from EProcurement Reforms," Washington, 2020.  
[19]	Kingdom of Eswatini, "Public Procurement Act No. 7 of 2011," Government of Eswatini, Mbabane, 2011.  
[20]	MAPS Initiative, "Eswatini Country Procurement Assessment," Methodology for Assessing Procurement Systems, African Development Bank, 2024.  
[21]	G. M. Mwaniki, "The role of integrated financial management information system (IFMIS) in promoting transparency in public procurement in Kenya," Int. J. Supply Chain Logist., vol. 4, no. 1, pp. 16-26, 2020.   
[22]	V. Venkatesh, M. G. Morris, G. B. Davis, and F. D. Davis, "User acceptance of information technology: Toward a unified view," MIS Q., vol. 27, no. 3, pp. 425-478, Sep. 2003.   
[23]	D. Coviello and M. Mariniello, "Publicity requirements in public procurement: Evidence from a regression discontinuity design," J. Public Econ., vol. 109, pp. 76-100, 2014.   
[24]	OECD, Reforming Public Procurement: Progress and Challenges. Paris: OECD Publishing, 2020.   
[25]	African Development Bank, Country Strategy Paper for the Kingdom of Eswatini 2020-2024. Abidjan: African Development Bank, 2021.   
