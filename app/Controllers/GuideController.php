<?php

namespace App\Controllers;

use Core\Controller;
use Core\Auth;

class GuideController extends Controller
{
    public function index(): void
    {
        $this->requireAuth();
        $this->view('guide.index', [
            'pageTitle' => 'User Guide - Freelance Proposal Optimizer',
            'activePage' => 'guide',
        ]);
    }
}
