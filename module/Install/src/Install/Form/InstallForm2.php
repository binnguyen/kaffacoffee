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


class InstallForm2 extends Form{

    public function __construct($name = null){

        parent::__construct('installstep2');
        $this->setAttribute('method', 'post');
        $this->setAttribute('class', 'form-horizontal cutom-form');
        $this->setAttribute('enctype', 'multipart/form-data');
        $this->setAttribute('action', '/install/installstep2');
        $this->prepareElements();
    }

    public function prepareElements()
    {
        // add() can take either an Element/Fieldset instance,
        // or a specification, from which the appropriate object
        // will be built.


        $this->add(array(
            'name' => 'adminName',
            'options' => array(
                'label' => 'Username'
            ),
            'attributes' => array(
                'type'  => 'text',
                'class'=>'input-xlarge span12'
            ),
        ));

        $this->add(array(
            'name' => 'adminPassword1',
            'options' => array(
                'label' => 'Password'
            ),
            'attributes' => array(
                'type'  => 'password',
                'class'=>'input-xlarge span12'
            ),
        ));

        $this->add(array(
            'name' => 'adminPassword2',
            'options' => array(
                'label' => 'Confirm Password'
            ),
            'attributes' => array(
                'type'  => 'password',
                'class'=>'input-xlarge span12'
            ),
        ));


        $this->add(array(
            'name' => 'send',
            'attributes' => array(
                'type'  => 'submit',
                'value' => 'Next Step ',
                'class' => 'btn btn-danger'
            ),
        ));




        // We could also define the input filter here, or
        // lazy-create it in the getInputFilter() method.
    }
}