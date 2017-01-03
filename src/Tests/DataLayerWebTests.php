<?php
namespace Drupal\datalayer\Tests;

use Drupal\simpletest\WebTestBase;

/**
 * @file
 * Tests the functionality of the DataLayer module.
 */

class DataLayerWebTests extends WebTestBase {

  /**
   * Modules to install.
   *
   * @var array
   */
  public static $modules = ['node', 'datalayer'];

  /**
   * {@inheritdoc}
   */
  public static function getInfo() {
    return array(
      'name' => 'DataLayer',
      'description' => 'Tests to ensure data makes it client-side.',
      'group' => 'DataLayer',
    );
  }

  /**
   * {@inheritdoc}
   */
  public function setUp() {
    parent::setUp();
    $admin_user = $this->drupalCreateUser(array(
      'access administration pages',
      'administer nodes',
      'administer site configuration',
    ));
    $this->drupalLogin($admin_user);

  }

  /**
   * Test DataLayer variable output by name.
   *
   * This will be helpful when/if the variable name can be customized.
   * @see https://www.drupal.org/node/2300577
   */
  public function testDataLayerVariableOutputByName() {
    $output = $this->drupalGet('node');
    $this->assertRaw('dataLayer = [{');
  }

  /**
   * Test DataLayer JS language settings.
   */
  public function testDataLayerJsLanguageSettings() {
    $output = $this->drupalGet('node');
    $this->assertRaw('"dataLayer":{"defaultLang"');
  }
}
