<?php
namespace Drupal\datalayer\Tests;

class DataLayerMockEntityController {

  /**
   * Load.
   */
  public function load() {
    return array(
      1 => (object) array(
        'tid' => 1,
        'vid' => 1,
        'name' => 'someTag',
        'vocabulary_machine_name' => 'tags',
      ),
    );
  }
}
