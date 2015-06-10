<?php

namespace App\Serializers;

class ContactSerializer {

    protected $viewable = ['id', 'first_name', 'last_name'];

    public function serialize($contact, $many=false)
    {
        if ($many)
        {
            $serialized_contacts = [];

            foreach ($contact as $c)
            {
                $serialized_contacts[] = $this->serialize($c, false);
            }

            return $serialized_contacts;
        }
        else
        {
            $serialized_contact = [];

            foreach($this->viewable as $attr)
            {
                $serialized_contact[$attr] = $contact->$attr;
            }

            return $serialized_contact;
        }
    }

}