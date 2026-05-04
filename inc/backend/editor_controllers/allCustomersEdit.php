<?php

/*
 * Customer editor
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
Editor::inst($db, 'customers')
    ->fields(
        Field::inst('customers.id')->set(false),
        Field::inst('customers.name')
            ->validator(Validate::notEmpty(ValidateOptions::inst()
                ->message('A company name is required')))
            ->validator(Validate::minLen(1, ValidateOptions::inst()
                ->message('Company name must be at least 1 characters long')))
            ->validator(Validate::maxLen(250, ValidateOptions::inst()
                ->message('Company name must be no longer than 250 characters'))),
        Field::inst('customers.tfn')
            ->validator(Validate::maxLen(30, ValidateOptions::inst()
                ->message('Company name must be no longer than 30 characters'))),
        Field::inst('customers.discount')
            ->validator(Validate::numeric('.', ValidateOptions::inst()
                ->message('Invalid number')))
            ->validator(Validate::minNum(0, '.', ValidateOptions::inst()
                ->message('Discount must be at least 0%')))
            ->validator(Validate::maxNum(100, '.', ValidateOptions::inst()
                ->message('Discount too high'))),
        Field::inst('customers.address_1')
            ->validator(Validate::notEmpty(ValidateOptions::inst()
                ->message('Address 1 is required')))
            ->validator(Validate::minLen(1, ValidateOptions::inst()
                ->message('Address 1 must be at least 1 characters long')))
            ->validator(Validate::maxLen(250, ValidateOptions::inst()
                ->message('Address 1 must be no longer than 250 characters'))),
        Field::inst('customers.address_2'),
        Field::inst('customers.suburb'),
        Field::inst('customers.city'),
        Field::inst('customers.post_code')
            ->validator(Validate::numeric('.', ValidateOptions::inst()
                ->message('Please enter a valid post code')))
            ->validator(Validate::notEmpty(ValidateOptions::inst()
                ->message('A post code is required')))
            ->validator(Validate::minLen(1, ValidateOptions::inst()
                ->message('Post code must be at least 1 characters long')))
            ->validator(Validate::maxLen(7, ValidateOptions::inst()
                ->message('Post code must be no longer than 7 characters'))),
        Field::inst('customers.first_name')
            ->validator(Validate::notEmpty(ValidateOptions::inst()
                ->message('A first name is required')))
            ->validator(Validate::minLen(1, ValidateOptions::inst()
                ->message('First name must be at least 1 characters long')))
            ->validator(Validate::maxLen(250, ValidateOptions::inst()
                ->message('First name must be no longer than 250 characters'))),
        Field::inst('customers.last_name')
            ->validator(Validate::notEmpty(ValidateOptions::inst()
                ->message('A last name is required')))
            ->validator(Validate::minLen(1, ValidateOptions::inst()
                ->message('Last name must be at least 1 characters long')))
            ->validator(Validate::maxLen(250, ValidateOptions::inst()
                ->message('Last name must be no longer than 250 characters'))),
        Field::inst('customers.contact_ph'),
        Field::inst('customers.contact_mob')
            ->validator(Validate::notEmpty(ValidateOptions::inst()
                ->message('Mobile is required'))),
        Field::inst('customers.email')
            ->setFormatter(function ($val, $data, $opts) {
                $emails = explode(';', ltrim(rtrim(trim($val), ';'), ';'));
                for($i = 0; $i < sizeof($emails); $i++) {
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
                ->message('Email is required')))
            ->validator(Validate::maxLen(500, ValidateOptions::inst()
                ->message('Email field must be no longer than 500 characters')))
    )
    ->process($_POST)
    ->json();
