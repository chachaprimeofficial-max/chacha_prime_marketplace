<?php
namespace App\Http\Controllers\Admin;
use App\Http\Controllers\Controller;
use App\Services\AdminProfitReportService;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\StreamedResponse;
class ProfitReportController extends Controller
{
 public function index(Request $request){$d=$request->validate(['from'=>'nullable|date','to'=>'nullable|date|after_or_equal:from']);return view('admin.reports.profit',app(AdminProfitReportService::class)->report($d['from']??null,$d['to']??null));}
 public function csv(Request $request):StreamedResponse{$d=$request->validate(['from'=>'nullable|date','to'=>'nullable|date|after_or_equal:from']);$r=app(AdminProfitReportService::class)->report($d['from']??null,$d['to']??null);return response()->streamDownload(function()use($r){$out=fopen('php://output','w');fputcsv($out,['Product','Units','Revenue','Cost','Gross Profit','Margin %']);foreach($r['rows'] as $x)fputcsv($out,[$x->title,$x->units,$x->revenue,$x->cost,$x->gross_profit,$x->margin_percent]);fclose($out);},'chacha-prime-profit-report.csv',['Content-Type'=>'text/csv']);}
}
