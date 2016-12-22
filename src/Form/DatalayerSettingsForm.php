<?php

/**
 * @file
 * Contains \Drupal\datalayer\Form\DatalayerSettingsForm.
 */

namespace Drupal\datalayer\Form;

use Drupal\Core\Form\ConfigFormBase;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Render\Element;
use Drupal\taxonomy\Entity\Vocabulary;
use Drupal\Core\Url;

class DatalayerSettingsForm extends ConfigFormBase {

  /**
   * {@inheritdoc}
   */
  public function getFormId() {
    return 'settings_form';
  }

  /**
   * {@inheritdoc}
   */
  public function submitForm(array &$form, FormStateInterface $form_state) {
    $config = $this->config('datalayer.settings');
    $config->set('add_page_meta', $form_state->getValue('add_page_meta'))
      ->set('output_terms', $form_state->getValue('output_terms'))
      ->set('output_fields', $form_state->getValue('output_fields'))
      ->set('lib_helper', $form_state->getValue('lib_helper'))
      ->set('entity_meta', $form_state->getValue('global_entity_meta'))
      ->set('vocabs', $form_state->getValue('vocabs'))
      ->set('expose_user_details', $form_state->getValue('expose_user_details'))
      ->set('expose_user_details_roles', $form_state->getValue('expose_user_details_roles'))
      ->save();

    parent::submitForm($form, $form_state);
  }

  /**
   * {@inheritdoc}
   */
  protected function getEditableConfigNames() {
    return ['datalayer.settings'];
  }

  public function buildForm(array $form, FormStateInterface $form_state) {
    // Setup vocabs.
    $vocabs = Vocabulary::loadMultiple();
    $v_options = [];
    foreach ($vocabs as $v) {
      $v_options[$v->id()] = $v->label();
    }
    $datalayer_settings = $this->config('datalayer.settings');

    // Get available meta data.
    $meta_data = _datalayer_collect_meta_properties();

    $form['global'] = [
      '#type' => 'fieldset',
      '#title' => t('Global'),
      '#collapsible' => FALSE,
      '#collapsed' => FALSE,
    ];
    $form['global']['add_page_meta'] = [
      '#type' => 'checkbox',
      '#title' => t('Add entity meta data to pages'),
      '#default_value' => $datalayer_settings->get('add_page_meta'),
    ];
    $form['global']['output_terms'] = [
      '#type' => 'checkbox',
      '#states' => [
        'enabled' => [
          ':input[name="datalayer_add_page_meta"]' => [
            'checked' => TRUE
            ]
          ]
        ],
      '#title' => t('Include taxonomy terms'),
      '#default_value' => $datalayer_settings->get('output_terms'),
    ];
    $form['global']['output_fields'] = [
      '#type' => 'checkbox',
      '#description' => t('Exposes a checkbox on field settings forms to expose data.'),
      '#title' => t('Include enabled field values'),
      '#default_value' => $datalayer_settings->get('output_fields'),
    ];
    $form['global']['lib_helper'] = [
      '#type' => 'checkbox',
      '#title' => t('Include "data layer helper" library'),
      '#default_value' => $datalayer_settings->get('lib_helper'),
      '#description' => t('Provides the ability to process messages passed to the dataLayer. See: :link on GitHub.', [
        ':link' => \Drupal::l(t('data-layer-helper'), Url::fromUri('https://github.com/google/data-layer-helper'))
        ]),
    ];

    $form['entity_meta'] = [
      '#type' => 'fieldset',
      '#title' => t('Entity meta data'),
      '#collapsible' => TRUE,
      '#collapsed' => FALSE,
      '#description' => t('The meta data details to ouput for client-side consumption. Marking none will output everything available.'),
    ];
    $form['entity_meta']['global_entity_meta'] = [
      '#type' => 'checkboxes',
      '#states' => [
        'enabled' => [
          ':input[name="datalayer_add_page_meta"]' => [
            'checked' => TRUE
            ]
          ]
        ],
      '#title' => '',
      '#default_value' => $datalayer_settings->get('entity_meta'),
      '#options' => array_combine($meta_data, $meta_data),
    ];

    $form['vocabs'] = [
      '#type' => 'fieldset',
      '#title' => t('Taxonomy'),
      '#collapsible' => TRUE,
      '#collapsed' => FALSE,
      '#description' => t('The vocabularies which should be output within page meta data. Marking none will output everything available.'),
    ];
    $form['vocabs']['vocabs'] = [
      '#type' => 'checkboxes',
      '#states' => [
        'enabled' => [
          ':input[name="datalayer_output_terms"]' => [
            'checked' => TRUE
            ]
          ]
        ],
      '#title' => '',
      '#default_value' => $datalayer_settings->get('vocabs'),
      '#options' => $v_options,
    ];

    $form['user'] = [
      '#type' => 'fieldset',
      '#title' => t('User Details'),
      '#collapsible' => TRUE,
      '#collapsed' => FALSE,
      '#description' => t('Details about the current user can be output to the dataLayer.'),
    ];

    $form['user']['expose_user_details'] = [
      '#type' => 'textarea',
      '#title' => t('Expose user details'),
      '#default_value' => $datalayer_settings->get('expose_user_details'),
      '#description' => t('Pages that should expose active user details to the dataLayer. Leaving empty will expose nothing.'),
    ];

    $user_roles =  user_roles(TRUE);
    $role_options = [];
    foreach ($user_roles as $id => $role) {
      $role_options[$id] = $role->label();
    }
    $form['user']['expose_user_details_roles'] = [
      '#type' => 'checkboxes',
      '#options' => $role_options,
      '#multiple' => TRUE,
      '#title' => t('Expose user roles'),
      '#default_value' => $datalayer_settings->get('expose_user_details_roles'),
      '#description' => t('Roles that should expose active user details to the dataLayer. Leaving empty will expose to all roles.'),
    ];

    return parent::buildForm($form, $form_state);
  }

}
