<?php

namespace Drupal\cleco_migrations\Helper\Traits;

/**
 * Trait for migration.
 */
trait MigrationHelperTrait {

  /**
   * Get hash data.
   *
   * @param mixed $data
   *   Data to be processed.
   *
   * @return string
   *   Return hashed data.
   */
  public function getHashKey($data) {
    $value = (!is_array($data)) ? (array) $data : $data;
    return hash('sha256', serialize(array_map('strval', $value)));
  }

  /**
   * Process multiple paragraph.
   *
   * @param int $tid
   *   Test to be processed.
   * @param string $source_id
   *   Sourceid of the migration.
   * @param string $migration_id
   *   Name of the migration of source taxonomy.
   */
  public function mapAddedTaxonomyTerm($tid, $source_id, $migration_id) {
    $table = ($migration_id) ? 'migrate_map_' . $migration_id : '';
    $this->connection->insert($table)
      ->fields(
        [
          'sourceid1' => $source_id,
          'destid1' => $tid,
          'source_ids_hash' => $this->getHashKey($source_id),
        ]
      )->execute();
  }

  /**
   * Get migrated taxonomy tid.
   *
   * @param string $source_id1
   *   Test to be processed.
   * @param string $migration_id
   *   Sourceid of the migration.
   *
   * @return int
   *   Returns migrated tid.
   */
  public function getMigratedTaxonomyTid($source_id1, $migration_id) {
    $tid = NULL;
    $table = ($migration_id) ? 'migrate_map_' . $migration_id : '';
    $query = $this->connection->select($table, 't');
    $query->addField('t', 'destid1');
    $query->condition('t.sourceid1', $source_id1, '=');
    $tid = $query->execute()->fetchField();
    return $tid;
  }

  /**
   * Update hash information.
   *
   * @param string $hash_key
   *   Hash data of the image.
   * @param string $sourceid1
   *   Source id.
   * @param int $destid1
   *   Destination id.
   * @param string $migration_id
   *   Migraion instance.
   * @param string $source_row_status
   *   Migraion source row status.
   */
  public function updateMigrationRecord($hash_key, $sourceid1, $destid1, $migration_id, $source_row_status = 0) {
    $table = ($migration_id) ? 'migrate_map_' . $migration_id : '';
    if (empty($table)) {
      return;
    }
    $this->connection->merge($table)
      ->key('source_ids_hash', $hash_key)
      ->fields([
        'source_ids_hash' => $hash_key,
        'sourceid1' => $sourceid1,
        'destid1' => $destid1,
        'source_row_status' => $source_row_status,
      ])
      ->execute();
  }

  /**
   * List of valid atrribite ID.
   *
   * @return mixed
   *   Returns attribute ids.
   */
  public function getAttributeList() {
    return [
      "ATT756190",
      "ATT665413",
      "ATT670148",
      "ATT26930",
      "asset.size",
      "Footnotes",
      "ATT678745",
      "asset.uploaded",
      "ATT713553",
      "ATT28176",
      "ATT713554",
      "ATT28175",
      "ATT28174",
      "ATT26835",
      "ATT713555",
      "ATT28172",
      "ATT713556",
      "ATT835",
      "ATT688984",
      "ATT669469",
      "ATT145",
      "ATT17992",
      "ATT682309",
      "ATT661950",
      "ATT728192",
      "ATT682308",
      "ATT661599",
      "ATT661600",
      "ATT713557",
      "Brand",
      "ATT713558",
      "ATT661601",
      "ATT661602",
      "ATT661603",
      "asset.extension",
      "ATT661604",
      "ATT584578",
      "ATT661605",
      "asset.mime-type",
      "ATT661607",
      "ATT167",
      "asset.compression",
      "ATT22387",
      "ATT670152",
      "asset.xdpi",
      "ATT675755",
      "asset.class",
      "ATT19744",
      "asset.pixel-width",
      "asset.colorspace",
      "ATT670145",
      "asset.profile",
      "ATT19743",
      "asset.ydpi",
      "ATT180",
      "asset.format",
      "ATT182",
      "ATT662514",
      "ATT727414",
      "ATT583306",
      "ATT28568",
      "ATT664414",
      "ATT664415",
      "ATT670156",
      "ATT664082",
      "ATT584357",
      "ATT670154",
      "ATT670486",
      "asset.pixel-height",
      "ListPrice",
      "ATT583311",
      "ATT664420",
      "ATT670485",
      "ATT664418",
      "ATT664419",
      "CustomerPrice",
      "ATT664417",
      "asset.width",
      "ATT664416",
      "ATT672487",
      "ATT670157",
      "ATT664425",
      "ATT583308",
      "ATT670153",
      "asset.height",
      "ATT664424",
      "ATT28567",
      "ATT672488",
      "ATT670159",
      "ATT667662",
      "asset.depth",
      "ATT664422",
      "ATT664423",
      "ATT499",
      "ATT670158",
      "ATT664421",
      "asset.samples",
      "ATT498",
      "ATT664431",
      "ATT664429",
      "ATT26811",
      "ATT584360",
      "ATT16699",
      "asset.colors",
      "ATT16698",
      "ATT664428",
      "ATT16697",
      "ATT664426",
      "ATT664427",
      "ATT16696",
      "ATT665661",
      "ATT16695",
      "ATT664436",
      "ATT26649",
      "ATT665662",
      "ATT664434",
      "ATT16694",
      "ATT664433",
      "ATT16693",
      "ATT16692",
      "ATT583314",
      "ATT665660",
      "ATT556",
      "ATT26863",
      "ATT16691",
      "ATT727185",
      "ATT664086",
      "ATT16689",
      "ATT727184",
      "ATT664440",
      "ATT16688",
      "ATT727183",
      "ATT584366",
      "ATT664439",
      "ATT229",
      "ATT584365",
      "asset.dsc-conformance",
      "ATT674089",
      "asset.creator",
      "ATT664087",
      "ATT584368",
      "ATT664444",
      "ATT664442",
      "ATT664443",
      "ATT583320",
      "ATT583321",
      "ATT584369",
      "asset.filename",
      "ATT584371",
      "ATT664089",
      "asset.extra-samples",
      "ATT26838",
      "ATT584375",
      "ATT584374",
      "ATT726473",
      "ATT584377",
      "ATT584376",
      "ATT726472",
      "ATT665609",
      "ATT665400",
      "ATT584379",
      "ATT726477",
      "ATT22563",
      "ATT726476",
      "ATT665611",
      "ATT728236",
      "ATT726475",
      "ATT584380",
      "ATT665612",
      "ATT726474",
      "ATT665404",
      "ATT665617",
      "ATT665403",
      "ATT665402",
      "ATT665615",
      "ATT665401",
      "SAP_SALES_ORG_STATUS",
      "ATT675402",
      "ATT889",
      "ATT665408",
      "ATT665621",
      "ATT665407",
      "ATT665618",
      "ATT665406",
      "ATT665619",
      "ATT665405",
      "ATT665620",
      "ATT665411",
      "Pro Landing Body",
      "ATT18209",
      "ATT728082",
      "ATT665410",
      "ATT22085",
      "ATT665409",
      "ATT22087",
      "ATT665414",
      "ATT22086",
      "ATT617",
      "ATT22090",
      "ATT22088",
      "ATT618",
      "ATT22089",
      "ATT665419",
      "ATT665417",
      "ATT727431",
      "ATT665416",
      "ATT661684",
      "ATT664878",
      "ATT665422",
      "ATT575",
      "ATT242",
      "ATT665421",
      "ATT243",
      "ATT665420",
      "ATT665418",
      "ATT663751",
      "ATT663752",
      "ATT22507",
      "ATT26650",
      "Catalog Number",
      "ATT728124",
      "ATT26820",
      "ATT17322",
      "ATT17321",
      "ATT664790",
      "ATT678997",
      "ATT17319",
      "ATT17318",
      "ATT17317",
      "ATT665259",
      "ATT727457",
      "ATT16675",
      "ATT665424",
      "ATT16674",
      "ATT664961",
      "ATT16672",
      "ATT665264",
      "ATT665263",
      "ATT16670",
      "ATT728114",
      "ATT665262",
      "ATT728113",
      "ATT665261",
      "ATT619",
      "ATT728135",
      "ATT679076",
      "ATT665265",
      "ATT728137",
      "asset.pages",
      "ATT26657",
      "ATT662376",
      "ATT728136",
      "ATT670146",
      "ATT670147",
      "ATT662377",
      "ATT662378",
      "ATT728132",
      "ATT662379",
      "ATT662381",
      "ATT728131",
      "ATT662382",
      "ATT728134",
      "ATT662384",
      "ATT728133",
      "ATT584484",
      "ATT678643",
      "ATT584483",
      "ATT674416",
      "ATT727474",
      "ATT350",
      "ATT584487",
      "ATT678639",
      "ATT668491",
      "ATT584486",
      "ATT678640",
      "ATT678642",
      "ATT672392",
      "ATT670151",
      "ATT699",
      "ATT727466",
      "ATT698650",
      "ATT727471",
      "ATT661691",
      "ATT364",
      "ATT661692",
      "asset.preview-format",
      "ATT17711",
      "ATT584466",
      "ATT676136",
      "ATT584465",
      "SAP Material Status",
      "ATT662540",
      "ATT665650",
      "ATT340",
      "ATT665651",
      "ATT344",
      "ATT662541",
      "ATT345",
      "ATT665654",
      "ATT665655",
      "ATT672817",
      "ATT675745",
      "ATT919",
      "ATT665652",
      "ATT665653",
      "ATT672818",
      "ATT16676",
      "ATT672816",
      "ATT665656",
      "ATT584471",
      "ATT665657",
      "ATT584473",
      "ATT584472",
      "ATT662009",
      "ATT15738",
      "ATT696034",
      "ATT584476",
      "ATT668501",
      "ATT584475",
      "ATT584478",
      "ATT584477",
      "ATT696033",
      "ATT696024",
      "ATT712",
      "ATT711",
      "ATT100",
      "ATT948",
      "ATT101",
      "ATT102",
      "ATT922",
      "ATT375",
      "ATT104",
      "ATT103",
      "ATT672823",
      "ATT105",
      "ATT921",
      "ATT106",
      "ATT672824",
      "ATT108",
      "ATT107",
      "ATT669519",
      "ATT27351",
      "ATT696018",
      "Display Sequence",
      "ATT109",
      "ATT672821",
      "DimensionA",
      "ATT696019",
      "ATT584387",
      "ATT110",
      "ATT672822",
      "ATT111",
      "Table Sort Order",
      "ATT729",
      "ATT672819",
      "ATT384",
      "Table Display Sequence",
      "ATT732",
      "ATT672820",
      "ATT662011",
      "ATT662010",
      "ATT781",
      "ATT674031",
      "ATT728177",
      "ATT674030",
      "ATT660049",
      "ATT439",
      "ATT661955",
      "ATT28149",
      "ATT674029",
      "ATT670298",
      "ATT713522",
      "ATT674028",
      "ATT662434",
      "Table Name",
      "ATT660051",
      "ATT728166",
      "ATT660052",
      "ATT802",
      "ATT17332",
      "ATT664496",
      "Table Sort Order Set Components",
      "ATT938",
      "ATT659132",
      "ATT420",
      "ATT659133",
      "ATT675633",
      "ATT425",
      "ATT674034",
      "ATT585085",
      "asset.format-version",
      "ATT665438",
      "ATT434",
      "ATT674033",
    ];
  }

}
