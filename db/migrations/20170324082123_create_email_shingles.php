<?php

use Phinx\Migration\AbstractMigration;

class CreateEmailShingles extends AbstractMigration
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
      $table = $this->table('email_shingles');

      $table->addColumn('email_id', 'biginteger', array('signed' => false))
            ->addColumn('shingle_id', 'integer')
            ->addForeignKey('email_id', 'SpamOut', 'SpamOutID', array('delete'=> 'CASCADE'))
            ->addForeignKey('shingle_id', 'shingles', 'id', array('delete'=> 'CASCADE'))
            ->addIndex(array('email_id', 'shingle_id'), array('unique' => true))
            ->create();
    }
}
