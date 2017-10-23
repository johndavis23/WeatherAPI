<?php

use App\Classes\Model;
use App\Controllers\OpenWeatherController;

class Test extends PHPUnit\Framework\TestCase
{

    /*

    Test our model functions; this code was pre-existing, but requires at least minimal
    test coverage for what we are using from it.

    TODO: Switch from default database over to a test database using third
    constructor parameters ie Model('test','id','testing') and adjust migrations;

    */

    public function testCreate()
    {
        try {
            $model = new Model('test_table', 'id');
            $model->create(
                [
                    'id'   => 1,
                    'test' => "Testing",
                ]
            );
        } catch (Exception $e) {
            $this->fail("Failed Creation: ".$e->getMessage());
        }
    }

    public function testRead()
    {
        try {
            $model = new Model('test_table', 'id');
            $results = $model->read(
                [
                    'test' => "Testing",
                ]
            );
            if ($results[0]['test'] !== "Testing")
                $this->fail("Failed Reading Value 'Testing'");
        } catch (Exception $e) {
            $this->fail("Failed Reading: ".$e->getMessage());
        }
    }


    public function testUpdate()
    {
        try {
            $model = new Model('test_table', 'id');
            $results = $model->read(
                [
                    'test' => "Testing",
                ]
            );
            if ($results[0]['test'] !== "Testing")
                $this->fail("Failed Reading Value 'Testing'");

            $model->update(['test'=>'Unit'],['test'=>"Testing"]);

            $results = $model->read(
                [
                    'test' => "Unit",
                ]
            );

            if ($results[0]['test'] !== "Unit")
                $this->fail("Failed Reading Value 'Unit'");

        } catch (Exception $e) {
            $this->fail("Failed Update: ".$e->getMessage());
        }
    }

    public function testDelete()
    {
        try {
            $model = new Model('test_table');
            $where = ['test'=>"Unit"];

            $count = count($model->read($where));
            $model->delete($where, null);
            $new_count = count($model->read($where));

            if ($count == 0 OR $new_count > 0)
                throw new Exception('Counts on delete inconsistent '.$count.' '.$new_count);
        } catch (Exception $e) {
            $this->fail("Failed Deletion: ".$e->getMessage());
        }
    }

    /* Test our console controller */

    public function testConsoleController()
    {
        //run the command using ` operators and grab the result
        $result = `php index.php Job Help`;

        if (strlen($result) === 0) //just looking for some sort of output here.
            $this->fail("Failed to call Job Help.");
    }


    /* Test Weather API specific functions */
    public function testImperial()
    {
        /* not a great way I can think of doing this except a range check, which
           isn't really a good or valid way to test. */
    }

    public function testGetDataFromDecodedResponse()
    {
        $sample_response = '{"coord":
{"lon":145.77,"lat":-16.92},
"weather":[{"id":803,"main":"Clouds","description":"broken clouds","icon":"04n"}],
"base":"cmc stations",
"main":{"temp":293.25,"pressure":1019,"humidity":83,"temp_min":289.82,"temp_max":295.37},
"wind":{"speed":5.1,"deg":150},
"clouds":{"all":75},
"rain":{"3h":3},
"dt":1435658272,
"sys":{"type":1,"id":8166,"message":0.0166,"country":"AU","sunrise":1435610796,"sunset":1435650870},
"id":2172797,
"name":"Cairns",
"cod":200}';

        $stdobj = json_decode($sample_response);
        $cont = new OpenWeatherController();
        $result = $cont->getDataFromDecodedResponse($stdobj, '');


        $weather = $stdobj;
        $main = $weather->main ? $weather->main : false;
        $wind = $weather->wind ? $weather->wind : false;
        $weather_field = $weather->weather;

        $this->assertEquals($result['zip'],'');
        $this->assertEquals($result['description'],$weather_field[0]->main);

        $this->assertEquals($result['pressure'],$main->pressure);


        $this->assertEquals($result['fahrenheit'],$main->temp);

        //test range here as well
        //-60 to 224

        $this->assertEquals($result['humidity'],$main->humidity);

        $this->assertEquals($result['wind_speed'],$wind->speed);

        $this->assertEquals($result['wind_deg'],$wind->deg);

      //  $this->assertEquals($cont['updated'],'');


    }
}