<?php
namespace Drupal\datalayer\Tests;

use Drupal\simpletest\KernelTestBase;
use Drupal\Tests\UnitTestCase;
use Drupal\node\Entity\Node;
use Drupal\user\Entity\User;
use Drupal\Core\Language\LanguageInterface;
use Drupal\Core\Url;
use Symfony\Cmf\Component\Routing\RouteObjectInterface;
use Symfony\Component\Routing\Route;

/**
 * @file
 * Tests the functionality of the DataLayer module.
 */

class DataLayerUnitTests extends KernelTestBase {

  /**
   * Modules to install.
   *
   * @var array
   */
  public static $modules = ['datalayer', 'system', 'user', 'node', 'taxonomy', 'text'];

  /**
   * {@inheritdoc}
   */
  public static function getInfo() {
    return array(
      'name' => 'DataLayer Unit Tests',
      'description' => 'Tests to ensure data makes it client-side.',
      'group' => 'DataLayer',
    );
  }

  /**
   * {@inheritdoc}
   */
  public function setUp() {
    parent::setUp();
    $this->installConfig(['system']);
  }

  /**
   * Test DataLayer Defaults function.
   */
  public function testDataLayerDefaults() {
    // $this->setupMockLanguage();
    $this->assertEqual(
      array('drupalLanguage' => $this->defaultLanguageData()['id'], 'drupalCountry' => $this->config('system.date')->get('country.default')),
      _datalayer_defaults()
    );
  }

  /**
   * Test DataLayer Add Will Add Data.
   */
  public function testDataLayerAddWillAddData() {
    $this->setupEmptyDataLayer();
    $this->assertEqual(
      array('foo' => 'bar'),
      datalayer_add(array('foo' => 'bar'))
    );
  }

  /**
   * Test DataLayer Add Does Not Overwrite By Default.
   */
  public function testDataLayerAddDoesNotOverwriteByDefault() {
    $this->setupEmptyDataLayer();
    datalayer_add(array('foo' => 'bar'));
    $this->assertEqual(
      array('foo' => 'bar'),
      datalayer_add(array('foo' => 'baz'))
    );
  }

  /**
   * Test DataLayer Add Will Overwrite With Flag.
   */
  public function testDataLayerAddWillOverwriteWithFlag() {
    $this->setupEmptyDataLayer();
    datalayer_add(array('foo' => 'bar'));
    $this->assertEqual(
      array('foo' => 'baz'),
      datalayer_add(array('foo' => 'baz'), TRUE)
    );
  }

  /**
   * Test DataLayer Menu Get Any Object.
   *
   * Returns False Without Load Functions.
   */
  public function testDataLayerMenuGetAnyObjectReturnsNullWithoutContentEntityInterface() {
    $item = $this->setupMockNode();
    $result = _datalayer_menu_get_any_object();
    $this->assertNull($result);
  }

  /**
   * Test DataLayer Menu Get Any Object Returns Object.
   */
  public function testDataLayerMenuGetAnyObjectReturnsObject() {
    $this->setupMockRouteMatch();
    $object = _datalayer_menu_get_any_object();
    $this->assertTrue(is_object($object));
    $this->assertEqual($object->getEntityTypeId(), 'node');
  }

  /**
   * Test DataLayer Get Entity Terms Returns Empty Array.
   */
  public function testDataLayerGetEntityTermsReturnsEmptyArray() {
    $item = $this->setupMockNode();
    $this->setupMockFieldMap();
    $terms = _datalayer_get_entity_terms($item);
    $this->assertEqual(array(), $terms);
  }

  /**
   * Test DataLayer Get Entity Terms Returns Term Array.
   */
  public function testDataLayerGetEntityTermsReturnsTermArray() {
    $item = $this->setupMockNode();
    $this->setupMockEntityTerms();
    $terms = _datalayer_get_entity_terms($item);
    $this->assertEqual(array('tags' => array(1 => 'someTag')), $terms);
  }

  /**
   * Test DataLayer Get Entity Terms Returns Entity Data Array.
   */
  public function testDataLayerGetEntityDataReturnsEntityDataArray() {
    $this->setupEmptyDataLayer();
    $item = $this->setupMockNode();
    $this->setupMockEntityTerms();
    $entity_data = _datalayer_get_entity_data($item);
    $this->assertEqual(
      $this->getExpectedEntityDataArray(),
      $entity_data
    );
  }

  /**
   * Setup user.
   */
  public function setupMockUser() {
    $edit = array(
      'uid'      => 1,
      'name'     => 'admin',
      'password' => 'password',
      'langcode' => LanguageInterface::LANGCODE_NOT_SPECIFIED,
    );

    $user = User::create($edit);

    return $user;
  }

  /**
   * Setup language.
   */
  public function setupMockLanguage($lang = 'en') {
    $language = \Drupal::languageManager()->getCurrentLanguage();
    $language->getId();
  }

  /**
   * Setup empty datalayer.
   */
  public function setupEmptyDataLayer() {
    $data = &drupal_static('datalayer_add', array());
  }

  /**
   * Setup mock node.
   */
  public function setupMockNode() {
    $item = &drupal_static(__FUNCTION__);
    if (!$item) {
      $user = $this->setupMockUser();
      // Create a node.
      $edit = array(
        'uid'      => $user,
        'name'     => 'admin',
        'type'     => 'article',
        'langcode' => LanguageInterface::LANGCODE_NOT_SPECIFIED,
        'title'    => 'testing_transaction_exception',
      );
      $item = Node::create($edit);
    }

    return $item;
  }

  /**
   * Setup Mock RouteMatch.
   */
  public function setupMockRouteMatch() {
    $item = $this->setupMockNode();
    $request = &drupal_static(__FUNCTION__);
    if (!$request) {
      $request = \Drupal::request()->create('/node/1', 'GET', array('node' => $item));
      $request->attributes->set(RouteObjectInterface::ROUTE_OBJECT, new Route('/node/{node}', array('node' => 1)));
      $request->attributes->set(RouteObjectInterface::ROUTE_NAME, 'entity.node.canonical');
      $request->attributes->set('node', $item);
      $this->container->get('request_stack')->push($request);
    }
  }

  /**
   * Setup Mock Field Map.
   */
  public function setupMockFieldMap() {
    $field_map = &drupal_static('_field_info_field_cache');
    $field_map = new DataLayerMockFieldInfo();
  }

  /**
   * Setup Mock Field Language.
   */
  public function setupMockFieldLanguage() {
    $field_language = &drupal_static('field_language');
    $field_language = array(
      'node' => array(
        1 => array(
          'en' => array(
            'field_tags' => 'und',
          ),
        ),
      ),
    );
  }

  /**
   * Setup Mock Entity Info.
   */
  public function setupMockEntityInfo() {
    $entity_info = &drupal_static('entity_get_info');
    $entity_info = array(
      'node' => array(
        'entity keys' => array(
          'id' => 'nid',
          'revision' => 'vid',
          'bundle' => 'type',
          'label' => 'title',
          'language' => 'language',
        ),
      ),
      'taxonomy_term' => array(
        'controller class' => 'TaxonomyTermController',
        'base table' => 'taxonomy_term_data',
        'uri callback' => 'taxonomy_term_uri',
        'entity keys' => array(
          'id' => 'tid',
          'bundle' => 'vocabulary_machine_name',
          'label' => 'name',
          'revision' => '',
        ),
        'bundles' => array(
          'tags' => array(
            'label' => 'Tags',
            'admin' => array(
              'path' => 'admin/structure/taxonomy/%taxonomy_vocabulary_machine_name',
              'real path' => 'admin/structure/taxonomy/tags',
              'bundle argument' => 3,
              'access arguments' => array(0 => 'administer taxonomy'),
            ),
          ),
        ),
      ),
    );
  }

  /**
   * Setup Mock Entity Controller.
   */
  public function setupMockEntityController() {
    $entity_contoller = &drupal_static('entity_get_controller');
    $entity_contoller = array(
      'taxonomy_term' => new DataLayerMockEntityController(),
    );
  }

  /**
   * Setup Mock Entity Terms.
   */
  public function setupMockEntityTerms() {
    $this->setupMockFieldMap();
    $this->setupMockLanguage('en');
    $this->setupMockFieldLanguage();
    $this->setupMockEntityInfo();
    $this->setupMockEntityController();
  }

  /**
   * Get expected entity data array.
   */
  public function getExpectedEntityDataArray() {
    return array(
      'entityType' => 'node',
      'entityBundle' => 'article',
      'entityId' => 1,
      'entityLabel' => 'My Article',
      'entityLangcode' => 'und',
      'entityTnid' => 0,
      'entityVid' => 1,
      'entityName' => 'admin',
      'entityUid' => 1,
      'entityCreated' => '1435019805',
      'entityStatus' => 1,
      'entityTaxonomy' => array(
        'tags' => array(
          1 => 'someTag',
        ),
      ),
    );
  }

}
