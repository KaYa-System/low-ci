<?php

namespace App\Http\Controllers;

use App\Models\LegalDocument;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class DashboardController extends Controller
{
    public function index()
    {
        $user = Auth::user();
        $isAdmin = $user && $user->is_admin;

        $data = [
            'isAdmin' => $isAdmin,
        ];

        if ($isAdmin) {
            $documents = LegalDocument::with('category')->get();

            $data['adminStats'] = [
                'total' => $documents->count(),
                'published' => $documents->where('status', 'published')->count(),
                'drafts' => $documents->where('status', 'draft')->count(),
                'totalViews' => $documents->sum('views_count'),
            ];

            $data['documents'] = $documents->take(10)->toArray(); // Recent documents for admin
        }

        return inertia('Dashboard', $data);
    }
}
