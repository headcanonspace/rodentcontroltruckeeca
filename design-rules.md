This file contains the strict guidelines, coding practices, and content standards for developing, designing, and maintaining the website. All current and future changes must comply fully with these rules.

---

## 📋 Standard Core Rules

### Rule 1: MANDATORY PREREQUISITE — Read and Comply with All Rules First
* **Strict Guideline**: BEFORE proposing or executing ANY change, modification, or edit to this codebase—no matter how minor or trivial (including but not limited to styling, copywriting, meta tags, and internal links)—you **MUST** read this `design-rules.md` file in its entirety.
* **Strict Enforcement**: All current and future developers (including AI coding agents) are strictly bound to this requirement. Every modification must align 100% with the guidelines defined herein. Ignoring or bypassing any rule in this document is absolutely prohibited and constitutes an immediate failure of the task.

### Rule 2: Never Use Em Dashes (`—`)
* **Strict Guideline**: Under no circumstances should an em dash (`—`) be used anywhere in the content, HTML source, copywriting, or tags. The use of em dashes is STRICTLY PROHIBITED site-wide.
* **Required Action**: You MUST use standard hyphens (`-`), colons (`:`), commas (`,`), or rewrite the sentence to maintain clean flow.

### Rule 3: Content Preservation During Redesigns
* **Strict Guideline**: You MUST NOT change, rewrite, modify, or add to the core copywriting, headings, or content under any circumstances unless explicitly commanded by the user. Modifying page copy during design adjustments is STRICTLY FORBIDDEN.
* **Required Action**: All layouts, styles, and components MUST wrap the existing content exactly as it is written in the raw file without altering a single word.

### Rule 4: Standardized FAQ Headings & Alignment
* **Layout Rule**: 
  * The FAQ section heading MUST be perfectly centered on the page.
  * It is MANDATORY that the heading is accompanied by its standard short introductory paragraph, which MUST also be centered directly below the heading. Left-aligning FAQ headings or intros is STRICTLY PROHIBITED.

### Rule 5: Never Use Gradient Colors Site-wide
* **Strict Guideline**: Under no circumstances should gradient colors (`linear-gradient`, `radial-gradient`, Tailwind `bg-gradient-to-...`) be used anywhere on the website. The use of gradients is ABSOLUTELY PROHIBITED site-wide.
* **Required Action**: All backgrounds, cards, buttons, borders, and visual callouts MUST use curated, solid colors only.

### Rule 6: FAQ Sections Must Always Be Interactive Accordions
* **Strict Guideline**: The FAQ section on every page MUST always be styled and developed as an interactive Accordion (click a question to expand or collapse its answer). Static lists of FAQs are STRICTLY PROHIBITED.
* **Layout Rule**: Question cards MUST toggle open and closed cleanly with smooth transitions. The answers MUST remain hidden by default until a user clicks on the corresponding question.
* **Site-wide Synchronization**: Any visual, styling, or behavioral changes to the FAQ section MUST be made globally inside the shared `FaqAccordion` component. Making page-specific FAQ overrides is STRICTLY FORBIDDEN.

### Rule 7: Prohibit Pill Badges, Tags, and Status Indicator Chips
* **Strict Guideline**: You MUST NOT add, use, or implement Pill Badges, Tags, or status indicator Chips styled in a capsule pill format anywhere on the website. The use of capsule-styled text wrappers is STRICTLY PROHIBITED.
* **Required Action**: Sub-headings MUST use standard, elegant typography without any wrapping borders, frames, or pulsing dots.

### Rule 8: Never Use Emojis in Headings
* **Strict Guideline**: Emojis (e.g. `❓`, `👤`, `✍️`, etc.) MUST NEVER be used in page titles (H1), section subheadings (H2, H3), card headings, or accordion button headers. Headings MUST remain clean, professional, and strictly text-only.

### Rule 9: Prioritize Server-Side Rendering (SSR) Structure
* **Strict Guideline**: You MUST write and design the main pages (`page.tsx`) as Server Components. Placing the `"use client"` directive at the top level of any page router entry is STRICTLY PROHIBITED.
* **Layout Rule**: All main metadata declarations, JSON-LD Schema structures, static heading assets, and prose blocks MUST remain server-side to maximize SEO crawling. You MUST isolate client-side interactions, states, search bars, inputs, or collapsible accordions into dedicated leaf components (e.g. `FaqAccordion.tsx`) with the `"use client"` directive, and import them inside the parent Server page.


## 📋 Targeted Scope Rules

### Rule 11: Specific and Minimal Code Edits Only
* **Strict Guideline**: Whichever section of whichever page or component is requested to be edited, you MUST ONLY touch and modify the code corresponding to that specific requested section. Modifying surrounding sections or unrelated files is STRICTLY FORBIDDEN.

### Rule 12: Always Use SVGs Instead of Emojis
* **Strict Guideline**: Emojis (e.g. 🚀, ✨, 🔥) MUST NEVER be used in the UI, components, or content layout. The use of text-based emojis is STRICTLY PROHIBITED.
* **Required Action**: You MUST always use proper SVG icons (such as those from the Lucide library) to ensure crisp rendering and color-matching with the established theme variables.

### Rule 13: Strict Separation of Content and Code/Design
* **Strict Guideline**: When adding or modifying content, you MUST NEVER alter the underlying design, styling, layout, or component structure. Altering CSS classes or DOM hierarchy during content edits is STRICTLY PROHIBITED.
* **Required Action**: You MUST preserve existing `className` styles, component logic, and structures exactly as they are. Only inject the new text/content where required.

### Rule 14: Never Use Contact Forms
* **Strict Guideline**: Under no circumstances should HTML `<form>` elements, lead-generation forms, email inputs, or contact forms be used anywhere on the entire website. The use of lead-capture forms is STRICTLY PROHIBITED.
* **Required Action**: All call-to-actions MUST direct users to call the phone number directly. You MUST NOT build or display UI for submitting text or email inputs.

### Rule 15: Global and Identical Footer
* **Strict Guideline**: The website footer MUST be completely identical and global across every single HTML page on the site. Overriding the footer on a per-page basis is STRICTLY PROHIBITED.
* **Required Action**: You MUST NOT use page-specific active styles for links inside the footer. All footer links MUST use global, absolute paths (e.g. `/about` or `index.html#faqs`) so the footer functions flawlessly on every page.

### Rule 16: Global and Identical Header
* **Strict Guideline**: The website top navbar and main header MUST be completely identical and global across every single HTML page on the site. Overriding the header on a per-page basis is STRICTLY PROHIBITED.
* **Required Action**: You MUST NOT use page-specific active styles for links inside the header navigation.

### Rule 18: Always Use Clean Slugs/URLs
* **Strict Guideline**: All internal links across the website MUST use clean, extensionless slugs (e.g. `/about`, `/residential-control`) instead of direct file paths (like `/pages/about.html` or `/about.html`). Exposing `.html` extensions or structural directories (like `pages/`) in the `href` attributes is STRICTLY PROHIBITED.
* **Required Action**: You MUST rely on the `_redirects` configuration to map clean root-level slugs to their actual file locations in the repository.

### Rule 19: Semantic Text Case and Ctrl+F Findability
* **Strict Guideline**: Text within headings (H1, H2, etc.) or any body content MUST always be written in standard Title Case or Sentence Case in the raw HTML. Hardcoding ALL CAPS text in the HTML source is STRICTLY PROHIBITED.
* **Required Action**: If a design requires capitalized headings, you MUST use the CSS class `uppercase` (Tailwind) to achieve the visual effect.
* **Avoid Line Breaks in Keywords**: You MUST NOT insert `<br>` or `<br/>` tags in the middle of important SEO keyword phrases (e.g. inside an H1). You MUST use CSS properties like `max-width`, `padding`, or `word-break` to control line wrapping.

### Rule 20: Compact Section Padding for Single-Viewport Viewing
* **Strict Guideline**: Every content section on the website MUST be designed to fit within a single viewport height whenever possible. Over-padding sections is STRICTLY PROHIBITED.
* **Required Spacing Rules**:
  * **Hero Sections**: Use `py-12 sm:py-16 lg:py-20` (use of `py-24` or larger is STRICTLY PROHIBITED).
  * **Regular Content Sections**: Use `py-10 sm:py-14` (use of `py-20` or larger is STRICTLY PROHIBITED).
  * **CTA Sections**: Use `py-8 sm:py-10` (use of `py-12` or larger is STRICTLY PROHIBITED).
  * **Section Heading Margins**: Use `mb-10` (use of `mb-16` or larger is STRICTLY PROHIBITED).

### Rule 21: Standard Homepage Section Hierarchy (Generalized for All Local Services)
* **Strict Guideline**: Every local service homepage MUST strictly follow the consistent section hierarchy to maximize conversions and user flow. Re-ordering or omitting sections without explicit request is STRICTLY FORBIDDEN.
* **Required Hierarchy**: The strict ordered hierarchy MUST be:
  1. Header Info (Top Bar)
  2. Header (Navbar)
  3. Hero Section
  4. USP Banner (Trust Bar)
  5. About Us / Local Expertise
  6. Services Overview
  7. Why Choose Us
  8. Service Areas / Target Locations
  9. Fun Facts / Industry Stats
  10. FAQ (Accordion)
  11. Final CTA / Helpline
  12. Footer
* **Content Constraint**: The "Fun Facts" or "Stats" section MUST only contain factual, verifiable industry facts and MUST NEVER contain unverified or false business claims.


### Rule 23: Prohibition of Testimonials and Reviews Sections
* **Strict Guideline**: Under no circumstances should customer testimonials, review sections, or star ratings be used anywhere on the entire website. The inclusion of review sections is STRICTLY PROHIBITED.
* **Required Action**: You MUST NOT add, design, or implement any sections displaying user feedback, testimonials, or perceived customer reviews. All trust signals must be conveyed through factual business credentials, service guarantees, or professional copy rather than user reviews.

### Rule 24: Prohibition of Directory and Contractor Referral Branding
* **Strict Guideline**: Under no circumstances should the website copy contain referral or directory phrases that imply the site is a connecting resource or directory matching users with external contractors. Referral-based copywriting is STRICTLY PROHIBITED.
* **Prohibited Phrases**: You MUST NEVER use phrases like "Connecting you with certified local contractors", "connecting property owners with specialists", "local resource connecting", "lead-generation local resource", or other referral-based terminology in the footers, body copy, or headings.
* **Required Layout**: All website copy and structure MUST present the website as a direct local business service provider (e.g. "Providing professional spider control and pest management services...").

### Rule 25: Strict Keyword Placement Rules
* **Strict Guideline**: The following keyword placement rules MUST be followed. However, you MUST only implement and apply these rules when explicitly commanded by the user to change or modify the website content:
  - **Primary Keyword**: You MUST place the primary keyword ONLY in the meta title, the main H1 heading, and within the first 100 words of the body content. Using the primary keyword outside of these designated areas is STRICTLY PROHIBITED.
  - **Secondary Keyword**: You MUST use the secondary keyword exactly one time (1 time) across the meta description and/or the body content. Duplicate or multiple uses of the secondary keyword are STRICTLY PROHIBITED.
  - **Semantic Entities and Related Keywords**: You MUST incorporate only the specific semantic entities and related keywords explicitly provided by the user.

### Rule 26: Prohibition of Tailwind CSS CDN
* **Strict Guideline**: You MUST NOT load Tailwind CSS via CDN (`<script src="https://cdn.tailwindcss.com"></script>`) in any HTML files. The use of the Tailwind CDN is STRICTLY PROHIBITED.
* **Required Action**: All HTML files MUST link to the locally compiled stylesheet (`<link rel="stylesheet" href="assets/css/tailwind.css">` or relative equivalent). When making style or theme changes, you MUST use the local Node.js build process and compile changes by running `npm run build`.
