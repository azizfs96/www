<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use App\Models\WafEvent;

class AiAnalysisService
{
    protected $apiKey;
    protected $apiUrl;

    public function __construct()
    {
        $this->apiKey = config('services.openai.api_key');
        $this->apiUrl = 'https://api.openai.com/v1/chat/completions';
    }

    /**
     * تحليل حدث WAF باستخدام AI
     */
    public function analyzeWafEvent(WafEvent $event): array
    {
        if (!$this->apiKey) {
            return [
                'success' => false,
                'error' => 'WAF Gate AI not enabled yet',
            ];
        }

        try {
            $prompt = $this->buildPrompt($event);
            
            $response = Http::timeout(30)
                ->withHeaders([
                    'Authorization' => "Bearer {$this->apiKey}",
                    'Content-Type' => 'application/json',
                ])
                ->post($this->apiUrl, [
                    'model' => 'gpt-4o-mini', // أرخص وأسرع من gpt-4
                    'messages' => [
                        [
                            'role' => 'system',
                            'content' => 'You are a cybersecurity expert specialized in Web Application Firewall (WAF) analysis. Analyze security events and provide clear, actionable insights in Arabic.',
                        ],
                        [
                            'role' => 'user',
                            'content' => $prompt,
                        ],
                    ],
                    'temperature' => 0.3,
                    'max_tokens' => 1000,
                ]);

            if ($response->successful()) {
                $data = $response->json();
                $analysis = $data['choices'][0]['message']['content'] ?? 'No analysis available';

                return [
                    'success' => true,
                    'analysis' => $analysis,
                    'tokens_used' => $data['usage']['total_tokens'] ?? 0,
                ];
            }

            return [
                'success' => false,
                'error' => 'Failed to get AI analysis: ' . $response->body(),
            ];

        } catch (\Exception $e) {
            Log::error('AI Analysis Error: ' . $e->getMessage());
            
            return [
                'success' => false,
                'error' => 'Error connecting to AI service: ' . $e->getMessage(),
            ];
        }
    }

    /**
     * بناء prompt للـ AI
     */
    protected function buildPrompt(WafEvent $event): string
    {
        $prompt = "قم بتحليل هذا الحدث الأمني من WAF وقدم تحليلاً مفصلاً بالعربية:\n\n";
        
        $prompt .= "**معلومات الحدث:**\n";
        $prompt .= "- الوقت: {$event->event_time}\n";
        $prompt .= "- IP المصدر: {$event->client_ip}\n";
        
        if ($event->country) {
            $prompt .= "- الدولة: {$event->country}\n";
        }
        
        $prompt .= "- الموقع المستهدف: {$event->host}\n";
        $prompt .= "- المسار: {$event->uri}\n";
        $prompt .= "- HTTP Method: {$event->method}\n";
        $prompt .= "- الحالة: {$event->status}\n";
        
        if ($event->rule_id) {
            $prompt .= "- Rule ID: {$event->rule_id}\n";
        }
        
        if ($event->severity) {
            $prompt .= "- Severity: {$event->severity}\n";
        }
        
        if ($event->message) {
            $prompt .= "- الرسالة: {$event->message}\n";
        }
        
        if ($event->user_agent) {
            $prompt .= "- User Agent: {$event->user_agent}\n";
        }
        
        $prompt .= "\n**المطلوب:**\n";
        $prompt .= "1. 🎯 **نوع الهجوم**: ما نوع الهجوم المحتمل؟\n";
        $prompt .= "2. ⚠️ **مستوى الخطورة**: هل هذا تهديد حقيقي أم False Positive؟\n";
        $prompt .= "3. 🔍 **التفاصيل**: شرح تفصيلي لما حدث\n";
        $prompt .= "4. 💡 **التوصيات**: ماذا يجب فعله؟\n";
        $prompt .= "5. 🛡️ **الإجراءات**: هل يجب حظر هذا IP؟\n\n";
        
        $prompt .= "قدم إجابة منظمة ومختصرة باستخدام العناوين أعلاه.";
        
        return $prompt;
    }

    /**
     * تحليل مجموعة أحداث (للأنماط)
     */
    public function analyzeEventPattern(array $events): array
    {
        if (!$this->apiKey) {
            return [
                'success' => false,
                'error' => 'WAF Gate AI not enabled yet',
            ];
        }

        try {
            $prompt = $this->buildPatternPrompt($events);
            
            $response = Http::timeout(30)
                ->withHeaders([
                    'Authorization' => "Bearer {$this->apiKey}",
                    'Content-Type' => 'application/json',
                ])
                ->post($this->apiUrl, [
                    'model' => 'gpt-4o-mini',
                    'messages' => [
                        [
                            'role' => 'system',
                            'content' => 'You are a cybersecurity expert. Analyze patterns in WAF events and identify coordinated attacks or suspicious behavior in Arabic.',
                        ],
                        [
                            'role' => 'user',
                            'content' => $prompt,
                        ],
                    ],
                    'temperature' => 0.3,
                    'max_tokens' => 1200,
                ]);

            if ($response->successful()) {
                $data = $response->json();
                $analysis = $data['choices'][0]['message']['content'] ?? 'No analysis available';

                return [
                    'success' => true,
                    'analysis' => $analysis,
                ];
            }

            return [
                'success' => false,
                'error' => 'Failed to analyze pattern',
            ];

        } catch (\Exception $e) {
            return [
                'success' => false,
                'error' => $e->getMessage(),
            ];
        }
    }

    /**
     * بناء prompt لتحليل الأنماط
     */
    protected function buildPatternPrompt(array $events): string
    {
        $prompt = "حلل هذه المجموعة من أحداث WAF وابحث عن أنماط هجومية:\n\n";
        
        foreach ($events as $i => $event) {
            $prompt .= "حدث " . ($i + 1) . ":\n";
            $prompt .= "- IP: {$event->client_ip}\n";
            $prompt .= "- وقت: {$event->event_time}\n";
            $prompt .= "- مسار: {$event->uri}\n";
            $prompt .= "- Rule: {$event->rule_id}\n\n";
        }
        
        $prompt .= "\n**ابحث عن:**\n";
        $prompt .= "1. هل هذا هجوم منسق؟\n";
        $prompt .= "2. هل هناك IPs مشتركة؟\n";
        $prompt .= "3. ما نوع الهجوم المحتمل؟\n";
        $prompt .= "4. التوصيات لإيقاف الهجوم\n";
        
        return $prompt;
    }
}
