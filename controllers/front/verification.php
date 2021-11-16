<?php

class StudentdiscountsVerificationModuleFrontController extends ModuleFrontController {
	public function initContent() {
    	    parent::initContent();
        $token = Tools::getValue('token');
        $email = Tools::getValue('email');
		$customerId = Context::getContext()->customer->id;

		$isOk = ($customerId == null) ? true : $this->customerIsStudent($customerId, $email);

    	if ($isOk && $this->isEmailExist($email, $token)) {     	
        	$db = Db::getInstance();
        	$query = 'UPDATE `' . _DB_PREFIX_ . 'studentdiscounts` SET verificated = 1, validated = ' . $this->isStudentDomain($email) . ' WHERE email = "'.$email.'" AND token = "'. $token . '"';
        	$db->execute($query);
			$query = 'SELECT * FROM `' . _DB_PREFIX_ . 'studentdiscounts` WHERE email = "' . $email . '"';
        	$result = Db::getInstance()->executeS($query);
			$this->l('Your domain is not on the list of student domains in our database. Please allow 1 business day for manual verification. To complete the creation of a student account, you must send a photo of your student ID.');
			$this->l('To complete the creation of a student account, you must send a photo of your student ID.');
			$this->l('Your account is active. You can take advantage of student discounts!');
			$text1 = $this->trans(
			'Your domain is not on the list of student domains in our database. Please allow 1 business day for manual verification. To complete the creation of a student account, you must send a photo of your student ID.'
			,[],'Modules.Studentdiscounts.Verification');
			$text2 = $this->trans(
			'To complete the creation of a student account, you must send a photo of your student ID.'
			,[],'Modules.Studentdiscounts.Verification');
			$text3 = $this->trans(
			'Your account is active. You can take advantage of student discounts!'
			,[],'Modules.Studentdiscounts.Verification');
			if ($result[0]['validated'] == 0) {
				$this->context->smarty->assign('verificationText', $text1);
			} else if ($result[0]['validated'] == 1 && $result[0]['active'] == '0') {
				$this->context->smarty->assign('verificationText', $text2);
			} else if ($result[0]['validated'] == 1 && $result[0]['active'] == '1') {
				$this->context->smarty->assign('verificationText', $text3);
			}

        	$this->setTemplate('module:studentdiscounts/views/templates/front/verification.tpl');
        } else {
        	$this->setTemplate('module:studentdiscounts/views/templates/front/error-verification.tpl');
        }
	}

	private function customerIsStudent($id, $email) {
		$query = 'SELECT * FROM `' . _DB_PREFIX_ . 'studentdiscounts` WHERE email = "'.$email.'" AND id_customer = ' . $id;
		$result = Db::getInstance()->executeS($query);
		
		if (count($result) == 0) {
			return false;
		} else return true;
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