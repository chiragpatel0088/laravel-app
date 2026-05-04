<?php

/*
 * Job types editor
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

// Build our Editor instance and process the data coming from _POST
Editor::inst($db, 'job_types')
    ->fields(
        Field::inst('job_types.id')->set(false),
        Field::inst('job_types.type_name')
            ->validator(Validate::notEmpty(ValidateOptions::inst()
                ->message('A type name is required')))
            ->validator(Validate::minLen(1, ValidateOptions::inst()
                ->message('Type name must be at least 1 characters long')))
            ->validator(Validate::maxLen(250, ValidateOptions::inst()
                ->message('Type name must be no longer than 250 characters'))),
        Field::inst('job_types.type_charge')
            ->validator(Validate::numeric('.', ValidateOptions::inst()
                ->message('Please enter a valid number')))
            ->validator(Validate::notEmpty(ValidateOptions::inst()
                ->message('Charge is required')))
            ->validator(Validate::minLen(1, ValidateOptions::inst()
                ->message('Charge must be at least 1 characters long')))
            ->validator(Validate::maxLen(10, ValidateOptions::inst()
                ->message('Charge must be no longer than 10 characters')))
    )
    ->process($_POST)
    ->json();
