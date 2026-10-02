<?php

namespace App\Http\Controllers\Admin;

use App\Enums\PaymentStatus;
use App\Http\Controllers\Controller;
use App\Http\Requests\Payment\VerifyPaymentRequest;
use App\Models\Payment;
use App\Services\PaymentService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class AdminPaymentController extends Controller
{
    public function __construct(private readonly PaymentService $payments) {}

    public function index(Request $request): View
    {
        $payments = Payment::with(['order.customer', 'verifier'])
            ->when($request->filled('status'), fn ($query) => $query->where('status', $request->string('status')))
            ->latest()->paginate(15)->withQueryString();

        return view('admin.payments.index', compact('payments'));
    }

    public function verify(VerifyPaymentRequest $request, Payment $payment): RedirectResponse
    {
        try {
            $status = PaymentStatus::from($request->validated('status'));
            $status === PaymentStatus::PAID
                ? $this->payments->confirmPayment($payment, $request->user())
                : $this->payments->rejectPayment($payment, $request->user());

            return back()->with('success', $status === PaymentStatus::PAID ? 'Pembayaran dikonfirmasi lunas.' : 'Pembayaran ditolak dan customer dapat mengirim ulang.');
        } catch (\Exception $exception) {
            return back()->with('error', $exception->getMessage());
        }
    }
}
