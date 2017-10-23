<?php


use Phinx\Migration\AbstractMigration;

class WeatherData extends AbstractMigration
{
    /**
     * Change Method.
     *
     * Write your reversible migrations using this method.
     *
     * More information on writing migrations is available here:
     * http://docs.phinx.org/en/latest/migrations.html#the-abstractmigration-class
     *
     * The following commands can be used in this method and Phinx will
     * automatically reverse them when rolling back:
     *
     *    createTable
     *    renameTable
     *    addColumn
     *    renameColumn
     *    addIndex
     *    addForeignKey
     *
     * Remember to call "create()" or "update()" and NOT "save()" when working
     * with the Table class.
     */
    public function change()
    {
        $table = $this->table('weather_data');
        $table
            /*
             *  BUSINESS REQUIREMENTS: 2017
                - Zip code,
                - General weather conditions (e.g. sunny, rainy, etc),
                - Atmospheric pressure,
                - Temperature (in Fahrenheit),
                - Winds (direction and speed),
                - Humidity,
                - Timestamp (in UTC)
             */
            ->addColumn('zip',      'string')   // if US only traffic can save marginal space
                                                // and time by using an integer. String has
                                                // more international support.
            ->addColumn('description', 'string')
            ->addColumn('pressure',    'string')
            ->addColumn('fahrenheit',  'string')

            ->addColumn('humidity',    'float')

            ->addColumn('wind_speed',  'float')
            ->addColumn('wind_deg',    'float')
            ->addColumn('updated',     'datetime')
            ->create();
    }
}
