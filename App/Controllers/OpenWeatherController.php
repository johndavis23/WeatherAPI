<?php
namespace App\Controllers;

use App\Classes\Controller;
use App\Classes\View;
use App\Classes\Model;
use App\Util\Util;

/* our external imports*/
use RestClient;
use Carbon\Carbon;


class OpenWeatherController extends Controller
{
    protected $validActions = [
        "Fetch",
    ];


    function Fetch()
    {
        $API_LIMIT = getenv('API_LIMIT');
        $API_KEY   = getenv('OPEN_MAPS_API_KEY');

        $api = new RestClient(
            [
                'base_url'   => "http://api.openweathermap.org/data/2.5/",
                'parameters' => [
                    "APPID"  => $API_KEY,
                    'units'  => 'imperial'
                ]
            ]
        );

        $model = new Model('weather_data');

        global $OPEN_WEATHER_ZIPS;

        //NOTE:   A single ID counts as a one API call as far as our limits go, so
        //        breaking up the calls will have more to do with performance and granularity.
        //        It might be worthwhile to look into using the multiple zip call in the future
        //        https://openweathermap.org/current#bulk
        foreach ($OPEN_WEATHER_ZIPS as $zip) {
            $result = $api->get(
                "weather",
                [
                    'zip'  => $zip,
                    "us"
                ]
            );

            if ($result->info->http_code == 200) {
                $weather = $result->decode_response();

                $data = $this->getDataFromDecodedResponse($weather, $zip);

                $where = ['zip' => $data['zip']];

                if (count($model->read($where))) {
                    $model->update($data, $where);
                } else {
                    $model->create($data);
                }
            }
        }
    }

    /**
     * @param $weather
     * @return array
     */
    public function getDataFromDecodedResponse($weather, $zip = '')
    {
        /* Business requirements 2017
            - Zip code,
            - General weather conditions (e.g. sunny, rainy, etc),
            - Atmospheric pressure,
            - Temperature (in Fahrenheit),
            - Winds (direction and speed),
            - Humidity,
            - Timestamp (in UTC)
        */

        $main = $weather->main ? $weather->main : false;
        $wind = $weather->wind ? $weather->wind : false;
        $weather_field = $weather->weather;

        $zip         = $zip;
        $weather     = $weather_field[0]->main;
        $wind_speed  = $wind ? $wind->speed : '';
        $wind_deg    = $wind ? $wind->deg : '';
        $temperature = $main->temp;
        $humidity    = $main->humidity;
        $pressure    = $main->pressure;


        $data = [
            'zip'           => $zip,
            'description'   => $weather,
            'pressure'      => $pressure,
            'fahrenheit'    => $temperature,
            'humidity'      => $humidity,
            'wind_speed'    => $wind_speed,
            'wind_deg'      => $wind_deg,
            'updated'       => Carbon::now()
        ];
        return $data;
    }
}
