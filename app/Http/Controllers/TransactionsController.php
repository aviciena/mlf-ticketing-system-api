<?php

namespace App\Http\Controllers;

use App\Helpers\Utils;
use App\Http\Requests\CreateTransactionRequest;
use App\Models\EventTicket;
use App\Models\Holder;
use App\Models\HolderCategories;
use App\Models\Ticket;
use App\Models\TicketStatus;
use App\Models\Transaction;
use App\Models\TransactionDetails;
use App\Models\ValidityTicket;
use Carbon\Carbon;
use Carbon\CarbonInterval;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;

use Xendit\Invoice\CreateInvoiceRequest;
use Xendit\Invoice\InvoiceApi;

class TransactionsController extends BaseController
{
    public function create(CreateTransactionRequest $request)
    {
        $validate = $request->validated();
        $totalTicket = 0;
        $ubTotal = 0;
        $transactionDetails = [];
        foreach ($validate['tickets'] as $ticket) {
            $eventTicket = EventTicket::find($ticket['id']);
            $totalTicket += $ticket['quantity'];
            if ($eventTicket->price) {
                $ubTotal += $eventTicket->price * $ticket['quantity'];
            }

            $tDetail = [
                'event_ticket_id' => $eventTicket->id,
                'qty' => $ticket['quantity'],
                'price' => $eventTicket->price,
                'subtotal' => $eventTicket->price * $ticket['quantity']
            ];

            array_push($transactionDetails, $tDetail);
        }

        $totalPrice = $ubTotal + $validate['donation_amount'] + $validate['fee'];
        $invoiceId = 'INV-' . date('YmdHis') . '-' . strtoupper(Str::random(4));

        $holderObj = [
            'name' => $validate['name'],
            'given_names' => $validate['name'],
            'mobile_phone' => $validate['phone'],
            'phone_number' => $validate['phone'],
            'email' => $validate['email'],
            'sex' => $validate['gender']
        ];

        $xenditItems = $validate['tickets'];
        $xenditItems[] = ['name' => 'fee', 'price' => $validate['fee'], 'quantity' => 1];
        if ($validate['donation_amount'] > 0) {
            $xenditItems[] = ['name' => 'donation', 'price' => $validate['donation_amount'], 'quantity' => 1];
        }

        // Create Xendit Request Payment
        $xenditResult = $this->doGetXendit([
            'invoiceId' => $invoiceId,
            'customer' => $holderObj,
            'tickets' => $xenditItems,
            'amount' => $totalPrice,
            'description' => 'Pembelian Tiket ' . $validate['event']['name']
        ]);

        if (is_null($xenditResult)) {
            return response()->json([
                'success' => false,
                'message' => 'Gagal terhubung ke layanan pembayaran Xendit.'
            ], 500);
        }

        try {
            DB::beginTransaction();

            $data = [
                'id' => $invoiceId,
                'event_id' => $validate['event']['id'],
                'donation' => $validate['donation_amount'],
                'fee' => $validate['fee'],
                'subtotal' => $ubTotal,
                'total_ticket' => $totalTicket,
                'total_price' => $totalPrice,
                'reference_code' => $xenditResult['invoice_url'],
                'status' => 'pending'
            ];

            // Create Transaction
            $transaction = Transaction::create($data);

            // Create Transaction Details
            foreach ($transactionDetails as $detail) {
                $detail['transaction_id'] = $transaction->id;
                TransactionDetails::create($detail);
            }

            // Looping for Create Ticket
            foreach ($validate['tickets'] as $ticket) {
                $eventTicket = EventTicket::find($ticket['id']);
                $holderCategoryId = HolderCategories::where('description', 'Visitor Online')->value('id');
                $holderObj['category_id'] = $holderCategoryId;
                $holder = Holder::create($holderObj);

                $paymentId = $eventTicket['event_ticket_category_id'];
                $validity = ValidityTicket::where('id', $eventTicket['validity_type_id'])->first();

                $ticketStatus = "Booked";

                if ($validity['code'] != "ad") {
                    $now = Carbon::now();
                    $endDate = Carbon::parse($eventTicket['end_date']);
                    if ($now->greaterThan($endDate)) {
                        $ticketStatus = 'Expired';
                    }
                }

                $ticketStatusId = TicketStatus::where('description', $ticketStatus)->value('id');

                $data = [
                    'transaction_id' => $transaction->id,
                    'events_ticket_id' => $ticket['id'],
                    'ticket_status_id' => $ticketStatusId,
                    'payment_status_id' => $paymentId,
                    'validity_ticket_id' => $validity['id'],
                    'validity_start_date' => $eventTicket['start_date'],
                    'validity_end_date' => $validity['code'] != "ad" ? $eventTicket['end_date'] : null,
                    'allow_multiple_checkin' => $eventTicket['allow_multiple_checkin'],
                    'holder_ticket_id' => $holder->id,
                    'created_by' => $validate['name']
                ];

                $ticketId = Utils::generateRandomString();

                // Create Ticket
                Ticket::create(array_merge($data, ['id' => $ticketId]));

                // Decrease event ticket quota
                $eventTicket->decrement('quota', $ticket['quantity']);
            }
            DB::commit();
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'success' => false,
                'message' => 'Gagal membuat transaksi',
                'detail' => $e->getMessage(),
            ], 500);
        }


        return $this->sendResponse([
            'invoice_url' => $xenditResult['invoice_url'],
            'transaction_id' => $transaction->id
        ], 'Transaction Created');
    }

    private function doGetXendit($data)
    {
        // Mengonversi durasi 15 menit menjadi total detik (900)
        $expiredDuration = CarbonInterval::minutes(15)->totalSeconds;

        $apiInstance = new InvoiceApi();
        $createInvoiceRequest = new CreateInvoiceRequest([
            'external_id' => $data['invoiceId'],
            'description' => $data['description'],
            'amount' => $data['amount'],
            'invoice_duration' => $expiredDuration,
            'currency' => 'IDR',
            'customer' => $data['customer'],
            'items' => $data['tickets'],
            'success_redirect_url' => config('services.xendit.success_redirect_url'),
            'failure_redirect_url' => config('services.xendit.failure_redirect_url'),
        ]);

        try {
            return $apiInstance->createInvoice($createInvoiceRequest);
        } catch (\Xendit\XenditSdkException $e) {
            Log::info('Exception when calling InvoiceApi->createInvoice: ' . $e->getMessage());
            Log::info('Full Error: ' . json_encode($e->getFullError()));
            return null;
        }
    }
}
