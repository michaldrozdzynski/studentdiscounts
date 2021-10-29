<?php

class StudentDomainClass extends ObjectModelCore
{
    public $id_student_domain;
    public $domain;

    public static $definition = array(
      'table' => 'student_domain',
      'primary' => 'id_student_domain',
      'multilang' => false,
      'fields' => array(
        'id_student_domain'=> array('type' => self::TYPE_NOTHING, 'validate' => 'isUnsignedId'),
        'domain' => array('type' => self::TYPE_STRING, 'validate' => 'isGenericName', 'required' => true),
      )
    );
}