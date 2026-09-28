<?php
namespace App\Http\Controllers\Admin;
use App\Http\Controllers\Controller;
use App\Services\AdminAnalyticsService;
class CommerceDashboardController extends Controller
{
 public function index(){return view('admin.commerce-dashboard',app(AdminAnalyticsService::class)->overview());}
}
