<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Models\Company;
use App\Models\User;
use App\Models\ShortUrl;
use App\Models\UrlClick;
use Carbon\Carbon;
use Symfony\Component\HttpFoundation\StreamedResponse;

class DashboardController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
    }

    public function index(Request $request)
    {
        $user = Auth::user();
        $interval = $request->get('interval', 'this_month');

        if ($user->isSuperAdmin()) {
            return $this->superAdminDashboard($request, $interval);
        }

        if ($user->isAdmin()) {
            return $this->adminDashboard($request, $interval);
        }

        return $this->memberDashboard($request, $interval);
    }

    private function superAdminDashboard(Request $request, $interval)
    {
        $clients = Company::withCount(['users', 'shortUrls'])
            ->with(['shortUrls' => function ($q) {
                $q->select('id', 'company_id', 'clicks_count');
            }])
            ->orderBy('id', 'DESC')
            ->paginate(10, ['*'], 'clients_page');

        $query = ShortUrl::with(['company', 'user']);
        $this->applyDateFilter($query, $interval);
        $urls = $query->orderBy('id', 'DESC')->paginate(10, ['*'], 'urls_page');

        return view('dashboard.superadmin', compact('clients', 'urls', 'interval'));
    }

    private function adminDashboard(Request $request, $interval)
    {
        $user = Auth::user();
        $companyId = $user->company_id;

        $members = User::where('company_id', $companyId)
            ->with(['roles', 'shortUrls'])
            ->withCount('shortUrls')
            ->orderBy('id', 'ASC')
            ->paginate(10, ['*'], 'members_page');

        $query = ShortUrl::where('company_id', $companyId)->with('user');
        $this->applyDateFilter($query, $interval);
        $urls = $query->orderBy('id', 'DESC')->paginate(10, ['*'], 'urls_page');

        return view('dashboard.admin', compact('members', 'urls', 'interval'));
    }

    private function memberDashboard(Request $request, $interval)
    {
        $user = Auth::user();

        $query = ShortUrl::where('user_id', $user->id);
        $this->applyDateFilter($query, $interval);
        $urls = $query->orderBy('id', 'DESC')->paginate(10, ['*'], 'urls_page');

        return view('dashboard.member', compact('urls', 'interval'));
    }

    public function exportCsv(Request $request)
    {
        $user = Auth::user();
        $interval = $request->get('interval', 'this_month');

        if ($user->isSuperAdmin()) {
            $query = ShortUrl::with('company');
        } elseif ($user->isAdmin()) {
            $query = ShortUrl::where('company_id', $user->company_id)->with('user');
        } else {
            $query = ShortUrl::where('user_id', $user->id);
        }

        $this->applyDateFilter($query, $interval);
        $records = $query->orderBy('id', 'DESC')->get();

        $filename = 'urls_export_' . date('Y-m-d_His') . '.csv';

        $response = new StreamedResponse(function () use ($records, $user) {
            $handle = fopen('php://output', 'w');

            if ($user->isSuperAdmin()) {
                fputcsv($handle, ['Short URL', 'Original URL', 'Clicks', 'Client Name', 'Created On']);
                foreach ($records as $row) {
                    fputcsv($handle, [
                        $row->short_url,
                        $row->original_url,
                        $row->clicks_count,
                        $row->company ? $row->company->name : 'N/A',
                        $row->created_at->format('Y-m-d H:i:s'),
                    ]);
                }
            } elseif ($user->isAdmin()) {
                fputcsv($handle, ['Short URL', 'Original URL', 'Clicks', 'User Name', 'Created On']);
                foreach ($records as $row) {
                    fputcsv($handle, [
                        $row->short_url,
                        $row->original_url,
                        $row->clicks_count,
                        $row->user ? $row->user->name : 'N/A',
                        $row->created_at->format('Y-m-d H:i:s'),
                    ]);
                }
            } else {
                fputcsv($handle, ['Short URL', 'Original URL', 'Clicks', 'Created On']);
                foreach ($records as $row) {
                    fputcsv($handle, [
                        $row->short_url,
                        $row->original_url,
                        $row->clicks_count,
                        $row->created_at->format('Y-m-d H:i:s'),
                    ]);
                }
            }

            fclose($handle);
        });

        $response->headers->set('Content-Type', 'text/csv');
        $response->headers->set('Content-Disposition', 'attachment; filename="' . $filename . '"');

        return $response;
    }

    private function applyDateFilter($query, $interval)
    {
        if ($interval === 'today') {
            $query->whereDate('created_at', Carbon::today());
        } elseif ($interval === 'last_week') {
            $query->whereBetween('created_at', [Carbon::now()->subDays(7)->startOfDay(), Carbon::now()->endOfDay()]);
        } elseif ($interval === 'last_month') {
            $query->whereBetween('created_at', [Carbon::now()->subDays(30)->startOfDay(), Carbon::now()->endOfDay()]);
        } elseif ($interval === 'this_month') {
            $query->whereBetween('created_at', [Carbon::now()->startOfMonth(), Carbon::now()->endOfMonth()]);
        }
    }
}
