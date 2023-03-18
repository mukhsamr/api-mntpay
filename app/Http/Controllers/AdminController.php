<?php

namespace App\Http\Controllers;

use App\Models\Topup;
use App\Models\Transaction;
use App\Models\User;
use App\Models\Withdraw;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;

class AdminController extends Controller
{
    public function index()
    {
        $topup = Topup::sum('nominal');
        $seller = Transaction::sum('nominal');
        $withdraw = Withdraw::sum('nominal');

        return response()->json([
            'total_saldo' => number_format($topup - $withdraw),
            'total_buyer' => number_format($topup - $seller),
            'total_seller' => number_format($seller - $withdraw),
            'total_withdraw' => number_format($withdraw),
        ]);
    }

    public function transactionList(?string $limit = null)
    {
        return response()->json(
            Transaction::orderByDesc('id')
                ->withPengirim()
                ->withPenerima()
                ->limit($limit)
                ->get()
                ->each(
                    fn ($val) => $val->nominal = number_format($val->nominal)
                )
        );
    }

    public function withdrawList()
    {
        return response()->json(
            Withdraw::orderByDesc('id')
                ->withPengirim()
                ->withPenerima()
                ->get()
                ->each(
                    fn ($val) => $val->nominal = number_format($val->nominal)
                )
        );
    }

    public function topupList()
    {
        return response()->json(
            Topup::orderByDesc('id')
                ->withPengirim()
                ->withPenerima()
                ->get()
                ->each(
                    fn ($val) => $val->nominal = number_format($val->nominal)
                )
        );
    }

    public function accountList(?string $tipe = null)
    {
        if ($tipe) {
            return response()->json(
                User::where('tipe', $tipe)
                    ->orderByDesc('id')
                    ->get()
            );
        }

        return response()->json(
            User::whereNot('tipe', 'A')
                ->orderByDesc('id')
                ->get()
        );
    }


    // Topup
    public function storeTopup(Request $request)
    {
        $user = Auth::user();
        $nota = 'TP' . $user->id . substr(time(), 4, 6);

        $nominal = str_replace('.', '', $request->nominal);

        $data = [
            'nota' => $nota,
            'pengirim' => $user->id,
            'penerima' => $request->buyer_id,
            'nominal' => $nominal
        ];

        try {
            Topup::create($data);

            $buyer = User::find($request->buyer_id);
            return response()->json([
                'nota' => $nota,
                'buyer' => $buyer->nama,
                'nominal' => $nominal
            ]);
        } catch (\Throwable $th) {

            Log::error($th->getMessage());

            return response()->json([
                'error' => $th->getMessage()
            ]);
        }
    }


    // Withdraw
    public function storeWithdraw(Request $request)
    {
        if (!$this->getSaldoSeller($request->seller_id, $request->nominal)) {
            return response()->json([
                'error' => 'Saldo tidak cukup',
            ], 403);
        }

        $user = Auth::user();

        $nominal = str_replace('.', '', $request->nominal);
        $nota = 'WD' . $request->admin_id . substr(time(), 4, 6);

        $data = [
            'nota' => $nota,
            'pengirim' => $user->id,
            'penerima' => $request->seller_id,
            'nominal' => $nominal
        ];

        try {
            Withdraw::create($data);

            $seller = User::find($request->seller_id);
            return response()->json([
                'nota' => $nota,
                'seller' => $seller->nama,
                'nominal' => $nominal
            ]);
        } catch (\Throwable $th) {

            Log::error($th->getMessage());

            return response()->json([
                'error' => $th->getMessage()
            ]);
        }
    }


    // User
    public function storeUser(Request $request)
    {
        $data = [
            'no_hp' => $request->no_hp,
            'nama' => $request->nama,
            'pin' => bcrypt($request->pin),
            'tipe' => $request->tipe
        ];

        try {
            $user = User::create($data);
            return response()->json($user);
        } catch (\Throwable $th) {

            Log::error($th->getMessage());

            return response()->json([
                'error' => $th->getMessage()
            ], 500);
        }
    }

    public function updateUser(Request $request)
    {
        $data = [
            'no_hp' => $request->no_hp,
            'nama' => $request->nama,
            'tipe' => $request->tipe
        ];

        if ($request->pin) {
            $data['pin'] = bcrypt($request->pin);
        }

        try {
            $find = User::find($request->id);
            $find->update($data);

            return response()->json($find);
        } catch (\Throwable $th) {

            Log::error($th->getMessage());

            return response()->json([
                'error' => $th->getMessage()
            ], 500);
        }
    }

    public function destroyUser(Request $request)
    {
        $user = User::find($request->user_id);
        try {
            $user->delete();
            return response()->json(true);
        } catch (\Throwable $th) {

            Log::error($th->getMessage());

            return response()->json([
                'error' => $th->getMessage()
            ], 500);
        }
    }


    // ========
    public function getSaldoSeller($seller_id, $nominal)
    {
        $user = User::withSum('sellerTransactions as masuk', 'nominal')
            ->withSum('sellerWithdraws as withdraw', 'nominal')
            ->find($seller_id);

        return $user->masuk - $nominal - $user->withdraw >= 0;
    }
}
