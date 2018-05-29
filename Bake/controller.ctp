<?php

namespace Backend\Controller;

use Cake\Event\Event;
use Cake\ORM\Entity;
use Cake\Core\Configure;
use App\Model\Entity\<%= $name %>;
use FsCore\Controller\CrudController;
use FsCore\Utility\Utils;

class <%= $pluralName %>Controller extends CrudController {

    protected $slug = false;
    protected $hasSeo = true;
    protected $hasListSeo = true;
    <%  if (!empty($orderField)): %>
        protected $hasOrder = [
            'filter' => []
        ];
    <%  endif; %>
    <%  if (!empty($singlePictures)) : %>
        protected $singlePhotos = [
        <%  foreach ( $singlePictures as $photo ): %>
        '<%= $photo %>' => [
                'isRequired' => true,
                'fixRatio' => false,
                'width' => 420,
                'height' => 420,
            ],
        <%  endforeach; %>
        ];
    <%  endif; %>
    <%  if (!empty($multiLanguanges)) : %>
    protected $multiLangFields = [
    <%  foreach ( $multiLanguanges as $multiLangField ): %>
    '<%= $multiLangField %>' => [
            'input' => 'text',
            'label' => '<%= $this->_pluralHumanName($multiLangField) %>',
            'validation' => [
                'notBlank' => 'Please input <%= $this->_pluralHumanName($name) %> <%= $this->_pluralHumanName($multiLangField) %>',
                'maxLength' => [
                    'validationValue' => 200,
                    'errorMsg' => '<%= $this->_pluralHumanName($name) %> <%= $this->_pluralHumanName($multiLangField) %> limit is 200 characters.',
                ],
            ],
        ],
    <%  endforeach; %>
];
 <%  endif; %>
    <%  if (!empty($multiPictures)) : %>
        protected $multiPhotos = [
        <%  foreach ( $multiPictures as $multiPicField ): %>
        '<%= $multiPicField %>' => [
            'isRequired' => true,
         ],
        <%  endforeach; %>
    ];
    <%  endif; %>

    public function initialize() {
        parent::initialize();
        Utils::useTables($this, ['App.<%= $pluralName %>']);

        $this->modelName = '<%= $pluralName %>';
        $this->model = $this-><%= $pluralName %>;
        <%  if (!empty($statusField) ):  %>
        $this->activationFields['status'] = $this->model->getStatusList();
        <%  endif; %>
    }
    
    protected function _prepareObject(Entity $object) {
        $inputTypes = [
        <%  if (!empty($fields)) :  %>
            <%  foreach ($fields as $field ): %>
            '<%= $field %>' => [
                'input' => '<%= ($schema->columnType($field)=='string') ? 'text' : '' %>',
                'label' => '<%= $this->_pluralHumanName($field) %>',
                'currentValue' => !empty($object) ? $object-><%= $field %> : false,
            ],
            <% endforeach; %>
        <%  endif; %>
        <%  if (!empty($belongtoFields)) :  %>
            <%  foreach ($belongtoFields as $key => $field ): %>
                '<%= $key %>' => [
                            'input' => 'dropdown',
                            'options' => '',
                            'label' => '<%= $this->_pluralHumanName($field) %>',
                            'currentValue' => !empty($object) ? $object-><%= $key %> : false,
                ],
            <% endforeach; %>
        <%  endif; %>
];
        $inputTypes = array_merge($inputTypes, $this->_prepareCommonObject($object));
        $this->set('inputTypes', $inputTypes);

        return true;
    }
    
    protected function getObject($id = null, $contain = [], $parse = false) {
        Utils::useComponents($this, ['FsCore.MultiLanguage']);
        $currentLanguage = $this->MultiLanguage->getCurrentLanguage();
        
        $object = parent::getObject($id, $contain, $parse);
        if ($parse && !empty($object)) {

        }

        return $object;
    }

    protected function getAllObjects($contain = [], $conditions = []) {
        Utils::useComponents($this, ['FsCore.MultiLanguage']);
        $currentLanguage = $this->MultiLanguage->getCurrentLanguage();
        
        $records = parent::getAllObjects($contain, $conditions);
        foreach ($records as $record) {

        }

        return $records;
    }

}