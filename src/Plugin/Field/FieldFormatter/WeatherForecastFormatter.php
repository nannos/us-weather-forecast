<?php

/**
 * @file
 * Contains \Drupal\us_weather_forecast\Plugin\field\FieldFormatter\WeatherForecastFormatter.
 */

namespace Drupal\us_weather_forecast\Plugin\Field\FieldFormatter;

use Drupal\Core\Field\FieldDefinitionInterface;
use Drupal\Core\Field\FieldItemListInterface;
use Drupal\Core\Field\FormatterBase;
use Drupal\us_weather_forecast\UsWeatherForecastManager;
use Drupal\Core\Plugin\ContainerFactoryPluginInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Plugin implementation of the 'weather_forecast_formatter' formatter.
 *
 * @FieldFormatter(
 *   id = "weather_forecast_formatter",
 *   label = @Translation("Weather forecast by city"),
 *   field_types = {
 *     "string",
 *     "string_long",
 *     "text",
 *     "text_long",
 *     "text_with_summary",
 *   }
 * )
 */
class WeatherForecastFormatter extends FormatterBase implements ContainerFactoryPluginInterface {
  /**
   * Methods to make an API call and tool to handle the output.
   *
   * @var \Drupal\us_weather_forecast\UsWeatherForecastManager
   */
  protected $usWeatherForecastManager;

  /**
   * WeatherForecast formatter constructor.
   *
   * @param string $plugin_id
   *   The plugin_id for the formatter.
   * @param mixed $plugin_definition
   *   The plugin implementation definition.
   * @param \Drupal\Core\Field\FieldDefinitionInterface $field_definition
   *   The definition of the field to which the formatter is associated.
   * @param array $settings
   *   The formatter settings.
   * @param string $label
   *   The formatter label display setting.
   * @param string $view_mode
   *   The view mode.
   * @param array $third_party_settings
   *   Any third party settings settings.
   * @param \Drupal\us_weather_forecast\UsWeatherForecastManager $us_weather_forecast_manager
   *   Methods to make an API call and tool to handle the output.
   */   
  public function __construct($plugin_id, $plugin_definition, FieldDefinitionInterface $field_definition, array $settings, $label, $view_mode, array $third_party_settings, UsWeatherForecastManager $us_weather_forecast_manager) {    
    parent::__construct($plugin_id, $plugin_definition, $field_definition, $settings, $label, $view_mode, $third_party_settings);
    $this->usWeatherForecastManager = $us_weather_forecast_manager;
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container, array $configuration, $plugin_id, $plugin_definition) {
    return new static(
      $plugin_id,
      $plugin_definition,
      $configuration['field_definition'],
      $configuration['settings'],
      $configuration['label'],
      $configuration['view_mode'],
      $configuration['third_party_settings'],
      $container->get('us_weather_forecast.manager')
    );
  }  
  
  /**
   * {@inheritdoc}
   */
  public function settingsSummary() {
    $summary = [];
    $summary[] = t('Retrieve weather forecast for a US city.');
  
    return $summary;
  }
  
  /**
   * {@inheritdoc}
   */
  public function viewElements(FieldItemListInterface $items, $langcode) {
    $elements = [];

    foreach ($items as $delta => $item) {
      if ($this->getSetting('use_summary') && !empty($item->summary)) {
        $city = $item->summary;
      }
      else {
        $city = $item->value;
      }
    
      if ($weather_forecast = $this->usWeatherForecastManager->getWeatherForecast($city)) {    
        $timezone = timezone_name_from_abbr('', $weather_forecast->city->timezone, 0);    
        $forecast_list = $weather_forecast->list;
        
        $elements[$delta] = [
          '#theme' => 'us_weather_forecast',
          '#city' => $weather_forecast->city->name,      
          '#timezone' => $timezone,
          '#forecast_list' => $forecast_list,
          '#attached' => [
            'library' => [
              'us_weather_forecast/us_weather_forecast_theme',
            ],
          ],
          '#cache' => array('max-age' => 0),
        ];
      }
    }

    return $elements;
  }
}