
# Apex Tools - Build Document

Welcome to the Apex Tools Build doc.

## List of relevant links

### GEARWRENCH North America
Prod: https://www.gearwrench.com/
Stage: https://stg-www.gearwrench.com/
Dev: http://dev-www.gearwrench.com/

### Crescent Tools North America
Prod: https://www.crescenttool.com/
Stage: https://stg-www.crescenttool.com/
Dev: http://dev-www.crescenttool.com/


### GEARWRENCH Australia
Prod: https://prod-www.gearwrench.com.au/
Stage: https://stg-www.gearwrench.com.au/
Dev: https://dev-www.gearwrench.com.au/

### Crescent Tools Australia
Prod: https://prod-www.crescenttool.com.au/
Stage: https://stg-www.crescenttool.com.au/
Dev: http://dev-www.crescenttool.com.au/


### SATA Brazil
Final Prod: http://sataferramentas.com.br (old site until launch)
Prod: https://prod-www.sataferramentas.com.br (8/8: not setup)
Stage: https://stg-www.sataferramentas.com.br (8/8: setup, no nav)
Dev: https://dev-www.sataferramentas.com.br  (8/8: setup, no nav)
Local (developers only): https://www.sataferramentas.com.br.docksal

### SATA Colombia
Final Prod: http://sata.com.co (old site until launch)
Prod: https://prod-www.sata.com.co (8/8: not setup)
Stage: https://stg-www.sata.com.co  (8/8: setup, no nav)
Dev: https://dev-www.sata.com.co  (8/8: setup, no nav)
Local (developers only): https://www.sata.com.co.docksal

### SATA EMEA
Final Prod: http://satatools.eu (old site until launch)
Prod: https://prod-www.satatools.eu (8/8: not setup)
Stage: https://stg-www.satatools.eu  (8/8: setup, no nav, no https)
Dev: https://dev-www.satatools.eu  (8/8: setup, no nav, no https)
Local (developers only): https://www.satatools.eu.docksal

### SATA US (North America)
Final Prod: http://satatools.us (old site until launch)
Prod: https://prod-www.satatools.us (8/8 not setup, no https)
Stage: https://stg-www.satatools.us  (8/8: setup, has nav!!, no https)
Dev: https://dev-www.satatools.us (8/8: setup, has nav!!, no https)
Local (developers only): https://www.satatools.us.docksal


## Table of Contents

- [Content Types](#content-types)
- [View Modes](#view-modes)
- [Taxonomies](#taxonomies)
- [Paragraph Components](#paragraph-components)
- [Views](#views)
- [Menus](#menus)
- [Blocks](#blocks)
- [Media Types](#media-types)
- [Image Styles](#image-styles)
- [Breakpoints](#breakpoints)
- [Regions](#regions)
- [Roles and Permissions](#roles-and-permissions)

---

## Content Types

### Article / Story
Article is used to create pages about a certain topic.
Articles will be tagged with an article category (taxonomy).

| Field                                              | Machine Name                 | Type                        | Required | Notes                                                                                      |
|----------------------------------------------------|------------------------------|-----------------------------|----------|--------------------------------------------------------------------------------------------|
| Title                                              | title                        | Text                        | Yes      | Not displayed if hero in block title                                                       |
| Short title                                        | field_title_short            | Text                        | No       | Used in place of main title on non-full-content view-modes for content with longer titles. |
| Lead-in/Summary Text                               | body                         | Text long, with summary     | No       |                                                                                            |
| Hero Image                                         | field_featured_media         | Entity reference            | No       |                                                                                            |
| Listing Image                                      | field_media                  | Entity reference            | No       |                                                                                            |
| Published Date                                     | field_date                   | Date                        | Yes      |                                                                                            |
| Related Assets                                     | field_assets                 | Entity Reference            | Yes      | Entity type: Node; Bundles: Asset                                                          |
| Content                                            | field_components             | Entity Reference Revisions  | No       |                                                                                            |
| Category                                           | field_category               | Entity Reference            | Yes      |                                                                                            |
| Topic                                              | field_topic                  | Entity Reference (Taxonomy) | No       | No limit                                                                                   |
| Featured on 'All' tab of Article Hub               | field_article_hub_sticky_all | Boolean                     | No       |                                                                                            |
| Featured on associated category tab of Article Hub | field_article_hub_sticky_cat | Boolean                     | No       |                                                                                            |
| Meta tags                                          | field_meta_tags              | Meta tags                   | No       |                                                                                            |

### Basic Page
Basic Page is used to create pages like the 'About Us' page.
Drupal Core content type.

| Field                | Machine Name         | Type                       | Required | Notes      |
|----------------------|----------------------|----------------------------|----------|------------|
| Title                | title                | Text                       | Yes      |            |
| Lead-in/Summary Text | body                 | Text long, with summary    | No       |            |
| Listing Image        | field_media          | Entity reference           | No       |            |
| Hero                 | field_component_hero | Entity reference           | No       |            |
| Content              | field_components     | Entity Reference Revisions | No       |            |
| Meta tags            | field_meta_tags      | Meta tags                  | No       |            |

### Landing Page
Landing Page is used to create the first-level navigation pages led with a hero.

| Field                | Machine Name         | Type                       | Required | Notes                                |
|----------------------|----------------------|----------------------------|----------|--------------------------------------|
| Title                | title                | Text                       | Yes      | Not displayed if hero in block title |
| Lead-in/Summary Text | body                 | Text long, with summary    | No       |                                      |
| Listing Image        | field_media          | Entity reference           | No       |                                      |
| Hero                 | field_component_hero | Entity reference revisions | No       |                                      |
| Content              | field_components     | Entity Reference Revisions | No       |                                      |
| Meta tags            | field_meta_tags      | Meta tags                  | No       |                                      |

### Product Page
Product content type.
Data from an API feed that adds content on cron.

| Field                   | Machine Name                  | Type              | Required | Notes |
|-------------------------|-------------------------------|-------------------|----------|-------|
| Title                   | title                         | Text              | Yes      |       |
| Listing Image           | field_media                   | Entity reference  | No       |       |
| Long Description	       | field_long_description        | Text (plain)      | No       |       |
| Product Classifications | field_product_classifications | 	Entity reference | No       |       |
| Product Features        | field_product_features        | Text (plain)      |          |       |
| Product Images          | field_product_images          | Entity reference  |          |       |
| Product Specifications  | field_product_specifications  | Entity reference  |          |       |
| Set?                    | field_set                     | Boolean           |          |       |
| Set Components          | field_set_components          | Entity reference  |          |       |
| SKU Group               | field_sku_group               | Text (plain)      |          |       |
| UPC                     | field_upc                     | Text (plain)      |          |       |
| Meta tags               | field_meta_tags               | Meta tags         | No       |       |

### Product Listing Page
Product Listing content type.
Data from an API feed that adds content on cron.

| Field                   | Machine Name                  | Type              | Required | Notes |
|-------------------------|-------------------------------|-------------------|----------|-------|
| Title                   | title                         | Text              | Yes      |       |
| Product Classifications | field_product_classifications | 	Entity reference | No       |       |
| Meta tags               | field_meta_tags               | Meta tags         | No       |       |
---

## View Modes
View modes are a collection of fields that are displayed for each bundle type.
View modes can be utilized for Content Types, Paragraphs, Blocks, Taxonomies and more.

| View Modes / Content Type     | Basic Page | Landing Page | Product | Product Listing |
|-------------------------------|------------|--------------|---------|-----------------|
| [Default](#default)           | X          | X            | X       | X               |
| [Full Content](#full-content) | X          | X            | X       | X               |
| [Teaser](#teaser)             |            |              | X       |                 |
| [Tile](#tile)                 |            |              | X       |                 |
| [Summary](#summary)           |            |              | X       |                 |


### Default
All content types have a default view mode.
We will not be utilizing the default view mode for this particular project.

### Full Content
All content types have a full mode, and this is the full layout view for the content.

| Field Name           | Content Types          | Notes   |
|----------------------|------------------------|---------|
| Lead-in/Summary Text | All but landing pages  | Default |
| Content (Components) | All                    |         |
| Reading Length       | Article                |         |
| Published Date       | Article, Press Release |         |
| Listing Image        | Article, Basic Page    |         |

### Summary
Summary used in Article and Press Release. Has title, intro content, and date. (Title assumed)

| Field Name      | Content Types           | Notes            |
|-----------------|-------------------------|------------------|
| Short Title     | Article, Press Release  |                  |
| Category        | Article,  Press Release | Label (unlinked) |
| Published Date  | Article, Press Release  |                  |
| Intro (summary) | All                     | Smart trimmed    |

### Teaser
View mode used by articles and press releases with image and copy.

| Field Name      | Content Types           | Notes            |
|-----------------|-------------------------|------------------|
| Short Title     | Article, Press Release  |                  |
| Listing Image   | Article, Press Release  |                  |
| Category        | Article,  Press Release | Label (unlinked) |
| Published Date  | Article, Press Release  |                  |
| Intro (summary) | Article, Press Release  | Smart trimmed    |

### Tile
Tile view mode is used in the navigation for trending stories.

| Field Name    | Content Types          | Notes |
|---------------|------------------------|-------|
| Short Title   | Article, Press Release |       |
| Listing Image | Articles               |       |

---

## Taxonomies
Taxonomy allows you to categorize content within your site.

| Vocabulary              | Content Types            | Required | Notes |
|-------------------------|--------------------------|----------|-------|
| Product Classifications | Product/Product Category | Yes      |       |
| Product Specifications  | Product                  | Yes      |       |

Will also have custom tagging per basic page, for search results

#### View Modes (Taxonomy)

| View Mode        | Full |
|------------------|------|
| Article Category | X    |

---

## Paragraph Components

#### Content
Basic content component. Title, content, and CTA; all fields not required.
Types:
  - Default (smaller container)
  - Media 50/50, R/L aligned
  - Media 50/50, R/L aligned no crop

| Field                | Machine Name            | Type                   | Required | Notes                 |
|----------------------|-------------------------|------------------------|----------|-----------------------|
| Header               | field_title             | text                   | No       |                       |
| Content              | field_content           | text (formatted, long) | No       |                       |
| Call to Action       | field_link              | links                  | No       |                       |
| Media                | field_media_item        | Entity Reference       | No       | Images only           |
| Media Layout         | field_media_layout      | Entity Reference       | No       | Types mentioned above |
| Hide Media on mobile | field_media_hide_mobile | Boolean                | No       |                       |
| Layout               | field_layout            | Entity Reference       | No       |                       |
| Custom Classes       | field_custom_classes    | text                   | No       |                       |
| Style                | field_style             | class list             | No       |                       |
| Anchor               | field_html_anchor       | Text                   | No       |                       |

#### Content Driver
Header with items below it.

| Field          | Machine Name     | Type                       | Required | Notes      |
|----------------|------------------|----------------------------|----------|------------|
| Components     | field_components | Entity reference revisions | No       | Limit of 4 |
| Title/Headline | field_title      | text                       | Yes      |            |

#### Content Driver Item
Used withing Content Driver component. Each item contains an header, text, and a link.

| Field          | Machine Name     | Type               | Required | Notes |
|----------------|------------------|--------------------|----------|-------|
| Media          | field_media_item | media              | No       |       |
| Title          | field_title      | text               | Yes      |       |
| Content        | field_content    | text (plain, long) | Yes      |       |
| Call to Action | field_link       | link               | No       |       |


#### Featured Content
Featured content references other pages, with a variety of view modes.
- Summary: # of referenced content determines 2 column or 3 column layout
- Summary Highlight: 1st item highlighted, 4 additional in grid
- Tailored Content (may not be final): Half width image, multiple results in slider
- Teaser: # of referenced content determines 2 column or 3 column layout

| Field             | Machine Name                     | Type             | Required | Notes                                                                                                            |
|-------------------|----------------------------------|------------------|----------|------------------------------------------------------------------------------------------------------------------|
| Header            | field_title                      | text             | Yes      | Looks like eyebrow in some VMs                                                                                   |
| Hide Header       | field_title_hide                 | boolean          |          | Allows to hide headers even though they are required for accessibility.                                          |
| Content           | field_related_nodes              | Entity Reference | No       | Autocomplete                                                                                                     |
| Layout            | field_layout                     | Entity Reference | No       |                                                                                                                  |
| Custom Classes    | field_custom_classes             | text             | No       |                                                                                                                  |
| Style             | field_style                      | class list       | No       |                                                                                                                  |
| Anchor            | field_html_anchor                | Text             | No       |                                                                                                                  |
| Limit             | field_limit                      | Number (integer) | No       | Limit max number of items shown. Manually-selected items take precedence. Defaults to <em>10</em> if left empty. |
| Sort by           | field_featured_content_sort      | List (text)      | No       | Options: Date Created, Title. Defaults to <em>Date Created</em> if left empty.                                   |
| Content Types     | field_content_types              | Entity Reference | No       |                                                                                                                  |
| Categories        | field_taxonomy_categories        | Entity Reference | No       |                                                                                                                  |
| Topics            | field_taxonomy_topics            | Entity Reference | No       |                                                                                                                  |
| Content Overrides | field_featured_content_overrides | Entity Reference | No       | Autocomplete                                                                                                     |

#### Featured Media
Featured media component for embedding images, remote videos.
Adding multiple will create carousel.
Layout:
    - Full
    - Thirds

| Field          | Machine Name         | Type                   | Required | Notes        |
|----------------|----------------------|------------------------|----------|--------------|
| Layout         | field_layout         | Entity reference       | No       | Full, thirds |
| Header         | field_title          | text                   | No       |              |
| Description    | field_content        | text (formatted, long) | No       |              |
| Media          | field_media_items    | Entity reference       | No       |              |
| Custom Classes | field_custom_classes | text                   | No       |              |
| Style          | field_style          | class list             | No       |              |
| Anchor         | field_html_anchor    | Text                   | No       |              |

#### Hero
Used in conjunction with Hero Slide.

| Field          | Machine Name         | Type                       | Required | Notes |
|----------------|----------------------|----------------------------|----------|-------|
| Slides         | field_components     | Entity reference revisions | No       |       |
| Custom Classes | field_custom_classes | text                       | No       |       |
| Style          | field_style          | class list                 | No       |       |
| Anchor         | field_html_anchor    | Text                       | No       |       |

#### Hero Slide
Used inside Hero component. Contains title, copy, and CTA.

| Field               | Machine Name                  | Type                   | Required | Notes |
|---------------------|-------------------------------|------------------------|----------|-------|
| Title               | title                         | text                   | No       |       |
| Content             | field_content                 | text (formatted, long) | No       |       |
| Background          | field_media_background        | Entity reference       | No       |       |
| Background (mobile) | field_media_background_mobile | Entity reference       | No       |       |
| Custom Classes      | field_custom_classes          | text                   | No       |       |
| Style               | field_style                   | class list             | No       |       |

#### List Content
Header with copy and text.

| Field          | Machine Name       | Type                       | Required | Notes |
|----------------|--------------------|----------------------------|----------|-------|
| Components     | field_components   | Entity reference revisions | No       |       |
| Content        | field_content      | text (formatted, long)     | Yes      |       |
| Custom Classes | field_class_custom | text                       | No       |       |
| Layout         | field_layout       | Entity Reference           | No       |       |
| Style          | field_style        | class list                 | No       |       |
| Anchor         | field_html_anchor  | Text                       | No       |       |

#### List Content Item
Used withing List Content component. Each item contains an icon, header, text, and a link.

| Field          | Machine Name     | Type               | Required | Notes |
|----------------|------------------|--------------------|----------|-------|
| Media          | field_media_item | media              | No       |       |
| Title          | field_title      | text               | Yes      |       |
| Content        | field_content    | text (plain, long) | Yes      |       |
| Call to Action | field_link       | link               | No       |       |

#### Section
Section bundle together other components into one section (for striping effect).

| Field          | Machine Name         | Type                       | Required | Notes |
|----------------|----------------------|----------------------------|----------|-------|
| Components     | field_components     | Entity reference revisions | No       |       |
| Custom Classes | field_custom_classes | text                       | No       |       |
| Style          | field_style          | Entity reference           | No       |       |
| Anchor         | field_html_anchor    | Text                       | No       |       |

#### Spotlight Tabs
A component that displays content spotlights in a tabbed format.

| Field          | Machine Name         | Type                       | Required | Notes                                                                            |
|----------------|----------------------|----------------------------|----------|----------------------------------------------------------------------------------|
| Label          | field_title          | text                       | Yes      | Visually hidden, but needed to describe the component for accessibility reasons. |
| Tabs           | field_components     | Entity reference revisions | No       |                                                                                  |
| Custom Classes | field_custom_classes | text                       | No       |                                                                                  |
| Style          | field_style          | Entity reference           | No       |                                                                                  |
| Anchor         | field_html_anchor    | Text                       | No       |                                                                                  |

#### Spotlight Tab
Used within the Spotlight Tabs component.

| Field           | Machine Name           | Type                   | Required | Notes        |
|-----------------|------------------------|------------------------|----------|--------------|
| Title           | field_title            | text                   | Yes      |              |
| Eyebrow         | field_eyebrow          | text                   | No       |              |
| Subtitle        | field_subtitle         | text                   | Yes      |              |
| Content         | field_content          | text (formatted, long) | No       |              |
| Call to Action  | field_link             | link                   | No       |              |
| Related Stories | field_related_nodes    | Entity Reference       | No       | Autocomplete |
| Background      | field_media_background | Entity reference       | No       |              |

### Other:
#### Embed: iframe
Paragraph component to embed an iframe. May be used for instagram feed.

| Field          | Machine Name         | Type        | Required | Notes        |
|----------------|----------------------|-------------|----------|--------------|
| Title          | field_title          | text        | Yes      |              |
| Height         | field_height         | Text(plain) | No       |              |
| Width          | field_width          | Text(plain) | No       |              |
| URL            | field_link           | Link        | No       |              |
| Custom Classes | field_custom_classes | text        | No       |              |
| Style          | field_style          | class list  | No       | Color picker |

#### Embed: View
Paragraph component to embed a view. (Related Articles)

| Field          | Machine Name         | Type            | Required | Notes        |
|----------------|----------------------|-----------------|----------|--------------|
| Title          | field_title          | text            | Yes      |              |
| View           | field_view           | views reference | No       |              |
| Custom Classes | field_custom_classes | text            | No       |              |
| Style          | field_style          | class list      | No       | Color picker |

---

## Views
#### Search
 * Page, block
 * Relationship: Content entity referenced from field_page_reference (field on landing page)
 * Display all published, content type press release, limit 5, sort by last 5 publish date.

---

## Blocks
 Blocks are added into appropriate regions via the block interface.

| Block Name        | Type   | Region | Placement |
|-------------------|--------|--------|-----------|
| Footer: Copyright | Custom | Footer | All Pages |
| Footer: Privacy   | Custom | Footer | All Pages |

---

## Menus
| Label         | Type     | Notes |
|---------------|----------|-------|
| Site Branding | Internal |       |

#### Main Menu
| Label                 | Link                   | Type     | Notes |
|-----------------------|------------------------|----------|-------|
| About Us              | /about-us              | Internal |       |
| Our Business          | /our-business/         | Internal |       |
| Social Responsibility | /social-responsibility | Internal |       |
| Careers               | /careers               | Internal |       |
| Our Stories           | /our-stories           | Internal |       |
| Investors             | /investors             | Internal |       |

#### Footer Menu
| Label     | Link | Type         | Notes |
|-----------|------|--------------|-------|
| Copyright | no   | static block |       |

#### Social Menu
| Label   | Link | Type     | Notes |
|---------|------|----------|-------|
| Twitter |      | External |       |


---

## Media Types

### Asset
A file or set of files to be provided as a downloadable asset.

| Label            | Machine Name      | Type        | Notes                                                                                  |
|------------------|-------------------|-------------|----------------------------------------------------------------------------------------|
| File             | field_media_file  | File        |                                                                                        |
| Additional Files | field_media_files | File        | Optional. An unlimited file field. "Description" option is enabled to label each file. |
| Thumbnail        | field_thumbnail   | Image       | Required.                                                                              |
| Asset Type       | field_asset_type  | List (text) | Required.                                                                              |

### Image
| Label   | Machine Name        | Type         | Notes                  |
|---------|---------------------|--------------|------------------------|
| Image	  | field_media_image   | Image	       |                        |
| Caption | field_media_caption | Text (plain) | Overlapping figcaption |

### Remote video
| Label     | Machine Name                  | Type         | Notes |
|-----------|-------------------------------|--------------|-------|
| Video Url | field_media_video_embed_field | Video Embed	 |       |
| Caption   | field_media_caption           | Text (plain) |       |

### File (pdf)
| Label | Machine Name     | Type | Notes |
|-------|------------------|------|-------|
| File  | field_media_file | File |       |

---

## Image Styles
 * Biggest (landscape, portrait, scaled, square in various ratios)
 * Huge (landscape, portrait, scaled, square in various ratios)
 * Large (landscape, portrait, scaled, square in various ratios)
 * Medium (landscape, portrait, scaled, square in various ratios)
 * Small (landscape, portrait, scaled, square in various ratios)
 * Tiny (landscape, portrait, scaled, square in various ratios)
 * Thumbnail
 * Media Library Thumbnail

## Responsive Image Styles
 * hero
 * narrow
 * wide
 * featured media: Full
 * featured media: Third
 * featured content: Blade
 * featured content: Stack

---

## Breakpoints
 * Extra Small: max (30.00 x 16px == 480px)

 * Small_min: min (30.01 x 16px ~= 481px)

 * Small: min (30.01 x 16px ~= 481px), max (35.50 x 16px == 768px)

 * Medium (min): min (35.51 x 16px ~= 769px)

 * Medium: min (35.51 x 16px ~= 769px), max (48.00 x 16px == 980px)

 * Large (min): min (48.01 x 16px ~= 981px)

 * Large: min (48.01 x 16px ~= 981px), max (61.25 x 16px == 1280px)

 * Extra Large (min): min (61.26 x 16px ~= 1281px)

 * Extra Large: min (61.26 x 16px ~= 1281px), max (80.00 x 16px == 1450px)

 * Grande: min (61.26 x 16px ~= 1281px)

---

## Regions
  - page_top: 'Page top'
  - header: 'Header'
  - pre_content: 'Pre-content'
  - content: Content
  - post_content: 'Post-content'
  - footer_first: 'Footer: First'
  - footer_second: 'Footer: Second'
  - footer_third: 'Footer: Third'
  - page_bottom: 'Page bottom'

---

## Roles and Permissions
 * Super Administrator
 * Site Administrator
 * Content Editor
 * Anonymous user
 * Authenticated user
