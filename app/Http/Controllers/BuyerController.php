<?php

namespace App\Http\Controllers;

use App\Models\Transaction;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;

class BuyerController extends Controller
{
    public function index()
    {
        /** @var User $user **/
        $user = Auth::user();

        $topup = $user->buyerTopups()->sum('nominal');
        $transaction = $user->buyerTransactions()->sum('nominal');
        $withdraw = $user->withdraws()->sum('nominal');

        return response()->json([
            'saldo' => number_format($topup - $transaction - $withdraw),
            'topup' => number_format($topup),
            'transaksi' => number_format($transaction),
            'withdraw' => number_format($withdraw),
            'wd_qrcode' => [
                'type' => 'wd',
                'id' => $user->id,
                'nama' => $user->nama,
                'no_hp' => $user->no_hp,
            ],
        ]);
    }

    public function transactionSimpleList()
    {
        /** @var User $user **/
        $user = Auth::user();

        return response()->json(
            $user
                ->buyerTransactions()
                ->orderByDesc('id')
                ->withPenerima()
                ->limit(6)
                ->get()
                ->each(
                    fn ($val) => $val->nominal = number_format($val->nominal)
                )
        );
    }

    public function transactionList()
    {
        /** @var User $user **/
        $user = Auth::user();

        return response()->json(
            $user
                ->buyerTransactions()
                ->orderByDesc('id')
                ->withPenerima()
                ->get()
                ->each(
                    fn ($val) => $val->nominal = number_format($val->nominal)
                )
        );
    }

    // Payment 

    public function store(Request $request)
    {
        /** @var User $user **/
        $user = Auth::user();

        $nominal = str_replace('.', '', $request->nominal);

        if (!$this->getSaldo($user->id, $nominal)) {
            return response()->json([
                'error' => 'Saldo tidak cukup',
            ], 403);
        }

        $seller = User::find($request->seller_id);
        $nota = 'TR' . $user->id . substr(time(), 4, 6);

        $data = [
            'nota' => $nota,
            'pengirim' => $user->id,
            'penerima' => $seller->id,
            'nominal' => $nominal
        ];

        try {
            Transaction::create($data);

            return response()->json([
                'nota' => $nota,
                'nominal' => $request->nominal,
                'sellerNama' => $seller->nama,
                'sellerNoHp' => $seller->no_hp
            ]);
        } catch (\Throwable $th) {

            Log::error($th->getMessage());

            return response()->json([
                'error' => $th->getMessage(),
            ], 500);
        }
    }

    public function checkPin(Request $request)
    {
        $cond = Hash::check($request->pin, Auth::user()->pin);
        return response()->json($cond);
    }

    // =========

    private function getSaldo($user_id, $nominal): bool
    {
        $user = User::withSum('buyerTopups as topup', 'nominal')
            ->withSum('buyerTransactions as transaction', 'nominal')
            ->find($user_id);

        return $user->topup - $nominal - $user->transaction >= 0;
    }
}
