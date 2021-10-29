<?php

class StudentdiscountsVerificationModuleFrontController extends ModuleFrontController {
	public function initContent() {
    	    parent::initContent();
        $token = Tools::getValue('token');
        $email = Tools::getValue('email');
    	if ($this->isEmailExist($email, $token)) {
        	$db = Db::getInstance();
        	$query = 'UPDATE `' . _DB_PREFIX_ . 'studentdiscounts` SET verificated = 1, validated = ' . $this->isStudentDomain($email) . ' WHERE email = "'.$email.'" AND token = "'. $token . '"';
        	$db->execute($query);
			
        	$this->setTemplate('module:studentdiscounts/views/templates/front/verification.tpl');
        } else {
        	$this->setTemplate('module:studentdiscounts/views/templates/front/error-verification.tpl');
        }
	}

	private function isEmailExist($email, $token) {
    	$query = 'SELECT COUNT(email) FROM `' . _DB_PREFIX_ . 'studentdiscounts` WHERE email = "'.$email.'" AND token = "'. $token . '"';
    	$db = Db::getInstance();
    	$result = $db->executeS($query);
    	$result = (int) $result[0]['COUNT(email)'];
    	if ($result > 0) {
        	return true;
        } else {
        	return false;
        }
    }

	private function isStudentDomain($email) {
		$domain = explode('@', $email)[1];

		$query = 'SELECT COUNT(domain) FROM `' . _DB_PREFIX_ . 'student_domain` WHERE domain = "'.$domain.'"';
    	$db = Db::getInstance();
    	$result = $db->executeS($query);
    	$result = (int) $result[0]['COUNT(domain)'];
    	if ($result > 0) {
        	$query = 'SELECT * FROM `' . _DB_PREFIX_ . 'customer_group` WHERE id_customer = ' . $customerId . 'AND id_group = ' . Configuration::get('STUDENT_GROUP');
    		$res = Db::getInstance()->getRow($query);
        
    		if (!$res) {
    			Db::getInstance()->insert("customer_group", [
          			'id_customer' => $customerId,
            		'id_group' => Configuration::get('STUDENT_GROUP'),
        		]);
        	}
        } else {
        	return 0;
        }
	}

	private function getCustomerId($email) {
    	$query = 'SELECT id_customer FROM `' . _DB_PREFIX_ . 'customer` WHERE email = \''.$email . '\'';
    	$result = Db::getInstance()->getRow($query);
    	$id = $result['id_customer'];
    
    	return $id;
    }
}