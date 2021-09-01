
# Apex Tools - Build Document

Welcome to the Apex Tools Build doc.

## List of relevant links


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

| Field  | Machine Name | Type                      | Required | Notes      |
|--------|--------------|---------------------------|----------|------------|
|Title   |title         |Text                       |Yes       | Not displayed if hero in block title |
|Short title   |field_title_short   |Text           |No        | Used in place of main title on non-full-content view-modes for content with longer titles. |
|Lead-in/Summary Text |body |Text long, with summary |No       |            |
|Hero Image |field_featured_media |Entity reference |No        |            |
|Listing Image |field_media |Entity reference       |No        |            |
|Published Date |field_date |Date                   |Yes       |            |
|Related Assets |field_assets |Entity Reference |Yes   | Entity type: Node; Bundles: Asset |
|Content |field_components |Entity Reference Revisions |No        |            |
|Category |field_category |Entity Reference |Yes       |            |
|Topic |field_topic |Entity Reference (Taxonomy)      |No        | No limit   |
|Featured on 'All' tab of Article Hub |field_article_hub_sticky_all|Boolean |No        |    |
|Featured on associated category tab of Article Hub  |field_article_hub_sticky_cat|Boolean |No        |    |
|Meta tags |field_meta_tags |Meta tags              |No        |            |

### Basic Page
Basic Page is used to create pages like the 'About Us' page.
Drupal Core content type.

| Field  | Machine Name | Type                      | Required | Notes      |
|--------|--------------|---------------------------|----------|------------|
|Title   |title         |Text                       |Yes       |            |
|Lead-in/Summary Text |body |Text long, with summary|No        |            |
|Listing Image |field_media |Entity reference       |No        |            |
|Hero |field_component_hero |Entity reference |No        |            |
|Content |field_components |Entity Reference Revisions |No        |            |
|Meta tags |field_meta_tags |Meta tags              |No        |            |

### Landing Page
Landing Page is used to create the first-level navigation pages led with a hero.

| Field  | Machine Name | Type                      | Required | Notes      |
|--------|--------------|---------------------------|----------|------------|
|Title   |title         |Text                       |Yes       | Not displayed if hero in block title |
|Lead-in/Summary Text |body |Text long, with summary |No       |            |
|Listing Image |field_media |Entity reference       |No        |            |
|Hero    |field_component_hero |Entity reference revisions |No |            |
|Content |field_components |Entity Reference Revisions |No        |            |
|Meta tags |field_meta_tags |Meta tags              |No        |            |

### Product Page
Product content type.
Data from an API feed that adds content on cron.

| Field  | Machine Name | Type                      | Required | Notes      |
|--------|--------------|---------------------------|----------|------------|
|Title   |title         |Text                       |Yes       |            |
|Listing Image |field_media |Entity reference       |No        |            |
|Long Description	 |field_long_description|Text (plain) |No        |            |
|Product Classifications  |field_product_classifications|	Entity reference|No| |
|Product Features |field_product_features  |Text (plain) | | |
|Product Images |field_product_images| Entity reference  | | |
|Product Specifications |field_product_specifications|  Entity reference  | | |
|Set? |field_set  |Boolean  | | |
|Set Components |field_set_components | Entity reference  | | |
|SKU Group  |field_sku_group  |Text (plain)  | | |
|UPC  |field_upc  |Text (plain)  | | |
|Meta tags |field_meta_tags |Meta tags  |No  | | |

### Product Listing Page
Product Listing content type.
Data from an API feed that adds content on cron.

| Field  | Machine Name | Type                      | Required | Notes      |
|--------|--------------|---------------------------|----------|------------|
|Title   |title         |Text                       |Yes       |            |
|Product Classifications  |field_product_classifications|	Entity reference|No| |
|Meta tags |field_meta_tags |Meta tags  |No  | | |
---

## View Modes
View modes are a collection of fields that are displayed for each bundle type.
View modes can be utilized for Content Types, Paragraphs, Blocks, Taxonomies and more.

|View Modes / Content Type| Basic Page| Landing Page | Product | Product Listing|
|------------ | :----: | :----: | :----: | :----: | :----: |
|[Default](#default)|X|X|X|X|
|[Full Content](#full-content)|X|X|X|X|
|[Teaser](#teaser)| | |X| |
|[Tile](#tile)| | |X| |
|[Summary](#summary)| | |X| |

### Default
All content types have a default view mode.
We will not be utilizing the default view mode for this particular project.

### Full Content
All content types have a full mode, and this is the full layout view for the content.

|Field Name      |Content Types             |Notes  |
|--------------  | :----------------------: | :---  |
|Lead-in/Summary Text|All but landing pages |Default|
|Content (Components)|All                   |       |
|Reading Length  | Article                  |       |
|Published Date  | Article, Press Release   |       |
|Listing Image   | Article, Basic Page      |       |

### Summary
Summary used in Article and Press Release. Has title, intro content, and date. (Title assumed)

|Field Name      |Content Types            |Notes  |
|-------------- | :----------------------: | :---  |
|Short Title    | Article, Press Release   |       |
|Category       |Article,  Press Release   | Label (unlinked) |
|Published Date |Article, Press Release    |       |
|Intro (summary)|All                       | Smart trimmed |

### Teaser
View mode used by articles and press releases with image and copy.

|Field Name      |Content Types            |Notes  |
|-------------- | :----------------------: | :---  |
|Short Title    | Article, Press Release   |       |
|Listing Image  |Article, Press Release    |       |
|Category       |Article,  Press Release   | Label (unlinked) |
|Published Date |Article, Press Release    |       |
|Intro (summary)|Article, Press Release    | Smart trimmed |

### Tile
Tile view mode is used in the navigation for trending stories.

|Field Name      |Content Types            |Notes  |
|-------------- | :----------------------: | :---  |
|Short Title    | Article, Press Release   ||
|Listing Image  |Articles                  |       |

---

## Taxonomies
Taxonomy allows you to categorize content within your site.

|Vocabulary|Content Types|Required|Notes|
| :--- | :--- | :--- | :--- |
|Article Category|Article|Yes||

Will also have custom tagging per article, for search results

#### View Modes (Taxonomy)

|View Mode|Full|
|---------| :---: |
|Article Category|X|

---

## Paragraph Components

#### Content
Basic content component. Title, content, and CTA; all fields not required.
Types:
  - Default (smaller container)
  - Media 50/50, R/L aligned
  - Media 50/50, R/L aligned no crop

| Field | Machine Name | Type | Required | Notes |
|-------|--------------|------|----------|-------|
|Header |field_title   |text  |No        |       |
|Content|field_content |text (formatted, long)|No|
|Call to Action|field_link |links        |No|    |
|Media  |field_media_item |Entity Reference|No|Images only|
|Media Layout |field_media_layout |Entity Reference|No|Types mentioned above|
|Hide Media on mobile|field_media_hide_mobile|Boolean|No| |
|Layout |field_layout |Entity Reference|No|      |
|Custom Classes|field_custom_classes|text|No|    |
|Style  |field_style   |class list|No    |       |
|Anchor |field_html_anchor|Text|No       |       |

#### Featured Content
Featured content references other pages, with a variety of view modes.
- Blade: eyebrow, header, copy, expanded copy, text link
- Summary: # of referenced content determines 2 column or 3 column layout
- Summary Highlight: 1st item highlighted, 4 additional in grid
- Tailored Content (may not be final): Half width image, multiple results in slider
- Teaser: # of referenced content determines 2 column or 3 column layout

| Field | Machine Name | Type | Required | Notes |
|-------|--------------|------|----------|-------|
|Header  |field_title   |text  |Yes      | Looks like eyebrow in some VMs |
|Hide Header  |field_title_hide   |boolean  |      | Allows to hide headers even though they are required for accessibility. |
|Content|field_related_nodes|Entity Reference|No|Autocomplete|
|Layout |field_layout |Entity Reference|No|      |
|Custom Classes|field_custom_classes|text|No|    |
|Style  |field_style   |class list|No    |       |
|Anchor |field_html_anchor|Text|No       |       |
|Limit  |field_limit   | Number (integer)|No|Limit max number of items shown. Manually-selected items take precedence. Defaults to <em>10</em> if left empty.|
|Sort by|field_featured_content_sort |List (text)|No|Options: Date Created, Title. Defaults to <em>Date Created</em> if left empty.|
|Content Types|field_content_types|Entity Reference|No|  |
|Categories|field_taxonomy_categories|Entity Reference|No|  |
|Topics|field_taxonomy_topics|Entity Reference|No|  |
|Content Overrides|field_featured_content_overrides|Entity Reference|No|Autocomplete|

#### Featured Media
Featured media component for embedding images, remote videos.
Adding multiple will create carousel.
Layout:
    - Full
    - Thirds

| Field | Machine Name | Type | Required | Notes |
|-------|--------------|------|----------|-------|
|Layout |field_layout  |Entity reference |No     |Full, thirds|
|Header|field_title|text|No|    |
|Description|field_content |text (formatted, long)|No|
|Media  |field_media_items|Entity reference| No  |
|Custom Classes|field_custom_classes|text|No|    |
|Style|field_style|class list|No|   |
|Anchor |field_html_anchor|Text|No       |       |

#### Hero
Used in conjunction with Hero Slide.

| Field | Machine Name | Type | Required | Notes |
|-------|--------------|------|----------|-------|
|Slides |field_components|Entity reference revisions|No|
|Custom Classes|field_custom_classes|text|No|    |
|Style  |field_style   |class list|No    |       |
|Anchor |field_html_anchor|Text|No       |       |

#### Hero Slide
Used inside Hero component. Contains title, copy, and CTA.

| Field | Machine Name | Type | Required | Notes |
|-------|--------------|------|----------|-------|
|Title  |title         |text  |No        |       |
|Content|field_content |text (formatted, long)|No|
|Background| field_media_background| Entity reference|No||
|Background (mobile)| field_media_background_mobile| Entity reference|No||
|Custom Classes|field_custom_classes|text|No|    |
|Style  |field_style   |class list|No    |       |

#### List Content
Header with copy and text.

| Field    | Machine Name | Type | Required      | Notes |
|----------|--------------|------|---------------|-------|
|Components|field_components|Entity reference revisions|No|
|Content|field_content |text (formatted, long)|Yes|
|Custom Classes|field_class_custom|text|No| |
|Layout |field_layout |Entity Reference|No| |
|Style|field_style|class list|No| |
|Anchor|field_html_anchor|Text|No| |

#### List Content Item
Used withing List Content component. Each item contains an icon, header, text, and a link.

| Field | Machine Name | Type         | Required | Notes |
|-------|--------------|--------------|----------|-------|
|Media  |field_media_item|media       |No        | |
|Title  |field_title   |text          |Yes       | |
|Content|field_content |text (plain, long)|Yes   | |
|Call to Action|field_link|link       |No        | |

#### Pullquote
Component for a quote. Background color can be specified via classy styles.

| Field | Machine Name | Type | Required | Notes |
|-------|--------------|------|----------|-------|
|Content|field_content |text (formatted, long)|No|
|Attribution|field_attribution|Text - plain|No|  |
|Custom Classes|field_custom_classes|text|No|    |
|Style|field_style|class list|No|Color picker    |
|Anchor |field_html_anchor|Text|No       |       |

#### Section
Section bundle together other components into one section (for striping effect).

| Field | Machine Name | Type | Required | Notes |
|-------|--------------|------|----------|-------|
|Components|field_components|Entity reference revisions|No||
|Custom Classes|field_custom_classes|text|No| |
|Style|field_style|Entity reference|No   | |
|Anchor |field_html_anchor|Text|No       | |

#### Spotlight Tabs
A component that displays content spotlights in a tabbed format.

| Field | Machine Name | Type | Required | Notes |
|-------|--------------|------|----------|-------|
|Label  |field_title   |text  |Yes       | Visually hidden, but needed to describe the component for accessibility reasons.|
|Tabs|field_components|Entity reference revisions|No| |
|Custom Classes|field_custom_classes|text|No| |
|Style|field_style|Entity reference|No   | |
|Anchor |field_html_anchor|Text|No       | |

#### Spotlight Tab
Used within the Spotlight Tabs component.

| Field | Machine Name | Type | Required | Notes |
|-------|--------------|------|----------|-------|
|Title  |field_title   |text  |Yes       |       |
|Eyebrow|field_eyebrow |text  |No        |       |
|Subtitle|field_subtitle|text |Yes       | |
|Content|field_content |text (formatted, long)|No| |
|Call to Action|field_link|link|No       | |
|Related Stories|field_related_nodes|Entity Reference|No|Autocomplete|
|Background| field_media_background| Entity reference|No||

#### Stock Ticker
Basic content component with stock ticker information.

| Field | Machine Name | Type | Required | Notes |
|-------|--------------|------|----------|-------|
|Header |field_title   |text  |No        |       |
|Content|field_content |text (formatted, long)|No|
|Call to Action|field_link |links        |No|    |
|Custom Classes|field_custom_classes|text|No|    |
|Style  |field_style   |class list|No    |       |
|Anchor |field_html_anchor|Text|No       |       |

#### Pie Chart
Can be used within content component, R/L/Center aligned
Pie chart data point can have percentage, caption.
Pie chart title and caption.

| Field | Machine Name | Type | Required | Notes |
|-------|--------------|------|----------|-------|
|Label  |field_title   |text  |Yes       |Visually Hidden |
|Description|field_content |text formatted  |No       |
|Data|field_data |Table Field  |No       | 2 cols, first row filled|
|Call to Action|field_link |Link |No       | |
|Layout|field_layout |Entity Reference (Classy) |No |Default Centered |
|Custom Classes|field_custom_classes|text|No|    |
|Style|field_style|class list|No| Background styles |

### Other:
#### Embed: form
Paragraph component to embed a form.

| Field | Machine Name | Type | Required | Notes |
|:------|:-------------|:----:|:--------:|-------|
|Title  |field_title   |text  |Yes       |       |
|Hide Title  |field_title_hide   |boolean  |      | Allows to hide headers even though they are required for accessibility. |
|Content|field_content |text (formatted, long)|No||
|Form Markup|field_markup |text (formatted, long)|Yes| Uses a custom "form" text formatter option |
|Form provider|field_markup_provider|class list|Yes|Defaults to "General". Exists to allow to target provider-specific markup/classes for styling purposes.|
|Custom Classes|field_custom_classes|text|No|    |
|Style|field_style|class list|No|                |
|Anchor |field_html_anchor|Text|No       |       |

#### Embed: iframe
Paragraph component to embed an iframe. May be used for instagram feed.

| Field | Machine Name | Type | Required | Notes |
|:------|:-------------|:----:|:--------:|-------|
|Title  |field_title   |text  |Yes       |       |
|Height |field_height  |Text(plain)|No   |       |
|Width  |field_width   |Text(plain)|No   |       |
|URL    |field_link    |Link  |No        |       |
|Custom Classes|field_custom_classes|text|No|    |
|Style|field_style|class list|No|Color picker    |

#### Embed: View
Paragraph component to embed a view. (Related Articles)

| Field | Machine Name | Type | Required | Notes |
|:------|:-------------|:----:|:--------:|-------|
|Title  |field_title   |text  |Yes       |       |
|View   |field_view    |views reference|No|      |
|Custom Classes|field_custom_classes|text|No|    |
|Style|field_style|class list|No|Color picker    |

---

## Views

#### Article Hub
 * Embed
 * Exposed Filter: Categories
 * Sort: Sticky by category (sort swapped programmatically based on filter selection), falls back to date descending
 * Display all published, content type article, limit 9.

#### Content Archive
 * Embed (Article, Press Release, Article/Press Release)
 * Contextual filter on published date (field_date, granularity: year), via query parameter.
 * Display all published, content type (depends on display), exposed category filter, exposed topic filter.
 * Year filter added programmatically in wba_core_form_views_exposed_form_alter

#### Latest News
 * Latest News 1/4 (view mode summary: 1 main article, 4 smaller articles)
 * Block
 * Relationship: Content entity referenced from field_page_reference (field on landing page)
 * Display all published, content type press release, limit 5, sort by last 5 publish date.

#### Search
 * Page, block
 * Relationship: Content entity referenced from field_page_reference (field on landing page)
 * Display all published, content type press release, limit 5, sort by last 5 publish date.

---

## Blocks
 Blocks are added into appropriate regions via the block interface.

|Block Name|Type|Region|Placement|
|---------| :---: | :---: | :---: |
|Footer: Copyright |Custom|Footer|All Pages
|Footer: Privacy|Custom|Footer|All Pages

---

## Menus
|Label|Type|Notes|
|---------| :---: | :--- |
|Site Branding|Internal|

#### Main Menu
|Label|Link|Type|Notes|
|---------| :---: | :---: | :--- |
|About Us| /about-us |Internal|
|Our Business|/our-business/ |Internal|
|Social Responsibility|/social-responsibility     |Internal|   |
|Careers|/careers     |Internal|   |
|Our Stories|/our-stories |Internal|   |
|Investors|/investors |Internal|   |

#### Footer Menu
|Label|Link|Type|Notes|
|---------| :---: | :---: | :--- |
|Copyright| no | static block|

#### Social Menu
|Label|Link|Type|Notes|
|---------| :---: | :---: | :--- |
|Twitter| |External|


---

## Media Types

### Asset
A file or set of files to be provided as a downloadable asset.
|Label|Machine Name|Type|Notes|
|---------| :---: | :---: | :--- |
|File|field_media_file |File| |
|Additional Files|field_media_files |File| Optional. An unlimited file field. "Description" option is enabled to label each file.|
|Thumbnail|field_thumbnail |Image| Required. |
|Asset Type|field_asset_type |List (text)| Required. |

### Image
|Label|Machine Name|Type|Notes|
|---------| :---: | :---: | :--- |
|Image	  |field_media_image |Image	|
|Caption  |field_media_caption |Text (plain)| Overlapping figcaption|

### Remote video
|Label|Machine Name|Type|Notes|
|---------| :---: | :---: | :--- |
|Video Url|field_media_video_embed_field |Video Embed	|
|Caption  |field_media_caption |Text (plain)| |

### File (pdf)
|Label|Machine Name|Type|Notes|
|---------| :---: | :---: | :--- |
|File|field_media_file |File|

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
