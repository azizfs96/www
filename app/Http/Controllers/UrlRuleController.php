<?php

namespace App\Http\Controllers;

use App\Models\UrlRule;
use Illuminate\Http\Request;

class UrlRuleController extends Controller
{
    public function index()
    {
        return view('waf.url-rules.index', [
            'rules' => UrlRule::orderBy('id', 'desc')->get(),
        ]);
    }

    public function create()
    {
        return view('waf.url-rules.create');
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'name'        => 'nullable|string|max:255',
            'path'        => 'required|string|max:255',
            'allowed_ips' => 'required|string',
        ]);

        $data['enabled'] = true;

        UrlRule::create($data);

        return redirect('/waf/url-rules')->with('status', 'تم إضافة القاعدة بنجاح، لا تنسَ مزامنة القواعد مع Nginx.');
    }
}
