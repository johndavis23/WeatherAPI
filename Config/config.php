<?php
/* Consider moving these to environment variables */
global $APP_URL;
$APP_URL  =  getenv('APP_URL');

global $DATABASES;
$DATABASES = [
      "default"  => [
          getenv('DB_SERVER'),
          getenv('DB_USERNAME'),
          getenv('DB_PASSWORD'),
          getenv('DB_NAME')
      ],

     // "testing   => [TEST_SQL_SERVER,TEST_SQL_USERNAME,TEST_SQL_PASSWORD,TEST_SQL_NAME]
     // "ext"      => [EXT_SQL_SERVER,EXT_SQL_USERNAME,EXT_SQL_PASSWORD,EXT_SQL_NAME] ,
];

global $OPEN_WEATHER_FREQ;
$OPEN_WEATHER_FREQ = App\Controllers\JobController::JOB_MINUTELY;

global $OPEN_WEATHER_ZIPS;
$OPEN_WEATHER_ZIPS = [
    '37919',
    '37377'
];
?>
