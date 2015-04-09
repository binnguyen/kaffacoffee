<?php
/**
 * Created by PhpStorm.
 * User: tristria
 * Date: 10/1/2014
 * Time: 2:16 PM
 */

namespace Admin\Form;
use Velacolib\Utility\Utility;
use Zend\Code\Scanner\Util;
use Zend\Form\Form;
use Zend\Form\Element;


class itemUnitConvertForm extends Form{

    public function __construct($name = null){

        parent::__construct('itemUnitConvertForm');
        $this->setAttribute('method', 'post');
        $this->setAttribute('class', 'form-horizontal');
        $this->setAttribute('enctype', 'multipart/form-data');
        $this->setAttribute('action', '/admin/config/additemunit');
        $this->prepareElements();
    }

    public function prepareElements()
    {
        // add() can take either an Element/Fieldset instance,
        // or a specification, from which the appropriate object
        // will be built.

        $this->add(array(
            'name' => 'id',
            'attributes' => array(
                'type'  => 'hidden',
            ),
        ));

        $value_option = Utility::getUnitListForSelect();
        $this->add(array(
            'type' => 'Zend\Form\Element\Select',
            'name' => 'unit_item_one',
            'options' => array(
                'value_options' => $value_option,
                'label' => 'Unit Item 1'
            ),
            'attributes' => array(
                'class' => 'input-xlarge span12'
            ),
        ));

        $this->add(array(
            'name' => 'value',
            'attributes' => array(
                'type'  => 'text',
            ),
            'options' => array(
                'label' => 'Convert Value'
            ),
        ));


        $this->add(array(
            'type' => 'Zend\Form\Element\Select',
            'name' => 'unit_item_two',
            'options' => array(
                'value_options' => $value_option,
                'label' => 'Unit Item 2'
            ),
            'attributes' => array(

                'class' => 'input-xlarge span12'
            ),
        ));




        $this->add(array(
            'name' => 'send',
            'attributes' => array(
                'type'  => 'submit',
                'value' => 'Submit',
            ),
        ));




        // We could also define the input filter here, or
        // lazy-create it in the getInputFilter() method.
    }
}