<?php
namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\Models\User;
use App\Models\Contact;
use Carbon\Carbon;

class ContactController extends Controller
{
    private function userId(): int
    {
        $userId = session('auth_user');
        $user   = User::find($userId);
        return $user->parent_user_id ?? $userId;
    }

    
    public function contacts()
    {
        $userId   = $this->userId();
        $user     = User::findOrFail(session('auth_user'));
        $contacts = DB::table('contacts')->where('user_id',$userId)->orderBy('name')->get();
        $groups   = DB::table('contacts')->where('user_id',$userId)->whereNotNull('group_name')->select('group_name',DB::raw('count(*) as total'))->groupBy('group_name')->get();
        $stats    = [
            'total'  => $contacts->count(),
            'groups' => $groups->count(),
            'recent' => DB::table('contacts')->where('user_id',$userId)->where('created_at','>=',Carbon::now()->subDays(7))->count(),
        ];
        return view('user.contacts', compact('user','contacts','groups','stats'));
    }


    public function list()
    {
        $userId   = $this->userId();
        $contacts = DB::table("contacts")->where("user_id",$userId)->orderBy("name")->get();
        return response()->json(["contacts" => $contacts]);
    }

    public function stats()
    {
        $userId = $this->userId();
        return response()->json([
            'total'  => DB::table('contacts')->where('user_id',$userId)->count(),
            'groups' => DB::table('contacts')->where('user_id',$userId)->whereNotNull('group_name')->distinct('group_name')->count(),
            'recent' => DB::table('contacts')->where('user_id',$userId)->where('created_at','>=',Carbon::now()->subDays(7))->count(),
        ]);
    }

    
    public function groups()
    {
        $userId = $this->userId();
        $groups = DB::table('contacts')->where('user_id',$userId)->whereNotNull('group_name')->select('group_name',DB::raw('count(*) as total'))->groupBy('group_name')->get();
        return response()->json(['groups'=>$groups]);
    }


    public function store(Request $request)
    {
        $request->validate(['name'=>'required','phone'=>'required']);
        $userId = $this->userId();

        // Check plan limit
        $sub = DB::table('subscriptions')->where('user_id',$userId)->where('status','active')->where('expires_at','>',now())->orderByDesc('created_at')->first();
        if ($sub) {
            $plan = DB::table('plans')->find($sub->plan_id);
            if ($plan && !DB::table('contacts')->where('user_id',$userId)->where('phone',$request->phone)->exists()) {
                $count = DB::table('contacts')->where('user_id',$userId)->count();
                if ($count >= $plan->contacts_limit) {
                    return response()->json(['error'=>"Your {$plan->name} plan allows only {$plan->contacts_limit} contacts. Upgrade to add more."],403);
                }
            }
        }

        $contact = Contact::updateOrCreate(
            ['user_id'=>$userId,'phone'=>$request->phone],
            ['name'=>$request->name,'email'=>$request->email,'group_name'=>$request->group_name,'tags'=>$request->tags,'notes'=>$request->notes]
        );
        return response()->json(['success'=>true,'contact'=>$contact]);
    }

    public function update(Request $request, $id)
    {
        $userId = $this->userId();
        DB::table('contacts')->where('id',$id)->where('user_id',$userId)->update([
            'name'       => $request->name,
            'phone'      => $request->phone,
            'email'      => $request->email,
            'group_name' => $request->group_name,
            'tags'       => $request->tags,
            'notes'      => $request->notes,
            'updated_at' => now(),
        ]);
        return response()->json(['success'=>true]);
    }

    public function destroy($id)
    {
        DB::table('contacts')->where('id',$id)->where('user_id',$this->userId())->delete();
        return response()->json(['success'=>true]);
    }

    
    public function export()
    {
        $userId   = $this->userId();
        $contacts = DB::table('contacts')->where('user_id',$userId)->get();
        $csv  = "Name,Phone,Email,Group,Tags,Notes\n";
        foreach ($contacts as $c) {
            $csv .= implode(',', [
                '"'.($c->name??'').'"','"'.($c->phone??'').'"','"'.($c->email??'').'"',
                '"'.($c->group_name??'').'"','"'.($c->tags??'').'"','"'.($c->notes??'').'"',
            ])."\n";
        }
        return response($csv,200,['Content-Type'=>'text/csv','Content-Disposition'=>'attachment;filename=contacts.csv']);
    }

    
    public function import(Request $request)
    {
        $request->validate(['file'=>'required|file|mimes:csv,txt']);
        $userId  = $this->userId();
        $file    = $request->file('file');
        $rows    = array_map('str_getcsv', file($file->path()));
        $header  = array_shift($rows);
        $header  = array_map('strtolower', array_map('trim', $header));
        $imported = 0; $skipped = 0;
        foreach ($rows as $row) {
            $data = array_combine($header, $row);
            $phone = preg_replace('/[^0-9]/',' ',trim($data['phone']??''));
            if (!$phone) { $skipped++; continue; }
            $exists = DB::table('contacts')->where('user_id',$userId)->where('phone',$phone)->exists();
            if ($exists) { $skipped++; continue; }
            DB::table('contacts')->insert(['user_id'=>$userId,'name'=>$data['name']??'Unknown','phone'=>$phone,'email'=>$data['email']??null,'tags'=>$data['tags']??null,'created_at'=>now(),'updated_at'=>now()]);
            $imported++;
        }
        return response()->json(['imported'=>$imported,'skipped'=>$skipped]);
    }
}