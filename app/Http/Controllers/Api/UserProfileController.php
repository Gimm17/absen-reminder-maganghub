<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class UserProfileController extends Controller
{
    /** GET /api/user?user_id=X — info ringkas user untuk header dashboard. */
    public function show(Request $request): JsonResponse
    {
        $data = $request->validate([
            'user_id' => ['required', 'integer', 'exists:users,id'],
        ]);

        $user = User::findOrFail($data['user_id']);

        return response()->json([
            'id'    => $user->id,
            'name'  => $user->name,
            'email' => $user->email,
            'slots' => $user->activeSlots(),
        ]);
    }

    /**
     * POST /api/user/email — ganti/daftarkan email penerima notifikasi.
     * Body: { user_id, email }
     */
    public function updateEmail(Request $request): JsonResponse
    {
        $data = $request->validate([
            'user_id' => ['required', 'integer', 'exists:users,id'],
            'email'   => ['required', 'email', 'max:190', Rule::unique('users', 'email')->ignore($request->input('user_id'))],
        ]);

        $user = User::findOrFail($data['user_id']);

        // Email adalah kunci identitas user (dibuat saat subscribe pertama).
        // Kalau diganti, endpoint push yang sudah ada tetap menempel ke user yang sama.
        $user->email = $data['email'];
        $user->save();

        return response()->json([
            'ok'      => true,
            'email'   => $user->email,
            'message' => 'Email notifikasi berhasil diperbarui.',
        ]);
    }

    /**
     * PATCH /api/user/slots — aktif/nonaktifkan slot reminder.
     * Body: { user_id, notify_slot_1?, notify_slot_2?, notify_slot_3? }
     */
    public function updateSlots(Request $request): JsonResponse
    {
        $data = $request->validate([
            'user_id'        => ['required', 'integer', 'exists:users,id'],
            'notify_slot_1'  => ['sometimes', 'boolean'],
            'notify_slot_2'  => ['sometimes', 'boolean'],
            'notify_slot_3'  => ['sometimes', 'boolean'],
        ]);

        $user = User::findOrFail($data['user_id']);

        foreach (['notify_slot_1', 'notify_slot_2', 'notify_slot_3'] as $col) {
            if (array_key_exists($col, $data)) {
                $user->{$col} = (bool) $data[$col];
            }
        }
        $user->save();

        return response()->json([
            'ok'    => true,
            'slots' => $user->activeSlots(),
        ]);
    }
}