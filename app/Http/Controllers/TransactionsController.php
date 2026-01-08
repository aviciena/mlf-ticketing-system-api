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
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

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
        $data = [
            'id' => 'INV-' . date('YmdHis') . '-' . strtoupper(Str::random(4)),
            'event_id' => $validate['event']['id'],
            'donation' => $validate['donation_amount'],
            'fee' => $validate['fee'],
            'subtotal' => $ubTotal,
            'total_ticket' => $totalTicket,
            'total_price' => $totalPrice,
            'status' => 'pending'
        ];


        try {
            DB::beginTransaction();

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
                $holder = Holder::create([
                    'category_id' => $holderCategoryId,
                    'name' => $validate['name'],
                    'mobile_phone' => $validate['phone'],
                    'email' => $validate['email'],
                    'sex' => $validate['gender']
                ]);

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


        return $this->sendResponse($transaction, 'Transaction Created');
    }
}
