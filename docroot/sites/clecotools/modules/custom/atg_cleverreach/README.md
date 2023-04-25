# Drupal Module
[Drupal CleverReach Module](https://www.drupal.org/project/cleverreach)

As of 9/17/2018 the module had some issues which resulted to a custom module.

#### Drupal Module Issues
- Block caching and bugginess
- Only "textfield" type support
- Submit button weight hardcoded
- Email field, label, and weight hardcoded
- Sorting UI difficulty
- Utilizes SOAP service

# ATG Module
- Add hidden field CleverReach ID (`cleverreach_id`) to each form that needs to submit to CleverReach. 
- Set EN value to null
- Set translated field values for each language

# Notes
This is a hack to connect forms to CleverReach. In the end we should utilize the Drupal module, but only when it's out of "dev"
