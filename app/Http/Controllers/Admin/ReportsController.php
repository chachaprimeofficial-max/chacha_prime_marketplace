<?php
namespace App\Http\Controllers\Admin;
use App\Http\Controllers\Controller;
use App\Services\AdminReportsService;
use Illuminate\Http\Request;
class ReportsController extends Controller
{
 public function index(Request $request){$d=$request->validate(['from'=>'nullable|date','to'=>'nullable|date|after_or_equal:from']);return view('admin.reports.index',app(AdminReportsService::class)->summary($d['from']??null,$d['to']??null));}
}
