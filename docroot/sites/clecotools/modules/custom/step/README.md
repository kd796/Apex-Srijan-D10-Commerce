
# Nice-to-haves
- Create a settings page where an admin can manually delete a document or where they can upload an XML of products to delete. Same theory for classifications.

---

# General

## Overview
- English must be created first because the logic is based on English keys. It was intentionally developed this way because I believed having readable key names vs UID's would make it easier to develop the front-end and have other developers jump in when needed.
- While processing the XML we are mapping the atribute ID to a static JSON file we created. That static JSON file allows us to use the same key names for any language. See `StepService::indexData` line 294. If this line is uncommented each Attribute ID and name will be generated. You can then manually update the products.json, if needed, at any time. The client confirmed the attributed will rarely ever change, if ever after the site build.
- Please refer to `mappings/products.json` to see the full list of attributes and key names.
- The CMS does not have logic to remove or disable products. At some point we should create a settings page where an admin can manually delete a document or where they can upload an XML of products to delete.
- There are three indices per locale prefixed with site name and locale code, followed by index type. See example below:
    - cleco_en_products
    - cleco_en_downloads
    - cleco_en_nodes
- Legacy documents are uploaded manually via an XML and are global. The structure is completely different from the normal XML files. Please see STEP Legacy section.
- If for any reason the data is compromised you can delete each index separately in their respective settings page. Ask the client for a bulk export from STEP, import the XML, save the page, and run the ES import process. If Drupal times out, you may need to increase the memory limit. I was working with 16-18mb files without issues.
- ElasticSearh is hosted on AWS. There's only one environment and the dev site points to ElasticSearch production.
- The dev site uses production's STEP assets. STEP assets are copied from the "step-mount" folder and the ES indexing creates resizes.

## Cron
- ~~The `atg_cron_cron()` should only be ran from one site because it will update all ES indices and images.~~
- Each server env now has an ES index prefix set in dotenv. Cron can be set for each environment if both staging and prod have different indices. Both servers utilize the same assets, but each ES index will be unique to the env.
- Cron runs every 24hrs at 3am and can be ran manually from Drupal.
- We do no use Drupal's cron since it's configuration is set from the last time cron was ran and not by a specific time.
- `StepService $locales` must match the site's language code exactly. We use the `ContextID` attribute in the XML to determine what ES index and CMS data to update. 
- I would advise any developer working on the site to install ElasticSearch locally. Please see "ElasticSearch Local Installation"

## AWS
- Credentials are in 1password
- Modify the access policy to add IPs
- If you're working locally you may need to log into AWS and add your IP to the access policy.

## ES Mapping
- There's a unique mapping JSON file for each index in `src/config/mappings`
- All ranges are mapped as floats to calculate decimals. 
- Commas will break. International commas are converted to decimal points.

## Import Process Overview
- Drupal processes an XML file or files if using delta feeds
- Creates/Updates "Products" Vocabulary from Classifications "Power Tools Web Hier Streamline"
    - Vocabulary fields used in product sections on "/products" page
- Creates/Updates "Product Filters" Vocabulary from all existing Attributes
    - Vocabulary used for Aggregations on "/products/product-catalog"
    - Vocabulary defines default order of aggregations
- Creates/Updates ES Index
- Syncs images from "step-mount"
- Resizes images


## Adding New Language
- Configure Drupal and add new locale
- Add langcode code to `StepService $locales`
- Visit `/admin/config/step/settings` to bulk import the XML
- Once the page refreshes you should see the index under "ELASTICSEARCH INDICES"
- Ensure Vocabularies Products and Product Filters were properly translated.
- Visit `/products/product-catalog` translated page to test the data.


## Filters per product
This is a file to easily visualize the filters per product. 
You may also view the real, up-to-date data by using Kibana.
To regenerate the document uncomment lines 781-783 in `StepService::buildProducts`
[ElasticSearch Product Filter List](./readme/STEP_PRODUCT_FILTERS.md)


## ES Commands
**Local ES Health**
`curl -X GET http://127.0.0.1:9200/_cat/indices?v`
**AWS ES Health**
`curl -X GET https://search-atg-tid3j5pwom3supbbmkm7djf3ki.us-east-2.es.amazonaws.com:443/_cat/indices?v`
**Examples**
curl -X GET https://search-atg-tid3j5pwom3supbbmkm7djf3ki.us-east-2.es.amazonaws.com:443/_cluster/allocation/explain?include_disk_info=true
**Disk Space**
curl -X GET https://search-atg-tid3j5pwom3supbbmkm7djf3ki.us-east-2.es.amazonaws.com:443/_cat/allocation?v


---

# STEP Legacy
Instead of a STEP XML file, the legacy documents will be provided by a XLS file. STEP did not export the "Associated Products" needed for searching.

1. Convert XLS to XML 
2. Follow the formatting below.
3. Import to Configuration - Development - STEP - Legacy Documents

## XML Format Example
``` xml
<data>
  <Documents>
      <Document>
        <Asset></Asset>
        <ID></ID>
        <Name></Name>
        <File_Name></File_Name>
        <Associated_Product></Associated_Product>
        <Language></Language>
      </Document>
    </Documents>
</data>
```

---

# ElasticSearch Local Installation

## ElasticSearch and Kibana 
- `$ brew install elasticsearch` [ElasticSearch](https://www.elastic.co/guide/en/elasticsearch/reference/current/_installation.html)
- Install kibana into local env `$ brew install kibana` [Kibana](https://www.elastic.co/guide/en/kibana/current/install.html)

## Elastic-HQ 
[Elastic HQ](https://github.com/ElasticHQ/elasticsearch-HQ)
[Getting Started](http://docs.elastichq.org/installation.html)

#### Install
- `$ cd` root of directory
- `$ sudo python3 -m pip install -U --force-reinstall pip`
- `$ sudo pip install -r requirements.txt` 
- `$ python3 application.py`

## Start Services Locally
`$ elasticsearch`
`$ kibana`
`$ python3 application.py` from root of Elastic-HQ directory

## Visit Locally
- Elasticsearch: http://localhost:9200
- Kibana: http://localhost:5601
- Elastic HQ: http://localhost:5000

---

# ElasticSearch General Overview
[Cheatsheet](http://elasticsearch-cheatsheet.jolicode.com/)
[Reference](https://www.elastic.co/guide/en/elasticsearch/reference/current/index.html)
[Shards](https://www.elastic.co/blog/how-many-shards-should-i-have-in-my-step-cluster)
[Maximize Indexing](https://qbox.io/blog/maximize-guide-elasticsearch-indexing-performance-part-2)
[Mapping: Index per document type vs Custom Field Type](https://www.elastic.co/guide/en/elasticsearch/reference/current/removal-of-types.html)
[ES Practical Overview](https://www.elastic.co/blog/a-practical-introduction-to-elasticsearch)
[ES Overview [Outdated, but helpful]](https://www.youtube.com/watch?v=UPkqFvjN-yI)
[Shards Calclulation](https://qbox.io/blog/optimizing-elasticsearch-how-many-shards-per-index)
[Example: PHP, MySQL, Indexing, Updating, Deleting, Searching](https://www.cloudways.com/blog/setup-elasticsearch-with-mysql)
[Fuzziness](https://www.elastic.co/guide/en/elasticsearch/guide/current/fuzziness.html)

## Overview
[ES Terminology](https://www.elastic.co/guide/en/step/reference/current/glossary.html)

Data in Elasticsearch is organized into indices. Each index is made up of one or more shards. Each shard is an instance of a Lucene index, which you can think of as a self-contained search engine that indexes and handles queries for a subset of the data in an Elasticsearch cluster. Both replica and primary shards can serve querying requests.

#### Shards
Where the data we index is stored. The main difference between a primary and a replica shard is that only the primary shard can accept indexing requests.

#### Index
The name given to a group of shards. It's a collection of documents. It has a mapping which contains a type, which contains properties.

#### Replica
By default, Elasticsearch creates five primary shards and one replica for each index. This means that each index will consist of five primary shards, and each shard will have one copy. Replicas are primarily for search performance, and a user can add or remove them at any time. 

