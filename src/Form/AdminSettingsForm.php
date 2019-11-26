<?php
/**
 * @file
 * Contains \Drupal\us_weather_forecast\Form\AdminSettingsForm.
 */

namespace Drupal\us_weather_forecast\Form;

use Drupal\Core\Config\ConfigFactoryInterface;
use Drupal\Core\Form\ConfigFormBase;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Url;
use Drupal\Core\Link;

/**
 * Defines a form to configure module settings.
 */
class AdminSettingsForm extends ConfigFormBase {
  /**
   * {@inheritdoc}
   */
  public function getFormId() {
    return 'us_weather_forecast_admin_settings';
  }
  
  /**
   * {@inheritdoc}
   */
  protected function getEditableConfigNames() {
    return ['us_weather_forecast.admin_settings'];
  }  

  /**
   * {@inheritdoc}
   */
  public function buildForm(array $form, FormStateInterface $form_state) {
    $form = parent::buildForm($form, $form_state);

    // Get config settings.
    $config = $this->configFactory->get('us_weather_forecast.admin_settings');

    // Create the OpenWeatherMap Api link.
    $url = Url::fromUri('http://www.openweathermap.org');
    $link = Link::fromTextAndUrl('OpenWeatherMap.org', $url)->toString();

    $form['api_key'] = [
      '#type' => 'textfield',
      '#title' => t('OpenWeather Api Key'),
      '#description' => t('Get your Api key at %link', ['%link' => $link]),
      '#default_value' => $config->get('api_key'),
      '#required' => TRUE,
    ];

    return $form;
  }

  /**
   * {@inheritdoc}
   */
  public function submitForm(array &$form, FormStateInterface $form_state) {
    $config = $this->configFactory->getEditable('us_weather_forecast.admin_settings');
    
    $config
      ->set('api_key', $form_state->getValue('api_key'))
      ->save();

    parent::submitForm($form, $form_state);
  }
}