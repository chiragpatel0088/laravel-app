<?php

/*
 * Trucks editor
 */

// DataTables PHP library
include("../editor/DataTables.php");

// Alias Editor classes so they are easy to use
use
    DataTables\Editor,
    DataTables\Editor\Field,
    DataTables\Editor\Format,
    DataTables\Editor\Mjoin,
    DataTables\Editor\Options,
    DataTables\Editor\Upload,
    DataTables\Editor\Validate,
    DataTables\Editor\ValidateOptions;

global $database;

// Build our Editor instance and process the data coming from _POST
Editor::inst($db, 'trucks')
    ->fields(
        Field::inst('trucks.id')->set(false),
        Field::inst('trucks.number_plate')
            ->validator(Validate::notEmpty(ValidateOptions::inst()
                ->message('Number plate is required')))
            ->validator(Validate::maxLen(50, ValidateOptions::inst()
                ->message('Number plate must be no longer than 50 characters'))),
        Field::inst('trucks.brand')
            ->validator(Validate::notEmpty(ValidateOptions::inst()
                ->message('Brand is required')))
            ->validator(Validate::maxLen(250, ValidateOptions::inst()
                ->message('Brand must be no longer than 250 characters'))),
        Field::inst('trucks.tare')
            ->validator(Validate::numeric('.', ValidateOptions::inst()
                ->message('Invalid number')))
            ->validator(Validate::minNum(0, '.', ValidateOptions::inst()
                ->message('Tare too low')))
            ->validator(Validate::maxNum(1000, '.', ValidateOptions::inst()
                ->message('Tare too high'))),
        Field::inst('trucks.boom')
            ->validator(Validate::numeric('.', ValidateOptions::inst()
                ->message('Invalid number')))
            ->validator(Validate::minNum(0, '.', ValidateOptions::inst()
                ->message('Boom too short :(')))
            ->validator(Validate::maxNum(1000, '.', ValidateOptions::inst()
                ->message('Boom too long :)'))),
        Field::inst('trucks.capacity')
            ->validator(Validate::numeric('.', ValidateOptions::inst()
                ->message('Invalid number')))
            ->validator(Validate::notEmpty(ValidateOptions::inst()
                ->message('Capacity is required')))
            ->validator(Validate::minNum(1, '.', ValidateOptions::inst()
                ->message('Capacity too low :(')))
            ->validator(Validate::maxNum(1000, '.', ValidateOptions::inst()
                ->message('Capacity too high'))),
        Field::inst('trucks.max_speed')
            ->validator(Validate::numeric('.', ValidateOptions::inst()
                ->message('Invalid number'))),
        Field::inst('trucks.est_fee')
            ->validator(Validate::notEmpty(ValidateOptions::inst()
                ->message('A establishment fee is required')))
            ->validator(Validate::numeric('.', ValidateOptions::inst()
                ->message('Invalid number'))),
        Field::inst('trucks.hourly_rate')
            ->validator(Validate::notEmpty(ValidateOptions::inst()
                ->message('A hourly rate is required')))
            ->validator(Validate::numeric('.', ValidateOptions::inst()
                ->message('Invalid number'))),
        Field::inst('trucks.min')
            ->validator(Validate::notEmpty(ValidateOptions::inst()
                ->message('A minimum charge is required')))
            ->validator(Validate::numeric('.', ValidateOptions::inst()
                ->message('Invalid number'))),
        Field::inst('trucks.travel_rate_km')
            ->validator(Validate::notEmpty(ValidateOptions::inst()
                ->message('A travel rate is required')))
            ->validator(Validate::numeric('.', ValidateOptions::inst()
                ->message('Invalid number'))),
        Field::inst('trucks.disposal_fee')
            ->validator(Validate::numeric('.', ValidateOptions::inst()
                ->message('Invalid number'))),
        Field::inst('trucks.washout')
            ->validator(Validate::numeric('.', ValidateOptions::inst()
                ->message('Invalid number'))),
        Field::inst('trucks.row_order')
            ->validator(Validate::numeric('', ValidateOptions::inst()
                ->message('Row ordering error occured in allTrucksEdit.php')))
    )
    ->on('preCreate', function ($editor, $values) use ($database) {
        // Sets default value of row_order to be the largest row order value + 1, this makes new trucks default to bottom of the table/list
        $editor->field('trucks.row_order')->setValue($database->getMaxRowOrderValueFromTrucks() + 1);
    })
    ->on('preRemove', function ($editor, $id, $values) {
        // On remove, the sequence needs to be updated to decrement all rows
        // beyond the deleted row. Get the current reading order by id (don't
        // use the submitted value in case of a multi-row delete).
        $order = $editor->db()
            ->select('trucks', 'trucks.row_order', array('id' => $id))
            ->fetch();
        $editor->db()
            ->query('update', 'trucks')
            ->set('trucks.row_order', 'trucks.row_order-1', false)
            ->where('trucks.row_order', $order['trucks.row_order'], '>')
            ->where('trucks.id', $id)
            ->exec();
    })
    ->process($_POST)
    ->json();
