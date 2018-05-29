<?php

namespace App\Model\Table;

use App\Model\Entity\<%= $entity %>;
use Cake\Core\Configure;
use Cake\Event\Event;
use Cake\ORM\Query;
use Cake\ORM\RulesChecker;
use Cake\ORM\Table;
use Cake\Validation\Validator;
use FsCore\Model\Table\FsTable;

class <%= $pluralName %>Table extends FsTable {

    protected $multiLanguages = [
//        'fieldName',
    ];
    protected $singlePhotos = [
//        'foreignKey' => 'upperCaseFirstFieldNames',
    ];
    protected $multiPhotos = [
//        'fieldName' => 'upperCaseFirstFieldNames',
    ];
    protected $belongRelations = [
//        'foreignKey' => 'classNames',
    ];

    public function initialize(array $config) {
        parent::initialize($config);

        $this->table('<%= $name %>');
        $this->primaryKey('id');
        $this->addBehavior('Timestamp');
    }

    public function validationDefault(Validator $validator) {
        $validator->integer('id')->allowEmpty('id', 'create');
        <% if(!empty($fields)): %>
            <% foreach ($fields as $field) : %>
                <% if(($schema->columnType($field)=='string')) : %>
                    $validator->requirePresence('<%= $field %>')->notEmpty('<%= $field %>', __('Please fill this field'));
                <% endif; %>
                <% if((in_array($schema->columnType($field), ['integer','boolean']))): %>
                   $validator->integer('<%= $field %>')->requirePresence('<%= $field %>', 'create', __('Please ill this field'))
                        ->notEmpty('<%= $field %>', 'create', __('Please ill this field'));
                <% endif; %>
            <% endforeach; %>
        <% endif; %>

        return $validator;
    }

    public function beforeMarshal(Event $event, $data) {
        <% if(!empty($fields)): %>
            <% foreach ($fields as $field) : %>
            if (isset($data['<%= $field %>']) && empty($data['<%= $field %>'])) {
                unset($data['<%= $field %>']);
            }
            <% endforeach; %>
        <% endif; %>
        <% if(!empty($dateFields)): %>
            <% foreach ($dateFields as $field) : %>
            if (!empty($data['<%= $field %>'])) {
                list($day, $month, $year) = explode('/', $data['<%= $field %>']);
                $data['<%= $field %>'] = strtotime("{$year}/{$month}/{$day}");
            }
            if (empty($data['<%= $field %>'])) {
                $data['<%= $field %>'] = time();
            }
            $data['<%= $field %>'] = date('Y/m/d 00:00:00', $data['<%= $field %>']);
            <% endforeach; %>
        <% endif; %>
    }
<% if(!empty($statusField)): %>
    public function getStatusList() {
        return [
            ACTIVE => __('Active'),
            INACTIVE => __('Inactive'),
        ];
    }
<% endif; %>
}