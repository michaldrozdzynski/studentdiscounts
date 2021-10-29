<?php

class StudentDiscountClass extends ObjectModelCore
{
    public $id_studentdiscounts;
    public $email;
	public $validated;
	public $verificated;
	public $token;

    public static $definition = array(
      'table' => 'studentdiscounts',
      'primary' => 'id_studentdiscounts',
      'multilang' => false,
      'fields' => array(
        'id_student_domain'=> array('type' => self::TYPE_NOTHING, 'validate' => 'isUnsignedId'),
        'email' => array('type' => self::TYPE_STRING, 'validate' => 'isGenericName', 'required' => true),
      	'validated' => array('type' => self::TYPE_BOOL, 'required' => true),
		'verificated' => array('type' => self::TYPE_BOOL, 'required' => true),
        'token' => array('type' => self::TYPE_STRING, 'validate' => 'isGenericName', 'required' => true),
      )
    );
}