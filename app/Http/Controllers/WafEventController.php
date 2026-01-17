<?php

namespace App\Http\Controllers;

use App\Models\WafEvent;
use App\Services\AiAnalysisService;
use Illuminate\Http\Request;

class WafEventController extends Controller
{
    protected $aiService;

    public function __construct(AiAnalysisService $aiService)
    {
        $this->aiService = $aiService;
    }

    /**
     * تحليل حدث واحد بالـ AI
     */
    public function analyze(Request $request, WafEvent $event)
    {
        $result = $this->aiService->analyzeWafEvent($event);

        if ($result['success']) {
            return response()->json([
                'success' => true,
                'analysis' => $result['analysis'],
                'event' => [
                    'id' => $event->id,
                    'client_ip' => $event->client_ip,
                    'country' => $event->country,
                    'host' => $event->host,
                    'uri' => $event->uri,
                    'method' => $event->method,
                    'status' => $event->status,
                    'rule_id' => $event->rule_id,
                    'message' => $event->message,
                    'event_time' => $event->event_time->format('Y-m-d H:i:s'),
                ],
            ]);
        }

        return response()->json([
            'success' => false,
            'error' => $result['error'] ?? 'فشل التحليل',
        ], 500);
    }

    /**
     * تحليل مجموعة أحداث (للأنماط)
     */
    public function analyzePattern(Request $request)
    {
        $eventIds = $request->input('event_ids', []);
        
        if (empty($eventIds) || count($eventIds) > 20) {
            return response()->json([
                'success' => false,
                'error' => 'يرجى اختيار من 1 إلى 20 حدثاً',
            ], 400);
        }

        $events = WafEvent::whereIn('id', $eventIds)->get();
        
        $result = $this->aiService->analyzeEventPattern($events->toArray());

        if ($result['success']) {
            return response()->json([
                'success' => true,
                'analysis' => $result['analysis'],
                'event_count' => $events->count(),
            ]);
        }

        return response()->json([
            'success' => false,
            'error' => $result['error'] ?? 'فشل التحليل',
        ], 500);
    }
}
