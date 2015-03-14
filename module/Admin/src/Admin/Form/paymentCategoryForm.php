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

class paymentCategoryForm extends Form{

    protected $translator;

    protected $inputFilter;
    public function __construct($name = null){
        parent::__construct('paymentCategoryForm');
        $this->setAttribute('method', 'post');
        $this->setAttribute('class', 'form-horizontal');
        $this->setAttribute('enctype', 'multipart/form-data');
        $this->setAttribute('action', '/admin/payment-category/add');
        $this->prepareElements();

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
            'name' => 'name',
            'options' => array(
                'label' => $translator->translate('Name'),
            ),
            'attributes' => array(
                'type' => 'text',
                'class' => 'input-xlarge'
            ),
        ));


        $this->add(array(
            'name' => 'submit',
            'attributes' => array(
                'type'  => 'submit',
                'value' => 'Save',
                'id' => 'submitbutton',
                'class' => 'btn btn-danger'
            ),
        ));
    }



} 