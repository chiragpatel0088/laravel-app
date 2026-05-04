<?php

/*
 * Supplier editor
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
Editor::inst($db, 'suppliers')
    ->fields(
        Field::inst('suppliers.id')->set(false),
        Field::inst('suppliers.supplier_name')
            ->validator(Validate::notEmpty(ValidateOptions::inst()
                ->message('A supplier name is required')))
            ->validator(Validate::minLen(1, ValidateOptions::inst()
                ->message('Supplier name must be at least 1 characters long')))
            ->validator(Validate::maxLen(250, ValidateOptions::inst()
                ->message('Supplier name must be no longer than 250 characters'))),
        Field::inst('suppliers.supplier_firstname')
            ->validator(Validate::notEmpty(ValidateOptions::inst()
                ->message('A first name is required')))
            ->validator(Validate::minLen(1, ValidateOptions::inst()
                ->message('First name must be at least 1 characters long')))
            ->validator(Validate::maxLen(250, ValidateOptions::inst()
                ->message('First name must be no longer than 250 characters'))),
        Field::inst('suppliers.supplier_lastname')
            ->validator(Validate::maxLen(250, ValidateOptions::inst()
                ->message('Last name must be no longer than 250 characters'))),
        Field::inst('suppliers.email')
            ->setFormatter(function ($val, $data, $opts) {
                $emails = explode(';', ltrim(rtrim(trim($val), ';'), ';'));
                for ($i = 0; $i < sizeof($emails); $i++) {
                    $emails[$i] = trim($emails[$i]);
                }
                return implode("; ", $emails);
            })
            ->validator(function ($val, $data, $field, $host) {
                // Check the input is a email or multiple emails
                // Check for colons as they are not allowed, users seem to frequently get 'mailto:' prepended to mail inputs when copy pasting frequently..
                $emails = explode(';', trim($val));
                $invalidEmails = array_filter($emails, function ($email) {
                    $valid_email = filter_var(trim($email), FILTER_VALIDATE_EMAIL);
                    if (empty($valid_email)) {
                        return $email;
                    } else return false;
                });
                // Return error message with invalid emails if theres a invalid emails
                if (sizeof($invalidEmails) > 0) {
                    return '<strong>Invalid emails:</strong> ' . implode(",", $invalidEmails);
                } else return true;
            })
            ->validator(Validate::notEmpty(ValidateOptions::inst()
                ->message('A email is required')))
            ->validator(Validate::maxLen(500, ValidateOptions::inst()
                ->message('Email field must be no longer than 500 characters'))),
        Field::inst('suppliers.contact_ph'),
        Field::inst('suppliers.contact_mob'),
        Field::inst('suppliers.supplier_code')->validator(Validate::maxLen(5, ValidateOptions::inst()
            ->message('Must be less than 5 characters')))
    )
    ->process($_POST)
    ->json();
