<?php
namespace Drupal\datalayer\Tests;

class DataLayerMockFieldInfo {

  /**
   * Get field map.
   */
  public function getFieldMap() {
    return array(
      'comment_body' => array(
        'bundles' => array(
          'comment' => array(
            0 => 'comment_node_page',
            1 => 'comment_node_article',
          ),
        ),
        'type' => 'text_long',
      ),
      'body' => array(
        'bundles' => array(
          'node' => array(
            0 => 'page',
            1 => 'article',
          ),
        ),
        'type' => 'text_with_summary',
      ),
      'field_tags' => array(
        'bundles' => array('node' => array(0 => 'article')),
        'type' => 'taxonomy_term_reference',
      ),
      'field_image' => array(
        'bundles' => array('node' => array(0 => 'article')),
        'type' => 'image',
      ),
    );
  }
}
