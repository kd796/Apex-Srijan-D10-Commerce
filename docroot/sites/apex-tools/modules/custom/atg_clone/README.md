# ATG - Clone
The purpose of this module is to provide the editor with the capability to clone existing content and to give them the flexibility to modify the structure.

### Why
Drupal's translate feature expects the translated content to mirror the sources structure exactly. It's purely for translating content and it doesn't provide editors with the flexibility of adding or removing sections or fields.

There were also a few bugs found during development.
- Unable to delete translated node at times
- Unable to translate link field
- Known issues with Paragraphs revisions
- Bugginess of some sections disappearing from the original source.
- Overall user experience is a little confusing while translating and saving content with domains, language codes, untranslated content, user's language setting, interface language, and page redirects on save.

### Dependencies
1. Quick Clone Node
2. Custom Patch `quick_node_clone_add_class_var_node_4.patch`
2. Drupal Language Selector

### Features
- Refactors Quick Clone Node to store additional data in the `atg_clone` table to act as translation relationships.
- Refactors the langauge selector links
- Ensures translation doesn't already exist
- Ensures orphans do not exist by removing data when nodes are deleted.

### How
1. On the content index page
    - Click the arrow next to the Edit button, click Clone
    - _Or_ click the Edit button, next click the Clone tab
2. Make sure to select a language from the select list. Drupal will throw an error message if the language already exist for the cloned node.
3. Click save
