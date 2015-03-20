<?php
/**
 * Created by PhpStorm.
 * User: tri
 * Date: 7/14/2014
 * Time: 5:27 PM
 */
namespace Admin\Form;
use Zend\Form\Form;
use Zend\Form\Element;


class surtaxForm extends Form {
//    protected $objectManager;
    public function __construct($name = null)
    {

        // we want to ignore the name passed
        parent::__construct('surtaxForm');
        $this->setAttribute('method', 'post');
        $this->setAttribute('class', 'form-horizontal');

        $this->add(array(
            'name' => 'id',
            'attributes' => array(
                'type' => 'hidden',

            ),
        ));
        $this->add(array(
            'name' => 'name',
            'attributes' => array(
                'type' => 'text',

            ),
        ));
        $this->add(array(
            'name' => 'value',
            'attributes' => array(
                'type' => 'text',
            ),
        ));

        $this->add(array(
            'name' => 'type',
            'attributes' => array(
                'type' => 'text',
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