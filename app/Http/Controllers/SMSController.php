<?php

namespace App\Http\Controllers;

use App\Services\SMSGatewayService;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;

class SMSController extends Controller
{
    private SMSGatewayService $smsService;

    public function __construct(SMSGatewayService $smsService)
    {
        $this->smsService = $smsService;
    }

    public function sendSMS(Request $request): JsonResponse
    {
        $request->validate([
            'mobile' => 'required|string',
            'message' => 'required|string',
            'language' => 'sometimes|integer|in:1,2,3',
        ]);

        $language = $request->input('language', 1);
        $result = $this->smsService->sendSMS(
            $request->input('mobile'),
            $request->input('message'),
            $language
        );

        if ($result['success']) {
            return response()->json([
                'success' => true,
                'message' => 'SMS sent successfully',
                'data' => $result['data'] ?? $result['body'],
            ], 200);
        }

        return response()->json([
            'success' => false,
            'message' => 'Failed to send SMS',
            'error' => $result['error'] ?? $result['body'],
        ], 400);
    }

    public function sendSMSMultiple(Request $request): JsonResponse
    {
        $request->validate([
            'mobiles' => 'required|array|min:1',
            'mobiles.*' => 'required|string',
            'message' => 'required|string',
            'language' => 'sometimes|integer|in:1,2,3',
        ]);

        $language = $request->input('language', 1);
        $result = $this->smsService->sendSMSMultiple(
            $request->input('mobiles'),
            $request->input('message'),
            $language
        );

        if ($result['success']) {
            return response()->json([
                'success' => true,
                'message' => 'SMS sent successfully to multiple recipients',
                'data' => $result['data'] ?? $result['body'],
            ], 200);
        }

        return response()->json([
            'success' => false,
            'message' => 'Failed to send SMS',
            'error' => $result['error'] ?? $result['body'],
        ], 400);
    }

    public function sendEnglishSMS(Request $request): JsonResponse
    {
        $request->validate([
            'mobile' => 'required|string',
            'message' => 'required|string',
        ]);

        $result = $this->smsService->sendEnglishSMS(
            $request->input('mobile'),
            $request->input('message')
        );

        if ($result['success']) {
            return response()->json([
                'success' => true,
                'message' => 'English SMS sent successfully',
                'data' => $result['data'] ?? $result['body'],
            ], 200);
        }

        return response()->json([
            'success' => false,
            'message' => 'Failed to send SMS',
            'error' => $result['error'] ?? $result['body'],
        ], 400);
    }

    public function sendArabicSMS(Request $request): JsonResponse
    {
        $request->validate([
            'mobile' => 'required|string',
            'message' => 'required|string',
        ]);

        $result = $this->smsService->sendArabicSMS(
            $request->input('mobile'),
            $request->input('message')
        );

        if ($result['success']) {
            return response()->json([
                'success' => true,
                'message' => 'Arabic SMS sent successfully',
                'data' => $result['data'] ?? $result['body'],
            ], 200);
        }

        return response()->json([
            'success' => false,
            'message' => 'Failed to send SMS',
            'error' => $result['error'] ?? $result['body'],
        ], 400);
    }

    public function sendUnicodeSMS(Request $request): JsonResponse
    {
        $request->validate([
            'mobile' => 'required|string',
            'message' => 'required|string',
        ]);

        $result = $this->smsService->sendUnicodeSMS(
            $request->input('mobile'),
            $request->input('message')
        );

        if ($result['success']) {
            return response()->json([
                'success' => true,
                'message' => 'Unicode SMS sent successfully',
                'data' => $result['data'] ?? $result['body'],
            ], 200);
        }

        return response()->json([
            'success' => false,
            'message' => 'Failed to send SMS',
            'error' => $result['error'] ?? $result['body'],
        ], 400);
    }

    public function checkBalance(): JsonResponse
    {
        $result = $this->smsService->checkBalance();

        if ($result['success']) {
            return response()->json([
                'success' => true,
                'message' => 'Balance retrieved successfully',
                'data' => $result['data'] ?? $result['body'],
            ], 200);
        }

        return response()->json([
            'success' => false,
            'message' => 'Failed to check balance',
            'error' => $result['error'] ?? $result['body'],
        ], 400);
    }
}
