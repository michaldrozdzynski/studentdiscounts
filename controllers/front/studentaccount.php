<?php

class StudentdiscountsStudentaccountModuleFrontController extends ModuleFrontController {

	public function initContent() {
    	parent::initContent();

        $customer = Context::getContext()->customer;
       //ump(Tools::getHttpHost(true).__PS_BASE_URI__);
        if ($customer->id == null) {
            Tools::redirect(Tools::getHttpHost(true).__PS_BASE_URI__);

        }
        $name = $customer->firstname . ' ' . $customer->lastname;
        $email = $customer->email;
        $result = $this->getStudentByEmail($email);

        if (count($result) == 0) {
            Tools::redirect(Tools::getHttpHost(true).__PS_BASE_URI__);
        }
        
        $id = $result[0]['id_studentdiscounts'];
        $images = $this->getImageById($id);
        $studentCart = [];
        foreach ($images as $image) {
            array_push($studentCart, $image['image']);
        }

        $date=date_create($result[0]['account_validity_period']);
        $data = [
            'verificatedValue' => $result[0]['verificated'],
            'verificated' => $result[0]['verificated'] == 1 ? $this->l('Email verified.') : $this->l('Email not verified. Check your e-mail box to verify your e-mail address.'),
            'valdatedValue' => $result[0]['validated'],
            'validated' => $result[0]['validated'] == 1 ? $this->l('Student domain confirmed.') : $this->l('Student domain not confirmed. Wait for confirmation.'),
            'activeValue' => $result[0]['active'],
            'studentCart' => $studentCart,
            'active' => $result[0]['active'] == 1 ? $this->l('Account active until ') . date_format($date,"d.m.Y") : $this->l('The account is not active'),
            'email' => $email,
            'name' => $name,
            'target_dir' => '/presta/modules/studentdiscounts/upload/studentcarts/',
        ];

        $this->context->smarty->assign('studentaccount', $data);
        $this->setTemplate('module:studentdiscounts/views/templates/front/studentaccount.tpl');
	}

    public function getStudentByEmail($email) {
        $query = 'SELECT * FROM `' . _DB_PREFIX_ . 'studentdiscounts` WHERE email = \''.$email . '\'';
        $result = Db::getInstance()->executeS($query);
        
        return $result;
    }

    private function getImageById($id) {
        $query = 'SELECT image FROM `' . _DB_PREFIX_ . 'studentdiscounts_image` WHERE 	id_studentdiscounts  = '.$id . ' LIMIT 2';
        $result = Db::getInstance()->executeS($query);
        
        return $result; 
    }

    public function postProcess() {

            $customer = Context::getContext()->customer;
            if ($customer->id == null) {
                Tools::redirect(Tools::getHttpHost(true).__PS_BASE_URI__);
            }
            $email = $customer->email;
            $result = $this->getStudentByEmail($email);
            $studentId = $result[0]['id_studentdiscounts'];

            if (isset($_FILES['studentCart'])) {
                $files =  $_FILES['studentCart'];
                $files = $this->reArrayFiles( $_FILES['studentCart']);
                 $acceptFileType = ['JPG', 'PDF', 'GIF', 'JPEG', 'pdf', 'gif'];
                foreach ($files as $file) {
                    $target_dir = _PS_MODULE_DIR_. 'studentdiscounts/upload/studentcarts/';
                    $target_file = $target_dir . basename($file["name"]);
                    $file_name = basename($file["name"]);
                    $uploadOk = true;
                    $imageFileType = pathinfo($target_file,PATHINFO_EXTENSION);
                    $filename = '';
                    if(!in_array($imageFileType, $acceptFileType)) {
                        $uploadOk = false;
                    }

                    do {
                        $filename = uniqid('studentcart', true) . '.' . $imageFileType;
                        $target_file = $target_dir . $filename;
                    } while (file_exists($target_file));
                    
                    if ($uploadOk && $filename != '') {
                        if (move_uploaded_file($file["tmp_name"], $target_file))
                        {
                            Db::getInstance()->insert(
                                'studentdiscounts_image',
                                [    
                                    'id_studentdiscounts' => $studentId,
                                    'image' => $filename,
                                ]
                            );
                        }
                    }
                }
            }
    }

    private function reArrayFiles(&$file_post) {
        $file_ary = array();
        $file_count = count($file_post['name']);
        $file_keys = array_keys($file_post);
    
        for ($i=0; $i<$file_count; $i++) {
            foreach ($file_keys as $key) {
                $file_ary[$i][$key] = $file_post[$key][$i];
            }
        }
    
        return $file_ary;
    }
}