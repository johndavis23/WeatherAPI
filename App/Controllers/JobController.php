<?php
namespace App\Controllers;

use App\Classes\Controller;
use App\Classes\View;
use App\Classes\Model;
use App\Util\Util;

/* our external imports*/
use RestClient;
use Carbon\Carbon;
use Cron\CronExpression;



class InvalidAPIKey extends \Exception {};

class JobController extends Controller
{
    //bitmasks, add in powers of two
    const JOB_MINUTELY = 1;
    const JOB_HOURLY   = 2;
    const JOB_DAILY    = 4;
    const JOB_MONTHLY  = 8;

    protected $validActions = [
        "Help",
        "ImportZips",
        'RunJobs',
        'upJobs',
        'minutely',
        'hourly',
        'daily',
        'monthly'
    ];


    //Our Commands
    function Help()
    {
        echo "Help Information",PHP_EOL;
    }

    function minutely()
    {
        global $OPEN_WEATHER_FREQ;

        if ($OPEN_WEATHER_FREQ & static::JOB_MINUTELY) {
            $result = (new OpenWeatherController())->Fetch();
            exit($result ? 0: 1);
        }
    }

    function hourly()
    {
        global $OPEN_WEATHER_FREQ;

        if ($OPEN_WEATHER_FREQ & static::JOB_HOURLY) {
            $result = (new OpenWeatherController())->Fetch();
            exit($result ? 0: 1);
        }
    }

    function daily()
    {
        global $OPEN_WEATHER_FREQ;

        if ($OPEN_WEATHER_FREQ & static::JOB_DAILY) {
            $result = (new OpenWeatherController())->Fetch();
            exit($result ? 0: 1);
        }
    }
    function monthly()
    {
        global $OPEN_WEATHER_FREQ;

        if ($OPEN_WEATHER_FREQ & static::JOB_MONTHLY) {
            $result = (new OpenWeatherController())->Fetch();
            exit($result ? 0: 1);
        }
    }

    /* this should be living in a seed file. I am leaving it here unless we get more
       seedable data to improve readability */
    function upJobs()
    {
        $cronJobModel = new Model('cron_jobs');
        /* you can also extend model isntead of performing ad hoc access */

        //minutely
        $cronJobModel->create(
            [
                'cron'      => '*     *     *     *     *',
                'command'   => 'php index.php Job minutely',
                /* ^
                 * not crazy about this col. Need to make a command factory that takes in
                 * a controller name and function name instead of running these as exec
                 * in case of bad guys putting something ugly in there.
                 */
                'updated'   => Carbon::now(),
                'created'   => Carbon::now()
            ]
        );

        $cronJobModel->create(
            [
                'cron'      => '4     *     *     *     *',
                'command'   => 'php index.php Job hourly',
                'updated'   => Carbon::now(),
                'created'   => Carbon::now()
            ]
        );
        //hourly
        $cronJobModel->create(
            [
                'cron'      => '4     2     *     *     *',
                'command'   => 'php index.php Job daily',
                'updated'   => Carbon::now(),
                'created'   => Carbon::now()
            ]
        );
        //daily
        $cronJobModel->create(
            [
                'cron'      => '4     2     1     *     *',
                'command'   => 'php index.php Job monthly',
                'updated'   => Carbon::now(),
                'created'   => Carbon::now()
            ]
        );
    }

    function RunJobs()
    {

        /* you can also extend model instead of performing ad hoc access; */
        $cronJobModel = new Model('cron_jobs');
        $cronLogModel = new Model('cron_log');

        // a bit dangerous with too many records. Assuming only a few;
        // consider using the paginated version for a large amount,
        // or a db cursor
        $jobs = $cronJobModel->readAll();

        foreach ($jobs as $job) {

            try {
                $cron = CronExpression::factory($job['cron']);
            } catch (\Exception $e) {
                echo $e->getMessage();
                return false;
            }

            if ($cron->isDue()) {

                $command = $job['command'];
                $output  = '';

                //TODO: replace this with a factory and two fields in the db
                $result  = exec($command,$output);


                $cronLogModel->create(
                    [
                        'cron'      => $job['cron'],
                        'job'       => $job['id'],
                        'command'   => $command,

                        'output'    => implode(PHP_EOL, $output),

                        'completed' => $result ? Carbon::now() : null,
                        'updated'   => Carbon::now(),
                        'created'   => Carbon::now()
                    ]
                );

            }
        }
    }
}
