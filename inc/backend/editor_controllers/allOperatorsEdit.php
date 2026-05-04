<?php

/*
 * Customer editor
 */

// DataTables PHP library
include("../editor/DataTables.php");

global $session;

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
Editor::inst($db, 'users')
    ->fields(
        Field::inst('users.ID')->set(false),
        Field::inst('users.user_login')
            ->validator(Validate::notEmpty(ValidateOptions::inst()
                ->message('A username is required')))
            ->validator(Validate::minLen(8, ValidateOptions::inst()
                ->message('Username must be at least 8 characters long')))
            ->validator(Validate::maxLen(50, ValidateOptions::inst()
                ->message('Username must be no longer than 50 characters')))
            ->validator(Validate::unique(ValidateOptions::inst()
                ->message('Username has been taken'))),
        Field::inst('users.user_firstname')
            ->validator(Validate::notEmpty(ValidateOptions::inst()
                ->message('A first name is required')))
            ->validator(Validate::minLen(1, ValidateOptions::inst()
                ->message('First name must be at least 1 characters long')))
            ->validator(Validate::maxLen(50, ValidateOptions::inst()
                ->message('First name must be no longer than 50 characters'))),
        Field::inst('users.user_lastname')
            ->validator(Validate::notEmpty(ValidateOptions::inst()
                ->message('A last name is required')))
            ->validator(Validate::minLen(1, ValidateOptions::inst()
                ->message('Last name must be at least 1 characters long')))
            ->validator(Validate::maxLen(50, ValidateOptions::inst()
                ->message('Last name must be no longer than 50 characters'))),
        Field::inst('users.user_phone')
            ->validator(Validate::notEmpty(ValidateOptions::inst()
                ->message('Mobile is required'))),
        Field::inst('users.user_email')
            ->validator(Validate::notEmpty(ValidateOptions::inst()
                ->message('Email is required')))
            ->validator(Validate::minLen(1, ValidateOptions::inst()
                ->message('Email must be at least 1 characters long')))
            ->validator(Validate::maxLen(250, ValidateOptions::inst()
                ->message('Email must be no longer than 250 characters'))),
        Field::inst('users.user_activated')
            ->setFormatter(function ($val, $data, $opts) {
                return !$val ? 0 : 1;
            }),
        Field::inst('users.user_linesman')
            ->setFormatter(function ($val, $data, $opts) {
                return !$val ? 0 : 1;
            })

    )
    ->on('postCreate', function ($editor, $id, $values, $row) use ($session) {
        // Set the password up to the default platinum+year
        $time = time();
        $usersalt = $session->generateRandStr(8);
        $password = sha1($usersalt . "operator" . date("Y"));
        $editor->db()
            ->query('update', 'users')
            ->set(array('user_pass' => $password, 'user_salt' => $usersalt, 'user_timestamp' => $time, 'user_activated' => 1), false)
            ->where('ID', $id)
            ->exec();
    })
    // ->where('users.user_level', 1)
    ->where(function ($q) {
        $q->or_where('users.user_level', 1)
            ->or_where('users.user_level', 4);
    })
    ->process($_POST)
    ->json();
