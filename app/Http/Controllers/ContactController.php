<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Validator;

class ContactController extends Controller
{
    public function sendMessage(Request $request)
    {
        // Validate the request
        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:255',
            'email' => 'required|email|max:255',
            'country_code' => 'nullable|string|max:5',
            'phone' => 'nullable|string|max:20',
            'subject' => 'nullable|string|max:255',
            'message' => 'required|string|max:2000',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validation failed',
                'errors' => $validator->errors()
            ], 422);
        }

        try {
            // Prepare email data
            $emailData = [
                'name' => $request->name,
                'email' => $request->email,
                'phone' => trim(($request->country_code ? ($request->country_code . ' ') : '') . ($request->phone ?? '')),
                'subject' => $request->subject ?: 'Contact Form Submission',
                'messageContent' => $request->message,
                'timestamp' => now()->format('Y-m-d H:i:s'),
                'ip_address' => $request->ip(),
                'user_agent' => $request->userAgent(),
            ];

            // Send email using configured SMTP mailer
            Mail::mailer('smtp')->send('emails.contact', $emailData, function ($message) use ($emailData) {
                $message->from('support@etechnocode.com', 'Luzori Support')
                        ->to('support@etechnocode.com')
                        ->replyTo($emailData['email'], $emailData['name'])
                        ->subject('New Contact Form Submission: ' . $emailData['subject']);
            });

            return response()->json([
                'success' => true,
                'message' => 'Your message has been sent successfully! We\'ll get back to you soon.'
                
            ]);

        } catch (\Exception $e) {
            \Log::error('Contact form email failed: ' . $e->getMessage());
            
            return response()->json([
                'success' => false,
                'message' => 'Sorry, there was an error sending your message. Please try again later.'
            ], 500);
        }
    }
}
