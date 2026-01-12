<?php

namespace App\Http\Controllers;

use App\Models\IpRule;
use Illuminate\Http\Request;

class IpRuleController extends Controller
{
    public function index()
    {
        $rules = IpRule::orderByDesc('created_at')->get();

        return view('waf.ip-rules', compact('rules'));
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'ip'   => 'required|ip',
            'type' => 'required|in:allow,block',
        ]);

        IpRule::create($data);

        $this->syncFiles();

        return redirect()->route('ip-rules.index')->with('status', 'تم حفظ القاعدة بنجاح.');
    }

    public function destroy(IpRule $ipRule)
    {
        $ipRule->delete();

        $this->syncFiles();

        return redirect()->route('ip-rules.index')->with('status', 'تم حذف القاعدة.');
    }

    /**
     * مزامنة قاعدة البيانات مع ملفات ModSecurity
     */
    protected function syncFiles(): void
    {
        $whitelist = IpRule::where('type', 'allow')
            ->pluck('ip')
            ->filter()
            ->implode(PHP_EOL);

        $blacklist = IpRule::where('type', 'block')
            ->pluck('ip')
            ->filter()
            ->implode(PHP_EOL);

        file_put_contents('/etc/nginx/modsec/whitelist.txt', $whitelist . PHP_EOL);
        file_put_contents('/etc/nginx/modsec/blacklist.txt', $blacklist . PHP_EOL);

        // إعادة تحميل Nginx (تحتاج صلاحيات sudo لـ www-data)
        @exec('sudo systemctl reload nginx > /dev/null 2>&1 &');
    }
}
