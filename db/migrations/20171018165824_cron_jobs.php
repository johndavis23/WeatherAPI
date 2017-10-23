<?php


use Phinx\Migration\AbstractMigration;

class CronJobs extends AbstractMigration
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
        $table = $this->table('cron_jobs');
        $table
              ->addColumn('cron',     'string')
              ->addColumn('command',  'string')
              ->addColumn('updated',  'datetime')
              ->addColumn('created',  'datetime')
              ->create();
    }

    /**
     * Migrate Up.
     */
    public function up()
    {
        // execute()

    }

    /**
     * Migrate Down.
     */
    public function down()
    {

    }
}
