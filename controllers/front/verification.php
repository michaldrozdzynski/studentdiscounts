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
        	return 1;
        } else {
        	return 0;
        }
	}
}