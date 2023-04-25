<?php

namespace Drupal\campbell_common\Controller;

use Drupal\Core\Controller\ControllerBase;

/**
 * Returns responses for Campbell Common routes.
 */
class CampbellCommonController extends ControllerBase {

  /**
   * Builds the response.
   */
  public function products() {

    // Render a taxonomy blocks.
    $welded_chain = views_embed_view('taxonomy_menu', 'welded_chain');
    $forged_fittings = views_embed_view('taxonomy_menu', 'forged_fittings');
    $cable_and_wire_rope = views_embed_view('taxonomy_menu', 'cable_and_wire_rope');
    $weldless_chain = views_embed_view('taxonomy_menu', 'weldless_chain');
    $hobby_craft_and_deco = views_embed_view('taxonomy_menu', 'hobby_craft_and_deco');
    $assemblies = views_embed_view('taxonomy_menu', 'assemblies');
    $overhead_lifting = views_embed_view('taxonomy_menu', 'overhead_lifting');
    $lifting_clamps = views_embed_view('taxonomy_menu', 'lifting_clamps');
    $blocks = views_embed_view('taxonomy_menu', 'blocks');
    $accessories = views_embed_view('taxonomy_menu', 'accessories');
    $pre_cut_packaged = views_embed_view('taxonomy_menu', 'pre_cut_packaged');
    $chain_and_cable_cutters = views_embed_view('taxonomy_menu', 'chain_and_cable_cutters');
    // Render a Feature block.
    $entityTypeManager = $this->entityTypeManager();
    $featuredblock = views_embed_view('featured_menu_product', 'featureed_product_menu');

    // Return a render array that includes both views.
    return [
      '#theme' => 'taxonomy_terms_view',
      '#welded_chain' => $welded_chain,
      '#forged_fittings' => $forged_fittings,
      '#cable_and_wire_rope' => $cable_and_wire_rope,
      '#weldless_chain' => $weldless_chain,
      '#hobby_craft_and_deco' => $hobby_craft_and_deco,
      '#assemblies' => $assemblies,
      '#overhead_lifting' => $overhead_lifting,
      '#lifting_clamps' => $lifting_clamps,
      '#blocks' => $blocks,
      '#accessories' => $accessories,
      '#pre_cut_packaged' => $pre_cut_packaged,
      '#chain_and_cable_cutters' => $chain_and_cable_cutters,
      '#featuredblock' => $featuredblock,
    ];
  }

}
