<?php

class AdminStudentDomainController extends ModuleAdminController{
    public function __construct()
    {
      $db = \Db::getInstance();
      $id_lang = Context::getContext()->language->id;

      $this->table = "student_domain";
      $this->className = "StudentDomainClass";
      $this->fields_list = array(
        'id_student_domain'=> array(
            'title' => "ID",
            'align' => 'center',
            'class' => 'fixed-width-xs'
          ),
        'domain' => array(
            'title' => 'Domain',
            'orderby' => true,
            'class' => 'fixed-width-xxl'
          ),
      );

      $this->actions = ['delete'];

      $this->bulk_actions = array(
            'delete' => array(
                'text'    => 'Delete selected',
                'icon'    => 'icon-trash',
                'confirm' => 'Delete selected items?',
            ),
        );

        $this->fields_form = [
            'legend' => [
                'title' => 'Settings',
            ],
            'input' => [
                [
                    'type' => 'text',
                    'label' => 'domain',
                    'name' => 'domain',
                    'required' => true
                ],
            ],
            'submit' => [
                'title' => 'Save',
                'class' => 'btn btn-default pull-right'
            ]
        ];
    
        $this->fields_form[1] = [
            'legend' => [
                'title' => 'Settings',
            ],
            'input' => [
                [
                    'type' => 'text',
                    'label' => 'domain',
                    'name' => 'domain',
                    'required' => true
                ],
            ],
            'submit' => [
                'title' => 'Save',
                'class' => 'btn btn-default pull-right'
            ]
        ];

      $this->bootstrap = true;
      parent::__construct();
    }
}
