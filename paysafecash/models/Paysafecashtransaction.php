<?php

class PaysafecashtransactionClass extends ObjectModel {
    public $id_paysafecashtransaction;
    public $transaction_id;
    public $order_id;
    public $cart_id;
    public $status;
    public $transaction_time;
    public static $definition = [
        'table' => 'paysafecashtransaction',
        'primary' => 'id_paysafecashtransaction',
        'fields' => [
            'transaction_id' =>  ['type' => self::TYPE_STRING, 'validate' => 'isAnything', 'required'=>true],
            'order_id' =>  ['type' => self::TYPE_STRING, 'validate' => 'isAnything', 'required'=>true],
            'cart_id' =>  ['type' => self::TYPE_STRING, 'validate' => 'isAnything', 'required'=>true],
            'status' =>  ['type' => self::TYPE_STRING, 'validate' => 'isAnything', 'required'=>true],
            'transaction_time' =>  ['type' => self::TYPE_DATE, 'validate' => 'isDateFormat'],
        ],
    ];

}