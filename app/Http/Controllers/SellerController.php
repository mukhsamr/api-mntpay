<?php

namespace App\Http\Controllers;

use Illuminate\Support\Facades\Auth;

class SellerController extends Controller
{
    public function index()
    {
        /** @var User $user **/
        $user = Auth::user();

        $transaction = $user->sellerTransactions()->sum('nominal');
        $withdraw = $user->withdraws()->sum('nominal');

        $qrcode = [
            'type' => 'tr',
            'id' => $user->id,
            'no_hp' => $user->no_hp,
            'nama' => $user->nama
        ];

        $wd_qrcode = [
            'type' => 'wd',
            'id' => $user->id,
            'nama' => $user->nama,
            'no_hp' => $user->no_hp,
        ];

        return response()->json([
            'saldo' => number_format($transaction - $withdraw),
            'transaksi' => number_format($transaction),
            'withdraw' => number_format($withdraw),
            'qrcode' => $qrcode,
            'wd_qrcode' => $wd_qrcode,
        ]);
    }

    public function transactionSimpleList()
    {
        /** @var User $user **/
        $user = Auth::user();

        return response()->json(
            $user
                ->sellerTransactions()
                ->orderByDesc('id')
                ->withPengirim()
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
                ->sellerTransactions()
                ->orderByDesc('id')
                ->withPengirim()
                ->get()
                ->each(
                    fn ($val) => $val->nominal = number_format($val->nominal)
                )
        );
    }
}
