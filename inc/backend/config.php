<?php

/**
 * dashboards/corporate_slim/config.php
 *
 * Author: pixelcave
 *
 * Corporate Slim dashboard configuration file
 *
 */

// **************************************************************************************************
// INCLUDED VIEWS
// **************************************************************************************************

$dm->inc_side_overlay           = 'inc/backend/views/inc_side_overlay.php';
$dm->inc_sidebar                = 'inc/backend/views/inc_sidebar.php';
$dm->inc_header                 = 'inc/backend/views/inc_header.php';
$dm->inc_footer                 = 'inc/backend/views/inc_footer.php';


// **************************************************************************************************
// GENERIC
// **************************************************************************************************

$dm->theme                      = 'xpro';


// **************************************************************************************************
// SIDEBAR & SIDE OVERLAY
// **************************************************************************************************

$dm->l_sidebar_visible_desktop  = false;
$dm->l_sidebar_dark             = true;


// **************************************************************************************************
// HEADER
// **************************************************************************************************

$dm->l_header_style             = 'dark';


// **************************************************************************************************
// MAIN CONTENT
// **************************************************************************************************

$dm->l_m_content                = 'boxed';


// **************************************************************************************************
// MAIN MENU
// **************************************************************************************************

/**
 * Check logged in before generating menu
 * */

global $database;

if (!isset($session->userinfo['user_level'])) {
    header("Location: index");
}

// Admins at level 3
if ($session->isUserlevel(3)) {
    $dm->main_nav = array(
        array(
            'name'  => 'Jobs',
            'icon'  => 'fa fa-toolbox',
            'url'   => 'jobs_panel'
        ),
        array(
            'name'  => 'Quotes',
            'icon'  => 'fa fa-file-invoice',
            'url'   => 'quotes_panel'
        ),
        array(
            'name'  => 'Site Inspections',
            'icon'  => 'far fa-eye',
            'url'   => 'site_inspections_panel'
        ),
        array(
            'name'  => 'Database',
            'icon'  => 'fa fa-boxes',
            'sub'   => array(
                array(
                    'name'  => 'Operators',
                    'icon'  => 'fa fa-portrait',
                    'url'   => 'operators'
                ),
                array(
                    'name'  => 'Customers',
                    'icon'  => 'fa fa-address-book',
                    'url'   => 'customers'
                ),
                array(
                    'name'  => 'Foremen',
                    'icon'  => 'fa fa-user-tie',
                    'url'   => 'foremen'
                ),
                array(
                    'name'  => 'Layers',
                    'icon'  => 'fa fa-hard-hat',
                    'url'   => 'layers'
                ),
                array(
                    'name'  => 'Suppliers',
                    'icon'  => 'fa fa-box-open',
                    'url'   => 'suppliers'
                ),
                array(
                    'name'  => 'Trucks',
                    'icon'  => 'fa fa-truck-monster',
                    'url'   => 'trucks'
                ),
                array(
                    'name'  => 'Concrete Types',
                    'icon'  => 'fa fa-cubes',
                    'url'   => 'concretes'
                ),
                array(
                    'name'  => 'Job Types',
                    'icon'  => 'fa fa-fill-drip',
                    'url'   => 'job_types'
                ),
                array(
                    'name'  => 'Reports',
                    'icon'  => 'fa fa-file-pdf',
                    'url'   => 'reports'
                )
            )
        )
    );
} else if ($session->isUserlevel(2)) {
    $dm->main_nav = array(
        array(
            'name'  => 'Operator Dashboard',
            'icon'  => 'fa fa-boxes',
            'url'   => 'operator_main'
        ),
        array(
            'name'  => 'Jobs',
            'icon'  => 'fa fa-toolbox',
            'url'   => 'jobs_panel'
        ),
        array(
            'name'  => 'Quotes',
            'icon'  => 'fa fa-file-invoice',
            'url'   => 'quotes_panel'
        ),
        array(
            'name'  => 'Site Inspections',
            'icon'  => 'far fa-eye',
            'url'   => 'site_inspections_panel'
        ),
        array(
            'name'  => 'Database',
            'icon'  => 'fa fa-boxes',
            'sub'   => array(
                array(
                    'name'  => 'Operators',
                    'icon'  => 'fa fa-portrait',
                    'url'   => 'operators'
                ),
                array(
                    'name'  => 'Customers',
                    'icon'  => 'fa fa-address-book',
                    'url'   => 'customers'
                ),
                array(
                    'name'  => 'Foremen',
                    'icon'  => 'fa fa-user-tie',
                    'url'   => 'foremen'
                ),
                array(
                    'name'  => 'Layers',
                    'icon'  => 'fa fa-hard-hat',
                    'url'   => 'layers'
                ),
                array(
                    'name'  => 'Suppliers',
                    'icon'  => 'fa fa-box-open',
                    'url'   => 'suppliers'
                ),
                array(
                    'name'  => 'Trucks',
                    'icon'  => 'fa fa-truck-monster',
                    'url'   => 'trucks'
                ),
                array(
                    'name'  => 'Concrete Types',
                    'icon'  => 'fa fa-cubes',
                    'url'   => 'concretes'
                ),
                array(
                    'name'  => 'Job Types',
                    'icon'  => 'fa fa-fill-drip',
                    'url'   => 'job_types'
                ),
                array(
                    'name'  => 'Reports',
                    'icon'  => 'fa fa-file-pdf',
                    'url'   => 'reports'
                )
            )
        )
    );
} else if ($session->isUserlevel(4)) { //Supervisor
    $dm->main_nav = array(
        array(
            'name'  => 'Operator Dashboard',
            'icon'  => 'fa fa-boxes',
            'url'   => 'operator_main'
        ),
        array(
            'name'  => 'Jobs',
            'icon'  => 'fa fa-toolbox',
            'url'   => 'jobs_panel'
        ),
        // array(
        //     'name'  => 'Quotes',
        //     'icon'  => 'fa fa-file-invoice',
        //     'url'   => 'quotes_panel'
        // ),
        array(
            'name'  => 'Site Inspections',
            'icon'  => 'far fa-eye',
            'url'   => 'site_inspections_panel'
        ),
        array(
            'name'  => 'Database',
            'icon'  => 'fa fa-boxes',
            'sub'   => array(
                array(
                    'name'  => 'Operators',
                    'icon'  => 'fa fa-portrait',
                    'url'   => 'operators'
                ),
                array(
                    'name'  => 'Customers',
                    'icon'  => 'fa fa-address-book',
                    'url'   => 'customers'
                ),
                array(
                    'name'  => 'Foremen',
                    'icon'  => 'fa fa-user-tie',
                    'url'   => 'foremen'
                ),
                array(
                    'name'  => 'Layers',
                    'icon'  => 'fa fa-hard-hat',
                    'url'   => 'layers'
                ),
                array(
                    'name'  => 'Suppliers',
                    'icon'  => 'fa fa-box-open',
                    'url'   => 'suppliers'
                ),
                array(
                    'name'  => 'Trucks',
                    'icon'  => 'fa fa-truck-monster',
                    'url'   => 'trucks'
                ),
                array(
                    'name'  => 'Concrete Types',
                    'icon'  => 'fa fa-cubes',
                    'url'   => 'concretes'
                ),
                array(
                    'name'  => 'Job Types',
                    'icon'  => 'fa fa-fill-drip',
                    'url'   => 'job_types'
                ),
                // array(
                //     'name'  => 'Reports',
                //     'icon'  => 'fa fa-file-pdf',
                //     'url'   => 'reports'
                // )
            )
        )
    );
} else if ($session->isUserlevel(1)) { // Operators
    $dm->main_nav = array(
        array(
            'name'  => 'Operator Dashboard',
            'icon'  => 'far fa-compass',
            'url'   => 'operator_main'
        )
    );
}
