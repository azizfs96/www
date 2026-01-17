<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;

class AddCountryRulesToMainConf extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'waf:add-country-rules-to-main-conf';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Add country-rules.conf to main.conf';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $mainConf = '/etc/nginx/modsec/main.conf';
        $includeLine = 'Include /etc/nginx/modsec/country-rules.conf';

        if (!file_exists($mainConf)) {
            $this->error("File not found: {$mainConf}");
            return 1;
        }

        $content = file_get_contents($mainConf);

        // التحقق من وجود السطر
        if (strpos($content, $includeLine) !== false) {
            $this->info("✅ country-rules.conf is already included in main.conf");
            return 0;
        }

        // إضافة السطر بعد url-rules.conf
        if (strpos($content, 'Include /etc/nginx/modsec/url-rules.conf') !== false) {
            $newContent = str_replace(
                'Include /etc/nginx/modsec/url-rules.conf',
                "Include /etc/nginx/modsec/url-rules.conf\n\nInclude /etc/nginx/modsec/country-rules.conf",
                $content
            );
        } else {
            // إذا لم نجد url-rules.conf، نضيف في النهاية
            $newContent = rtrim($content) . "\n\n{$includeLine}\n";
        }

        // عرض التغييرات
        $this->info("Adding to main.conf:");
        $this->line($includeLine);

        // حفظ الملف
        if (file_put_contents($mainConf, $newContent)) {
            $this->info("✅ Successfully added country-rules.conf to main.conf");
            $this->warn("⚠️  Please test nginx config: sudo nginx -t");
            $this->warn("⚠️  Then reload: sudo systemctl reload nginx");
            return 0;
        } else {
            $this->error("❌ Failed to write to main.conf (may need sudo)");
            $this->line("");
            $this->line("Please add this line manually to {$mainConf}:");
            $this->line($includeLine);
            return 1;
        }
    }
}

