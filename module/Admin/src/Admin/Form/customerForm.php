<?php
/**
 * Created by PhpStorm.
 * User: MrHung
 * Date: 11/6/14
 * Time: 9:37 AM
 */

namespace Admin\Form;

use Zend\Form\Form;
use Zend\Form\Element;
use Velacolib\Utility\Utility;

class customerForm extends Form{

    protected $translator;

    protected $inputFilter;
    public function __construct($name = null){
        parent::__construct('customerForm');
        $this->setAttribute('method', 'post');
        $this->setAttribute('class', 'form-horizontal');
        $this->setAttribute('enctype', 'multipart/form-data');
        $this->setAttribute('action', '/admin/customer/add');
        $this->prepareElements();

    }



    public function exchangeArray($data)
    {
        $this->profilename  = (isset($data['profilename']))  ? $data['profilename']     : null;
        $this->fileupload  = (isset($data['fileupload']))  ? $data['fileupload']     : null;
    }

    public function prepareElements(){

        $translator = Utility::translate();

        $this->add(array(
            'name' => 'id',
            'attributes' => array(
                'type' => 'hidden',

            ),
        ));

        $this->add(array(
            'name' => 'fullname',
            'options' => array(
                'label' => $translator->translate('Full name'),
            ),
            'attributes' => array(
                'type' => 'text',
                'class' => 'input-xlarge'
            ),
        ));

        $this->add(array(
            'name' => 'nicename',
            'options' => array(
                'label' => $translator->translate('Nice name'),
            ),
            'attributes' => array(
                'type' => 'text',
                'class' => 'input-xlarge'
            ),
        ));

        $this->add(array(
            'name' => 'customerCode',
            'options' => array(
                'label' => $translator->translate('Customer code'),
            ),
            'attributes' => array(
                'type' => 'text',
                'class' => 'input-xlarge'
            ),
        ));

        $this->add(array(
            'name' => 'level',
            'options' => array(
                'label' => $translator->translate('Level'),
            ),
            'attributes' => array(
                'type' => 'text',
                'class' => 'input-xlarge'
            ),
        ));

        $this->add(array(
            'name' => 'phone',
            'options' => array(
                'label' => $translator->translate('Phone'),
            ),
            'attributes' => array(
                'type' => 'text',
                'class' => 'input-xlarge'
            ),
        ));

        $this->add(array(
            'name' => 'email',
            'options' => array(
                'label' => $translator->translate('Email'),
            ),
            'attributes' => array(
                'type' => 'text',
                'class' => 'input-xlarge'
            ),
        ));

        $this->add(array(
            'name' => 'address',
            'options' => array(
                'label' => $translator->translate('Address'),
            ),
            'attributes' => array(
                'type' => 'text',
                'class' => 'input-xlarge'
            ),
        ));

        $this->add(array(
            'name' => 'birthday',
            'options' => array(
                'label' => $translator->translate('Birthday'),
            ),
            'attributes' => array(
                'type' => 'text',
                'class' => 'input-xlarge date-picker'
            ),
        ));

        $this->add(array(
            'name' => 'avatar',
            'options' => array(
                'label' => $translator->translate('Avatar'),
            ),
            'attributes' => array(
                'type' => 'file',
                'class' => 'input-xlarge avatar'
            ),
        ));
        $this->add(array(
            'name' => 'avatar_old',
            'attributes' => array(
                'type' => 'hidden',
                'class' => 'input-xlarge avatar'
            ),
        ));

        $this->add(array(
            'name' => 'submit',
            'attributes' => array(
                'type'  => 'submit',
                'value' => 'Go',
                'id' => 'submitbutton',
                'class' => 'btn btn-primary span3 typeahead'
            ),
        ));
    }



} 