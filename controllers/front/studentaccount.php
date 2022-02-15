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
        $result = $this->getStudentByCustomerId($customer->id);

        if (count($result) == 0) {
            Tools::redirect(Tools::getHttpHost(true).__PS_BASE_URI__);
        }

        if (Tools::getValue('deletePhotos') == 1) {
            $studentId = $result[0]['id_studentdiscounts'];
            $this->deleteImageById($studentId);
        }
        
        $id = $result[0]['id_studentdiscounts'];
        $images = $this->getImageById($id);
        $studentCart = [];
        foreach ($images as $image) {
            array_push($studentCart, $image['image']);
        }
        $this->l('Email verified.');
        $this->l('Email not verified. Check your e-mail box to verify your e-mail address.');
        $this->l('Student domain confirmed.');
        $this->l('Student domain not confirmed. Wait for confirmation.');
        $this->l('Account active until ');
        $this->l('The account is not active');
        $date=date_create($result[0]['account_validity_period']);
        $data = [
            'verificatedValue' => $result[0]['verificated'],
            'verificated' => $result[0]['verificated'] == 1 ? $this->trans('Email verified.',[],'Modules.Studentdiscounts.Studentaccount') : $this->trans('Email not verified. Check your e-mail box to verify your e-mail address.',[],'Modules.Studentdiscounts.Studentaccount'),
            'valdatedValue' => $result[0]['validated'],
            'validated' => $result[0]['validated'] == 1 ? $this->trans('Student domain confirmed.',[],'Modules.Studentdiscounts.Studentaccount') : $this->trans('Student domain not confirmed. Wait for confirmation.',[],'Modules.Studentdiscounts.Studentaccount'),
            'activeValue' => $result[0]['active'],
            'studentCart' => $studentCart,
            'active' => $result[0]['active'] == 1 ? $this->trans('Account active until ',[],'Modules.Studentdiscounts.Studentaccount') . ' ' . date_format($date,"d.m.Y") : $this->trans('The account is not active',[],'Modules.Studentdiscounts.Studentaccount'),
            'email' => $result[0]['email'],
            'name' => $name,
            'images' => $studentCart,
        ];

        $this->context->smarty->assign('studentaccount', $data);
        $this->setTemplate('module:studentdiscounts/views/templates/front/studentaccount.tpl');
	}

    private function getStudentByCustomerId($customerId) {
        $query = 'SELECT * FROM `' . _DB_PREFIX_ . 'studentdiscounts` WHERE id_customer = ' . $customerId;
        $result = Db::getInstance()->executeS($query);

        return $result;
    }

    private function getImageById($id) {
        $query = 'SELECT image FROM `' . _DB_PREFIX_ . 'studentdiscounts_image` WHERE 	id_studentdiscounts  = '.$id . ' LIMIT 2';
        $result = Db::getInstance()->executeS($query);
        
        return $result; 
    }

    private function deleteImageById($id) {
        $query = 'SELECT image FROM `' . _DB_PREFIX_ . 'studentdiscounts_image` WHERE 	id_studentdiscounts  = ' . $id;
        $images = Db::getInstance()->executeS($query);
        $query = 'DELETE FROM ' . _DB_PREFIX_ . 'studentdiscounts_image WHERE id_studentdiscounts = '.$id ;
        Db::getInstance()->execute($query);
        foreach ($images as $image) {
            $filename = $image['image'];
            $filename = _PS_MODULE_DIR_ .'studentdiscounts/upload/studentcarts/' . $filename;
            if (file_exists($filename)) {
                unlink($filename);
            }
        }
    }

    private function sendMail($email, $link, $message, $subject) {
        Mail::Send(
           (int)(Configuration::get('PS_LANG_DEFAULT')), // defaut language id
           'student_send_photo_alert', // email template file to be use
           $subject, // email subject
           array(
               '{email}' => $email, // sender email address
               '{message}' => $message,
               '{link}' => $link,
           ),
           $email, // receiver email address
           NULL, //receiver name
           NULL, //from email address
           NULL,  //from name
           NULL, //file attachment
           NULL, //mode smtp
           _PS_MODULE_DIR_ . 'studentdiscounts/mails' //custom template path
       );
   }

    public function postProcess() {

        $customer = Context::getContext()->customer;
        if ($customer->id == null) {
            Tools::redirect(Tools::getHttpHost(true).__PS_BASE_URI__);
        }
        $email = $customer->email;
        $result = $this->getStudentByCustomerId($customer->id);
        $studentId = $result[0]['id_studentdiscounts'];
        if (isset($_FILES['studentCart'])) {
            $files =  $_FILES['studentCart'];
            $files = $this->reArrayFiles( $_FILES['studentCart']);
                $acceptFileType = ['JPG', 'PDF', 'GIF', 'JPEG', 'jpg', 'jpeg', 'pdf', 'gif'];
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
                    $result = $this->getImageById($studentId);
                    if (count($result) < 2 && move_uploaded_file($file["tmp_name"], $target_file))
                    {
                        Db::getInstance()->insert(
                            'studentdiscounts_image',
                            [    
                                'id_studentdiscounts' => $studentId,
                                'image' => $filename,
                            ]
                        );
                        $link = Configuration::get('MODULE_LINK');
                        $message = $this->l('The student has sent a photo of the student ID. Check and approve they account.');
                        $subject = $this->l('Student has sent photo');
                        $message = $this->trans('The student has sent a photo of the student ID. Check and approve they account.', [], 'Modules.Studentdiscounts.Studentaccount');
                        $subject = $this->trans('Student has sent photo', [], 'Modules.Studentdiscounts.Studentaccount');
                        $email = Configuration::get('PS_SHOP_EMAIL');
                        $this->sendMail($email, $link, $message, $subject);
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