<?php
/**
 * @file
 * Contains Drupal\us_weather_forecast\UsWeatherForecastManager.
 */

namespace Drupal\us_weather_forecast;

use Drupal\Core\Config\ConfigFactoryInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;
use GuzzleHttp\Exception\GuzzleException;
use GuzzleHttp\ClientInterface;

/**
 * Methods to make an API call and tool to handle the output.
 */
class UsWeatherForecastManager {

  /**
   * The configuration factory.
   *
   * @var \Drupal\Core\Config\ConfigFactoryInterface
   */
  protected $configFactory;
  
  /**
   * The Guzzle Http Client.
   *
   * @var \GuzzleHttp\ClientInterface
   */
  protected $httpClient;

  /**
   * UsWeather constructor.
   *
   * @param \Drupal\Core\Config\ConfigFactoryInterface $configFactory
   * The configuration factory.
   * @param \GuzzleHttp\Client $http_client
   * A client to make http requests.
   */
  public function __construct(ConfigFactoryInterface $config_factory, ClientInterface $http_client) {
    $this->config = $config_factory->getEditable('us_weather_forecast.admin_settings');
    $this->httpClient = $http_client;
  }

  /**
   * Make a request to the OpenWeather Forecast Api and return it as an object.
   *
   * @param string $city
   *   City name.
   *
   * @return array
   *   An array containing weather data.
   */
  public function getWeatherForecast($city) {
    $data = [];

    try {  
      // Define Api url.
      $api_url = 'http://api.openweathermap.org/data/2.5/forecast/';
      
      $response = $this->httpClient->request('GET', 
        $api_url,
        [
          'query' => [
            'q' => $city . ',us',
            'appid' => $this->config->get('api_key'),
            'units' => 'imperial',
          ],
        ]
      );

      $response_data = $response->getBody();
      $data = json_decode($response_data);       
    }
    catch (GuzzleException $e) {
      watchdog_exception('us_weather_forecast', $e);
      return FALSE;
    }

    return $data;  
  }
}