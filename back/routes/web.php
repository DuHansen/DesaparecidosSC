<?php
use Illuminate\Http\Request;
use App\Http\Controllers\Api\DesaparecidosController;
use Illuminate\Support\Facades\Route;

Route::get('/desaparecidos/recentes', [DesaparecidosController::class, 'recentes']);
Route::post('/logout', function (Request $request) {
    // Verifica autenticação antes de fazer logout
    if (auth()->check()) {
        auth()->logout();
    }

    // Invalida a sessão
    $request->session()->invalidate();
    $request->session()->regenerateToken();

    return response()->json([
        'success' => true,
        'message' => 'Logout realizado com sucesso'
    ]);
})->middleware('web'); // Mantenha o middleware 'web' para sessão