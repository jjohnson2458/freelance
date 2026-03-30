<?php

/** @var \Core\Router $router */

// Auth routes
$router->get('login', 'AuthController@showLogin');
$router->post('login', 'AuthController@login');
$router->get('register', 'AuthController@showRegister');
$router->post('register', 'AuthController@register');
$router->get('logout', 'AuthController@logout');
$router->get('forgot-password', 'AuthController@showForgotPassword');
$router->post('forgot-password', 'AuthController@forgotPassword');
$router->get('reset-password/{token}', 'AuthController@showResetPassword');
$router->post('reset-password', 'AuthController@resetPassword');

// Dashboard
$router->get('', 'DashboardController@index');
$router->get('dashboard', 'DashboardController@index');

// Resumes
$router->get('resumes', 'ResumeController@index');
$router->get('resumes/create', 'ResumeController@create');
$router->post('resumes/store', 'ResumeController@store');
$router->get('resumes/edit/{id}', 'ResumeController@edit');
$router->post('resumes/update/{id}', 'ResumeController@update');
$router->post('resumes/delete/{id}', 'ResumeController@delete');
$router->post('resumes/activate/{id}', 'ResumeController@activate');

// Talents
$router->get('talents', 'TalentController@index');
$router->get('talents/create', 'TalentController@create');
$router->post('talents/store', 'TalentController@store');
$router->get('talents/edit/{id}', 'TalentController@edit');
$router->post('talents/update/{id}', 'TalentController@update');
$router->post('talents/delete/{id}', 'TalentController@delete');
$router->post('talents/toggle/{id}', 'TalentController@toggle');

// Jobs
$router->get('jobs', 'JobController@index');
$router->get('jobs/create', 'JobController@create');
$router->post('jobs/store', 'JobController@store');
$router->get('jobs/view/{id}', 'JobController@show');
$router->get('jobs/edit/{id}', 'JobController@edit');
$router->post('jobs/update/{id}', 'JobController@update');
$router->post('jobs/delete/{id}', 'JobController@delete');
$router->post('jobs/archive/{id}', 'JobController@archive');

// Proposals
$router->get('proposals', 'ProposalController@index');
$router->get('proposals/view/{id}', 'ProposalController@show');
$router->post('proposals/generate/{jobId}', 'ProposalController@generate');
$router->post('proposals/regenerate/{id}', 'ProposalController@regenerate');
$router->get('proposals/edit/{id}', 'ProposalController@edit');
$router->post('proposals/update/{id}', 'ProposalController@update');
$router->post('proposals/delete/{id}', 'ProposalController@delete');
$router->post('proposals/submit/{id}', 'ProposalController@submit');
$router->post('proposals/feedback/{id}', 'ProposalController@feedback');
$router->get('proposals/pdf/{id}', 'ProposalController@pdf');

// Platforms
$router->get('platforms', 'PlatformController@index');
$router->post('platforms/toggle/{id}', 'PlatformController@toggle');
$router->get('platforms/edit/{id}', 'PlatformController@edit');
$router->post('platforms/update/{id}', 'PlatformController@update');

// Proposal Rules
$router->get('rules', 'RulesController@index');
$router->get('rules/create', 'RulesController@create');
$router->post('rules/store', 'RulesController@store');
$router->get('rules/edit/{id}', 'RulesController@edit');
$router->post('rules/update/{id}', 'RulesController@update');
$router->post('rules/delete/{id}', 'RulesController@delete');
$router->post('rules/toggle/{id}', 'RulesController@toggle');
$router->post('rules/reorder', 'RulesController@reorder');

// Calendar / Availability
$router->get('calendar', 'CalendarController@index');
$router->post('calendar/store', 'CalendarController@store');
$router->post('calendar/update/{id}', 'CalendarController@update');
$router->post('calendar/delete/{id}', 'CalendarController@delete');

// User Guide
$router->get('guide', 'GuideController@index');
