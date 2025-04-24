<?php

namespace App\Http\Controllers\Client;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\DataTables\InfluencerDataTable;

class InfluencerController extends Controller
{
    public function index(InfluencerDataTable $influencerDataTable) {
        return $adminTypeDataTable->render('backend.influencer.index');
    }
}
