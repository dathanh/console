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

class FsControllerTask extends SimpleBakeTask {

    protected $_associationFilter = null;
    protected $nameTemp;
    protected $modelName = null;
    public $pathFragment = 'Controller/';
    public $tasks = [
        'Bake.Model',
        'Bake.BakeTemplate',
        'Bake.Test'
    ];

    public function name() {
        return 'fscontroller';
    }

    public function fileName($name) {
        return Inflector::camelize($name) . 'Controller.php';
    }

    public function template() {
        return 'controller';
    }

    public function templateData() {
        $name = $this->nameTemp;
        $controller = null;
        if (!empty($this->params['controller'])) {
            $controller = $this->params['controller'];
        }
        $tableName = $this->_camelize($name);
        if (empty($controller)) {
            $controller = $tableName;
        }
        $this->controllerName = $controller;

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
        $displayField = $modelObject->getDisplayField();
        $schema = $modelObject->getSchema();
        $fields = $schema->columns();
        $singlePictures = [];
        $belongtoFields = [];
        $dateField = [];
        $statusField = null;
        $orderField = null;

        foreach ($fields as $key => $field) {
            if (strpos($field, '_id')) {
                unset($fields[$key]);
            }
            if (($field == 'id') || ($field == 'created') || ($field == 'modified')) {
                unset($fields[$key]);
            }
            if (($field == 'status')) {
                unset($fields[$key]);
                $statusField = 'yes';
            }
            if (($field == 'display_order')) {
                unset($fields[$key]);
                $orderField = 'yes';
            }
            if (strpos($field, '_date')) {
                unset($fields[$key]);
                array_push($dateField, $field);
            }
        }

        $modelClass = $this->modelName;

        list(, $entityClass) = namespaceSplit($this->_entityName($this->modelName));
        $entityClass = sprintf('%s\Model\Entity\%s', $namespace, $entityClass);

        if (!class_exists($entityClass)) {
            $entityClass = EntityInterface::class;
        }
        $associations = $this->_filteredAssociations($modelObject);
        $keyFields = [];
        if (!empty($associations['BelongsTo'])) {
            foreach ($associations['BelongsTo'] as $assoc) {
                $keyFields[$assoc['foreignKey']] = $assoc['variable'];
            }
        }
        $multiLanguanges = [];
        $test = [];
        if (!empty($associations['HasOne'])) {
            foreach ($associations['HasOne'] as $nameField => $assoc) {
                if (strpos($nameField, 'English')) {
                    array_push($multiLanguanges, strtolower(str_replace('English', '', $nameField)));
                }
            }
        }
        $multiPictures = [];
        if (!empty($associations['HasMany'])) {
            foreach ($associations['HasMany'] as $nameField => $assoc) {
                if ($assoc['controller'] == 'MultiPhotos') {
                    array_push($multiPictures, strtolower($this->_singularName($nameField)));
                }
            }
        }

        if (!empty($associations['BelongsTo'])) {
            foreach ($associations['BelongsTo'] as $nameField => $assoc) {
                array_push($test, json_encode($assoc));
                if ($assoc['controller'] == 'Photos') {
                    array_push($singlePictures, $assoc['property']);
                } else {
                    $belongtoFields[$assoc['foreignKey']] = $assoc['property'];
                }
            }
        }
        $pluralVar = Inflector::variable($this->controllerName);
        $pluralHumanName = $this->_pluralHumanName($this->controllerName);

        return ['namespace' => $namespace,
            'fields' => $fields,
            'pluralVar' => $pluralVar,
            'pluralHumanName' => $pluralHumanName,
            'statusField' => $statusField,
            'orderField' => $orderField,
            'dateFields' => $dateField,
            'singlePictures' => $singlePictures,
            'multiLanguanges' => $multiLanguanges,
            'multiPictures' => $multiPictures,
            'schema' => $schema,
            'belongtoFields' => $belongtoFields,
            'test' => $test,
        ];
    }

    protected function _filteredAssociations(Table $model) {
        if (is_null($this->_associationFilter)) {
            $this->_associationFilter = new AssociationFilter();
        }

        return $this->_associationFilter->filterAssociations($model);
    }

    public function bake($name) {
        $this->nameTemp = $name;

        $this->BakeTemplate->set('pluralName', $name);
        $this->BakeTemplate->set('name', Inflector::singularize($name));
        $this->BakeTemplate->set($this->templateData());
        $contents = $this->BakeTemplate->generate($this->template());

        $filename = $this->getPath() . $this->fileName($name);
        $this->createFile($filename, $contents);
        $emptyFile = $this->getPath() . 'empty';
        $this->_deleteEmptyFile($emptyFile);

        return $contents;
    }

}
