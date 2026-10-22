<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\SupportTicket;
use App\Services\SupportTicketService;
use Illuminate\Http\Request;

class SupportTicketController extends Controller
{
    public function index(Request $request)
    {
        $status = $request->query('status');
        $q = SupportTicket::query()->with('center:id,name,domain')->orderByDesc('updated_at');
        if ($status) {
            $q->where('status', $status);
        }

        $tickets = $q->paginate(20)->withQueryString();

        return view('Admin.SubViews.SupportTicket.index', [
            'title' => __('locale.support_tickets'),
            'tickets' => $tickets,
            'status' => $status,
            'counts' => [
                'all' => SupportTicket::count(),
                'open' => SupportTicket::where('status', 'open')->count(),
                'in_progress' => SupportTicket::where('status', 'in_progress')->count(),
                'resolved' => SupportTicket::where('status', 'resolved')->count(),
                'closed' => SupportTicket::where('status', 'closed')->count(),
            ],
        ]);
    }

    public function show(int $id)
    {
        $ticket = SupportTicket::with(['center:id,name,domain', 'replies'])->findOrFail($id);

        return view('Admin.SubViews.SupportTicket.show', [
            'title' => __('locale.support_ticket') . ' #' . $ticket->id,
            'ticket' => $ticket,
        ]);
    }

    public function reply(Request $request, int $id, SupportTicketService $service)
    {
        $request->validate([
            'body' => 'required|string|max:10000',
            'status' => 'nullable|in:open,in_progress,resolved,closed',
        ]);

        $ticket = SupportTicket::findOrFail($id);
        $service->replyFromAdmin(
            $ticket,
            auth('admin')->user(),
            $request->input('body'),
            $request->input('status')
        );

        return redirect()->route('admin.support-tickets.show', $id)
            ->with('success', __('general.success'));
    }

    public function updateStatus(Request $request, int $id, SupportTicketService $service)
    {
        $request->validate(['status' => 'required|in:open,in_progress,resolved,closed']);
        $ticket = SupportTicket::findOrFail($id);
        $service->updateStatus($ticket, $request->input('status'));

        return back()->with('success', __('general.success'));
    }
}
