<?php

namespace App\Shell\Task;

use Bake\Shell\Task\SimpleBakeTask;
use Cake\Console\Shell;
use Cake\Core\Configure;
use Bake\Utility\Model\AssociationFilter;
use Cake\Datasource\EntityInterface;
use Cake\ORM\Table;
use Cake\ORM\TableRegistry;
use Cake\Utility\Inflector;

class FsModelTask extends SimpleBakeTask {

    protected $_associationFilter = null;
    protected $nameTemp;
    protected $modelName = null;
    public $pathFragment = '/Model/Table/';
    public $tasks = [
        'Bake.Model',
        'Bake.BakeTemplate',
        'Bake.Test'
    ];

    public function name() {
        return 'fsmodel';
    }

    public function fileName($name) {
        return Inflector::pluralize($name) . 'Table.php';
    }

    public function template() {
        return 'model';
    }

    public function templateData() {
        $name = $this->nameTemp;
        $tableName = $this->_camelize($name);

        $plugin = null;
        if (!empty($this->params['plugin'])) {
            $plugin = $this->params['plugin'] . '.';
        }
        $this->modelName = $tableName;

        if (TableRegistry::exists($this->modelName)) {
            $modelObject = TableRegistry::get($this->modelName);
        } else {
            $modelObject = TableRegistry::get($this->modelName, [
                        'connectionName' => $this->connection
            ]);
        }

        $namespace = Configure::read('App.namespace');
        $primaryKey = (array) $modelObject->getPrimaryKey();

        $schema = $modelObject->getSchema();
        $fields = $schema->columns();
        $dateFields = [];
        $statusField = null;


        foreach ($fields as $key => $field) {
            foreach ($fields as $key => $field) {
                if (($field == 'id') || ($field == 'created') || ($field == 'modified')) {
                    unset($fields[$key]);
                }
                if (($field == 'status')) {
                    unset($fields[$key]);
                    $statusField = 'yes';
                }
                if (($field == 'display_order')) {
                    unset($fields[$key]);
                }
                if (strpos($field, '_date')) {
                    array_push($dateFields, $field);
                    unset($fields[$key]);
                }
            }

            return [
                'namespace' => $namespace,
                'entity' => Inflector::classify($this->nameTemp),
                'fields' => $fields,
                'schema' => $schema,
                'dateFields' => $dateFields,
                'statusField' => $statusField,
            ];
        }
    }

    public function bake($name) {
        $this->nameTemp = $name;

        $this->BakeTemplate->set('pluralName', Inflector::pluralize($name));
        $this->BakeTemplate->set('name', strtolower($name));
        $this->BakeTemplate->set($this->templateData());
        $contents = $this->BakeTemplate->generate($this->template());

        $filename = $this->getPath() . $this->fileName($name);
        $this->createFile($filename, $contents);
        $emptyFile = $this->getPath() . 'empty';
        $this->_deleteEmptyFile($emptyFile);

        return $contents;
    }

}
