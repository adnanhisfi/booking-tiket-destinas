<?php

namespace App\Http\Controllers\Web\Admin;

use App\Http\Controllers\Controller;
use App\Services\TicketService;
use Illuminate\Http\Request;
use Illuminate\View\View;

class ScanController extends Controller
{
    public function index(): View
    {
        return view('admin.scan');
    }

    public function scan(Request $request, TicketService $tickets): View
    {
        $data = $request->validate([
            'code' => ['required', 'string', 'max:100'],
        ], [
            'code.required' => 'Kode tiket atau isi QR wajib diisi.',
        ]);

        $code = trim($data['code']);

        if (str_starts_with($code, 'JLK:')) {
            $code = substr($code, 4);
        }

        $result = $tickets->validate($code, $request->user());

        return view('admin.scan', [
            'result' => $result,
            'code' => $data['code'],
        ]);
    }
}
