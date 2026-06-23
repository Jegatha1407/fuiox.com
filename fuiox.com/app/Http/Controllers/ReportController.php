<?php
namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\Models\User;
use Carbon\Carbon;

class ReportController extends Controller
{
    private function userId(): int { return session('auth_user'); }

    public function reports()
    {
        $userId = $this->userId();
        $user   = User::findOrFail($userId);

        $totalSent     = DB::table('messages')->where('user_id',$userId)->where('type','outgoing')->count();
        $totalReceived = DB::table('messages')->where('user_id',$userId)->where('type','incoming')->count();
        $totalContacts = DB::table('messages')->where('user_id',$userId)->distinct('wa_id')->count('wa_id');
        $replied       = DB::table('messages')->where('user_id',$userId)->where('type','incoming')->distinct('wa_id')->count('wa_id');
        $responseRate  = $totalContacts > 0 ? round(($replied/$totalContacts)*100) : 0;

        $stats = ['total_sent'=>$totalSent,'total_received'=>$totalReceived,'total_contacts'=>$totalContacts,'response_rate'=>$responseRate];

        // Last 7 days
        $last7 = collect(range(6,0))->map(function($i) use ($userId) {
            $date = Carbon::now()->subDays($i)->format('Y-m-d');
            return [
                'date'     => Carbon::now()->subDays($i)->format('d M'),
                'sent'     => DB::table('messages')->where('user_id',$userId)->where('type','outgoing')->whereDate('created_at',$date)->count(),
                'received' => DB::table('messages')->where('user_id',$userId)->where('type','incoming')->whereDate('created_at',$date)->count(),
            ];
        })->values()->toArray();

        // Last 30 days
        $last30 = collect(range(29,0))->map(function($i) use ($userId) {
            $date = Carbon::now()->subDays($i)->format('Y-m-d');
            return [
                'date'     => Carbon::now()->subDays($i)->format('d M'),
                'sent'     => DB::table('messages')->where('user_id',$userId)->where('type','outgoing')->whereDate('created_at',$date)->count(),
                'received' => DB::table('messages')->where('user_id',$userId)->where('type','incoming')->whereDate('created_at',$date)->count(),
            ];
        })->values()->toArray();

        // Hourly
        $hourly = collect(range(0,23))->map(function($h) use ($userId) {
            return ['hour'=>str_pad($h,2,'0',STR_PAD_LEFT).':00','total'=>DB::table('messages')->where('user_id',$userId)->whereRaw('HOUR(created_at)=?',[$h])->count()];
        })->values()->toArray();

        // Media types
        $mediaTypes = DB::table('messages')->where('user_id',$userId)->where('type','outgoing')->whereNotNull('media_type')->select('media_type',DB::raw('count(*) as total'))->groupBy('media_type')->pluck('total','media_type')->toArray();

        // Top contacts
        $topContacts = DB::table('messages')->where('user_id',$userId)->select('wa_id as phone',DB::raw('count(*) as total'))->groupBy('wa_id')->orderByDesc('total')->limit(10)->get()->map(function($c) use ($userId) {
            $contact = DB::table('contacts')->where('user_id',$userId)->where('phone',$c->phone)->first();
            $c->name = $contact->name ?? $c->phone;
            return $c;
        });

        // Campaign performance
        $campaigns = DB::table('campaigns')->where('user_id',$userId)->orderByDesc('created_at')->limit(10)->get();

        return view('user.reports', compact('user','stats','last7','last30','hourly','mediaTypes','topContacts','campaigns'));
    }
}