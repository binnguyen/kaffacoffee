<?php
/**
 * Created by PhpStorm.
 * User: tristria
 * Date: 10/1/2014
 * Time: 2:16 PM
 */

namespace Admin\Form;
use Zend\Form\Form;
use Zend\Form\Element;


class configForm extends Form{

    public function __construct($name = null){

        parent::__construct('configForm');
        $this->setAttribute('method', 'post');
        $this->setAttribute('class', 'form-horizontal');
        $this->setAttribute('enctype', 'multipart/form-data');
        $this->setAttribute('action', '/admin/config/add');
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

        $this->add(array(
            'name' => 'name',
            'options' => array(

            ),
            'attributes' => array(
                'type'  => 'hidden',
                'class'=>'input-xlarge'
            ),
        ));


        $this->add(array(
            'name' => 'value',
            'options' => array(
                'label' => 'Config value'
            ),
            'attributes' => array(
                'type'  => 'text',
                'class'=>'input-xlarge'
            ),
        ));

        $this->add(array(
            'name' => 'type',
            'options' => array(
            ),
            'attributes' => array(
                'type'  => 'hidden',
                'class'=>'input-xlarge'
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