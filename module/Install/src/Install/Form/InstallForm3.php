<?php
/**
 * Created by PhpStorm.
 * User: tristria
 * Date: 10/1/2014
 * Time: 2:16 PM
 */

namespace Install\Form;
use Zend\Form\Form;
use Zend\Form\Element;


class InstallForm3 extends Form{

    public function __construct($name = null){

        parent::__construct('installstep3');
        $this->setAttribute('method', 'post');
        $this->setAttribute('class', 'form-horizontal cutom-form');
        $this->setAttribute('enctype', 'multipart/form-data');
        $this->setAttribute('action', '/install/installstep3');
        $this->prepareElements();
    }

    public function prepareElements()
    {
        // add() can take either an Element/Fieldset instance,
        // or a specification, from which the appropriate object
        // will be built.

        $value_option = array('1' => 'Yes', '0' => 'No');
        $this->add(array(
            'type' => 'Zend\Form\Element\Select',
            'name' => 'issample',
            'options' => array(
                'label' => 'Insert Sample Data',
                'value_options' => $value_option,
            )
        ));
        $value_option  = array('en_us'=>'English', 'vn_VN'=>'Vietnamese');
        $this->add(array(
            'type' => 'Zend\Form\Element\Select',
            'name' => 'lang',
            'options' => array(
                'label' => 'Language',
                'value_options' => $value_option,
            )
        ));

        $this->add(array(
            'name' => 'send',
            'attributes' => array(
                'type'  => 'submit',
                'value' => 'Next Step ',
                'class'=> 'btn btn-danger'
            ),
        ));




        // We could also define the input filter here, or
        // lazy-create it in the getInputFilter() method.
    }
}