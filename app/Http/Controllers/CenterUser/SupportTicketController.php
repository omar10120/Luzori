<?php

namespace App\Http\Controllers\CenterUser;

use App\Http\Controllers\Controller;
use App\Models\SupportTicket;
use App\Services\SupportChatService;
use App\Services\SupportTicketService;
use Illuminate\Http\Request;

class SupportTicketController extends Controller
{
    public function index(Request $request)
    {
        $center = SupportChatService::resolveActiveCenter();
        abort_unless($center, 404);

        $status = $request->query('status');
        $q = SupportTicket::query()
            ->where('center_id', $center->id)
            ->orderByDesc('updated_at');
        if ($status) {
            $q->where('status', $status);
        }

        return view('CenterUser.SubViews.SupportTicket.index', [
            'title' => __('locale.support_tickets'),
            'tickets' => $q->paginate(20)->withQueryString(),
            'status' => $status,
            'center' => $center,
        ]);
    }

    public function create()
    {
        $center = SupportChatService::resolveActiveCenter();
        abort_unless($center, 404);

        return view('CenterUser.SubViews.SupportTicket.create', [
            'title' => __('locale.new_support_ticket'),
            'center' => $center,
        ]);
    }

    public function store(Request $request, SupportTicketService $service)
    {
        $request->validate([
            'subject' => 'required|string|max:200',
            'body' => 'required|string|max:10000',
            'priority' => 'nullable|in:low,normal,high,urgent',
        ]);

        $center = SupportChatService::resolveActiveCenter();
        abort_unless($center, 404);

        $ticket = $service->createFromCenter(
            $center,
            auth('center_user')->user(),
            $request->only('subject', 'body', 'priority')
        );

        return redirect()->route('center_user.support-tickets.show', $ticket->id)
            ->with('success', __('general.success'));
    }

    public function show(int $id)
    {
        $center = SupportChatService::resolveActiveCenter();
        abort_unless($center, 404);

        $ticket = SupportTicket::with('replies')
            ->where('center_id', $center->id)
            ->findOrFail($id);

        return view('CenterUser.SubViews.SupportTicket.show', [
            'title' => __('locale.support_ticket') . ' #' . $ticket->id,
            'ticket' => $ticket,
            'center' => $center,
        ]);
    }

    public function reply(Request $request, int $id, SupportTicketService $service)
    {
        $request->validate(['body' => 'required|string|max:10000']);

        $center = SupportChatService::resolveActiveCenter();
        abort_unless($center, 404);

        $ticket = SupportTicket::where('center_id', $center->id)->findOrFail($id);
        $service->replyFromCenter($ticket, auth('center_user')->user(), $request->input('body'));

        return redirect()->route('center_user.support-tickets.show', $id)
            ->with('success', __('general.success'));
    }
}
