php<php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Models\Voucher;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class VoucherController extends Controller
{
    public function check(Request $request): JsonResponse
    {
        $data = $request->validate([
            'code' => ['required', 'string', 'max:50'],
            'destination_id' => ['required', 'integer', 'exists:destinations,id'],
            'amount' => ['required', 'numeric', 'min:0'],
        ]);

        $voucher = Voucher::where('code', Str::upper(trim($data['code'])))->first();

        if (! $voucher) {
            return response()->json(['message' => 'Kode voucher tidak ditemukan.'], 404);
        }

        if ($message = $voucher->isApplicable((int) $data['destination_id'], (float) $data['amount'])) {
            return response()->json(['message' => $message, 'valid' => false], 422);
        }

        return response()->json([
            'valid' => true,
            'data' => [
                'code' => $voucher->code,
                'type' => $voucher->type,
                'value' => (float) $voucher->value,
                'discount' => $voucher->discountFor((float) $data['amount']),
            ],
        ]);
    }
}
