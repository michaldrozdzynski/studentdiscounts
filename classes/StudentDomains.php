<?php

class StudentDomains {
    public static function gets()
    {
        $sql = 'SELECT * FROM ' . _DB_PREFIX_ . 'student_domain ORDER BY id_student_domain';

        return Db::getInstance()->executeS($sql);
    }

    public static function delete($id)
    {
        $query =  'DELETE FROM ' . _DB_PREFIX_ . 'student_domain WHERE id_student_domain = ' . $id; 
        Db::getInstance()->execute($query);
    }

    public static function add($domain)
    {
        $query = 'SELECT COUNT(domain) FROM `' . _DB_PREFIX_ . 'student_domain` WHERE domain = "'.$domain.'"';
        $db = Db::getInstance();
        $result = $db->executeS($query);
        $result = (int) $result[0]['COUNT(domain)'];
        if ($result == 0) {
            $db->insert("student_domain", [
                'domain' => $domain,
            ]);
        }
    
    }
}